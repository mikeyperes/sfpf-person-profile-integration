<?php

declare( strict_types=1 );

$root = dirname( __DIR__ );

define( 'ABSPATH', $root . '/' );
define( 'SFPF_PLUGIN_DIR', $root . '/' );

$GLOBALS['sfpf_activity_test_options'] = [
    'sfpf_activity_log' => [
        [
            'timestamp' => '2026-08-06 01:00:00',
            'message'   => 'Legacy row',
            'type'      => 'warning',
            'user'      => 7,
        ],
    ],
];

function get_option( string $name, mixed $default = false ): mixed {
    return $GLOBALS['sfpf_activity_test_options'][ $name ] ?? $default;
}

function update_option( string $name, mixed $value, bool $autoload = false ): bool {
    unset( $autoload );
    $GLOBALS['sfpf_activity_test_options'][ $name ] = $value;
    return true;
}

function delete_option( string $name ): bool {
    unset( $GLOBALS['sfpf_activity_test_options'][ $name ] );
    return true;
}

function current_time( string $type ): string {
    unset( $type );
    return '2026-08-06 02:00:00';
}

function get_current_user_id(): int {
    return 11;
}

function sanitize_key( string $value ): string {
    return preg_replace( '/[^a-z0-9_\-]/', '', strtolower( $value ) ) ?: '';
}

function absint( mixed $value ): int {
    return abs( (int) $value );
}

function wp_json_encode( mixed $value, int $flags = 0 ): string|false {
    return json_encode( $value, $flags );
}

$core = $root . '/lib/hexa-wordpress-plugin-core';
require_once $core . '/bootstrap.php';
HexaPluginCorePackageRegistry::register_candidate( 'sfpf-activity-test', $core );
HexaPluginCorePackageRegistry::resolve();

require_once $root . '/src/Autoloader.php';
SFPF\PersonProfile\Autoloader::register( $root . '/src' );
require_once $root . '/includes/logging.php';

$legacy = sfpf_person_website\get_activity_log();
if ( 'Legacy row' !== ( $legacy[0]['message'] ?? '' ) || 'warning' !== ( $legacy[0]['type'] ?? '' ) ) {
    fwrite( STDERR, "Legacy activity row was not preserved.\n" );
    exit( 1 );
}

sfpf_person_website\write_log( [ 'phase' => 'complete' ], 'success' );
$entries = sfpf_person_website\get_activity_log();
$stored = $GLOBALS['sfpf_activity_test_options']['sfpf_activity_log'] ?? [];
if (
    '{"phase":"complete"}' !== ( $entries[0]['message'] ?? '' )
    || 'success' !== ( $entries[0]['type'] ?? '' )
    || 'success' !== ( $stored[1]['level'] ?? '' )
) {
    fwrite( STDERR, "Core-backed activity entry was not persisted compatibly.\n" );
    exit( 1 );
}

sfpf_person_website\clear_activity_log();
if ( array_key_exists( 'sfpf_activity_log', $GLOBALS['sfpf_activity_test_options'] ) ) {
    fwrite( STDERR, "Core-backed activity log was not cleared.\n" );
    exit( 1 );
}

echo "PASS: Legacy SFPF activity rows migrate to Core storage without changing caller output.\n";
