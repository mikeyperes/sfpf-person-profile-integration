<?php

declare( strict_types=1 );

namespace {
    if ( ! defined( 'ABSPATH' ) ) {
        define( 'ABSPATH', dirname( __DIR__ ) . '/' );
    }

    $GLOBALS['sfpf_dn_fields'] = [
        'zero'        => 0,
        'empty_array' => [],
        'false_value' => false,
    ];

    function absint( mixed $value ): int {
        return abs( (int) $value );
    }

    function get_field( string $name, mixed $context = false ): mixed {
        unset( $context );
        return $GLOBALS['sfpf_dn_fields'][ $name ] ?? null;
    }

    function esc_url_raw( mixed $value ): string {
        return str_replace( 'raw.example.test', 'sanitized.example.test', trim( (string) $value ) );
    }

    function wp_strip_all_tags( mixed $value, bool $remove_breaks = false ): string {
        unset( $remove_breaks );
        return strip_tags( (string) $value );
    }

    function wp_get_attachment_image_src( int $id, string $size ): array|false {
        if ( 5 !== $id ) {
            return false;
        }
        return 'full' === $size ? [ 'https://cdn.example.test/attachment-full.jpg', 1200, 800 ] : false;
    }

    function get_post_meta( int $id, string $key, bool $single = true ): string {
        unset( $id, $key, $single );
        return 'Attachment alt';
    }

    function get_the_title( int $id ): string {
        unset( $id );
        return 'Attachment title';
    }

    function wp_get_attachment_caption( int $id ): string {
        unset( $id );
        return 'Attachment caption';
    }
}

namespace sfpf_person_website {
    require_once __DIR__ . '/load-core-data-normalization.php';
    require_once dirname( __DIR__ ) . '/includes/helper-functions.php';
    require_once dirname( __DIR__ ) . '/schema/schema-builder.php';

    $failures = [];
    $assert = static function ( bool $condition, string $message ) use ( &$failures ): void {
        if ( ! $condition ) {
            $failures[] = $message;
        }
    };

    $assert(
        [ 'https://raw.example.test/one', 'ftp://files.example.test/two' ] === sfpf_normalize_url_values( [
            [ 'https://raw.example.test/one' ],
            "ftp://files.example.test/two\ninvalid",
            'https://raw.example.test/one',
        ] ),
        'Nested URL compatibility changed order, schemes, sanitization, or deduplication.'
    );
    $assert( 0 === get_acf_field( 'zero', false, 'fallback' ), 'get_acf_field no longer preserves zero.' );
    $assert( [] === get_acf_field( 'empty_array', false, 'fallback' ), 'get_acf_field no longer preserves an empty array.' );
    $assert( 'fallback' === get_acf_field( 'false_value', false, 'fallback' ), 'get_acf_field no longer defaults false.' );
    $assert( 'fallback' === _sf( 'zero', 7, 'fallback' ), '_sf no longer applies its historical empty() default policy.' );
    $assert(
        [ '<b>Repeated</b>', '<b>Repeated</b>' ] === _repeater_values( [
            [ 'name' => '<b>Repeated</b>' ],
            [ 'name' => '0' ],
            [ 'name' => '<b>Repeated</b>' ],
        ] ),
        'Repeater compatibility changed raw values, falsey omission, or duplicates.'
    );

    $attachment = sfpf_gallery_image_from_attachment( 5, 'thumbnail' );
    $assert(
        is_array( $attachment )
        && [ 'id', 'url', 'full_url', 'alt', 'title', 'caption' ] === array_keys( $attachment )
        && 'https://cdn.example.test/attachment-full.jpg' === $attachment['url'],
        'Attachment compatibility no longer returns the fixed shape with full-size fallback.'
    );

    $gallery = sfpf_normalize_gallery_images( json_encode( [
        [ 'url' => 'https://cdn.example.test/duplicate.jpg', 'title' => 'First' ],
        [ 'url' => 'https://cdn.example.test/duplicate.jpg', 'title' => 'Second' ],
        'https://cdn.example.test/plain.jpg',
    ] ) );
    $assert(
        2 === count( $gallery )
        && 'First' === $gallery[0]['title']
        && 'plain.jpg' === $gallery[1]['title'],
        'Gallery compatibility changed JSON handling, first-duplicate precedence, or URL titles.'
    );
    $assert( [] === sfpf_normalize_gallery_images( 5 ), 'A non-string scalar gallery is no longer ignored.' );

    if ( [] !== $failures ) {
        foreach ( $failures as $failure ) {
            fwrite( STDERR, 'FAIL: ' . $failure . PHP_EOL );
        }
        exit( 1 );
    }

    echo 'PASS: SFPF normalization callbacks remain thin, behavior-compatible Core delegates.' . PHP_EOL;
}
