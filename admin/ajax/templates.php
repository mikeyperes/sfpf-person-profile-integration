<?php

declare( strict_types=1 );

namespace sfpf_person_website;

/**
 * Template save and application actions.
 *
 * Callback names remain in the legacy namespace for template and hook compatibility.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Save template
 */
function ajax_save_template() {
    verify_ajax_nonce();

    $template_key = sanitize_key($_POST['template_key'] ?? '');
    $content = wp_kses_post($_POST['content'] ?? '');

    if (!$template_key) {
        wp_send_json_error('Invalid template key');
    }

    update_option('sfpf_template_' . $template_key, $content);
    write_log("Template saved: {$template_key}");

    wp_send_json_success(['template_key' => $template_key]);
}
add_action('wp_ajax_sfpf_save_template', __NAMESPACE__ . '\\ajax_save_template');

/**
 * Apply template to page
 */
function ajax_apply_template() {
    verify_ajax_nonce();

    $template_key = sanitize_key($_POST['template_key'] ?? '');

    if (!$template_key) {
        wp_send_json_error('Invalid template key');
    }

    $page_id = get_option('sfpf_page_' . $template_key, 0);

    if (!$page_id) {
        wp_send_json_error('No page assigned for this template. Please assign a page first.');
    }

    $content = get_option('sfpf_template_' . $template_key, '__sfpf_template_missing__');
    if ($content === '__sfpf_template_missing__' && function_exists(__NAMESPACE__ . '\\get_default_page_template')) {
        $content = get_default_page_template($template_key);
    }

    $result = wp_update_post([
        'ID' => $page_id,
        'post_content' => $content,
    ]);

    if (is_wp_error($result)) {
        wp_send_json_error($result->get_error_message());
    }

    write_log("Template applied: {$template_key} to page {$page_id}");

    wp_send_json_success(['page_id' => $page_id]);
}
add_action('wp_ajax_sfpf_apply_template', __NAMESPACE__ . '\\ajax_apply_template');

/**
 * Apply default template to page
 */
function ajax_apply_default_template() {
    verify_ajax_nonce();

    $page_id = intval($_POST['page_id'] ?? 0);
    $page_key = sanitize_key($_POST['page_key'] ?? '');
    $force = isset($_POST['force']) && $_POST['force'] === 'true';

    if (!$page_id || !$page_key) {
        wp_send_json_error('Invalid page data');
    }

    $page = get_post($page_id);
    if (!$page) {
        wp_send_json_error('Page not found');
    }

    // Check if page has content
    $has_content = !empty(trim($page->post_content));

    if ($has_content && !$force) {
        wp_send_json_error([
            'code' => 'has_content',
            'message' => 'Page already has content. Overwrite?'
        ]);
    }

    // Get default template
    $template_content = get_default_page_template($page_key);

    if (empty($template_content)) {
        wp_send_json_error('No default template available for this page type');
    }

    $result = wp_update_post([
        'ID' => $page_id,
        'post_content' => $template_content,
    ]);

    if (is_wp_error($result)) {
        wp_send_json_error($result->get_error_message());
    }

    write_log("Default template applied to page {$page_id} (key: {$page_key})");

    wp_send_json_success(['page_id' => $page_id, 'page_key' => $page_key]);
}
add_action('wp_ajax_sfpf_apply_default_template', __NAMESPACE__ . '\\ajax_apply_default_template');
