<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\ContentTypes\Exhibition;

use SFPF\PersonProfile\ContentTypes\ContentTypeCatalog;

defined( 'ABSPATH' ) || exit;

/**
 * The Exhibition content type. Exhibitions, shows and fairs that showed the person's work, each with ExhibitionEvent schema.
 * Off until a site turns it on; its fields are on with it.
 */
final class ExhibitionContentType {
    public const ID        = 'exhibition';
    public const POST_TYPE = 'exhibition';

    /** @return array<string,mixed> */
    public static function definition(): array {
        return [
            'id'              => self::ID,
            'owner'           => 'SFPF Person Profile Integration',
            'description'     => 'Exhibitions, shows and fairs that showed the person\'s work, each with ExhibitionEvent schema.',
            'enabled_default' => false,
            'post_type'       => [
                'key'          => self::POST_TYPE,
                'singular'     => 'Exhibition',
                'plural'       => 'Exhibitions',
                'rewrite_slug' => 'exhibition',
                'args'         => [ 'has_archive' => 'exhibitions', 'supports' => [ 'title', 'editor', 'excerpt', 'thumbnail', 'custom-fields' ], 'rewrite' => [ 'with_front' => false, 'feeds' => false ] ] + ContentTypeCatalog::profile_args( 'dashicons-art', 26 ),
            ],
            'field_groups'    => [
                [
                    'id'              => 'exhibition-fields',
                    'label'           => 'Exhibition Fields',
                    'description'     => 'Type, dates, venue, city, address, organizer, URL, works shown, gallery and featured flag. The exhibition name is the post title.',
                    'group_key'       => 'group_sfpf_exhibition',
                    'enabled_default' => true,
                    'definition'      => [ ExhibitionFields::class, 'group' ],
                    'fields'          => [ 'Type', 'Start Date', 'End Date', 'Venue', 'City', 'Address', 'Organizer', 'Organizer (Organization Record)', 'URL', 'Works Shown', 'Gallery', 'Featured' ],
                    'dependencies'    => [],
                ],
            ],
        ];
    }
}
