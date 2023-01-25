<?php

// Subpackage namespace
namespace LittleBizzy\RemoveCategoryBase;

/**
 * Admin Notices class
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



	/**
	 * Original category permastruct
	 */
	private $old_category_permastruct;



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
		add_action('init', array(&$this, 'new_category_permastruct'));

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
	public function new_category_permastruct() {

		// Globals
		global $wp_rewrite;

		// Valid permastruct key
		$key = $this->get_permastruct_key();

		// Save old version
		$this->old_category_permastruct = $wp_rewrite->extra_permastructs['category'][$key];

		// Set the new permastruct
		$wp_rewrite->extra_permastructs['category'][$key] = '%category%';
	}



	/**
	 * Category rewrites
	 */
	public function rewrite_rules($rules) {
		require_once dirname(FILE).'/core/rewrite.php';
		return RVCTBS_Core_Rewrite::instance()->get_rules();
	}



	/**
	 * Check current request
	 */
	public function request($query_vars) {

		// Check redirection query
		if (!empty($query_vars['rvctbs_category_redirect'])) {
			require_once dirname(FILE).'/core/redirect.php';
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
	 * Plugin activation hook
	 */
	public function activation() {
		flush_rewrite_rules();
	}



	/**
	 * Plugin deactivation hook
	 */
	public function deactivation() {

		// Remove current filter
		remove_filter('category_rewrite_rules', array(&$this, 'rewrite_rules'));

		// Restart the permastruct value
		$this->restore_category_permastruct();

		// Done
		flush_rewrite_rules();
	}



	// Internal
	// ---------------------------------------------------------------------------------------------------



	/**
	 * Replaces the category permastruct by the original value
	 */
	private function restore_category_permastruct() {

		// Globals
		global $wp_rewrite;

		// Get the proper key
		$key = $this->get_permastruct_key();

		// Replaces current permastruct
		$wp_rewrite->extra_permastructs['category'][$key] = $this->old_category_permastruct;
	}



	/**
	 * Permastruct key based on version
	 */
	private function get_permastruct_key() {
		global $wp_version;
		return ($wp_version >= 3.4)? 'struct' : 0;
	}



}
