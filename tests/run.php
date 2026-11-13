<?php
/** Runs every test file in this folder, each in its own PHP process: php tests/run.php */

declare( strict_types=1 );

$tests = array_values( array_filter( glob( __DIR__ . '/*.php' ) ?: [], static fn( string $path ): bool => ! in_array( basename( $path ), [ 'run.php', 'bootstrap.php' ], true ) ) );
sort( $tests );
foreach ( $tests as $test ) {
    passthru( escapeshellarg( PHP_BINARY ) . ' ' . escapeshellarg( $test ), $status );
    if ( 0 !== $status ) {
        fwrite( STDERR, 'FAIL: ' . basename( $test ) . PHP_EOL );
        exit( $status );
    }
}
echo 'SFPF suite passed: ' . count( $tests ) . " test files.\n";
