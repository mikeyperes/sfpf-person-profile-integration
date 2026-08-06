<?php

declare( strict_types=1 );

namespace sfpf_person_website;

/**
 * Elementor assignment, import, and template deletion actions.
 *
 * Callback names remain in the legacy namespace for template and hook compatibility.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Save Elementor Loop Assignments
 */
function ajax_save_elementor_loops() {
    $assignments_json = stripslashes($_POST['assignments'] ?? '{}');
    $assignments = json_decode($assignments_json, true);

    if (!is_array($assignments)) {
        $assignments = [];
    }

    // Sanitize
    $sanitized = [];
    foreach ($assignments as $cpt => $template_id) {
        $sanitized[sanitize_key($cpt)] = intval($template_id);
    }

    update_option('sfpf_elementor_loop_assignments', $sanitized);
    write_log("Elementor loop assignments saved");

    wp_send_json_success();
}

/**
 * Import Elementor Loop Templates
 */
function ajax_import_elementor_templates() {
    // Check if Elementor is active
    if (!defined('ELEMENTOR_VERSION')) {
        wp_send_json_error('Elementor is not active');
        return;
    }

    $templates = $_POST['templates'] ?? [];

    if (!is_array($templates) || empty($templates)) {
        wp_send_json_error('No templates selected');
        return;
    }

    // Define available templates with post type settings
    $available_templates = [
        'hexa-book' => [
            'name' => 'Hexa - Book - Default Loop Item #1',
            'file' => 'hexa-book-default-loop.json',
            'post_type' => 'book',
        ],
        'hexa-organization' => [
            'name' => 'Hexa - Organization - Default Loop Item #1',
            'file' => 'hexa-organization-default-loop.json',
            'post_type' => 'organization',
        ],
        'hexa-testimonial' => [
            'name' => 'Hexa - Testimonial - Default Loop Item #1',
            'file' => 'hexa-testimonial-default-loop.json',
            'post_type' => 'testimonial',
        ],
    ];

    $imported = 0;
    $errors = [];

    foreach ($templates as $template_key) {
        $template_key = sanitize_key($template_key);

        if (!isset($available_templates[$template_key])) {
            $errors[] = "Unknown template: {$template_key}";
            continue;
        }

        $template_info = $available_templates[$template_key];
        $file_path = SFPF_PLUGIN_DIR . 'assets/elementor-templates/' . $template_info['file'];

        if (!file_exists($file_path)) {
            $errors[] = "Template file not found: {$template_info['file']}";
            continue;
        }

        // Read template JSON
        $json_content = file_get_contents($file_path);
        $template_data = json_decode($json_content, true);

        if (!$template_data || !isset($template_data['content'])) {
            $errors[] = "Invalid template format: {$template_info['file']}";
            continue;
        }

        // Check if template with same name already exists
        $existing = get_posts([
            'post_type' => 'elementor_library',
            'title' => $template_info['name'],
            'post_status' => 'publish',
            'posts_per_page' => 1,
        ]);

        if (!empty($existing)) {
            $errors[] = "Template already exists: {$template_info['name']}";
            continue;
        }

        // Create the template post
        $post_id = wp_insert_post([
            'post_title' => $template_info['name'],
            'post_status' => 'publish',
            'post_type' => 'elementor_library',
        ]);

        if (is_wp_error($post_id)) {
            $errors[] = "Failed to create template: {$template_info['name']}";
            continue;
        }

        // Set template type meta for loop-item
        update_post_meta($post_id, '_elementor_template_type', 'loop-item');
        update_post_meta($post_id, '_elementor_edit_mode', 'builder');
        update_post_meta($post_id, '_elementor_version', defined('ELEMENTOR_VERSION') ? ELEMENTOR_VERSION : '3.25.0');

        // Set the Elementor data (the content array) - this is critical
        $content_data = $template_data['content'];

        // Ensure content is properly formatted
        if (!empty($content_data)) {
            update_post_meta($post_id, '_elementor_data', wp_json_encode($content_data));
        }

        // Set page settings with correct post type for preview
        $page_settings = $template_data['page_settings'] ?? [];
        $page_settings['preview_type'] = 'single/' . $template_info['post_type'];

        // Find a sample post for preview
        $sample_posts = get_posts([
            'post_type' => $template_info['post_type'],
            'posts_per_page' => 1,
            'post_status' => 'publish',
        ]);
        if (!empty($sample_posts)) {
            $page_settings['preview_id'] = $sample_posts[0]->ID;
        }

        update_post_meta($post_id, '_elementor_page_settings', $page_settings);

        // Set taxonomy for loop item to specify source
        wp_set_object_terms($post_id, 'loop-item', 'elementor_library_type');

        // Store additional meta for the source post type
        update_post_meta($post_id, '_elementor_source', 'local');
        update_post_meta($post_id, '_wp_page_template', 'elementor_canvas');
        update_post_meta($post_id, '_elementor_css', ''); // Will be regenerated by Elementor

        // Store which post type this loop is for (our own meta)
        update_post_meta($post_id, '_sfpf_loop_post_type', $template_info['post_type']);

        $imported++;
        write_log("Imported Elementor template: {$template_info['name']} (ID: {$post_id}, Post Type: {$template_info['post_type']})");
    }

    if ($imported > 0) {
        $message = "Successfully imported {$imported} template" . ($imported > 1 ? 's' : '');
        if (!empty($errors)) {
            $message .= ". Some errors: " . implode(', ', $errors);
        }
        wp_send_json_success(['message' => $message, 'imported' => $imported]);
    } else {
        wp_send_json_error(implode(', ', $errors) ?: 'No templates imported');
    }
}

/**
 * Delete Elementor template
 */
function ajax_delete_elementor_template() {
    $template_id = intval($_POST['template_id'] ?? 0);

    if (!$template_id) {
        wp_send_json_error('Invalid template ID');
    }

    // Verify it's an Elementor template
    $post = get_post($template_id);
    if (!$post || $post->post_type !== 'elementor_library') {
        wp_send_json_error('Not a valid Elementor template');
    }

    // Delete the template
    $result = wp_delete_post($template_id, true);

    if ($result) {
        write_log("Deleted Elementor template ID: {$template_id}");
        wp_send_json_success(['deleted' => $template_id]);
    } else {
        wp_send_json_error('Failed to delete template');
    }
}
