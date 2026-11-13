<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\ContentTypes\Quote;

use SFPF\PersonProfile\ContentTypes\ContentTypeCatalog;

defined( 'ABSPATH' ) || exit;

/**
 * The Quote content type. Quotes by the person, each with Quotation schema.
 * Off until a site turns it on; its fields are on with it.
 */
final class QuoteContentType {
    public const ID        = 'quote';
    public const POST_TYPE = 'quote';

    /** @return array<string,mixed> */
    public static function definition(): array {
        return [
            'id'              => self::ID,
            'owner'           => 'SFPF Person Profile Integration',
            'description'     => 'Quotes by the person, each with Quotation schema.',
            'enabled_default' => false,
            'post_type'       => [
                'key'          => self::POST_TYPE,
                'singular'     => 'Quote',
                'plural'       => 'Quotes',
                'rewrite_slug' => 'quote',
                'args'         => ContentTypeCatalog::profile_args( 'dashicons-format-quote', 24 ),
            ],
            'field_groups'    => [
                [
                    'id'              => 'quote-fields',
                    'label'           => 'Quote Fields',
                    'description'     => 'Quote text, attribution, source URL, publication details, and one or more raster or SVG logos.',
                    'group_key'       => 'group_sfpf_quote',
                    'enabled_default' => true,
                    'definition'      => [ QuoteFields::class, 'group' ],
                    'fields'          => [ 'Quote', 'Assigned Name', 'URL', 'Logos', 'Publication Name', 'Publication Info' ],
                    'dependencies'    => [ 'SVG Support for sanitized SVG uploads' ],
                ],
            ],
        ];
    }
}
