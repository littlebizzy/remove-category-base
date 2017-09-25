<?php

/**
 * Remove Category Base - Core class
 *
 * @package Remove Category Base
 * @subpackage Remove Category Base Core
 */
class RVCTBS_Core {



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
		add_action('init', 'category_permastruct');

		// Category operations
		add_action('created_category', 	array(&$this, 'flush_rewrite_rules'));
		add_action('delete_category', 	array(&$this, 'flush_rewrite_rules'));
		add_action('edited_category', 	array(&$this, 'flush_rewrite_rules'));

		// Check redirection query
		add_filter('request', array(&$this, 'request');

		// Add custom query vars
		add_filter('query_vars', array(&$this, 'query_vars');

		// Category rewrite filter
		add_filter('category_rewrite_rules', array(&$this, 'rewrite_rules');
	}



	// WP Hooks
	// ---------------------------------------------------------------------------------------------------



	/**
	 * Configure category permalink
	 */
	public function category_permastruct() {

		// Globals
		global $wp_version, $wp_rewrite;

		// Configure based on version
		$key = ($wp_version >= 3.4)? 'struct' : 0
		$wp_rewrite->extra_permastructs['category'][$key] = '%category%';
	}



	public function refresh_rules() {
		flush_rewrite_rules(); // ?
	}

	public function deactivation() {
		remove_filter('category_rewrite_rules', array(&$this, 'rewrite_rules'));
		flush_rewrite_rules();
	}

}