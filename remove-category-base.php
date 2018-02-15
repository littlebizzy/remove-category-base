<?php
/*
Plugin Name: Remove Category Base
Plugin URI: https://www.littlebizzy.com/plugins/remove-category-base
Description: Completely disables the category base from all URLs generated by WordPress so that there is no category slug displayed on archive permalinks, etc.
Version: 1.0.4
Author: LittleBizzy
Author URI: https://www.littlebizzy.com
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html
Prefix: RVCTBS
*/

// Admin Notices module
require_once dirname(__FILE__).'/admin-notices.php';
RVCTBS_Admin_Notices::instance(__FILE__);

/**
 * Admin Notices Multisite check
 * Uncomment //return to disable this plugin on Multisite installs
 */
require_once dirname(__FILE__).'/admin-notices-ms.php';
if (false !== \LittleBizzy\RemoveCategoryBase\Admin_Notices_MS::instance(__FILE__)) {
	//return;
}

// Block direct calls
if (!function_exists('add_action'))
	die;

// This plugin constants
define('RVCTBS_FILE', __FILE__);
define('RVCTBS_PATH', dirname(RVCTBS_FILE));
define('RVCTBS_VERSION', '1.0.4');

// Load main class
require_once RVCTBS_PATH.'/core/core.php';
RVCTBS_Core::instance();

// Plugin hooks
register_activation_hook(__FILE__, array(RVCTBS_Core::instance(), 'activation'));
register_deactivation_hook(__FILE__, array(RVCTBS_Core::instance(), 'deactivation'));
