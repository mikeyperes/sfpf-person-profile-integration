<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Diagnostics;

use Hexa\PluginCore\CoreContracts\ModuleInterface;

defined( 'ABSPATH' ) || exit;

/**
 * /sfpf-profile-debug/ for administrators (404 for everyone else): the
 * profile report as a page, or as JSON with ?format=json. Never indexed.
 */
final class DebugEndpoint implements ModuleInterface {
    public const ROUTE     = 'sfpf-profile-debug';
    public const QUERY_VAR = 'sfpf_profile_debug';

    public function register(): void {
        add_action( 'init', [ self::class, 'add_rewrite_rule' ], 1 );
        add_filter( 'query_vars', static fn( array $vars ): array => array_merge( $vars, [ self::QUERY_VAR ] ) );
        add_action( 'template_redirect', [ $this, 'render' ], 0 );
    }

    public static function add_rewrite_rule(): void {
        add_rewrite_rule( '^' . self::ROUTE . '/?$', 'index.php?' . self::QUERY_VAR . '=1', 'top' );
    }

    public function render(): void {
        if ( ! get_query_var( self::QUERY_VAR ) ) {
            return;
        }
        nocache_headers();
        header( 'X-Robots-Tag: noindex, nofollow', true );
        if ( ! current_user_can( 'manage_options' ) ) {
            status_header( 404 );
            exit;
        }
        $report = ProfileReport::build();
        status_header( 200 );
        if ( 'json' === sanitize_key( (string) wp_unslash( $_GET['format'] ?? '' ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only report for administrators.
            wp_send_json( $report );
        }
        header( 'Content-Type: text/html; charset=utf-8' );
        echo '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex,nofollow"><title>SFPF profile report</title>';
        echo '<style>body{font:14px/1.5 system-ui,sans-serif;margin:0;background:#f6f7f9;color:#111}main{max-width:1100px;margin:0 auto;padding:24px}section{background:#fff;border:1px solid #dde1e6;border-radius:8px;padding:16px;margin:0 0 16px}h1{font-size:22px}h2{font-size:15px;text-transform:uppercase;letter-spacing:.05em}table{border-collapse:collapse;width:100%}td,th{border-bottom:1px solid #eef0f3;padding:6px;text-align:left;vertical-align:top}pre{background:#111827;color:#e5e7eb;padding:12px;border-radius:6px;overflow:auto;max-height:480px;white-space:pre-wrap;word-break:break-word}.no{color:#b42318}.yes{color:#067647}</style></head><body><main>';
        echo '<h1>SFPF profile report</h1><p>Plugin ' . esc_html( (string) $report['plugin'] ) . ', ' . esc_html( (string) $report['generated_at'] ) . ' UTC. <a href="?format=json">JSON</a></p>';
        echo '<section><h2>Person</h2>' . ( null === $report['person'] ? '<p class="no">No person is set up. Choose one in HWS Base Tools.</p>' : '<p>' . esc_html( (string) $report['person']['name'] ) . ' (user ' . (int) $report['person']['user_id'] . ') <a href="' . esc_url( (string) $report['person']['edit_url'] ) . '">Edit profile</a></p>' ) . '</section>';
        echo '<section><h2>Values</h2><table>';
        foreach ( $report['values'] as $field => $value ) {
            echo '<tr><th>' . esc_html( $field ) . '</th><td class="' . ( $value['count'] > 0 ? 'yes' : 'no' ) . '">' . (int) $value['count'] . '</td><td>' . esc_html( (string) $value['text'] ) . '</td></tr>';
        }
        echo '</table></section><section><h2>Sections</h2><table>';
        foreach ( $report['sections'] as $id => $has ) {
            echo '<tr><th>' . esc_html( $id ) . '</th><td class="' . ( $has ? 'yes">Has content' : 'no">Empty' ) . '</td></tr>';
        }
        echo '</table></section><section><h2>Pages</h2><table>';
        foreach ( $report['pages'] as $key => $page ) {
            echo '<tr><th>' . esc_html( $key ) . '</th><td><a href="' . esc_url( $page['url'] ) . '">' . esc_html( $page['url'] ) . '</a></td><td>' . esc_html( $page['status'] ) . '</td></tr>';
        }
        echo '</table></section>';
        foreach ( $report['schema'] as $label => $page ) {
            echo '<section><h2>Schema: ' . esc_html( $label ) . '</h2><p><a href="' . esc_url( $page['url'] ) . '">' . esc_html( $page['url'] ) . '</a></p><pre>' . esc_html( (string) wp_json_encode( $page['document'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) ) . '</pre></section>';
        }
        echo '</main></body></html>';
        exit;
    }
}
