<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Admin\Profile;

use SFPF\PersonProfile\Person\Links;

defined( 'ABSPATH' ) || exit;

/**
 * Turns pasted text (links, a list, or a page's HTML) into article rows:
 * every link found, cleaned, without repeats or links the person already has,
 * each with its source and the title of its page.
 */
final class ArticleImport {
    private const MAX_LINKS   = 50;
    private const TITLE_CHARS = 200;

    /**
     * @param list<string> $existing_urls The person's current article links.
     * @return array{articles:list<array{title:string,source:string,url:string}>,report:list<string>,duplicates:int}
     */
    public function process( string $input, array $existing_urls ): array {
        $report = [];
        $found  = $this->extract( $input );
        $report[] = 'Links found: ' . count( $found );

        $known = [];
        foreach ( $existing_urls as $url ) {
            $known[ self::key( $url ) ] = true;
        }
        $articles   = [];
        $duplicates = 0;
        foreach ( $found as $url ) {
            $key = self::key( $url );
            if ( isset( $known[ $key ] ) ) {
                $duplicates++;
                $report[] = 'Skipped, already listed: ' . $url;
                continue;
            }
            $known[ $key ] = true;
            if ( count( $articles ) >= self::MAX_LINKS ) {
                $report[] = 'Stopped at ' . self::MAX_LINKS . ' new links; paste the rest separately.';
                break;
            }
            [ $title, $how ] = $this->title( $url );
            $articles[] = [ 'title' => $title, 'source' => Links::host( $url ), 'url' => $url ];
            $report[]   = 'Added: ' . $url . "\n  Title: " . ( '' !== $title ? $title : '(none)' ) . ' [' . $how . ']';
        }
        return [ 'articles' => $articles, 'report' => $report, 'duplicates' => $duplicates ];
    }

    /** @return list<string> Every distinct link in the text, with https:// added where it was missing. */
    public function extract( string $input ): array {
        $candidates = [];
        if ( preg_match_all( '/href=["\']([^"\']+)["\']/i', $input, $matches ) ) {
            $candidates = $matches[1];
        }
        $rest = (string) preg_replace( '/href=["\'][^"\']+["\']/i', '', $input );
        if ( preg_match_all( '#https?://[^\s<>"\')\]]+#i', $rest, $matches ) ) {
            $candidates = array_merge( $candidates, $matches[0] );
            $rest       = str_replace( $matches[0], ' ', $rest );
        }
        // Links pasted without https:// ("www.site.com/page" or "site.com/page"), from text outside links.
        $rest = (string) preg_replace( '#<a\b[^>]*>.*?</a>#is', ' ', $rest );
        foreach ( preg_split( '/[\s,]+/', wp_strip_all_tags( $rest ) ) ?: [] as $part ) {
            $part = trim( $part, " \t\n\r\0\x0B,;\"'<>()[]" );
            if ( preg_match( '/^[a-z0-9][a-z0-9\-]*(\.[a-z0-9\-]+)*\.[a-z]{2,}(\/\S*)?$/i', $part ) ) {
                $candidates[] = $part;
            }
        }
        $urls = [];
        foreach ( $candidates as $candidate ) {
            $candidate = rtrim( trim( html_entity_decode( (string) $candidate ) ), '.,;)\'">\\' );
            if ( '' === $candidate || '#' === $candidate[0] || preg_match( '/^(javascript|mailto|tel):/i', $candidate ) ) {
                continue;
            }
            $url = preg_match( '#^https?://#i', $candidate ) ? $candidate : 'https://' . $candidate;
            $url = (string) preg_replace( '#^(https?://)www\.#i', '$1', $url );
            $url = esc_url_raw( $url, [ 'http', 'https' ] );
            if ( '' !== $url && filter_var( $url, FILTER_VALIDATE_URL ) ) {
                $urls[ self::key( $url ) ] ??= $url;
            }
        }
        return array_values( $urls );
    }

    /** @return array{0:string,1:string} The page's title and how it was found. */
    private function title( string $url ): array {
        $response = wp_safe_remote_get( $url, [ 'timeout' => 10, 'redirection' => 3, 'limit_response_size' => 524288, 'user-agent' => 'Mozilla/5.0 (compatible; SFPF article import)' ] );
        $code     = is_wp_error( $response ) ? 0 : (int) wp_remote_retrieve_response_code( $response );
        $body     = is_wp_error( $response ) ? '' : (string) wp_remote_retrieve_body( $response );
        if ( $code >= 200 && $code < 400 && '' !== $body ) {
            if ( preg_match( '/<title[^>]*>(.*?)<\/title>/is', $body, $match ) ) {
                $title = self::clean( $match[1] );
                if ( '' !== $title ) {
                    return [ $title, 'page title' ];
                }
            }
            if ( preg_match( '/<meta[^>]+(?:property=["\']og:title["\'][^>]+content=["\']([^"\']+)["\']|content=["\']([^"\']+)["\'][^>]+property=["\']og:title["\'])/is', $body, $match ) ) {
                return [ self::clean( '' !== $match[1] ? $match[1] : $match[2] ), 'og:title' ];
            }
        }
        $status = is_wp_error( $response ) ? 'not reachable: ' . $response->get_error_message() : 'HTTP ' . $code;
        $slug   = basename( rtrim( (string) wp_parse_url( $url, PHP_URL_PATH ), '/' ) );
        if ( '' !== $slug && ! preg_match( '/\.\w{2,4}$/', $slug ) ) {
            return [ ucwords( str_replace( [ '-', '_' ], ' ', $slug ) ), $status . ', title from the link' ];
        }
        return [ '', $status . ', no title' ];
    }

    private static function clean( string $title ): string {
        $title = trim( html_entity_decode( wp_strip_all_tags( $title ), ENT_QUOTES, 'UTF-8' ) );
        $title = trim( (string) preg_replace( '/\s+/', ' ', $title ) );
        return function_exists( 'mb_substr' ) && mb_strlen( $title ) > self::TITLE_CHARS ? mb_substr( $title, 0, self::TITLE_CHARS - 1 ) . '…' : $title;
    }

    /** A link compared without its scheme, www and trailing slash. */
    private static function key( string $url ): string {
        return strtolower( (string) preg_replace( '#^https?://(www\.)?#i', '', rtrim( $url, '/' ) ) );
    }
}
