<?php
/*
Plugin Name: Export Database
Plugin URI: https://www.littlebizzy.com/plugins/export-database
Description: Backup your WordPress website
Version: 2.1.0
Author: LittleBizzy
Author URI: https://www.littlebizzy.com
Requires PHP: 7.0
Tested up to: 6.9
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html
Update URI: false
GitHub Plugin URI: littlebizzy/export-database
Primary Branch: master
Text Domain: export-database
*/

// prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// override wordpress.org with git updater
add_filter( 'gu_override_dot_org', function( $overrides ) {
    $overrides[] = 'export-database/export-database.php';
    return $overrides;
}, 999 );

// add settings link on plugins page
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'expdbs_settings_link' );
function expdbs_settings_link( $links ) {

    $settings_link = '<a href="' . admin_url( 'tools.php?page=export-database' ) . '">Settings</a>';

    array_unshift( $links, $settings_link );

    return $links;
}

// return plugin path
function expdbs_path() {
    return plugin_dir_path( __FILE__ );
}

// return plugin url
function expdbs_url() {
    return plugin_dir_url( __FILE__ );
}

// return migrations folder path
function expdbs_folder() {
    $upload = wp_upload_dir();
    return trailingslashit( $upload['basedir'] ) . 'expdbs-migrations';
}

// ensure migrations folder exists and is writable
function expdbs_ensure_folder() {
    $folder = expdbs_folder();

    if ( ! file_exists( $folder ) ) {
        wp_mkdir_p( $folder );
    }

    if ( ! is_dir( $folder ) || ! is_writable( $folder ) ) {
        wp_die( 'Export folder is not writable: ' . esc_html( $folder ) );
    }

    return $folder;
}

// safe file write
function expdbs_safe_write( $path, $data, $append = false ) {
    expdbs_ensure_folder();
    $mode = $append ? FILE_APPEND : 0;
    $result = @file_put_contents( $path, $data, $mode );
    if ( false === $result ) wp_die( 'Unable to write file: ' . esc_html( $path ) );
}

// safe file read
function expdbs_safe_read( $path ) {
    if ( ! file_exists( $path ) || ! is_readable( $path ) ) wp_die( 'File not readable: ' . esc_html( $path ) );
    $data = @file_get_contents( $path );
    if ( false === $data ) wp_die( 'Unable to read file: ' . esc_html( $path ) );
    return $data;
}

// safe delete
function expdbs_safe_unlink( $path ) {
    if ( file_exists( $path ) && is_writable( $path ) ) @unlink( $path );
}

// create short-lived download token so the filename never appears in the url
function expdbs_create_download_token( $file ) {

    $token = bin2hex( random_bytes( 16 ) );

    set_transient(
        'expdbs_dl_' . $token,
        array(
            'file' => (string) $file,
            'user' => (int) get_current_user_id(),
        ),
        10 * MINUTE_IN_SECONDS
    );

    return $token;
}

// include logic files
require_once expdbs_path() . 'inc/export.php';
require_once expdbs_path() . 'inc/ajax.php';

// enqueue admin scripts
function expdbs_admin_assets() {
    wp_enqueue_style( 'expdbs-admin', expdbs_url() . 'assets/admin.css' );
    wp_enqueue_script( 'expdbs-admin', expdbs_url() . 'assets/admin.js', array('jquery'), null, true );
    wp_localize_script( 'expdbs-admin', 'expdbs_ajax', array(
        'ajax_url' => admin_url( 'admin-ajax.php' ),
        'nonce'    => wp_create_nonce( 'expdbs_nonce' ),
    ) );
}
add_action( 'admin_enqueue_scripts', 'expdbs_admin_assets' );

// render admin page
function expdbs_admin_page() {
    expdbs_ensure_folder();
    $folder = expdbs_folder();
    $files = glob( $folder . '/*' );
    ?>

    <div class="wrap">
        <h1>Export Database</h1>

        <p>
            <label>
                <input id="expdbs-compress" type="checkbox" checked="checked" />
                Enable compression (uses ZIP when supported, otherwise GZIP if available)
            </label>
        </p>

        <p>
            <button id="expdbs-export" class="button button-primary button-large">
                Export Database
            </button>
        </p>

        <div id="expdbs-status"></div>

        <hr>

        <h2>Existing Exports</h2>

        <table class="widefat">
            <thead>
            <tr>
                <th>filename</th>
                <th>size</th>
                <th>date</th>
                <th>actions</th>
            </tr>
            </thead>
            <tbody>
            <?php if ( empty( $files ) ) : ?>
                <tr><td colspan="4">no export files found.</td></tr>
            <?php else: ?>
                <?php foreach ( $files as $file ):
                    $name  = basename( $file );
                    $token = expdbs_create_download_token( $name );
                    ?>
                    <tr>
                        <td><?php echo esc_html( $name ); ?></td>
                        <td><?php echo size_format( filesize( $file ) ); ?></td>
                        <td><?php echo date( 'Y-m-d H:i:s', filemtime( $file ) ); ?></td>
                        <td>
                            <a href="<?php echo esc_url(
                                admin_url(
                                    'admin-ajax.php?action=expdbs_download&token=' . urlencode( $token ) . '&nonce=' . wp_create_nonce( 'expdbs_nonce' )
                                )
                            ); ?>" class="button">download</a>

                            <button class="button expdbs-delete" data-file="<?php echo esc_attr( $name ); ?>">
                                delete
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php
}

// register admin menu
function expdbs_admin_menu() {
    add_management_page( 'Export Database', 'Export Database', 'manage_options', 'export-database', 'expdbs_admin_page' );
}
add_action( 'admin_menu', 'expdbs_admin_menu' );

// Ref: ChatGPT
