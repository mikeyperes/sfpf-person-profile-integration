<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Admin\Tabs;

use Hexa\PluginCore\WpAdminTabs\TabDefinition;
use Hexa\PluginCore\WpAdminTabs\TabRegistry;

defined( 'ABSPATH' ) || exit;

/** Defines the admin tabs without loading their renderers until a tab opens. */
final class DashboardTabs {
    private const GROUPS = [ 'Overview', 'Person', 'Schema', 'Content', 'Site', 'System' ];

    private const TABS = [
        'overview'      => [ 'Status', 'Overview', StatusTab::class ],
        'person'        => [ 'Person Values', 'Person', PersonTab::class ],
        'press-releases'=> [ 'Press Releases', 'Content', PressReleasesTab::class ],
        'shortcodes'    => [ 'Shortcodes', 'Person', ShortcodesTab::class ],
        'schema'        => [ 'Schema', 'Schema', SchemaTab::class ],
        'content-types' => [ 'Content Types', 'Content', ContentTypesTab::class ],
        'awards'        => [ 'Awards', 'Content', ContentTypesTab::class ],
        'exhibitions'   => [ 'Exhibitions', 'Content', ContentTypesTab::class ],
        'pages'         => [ 'Pages & Menus', 'Site', PagesTab::class ],
        'templates'     => [ 'Loop Templates', 'Site', TemplatesTab::class ],
        'faq'           => [ 'FAQ Sets', 'Site', FaqTab::class ],
        'breadcrumbs'   => [ 'Breadcrumbs', 'Site', BreadcrumbsTab::class ],
        'settings'      => [ 'Settings', 'Site', SettingsTab::class ],
        'sitemaps'      => [ 'Sitemaps', 'Site', SitemapsTab::class ],
        'update-center' => [ 'Update Center', 'System', UpdateCenterTab::class ],
        'debug'         => [ 'Debug', 'System', DebugTab::class ],
    ];

    /** Provides SFPF tab definitions to Hexa WP Core. */
    public static function registry(): TabRegistry {
        $registry = new TabRegistry();
        foreach ( self::TABS as $id => [ $label, , $class ] ) {
            $registry->add(
                new TabDefinition(
                    $id,
                    $label,
                    static function () use ( $id, $class ): void {
                        $tab = self::create( $id, $class );
                        $tab->render();
                    }
                )
            );
        }
        return $registry;
    }

    /** Places site and extension tabs into the dashboard's named groups. */
    public static function groups( array $tabs ): array {
        $groups = array_fill_keys( self::GROUPS, [] );
        foreach ( array_keys( $tabs ) as $id ) {
            $groups[ self::TABS[ $id ][1] ?? 'System' ][] = (string) $id;
        }

        $result = [];
        foreach ( $groups as $label => $ids ) {
            if ( [] !== $ids ) {
                $result[] = [ 'label' => $label, 'tabs' => $ids ];
            }
        }
        return $result;
    }

    /** Preserves the existing tab objects for callers of Dashboard::tabs(). */
    public static function tab_objects(): array {
        $tabs = [];
        foreach ( self::TABS as $id => [ , , $class ] ) {
            $tabs[ $id ] = self::create( $id, $class );
        }
        return $tabs;
    }

    /** Supplies the existing focused content views for Awards and Exhibitions. */
    private static function create( string $id, string $class ): Tab {
        return match ( $id ) {
            'awards' => new ContentTypesTab( 'awards', 'Awards', [ 'award' ], [], 'Awards the person received. Turn the content type on here; each award gets a page at /award/name/, the list is at /awards/, and every award is added to the person\'s schema.' ),
            'exhibitions' => new ContentTypesTab( 'exhibitions', 'Exhibitions', [ 'exhibition' ], [], 'Exhibitions, shows and fairs that showed the person\'s work. Each gets a page at /exhibition/name/ with ExhibitionEvent schema, and the list is at /exhibitions/.' ),
            default => new $class(),
        };
    }
}
