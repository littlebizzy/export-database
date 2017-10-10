<?php
/*
Plugin Name: Export Database
Plugin URI: https://www.littlebizzy.com/plugins/export-database
Description: Quickly and easily export your WordPress database with a single click for the purposes of migration, testing, or backup (in either SQL or ZIP format).
Version: 1.0.2
Author: LittleBizzy
Author URI: https://www.littlebizzy.com
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

/* Initialization */

// Avoid script calls via plugin URL
if (!function_exists('add_action'))
	die;

// This plugin constants
define('EXPDBS_FILE', __FILE__);
define('EXPDBS_PATH', dirname(EXPDBS_FILE));
define('EXPDBS_VERSION', '1.0.5');

// Only admin area
if (!is_admin())
	return;

// Run instance
require_once(EXPDBS_PATH.'/core/core.php');
EXPDBS_Core::instance();