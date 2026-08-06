<?php

declare( strict_types=1 );

namespace sfpf_person_website;

/**
 * Bulk article URL parsing and metadata extraction action.
 *
 * Callback names remain in the legacy namespace for template and hook compatibility.
 */

defined( 'ABSPATH' ) || exit;

// =============================================================================
// ARTICLES BULK IMPORT
// =============================================================================

/**
 * Process bulk article URLs: extract from any input, deduplicate, HTTP fetch titles, extract sources
 * One parser handles everything: HTML dumps, plain URLs, mixed content, bare domains
 */
function ajax_process_articles() {
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
