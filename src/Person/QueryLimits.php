<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Person;

defined( 'ABSPATH' ) || exit;

/**
 * Upper limits for public queries, so a shortcode or schema build never loads
 * an unbounded number of posts. Filters let a site raise or lower them.
 */
final class QueryLimits {
    /** @return array{default:int,hard_max:int} */
    public static function for_context( string $context, int $default, int $hard_max ): array {
        $context  = sanitize_key( $context );
        $default  = (int) apply_filters( 'sfpf_' . $context . '_default_limit', (int) apply_filters( 'sfpf_frontend_query_default_limit', $default, $context ) );
        $hard_max = (int) apply_filters( 'sfpf_' . $context . '_hard_max', (int) apply_filters( 'sfpf_frontend_query_hard_max', $hard_max, $context ) );
        $hard_max = max( 1, $hard_max );
        return [ 'default' => min( $hard_max, max( 1, $default ) ), 'hard_max' => $hard_max ];
    }

    /** A requested count, or the default when none was asked for, never above the maximum. */
    public static function clamp( int $requested, int $default, int $hard_max ): int {
        $hard_max = max( 1, $hard_max );
        return min( $requested > 0 ? $requested : min( $hard_max, max( 1, $default ) ), $hard_max );
    }
}
