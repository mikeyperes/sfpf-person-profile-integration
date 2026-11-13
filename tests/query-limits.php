<?php
/** Public queries stay bounded, and filters can move the bounds. */

declare( strict_types=1 );

require __DIR__ . '/bootstrap.php';

use SFPF\PersonProfile\Person\QueryLimits;

sfpf_assert( [ 'default' => 12, 'hard_max' => 48 ] === QueryLimits::for_context( 'loop', 12, 48 ), 'Defaults apply without filters.' );
$GLOBALS['sfpf_test_filters']['sfpf_loop_hard_max'] = static fn(): int => 5;
sfpf_assert( [ 'default' => 5, 'hard_max' => 5 ] === QueryLimits::for_context( 'loop', 12, 48 ), 'The default never passes a lowered maximum.' );
sfpf_assert( 48 === QueryLimits::clamp( 500, 12, 48 ) && 12 === QueryLimits::clamp( 0, 12, 48 ), 'Requests are clamped and empty ones take the default.' );

echo "PASS: query limits.\n";
