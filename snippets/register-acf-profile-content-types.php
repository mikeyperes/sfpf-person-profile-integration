<?php

namespace sfpf_person_website;

defined( 'ABSPATH' ) || exit;

/** @return array<string,mixed> */
function press_release_acf_field_group(): array {
    return [
        'key' => 'group_sfpf_press_release',
        'title' => 'Press Release Fields',
        'fields' => [
            [
                'key' => 'field_sfpf_press_release_source_name',
                'label' => 'Source Name',
                'name' => 'sfpf_press_release_source_name',
                'type' => 'text',
                'instructions' => 'Original publisher or distribution source, when different from this website.',
            ],
            [
                'key' => 'field_sfpf_press_release_original_url',
                'label' => 'Original URL',
                'name' => 'sfpf_press_release_original_url',
                'type' => 'url',
                'instructions' => 'Canonical external URL for the original press release, when applicable.',
            ],
            [
                'key' => 'field_sfpf_press_release_date',
                'label' => 'Release Date',
                'name' => 'sfpf_press_release_date',
                'type' => 'date_picker',
                'display_format' => 'F j, Y',
                'return_format' => 'Y-m-d',
                'first_day' => 1,
            ],
            sfpf_legacy_true_false_field( 'field_sfpf_press_release_featured', 'Featured', 'sfpf_press_release_featured' ),
        ],
        'location' => [ [ [ 'param' => 'post_type', 'operator' => '==', 'value' => '@post_type' ] ] ],
        'menu_order' => 0,
        'position' => 'normal',
        'style' => 'default',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
        'active' => true,
        'show_in_rest' => 0,
    ];
}

/** @return array<string,mixed> */
function interview_acf_field_group(): array {
    return [
        'key' => 'group_64b623fb36208',
        'title' => 'Interview',
        'fields' => [
            sfpf_legacy_wysiwyg_field( 'field_64bc48f36f3a1', 'Podcast Name', 'podcast_name' ),
            sfpf_legacy_wysiwyg_field( 'field_64bf870639653', 'Guest Name', 'guest_name' ),
            sfpf_legacy_wysiwyg_field( 'field_64bf870e39654', 'Host Name', 'host_name' ),
            sfpf_legacy_text_field( 'field_64bb8d7a325e8', 'Primary URL', 'primary_url' ),
            sfpf_legacy_wysiwyg_field( 'field_64bb8d69325e7', 'Additional Links', 'additional_links' ),
            sfpf_legacy_wysiwyg_field( 'field_64b623fbd4aa4', 'Press', 'press' ),
        ],
        'location' => [ [ [ 'param' => 'post_type', 'operator' => '==', 'value' => '@post_type' ] ] ],
        'menu_order' => 0,
        'position' => 'normal',
        'style' => 'default',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
        'active' => true,
        'show_in_rest' => 0,
    ];
}

/** @return array<string,mixed> */
function contributing_profile_acf_field_group(): array {
    return [
        'key' => 'group_64bb58d10c008',
        'title' => 'Contributing Profile',
        'fields' => [
            sfpf_legacy_text_field( 'field_64bb58d291399', 'URL', 'url' ),
            [
                'key' => 'field_64bc93db4f0f8',
                'label' => 'Secondary Logo',
                'name' => 'secondary_logo',
                'type' => 'image',
                'return_format' => 'url',
                'preview_size' => 'medium',
                'library' => 'all',
            ],
            sfpf_legacy_true_false_field( 'field_64bc93ee4f0f9', 'Featured Item', 'featured_item' ),
            sfpf_legacy_true_false_field( 'field_64bc94084f0fa', 'Primary Featured Item', 'primary_featured_item' ),
        ],
        'location' => [ [ [ 'param' => 'post_type', 'operator' => '==', 'value' => '@post_type' ] ] ],
        'menu_order' => 0,
        'position' => 'normal',
        'style' => 'default',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
        'active' => true,
        'show_in_rest' => 0,
    ];
}

/** @return array<string,mixed> */
function sfpf_legacy_wysiwyg_field( string $key, string $label, string $name ): array {
    return [
        'key' => $key,
        'label' => $label,
        'name' => $name,
        'type' => 'wysiwyg',
        'tabs' => 'all',
        'toolbar' => 'full',
        'media_upload' => 1,
        'delay' => 0,
    ];
}

/** @return array<string,mixed> */
function sfpf_legacy_text_field( string $key, string $label, string $name ): array {
    return [ 'key' => $key, 'label' => $label, 'name' => $name, 'type' => 'text' ];
}

/** @return array<string,mixed> */
function sfpf_legacy_true_false_field( string $key, string $label, string $name ): array {
    return [ 'key' => $key, 'label' => $label, 'name' => $name, 'type' => 'true_false', 'default_value' => 0, 'ui' => 0 ];
}
