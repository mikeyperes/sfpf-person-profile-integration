<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile;

use SFPF\PersonProfile\Core\CoreIntegration;

defined( 'ABSPATH' ) || exit;

final class Plugin {
    private static bool $registered = false;

    public static function register(): void {
        if ( self::$registered ) {
            return;
        }

        require_once __DIR__ . '/Core/CoreIntegration.php';

        if ( did_action( 'plugins_loaded' ) ) {
            CoreIntegration::boot();
        } else {
            add_action( 'plugins_loaded', [ CoreIntegration::class, 'boot' ], 20 );
        }

        self::$registered = true;
    }
}
