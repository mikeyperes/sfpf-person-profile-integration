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
        $output = '<span style="color:#fbbf24;">⚠️ No posts found for type: ' . esc_html($type) . '</span>';
        wp_send_json_success(['output' => $output]);
        return;
    }
    
    $scan_time = current_time('Y-m-d H:i:s');
    $output .= '<div style="color:#10b981;margin-bottom:5px;font-size:14px;">📊 Schema Detection Results: ' . strtoupper($type) . '</div>';
    $output .= '<div style="color:#6b7280;font-size:11px;margin-bottom:10px;">🕐 Scanned at: ' . esc_html($scan_time) . ' (cache bypassed)</div>';
    $output .= '<div style="border-top:1px solid #374151;padding-top:10px;">';
    
    foreach ($urls as $item) {
        // Add cache-busting query parameter
        $cache_bust = 'sfpf_nocache=' . time() . '_' . wp_rand(1000, 9999);
        $fetch_url = add_query_arg($cache_bust, '', $item['url']);
        
        $start_time = microtime(true);
        $response = wp_remote_get($fetch_url, [
            'timeout' => 15,
            'sslverify' => false,
            'headers' => [
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
            ],
        ]);
        $fetch_time = round((microtime(true) - $start_time) * 1000);
        
        $output .= '<div style="margin-bottom:15px;padding-bottom:15px;border-bottom:1px solid #374151;">';
        $output .= '<div style="color:#60a5fa;margin-bottom:5px;font-size:13px;">🔗 ' . esc_html($item['title']) . '</div>';
        $output .= '<div style="color:#9ca3af;font-size:11px;margin-bottom:8px;">';
        $output .= '<a href="' . esc_url($item['url']) . '" target="_blank" style="color:#9ca3af;">' . esc_html($item['url']) . '</a>';
        $output .= '</div>';
        
        if (is_wp_error($response)) {
            $output .= '<div style="color:#f87171;font-size:12px;">❌ HTTP Error: ' . esc_html($response->get_error_message()) . '</div>';
            $output .= '<div style="color:#6b7280;font-size:11px;margin-top:4px;">⏱️ Response time: ' . $fetch_time . 'ms</div>';
        } else {
            $status_code = wp_remote_retrieve_response_code($response);
            $body = wp_remote_retrieve_body($response);
            $body_size = strlen($body);
            
            // Log HTTP details
            $output .= '<div style="color:#6b7280;font-size:11px;margin-bottom:8px;">';
            $output .= '📡 HTTP ' . $status_code . ' | ⏱️ ' . $fetch_time . 'ms | 📦 ' . number_format($body_size) . ' bytes';
            $output .= '</div>';
            
            // Find all JSON-LD scripts
            preg_match_all('/<script[^>]*type=["\']application\/ld\+json["\'][^>]*>(.*?)<\/script>/si', $body, $matches);
            
            if (!empty($matches[1])) {
                $output .= '<div style="color:#10b981;font-size:12px;margin-bottom:8px;">✅ Found ' . count($matches[1]) . ' schema block(s)</div>';
                
                foreach ($matches[1] as $i => $json_str) {
                    $schema = json_decode(trim($json_str), true);
                    
                    if ($schema) {
                        // Detect source based on various markers
                        $source = 'Unknown';
                        $source_color = '#9ca3af';
                        
                        if (strpos($json_str, 'rank-math') !== false || strpos($json_str, 'rankmath') !== false) {
                            $source = 'RankMath';
                            $source_color = '#e91e63';
                        } elseif (strpos($json_str, 'yoast') !== false) {
                            $source = 'Yoast SEO';
                            $source_color = '#a4286a';
                        } elseif (strpos($json_str, 'sfpf') !== false || strpos($json_str, 'SFPF') !== false) {
                            $source = 'SFPF Plugin';
                            $source_color = '#6366f1';
                        } elseif (strpos($json_str, 'WebSite') !== false && strpos($json_str, 'SearchAction') !== false) {
                            $source = 'RankMath (WebSite)';
                            $source_color = '#e91e63';
                        }
                        
                        // Get types
                        $types = [];
                        if (isset($schema['@type'])) {
                            $types[] = is_array($schema['@type']) ? implode(', ', $schema['@type']) : $schema['@type'];
                        }
                        if (isset($schema['@graph'])) {
                            foreach ($schema['@graph'] as $node) {
                                if (isset($node['@type'])) {
                                    $t = is_array($node['@type']) ? implode(', ', $node['@type']) : $node['@type'];
                                    $types[] = $t;
                                }
                            }
                        }
                        
                        $output .= '<div style="margin:8px 0 0 0;padding:10px;background:#0d1117;border-radius:4px;border-left:3px solid ' . $source_color . ';">';
                        $output .= '<div style="margin-bottom:6px;">';
                        $output .= '<span style="color:#a78bfa;font-weight:bold;">Block ' . ($i + 1) . '</span>';
                        $output .= ' <span style="color:' . $source_color . ';font-size:11px;background:#1e1e2e;padding:2px 6px;border-radius:3px;">' . $source . '</span>';
                        $output .= '</div>';
                        $output .= '<div style="color:#fbbf24;font-size:12px;margin-bottom:8px;">Types: ' . implode(', ', array_unique($types)) . '</div>';
                        
                        // Always show schema structure
                        if (isset($schema['@graph']) && is_array($schema['@graph'])) {
                            // Graph-based schema: show each node
                            foreach ($schema['@graph'] as $gi => $node) {
                                $node_type = isset($node['@type']) ? (is_array($node['@type']) ? implode(', ', $node['@type']) : $node['@type']) : 'Unknown';
                                $output .= '<div style="margin:6px 0;padding:8px;background:#161b22;border-radius:4px;border-left:2px solid #374151;">';
                                $output .= '<div style="color:#60a5fa;font-size:11px;font-weight:bold;margin-bottom:4px;">' . esc_html($node_type) . '</div>';
                                $output .= '<div style="font-size:11px;color:#9ca3af;line-height:1.6;">';
                                
                                $show_props = ['@id', 'name', 'url', 'description', 'image', 'sameAs', 
                                               'datePublished', 'dateModified', 'author', 'publisher', 
                                               'headline', 'mainEntityOfPage', 'foundingDate', 'founder',
                                               'jobTitle', 'alumniOf', 'knowsAbout', 'email', 'telephone'];
                                foreach ($show_props as $prop) {
                                    if (isset($node[$prop])) {
                                        $val = $node[$prop];
                                        if (is_array($val)) {
                                            if (isset($val['@type'])) {
                                                $val = '{' . $val['@type'] . '}';
                                            } elseif (isset($val['@id'])) {
                                                $val = $val['@id'];
                                            } elseif (isset($val[0])) {
                                                $val = is_string($val[0]) 
                                                    ? implode(', ', array_slice($val, 0, 3)) . (count($val) > 3 ? '... +' . (count($val) - 3) . ' more' : '')
                                                    : '[' . count($val) . ' items]';
                                            } else {
                                                $val = json_encode($val);
                                            }
                                        }
                                        if (is_string($val) && strlen($val) > 100) {
                                            $val = substr($val, 0, 100) . '...';
                                        }
                                        $output .= '<span style="color:#6b7280;">' . esc_html($prop) . ':</span> ' . esc_html($val) . '<br>';
                                    }
                                }
                                $output .= '</div></div>';
                            }
                        } else {
                            // Flat schema: show top-level properties
                            $output .= '<div style="margin:6px 0;padding:8px;background:#161b22;border-radius:4px;font-size:11px;color:#9ca3af;line-height:1.6;">';
                            $show_props = ['@id', '@type', 'name', 'url', 'description', 'image', 'sameAs',
                                           'datePublished', 'dateModified', 'author', 'publisher',
                                           'headline', 'mainEntityOfPage', 'foundingDate', 'founder',
                                           'jobTitle', 'alumniOf', 'knowsAbout', 'mainEntity'];
                            foreach ($show_props as $prop) {
                                if (isset($schema[$prop]) && $prop !== '@type') {
                                    $val = $schema[$prop];
                                    if (is_array($val)) {
                                        if (isset($val['@type'])) {
                                            $val = '{' . $val['@type'] . '}';
                                        } elseif (isset($val['@id'])) {
                                            $val = $val['@id'];
                                        } elseif (isset($val[0])) {
                                            $val = is_string($val[0])
                                                ? implode(', ', array_slice($val, 0, 3)) . (count($val) > 3 ? '... +' . (count($val) - 3) . ' more' : '')
                                                : '[' . count($val) . ' items]';
                                        } else {
                                            $val = json_encode($val);
                                        }
                                    }
                                    if (is_string($val) && strlen($val) > 100) {
                                        $val = substr($val, 0, 100) . '...';
                                    }
                                    $output .= '<span style="color:#6b7280;">' . esc_html($prop) . ':</span> ' . esc_html($val) . '<br>';
                                }
                            }
                            $output .= '</div>';
                        }
                        
                        if ($debug) {
                            $output .= '<details style="margin-top:8px;"><summary style="color:#60a5fa;cursor:pointer;font-size:11px;">View Full JSON</summary>';
                            $output .= '<pre style="background:#161b22;padding:10px;border-radius:4px;margin:5px 0;font-size:10px;max-height:300px;overflow:auto;white-space:pre-wrap;">';
                            $output .= esc_html(json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
                            $output .= '</pre></details>';
                        }
                        
                        $output .= '</div>';
                    } else {
                        $json_error = json_last_error_msg();
                        $output .= '<div style="margin:8px 0 0 15px;color:#f87171;font-size:12px;">';
                        $output .= '⚠️ Block ' . ($i + 1) . ': Invalid JSON - ' . esc_html($json_error);
                        $output .= '</div>';
                    }
                }
            } else {
                $output .= '<div style="color:#fbbf24;font-size:12px;">⚠️ No JSON-LD schema found on this page</div>';
                
                // Check if there's any script tags at all
                preg_match_all('/<script[^>]*>/si', $body, $script_matches);
                if ($debug && !empty($script_matches[0])) {
                    $output .= '<div style="color:#6b7280;font-size:11px;margin-top:4px;">Found ' . count($script_matches[0]) . ' total script tags (none are JSON-LD)</div>';
                }
            }
        }
        
        $output .= '</div>';
    }
    
    $output .= '</div>';
    $output .= '<div style="color:#6b7280;font-size:11px;margin-top:10px;padding-top:10px;border-top:1px solid #374151;">💡 Tip: Enable debug mode for detailed JSON output</div>';
    
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
    
    // Check if we already have a page assigned for this key
    $existing_assigned = get_option('sfpf_page_' . $page_key, 0);
    if ($existing_assigned && get_post($existing_assigned)) {
        wp_send_json_success(['page_id' => $existing_assigned, 'existing' => true, 'message' => 'Page already assigned']);
        return;
    }
    
    // Get parent ID if specified
    $parent_id = 0;
    if ($parent_key) {
        $parent_id = get_option('sfpf_page_' . $parent_key, 0);
    }
    
    // Check if page already exists with this exact slug and parent
    $existing_args = [
        'name' => $slug,
        'post_type' => 'page',
        'post_status' => 'any',
        'posts_per_page' => 1,
    ];
    if ($parent_id) {
        $existing_args['post_parent'] = $parent_id;
    }
    $existing_pages = get_posts($existing_args);
    
    if (!empty($existing_pages)) {
        $existing = $existing_pages[0];
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
add_action('wp_ajax_sfpf_import_elementor_templates', __NAMESPACE__ . '\\ajax_import_elementor_templates');

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
 * Delete Elementor template
 */
function ajax_delete_elementor_template() {
    verify_ajax_nonce();
    
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
add_action('wp_ajax_sfpf_delete_elementor_template', __NAMESPACE__ . '\\ajax_delete_elementor_template');

/**
 * Run debug action
 */
function ajax_run_debug() {
    verify_ajax_nonce();
    
    $action = sanitize_key($_POST['debug_action'] ?? '');
    $output = '';
    
    switch ($action) {
        case 'check_homepage_schema':
            $output = debug_homepage_schema();
            break;
        case 'check_founder_data':
            $output = debug_founder_data();
            break;
        case 'check_injection_hook':
            $output = debug_injection_hook();
            break;
        case 'test_schema_build':
            $output = debug_test_schema_build();
            break;
        case 'check_elementor_templates':
            $output = debug_elementor_templates();
            break;
        case 'check_loop_items':
            $output = debug_loop_items();
            break;
        case 'check_template_meta':
            $output = debug_template_meta();
            break;
        case 'repair_elementor_templates':
            $output = debug_repair_elementor_templates();
            break;
        case 'check_professions':
            $output = debug_professions();
            break;
        case 'check_user_meta':
            $output = debug_user_meta();
            break;
        case 'list_acf_fields':
            $output = debug_acf_fields();
            break;
        default:
            $output = "Unknown debug action: {$action}";
    }
    
    wp_send_json_success(['output' => $output]);
}
add_action('wp_ajax_sfpf_run_debug', __NAMESPACE__ . '\\ajax_run_debug');

/**
 * Debug: Repair Elementor templates by re-importing data
 */
function debug_repair_elementor_templates() {
    $output = "=== REPAIR ELEMENTOR TEMPLATES ===\n\n";
    
    // Template definitions
    $templates_to_repair = [
        'hexa-book-default-loop' => [
            'file' => 'hexa-book-default-loop.json',
            'post_type' => 'book',
        ],
        'hexa-organization-default-loop' => [
            'file' => 'hexa-organization-default-loop.json',
            'post_type' => 'organization',
        ],
        'hexa-testimonial-default-loop' => [
            'file' => 'hexa-testimonial-default-loop.json',
            'post_type' => 'testimonial',
        ],
    ];
    
    // Find existing templates that need repair
    $templates = get_posts([
        'post_type' => 'elementor_library',
        'posts_per_page' => -1,
        'post_status' => 'any',
        'meta_key' => '_elementor_template_type',
        'meta_value' => 'loop-item',
    ]);
    
    $output .= "Found " . count($templates) . " loop-item templates\n\n";
    
    $repaired = 0;
    foreach ($templates as $t) {
        $output .= "Processing: {$t->post_title} (ID: {$t->ID})\n";
        
        // Check if _elementor_data is empty
        $current_data = get_post_meta($t->ID, '_elementor_data', true);
        
        if (!empty($current_data)) {
            $decoded = json_decode($current_data, true);
            if (!empty($decoded)) {
                $output .= "  ✅ Already has valid data (" . count($decoded) . " elements)\n\n";
                continue;
            }
        }
        
        $output .= "  ⚠️ Empty or invalid _elementor_data, attempting repair...\n";
        
        // Try to find matching JSON file
        $json_dir = SFPF_PLUGIN_DIR . 'assets/elementor-templates/';
        $matched_file = null;
        
        foreach ($templates_to_repair as $key => $info) {
            if (stripos($t->post_title, 'book') !== false && stripos($info['file'], 'book') !== false) {
                $matched_file = $json_dir . $info['file'];
                break;
            } elseif (stripos($t->post_title, 'organization') !== false && stripos($info['file'], 'organization') !== false) {
                $matched_file = $json_dir . $info['file'];
                break;
            } elseif (stripos($t->post_title, 'testimonial') !== false && stripos($info['file'], 'testimonial') !== false) {
                $matched_file = $json_dir . $info['file'];
                break;
            }
        }
        
        if ($matched_file && file_exists($matched_file)) {
            $json_content = file_get_contents($matched_file);
            $template_data = json_decode($json_content, true);
            
            if ($template_data && isset($template_data['content'])) {
                // Update the _elementor_data
                update_post_meta($t->ID, '_elementor_data', wp_json_encode($template_data['content']));
                
                // Also ensure other meta is set
                update_post_meta($t->ID, '_elementor_template_type', 'loop-item');
                update_post_meta($t->ID, '_elementor_edit_mode', 'builder');
                update_post_meta($t->ID, '_elementor_version', defined('ELEMENTOR_VERSION') ? ELEMENTOR_VERSION : '3.25.0');
                
                // Set taxonomy
                wp_set_object_terms($t->ID, 'loop-item', 'elementor_library_type');
                
                $output .= "  ✅ Repaired! Imported " . count($template_data['content']) . " elements from " . basename($matched_file) . "\n";
                $repaired++;
            } else {
                $output .= "  ❌ Could not parse JSON file\n";
            }
        } else {
            $output .= "  ❌ No matching JSON file found\n";
        }
        
        $output .= "\n";
    }
    
    $output .= "=== SUMMARY ===\n";
    $output .= "Repaired: {$repaired} templates\n";
    $output .= "\nNote: After repair, you may need to:\n";
    $output .= "1. Edit the template in Elementor\n";
    $output .= "2. Save/Update it once to regenerate CSS\n";
    
    return $output;
}

/**
 * Debug: Check homepage schema
 */
function debug_homepage_schema() {
    $output = "=== HOMEPAGE SCHEMA DEBUG ===\n\n";
    
    // Check front page settings
    $show_on_front = get_option('show_on_front');
    $page_on_front = get_option('page_on_front');
    $output .= "show_on_front: {$show_on_front}\n";
    $output .= "page_on_front: {$page_on_front}\n\n";
    
    // Check schema type option
    $schema_type = get_option('sfpf_homepage_schema_type', 'none');
    $output .= "sfpf_homepage_schema_type: {$schema_type}\n\n";
    
    if ($show_on_front !== 'page') {
        $output .= "❌ PROBLEM: WordPress is not set to use a static homepage.\n";
        $output .= "   Go to Settings > Reading and set 'Your homepage displays' to 'A static page'\n";
        return $output;
    }
    
    if (!$page_on_front) {
        $output .= "❌ PROBLEM: No homepage is set.\n";
        return $output;
    }
    
    // Check if schema is stored
    $schema = get_field('schema_markup', $page_on_front);
    if ($schema) {
        $output .= "✅ Schema is stored in ACF field 'schema_markup'\n";
        $output .= "Schema length: " . strlen($schema) . " bytes\n\n";
        
        // Validate JSON
        $decoded = json_decode($schema);
        if (json_last_error() === JSON_ERROR_NONE) {
            $output .= "✅ Schema is valid JSON\n\n";
            $output .= "Schema preview:\n" . substr($schema, 0, 500) . "...\n";
        } else {
            $output .= "❌ Schema is invalid JSON: " . json_last_error_msg() . "\n";
        }
    } else {
        $output .= "❌ No schema stored in ACF field\n";
        $output .= "   Click 'Reprocess Homepage Schema' button to generate\n";
    }
    
    return $output;
}

/**
 * Debug: Check founder data
 */
function debug_founder_data() {
    $output = "=== FOUNDER DATA DEBUG ===\n\n";
    
    $founder_id = get_founder_user_id();
    $output .= "Founder User ID: " . ($founder_id ?: 'NOT SET') . "\n\n";
    
    if (!$founder_id) {
        $output .= "❌ No founder configured.\n";
        $output .= "   Go to Website Settings and set the Founder user.\n";
        return $output;
    }
    
    $user = get_userdata($founder_id);
    if (!$user) {
        $output .= "❌ User ID {$founder_id} not found!\n";
        return $output;
    }
    
    $output .= "User Data:\n";
    $output .= "  - display_name: {$user->display_name}\n";
    $output .= "  - user_email: {$user->user_email}\n";
    $output .= "  - first_name: " . get_user_meta($founder_id, 'first_name', true) . "\n";
    $output .= "  - last_name: " . get_user_meta($founder_id, 'last_name', true) . "\n\n";
    
    // Check entity type
    $entity_type = get_field('entity_type', 'user_' . $founder_id);
    $output .= "Entity Type: " . ($entity_type ?: 'NOT SET') . "\n";
    
    // Check title
    $title = get_field('title', 'user_' . $founder_id);
    $output .= "Title: " . ($title ?: 'NOT SET') . "\n";
    
    // Check biography
    $bio = get_field('biography', 'user_' . $founder_id);
    $output .= "Biography: " . ($bio ? strlen($bio) . ' chars' : 'NOT SET') . "\n";
    
    return $output;
}

/**
 * Debug: Check injection hook
 */
function debug_injection_hook() {
    $output = "=== SCHEMA INJECTION HOOK DEBUG ===\n\n";
    
    // Check if wp_head has our hook
    global $wp_filter;
    
    $found = false;
    if (isset($wp_filter['wp_head'])) {
        foreach ($wp_filter['wp_head']->callbacks as $priority => $callbacks) {
            foreach ($callbacks as $name => $callback) {
                if (strpos($name, 'inject_schema_markup') !== false) {
                    $output .= "✅ Hook found at priority {$priority}\n";
                    $output .= "   Function: {$name}\n";
                    $found = true;
                }
            }
        }
    }
    
    if (!$found) {
        $output .= "❌ inject_schema_markup hook NOT found in wp_head!\n";
        $output .= "   This means schema will not be injected.\n\n";
        $output .= "   Possible causes:\n";
        $output .= "   - Schema injection not enabled\n";
        $output .= "   - Plugin files not loading correctly\n";
    }
    
    // Check if function exists
    $output .= "\nFunction exists:\n";
    $output .= "  - enable_schema_injection: " . (function_exists(__NAMESPACE__ . '\\enable_schema_injection') ? '✅ Yes' : '❌ No') . "\n";
    $output .= "  - inject_schema_markup: " . (function_exists(__NAMESPACE__ . '\\inject_schema_markup') ? '✅ Yes' : '❌ No') . "\n";
    $output .= "  - get_post_schema: " . (function_exists(__NAMESPACE__ . '\\get_post_schema') ? '✅ Yes' : '❌ No') . "\n";
    
    return $output;
}

/**
 * Debug: Test schema build
 */
function debug_test_schema_build() {
    $output = "=== TEST SCHEMA BUILD ===\n\n";
    
    $front_page_id = get_front_page_id();
    if (!$front_page_id) {
        $output .= "❌ No front page set\n";
        return $output;
    }
    
    $schema_type = get_option('sfpf_homepage_schema_type', 'none');
    $output .= "Schema type setting: {$schema_type}\n\n";
    
    if ($schema_type === 'none') {
        $output .= "Schema injection is disabled.\n";
        return $output;
    }
    
    // Try to build schema
    if (function_exists(__NAMESPACE__ . '\\build_homepage_schema')) {
        $schema = build_homepage_schema($front_page_id, $schema_type);
        if ($schema) {
            $json = json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            $output .= "✅ Schema built successfully!\n\n";
            $output .= "Schema:\n{$json}\n";
        } else {
            $output .= "❌ build_homepage_schema returned empty\n";
        }
    } else {
        $output .= "❌ build_homepage_schema function not found\n";
    }
    
    return $output;
}

/**
 * Debug: Check Elementor templates
 */
function debug_elementor_templates() {
    $output = "=== ELEMENTOR TEMPLATES DEBUG ===\n\n";
    
    $templates = get_posts([
        'post_type' => 'elementor_library',
        'posts_per_page' => -1,
        'post_status' => 'any',
    ]);
    
    $output .= "Total Elementor templates: " . count($templates) . "\n\n";
    
    foreach ($templates as $t) {
        $type = get_post_meta($t->ID, '_elementor_template_type', true);
        $output .= "ID: {$t->ID} | Type: {$type} | Title: {$t->post_title}\n";
    }
    
    return $output;
}

/**
 * Debug: Check loop items
 */
function debug_loop_items() {
    $output = "=== ELEMENTOR LOOP ITEMS DEBUG ===\n\n";
    
    $loop_items = get_posts([
        'post_type' => 'elementor_library',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'tax_query' => [
            [
                'taxonomy' => 'elementor_library_type',
                'field' => 'slug',
                'terms' => 'loop-item',
            ],
        ],
    ]);
    
    $output .= "Loop items found: " . count($loop_items) . "\n\n";
    
    if (empty($loop_items)) {
        // Try alternative query
        $output .= "Trying alternative query (by meta)...\n";
        $loop_items = get_posts([
            'post_type' => 'elementor_library',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'meta_query' => [
                [
                    'key' => '_elementor_template_type',
                    'value' => 'loop-item',
                ],
            ],
        ]);
        $output .= "Found by meta: " . count($loop_items) . "\n\n";
    }
    
    foreach ($loop_items as $item) {
        $output .= "ID: {$item->ID} | {$item->post_title}\n";
        $data = get_post_meta($item->ID, '_elementor_data', true);
        $output .= "  - Has _elementor_data: " . ($data ? 'Yes (' . strlen($data) . ' bytes)' : 'NO') . "\n";
    }
    
    return $output;
}

/**
 * Debug: Check template metadata
 */
function debug_template_meta() {
    $output = "=== ELEMENTOR TEMPLATE METADATA DEBUG ===\n\n";
    
    // Get templates imported by our plugin
    $templates = get_posts([
        'post_type' => 'elementor_library',
        'posts_per_page' => 10,
        'post_status' => 'any',
        'meta_key' => '_elementor_template_type',
        'meta_value' => 'loop-item',
    ]);
    
    if (empty($templates)) {
        $output .= "No loop-item templates found.\n";
        return $output;
    }
    
    foreach ($templates as $t) {
        $output .= "=== Template ID: {$t->ID} ===\n";
        $output .= "Title: {$t->post_title}\n";
        $output .= "Status: {$t->post_status}\n\n";
        
        // Get all meta
        $meta = get_post_meta($t->ID);
        foreach ($meta as $key => $values) {
            if (strpos($key, '_elementor') !== false || strpos($key, 'sfpf') !== false) {
                $value = $values[0];
                if (strlen($value) > 200) {
                    $value = substr($value, 0, 200) . '...';
                }
                $output .= "  {$key}: {$value}\n";
            }
        }
        
        // Check _elementor_data specifically
        $data = get_post_meta($t->ID, '_elementor_data', true);
        $output .= "\n_elementor_data analysis:\n";
        if (empty($data)) {
            $output .= "  ❌ EMPTY - This is why the template appears blank!\n";
        } else {
            $output .= "  Length: " . strlen($data) . " bytes\n";
            $decoded = json_decode($data, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $output .= "  ❌ Invalid JSON: " . json_last_error_msg() . "\n";
            } else {
                $output .= "  ✅ Valid JSON\n";
                $output .= "  Elements count: " . (is_array($decoded) ? count($decoded) : 'N/A') . "\n";
                // Show first element structure
                if (is_array($decoded) && !empty($decoded[0])) {
                    $output .= "  First element type: " . ($decoded[0]['elType'] ?? 'unknown') . "\n";
                }
            }
        }
        
        // Check taxonomy
        $terms = wp_get_object_terms($t->ID, 'elementor_library_type', ['fields' => 'names']);
        $output .= "\nTaxonomy terms: " . (is_array($terms) ? implode(', ', $terms) : 'none') . "\n";
        
        $output .= "\n";
    }
    
    // Add JSON file analysis
    $output .= "\n=== SOURCE JSON FILES ANALYSIS ===\n\n";
    $json_dir = SFPF_PLUGIN_DIR . 'assets/elementor-templates/';
    
    if (is_dir($json_dir)) {
        $files = glob($json_dir . '*.json');
        foreach ($files as $file) {
            $filename = basename($file);
            $content = file_get_contents($file);
            $data = json_decode($content, true);
            
            $output .= "{$filename}:\n";
            $output .= "  Size: " . strlen($content) . " bytes\n";
            $output .= "  Has 'content': " . (isset($data['content']) ? 'Yes (' . count($data['content']) . ' elements)' : 'No') . "\n";
            $output .= "\n";
        }
    }
    
    return $output;
}

/**
 * Debug: Check professions field
 */
function debug_professions() {
    $output = "=== PROFESSIONS FIELD DEBUG ===\n\n";
    
    $founder_id = get_founder_user_id();
    if (!$founder_id) {
        $output .= "❌ No founder user ID\n";
        return $output;
    }
    
    $output .= "Founder User ID: {$founder_id}\n\n";
    
    // Get professions using get_field
    $profs = get_field('professions', 'user_' . $founder_id);
    
    $output .= "get_field('professions', 'user_{$founder_id}'):\n";
    $output .= "Type: " . gettype($profs) . "\n";
    
    if ($profs === null || $profs === false) {
        $output .= "Value: " . var_export($profs, true) . "\n\n";
        $output .= "❌ Field returned null/false - field may not exist\n";
    } elseif (empty($profs)) {
        $output .= "Value: empty array/string\n";
        $output .= "Field exists but is empty.\n";
    } else {
        $output .= "Count: " . (is_array($profs) ? count($profs) : 'N/A') . "\n\n";
        $output .= "Raw data:\n" . print_r($profs, true) . "\n";
    }
    
    // Also check direct user meta
    $output .= "\n=== Direct User Meta Check ===\n";
    $meta_value = get_user_meta($founder_id, 'professions', true);
    $output .= "get_user_meta() result:\n";
    $output .= "Type: " . gettype($meta_value) . "\n";
    if ($meta_value) {
        $output .= "Value: " . print_r($meta_value, true) . "\n";
    } else {
        $output .= "Value: empty\n";
    }
    
    return $output;
}

/**
 * Debug: Check user meta
 */
function debug_user_meta() {
    $output = "=== USER META DEBUG ===\n\n";
    
    $founder_id = get_founder_user_id();
    if (!$founder_id) {
        $output .= "❌ No founder user ID\n";
        return $output;
    }
    
    $output .= "All user meta for user {$founder_id}:\n\n";
    
    $all_meta = get_user_meta($founder_id);
    foreach ($all_meta as $key => $values) {
        // Skip internal WP fields
        if (in_array($key, ['session_tokens', 'wp_capabilities', 'wp_user_level', 'rich_editing', 'syntax_highlighting'])) {
            continue;
        }
        
        $value = $values[0];
        if (is_serialized($value)) {
            $value = '[serialized] ' . substr($value, 0, 100);
        } elseif (strlen($value) > 100) {
            $value = substr($value, 0, 100) . '...';
        }
        $output .= "{$key}: {$value}\n";
    }
    
    return $output;
}

/**
 * Debug: List ACF fields for user
 */
function debug_acf_fields() {
    $output = "=== ACF FIELDS FOR USER ===\n\n";
    
    $founder_id = get_founder_user_id();
    if (!$founder_id) {
        $output .= "❌ No founder user ID\n";
        return $output;
    }
    
    $output .= "Checking ACF fields for user_{$founder_id}:\n\n";
    
    // List of expected fields
    $fields = [
        'entity_type', 'title', 'biography', 'biography_short',
        'professions', 'education', 'job_title', 'sameas'
    ];
    
    foreach ($fields as $field) {
        $value = get_field($field, 'user_' . $founder_id);
        $type = gettype($value);
        
        if ($value === null || $value === false) {
            $output .= "❌ {$field}: NOT SET\n";
        } elseif (is_array($value)) {
            $output .= "✅ {$field}: array with " . count($value) . " items\n";
        } elseif (is_string($value)) {
            $output .= "✅ {$field}: string (" . strlen($value) . " chars)\n";
        } else {
            $output .= "✅ {$field}: {$type}\n";
        }
    }
    
    return $output;
}

/**
 * Run custom debug script
 */
function ajax_run_custom_debug() {
    verify_ajax_nonce();
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized');
    }
    
    $script = wp_unslash($_POST['script'] ?? '');
    
    if (empty($script)) {
        wp_send_json_error('No script provided');
    }
    
    // Capture output
    ob_start();
    
    try {
        // Execute in a function scope to avoid variable conflicts
        $execute = function() use ($script) {
            eval($script);
        };
        $execute();
    } catch (\Throwable $e) {
        echo "Error: " . $e->getMessage() . "\n";
        echo "Line: " . $e->getLine() . "\n";
    }
    
    $output = ob_get_clean();
    
    wp_send_json_success(['output' => $output ?: 'Script executed with no output']);
}
add_action('wp_ajax_sfpf_run_custom_debug', __NAMESPACE__ . '\\ajax_run_custom_debug');

/**
 * Export debug report
 */
function ajax_export_debug_report() {
    verify_ajax_nonce();
    
    $report = "=== SFPF Person Profile Debug Report ===\n";
    $report .= "Generated: " . current_time('Y-m-d H:i:s') . "\n\n";
    
    $report .= debug_homepage_schema() . "\n\n";
    $report .= debug_founder_data() . "\n\n";
    $report .= debug_injection_hook() . "\n\n";
    $report .= debug_professions() . "\n\n";
    $report .= debug_acf_fields() . "\n\n";
    
    wp_send_json_success(['report' => $report]);
}
add_action('wp_ajax_sfpf_export_debug_report', __NAMESPACE__ . '\\ajax_export_debug_report');
