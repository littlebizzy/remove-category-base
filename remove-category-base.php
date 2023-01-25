<?php
/*
Plugin Name: Remove Category Base
Plugin URI: https://www.littlebizzy.com/plugins/remove-category-base
Description: Removes and 301s category base
Version: 1.3.0
Author: LittleBizzy
Author URI: https://www.littlebizzy.com
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html
GitHub Plugin URI: littlebizzy/remove-category-base
Primary Branch: master
Prefix: RVCTBS
*/

// Plugin namespace
namespace LittleBizzy\RemoveCategoryBase;

// disable wordpress.org updates
add_filter(
	'gu_override_dot_org',
	function ( $overrides ) {
		return array_merge(
			$overrides,
			array( 'remove-category-base/remove-category-base.php' )
		);
	}
);

// Plugin constants
define('RVCTBS_FILE', __FILE__);
define('RVCTBS_PATH', dirname(RVCTBS_FILE));
define('RVCTBS_VERSION', '1.3.0');
const FILE = __FILE__;
const PREFIX = 'rvctbs';
const VERSION = '1.3.0';

// Block direct calls
if (!function_exists('add_action'))
	die;

// Load main class
require_once dirname(FILE).'/core/core.php';
RVCTBS_Core::instance();

// Plugin hooks
register_activation_hook(__FILE__, array(RVCTBS_Core::instance(), 'activation'));
register_deactivation_hook(__FILE__, array(RVCTBS_Core::instance(), 'deactivation'));
