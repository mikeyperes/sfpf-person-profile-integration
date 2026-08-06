<?php

declare( strict_types=1 );

namespace sfpf_person_website;

/**
 * Knowledge graph normalization and Rank Math integration.
 *
 * Callback names remain in the legacy namespace for template and hook compatibility.
 */

defined( 'ABSPATH' ) || exit;

// ============================================================================
// GOOGLE KNOWLEDGE GRAPH ID AUTO-EXTRACTION
// ============================================================================

/**
 * Auto-extract KGMID from full Google URL when saving the field
 * If user pastes full URL like https://www.google.com/search?kgmid=/g/11gyz2y3lp&...
 * we extract just /g/11gyz2y3lp
 */
function sanitize_kgid_on_save($value, $post_id, $field) {
    if (empty($value) || !is_string($value)) return $value;
    $value = trim($value);

    // If it looks like a full URL, extract kgmid parameter
    if (preg_match('/kgmid=([^&\s]+)/i', $value, $m)) {
        $value = urldecode($m[1]);
    }

    // Clean up - should start with /
    $value = trim($value);
    if (!empty($value) && $value[0] !== '/') {
        // If just the ID without leading slash
        if (preg_match('#^[a-z]/[a-zA-Z0-9_]+$#', $value)) {
            $value = '/' . $value;
        }
    }

    return $value;
}
add_filter('acf/update_value/name=knowledge_graph_id', __NAMESPACE__ . '\\sanitize_kgid_on_save', 10, 3);

// ============================================================================
// RANKMATH SCHEMA CONTROL
// ============================================================================

/**
 * Disable RankMath schema on specific post types
 */
function disable_rankmath_schema($data) {
    if (is_front_page() && get_option('sfpf_rankmath_disable_homepage', false)) {
        return [];
    }

    $bio_page_id = (int) get_option('sfpf_page_biography', 0);
    if ($bio_page_id && is_page($bio_page_id) && get_option('sfpf_rankmath_disable_biography', false)) {
        return [];
    }

    if (is_singular('book') && get_option('sfpf_rankmath_disable_books', false)) {
        return [];
    }

    return $data;
}
add_filter('rank_math/json_ld', __NAMESPACE__ . '\\disable_rankmath_schema', 999);

// ============================================================================
// RANKMATH BREADCRUMB VISIBILITY CONTROL
// ============================================================================

/**
 * Hide RankMath breadcrumbs on front page, excluded pages, and excluded CPT singles
 * Hooks into the breadcrumb HTML output and returns empty string to hide
 */
function filter_rankmath_breadcrumbs($html) {
    // Hide on front page
    if (is_front_page() && get_option('sfpf_breadcrumb_hide_frontpage', false)) {
        return '';
    }

    // Hide on excluded pages
    $excluded_pages = get_option('sfpf_breadcrumb_excluded_pages', []);
    if (is_array($excluded_pages) && !empty($excluded_pages) && is_page()) {
        $current_id = get_queried_object_id();
        if (in_array($current_id, array_map('intval', $excluded_pages))) {
            return '';
        }
    }

    // Hide on excluded CPT singles
    $excluded_cpts = get_option('sfpf_breadcrumb_excluded_cpts', []);
    if (is_array($excluded_cpts) && !empty($excluded_cpts) && is_singular()) {
        $current_type = get_post_type();
        if (in_array($current_type, $excluded_cpts)) {
            return '';
        }
    }

    return $html;
}
add_filter('rank_math/frontend/breadcrumb/html', __NAMESPACE__ . '\\filter_rankmath_breadcrumbs', 10);
