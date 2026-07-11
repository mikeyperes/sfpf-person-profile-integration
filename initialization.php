<?php
/**
 * Plugin Name: SFPF Person Profile Integration
 * Plugin URI: https://seoforpublicfigures.com
 * Description: Personal website schema management, page structures, and content templates. Integrates with HWS Base Tools for website settings.
 * Version: 1.7.2
 * Author: SEO For Public Figures
 * Author URI: https://seoforpublicfigures.com
 * Text Domain: sfpf-person-profile-integration
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 8.0
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

namespace sfpf_person_website;

defined('ABSPATH') || exit;

/**
 * Plugin Constants
 */
define("SFPF_PLUGIN_VERSION", "1.7.2");
define('SFPF_PLUGIN_FILE', __FILE__);
define('SFPF_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SFPF_PLUGIN_URL', plugin_dir_url(__FILE__));
define('SFPF_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('SFPF_PROFILE_DEBUG_ROUTE', 'sfpf-profile-debug');

/**
 * Config Class
 */
class Config {
    public static $version = "1.7.2";
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

/** Register this plugin's vendored Core package with the shared resolver. */
$hexa_plugin_core_root = SFPF_PLUGIN_DIR . 'lib/hexa-wordpress-plugin-core';
require_once $hexa_plugin_core_root . '/bootstrap.php';
\hexa_plugin_core_register_package( 'sfpf-person-profile-integration', $hexa_plugin_core_root );

require_once SFPF_PLUGIN_DIR . 'src/Plugin.php';
\SFPF\PersonProfile\Plugin::register();

// ============================================================================
// LOAD HELPER FILES IMMEDIATELY (before any hooks)
// ============================================================================
require_once SFPF_PLUGIN_DIR . 'includes/helper-functions.php';
require_once SFPF_PLUGIN_DIR . 'includes/logging.php';
require_once SFPF_PLUGIN_DIR . 'includes/snippets-loader.php';
require_once SFPF_PLUGIN_DIR . 'includes/elementor-social-icons.php';
require_once SFPF_PLUGIN_DIR . 'includes/elementor-display-conditions.php';

require_once SFPF_PLUGIN_DIR . 'src/Runtime/LegacyModuleLoader.php';
\SFPF\PersonProfile\Runtime\LegacyModuleLoader::load();
