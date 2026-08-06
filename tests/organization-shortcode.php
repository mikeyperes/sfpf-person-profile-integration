<?php

declare( strict_types=1 );

define( 'ABSPATH', dirname( __DIR__ ) . '/' );

$GLOBALS['sfpf_org_fields'] = [
    101 => [
        'sub_title' => 'Canonical subtitle',
        'url' => 'https://one.example/about',
        'image_cropped' => [
            'ID' => 501,
            'url' => 'https://cdn.example/logo-full.png',
            'sizes' => [ 'large' => 'https://cdn.example/logo-large.png' ],
        ],
    ],
    102 => [
        'url' => 'https://two.example',
        'image_cropped' => '502',
    ],
    103 => [
        'image_cropped' => 'https://cdn.example/logo-direct.png',
    ],
];

function absint( mixed $value ): int {
    return abs( (int) $value );
}

function sanitize_key( string $value ): string {
    return (string) preg_replace( '/[^a-z0-9_\-]/', '', strtolower( $value ) );
}

function shortcode_atts( array $pairs, array $atts, string $shortcode = '' ): array {
    unset( $shortcode );
    return array_merge( $pairs, array_intersect_key( $atts, $pairs ) );
}

function get_post_type( int $post_id ): string {
    return in_array( $post_id, [ 101, 102, 103 ], true ) ? 'organization' : 'post';
}

function get_queried_object_id(): int {
    return 0;
}

function get_the_title( int $post_id ): string {
    return 'Organization ' . $post_id;
}

function get_field( string $field, int $post_id ): mixed {
    return $GLOBALS['sfpf_org_fields'][ $post_id ][ $field ] ?? null;
}

function get_post_meta( int $post_id, string $field, bool $single ): mixed {
    unset( $single );
    return $GLOBALS['sfpf_org_fields'][ $post_id ][ $field ] ?? null;
}

function wp_get_attachment_image_url( int $attachment_id, string $size ): string {
    return 'https://media.example/' . $attachment_id . '-' . $size . '.jpg';
}

function get_the_post_thumbnail_url( int $post_id, string $size ): string {
    return 'https://media.example/featured-' . $post_id . '-' . $size . '.jpg';
}

function get_permalink( int $post_id ): string {
    return 'https://example.test/organization/' . $post_id . '/';
}

function esc_url( string $value ): string {
    return $value;
}

function esc_attr( string $value ): string {
    return htmlspecialchars( $value, ENT_QUOTES );
}

function esc_html( mixed $value ): string {
    return htmlspecialchars( (string) $value, ENT_QUOTES );
}

function wp_kses_post( string $value ): string {
    return $value;
}

function wp_json_encode( mixed $value ): string {
    return (string) json_encode( $value );
}

require dirname( __DIR__ ) . '/includes/shortcodes/organization.php';

$cases = [
    'canonical field syntax with explicit Organization ID' => [
        [ 'field' => 'name', 'id' => '101' ],
        'Organization 101',
    ],
    'legacy snippet field and post ID syntax' => [
        [ 'id' => 'url', 'post_id' => '102' ],
        'https://two.example',
    ],
    'cropped logo array prefers requested ACF size' => [
        [ 'field' => 'logo', 'id' => '101', 'size' => 'large' ],
        'https://cdn.example/logo-large.png',
    ],
    'cropped logo numeric string resolves as attachment' => [
        [ 'field' => 'image_cropped', 'id' => '102', 'size' => 'full' ],
        'https://media.example/502-full.jpg',
    ],
    'cropped logo URL return format is preserved' => [
        [ 'field' => 'logo', 'id' => '103' ],
        'https://cdn.example/logo-direct.png',
    ],
    'featured image uses the WordPress thumbnail API' => [
        [ 'field' => 'featured_image_url', 'id' => '101', 'size' => 'large' ],
        'https://media.example/featured-101-large.jpg',
    ],
    'documented subtitle alias maps to sub_title' => [
        [ 'field' => 'subtitle', 'id' => '101' ],
        'Canonical subtitle',
    ],
    'non-Organization explicit ID is rejected' => [
        [ 'field' => 'name', 'id' => '999' ],
        '',
    ],
];

foreach ( $cases as $label => [ $atts, $expected ] ) {
    $actual = sfpf_person_website\organization_shortcode( $atts );
    if ( $expected !== $actual ) {
        fwrite( STDERR, 'FAIL: ' . $label . ' expected ' . var_export( $expected, true ) . ' got ' . var_export( $actual, true ) . PHP_EOL );
        exit( 1 );
    }
}

echo 'PASS: Organization shortcode canonical, legacy, logo, and featured-image contracts.' . PHP_EOL;
