<?php
/**
 * Plugin Name: SFPF Person Profile Integration
 * Plugin URI: https://seoforpublicfigures.com
 * Description: Personal website schema management, page structures, and content templates. Integrates with HWS Base Tools for website settings.
 * Version: 3.0.0
 * Author: SEO For Public Figures
 * Author URI: https://seoforpublicfigures.com
 * Text Domain: sfpf-person-profile-integration
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 8.0
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

declare( strict_types=1 );

namespace SFPF\PersonProfile;

defined('ABSPATH') || exit;

/**
 * Plugin Constants
 */
define("SFPF_PLUGIN_VERSION", "3.0.0");
define('SFPF_PLUGIN_FILE', __FILE__);
define('SFPF_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SFPF_PLUGIN_URL', plugin_dir_url(__FILE__));
define('SFPF_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('SFPF_PROFILE_DEBUG_ROUTE', 'sfpf-profile-debug');

/** Register this plugin's vendored Core package with the shared resolver. */
$hexa_plugin_core_root = SFPF_PLUGIN_DIR . 'lib/hexa-wordpress-plugin-core';
require_once $hexa_plugin_core_root . '/bootstrap.php';
\hexa_plugin_core_register_package( 'sfpf-person-profile-integration', $hexa_plugin_core_root );

require_once SFPF_PLUGIN_DIR . 'src/Autoloader.php';
Autoloader::register( SFPF_PLUGIN_DIR . 'src' );

Plugin::register();
