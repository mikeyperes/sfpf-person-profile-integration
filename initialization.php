<?php
/**
 * Plugin Name: SFPF Person Profile Integration
 * Plugin URI: https://seoforpublicfigures.com
 * Description: Personal website schema management, page structures, and content templates. Integrates with HWS Base Tools for website settings.
 * Version: 1.3.4
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
define('SFPF_PLUGIN_VERSION', '1.3.4');
define('SFPF_PLUGIN_FILE', __FILE__);
define('SFPF_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SFPF_PLUGIN_URL', plugin_dir_url(__FILE__));
define('SFPF_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Config Class
 */
class Config {
    public static $version = '1.3.4';
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
