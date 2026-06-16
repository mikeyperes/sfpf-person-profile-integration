<?php
/**
 * Plugin Name: SFPF Person Profile Integration
 * Plugin URI: https://seoforpublicfigures.com
 * Description: Personal website schema management, page structures, and content templates. Integrates with HWS Base Tools for website settings.
 * Version: 1.6.13
 * Author: SEO For Public Figures
 * Author URI: https://seoforpublicfigures.com
 * Text Domain: sfpf-person-profile-integration
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

namespace sfpf_person_website;

defined('ABSPATH') || exit;

/**
 * Plugin Constants
 */
define('SFPF_PLUGIN_VERSION', '1.6.13');
define('SFPF_PLUGIN_FILE', __FILE__);
define('SFPF_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SFPF_PLUGIN_URL', plugin_dir_url(__FILE__));
define('SFPF_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('SFPF_PROFILE_DEBUG_ROUTE', 'sfpf-profile-debug');

/**
 * Config Class
 */
class Config {
    public static $version = '1.6.13';
    public static $slug = 'sfpf-person-profile-integration';
    public static $text_domain = 'sfpf-person-profile-integration';
    public static $menu_slug = 'sfpf-person-profile';
    public static $plugin_folder_name = 'sfpf-person-profile-integration';
    public static $plugin_starter_file = 'initialization.php';
    public static $github_repo = 'mikeyperes/sfpf-person-profile-integration';
    public static $github_branch = 'main';

    public static function get_plugin_basename() {
        return self::$plugin_folder_name . '/' . self::$plugin_starter_file;
    }

    public static $snippets = [
        'book_cpt' => 'sfpf_enable_book_cpt',
        'book_acf' => 'sfpf_enable_book_acf',
        'organization_cpt' => 'sfpf_enable_organization_cpt',
        'organization_acf' => 'sfpf_enable_organization_acf',
        'testimonial_cpt' => 'sfpf_enable_testimonial_cpt',
        'user_schema_acf' => 'sfpf_enable_user_schema_acf',
        'homepage_acf' => 'sfpf_enable_homepage_acf',
    ];
}

// ============================================================================
// LOAD HELPER FILES IMMEDIATELY (before any hooks)
// ============================================================================
require_once SFPF_PLUGIN_DIR . 'includes/helper-functions.php';
require_once SFPF_PLUGIN_DIR . 'includes/logging.php';
require_once SFPF_PLUGIN_DIR . 'includes/snippets-loader.php';
require_once SFPF_PLUGIN_DIR . 'includes/elementor-social-icons.php';
require_once SFPF_PLUGIN_DIR . 'includes/elementor-display-conditions.php';

// ============================================================================
// CPT LOADING - Hook to init priority 0
// ============================================================================
function load_cpt_snippets() {
    $snippets_dir = SFPF_PLUGIN_DIR . 'snippets/';

    if (get_option('sfpf_enable_book_cpt', false)) {
        $file = $snippets_dir . 'register-cpt-book.php';
        if (file_exists($file)) require_once $file;
    }

    if (get_option('sfpf_enable_organization_cpt', false)) {
        $file = $snippets_dir . 'register-cpt-organization.php';
        if (file_exists($file)) require_once $file;
    }

    if (get_option('sfpf_enable_testimonial_cpt', false)) {
        $file = $snippets_dir . 'register-cpt-testimonial.php';
        if (file_exists($file)) require_once $file;
    }
}
add_action('init', __NAMESPACE__ . '\\load_cpt_snippets', 0);

// ============================================================================
// MAIN INIT - Hook to init priority 5
// ============================================================================
function init_plugin() {
    // Load schema files
    $schema_files = ['schema-templates.php', 'schema-builder.php', 'schema-manager.php', 'schema-injector.php'];
    foreach ($schema_files as $file) {
        $path = SFPF_PLUGIN_DIR . 'schema/' . $file;
        if (file_exists($path)) require_once $path;
    }

    // Enable schema injection on frontend
    if (!is_admin() && function_exists(__NAMESPACE__ . '\\enable_schema_injection')) {
        enable_schema_injection();
    }

    // Admin only
    if (is_admin()) {
        require_once SFPF_PLUGIN_DIR . 'admin/settings-dashboard.php';
        require_once SFPF_PLUGIN_DIR . 'admin/ajax-handlers.php';
        require_once SFPF_PLUGIN_DIR . 'admin/dashboard-plugin-info.php';
    }
}
add_action('init', __NAMESPACE__ . '\\init_plugin', 5);

// ============================================================================
// ACF FIELDS LOADING - Hook to acf/init
// ============================================================================
function load_acf_field_groups() {
    if (!function_exists('acf_add_local_field_group')) {
        return;
    }

    $snippets_dir = SFPF_PLUGIN_DIR . 'snippets/';

    // Book ACF
    if (get_option('sfpf_enable_book_acf', false)) {
        $file = $snippets_dir . 'register-acf-book.php';
        if (file_exists($file)) {
            require_once $file;
            register_book_acf_fields();
        }
    }

    // Organization ACF
    if (get_option('sfpf_enable_organization_acf', false)) {
        $file = $snippets_dir . 'register-acf-organization.php';
        if (file_exists($file)) {
            require_once $file;
            register_organization_acf_fields();
        }
    }

    // User Schema ACF (education, sameas, etc.)
    if (get_option('sfpf_enable_user_schema_acf', false)) {
        $file = $snippets_dir . 'register-acf-user-schema.php';
        if (file_exists($file)) {
            require_once $file;
            register_user_schema_acf_fields();
        }
    }

    // Homepage ACF
    if (get_option('sfpf_enable_homepage_acf', false)) {
        $file = $snippets_dir . 'register-acf-homepage.php';
        if (file_exists($file)) {
            require_once $file;
            register_homepage_acf_fields();
        }
    }
}
add_action('acf/init', __NAMESPACE__ . '\\load_acf_field_groups', 10);

/**
 * Plugin activation
 */
function activate_plugin() {
    // Set default options — add_option only writes if key doesn't exist yet
    add_option('sfpf_homepage_schema_type', 'person');
    add_option('sfpf_biography_schema_type', 'profile_page_only');
    add_option('sfpf_rankmath_disable_biography', false);
    add_option(SFPF_HIDE_EMPTY_ELEMENTOR_SOCIAL_ICONS_OPTION, 1);
    add_option(SFPF_ELEMENTOR_DYNAMIC_VISIBILITY_OPTION, 1);

    // Migration: fix sites that had the old 'none' default from previous activation bug
    $current_hp = get_option('sfpf_homepage_schema_type');
    if ($current_hp === 'none') {
        // Only auto-fix if the user hasn't explicitly saved a schema type yet
        // (check if the option was set by old activation code vs. user choice)
        $hp_was_explicitly_saved = get_option('sfpf_homepage_schema_explicitly_saved', false);
        if (!$hp_was_explicitly_saved) {
            update_option('sfpf_homepage_schema_type', 'person');
        }
    }

    // Flush rewrite rules
    flush_rewrite_rules();

    // Log activation
    if (function_exists(__NAMESPACE__ . '\\write_log')) {
        write_log('Plugin activated');
    }
}
register_activation_hook(__FILE__, __NAMESPACE__ . '\\activate_plugin');

/**
 * Plugin deactivation
 */
function deactivate_plugin() {
    // Flush rewrite rules
    flush_rewrite_rules();

    // Log deactivation
    if (function_exists(__NAMESPACE__ . '\\write_log')) {
        write_log('Plugin deactivated');
    }
}
register_deactivation_hook(__FILE__, __NAMESPACE__ . '\\deactivate_plugin');


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
            ['scope' => 'company', 'notion_field' => 'Gallery', 'wordpress_field' => 'gallery', 'type' => 'Organization ACF Gallery', 'shortcode' => '[organization field="gallery"]'],
        ],
        'shortcodes' => $shortcodes,
        'rendered' => $rendered,
        'schema' => $schema,
    ];
}

function render_public_profile_debug_page() {
    if (!get_query_var('sfpf_profile_debug')) return;
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
    echo '<div class="panel"><h1>SFPF Profile Debug</h1><div>Noindex public diagnostic output for the SFPF plugin.</div><div style="margin-top:8px"><strong>Plugin:</strong> ' . esc_html($data['plugin_version']) . ' | <strong>Generated:</strong> ' . esc_html($data['generated_at']) . '</div></div>';
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

/**
 * Add settings link on plugins page
 */
function add_settings_link($links) {
    $settings_link = '<a href="' . admin_url('options-general.php?page=sfpf-person-profile') . '">Settings</a>';
    array_unshift($links, $settings_link);
    return $links;
}
add_filter('plugin_action_links_' . SFPF_PLUGIN_BASENAME, __NAMESPACE__ . '\\add_settings_link');

/**
 * Check for required plugins
 */
function check_requirements() {
    if (!is_admin()) {
        return;
    }

    // Check for ACF
    if (!class_exists('ACF') && !function_exists('get_field')) {
        add_action('admin_notices', function() {
            echo '<div class="notice notice-warning"><p><strong>SFPF Person Profile Integration:</strong> Advanced Custom Fields (ACF) plugin is recommended for full functionality.</p></div>';
        });
    }
}
add_action('admin_init', __NAMESPACE__ . '\\check_requirements');

/**
 * Clean up duplicate database-stored ACF field groups on user profiles
 *
 * The duplicate Entity Type (field_hws_entity_type) and Education fields are stored
 * in the WordPress database as ACF field groups. This migration finds and removes them.
 * Our code-registered groups (via acf_add_local_field_group) are NOT in wp_posts.
 */
function cleanup_duplicate_acf_groups() {
    $cleanup_version = '1.6.1';

    // Force re-run: delete old versions to ensure this always executes after update
    $current = get_option('sfpf_acf_cleanup_version', '');
    if ($current === $cleanup_version) {
        return;
    }
    // Delete any old version markers to force fresh run
    delete_option('sfpf_acf_cleanup_version');

    global $wpdb;
    $removed = [];

    // ── STRATEGY 1: Find specific known dud field keys and trace to parent groups ──
    $dud_field_keys = [
        'field_hws_entity_type',
        'field_hws_education',
        'field_hws_biography',
        'field_hws_biography_short',
        'field_hws_title',
        'field_hws_professions',
        'field_hws_sameas',
        'field_hws_additional_name',
        'field_hws_alternate_names',
        'field_hws_knowledge_graph_images',
        'field_hws_inception_date',
        'field_hws_headquarters',
    ];

    foreach ($dud_field_keys as $field_key) {
        $field_post = $wpdb->get_row($wpdb->prepare(
            "SELECT ID, post_parent FROM {$wpdb->posts} WHERE post_type = 'acf-field' AND post_name = %s LIMIT 1",
            $field_key
        ));

        if ($field_post && $field_post->post_parent > 0) {
            $group_id = $field_post->post_parent;
            if (!in_array($group_id, $removed)) {
                sfpf_delete_acf_group_recursively($wpdb, $group_id);
                $removed[] = $group_id;
            }
        }
    }

    // ── STRATEGY 2: Find any DB groups with conflicting field names targeting users ──
    $all_groups = $wpdb->get_results(
        "SELECT ID, post_title, post_excerpt, post_content FROM {$wpdb->posts}
         WHERE post_type = 'acf-field-group'
         AND post_status IN ('publish', 'acf-disabled', 'draft', 'trash', 'private')"
    );

    if (!empty($all_groups)) {
        foreach ($all_groups as $group) {
            if (in_array($group->ID, $removed)) continue;

            $dominated = false;

            // Check if this group targets user profiles
            $targets_users = (strpos($group->post_content, 'user_form') !== false
                           || strpos($group->post_content, 'user_role') !== false);

            if ($targets_users) {
                // Check child fields for our field names
                $child_names = $wpdb->get_col($wpdb->prepare(
                    "SELECT post_excerpt FROM {$wpdb->posts} WHERE post_parent = %d AND post_type = 'acf-field'",
                    $group->ID
                ));

                $our_names = ['entity_type', 'education', 'biography', 'biography_short',
                              'title', 'professions', 'additional_name', 'alternate_names',
                              'knowledge_graph_images', 'sameas', 'inception_date', 'headquarters'];

                foreach ($child_names as $cn) {
                    if (in_array($cn, $our_names, true)) {
                        $dominated = true;
                        break;
                    }
                }
            }

            // Match by known group keys or titles
            if (in_array($group->post_excerpt, ['group_sfpf_user_schema_structures', 'group_sfpf_organization', 'group_hws_user_schema'], true)) {
                $dominated = true;
            }
            if (in_array($group->post_title, ['Schema.org Structured Data', 'Organization Details'], true)) {
                $dominated = true;
            }

            if ($dominated) {
                sfpf_delete_acf_group_recursively($wpdb, $group->ID);
                $removed[] = $group->ID;
            }
        }
    }

    // ── STRATEGY 3: Orphan field cleanup - find any stray acf-field posts with hws keys ──
    $wpdb->query(
        "DELETE FROM {$wpdb->posts} WHERE post_type = 'acf-field' AND post_name LIKE 'field_hws_%'"
    );

    if (!empty($removed) && function_exists(__NAMESPACE__ . '\\write_log')) {
        write_log("ACF Cleanup: Removed " . count($removed) . " duplicate DB group(s): IDs " . implode(', ', $removed));
    }

    update_option('sfpf_acf_cleanup_version', $cleanup_version);
}

/**
 * Recursively delete an ACF field group and all its children from the database
 */
function sfpf_delete_acf_group_recursively($wpdb, $group_id) {
    // Get all child fields
    $children = $wpdb->get_col($wpdb->prepare(
        "SELECT ID FROM {$wpdb->posts} WHERE post_parent = %d AND post_type = 'acf-field'",
        $group_id
    ));

    foreach ($children as $child_id) {
        // Delete grandchildren (sub-fields of repeaters/groups)
        $grandchildren = $wpdb->get_col($wpdb->prepare(
            "SELECT ID FROM {$wpdb->posts} WHERE post_parent = %d AND post_type = 'acf-field'",
            $child_id
        ));
        foreach ($grandchildren as $gc_id) {
            // Great-grandchildren
            $wpdb->query($wpdb->prepare(
                "DELETE FROM {$wpdb->posts} WHERE post_parent = %d AND post_type = 'acf-field'", $gc_id
            ));
            $wpdb->delete($wpdb->posts, ['ID' => $gc_id]);
            $wpdb->delete($wpdb->postmeta, ['post_id' => $gc_id]);
        }
        $wpdb->delete($wpdb->posts, ['ID' => $child_id]);
        $wpdb->delete($wpdb->postmeta, ['post_id' => $child_id]);
    }

    // Delete the group itself
    $wpdb->delete($wpdb->postmeta, ['post_id' => $group_id]);
    $wpdb->delete($wpdb->posts, ['ID' => $group_id]);
}

add_action('admin_init', __NAMESPACE__ . '\\cleanup_duplicate_acf_groups', 1);

/**
 * Migrate articles field from textarea (string) to repeater (array)
 * Runs once per user if old format detected
 */
function migrate_articles_textarea_to_repeater() {
    if (get_option('sfpf_articles_migration_done', false)) {
        return;
    }

    $founder_id = get_founder_user_id();
    if (!$founder_id) {
        return;
    }

    $articles = get_field('articles', 'user_' . $founder_id);

    // Only migrate if it's a string (old textarea format)
    if (!is_string($articles) || empty(trim($articles))) {
        update_option('sfpf_articles_migration_done', true);
        return;
    }

    $urls = array_filter(array_map('trim', explode("\n", $articles)));
    if (empty($urls)) {
        update_option('sfpf_articles_migration_done', true);
        return;
    }

    $repeater = [];
    foreach ($urls as $url) {
        if (!filter_var($url, FILTER_VALIDATE_URL)) continue;
        $parsed = wp_parse_url($url);
        $source = preg_replace('/^www\./', '', $parsed['host'] ?? '');
        $repeater[] = [
            'title' => '',
            'source' => $source,
            'url' => $url,
        ];
    }

    if (!empty($repeater)) {
        update_field('articles', $repeater, 'user_' . $founder_id);
        write_log("Migrated " . count($repeater) . " articles from textarea to repeater for user {$founder_id}");
    }

    update_option('sfpf_articles_migration_done', true);
}
add_action('admin_init', __NAMESPACE__ . '\\migrate_articles_textarea_to_repeater', 20);

/**
 * Runtime ACF field filter:
 * 1. Block duplicate hws-prefixed entity_type/education fields
 * 2. Enrich Education History with LinkedIn/Crunchbase links
 */
add_filter('acf/prepare_field', function($field) {
    if (!$field || !is_array($field)) return $field;

    // Block duplicate entity_type / education fields with old hws prefix
    if (isset($field['key']) && strpos($field['key'], 'field_hws_') === 0) {
        return false;
    }

    // Enrich Education History with LinkedIn/Crunchbase links
    if (isset($field['key']) && $field['key'] === 'field_sfpf_education_repeater') {
        $screen = function_exists('get_current_screen') ? get_current_screen() : null;
        if ($screen && ($screen->id === 'profile' || $screen->id === 'user-edit')) {
            $user_id = defined('IS_PROFILE_PAGE') && IS_PROFILE_PAGE ? get_current_user_id() : (isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0);
            if ($user_id) {
                $urls = get_field('urls', 'user_' . $user_id);
                $links = [];
                if (!empty($urls['linkedin'])) {
                    $links[] = '<a href="' . esc_url($urls['linkedin']) . '" target="_blank" style="color:#0a66c2;">LinkedIn ↗</a>';
                }
                if (!empty($urls['crunchbase'])) {
                    $links[] = '<a href="' . esc_url($urls['crunchbase']) . '" target="_blank" style="color:#0288d1;">Crunchbase ↗</a>';
                }
                if (!empty($links)) {
                    $field['instructions'] .= '<br><span style="color:#6b7280;">Profile: ' . implode(' &nbsp;|&nbsp; ', $links) . '</span>';
                }
            }
        }
    }

    return $field;
});

// ═══════════════════════════════════════════════════════════════════════════
// ACF REPEATER HYDRATION FIX (profile.php / user-edit.php)
//
// On user profile screens ACF's repeater load pipeline sometimes returns
// arrays with the correct row count but empty subfield values ("shell rows").
// When the form renders blank and gets submitted, ACF overwrites real data
// with empty strings.
//
// Fix has three layers:
//   1. acf/load_value   – rebuild from usermeta if value has no actual data
//   2. acf/prepare_field – render-stage injection (ACF may skip load_value)
//   3. acf/update_value  – save guard to prevent blank overwrites
//
// CRITICAL: ACF mutates $field['name'] during prepare_field to
// "acf[field_key]", so we NEVER use $field['name'] for meta lookups.
// Instead we use a hardcoded key→meta_name mapping.
// ═══════════════════════════════════════════════════════════════════════════

/**
 * Detect WordPress user profile screens via $pagenow.
 * get_current_screen() is unreliable in ACF hook contexts (too early/undefined).
 */
function sfpf_is_user_profile_screen() {
    if (!is_admin()) return false;
    $pagenow = $GLOBALS['pagenow'] ?? '';
    if (in_array($pagenow, ['profile.php', 'user-edit.php'], true)) return true;
    if (defined('IS_PROFILE_PAGE') && IS_PROFILE_PAGE) return true;
    $a = current_action();
    if (in_array($a, ['show_user_profile', 'edit_user_profile', 'personal_options_update', 'edit_user_profile_update'], true)) return true;
    return false;
}

/**
 * Get the user ID being edited on profile/user-edit screens.
 */
function sfpf_get_profile_target_user_id() {
    if (($GLOBALS['pagenow'] ?? '') === 'profile.php') {
        return (int) get_current_user_id();
    }
    if (isset($_GET['user_id'])) {
        return (int) $_GET['user_id'];
    }
    return 0;
}

/**
 * Map ACF field keys to their real usermeta base names.
 *
 * ACF mutates $field['name'] during prepare_field (e.g. "acf[field_sfpf_education_repeater]")
 * so we can NEVER trust it for building meta keys like "education_0_college".
 * This mapping is the single source of truth.
 */
function sfpf_repeater_meta_name($field) {
    static $map = [
        'field_sfpf_education_repeater'  => 'education',
        'field_sfpf_professions_repeater' => 'professions',
        'field_sfpf_alternate_names'     => 'alternate_names',
        'field_sfpf_nationality'         => 'nationality',
        'field_sfpf_knows_language'      => 'knows_language',
        'field_sfpf_awards'              => 'awards',
        'field_sfpf_articles'            => 'articles',
    ];
    $key = $field['key'] ?? '';
    return $map[$key] ?? '';
}

/**
 * Check whether a repeater value array has any actual subfield data.
 *
 * ACF can return an array with N rows but all subfields empty ("shell rows").
 * This returns true only if at least one row has one non-empty subfield.
 */
function sfpf_repeater_value_has_data($value, $field) {
    if (!is_array($value) || empty($value)) return false;
    $sub_fields = $field['sub_fields'] ?? [];
    if (empty($sub_fields)) return false;

    foreach ($value as $row) {
        if (!is_array($row)) continue;
        foreach ($sub_fields as $sf) {
            $v = $row[$sf['key']] ?? ($row[$sf['name']] ?? '');
            if ($v !== '' && $v !== null) return true;
        }
    }
    return false;
}

/**
 * Rebuild a repeater's value from usermeta.
 *
 * Reads the row count from get_user_meta($uid, $meta_name, true),
 * then reads each subfield: {meta_name}_{i}_{subfield_name}.
 */
function sfpf_rebuild_repeater_from_usermeta($user_id, $meta_name, $field) {
    $count = (int) get_user_meta($user_id, $meta_name, true);
    if ($count <= 0) return [];

    $sub_fields = $field['sub_fields'] ?? [];
    if (empty($sub_fields)) return [];

    $rows = [];
    for ($i = 0; $i < $count; $i++) {
        $row = [];
        foreach ($sub_fields as $sf) {
            $mk = "{$meta_name}_{$i}_{$sf['name']}";
            $row[$sf['key']] = get_user_meta($user_id, $mk, true);
        }
        $rows[] = $row;
    }
    return $rows;
}

/**
 * Normalize ACF's post_id to a numeric user ID.
 * ACF passes "user_3" or "3" depending on context.
 */
function sfpf_normalize_user_id($post_id) {
    if (is_string($post_id) && strpos($post_id, 'user_') === 0) {
        return (int) substr($post_id, 5);
    }
    if (is_numeric($post_id)) {
        return (int) $post_id;
    }
    return 0;
}

/**
 * acf/load_value handler for all repeaters.
 *
 * If ACF returns shell rows (correct count but empty subfields),
 * rebuild from usermeta. Uses key→meta_name mapping, not $field['name'].
 */
function sfpf_fix_repeater_load_value($value, $post_id, $field) {
    if (!sfpf_is_user_profile_screen()) return $value;

    $user_id = sfpf_normalize_user_id($post_id);
    if ($user_id <= 0) return $value;

    $meta_name = sfpf_repeater_meta_name($field);
    if (empty($meta_name)) return $value;

    // Only keep existing value if it has ACTUAL data (not shell rows)
    if (sfpf_repeater_value_has_data($value, $field)) {
        return $value;
    }

    return sfpf_rebuild_repeater_from_usermeta($user_id, $meta_name, $field);
}

/**
 * acf/update_value save guard for all repeaters.
 *
 * If a subfield arrives empty but DB has a value, keep the DB value.
 * Trims fully empty rows to prevent row count inflation.
 */
function sfpf_repeater_save_guard($value, $post_id, $field) {
    if (!sfpf_is_user_profile_screen()) return $value;

    $user_id = sfpf_normalize_user_id($post_id);
    if ($user_id <= 0) return $value;
    if (!is_array($value)) return $value;

    $sub_fields = $field['sub_fields'] ?? [];
    if (empty($sub_fields)) return $value;

    $meta_name = sfpf_repeater_meta_name($field);
    if (empty($meta_name)) return $value;

    $merged = [];
    foreach ($value as $i => $row) {
        if (!is_array($row)) $row = [];

        foreach ($sub_fields as $sf) {
            $incoming = $row[$sf['key']] ?? '';
            if ($incoming === '' || $incoming === null) {
                $existing = get_user_meta($user_id, "{$meta_name}_{$i}_{$sf['name']}", true);
                if ($existing !== '' && $existing !== null) {
                    $row[$sf['key']] = $existing;
                }
            }
        }

        // Only keep rows with at least one non-empty value
        $all_empty = true;
        foreach ($sub_fields as $sf) {
            if (($row[$sf['key']] ?? '') !== '') { $all_empty = false; break; }
        }
        if (!$all_empty) {
            $merged[] = $row;
        }
    }

    return $merged;
}

// Register all hooks for each repeater
$sfpf_repeater_keys = [
    'field_sfpf_education_repeater',
    'field_sfpf_professions_repeater',
    'field_sfpf_alternate_names',
    'field_sfpf_nationality',
    'field_sfpf_knows_language',
    'field_sfpf_awards',
    'field_sfpf_articles',
];

foreach ($sfpf_repeater_keys as $rk) {
    // Layer 1: Rebuild on load if shell rows
    add_filter("acf/load_value/key={$rk}", __NAMESPACE__ . '\\sfpf_fix_repeater_load_value', 20, 3);

    // Layer 2: Render-stage injection (ACF may skip load_value on profile.php)
    add_filter("acf/prepare_field/key={$rk}", function($field) {
        if (!$field || !is_array($field)) return $field;
        if (!sfpf_is_user_profile_screen()) return $field;

        $user_id = sfpf_get_profile_target_user_id();
        if ($user_id <= 0) return $field;

        $meta_name = sfpf_repeater_meta_name($field);
        if (empty($meta_name)) return $field;

        // Only inject if current value has no actual data
        if (!sfpf_repeater_value_has_data($field['value'] ?? null, $field)) {
            $rebuilt = sfpf_rebuild_repeater_from_usermeta($user_id, $meta_name, $field);
            if (!empty($rebuilt)) {
                $field['value'] = $rebuilt;
            }
        }

        return $field;
    }, 50);

    // Layer 3: Save guard
    add_filter("acf/update_value/key={$rk}", __NAMESPACE__ . '\\sfpf_repeater_save_guard', 1, 3);
}

/**
 * Block duplicate ACF field groups from loading.
 * Prevents DB-stored copies from overriding code-registered groups.
 */
add_filter('acf/load_field_groups', function($field_groups) {
    if (!is_array($field_groups)) return $field_groups;

    $blocked_prefixes = ['group_hws_', 'group_sfpf_'];

    return array_filter($field_groups, function($group) use ($blocked_prefixes) {
        if (!isset($group['key'])) return true;
        $is_db = isset($group['ID']) && $group['ID'] > 0;
        if (!$is_db) return true;
        foreach ($blocked_prefixes as $prefix) {
            if (strpos($group['key'], $prefix) === 0) return false;
        }
        return true;
    });
});

// ============================================================================
// FAQ SHORTCODES (Sets-based structure)
// ============================================================================

/**
 * Get FAQ set by slug
 */
function get_faq_set_by_slug($slug) {
    $faq_sets = get_option('sfpf_faq_sets', []);

    // "primary" resolves to the designated primary set, or the first set
    if ($slug === 'primary') {
        $primary_slug = get_option('sfpf_primary_faq_set', '');
        if ($primary_slug) {
            foreach ($faq_sets as $set) {
                if (($set['slug'] ?? '') === $primary_slug) {
                    return $set;
                }
            }
        }
        // Fallback: return first set
        return !empty($faq_sets) ? $faq_sets[0] : null;
    }

    foreach ($faq_sets as $set) {
        if (($set['slug'] ?? '') === $slug) {
            return $set;
        }
    }
    return null;
}

/**
 * FAQ shortcode
 * [sfpf_faq set="slug"] - All FAQs from a set
 * [sfpf_faq set="slug" index="0"] - Single FAQ from a set
 * [sfpf_faq set="slug" style="accordion"] - Accordion style
 */
function sfpf_faq_shortcode($atts) {
    $atts = shortcode_atts([
        'set' => '',
        'index' => null,
        'style' => 'list', // list, accordion
    ], $atts);

    if (empty($atts['set'])) {
        return '<!-- SFPF FAQ: No set specified -->';
    }

    $set = get_faq_set_by_slug($atts['set']);
    if (!$set || empty($set['items'])) {
        return '<!-- SFPF FAQ: Set not found or empty -->';
    }

    $items = $set['items'];

    // Single item
    if ($atts['index'] !== null) {
        $index = intval($atts['index']);
        if (!isset($items[$index])) {
            return '';
        }
        $faq = $items[$index];
        return '<div class="sfpf-faq-single" data-set="' . esc_attr($atts['set']) . '" data-index="' . $index . '">
            <div class="sfpf-faq-question" style="font-weight:600;font-size:16px;margin-bottom:8px;">' . esc_html($faq['question']) . '</div>
            <div class="sfpf-faq-answer">' . wp_kses_post($faq['answer']) . '</div>
        </div>';
    }

    // Multiple items
    if ($atts['style'] === 'accordion') {
        return render_faq_accordion($set, $items);
    }

    // Default list style - collapsible, all closed on load
    $html = '<div class="sfpf-faq-list" data-set="' . esc_attr($atts['set']) . '">';
    foreach ($items as $i => $faq) {
        if (!empty($faq['question'])) {
            $html .= '<div class="sfpf-faq-item" style="margin-bottom:12px;background:#f9fafb;border-radius:8px;border:1px solid #e5e7eb;overflow:hidden;">';
            $html .= '<div role="button" tabindex="0" class="sfpf-faq-toggle" style="width:100%;padding:16px 20px;background:transparent;border:none;text-align:left;cursor:pointer;display:flex !important;justify-content:space-between;align-items:center;box-sizing:border-box;-webkit-appearance:none;appearance:none;font-family:inherit;line-height:1.4;" onclick="var c=this.nextElementSibling;var icon=this.querySelector(\'.sfpf-toggle-icon\');if(c.style.display===\'none\'||c.style.display===\'\'){c.style.display=\'block\';icon.textContent=\'−\';this.parentElement.classList.add(\'open\');}else{c.style.display=\'none\';icon.textContent=\'+\';this.parentElement.classList.remove(\'open\');}">';
            $html .= '<span style="font-weight:600;font-size:16px;color:#1e1e1e !important;display:block;">' . esc_html($faq['question']) . '</span>';
            $html .= '<span class="sfpf-toggle-icon" style="font-size:20px;color:#6b7280;flex-shrink:0;margin-left:12px;">+</span>';
            $html .= '</div>';
            $html .= '<div class="sfpf-faq-answer" style="display:none;padding:0 20px 16px;color:#4b5563;line-height:1.6;">' . wp_kses_post($faq['answer']) . '</div>';
            $html .= '</div>';
        }
    }
    $html .= '</div>';

    // Inject schema if enabled
    if (get_option('sfpf_inject_faq_schema', true)) {
        $html .= render_faq_schema($items);
    }

    return $html;
}
add_shortcode('sfpf_faq', __NAMESPACE__ . '\\sfpf_faq_shortcode');

/**
 * Render FAQ as accordion
 */
function render_faq_accordion($set, $items) {
    $html = '<div class="sfpf-faq-accordion" data-set="' . esc_attr($set['slug']) . '" style="border:1px solid #e5e7eb;border-radius:8px;overflow:hidden;">';

    foreach ($items as $i => $faq) {
        if (!empty($faq['question'])) {
            $html .= '<div class="sfpf-accordion-item" style="border-bottom:1px solid #e5e7eb;">';
            $html .= '<div role="button" tabindex="0" class="sfpf-accordion-trigger" style="width:100%;padding:15px 20px;background:#fff;border:none;text-align:left;cursor:pointer;display:flex !important;justify-content:space-between;align-items:center;font-weight:600;font-size:15px;box-sizing:border-box;font-family:inherit;line-height:1.4;" onclick="var c=this.nextElementSibling;var icon=this.querySelector(\'.sfpf-accordion-icon\');if(c.style.display===\'none\'||c.style.display===\'\'){c.style.display=\'block\';icon.textContent=\'−\';this.parentElement.classList.add(\'open\');}else{c.style.display=\'none\';icon.textContent=\'+\';this.parentElement.classList.remove(\'open\');}">';
            $html .= '<span style="color:#1e1e1e !important;display:block;">' . esc_html($faq['question']) . '</span>';
            $html .= '<span class="sfpf-accordion-icon" style="font-size:20px;transition:transform 0.2s;flex-shrink:0;margin-left:12px;">+</span>';
            $html .= '</div>';
            $html .= '<div class="sfpf-accordion-content" style="display:none;padding:15px 20px;background:#f9fafb;border-top:1px solid #e5e7eb;">';
            $html .= wp_kses_post($faq['answer']);
            $html .= '</div>';
            $html .= '</div>';
        }
    }

    $html .= '</div>';

    // Inject schema if enabled
    if (get_option('sfpf_inject_faq_schema', true)) {
        $html .= render_faq_schema($items);
    }

    return $html;
}

/**
 * Render FAQ schema
 */
function render_faq_schema($items) {
    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => [],
    ];

    foreach ($items as $faq) {
        if (!empty($faq['question']) && !empty($faq['answer'])) {
            $schema['mainEntity'][] = [
                '@type' => 'Question',
                'name' => $faq['question'],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => wp_strip_all_tags($faq['answer']),
                ],
            ];
        }
    }

    if (empty($schema['mainEntity'])) {
        return '';
    }

    return '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>';
}

/**
 * FAQ Schema only shortcode
 * [sfpf_faq_schema set="slug"]
 */
function sfpf_faq_schema_shortcode($atts) {
    $atts = shortcode_atts(['set' => ''], $atts);

    if (empty($atts['set'])) {
        return '';
    }

    $set = get_faq_set_by_slug($atts['set']);
    if (!$set || empty($set['items'])) {
        return '';
    }

    return render_faq_schema($set['items']);
}
add_shortcode('sfpf_faq_schema', __NAMESPACE__ . '\\sfpf_faq_schema_shortcode');

/**
 * Elementor FAQ integration shortcode
 * [sfpf_elementor_faq set="slug" target=".elementor-accordion"]
 *
 * Injects JavaScript that populates Elementor accordion widgets with FAQ content
 */
function sfpf_elementor_faq_shortcode($atts) {
    $atts = shortcode_atts([
        'set' => '',
        'target' => '.elementor-accordion',
    ], $atts);

    if (empty($atts['set'])) {
        return '<!-- SFPF Elementor FAQ: No set specified -->';
    }

    $set = get_faq_set_by_slug($atts['set']);
    if (!$set || empty($set['items'])) {
        return '<!-- SFPF Elementor FAQ: Set not found or empty -->';
    }

    $items = $set['items'];
    $target = esc_js($atts['target']);

    // Prepare FAQ data for JavaScript
    $faq_data = [];
    foreach ($items as $faq) {
        if (!empty($faq['question'])) {
            $faq_data[] = [
                'question' => $faq['question'],
                'answer' => $faq['answer'],
            ];
        }
    }

    $json_data = wp_json_encode($faq_data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    $html = '<script>
(function() {
    var faqData = ' . $json_data . ';
    var targetSelector = "' . $target . '";

    function populateElementorAccordion() {
        var accordion = null;
        var el = document.querySelector(targetSelector);

        if (!el) {
            console.log("SFPF FAQ: Element not found with selector:", targetSelector);
            return;
        }

        // Check if this element IS the accordion
        if (el.classList.contains("elementor-accordion")) {
            accordion = el;
        }
        // Check if the accordion is inside this element (user added class to widget wrapper)
        if (!accordion) {
            accordion = el.querySelector(".elementor-accordion");
        }
        // Check if this element has accordion items directly
        if (!accordion && el.querySelector(".elementor-accordion-item")) {
            accordion = el;
        }

        if (!accordion) {
            console.log("SFPF FAQ: No .elementor-accordion found in or at:", targetSelector);
            return;
        }

        var items = accordion.querySelectorAll(".elementor-accordion-item");

        faqData.forEach(function(faq, index) {
            if (items[index]) {
                var title = items[index].querySelector(".elementor-accordion-title");
                if (title) {
                    title.textContent = faq.question;
                }
                var content = items[index].querySelector(".elementor-tab-content");
                if (content) {
                    content.innerHTML = faq.answer;
                }
            }
        });

        console.log("SFPF FAQ: Populated " + Math.min(faqData.length, items.length) + " accordion items");
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", populateElementorAccordion);
    } else {
        setTimeout(populateElementorAccordion, 100);
    }

    if (typeof jQuery !== "undefined") {
        jQuery(window).on("elementor/frontend/init", function() {
            setTimeout(populateElementorAccordion, 500);
        });
    }
})();
</script>';

    // Inject schema
    if (get_option('sfpf_inject_faq_schema', true)) {
        $html .= render_faq_schema($items);
    }

    return $html;
}
add_shortcode('sfpf_elementor_faq', __NAMESPACE__ . '\\sfpf_elementor_faq_shortcode');

// ============================================================================
// GOOGLE KNOWLEDGE GRAPH ID AUTO-EXTRACTION
// ============================================================================

/**
 * Auto-extract KGMID from full Google URL when saving the field
 * If user pastes full URL like https://www.google.com/search?kgmid=/g/11gyz2y3lp&...
 * we extract just /g/11gyz2y3lp
 */
function sanitize_kgid_on_save($value, $post_id, $field) {
    if (empty($value) || !is_string($value)) return $value;
    $value = trim($value);

    // If it looks like a full URL, extract kgmid parameter
    if (preg_match('/kgmid=([^&\s]+)/i', $value, $m)) {
        $value = urldecode($m[1]);
    }

    // Clean up - should start with /
    $value = trim($value);
    if (!empty($value) && $value[0] !== '/') {
        // If just the ID without leading slash
        if (preg_match('#^[a-z]/[a-zA-Z0-9_]+$#', $value)) {
            $value = '/' . $value;
        }
    }

    return $value;
}
add_filter('acf/update_value/name=knowledge_graph_id', __NAMESPACE__ . '\\sanitize_kgid_on_save', 10, 3);

// ============================================================================
// RANKMATH SCHEMA CONTROL
// ============================================================================

/**
 * Disable RankMath schema on specific post types
 */
function disable_rankmath_schema($data) {
    if (is_front_page() && get_option('sfpf_rankmath_disable_homepage', false)) {
        return [];
    }

    $bio_page_id = (int) get_option('sfpf_page_biography', 0);
    if ($bio_page_id && is_page($bio_page_id) && get_option('sfpf_rankmath_disable_biography', false)) {
        return [];
    }

    if (is_singular('book') && get_option('sfpf_rankmath_disable_books', false)) {
        return [];
    }

    if (is_singular('organization') && get_option('sfpf_rankmath_disable_organizations', false)) {
        return [];
    }

    if (is_singular('testimonial') && get_option('sfpf_rankmath_disable_testimonials', false)) {
        return [];
    }

    return $data;
}
add_filter('rank_math/json_ld', __NAMESPACE__ . '\\disable_rankmath_schema', 999);

// ============================================================================
// RANKMATH BREADCRUMB VISIBILITY CONTROL
// ============================================================================

/**
 * Hide RankMath breadcrumbs on front page, excluded pages, and excluded CPT singles
 * Hooks into the breadcrumb HTML output and returns empty string to hide
 */
function filter_rankmath_breadcrumbs($html) {
    // Hide on front page
    if (is_front_page() && get_option('sfpf_breadcrumb_hide_frontpage', false)) {
        return '';
    }

    // Hide on excluded pages
    $excluded_pages = get_option('sfpf_breadcrumb_excluded_pages', []);
    if (is_array($excluded_pages) && !empty($excluded_pages) && is_page()) {
        $current_id = get_queried_object_id();
        if (in_array($current_id, array_map('intval', $excluded_pages))) {
            return '';
        }
    }

    // Hide on excluded CPT singles
    $excluded_cpts = get_option('sfpf_breadcrumb_excluded_cpts', []);
    if (is_array($excluded_cpts) && !empty($excluded_cpts) && is_singular()) {
        $current_type = get_post_type();
        if (in_array($current_type, $excluded_cpts)) {
            return '';
        }
    }

    return $html;
}
add_filter('rank_math/frontend/breadcrumb/html', __NAMESPACE__ . '\\filter_rankmath_breadcrumbs', 10);

// ============================================================================
// ELEMENTOR LOOP SHORTCODE
// ============================================================================

/**
 * Elementor Loop Shortcode
 * [sfpf_loop cpt="book" columns="3" rows="2" responsive="true"]
 *
 * Displays posts using assigned Elementor Loop Item template
 */
function sfpf_loop_shortcode($atts) {
    $atts = shortcode_atts([
        'cpt' => 'book',
        'columns' => '3',
        'rows' => '',
        'responsive' => 'true',
    ], $atts);

    $cpt = sanitize_key($atts['cpt']);
    $columns = intval($atts['columns']) ?: 3;
    $responsive = $atts['responsive'] === 'true';
    $rows = !empty($atts['rows']) ? intval($atts['rows']) : 0;

    // Get assigned loop template
    $assignments = get_option('sfpf_elementor_loop_assignments', []);
    $template_id = $assignments[$cpt] ?? 0;

    if (!$template_id) {
        return '<!-- SFPF Loop: No template assigned for ' . esc_html($cpt) . ' -->';
    }

    // Calculate posts per page
    $posts_per_page = $rows > 0 ? ($columns * $rows) : -1;

    // Get posts
    $posts = get_posts([
        'post_type' => $cpt,
        'posts_per_page' => $posts_per_page,
        'post_status' => 'publish',
        'orderby' => 'date',
        'order' => 'DESC',
    ]);

    if (empty($posts)) {
        return '<!-- SFPF Loop: No ' . esc_html($cpt) . ' posts found -->';
    }

    // Build responsive styles
    $grid_styles = "display:grid;grid-template-columns:repeat({$columns}, 1fr);gap:20px;";

    if ($responsive) {
        $grid_styles .= "max-width:100%;";
    }

    $html = '<div class="sfpf-loop sfpf-loop-' . esc_attr($cpt) . '" style="' . $grid_styles . '">';

    // Check if Elementor is available
    if (class_exists('\\Elementor\\Plugin')) {
        $elementor = \Elementor\Plugin::instance();

        foreach ($posts as $post) {
            // Set up post data
            setup_postdata($post);

            // Render the loop item template
            $html .= '<div class="sfpf-loop-item">';

            // Use Elementor to render the template with current post context
            if (method_exists($elementor->frontend, 'get_builder_content_for_display')) {
                $html .= $elementor->frontend->get_builder_content_for_display($template_id, true);
            } else {
                $html .= $elementor->frontend->get_builder_content($template_id, true);
            }

            $html .= '</div>';
        }

        wp_reset_postdata();
    } else {
        // Fallback without Elementor
        foreach ($posts as $post) {
            $html .= '<div class="sfpf-loop-item" style="padding:15px;background:#f9fafb;border-radius:6px;">';
            $html .= '<h4 style="margin:0 0 10px;">' . esc_html($post->post_title) . '</h4>';
            $html .= '<p style="margin:0;color:#666;font-size:13px;">' . wp_trim_words($post->post_excerpt ?: $post->post_content, 20) . '</p>';
            $html .= '</div>';
        }
    }

    $html .= '</div>';

    // Add responsive CSS
    if ($responsive) {
        $html .= '<style>
        @media (max-width: 768px) {
            .sfpf-loop-' . esc_attr($cpt) . ' { grid-template-columns: repeat(2, 1fr) !important; }
        }
        @media (max-width: 480px) {
            .sfpf-loop-' . esc_attr($cpt) . ' { grid-template-columns: 1fr !important; }
        }
        </style>';
    }

    return $html;
}
add_shortcode('sfpf_loop', __NAMESPACE__ . '\\sfpf_loop_shortcode');

// =============================================================================
// ORGANIZATION SHORTCODE
// =============================================================================

/**
 * Organization shortcode: [organization field="name"]
 *
 * Attributes:
 * - field: (required) The ACF field name to retrieve
 * - id: (optional) Specific organization ID, defaults to primary organization
 * - link: (optional) "true" to wrap URL in anchor tag
 * - target: (optional) Target for link (e.g., "_blank")
 * - pretty: (optional) "true" to strip protocol from URLs
 */
function organization_shortcode($atts) {
    $atts = shortcode_atts([
        'field' => '',
        'id' => '',
        'link' => 'false',
        'target' => '',
        'pretty' => 'false',
        'format' => 'html',
        'size' => 'large',
        'columns' => '3',
        'action' => '',
    ], $atts, 'organization');

    // Get organization ID
    $org_id = $atts['id'];
    if (empty($org_id)) {
        $primary_org = get_primary_organization();
        if (!$primary_org) {
            return '';
        }
        $org_id = $primary_org->ID;
    }

    if (($atts['action'] ?? '') === 'display_gallery') {
        $gallery = function_exists('get_field') ? get_field('gallery', $org_id) : [];
        $images = sfpf_normalize_gallery_images($gallery, $atts['size'] ?? 'large');
        if (($atts['format'] ?? '') === 'json') return wp_json_encode($images);
        if (($atts['format'] ?? '') === 'urls') return esc_html(implode("\n", array_map(function($image) { return $image['url'] ?? ''; }, $images)));
        if (($atts['format'] ?? '') === 'count') return (string) count($images);
        return sfpf_render_gallery_html($images, 'sfpf-organization-gallery', (int) ($atts['columns'] ?? 3));
    }

    $field = $atts['field'];
    if (empty($field)) {
        return '';
    }
    $value = '';

    // Handle special fields
    switch ($field) {
        case 'title':
        case 'name':
            $value = get_the_title($org_id);
            break;

        case 'headquarters_location':
            $hq = get_field('headquarters', $org_id);
            $value = $hq['location'] ?? '';
            break;

        case 'headquarters_wikipedia':
            $hq = get_field('headquarters', $org_id);
            $value = $hq['wikipedia_url'] ?? '';
            break;

        case 'logo':
            $logo = get_field('image_cropped', $org_id);
            $value = isset($logo['url']) ? $logo['url'] : '';
            break;

        case 'permalink':
            $value = get_permalink($org_id);
            break;

        case 'gallery':
            $gallery = get_field('gallery', $org_id);
            $images = sfpf_normalize_gallery_images($gallery, $atts['size'] ?? 'large');
            if (($atts['format'] ?? '') === 'json') return wp_json_encode($images);
            if (($atts['format'] ?? '') === 'urls') return esc_html(implode("\n", array_map(function($image) { return $image['url'] ?? ''; }, $images)));
            if (($atts['format'] ?? '') === 'count') return (string) count($images);
            return sfpf_render_gallery_html($images, 'sfpf-organization-gallery', (int) ($atts['columns'] ?? 3));

        default:
            // Handle url_* fields (individual social URL fields)
            if (strpos($field, 'url_') === 0) {
                $platform = substr($field, 4); // strip 'url_'
                $value = get_field('url_' . $platform, $org_id);
                break;
            }

            $value = get_field($field, $org_id);
            break;
    }

    if (empty($value)) {
        return '';
    }

    // Format URL if needed
    if (filter_var($value, FILTER_VALIDATE_URL)) {
        return format_url_output($value, $atts);
    }

    return $value;
}
add_shortcode('organization', __NAMESPACE__ . '\\organization_shortcode');

// =============================================================================
// BOOK SHORTCODE
// =============================================================================

/**
 * Book shortcode: [book field="name"]
 *
 * Attributes:
 * - field: (required) The ACF field name to retrieve
 * - id: (optional) Specific book ID, defaults to primary book
 * - link: (optional) "true" to wrap URL in anchor tag
 * - target: (optional) Target for link (e.g., "_blank")
 * - pretty: (optional) "true" to strip protocol from URLs
 */
function book_shortcode($atts) {
    $atts = shortcode_atts([
        'field' => '',
        'id' => '',
        'link' => 'false',
        'target' => '',
        'pretty' => 'false',
    ], $atts, 'book');

    if (empty($atts['field'])) {
        return '';
    }

    // Get book ID
    $book_id = $atts['id'];
    if (empty($book_id)) {
        $primary_book = get_primary_book();
        if (!$primary_book) {
            return '';
        }
        $book_id = $primary_book->ID;
    }

    $field = $atts['field'];
    $value = '';

    // Handle special fields
    switch ($field) {
        case 'title':
        case 'name':
            $value = get_the_title($book_id);
            break;

        case 'cover':
            $cover = get_field('cover', $book_id);
            $value = isset($cover['url']) ? $cover['url'] : '';
            break;

        case 'permalink':
            $value = get_permalink($book_id);
            break;

        default:
            $value = get_field($field, $book_id);
            break;
    }

    if (empty($value)) {
        return '';
    }

    // Format URL if needed
    if (filter_var($value, FILTER_VALIDATE_URL)) {
        return format_url_output($value, $atts);
    }

    return $value;
}
add_shortcode('book', __NAMESPACE__ . '\\book_shortcode');

// =============================================================================
// FOUNDER SHORTCODE - [founder id="..."] or [founder action="..."]
// =============================================================================

/**
 * Founder shortcode handler
 *
 * [founder id="name"] - Get founder field value
 * [founder id="biography"] - Get biography
 * [founder action="display_education"] - Display formatted education
 * [founder action="display_professions_with_summary"] - Display professions with summaries
 * [founder action="display_socials"] - Display social links
 */
function founder_shortcode($atts) {
    $atts = shortcode_atts([
        'id' => '',
        'action' => '',
        'format' => 'html',
        'index' => '',
        'field' => '',
        'size' => 'full',
        'columns' => '3',
    ], $atts);

    $user_id = get_founder_user_id();
    if (!$user_id) {
        return '';
    }

    // Handle actions
    if (!empty($atts['action'])) {
        switch ($atts['action']) {
            case 'display_education':
                return founder_display_education($user_id);
            case 'display_gallery':
                return founder_display_gallery($user_id, $atts);
            case 'display_professions_with_summary':
                return founder_display_professions($user_id);
            case 'display_socials':
                return founder_display_socials($user_id);
            case 'display_articles':
                return founder_display_articles($user_id, $atts['format'] ?? 'titled');
            case 'display_location_born':
                return founder_display_location_born($user_id, $atts['format'] ?? 'link');
            case 'display_knowledge_panel':
                $kgid = get_field('knowledge_graph_id', 'user_' . $user_id);
                if (empty($kgid)) return '';
                $full_url = 'https://www.google.com/search?kgmid=' . urlencode($kgid) . '&hl=en-US';
                return '<a href="' . esc_url($full_url) . '" target="_blank" rel="noopener" title="' . esc_attr($full_url) . '">' . esc_html($full_url) . '</a>';
            case 'display_nationality':
                $nationality = get_field('nationality', 'user_' . $user_id);
                if (empty($nationality)) return '';
                // Handle repeater
                if (is_array($nationality)) {
                    $values = array_filter(array_map(function($n) { return trim($n['value'] ?? ''); }, $nationality));
                } else {
                    $values = array_filter(array_map('trim', explode(',', $nationality)));
                }
                if (empty($values)) return '';
                return '<span class="founder-nationality">' . esc_html(implode(', ', $values)) . '</span>';
            case 'display_organizations_founded':
                return founder_display_organizations_founded($atts['format'] ?? 'cards');
            case 'display_bio_full':
                return founder_display_bio_full($user_id);
            default:
                return '';
        }
    }

    // Handle id-based retrieval
    $field_name = $atts['id'];
    if (empty($field_name)) {
        return '';
    }

    // Special handling for different fields
    switch ($field_name) {
        case 'name':
            $first = esc_html(get_user_meta($user_id, 'first_name', true));
            $last  = esc_html(get_user_meta($user_id, 'last_name', true));
            if ($first || $last) {
                $parts = [];
                if ($first) {
                    $parts[] = '<span class="first_name">' . $first . '</span>';
                }
                if ($last) {
                    $parts[] = '<span class="last_name">' . $last . '</span>';
                }
                return '<span class="founder-name">' . implode(' ', $parts) . '</span>';
            }
            $user = get_userdata($user_id);
            return $user ? '<span class="founder-name">' . rtrim(esc_html($user->display_name), '.') . '</span>' : '';

        case 'first_name':
            return esc_html(get_user_meta($user_id, 'first_name', true));

        case 'last_name':
            return esc_html(get_user_meta($user_id, 'last_name', true));

        case 'email':
            $user = get_userdata($user_id);
            return $user ? esc_html($user->user_email) : '';

        case 'website':
            $user = get_userdata($user_id);
            return $user ? esc_url($user->user_url) : '';

        case 'avatar':
            $size_map = [
                'thumbnail' => 150,
                'medium' => 300,
                'medium_large' => 768,
                'large' => 1024,
                'full' => 0,
            ];
            $size = $atts['size'];
            $px = isset($size_map[$size]) ? $size_map[$size] : 0;
            // Prefer real uploaded avatar (wp-user-avatars) to bypass Gravatar/LiteSpeed avatar-cache localization (which can serve a stale blank placeholder).
            $wpua_id = (int) get_user_meta($user_id, 'wp_user_avatar', true);
            if ($wpua_id) { $real = wp_get_attachment_image_url($wpua_id, 'full'); if ($real) { return esc_url($real); } }
            $wpua_meta = get_user_meta($user_id, 'wp_user_avatars', true);
            if (is_array($wpua_meta) && !empty($wpua_meta['full'])) { return esc_url($wpua_meta['full']); }
            if ($px > 0) {
                return esc_url(get_avatar_url($user_id, ['size' => $px]));
            }
            return esc_url(get_avatar_url($user_id));

        case 'professions':
            $professions = get_field('professions', 'user_' . $user_id);
            if (empty($professions)) return '';
            $names = [];
            foreach ($professions as $p) {
                if (!empty($p['name'])) {
                    $names[] = $p['name'];
                }
            }
            return $atts['format'] === 'json' ? json_encode($names) : implode(', ', $names);

        case 'education':
            $education = get_field('education', 'user_' . $user_id);
            if (empty($education)) return '';

            if (!empty($atts['index']) && is_numeric($atts['index'])) {
                $idx = intval($atts['index']);
                if (isset($education[$idx])) {
                    if (!empty($atts['field'])) {
                        return esc_html($education[$idx][$atts['field']] ?? '');
                    }
                    return esc_html($education[$idx]['college'] ?? '');
                }
                return '';
            }

            if ($atts['format'] === 'json') {
                return json_encode($education);
            }

            $output = '<ul class="founder-education">';
            foreach ($education as $edu) {
                $output .= '<li>';
                if (!empty($edu['college'])) {
                    $output .= '<span class="college">' . esc_html($edu['college']) . '</span>';
                }
                if (!empty($edu['designation'])) {
                    $output .= ' - <span class="designation">' . esc_html($edu['designation']) . '</span>';
                }
                if (!empty($edu['major'])) {
                    $output .= ' in <span class="major">' . esc_html($edu['major']) . '</span>';
                }
                if (!empty($edu['year'])) {
                    $output .= ' <span class="year">(' . esc_html($edu['year']) . ')</span>';
                }
                $output .= '</li>';
            }
            $output .= '</ul>';
            return $output;

        case 'gallery':
        case 'knowledge_graph_images':
            $gallery = get_field($field_name, 'user_' . $user_id);
            $images = sfpf_normalize_gallery_images($gallery, $atts['size'] ?? 'large');
            if (($atts['format'] ?? '') === 'json') return wp_json_encode($images);
            if (($atts['format'] ?? '') === 'urls') return esc_html(implode("\n", array_map(function($image) { return $image['url'] ?? ''; }, $images)));
            if (($atts['format'] ?? '') === 'count') return (string) count($images);
            return sfpf_render_gallery_html($images, 'sfpf-founder-gallery', (int) ($atts['columns'] ?? 3));

        case 'articles':
            $articles = get_field('articles', 'user_' . $user_id);
            if (empty($articles) || !is_array($articles)) return '';
            if ($atts['format'] === 'json') {
                return wp_json_encode($articles);
            }
            // Plain text list of URLs
            $urls = array_filter(array_map(function($a) { return $a['url'] ?? ''; }, $articles));
            return esc_html(implode("\n", $urls));

        case 'location_born_location':
            $lb = get_field('location_born', 'user_' . $user_id);
            return esc_html($lb['location'] ?? '');

        case 'location_born_url':
            $lb = get_field('location_born', 'user_' . $user_id);
            return esc_url($lb['wikipedia_url'] ?? '');

        case 'nationality':
            $nationality = get_field('nationality', 'user_' . $user_id);
            if (empty($nationality) || !is_array($nationality)) {
                // Backward compat: if it's a string (old text field), return as-is
                if (is_string($nationality) && !empty($nationality)) {
                    return $atts['format'] === 'json' ? wp_json_encode(array_map('trim', explode(',', $nationality))) : esc_html($nationality);
                }
                return '';
            }
            $values = array_filter(array_map(function($n) { return trim($n['value'] ?? ''); }, $nationality));
            if (empty($values)) return '';
            if ($atts['format'] === 'json') return wp_json_encode(array_values($values));
            return esc_html(implode(', ', $values));

        case 'knows_language':
            $langs = get_field('knows_language', 'user_' . $user_id);
            if (empty($langs) || !is_array($langs)) return '';
            $lang_vals = array_filter(array_map(function($l) { return trim($l['value'] ?? ''); }, $langs));
            if (empty($lang_vals)) return '';
            if ($atts['format'] === 'json') return wp_json_encode(array_values($lang_vals));
            return esc_html(implode(', ', $lang_vals));

        case 'awards':
            $awards = get_field('awards', 'user_' . $user_id);
            if (empty($awards) || !is_array($awards)) return '';
            $award_vals = array_filter(array_map(function($a) { return trim($a['value'] ?? ''); }, $awards));
            if (empty($award_vals)) return '';
            if ($atts['format'] === 'json') return wp_json_encode(array_values($award_vals));
            return esc_html(implode(', ', $award_vals));

        default:
            // Handle url_* fields (pull from urls group)
            if (strpos($field_name, 'url_') === 0) {
                $platform = substr($field_name, 4);
                $urls = get_field('urls', 'user_' . $user_id);
                if (is_array($urls) && !empty($urls[$platform])) {
                    return esc_url($urls[$platform]);
                }
                return '';
            }

            // Try ACF field
            $value = get_field($field_name, 'user_' . $user_id);
            if ($value !== null && $value !== false && $value !== '') {
                return is_array($value) ? json_encode($value) : wp_kses_post($value);
            }
            return '';
    }
}
add_shortcode('founder', __NAMESPACE__ . '\\founder_shortcode');

/**
 * Display founder gallery in formatted HTML.
 */
function founder_display_gallery($user_id, $atts = []) {
    $gallery = function_exists('get_field') ? get_field('gallery', 'user_' . $user_id) : [];
    $images = sfpf_normalize_gallery_images($gallery, $atts['size'] ?? 'large');
    if (($atts['format'] ?? '') === 'json') return wp_json_encode($images);
    if (($atts['format'] ?? '') === 'urls') return esc_html(implode("\n", array_map(function($image) { return $image['url'] ?? ''; }, $images)));
    if (($atts['format'] ?? '') === 'count') return (string) count($images);
    return sfpf_render_gallery_html($images, 'sfpf-founder-gallery', (int) ($atts['columns'] ?? 3));
}

/**
 * Display founder education in formatted HTML
 */
function founder_display_education($user_id) {
    $education = get_field('education', 'user_' . $user_id);
    if (empty($education)) {
        return '';
    }

    $output = '<div class="founder-education">';
    foreach ($education as $i => $edu) {
        $output .= '<div class="education-item">';

        $school_name = esc_html($edu['college'] ?? '');
        $wiki_url = $edu['wiki_url'] ?? '';

        if ($school_name) {
            $output .= '<div class="college">';
            if ($wiki_url) {
                $output .= '<a href="' . esc_url($wiki_url) . '" target="_blank" rel="noopener">' . $school_name . '</a>';
            } else {
                $output .= $school_name;
            }
            $output .= '</div>';
        }

        $has_degree = !empty($edu['designation']) || !empty($edu['major']);
        if ($has_degree) {
            $output .= '<div class="degree">';
            if (!empty($edu['designation'])) {
                $output .= '<span class="designation">' . esc_html($edu['designation']) . '</span>';
            }
            if (!empty($edu['designation']) && !empty($edu['major'])) {
                $output .= ' in ';
            }
            if (!empty($edu['major'])) {
                $output .= '<span class="major">' . esc_html($edu['major']) . '</span>';
            }
            $output .= '</div>';
        }

        if (!empty($edu['year'])) {
            $output .= '<div class="year">' . esc_html($edu['year']) . '</div>';
        }

        $output .= '</div>';
    }
    $output .= '</div>';

    return $output;
}

/**
 * Display founder professions with summaries
 */
function founder_display_professions($user_id) {
    $professions = get_field('professions', 'user_' . $user_id);
    if (empty($professions)) {
        return '';
    }

    $output = '<div class="founder-professions">';
    foreach ($professions as $prof) {
        $prof_name = $prof['name'] ?? '';
        if (empty($prof_name)) continue;

        $output .= '<div class="profession-item">';
        $output .= '<div class="name">' . esc_html($prof_name) . '</div>';

        // If there's a linked page, show link and excerpt
        if (!empty($prof['page'])) {
            $page_id = is_array($prof['page']) ? $prof['page']['ID'] : $prof['page'];
            $page = get_post($page_id);
            if ($page) {
                $output .= '<a class="page-link" href="' . esc_url(get_permalink($page_id)) . '" target="_blank">View Details →</a>';
                if (!empty($page->post_content)) {
                    $output .= '<div class="page-content">' . apply_filters('the_content', $page->post_content) . '</div>';
                }
            }
        }

        // Show summary if available
        if (!empty($prof['summary'])) {
            $output .= '<div class="summary">' . wp_kses_post($prof['summary']) . '</div>';
        }

        $output .= '</div>';
    }
    $output .= '</div>';

    return $output;
}

/**
 * Display founder social links
 */
function founder_display_socials($user_id) {
    // Get socials from website settings (HWS Base Tools)
    if (!function_exists('get_field')) {
        return '';
    }

    $website = get_field('website', 'option');
    $socials = $website['social_media'] ?? [];

    if (empty($socials)) {
        return '';
    }

    $social_labels = [
        'facebook' => 'Facebook',
        'instagram' => 'Instagram',
        'twitter' => 'Twitter/X',
        'x' => 'X',
        'linkedin' => 'LinkedIn',
        'youtube' => 'YouTube',
        'tiktok' => 'TikTok',
        'github' => 'GitHub',
        'wikipedia' => 'Wikipedia',
        'imdb' => 'IMDb',
        'muckrack' => 'Muck Rack',
        'crunchbase' => 'Crunchbase',
    ];

    $output = '<div class="founder-socials">';
    $output .= '<ul class="social-list">';

    foreach ($socials as $platform => $url) {
        if (empty($url)) continue;
        $label = $social_labels[$platform] ?? ucfirst($platform);
        $output .= '<li class="social-item ' . esc_attr($platform) . '"><a class="social-link" href="' . esc_url($url) . '" target="_blank" rel="noopener">' . esc_html($label) . '</a></li>';
    }

    $output .= '</ul>';
    $output .= '</div>';

    return $output;
}

// =============================================================================
// URL FORMATTING HELPER
// =============================================================================

/**
 * Format URL output with link/pretty/target options
 */
function format_url_output($url, $atts) {
    $link = filter_var($atts['link'], FILTER_VALIDATE_BOOLEAN);
    $pretty = filter_var($atts['pretty'], FILTER_VALIDATE_BOOLEAN);
    $target = !empty($atts['target']) ? $atts['target'] : '';

    // Get display text
    $display = $url;
    if ($pretty) {
        $display = preg_replace('#^https?://#', '', $url);
        $display = rtrim($display, '/');
    }

    if ($link) {
        $target_attr = $target ? ' target="' . esc_attr($target) . '"' : '';
        return '<a href="' . esc_url($url) . '"' . $target_attr . '>' . esc_html($display) . '</a>';
    }

    return esc_html($display);
}

// =============================================================================
// ARTICLES DISPLAY
// =============================================================================

/**
 * Display articles with multiple format options
 * Supports new repeater format (title/source/url) with fallback to old textarea
 *
 * Formats: titled (default), cards, sources, compact
 */
function founder_display_articles($user_id, $format = 'titled') {
    $articles_raw = get_field('articles', 'user_' . $user_id);

    // Normalize to array of {title, source, url}
    $articles = [];
    if (is_array($articles_raw) && !empty($articles_raw)) {
        foreach ($articles_raw as $item) {
            if (empty($item['url'])) continue;
            $url = $item['url'];
            $source = $item['source'] ?? '';
            if (empty($source)) {
                $parsed = wp_parse_url($url);
                $source = preg_replace('/^www\./', '', $parsed['host'] ?? '');
            }
            $articles[] = [
                'title' => $item['title'] ?? '',
                'source' => $source,
                'url' => $url,
            ];
        }
    } elseif (is_string($articles_raw) && !empty(trim($articles_raw))) {
        $urls = array_filter(array_map('trim', explode("\n", $articles_raw)));
        foreach ($urls as $url) {
            if (!filter_var($url, FILTER_VALIDATE_URL)) continue;
            $parsed = wp_parse_url($url);
            $source = preg_replace('/^www\./', '', $parsed['host'] ?? '');
            $articles[] = [
                'title' => '',
                'source' => $source,
                'url' => $url,
            ];
        }
    }

    if (empty($articles)) {
        return '';
    }

    $output = '<div class="founder-articles format-' . esc_attr($format) . '">';

    // Shared styles block (injected once)
    static $articles_styles_injected = false;
    if (!$articles_styles_injected) {
        $articles_styles_injected = true;
        $output .= '<style>
.founder-articles a.article-link {
    color: inherit;
    text-decoration: none;
    transition: color 0.15s ease;
}
.founder-articles a.article-link:hover {
    color: #2563eb;
}
.founder-articles a.article-link:hover .article-arrow {
    opacity: 1;
    transform: translateX(2px);
}
.founder-articles .article-arrow {
    display: inline-block;
    opacity: 0.35;
    transition: opacity 0.15s ease, transform 0.15s ease;
    font-size: 0.8em;
    margin-left: 6px;
    vertical-align: baseline;
}
.founder-articles .article-source-tag {
    display: inline-block;
    font-size: 11px;
    color: #9ca3af;
    margin-left: 10px;
    font-weight: 400;
    vertical-align: baseline;
}
.founder-articles .article-card-wrap {
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 16px 20px;
    margin-bottom: 10px;
    transition: border-color 0.15s ease, box-shadow 0.15s ease;
    background: #fff;
}
.founder-articles .article-card-wrap:hover {
    border-color: #cbd5e1;
    box-shadow: 0 1px 4px rgba(0,0,0,0.04);
}
.founder-articles .article-card-wrap a {
    text-decoration: none;
    color: inherit;
}
.founder-articles .article-card-wrap .card-url {
    font-size: 12px;
    color: #9ca3af;
    margin-top: 6px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
</style>';
    }

    switch ($format) {
        case 'cards':
            foreach ($articles as $a) {
                $title = esc_html($a['title'] ?: preg_replace('#^https?://#', '', rtrim($a['url'], '/')));
                $source = esc_html($a['source']);
                $url = esc_url($a['url']);
                $pretty_url = preg_replace('#^https?://#', '', rtrim($a['url'], '/'));
                $output .= '<div class="article-card-wrap">';
                $output .= '<a href="' . $url . '" target="_blank" rel="noopener">';
                $output .= '<div style="font-weight:600;font-size:15px;line-height:1.4;">' . $title;
                if ($source) {
                    $output .= '<span class="article-source-tag">' . $source . '</span>';
                }
                $output .= '</div>';
                $output .= '<div class="card-url">' . esc_html($pretty_url) . '</div>';
                $output .= '</a>';
                $output .= '</div>';
            }
            break;

        case 'sources':
            $grouped = [];
            foreach ($articles as $a) {
                $key = $a['source'] ?: 'Other';
                $grouped[$key][] = $a;
            }
            ksort($grouped);
            foreach ($grouped as $source => $items) {
                $output .= '<div style="margin-bottom:24px;">';
                $output .= '<div style="font-size:12px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:10px;padding-bottom:6px;border-bottom:1px solid #f3f4f6;">' . esc_html($source) . ' <span style="font-weight:400;">(' . count($items) . ')</span></div>';
                foreach ($items as $a) {
                    $title = esc_html($a['title'] ?: preg_replace('#^https?://#', '', rtrim($a['url'], '/')));
                    $output .= '<div style="padding:6px 0;"><a class="article-link" href="' . esc_url($a['url']) . '" target="_blank" rel="noopener"><span class="article-arrow" style="margin-left:0;margin-right:6px;">↗</span>' . $title . '</a></div>';
                }
                $output .= '</div>';
            }
            break;

        case 'compact':
            $output .= '<div style="border-top:1px solid #f3f4f6;">';
            foreach ($articles as $a) {
                $title = esc_html($a['title'] ?: preg_replace('#^https?://#', '', rtrim($a['url'], '/')));
                $source = esc_html($a['source']);
                $output .= '<div style="padding:10px 0;border-bottom:1px solid #f3f4f6;display:flex;justify-content:space-between;align-items:center;gap:12px;">';
                $output .= '<a class="article-link" href="' . esc_url($a['url']) . '" target="_blank" rel="noopener" style="font-weight:500;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">' . $title . '</a>';
                if ($source) {
                    $output .= '<span class="article-source-tag" style="margin-left:auto;flex-shrink:0;">' . $source . '</span>';
                }
                $output .= '</div>';
            }
            $output .= '</div>';
            break;

        case 'titled':
        default:
            foreach ($articles as $a) {
                $title = esc_html($a['title'] ?: preg_replace('#^https?://#', '', rtrim($a['url'], '/')));
                $source = esc_html($a['source']);
                $output .= '<div style="padding:8px 0;">';
                $output .= '<a class="article-link" href="' . esc_url($a['url']) . '" target="_blank" rel="noopener" style="font-weight:500;font-size:15px;line-height:1.5;">';
                $output .= '<span class="article-arrow" style="margin-left:0;margin-right:6px;">↗</span>';
                $output .= $title;
                $output .= '</a>';
                if ($source) {
                    $output .= '<span class="article-source-tag">' . $source . '</span>';
                }
                $output .= '</div>';
            }
            break;
    }

    $output .= '</div>';

    return $output;
}

// =============================================================================
// LOCATION BORN DISPLAY
// =============================================================================

/**
 * Display location born
 * format: link (default), text, inline
 */
function founder_display_location_born($user_id, $format = 'link') {
    $lb = get_field('location_born', 'user_' . $user_id);
    if (empty($lb) || empty($lb['location'])) {
        return '';
    }

    $location = esc_html($lb['location']);
    $wiki_url = $lb['wikipedia_url'] ?? '';

    switch ($format) {
        case 'text':
            return '<div class="founder-location-born"><span class="location-born-label">Location Born:</span> <span class="location-born-value">' . $location . '</span></div>';

        case 'inline':
            if ($wiki_url) {
                return '<span class="founder-location-born-inline"><a href="' . esc_url($wiki_url) . '" target="_blank" rel="noopener">' . $location . '</a></span>';
            }
            return '<span class="founder-location-born-inline">' . $location . '</span>';

        case 'link':
        default:
            $location_html = $location;
            if ($wiki_url) {
                $location_html = '<a href="' . esc_url($wiki_url) . '" target="_blank" rel="noopener">' . $location . '</a>';
            }
            return '<div class="founder-location-born"><span class="location-born-label">Birthplace:</span> <span class="location-born-value">' . $location_html . '</span></div>';
    }
}

// =============================================================================
// ORGANIZATIONS FOUNDED DISPLAY
// =============================================================================

/**
 * Display organizations founded
 * format: cards (default), list, compact
 */
function founder_display_organizations_founded($format = 'cards') {
    $orgs = get_posts([
        'post_type'      => 'organization',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'orderby'        => 'date',
        'order'          => 'ASC',
    ]);

    if (empty($orgs)) {
        return '';
    }

    $output = '<div class="founder-organizations-founded format-' . esc_attr($format) . '">';

    foreach ($orgs as $org) {
        $org_id    = $org->ID;
        $name      = esc_html($org->post_title);
        $url       = get_field('url', $org_id);
        $summary   = get_field('short_summary', $org_id);
        $founding   = get_field('founding_date', $org_id);
        $hq         = get_field('headquarters', $org_id);
        $hq_loc     = $hq['location'] ?? '';
        $logo_field = get_field('image_cropped', $org_id);
        $logo_url   = $logo_field['url'] ?? '';
        $permalink  = get_permalink($org_id);

        switch ($format) {
            case 'compact':
                $output .= '<div class="org-item org-compact">';
                $name_html = $permalink ? '<a href="' . esc_url($permalink) . '">' . $name . '</a>' : $name;
                $output .= '<span class="org-name">' . $name_html . '</span>';
                if ($founding) {
                    $output .= ' <span class="org-date">(' . esc_html($founding) . ')</span>';
                }
                if ($hq_loc) {
                    $output .= ' <span class="org-hq">— ' . esc_html($hq_loc) . '</span>';
                }
                $output .= '</div>';
                break;

            case 'list':
                $output .= '<div class="org-item org-list-item" style="margin-bottom:15px;padding-bottom:15px;border-bottom:1px solid #e5e7eb;">';
                $name_html = $permalink ? '<a href="' . esc_url($permalink) . '">' . $name . '</a>' : $name;
                $output .= '<div class="org-name" style="font-weight:600;font-size:16px;">' . $name_html . '</div>';
                $meta_parts = [];
                if ($founding) $meta_parts[] = esc_html($founding);
                if ($hq_loc) $meta_parts[] = esc_html($hq_loc);
                if ($url) $meta_parts[] = '<a href="' . esc_url($url) . '" target="_blank" rel="noopener">' . esc_html(preg_replace('#^https?://#', '', rtrim($url, '/'))) . '</a>';
                if (!empty($meta_parts)) {
                    $output .= '<div class="org-meta" style="font-size:13px;color:#6b7280;margin-top:4px;">' . implode(' · ', $meta_parts) . '</div>';
                }
                if ($summary) {
                    $output .= '<div class="org-summary" style="margin-top:8px;color:#374151;">' . wp_kses_post($summary) . '</div>';
                }
                $output .= '</div>';
                break;

            case 'cards':
            default:
                $output .= '<div class="org-item org-card" style="display:flex;gap:16px;margin-bottom:20px;padding:20px;background:#f9fafb;border-radius:8px;border:1px solid #e5e7eb;">';

                if ($logo_url) {
                    $output .= '<div class="org-logo" style="flex-shrink:0;width:80px;height:80px;">';
                    $output .= '<img src="' . esc_url($logo_url) . '" alt="' . $name . '" style="width:80px;height:80px;object-fit:contain;border-radius:8px;">';
                    $output .= '</div>';
                }

                $output .= '<div class="org-details" style="flex:1;min-width:0;">';
                $name_html = $permalink ? '<a href="' . esc_url($permalink) . '" style="text-decoration:none;color:inherit;">' . $name . '</a>' : $name;
                $output .= '<div class="org-name" style="font-weight:700;font-size:18px;margin-bottom:4px;">' . $name_html . '</div>';

                $meta_parts = [];
                if ($founding) $meta_parts[] = '<span class="org-date">' . esc_html($founding) . '</span>';
                if ($hq_loc) $meta_parts[] = '<span class="org-hq">' . esc_html($hq_loc) . '</span>';
                if (!empty($meta_parts)) {
                    $output .= '<div class="org-meta" style="font-size:13px;color:#6b7280;margin-bottom:8px;">' . implode(' · ', $meta_parts) . '</div>';
                }

                if ($summary) {
                    $output .= '<div class="org-summary" style="color:#374151;line-height:1.6;">' . wp_kses_post($summary) . '</div>';
                }

                if ($url) {
                    $output .= '<div class="org-url" style="margin-top:8px;"><a href="' . esc_url($url) . '" target="_blank" rel="noopener" style="font-size:13px;color:#2563eb;">' . esc_html(preg_replace('#^https?://#', '', rtrim($url, '/'))) . '</a></div>';
                }

                $output .= '</div>';
                $output .= '</div>';
                break;
        }
    }

    $output .= '</div>';
    return $output;
}

// =============================================================================
// BIO FULL DISPLAY (composite shortcode)
// =============================================================================

/**
 * Display full biography structure with all sections
 * Conditionally shows each section only if data exists
 */
function founder_display_bio_full($user_id) {
    $user_key = 'user_' . $user_id;
    $output = '<div class="founder-bio-full">';

    // Biography
    $bio = get_field('biography', $user_key);
    if (!empty($bio)) {
        $output .= '<div class="bio-section bio-section-biography">';
        $output .= '<h3>Biography</h3>';
        $output .= '<div class="bio-content">' . wp_kses_post($bio) . '</div>';
        $output .= '</div>';
    }

    // Alternate Names
    $alt_names = get_field('alternate_names', $user_key);
    if (!empty($alt_names) && is_array($alt_names)) {
        $names = array_filter(array_map(function($n) { return $n['name'] ?? ''; }, $alt_names));
        if (!empty($names)) {
            $output .= '<div class="bio-section bio-section-alternate-names">';
            $output .= '<h3>Also Known As</h3>';
            $output .= '<div class="bio-content">' . esc_html(implode(', ', $names)) . '</div>';
            $output .= '</div>';
        }
    }

    // Education
    $education_html = founder_display_education($user_id);
    if (!empty($education_html)) {
        $output .= '<div class="bio-section bio-section-education">';
        $output .= '<h3>Education</h3>';
        $output .= '<div class="bio-content">' . $education_html . '</div>';
        $output .= '</div>';
    }

    // Location Born
    $location_html = founder_display_location_born($user_id, 'link');
    if (!empty($location_html)) {
        $output .= '<div class="bio-section bio-section-location-born">';
        $output .= '<h3>Birthplace</h3>';
        $output .= '<div class="bio-content">' . $location_html . '</div>';
        $output .= '</div>';
    }

    // Organizations Founded
    $orgs_html = founder_display_organizations_founded('cards');
    if (!empty($orgs_html)) {
        $output .= '<div class="bio-section bio-section-organizations">';
        $output .= '<h3>Organizations Founded</h3>';
        $output .= '<div class="bio-content">' . $orgs_html . '</div>';
        $output .= '</div>';
    }

    // Professions
    $professions_html = founder_display_professions($user_id);
    if (!empty($professions_html)) {
        $output .= '<div class="bio-section bio-section-professions">';
        $output .= '<h3>Professions</h3>';
        $output .= '<div class="bio-content">' . $professions_html . '</div>';
        $output .= '</div>';
    }

    // Social Links
    $socials_html = founder_display_socials($user_id);
    if (!empty($socials_html)) {
        $output .= '<div class="bio-section bio-section-socials">';
        $output .= '<h3>Connect</h3>';
        $output .= '<div class="bio-content">' . $socials_html . '</div>';
        $output .= '</div>';
    }

    $output .= '</div>';

    // Only return if we have at least one section
    if (strpos($output, 'bio-section') === false) {
        return '';
    }

    return $output;
}

// =============================================================================
// SANITIZE URLs BUTTON - PROFILE PAGE SCRIPT
// =============================================================================

/**
 * Add jQuery handler for sanitize URLs button and articles bulk import on user profile pages
 */
add_action('admin_footer', function() {
    $screen = get_current_screen();
    if (!$screen || !in_array($screen->id, ['profile', 'user-edit'])) return;
    ?>
    <script>
    jQuery(document).ready(function($) {
        // Old sanitize URLs button (for sameas textarea)
        $(document).on('click', '.sfpf-sanitize-urls', function(e) {
            e.preventDefault();
            var targetKey = $(this).data('target');
            var $textarea = $('[name="acf[' + targetKey + ']"]');
            if (!$textarea.length) {
                $textarea = $('#acf-' + targetKey + ' textarea');
            }
            if (!$textarea.length) return;

            var raw = $textarea.val();
            if (!raw.trim()) return;

            var parts = raw.split(/[\n\r,\s]+/);
            var cleaned = [];
            parts.forEach(function(part) {
                part = part.trim();
                if (!part) return;
                part = part.replace(/[,;]+$/, '');
                if (!part.match(/\S+\.\S+/)) return;
                part = part.replace(/^https?:\/\//i, '');
                part = part.replace(/^www\./i, '');
                if (!part) return;
                cleaned.push('https://' + part);
            });

            $textarea.val(cleaned.join("\n"));

            var $btn = $(this);
            var origText = $btn.text();
            $btn.text('Cleaned ' + cleaned.length + ' URLs').prop('disabled', true);
            setTimeout(function() { $btn.text(origText).prop('disabled', false); }, 2000);
        });

        // Articles bulk import handler
        $(document).on('click', '#sfpf-process-articles', function(e) {
            e.preventDefault();

            var $btn = $(this);
            var $input = $('#sfpf-articles-bulk-input');
            var $report = $('#sfpf-articles-report');
            var $header = $('#sfpf-articles-report-header');
            var $body = $('#sfpf-articles-report-body');
            var $footer = $('#sfpf-articles-report-footer');
            var $spinner = $('#sfpf-articles-spinner');
            var raw = $input.val();

            if (!raw.trim()) {
                $report.css('display', 'flex');
                $header.html('<span style="color:#fbbf24;font-weight:600;">⚠ No input</span>');
                $body.html('<span style="color:#94a3b8;">Paste URLs or HTML above.</span>');
                $footer.empty();
                return;
            }

            $btn.prop('disabled', true).text('Processing...');
            $spinner.css({display: 'inline-block', visibility: 'visible'}).addClass('is-active');
            $report.css('display', 'flex');
            $header.html('<span style="color:#94a3b8;">⏳ Processing...</span>');
            $body.html('<span style="color:#94a3b8;">Sanitizing URLs, checking duplicates, fetching titles...<br>This may take a moment for many URLs.</span>');
            $footer.empty();

            var userId = $('input[name="user_id"]').val() || $('input[name="checkuser_id"]').val() || '0';

            $.post(ajaxurl, {
                action: 'sfpf_process_articles',
                nonce: '<?php echo wp_create_nonce('sfpf_ajax'); ?>',
                urls: raw,
                user_id: userId
            }, function(response) {
                $spinner.css({display: 'none', visibility: 'hidden'}).removeClass('is-active');
                $btn.prop('disabled', false).text('⚡ Process & Import');

                if (response.success) {
                    var d = response.data;

                    // ── Inject articles into ACF repeater ──
                    if (d.articles && d.articles.length > 0) {
                        var $repeater = $('[data-key="field_sfpf_articles"]').find('.acf-repeater');

                        $.each(d.articles, function(i, article) {
                            $repeater.find('> .acf-actions .acf-repeater-add-row').trigger('click');
                            var $row = $repeater.find('tbody > tr.acf-row:not(.acf-clone)').last();
                            $row.find('[data-key="field_sfpf_article_title"] input').val(article.title || '');
                            $row.find('[data-key="field_sfpf_article_source"] input').val(article.source || '');
                            $row.find('[data-key="field_sfpf_article_url"] input').val(article.url || '');
                            $row.find('input').trigger('change');
                        });
                    }

                    // Header
                    $header.html('<div style="color:#4ade80;font-weight:700;font-size:14px;">✅ Import complete — ' + d.imported + ' articles added (' + d.total + ' total)</div>');

                    // Body — scrollable report
                    var bodyHtml = d.report.replace(/\n/g, '<br>');
                    if (d.original_input) {
                        bodyHtml += '<details style="margin-top:16px;border-top:1px solid #334155;padding-top:12px;">';
                        bodyHtml += '<summary style="cursor:pointer;color:#94a3b8;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;">Original Input</summary>';
                        bodyHtml += '<pre style="margin-top:8px;padding:10px;background:#0f172a;border-radius:4px;color:#64748b;font-size:11px;white-space:pre-wrap;word-break:break-all;max-height:150px;overflow-y:auto;">' + $('<span>').text(d.original_input).html() + '</pre>';
                        bodyHtml += '</details>';
                    }
                    $body.html(bodyHtml);

                    // Footer
                    if (d.imported > 0) {
                        $footer.html('<div style="color:#93c5fd;font-size:13px;">💾 <strong>' + d.imported + ' articles</strong> added to repeater. <strong>Save/Update the profile</strong> to persist.</div>');
                    } else {
                        $footer.html('<div style="color:#94a3b8;font-size:12px;">No new articles to add.</div>');
                    }

                    $input.val('');
                } else {
                    $header.html('<span style="color:#f87171;font-weight:600;">❌ Error</span>');
                    $body.html('<div style="color:#f87171;">' + (response.data || 'Unknown error') + '</div>');
                    $footer.empty();
                }
            }).fail(function(xhr) {
                $spinner.css({display: 'none', visibility: 'hidden'}).removeClass('is-active');
                $btn.prop('disabled', false).text('⚡ Process & Import');
                $header.html('<span style="color:#f87171;font-weight:600;">❌ AJAX Failed</span>');
                $body.html('<div style="color:#f87171;">Request failed. Check your connection.</div>');
                $footer.empty();
            });
        });

        // Remove All Articles handler
        $(document).on('click', '#sfpf-remove-all-articles', function(e) {
            e.preventDefault();

            var $repeater = $('[data-key="field_sfpf_articles"]').find('.acf-repeater');
            var $rows = $repeater.find('tbody > tr.acf-row:not(.acf-clone)');
            var count = $rows.length;

            if (count === 0) {
                alert('No articles to remove.');
                return;
            }

            if (!confirm('Remove all ' + count + ' articles from the repeater?\n\nThis won\'t be permanent until you save the profile.')) {
                return;
            }

            // Remove rows from last to first to avoid index issues
            $($rows.get().reverse()).each(function() {
                $(this).find('.acf-row-handle .acf-icon.-minus').trigger('click');
            });

            // Show confirmation in report area
            var $report = $('#sfpf-articles-report');
            var $header = $('#sfpf-articles-report-header');
            var $body = $('#sfpf-articles-report-body');
            var $footer = $('#sfpf-articles-report-footer');
            $report.css('display', 'flex');
            $header.html('<div style="color:#fbbf24;font-weight:700;font-size:14px;">🗑 Removed ' + count + ' articles</div>');
            $body.html('<span style="color:#94a3b8;">All rows cleared from the repeater.</span>');
            $footer.html('<div style="color:#f59e0b;font-size:13px;">⚠️ <strong>Save/Update the profile</strong> to make this permanent.</div>');
        });

        // KGID dynamic URL display
        function updateKgidLink() {
            var $field = $('[data-name="knowledge_graph_id"] input[type="text"]');
            var $display = $('#sfpf-kgid-link-display');
            if (!$field.length || !$display.length) return;
            var val = $field.val().trim();
            if (val) {
                var fullUrl = 'https://www.google.com/search?kgmid=' + encodeURIComponent(val);
                $display.html('<a href="' + fullUrl + '" target="_blank" style="color:#2563eb;word-break:break-all;">' + fullUrl + '</a> — opens Knowledge Panel in browser');
            } else {
                $display.html('Enter a KGMID above to see the full Knowledge Panel URL.');
            }
        }
        updateKgidLink();
        $(document).on('input change', '[data-name="knowledge_graph_id"] input[type="text"]', updateKgidLink);
    });
    </script>
    <?php
});


// =============================================================================
// AUTHOR ARCHIVE PROFILE RENDERER
// =============================================================================

add_filter("rank_math/json_ld", __NAMESPACE__ . "\sfpf_author_archive_disable_rankmath_schema", 99);
add_action("template_redirect", __NAMESPACE__ . "\sfpf_author_archive_template", 0);

function sfpf_author_archive_disable_rankmath_schema($data) {
    return is_author() ? [] : $data;
}

function sfpf_author_archive_template() {
    if (is_admin() || wp_doing_ajax() || is_feed() || !is_author()) {
        return;
    }
    $author = get_queried_object();
    if (!$author instanceof \WP_User) {
        return;
    }
    status_header(200);
    get_header();
    echo sfpf_render_author_archive_profile((int) $author->ID);
    get_footer();
    exit;
}

function sfpf_author_archive_field($user_id, $field, $default = "") {
    if (function_exists("get_field")) {
        $value = get_field($field, "user_" . $user_id);
        if ($value !== null && $value !== false && $value !== "") return $value;
    }
    $value = get_user_meta($user_id, $field, true);
    return ($value !== "" && $value !== null && $value !== false) ? $value : $default;
}

function sfpf_author_archive_plain($value) {
    if (is_array($value)) {
        $parts = [];
        foreach ($value as $item) {
            if (is_array($item)) {
                foreach ($item as $part) {
                    if (is_scalar($part) && trim((string) $part) !== "") $parts[] = trim((string) $part);
                }
            } elseif (is_scalar($item) && trim((string) $item) !== "") {
                $parts[] = trim((string) $item);
            }
        }
        return implode(", ", array_unique($parts));
    }
    return trim(wp_strip_all_tags((string) $value));
}

function sfpf_author_archive_lines($value) {
    if (empty($value)) return [];
    if (is_array($value)) {
        $lines = [];
        foreach ($value as $item) {
            $candidate = is_array($item) ? ($item["url"] ?? $item["value"] ?? "") : (is_scalar($item) ? (string) $item : "");
            if (trim($candidate) !== "") $lines[] = trim($candidate);
        }
        return array_values(array_filter(array_unique($lines)));
    }
    return array_values(array_filter(array_unique(array_map("trim", preg_split("/\r\n|\r|\n/", (string) $value)))));
}

function sfpf_author_archive_url_group($user_id) {
    $urls = sfpf_author_archive_field($user_id, "urls", []);
    return is_array($urls) ? array_filter($urls, function($url) { return is_string($url) && trim($url) !== ""; }) : [];
}

function sfpf_render_author_archive_profile($user_id) {
    $user = get_userdata($user_id);
    if (!$user) return "";

    $first = trim((string) get_user_meta($user_id, "first_name", true));
    $last = trim((string) get_user_meta($user_id, "last_name", true));
    $name = trim($first . " " . $last) ?: $user->display_name;
    $title = sfpf_author_archive_plain(sfpf_author_archive_field($user_id, "title") ?: sfpf_author_archive_field($user_id, "additional_title"));
    $bio = sfpf_author_archive_field($user_id, "biography") ?: sfpf_author_archive_field($user_id, "biography_short") ?: $user->description;
    $short_bio = sfpf_author_archive_field($user_id, "biography_short");
    $public_email = sfpf_author_archive_field($user_id, "additional_public_email") ?: $user->user_email;
    $birth_date = sfpf_author_archive_plain(sfpf_author_archive_field($user_id, "birth_date"));
    $gender = sfpf_author_archive_plain(sfpf_author_archive_field($user_id, "gender"));
    $nationality = sfpf_author_archive_plain(sfpf_author_archive_field($user_id, "nationality"));
    $kgid = sfpf_author_archive_plain(sfpf_author_archive_field($user_id, "knowledge_graph_id"));
    $avatar = get_avatar_url($user_id, ["size" => 300]);
    $urls = sfpf_author_archive_url_group($user_id);
    $sameas = sfpf_author_archive_lines(sfpf_author_archive_field($user_id, "sameas"));
    $education = sfpf_author_archive_field($user_id, "education", []);
    $articles = sfpf_author_archive_field($user_id, "articles", []);
    $labels = ["website" => "Website", "linkedin" => "LinkedIn", "crunchbase" => "Crunchbase", "wikipedia" => "Wikipedia", "facebook" => "Facebook", "instagram" => "Instagram", "x" => "X", "youtube" => "YouTube", "imdb" => "IMDb", "muckrack" => "Muck Rack"];

    ob_start();
    ?>
    <main id="content" class="site-main sfpf-author-archive">
        <style>.sfpf-author-archive{max-width:1120px;margin:0 auto;padding:48px 20px 64px;color:#111827;font-family:inherit}.sfpf-author-hero{display:grid;grid-template-columns:180px 1fr;gap:32px;align-items:start;background:linear-gradient(135deg,#f8fafc 0%,#eff6ff 100%);border:1px solid #dbeafe;border-radius:28px;padding:32px;box-shadow:0 20px 50px rgba(15,23,42,.08)}.sfpf-author-avatar{width:180px;height:180px;border-radius:24px;object-fit:cover;border:1px solid #dbeafe;background:#fff}.sfpf-author-kicker{font-size:12px;font-weight:800;letter-spacing:.14em;text-transform:uppercase;color:#2563eb;margin:0 0 10px}.sfpf-author-name{font-size:42px;line-height:1.05;margin:0 0 10px;color:#0f172a}.sfpf-author-title{font-size:18px;line-height:1.5;color:#475569;margin:0 0 18px}.sfpf-author-bio{font-size:16px;line-height:1.75;color:#334155}.sfpf-author-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px;margin-top:22px}.sfpf-author-card{background:#fff;border:1px solid #e5e7eb;border-radius:18px;padding:18px}.sfpf-author-label{font-size:11px;font-weight:900;letter-spacing:.1em;text-transform:uppercase;color:#64748b;margin:0 0 6px}.sfpf-author-value{font-size:15px;color:#111827;word-break:break-word}.sfpf-author-section{margin-top:28px;background:#fff;border:1px solid #e5e7eb;border-radius:22px;padding:24px}.sfpf-author-section h2{margin:0 0 16px;font-size:22px}.sfpf-author-list{display:grid;gap:12px;margin:0;padding:0;list-style:none}.sfpf-author-list li{border:1px solid #eef2f7;background:#f8fafc;border-radius:14px;padding:13px 14px}.sfpf-author-links{display:flex;flex-wrap:wrap;gap:10px}.sfpf-author-links a{border:1px solid #bfdbfe;border-radius:999px;padding:8px 12px;background:#eff6ff;color:#1d4ed8;text-decoration:none;font-weight:700;font-size:13px}.sfpf-author-links a:hover{background:#dbeafe}.sfpf-author-muted{color:#64748b}.sfpf-author-schema-link{font-size:13px;word-break:break-all;color:#2563eb}@media(max-width:760px){.sfpf-author-hero{grid-template-columns:1fr;padding:22px}.sfpf-author-avatar{width:132px;height:132px}.sfpf-author-name{font-size:32px}.sfpf-author-grid{grid-template-columns:1fr}}</style>
        <section class="sfpf-author-hero"><img class="sfpf-author-avatar" src="<?php echo esc_url($avatar); ?>" alt="<?php echo esc_attr($name); ?>"><div><p class="sfpf-author-kicker">Author Profile</p><h1 class="sfpf-author-name"><?php echo esc_html($name); ?></h1><?php if ($title): ?><p class="sfpf-author-title"><?php echo esc_html($title); ?></p><?php endif; ?><?php if ($short_bio): ?><div class="sfpf-author-bio"><?php echo wp_kses_post(wpautop($short_bio)); ?></div><?php endif; ?><?php if ($urls || $sameas): ?><div class="sfpf-author-links"><?php foreach ($urls as $key => $url): if (empty($url)) continue; ?><a href="<?php echo esc_url($url); ?>" target="_blank" rel="noopener"><?php echo esc_html($labels[$key] ?? ucwords(str_replace("_", " ", $key))); ?></a><?php endforeach; ?><?php foreach ($sameas as $url): ?><a href="<?php echo esc_url($url); ?>" target="_blank" rel="noopener"><?php echo esc_html(parse_url($url, PHP_URL_HOST) ?: $url); ?></a><?php endforeach; ?></div><?php endif; ?></div></section>
        <section class="sfpf-author-grid" aria-label="Author facts"><?php if ($public_email): ?><div class="sfpf-author-card"><p class="sfpf-author-label">Email</p><div class="sfpf-author-value"><a href="mailto:<?php echo esc_attr($public_email); ?>"><?php echo esc_html($public_email); ?></a></div></div><?php endif; ?><?php if ($birth_date): ?><div class="sfpf-author-card"><p class="sfpf-author-label">Birth Date</p><div class="sfpf-author-value"><?php echo esc_html($birth_date); ?></div></div><?php endif; ?><?php if ($nationality): ?><div class="sfpf-author-card"><p class="sfpf-author-label">Nationality</p><div class="sfpf-author-value"><?php echo esc_html($nationality); ?></div></div><?php endif; ?><?php if ($gender): ?><div class="sfpf-author-card"><p class="sfpf-author-label">Gender</p><div class="sfpf-author-value"><?php echo esc_html($gender); ?></div></div><?php endif; ?><?php if ($kgid): ?><div class="sfpf-author-card"><p class="sfpf-author-label">Knowledge Graph</p><div class="sfpf-author-value"><a class="sfpf-author-schema-link" href="<?php echo esc_url("https://www.google.com/search?kgmid=" . rawurlencode($kgid)); ?>" target="_blank" rel="noopener"><?php echo esc_html($kgid); ?></a></div></div><?php endif; ?></section>
        <?php if ($bio): ?><section class="sfpf-author-section"><h2>Biography</h2><div class="sfpf-author-bio"><?php echo wp_kses_post(wpautop($bio)); ?></div></section><?php endif; ?>
        <?php if (is_array($education) && !empty($education)): ?><section class="sfpf-author-section"><h2>Education</h2><ul class="sfpf-author-list"><?php foreach ($education as $row): $college = trim((string) ($row["college"] ?? "")); if (!$college) continue; ?><li><strong><?php echo esc_html($college); ?></strong><?php if (!empty($row["designation"]) || !empty($row["major"])): ?> <span class="sfpf-author-muted">— <?php echo esc_html(trim(($row["designation"] ?? "") . " " . ($row["major"] ?? ""))); ?></span><?php endif; ?><?php if (!empty($row["year"])): ?> <span class="sfpf-author-muted">(<?php echo esc_html($row["year"]); ?>)</span><?php endif; ?></li><?php endforeach; ?></ul></section><?php endif; ?>
        <?php if (is_array($articles) && !empty($articles)): ?><section class="sfpf-author-section"><h2>Articles and Press</h2><ul class="sfpf-author-list"><?php foreach ($articles as $article): $url = $article["url"] ?? ""; $article_title = $article["title"] ?? $url; if (!$url && !$article_title) continue; ?><li><?php if ($url): ?><a href="<?php echo esc_url($url); ?>" target="_blank" rel="noopener"><?php echo esc_html($article_title ?: $url); ?></a><?php else: ?><?php echo esc_html($article_title); ?><?php endif; ?><?php if (!empty($article["source"])): ?> <span class="sfpf-author-muted">— <?php echo esc_html($article["source"]); ?></span><?php endif; ?></li><?php endforeach; ?></ul></section><?php endif; ?>
    </main>
    <?php
    return ob_get_clean();
}
