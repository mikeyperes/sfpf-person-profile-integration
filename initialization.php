<?php
/**
 * Plugin Name: SFPF Person Profile Integration
 * Plugin URI: https://github.com/mikeyperes/sfpf-person-profile-integration
 * Description: Person profile websites for SEO for Public Figures: the person's fields, schema for search engines and Knowledge Panels, shortcodes, pages and content types.
 * Version: 4.0.0
 * Author: SEO For Public Figures
 * Author URI: https://seoforpublicfigures.com
 * Text Domain: sfpf-person-profile-integration
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 8.1
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * GitHub Plugin URI: https://github.com/mikeyperes/sfpf-person-profile-integration
 * GitHub Branch: main
 */

declare( strict_types=1 );

defined( 'ABSPATH' ) || exit;

if ( defined( 'SFPF_PERSON_PROFILE_BOOTSTRAPPED' ) ) {
    return;
}
define( 'SFPF_PERSON_PROFILE_BOOTSTRAPPED', true );

require_once __DIR__ . '/src/PluginRuntime/Autoloader.php';
\SFPF\PersonProfile\PluginRuntime\Autoloader::register( __DIR__ . '/src' );

require_once __DIR__ . '/lib/hexa-wordpress-plugin-core/bootstrap.php';
\hexa_plugin_core_register_package( 'sfpf-person-profile-integration', __DIR__ . '/lib/hexa-wordpress-plugin-core' );

\SFPF\PersonProfile\PluginRuntime\Lifecycle::register( __FILE__ );

if ( did_action( 'plugins_loaded' ) ) {
    \SFPF\PersonProfile\PluginRuntime\Bootstrap::boot();
} else {
    add_action( 'plugins_loaded', [ \SFPF\PersonProfile\PluginRuntime\Bootstrap::class, 'boot' ], 20 );
}
