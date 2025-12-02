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

// download file
function expdbs_ajax_download() {
    expdbs_check_ajax();
    if ( empty( $_GET['file'] ) ) wp_die( 'Missing file' );
    $folder = expdbs_folder();
    $file   = basename( $_GET['file'] );
    $path   = $folder . '/' . $file;
    $data = expdbs_safe_read( $path );
    header( 'Content-Type: application/octet-stream' );
    header( 'Content-Disposition: attachment; filename="' . $file . '"' );
    header( 'Content-Length: ' . strlen( $data ) );
    echo $data;
    exit;
}
add_action( 'wp_ajax_expdbs_download', 'expdbs_ajax_download' );
