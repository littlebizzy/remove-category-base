<?php

// Subpackage namespace
namespace LittleBizzy\RemoveCategoryBase;

/**
 * Admin Notices class
 *
 * @package Remove Category Base
 * @subpackage Remove Category Base Core
 */
final class RVCTBS_Core_Redirect {



	// Properties
	// ---------------------------------------------------------------------------------------------------



	/**
	 * Single class instance
	 */
	private static $instance;



	// Initialization
	// ---------------------------------------------------------------------------------------------------



	/**
	 * Create or retrieve instance
	 */
	public static function instance($slug = null) {

		// Check instance
		if (!isset(self::$instance))
			self::$instance = new self($slug);

		// Done
		return self::$instance;
	}



	/**
	 * Empty private constructor
	 */
	private function __construct($slug) {
		if (!empty($slug))
			$this->redirect($slug);
	}



	// Methods
	// ---------------------------------------------------------------------------------------------------



	/**
	 * Do the redirect in a header-clean way
	 */
	public function redirect($slug) {

		// Check term URL by slug
		if (false === ($url = $this->get_slug_link($slug)))
			return;

		// Remove existing headers
		$this->remove_headers();

		// Do the redirection
		wp_redirect($url, 301);

		// End
		die;
	}



	/**
	 * Remove any existing header
	 */
	private function remove_headers() {

		// Check headers list
		$headers = @headers_list();
		if (!empty($headers) && is_array($headers)) {

			// Check header_remove function (PHP 5 >= 5.3.0, PHP 7)
			$remove_function = function_exists('header_remove');

			// Enum and clean
			foreach ($headers as $header) {
				list($k, $v) = array_map('trim', explode(':', $header, 2));
				$remove_function? @header_remove($k) : @header($k.':');
			}
		}
	}



	// Internal
	// ---------------------------------------------------------------------------------------------------



	/**
	 * Retrieve slug link
	 */
	private function get_slug_link($slug) {

		// Extract last slug
		$slug = explode('/', trim($slug, '/'));
		$slug = $slug[count($slug) - 1];

		// Check result
		if (empty($slug))
			return false;

		// Check category URL
		$url = get_term_link($slug, 'category');
		return (empty($url) || is_wp_error($url))? false : $url;
	}



}
