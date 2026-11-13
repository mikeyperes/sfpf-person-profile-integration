<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\ContentTypes\Award;

use SFPF\PersonProfile\ContentTypes\ContentTypeCatalog;

defined( 'ABSPATH' ) || exit;

/**
 * The Award content type. Awards the person received. Each one joins the person's awards in schema.
 * Off until a site turns it on; its fields are on with it.
 */
final class AwardContentType {
    public const ID        = 'award';
    public const POST_TYPE = 'award';

    /** @return array<string,mixed> */
    public static function definition(): array {
        return [
            'id'              => self::ID,
            'owner'           => 'SFPF Person Profile Integration',
            'description'     => 'Awards the person received. Each one joins the person\'s awards in schema.',
            'enabled_default' => false,
            'post_type'       => [
                'key'          => self::POST_TYPE,
                'singular'     => 'Award',
                'plural'       => 'Awards',
                'rewrite_slug' => 'award',
                'args'         => [ 'has_archive' => 'awards', 'supports' => [ 'title', 'editor', 'excerpt', 'thumbnail', 'custom-fields' ], 'rewrite' => [ 'with_front' => false, 'feeds' => false ] ] + ContentTypeCatalog::profile_args( 'dashicons-awards', 25 ),
            ],
            'field_groups'    => [
                [
                    'id'              => 'award-fields',
                    'label'           => 'Award Fields',
                    'description'     => 'Year, giver (text or Organization record), source URL and featured flag. The award name is the post title.',
                    'group_key'       => 'group_sfpf_award',
                    'enabled_default' => true,
                    'definition'      => [ AwardFields::class, 'group' ],
                    'fields'          => [ 'Year', 'Given By', 'Given By (Organization Record)', 'URL', 'Featured' ],
                    'dependencies'    => [],
                ],
            ],
        ];
    }
}
