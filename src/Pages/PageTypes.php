<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Pages;

use SFPF\PersonProfile\ContentTypes\ContentTypeCatalog;
use SFPF\PersonProfile\Settings\Settings;

defined( 'ABSPATH' ) || exit;

/**
 * The one list of person website pages: each page's key, title, address,
 * parent, design (its own design or the shared text-page design), default
 * state and content source, plus every content type's single and list
 * layouts. Setup and redesign tools read this catalog; the Pages tab creates
 * and links the pages from it. Keys and option names are stored data.
 */
final class PageTypes {
    /**
     * The person pages as a tree: top-level pages with their children.
     *
     * @return array<string,array<string,mixed>>
     */
    public static function structure(): array {
        return [
            'biography'       => [
                'title'       => 'Biography',
                'slug'        => 'biography',
                'design'      => 'custom',
                'description' => 'The person\'s full profile. Search engines read it as the person\'s profile page.',
                'children'    => [
                    'education'             => [ 'title' => 'Education', 'slug' => 'education', 'description' => 'Schools, degrees and years.' ],
                    'location_born'         => [ 'title' => 'Location Born', 'slug' => 'location-born', 'description' => 'Where the person was born.' ],
                    'organizations_founded' => [ 'title' => 'Organizations Founded', 'slug' => 'organizations-founded', 'description' => 'The organizations the person founded.' ],
                    'alternate_names'       => [ 'title' => 'Alternate Names', 'slug' => 'alternate-names', 'description' => 'Other names the person is known by.' ],
                ],
            ],
            'professions'     => [ 'title' => 'Professions', 'slug' => 'professions', 'description' => 'The person\'s professions; each one can have its own page under this one.' ],
            'recent_articles' => [ 'title' => 'Recent Articles', 'slug' => 'recent-articles', 'description' => 'Articles by or about the person.' ],
            'additional_urls' => [ 'title' => 'Additional URLs', 'slug' => 'additional-urls', 'description' => 'Other pages about the person around the web.' ],
            'gallery'         => [ 'title' => 'Gallery', 'slug' => 'gallery', 'description' => 'The person\'s public photos.' ],
            'connect'         => [ 'title' => 'Connect', 'slug' => 'connect', 'design' => 'custom', 'description' => 'How to reach the person, and their social profiles.' ],
            'faqs'            => [ 'title' => 'FAQs', 'slug' => 'faqs', 'description' => 'The person\'s questions and answers. HWS Base Tools also lists an FAQs page; link the same page in both.' ],
            'media'           => [ 'title' => 'Media', 'slug' => 'media', 'design' => 'custom', 'description' => 'Recent articles, interviews and podcasts in one place.' ],
            'interviews'      => [ 'title' => 'Interviews', 'slug' => 'interviews', 'design' => 'custom', 'description' => 'Interviews with the person.' ],
            'podcasts'        => [ 'title' => 'Podcasts', 'slug' => 'podcasts', 'design' => 'custom', 'description' => 'Podcasts the person hosts or appears on.' ],
        ];
    }

    /**
     * The flat catalog: every page and layout with its full definition.
     *
     * @return array<string,array<string,mixed>>
     */
    public static function definitions(): array {
        $pages = [];
        foreach ( self::structure() as $key => $page ) {
            $children = (array) ( $page['children'] ?? [] );
            unset( $page['children'] );
            $pages[ $key ] = self::page( $key, $page, '' );
            foreach ( $children as $child_key => $child ) {
                $pages[ $child_key ] = self::page( $child_key, $child, $key );
            }
        }
        // HWS Base Tools owns the FAQs page type; the person's FAQ only reuses its link here.
        unset( $pages['faqs'] );
        foreach ( ContentTypeCatalog::content_types()->definitions() as $definition ) {
            $type    = (string) ( $definition['post_type']['key'] ?? $definition['id'] ?? '' );
            $slug    = (string) ( $definition['post_type']['rewrite_slug'] ?? $type );
            $archive = $definition['post_type']['args']['has_archive'] ?? false;
            $rule    = Settings::CONTENT_TYPES . '.' . ( $definition['id'] ?? $type ) . '.enabled';
            if ( '' === $type ) {
                continue;
            }
            $pages[ $type ] = [
                'key' => $type, 'title' => (string) ( $definition['post_type']['singular'] ?? $type ), 'slug' => '/' . trim( $slug, '/' ) . '/<name>/',
                'slugs' => [], 'parent' => '', 'kind' => 'profile', 'design' => 'custom', 'enabled_by_default' => false, 'conditions' => [ $rule ],
                'content_source' => 'The ' . (string) ( $definition['post_type']['singular'] ?? $type ) . ' content type', 'slug_source' => 'Registered content-type rewrite settings',
            ];
            if ( $archive ) {
                $path = is_string( $archive ) ? $archive : $slug;
                $pages[ $type . '_archive' ] = [
                    'key' => $type . '_archive', 'title' => (string) ( $definition['post_type']['plural'] ?? $type ), 'slug' => trim( $path, '/' ), 'url' => home_url( '/' . trim( $path, '/' ) . '/' ),
                    'slugs' => [], 'parent' => '', 'kind' => 'layout', 'design' => 'custom', 'enabled_by_default' => false, 'conditions' => [ $rule ],
                    'content_source' => 'The list of ' . strtolower( (string) ( $definition['post_type']['plural'] ?? $type ) ),
                ];
            }
        }
        return $pages;
    }

    /** @return array<string,array<string,mixed>> Ordinary pages only. */
    public static function pages(): array {
        return array_filter( self::definitions(), static fn( array $page ): bool => 'page' === $page['kind'] );
    }

    /** @return array<string,int> Page key => linked page ID, for the pages that are linked. */
    public static function linked_pages(): array {
        $linked = [];
        foreach ( self::structure() as $key => $page ) {
            foreach ( array_merge( [ $key ], array_keys( (array) ( $page['children'] ?? [] ) ) ) as $page_key ) {
                $id = Settings::assigned_page( $page_key );
                if ( $id > 0 ) {
                    $linked[ $page_key ] = $id;
                }
            }
        }
        return $linked;
    }

    /** The person page key a page is linked to, or an empty string. */
    public static function key_for_page( int $page_id ): string {
        $key = array_search( $page_id, self::linked_pages(), true );
        return false === $key ? '' : (string) $key;
    }

    /** The page HWS Base Tools links for one of its own page types (FAQs, Contact…), or 0. */
    public static function base_tools_page( string $key ): int {
        $id = function_exists( 'hws_base_tools_linked_page' ) ? absint( hws_base_tools_linked_page( $key ) ) : absint( get_option( 'hws_site_page_assignment_' . sanitize_key( $key ), 0 ) );
        return $id > 0 && 'page' === get_post_type( $id ) ? $id : 0;
    }

    /** A page under the Professions page: one profession's own page. */
    public static function is_profession_page( int $page_id ): bool {
        $parent = Settings::assigned_page( 'professions' );
        return $parent > 0 && $page_id > 0 && (int) wp_get_post_parent_id( $page_id ) === $parent;
    }

    /**
     * @param array<string,mixed> $page
     * @return array<string,mixed>
     */
    private static function page( string $key, array $page, string $parent ): array {
        $definition = $page + [
            'key' => $key, 'slugs' => [], 'parent' => $parent, 'kind' => 'page', 'design' => 'text', 'enabled_by_default' => true,
            'conditions' => [], 'content_source' => 'PageTemplates::default_template(' . $key . ')', 'description' => '',
        ];
        $id = Settings::assigned_page( $key );
        if ( $id > 0 ) {
            $definition['url']  = (string) get_permalink( $id );
            $definition['slug'] = trim( (string) wp_parse_url( $definition['url'], PHP_URL_PATH ), '/' );
        }
        return $definition;
    }
}
