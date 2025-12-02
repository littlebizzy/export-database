<?php

/**
 * Export Database - Core AJAX class
 *
 * @package Export Database
 * @subpackage Export Database Core
 */
class EXPDBS_Core_AJAX {



	// Properties
	// ---------------------------------------------------------------------------------------------------



	/**
	 * Single class instance
	 */
	private static $instance;



	/**
	 * AJAX response
	 */
	private $response;



	// Initialization
	// ---------------------------------------------------------------------------------------------------



	/**
	 * Create or retrieve instance
	 */
	public static function instance() {

		// Check instance
		if ( ! isset( self::$instance ) ) {
			self::$instance = new EXPDBS_Core_AJAX;
		}

		// Done
		return self::$instance;
	}



	/**
	 * Constructor
	*/
	private function __construct() {

		// Run requested method
		$method = substr( $_POST['action'], 7 );

		if ( method_exists( $this, $method ) ) {
			add_action( 'wp_ajax_' . $_POST['action'], array( $this, $method ) );
		}
	}



	// Migration methods
	// ---------------------------------------------------------------------------------------------------



	/**
	 * Start the migration
	 */
	public function start() {

		// Load export library
		$this->load_export();

		// Check folder
		if ( ! EXPDBS_Core_Export::check_migrations_folder( $folder ) ) {
			$this->error_ajax_response( 'Cannot create the folder: ' . $folder );
		}

		// Compression
		$compress = ! empty( $_POST['compress'] );

		// Start the migration
		$key = EXPDBS_Core_Export::start( $compress );
		if ( false === $key ) {
			$this->error_ajax_response( 'Cannot write the file.' );
		}

		// Set key and show response
		$this->response['data']['is_done'] = false;
		$this->response['data']['key']     = $key;

		$this->output_ajax_response();
	}



	/**
	 * Process table
	 */
	public function export() {

		// Load export library
		$this->load_export();

		// Check key argument
		if ( empty( $_POST['key'] ) || 32 != strlen( $_POST['key'] ) ) {
			$this->error_ajax_response( 'Error in key argument.' );
		}

		// Check export process
		$migration = EXPDBS_Core_Export::export( $_POST['key'] );
		if ( false === $migration ) {
			$this->error_ajax_response( 'Error when exporting data.' );
		}

		// Default response
		$this->response['data']['is_done'] = false;

		// Check generation end
		if ( $migration['total']['done'] ) {

			if ( $migration['zip'] || $migration['gzip'] ) {
				$this->response['data']['compressing'] = true;

			} else {
				// File generated
				$this->response['data']['is_done'] = true;
			}

		} else {

			// Progress
			$this->response['data']['percent'] = empty( $migration['total']['rows'] )
				? 0
				: round( ( $migration['total']['index'] / $migration['total']['rows'] ) * 100 );
		}

		// Output
		$this->output_ajax_response();
	}



	/**
	 * Compress data
	 */
	public function compress() {

		// Load export library
		$this->load_export();

		// Check key argument
		if ( empty( $_POST['key'] ) || 32 != strlen( $_POST['key'] ) ) {
			$this->error_ajax_response( 'Error in key argument.' );
		}

		// Check compression process
		$migration = EXPDBS_Core_Export::compress( $_POST['key'] );
		if ( false === $migration ) {
			$this->error_ajax_response( 'Error when compressing data.' );
		}

		$this->response['data']['is_done'] = true;

		$this->output_ajax_response();
	}



	/**
	 * Download file
	 */
	public function download() {

		// Load export library
		$this->load_export();

		// Check key value
		if ( empty( $_POST['key'] ) || 32 != strlen( $_POST['key'] ) ) {
			wp_die( 'Not valid key' );
		}

		// Download file
		EXPDBS_Core_Export::download( $_POST['key'] );
	}



	// AJAX abstract procedures
	// ---------------------------------------------------------------------------------------------------



	/**
	 * Load export library
	 */
	private function load_export() {

		// Check submit data
		$this->check_ajax_submit();

		// Load export class
		require_once EXPDBS_PATH . '/core/export.php';
	}



	/**
	 * Check and initialize ajax respose
	 */
	private function check_ajax_submit() {

		// Default response
		$this->response = $this->default_ajax_response();

		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			$this->response['status'] = 'error';
			$this->response['reason'] = 'Operation not allowed for the current user.';
			$this->output_ajax_response();
		}

		// Nonce check
		if (
			! isset( $_POST['nonce'] ) ||
			! wp_verify_nonce( $_POST['nonce'], EXPDBS_FILE )
		) {
			$this->response['status'] = 'error';
			$this->response['reason'] = 'Security verification error: please reload this page and try again.';
			$this->output_ajax_response();
		}
	}



	/**
	 * Return array of ajax response
	 */
	private function default_ajax_response( $nonce_seed = null ) {

		return array(
			'status' => 'ok',
			'reason' => '',
			'data'   => array(),
		);
	}



	/**
	 * Custom error ajax response
	 */
	private function error_ajax_response( $reason ) {

		$this->response            = $this->default_ajax_response();
		$this->response['status']  = 'error';
		$this->response['reason']  = $reason;

		$this->output_ajax_response();
	}



	/**
	 * Output AJAX in JSON format and exit
	 */
	private function output_ajax_response() {

		@header( 'Content-Type: application/json' );
		die( wp_json_encode( $this->response ) );
	}



}
