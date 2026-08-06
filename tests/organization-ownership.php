<?php

declare( strict_types=1 );

define( 'ABSPATH', dirname( __DIR__ ) . '/' );
define( 'SFPF_PLUGIN_FILE', dirname( __DIR__ ) . '/initialization.php' );
define( 'SFPF_PLUGIN_DIR', dirname( __DIR__ ) . '/' );

$GLOBALS['sfpf_ownership_options'] = [
    'sfpf_shared_content_type_ownership_migration' => '1.8.0',
    'hws_content_type_settings' => [
        'organization' => [
            'enabled' => 1,
            'singular' => 'Company',
            'plural' => 'Companies',
            'rewrite_slug' => 'companies',
            'field_groups' => [],
        ],
    ],
    'smp_enable_cpt_organization' => 1,
    'sfpf_enable_organization_acf' => 1,
];

function get_option( string $name, mixed $default = false ): mixed {
    return array_key_exists( $name, $GLOBALS['sfpf_ownership_options'] )
        ? $GLOBALS['sfpf_ownership_options'][ $name ]
        : $default;
}

function update_option( string $name, mixed $value, bool $autoload = true ): bool {
    unset( $autoload );
    $GLOBALS['sfpf_ownership_options'][ $name ] = $value;
    return true;
}

function sanitize_text_field( string $value ): string {
    return trim( strip_tags( $value ) );
}

function sanitize_title( string $value ): string {
    return trim( strtolower( (string) preg_replace( '/[^a-zA-Z0-9\-]+/', '-', $value ) ), '-' );
}

function add_action( string $hook, callable|string $callback, int $priority = 10 ): void {
    unset( $hook, $callback, $priority );
}

function register_activation_hook( string $file, callable|string $callback ): void {
    unset( $file, $callback );
}

function register_deactivation_hook( string $file, callable|string $callback ): void {
    unset( $file, $callback );
}

require dirname( __DIR__ ) . '/includes/runtime/lifecycle.php';

sfpf_person_website\migrate_shared_content_type_ownership();

$organization = $GLOBALS['sfpf_ownership_options']['sfpf_content_type_settings']['organization'] ?? [];
$expected = [
    'enabled' => 1,
    'singular' => 'Company',
    'plural' => 'Companies',
    'rewrite_slug' => 'companies',
    'field_groups' => [ 'organization-details' => 1 ],
];

if ( $expected !== $organization ) {
    fwrite( STDERR, 'FAIL: Organization settings were not migrated intact.' . PHP_EOL );
    exit( 1 );
}

if ( '3.1.0' !== ( $GLOBALS['sfpf_ownership_options']['sfpf_shared_content_type_ownership_migration'] ?? '' ) ) {
    fwrite( STDERR, 'FAIL: Organization ownership migration marker was not advanced.' . PHP_EOL );
    exit( 1 );
}

$GLOBALS['sfpf_ownership_options']['hws_content_type_settings']['organization']['rewrite_slug'] = 'changed-later';
sfpf_person_website\migrate_shared_content_type_ownership();
if ( 'companies' !== $GLOBALS['sfpf_ownership_options']['sfpf_content_type_settings']['organization']['rewrite_slug'] ) {
    fwrite( STDERR, 'FAIL: Organization ownership migration is not idempotent.' . PHP_EOL );
    exit( 1 );
}

echo 'PASS: Organization settings migrate from HWS to SFPF exactly once.' . PHP_EOL;
