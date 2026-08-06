<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile;

use SFPF\PersonProfile\Core\CoreIntegration;
use SFPF\PersonProfile\Runtime\LegacyModuleLoader;
use SFPF\PersonProfile\Shortcodes\ShortcodeRegistrar;

defined( 'ABSPATH' ) || exit;

final class Plugin {
    private static bool $registered = false;
    private static bool $booted = false;

    public static function register(): void {
        if ( self::$registered ) {
            return;
        }

        self::$registered = true;
        self::load_compatibility_surface();
        ShortcodeRegistrar::register();

        if ( did_action( 'plugins_loaded' ) ) {
            self::boot();
            return;
        }

        add_action( 'plugins_loaded', [ self::class, 'boot' ], 20 );
    }

    public static function boot(): void {
        if ( self::$booted ) {
            return;
        }

        CoreIntegration::boot();
        self::$booted = true;

        do_action( 'sfpf_person_profile_booted', CoreIntegration::context() );
    }

    private static function load_compatibility_surface(): void {
        foreach (
            [
                'includes/helper-functions.php',
                'includes/frontend-query-bounds.php',
                'includes/logging.php',
                'includes/snippets-loader.php',
                'includes/elementor-social-icons.php',
                'includes/elementor-display-conditions.php',
            ] as $module
        ) {
            require_once SFPF_PLUGIN_DIR . $module;
        }

        LegacyModuleLoader::load();
    }
}
