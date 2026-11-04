<?php
declare( strict_types=1 );
namespace SFPF\PersonProfile\Pages;
defined( 'ABSPATH' ) || exit;

/** Canonical catalog; legacy structure helpers retain their existing tree and keys. */
final class PageTypes {
    public static function structure(): array {
    return [
        'biography' => [
            'title' => 'Biography',
            'slug' => 'biography',
            'parent' => null,
            'children' => [
                'education' => [
                    'title' => 'Education',
                    'slug' => 'education',
                ],
                'location_born' => [
                    'title' => 'Location Born',
                    'slug' => 'location-born',
                ],
                'organizations_founded' => [
                    'title' => 'Organizations Founded',
                    'slug' => 'organizations-founded',
                ],
                'alternate_names' => [
                    'title' => 'Alternate Names',
                    'slug' => 'alternate-names',
                ],
            ],
        ],
        'professions' => [
            'title' => 'Professions',
            'slug' => 'professions',
            'parent' => null,
            'children' => [],
        ],
        'recent_articles' => [
            'title' => 'Recent Articles',
            'slug' => 'recent-articles',
            'parent' => null,
            'children' => [],
        ],
        'additional_urls' => [
            'title' => 'Additional URLs',
            'slug' => 'additional-urls',
            'parent' => null,
            'children' => [],
        ],
        'connect' => [
            'title' => 'Connect',
            'slug' => 'connect',
            'parent' => null,
            'children' => [],
        ],
        'faqs' => [
            'title' => 'FAQs',
            'slug' => 'faqs',
            'parent' => null,
            'children' => [],
        ],
        'gallery' => [
            'title' => 'Gallery',
            'slug' => 'gallery',
            'parent' => null,
            'children' => [],
        ],
    ];
    }

    public static function definitions(): array {
        $pages = [];
        foreach ( self::structure() as $key => $page ) {
            $children = $page['children'] ?? [];
            unset( $page['children'] );
            $pages[$key] = $page;
            foreach ( $children as $child_key => $child ) {
                $pages[$child_key] = $child + [ 'parent' => $key ];
            }
        }
        // The legacy tree retains this assignment; its definition belongs to Base Tools.
        unset( $pages['faqs'] );
        foreach ( $pages as $key => &$page ) {
            $page += [ 'kind' => 'page', 'design' => 'text', 'enabled_by_default' => true, 'conditions' => [], 'content_source' => 'Person website founder fields', 'slugs' => [] ];
            $id = (int) get_option( 'sfpf_page_' . $key, 0 );
            if ( $id && get_post( $id ) ) {
                $page['url'] = get_permalink( $id );
                $page['slug'] = trim( (string) wp_parse_url( $page['url'], PHP_URL_PATH ), '/' );
            }
        }
        unset( $page );
        // Media pages: the header's Media menu links to these.
        foreach ( [
            'media'      => [ 'title' => 'Media', 'slug' => 'media', 'content_source' => 'Recent Articles, Interviews and Podcasts' ],
            'interviews' => [ 'title' => 'Interviews', 'slug' => 'interviews', 'content_source' => 'Interview content type' ],
            'podcasts'   => [ 'title' => 'Podcasts', 'slug' => 'podcasts', 'content_source' => 'Podcasts the person hosts or appears on' ],
        ] as $key => $page ) {
            $pages[$key] = $page + [ 'parent' => '', 'kind' => 'page', 'design' => 'custom', 'enabled_by_default' => true, 'conditions' => [], 'slugs' => [] ];
        }
        // Every content type this plugin defines, switched on or not: its single page, and its list page where it has one.
        foreach ( \SFPF\PersonProfile\ContentTypes\PersonContentTypes::content_types()->definitions() as $definition ) {
            $type      = (string) ( $definition['post_type']['key'] ?? $definition['id'] ?? '' );
            $archive   = $definition['post_type']['args']['has_archive'] ?? false;
            $condition = 'sfpf_content_type_settings.' . ( $definition['id'] ?? $type ) . '.enabled';
            $source    = 'PersonContentTypes::content_types';
            $slug      = (string) ( $definition['post_type']['rewrite_slug'] ?? $type );
            if ( '' === $type ) { continue; }
            $pages[$type] = [ 'title' => (string) ( $definition['post_type']['singular'] ?? ucfirst( $type ) ), 'slug' => '/' . trim( $slug, '/' ) . '/<name>/', 'parent' => '', 'kind' => 'profile', 'design' => 'custom', 'enabled_by_default' => false, 'conditions' => [ $condition ], 'content_source' => $source, 'slug_source' => 'Registered content-type rewrite settings' ];
            if ( $archive ) {
                $path = is_string( $archive ) ? $archive : $slug;
                $pages[$type . '_archive'] = [ 'title' => (string) ( $definition['post_type']['plural'] ?? ucfirst( $type ) ), 'slug' => trim( $path, '/' ), 'url' => home_url( '/' . trim( $path, '/' ) . '/' ), 'kind' => 'layout', 'design' => 'custom', 'enabled_by_default' => false, 'conditions' => [ $condition ], 'content_source' => $source ];
            }
        }
        return $pages;
    }

    public static function pages(): array {
        return array_filter( self::definitions(), static fn( array $page ): bool => 'page' === $page['kind'] );
    }
}
