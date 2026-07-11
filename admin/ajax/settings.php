<?php

declare( strict_types=1 );

namespace sfpf_person_website;

/**
 * Snippet, schema type, Rank Math, and breadcrumb settings actions.
 *
 * Callback names remain in the legacy namespace for template and hook compatibility.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Toggle snippet
 */
function ajax_toggle_snippet() {
    verify_ajax_nonce();

    $snippet_id = sanitize_key($_POST['snippet_id'] ?? '');
    $enabled = intval($_POST['enabled'] ?? 0);

    if (!$snippet_id) {
        wp_send_json_error('Invalid snippet ID');
    }

    update_option($snippet_id, $enabled ? 1 : 0);
    write_log("Snippet toggled: {$snippet_id} = " . ($enabled ? 'enabled' : 'disabled'));

    wp_send_json_success(['snippet_id' => $snippet_id, 'enabled' => $enabled]);
}
add_action('wp_ajax_sfpf_toggle_snippet', __NAMESPACE__ . '\\ajax_toggle_snippet');

/**
 * Save schema type
 */
function ajax_save_schema_type() {
    verify_ajax_nonce();

    $schema_type = sanitize_key($_POST['schema_type'] ?? 'none');

    $valid_types = ['none', 'profile_page_only', 'person', 'profile_page'];
    if (!in_array($schema_type, $valid_types)) {
        wp_send_json_error('Invalid schema type');
    }

    update_option('sfpf_homepage_schema_type', $schema_type);
    update_option('sfpf_homepage_schema_explicitly_saved', true);
    write_log("Homepage schema type set to: {$schema_type}");

    wp_send_json_success(['schema_type' => $schema_type]);
}
add_action('wp_ajax_sfpf_save_schema_type', __NAMESPACE__ . '\\ajax_save_schema_type');

/**
 * Save Biography schema type
 */
function ajax_save_biography_schema_type() {
    verify_ajax_nonce();

    $schema_type = sanitize_key($_POST['schema_type'] ?? 'none');
    $valid_types = ['none', 'person', 'profile_page', 'profile_page_only'];

    if (!in_array($schema_type, $valid_types)) {
        wp_send_json_error('Invalid schema type');
    }

    update_option('sfpf_biography_schema_type', $schema_type);
    write_log("Biography schema type set to: {$schema_type}");

    wp_send_json_success(['schema_type' => $schema_type]);
}
add_action('wp_ajax_sfpf_save_biography_schema_type', __NAMESPACE__ . '\\ajax_save_biography_schema_type');

/**
 * Save RankMath settings
 */
function ajax_save_rankmath_settings() {
    verify_ajax_nonce();

    $disable_homepage = !empty($_POST['disable_homepage']);
    $disable_biography = !empty($_POST['disable_biography']);
    $disable_books = !empty($_POST['disable_books']);
    $disable_organizations = !empty($_POST['disable_organizations']);
    $disable_testimonials = !empty($_POST['disable_testimonials']);

    update_option('sfpf_rankmath_disable_homepage', $disable_homepage);
    update_option('sfpf_rankmath_disable_biography', $disable_biography);
    update_option('sfpf_rankmath_disable_books', $disable_books);
    update_option('sfpf_rankmath_disable_organizations', $disable_organizations);
    update_option('sfpf_rankmath_disable_testimonials', $disable_testimonials);

    write_log("RankMath settings updated");

    wp_send_json_success();
}
add_action('wp_ajax_sfpf_save_rankmath_settings', __NAMESPACE__ . '\\ajax_save_rankmath_settings');

/**
 * Save Breadcrumb visibility settings
 */
function ajax_save_breadcrumb_settings() {
    verify_ajax_nonce();

    $hide_frontpage = !empty($_POST['hide_frontpage']);
    $excluded_pages = isset($_POST['excluded_pages']) && is_array($_POST['excluded_pages'])
        ? array_map('intval', $_POST['excluded_pages'])
        : [];
    $excluded_cpts = isset($_POST['excluded_cpts']) && is_array($_POST['excluded_cpts'])
        ? array_map('sanitize_key', $_POST['excluded_cpts'])
        : [];

    update_option('sfpf_breadcrumb_hide_frontpage', $hide_frontpage);
    update_option('sfpf_breadcrumb_excluded_pages', $excluded_pages);
    update_option('sfpf_breadcrumb_excluded_cpts', $excluded_cpts);

    write_log("Breadcrumb settings updated — front page: " . ($hide_frontpage ? 'hidden' : 'visible') . ", excluded pages: " . count($excluded_pages) . ", excluded CPTs: " . implode(', ', $excluded_cpts));

    wp_send_json_success();
}
add_action('wp_ajax_sfpf_save_breadcrumb_settings', __NAMESPACE__ . '\\ajax_save_breadcrumb_settings');
