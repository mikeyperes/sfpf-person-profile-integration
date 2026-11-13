<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Shortcodes;

defined( 'ABSPATH' ) || exit;

/**
 * The attributes every SFPF shortcode understands, parsed one way:
 *
 * - format    how a value prints: auto, text, html, url, link, list, json, count
 * - separator between list items (default ", ")
 * - link      "true" wraps a link or email in an anchor
 * - target    the anchor's target, such as _blank
 * - pretty    "true" shows a link without https:// and the trailing slash
 * - index     one item of a list, counting from 0
 * - key       one value of that item (for example key="school")
 * - fallback  text printed when there is no value
 * - class     extra classes on the wrapper of HTML output
 * - style     "none" leaves the plugin's styling off (same HTML)
 * - heading   an optional heading above a section, heading_tag its tag
 */
final class Attributes {
    public const COMMON = [
        'format'      => 'auto',
        'separator'   => ', ',
        'link'        => 'false',
        'target'      => '',
        'pretty'      => 'false',
        'index'       => '',
        'key'         => '',
        'fallback'    => '',
        'class'       => '',
        'style'       => '',
        'heading'     => '',
        'heading_tag' => 'h2',
    ];

    /**
     * @param array<string,string> $defaults The shortcode's own attributes.
     * @return array<string,string>
     */
    public static function parse( mixed $atts, array $defaults, string $tag ): array {
        $atts = shortcode_atts( $defaults + self::COMMON, is_array( $atts ) ? $atts : [], $tag );
        foreach ( $atts as $name => $value ) {
            $value = is_scalar( $value ) ? (string) $value : '';
            // A separator keeps its spaces (", " or " · ").
            $atts[ $name ] = 'separator' === $name ? $value : trim( $value );
        }
        return $atts;
    }

    public static function flag( array $atts, string $name ): bool {
        return in_array( strtolower( (string) ( $atts[ $name ] ?? '' ) ), [ '1', 'true', 'yes', 'on' ], true );
    }

    public static function index( array $atts ): ?int {
        return isset( $atts['index'] ) && is_numeric( $atts['index'] ) ? max( 0, (int) $atts['index'] ) : null;
    }

    public static function styled( array $atts ): bool {
        return 'none' !== strtolower( (string) ( $atts['style'] ?? '' ) );
    }

    public static function target( array $atts ): string {
        return in_array( $atts['target'] ?? '', [ '_blank', '_self', '_parent', '_top' ], true ) ? (string) $atts['target'] : '';
    }

    /** Space-separated class names from the class attribute, sanitized. */
    public static function classes( array $atts ): string {
        return implode( ' ', array_filter( array_map( 'sanitize_html_class', preg_split( '/\s+/', (string) ( $atts['class'] ?? '' ) ) ?: [] ) ) );
    }

    public static function heading_tag( array $atts ): string {
        $tag = strtolower( (string) ( $atts['heading_tag'] ?? 'h2' ) );
        return in_array( $tag, [ 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'div' ], true ) ? $tag : 'h2';
    }
}
