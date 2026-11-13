<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Shortcodes;

use SFPF\PersonProfile\Person\PersonResolver;

defined( 'ABSPATH' ) || exit;

/**
 * The shortcodes older pages use, each mapped onto today's shortcodes so the
 * page keeps showing the same thing:
 *
 * - [founder id="…"] and [founder action="display_…"]  → [person] / [person_section]
 * - [sfpf_person_faq] and [person_faq]                → [person_section id="faq"]
 * - [sfpf_faq set="…"]                                → [faq_set]
 * - [sfpf_faq_schema set="…"]                         → nothing: the set's FAQ schema
 *                                                       now joins the page's schema block
 * - [sfpf_loop cpt="…"]                               → [person_loop]
 */
final class LegacyShortcodes {
    /** [founder action="…"] => [person_section id] with the attributes it maps. */
    private const ACTIONS = [
        'display_education'                => [ 'id' => 'education' ],
        'display_gallery'                  => [ 'id' => 'gallery' ],
        'display_professions_with_summary' => [ 'id' => 'professions', 'page' => 'content' ],
        'display_socials'                  => [ 'id' => 'socials', 'source' => 'site' ],
        'display_articles'                 => [ 'id' => 'articles' ],
        'display_additional_urls'          => [ 'id' => 'additional_urls' ],
        'display_faq'                      => [ 'id' => 'faq' ],
        'display_location_born'            => [ 'id' => 'birthplace' ],
        'display_knowledge_panel'          => [ 'id' => 'knowledge_panel' ],
        'display_nationality'              => [ 'id' => 'nationality' ],
        'display_organizations_founded'    => [ 'id' => 'organizations' ],
        'display_bio_full'                 => [ 'id' => 'profile' ],
    ];

    /** [founder id="…"] names that are now named differently. */
    private const FIELDS = [
        'knowledge_graph_url'    => 'knowledge_panel_url',
        'location_born_location' => 'birthplace',
        'location_born_url'      => 'birthplace_url',
        'knows_language'         => 'languages',
        'sameas'                 => 'same_as',
        'wikimedia_commons_urls' => 'wikimedia_photos',
        'additional_title'       => 'title',
        'additional_public_email' => 'email',
        'additional_public_phone' => 'phone',
    ];

    /** Education keys before 4.0 => today's keys. */
    private const EDUCATION_KEYS = [ 'college' => 'school', 'designation' => 'degree', 'major' => 'field', 'wiki_url' => 'wikipedia_url' ];

    /** @param array<string,string>|string $atts */
    public static function founder( mixed $atts ): string {
        $atts   = is_array( $atts ) ? array_map( 'strval', $atts ) : [];
        $format = strtolower( $atts['format'] ?? '' );
        $action = sanitize_key( $atts['action'] ?? '' );
        if ( isset( self::ACTIONS[ $action ] ) ) {
            $section = self::ACTIONS[ $action ];
            $field   = [ 'display_gallery' => 'gallery', 'display_faq' => 'faq' ][ $action ] ?? '';
            if ( '' !== $field && in_array( $format, [ 'json', 'urls', 'count' ], true ) ) {
                return PersonShortcode::render( [ 'field' => $field, 'format' => 'urls' === $format ? 'auto' : $format ] + $atts );
            }
            $layout = $atts['style'] ?? $format;
            return PersonSectionShortcode::render( $section + ( '' !== $layout && ! in_array( $layout, [ 'html' ], true ) ? [ 'layout' => $layout ] : [] ) + self::pass( $atts, [ 'columns', 'size', 'class' ] ) );
        }
        $field = sanitize_key( $atts['id'] ?? '' );
        return match ( $field ) {
            ''          => '',
            'name'      => self::name(),
            'urls_wikidata' => '',
            'gallery', 'knowledge_graph_images' => in_array( $format, [ 'json', 'urls', 'count' ], true )
                ? PersonShortcode::render( [ 'field' => $field, 'format' => 'urls' === $format ? 'auto' : $format ] + self::pass( $atts, [ 'size' ] ) )
                : PersonSectionShortcode::render( [ 'id' => 'gallery', 'source' => 'gallery' === $field ? 'photos' : 'knowledge_graph' ] + self::pass( $atts, [ 'columns', 'size' ] ) ),
            'faq'       => in_array( $format, [ 'json', 'count' ], true ) ? PersonShortcode::render( [ 'field' => 'faq', 'format' => $format ] ) : PersonSectionShortcode::render( [ 'id' => 'faq' ] + ( '' !== ( $atts['style'] ?? '' ) ? [ 'layout' => $atts['style'] ] : [] ) ),
            'education' => self::education( $atts, $format ),
            default     => PersonShortcode::render( [ 'field' => self::FIELDS[ $field ] ?? $field, 'format' => in_array( $format, [ 'json', 'count', 'list' ], true ) ? $format : 'auto' ] + self::pass( $atts, [ 'size', 'link', 'target', 'pretty' ] ) ),
        };
    }

    /** @param array<string,string>|string $atts */
    public static function person_faq( mixed $atts ): string {
        $atts = is_array( $atts ) ? $atts : [];
        return PersonSectionShortcode::render( [ 'id' => 'faq', 'layout' => (string) ( $atts['style'] ?? $atts['layout'] ?? $atts['format'] ?? 'accordion' ) ] + $atts );
    }

    /** @param array<string,string>|string $atts */
    public static function faq_set( mixed $atts ): string {
        $atts = is_array( $atts ) ? $atts : [];
        // The older shortcode opened every answer on click and printed questions as a list.
        return FaqSetShortcode::render( [ 'slug' => (string) ( $atts['set'] ?? '' ), 'layout' => 'list' === ( $atts['style'] ?? 'list' ) ? 'accordion' : (string) ( $atts['style'] ?? 'accordion' ) ] + $atts );
    }

    public static function faq_schema(): string {
        return '';
    }

    /** @param array<string,string>|string $atts */
    public static function loop( mixed $atts ): string {
        return ContentLoopShortcode::render( is_array( $atts ) ? $atts : [] );
    }

    /** The name as [founder id="name"] always printed it: first and last name in their own spans. */
    private static function name(): string {
        $profile = PersonResolver::profile();
        if ( null === $profile ) {
            return '';
        }
        $parts = array_filter(
            [
                '' !== $profile->first_name() ? '<span class="first_name">' . esc_html( $profile->first_name() ) . '</span>' : '',
                '' !== $profile->last_name() ? '<span class="last_name">' . esc_html( $profile->last_name() ) . '</span>' : '',
            ]
        );
        return '<span class="founder-name">' . ( [] !== $parts ? implode( ' ', $parts ) : esc_html( $profile->display_name() ) ) . '</span>';
    }

    /** @param array<string,string> $atts */
    private static function education( array $atts, string $format ): string {
        if ( isset( $atts['index'] ) && is_numeric( $atts['index'] ) ) {
            $key = self::EDUCATION_KEYS[ sanitize_key( $atts['field'] ?? 'college' ) ] ?? sanitize_key( $atts['field'] ?? 'school' );
            return PersonShortcode::render( [ 'field' => 'education', 'index' => $atts['index'], 'key' => '' !== $key ? $key : 'school' ] );
        }
        if ( 'json' === $format ) {
            return PersonShortcode::render( [ 'field' => 'education', 'format' => 'json' ] );
        }
        return PersonSectionShortcode::render( [ 'id' => 'education', 'layout' => 'inline', 'style' => 'none' ] );
    }

    /**
     * @param array<string,string> $atts
     * @param list<string>         $keys
     * @return array<string,string>
     */
    private static function pass( array $atts, array $keys ): array {
        return array_intersect_key( $atts, array_flip( $keys ) );
    }
}
