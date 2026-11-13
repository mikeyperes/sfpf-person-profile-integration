<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Shortcodes;

use SFPF\PersonProfile\Person\QueryLimits;
use SFPF\PersonProfile\Settings\Settings;

defined( 'ABSPATH' ) || exit;

/**
 * [person_loop type="book" columns="3" rows="2"]
 *
 * The newest posts of a content type in a grid, each drawn with the Elementor
 * loop item template chosen for that type in Templates. Without a template
 * (or without Elementor) each item shows its title and summary.
 */
final class ContentLoopShortcode {
    public const TAG          = 'person_loop';
    public const DEFAULT_SIZE = 12;
    public const HARD_MAX     = 48;

    /** @param array<string,string>|string $atts */
    public static function render( mixed $atts ): string {
        $atts    = Attributes::parse( $atts, [ 'type' => 'book', 'cpt' => '', 'columns' => '3', 'rows' => '' ], self::TAG );
        $type    = sanitize_key( '' !== $atts['cpt'] ? $atts['cpt'] : $atts['type'] );
        $limits  = QueryLimits::for_context( 'loop', self::DEFAULT_SIZE, self::HARD_MAX );
        $columns = max( 1, min( 6, absint( $atts['columns'] ) ?: 3 ) );
        $rows    = absint( $atts['rows'] );
        $count   = QueryLimits::clamp( $rows > 0 ? $columns * min( $rows, $limits['hard_max'] ) : 0, $limits['default'], $limits['hard_max'] );
        if ( ! post_type_exists( $type ) ) {
            return '';
        }
        $posts = get_posts( [ 'post_type' => $type, 'post_status' => 'publish', 'posts_per_page' => $count, 'orderby' => 'date', 'order' => 'DESC', 'ignore_sticky_posts' => true, 'no_found_rows' => true ] );
        if ( [] === $posts ) {
            return esc_html( $atts['fallback'] );
        }
        if ( Attributes::styled( $atts ) ) {
            SectionStyles::enqueue();
        }
        $template = absint( Settings::map( Settings::LOOP_TEMPLATES )[ $type ] ?? 0 );
        $elementor = $template > 0 && class_exists( '\Elementor\Plugin' ) ? \Elementor\Plugin::instance() : null;
        $html = '<div class="' . esc_attr( trim( 'sfpf-section sfpf-loop sfpf-loop-' . $type . ' ' . Attributes::classes( $atts ) ) ) . '" style="--sfpf-loop-columns:' . $columns . '">';
        global $post;
        $previous = $post;
        foreach ( $posts as $item ) {
            $post = $item; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Elementor's loop template reads the global post.
            setup_postdata( $item );
            $card = null !== $elementor ? (string) $elementor->frontend->get_builder_content_for_display( $template, true ) : '';
            if ( '' === trim( $card ) ) {
                $card = '<h3 class="sfpf-loop__title"><a href="' . esc_url( (string) get_permalink( $item ) ) . '">' . esc_html( get_the_title( $item ) ) . '</a></h3><p class="sfpf-loop__summary">' . esc_html( wp_trim_words( $item->post_excerpt ?: wp_strip_all_tags( strip_shortcodes( $item->post_content ) ), 20 ) ) . '</p>';
            }
            $html .= '<div class="sfpf-loop__item sfpf-loop-item">' . $card . '</div>';
        }
        $post = $previous; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
        wp_reset_postdata();
        return $html . '</div>';
    }
}
