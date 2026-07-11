<?php

declare( strict_types=1 );

namespace sfpf_person_website;

/**
 * Schema URL detection action and output formatting.
 *
 * Callback names remain in the legacy namespace for template and hook compatibility.
 */

defined( 'ABSPATH' ) || exit;

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

    if (class_exists("Hexa\\PluginCore\\SchemaDetection\\SchemaPageScanner") && class_exists("Hexa\\PluginCore\\SchemaDetection\\SchemaScanRenderer")) {
        $expected = [];
        $expected_map = [
            "person" => "Person",
            "profile_page_only" => "ProfilePage + Person",
            "profile_page" => "ProfilePage + Person",
            "none" => "None (disabled)",
        ];

        if ($type === "homepage") {
            $hp_type = get_option("sfpf_homepage_schema_type", "person");
            $expected[] = "<strong>SFPF Expected:</strong> " . ($expected_map[$hp_type] ?? $hp_type);
        } elseif ($type === "biography") {
            $bio_type = get_option("sfpf_biography_schema_type", "profile_page_only");
            $expected[] = "<strong>SFPF Expected:</strong> " . ($expected_map[$bio_type] ?? $bio_type);
        } elseif ($type === "books") {
            $expected[] = "<strong>SFPF Expected:</strong> Book";
        } elseif ($type === "organizations") {
            $expected[] = "<strong>SFPF Expected:</strong> Organization";
        } elseif ($type === "testimonials") {
            $expected[] = "<strong>SFPF Expected:</strong> Testimonial review schema if enabled";
        }

        if (defined("RANK_MATH_VERSION")) {
            $rm_disabled = false;
            if ($type === "homepage") $rm_disabled = get_option("sfpf_rankmath_disable_homepage", false);
            elseif ($type === "biography") $rm_disabled = get_option("sfpf_rankmath_disable_biography", false);
            elseif ($type === "books") $rm_disabled = get_option("sfpf_rankmath_disable_books", false);
            elseif ($type === "organizations") $rm_disabled = get_option("sfpf_rankmath_disable_organizations", false);
            $expected[] = "<strong>RankMath:</strong> " . ($rm_disabled ? "Disabled for this type" : "Active - may inject its own schema");
        }

        $scanner = new \Hexa\PluginCore\SchemaDetection\SchemaPageScanner();
        $renderer = new \Hexa\PluginCore\SchemaDetection\SchemaScanRenderer();
        $scans = [];

        foreach ($urls as $item) {
            $scans[] = $scanner->scanUrl($item["url"], [
                "title" => $item["title"],
                "cache_bust" => true,
                "timeout" => 15,
                "sslverify" => false,
            ]);
        }

        wp_send_json_success(["output" => $renderer->renderReport($scans, ["title" => "Schema Detection Results: " . strtoupper($type), "subtitle" => "Scanned at " . current_time("Y-m-d H:i:s") . " with Hexa Core SchemaDetection.", "expected" => $expected, "debug" => $debug])]);
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
