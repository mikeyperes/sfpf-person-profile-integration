<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Shortcodes;

use SFPF\PersonProfile\Person\FieldValue;
use SFPF\PersonProfile\Person\Links;

defined( 'ABSPATH' ) || exit;

/**
 * Prints one field value in the format a shortcode asks for. Every value
 * shortcode ([person], [book], [organization]) prints through here, so the
 * same attributes work the same way everywhere.
 */
final class Output {
    /** @param array<string,string> $atts */
    public static function value( FieldValue $value, array $atts ): string {
        $format = strtolower( (string) ( $atts['format'] ?? 'auto' ) );
        $value  = self::pick( $value, $atts );
        if ( 'count' === $format ) {
            return (string) $value->count();
        }
        if ( $value->is_empty() ) {
            return esc_html( (string) ( $atts['fallback'] ?? '' ) );
        }
        if ( 'json' === $format ) {
            return esc_html( (string) wp_json_encode( $value->raw, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) );
        }
        return match ( $value->type ) {
            FieldValue::HTML   => 'text' === $format ? esc_html( wp_strip_all_tags( (string) $value->value ) ) : wp_kses_post( (string) $value->value ),
            FieldValue::URL    => self::url( (string) $value->value, $atts, $value->label ),
            FieldValue::EMAIL  => self::email( (string) $value->value, $atts ),
            FieldValue::LIST, FieldValue::URLS => self::items( $value, $atts ),
            default            => esc_html( (string) $value->value ),
        };
    }

    /** A link as a plain address (for Elementor link fields), or an anchor with link="true". */
    public static function url( string $url, array $atts, string $label = '' ): string {
        if ( '' === $url ) {
            return '';
        }
        $text = '' !== $label ? $label : ( Attributes::flag( $atts, 'pretty' ) ? Links::pretty( $url ) : $url );
        if ( Attributes::flag( $atts, 'link' ) || 'link' === ( $atts['format'] ?? '' ) ) {
            return self::anchor( $url, $text, $atts );
        }
        return Attributes::flag( $atts, 'pretty' ) ? esc_html( Links::pretty( $url ) ) : esc_url( $url );
    }

    public static function anchor( string $url, string $text, array $atts, string $class = '' ): string {
        $target = Attributes::target( $atts );
        return '<a href="' . esc_url( $url ) . '"' . ( '' !== $class ? ' class="' . esc_attr( $class ) . '"' : '' )
            . ( '' !== $target ? ' target="' . esc_attr( $target ) . '"' . ( '_blank' === $target ? ' rel="noopener"' : '' ) : '' ) . '>' . esc_html( $text ) . '</a>';
    }

    private static function email( string $email, array $atts ): string {
        return Attributes::flag( $atts, 'link' ) || 'link' === ( $atts['format'] ?? '' )
            ? '<a href="' . esc_url( 'mailto:' . $email ) . '">' . esc_html( $email ) . '</a>'
            : esc_html( $email );
    }

    private static function items( FieldValue $value, array $atts ): string {
        $items  = (array) $value->value;
        $format = strtolower( (string) ( $atts['format'] ?? 'auto' ) );
        $is_url = FieldValue::URLS === $value->type;
        $print  = static fn( string $item ): string => $is_url ? self::url( $item, $atts ) : esc_html( $item );
        if ( 'list' === $format ) {
            return '<ul class="sfpf-field-list">' . implode( '', array_map( static fn( string $item ): string => '<li>' . $print( $item ) . '</li>', $items ) ) . '</ul>';
        }
        $separator = $is_url && 'auto' === $format && ! Attributes::flag( $atts, 'link' ) ? "\n" : (string) ( $atts['separator'] ?? ', ' );
        return implode( esc_html( $separator ), array_map( $print, $items ) );
    }

    /** One item of a list (index="2"), and one value of it (key="school"). */
    private static function pick( FieldValue $value, array $atts ): FieldValue {
        $index = Attributes::index( $atts );
        $key   = sanitize_key( (string) ( $atts['key'] ?? '' ) );
        if ( null === $index && '' === $key ) {
            return $value;
        }
        $rows = null !== $index ? array_slice( $value->rows, $index, 1 ) : $value->rows;
        if ( '' === $key && [] === $value->rows ) {
            $items = array_slice( (array) $value->value, (int) $index, 1 );
            return new FieldValue( $value->type, $items, $items );
        }
        $values = array_values( array_filter( array_map( static fn( array $row ): string => (string) ( $row[ '' !== $key ? $key : array_key_first( $row ) ] ?? '' ), $rows ), static fn( string $item ): bool => '' !== $item ) );
        $type   = [] !== $values && str_contains( $key, 'url' ) ? FieldValue::URLS : FieldValue::LIST;
        return new FieldValue( $type, $values, $values );
    }
}
