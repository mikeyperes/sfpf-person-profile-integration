<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Shortcodes;

use SFPF\PersonProfile\Faq\FaqSets;
use SFPF\PersonProfile\Shortcodes\Sections\FaqSection;

defined( 'ABSPATH' ) || exit;

/**
 * [faq_set slug="main" layout="accordion"]
 *
 * A site-wide FAQ set from the FAQ tab (the primary set without a slug), or
 * one of its questions with index="0". Its FAQ schema joins the page's schema
 * block automatically.
 */
final class FaqSetShortcode {
    public const TAG = 'faq_set';

    /** @param array<string,string>|string $atts */
    public static function render( mixed $atts ): string {
        $atts  = Attributes::parse( $atts, [ 'slug' => '', 'set' => '', 'layout' => 'accordion', 'first_open' => 'false' ], self::TAG );
        $items = FaqSets::set_items( '' !== $atts['slug'] ? $atts['slug'] : $atts['set'], Attributes::index( $atts ) );
        if ( [] === $items ) {
            return esc_html( $atts['fallback'] );
        }
        if ( Attributes::styled( $atts ) ) {
            SectionStyles::enqueue();
        }
        $tag     = Attributes::heading_tag( $atts );
        $heading = '' !== $atts['heading'] ? '<' . $tag . ' class="sfpf-section__heading">' . esc_html( $atts['heading'] ) . '</' . $tag . '>' : '';
        return '<div class="' . esc_attr( trim( 'sfpf-section sfpf-faq sfpf-faq-set ' . Attributes::classes( $atts ) ) ) . '">' . $heading . FaqSection::items( $items, 'list' === $atts['layout'] ? 'list' : 'accordion', Attributes::flag( $atts, 'first_open' ) ) . '</div>';
    }
}
