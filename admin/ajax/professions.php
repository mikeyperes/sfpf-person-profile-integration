<?php

declare( strict_types=1 );

namespace sfpf_person_website;

/**
 * Profession page creation and deletion actions.
 *
 * Callback names remain in the legacy namespace for template and hook compatibility.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Create profession page
 */
function ajax_create_profession_page() {
    verify_ajax_nonce();

    $profession = sanitize_text_field($_POST['profession'] ?? '');
    $index = intval($_POST['index'] ?? 0);

    if (empty($profession)) {
        wp_send_json_error('Profession name is required');
    }

    // Get professions page as parent
    $professions_page_id = get_option('sfpf_page_professions', 0);

    // Create slug from profession name
    $slug = sanitize_title($profession);

    // Check if page already exists
    $existing = get_posts([
        'name' => $slug,
        'post_type' => 'page',
        'post_status' => 'publish',
        'posts_per_page' => 1,
    ]);

    if (!empty($existing)) {
        $page_id = $existing[0]->ID;
    } else {
        // Create the page
        $page_id = wp_insert_post([
            'post_title' => $profession,
            'post_name' => $slug,
            'post_content' => '',
            'post_status' => 'publish',
            'post_type' => 'page',
            'post_parent' => $professions_page_id > 0 ? $professions_page_id : 0,
        ]);

        if (is_wp_error($page_id)) {
            wp_send_json_error($page_id->get_error_message());
        }
    }

    // Update the founder's professions ACF field to link to this page
    $founder_user_id = get_founder_user_id();
    if ($founder_user_id) {
        $professions = get_field('professions', 'user_' . $founder_user_id) ?: [];
        if (isset($professions[$index])) {
            $professions[$index]['page'] = $page_id;
            update_field('professions', $professions, 'user_' . $founder_user_id);
        }
    }

    write_log("Profession page created: {$profession} (ID: {$page_id})");

    wp_send_json_success([
        'page_id' => $page_id,
        'title' => $profession,
        'permalink' => get_permalink($page_id),
        'edit_url' => get_edit_post_link($page_id, 'raw'),
    ]);
}
add_action('wp_ajax_sfpf_create_profession_page', __NAMESPACE__ . '\\ajax_create_profession_page');

/**
 * Delete profession page and unlink from ACF
 */
function ajax_delete_profession_page() {
    verify_ajax_nonce();

    $page_id = intval($_POST['page_id'] ?? 0);
    $index = intval($_POST['index'] ?? 0);

    if (!$page_id) {
        wp_send_json_error('Invalid page ID');
    }

    // Unlink from ACF professions field
    $founder_user_id = get_founder_user_id();
    if ($founder_user_id) {
        $professions = get_field('professions', 'user_' . $founder_user_id) ?: [];
        if (isset($professions[$index])) {
            $professions[$index]['page'] = null;
            update_field('professions', $professions, 'user_' . $founder_user_id);
        }
    }

    // Trash the page
    $result = wp_trash_post($page_id);
    if (!$result) {
        wp_send_json_error('Failed to delete page');
    }

    write_log("Profession page deleted (ID: {$page_id})");
    wp_send_json_success(['page_id' => $page_id]);
}
add_action('wp_ajax_sfpf_delete_profession_page', __NAMESPACE__ . '\\ajax_delete_profession_page');
