<?php
/*
Plugin Name: Remove Category Base
Plugin URI: https://www.littlebizzy.com/plugins/remove-category-base
Description: Removes and 301s category base
Version: 2.0.0
Author: LittleBizzy
Author URI: https://www.littlebizzy.com
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html
GitHub Plugin URI: littlebizzy/remove-category-base
Primary Branch: master
*/

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Flush rewrite rules when the plugin is activated or deactivated.
 */
function remove_category_base_flush_rules() {
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'remove_category_base_flush_rules');
register_deactivation_hook(__FILE__, 'remove_category_base_flush_rules');

/**
 * Flush rewrite rules when categories are created, edited, or deleted.
 */
function remove_category_base_refresh_rules() {
    remove_category_base_flush_rules();
}
add_action('created_category', 'remove_category_base_refresh_rules');
add_action('delete_category', 'remove_category_base_refresh_rules');
add_action('edited_category', 'remove_category_base_refresh_rules');

/**
 * Remove the category base from permalinks.
 */
add_action('init', 'remove_category_base');
function remove_category_base() {
    global $wp_rewrite;
    $wp_rewrite->extra_permastructs['category']['struct'] = '%category%';
}

/**
 * Update category rewrite rules to handle parent categories, pagination, and feed URLs.
 */
add_filter('category_rewrite_rules', 'update_category_rewrite_rules');
function update_category_rewrite_rules($rules) {
    $new_rules = array();
    $categories = get_categories(array('hide_empty' => false));

    foreach ($categories as $category) {
        $category_nicename = $category->slug;

        // Handle parent categories
        if ($category->parent != 0) {
            $category_nicename = get_category_parents($category->parent, false, '/', true) . $category_nicename;
        }

        // Add rewrite rules for pagination and feeds
        $new_rules["({$category_nicename})/{$wp_rewrite->pagination_base}/?([0-9]{1,})/?$"] = 'index.php?category_name=$matches[1]&paged=$matches[2]';
        $new_rules["({$category_nicename})/(feed|rdf|rss|rss2|atom)/?$"] = 'index.php?category_name=$matches[1]&feed=$matches[2]';
        $new_rules["({$category_nicename})/?$"] = 'index.php?category_name=$matches[1]';
    }

    // Redirect support from old category base
    $old_category_base = get_option('category_base') ? get_option('category_base') : 'category';
    $old_category_base = trim($old_category_base, '/');
    $new_rules[$old_category_base . '/(.*)$'] = 'index.php?category_redirect=$matches[1]';

    return $new_rules + $rules;
}

/**
 * Add 'category_redirect' to query variables to handle redirection of old URLs.
 */
add_filter('query_vars', 'add_category_redirect_query_var');
function add_category_redirect_query_var($query_vars) {
    $query_vars[] = 'category_redirect';
    return $query_vars;
}

/**
 * Handle the redirection of old category base URLs (301 redirects).
 */
add_filter('request', 'redirect_old_category_base');
function redirect_old_category_base($query_vars) {
    if (isset($query_vars['category_redirect'])) {
        $catlink = trailingslashit(home_url()) . user_trailingslashit($query_vars['category_redirect'], 'category');
        wp_redirect(esc_url_raw($catlink), 301);
        exit();
    }
    return $query_vars;
}

// Ref: ChatGPT
// Ref: https://wordpress.org/plugins/no-category-base-wpml/
