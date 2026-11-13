<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Schema;

defined( 'ABSPATH' ) || exit;

/**
 * Which shortcodes a page uses, read from its content and its Elementor data
 * before the page renders, so schema printed in the page head can match what
 * the page body shows.
 */
final class ShortcodeUsage {
    /** @var array<int,list<array{tag:string,atts:array<string,string>}>> */
    private static array $cache = [];

    /**
     * @param list<string> $tags
     * @return list<array{tag:string,atts:array<string,string>}>
     */
    public static function find( int $post_id, array $tags ): array {
        return array_values( array_filter( self::all( $post_id ), static fn( array $use ): bool => in_array( $use['tag'], $tags, true ) ) );
    }

    /** @return list<array{tag:string,atts:array<string,string>}> */
    public static function all( int $post_id ): array {
        if ( $post_id <= 0 ) {
            return [];
        }
        if ( isset( self::$cache[ $post_id ] ) ) {
            return self::$cache[ $post_id ];
        }
        $text = (string) get_post_field( 'post_content', $post_id );
        $elementor = get_post_meta( $post_id, '_elementor_data', true );
        if ( is_string( $elementor ) && '' !== $elementor ) {
            $decoded = json_decode( $elementor, true );
            if ( is_array( $decoded ) ) {
                array_walk_recursive(
                    $decoded,
                    static function ( mixed $value ) use ( &$text ): void {
                        if ( is_string( $value ) && str_contains( $value, '[' ) ) {
                            $text .= "\n" . $value;
                        }
                    }
                );
            }
        }
        $uses = [];
        if ( str_contains( $text, '[' ) && preg_match_all( '/' . get_shortcode_regex() . '/s', $text, $matches, PREG_SET_ORDER ) ) {
            foreach ( $matches as $match ) {
                if ( '[' === $match[1] && ']' === $match[6] ) {
                    continue; // An escaped [[shortcode]] prints literally.
                }
                $atts   = shortcode_parse_atts( $match[3] );
                $uses[] = [ 'tag' => (string) $match[2], 'atts' => is_array( $atts ) ? array_map( 'strval', $atts ) : [] ];
            }
        }
        return self::$cache[ $post_id ] = $uses;
    }

    public static function forget( int $post_id = 0 ): void {
        if ( $post_id > 0 ) {
            unset( self::$cache[ $post_id ] );
            return;
        }
        self::$cache = [];
    }
}
