<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\ContentTypes\Interview;

defined( 'ABSPATH' ) || exit;

/**
 * The Interview fields: podcast or show, guest, host, the main link, more links and press.
 * The keys are stored data and never change.
 */
final class InterviewFields {
    /** @return array<string,mixed> */
    public static function group(): array {
        return [
            'key' => 'group_64b623fb36208',
            'title' => 'Interview',
            'fields' => [
                [
                    'key' => 'field_64bc48f36f3a1',
                    'label' => 'Podcast Name',
                    'name' => 'podcast_name',
                    'type' => 'wysiwyg',
                    'tabs' => 'all',
                    'toolbar' => 'full',
                    'media_upload' => 1,
                    'delay' => 0,
                ],
                [
                    'key' => 'field_64bf870639653',
                    'label' => 'Guest Name',
                    'name' => 'guest_name',
                    'type' => 'wysiwyg',
                    'tabs' => 'all',
                    'toolbar' => 'full',
                    'media_upload' => 1,
                    'delay' => 0,
                ],
                [
                    'key' => 'field_64bf870e39654',
                    'label' => 'Host Name',
                    'name' => 'host_name',
                    'type' => 'wysiwyg',
                    'tabs' => 'all',
                    'toolbar' => 'full',
                    'media_upload' => 1,
                    'delay' => 0,
                ],
                [
                    'key' => 'field_64bb8d7a325e8',
                    'label' => 'Primary URL',
                    'name' => 'primary_url',
                    'type' => 'text',
                ],
                [
                    'key' => 'field_64bb8d69325e7',
                    'label' => 'Additional Links',
                    'name' => 'additional_links',
                    'type' => 'wysiwyg',
                    'tabs' => 'all',
                    'toolbar' => 'full',
                    'media_upload' => 1,
                    'delay' => 0,
                ],
                [
                    'key' => 'field_64b623fbd4aa4',
                    'label' => 'Press',
                    'name' => 'press',
                    'type' => 'wysiwyg',
                    'tabs' => 'all',
                    'toolbar' => 'full',
                    'media_upload' => 1,
                    'delay' => 0,
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
