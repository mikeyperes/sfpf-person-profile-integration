<?php

declare( strict_types=1 );

namespace sfpf_person_website;

/**
 * Authenticated profile diagnostics and debug endpoint routing.
 *
 * Callback names remain in the legacy namespace for template and hook compatibility.
 */

defined( 'ABSPATH' ) || exit;

// ============================================================================
// PUBLIC PROFILE DEBUG ENDPOINT
// ============================================================================

function register_public_profile_debug_endpoint() {
    add_rewrite_rule('^sfpf-profile-debug/?$', 'index.php?sfpf_profile_debug=1', 'top');
}
add_action('init', __NAMESPACE__ . '\\register_public_profile_debug_endpoint', 1);

function add_public_profile_debug_query_var($vars) {
    $vars[] = 'sfpf_profile_debug';
    return $vars;
}
add_filter('query_vars', __NAMESPACE__ . '\\add_public_profile_debug_query_var');

function sfpf_current_request_url() {
    $scheme = is_ssl() ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? parse_url(home_url('/'), PHP_URL_HOST);
    $uri = $_SERVER['REQUEST_URI'] ?? '/sfpf-profile-debug/';
    return esc_url_raw($scheme . '://' . $host . $uri);
}

function sfpf_profile_debug_urls() {
    $debug = home_url('/sfpf-profile-debug/');
    $urls = [
        'request_url' => sfpf_current_request_url(),
        'public_debug_html' => $debug,
        'public_debug_json' => add_query_arg('format', 'json', $debug),
        'site_url' => site_url('/'),
        'home_url' => home_url('/'),
        'rest_api' => rest_url('/'),
        'homepage' => home_url('/'),
    ];
    foreach (wp_load_alloptions() as $key => $value) {
        if (strpos((string) $key, 'sfpf_page_') === 0 && (int) $value > 0) {
            $permalink = get_permalink((int) $value);
            if ($permalink) $urls[$key] = $permalink;
        }
    }
    $books = get_posts(['post_type' => 'book', 'post_status' => 'publish', 'posts_per_page' => -1, 'orderby' => 'ID', 'order' => 'ASC']);
    foreach ($books as $post) $urls['book_' . $post->ID] = get_permalink($post);
    $orgs = get_posts(['post_type' => 'organization', 'post_status' => 'publish', 'posts_per_page' => -1, 'orderby' => 'ID', 'order' => 'ASC']);
    foreach ($orgs as $post) $urls['organization_' . $post->ID] = get_permalink($post);
    return array_filter($urls);
}

function sfpf_profile_debug_data() {
    $founder_id = get_founder_user_id();
    $user_key = $founder_id ? 'user_' . $founder_id : '';
    $org = get_primary_organization();
    $person_gallery = $user_key && function_exists('get_field') ? get_field('gallery', $user_key) : [];
    $org_gallery = ($org && function_exists('get_field')) ? get_field('gallery', $org->ID) : [];
    $shortcodes = [
        'education' => '[founder action="display_education"]',
        'gallery' => '[founder action="display_gallery"]',
        'gallery_urls' => '[founder id="gallery" format="urls"]',
        'additional_urls' => '[founder action="display_additional_urls"]',
        'additional_urls_json' => '[founder id="additional_urls" format="json"]',
        'organization_gallery' => '[organization field="gallery"]',
    ];
    $rendered = [];
    foreach ($shortcodes as $key => $shortcode) $rendered[$key] = do_shortcode($shortcode);
    $schema = [];
    if (function_exists(__NAMESPACE__ . '\\build_person_schema')) $schema['person'] = build_person_schema();
    if ($org && function_exists(__NAMESPACE__ . '\\build_organization_schema')) $schema['organization'] = build_organization_schema($org->ID);
    return [
        'generated_at' => current_time('c'),
        'plugin_version' => SFPF_PLUGIN_VERSION,
        'urls' => sfpf_profile_debug_urls(),
        'founder' => ['user_id' => $founder_id, 'name' => $founder_id ? get_the_author_meta('display_name', $founder_id) : '', 'gallery_count' => count(sfpf_normalize_gallery_images($person_gallery)), 'gallery' => sfpf_normalize_gallery_images($person_gallery)],
        'organization' => ['post_id' => $org ? $org->ID : 0, 'name' => $org ? get_the_title($org) : '', 'gallery_count' => count(sfpf_normalize_gallery_images($org_gallery)), 'gallery' => sfpf_normalize_gallery_images($org_gallery)],
        'mappings' => [
            ['scope' => 'person', 'notion_field' => 'Gallery', 'wordpress_field' => 'gallery', 'type' => 'ACF Gallery', 'shortcode' => '[founder action="display_gallery"]'],
            ['scope' => 'person', 'notion_field' => 'Additional URLs', 'wordpress_field' => 'additional_urls', 'type' => 'ACF Repeater', 'shortcode' => '[founder action="display_additional_urls"]'],
            ['scope' => 'company', 'notion_field' => 'Gallery', 'wordpress_field' => 'gallery', 'type' => 'Organization ACF Gallery', 'shortcode' => '[organization field="gallery"]'],
        ],
        'shortcodes' => $shortcodes,
        'rendered' => $rendered,
        'schema' => $schema,
    ];
}

function render_public_profile_debug_page() {
    if (!get_query_var('sfpf_profile_debug')) return;
    if (!is_user_logged_in() || !current_user_can('manage_options')) {
        status_header(404);
        nocache_headers();
        header('X-Robots-Tag: noindex, nofollow', true);
        exit;
    }
    $data = sfpf_profile_debug_data();
    status_header(200);
    nocache_headers();
    header('X-Robots-Tag: noindex, nofollow', true);
    if (isset($_GET['format']) && sanitize_key($_GET['format']) === 'json') {
        header('Content-Type: application/json; charset=utf-8');
        echo wp_json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        exit;
    }
    header('Content-Type: text/html; charset=utf-8');
    echo '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex,nofollow"><title>SFPF Profile Debug</title><style>body{font-family:system-ui,-apple-system,Segoe UI,sans-serif;margin:0;background:#f8fafc;color:#0f172a}.wrap{max-width:1120px;margin:0 auto;padding:32px 18px}.panel{background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:18px;margin:0 0 18px;box-shadow:0 12px 32px rgba(15,23,42,.06)}h1{font-size:28px;margin:0 0 6px}h2{font-size:16px;margin:0 0 12px;text-transform:uppercase;letter-spacing:.06em;color:#334155}code,pre{font-family:ui-monospace,SFMono-Regular,Menlo,monospace}table{border-collapse:collapse;width:100%;font-size:13px}td,th{border-bottom:1px solid #e2e8f0;padding:8px;text-align:left;vertical-align:top}.ok{color:#047857;font-weight:700}.miss{color:#be123c;font-weight:700}.url{word-break:break-all;color:#2563eb}.rendered{border:1px solid #e2e8f0;border-radius:8px;padding:14px;background:#f8fafc;overflow:auto}pre{white-space:pre-wrap;word-break:break-word;background:#0f172a;color:#e2e8f0;border-radius:8px;padding:14px;max-height:520px;overflow:auto}</style></head><body><main class="wrap">';
    echo '<div class="panel"><h1>SFPF Profile Debug</h1><div>Authenticated administrator diagnostic output for the SFPF plugin.</div><div style="margin-top:8px"><strong>Plugin:</strong> ' . esc_html($data['plugin_version']) . ' | <strong>Generated:</strong> ' . esc_html($data['generated_at']) . '</div></div>';
    echo '<section class="panel"><h2>Dynamic URLs</h2><table><tbody>';
    foreach ($data['urls'] as $label => $url) echo '<tr><th>' . esc_html($label) . '</th><td><a class="url" href="' . esc_url($url) . '" target="_blank" rel="noopener">' . esc_html($url) . '</a></td></tr>';
    echo '</tbody></table></section>';
    echo '<section class="panel"><h2>Gallery Status</h2><table><tbody>';
    echo '<tr><th>Founder</th><td>' . esc_html($data['founder']['name'] ?: 'Not set') . ' (user ' . (int) $data['founder']['user_id'] . ')</td><td class="' . ($data['founder']['gallery_count'] ? 'ok' : 'miss') . '">' . (int) $data['founder']['gallery_count'] . ' image(s)</td></tr>';
    echo '<tr><th>Organization</th><td>' . esc_html($data['organization']['name'] ?: 'Not set') . ' (post ' . (int) $data['organization']['post_id'] . ')</td><td class="' . ($data['organization']['gallery_count'] ? 'ok' : 'miss') . '">' . (int) $data['organization']['gallery_count'] . ' image(s)</td></tr>';
    echo '</tbody></table></section>';
    echo '<section class="panel"><h2>Field Mappings</h2><table><thead><tr><th>Scope</th><th>Notion</th><th>WordPress</th><th>Shortcode</th></tr></thead><tbody>';
    foreach ($data['mappings'] as $row) echo '<tr><td>' . esc_html($row['scope']) . '</td><td><code>' . esc_html($row['notion_field']) . '</code></td><td><code>' . esc_html($row['wordpress_field']) . '</code><br>' . esc_html($row['type']) . '</td><td><code>' . esc_html($row['shortcode']) . '</code></td></tr>';
    echo '</tbody></table></section>';
    echo '<section class="panel"><h2>Rendered Shortcodes</h2>';
    foreach ($data['shortcodes'] as $key => $shortcode) echo '<h3><code>' . esc_html($shortcode) . '</code></h3><div class="rendered">' . ($data['rendered'][$key] ?: '<span class="miss">No output</span>') . '</div>';
    echo '</section><section class="panel"><h2>Schema Snapshot</h2><pre>' . esc_html(wp_json_encode($data['schema'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) . '</pre></section>';
    echo '</main></body></html>';
    exit;
}
add_action('template_redirect', __NAMESPACE__ . '\\render_public_profile_debug_page', 0);
