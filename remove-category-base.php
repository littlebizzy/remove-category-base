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
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Disable WordPress.org updates for this plugin
add_filter( 'gu_override_dot_org', function ( $overrides ) {
    $overrides[] = 'remove-category-base/remove-category-base.php';
    return $overrides;
});

// Hook to remove the category base from permalinks
add_action( 'init', 'remove_category_base' );
function remove_category_base() {
    global $wp_rewrite;
    $wp_rewrite->extra_permastructs['category']['struct'] = '%category%';
}

// Hook to flush rewrite rules during plugin activation
register_activation_hook( __FILE__, 'remove_category_base_on_activation' );
function remove_category_base_on_activation() {
    flush_rewrite_rules();  // Ensure the rules are flushed once
}

// Hook to restore default structure and flush rewrite rules on deactivation
register_deactivation_hook( __FILE__, 'remove_category_base_on_deactivation' );
function remove_category_base_on_deactivation() {
    global $wp_rewrite;
    $wp_rewrite->extra_permastructs['category']['struct'] = '/category/%category%';
    flush_rewrite_rules();
}

// Hook to flush rewrite rules when categories are created, edited, or deleted
add_action( 'created_category', 'flush_rewrite_rules' );
add_action( 'edited_category', 'flush_rewrite_rules' );
add_action( 'delete_category', 'flush_rewrite_rules' );

// Update category rewrite rules to handle hierarchy, pagination, and feeds
add_filter( 'category_rewrite_rules', 'update_category_rewrite_rules' );
function update_category_rewrite_rules( $rules ) {
    $new_rules  = array();
    $categories = get_categories( array( 'hide_empty' => false ) );

    foreach ( $categories as $category ) {
        // Get full hierarchical path without HTML
        $category_nicename = rtrim( get_category_parents( $category->term_id, false, '/', false ), '/' ); // get full hierarchical path without HTML
        $category_nicename = sanitize_title( $category_nicename ); // sanitize slug

        // Add rewrite rules for hierarchical category structures
        $new_rules["({$category_nicename})/page/?([0-9]{1,})/?$"] = 'index.php?category_name=$matches[1]&paged=$matches[2]';
        $new_rules["({$category_nicename})/(feed|rdf|rss|rss2|atom)/?$"] = 'index.php?category_name=$matches[1]&feed=$matches[2]';
        $new_rules["({$category_nicename})/?$"] = 'index.php?category_name=$matches[1]';
    }

    // Redirect old category base URLs
    $old_base = sanitize_title( get_option( 'category_base', 'category' ) );
    $old_base = trim( $old_base, '/' ); // Ensure no leading or trailing slashes
    
    // Check if site uses trailing slashes in permalinks
    $permalink_structure = get_option( 'permalink_structure' );
    if ( $permalink_structure && substr( $permalink_structure, -1 ) === '/' ) {
        // Site uses trailing slashes
        $new_rules[ trailingslashit( $old_base ) . '(.*)$' ] = 'index.php?category_redirect=$matches[1]';
    } else {
        // Site does not use trailing slashes
        $new_rules[ untrailingslashit( $old_base ) . '/(.*)$' ] = 'index.php?category_redirect=$matches[1]';
    }
    
    return $new_rules + $rules;
}

// Add 'category_redirect' to query variables for old URLs
add_filter( 'query_vars', function( $query_vars ) {
    $query_vars[] = 'category_redirect';
    return $query_vars;
});

// Handle 301 redirects for old category base URLs (supports both trailing and non-trailing slashes)
add_filter( 'request', function( $query_vars ) {
    if ( isset( $query_vars['category_redirect'] ) && ! empty( $query_vars['category_redirect'] ) ) {
        // Build the new category link
        $catlink = home_url( sanitize_title( $query_vars['category_redirect'] ) );
        $permalink_structure = get_option( 'permalink_structure' );

        // Redirect based on permalink structure (trailing or non-trailing slashes)
        $catlink = ( $permalink_structure && substr( $permalink_structure, -1 ) === '/' )
            ? trailingslashit( $catlink )   // add trailing slash
            : untrailingslashit( $catlink ); // remove trailing slash

        wp_safe_redirect( esc_url_raw( $catlink ), 301 );
        exit;
    }
    return $query_vars;
});

// Ref: ChatGPT
// Ref: https://wordpress.org/plugins/no-category-base-wpml/
