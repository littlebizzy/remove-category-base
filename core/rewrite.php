<?php

// Subpackage namespace
namespace LittleBizzy\RemoveCategoryBase;

/**
 * Admin Notices class
 *
 * @package Remove Category Base
 * @subpackage Remove Category Base Core
 */
final class RVCTBS_Core_Rewrite {



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
	 * Empty private constructor
	 */
	private function __construct() {}



	// Methods
	// ---------------------------------------------------------------------------------------------------



	/**
	 * Compose new category rules
	 */
	public function get_rules() {

		// Globals
		global $wp_rewrite;

		// Initialize
		$custom = array();

		// Enum categories
		$categories = $this->get_categories();
		foreach ($categories as $category) {

			// Prepare slug
			$slug = (!empty($category->parent) && $category->parent != $category->cat_ID)? get_category_parents($category->parent, false, '/', true).$category->slug : $category->slug;

			// Compose new rules
			$custom['('.$slug.')/(?:feed/)?(feed|rdf|rss|rss2|atom)/?$'] = 'index.php?category_name=$matches[1]&feed=$matches[2]';
			$custom["({$slug})/{$wp_rewrite->pagination_base}/?([0-9]{1,})/?$"] = 'index.php?category_name=$matches[1]&paged=$matches[2]';
			$custom['('.$slug.')/?$'] = 'index.php?category_name=$matches[1]';
		}

		// Add old rule redirects
		$custom[$this->get_old_base_rule().'/(.*)$'] = 'index.php?rvctbs_category_redirect=$matches[1]';

		// Done
		return $custom;
	}



	// Internal
	// ---------------------------------------------------------------------------------------------------



	/**
	 * Retrieve current categories
	 */
	private function get_categories() {

		// Special case for Sitepress
		if ($this->is_sitepress_active()) {

			// Globals
			global $sitepress;

			// Remove/add terms_clauses filter
			remove_filter('terms_clauses', array($sitepress, 'terms_clauses'));
			$categories = get_categories(array('hide_empty' => false));
			add_filter('terms_clauses', array($sitepress, 'terms_clauses'), 10, 4);

		// Normal
		} else {

			// All categories
			$categories = get_categories(array('hide_empty' => false));
		}

		// Last check
		if (empty($categories) || !is_array($categories))
			$categories = array();

		// Done
		return $categories;
	}



	/**
	 * Detect whether the plugin Sitepress is active
	 */
	private function is_sitepress_active() {

		// Check class
		if (!class_exists('SitePress'))
			return false;

		// Globals
		global $sitepress;

		// Check object
		return (!empty($sitepress) && is_object($sitepress) && is_a($sitepress, 'SitePress'));
	}



	/**
	 * Retrieve old base category rule
	 */
	private function get_old_base_rule() {
		$old_base = trim(''.get_option('category_base'), '/');
		return ('' === $old_base)? 'category' : $old_base;
	}



}
