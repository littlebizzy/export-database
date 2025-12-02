<?php

if ( ! defined( 'ABSPATH' ) ) exit;

// build filename using dbname + timestamp + random
function expdbs_make_filename() {
    $dbname = sanitize_file_name( DB_NAME );
    $stamp  = date( 'Ymd_His' );
    $rand   = wp_generate_password( 8, false );
    return "{$dbname}_{$stamp}_{$rand}.sql";
}

// build sql header
function expdbs_sql_header() {
    $charset = defined( 'DB_CHARSET' ) ? DB_CHARSET : 'utf8';
    return "# database export\n"
         . "# generated: " . date( 'Y-m-d H:i:s T' ) . "\n"
         . "# host: " . DB_HOST . "\n"
         . "# db: " . DB_NAME . "\n"
         . "SET sql_mode='NO_AUTO_VALUE_ON_ZERO';\n"
         . "/*!40101 SET NAMES {$charset} */;\n\n";
}

// perform export action
function expdbs_do_export( $compress = false ) {
    global $wpdb;

    expdbs_ensure_folder();
    $folder = expdbs_folder();
    $filename = expdbs_make_filename();
    $path     = $folder . '/' . $filename;

    // write header
    expdbs_safe_write( $path, expdbs_sql_header() );

    // get tables
    $tables = $wpdb->get_col( "SHOW TABLES LIKE '{$wpdb->base_prefix}%'" );

    foreach ( $tables as $table ) {

        // create statement
        $create = $wpdb->get_row( "SHOW CREATE TABLE `$table`", ARRAY_N );
        if ( empty( $create[1] ) ) continue;

        expdbs_safe_write( $path,
            "\nDROP TABLE IF EXISTS `$table`;\n" .
            $create[1] . ";\n\n",
            true
        );

        // fetch table rows
        $rows = $wpdb->get_results( "SELECT * FROM `$table`", ARRAY_A );
        if ( empty( $rows ) ) continue;

        expdbs_safe_write( $path, "INSERT INTO `$table` VALUES\n", true );

        $first = true;

        foreach ( $rows as $row ) {
            $vals = array();

            foreach ( $row as $v ) {
                if ( is_null( $v ) ) {
                    $vals[] = "NULL";
                } else {
                    $vals[] = "'" . esc_sql( $v ) . "'";
                }
            }

            expdbs_safe_write(
                $path,
                ( $first ? '' : ",\n" ) . "(" . implode( ',', $vals ) . ")",
                true
            );

            $first = false;
        }

        expdbs_safe_write( $path, ";\n\n", true );
    }

    // zip compression
    if ( $compress && class_exists( 'ZipArchive' ) ) {
        $zipfile = $path . '.zip';
        $zip = new ZipArchive();
        if ( $zip->open( $zipfile, ZipArchive::CREATE ) === true ) {
            $zip->addFile( $path, $filename );
            $zip->close();
            expdbs_safe_unlink( $path );
            return basename( $zipfile );
        }
    }

    // gzip fallback
    if ( $compress && function_exists( 'gzopen' ) ) {
        $gzfile = $path . '.gz';
        $out = gzopen( $gzfile, 'wb9' );
        $in  = fopen( $path, 'rb' );

        while ( ! feof( $in ) ) {
            gzwrite( $out, fread( $in, 1024 * 512 ) );
        }

        fclose( $in );
        gzclose( $out );

        expdbs_safe_unlink( $path );
        return basename( $gzfile );
    }

    return $filename;
}
