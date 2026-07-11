<?php

declare( strict_types=1 );

namespace sfpf_person_website;

/**
 * Full-site schema checklist generation.
 *
 * Callback names remain in the legacy namespace for template and hook compatibility.
 */

defined( 'ABSPATH' ) || exit;

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
        if (class_exists("Hexa\\PluginCore\\SchemaDetection\\SchemaPageScanner")) {
            $scanner = new \Hexa\PluginCore\SchemaDetection\SchemaPageScanner();
            $scan = $scanner->scanUrl($url, [
                "title" => $url,
                "cache_bust" => true,
                "timeout" => 15,
                "sslverify" => false,
            ]);
            return [
                "error" => $scan["error"],
                "time_ms" => $scan["time_ms"],
                "types" => $scan["types"],
                "sources" => array_keys((array) $scan["types_by_source"]),
                "blocks" => array_column((array) $scan["blocks"], "schema"),
                "status" => $scan["status"],
                "block_count" => $scan["block_count"],
            ];
        }

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
