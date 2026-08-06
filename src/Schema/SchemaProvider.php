<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Schema;

defined( 'ABSPATH' ) || exit;

final class SchemaProvider {
    /** @return array<string,mixed> */
    public static function current(): array {
        if ( ! function_exists( 'sfpf_person_website\\build_person_schema' ) ) {
            return [];
        }

        if ( is_front_page() ) {
            $type = (string) get_option( 'sfpf_homepage_schema_type', 'person' );
            return 'none' === $type ? [] : self::decode( \sfpf_person_website\build_homepage_schema_for_injection( $type, \sfpf_person_website\get_front_page_id() ) );
        }
        if ( is_singular( 'book' ) ) {
            return (array) \sfpf_person_website\build_book_schema( get_queried_object_id() );
        }
        if ( is_page() ) {
            $page_id = get_queried_object_id();
            $biography_id = (int) get_option( 'sfpf_page_biography', 0 );
            $type = (string) get_option( 'sfpf_biography_schema_type', 'profile_page_only' );
            if ( $biography_id > 0 && $page_id === $biography_id && 'none' !== $type ) {
                return self::decode( \sfpf_person_website\build_homepage_schema_for_injection( $type, $page_id ) );
            }
        }
        return [];
    }

    /** @return array<string,mixed> */
    private static function decode( mixed $schema ): array {
        if ( is_array( $schema ) ) return $schema;
        if ( ! is_string( $schema ) || '' === $schema ) return [];
        $decoded = json_decode( $schema, true );
        return is_array( $decoded ) ? $decoded : [];
    }
}
