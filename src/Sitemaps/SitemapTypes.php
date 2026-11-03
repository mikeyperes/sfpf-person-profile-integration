<?php

namespace SFPF\PersonProfile\Sitemaps;

use Hexa\PluginCore\CoreContracts\ModuleInterface;
use Hexa\PluginCore\Sitemaps\SitemapProviders;
use Hexa\PluginCore\Sitemaps\SitemapsPanel;

/**
 * The sitemaps this plugin owns: the person's content types (books,
 * organizations, press releases, interviews, contributing profiles, quotes).
 * HWS Base Tools shows them read-only and links to the Sitemaps tab.
 */
final class SitemapTypes implements ModuleInterface {
    public const SLUG = 'sfpf-person-profile-integration';

    public static function definitions(): array {
        $why  = 'Pages about the person\'s work help search engines build their profile.';
        $defs = [];
        foreach ( [ 'book', 'organization', 'press-release', 'interview', 'contributing-profile' ] as $type ) {
            $defs[] = [ 'id' => 'post_type_' . $type, 'recommended' => true, 'why' => $why ];
        }
        $defs[] = [ 'id' => 'post_type_quote', 'recommended' => static fn( array $row ) => ! empty( $row['recommended'] ), 'why' => 'Quotes follow the usual content type rule.' ];
        return $defs;
    }

    public function register(): void {
        if ( ! class_exists( SitemapProviders::class ) ) {
            return;
        }
        SitemapProviders::register( self::SLUG, 'SFPF Person Profile Integration', admin_url( 'options-general.php?page=sfpf-person-profile&tab=sitemaps' ), self::definitions() );
        add_filter( 'sfpf_dashboard_tabs', static function ( $tabs ) { $tabs = (array) $tabs; $tabs['sitemaps'] = [ 'label' => 'Sitemaps' ]; return $tabs; } );
        add_filter( 'sfpf_dashboard_render_tab', static function ( $rendered, $tab ) {
            if ( 'sitemaps' !== $tab ) {
                return $rendered;
            }
            SitemapsPanel::render( self::SLUG );
            return true;
        }, 10, 2 );
    }
}
