<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Shortcodes;

use Hexa\PluginCore\DataNormalization\ValueNormalizer;
use SFPF\PersonProfile\ContentTypes\Book\BookRecord;
use SFPF\PersonProfile\Person\FieldValue;

defined( 'ABSPATH' ) || exit;

/**
 * [book field="amazon_url" link="true" target="_blank"]
 *
 * One value of a book: the book whose page is open, the one named with id,
 * else the primary book. Special fields: title (or name), cover (the featured
 * image link) and permalink; any other name is the book's stored field.
 */
final class BookShortcode {
    public const TAG = 'book';

    /** @param array<string,string>|string $atts */
    public static function render( mixed $atts ): string {
        $atts  = Attributes::parse( $atts, [ 'field' => '', 'id' => '', 'size' => 'large' ], self::TAG );
        $book  = BookRecord::resolve( absint( $atts['id'] ) );
        $field = sanitize_key( $atts['field'] );
        if ( null === $book || '' === $field ) {
            return esc_html( $atts['fallback'] );
        }
        return Output::value( self::value( $book, $field, $atts ), $atts );
    }

    /** @param array<string,string> $atts */
    public static function value( BookRecord $book, string $field, array $atts ): FieldValue {
        $size = in_array( $atts['size'] ?? '', get_intermediate_image_sizes(), true ) ? (string) $atts['size'] : 'large';
        return match ( $field ) {
            'title', 'name' => FieldValue::text( (string) get_the_title( $book->id ) ),
            'cover', 'featured_image', 'featured_image_url' => FieldValue::url( (string) get_the_post_thumbnail_url( $book->id, $size ) ),
            'permalink'     => FieldValue::url( (string) get_permalink( $book->id ), (string) get_the_title( $book->id ) ),
            default         => self::stored( $book->field( $field ) ),
        };
    }

    /** A stored value: a link, text with formatting, or a list. */
    public static function stored( mixed $value ): FieldValue {
        if ( is_array( $value ) ) {
            return FieldValue::items( ValueNormalizer::strings( $value, [ 'value', 'name', 'quote', 'url', 'title' ] ), ValueNormalizer::rows( $value ), $value );
        }
        if ( ! is_scalar( $value ) ) {
            return FieldValue::none();
        }
        $value = (string) $value;
        return false !== filter_var( $value, FILTER_VALIDATE_URL ) ? FieldValue::url( $value ) : FieldValue::html( $value );
    }
}
