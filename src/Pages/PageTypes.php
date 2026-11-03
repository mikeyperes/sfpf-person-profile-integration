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
        foreach ( [ 'book', 'organization', 'testimonial' ] as $type ) {
            $object = get_post_type_object( $type );
            if ( ! $object ) { continue; }
            $condition = 'sfpf_content_type_settings.' . $type . '.enabled';
            $slug = $object ? (string) ( $object->rewrite['slug'] ?? $type ) : $type;
            $pages[$type] = [ 'title' => $object->labels->singular_name ?? ucfirst( $type ), 'slug' => '/' . trim( $slug, '/' ) . '/<name>/', 'parent' => '', 'kind' => 'profile', 'design' => 'custom', 'enabled_by_default' => false, 'conditions' => [ $condition ], 'content_source' => 'PersonContentTypes::content_types', 'slug_source' => 'Registered content-type rewrite settings' ];
            if ( $object && $object->has_archive ) {
                $url = get_post_type_archive_link( $type );
                $pages[$type . '_archive'] = [ 'title' => $object->labels->name, 'slug' => trim( (string) wp_parse_url( $url, PHP_URL_PATH ), '/' ), 'url' => $url, 'kind' => 'layout', 'design' => 'custom', 'enabled_by_default' => false, 'conditions' => [ $condition ], 'content_source' => 'PersonContentTypes::content_types' ];
            }
        }
        return $pages;
    }

    public static function pages(): array {
        return array_filter( self::definitions(), static fn( array $page ): bool => 'page' === $page['kind'] );
    }
}
