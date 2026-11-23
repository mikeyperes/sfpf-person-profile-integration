<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\ContentTypes\Interview;

use SFPF\PersonProfile\Fields\FieldKeys;

defined( 'ABSPATH' ) || exit;

/**
 * The Interview fields: podcast or show, guest, host, the main link, more links and press.
 * Field names are stable. FieldKeys selects the identifiers used by the site.
 */
final class InterviewFields {
    /** @return array<string,mixed> */
    public static function group(): array {
        return FieldKeys::active_group( self::canonical_group() );
    }

    /** Describes the fields with the canonical keys. */
    private static function canonical_group(): array {
        return [
            'key' => 'group_sfpf_interview',
            'title' => 'Interview',
            'fields' => [
                [
                    'key' => 'field_sfpf_interview_podcast_name',
                    'label' => 'Podcast Name',
                    'name' => 'podcast_name',
                    'type' => 'wysiwyg',
                    'tabs' => 'all',
                    'toolbar' => 'full',
                    'media_upload' => 1,
                    'delay' => 0,
                ],
                [
                    'key' => 'field_sfpf_interview_guest_name',
                    'label' => 'Guest Name',
                    'name' => 'guest_name',
                    'type' => 'wysiwyg',
                    'tabs' => 'all',
                    'toolbar' => 'full',
                    'media_upload' => 1,
                    'delay' => 0,
                ],
                [
                    'key' => 'field_sfpf_interview_host_name',
                    'label' => 'Host Name',
                    'name' => 'host_name',
                    'type' => 'wysiwyg',
                    'tabs' => 'all',
                    'toolbar' => 'full',
                    'media_upload' => 1,
                    'delay' => 0,
                ],
                [
                    'key' => 'field_sfpf_interview_primary_url',
                    'label' => 'Primary URL',
                    'name' => 'primary_url',
                    'type' => 'text',
                ],
                [
                    'key' => 'field_sfpf_interview_additional_links',
                    'label' => 'Additional Links',
                    'name' => 'additional_links',
                    'type' => 'wysiwyg',
                    'tabs' => 'all',
                    'toolbar' => 'full',
                    'media_upload' => 1,
                    'delay' => 0,
                ],
                [
                    'key' => 'field_sfpf_interview_press',
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
