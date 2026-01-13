<?php

if ( ! defined( 'ABSPATH' ) ) exit;

// verify ajax request
function expdbs_check_ajax() {
    if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Not allowed' );
    if ( empty( $_REQUEST['nonce'] ) || ! wp_verify_nonce( $_REQUEST['nonce'], 'expdbs_nonce' ) ) wp_die( 'Security check failed' );
}

// handle export ajax
function expdbs_ajax_export() {
    expdbs_check_ajax();
    $compress = ! empty( $_POST['compress'] );
    $file = expdbs_do_export( $compress );
    wp_send_json_success( array( 'file' => $file ) );
}
add_action( 'wp_ajax_expdbs_export', 'expdbs_ajax_export' );

// delete file
function expdbs_ajax_delete() {
    expdbs_check_ajax();
    if ( empty( $_POST['file'] ) ) wp_send_json_error( 'Missing file' );
    $folder = expdbs_folder();
    $file   = basename( $_POST['file'] );
    $path   = $folder . '/' . $file;
    expdbs_safe_unlink( $path );
    wp_send_json_success();
}
add_action( 'wp_ajax_expdbs_delete', 'expdbs_ajax_delete' );

// download file via token
function expdbs_ajax_download() {

    expdbs_check_ajax();

    if ( empty( $_GET['token'] ) ) wp_die( 'Missing token' );

    $token = preg_replace( '/[^a-f0-9]/', '', strtolower( (string) $_GET['token'] ) );

    if ( empty( $token ) ) wp_die( 'Invalid token' );

    $payload = get_transient( 'expdbs_dl_' . $token );

    if ( empty( $payload ) || empty( $payload['file'] ) || empty( $payload['user'] ) ) {
        wp_die( 'Download expired' );
    }

    if ( (int) $payload['user'] !== (int) get_current_user_id() ) {
        wp_die( 'Not allowed' );
    }

    $folder = expdbs_folder();
    $file   = basename( (string) $payload['file'] );
    $path   = $folder . '/' . $file;

    if ( ! file_exists( $path ) || ! is_readable( $path ) ) {
        delete_transient( 'expdbs_dl_' . $token );
        wp_die( 'File not readable' );
    }

    delete_transient( 'expdbs_dl_' . $token );

    if ( function_exists( 'nocache_headers' ) ) {
        nocache_headers();
    }

    header( 'Content-Type: application/octet-stream' );
    header( 'Content-Disposition: attachment; filename="' . $file . '"' );
    header( 'Content-Length: ' . filesize( $path ) );

    @readfile( $path );
    exit;
}
add_action( 'wp_ajax_expdbs_download', 'expdbs_ajax_download' );
