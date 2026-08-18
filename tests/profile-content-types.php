<?php

declare( strict_types=1 );

define( 'ABSPATH', dirname( __DIR__ ) . '/' );

function add_action( string $hook, callable|string $callback, int $priority = 10 ): void {
    unset( $hook, $callback, $priority );
}

require dirname( __DIR__ ) . '/snippets/register-acf-profile-content-types.php';
require dirname( __DIR__ ) . '/snippets/register-acf-quote.php';
require dirname( __DIR__ ) . '/snippets/register-acf-organization.php';
require dirname( __DIR__ ) . '/snippets/register-acf-book.php';

$groups = [
    'book' => sfpf_person_website\book_acf_field_group(),
    'press-release' => sfpf_person_website\press_release_acf_field_group(),
    'interview' => sfpf_person_website\interview_acf_field_group(),
    'contributing-profile' => sfpf_person_website\contributing_profile_acf_field_group(),
    'quote' => sfpf_person_website\quote_acf_field_group(),
];

$expected = [
    'book' => [ 'group_sfpf_book', 'book' ],
    'press-release' => [ 'group_sfpf_press_release', [ 'sfpf_press_release_source_name', 'sfpf_press_release_original_url', 'sfpf_press_release_date', 'sfpf_press_release_featured' ] ],
    'interview' => [ 'group_64b623fb36208', [ 'podcast_name', 'guest_name', 'host_name', 'primary_url', 'additional_links', 'press' ] ],
    'contributing-profile' => [ 'group_64bb58d10c008', [ 'url', 'secondary_logo', 'featured_item', 'primary_featured_item' ] ],
    'quote' => [ 'group_sfpf_quote', [ 'quote', 'assigned_name', 'url', 'logos', 'publication_name', 'publication_info' ] ],
];

foreach ( $expected as $post_type => [ $group_key, $field_names ] ) {
    $group = $groups[ $post_type ];
    $names = array_values(
        array_filter(
            array_map(
                static fn( array $field ): string => (string) ( $field['name'] ?? '' ),
                (array) ( $group['fields'] ?? [] )
            )
        )
    );
    $location = (string) ( $group['location'][0][0]['value'] ?? '' );

    if ( 'book' === $post_type ) {
        $book_fields = array_column( (array) ( $group['fields'] ?? [] ), null, 'name' );
        $quotes = is_array( $book_fields['quotes'] ?? null ) ? $book_fields['quotes'] : [];
        $quote_fields = array_column( (array) ( $quotes['sub_fields'] ?? [] ), null, 'name' );
        if (
            $group_key !== ( $group['key'] ?? '' )
            || 'book' !== $location
            || 'repeater' !== ( $quotes['type'] ?? '' )
            || [ 'quote', 'url', 'tagline' ] !== array_keys( $quote_fields )
            || 'textarea' !== ( $quote_fields['quote']['type'] ?? '' )
            || 'url' !== ( $quote_fields['url']['type'] ?? '' )
            || 'text' !== ( $quote_fields['tagline']['type'] ?? '' )
        ) {
            fwrite( STDERR, 'Invalid canonical Book Quotes repeater structure.' . PHP_EOL );
            exit( 1 );
        }
        continue;
    }

    if ( $group_key !== ( $group['key'] ?? '' ) || $field_names !== $names || '@post_type' !== $location ) {
        fwrite( STDERR, 'Invalid ACF structure for ' . $post_type . PHP_EOL );
        exit( 1 );
    }
}

$quote_fields = array_column( (array) ( $groups['quote']['fields'] ?? [] ), null, 'name' );
$logos = is_array( $quote_fields['logos'] ?? null ) ? $quote_fields['logos'] : [];
if (
    'gallery' !== ( $logos['type'] ?? '' )
    || 'id' !== ( $logos['return_format'] ?? '' )
    || 'svg,png,jpg,jpeg,webp' !== ( $logos['mime_types'] ?? '' )
) {
    fwrite( STDERR, 'Quote logos must be a multi-image attachment gallery with explicit SVG and raster types.' . PHP_EOL );
    exit( 1 );
}

$organization = sfpf_person_website\organization_acf_field_group();
$organization_names = array_values(
    array_filter(
        array_map(
            static fn( array $field ): string => (string) ( $field['name'] ?? '' ),
            (array) ( $organization['fields'] ?? [] )
        )
    )
);
if (
    'group_sfpf_organization' !== ( $organization['key'] ?? '' )
    || 'organization' !== ( $organization['location'][0][0]['value'] ?? '' )
    || ! in_array( 'image_cropped', $organization_names, true )
) {
    fwrite( STDERR, 'Invalid canonical Organization ACF structure.' . PHP_EOL );
    exit( 1 );
}

echo 'PASS: Profile, Organization, Quote, and Book ACF structures preserve their field contracts.' . PHP_EOL;
