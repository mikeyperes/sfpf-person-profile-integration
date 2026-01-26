<?php
/**
 * Plugin Name: SFPF Person Profile Integration
 * Plugin URI: https://seoforpublicfigures.com
 * Description: Personal website schema management, page structures, and content templates. Integrates with HWS Base Tools for website settings.
 * Version: 1.0.0
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
define('SFPF_PLUGIN_VERSION', '1.0.0');
define('SFPF_PLUGIN_FILE', __FILE__);
define('SFPF_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SFPF_PLUGIN_URL', plugin_dir_url(__FILE__));
define('SFPF_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Config Class
 * 
 * Central configuration for the plugin.
 */
class Config {
    /** @var string Plugin version */
    public static $version = '1.0.0';
    
    /** @var string Plugin slug */
    public static $slug = 'sfpf-person-profile-integration';
    
    /** @var string Text domain */
    public static $text_domain = 'sfpf-person-profile-integration';
    
    /** @var string Menu slug */
    public static $menu_slug = 'sfpf-person-profile';
    
    // Plugin identification - use these everywhere, never hardcode
    public static $plugin_folder_name = 'sfpf-person-profile-integration';
    public static $plugin_starter_file = 'initialization.php';
    public static $github_repo = 'mikeyperes/sfpf-person-profile-integration';
    public static $github_branch = 'main';
    
    /**
     * Get the full plugin basename (folder/file.php)
     */
    public static function get_plugin_basename() {
        return self::$plugin_folder_name . '/' . self::$plugin_starter_file;
    }
    
    /** @var array Snippet IDs */
    public static $snippets = [
        'book_cpt' => 'sfpf_enable_book_cpt',
        'book_acf' => 'sfpf_enable_book_acf',
        'organization_cpt' => 'sfpf_enable_organization_cpt',
        'organization_acf' => 'sfpf_enable_organization_acf',
        'homepage_acf' => 'sfpf_enable_homepage_acf',
    ];
}

/**
 * Initialize the plugin
 */
function init_plugin() {
    // Load helper functions first
    require_once SFPF_PLUGIN_DIR . 'includes/helper-functions.php';
    require_once SFPF_PLUGIN_DIR . 'includes/logging.php';
    require_once SFPF_PLUGIN_DIR . 'includes/snippets-loader.php';
    
    // Load schema files
    if (file_exists(SFPF_PLUGIN_DIR . 'schema/schema-templates.php')) {
        require_once SFPF_PLUGIN_DIR . 'schema/schema-templates.php';
    }
    if (file_exists(SFPF_PLUGIN_DIR . 'schema/schema-builder.php')) {
        require_once SFPF_PLUGIN_DIR . 'schema/schema-builder.php';
    }
    if (file_exists(SFPF_PLUGIN_DIR . 'schema/schema-manager.php')) {
        require_once SFPF_PLUGIN_DIR . 'schema/schema-manager.php';
    }
    if (file_exists(SFPF_PLUGIN_DIR . 'schema/schema-injector.php')) {
        require_once SFPF_PLUGIN_DIR . 'schema/schema-injector.php';
    }
    
    // Load enabled snippets
    load_enabled_snippets();
    
    // Admin only
    if (is_admin()) {
        require_once SFPF_PLUGIN_DIR . 'admin/settings-dashboard.php';
        require_once SFPF_PLUGIN_DIR . 'admin/ajax-handlers.php';
        require_once SFPF_PLUGIN_DIR . 'admin/dashboard-plugin-info.php';
    }
}
add_action('init', __NAMESPACE__ . '\\init_plugin', 5);

/**
 * Load enabled snippets
 */
function load_enabled_snippets() {
    $snippets_dir = SFPF_PLUGIN_DIR . 'snippets/';
    
    // Book CPT
    if (get_option('sfpf_enable_book_cpt', false)) {
        $file = $snippets_dir . 'register-cpt-book.php';
        if (file_exists($file)) {
            require_once $file;
        }
    }
    
    // Book ACF
    if (get_option('sfpf_enable_book_acf', false)) {
        $file = $snippets_dir . 'register-acf-book.php';
        if (file_exists($file)) {
            require_once $file;
        }
    }
    
    // Organization CPT
    if (get_option('sfpf_enable_organization_cpt', false)) {
        $file = $snippets_dir . 'register-cpt-organization.php';
        if (file_exists($file)) {
            require_once $file;
        }
    }
    
    // Organization ACF
    if (get_option('sfpf_enable_organization_acf', false)) {
        $file = $snippets_dir . 'register-acf-organization.php';
        if (file_exists($file)) {
            require_once $file;
        }
    }
    
    // Homepage ACF
    if (get_option('sfpf_enable_homepage_acf', false)) {
        $file = $snippets_dir . 'register-acf-homepage.php';
        if (file_exists($file)) {
            require_once $file;
        }
    }
}

/**
 * Plugin activation
 */
function activate_plugin() {
    // Set default options
    add_option('sfpf_homepage_schema_type', 'profile_page');
    
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
