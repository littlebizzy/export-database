<?php
/*
Plugin Name: Export Database
Plugin URI: https://www.littlebizzy.com/plugins/export-database
Description: Quickly and easily export your WordPress database with a single click for the purposes of migration, testing, or backup (in either SQL or ZIP format).
Version: 1.0.1
Author: LittleBizzy
Author URI: https://www.littlebizzy.com

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

Copyright 2017 by LittleBizzy

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