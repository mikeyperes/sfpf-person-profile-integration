<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\ContentTypes\PressRelease;

use SFPF\PersonProfile\ContentTypes\ContentTypeCatalog;

defined( 'ABSPATH' ) || exit;

/**
 * The Press Release content type. Press releases by or about the person, each with NewsArticle schema.
 * Off until a site turns it on; its fields are on with it.
 */
final class PressReleaseContentType {
    public const ID        = 'press-release';
    public const POST_TYPE = 'press-release';

    /** @return array<string,mixed> */
    public static function definition(): array {
        return [
            'id'              => self::ID,
            'owner'           => 'SFPF Person Profile Integration',
            'description'     => 'Press releases by or about the person, each with NewsArticle schema.',
            'enabled_default' => false,
            'legacy_enabled_option' => 'sfpf_enable_press_release_cpt',
            'post_type'       => [
                'key'          => self::POST_TYPE,
                'singular'     => 'Press Release',
                'plural'       => 'Press Releases',
                'rewrite_slug' => 'press-release',
                'args'         => ContentTypeCatalog::profile_args( 'dashicons-media-document', 21 ),
            ],
            'field_groups'    => [
                [
                    'id'              => 'press-release-fields',
                    'label'           => 'Press Release Fields',
                    'description'     => 'Source, original URL, release date, and featured controls for press releases.',
                    'group_key'       => 'group_sfpf_press_release',
                    'enabled_default' => true,
                    'legacy_option'   => 'sfpf_enable_press_release_acf',
                    'definition'      => [ PressReleaseFields::class, 'group' ],
                    'fields'          => [ 'Source Name', 'Original URL', 'Release Date', 'Featured' ],
                    'dependencies'    => [],
                ],
            ],
        ];
    }
}
