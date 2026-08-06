<?php

declare( strict_types=1 );

namespace {
    if ( ! defined( 'ABSPATH' ) ) {
        define( 'ABSPATH', dirname( __DIR__ ) . '/' );
    }

    $GLOBALS['sfpf_wikimedia_test_fields'] = [
        'founder' => [
            'founder_user' => 9,
        ],
        'wikimedia_commons_urls' => [
            [ 'url' => 'https://commons.wikimedia.org/wiki/File:Portrait.jpg' ],
            [ 'url' => 'javascript:alert(1)' ],
            [ 'url' => 'https://commons.wikimedia.org/wiki/File:Portrait.jpg' ],
        ],
        'sameas' => "https://example.com/profile\nhttps://www.wikidata.org/wiki/Q12345",
        'urls_wikidata' => "https://www.wikidata.org/wiki/Q12345\nhttps://www.wikidata.org/wiki/Q67890",
        'additional_urls' => [
            [ 'url' => 'https://www.wikidata.org/wiki/Q12345' ],
            [ 'url' => 'https://publisher.test/profile' ],
        ],
    ];

    function get_field( string $field, mixed $post_id = null ): mixed {
        unset( $post_id );
        return $GLOBALS['sfpf_wikimedia_test_fields'][ $field ] ?? null;
    }

    function get_site_url(): string {
        return 'https://example.com';
    }

    function get_userdata( int $user_id ): object|false {
        if ( 9 !== $user_id ) {
            return false;
        }

        return (object) [
            'display_name' => 'Example Person',
            'first_name'   => 'Example',
            'last_name'    => 'Person',
            'user_email'   => '',
            'description'  => '',
            'user_url'     => '',
        ];
    }

    function get_edit_user_link( int $user_id ): string {
        return 'https://example.com/wp-admin/user-edit.php?user_id=' . $user_id;
    }

    function get_author_posts_url( int $user_id ): string {
        return 'https://example.com/author/' . $user_id . '/';
    }

    function get_posts( array $args = [] ): array {
        unset( $args );
        return [];
    }

    final class WP_Query {
        public array $posts = [];
        public int $found_posts = 0;

        public function __construct( array $args = [] ) {
            unset( $args );
        }
    }

    function apply_filters( string $hook, mixed $value, mixed ...$args ): mixed {
        unset( $hook, $args );
        return $value;
    }

    function do_action( string $hook, mixed ...$args ): void {
        unset( $hook, $args );
    }

    function sanitize_key( mixed $key ): string {
        return preg_replace( '/[^a-z0-9_\-]/', '', strtolower( (string) $key ) ) ?? '';
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
    require_once __DIR__ . '/load-core-data-normalization.php';
    require_once dirname( __DIR__ ) . '/includes/helper-functions.php';
    require_once dirname( __DIR__ ) . '/includes/frontend-query-bounds.php';
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

    $same_as = $schema['sameAs'] ?? [];
    $expected_same_as = [
        'https://example.com/profile',
        'https://www.wikidata.org/wiki/Q12345',
        'https://www.wikidata.org/wiki/Q67890',
        'https://publisher.test/profile',
    ];

    if ( $expected_same_as !== $same_as ) {
        fwrite( STDERR, 'FAIL: Wikidata URLs were not retained and deduplicated in Person schema sameAs.' . PHP_EOL );
        exit( 1 );
    }

    echo 'PASS: Wikimedia Commons images and schema-only Wikidata sameAs URLs are normalized.' . PHP_EOL;
}
