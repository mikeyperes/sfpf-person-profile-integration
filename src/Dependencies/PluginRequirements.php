<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Dependencies;

use Hexa\PluginCore\PluginChecks\PluginCheckDefinition;
use Hexa\PluginCore\PluginChecks\PluginCheckService;
use Hexa\PluginCore\PluginChecks\PluginRecommendationRegistry;

defined( 'ABSPATH' ) || exit;

final class PluginRequirements {
    private static bool $registered = false;

    /** @return list<array<string,mixed>> */
    public static function definitions(): array {
        return [
            [
                'id'          => 'hws-base-tools',
                'name'        => 'HWS Base Tools',
                'plugin_file' => 'hws-base-tools/hws-base-tools.php',
                'slug'        => 'hws-base-tools',
                'source'      => 'github',
                'github_repo' => 'mikeyperes/hws-base-tools',
                'required'    => true,
                'checks'      => [ 'installed' => true, 'active' => true ],
                'notes'       => 'Supplies the canonical Person/entity and shared content-type contracts.',
            ],
            [
                'id'          => 'advanced-custom-fields-pro',
                'name'        => 'Advanced Custom Fields Pro',
                'plugin_file' => 'advanced-custom-fields-pro/acf.php',
                'slug'        => 'advanced-custom-fields-pro',
                'source'      => 'pro',
                'required'    => false,
                'recommended' => true,
                'checks'      => [ 'installed' => true, 'active' => true ],
                'notes'       => 'Required for the optional Person and Book field structures.',
            ],
            [
                'id'          => 'smc-organization-profile-integration',
                'name'        => 'SMC Organization Profile Integration',
                'plugin_file' => 'smc-organization-profile-integration/initialization.php',
                'slug'        => 'smc-organization-profile-integration',
                'source'      => 'github',
                'github_repo' => 'mikeyperes/smc-organization-profile-integration',
                'required'    => false,
                'recommended' => true,
                'checks'      => [ 'installed' => true, 'active' => true ],
                'notes'       => 'Canonical owner of Organization fields, output, and schema.',
            ],
        ];
    }

    public static function register(): void {
        if ( self::$registered ) {
            return;
        }

        PluginRecommendationRegistry::register_hexa_plugin(
            [
                'id'          => 'sfpf-person-profile-integration',
                'name'        => 'SFPF Person Profile Integration',
                'plugin_file' => SFPF_PLUGIN_BASENAME,
                'repo'        => 'mikeyperes/sfpf-person-profile-integration',
                'definitions' => self::definitions(),
            ]
        );

        if ( is_admin() ) {
            add_action( 'admin_notices', [ self::class, 'render_notice' ] );
        }

        self::$registered = true;
    }

    public static function render_notice(): void {
        if ( class_exists( 'ACF' ) || function_exists( 'get_field' ) ) {
            return;
        }

        $definition = new PluginCheckDefinition( self::definitions()[1] );
        $status     = PluginCheckService::status( $definition );
        if ( ! empty( $status['active'] ) ) {
            return;
        }

        echo '<div class="notice notice-warning"><p><strong>SFPF Person Profile Integration:</strong> '
            . 'Advanced Custom Fields Pro is recommended for the optional Person and Book field structures.'
            . '</p></div>';
    }
}
