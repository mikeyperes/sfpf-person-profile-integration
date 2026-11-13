<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\ContentTypes\Exhibition;

defined( 'ABSPATH' ) || exit;

/**
 * The Exhibition fields: type, dates, venue, city, address, organizer, link, works shown, gallery and the featured flag. The exhibition name is the post title.
 * The keys are stored data and never change.
 */
final class ExhibitionFields {
    /** @return array<string,mixed> */
    public static function group(): array {
        return [
            'key' => 'group_sfpf_exhibition',
            'title' => 'Exhibition Fields',
            'fields' => [
                [
                    'key' => 'field_sfpf_exhibition_type',
                    'label' => 'Type',
                    'name' => 'exhibition_type',
                    'type' => 'select',
                    'instructions' => 'Solo or group show. The exhibition name is the post title.',
                    'choices' => [
                        'solo' => 'Solo',
                        'group' => 'Group',
                        'fair' => 'Art fair',
                        'event' => 'Event',
                    ],
                    'allow_null' => 1,
                    'return_format' => 'value',
                ],
                [
                    'key' => 'field_sfpf_exhibition_start_date',
                    'label' => 'Start Date',
                    'name' => 'start_date',
                    'type' => 'date_picker',
                    'display_format' => 'F j, Y',
                    'return_format' => 'Y-m-d',
                    'first_day' => 0,
                ],
                [
                    'key' => 'field_sfpf_exhibition_end_date',
                    'label' => 'End Date',
                    'name' => 'end_date',
                    'type' => 'date_picker',
                    'display_format' => 'F j, Y',
                    'return_format' => 'Y-m-d',
                    'first_day' => 0,
                ],
                [
                    'key' => 'field_sfpf_exhibition_venue',
                    'label' => 'Venue',
                    'name' => 'venue',
                    'type' => 'text',
                    'instructions' => 'The place that hosted it, for example The Biltmore.',
                ],
                [
                    'key' => 'field_sfpf_exhibition_city',
                    'label' => 'City',
                    'name' => 'city',
                    'type' => 'text',
                    'instructions' => 'City and region, for example Coral Gables, Florida.',
                ],
                [
                    'key' => 'field_sfpf_exhibition_address',
                    'label' => 'Address',
                    'name' => 'address',
                    'type' => 'text',
                ],
                [
                    'key' => 'field_sfpf_exhibition_organizer',
                    'label' => 'Organizer',
                    'name' => 'organizer',
                    'type' => 'text',
                    'instructions' => 'Who organized it, for example RM Sotheby\'s ModaMiami.',
                ],
                [
                    'key' => 'field_sfpf_exhibition_organizer_organization',
                    'label' => 'Organizer (Organization Record)',
                    'name' => 'organizer_organization',
                    'type' => 'post_object',
                    'instructions' => 'Optional. Link the organizer to an Organization record when one exists.',
                    'post_type' => [
                        'organization',
                    ],
                    'return_format' => 'id',
                    'allow_null' => 1,
                    'multiple' => 0,
                ],
                [
                    'key' => 'field_sfpf_exhibition_url',
                    'label' => 'URL',
                    'name' => 'url',
                    'type' => 'url',
                    'instructions' => 'The exhibition or event page, or an article about it.',
                ],
                [
                    'key' => 'field_sfpf_exhibition_works_shown',
                    'label' => 'Works Shown',
                    'name' => 'works_shown',
                    'type' => 'repeater',
                    'instructions' => 'The pieces shown, by name.',
                    'layout' => 'table',
                    'button_label' => 'Add Work',
                    'sub_fields' => [
                        [
                            'key' => 'field_sfpf_exhibition_work_name',
                            'label' => 'Work',
                            'name' => 'value',
                            'type' => 'text',
                        ],
                    ],
                ],
                [
                    'key' => 'field_sfpf_exhibition_gallery',
                    'label' => 'Gallery',
                    'name' => 'gallery',
                    'type' => 'gallery',
                    'return_format' => 'id',
                    'preview_size' => 'medium',
                    'insert' => 'append',
                    'library' => 'all',
                ],
                [
                    'key' => 'field_sfpf_exhibition_featured',
                    'label' => 'Featured',
                    'name' => 'featured',
                    'type' => 'true_false',
                    'instructions' => 'Show this exhibition in featured lists, such as the homepage.',
                    'ui' => 1,
                    'default_value' => 0,
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
