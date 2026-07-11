<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Runtime;

defined( 'ABSPATH' ) || exit;

final class LegacyModuleLoader {
    private const MODULES = [
        'includes/runtime/lifecycle.php',
        'includes/runtime/profile-debug.php',
        'includes/runtime/plugin-admin.php',
        'includes/runtime/acf-user-profile.php',
        'includes/shortcodes/faq.php',
        'includes/runtime/schema-seo.php',
        'includes/shortcodes/loop.php',
        'includes/shortcodes/organization.php',
        'includes/shortcodes/book.php',
        'includes/shortcodes/founder.php',
        'includes/shortcodes/founder-articles.php',
        'includes/shortcodes/founder-sections.php',
        'includes/runtime/profile-admin-script.php',
        'includes/frontend/author-archive.php',
    ];

    private static bool $loaded = false;

    public static function load(): void {
        if ( self::$loaded ) {
            return;
        }

        self::$loaded = true;
        foreach ( self::MODULES as $module ) {
            require_once SFPF_PLUGIN_DIR . $module;
        }
    }
}
