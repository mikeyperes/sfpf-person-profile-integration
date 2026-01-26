<?php
namespace sfpf_person_website;

/**
 * AJAX Handlers
 * 
 * All AJAX operations for the plugin dashboard.
 * Uses unique action prefixes to avoid conflicts.
 * 
 * @package sfpf_person_website
 * @since 1.0.0
 */

defined('ABSPATH') || exit;

/**
 * Verify AJAX nonce
 */
function verify_ajax_nonce() {
    if (!wp_verify_nonce($_POST['nonce'] ?? '', 'sfpf_ajax')) {
        wp_send_json_error('Invalid security token');
        exit;
    }
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permission denied');
        exit;
    }
}

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
    
    $schema_type = sanitize_key($_POST['schema_type'] ?? 'profile_page');
    
    if (!in_array($schema_type, ['profile_page', 'person'])) {
        wp_send_json_error('Invalid schema type');
    }
    
    update_option('sfpf_homepage_schema_type', $schema_type);
    write_log("Homepage schema type set to: {$schema_type}");
    
    wp_send_json_success(['schema_type' => $schema_type]);
}
add_action('wp_ajax_sfpf_save_schema_type', __NAMESPACE__ . '\\ajax_save_schema_type');

/**
 * Reprocess schema
 */
function ajax_reprocess_schema() {
    verify_ajax_nonce();
    
    $type = sanitize_key($_POST['type'] ?? '');
    $count = 0;
    
    switch ($type) {
        case 'homepage':
            // Rebuild homepage schema
            $front_page_id = get_front_page_id();
            if ($front_page_id) {
                $schema = function_exists(__NAMESPACE__ . '\\build_homepage_schema') ? build_homepage_schema() : [];
                update_post_meta($front_page_id, '_sfpf_schema', wp_json_encode($schema));
                $count = 1;
            }
            write_log("Reprocessed homepage schema");
            break;
            
        case 'books':
            $books = get_posts([
                'post_type' => 'book',
                'posts_per_page' => -1,
                'post_status' => 'publish',
            ]);
            
            foreach ($books as $book) {
                $schema = function_exists(__NAMESPACE__ . '\\build_book_schema') ? build_book_schema($book->ID) : [];
                update_post_meta($book->ID, '_sfpf_schema', wp_json_encode($schema));
                $count++;
            }
            write_log("Reprocessed {$count} book schemas");
            break;
            
        case 'organizations':
            $orgs = get_posts([
                'post_type' => 'organization',
                'posts_per_page' => -1,
                'post_status' => 'publish',
            ]);
            
            foreach ($orgs as $org) {
                $schema = function_exists(__NAMESPACE__ . '\\build_organization_schema') ? build_organization_schema($org->ID) : [];
                update_post_meta($org->ID, '_sfpf_schema', wp_json_encode($schema));
                $count++;
            }
            write_log("Reprocessed {$count} organization schemas");
            break;
            
        default:
            wp_send_json_error('Invalid schema type');
    }
    
    wp_send_json_success(['type' => $type, 'count' => $count]);
}
add_action('wp_ajax_sfpf_reprocess_schema', __NAMESPACE__ . '\\ajax_reprocess_schema');

/**
 * Rebuild all schemas
 */
function ajax_rebuild_all_schema() {
    verify_ajax_nonce();
    
    $counts = ['homepage' => 0, 'books' => 0, 'organizations' => 0];
    
    // Homepage
    $front_page_id = get_front_page_id();
    if ($front_page_id) {
        $schema = function_exists(__NAMESPACE__ . '\\build_homepage_schema') ? build_homepage_schema() : [];
        update_post_meta($front_page_id, '_sfpf_schema', wp_json_encode($schema));
        $counts['homepage'] = 1;
    }
    
    // Books
    if (is_snippet_enabled('sfpf_enable_book_cpt')) {
        $books = get_posts([
            'post_type' => 'book',
            'posts_per_page' => -1,
            'post_status' => 'publish',
        ]);
        
        foreach ($books as $book) {
            $schema = function_exists(__NAMESPACE__ . '\\build_book_schema') ? build_book_schema($book->ID) : [];
            update_post_meta($book->ID, '_sfpf_schema', wp_json_encode($schema));
            $counts['books']++;
        }
    }
    
    // Organizations
    if (is_snippet_enabled('sfpf_enable_organization_cpt')) {
        $orgs = get_posts([
            'post_type' => 'organization',
            'posts_per_page' => -1,
            'post_status' => 'publish',
        ]);
        
        foreach ($orgs as $org) {
            $schema = function_exists(__NAMESPACE__ . '\\build_organization_schema') ? build_organization_schema($org->ID) : [];
            update_post_meta($org->ID, '_sfpf_schema', wp_json_encode($schema));
            $counts['organizations']++;
        }
    }
    
    write_log("Rebuilt all schemas: homepage={$counts['homepage']}, books={$counts['books']}, orgs={$counts['organizations']}");
    
    wp_send_json_success($counts);
}
add_action('wp_ajax_sfpf_rebuild_all_schema', __NAMESPACE__ . '\\ajax_rebuild_all_schema');

/**
 * Assign page
 */
function ajax_assign_page() {
    verify_ajax_nonce();
    
    $page_key = sanitize_key($_POST['page_key'] ?? '');
    $page_id = intval($_POST['page_id'] ?? 0);
    $parent_key = sanitize_key($_POST['parent_key'] ?? '');
    
    if (!$page_key) {
        wp_send_json_error('Invalid page key');
    }
    
    // Save the assignment
    update_option('sfpf_page_' . $page_key, $page_id);
    
    // If page was selected (not unassigned) and has a parent, update the page hierarchy
    if ($page_id > 0 && $parent_key) {
        $parent_page_id = get_option('sfpf_page_' . $parent_key, 0);
        
        if ($parent_page_id > 0) {
            // Update the page's parent to maintain hierarchy
            wp_update_post([
                'ID' => $page_id,
                'post_parent' => $parent_page_id,
            ]);
            write_log("Page {$page_id} assigned to {$page_key} with parent {$parent_page_id}");
        }
    } else {
        write_log("Page assigned: {$page_key} = {$page_id}");
    }
    
    wp_send_json_success(['page_key' => $page_key, 'page_id' => $page_id]);
}
add_action('wp_ajax_sfpf_assign_page', __NAMESPACE__ . '\\ajax_assign_page');

/**
 * Create page
 */
function ajax_create_page() {
    verify_ajax_nonce();
    
    $page_key = sanitize_key($_POST['page_key'] ?? '');
    $title = sanitize_text_field($_POST['title'] ?? '');
    $slug = sanitize_title($_POST['slug'] ?? '');
    $parent_key = sanitize_key($_POST['parent_key'] ?? '');
    
    if (!$page_key || !$title) {
        wp_send_json_error('Invalid page data');
    }
    
    // Get parent ID if specified
    $parent_id = 0;
    if ($parent_key) {
        $parent_id = get_option('sfpf_page_' . $parent_key, 0);
    }
    
    // Check if page already exists
    $existing = get_page_by_path($slug);
    if ($existing) {
        update_option('sfpf_page_' . $page_key, $existing->ID);
        wp_send_json_success(['page_id' => $existing->ID, 'existing' => true]);
        return;
    }
    
    // Create page
    $page_data = [
        'post_title' => $title,
        'post_name' => $slug,
        'post_content' => '',
        'post_status' => 'publish',
        'post_type' => 'page',
        'post_parent' => $parent_id,
    ];
    
    $page_id = wp_insert_post($page_data);
    
    if (is_wp_error($page_id)) {
        wp_send_json_error($page_id->get_error_message());
    }
    
    update_option('sfpf_page_' . $page_key, $page_id);
    write_log("Page created: {$title} (ID: {$page_id}, key: {$page_key})");
    
    wp_send_json_success(['page_id' => $page_id]);
}
add_action('wp_ajax_sfpf_create_page', __NAMESPACE__ . '\\ajax_create_page');

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
    
    $content = get_option('sfpf_template_' . $template_key, '');
    
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
 * Clear log
 */
function ajax_clear_log() {
    verify_ajax_nonce();
    
    delete_option('sfpf_activity_log');
    write_log("Activity log cleared");
    
    wp_send_json_success();
}
add_action('wp_ajax_sfpf_clear_log', __NAMESPACE__ . '\\ajax_clear_log');
