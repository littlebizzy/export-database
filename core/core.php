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
		if (!isset(self::$instance))
			self::$instance = new EXPDBS_Core;

		// Done
		return self::$instance;
	}



	/**
	 * Constructor
	*/
	private function __construct() {

		// Check AJAX mode
		if (defined('DOING_AJAX') && DOING_AJAX) {

			// Check the AJAX action
			if (!empty($_POST['action']) && 0 === strpos($_POST['action'], 'expdbs_')) {
				require_once(EXPDBS_PATH.'/core/ajax.php');
				EXPDBS_Core_AJAX::instance();
			}

		// Just the admin
		} else {

			// Admin menu handler
			add_action('admin_menu', array(&$this, 'admin_menu'));
		}
	}



	// Admin area launcher
	// ---------------------------------------------------------------------------------------------------



	/**
	 * Set the admin menu
	 */
	public function admin_menu() {
		$hook_suffix = add_management_page('Export Database', 'Export Database', 'export', 'export-database', array(&$this, 'admin_page'));
		add_action('load-'. $hook_suffix, array( $this, 'admin_assets'));
	}



	/**
	 * Load the admin page assets
	 */
	public function admin_assets() {
		require_once(EXPDBS_PATH.'/admin/admin.php');
		EXPDBS_Admin::instance();
	}



	/**
	 * Show the admin page
	 */
	public function admin_page() {
		require_once(EXPDBS_PATH.'/admin/admin.php');
		EXPDBS_Admin::instance()->view();
	}



}
