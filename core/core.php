<?php

/**
 * Remove Category Base - Core class
 *
 * @package Remove Category Base
 * @subpackage Remove Category Base Core
 */
final class RVCTBS_Core {



	// Properties
	// ---------------------------------------------------------------------------------------------------



	/**
	 * Single class instance
	 */
	private static $instance;



	// Initialization
	// ---------------------------------------------------------------------------------------------------



	/**
	 * Retrieve previous instance or create new one
	 */
	public static function instance() {

		// Check instance
		if (!isset(self::$instance))
			self::$instance = new self;

		// Done
		return self::$instance;
	}



	/**
	 * Constructor
	 */
	private function __construct() {

		// Initialization
		add_action('init', array(&$this, 'category_permastruct'));

		// Category operations
		add_action('created_category', 	'flush_rewrite_rules');
		add_action('delete_category', 	'flush_rewrite_rules');
		add_action('edited_category', 	'flush_rewrite_rules');

		// Check redirection query
		add_filter('request', array(&$this, 'request'));

		// Add custom query vars
		add_filter('query_vars', array(&$this, 'query_vars'));

		// Category rewrite filter
		add_filter('category_rewrite_rules', array(&$this, 'rewrite_rules'));
	}



	// WP Hooks
	// ---------------------------------------------------------------------------------------------------



	/**
	 * Configure category permalink
	 */
	public function category_permastruct() {

		// Globals
		global $wp_version, $wp_rewrite;

		// Configuring based on WP version
		$key = ($wp_version >= 3.4)? 'struct' : 0;
		$wp_rewrite->extra_permastructs['category'][$key] = '%category%';
	}



	/**
	 * Category rewrites
	 */
	public function rewrite_rules($rules) {
		require_once RVCTBS_PATH.'/core/rewrite.php';
		return RVCTBS_Core_Rewrite::instance()->get_rules();
	}



	/**
	 * Check current request
	 */
	public function request($query_vars) {

		// Check redirection query
		if (!empty($query_vars['rvctbs_category_redirect'])) {
			require_once RVCTBS_PATH.'/core/redirect.php';
			RVCTBS_Core_Redirect::instance($query_vars['rvctbs_category_redirect']);
		}

		// Default
		return $query_vars;
	}



	/**
	 * Add valid custom query var
	 */
	public function query_vars($public_query_vars) {
		$public_query_vars[] = 'rvctbs_category_redirect';
		return $public_query_vars;
	}



	// Methods
	// ---------------------------------------------------------------------------------------------------



	/**
	 * Plugin deactivation hook
	 */
	public function deactivation() {
		remove_filter('category_rewrite_rules', array(&$this, 'rewrite_rules'));
		flush_rewrite_rules();
	}



}