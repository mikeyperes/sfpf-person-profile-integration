<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Person;

use Hexa\PluginCore\DataNormalization\ValueNormalizer;

defined( 'ABSPATH' ) || exit;

/**
 * Link rules for a person profile: link lists (articles, additional URLs),
 * profile-link text areas, Wikidata links (kept for schema, never shown) and
 * the Google Knowledge Graph ID.
 */
final class Links {
    /**
     * A link repeater, or its older one-URL-per-line text, as clean rows.
     *
     * @return list<array{title:string,source:string,url:string}>
     */
    public static function rows( mixed $value ): array {
        if ( is_string( $value ) ) {
            $value = array_map( static fn( string $url ): array => [ 'url' => $url ], preg_split( '/\R/', $value ) ?: [] );
        }
        $rows = [];
        foreach ( is_array( $value ) ? $value : [] as $item ) {
            $url = ValueNormalizer::url( is_array( $item ) ? ( $item['url'] ?? '' ) : '' );
            if ( '' === $url || ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
                continue;
            }
            $source = trim( (string) ( $item['source'] ?? '' ) );
            $rows[] = [
                'title'  => trim( (string) ( $item['title'] ?? '' ) ),
                'source' => '' !== $source ? $source : self::host( $url ),
                'url'    => $url,
            ];
        }
        return $rows;
    }

    /** @return list<array{title:string,source:string,url:string}> */
    public static function public_rows( mixed $value ): array {
        return array_values( array_filter( self::rows( $value ), static fn( array $row ): bool => ! self::is_wikidata( $row['url'] ) ) );
    }

    /** @return list<string> Valid URLs from text or nested values. */
    public static function urls( mixed $value ): array {
        return ValueNormalizer::url_values( $value, false, false );
    }

    /** @return list<string> */
    public static function public_urls( mixed $value ): array {
        return array_values( array_filter( self::urls( $value ), static fn( string $url ): bool => ! self::is_wikidata( $url ) ) );
    }

    /** @return list<string> */
    public static function wikidata_urls( mixed ...$values ): array {
        $urls = [];
        foreach ( $values as $value ) {
            foreach ( self::urls( $value ) as $url ) {
                if ( self::is_wikidata( $url ) ) {
                    $urls[] = $url;
                }
            }
        }
        return array_values( array_unique( $urls ) );
    }

    public static function is_wikidata( string $url ): bool {
        $host = strtolower( rtrim( (string) wp_parse_url( $url, PHP_URL_HOST ), '.' ) );
        return 'wikidata.org' === $host || str_ends_with( $host, '.wikidata.org' );
    }

    public static function host( string $url ): string {
        return (string) preg_replace( '/^www\./i', '', (string) wp_parse_url( $url, PHP_URL_HOST ) );
    }

    /** A URL without its scheme and trailing slash, for display. */
    public static function pretty( string $url ): string {
        return (string) preg_replace( '#^https?://#i', '', rtrim( $url, '/' ) );
    }

    /**
     * The Knowledge Graph ID from an ID or a pasted Google URL: /g/… or /m/…,
     * or an empty string when it is not one.
     */
    public static function knowledge_graph_id( mixed $value ): string {
        $value = trim( (string) ( is_scalar( $value ) ? $value : '' ) );
        if ( '' === $value ) {
            return '';
        }
        if ( preg_match( '/[?&]kgmid=([^&\s#]+)/i', $value, $match ) ) {
            $value = $match[1];
        }
        $value = rawurldecode( $value );
        if ( preg_match( '#^(?:g|m)/[A-Za-z0-9_-]+$#', $value ) ) {
            $value = '/' . $value;
        }
        return preg_match( '#^/(?:g|m)/[A-Za-z0-9_-]+$#', $value ) ? $value : '';
    }

    public static function knowledge_panel_url( mixed $value ): string {
        $kgmid = self::knowledge_graph_id( $value );
        return '' === $kgmid ? '' : 'https://www.google.com/search?kgmid=' . rawurlencode( $kgmid ) . '&hl=en-US';
    }
}
