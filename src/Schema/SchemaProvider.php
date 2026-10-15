<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Schema;

use Hexa\PluginCore\SchemaTools\SchemaPage;

defined( 'ABSPATH' ) || exit;

/**
 * Decides which pages of a person site carry SFPF schema (the homepage, the
 * biography page and each book) and adds them to HexaWP Core's shared schema
 * block. Organization pages belong to SMC.
 */
final class SchemaProvider {
    public const SOURCE = 'sfpf-person';
    public const PRIORITY = 10;

    public static function register(): void {
        SchemaPage::register( self::SOURCE, [ self::class, 'current' ], self::PRIORITY );
    }

    /** @return list<array<string,mixed>> */
    public static function current(): array {
        if ( ! function_exists( 'sfpf_person_website\\build_profile_page_nodes' ) ) {
            return [];
        }

        if ( is_front_page() ) {
            $type = (string) get_option( 'sfpf_homepage_schema_type', 'person' );
            return \sfpf_person_website\build_profile_page_nodes( $type, \sfpf_person_website\get_front_page_id() );
        }
        if ( is_singular( 'book' ) ) {
            return \sfpf_person_website\build_book_nodes( get_queried_object_id() );
        }
        if ( is_page() ) {
            $page_id = get_queried_object_id();
            $biography_id = (int) get_option( 'sfpf_page_biography', 0 );
            if ( $biography_id > 0 && $page_id === $biography_id ) {
                return \sfpf_person_website\build_profile_page_nodes( (string) get_option( 'sfpf_biography_schema_type', 'profile_page_only' ), $page_id );
            }
        }
        return [];
    }
}
