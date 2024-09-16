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

// Disable WordPress.org updates for this plugin
add_filter('gu_override_dot_org', function ($overrides) {
    $overrides[] = 'remove-category-base/remove-category-base.php';
    return $overrides;
});

// Flush rewrite rules on plugin activation and deactivation
register_activation_hook(__FILE__, 'remove_category_base_flush_rewrite');
register_deactivation_hook(__FILE__, 'remove_category_base_flush_rewrite');

// Flush rewrite rules function
function remove_category_base_flush_rewrite() {
    flush_rewrite_rules();
}

// Remove category base from permalinks
add_action('init', 'remove_category_base');
function remove_category_base() {
    global $wp_rewrite;
    $wp_rewrite->extra_permastructs['category']['struct'] = '%category%';
}

// Update category rewrite rules to handle hierarchy, pagination, and feeds
add_filter('category_rewrite_rules', 'update_category_rewrite_rules');
function update_category_rewrite_rules($rules) {
    $new_rules = array();
    $categories = get_categories(array('hide_empty' => false));

    foreach ($categories as $category) {
        // Get the full hierarchical path of the category
        $category_nicename = rtrim(get_category_parents($category->term_id, false, '/', true), '/');
        $category_nicename = sanitize_title($category_nicename); // Sanitize slug

        // Add rewrite rules for hierarchical category structures
        $new_rules["({$category_nicename})/page/?([0-9]{1,})/?$"] = 'index.php?category_name=$matches[1]&paged=$matches[2]';
        $new_rules["({$category_nicename})/(feed|rdf|rss|rss2|atom)/?$"] = 'index.php?category_name=$matches[1]&feed=$matches[2]';
        $new_rules["({$category_nicename})/?$"] = 'index.php?category_name=$matches[1]';
    }

    // Redirect old category base URLs
    $old_base = sanitize_title(get_option('category_base', 'category'));
    $new_rules["{$old_base}/(.*)$"] = 'index.php?category_redirect=$matches[1]';

    return $new_rules + $rules;
}

// Add 'category_redirect' to query variables for old URLs
add_filter('query_vars', function($query_vars) {
    $query_vars[] = 'category_redirect';
    return $query_vars;
});

// Handle 301 redirects for old category base URLs (SEO-friendly: both trailing and non-trailing slashes)
add_filter('request', function($query_vars) {
    if (isset($query_vars['category_redirect'])) {
        $catlink = home_url($query_vars['category_redirect']);
        $permalink_structure = get_option('permalink_structure');

        // Redirect based on permalink structure (trailing or non-trailing slashes)
        $catlink = ($permalink_structure && substr($permalink_structure, -1) === '/')
            ? trailingslashit($catlink)   // Add trailing slash if permalinks end with '/'
            : untrailingslashit($catlink); // Remove trailing slash if not

        wp_safe_redirect(esc_url_raw($catlink), 301);
        exit;
    }
    return $query_vars;
});

// Flush rewrite rules when categories are created, edited, or deleted
add_action('created_category', 'remove_category_base_flush_rewrite');
add_action('edited_category', 'remove_category_base_flush_rewrite');
add_action('delete_category', 'remove_category_base_flush_rewrite');

// Display admin success notice when rewrite rules have been flushed
add_action('admin_notices', 'remove_category_base_admin_notice');
function remove_category_base_admin_notice() {
    if (get_transient('remove_category_base_flush_notice')) {
        ?>
        <div class="notice notice-success">
            <p><?php esc_html_e('The category base has been removed and rewrite rules successfully flushed.', 'remove-category-base'); ?></p>
        </div>
        <?php
        delete_transient('remove_category_base_flush_notice');
    }
}

// Ref: ChatGPT
// Ref: https://wordpress.org/plugins/no-category-base-wpml/
