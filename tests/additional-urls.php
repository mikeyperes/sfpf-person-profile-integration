<?php

declare( strict_types=1 );

namespace {
    if ( ! defined( 'ABSPATH' ) ) {
        define( 'ABSPATH', dirname( __DIR__ ) . '/' );
    }

    $GLOBALS['sfpf_link_repeater_test_fields'] = [];

    function get_field( string $field_name, string $post_id = '' ): mixed {
        unset( $post_id );
        return $GLOBALS['sfpf_link_repeater_test_fields'][ $field_name ] ?? null;
    }

    function wp_parse_url( string $url ): array|false {
        return parse_url( $url );
    }

    function esc_html( mixed $value ): string {
        return htmlspecialchars( (string) $value, ENT_QUOTES, 'UTF-8' );
    }

    function esc_attr( mixed $value ): string {
        return esc_html( $value );
    }

    function esc_url( mixed $value ): string {
        $url = trim( (string) $value );
        return filter_var( $url, FILTER_VALIDATE_URL ) ? esc_html( $url ) : '';
    }
}

namespace sfpf_person_website {
    require_once __DIR__ . '/load-core-data-normalization.php';
    require_once dirname( __DIR__ ) . '/includes/helper-functions.php';
    require_once dirname( __DIR__ ) . '/includes/shortcodes/founder-articles.php';

    $failures = [];
    $assert = static function ( bool $condition, string $message ) use ( &$failures ): void {
        if ( ! $condition ) {
            $failures[] = $message;
        }
    };

    $GLOBALS['sfpf_link_repeater_test_fields'] = [
        'additional_urls' => [
            [
                'title'  => 'Knowledge <Profile>',
                'source' => 'example.com',
                'url'    => 'https://example.com/profile',
            ],
            [
                'title'  => 'Derived source',
                'source' => '',
                'url'    => 'https://www.example.org/person',
            ],
            [
                'title'  => 'Invalid link',
                'source' => 'unsafe',
                'url'    => 'javascript:alert(1)',
            ],
            [
                'title'  => 'Schema identity',
                'source' => 'Wikidata',
                'url'    => 'https://www.wikidata.org/wiki/Q12345',
            ],
        ],
        'articles' => [
            [
                'title'  => 'Article title',
                'source' => 'publisher.test',
                'url'    => 'https://publisher.test/article',
            ],
        ],
    ];

    $titled = founder_display_additional_urls( 7, 'titled' );
    $assert( str_contains( $titled, 'founder-additional-urls format-titled' ), 'Additional URLs context class is missing.' );
    $assert( str_contains( $titled, 'Knowledge &lt;Profile&gt;' ), 'Additional URL title is not escaped.' );
    $assert( str_contains( $titled, 'example.org' ), 'Source is not derived from the URL host.' );
    $assert( ! str_contains( $titled, 'javascript:' ), 'Invalid URLs are not rejected.' );
    $assert( ! str_contains( $titled, 'wikidata.org' ), 'Wikidata leaked into public Additional URLs output.' );

    $cards = founder_display_additional_urls( 7, 'cards' );
    $assert( str_contains( $cards, 'format-cards' ) && str_contains( $cards, 'article-card-wrap' ), 'Cards format is not rendered.' );
    $assert( str_contains( $cards, 'example.com/profile' ), 'Cards format does not expose the full URL.' );

    $sources = founder_display_additional_urls( 7, 'sources' );
    $assert( str_contains( $sources, 'example.com' ) && str_contains( $sources, 'example.org' ), 'Sources format does not group normalized hosts.' );

    $compact = founder_display_additional_urls( 7, 'compact' );
    $assert( str_contains( $compact, 'format-compact' ) && str_contains( $compact, 'Derived source' ), 'Compact format is not rendered.' );

    $article = founder_display_articles( 7, 'titled' );
    $assert( str_contains( $article, 'founder-article-links format-titled' ), 'Recent Articles no longer uses the shared link renderer.' );
    $assert( str_contains( $article, 'Article title' ), 'Recent Articles content regressed.' );

    $GLOBALS['sfpf_link_repeater_test_fields']['additional_urls'] = "https://legacy.test/profile\ninvalid";
    $legacy = founder_display_additional_urls( 7, 'titled' );
    $assert( str_contains( $legacy, 'legacy.test' ) && ! str_contains( $legacy, '>invalid<' ), 'Legacy newline URL fallback failed.' );

    $assert(
        [ 'https://example.test/profile' ] === sfpf_filter_public_urls("https://www.wikidata.org/wiki/Q12345\nhttps://example.test/profile"),
        'Wikidata was not removed from a public URL collection.'
    );
    $assert(
        'https://www.google.com/search?kgmid=%2Fg%2F11abc_123&hl=en-US' === sfpf_knowledge_panel_url('/g/11abc_123'),
        'The dynamic Google Knowledge Panel URL was not constructed from a KGMID.'
    );

    if ( [] !== $failures ) {
        foreach ( $failures as $failure ) {
            fwrite( STDERR, 'FAIL: ' . $failure . PHP_EOL );
        }
        exit( 1 );
    }

    echo 'PASS: Additional URLs hides Wikidata publicly and Knowledge Panel URLs remain dynamic.' . PHP_EOL;
}
