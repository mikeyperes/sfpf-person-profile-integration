<?php

declare( strict_types=1 );

namespace {
    if ( ! defined( 'ABSPATH' ) ) {
        define( 'ABSPATH', dirname( __DIR__ ) . '/' );
    }

    $GLOBALS['sfpf_query_test_filters'] = [];
    $GLOBALS['sfpf_query_test_actions'] = [];
    $GLOBALS['sfpf_query_test_wp_queries'] = [];
    $GLOBALS['sfpf_query_test_get_posts'] = [];
    $GLOBALS['sfpf_query_test_relationship_ids'] = [ 101, 102, 103 ];
    $GLOBALS['sfpf_query_test_relationship_ids_by_field'] = [];
    $GLOBALS['sfpf_query_test_organizations'] = [
        101 => [ 'title' => 'Alpha Organization', 'url' => 'https://alpha.example/' ],
        102 => [ 'title' => 'Beta Organization', 'url' => 'https://beta.example/' ],
        103 => [ 'title' => 'Gamma Organization', 'url' => 'https://gamma.example/' ],
        999 => [ 'title' => 'Unrelated Organization', 'url' => 'https://unrelated.example/' ],
    ];

    final class WP_Query {
        /** @var list<int> */
        public array $posts = [];
        public int $found_posts = 0;

        /** @param array<string,mixed> $args */
        public function __construct( array $args = [] ) {
            $GLOBALS['sfpf_query_test_wp_queries'][] = $args;
            $meta_field = (string) ( $args['meta_query'][0]['key'] ?? '' );
            $field_ids = $GLOBALS['sfpf_query_test_relationship_ids_by_field'];
            $ids = array_key_exists( $meta_field, $field_ids )
                ? $field_ids[ $meta_field ]
                : $GLOBALS['sfpf_query_test_relationship_ids'];
            $this->found_posts = count( $ids );
            $offset = max( 0, (int) ( $args['offset'] ?? 0 ) );
            $limit = max( 0, (int) ( $args['posts_per_page'] ?? 0 ) );
            $this->posts = array_slice( $ids, $offset, $limit );
        }
    }

    function apply_filters( string $hook, mixed $value, mixed ...$args ): mixed {
        unset( $args );
        if ( ! array_key_exists( $hook, $GLOBALS['sfpf_query_test_filters'] ) ) {
            return $value;
        }

        $filtered = $GLOBALS['sfpf_query_test_filters'][ $hook ];
        return is_callable( $filtered ) ? $filtered( $value ) : $filtered;
    }

    function do_action( string $hook, mixed ...$args ): void {
        $GLOBALS['sfpf_query_test_actions'][] = [ $hook, ...$args ];
    }

    function add_shortcode( string $tag, callable $callback ): void {
        unset( $tag, $callback );
    }

    function shortcode_atts( array $pairs, array $atts ): array {
        return array_merge( $pairs, array_intersect_key( $atts, $pairs ) );
    }

    function sanitize_key( mixed $key ): string {
        return preg_replace( '/[^a-z0-9_\-]/', '', strtolower( (string) $key ) ) ?? '';
    }

    function sanitize_text_field( mixed $value ): string {
        return trim( strip_tags( (string) $value ) );
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

    function esc_url_raw( mixed $value ): string {
        $url = trim( (string) $value );
        return filter_var( $url, FILTER_VALIDATE_URL ) ? $url : '';
    }

    function wp_strip_all_tags( mixed $value ): string {
        return strip_tags( (string) $value );
    }

    function wp_kses_post( mixed $value ): string {
        return (string) $value;
    }

    function wp_trim_words( string $text, int $word_count ): string {
        $words = preg_split( '/\s+/', trim( strip_tags( $text ) ) ) ?: [];
        return implode( ' ', array_slice( $words, 0, $word_count ) );
    }

    function get_option( string $name, mixed $default = false ): mixed {
        if ( 'sfpf_elementor_loop_assignments' === $name ) {
            return [ 'book' => 77 ];
        }

        return $default;
    }

    function get_posts( array $args = [] ): array {
        $GLOBALS['sfpf_query_test_get_posts'][] = $args;
        return [
            (object) [ 'ID' => 201, 'post_title' => 'Book One', 'post_excerpt' => 'First book', 'post_content' => '' ],
            (object) [ 'ID' => 202, 'post_title' => 'Book Two', 'post_excerpt' => 'Second book', 'post_content' => '' ],
        ];
    }

    function get_the_title( mixed $post ): string {
        $post_id = is_object( $post ) && isset( $post->ID ) ? (int) $post->ID : (int) $post;
        return $GLOBALS['sfpf_query_test_organizations'][ $post_id ]['title'] ?? '';
    }

    function get_permalink( mixed $post ): string {
        $post_id = is_object( $post ) && isset( $post->ID ) ? (int) $post->ID : (int) $post;
        return 'https://example.test/organization/' . $post_id . '/';
    }

    function get_field( string $field, mixed $post_id = null ): mixed {
        if ( is_int( $post_id ) && isset( $GLOBALS['sfpf_query_test_organizations'][ $post_id ] ) ) {
            return 'url' === $field ? $GLOBALS['sfpf_query_test_organizations'][ $post_id ]['url'] : null;
        }

        return null;
    }

    function get_site_url(): string {
        return 'https://example.test';
    }

    function get_avatar_url( int $user_id, array $args = [] ): string {
        unset( $user_id, $args );
        return '';
    }
}

namespace sfpf_person_website {
    function get_founder_user_id(): int {
        return 9;
    }

    /** @return array<string,mixed> */
    function get_founder_full_info(): array {
        return [
            'id'           => 9,
            'display_name' => 'Example Founder',
            'first_name'   => 'Example',
            'last_name'    => 'Founder',
            'email'        => '',
            'urls'         => [],
        ];
    }

    function sfpf_collect_wikidata_urls( mixed $value ): array {
        unset( $value );
        return [];
    }

    require_once dirname( __DIR__ ) . '/includes/frontend-query-bounds.php';
    require_once dirname( __DIR__ ) . '/includes/shortcodes/loop.php';
    require_once dirname( __DIR__ ) . '/includes/shortcodes/founder-sections.php';
    require_once dirname( __DIR__ ) . '/schema/schema-builder.php';

    $failures = [];
    $assert = static function ( bool $condition, string $message ) use ( &$failures ): void {
        if ( ! $condition ) {
            $failures[] = $message;
        }
    };

    $html = sfpf_loop_shortcode( [ 'cpt' => 'book', 'columns' => '3' ] );
    $loop_args = $GLOBALS['sfpf_query_test_get_posts'][0] ?? [];
    $assert( 12 === ( $loop_args['posts_per_page'] ?? 0 ), 'Omitted loop rows did not use the positive default.' );
    $assert( str_contains( $html, 'Book One' ) && str_contains( $html, 'Book Two' ), 'Bounded loop output lost posts.' );

    $GLOBALS['sfpf_query_test_filters'] = [
        'sfpf_loop_default_limit' => 5,
        'sfpf_loop_hard_max'      => 7,
    ];
    sfpf_loop_shortcode( [ 'cpt' => 'book', 'columns' => '4', 'rows' => '1000' ] );
    $loop_args = $GLOBALS['sfpf_query_test_get_posts'][1] ?? [];
    $assert( 7 === ( $loop_args['posts_per_page'] ?? 0 ), 'Explicit loop rows were not clamped to the filtered hard maximum.' );

    sfpf_loop_shortcode( [ 'cpt' => 'book', 'columns' => '4' ] );
    $loop_args = $GLOBALS['sfpf_query_test_get_posts'][2] ?? [];
    $assert( 5 === ( $loop_args['posts_per_page'] ?? 0 ), 'Filtered loop default was not honored.' );

    $GLOBALS['sfpf_query_test_filters'] = [
        'sfpf_founder_organizations_default_limit' => 2,
        'sfpf_founder_organizations_hard_max'      => 5,
    ];
    $GLOBALS['sfpf_query_test_wp_queries'] = [];
    $org_html = founder_display_organizations_founded( 'compact', 9 );
    $relationship_queries = $GLOBALS['sfpf_query_test_wp_queries'];

    foreach ( [ 'Alpha Organization', 'Beta Organization', 'Gamma Organization' ] as $organization_name ) {
        $assert( str_contains( $org_html, $organization_name ), 'Relationship batching lost ' . $organization_name . ' from founder output.' );
    }
    $assert( ! str_contains( $org_html, 'Unrelated Organization' ), 'Founder output included an unrelated Organization.' );
    $assert( 2 === count( $relationship_queries ), 'Founder relationships were not completed through bounded batches.' );
    $assert( false === ( $relationship_queries[0]['no_found_rows'] ?? true ), 'Initial founder query did not use a count for completeness.' );
    $assert( true === ( $relationship_queries[1]['no_found_rows'] ?? false ), 'Follow-up founder query repeated the count operation.' );
    $assert( 2 === ( $relationship_queries[0]['posts_per_page'] ?? 0 ), 'Founder relationship batch size was not honored.' );
    $assert( 1 === ( $relationship_queries[1]['posts_per_page'] ?? 0 ), 'Final founder relationship batch was not sized to the remainder.' );

    $meta_query = $relationship_queries[0]['meta_query'] ?? [];
    $canonical_clause = array_values(
        array_filter(
            $meta_query,
            static fn ( mixed $clause ): bool => is_array( $clause ) && 'founder_users' === ( $clause['key'] ?? '' )
        )
    );
    $assert( [] !== $canonical_clause, 'Founder lookup did not use the SMC founder_users ACF relationship.' );
    $assert( 'ids' === ( $relationship_queries[0]['fields'] ?? '' ), 'Founder relationship query loaded full post objects.' );

    $GLOBALS['sfpf_query_test_wp_queries'] = [];
    $schema = build_person_schema();
    $works_for = $schema['worksFor'] ?? [];
    $works_for_names = array_column( $works_for, 'name' );
    $assert(
        [ 'Alpha Organization', 'Beta Organization', 'Gamma Organization' ] === $works_for_names,
        'Person schema did not preserve every explicit founder relationship across bounded batches.'
    );
    $assert( 2 === count( $GLOBALS['sfpf_query_test_wp_queries'] ), 'Person schema did not reuse the bounded relationship query path.' );

    $GLOBALS['sfpf_query_test_relationship_ids_by_field'] = [
        'founder_users' => [],
        'founder'       => [ 103 ],
    ];
    $GLOBALS['sfpf_query_test_wp_queries'] = [];
    $legacy_ids = sfpf_founder_organization_ids( 9 );
    $legacy_queries = $GLOBALS['sfpf_query_test_wp_queries'];
    $assert( [ 103 ] === $legacy_ids, 'Legacy founder relationship fallback was not preserved.' );
    $assert( 2 === count( $legacy_queries ), 'Legacy founder fallback did not follow an empty canonical query.' );
    $assert( 'founder' === ( $legacy_queries[1]['meta_query'][0]['key'] ?? '' ), 'Legacy fallback queried the wrong ACF field.' );

    $GLOBALS['sfpf_query_test_relationship_ids_by_field'] = [
        'founder_users' => [],
        'founder'       => [],
    ];
    $GLOBALS['sfpf_query_test_actions'] = [];
    $GLOBALS['sfpf_query_test_wp_queries'] = [];
    $inventory_ids = sfpf_founder_organization_ids( 9 );
    $inventory_queries = $GLOBALS['sfpf_query_test_wp_queries'];
    $assert( [ 101, 102, 103 ] === $inventory_ids, 'Bounded inventory fallback lost pre-relationship Organization output.' );
    $assert( 4 === count( $inventory_queries ), 'Inventory fallback did not use counted, bounded batches after relationship checks.' );
    $assert( ! isset( $inventory_queries[2]['meta_query'] ), 'Inventory fallback retained a relationship constraint.' );
    foreach ( $inventory_queries as $query_args ) {
        $query_limit = (int) ( $query_args['posts_per_page'] ?? 0 );
        $assert( $query_limit > 0 && $query_limit <= 2, 'An inventory fallback query escaped its positive batch cap.' );
    }
    $inventory_action = array_values(
        array_filter(
            $GLOBALS['sfpf_query_test_actions'],
            static fn ( array $action ): bool => 'sfpf_founder_organizations_inventory_fallback' === ( $action[0] ?? '' )
        )
    );
    $assert( [] !== $inventory_action, 'Use of the legacy Organization inventory fallback was silent.' );

    $GLOBALS['sfpf_query_test_filters']['sfpf_founder_organizations_allow_inventory_fallback'] = false;
    $GLOBALS['sfpf_query_test_wp_queries'] = [];
    $assert( [] === sfpf_founder_organization_ids( 9 ), 'Inventory fallback opt-out did not suppress unrelated Organizations.' );
    $assert( 2 === count( $GLOBALS['sfpf_query_test_wp_queries'] ), 'Inventory fallback opt-out ran an unnecessary inventory query.' );

    $GLOBALS['sfpf_query_test_relationship_ids_by_field'] = [];
    $GLOBALS['sfpf_query_test_relationship_ids'] = [ 101, 102, 103, 104, 105, 106, 107, 108 ];
    foreach ( $GLOBALS['sfpf_query_test_relationship_ids'] as $organization_id ) {
        $GLOBALS['sfpf_query_test_organizations'][ $organization_id ] ??= [
            'title' => 'Organization ' . $organization_id,
            'url'   => 'https://org-' . $organization_id . '.example/',
        ];
    }
    $GLOBALS['sfpf_query_test_filters'] = [
        'sfpf_founder_organizations_default_limit' => 3,
        'sfpf_founder_organizations_hard_max'      => 4,
    ];
    $GLOBALS['sfpf_query_test_actions'] = [];
    $GLOBALS['sfpf_query_test_wp_queries'] = [];
    $capped_ids = sfpf_founder_organization_ids( 9 );
    $assert( 4 === count( $capped_ids ), 'Founder relationship results exceeded or fell short of the hard maximum.' );
    foreach ( $GLOBALS['sfpf_query_test_wp_queries'] as $query_args ) {
        $query_limit = (int) ( $query_args['posts_per_page'] ?? 0 );
        $assert( $query_limit > 0 && $query_limit <= 3, 'A founder relationship batch escaped its positive query cap.' );
    }
    $truncation = array_values(
        array_filter(
            $GLOBALS['sfpf_query_test_actions'],
            static fn ( array $action ): bool => 'sfpf_founder_organizations_truncated' === ( $action[0] ?? '' )
        )
    );
    $assert( [] !== $truncation, 'Founder relationship truncation was silent.' );
    $assert( 8 === ( $truncation[0][2] ?? 0 ) && 4 === ( $truncation[0][3] ?? 0 ), 'Truncation action did not report total and cap.' );

    $GLOBALS['sfpf_query_test_filters'] = [
        'sfpf_loop_default_limit' => 99,
        'sfpf_loop_hard_max'      => 4,
    ];
    $limits = sfpf_frontend_query_limits( 'loop', 12, 48 );
    $assert( 4 === $limits['default'] && 4 === $limits['hard_max'], 'A filtered default was not clamped to its hard maximum.' );
    $assert( 4 === sfpf_clamp_frontend_query_limit( 999, $limits['default'], $limits['hard_max'] ), 'Requested limit escaped the hard maximum.' );

    foreach ( [ 'includes/shortcodes/loop.php', 'includes/shortcodes/founder-sections.php', 'schema/schema-builder.php' ] as $relative_path ) {
        // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Local test fixture.
        $source = file_get_contents( dirname( __DIR__ ) . '/' . $relative_path );
        $assert(
            false !== $source && 0 === preg_match( "/['\"]posts_per_page['\"]\s*=>\s*-1/", $source ),
            'Unbounded frontend posts_per_page remains in ' . $relative_path . '.'
        );
    }

    foreach ( $GLOBALS['sfpf_query_test_get_posts'] as $query_args ) {
        $assert( (int) ( $query_args['posts_per_page'] ?? 0 ) > 0, 'A loop query used a non-positive posts_per_page value.' );
    }

    if ( [] !== $failures ) {
        foreach ( $failures as $failure ) {
            fwrite( STDERR, 'FAIL: ' . $failure . PHP_EOL );
        }
        exit( 1 );
    }

    echo 'PASS: Frontend loops and founder relationships use complete, filterable bounded queries.' . PHP_EOL;
}
