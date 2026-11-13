<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Shortcodes;

use SFPF\PersonProfile\Person\PersonResolver;
use SFPF\PersonProfile\Shortcodes\Sections\SectionRegistry;

defined( 'ABSPATH' ) || exit;

/**
 * [person_section id="education" layout="list" heading="Education"]
 *
 * One block of the person's page. Every section shares the wrapper, the
 * optional heading, class and style="none"; a section with no content prints
 * nothing at all, heading included.
 */
final class PersonSectionShortcode {
    public const TAG = 'person_section';

    /** @param array<string,string>|string $atts */
    public static function render( mixed $atts ): string {
        $id      = sanitize_key( (string) ( is_array( $atts ) ? ( $atts['id'] ?? '' ) : '' ) );
        $section = SectionRegistry::get( $id );
        $profile = PersonResolver::profile();
        if ( null === $section || null === $profile ) {
            return '';
        }
        $layouts = array_keys( $section->layouts() );
        $atts    = Attributes::parse( $atts, [ 'id' => $id, 'layout' => $layouts[0] ?? '' ] + $section->attributes(), self::TAG );
        if ( ! in_array( $atts['layout'], $layouts, true ) ) {
            $atts['layout'] = $layouts[0] ?? '';
        }
        if ( ! $section->has_content( $profile ) ) {
            return esc_html( $atts['fallback'] );
        }
        $body = $section->render( $profile, $atts );
        if ( '' === trim( $body ) ) {
            return esc_html( $atts['fallback'] );
        }
        $styled = Attributes::styled( $atts );
        if ( $styled ) {
            SectionStyles::enqueue();
        }
        $slug    = str_replace( '_', '-', $section->id() );
        $classes = trim( 'sfpf-section sfpf-' . $slug . ' sfpf-' . $slug . '--' . sanitize_html_class( $atts['layout'] ) . ( $styled ? '' : ' sfpf-section--unstyled' ) . ' ' . Attributes::classes( $atts ) );
        $tag     = Attributes::heading_tag( $atts );
        $heading = '' !== $atts['heading'] ? '<' . $tag . ' class="sfpf-section__heading">' . esc_html( $atts['heading'] ) . '</' . $tag . '>' : '';
        return '<div class="' . esc_attr( $classes ) . '" data-sfpf-section="' . esc_attr( $section->id() ) . '">' . $heading . $body . '</div>';
    }
}
