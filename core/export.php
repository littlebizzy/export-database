<?php

/**
 * Export Database - Export class
 *
 * @package Export Database
 * @subpackage Export Database Core
 */
class EXPDBS_Core_Export {



	// Constants
	// ---------------------------------------------------------------------------------------------------



	/**
	 * Single class instance
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
	public static function start($compress = false) {

		// Timeout
		self::no_time_limit();

		// Generate key
		$key = md5(EXPDBS_FILE.microtime().rand(0, 999999));

		// Save file
		$result = @file_put_contents(self::get_migration_path($key), self::get_head());
		if (false === $result)
			return false;

		// New migration
		self::add_migration($key);

		// Compression checks
		$zip  = $compress? (extension_loaded('zip') && class_exists('ZipArchive')) : false;
		$gzip = $compress? function_exists('gzopen') : false;

		// Migration object
		$migration = array(
			'key'	 => $key,
			'total'  => array('index' => 0, 'rows' => 0, 'done' => false),
			'info'   => array(),
			'struct' => array(),
			'zip'	 => $zip?  md5($key.EXPDBS_FILE.microtime().rand(0, 999999)) : false,
			'gzip' 	 => $gzip? md5($key.EXPDBS_FILE.microtime().rand(0, 999999)) : false,
		);

		// Retrieve tables
		$tables = self::get_tables();
		if (!empty($tables) && is_array($tables)) {

			// Enum tables
			foreach ($tables as $table) {

				// Table rows
				$rows_count = self::get_table_rows_count($table);
				$migration['total']['rows'] += $rows_count;

				// Initialize table info
				$migration['info'][$table] = array(
					'index'  => 0,
					'rows' 	 => $rows_count,
					'done' 	 => false,
				);

				// Prepare table struct
				$migration['struct'][$table] = self::get_table_struct($table);
			}
		}

		// Update data
		self::set_migration($key, $migration);

		// Done
		return $key;
	}



	/**
	 * Export process
	 */
	public static function export($key) {

		// Timeout
		self::no_time_limit();

		// Touch timer
		if (!self::touch_migration($key))
			return false;

		// Retrieve migration
		if (false === ($migration = self::get_migration($key)))
			return false;

		// Initialize
		$chunk = '';

		// Enum tables
		foreach ($migration['info'] as $table => $info) {

			// Check if finished
			if ($info['done'])
				continue;

			// Prepare end of data
			$data_end = "#\n";
			$data_end .= '# End of data rows of table '.self::bq($table)."\n";
			$data_end .= '# --------------------------------------------------------'."\n\n\n";

			// Check start
			if (empty($info['index'])) {

				// DROP table statement
				$chunk .= "\n";
				$chunk .= "#\n";
				$chunk .= '# Delete if exists '.self::bq($table)." table\n";
				$chunk .= "#\n\n";
				$chunk .= 'DROP TABLE IF EXISTS '.self::bq($table).";\n\n";

				// Create table
				if (false === ($create_table_query = self::create_table_query($table)))
					return false;

				// Add to dump
				$chunk .= "#\n";
				$chunk .= '# Table structure of '.self::bq($table)."\n";
				$chunk .= "#\n\n";
				$chunk .= $create_table_query."\n\n";

				// Start contents
				$chunk .= "#\n";
				$chunk .= '# Rows of table '.self::bq($table)."\n";
				$chunk .= "#\n\n";
			}

			// Check table rows
			if (empty($info['rows'])) {
				$chunk .= $data_end;
				$migration['info'][$table]['done'] = true;
				continue;
			}

			// Populate rows
			if (false === ($table_rows = self::get_table_rows($table, $migration)))
				return false;

			// Add to dump
			$chunk .= $table_rows;

			// Check finished table
			if ($migration['info'][$table]['done'])
				$chunk .= $data_end;

			// No more dump
			if ('' === $table_rows) {

				// Is done
				$migration['info'][$table]['done'] = true;

				// Check new line
				if (!empty($info['index']))
					$chunk .= "\n";

				// End of data
				$chunk .= $data_end;

				// Next
				continue;
			}

			// Save chunk
			$result = @file_put_contents(self::get_migration_path($key), $chunk, FILE_APPEND);
			if (false === $result)
				return false;

			// Done
			break;
		}

		// Check rows
		if (empty($migration['total']['rows']) || $migration['total']['index'] >= $migration['total']['rows'])
			$migration['total']['done'] = true;

		// Save migration data
		self::set_migration($key, $migration);

		// Done
		return $migration;
	}



	/**
	 * Compress generated file
	 */
	public static function compress($key) {

		// Touch timer
		if (!self::touch_migration($key))
			wp_die('Could not find the migration in the summary file by key: '.esc_html($key));

		// Retrieve migration
		if (false === ($migration = self::get_migration($key)))
			wp_die('Could not find the migration file by key: '.esc_html($key));

		// Initialize
		$result = true;

		// Original file
		$path_sql = self::get_migration_path($key);

		// ZIP compression
		if ($migration['zip']) {

			// Add key to the migration index
			self::add_migration($migration['zip']);

			// Create ZIP file
			$zip = new ZipArchive();
			if (true !== $zip->open(self::get_migration_path($migration['zip']), ZipArchive::CREATE)) {
				$result = false;
				$migration['zip'] = false;

			// Continue
			} else {

				// Add file
				if (!$zip->addFile($path_sql, sanitize_file_name(DB_NAME).'.sql')) {
					$result = false;
					$migration['zip'] = false;
				}

				// End
				if (!$zip->close()) {
					$result = false;
					$migration['zip'] = false;
				}
			}

			// Save if fails
			if (!$result)
				self::set_migration($key, $migration);
		}

		// gZIP compression
		if ($migration['gzip'] && !$migration['zip']) {

			// Init result
			$result = true;

			// Add key to the migration index
			self::add_migration($migration['gzip']);

			// Open target file
			if (false === ($fp_out = @gzopen(self::get_migration_path($migration['gzip']), 'wb9'))) {
				$result = false;
				$migration['gzip'] = false;

			// Open original file
			} elseif (false === ($fp_in = @fopen($path_sql, 'rb'))) {
				@gzclose($fp_out);
				$result = false;
				$migration['gzip'] = false;

			// Continue
			} else {

				// Add content
				while (!feof($fp_in))
					@gzwrite($fp_out, fread($fp_in, 1024 * 512));

				// Close handlers
				@fclose($fp_in);
				@gzclose($fp_out);
			}

			// Save if fails
			if (!$result)
				self::set_migration($key, $migration);
		}

		// Remove if success
		if ($result)
			@unlink(self::get_migration_path($key));

		// Done
		return $result;
	}



	/**
	 * Finally download file
	 */
	public static function download($key) {

		// Timeout
		self::no_time_limit();

		// Touch timer
		if (!self::touch_migration($key))
			wp_die('Could not find the migration in the summary file by key: '.esc_html($key));

		// Retrieve migration
		if (false === ($migration = self::get_migration($key)))
			wp_die('Could not find the migration file by key: '.esc_html($key));

		// Initialize
		$ext = '';

		// Check ZIP compression
		if ($migration['zip']) {
			$ext = '.zip';
			$key = $migration['zip'];

		// Check gZIP compression
		} elseif ($migration['gzip']) {
			$ext = '.gz';
			$key = $migration['gzip'];
		}

		// Check path
		$path = self::get_migration_path($key);
		if (!@file_exists($path))
			wp_die('Could not find the migration file:'.'<br />'.esc_html($path));

		// Force download
		@header('Content-Description: File Transfer');
		@header('Content-Type: application/octet-stream');
		@header('Content-Length: '.@filesize($path));
		@header('Content-Disposition: attachment; filename='.sanitize_file_name(DB_NAME).'.sql'.$ext);
		@readfile($path);

		// Remove file
		@unlink($path);

		// End
		die;
	}



	/**
	 * Atempts to create the migrations folder
	 */
	public static function check_migrations_folder(&$folder) {
		$folder = self::get_migrations_folder();
		return wp_mkdir_p($folder);
	}



	/**
	 * Do a cleanup of old migrations
	 */
	public static function cleanup() {

		// Retrieve migration
		$migrations = @json_decode(get_option('expdbs_migrations'), true);
		if (empty($migrations) || !is_array($migrations))
			return;

		// Initialize
		$remove = [];

		// Enum migrations
		foreach ($migrations as $key => $timestamp) {

			// Check length
			if (32 != strlen($key))
				continue;

			// Check timestamp
			$timestamp = (int) $timestamp;
			if (empty($timestamp) || time() - $timestamp > self::CLEANUP_MIGRATION_TIME)
				$remove[] = $key;
		}

		// Check items
		if (empty($remove))
			return;

		// Remove process
		$migrations_new = array();
		foreach ($migrations as $key => $timestamp) {

			// Check element to remove
			if (in_array($key, $remove)) {

				// Remove from database
				delete_option('expdbs_migrations_'.$key);

				// Remove target file
				$path = self::get_migration_path($key);
				if (@file_exists($path))
					@unlink($path);

				// Done
				continue;
			}

			// Keep element
			$migrations_new[$key] = $timestamp;
		}

		// Main migrations option
		update_option('expdbs_migrations', @json_encode($migrations_new), false);
	}



	// Internal methods
	// ---------------------------------------------------------------------------------------------------



	/**
	 * Retrieve head
	 */
	private static function get_head() {
		$head =  '# WordPress MySQL database export'."\n";
		$head .= '#'."\n";
		$head .= '# Generated: '.date('l j. F Y H:i T')."\n";
		$head .= '# Hostname: '.DB_HOST."\n";
		$head .= '# Database: '.self::bq(DB_NAME)."\n";
		$head .= '# --------------------------------------------------------'."\n\n";
		$head .= '/*!40101 SET NAMES '.(defined('DB_CHARSET')? DB_CHARSET : 'utf8').' */;'."\n\n";
		$head .= "SET sql_mode='NO_AUTO_VALUE_ON_ZERO';\n\n";
		return $head;
	}



	/**
	 * Retrieve WP database tables
	 */
	private static function get_tables() {

		// Globals
		global $wpdb;

		// Initialize
		$tables = array();

		// Check results
		$results = $wpdb->get_results('SHOW FULL TABLES', ARRAY_N);
		if (!empty($results) && is_array($results)) {

			// Enum results
			foreach ($results as $table) {

				// Avoid views and non WP tables
				if (0 !== strpos($table[0], $wpdb->base_prefix) || $table[1] == 'VIEW')
					continue;

				// Set table
				$tables[] = $table[0];
			}
		}

		// Done
		return $tables;
	}



	/**
	 * Number of rows of specific table
	 */
	private static function get_table_rows_count($table) {
		global $wpdb;
		return (int) $wpdb->get_var('SELECT COUNT(*) FROM '.$table);
	}



	/**
	 * Retrieve table structure
	 */
	private static function get_table_struct($table) {

		// Globals
		global $wpdb;

		// Describre table
		$description = $wpdb->get_results('DESCRIBE '.self::bq($table));
		if (empty($description) || !is_array($description))
			return false;

		// Initialize
		$int_types = array('tinyint', 'smallint', 'mediumint', 'int', 'bigint');

		// Enum struct data
		foreach ($description as $item) {

			// Default values
			$info = array(
				'field'   => $item->Field,
				'type' 	  => $item->Type,
				'int' 	  => false,
				'binary'  => false,
				'bit' 	  => false,
				'prikey'  => false,
				'default' => (null === $item->Default)? 'NULL' : $item->Default,
			);

			// Check int type
			$type = strtolower($item->Type);
			foreach ($int_types as $int_type) {
				if (0 === strpos($type, $int_type)) {
					$info['int'] = true;
					break;
				}
			}

			// Check binary
			if (!$info['int']) {

				// Check binary
				if (0 === strpos($type, 'binary')) {
					$info['binary'] = true;

				// Check bit
				} elseif (0 === strpos($type, 'bit')) {
					$info['bit'] = true;
				}
			}

			if ('PRI' === $item->Key)
				$info['prikey'] = true;

			// Add field
			$struct[$item->Field] = $info;
		}

		// Done
		return $struct;
	}



	/**
	 * Compose the create table
	 */
	private static function create_table_query($table) {

		// Globals
		global $wpdb;

		// Perform query
		$result = $wpdb->get_results('SHOW CREATE TABLE '.self::bq($table), ARRAY_N);
		if (empty($result) || !is_array($result) || 1 != count($result) || empty($result[0][1]))
			return false;

		// Prepare query
		$query = $result[0][1];
		$query = str_replace('TYPE=', 'ENGINE=', $query);

		// Done
		return $query.';';
	}



	/**
	 * Dump table values
	 */
	private static function get_table_rows($table, &$migration) {

		// Globals
		global $wpdb;

		// Extract primary keys
		$primary_keys = array();
		foreach ($migration['struct'][$table] as $field => $struct) {
			if (!isset($first_field))
				$first_field = $field;
			if ($struct['prikey'])
				$primary_keys[] = $field;
		}

		// No columns
		if (empty($first_field) && empty($primary_keys))
			return '';

		// Info array
		$info = $migration['info'][$table];

		// Prepare select
		$select = 'SELECT '.self::bq($table).'.*';

		// Prepare binary and hex fields
		$bins = $bits = '';
		foreach ($migration['struct'][$table] as $field => $struct) {

			// Binary fields
			if ($struct['binary']) {
				$bins .= ', HEX('.self::bq($field). ') as '.self::bq(strtolower($field).'__hex');

			// Bit fields
			} elseif ($struct['bit']) {
				$bits .= ', '.self::bq($field).'+0 as '.self::bq(strtolower($field).'__bit');
			}
		}

		// Add special fields
		$select .= $bins.$bits;

		// Prepare from
		$from = ' FROM '.self::bq($table);

		// Set order
		$orderby = ' ORDER BY '.(empty($primary_keys)? self::bq($first_field) : implode(', ', array_map(array(__CLASS__, 'bq'), $primary_keys)));

		// Set limit
		$limit = ' LIMIT '.(empty($info['index'])? '' : esc_sql($info['index']).', ').self::ROWS_PER_QUERY;

		// Compose query
		$sql = $select.$from.$orderby.$limit;

		// Retrieve rows
		$rows = $wpdb->get_results($sql, ARRAY_A);
		if (empty($rows) || !is_array($rows)) {
			$migration['info'][$table]['done'] = true;
			return '';
		}

		// Initialize
		$dump = '';
		$rows_count = count($rows);
		$struct = $migration['struct'][$table];

		// Enum rows
		foreach ($rows as $row) {

			// Insert into clause
			if (empty($dump)) {

				// Insert into section
				$dump = 'INSERT INTO '.self::bq($table).'( '.implode(', ', array_map(array(__CLASS__, 'bq'), array_keys($row))).' ) VALUES'."\n";

			// Next
			} else {

				// Prepare this line
				$dump .= ','."\n";
			}

			// Enum fields
			$values = array();
			foreach ($row as $field => $value) {

				// Integer types
				if (isset($struct[$field]) && $struct[$field]['int']) {
					$value = (null === $value || '' === $value) ? $struct[$field]['default'] : $value;
					$values[] = ('' === $value)? "''" : $value;
					continue;
				}

				// Direct NULL value
				if (null === $value) {
					$values[] = 'NULL';
					continue;
				}

				// Binary data replacement
				if (isset($struct[$field]) && $struct[$field]['binary']) {
					$hex_field = strtolower($field).'__hex';
					if (isset($row[$hex_field])) {
						$values[] = "UNHEX('".$row[$hex_field]."')";
						unset($row[$hex_field]);
						continue;
					}
				}

				// Bit data replacement
				if (isset($struct[$field]) && $struct[$field]['bit']) {
					$bit_field = strtolower($field).'__bit';
					if (isset($row[$bit_field])) {
						$values[] = "b'".$row->$bit_key."'";
						unset($row[$bit_field]);
						continue;
					}
				}

				// Add string value
				$multibyte_search  = array("\x00", "\x0a", "\x0d", "\x1a");
				$multibyte_replace = array('\0', '\n', '\r', '\Z');
				$value = str_replace('\'', '\\\'', str_replace( '\\', '\\\\', $value));
				$value = str_replace($multibyte_search, $multibyte_replace, $value);
				$values[] = "'".$value."'";
			}

			// New line
			$dump .= '('.implode(', ', $values).')';
		}

		// End of dump
		$dump .= ';'."\n";

		// Move the indexes
		$migration['info'][$table]['index'] += $rows_count;
		$migration['total']['index'] += $rows_count;

		// Fix extra rows
		if ($migration['info'][$table]['index'] > $migration['info'][$table]['rows']) {
			$diff = $migration['info'][$table]['index'] - $migration['info'][$table]['rows'];
			$migration['info'][$table]['rows'] += $diff;
			$migration['total']['rows'] += $diff;
		}

		// Check table done
		if ($rows_count < self::ROWS_PER_QUERY) {
			$migration['info'][$table]['done'] = true;
			$dump .= "\n";
		}

		// Done
		return $dump;
	}



	// Migration options
	// ---------------------------------------------------------------------------------------------------



	/**
	 * Add migration to the migrations index
	 */
	private static function add_migration($key) {

		// Retrieve migrations index
		$migrations = @json_decode(get_option('expdbs_migrations'), true);
		if (empty($migrations) || !is_array($migrations))
			$migrations = array();

		// Add timestamp
		$migrations[$key] = time();
		update_option('expdbs_migrations', @json_encode($migrations), false);
	}



	/**
	 * Touch migration timer
	 */
	private static function touch_migration($key) {

		// Retrieve migrations summary
		$migrations = @json_decode(get_option('expdbs_migrations'), true);
		if (empty($migrations) || !is_array($migrations) || empty($migrations[$key]))
			return false;

		// Touch the timer
		$migrations[$key] = time();
		update_option('expdbs_migrations', @json_encode($migrations), false);

		// Done
		return true;
	}



	/**
	 * Retrieve migration data
	 */
	private static function get_migration($key) {
		$migration = @json_decode(get_option('expdbs_migrations_'.$key), true);
		return (empty($migration) || !is_array($migration))? false : $migration;
	}



	/**
	 * Save existing migration
	 */
	private static function set_migration($key, $migration) {
		update_option('expdbs_migrations_'.$key, @json_encode($migration), false);
	}



	/**
	 * Retrieve migration path
	 */
	private static function get_migration_path($key) {
		return self::get_migrations_folder().'/'.$key;
	}



	/**
	 * Retrieve migrations folder
	 */
	private static function get_migrations_folder() {
		$upload_dir = wp_upload_dir();
		return rtrim($upload_dir['basedir'], '/').'/expdbs-migrations';
	}



	// Util
	// ---------------------------------------------------------------------------------------------------



	/**
	 * Add quotes to tables and fields
	 */
	private static function bq($value) {

		// Check value
		if (empty($value) || $value == '*')
			return $value;

		// No array
		if (!is_array($value))
			return '`'.$value.'`';

		// Initialize
		$values = array();

		// Populate
		reset($value);
		while (list($key, $val) = each($value))
			$values[$key] = '`'.$val.'`';

		// Done
		return $values;
	}



	/**
	 * Set no time limit
	 */
	private static function no_time_limit() {
		if (!function_exists('ini_get') || !@ini_get('safe_mode'))
			@set_time_limit(0);
	}



}