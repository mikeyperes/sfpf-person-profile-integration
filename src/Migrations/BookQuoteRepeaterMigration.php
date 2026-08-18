<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Migrations;

defined( 'ABSPATH' ) || exit;

/**
 * Consolidates legacy Book quote fields into the canonical quotes repeater.
 * Legacy metadata is retained after verification so the migration is reversible.
 */
final class BookQuoteRepeaterMigration {
    public const OPTION = 'sfpf_book_quotes_repeater_migration';
    public const VERSION = '1';

    private const DESTINATION_FIELD_KEY = 'field_sfpf_book_quotes';
    private const QUOTE_FIELD_KEY = 'field_sfpf_book_quote_text';
    private const URL_FIELD_KEY = 'field_sfpf_book_quote_url';
    private const TAGLINE_FIELD_KEY = 'field_sfpf_book_quote_tagline';

    /** @var list<string> */
    private const LEGACY_REPEATERS = [ 'notable_quotes', 'book_quotes' ];

    /** @var list<string> */
    private const LEGACY_SINGLE_FIELDS = [ 'quote', 'book_quote' ];

    public static function register(): void {
        add_action( 'acf/init', [ self::class, 'run' ], 20 );
    }

    /** @return array<string,mixed> */
    public static function run(): array {
        $stored = get_option( self::OPTION, [] );
        if ( is_array( $stored ) && self::VERSION === (string) ( $stored['version'] ?? '' ) ) {
            return $stored;
        }

        $report = [
            'version'         => self::VERSION,
            'posts_scanned'   => 0,
            'posts_changed'   => 0,
            'legacy_rows'     => 0,
            'rows_written'    => 0,
            'legacy_retained' => true,
            'errors'          => [],
        ];

        if ( ! function_exists( 'get_posts' ) || ! function_exists( 'get_post_meta' ) || ! function_exists( 'update_field' ) ) {
            $report['errors'][] = 'The WordPress and ACF field APIs required for Book quote migration are unavailable.';
            return $report;
        }

        $page = 1;
        do {
            $post_ids = get_posts(
                [
                    'post_type'              => [ 'book', 'hbs-book' ],
                    'post_status'            => function_exists( 'get_post_stati' ) ? array_keys( get_post_stati() ) : 'any',
                    'posts_per_page'         => 100,
                    'paged'                  => $page,
                    'orderby'                => 'ID',
                    'order'                  => 'ASC',
                    'fields'                 => 'ids',
                    'no_found_rows'          => true,
                    'update_post_meta_cache' => false,
                    'update_post_term_cache' => false,
                ]
            );
            $post_ids = is_array( $post_ids ) ? array_map( 'intval', $post_ids ) : [];

            foreach ( $post_ids as $post_id ) {
                ++$report['posts_scanned'];
                $result = self::migrate_post( $post_id );
                $report['legacy_rows'] += $result['legacy_rows'];
                $report['rows_written'] += $result['rows_written'];
                if ( $result['changed'] ) {
                    ++$report['posts_changed'];
                }
                foreach ( $result['errors'] as $error ) {
                    $report['errors'][] = 'Book ' . $post_id . ': ' . $error;
                }
            }

            ++$page;
        } while ( 100 === count( $post_ids ) );

        if ( [] === $report['errors'] ) {
            update_option( self::OPTION, $report, false );
        }

        return $report;
    }

    /**
     * @return array{changed:bool,legacy_rows:int,rows_written:int,errors:list<string>}
     */
    public static function migrate_post( int $post_id ): array {
        $result = [
            'changed'      => false,
            'legacy_rows'  => 0,
            'rows_written' => 0,
            'errors'       => [],
        ];

        if ( $post_id <= 0 ) {
            $result['errors'][] = 'Invalid post ID.';
            return $result;
        }

        $destination = self::read_rows( $post_id, 'quotes' );
        $legacy = [];
        foreach ( self::LEGACY_REPEATERS as $field_name ) {
            $legacy = self::merge_rows( $legacy, self::read_rows( $post_id, $field_name ) );
        }
        foreach ( self::LEGACY_SINGLE_FIELDS as $field_name ) {
            $value = self::string_value( get_post_meta( $post_id, $field_name, true ) );
            if ( '' !== trim( $value ) ) {
                $legacy = self::merge_rows( $legacy, [ [ 'quote' => $value, 'url' => '', 'tagline' => '' ] ] );
            }
        }
        $result['legacy_rows'] = count( $legacy );

        if ( [] === $legacy ) {
            return $result;
        }

        $merged = self::merge_rows( $destination, $legacy );
        if ( $merged === $destination ) {
            return $result;
        }

        $field_rows = array_map(
            static fn( array $row ): array => [
                self::QUOTE_FIELD_KEY   => $row['quote'],
                self::URL_FIELD_KEY     => $row['url'],
                self::TAGLINE_FIELD_KEY => $row['tagline'],
            ],
            $merged
        );

        update_field( self::DESTINATION_FIELD_KEY, $field_rows, $post_id );
        $verified = self::read_rows_from_meta( $post_id, 'quotes' );
        if ( $verified !== $merged ) {
            $result['errors'][] = 'The canonical quotes rows did not verify after writing.';
            return $result;
        }

        $result['changed'] = true;
        $result['rows_written'] = count( $merged ) - count( $destination );
        return $result;
    }

    /** @return list<array{quote:string,url:string,tagline:string}> */
    private static function read_rows( int $post_id, string $field_name ): array {
        if ( function_exists( 'get_field' ) ) {
            $value = get_field( $field_name, $post_id );
            if ( is_array( $value ) ) {
                return self::normalize_rows( $value );
            }
        }

        return self::read_rows_from_meta( $post_id, $field_name );
    }

    /** @return list<array{quote:string,url:string,tagline:string}> */
    private static function read_rows_from_meta( int $post_id, string $field_name ): array {
        $count = (int) get_post_meta( $post_id, $field_name, true );
        $rows = [];
        for ( $index = 0; $index < $count; ++$index ) {
            $row = self::normalize_row(
                [
                    'quote'   => get_post_meta( $post_id, $field_name . '_' . $index . '_quote', true ),
                    'url'     => self::first_meta( $post_id, $field_name, $index, [ 'url', 'source_url' ] ),
                    'tagline' => self::first_meta( $post_id, $field_name, $index, [ 'tagline', 'attribution', 'assigned_name', 'publication_name', 'publication_info' ] ),
                ]
            );
            if ( self::has_value( $row ) ) {
                $rows[] = $row;
            }
        }
        return $rows;
    }

    /** @param list<string> $suffixes */
    private static function first_meta( int $post_id, string $field_name, int $index, array $suffixes ): string {
        foreach ( $suffixes as $suffix ) {
            $value = self::string_value( get_post_meta( $post_id, $field_name . '_' . $index . '_' . $suffix, true ) );
            if ( '' !== trim( $value ) ) {
                return $value;
            }
        }
        return '';
    }

    /** @param array<int,mixed> $rows @return list<array{quote:string,url:string,tagline:string}> */
    private static function normalize_rows( array $rows ): array {
        $normalized = [];
        foreach ( $rows as $row ) {
            if ( is_scalar( $row ) ) {
                $row = [ 'quote' => (string) $row ];
            }
            if ( ! is_array( $row ) ) {
                continue;
            }
            $row = self::normalize_row( $row );
            if ( self::has_value( $row ) ) {
                $normalized[] = $row;
            }
        }
        return $normalized;
    }

    /** @param array<string,mixed> $row @return array{quote:string,url:string,tagline:string} */
    private static function normalize_row( array $row ): array {
        return [
            'quote' => self::string_value( $row['quote'] ?? $row[ self::QUOTE_FIELD_KEY ] ?? '' ),
            'url' => self::string_value( $row['url'] ?? $row['source_url'] ?? $row[ self::URL_FIELD_KEY ] ?? '' ),
            'tagline' => self::string_value(
                $row['tagline']
                    ?? $row['attribution']
                    ?? $row['assigned_name']
                    ?? $row['publication_name']
                    ?? $row['publication_info']
                    ?? $row[ self::TAGLINE_FIELD_KEY ]
                    ?? ''
            ),
        ];
    }

    /**
     * @param list<array{quote:string,url:string,tagline:string}> $destination
     * @param list<array{quote:string,url:string,tagline:string}> $legacy
     * @return list<array{quote:string,url:string,tagline:string}>
     */
    private static function merge_rows( array $destination, array $legacy ): array {
        $merged = $destination;
        $seen = [];
        foreach ( $destination as $row ) {
            $seen[ serialize( $row ) ] = true;
        }
        foreach ( $legacy as $row ) {
            $signature = serialize( $row );
            if ( isset( $seen[ $signature ] ) ) {
                continue;
            }
            $seen[ $signature ] = true;
            $merged[] = $row;
        }
        return $merged;
    }

    /** @param array{quote:string,url:string,tagline:string} $row */
    private static function has_value( array $row ): bool {
        return '' !== trim( $row['quote'] ) || '' !== trim( $row['url'] ) || '' !== trim( $row['tagline'] );
    }

    private static function string_value( mixed $value ): string {
        return is_scalar( $value ) ? (string) $value : '';
    }
}
