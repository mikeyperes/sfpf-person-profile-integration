<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Person;

defined( 'ABSPATH' ) || exit;

/**
 * The published Organization records a person founded, in bounded, ID-only
 * queries. SMC Organization Profile stores founders in the multi-user
 * `founder_users` field; older records use the single `founder` field. A
 * site with neither relation falls back to every published Organization,
 * which a filter can switch off.
 */
final class PersonOrganizations {
    public const BATCH    = 20;
    public const HARD_MAX = 100;

    /** @var array<int,list<int>> */
    private static array $cache = [];

    /** @return list<int> */
    public static function ids( int $user_id ): array {
        if ( $user_id <= 0 || ! post_type_exists( 'organization' ) ) {
            return [];
        }
        if ( isset( self::$cache[ $user_id ] ) ) {
            return self::$cache[ $user_id ];
        }
        $limits = QueryLimits::for_context( 'founder_organizations', self::BATCH, self::HARD_MAX );
        $base = [
            'post_type'              => 'organization',
            'post_status'            => 'publish',
            'fields'                 => 'ids',
            'orderby'                => [ 'date' => 'ASC', 'ID' => 'ASC' ],
            'ignore_sticky_posts'    => true,
            'suppress_filters'       => true,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
        ];
        $relations = [
            // ACF stores several user IDs as quoted strings, matched with a quoted LIKE.
            [ 'key' => 'founder_users', 'value' => '"' . $user_id . '"', 'compare' => 'LIKE' ],
            [ 'key' => 'founder', 'value' => (string) $user_id, 'compare' => '=' ],
        ];
        $result = [ 'ids' => [], 'total' => 0 ];
        foreach ( $relations as $relation ) {
            $result = self::query( $base + [ 'meta_query' => [ $relation ] ], $limits );
            if ( $result['total'] > 0 ) {
                break;
            }
        }
        if ( 0 === $result['total'] && (bool) apply_filters( 'sfpf_founder_organizations_allow_inventory_fallback', true, $user_id ) ) {
            $result = self::query( $base, $limits );
            if ( $result['total'] > 0 ) {
                do_action( 'sfpf_founder_organizations_inventory_fallback', $user_id, $result['total'], $limits['hard_max'] );
            }
        }
        if ( $result['total'] > $limits['hard_max'] ) {
            do_action( 'sfpf_founder_organizations_truncated', $user_id, $result['total'], $limits['hard_max'] );
        }
        if ( [] !== $result['ids'] && function_exists( '_prime_post_caches' ) ) {
            _prime_post_caches( $result['ids'], false, true );
        }
        return self::$cache[ $user_id ] = $result['ids'];
    }

    public static function forget(): void {
        self::$cache = [];
    }

    /**
     * @param array<string,mixed>            $args
     * @param array{default:int,hard_max:int} $limits
     * @return array{ids:list<int>,total:int}
     */
    private static function query( array $args, array $limits ): array {
        $batch = min( $limits['default'], $limits['hard_max'] );
        $first = new \WP_Query( $args + [ 'posts_per_page' => $batch, 'no_found_rows' => false ] );
        $ids   = array_values( array_unique( array_map( 'intval', $first->posts ) ) );
        $total = max( count( $ids ), (int) $first->found_posts );
        $target = min( $total, $limits['hard_max'] );
        while ( count( $ids ) < $target ) {
            $next = new \WP_Query( $args + [ 'posts_per_page' => min( $batch, $target - count( $ids ) ), 'offset' => count( $ids ), 'no_found_rows' => true ] );
            $new  = array_values( array_diff( array_map( 'intval', $next->posts ), $ids ) );
            if ( [] === $new ) {
                break;
            }
            $ids = array_merge( $ids, $new );
        }
        return [ 'ids' => array_slice( $ids, 0, $limits['hard_max'] ), 'total' => $total ];
    }
}
