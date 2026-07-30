<?php

declare( strict_types=1 );

namespace sfpf_person_website;

/**
 * Shared resource bounds for public shortcodes and schema queries.
 */

defined( 'ABSPATH' ) || exit;

const SFPF_LOOP_DEFAULT_QUERY_LIMIT = 12;
const SFPF_LOOP_HARD_QUERY_MAX = 48;
const SFPF_FOUNDER_ORGANIZATION_QUERY_BATCH = 20;
const SFPF_FOUNDER_ORGANIZATION_HARD_QUERY_MAX = 100;

/**
 * Resolve filterable positive query limits for a frontend context.
 *
 * @return array{default:int,hard_max:int}
 */
function sfpf_frontend_query_limits( string $context, int $default, int $hard_max ): array {
    $context = sanitize_key( $context );

    $default = (int) apply_filters( 'sfpf_frontend_query_default_limit', $default, $context );
    $hard_max = (int) apply_filters( 'sfpf_frontend_query_hard_max', $hard_max, $context );

    $default = (int) apply_filters( 'sfpf_' . $context . '_default_limit', $default );
    $hard_max = (int) apply_filters( 'sfpf_' . $context . '_hard_max', $hard_max );

    $hard_max = max( 1, $hard_max );
    $default = min( $hard_max, max( 1, $default ) );

    return [
        'default'  => $default,
        'hard_max' => $hard_max,
    ];
}

/**
 * Clamp a requested count to a positive default and hard maximum.
 */
function sfpf_clamp_frontend_query_limit( int $requested, int $default, int $hard_max ): int {
    $hard_max = max( 1, $hard_max );
    $default = min( $hard_max, max( 1, $default ) );
    $requested = $requested > 0 ? $requested : $default;

    return min( $requested, $hard_max );
}

/**
 * Normalize the ID-only result returned by a bounded WP_Query.
 *
 * @param array<int,mixed> $posts Query results.
 * @return list<int>
 */
function sfpf_normalize_query_post_ids( array $posts ): array {
    $ids = [];

    foreach ( $posts as $post ) {
        $post_id = is_object( $post ) && isset( $post->ID ) ? (int) $post->ID : (int) $post;
        if ( $post_id > 0 ) {
            $ids[ $post_id ] = $post_id;
        }
    }

    return array_values( $ids );
}

/**
 * Run one bounded, count-aware Organization relationship query.
 *
 * @param array<string,mixed> $base_args Query arguments including meta_query.
 * @return array{ids:list<int>,total:int}
 */
function sfpf_query_founder_organization_relation( array $base_args, int $batch_size, int $hard_max ): array {
    $first_query = new \WP_Query(
        array_merge(
            $base_args,
            [
                'posts_per_page' => min( $batch_size, $hard_max ),
                'no_found_rows'  => false,
            ]
        )
    );

    $ids = sfpf_normalize_query_post_ids( $first_query->posts );
    $total = max( count( $ids ), (int) $first_query->found_posts );
    $target = min( $total, $hard_max );

    while ( count( $ids ) < $target ) {
        $remaining = $target - count( $ids );
        $query = new \WP_Query(
            array_merge(
                $base_args,
                [
                    'posts_per_page' => min( $batch_size, $remaining ),
                    'offset'         => count( $ids ),
                    'no_found_rows'  => true,
                ]
            )
        );
        $next_ids = sfpf_normalize_query_post_ids( $query->posts );
        $new_ids = array_values( array_diff( $next_ids, $ids ) );

        if ( [] === $new_ids ) {
            break;
        }

        $ids = array_values( array_unique( array_merge( $ids, $new_ids ) ) );
    }

    return [
        'ids'   => array_slice( $ids, 0, $hard_max ),
        'total' => $total,
    ];
}

/**
 * Find published Organizations explicitly related to the Person user.
 *
 * SMC Organization Profile stores WordPress founders in the multiple-user
 * `founder_users` ACF field. The single `founder` clause preserves the legacy
 * SFPF field. If neither relationship exists, a filterable inventory fallback
 * preserves pre-migration sites. Every path uses bounded ID-only batches; the
 * first query's count controls pagination without loading every Organization
 * object.
 *
 * @return list<int>
 */
function sfpf_founder_organization_ids( int $user_id = 0 ): array {
    $user_id = $user_id > 0 ? $user_id : (int) get_founder_user_id();
    if ( $user_id <= 0 ) {
        return [];
    }

    $limits = sfpf_frontend_query_limits(
        'founder_organizations',
        SFPF_FOUNDER_ORGANIZATION_QUERY_BATCH,
        SFPF_FOUNDER_ORGANIZATION_HARD_QUERY_MAX
    );
    $batch_size = min( $limits['default'], $limits['hard_max'] );

    $base_args = [
        'post_type'              => 'organization',
        'post_status'            => 'publish',
        'fields'                 => 'ids',
        'orderby'                => [
            'date' => 'ASC',
            'ID'   => 'ASC',
        ],
        'ignore_sticky_posts'    => true,
        'suppress_filters'       => true,
        'update_post_meta_cache' => false,
        'update_post_term_cache' => false,
    ];

    // ACF stores multiple User IDs as strings specifically for quoted LIKE lookups.
    $result = sfpf_query_founder_organization_relation(
        array_merge(
            $base_args,
            [
                'meta_query' => [
                    [
                        'key'     => 'founder_users',
                        'value'   => '"' . $user_id . '"',
                        'compare' => 'LIKE',
                    ],
                ],
            ]
        ),
        $batch_size,
        $limits['hard_max']
    );

    if ( 0 === $result['total'] ) {
        $result = sfpf_query_founder_organization_relation(
            array_merge(
                $base_args,
                [
                    'meta_query' => [
                        [
                            'key'     => 'founder',
                            'value'   => (string) $user_id,
                            'compare' => '=',
                        ],
                    ],
                ]
            ),
            $batch_size,
            $limits['hard_max']
        );
    }

    $used_inventory_fallback = false;
    if (
        0 === $result['total']
        && (bool) apply_filters( 'sfpf_founder_organizations_allow_inventory_fallback', true, $user_id )
    ) {
        $result = sfpf_query_founder_organization_relation(
            $base_args,
            $batch_size,
            $limits['hard_max']
        );
        $used_inventory_fallback = true;
    }

    $ids = $result['ids'];
    $total = $result['total'];

    if ( [] !== $ids && function_exists( '_prime_post_caches' ) ) {
        \_prime_post_caches( $ids, false, true );
    }

    if ( $total > $limits['hard_max'] ) {
        do_action( 'sfpf_founder_organizations_truncated', $user_id, $total, $limits['hard_max'] );
    }

    if ( $used_inventory_fallback && $total > 0 ) {
        do_action( 'sfpf_founder_organizations_inventory_fallback', $user_id, $total, $limits['hard_max'] );
    }

    return $ids;
}
