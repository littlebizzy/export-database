<?php

/**
 * Export Database – Export class
 *
 * @package Export Database
 * @subpackage Export Database Core
 */
class EXPDBS_Core_Export {



	// Constants
	// ---------------------------------------------------------------------------------------------------



	/**
	 * Rows per query
	 */
	const ROWS_PER_QUERY = 1000;



	/**
	 * Inactivity required for cleanup
	 */
	const CLEANUP_MIGRATION_TIME = 300;



	// Main methods
	// ---------------------------------------------------------------------------------------------------



	/**
	 * Start the migration
	 */
	public static function start( $compress = false ) {

		self::no_time_limit();

		// ensure migrations folder is valid
		$folder = self::get_migrations_folder();
		EXPDBS_Core::ensure_folder( $folder );

		$key  = md5( EXPDBS_FILE . microtime() . rand( 0, 999999 ) );
		$path = self::get_migration_path( $key );

		// write head
		if ( ! EXPDBS_Core::safe_write( $path, self::get_head() ) ) {
			wp_die( 'Cannot write initial SQL file: ' . esc_html( $path ) );
		}

		self::add_migration( $key );

		$zip  = $compress ? ( extension_loaded( 'zip' ) && class_exists( 'ZipArchive' ) ) : false;
		$gzip = $compress ? function_exists( 'gzopen' ) : false;

		$migration = array(
			'key'    => $key,
			'total'  => array( 'index' => 0, 'rows' => 0, 'done' => false ),
			'info'   => array(),
			'struct' => array(),
			'zip'    => $zip  ? md5( $key . EXPDBS_FILE . microtime() . rand( 0, 999999 ) ) : false,
			'gzip'   => $gzip ? md5( $key . EXPDBS_FILE . microtime() . rand( 0, 999999 ) ) : false,
		);

		$tables = self::get_tables();

		if ( ! empty( $tables ) && is_array( $tables ) ) {

			foreach ( $tables as $table ) {

				$rows_count = self::get_table_rows_count( $table );
				$migration['total']['rows'] += $rows_count;

				$migration['info'][ $table ] = array(
					'index' => 0,
					'rows'  => $rows_count,
					'done'  => false,
				);

				$migration['struct'][ $table ] = self::get_table_struct( $table );
			}
		}

		self::set_migration( $key, $migration );

		return $key;
	}



	/**
	 * Export process
	 */
	public static function export( $key ) {

		self::no_time_limit();

		if ( ! self::touch_migration( $key ) ) {
			return false;
		}

		$migration = self::get_migration( $key );
		if ( false === $migration ) {
			return false;
		}

		if ( ! isset( $migration['info'] ) || ! isset( $migration['total'] ) ) {
			return false;
		}

		// ensure folder exists
		$folder = self::get_migrations_folder();
		EXPDBS_Core::ensure_folder( $folder );

		$path  = self::get_migration_path( $key );
		$chunk = '';

		foreach ( $migration['info'] as $table => $info ) {

			if ( $info['done'] ) {
				continue;
			}

			$data_end  = "#\n";
			$data_end .= '# End of data rows of table ' . self::bq( $table ) . "\n";
			$data_end .= '# --------------------------------------------------------' . "\n\n\n";

			if ( empty( $info['index'] ) ) {

				$chunk .= "\n#\n# Delete if exists " . self::bq( $table ) . " table\n#\n\n";
				$chunk .= 'DROP TABLE IF EXISTS ' . self::bq( $table ) . ";\n\n";

				$create_table_query = self::create_table_query( $table );
				if ( false === $create_table_query ) {
					return false;
				}

				$chunk .= "#\n# Table structure of " . self::bq( $table ) . "\n#\n\n";
				$chunk .= $create_table_query . "\n\n";

				$chunk .= "#\n# Rows of table " . self::bq( $table ) . "\n#\n\n";
			}

			if ( empty( $info['rows'] ) ) {
				$chunk .= $data_end;
				$migration['info'][ $table ]['done'] = true;
				continue;
			}

			$table_rows = self::get_table_rows( $table, $migration );
			if ( false === $table_rows ) {
				return false;
			}

			$chunk .= $table_rows;

			if ( $migration['info'][ $table ]['done'] ) {
				$chunk .= $data_end;
			}

			if ( '' === $table_rows ) {

				$migration['info'][ $table ]['done'] = true;

				if ( ! empty( $info['index'] ) ) {
					$chunk .= "\n";
				}

				$chunk .= $data_end;
				continue;
			}

			// append safely (fixing the "temporary expression" fatal error)
			if ( ! EXPDBS_Core::safe_write( $path, $chunk, true ) ) {
				wp_die( 'Failed writing SQL chunk: ' . esc_html( $path ) );
			}

			break;
		}

		if ( empty( $migration['total']['rows'] ) || $migration['total']['index'] >= $migration['total']['rows'] ) {
			$migration['total']['done'] = true;
		}

		self::set_migration( $key, $migration );

		return $migration;
	}



	/**
	 * Compress generated file
	 */
	public static function compress( $key ) {

		if ( ! self::touch_migration( $key ) ) {
			wp_die( 'Could not find the migration in the summary file by key: ' . esc_html( $key ) );
		}

		$migration = self::get_migration( $key );
		if ( false === $migration ) {
			wp_die( 'Could not find the migration file by key: ' . esc_html( $key ) );
		}

		$folder = self::get_migrations_folder();
		EXPDBS_Core::ensure_folder( $folder );

		$result   = true;
		$path_sql = self::get_migration_path( $key );

		if ( ! is_readable( $path_sql ) ) {
			wp_die( 'Migration file is not readable: ' . esc_html( $path_sql ) );
		}

		// ZIP
		if ( $migration['zip'] ) {

			self::add_migration( $migration['zip'] );

			$zip = new ZipArchive();

			if ( true !== $zip->open( self::get_migration_path( $migration['zip'] ), ZipArchive::CREATE ) ) {

				$result = false;
				$migration['zip'] = false;

			} else {

				if ( ! $zip->addFile( $path_sql, sanitize_file_name( DB_NAME ) . '.sql' ) ) {
					$result = false;
					$migration['zip'] = false;
				}

				if ( ! $zip->close() ) {
					$result = false;
					$migration['zip'] = false;
				}
			}

			if ( ! $result ) {
				self::set_migration( $key, $migration );
			}
		}

		// GZIP
		if ( $migration['gzip'] && ! $migration['zip'] ) {

			self::add_migration( $migration['gzip'] );

			$fp_out = @gzopen( self::get_migration_path( $migration['gzip'] ), 'wb9' );

			if ( false === $fp_out ) {

				$result = false;
				$migration['gzip'] = false;

			} else {

				$fp_in = @fopen( $path_sql, 'rb' );

				if ( false === $fp_in ) {

					@gzclose( $fp_out );
					$result = false;
					$migration['gzip'] = false;

				} else {

					while ( ! feof( $fp_in ) ) {
						@gzwrite( $fp_out, fread( $fp_in, 1024 * 512 ) );
					}

					@fclose( $fp_in );
					@gzclose( $fp_out );
				}
			}

			if ( ! $result ) {
				self::set_migration( $key, $migration );
			}
		}

		if ( $result ) {
			EXPDBS_Core::safe_unlink( $path_sql );
		}

		return $result;
	}



	/**
	 * Finally download file
	 */
	public static function download( $key ) {

		self::no_time_limit();

		if ( ! self::touch_migration( $key ) ) {
			wp_die( 'Could not find the migration in the summary file by key: ' . esc_html( $key ) );
		}

		$migration = self::get_migration( $key );
		if ( false === $migration ) {
			wp_die( 'Could not find the migration file by key: ' . esc_html( $key ) );
		}

		$ext = '';

		if ( $migration['zip'] ) {
			$ext = '.zip';
			$key = $migration['zip'];
		} elseif ( $migration['gzip'] ) {
			$ext = '.gz';
			$key = $migration['gzip'];
		}

		$path = self::get_migration_path( $key );

		if ( ! file_exists( $path ) ) {
			wp_die( 'Could not find the migration file:<br />' . esc_html( $path ) );
		}

		if ( ! is_readable( $path ) ) {
			wp_die( 'Migration file is not readable: ' . esc_html( $path ) );
		}

		@header( 'Content-Description: File Transfer' );
		@header( 'Content-Type: application/octet-stream' );
		@header( 'Content-Length: ' . @filesize( $path ) );
		@header( 'Content-Disposition: attachment; filename=' . sanitize_file_name( DB_NAME ) . '.sql' . $ext );

		$data = EXPDBS_Core::safe_read( $path );
		echo $data;

		EXPDBS_Core::safe_unlink( $path );

		die;
	}



	// Folder
	// ---------------------------------------------------------------------------------------------------



	public static function check_migrations_folder( &$folder ) {

		$folder = self::get_migrations_folder();
		return EXPDBS_Core::ensure_folder( $folder );
	}



	/**
	 * Do a cleanup of old migrations
	 */
	public static function cleanup() {

		$migrations = @json_decode( get_option( 'expdbs_migrations' ), true );

		if ( empty( $migrations ) || ! is_array( $migrations ) ) {
			return;
		}

		$remove = array();

		foreach ( $migrations as $key => $timestamp ) {

			if ( 32 != strlen( $key ) ) {
				continue;
			}

			$timestamp = EXPDBS_Core::sanitize_timestamp( $timestamp );

			if ( empty( $timestamp ) || time() - $timestamp > self::CLEANUP_MIGRATION_TIME ) {
				$remove[] = $key;
			}
		}

		if ( empty( $remove ) ) {
			return;
		}

		$migrations_new = array();

		foreach ( $migrations as $key => $timestamp ) {

			if ( in_array( $key, $remove, true ) ) {

				delete_option( 'expdbs_migrations_' . $key );

				$path = self::get_migration_path( $key );
				EXPDBS_Core::safe_unlink( $path );

				continue;
			}

			$migrations_new[ $key ] = $timestamp;
		}

		update_option( 'expdbs_migrations', wp_json_encode( $migrations_new ), false );
	}



	// Internal methods
	// ---------------------------------------------------------------------------------------------------



	/**
	 * Retrieve head
	 */
	private static function get_head() {

		$head  = "# WordPress MySQL database export\n";
		$head .= "#\n";
		$head .= '# Generated: ' . date( 'l j. F Y H:i T' ) . "\n";
		$head .= '# Hostname: ' . DB_HOST . "\n";
		$head .= '# Database: ' . self::bq( DB_NAME ) . "\n";
		$head .= "# --------------------------------------------------------\n\n";
		$head .= '/*!40101 SET NAMES ' . ( defined( 'DB_CHARSET' ) ? DB_CHARSET : 'utf8' ) . " */;\n\n";
		$head .= "SET sql_mode='NO_AUTO_VALUE_ON_ZERO';\n\n";

		return $head;
	}



	/**
	 * Retrieve WP database tables
	 */
	private static function get_tables() {

		global $wpdb;

		$tables = array();

		$results = $wpdb->get_results( 'SHOW FULL TABLES', ARRAY_N );

		if ( ! empty( $results ) && is_array( $results ) ) {

			foreach ( $results as $table ) {

				if ( 0 !== strpos( $table[0], $wpdb->base_prefix ) || 'VIEW' === $table[1] ) {
					continue;
				}

				$tables[] = $table[0];
			}
		}

		return $tables;
	}



	/**
	 * Number of rows of specific table
	 */
	private static function get_table_rows_count( $table ) {

		global $wpdb;
		return (int) $wpdb->get_var( 'SELECT COUNT(*) FROM ' . $table );
	}



	/**
	 * Retrieve table structure
	 */
	private static function get_table_struct( $table ) {

		global $wpdb;

		$description = $wpdb->get_results( 'DESCRIBE ' . self::bq( $table ) );

		if ( empty( $description ) || ! is_array( $description ) ) {
			return false;
		}

		$int_types = array( 'tinyint', 'smallint', 'mediumint', 'int', 'bigint' );
		$struct    = array();

		foreach ( $description as $item ) {

			$info = array(
				'field'   => $item->Field,
				'type'    => $item->Type,
				'int'     => false,
				'binary'  => false,
				'bit'     => false,
				'prikey'  => false,
				'default' => ( null === $item->Default ) ? 'NULL' : $item->Default,
			);

			$type = strtolower( $item->Type );

			foreach ( $int_types as $int_type ) {
				if ( 0 === strpos( $type, $int_type ) ) {
					$info['int'] = true;
					break;
				}
			}

			if ( ! $info['int'] ) {

				if ( 0 === strpos( $type, 'binary' ) ) {
					$info['binary'] = true;

				} elseif ( 0 === strpos( $type, 'bit' ) ) {
					$info['bit'] = true;
				}
			}

			if ( 'PRI' === $item->Key ) {
				$info['prikey'] = true;
			}

			$struct[ $item->Field ] = $info;
		}

		return $struct;
	}



	/**
	 * Compose the create table
	 */
	private static function create_table_query( $table ) {

		global $wpdb;

		$result = $wpdb->get_results( 'SHOW CREATE TABLE ' . self::bq( $table ), ARRAY_N );

		if ( empty( $result ) || ! is_array( $result ) || empty( $result[0][1] ) ) {
			return false;
		}

		$query = $result[0][1];
		$query = str_replace( 'TYPE=', 'ENGINE=', $query );

		return $query . ';';
	}



	/**
	 * Dump table values
	 */
	private static function get_table_rows( $table, &$migration ) {

		global $wpdb;

		$primary_keys = array();
		$first_field  = null;

		foreach ( $migration['struct'][ $table ] as $field => $struct ) {

			if ( ! isset( $first_field ) ) {
				$first_field = $field;
			}

			if ( $struct['prikey'] ) {
				$primary_keys[] = $field;
			}
		}

		if ( empty( $first_field ) && empty( $primary_keys ) ) {
			return '';
		}

		$info   = $migration['info'][ $table ];
		$select = 'SELECT ' . self::bq( $table ) . '.*';
		$bins   = '';
		$bits   = '';

		foreach ( $migration['struct'][ $table ] as $field => $struct ) {

			if ( $struct['binary'] ) {

				$bins .= ', HEX(' . self::bq( $field ) . ') as ' . self::bq( strtolower( $field ) . '__hex' );

			} elseif ( $struct['bit'] ) {

				$bits .= ', ' . self::bq( $field ) . '+0 as ' . self::bq( strtolower( $field ) . '__bit' );
			}
		}

		$select .= $bins . $bits;

		$from = ' FROM ' . self::bq( $table );

		$order = empty( $primary_keys )
			? self::bq( $first_field )
			: implode( ', ', array_map( array( __CLASS__, 'bq' ), $primary_keys ) );

		$orderby = ' ORDER BY ' . $order;

		$limit = ' LIMIT ' . ( empty( $info['index'] ) ? '' : intval( $info['index'] ) . ', ' ) . self::ROWS_PER_QUERY;

		$sql = $select . $from . $orderby . $limit;

		$rows = $wpdb->get_results( $sql, ARRAY_A );

		if ( empty( $rows ) || ! is_array( $rows ) ) {
			$migration['info'][ $table ]['done'] = true;
			return '';
		}

		$dump   = '';
		$count  = count( $rows );
		$struct = $migration['struct'][ $table ];

		foreach ( $rows as $row ) {

			if ( empty( $dump ) ) {

				$dump = 'INSERT INTO ' . self::bq( $table ) .
					'( ' . implode( ', ', array_map( array( __CLASS__, 'bq' ), array_keys( $row ) ) ) .
					" ) VALUES\n";

			} else {

				$dump .= ",\n";
			}

			$values = array();

			foreach ( $row as $field => $value ) {

				if ( isset( $struct[ $field ] ) && $struct[ $field ]['int'] ) {

					$value = ( null === $value || '' === $value )
						? $struct[ $field ]['default']
						: $value;

					$values[] = ( '' === $value ) ? "''" : $value;
					continue;
				}

				if ( null === $value ) {
					$values[] = 'NULL';
					continue;
				}

				if ( isset( $struct[ $field ] ) && $struct[ $field ]['binary'] ) {

					$hex_field = strtolower( $field ) . '__hex';

					if ( isset( $row[ $hex_field ] ) ) {
						$values[] = "UNHEX('" . $row[ $hex_field ] . "')";
						continue;
					}
				}

				if ( isset( $struct[ $field ] ) && $struct[ $field ]['bit'] ) {

					$bit_field = strtolower( $field ) . '__bit';

					if ( isset( $row[ $bit_field ] ) ) {
						$values[] = "b'" . $row[ $bit_field ] . "'";
						continue;
					}
				}

				$search  = array( "\x00", "\x0a", "\x0d", "\x1a" );
				$replace = array( '\0', '\n', '\r', '\Z' );

				$value = str_replace( '\\', '\\\\', $value );
				$value = str_replace( "'", "\\'", $value );
				$value = str_replace( $search, $replace, $value );

				$values[] = "'" . $value . "'";
			}

			$dump .= '(' . implode( ', ', $values ) . ')';
		}

		$dump .= ";\n";

		$migration['info'][ $table ]['index'] += $count;
		$migration['total']['index']         += $count;

		if ( $migration['info'][ $table ]['index'] > $migration['info'][ $table ]['rows'] ) {

			$diff = $migration['info'][ $table ]['index'] - $migration['info'][ $table ]['rows'];

			$migration['info'][ $table ]['rows']  += $diff;
			$migration['total']['rows']           += $diff;
		}

		if ( $count < self::ROWS_PER_QUERY ) {

			$migration['info'][ $table ]['done'] = true;
			$dump .= "\n";
		}

		return $dump;
	}



	// Migration options
	// ---------------------------------------------------------------------------------------------------



	private static function add_migration( $key ) {

		$migrations = @json_decode( get_option( 'expdbs_migrations' ), true );

		if ( empty( $migrations ) || ! is_array( $migrations ) ) {
			$migrations = array();
		}

		$migrations[ $key ] = time();

		update_option( 'expdbs_migrations', wp_json_encode( $migrations ), false );
	}



	private static function touch_migration( $key ) {

		$migrations = @json_decode( get_option( 'expdbs_migrations' ), true );

		if ( empty( $migrations ) || ! is_array( $migrations ) || empty( $migrations[ $key ] ) ) {
			return false;
		}

		$migrations[ $key ] = time();

		update_option( 'expdbs_migrations', wp_json_encode( $migrations ), false );

		return true;
	}



	private static function get_migration( $key ) {

		$migration = @json_decode( get_option( 'expdbs_migrations_' . $key ), true );

		return ( empty( $migration ) || ! is_array( $migration ) ) ? false : $migration;
	}



	private static function set_migration( $key, $migration ) {

		update_option( 'expdbs_migrations_' . $key, wp_json_encode( $migration ), false );
	}



	private static function get_migration_path( $key ) {

		return self::get_migrations_folder() . '/' . $key;
	}



	public static function get_migrations_folder() {

		$upload_dir = wp_upload_dir();
		return rtrim( $upload_dir['basedir'], '/' ) . '/expdbs-migrations';
	}



	// Util
	// ---------------------------------------------------------------------------------------------------



	private static function bq( $value ) {

		if ( empty( $value ) || '*' === $value ) {
			return $value;
		}

		if ( ! is_array( $value ) ) {
			return '`' . $value . '`';
		}

		$values = array();

		foreach ( $value as $key => $val ) {
			$values[ $key ] = '`' . $val . '`';
		}

		return $values;
	}



	private static function no_time_limit() {

		if ( ! function_exists( 'ini_get' ) || ! @ini_get( 'safe_mode' ) ) {
			@set_time_limit( 0 );
		}
	}



}
