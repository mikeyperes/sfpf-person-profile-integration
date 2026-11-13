<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Sitemaps;

use Hexa\PluginCore\CoreContracts\ModuleInterface;
use Hexa\PluginCore\Sitemaps\SitemapProviders;
use SFPF\PersonProfile\PluginRuntime\PluginMetadata;

defined( 'ABSPATH' ) || exit;

/**
 * The sitemaps this plugin owns: its content types (books, organizations,
 * press releases, interviews, contributing profiles, quotes, awards and
 * exhibitions), and the person-website rule for the authors sitemap. The
 * Sitemaps tab shows them; HWS Base Tools lists them read-only.
 */
final class SitemapTypes implements ModuleInterface {
    public const SLUG = PluginMetadata::SLUG;

    /** @return list<array<string,mixed>> */
    public static function definitions(): array {
        $why  = 'Pages about the person\'s work help search engines build their profile.';
        $defs = [];
        foreach ( [ 'book', 'organization', 'press-release', 'interview', 'contributing-profile' ] as $type ) {
            $defs[] = [ 'id' => 'post_type_' . $type, 'recommended' => true, 'why' => $why ];
        }
        foreach ( [ 'quote' => 'Quotes', 'award' => 'Awards', 'exhibition' => 'Exhibitions' ] as $type => $label ) {
            $defs[] = [ 'id' => 'post_type_' . $type, 'recommended' => static fn( array $row ): bool => ! empty( $row['recommended'] ), 'why' => $label . ' follow the usual content type rule once the content type is on.' ];
        }
        // A person website lists its authors only when someone besides the person writes.
        $defs[] = [ 'id' => 'authors', 'site_types' => [ 'personal_website' ], 'recommended' => static fn( array $row ): bool => (int) ( $row['counts']['would_show'] ?? 0 ) > 1, 'why' => 'On a person website, only when more than one author has posts.' ];
        return $defs;
    }

    public function register(): void {
        SitemapProviders::register( self::SLUG, PluginMetadata::NAME, PluginMetadata::admin_url( 'sitemaps' ), self::definitions() );
    }
}
