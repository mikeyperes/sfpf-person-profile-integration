<?php

declare( strict_types=1 );

define( 'ABSPATH', dirname( __DIR__ ) . '/' );

require dirname( __DIR__ ) . '/snippets/register-acf-profile-content-types.php';

$groups = [
    'press-release' => sfpf_person_website\press_release_acf_field_group(),
    'interview' => sfpf_person_website\interview_acf_field_group(),
    'contributing-profile' => sfpf_person_website\contributing_profile_acf_field_group(),
];

$expected = [
    'press-release' => [ 'group_sfpf_press_release', [ 'sfpf_press_release_source_name', 'sfpf_press_release_original_url', 'sfpf_press_release_date', 'sfpf_press_release_featured' ] ],
    'interview' => [ 'group_64b623fb36208', [ 'podcast_name', 'guest_name', 'host_name', 'primary_url', 'additional_links', 'press' ] ],
    'contributing-profile' => [ 'group_64bb58d10c008', [ 'url', 'secondary_logo', 'featured_item', 'primary_featured_item' ] ],
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

    if ( $group_key !== ( $group['key'] ?? '' ) || $field_names !== $names || '@post_type' !== $location ) {
        fwrite( STDERR, 'Invalid ACF structure for ' . $post_type . PHP_EOL );
        exit( 1 );
    }
}

echo 'PASS: Profile content-type ACF structures preserve the migrated field contracts.' . PHP_EOL;
