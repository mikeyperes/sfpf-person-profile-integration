<?php

namespace sfpf_person_website;

defined( 'ABSPATH' ) || exit;

/** @return array<string,mixed> */
function press_release_acf_field_group(): array {
    return [
        'key' => 'group_64a7290b61191',
        'title' => 'Press Release Fields',
        'fields' => [
            [
                'key' => 'field_smpi_post_authors_notice',
                'label' => 'Article Authors',
                'name' => '',
                'type' => 'message',
                'message' => 'Select all authors for this press release. The first selected author is treated as primary when SMP Publication Integration is active.',
                'new_lines' => 'wpautop',
                'esc_html' => 0,
            ],
            [
                'key' => 'field_smpi_post_authors',
                'label' => 'Article Authors',
                'name' => 'smpi_post_authors',
                'type' => 'user',
                'instructions' => 'Select one or more WordPress authors. Leave empty to use the native WordPress author.',
                'role' => '',
                'return_format' => 'id',
                'multiple' => 1,
                'allow_null' => 1,
                'ui' => 1,
                'ajax' => 1,
            ],
            [
                'key' => 'field_65ab7ba0e849b',
                'label' => 'Post Summary',
                'name' => 'post_summary',
                'type' => 'wysiwyg',
                'instructions' => 'Add a concise summary for templates and article presentation.',
                'tabs' => 'all',
                'toolbar' => 'full',
                'media_upload' => 1,
                'delay' => 0,
            ],
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
