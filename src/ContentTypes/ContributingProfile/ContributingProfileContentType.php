<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\ContentTypes\ContributingProfile;

use SFPF\PersonProfile\ContentTypes\ContentTypeCatalog;

defined( 'ABSPATH' ) || exit;

/**
 * The Contributing Profile content type. Publications and platforms the person writes for. Their links join the person's profile links in schema.
 * Off until a site turns it on; its fields are on with it.
 */
final class ContributingProfileContentType {
    public const ID        = 'contributing-profile';
    public const POST_TYPE = 'contributing-profile';

    /** @return array<string,mixed> */
    public static function definition(): array {
        return [
            'id'              => self::ID,
            'owner'           => 'SFPF Person Profile Integration',
            'description'     => 'Publications and platforms the person writes for. Their links join the person\'s profile links in schema.',
            'enabled_default' => false,
            'legacy_enabled_option' => 'sfpf_enable_contributing_profile_cpt',
            'post_type'       => [
                'key'          => self::POST_TYPE,
                'singular'     => 'Contributing Profile',
                'plural'       => 'Contributing Profiles',
                'rewrite_slug' => 'contributing-profile',
                'args'         => ContentTypeCatalog::profile_args( 'dashicons-id-alt', 23 ),
            ],
            'field_groups'    => [
                [
                    'id'              => 'contributing-profile-fields',
                    'label'           => 'Contributing Profile Fields',
                    'description'     => 'Destination URL, alternate logo, and featured-profile controls.',
                    'group_key'       => 'group_64bb58d10c008',
                    'enabled_default' => true,
                    'legacy_option'   => 'sfpf_enable_contributing_profile_acf',
                    'definition'      => [ ContributingProfileFields::class, 'group' ],
                    'fields'          => [ 'URL', 'Secondary Logo', 'Featured Item', 'Primary Featured Item' ],
                    'dependencies'    => [],
                ],
            ],
        ];
    }
}
