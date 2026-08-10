<?php

declare( strict_types=1 );

define( 'ABSPATH', dirname( __DIR__ ) . '/' );
define( 'SFPF_PLUGIN_DIR', dirname( __DIR__ ) . '/' );

function add_action( string $hook, callable|string $callback, int $priority = 10 ): void {
    unset( $hook, $callback, $priority );
}
function get_option( string $name, mixed $default = false ): mixed {
    unset( $name );
    return $default;
}
function post_type_exists( string $key ): bool {
    unset( $key );
    return false;
}
function acf_get_local_field_group( string $key ): array|false {
    unset( $key );
    return false;
}
function admin_url( string $path = '' ): string {
    return 'https://example.test/wp-admin/' . ltrim( $path, '/' );
}
function wp_create_nonce( string $action ): string {
    return 'test-' . $action;
}
function esc_url( mixed $value ): string {
    return htmlspecialchars( (string) $value, ENT_QUOTES, 'UTF-8' );
}
function esc_attr( mixed $value ): string {
    return htmlspecialchars( (string) $value, ENT_QUOTES, 'UTF-8' );
}
function esc_html( mixed $value ): string {
    return htmlspecialchars( (string) $value, ENT_QUOTES, 'UTF-8' );
}
function wp_kses_post( mixed $value ): string {
    return (string) $value;
}
function sanitize_html_class( mixed $value ): string {
    return preg_replace( '/[^A-Za-z0-9_-]/', '', (string) $value ) ?: '';
}
function sanitize_key( mixed $value ): string {
    return preg_replace( '/[^a-z0-9_\-]/', '', strtolower( (string) $value ) ) ?: '';
}
function sanitize_title( mixed $value ): string {
    return trim( preg_replace( '/[^a-z0-9]+/', '-', strtolower( (string) $value ) ) ?: '', '-' );
}
function sanitize_text_field( mixed $value ): string {
    return trim( strip_tags( (string) $value ) );
}

$core_root = dirname( __DIR__ ) . '/lib/hexa-wordpress-plugin-core';
require $core_root . '/bootstrap.php';
hexa_plugin_core_register_package( 'sfpf-quote-test', $core_root );
HexaPluginCorePackageRegistry::resolve();

require dirname( __DIR__ ) . '/src/Autoloader.php';
SFPF\PersonProfile\Autoloader::register( dirname( __DIR__ ) . '/src' );

$registry = SFPF\PersonProfile\ContentTypes\PersonContentTypes::content_types();
$quote = $registry->definition( 'quote' );
if ( ! is_array( $quote ) ) {
    fwrite( STDERR, 'Quote CPT is missing from the Core registry.' . PHP_EOL );
    exit( 1 );
}

$groups = (array) ( $quote['field_groups'] ?? [] );
$group = $groups[0] ?? [];
if (
    'quote' !== ( $quote['post_type']['key'] ?? '' )
    || 'Quote' !== ( $quote['post_type']['singular'] ?? '' )
    || 'Quotes' !== ( $quote['post_type']['plural'] ?? '' )
    || 'group_sfpf_quote' !== ( $group['group_key'] ?? '' )
    || true === ( $quote['enabled_default'] ?? true )
    || true === ( $group['enabled_default'] ?? true )
) {
    fwrite( STDERR, 'Quote CPT or ACF toggle defaults are invalid.' . PHP_EOL );
    exit( 1 );
}

$html = ( new Hexa\PluginCore\ContentTypes\ContentTypeRenderer() )->render( $registry );
$checks = [
    '<span class="hpc-section-title">Quotes</span>',
    'id="hpc-content-type-enabled-quote"',
    '<span class="hpc-detail-card-title">Quote Fields</span>',
    'id="hpc-content-type-acf-enabled-quote-quote-fields"',
    '<strong>Quote</strong>',
    '>quote</span>',
    '>textarea</span>',
    '<strong>Logos</strong>',
    '>logos</span>',
    '>gallery</span>',
    '&quot;mime_types&quot;: &quot;svg,png,jpg,jpeg,webp&quot;',
];
foreach ( $checks as $expected ) {
    if ( ! str_contains( $html, $expected ) ) {
        fwrite( STDERR, 'Quote Core UI is missing: ' . $expected . PHP_EOL );
        exit( 1 );
    }
}

echo 'PASS: Quote CPT and ACF controls render through the generic Hexa Core interface.' . PHP_EOL;
