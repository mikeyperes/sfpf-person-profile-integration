<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Person;

defined( 'ABSPATH' ) || exit;

/**
 * One field's value, typed, so a shortcode, an Elementor tag or a display
 * condition can print or test it the same way.
 *
 * - value  a string, or a list of strings for LIST and URLS
 * - raw    the structured data, for format="json"
 * - rows   structured items (education rows, links, images) for index/key
 * - label  link text for a URL value, when it has a natural one
 */
final class FieldValue {
    public const TEXT  = 'text';
    public const HTML  = 'html';
    public const URL   = 'url';
    public const EMAIL = 'email';
    public const LIST  = 'list';
    public const URLS  = 'urls';

    /**
     * @param string|list<string>          $value
     * @param list<array<string,mixed>>    $rows
     */
    public function __construct(
        public readonly string $type,
        public readonly string|array $value,
        public readonly mixed $raw = null,
        public readonly array $rows = [],
        public readonly string $label = ''
    ) {
    }

    public static function text( string $value ): self {
        return new self( self::TEXT, trim( $value ), trim( $value ) );
    }

    public static function html( string $value ): self {
        return new self( self::HTML, trim( $value ), trim( $value ) );
    }

    public static function url( string $value, string $label = '' ): self {
        return new self( self::URL, $value, $value, [], $label );
    }

    public static function email( string $value ): self {
        return new self( self::EMAIL, $value, $value );
    }

    /**
     * @param list<string>              $items
     * @param list<array<string,mixed>> $rows
     */
    public static function items( array $items, array $rows = [], mixed $raw = null ): self {
        $items = array_values( array_filter( array_map( 'strval', $items ), static fn( string $item ): bool => '' !== trim( $item ) ) );
        return new self( self::LIST, $items, $raw ?? ( [] !== $rows ? $rows : $items ), $rows );
    }

    /**
     * @param list<string>              $urls
     * @param list<array<string,mixed>> $rows
     */
    public static function urls( array $urls, array $rows = [], mixed $raw = null ): self {
        $urls = array_values( array_filter( array_map( 'strval', $urls ), static fn( string $url ): bool => '' !== $url ) );
        return new self( self::URLS, $urls, $raw ?? ( [] !== $rows ? $rows : $urls ), $rows );
    }

    public static function none(): self {
        return new self( self::TEXT, '', null );
    }

    public function is_empty(): bool {
        if ( is_array( $this->value ) ) {
            return [] === $this->value;
        }
        return '' === trim( wp_strip_all_tags( $this->value ) ) && ! str_contains( $this->value, '<img' );
    }

    public function count(): int {
        if ( [] !== $this->rows ) {
            return count( $this->rows );
        }
        return is_array( $this->value ) ? count( $this->value ) : ( $this->is_empty() ? 0 : 1 );
    }

    /** The value as plain text, for places that take text only (Elementor text tags, attributes). */
    public function plain( string $separator = ', ' ): string {
        return is_array( $this->value ) ? implode( $separator, $this->value ) : trim( wp_strip_all_tags( $this->value ) );
    }
}
