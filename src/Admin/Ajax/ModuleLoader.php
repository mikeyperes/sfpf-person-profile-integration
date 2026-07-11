<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Admin\Ajax;

defined( 'ABSPATH' ) || exit;

final class ModuleLoader {
    private const MODULES = [
        'admin/ajax/support.php',
        'admin/ajax/settings.php',
        'admin/ajax/schema-detection.php',
        'admin/ajax/schema-checklist.php',
        'admin/ajax/schema-reprocess.php',
        'admin/ajax/site-structure.php',
        'admin/ajax/templates.php',
        'admin/ajax/maintenance.php',
        'admin/ajax/faq.php',
        'admin/ajax/elementor.php',
        'admin/ajax/professions.php',
        'admin/ajax/debug.php',
        'admin/ajax/articles.php',
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
