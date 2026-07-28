<?php

declare( strict_types=1 );

namespace {
    if ( ! defined( 'ABSPATH' ) ) {
        define( 'ABSPATH', dirname( __DIR__ ) . '/' );
    }

    $GLOBALS['sfpf_wikimedia_test_fields'] = [
        'wikimedia_commons_urls' => [
            [ 'url' => 'https://commons.wikimedia.org/wiki/File:Portrait.jpg' ],
            [ 'url' => 'javascript:alert(1)' ],
            [ 'url' => 'https://commons.wikimedia.org/wiki/File:Portrait.jpg' ],
        ],
    ];

    function get_field( string $field, mixed $post_id = null ): mixed {
        unset( $post_id );
        return $GLOBALS['sfpf_wikimedia_test_fields'][ $field ] ?? null;
    }

    function get_site_url(): string {
        return 'https://example.com';
    }

    function get_posts( array $args = [] ): array {
        unset( $args );
        return [];
    }

    function get_avatar_url( int $user_id, array $args = [] ): string {
        unset( $user_id, $args );
        return 'https://example.com/avatar.jpg';
    }

    function sanitize_text_field( mixed $value ): string {
        return trim( strip_tags( (string) $value ) );
    }

    function wp_strip_all_tags( mixed $value ): string {
        return strip_tags( (string) $value );
    }

    function esc_url_raw( mixed $value ): string {
        $url = trim( (string) $value );
        return filter_var( $url, FILTER_VALIDATE_URL ) ? $url : '';
    }
}

namespace sfpf_person_website {
    function get_founder_full_info(): array {
        return [
            'id'           => 9,
            'display_name' => 'Example Person',
            'first_name'   => 'Example',
            'last_name'    => 'Person',
            'email'        => '',
            'urls'         => [],
            'avatar_url'   => '',
        ];
    }

    require_once dirname( __DIR__ ) . '/schema/schema-builder.php';

    $schema = build_person_schema();
    $images = is_array( $schema['image'] ?? null ) ? $schema['image'] : [ $schema['image'] ?? '' ];
    $expected = [
        'https://commons.wikimedia.org/wiki/File:Portrait.jpg',
        'https://example.com/avatar.jpg',
    ];

    if ( $expected !== $images ) {
        fwrite( STDERR, 'FAIL: Wikimedia Commons URLs were not validated, deduplicated, and merged into Person schema images.' . PHP_EOL );
        exit( 1 );
    }

    echo 'PASS: Wikimedia Commons URLs are validated and merged into Person schema images.' . PHP_EOL;
}
