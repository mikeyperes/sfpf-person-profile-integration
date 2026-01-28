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
    
    $schema_type = sanitize_key($_POST['schema_type'] ?? 'none');
    
    $valid_types = ['none', 'profile_page_only', 'person', 'profile_page'];
    if (!in_array($schema_type, $valid_types)) {
        wp_send_json_error('Invalid schema type');
    }
    
    update_option('sfpf_homepage_schema_type', $schema_type);
    write_log("Homepage schema type set to: {$schema_type}");
    
    wp_send_json_success(['schema_type' => $schema_type]);
}
add_action('wp_ajax_sfpf_save_schema_type', __NAMESPACE__ . '\\ajax_save_schema_type');

/**
 * Save RankMath settings
 */
function ajax_save_rankmath_settings() {
    verify_ajax_nonce();
    
    $disable_homepage = !empty($_POST['disable_homepage']);
    $disable_books = !empty($_POST['disable_books']);
    $disable_organizations = !empty($_POST['disable_organizations']);
    $disable_testimonials = !empty($_POST['disable_testimonials']);
    
    update_option('sfpf_rankmath_disable_homepage', $disable_homepage);
    update_option('sfpf_rankmath_disable_books', $disable_books);
    update_option('sfpf_rankmath_disable_organizations', $disable_organizations);
    update_option('sfpf_rankmath_disable_testimonials', $disable_testimonials);
    
    write_log("RankMath settings updated");
    
    wp_send_json_success();
}
add_action('wp_ajax_sfpf_save_rankmath_settings', __NAMESPACE__ . '\\ajax_save_rankmath_settings');

/**
 * Detect schema on URLs
 */
function ajax_detect_schema() {
    verify_ajax_nonce();
    
    $type = sanitize_key($_POST['type'] ?? '');
    $debug = !empty($_POST['debug']);
    
    $output = '';
    $urls = [];
    
    switch ($type) {
        case 'homepage':
            $urls[] = ['url' => home_url('/'), 'title' => 'Homepage'];
            break;
            
        case 'books':
            $books = get_posts(['post_type' => 'book', 'posts_per_page' => 5, 'post_status' => 'publish']);
            foreach ($books as $book) {
                $urls[] = ['url' => get_permalink($book->ID), 'title' => $book->post_title];
            }
            break;
            
        case 'organizations':
            $orgs = get_posts(['post_type' => 'organization', 'posts_per_page' => 5, 'post_status' => 'publish']);
            foreach ($orgs as $org) {
                $urls[] = ['url' => get_permalink($org->ID), 'title' => $org->post_title];
            }
            break;
            
        case 'testimonials':
            $testimonials = get_posts(['post_type' => 'testimonial', 'posts_per_page' => 5, 'post_status' => 'publish']);
            foreach ($testimonials as $t) {
                $urls[] = ['url' => get_permalink($t->ID), 'title' => $t->post_title];
            }
            break;
            
        default:
            wp_send_json_error('Invalid type');
    }
    
    if (empty($urls)) {
        $output = '<span style="color:#fbbf24;">No posts found for type: ' . esc_html($type) . '</span>';
        wp_send_json_success(['output' => $output]);
        return;
    }
    
    $output .= '<div style="color:#10b981;margin-bottom:10px;">📊 Schema Detection Results for: ' . strtoupper($type) . '</div>';
    $output .= '<div style="border-top:1px solid #374151;padding-top:10px;">';
    
    foreach ($urls as $item) {
        $response = wp_remote_get($item['url'], ['timeout' => 10, 'sslverify' => false]);
        
        $output .= '<div style="margin-bottom:15px;padding-bottom:15px;border-bottom:1px solid #374151;">';
        $output .= '<div style="color:#60a5fa;margin-bottom:5px;">🔗 ' . esc_html($item['title']) . '</div>';
        $output .= '<div style="color:#9ca3af;font-size:11px;margin-bottom:8px;">' . esc_html($item['url']) . '</div>';
        
        if (is_wp_error($response)) {
            $output .= '<span style="color:#f87171;">❌ Error: ' . esc_html($response->get_error_message()) . '</span>';
        } else {
            $body = wp_remote_retrieve_body($response);
            
            // Find all JSON-LD scripts
            preg_match_all('/<script[^>]*type=["\']application\/ld\+json["\'][^>]*>(.*?)<\/script>/si', $body, $matches);
            
            if (!empty($matches[1])) {
                $output .= '<span style="color:#10b981;">✅ Found ' . count($matches[1]) . ' schema block(s)</span>';
                
                foreach ($matches[1] as $i => $json_str) {
                    $schema = json_decode(trim($json_str), true);
                    
                    if ($schema) {
                        // Detect source
                        $source = 'Unknown';
                        if (strpos($json_str, 'rank-math') !== false || strpos($json_str, 'rankmath') !== false) {
                            $source = 'RankMath';
                        } elseif (strpos($json_str, 'yoast') !== false) {
                            $source = 'Yoast SEO';
                        } elseif (strpos($json_str, 'sfpf') !== false || strpos($json_str, 'sfpf_person') !== false) {
                            $source = 'SFPF Plugin';
                        }
                        
                        // Get types
                        $types = [];
                        if (isset($schema['@type'])) {
                            $types[] = $schema['@type'];
                        }
                        if (isset($schema['@graph'])) {
                            foreach ($schema['@graph'] as $node) {
                                if (isset($node['@type'])) {
                                    $types[] = $node['@type'];
                                }
                            }
                        }
                        
                        $output .= '<div style="margin:8px 0 0 15px;">';
                        $output .= '<span style="color:#a78bfa;">Block ' . ($i + 1) . ':</span> ';
                        $output .= '<span style="color:#fbbf24;">' . implode(', ', array_unique($types)) . '</span>';
                        
                        if ($debug) {
                            $output .= ' <span style="color:#9ca3af;">(Source: ' . $source . ')</span>';
                        }
                        
                        if ($debug) {
                            $output .= '<pre style="background:#0d1117;padding:10px;border-radius:4px;margin:5px 0;font-size:10px;max-height:200px;overflow:auto;">';
                            $output .= esc_html(json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
                            $output .= '</pre>';
                        }
                        
                        $output .= '</div>';
                    } else {
                        $output .= '<div style="margin:8px 0 0 15px;color:#f87171;">⚠ Block ' . ($i + 1) . ': Invalid JSON</div>';
                    }
                }
            } else {
                $output .= '<span style="color:#fbbf24;">⚠ No JSON-LD schema found</span>';
            }
        }
        
        $output .= '</div>';
    }
    
    $output .= '</div>';
    
    wp_send_json_success(['output' => $output]);
}
add_action('wp_ajax_sfpf_detect_schema', __NAMESPACE__ . '\\ajax_detect_schema');

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

/**
 * Save FAQ Sets
 */
function ajax_save_faq_sets() {
    verify_ajax_nonce();
    
    $faq_sets_json = stripslashes($_POST['faq_sets'] ?? '[]');
    $faq_sets = json_decode($faq_sets_json, true);
    
    if (!is_array($faq_sets)) {
        $faq_sets = [];
    }
    
    // Sanitize FAQ sets
    $sanitized_sets = [];
    foreach ($faq_sets as $set) {
        $sanitized_items = [];
        $items = $set['items'] ?? [];
        
        foreach ($items as $item) {
            if (!empty($item['question']) || !empty($item['answer'])) {
                $sanitized_items[] = [
                    'question' => sanitize_text_field($item['question'] ?? ''),
                    'answer' => wp_kses_post($item['answer'] ?? ''),
                ];
            }
        }
        
        if (!empty($set['name']) || !empty($sanitized_items)) {
            $sanitized_sets[] = [
                'name' => sanitize_text_field($set['name'] ?? ''),
                'slug' => sanitize_key($set['slug'] ?? 'faq-set-' . count($sanitized_sets)),
                'items' => $sanitized_items,
            ];
        }
    }
    
    update_option('sfpf_faq_sets', $sanitized_sets);
    
    $inject_schema = !empty($_POST['inject_schema']);
    update_option('sfpf_inject_faq_schema', $inject_schema);
    
    write_log("FAQ sets saved: " . count($sanitized_sets) . " sets");
    
    wp_send_json_success(['count' => count($sanitized_sets)]);
}
add_action('wp_ajax_sfpf_save_faq_sets', __NAMESPACE__ . '\\ajax_save_faq_sets');

/**
 * Save Elementor Loop Assignments
 */
function ajax_save_elementor_loops() {
    verify_ajax_nonce();
    
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
add_action('wp_ajax_sfpf_save_elementor_loops', __NAMESPACE__ . '\\ajax_save_elementor_loops');

/**
 * Import Elementor Loop Templates
 */
function ajax_import_elementor_templates() {
    verify_ajax_nonce();
    
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
    
    // Define available templates
    $available_templates = [
        'hexa-book' => [
            'name' => 'Hexa - Book - Default Loop Item #1',
            'file' => 'hexa-book-default-loop.json',
        ],
        'hexa-organization' => [
            'name' => 'Hexa - Organization - Default Loop Item #1',
            'file' => 'hexa-organization-default-loop.json',
        ],
        'hexa-testimonial' => [
            'name' => 'Hexa - Testimonial - Default Loop Item #1',
            'file' => 'hexa-testimonial-default-loop.json',
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
        
        // Set template type meta
        update_post_meta($post_id, '_elementor_template_type', 'loop-item');
        update_post_meta($post_id, '_elementor_edit_mode', 'builder');
        
        // Set the Elementor data
        update_post_meta($post_id, '_elementor_data', wp_json_encode($template_data['content']));
        
        // Set page settings if available
        if (!empty($template_data['page_settings'])) {
            update_post_meta($post_id, '_elementor_page_settings', $template_data['page_settings']);
        }
        
        $imported++;
        write_log("Imported Elementor template: {$template_info['name']} (ID: {$post_id})");
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
add_action('wp_ajax_sfpf_import_elementor_templates', __NAMESPACE__ . '\\ajax_import_elementor_templates');
