<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Dependencies;

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
                'notes'       => 'Supplies the canonical website and primary-entity contracts.',
            ],
            [
                'id'          => 'svg-support',
                'name'        => 'SVG Support',
                'plugin_file' => 'svg-support/svg-support.php',
                'slug'        => 'svg-support',
                'source'      => 'wordpress_org',
                'required'    => false,
                'recommended' => true,
                'checks'      => [ 'installed' => true, 'active' => true ],
                'notes'       => 'Sanitizes SVG files selected in optional Quote logo galleries; raster logos do not require it.',
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

        self::$registered = true;
    }
}
