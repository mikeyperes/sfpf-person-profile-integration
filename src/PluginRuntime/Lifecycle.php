<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\PluginRuntime;

use SFPF\PersonProfile\Diagnostics\DebugEndpoint;

defined( 'ABSPATH' ) || exit;

/** Activation and deactivation: refresh the site's addresses so the plugin's pages resolve. */
final class Lifecycle {
    public static function register( string $main_file ): void {
        register_activation_hook( $main_file, [ self::class, 'activate' ] );
        register_deactivation_hook( $main_file, [ self::class, 'deactivate' ] );
    }

    public static function activate(): void {
        DebugEndpoint::add_rewrite_rule();
        flush_rewrite_rules( false );
    }

    public static function deactivate(): void {
        flush_rewrite_rules( false );
    }
}
