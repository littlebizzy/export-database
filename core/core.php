<?php

/**
 * Export Database - Core class
 *
 * @package Export Database
 * @subpackage Export Database Core
 */
class EXPDBS_Core {



	// Properties
	// ---------------------------------------------------------------------------------------------------



	/**
	 * Single class instance
	 */
	private static $instance;



	// Initialization
	// ---------------------------------------------------------------------------------------------------



	/**
	 * Create or retrieve instance
	 */
	public static function instance() {

		// Check instance
		if ( ! isset( self::$instance ) ) {
			self::$instance = new EXPDBS_Core;
		}

		// Done
		return self::$instance;
	}



	/**
	 * Constructor
	*/
	private function __construct() {

		// Check AJAX mode
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {

			require_once EXPDBS_PATH . '/core/ajax.php';
			EXPDBS_Core_AJAX::instance();

		// Just the admin
		} else {

			// Admin menu handler
			add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		}
	}



	// Admin area launcher
	// ---------------------------------------------------------------------------------------------------



	/**
	 * Set the admin menu
	 */
	public function admin_menu() {

		$hook_suffix = add_management_page(
			'Export Database',
			'Export Database',
			'manage_options',
			'export-database',
			array( $this, 'admin_page' )
		);

		add_action( 'load-' . $hook_suffix, array( $this, 'admin_assets' ) );
	}



	/**
	 * Load the admin page assets
	 */
	public function admin_assets() {

		require_once EXPDBS_PATH . '/admin/admin.php';
		EXPDBS_Admin::instance();
	}



	/**
	 * Show the admin page
	 */
	public function admin_page() {

		require_once EXPDBS_PATH . '/admin/admin.php';
		EXPDBS_Admin::instance()->view();
	}



	// safety helpers
	// ---------------------------------------------------------------------------------------------------



	/**
	 * ensure a folder exists and is readable/writable
	 */
	public static function ensure_folder( $folder ) {

		if ( is_dir( $folder ) ) {

			if ( ! is_readable( $folder ) || ! is_writable( $folder ) ) {
				wp_die( 'Export Database error: folder exists but is not fully readable/writable: ' . esc_html( $folder ) );
			}

			return true;
		}

		if ( ! wp_mkdir_p( $folder ) ) {
			wp_die( 'Export Database error: unable to create folder: ' . esc_html( $folder ) );
		}

		if ( ! is_readable( $folder ) || ! is_writable( $folder ) ) {
			wp_die( 'Export Database error: folder created but permissions are insufficient: ' . esc_html( $folder ) );
		}

		return true;
	}



	/**
	 * safe file delete
	 */
	public static function safe_unlink( $path ) {

		if ( empty( $path ) ) {
			return false;
		}

		if ( is_file( $path ) && is_writable( $path ) ) {
			return @unlink( $path );
		}

		return false;
	}



	/**
	 * safe file write
	 */
	public static function safe_write( $path, $content ) {

		$dir = dirname( $path );

		if ( ! is_writable( $dir ) ) {
			wp_die( 'Export Database error: cannot write file, directory is not writable: ' . esc_html( $path ) );
		}

		$result = @file_put_contents( $path, $content );

		if ( false === $result ) {
			wp_die( 'Export Database error: file_put_contents() failed writing: ' . esc_html( $path ) );
		}

		return true;
	}



	/**
	 * safe file read
	 */
	public static function safe_read( $path ) {

		if ( ! is_readable( $path ) ) {
			wp_die( 'Export Database error: file is not readable: ' . esc_html( $path ) );
		}

		$data = @file_get_contents( $path );

		if ( false === $data ) {
			wp_die( 'Export Database error: file_get_contents() failed: ' . esc_html( $path ) );
		}

		return $data;
	}



	/**
	 * sanitize timestamps
	 */
	public static function sanitize_timestamp( $value ) {

		$value = (int) $value;

		if ( $value <= 0 ) {
			return false;
		}

		return $value;
	}



}
