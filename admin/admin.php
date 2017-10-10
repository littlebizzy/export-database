<?php

/**
 * Export Database - Admin class
 *
 * @package Export Database
 * @subpackage Export Database Admin
 */
class EXPDBS_Admin {



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
			self::$instance = new EXPDBS_Admin;

		// Done
		return self::$instance;
	}



	/**
	 * Constructor
	*/
	private function __construct() {

		// Add styles and scripts
		$this->enqueue_scripts();

		// Cleanup function at the end
		add_action('shutdown', array(&$this, 'cleanup'));
	}



	/**
	 * Add styles and scripts
	 */
	private function enqueue_scripts() {

		// Common styles
		wp_enqueue_style('expdbs-admin-css', plugins_url('assets/admin.css', EXPDBS_FILE), array(), EXPDBS_VERSION);

		// Admin script
		wp_enqueue_script('expdbs-admin-script', plugins_url('assets/admin.js', EXPDBS_FILE), array('jquery'), EXPDBS_VERSION, true);
	}



	/**
	 * Admin page
	 */
	public function view() { ?>

		<h1>Export DB</h1>

		<p class="expdbs-view"><input id="expdbs-compress" type="checkbox" checked value="on" /><label for="expdbs-compress">&nbsp;Enable file compression if available in this server.</label></p>

		<p class="expdbs-view"><input id="expdbs-export" type="button" value="Export and download database" class="button button-primary button-large" data-nonce="<?php echo esc_attr(wp_create_nonce(EXPDBS_FILE)); ?>" /></p>

		<p id="expdbs-init" class="expdbs-hide">Initializing...</p>

		<p id="expdbs-gen" class="expdbs-hide">Generating SQL file: <span id="expdbs-gen-percent"></span> %</p>

		<p id="expdbs-comp" class="expdbs-hide">Compressing SQL file...</p>

		<p id="expdbs-done" class="expdbs-hide">Database exported succesfully!</p>

	<?php }



	/**
	 * Perform an automatic cleanup just in this page
	 */
	public function cleanup() {
		require_once(EXPDBS_PATH.'/core/export.php');
		EXPDBS_Core_Export::cleanup();
	}



}
