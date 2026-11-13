<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\ContentTypes\Interview;

use SFPF\PersonProfile\ContentTypes\ContentTypeCatalog;

defined( 'ABSPATH' ) || exit;

/**
 * The Interview content type. Interviews with the person, each linked to the person in schema.
 * Off until a site turns it on; its fields are on with it.
 */
final class InterviewContentType {
    public const ID        = 'interview';
    public const POST_TYPE = 'interview';

    /** @return array<string,mixed> */
    public static function definition(): array {
        return [
            'id'              => self::ID,
            'owner'           => 'SFPF Person Profile Integration',
            'description'     => 'Interviews with the person, each linked to the person in schema.',
            'enabled_default' => false,
            'legacy_enabled_option' => 'sfpf_enable_interview_cpt',
            'post_type'       => [
                'key'          => self::POST_TYPE,
                'singular'     => 'Interview',
                'plural'       => 'Interviews',
                'rewrite_slug' => 'interview',
                'args'         => ContentTypeCatalog::profile_args( 'dashicons-microphone', 22 ),
            ],
            'field_groups'    => [
                [
                    'id'              => 'interview-fields',
                    'label'           => 'Interview Fields',
                    'description'     => 'Podcast, guest, host, source-link, and press details for interviews.',
                    'group_key'       => 'group_64b623fb36208',
                    'enabled_default' => true,
                    'legacy_option'   => 'sfpf_enable_interview_acf',
                    'definition'      => [ InterviewFields::class, 'group' ],
                    'fields'          => [ 'Podcast Name', 'Guest Name', 'Host Name', 'Primary URL', 'Additional Links', 'Press' ],
                    'dependencies'    => [],
                ],
            ],
        ];
    }
}
