<?php
/**
 * Standalone test bootstrap: the WordPress functions the tested classes call,
 * and autoloading for the plugin and its bundled Hexa WP Core. No database.
 */

declare( strict_types=1 );

define( 'ABSPATH', dirname( __DIR__ ) . '/' );

$GLOBALS['sfpf_test_options'] = [];
$GLOBALS['sfpf_test_filters'] = [];

function get_option( string $name, mixed $default = false ): mixed { return array_key_exists( $name, $GLOBALS['sfpf_test_options'] ) ? $GLOBALS['sfpf_test_options'][ $name ] : $default; }
function update_option( string $name, mixed $value, mixed $autoload = null ): bool { $GLOBALS['sfpf_test_options'][ $name ] = $value; return true; }
function apply_filters( string $hook, mixed $value, mixed ...$args ): mixed { return isset( $GLOBALS['sfpf_test_filters'][ $hook ] ) ? $GLOBALS['sfpf_test_filters'][ $hook ]( $value, ...$args ) : $value; }
function add_action( string $hook, mixed $callback, int $priority = 10, int $args = 1 ): void {}
function add_filter( string $hook, mixed $callback, int $priority = 10, int $args = 1 ): void {}
function do_action( string $hook, mixed ...$args ): void {}
function sanitize_key( string $key ): string { return strtolower( (string) preg_replace( '/[^a-z0-9_\-]/i', '', $key ) ); }
function sanitize_title( string $title ): string { return trim( (string) preg_replace( '/[^a-z0-9]+/', '-', strtolower( $title ) ), '-' ); }
function sanitize_text_field( string $text ): string { return trim( strip_tags( $text ) ); }
function sanitize_html_class( string $class ): string { return (string) preg_replace( '/[^A-Za-z0-9_-]/', '', $class ); }
function sanitize_email( string $email ): string { return $email; }
function esc_html( string $text ): string { return htmlspecialchars( $text, ENT_QUOTES ); }
function esc_attr( string $text ): string { return htmlspecialchars( $text, ENT_QUOTES ); }
function esc_url( string $url ): string { return str_replace( '&', '&#038;', $url ); }
function esc_url_raw( string $url, ?array $protocols = null ): string { return preg_match( '#^https?://#i', $url ) ? $url : ''; }
function wp_kses_post( string $html ): string { return $html; }
function wp_strip_all_tags( string $text, bool $breaks = false ): string { return trim( strip_tags( $text ) ); }
function wp_parse_url( string $url, int $component = -1 ): mixed { return parse_url( $url, $component ); }
function wp_json_encode( mixed $data, int $flags = 0 ): string|false { return json_encode( $data, $flags ); }
function untrailingslashit( string $value ): string { return rtrim( $value, '/' ); }
function absint( mixed $value ): int { return abs( (int) $value ); }
function shortcode_atts( array $defaults, array $atts, string $tag = '' ): array { return array_merge( $defaults, array_intersect_key( $atts, $defaults ) ); }
function is_admin(): bool { return false; }

spl_autoload_register(
    static function ( string $class ): void {
        foreach ( [ 'SFPF\\PersonProfile\\' => dirname( __DIR__ ) . '/src/', 'Hexa\\PluginCore\\' => dirname( __DIR__ ) . '/lib/hexa-wordpress-plugin-core/src/' ] as $prefix => $root ) {
            if ( str_starts_with( $class, $prefix ) ) {
                $file = $root . str_replace( '\\', '/', substr( $class, strlen( $prefix ) ) ) . '.php';
                if ( is_readable( $file ) ) {
                    require_once $file;
                }
            }
        }
    }
);

function sfpf_assert( bool $condition, string $message ): void {
    if ( ! $condition ) {
        fwrite( STDERR, 'FAIL: ' . $message . PHP_EOL );
        exit( 1 );
    }
}
