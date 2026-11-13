<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Support;

use Hexa\PluginCore\CoreContracts\ModuleInterface;
use Hexa\PluginCore\PluginChecks\PluginRecommendationRegistry;
use SFPF\PersonProfile\PluginRuntime\PluginMetadata;

defined( 'ABSPATH' ) || exit;

/** The plugins SFPF needs or recommends, checked by Hexa WP Core's plugin checks. */
final class Requirements implements ModuleInterface {
    /** @return list<array<string,mixed>> */
    public static function definitions(): array {
        return [
            [
                'id' => 'hws-base-tools', 'name' => 'HWS Base Tools', 'plugin_file' => 'hws-base-tools/hws-base-tools.php', 'slug' => 'hws-base-tools',
                'source' => 'github', 'github_repo' => 'mikeyperes/hws-base-tools', 'required' => true, 'checks' => [ 'installed' => true, 'active' => true ],
                'notes' => 'Chooses the person the website is about (its primary entity), owns the social links on the profile, and controls Rank Math\'s schema.',
            ],
            [
                'id' => 'svg-support', 'name' => 'SVG Support', 'plugin_file' => 'svg-support/svg-support.php', 'slug' => 'svg-support',
                'source' => 'wordpress_org', 'required' => false, 'recommended' => true, 'checks' => [ 'installed' => true, 'active' => true ],
                'notes' => 'Makes SVG logos on Quotes safe to upload; other image types do not need it.',
            ],
        ];
    }

    public function register(): void {
        PluginRecommendationRegistry::register_hexa_plugin(
            [
                'id'          => PluginMetadata::SLUG,
                'name'        => PluginMetadata::NAME,
                'plugin_file' => PluginMetadata::basename(),
                'repo'        => PluginMetadata::GITHUB_REPOSITORY,
                'definitions' => self::definitions(),
            ]
        );
    }
}
