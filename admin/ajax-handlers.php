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
 * Mark a page as SFPF-managed so destructive actions only touch plugin-owned pages.
 *
 * @param int $page_id Page ID.
 * @param string $page_key Critical page key.
 * @return void
 */
function mark_sfpf_managed_page($page_id, $page_key) {
    update_post_meta($page_id, '_sfpf_managed_page', 1);
    update_post_meta($page_id, '_sfpf_page_key', $page_key);
}

/**
 * Check whether a page was created and owned by SFPF.
 *
 * @param int $page_id Page ID.
 * @param string $page_key Optional expected page key.
 * @return bool
 */
function is_sfpf_managed_page($page_id, $page_key = '') {
    if ((int) $page_id <= 0 || get_post_type($page_id) !== 'page') {
        return false;
    }

    if (!get_post_meta($page_id, '_sfpf_managed_page', true)) {
        return false;
    }

    if ($page_key === '') {
        return true;
    }

    $stored_key = get_post_meta($page_id, '_sfpf_page_key', true);
    return empty($stored_key) || $stored_key === $page_key;
}

/**
 * Build a standard page payload for AJAX responses.
 *
 * @param int $page_id Page ID.
 * @param bool $existing Whether the page already existed.
 * @param string $message Optional message.
 * @return array
 */
function get_page_ajax_payload($page_id, $existing = false, $message = '') {
    return [
        'page_id' => (int) $page_id,
        'existing' => (bool) $existing,
        'permalink' => get_permalink($page_id),
        'edit_url' => get_edit_post_link($page_id, 'raw'),
        'title' => get_the_title($page_id),
        'message' => $message,
    ];
}

/**
 * Extract FAQPage nodes from a decoded schema block.
 *
 * @param array $schema Schema block.
 * @return array
 */
function sfpf_get_faq_nodes($schema) {
    if (!is_array($schema)) {
        return [];
    }

    $nodes = [];
    $top_level_type = $schema['@type'] ?? null;
    $top_level_types = is_array($top_level_type) ? $top_level_type : [$top_level_type];
    if (in_array('FAQPage', array_filter($top_level_types), true)) {
        $nodes[] = $schema;
    }

    if (!empty($schema['@graph']) && is_array($schema['@graph'])) {
        foreach ($schema['@graph'] as $node) {
            if (!is_array($node)) {
                continue;
            }

            $node_type = $node['@type'] ?? null;
            $node_types = is_array($node_type) ? $node_type : [$node_type];
            if (in_array('FAQPage', array_filter($node_types), true)) {
                $nodes[] = $node;
            }
        }
    }

    return $nodes;
}

/**
 * Find invalid FAQ schema details inside decoded schema blocks.
 *
 * @param array $blocks Schema blocks.
 * @return array
 */
function sfpf_get_faq_schema_issues($blocks) {
    $issues = [];

    foreach ($blocks as $block) {
        foreach (sfpf_get_faq_nodes($block) as $faq_node) {
            $questions = $faq_node['mainEntity'] ?? [];

            if (empty($questions)) {
                $issues[] = 'FAQPage is missing mainEntity';
                continue;
            }

            if (isset($questions['@type']) || isset($questions['name'])) {
                $questions = [$questions];
            }

            foreach ($questions as $index => $question) {
                if (!is_array($question)) {
                    $issues[] = 'FAQ question ' . ($index + 1) . ' is malformed';
                    continue;
                }

                $question_name = trim(wp_strip_all_tags((string) ($question['name'] ?? '')));
                if ($question_name === '' || preg_match('/^Item #\d+$/i', $question_name)) {
                    $issues[] = 'FAQ question ' . ($index + 1) . ' uses a placeholder or empty name';
                }

                $accepted_answer = $question['acceptedAnswer'] ?? [];
                $answer_text = is_array($accepted_answer) ? ($accepted_answer['text'] ?? '') : '';
                $answer_text = trim(wp_strip_all_tags((string) $answer_text));

                if ($answer_text === '') {
                    $issues[] = 'FAQ question ' . ($index + 1) . ' has an empty acceptedAnswer';
                }
            }
        }
    }

    return array_values(array_unique($issues));
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
        
        case 'biography':
            $bio_page_id = get_option('sfpf_page_biography');
            if ($bio_page_id && get_post_status($bio_page_id) === 'publish') {
                $urls[] = ['url' => get_permalink($bio_page_id), 'title' => 'Biography Page'];
            } else {
                $output = '<span style="color:#fbbf24;">⚠️ No biography page found. Set one in the Critical Pages tab.</span>';
                wp_send_json_success(['output' => $output]);
                return;
            }
            break;
            
        case 'full_site':
            // Run full site checklist scan
            $checklist_output = sfpf_run_full_site_checklist($debug);
            wp_send_json_success(['output' => $checklist_output]);
            return;
            
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
    
    // ── Schema Expectations ──
    $expected = [];
    if ($type === 'homepage') {
        $hp_type = get_option('sfpf_homepage_schema_type', 'person');
        $expected_map = ['person' => 'Person', 'profile_page_only' => 'ProfilePage + Person', 'profile_page' => 'ProfilePage + Person', 'none' => 'None (disabled)'];
        $expected[] = '<strong>SFPF Expected:</strong> ' . ($expected_map[$hp_type] ?? $hp_type);
    } elseif ($type === 'biography') {
        $bio_type = get_option('sfpf_biography_schema_type', 'profile_page_only');
        $expected_map = ['person' => 'Person', 'profile_page_only' => 'ProfilePage + Person', 'profile_page' => 'ProfilePage + Person', 'none' => 'None (disabled)'];
        $expected[] = '<strong>SFPF Expected:</strong> ' . ($expected_map[$bio_type] ?? $bio_type);
    } elseif ($type === 'books') {
        $expected[] = '<strong>SFPF Expected:</strong> Book';
    } elseif ($type === 'organizations') {
        $expected[] = '<strong>SFPF Expected:</strong> Organization';
    }
    $rm_active = defined('RANK_MATH_VERSION');
    if ($rm_active) {
        $rm_disabled = false;
        if ($type === 'homepage') $rm_disabled = get_option('sfpf_rankmath_disable_homepage', false);
        elseif ($type === 'biography') $rm_disabled = get_option('sfpf_rankmath_disable_biography', false);
        elseif ($type === 'books') $rm_disabled = get_option('sfpf_rankmath_disable_books', false);
        elseif ($type === 'organizations') $rm_disabled = get_option('sfpf_rankmath_disable_organizations', false);
        $expected[] = '<strong>RankMath:</strong> ' . ($rm_disabled ? '<span style="color:#f59e0b;">Disabled for this type</span>' : '<span style="color:#e91e63;">Active — may inject its own schema</span>');
    }
    if (!empty($expected)) {
        $output .= '<div style="background:#1e293b;border:1px solid #334155;border-radius:6px;padding:8px 12px;margin-bottom:12px;font-size:11px;color:#94a3b8;line-height:1.8;">';
        $output .= '🎯 ' . implode('<br>', $expected);
        $output .= '</div>';
    }
    
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
            
            // Find all JSON-LD scripts with surrounding context for source detection
            preg_match_all('/(.{0,200})<script[^>]*type=["\']application\/ld\+json["\'][^>]*>(.*?)<\/script>/si', $body, $matches, PREG_SET_ORDER);
            
            if (!empty($matches)) {
                $output .= '<div style="color:#10b981;font-size:12px;margin-bottom:8px;">✅ Found ' . count($matches) . ' schema block(s)</div>';
                
                // Track types per source for conflict detection
                $types_by_source = [];
                $all_types_flat = [];
                
                foreach ($matches as $i => $match) {
                    $context_before = $match[1];
                    $json_str = $match[2];
                    $schema = json_decode(trim($json_str), true);
                    
                    if ($schema) {
                        // Detect source using HTML comment markers + content heuristics
                        $source = 'Unknown';
                        $source_color = '#9ca3af';
                        $source_icon = '❓';
                        
                        // SFPF wraps output in <!-- SFPF Person Website Schema -->
                        if (strpos($context_before, 'SFPF') !== false || strpos($context_before, 'sfpf') !== false) {
                            $source = 'SFPF Plugin';
                            $source_color = '#6366f1';
                            $source_icon = '🟣';
                        }
                        // RankMath detection
                        elseif (strpos($json_str, 'rank-math') !== false || strpos($json_str, 'rankmath') !== false
                            || strpos($context_before, 'rank-math') !== false || strpos($context_before, 'rank_math') !== false) {
                            $source = 'RankMath';
                            $source_color = '#e91e63';
                            $source_icon = '🔴';
                        }
                        // RankMath often outputs WebSite+SearchAction, BreadcrumbList, WebPage+Article combos
                        elseif (isset($schema['@graph']) && is_array($schema['@graph'])) {
                            $graph_types = array_map(function($n) {
                                return is_array($n['@type'] ?? null) ? implode(',', $n['@type']) : ($n['@type'] ?? '');
                            }, $schema['@graph']);
                            $gt = implode(',', $graph_types);
                            if (strpos($gt, 'WebSite') !== false && strpos($gt, 'BreadcrumbList') !== false) {
                                $source = 'RankMath';
                                $source_color = '#e91e63';
                                $source_icon = '🔴';
                            } elseif (strpos($gt, 'WebPage') !== false && strpos($gt, 'Article') !== false) {
                                $source = 'RankMath';
                                $source_color = '#e91e63';
                                $source_icon = '🔴';
                            }
                        }
                        // Yoast
                        elseif (strpos($json_str, 'yoast') !== false) {
                            $source = 'Yoast SEO';
                            $source_color = '#a4286a';
                            $source_icon = '🟤';
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
                        
                        // Track for conflict detection
                        foreach ($types as $t) {
                            $types_by_source[$source][] = $t;
                            $all_types_flat[] = $t;
                        }
                        
                        $output .= '<div style="margin:8px 0 0 0;padding:10px;background:#0d1117;border-radius:4px;border-left:3px solid ' . $source_color . ';">';
                        $output .= '<div style="margin-bottom:6px;display:flex;align-items:center;gap:8px;flex-wrap:wrap;">';
                        $output .= '<span style="color:#a78bfa;font-weight:bold;">Block ' . ($i + 1) . '</span>';
                        $output .= ' <span style="color:' . $source_color . ';font-size:11px;background:#1e1e2e;padding:2px 6px;border-radius:3px;">' . $source_icon . ' ' . $source . '</span>';
                        // Add RankMath edit link for RankMath blocks
                        if (stripos($source, 'RankMath') !== false) {
                            $rm_schema_url = admin_url('admin.php?page=rank-math-options-titles');
                            $output .= '<a href="' . esc_url($rm_schema_url) . '" target="_blank" style="color:#60a5fa;font-size:10px;background:#1e293b;padding:2px 8px;border-radius:3px;text-decoration:none;margin-left:auto;">⚙️ Edit in RankMath →</a>';
                        }
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
                        
                        // Always show full JSON structure
                        $output .= '<details style="margin-top:8px;"><summary style="color:#60a5fa;cursor:pointer;font-size:11px;">View Full JSON</summary>';
                        $output .= '<pre style="background:#161b22;padding:10px;border-radius:4px;margin:5px 0;font-size:10px;max-height:300px;overflow:auto;white-space:pre-wrap;">';
                        $output .= esc_html(json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
                        $output .= '</pre></details>';

                        $faq_issues = sfpf_get_faq_schema_issues([$schema]);
                        if (!empty($faq_issues)) {
                            $output .= '<div style="margin-top:8px;padding:8px;background:#451a1a;border:1px solid #dc2626;border-radius:6px;color:#fca5a5;font-size:11px;line-height:1.6;">';
                            $output .= '⚠️ FAQ schema issues: ' . esc_html(implode('; ', $faq_issues));
                            $output .= '</div>';
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
            
            // ── Conflict Detection ──
            if (!empty($all_types_flat)) {
                $type_counts = array_count_values($all_types_flat);
                $conflicts = [];
                
                // Check for duplicate Person or ProfilePage objects
                foreach (['Person', 'ProfilePage', 'Organization', 'WebSite'] as $check_type) {
                    if (($type_counts[$check_type] ?? 0) > 1) {
                        $conflict_sources = [];
                        foreach ($types_by_source as $src => $src_types) {
                            if (in_array($check_type, $src_types)) {
                                $conflict_sources[] = $src;
                            }
                        }
                        $conflicts[] = [
                            'type' => $check_type,
                            'count' => $type_counts[$check_type],
                            'sources' => $conflict_sources,
                        ];
                    }
                }
                
                // Check if both Person AND ProfilePage exist on same page (not necessarily a conflict, but flag it)
                $has_person = in_array('Person', $all_types_flat);
                $has_profile = in_array('ProfilePage', $all_types_flat);
                
                if (!empty($conflicts)) {
                    $output .= '<div style="margin-top:10px;padding:10px;background:#451a1a;border:1px solid #dc2626;border-radius:6px;">';
                    $output .= '<div style="color:#f87171;font-weight:bold;font-size:13px;margin-bottom:6px;">⚠️ Schema Conflicts Detected</div>';
                    foreach ($conflicts as $c) {
                        $output .= '<div style="color:#fca5a5;font-size:12px;margin-bottom:4px;">';
                        $output .= '• <strong>' . $c['count'] . 'x ' . esc_html($c['type']) . '</strong> objects found';
                        if (count($c['sources']) > 1) {
                            $output .= ' — coming from <strong>' . esc_html(implode(' + ', $c['sources'])) . '</strong>';
                        }
                        $output .= '</div>';
                    }
                    
                    // Check if RankMath is a source in any conflict
                    $rm_involved = false;
                    foreach ($conflicts as $c) {
                        foreach ($c['sources'] as $src) {
                            if (stripos($src, 'RankMath') !== false) {
                                $rm_involved = true;
                                break 2;
                            }
                        }
                    }
                    
                    if ($rm_involved) {
                        $rm_schema_url = admin_url('admin.php?page=rank-math-options-titles');
                        $output .= '<div style="margin-top:8px;display:flex;gap:8px;">';
                        $output .= '<a href="' . esc_url($rm_schema_url) . '" target="_blank" style="color:#60a5fa;font-size:11px;background:#1e293b;padding:4px 10px;border-radius:4px;text-decoration:none;">🔧 RankMath Schema Settings →</a>';
                        $output .= '<a href="' . esc_url(admin_url('admin.php?page=rank-math-options-general')) . '" target="_blank" style="color:#60a5fa;font-size:11px;background:#1e293b;padding:4px 10px;border-radius:4px;text-decoration:none;">⚙️ RankMath General Settings →</a>';
                        $output .= '</div>';
                        $output .= '<div style="color:#94a3b8;font-size:11px;margin-top:6px;">💡 To fix: Disable RankMath schema for this page type in the Schema tab, or disable it in RankMath\'s settings above.</div>';
                    }
                    $output .= '</div>';
                } elseif ($has_person && $has_profile && count(array_keys($types_by_source)) > 1) {
                    // Multiple sources but no type duplicates — informational
                    $output .= '<div style="margin-top:10px;padding:8px 10px;background:#1e293b;border:1px solid #334155;border-radius:6px;color:#94a3b8;font-size:11px;">';
                    $output .= 'ℹ️ Multiple schema sources detected (' . esc_html(implode(', ', array_keys($types_by_source))) . '). No conflicts found.';
                    $output .= '</div>';
                }
            }
        }
        
        $output .= '</div>';
    }
    
    $output .= '</div>';
    $output .= '<div style="color:#6b7280;font-size:11px;margin-top:10px;padding-top:10px;border-top:1px solid #374151;">💡 Tip: Enable debug mode for HTTP details and additional info</div>';
    
    wp_send_json_success(['output' => $output]);
}
add_action('wp_ajax_sfpf_detect_schema', __NAMESPACE__ . '\\ajax_detect_schema');

/**
 * Full site checklist scan
 * Scans all schema-bearing pages and produces a pass/fail checklist
 */
function sfpf_run_full_site_checklist($debug = false) {
    $scan_time = current_time('Y-m-d H:i:s');
    $checks = [];
    $pass_count = 0;
    $fail_count = 0;
    $warn_count = 0;
    
    // Helper: fetch page and find schema types
    $fetch_schemas = function($url) {
        $cache_bust = 'sfpf_nocache=' . time() . '_' . wp_rand(1000, 9999);
        $fetch_url = add_query_arg($cache_bust, '', $url);
        $start = microtime(true);
        $response = wp_remote_get($fetch_url, [
            'timeout' => 15,
            'sslverify' => false,
            'headers' => ['Cache-Control' => 'no-cache, no-store, must-revalidate'],
        ]);
        $time_ms = round((microtime(true) - $start) * 1000);
        
        if (is_wp_error($response)) {
            return ['error' => $response->get_error_message(), 'time_ms' => $time_ms, 'types' => [], 'sources' => [], 'blocks' => []];
        }
        
        $body = wp_remote_retrieve_body($response);
        $status = wp_remote_retrieve_response_code($response);
        preg_match_all('/<script[^>]*type=["\']application\/ld\+json["\'][^>]*>(.*?)<\/script>/si', $body, $matches);
        
        $types = [];
        $sources = [];
        $blocks = [];
        if (!empty($matches[1])) {
            foreach ($matches[1] as $json_str) {
                $schema = json_decode(trim($json_str), true);
                if (!$schema) continue;
                
                // Detect source
                $source = 'Unknown';
                if (strpos($json_str, 'rank-math') !== false || strpos($json_str, 'rankmath') !== false) $source = 'RankMath';
                elseif (strpos($json_str, 'sfpf') !== false || strpos($json_str, 'SFPF') !== false) $source = 'SFPF';
                elseif (strpos($json_str, 'yoast') !== false) $source = 'Yoast';
                elseif (strpos($json_str, 'WebSite') !== false && strpos($json_str, 'SearchAction') !== false) $source = 'RankMath';
                
                // Extract types
                if (isset($schema['@type'])) {
                    $t = is_array($schema['@type']) ? $schema['@type'] : [$schema['@type']];
                    $types = array_merge($types, $t);
                    $sources[$source] = true;
                }
                if (isset($schema['@graph'])) {
                    foreach ($schema['@graph'] as $node) {
                        if (isset($node['@type'])) {
                            $t = is_array($node['@type']) ? $node['@type'] : [$node['@type']];
                            $types = array_merge($types, $t);
                        }
                    }
                    $sources[$source] = true;
                }
                $blocks[] = $schema;
            }
        }
        
        return [
            'types' => array_unique($types),
            'sources' => array_keys($sources),
            'blocks' => $blocks,
            'time_ms' => $time_ms,
            'status' => $status,
            'block_count' => count($matches[1]),
        ];
    };
    
    // ── CHECK 1: Homepage Schema ──
    $homepage_schema_type = get_option('sfpf_homepage_schema_type', 'person');
    $hp_result = $fetch_schemas(home_url('/'));
    
    if ($homepage_schema_type === 'none') {
        $checks[] = ['status' => 'info', 'label' => 'Homepage Schema', 'detail' => 'Schema injection disabled (set to "None")', 'types' => $hp_result['types'], 'time' => $hp_result['time_ms']];
    } else {
        $expected_types = [];
        if (in_array($homepage_schema_type, ['person', 'profile_page'])) $expected_types[] = 'Person';
        if (in_array($homepage_schema_type, ['profile_page', 'profile_page_only'])) $expected_types[] = 'ProfilePage';
        
        $found_expected = !empty(array_intersect($expected_types, $hp_result['types']));
        if ($found_expected) {
            $checks[] = ['status' => 'pass', 'label' => 'Homepage Schema', 'detail' => 'Found: ' . implode(', ', $hp_result['types']) . ' (expected: ' . implode(', ', $expected_types) . ')', 'time' => $hp_result['time_ms']];
            $pass_count++;
        } else {
            $checks[] = ['status' => 'fail', 'label' => 'Homepage Schema', 'detail' => 'Expected: ' . implode(', ', $expected_types) . '. Found: ' . (empty($hp_result['types']) ? 'none' : implode(', ', $hp_result['types'])), 'time' => $hp_result['time_ms']];
            $fail_count++;
        }
    }
    
    // ── CHECK 2: Schema Conflicts on Homepage ──
    if (!empty($hp_result['blocks'])) {
        $hp_all_types = [];
        $hp_types_by_source = [];
        foreach ($hp_result['blocks'] as $block) {
            $block_json = json_encode($block);
            $src = 'Unknown';
            if (strpos($block_json, 'rank-math') !== false || strpos($block_json, 'rankmath') !== false) $src = 'RankMath';
            elseif (strpos($block_json, 'sfpf') !== false || strpos($block_json, 'SFPF') !== false) $src = 'SFPF';
            
            $block_types = [];
            if (isset($block['@type'])) $block_types[] = is_array($block['@type']) ? implode(',', $block['@type']) : $block['@type'];
            if (isset($block['@graph'])) {
                foreach ($block['@graph'] as $node) {
                    if (isset($node['@type'])) $block_types[] = is_array($node['@type']) ? implode(',', $node['@type']) : $node['@type'];
                }
            }
            foreach ($block_types as $bt) {
                $hp_all_types[] = $bt;
                $hp_types_by_source[$src][] = $bt;
            }
        }
        
        $hp_type_counts = array_count_values($hp_all_types);
        $hp_conflicts = [];
        foreach (['Person', 'ProfilePage', 'Organization'] as $cht) {
            if (($hp_type_counts[$cht] ?? 0) > 1) {
                $hp_conflicts[] = $cht . ' ×' . $hp_type_counts[$cht];
            }
        }
        
        if (!empty($hp_conflicts)) {
            $conflict_detail = 'Duplicate schema types on homepage: ' . implode(', ', $hp_conflicts) . '. Sources: ' . implode(' + ', array_keys($hp_types_by_source));
            $rm_in_conflict = false;
            foreach (array_keys($hp_types_by_source) as $s) { if (stripos($s, 'RankMath') !== false) $rm_in_conflict = true; }
            $check_entry = ['status' => 'fail', 'label' => 'Homepage Schema Conflicts', 'detail' => $conflict_detail, 'time' => 0];
            if ($rm_in_conflict) {
                $check_entry['action'] = admin_url('admin.php?page=rank-math-options-titles');
            }
            $checks[] = $check_entry;
            $fail_count++;
        } else {
            $checks[] = ['status' => 'pass', 'label' => 'Homepage Schema Conflicts', 'detail' => 'No duplicate schema types detected. Sources: ' . implode(', ', array_keys($hp_types_by_source)), 'time' => 0];
            $pass_count++;
        }
    }
    
    // ── CHECK 3: FAQ Schema on Homepage ──
    $faq_found = in_array('FAQPage', $hp_result['types']);
    $faq_issues = sfpf_get_faq_schema_issues($hp_result['blocks']);
    if ($faq_found && empty($faq_issues)) {
        $checks[] = ['status' => 'pass', 'label' => 'Homepage FAQ Schema', 'detail' => 'FAQPage schema detected on homepage', 'time' => 0];
        $pass_count++;
    } elseif ($faq_found) {
        $checks[] = ['status' => 'fail', 'label' => 'Homepage FAQ Schema', 'detail' => 'FAQPage schema detected but has issues: ' . implode('; ', $faq_issues), 'time' => 0, 'action' => get_edit_post_link((int) get_option('page_on_front'), 'raw')];
        $fail_count++;
    } else {
        $checks[] = ['status' => 'warn', 'label' => 'Homepage FAQ Schema', 'detail' => 'No FAQPage schema found on homepage. Add one via the FAQ tab if desired.', 'time' => 0];
        $warn_count++;
    }
    
    // ── CHECK 3: Breadcrumb Schema (via RankMath) ──
    $breadcrumb_found = in_array('BreadcrumbList', $hp_result['types']);
    $rm_active = is_plugin_active('seo-by-rank-math/rank-math.php');
    $breadcrumbs_enabled = false;
    if ($rm_active && class_exists('RankMath\\Helper')) {
        $breadcrumbs_enabled = \RankMath\Helper::is_breadcrumbs_enabled();
    }
    if ($breadcrumbs_enabled) {
        if ($breadcrumb_found) {
            $checks[] = ['status' => 'pass', 'label' => 'Breadcrumb Schema', 'detail' => 'BreadcrumbList schema found (RankMath)', 'time' => 0];
            $pass_count++;
        } else {
            $bc_hidden_front = get_option('sfpf_breadcrumb_hide_frontpage', false);
            if ($bc_hidden_front) {
                // Scan a subpage instead — try biography first, then contact
                $bc_scan_url = '';
                $bc_scan_label = '';
                $bc_bio_page_id = get_option('sfpf_page_biography');
                $bc_contact_page_id = get_option('sfpf_page_connect');
                
                if ($bc_bio_page_id && get_post_status($bc_bio_page_id) === 'publish') {
                    $bc_scan_url = get_permalink($bc_bio_page_id);
                    $bc_scan_label = get_the_title($bc_bio_page_id);
                } elseif ($bc_contact_page_id && get_post_status($bc_contact_page_id) === 'publish') {
                    $bc_scan_url = get_permalink($bc_contact_page_id);
                    $bc_scan_label = get_the_title($bc_contact_page_id);
                }
                
                if ($bc_scan_url) {
                    $bc_result = $fetch_schemas($bc_scan_url);
                    $bc_found_subpage = in_array('BreadcrumbList', $bc_result['types']);
                    if ($bc_found_subpage) {
                        $checks[] = ['status' => 'pass', 'label' => 'Breadcrumb Schema', 'detail' => 'Hidden on front page. Found BreadcrumbList on "' . $bc_scan_label . '" (' . $bc_result['time_ms'] . 'ms)', 'time' => $bc_result['time_ms']];
                        $pass_count++;
                    } else {
                        $checks[] = ['status' => 'warn', 'label' => 'Breadcrumb Schema', 'detail' => 'Hidden on front page and not found on "' . $bc_scan_label . '". RankMath may be enabled via theme support without rendering breadcrumb markup on this site.', 'time' => $bc_result['time_ms'], 'action' => admin_url('admin.php?page=rank-math-options-general&view=breadcrumbs')];
                        $warn_count++;
                    }
                } else {
                    $checks[] = ['status' => 'info', 'label' => 'Breadcrumb Schema', 'detail' => 'Hidden on front page. No biography or contact page assigned to scan instead. Set one in Critical Pages.', 'time' => 0];
                }
            } else {
                $checks[] = ['status' => 'warn', 'label' => 'Breadcrumb Schema', 'detail' => 'RankMath reports breadcrumbs enabled, but no BreadcrumbList schema was found on the homepage. Verify theme integration if you expect breadcrumb markup.', 'time' => 0, 'action' => admin_url('admin.php?page=rank-math-options-general&view=breadcrumbs')];
                $warn_count++;
            }
        }
    } else {
        $checks[] = ['status' => 'info', 'label' => 'Breadcrumb Schema', 'detail' => 'RankMath breadcrumbs not enabled.', 'time' => 0, 'action' => admin_url('admin.php?page=rank-math-options-general&view=breadcrumbs')];
    }
    
    // ── CHECK 4: Biography Page Schema ──
    $bio_schema_type = get_option('sfpf_biography_schema_type', 'profile_page_only');
    $bio_page_id = get_option('sfpf_page_biography');
    if ($bio_page_id && get_post_status($bio_page_id) === 'publish') {
        if ($bio_schema_type !== 'none') {
            $bio_result = $fetch_schemas(get_permalink($bio_page_id));
            $bio_expected = [];
            if (in_array($bio_schema_type, ['person', 'profile_page'])) $bio_expected[] = 'Person';
            if (in_array($bio_schema_type, ['profile_page', 'profile_page_only'])) $bio_expected[] = 'ProfilePage';
            
            $bio_ok = !empty(array_intersect($bio_expected, $bio_result['types']));
            if ($bio_ok) {
                $checks[] = ['status' => 'pass', 'label' => 'Biography Page Schema', 'detail' => 'Found: ' . implode(', ', $bio_result['types']), 'time' => $bio_result['time_ms']];
                $pass_count++;
            } else {
                $checks[] = ['status' => 'fail', 'label' => 'Biography Page Schema', 'detail' => 'Expected: ' . implode(', ', $bio_expected) . '. Found: ' . (empty($bio_result['types']) ? 'none' : implode(', ', $bio_result['types'])), 'time' => $bio_result['time_ms']];
                $fail_count++;
            }
        } else {
            $checks[] = ['status' => 'info', 'label' => 'Biography Page Schema', 'detail' => 'Biography schema set to "None". No injection active.', 'time' => 0];
        }
    } else {
        $checks[] = ['status' => 'warn', 'label' => 'Biography Page', 'detail' => 'No biography page assigned. Set one in the Critical Pages tab.', 'time' => 0];
        $warn_count++;
    }
    
    // ── CHECK 5: Organization CPTs ──
    $orgs = get_posts(['post_type' => 'organization', 'posts_per_page' => 5, 'post_status' => 'publish']);
    if (!empty($orgs)) {
        foreach ($orgs as $org) {
            $org_result = $fetch_schemas(get_permalink($org->ID));
            $has_org = in_array('Organization', $org_result['types']);
            if ($has_org) {
                $checks[] = ['status' => 'pass', 'label' => '🏢 ' . $org->post_title, 'detail' => 'Organization schema found. Sources: ' . implode(', ', $org_result['sources']), 'time' => $org_result['time_ms']];
                $pass_count++;
            } else {
                $checks[] = ['status' => 'fail', 'label' => '🏢 ' . $org->post_title, 'detail' => 'No Organization schema found. Types: ' . (empty($org_result['types']) ? 'none' : implode(', ', $org_result['types'])), 'time' => $org_result['time_ms']];
                $fail_count++;
            }
        }
    } else {
        $checks[] = ['status' => 'info', 'label' => 'Organizations', 'detail' => 'No published organizations found.', 'time' => 0];
    }
    
    // ── CHECK 6: Book CPTs ──
    $books = get_posts(['post_type' => 'book', 'posts_per_page' => 5, 'post_status' => 'publish']);
    if (!empty($books)) {
        foreach ($books as $book) {
            $book_result = $fetch_schemas(get_permalink($book->ID));
            $has_book = in_array('Book', $book_result['types']);
            if ($has_book) {
                $checks[] = ['status' => 'pass', 'label' => '📚 ' . $book->post_title, 'detail' => 'Book schema found. Sources: ' . implode(', ', $book_result['sources']), 'time' => $book_result['time_ms']];
                $pass_count++;
            } else {
                $checks[] = ['status' => 'fail', 'label' => '📚 ' . $book->post_title, 'detail' => 'No Book schema found. Types: ' . (empty($book_result['types']) ? 'none' : implode(', ', $book_result['types'])), 'time' => $book_result['time_ms']];
                $fail_count++;
            }
        }
    } else {
        $checks[] = ['status' => 'info', 'label' => 'Books', 'detail' => 'No published books found.', 'time' => 0];
    }
    
    // ── Build output ──
    $total = $pass_count + $fail_count + $warn_count;
    $out = '<div style="margin-bottom:10px;">';
    $out .= '<div style="color:#10b981;font-size:16px;font-weight:700;margin-bottom:4px;">🔍 Full Site Schema Checklist</div>';
    $out .= '<div style="color:#6b7280;font-size:11px;margin-bottom:12px;">Scanned at ' . esc_html($scan_time) . '</div>';
    
    // Summary bar
    $out .= '<div style="display:flex;gap:16px;margin-bottom:14px;padding:10px 14px;background:#0f172a;border-radius:6px;">';
    $out .= '<span style="color:#4ade80;font-weight:600;">✅ ' . $pass_count . ' passed</span>';
    if ($fail_count > 0) $out .= '<span style="color:#f87171;font-weight:600;">❌ ' . $fail_count . ' failed</span>';
    if ($warn_count > 0) $out .= '<span style="color:#fbbf24;font-weight:600;">⚠️ ' . $warn_count . ' warnings</span>';
    $out .= '</div>';
    
    // Checklist items
    foreach ($checks as $check) {
        $icon = '✅';
        $color = '#4ade80';
        if ($check['status'] === 'fail') { $icon = '❌'; $color = '#f87171'; }
        elseif ($check['status'] === 'warn') { $icon = '⚠️'; $color = '#fbbf24'; }
        elseif ($check['status'] === 'info') { $icon = 'ℹ️'; $color = '#60a5fa'; }
        
        $out .= '<div style="padding:8px 0;border-bottom:1px solid #1e293b;display:flex;align-items:flex-start;gap:8px;">';
        $out .= '<span style="flex-shrink:0;width:20px;text-align:center;">' . $icon . '</span>';
        $out .= '<div style="flex:1;">';
        $out .= '<span style="color:' . $color . ';font-weight:600;font-size:12px;">' . esc_html($check['label']) . '</span>';
        $out .= '<div style="color:#94a3b8;font-size:11px;margin-top:2px;">' . esc_html($check['detail']) . '</div>';
        if (!empty($check['time']) && $check['time'] > 0) {
            $out .= '<span style="color:#475569;font-size:10px;">⏱️ ' . $check['time'] . 'ms</span>';
        }
        if (!empty($check['action'])) {
            $out .= ' <a href="' . esc_url($check['action']) . '" target="_blank" style="color:#60a5fa;font-size:10px;margin-left:8px;">Open Settings →</a>';
        }
        $out .= '</div></div>';
    }
    
    $out .= '</div>';
    
    // Overall verdict
    if ($fail_count === 0) {
        $out .= '<div style="margin-top:12px;padding:10px 14px;background:#052e16;border:1px solid #16a34a;border-radius:6px;color:#86efac;font-size:13px;">🎉 <strong>All checks passed!</strong> Your schema setup looks good.</div>';
    } else {
        $out .= '<div style="margin-top:12px;padding:10px 14px;background:#450a0a;border:1px solid #dc2626;border-radius:6px;color:#fca5a5;font-size:13px;">⚠️ <strong>' . $fail_count . ' issue(s) found.</strong> Review failed checks above and reprocess schema from the Schema tab.</div>';
    }
    
    return $out;
}

/**
 * Reprocess schema
 */
function ajax_reprocess_schema() {
    verify_ajax_nonce();
    
    $type = sanitize_key($_POST['type'] ?? '');
    $count = 0;
    
    switch ($type) {
        case 'homepage':
            $schema_type = get_option('sfpf_homepage_schema_type', 'person');
            if ($schema_type !== 'none' && function_exists(__NAMESPACE__ . '\\reprocess_homepage_schema')) {
                $result = reprocess_homepage_schema();
                if (!empty($result['success'])) {
                    $count = 1;
                } else {
                    wp_send_json_error($result['message'] ?? 'Failed to reprocess homepage schema');
                }
            }
            write_log("Reprocessed homepage schema");
            break;
            
        case 'biography':
            $bio_schema_type = get_option('sfpf_biography_schema_type', 'profile_page_only');
            if ($bio_schema_type !== 'none' && function_exists(__NAMESPACE__ . '\\reprocess_biography_schema')) {
                $result = reprocess_biography_schema();
                if (!empty($result['success'])) {
                    $count = 1;
                } else {
                    wp_send_json_error($result['message'] ?? 'Failed to reprocess biography schema');
                }
            }
            write_log("Reprocessed biography schema");
            break;
            
        case 'books':
            $books = get_posts([
                'post_type' => 'book',
                'posts_per_page' => -1,
                'post_status' => 'publish',
            ]);
            
            foreach ($books as $book) {
                $result = function_exists(__NAMESPACE__ . '\\generate_and_save_schema')
                    ? generate_and_save_schema($book->ID)
                    : ['success' => false];
                if (!empty($result['success'])) {
                    $count++;
                }
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
                $result = function_exists(__NAMESPACE__ . '\\generate_and_save_schema')
                    ? generate_and_save_schema($org->ID)
                    : ['success' => false];
                if (!empty($result['success'])) {
                    $count++;
                }
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
    
    $counts = ['homepage' => 0, 'biography' => 0, 'books' => 0, 'organizations' => 0];
    
    // Homepage
    $hp_schema_type = get_option('sfpf_homepage_schema_type', 'person');
    if ($hp_schema_type !== 'none' && function_exists(__NAMESPACE__ . '\\reprocess_homepage_schema')) {
        $result = reprocess_homepage_schema();
        if (!empty($result['success'])) {
            $counts['homepage'] = 1;
        }
    }
    
    // Biography
    $bio_schema_type = get_option('sfpf_biography_schema_type', 'profile_page_only');
    if ($bio_schema_type !== 'none' && function_exists(__NAMESPACE__ . '\\reprocess_biography_schema')) {
        $result = reprocess_biography_schema();
        if (!empty($result['success'])) {
            $counts['biography'] = 1;
        }
    }
    
    // Books
    if (is_snippet_enabled('sfpf_enable_book_cpt')) {
        $books = get_posts([
            'post_type' => 'book',
            'posts_per_page' => -1,
            'post_status' => 'publish',
        ]);
        
        foreach ($books as $book) {
            $result = function_exists(__NAMESPACE__ . '\\generate_and_save_schema')
                ? generate_and_save_schema($book->ID)
                : ['success' => false];
            if (!empty($result['success'])) {
                $counts['books']++;
            }
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
            $result = function_exists(__NAMESPACE__ . '\\generate_and_save_schema')
                ? generate_and_save_schema($org->ID)
                : ['success' => false];
            if (!empty($result['success'])) {
                $counts['organizations']++;
            }
        }
    }
    
    write_log("Rebuilt all schemas: homepage={$counts['homepage']}, biography={$counts['biography']}, books={$counts['books']}, orgs={$counts['organizations']}");
    
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
        wp_send_json_success(get_page_ajax_payload($existing_assigned, true, 'Page already assigned'));
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
        if (is_sfpf_managed_page($existing->ID, $page_key)) {
            mark_sfpf_managed_page($existing->ID, $page_key);
            update_option('sfpf_page_' . $page_key, $existing->ID);
            wp_send_json_success(get_page_ajax_payload($existing->ID, true, 'Existing SFPF-managed page assigned'));
            return;
        }

        wp_send_json_error('A page with this slug already exists. Use the Assign Page dropdown to link that page instead of Create.');
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
    
    mark_sfpf_managed_page($page_id, $page_key);
    update_option('sfpf_page_' . $page_key, $page_id);
    write_log("Page created: {$title} (ID: {$page_id}, key: {$page_key})");
    
    wp_send_json_success(get_page_ajax_payload($page_id));
}
add_action('wp_ajax_sfpf_create_page', __NAMESPACE__ . '\\ajax_create_page');

/**
 * Delete page AJAX handler
 */
function ajax_delete_page() {
    verify_ajax_nonce();
    
    $page_key = sanitize_key($_POST['page_key'] ?? '');
    $page_id = intval($_POST['page_id'] ?? 0);
    
    if (!$page_key || !$page_id) {
        wp_send_json_error('Invalid page data');
    }

    $assigned_page_id = (int) get_option('sfpf_page_' . $page_key, 0);
    if ($assigned_page_id > 0) {
        $page_id = $assigned_page_id;
    }
    
    // Remove option assignment
    delete_option('sfpf_page_' . $page_key);

    if (!is_sfpf_managed_page($page_id, $page_key)) {
        write_log("Page unassigned without deletion: {$page_key} (ID: {$page_id})");
        wp_send_json_success([
            'page_key' => $page_key,
            'page_id' => $page_id,
            'trashed' => false,
            'message' => 'Page unassigned. Existing content was left intact.',
        ]);
        return;
    }

    // Trash the page
    $result = wp_trash_post($page_id);
    if (!$result) {
        wp_send_json_error('Failed to delete page');
    }
    
    write_log("Page deleted: {$page_key} (ID: {$page_id})");
    wp_send_json_success(['page_key' => $page_key, 'page_id' => $page_id, 'trashed' => true]);
}
add_action('wp_ajax_sfpf_delete_page', __NAMESPACE__ . '\\ajax_delete_page');

/**
 * Resolve a menu item and verify it belongs to the selected menu.
 *
 * @param int $menu_id Menu term ID.
 * @param int $menu_item_id Menu item post ID.
 * @return object|null
 */
function get_menu_item_from_menu($menu_id, $menu_item_id) {
    $menu_id = (int) $menu_id;
    $menu_item_id = (int) $menu_item_id;
    if ($menu_id <= 0 || $menu_item_id <= 0) {
        return null;
    }

    $items = wp_get_nav_menu_items($menu_id) ?: [];
    foreach ($items as $item) {
        if ((int) $item->ID === $menu_item_id) {
            return $item;
        }
    }

    return null;
}

/**
 * Find an existing menu item for a page.
 *
 * @param int $menu_id Menu term ID.
 * @param int $page_id Page ID.
 * @return object|null
 */
function find_page_menu_item($menu_id, $page_id) {
    $items = wp_get_nav_menu_items((int) $menu_id) ?: [];
    foreach ($items as $item) {
        if ($item->type === "post_type" && $item->object === "page" && (int) $item->object_id === (int) $page_id) {
            return $item;
        }
    }

    return null;
}

/**
 * Add or update a page menu item.
 *
 * @param int $menu_id Menu term ID.
 * @param int $page_id Page ID.
 * @param int $parent_menu_item_id Parent menu item ID.
 * @return array
 */
function upsert_page_menu_item($menu_id, $page_id, $parent_menu_item_id = 0) {
    $menu_id = (int) $menu_id;
    $page_id = (int) $page_id;
    $parent_menu_item_id = (int) $parent_menu_item_id;
    $existing = find_page_menu_item($menu_id, $page_id);
    $existing_id = $existing ? (int) $existing->ID : 0;

    $result = wp_update_nav_menu_item($menu_id, $existing_id, [
        "menu-item-object-id" => $page_id,
        "menu-item-object" => "page",
        "menu-item-type" => "post_type",
        "menu-item-parent-id" => $parent_menu_item_id,
        "menu-item-status" => "publish",
        "menu-item-title" => get_the_title($page_id),
    ]);

    if (is_wp_error($result)) {
        return ["success" => false, "message" => $result->get_error_message(), "menu_item_id" => 0, "created" => false];
    }

    return ["success" => true, "message" => $existing_id ? "Menu item updated." : "Menu item created.", "menu_item_id" => (int) $result, "created" => !$existing_id];
}

/**
 * Create a WordPress navigation menu.
 */
function ajax_create_navigation_menu() {
    verify_ajax_nonce();

    $menu_name = sanitize_text_field(wp_unslash($_POST["menu_name"] ?? ""));
    if ($menu_name === "") {
        wp_send_json_error("Menu name is required");
    }

    $existing = wp_get_nav_menu_object($menu_name);
    if ($existing) {
        wp_send_json_success(["menu_id" => (int) $existing->term_id, "name" => $existing->name, "message" => "Menu already exists."]);
    }

    $menu_id = wp_create_nav_menu($menu_name);
    if (is_wp_error($menu_id)) {
        wp_send_json_error($menu_id->get_error_message());
    }

    write_log("Navigation menu created: " . $menu_name . " (ID: " . (int) $menu_id . ")");
    wp_send_json_success(["menu_id" => (int) $menu_id, "name" => $menu_name, "message" => "Menu created."]);
}
add_action("wp_ajax_sfpf_create_navigation_menu", __NAMESPACE__ . "\\ajax_create_navigation_menu");

/**
 * Delete a WordPress navigation menu.
 */
function ajax_delete_navigation_menu() {
    verify_ajax_nonce();

    $menu_id = intval($_POST["menu_id"] ?? 0);
    $menu = $menu_id ? wp_get_nav_menu_object($menu_id) : null;
    if (!$menu) {
        wp_send_json_error("Menu not found");
    }

    $deleted = wp_delete_nav_menu($menu_id);
    if (!$deleted || is_wp_error($deleted)) {
        wp_send_json_error(is_wp_error($deleted) ? $deleted->get_error_message() : "Menu deletion failed");
    }

    write_log("Navigation menu deleted: " . $menu->name . " (ID: " . $menu_id . ")");
    wp_send_json_success(["menu_id" => $menu_id, "name" => $menu->name, "message" => "Menu deleted."]);
}
add_action("wp_ajax_sfpf_delete_navigation_menu", __NAMESPACE__ . "\\ajax_delete_navigation_menu");

/**
 * Attach one assigned SFPF page to a WordPress menu item.
 */
function ajax_attach_page_to_menu_item() {
    verify_ajax_nonce();

    $menu_id = intval($_POST["menu_id"] ?? 0);
    $parent_item_id = intval($_POST["parent_item_id"] ?? 0);
    $page_key = sanitize_key($_POST["page_key"] ?? "");
    $flat_pages = get_flat_critical_pages_structure();

    if (!$menu_id || !wp_get_nav_menu_object($menu_id)) {
        wp_send_json_error("Menu not found");
    }
    if (!$page_key || empty($flat_pages[$page_key])) {
        wp_send_json_error("Unknown SFPF page key");
    }
    if ($parent_item_id > 0 && !get_menu_item_from_menu($menu_id, $parent_item_id)) {
        wp_send_json_error("Parent menu item does not belong to the selected menu");
    }

    $page_id = (int) get_option("sfpf_page_" . $page_key, 0);
    if (!$page_id || get_post_status($page_id) !== "publish") {
        wp_send_json_error($flat_pages[$page_key]["title"] . " is not assigned to a published page");
    }

    $result = upsert_page_menu_item($menu_id, $page_id, $parent_item_id);
    if (!$result["success"]) {
        wp_send_json_error($result["message"]);
    }

    $menu = wp_get_nav_menu_object($menu_id);
    write_log("Attached SFPF page to menu: " . $page_key . " -> " . $menu->name);
    wp_send_json_success(["message" => $flat_pages[$page_key]["title"] . " attached to " . $menu->name . ".", "menu_item_id" => $result["menu_item_id"]]);
}
add_action("wp_ajax_sfpf_attach_page_to_menu_item", __NAMESPACE__ . "\\ajax_attach_page_to_menu_item");

/**
 * Attach one of the standard SFPF menu structures to a WordPress menu.
 */
function ajax_attach_menu_structure() {
    verify_ajax_nonce();

    $menu_id = intval($_POST["menu_id"] ?? 0);
    $parent_item_id = intval($_POST["parent_item_id"] ?? 0);
    $structure_key = sanitize_key($_POST["structure"] ?? "");
    $structures = get_navigation_menu_structures();
    $flat_pages = get_flat_critical_pages_structure();

    if (!$menu_id || !wp_get_nav_menu_object($menu_id)) {
        wp_send_json_error("Menu not found");
    }
    if (!$structure_key || empty($structures[$structure_key])) {
        wp_send_json_error("Unknown menu structure");
    }
    if ($parent_item_id > 0 && !get_menu_item_from_menu($menu_id, $parent_item_id)) {
        wp_send_json_error("Parent menu item does not belong to the selected menu");
    }

    $added = 0;
    $updated = 0;
    $skipped = 0;
    $created_by_key = [];

    foreach ($structures[$structure_key]["page_keys"] as $page_key) {
        if (empty($flat_pages[$page_key])) {
            $skipped++;
            continue;
        }

        $page_id = (int) get_option("sfpf_page_" . $page_key, 0);
        if (!$page_id || get_post_status($page_id) !== "publish") {
            $skipped++;
            continue;
        }

        $item_parent_id = $parent_item_id;
        $parent_key = $flat_pages[$page_key]["parent"] ?? null;
        if ($parent_key && isset($created_by_key[$parent_key])) {
            $item_parent_id = (int) $created_by_key[$parent_key];
        }

        $result = upsert_page_menu_item($menu_id, $page_id, $item_parent_id);
        if (!$result["success"]) {
            wp_send_json_error($result["message"]);
        }

        $created_by_key[$page_key] = (int) $result["menu_item_id"];
        if (!empty($result["created"])) {
            $added++;
        } else {
            $updated++;
        }
    }

    $menu = wp_get_nav_menu_object($menu_id);
    $label = $structures[$structure_key]["title"];
    $message = $label . " attached to " . $menu->name . ": " . $added . " added, " . $updated . " updated, " . $skipped . " skipped.";
    write_log("Navigation structure attached: " . $message);
    wp_send_json_success(["message" => $message, "added" => $added, "updated" => $updated, "skipped" => $skipped]);
}
add_action("wp_ajax_sfpf_attach_menu_structure", __NAMESPACE__ . "\\ajax_attach_menu_structure");

/**
 * Add critical pages to navigation menu with parent/child structure
 */
function ajax_add_pages_to_menu() {
    verify_ajax_nonce();
    
    $menu_id = intval($_POST['menu_id'] ?? 0);
    if (!$menu_id) {
        wp_send_json_error('Invalid menu');
    }
    
    $menu = wp_get_nav_menu_object($menu_id);
    if (!$menu) {
        wp_send_json_error('Menu not found');
    }
    
    $pages_structure = get_critical_pages_structure();
    $added = 0;
    
    // Get existing menu items to avoid duplicates
    $existing_items = wp_get_nav_menu_items($menu_id) ?: [];
    $existing_page_ids = [];
    foreach ($existing_items as $item) {
        if ($item->type === 'post_type' && $item->object === 'page') {
            $existing_page_ids[] = (int) $item->object_id;
        }
    }
    
    foreach ($pages_structure as $page_key => $page_data) {
        $page_id = get_option('sfpf_page_' . $page_key, 0);
        if (!$page_id || !get_post($page_id)) continue;
        
        // Skip if already in menu
        $parent_menu_item_id = 0;
        if (in_array((int)$page_id, $existing_page_ids)) {
            // Find the existing menu item ID so children can attach to it
            foreach ($existing_items as $item) {
                if ((int)$item->object_id === (int)$page_id) {
                    $parent_menu_item_id = $item->ID;
                    break;
                }
            }
        } else {
            $parent_menu_item_id = wp_update_nav_menu_item($menu_id, 0, [
                'menu-item-object-id'   => $page_id,
                'menu-item-object'      => 'page',
                'menu-item-type'        => 'post_type',
                'menu-item-status'      => 'publish',
                'menu-item-title'       => get_the_title($page_id),
            ]);
            if (!is_wp_error($parent_menu_item_id)) {
                $added++;
            }
        }
        
        // Add children
        if (!empty($page_data['children']) && $parent_menu_item_id) {
            foreach ($page_data['children'] as $child_key => $child_data) {
                $child_id = get_option('sfpf_page_' . $child_key, 0);
                if (!$child_id || !get_post($child_id)) continue;
                if (in_array((int)$child_id, $existing_page_ids)) continue;
                
                $result = wp_update_nav_menu_item($menu_id, 0, [
                    'menu-item-object-id'   => $child_id,
                    'menu-item-object'      => 'page',
                    'menu-item-type'        => 'post_type',
                    'menu-item-parent-id'   => $parent_menu_item_id,
                    'menu-item-status'      => 'publish',
                    'menu-item-title'       => get_the_title($child_id),
                ]);
                if (!is_wp_error($result)) {
                    $added++;
                }
            }
        }
    }
    
    if ($added === 0) {
        wp_send_json_success(['message' => 'All pages already in menu (0 added)']);
    }
    
    write_log("Added {$added} pages to menu: {$menu->name}");
    wp_send_json_success(['message' => "{$added} pages added to \"{$menu->name}\""]);
}
add_action('wp_ajax_sfpf_add_pages_to_menu', __NAMESPACE__ . '\\ajax_add_pages_to_menu');

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
    
    $primary_faq = sanitize_key($_POST['primary_faq_set'] ?? '');
    update_option('sfpf_primary_faq_set', $primary_faq);
    
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
    $schema_type = get_option('sfpf_homepage_schema_type', 'person');
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
    $schema = function_exists(__NAMESPACE__ . '\\get_post_schema')
        ? get_post_schema($page_on_front)
        : get_post_meta($page_on_front, 'schema_markup', true);
    $schema_source = function_exists(__NAMESPACE__ . '\\get_post_schema_source')
        ? get_post_schema_source($page_on_front)
        : null;

    if ($schema) {
        $output .= "✅ Schema is stored in " . ($schema_source ?: 'post meta') . "\n";
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
        $output .= "❌ No schema stored in canonical or legacy schema storage\n";
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

    $callback = __NAMESPACE__ . '\\inject_schema_markup';
    $hook_priority = has_action('wp_head', $callback);

    if (is_admin()) {
        $output .= "ℹ️ This debug action runs in admin/AJAX context.\n";
        $output .= "   SFPF only attaches schema injection during frontend requests, so a missing wp_head callback here is expected.\n\n";
    } elseif ($hook_priority !== false) {
        $output .= "✅ Hook found at priority {$hook_priority}\n";
        $output .= "   Function: {$callback}\n";
    } else {
        $output .= "❌ inject_schema_markup hook NOT found in wp_head during a frontend request.\n";
        $output .= "   This means schema will not be injected.\n\n";
    }

    $output .= "Runtime context:\n";
    $output .= "  - is_admin(): " . (is_admin() ? 'Yes' : 'No') . "\n";
    $output .= "  - front page configured: " . (get_front_page_id() ? 'Yes' : 'No') . "\n";
    $output .= "  - biography page configured: " . (get_option('sfpf_page_biography') ? 'Yes' : 'No') . "\n";

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
    
    $schema_type = get_option('sfpf_homepage_schema_type', 'person');
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

// =============================================================================
// ARTICLES BULK IMPORT
// =============================================================================

/**
 * Process bulk article URLs: extract from any input, deduplicate, HTTP fetch titles, extract sources
 * One parser handles everything: HTML dumps, plain URLs, mixed content, bare domains
 */
function ajax_process_articles() {
    verify_ajax_nonce();
    
    $raw_input = $_POST['urls'] ?? '';
    $user_id = intval($_POST['user_id'] ?? get_current_user_id());
    
    if (empty(trim($raw_input))) {
        wp_send_json_error('No URLs provided');
    }
    
    $report = [];
    $report[] = "═══ SFPF Article Import ═══";
    $report[] = "Started: " . current_time('Y-m-d H:i:s');
    $report[] = "";
    
    // ── Step 1: Extract every URL from the input ──
    // One pass: pull URLs from href="...", from bare https://..., from bare domains
    $found_urls = [];
    
    // Pass A: Pull URLs out of href="..." or href='...'
    if (preg_match_all('/href=["\']([^"\']+)["\']/i', $raw_input, $href_matches)) {
        foreach ($href_matches[1] as $url) {
            $url = trim($url);
            if (empty($url) || $url === '#' || strpos($url, 'javascript:') === 0 || strpos($url, 'mailto:') === 0) continue;
            $found_urls[] = $url;
        }
    }
    
    // Pass B: Pull bare https:// or http:// URLs that aren't already inside href=""
    // Strip all href="..." first so we don't double-count
    $without_hrefs = preg_replace('/href=["\'][^"\']+["\']/i', '', $raw_input);
    if (preg_match_all('#(https?://[^\s<>"\')\]]+)#i', $without_hrefs, $bare_matches)) {
        foreach ($bare_matches[1] as $url) {
            $url = rtrim($url, '.,;)\'">');
            $found_urls[] = $url;
        }
    }
    
    // Pass C: If nothing found yet, try bare domains (no protocol)
    if (empty($found_urls)) {
        $stripped = strip_tags($raw_input);
        $parts = preg_split('/[\n\r,\s]+/', $stripped);
        foreach ($parts as $part) {
            $part = trim($part, " \t\n\r\0\x0B,;\"'<>()[]");
            if (empty($part)) continue;
            if (preg_match('/^[a-z0-9][a-z0-9\-]*\.[a-z]{2,}/i', $part)) {
                $found_urls[] = $part;
            }
        }
    }
    
    // ── Step 2: Normalize all URLs ──
    $clean_urls = [];
    foreach ($found_urls as $url) {
        $url = trim($url);
        // Ensure protocol
        if (!preg_match('#^https?://#i', $url)) {
            $url = 'https://' . $url;
        }
        // Strip trailing junk
        $url = rtrim($url, '.,;)\'">\\');
        // Normalize www
        $url = preg_replace('#^(https?://)www\.#i', '$1', $url);
        // Validate
        if (!filter_var($url, FILTER_VALIDATE_URL)) continue;
        $clean_urls[] = $url;
    }
    
    $report[] = "▸ URLs extracted: " . count($clean_urls);
    
    // ── Step 3: Deduplicate within input ──
    $seen = [];
    $unique_urls = [];
    $input_dupes = 0;
    foreach ($clean_urls as $url) {
        $key = strtolower(preg_replace('#^https?://#i', '', rtrim($url, '/')));
        if (isset($seen[$key])) {
            $input_dupes++;
            continue;
        }
        $seen[$key] = true;
        $unique_urls[] = $url;
    }
    if ($input_dupes > 0) {
        $report[] = "▸ Input duplicates removed: {$input_dupes}";
    }
    
    // ── Step 4: Check against existing repeater ──
    $existing = get_field('articles', 'user_' . $user_id);
    $existing_urls = [];
    if (is_array($existing)) {
        foreach ($existing as $item) {
            if (!empty($item['url'])) {
                $norm = strtolower(preg_replace('#^https?://(www\.)?#i', '', rtrim($item['url'], '/')));
                $existing_urls[$norm] = true;
            }
        }
    }
    
    $new_urls = [];
    $skipped_dupes = 0;
    foreach ($unique_urls as $url) {
        $norm = strtolower(preg_replace('#^https?://(www\.)?#i', '', rtrim($url, '/')));
        if (isset($existing_urls[$norm])) {
            $report[] = "  ⊘ SKIP (already exists): {$url}";
            $skipped_dupes++;
        } else {
            $new_urls[] = $url;
        }
    }
    
    if ($skipped_dupes > 0) {
        $report[] = "▸ Already in repeater (skipped): {$skipped_dupes}";
    }
    
    $report[] = "▸ New URLs to process: " . count($new_urls);
    $report[] = "";
    
    if (empty($new_urls)) {
        $report[] = "✓ Nothing to import — all URLs already exist.";
        wp_send_json_success([
            'report' => implode("\n", $report),
            'original_input' => $raw_input,
            'imported' => 0,
            'total' => count($existing ?? []),
        ]);
    }
    
    // ── Step 5: For each URL — extract source, HTTP fetch title ──
    $processed = [];
    foreach ($new_urls as $i => $url) {
        $n = $i + 1;
        $report[] = "── Article {$n}/" . count($new_urls) . " ──";
        $report[] = "  URL:    {$url}";
        
        // Source = domain minus www
        $parsed = wp_parse_url($url);
        $source = preg_replace('/^www\./', '', $parsed['host'] ?? '');
        $report[] = "  Source: {$source}";
        
        // Always fetch the page for the title
        $title = '';
        $fetch_status = '';
        
        $response = wp_remote_get($url, [
            'timeout' => 10,
            'redirection' => 3,
            'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'sslverify' => false,
        ]);
        
        if (is_wp_error($response)) {
            $fetch_status = 'FAILED (' . $response->get_error_message() . ')';
        } else {
            $code = wp_remote_retrieve_response_code($response);
            $body = wp_remote_retrieve_body($response);
            
            if ($code >= 200 && $code < 400 && !empty($body)) {
                // Try <title>
                if (preg_match('/<title[^>]*>(.*?)<\/title>/is', $body, $m)) {
                    $title = trim(html_entity_decode(strip_tags($m[1]), ENT_QUOTES, 'UTF-8'));
                    $title = preg_replace('/\s*[|\-–—]\s*(Forbes|TechCrunch|The New York Times|NYT|WSJ|CNN|BBC|Reuters|Medium|LinkedIn|YouTube|Grit Daily|Expert Insights|Designli|SCRA|Block Telegraph).*$/i', '', $title);
                    $title = trim($title);
                    if (strlen($title) > 200) $title = substr($title, 0, 197) . '...';
                    $fetch_status = "OK (<title>)";
                }
                // Fallback: og:title (either attribute order)
                if (empty($title)) {
                    if (preg_match('/<meta[^>]+(?:property=["\']og:title["\'][^>]+content=["\']([^"\']+)["\']|content=["\']([^"\']+)["\'][^>]+property=["\']og:title["\'])/is', $body, $m)) {
                        $title = trim(html_entity_decode(strip_tags($m[1] ?: $m[2]), ENT_QUOTES, 'UTF-8'));
                        if (strlen($title) > 200) $title = substr($title, 0, 197) . '...';
                        $fetch_status = "OK (og:title)";
                    }
                }
                // Last resort: URL slug
                if (empty($title)) {
                    $path = $parsed['path'] ?? '';
                    $slug = basename(rtrim($path, '/'));
                    if ($slug && $slug !== '/' && !preg_match('/\.\w{2,4}$/', $slug)) {
                        $title = ucwords(str_replace(['-', '_'], ' ', $slug));
                        $fetch_status = "HTTP {$code} — no title, using slug";
                    } else {
                        $fetch_status = "HTTP {$code} — no title found";
                    }
                }
            } else {
                $fetch_status = "HTTP {$code}";
                // Slug fallback even on error
                $path = $parsed['path'] ?? '';
                $slug = basename(rtrim($path, '/'));
                if ($slug && $slug !== '/' && !preg_match('/\.\w{2,4}$/', $slug)) {
                    $title = ucwords(str_replace(['-', '_'], ' ', $slug));
                    $fetch_status .= " — using slug";
                }
            }
        }
        
        $report[] = "  Title:  " . ($title ?: '(none)');
        $report[] = "  Fetch:  {$fetch_status}";
        $report[] = "";
        
        $processed[] = [
            'title' => $title,
            'source' => $source,
            'url' => $url,
        ];
    }
    
    // ── Step 6: Return articles to jQuery — it will inject rows into ACF repeater ──
    // No update_field() here — user saves via the normal WP save button after reviewing
    $total_after = count(is_array($existing) ? $existing : []) + count($processed);
    
    $report[] = "═══ Summary ═══";
    $report[] = "Imported:   " . count($processed) . " new articles";
    $report[] = "Duplicates: " . ($input_dupes + $skipped_dupes) . " skipped";
    $report[] = "Total:      " . $total_after . " articles in repeater";
    $report[] = "";
    $report[] = "✓ Done! Articles added to repeater — save the profile to persist.";
    
    wp_send_json_success([
        'report' => implode("\n", $report),
        'original_input' => $raw_input,
        'imported' => count($processed),
        'total' => $total_after,
        'articles' => $processed,
    ]);
}
add_action('wp_ajax_sfpf_process_articles', __NAMESPACE__ . '\\ajax_process_articles');
