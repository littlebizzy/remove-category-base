<?php
/*
Plugin Name: Remove Category Base
Plugin URI: https://www.littlebizzy.com/plugins/remove-category-base
Description: Completely disables the category base from all URLs generated by WordPress so that there is no category slug displayed on archive permalinks, etc.
Version: 1.2.0
Author: LittleBizzy
Author URI: https://www.littlebizzy.com
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html
Prefix: RVCTBS
*/

// Plugin namespace
namespace LittleBizzy\RemoveCategoryBase;

// Plugin constants
define('RVCTBS_FILE', __FILE__);
define('RVCTBS_PATH', dirname(RVCTBS_FILE));
define('RVCTBS_VERSION', '1.1.0');
const FILE = __FILE__;
const PREFIX = 'rvctbs';
const VERSION = '1.1.0';

// Block direct calls
if (!function_exists('add_action'))
	die;

// Load main class
require_once dirname(FILE).'/core/core.php';
RVCTBS_Core::instance();

// Plugin hooks
register_activation_hook(__FILE__, array(RVCTBS_Core::instance(), 'activation'));
register_deactivation_hook(__FILE__, array(RVCTBS_Core::instance(), 'deactivation'));
