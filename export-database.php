<?php
/*
Plugin Name: Export Database
Plugin URI: https://www.littlebizzy.com/plugins/export-database
Description: Quickly and easily export your WordPress database with a single click for the purposes of migration, testing, or backup (in either SQL or ZIP format).
Version: 1.2.0
Author: LittleBizzy
Author URI: https://www.littlebizzy.com
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html
Prefix: EXPDBS
*/

// prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// plugin constants
define('EXPDBS_FILE', __FILE__);
define('EXPDBS_PATH', __DIR__);
define('EXPDBS_VERSION', '1.2.0');

// only admin area
if (!is_admin()) {
    return;
}

// run instance
require_once EXPDBS_PATH . '/core/core.php';
EXPDBS_Core::instance();

// Ref: ChatGPT
