<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\ContentTypes\ContributingProfile;

defined( 'ABSPATH' ) || exit;

/**
 * The Contributing Profile fields: the profile link, a second logo and the featured flags.
 * The keys are stored data and never change.
 */
final class ContributingProfileFields {
    /** @return array<string,mixed> */
    public static function group(): array {
        return [
            'key' => 'group_64bb58d10c008',
            'title' => 'Contributing Profile',
            'fields' => [
                [
                    'key' => 'field_64bb58d291399',
                    'label' => 'URL',
                    'name' => 'url',
                    'type' => 'text',
                ],
                [
                    'key' => 'field_64bc93db4f0f8',
                    'label' => 'Secondary Logo',
                    'name' => 'secondary_logo',
                    'type' => 'image',
                    'return_format' => 'url',
                    'preview_size' => 'medium',
                    'library' => 'all',
                ],
                [
                    'key' => 'field_64bc93ee4f0f9',
                    'label' => 'Featured Item',
                    'name' => 'featured_item',
                    'type' => 'true_false',
                    'default_value' => 0,
                    'ui' => 0,
                ],
                [
                    'key' => 'field_64bc94084f0fa',
                    'label' => 'Primary Featured Item',
                    'name' => 'primary_featured_item',
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
