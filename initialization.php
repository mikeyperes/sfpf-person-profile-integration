<?php
/**
 * Plugin Name: SFPF Person Profile Integration
 * Plugin URI: https://seoforpublicfigures.com
 * Description: Personal website schema management, page structures, and content templates. Integrates with HWS Base Tools for website settings.
 * Version: 1.4.4
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
define('SFPF_PLUGIN_VERSION', '1.4.4');
define('SFPF_PLUGIN_FILE', __FILE__);
define('SFPF_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SFPF_PLUGIN_URL', plugin_dir_url(__FILE__));
define('SFPF_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Config Class
 */
class Config {
    public static $version = '1.4.4';
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
    // Set default options
    add_option('sfpf_homepage_schema_type', 'none');
    
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
    $cleanup_version = '1.4.3';
    
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
 * Runtime ACF field filter:
 * 1. Block duplicate hws-prefixed fields
 * 2. Enrich Education History instruction with user's LinkedIn/Crunchbase URLs
 */
add_filter('acf/prepare_field', function($field) {
    if (!$field || !is_array($field)) return $field;
    
    // Block fields with old hws prefix
    if (isset($field['key']) && strpos($field['key'], 'field_hws_') === 0) {
        return false;
    }
    
    // Enrich Education History field with LinkedIn/Crunchbase links
    if (isset($field['key']) && $field['key'] === 'field_sfpf_education_repeater') {
        $screen = get_current_screen();
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

/**
 * Also filter out duplicate ACF field groups with hws keys from loading
 */
add_filter('acf/load_field_groups', function($field_groups) {
    if (!is_array($field_groups)) return $field_groups;
    
    return array_filter($field_groups, function($group) {
        // Block any DB-stored group whose key starts with group_hws_
        if (isset($group['key']) && strpos($group['key'], 'group_hws_') === 0) {
            return false;
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
    
    // Default list style
    $html = '<div class="sfpf-faq-list" data-set="' . esc_attr($atts['set']) . '">';
    foreach ($items as $i => $faq) {
        if (!empty($faq['question'])) {
            $html .= '<div class="sfpf-faq-item" style="margin-bottom:20px;padding:20px;background:#f9fafb;border-radius:8px;border:1px solid #e5e7eb;">';
            $html .= '<div class="sfpf-faq-question" style="font-weight:600;font-size:16px;margin-bottom:10px;color:#1e1e1e;">' . esc_html($faq['question']) . '</div>';
            $html .= '<div class="sfpf-faq-answer" style="color:#4b5563;line-height:1.6;">' . wp_kses_post($faq['answer']) . '</div>';
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
            $html .= '<button type="button" class="sfpf-accordion-trigger" style="width:100%;padding:15px 20px;background:#fff;border:none;text-align:left;cursor:pointer;display:flex;justify-content:space-between;align-items:center;font-weight:600;font-size:15px;" onclick="this.parentElement.classList.toggle(\'open\');this.nextElementSibling.style.display=this.nextElementSibling.style.display===\'none\'?\'block\':\'none\';">';
            $html .= '<span>' . esc_html($faq['question']) . '</span>';
            $html .= '<span class="sfpf-accordion-icon" style="font-size:20px;transition:transform 0.2s;">+</span>';
            $html .= '</button>';
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
        var accordion = document.querySelector(targetSelector);
        if (!accordion) {
            console.log("SFPF FAQ: Accordion not found with selector:", targetSelector);
            return;
        }
        
        var items = accordion.querySelectorAll(".elementor-accordion-item");
        
        faqData.forEach(function(faq, index) {
            if (items[index]) {
                // Update title
                var title = items[index].querySelector(".elementor-accordion-title");
                if (title) {
                    title.textContent = faq.question;
                }
                
                // Update content
                var content = items[index].querySelector(".elementor-tab-content");
                if (content) {
                    content.innerHTML = faq.answer;
                }
            }
        });
        
        console.log("SFPF FAQ: Populated " + Math.min(faqData.length, items.length) + " accordion items");
    }
    
    // Run on DOMContentLoaded
    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", populateElementorAccordion);
    } else {
        populateElementorAccordion();
    }
    
    // Also run after Elementor frontend init (for live preview)
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
// RANKMATH SCHEMA CONTROL
// ============================================================================

/**
 * Disable RankMath schema on specific post types
 */
function disable_rankmath_schema($data) {
    if (is_front_page() && get_option('sfpf_rankmath_disable_homepage', false)) {
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
    ], $atts, 'organization');
    
    if (empty($atts['field'])) {
        return '';
    }
    
    // Get organization ID
    $org_id = $atts['id'];
    if (empty($org_id)) {
        $primary_org = get_primary_organization();
        if (!$primary_org) {
            return '';
        }
        $org_id = $primary_org->ID;
    }
    
    $field = $atts['field'];
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
            case 'display_professions_with_summary':
                return founder_display_professions($user_id);
            case 'display_socials':
                return founder_display_socials($user_id);
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
            // Fallback to display_name with trailing period stripped
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
            
            // Default: HTML list
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
            
        default:
            // Handle url_* fields (pull from urls group)
            if (strpos($field_name, 'url_') === 0) {
                $platform = substr($field_name, 4); // strip 'url_'
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
        // Remove protocol and trailing slash
        $display = preg_replace('#^https?://#', '', $url);
        $display = rtrim($display, '/');
    }
    
    // Return as link or plain text
    if ($link) {
        $target_attr = $target ? ' target="' . esc_attr($target) . '"' : '';
        return '<a href="' . esc_url($url) . '"' . $target_attr . '>' . esc_html($display) . '</a>';
    }
    
    return esc_html($display);
}
