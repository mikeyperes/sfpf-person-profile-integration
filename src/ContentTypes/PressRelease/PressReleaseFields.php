<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\ContentTypes\PressRelease;

defined( 'ABSPATH' ) || exit;

/**
 * The Press Release fields: original source, original link, release date and the featured flag.
 * The keys are stored data and never change.
 */
final class PressReleaseFields {
    /** @return array<string,mixed> */
    public static function group(): array {
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
                [
                    'key' => 'field_sfpf_press_release_featured',
                    'label' => 'Featured',
                    'name' => 'sfpf_press_release_featured',
                    'type' => 'true_false',
                    'default_value' => 0,
                    'ui' => 0,
                ],
            ],
            'location' => [
                [
                    [
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => '@post_type',
                    ],
                ],
            ],
            'menu_order' => 0,
            'position' => 'normal',
            'style' => 'default',
            'label_placement' => 'top',
            'instruction_placement' => 'label',
            'active' => true,
            'show_in_rest' => 0,
        ];
    }
}
