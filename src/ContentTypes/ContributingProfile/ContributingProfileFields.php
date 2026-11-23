<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\ContentTypes\ContributingProfile;

use SFPF\PersonProfile\Fields\FieldKeys;

defined( 'ABSPATH' ) || exit;

/**
 * The Contributing Profile fields: the profile link, a second logo and the featured flags.
 * Field names are stable. FieldKeys selects the identifiers used by the site.
 */
final class ContributingProfileFields {
    /** @return array<string,mixed> */
    public static function group(): array {
        return FieldKeys::active_group( self::canonical_group() );
    }

    /** Describes the fields with the canonical keys. */
    private static function canonical_group(): array {
        return [
            'key' => 'group_sfpf_contributing_profile',
            'title' => 'Contributing Profile',
            'fields' => [
                [
                    'key' => 'field_sfpf_contributing_profile_url',
                    'label' => 'URL',
                    'name' => 'url',
                    'type' => 'text',
                ],
                [
                    'key' => 'field_sfpf_contributing_profile_secondary_logo',
                    'label' => 'Secondary Logo',
                    'name' => 'secondary_logo',
                    'type' => 'image',
                    'return_format' => 'url',
                    'preview_size' => 'medium',
                    'library' => 'all',
                ],
                [
                    'key' => 'field_sfpf_contributing_profile_featured_item',
                    'label' => 'Featured Item',
                    'name' => 'featured_item',
                    'type' => 'true_false',
                    'default_value' => 0,
                    'ui' => 0,
                ],
                [
                    'key' => 'field_sfpf_contributing_profile_primary_featured_item',
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
