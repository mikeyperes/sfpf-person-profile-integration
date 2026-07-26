<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Admin\Ajax;

defined( 'ABSPATH' ) || exit;

final class ModuleLoader {
    private const ACTION_MODULES = [
        'sfpf_toggle_snippet'                  => [ 'admin/ajax/settings.php' ],
        'sfpf_save_schema_type'                => [ 'admin/ajax/settings.php' ],
        'sfpf_save_biography_schema_type'      => [ 'admin/ajax/settings.php' ],
        'sfpf_save_rankmath_settings'          => [ 'admin/ajax/settings.php' ],
        'sfpf_save_breadcrumb_settings'        => [ 'admin/ajax/settings.php' ],
        'sfpf_detect_schema'                   => [ 'admin/ajax/schema-checklist.php', 'admin/ajax/schema-detection.php' ],
        'sfpf_reprocess_schema'                => [ 'admin/ajax/schema-reprocess.php' ],
        'sfpf_rebuild_all_schema'              => [ 'admin/ajax/schema-reprocess.php' ],
        'sfpf_assign_page'                     => [ 'admin/ajax/site-structure.php' ],
        'sfpf_create_page'                     => [ 'admin/ajax/site-structure.php' ],
        'sfpf_delete_page'                     => [ 'admin/ajax/site-structure.php' ],
        'sfpf_create_navigation_menu'          => [ 'admin/ajax/site-structure.php' ],
        'sfpf_delete_navigation_menu'          => [ 'admin/ajax/site-structure.php' ],
        'sfpf_attach_page_to_menu_item'        => [ 'admin/ajax/site-structure.php' ],
        'sfpf_attach_menu_structure'           => [ 'admin/ajax/site-structure.php' ],
        'sfpf_add_pages_to_menu'               => [ 'admin/ajax/site-structure.php' ],
        'sfpf_save_template'                   => [ 'admin/ajax/templates.php' ],
        'sfpf_apply_template'                  => [ 'admin/ajax/templates.php' ],
        'sfpf_apply_default_template'          => [ 'admin/ajax/templates.php' ],
        'sfpf_clear_log'                       => [ 'admin/ajax/maintenance.php' ],
        'sfpf_save_faq_sets'                   => [ 'admin/ajax/faq.php' ],
        'sfpf_save_elementor_loops'            => [ 'admin/ajax/elementor.php' ],
        'sfpf_import_elementor_templates'      => [ 'admin/ajax/elementor.php' ],
        'sfpf_delete_elementor_template'       => [ 'admin/ajax/elementor.php' ],
        'sfpf_create_profession_page'          => [ 'admin/ajax/professions.php' ],
        'sfpf_delete_profession_page'          => [ 'admin/ajax/professions.php' ],
        'sfpf_run_debug'                       => [ 'admin/ajax/debug.php' ],
        'sfpf_export_debug_report'             => [ 'admin/ajax/debug.php' ],
        'sfpf_process_articles'                => [ 'admin/ajax/articles.php' ],
    ];

    /** @var array<string,bool> */
    private static array $loaded = [];

    public static function load( ?string $action = null ): void {
        $action = null === $action ? self::requestAction() : sanitize_key( $action );
        if ( '' === $action || ! isset( self::ACTION_MODULES[ $action ] ) ) {
            return;
        }

        $modules = [ 'admin/ajax/support.php', ...self::ACTION_MODULES[ $action ] ];
        foreach ( array_unique( $modules ) as $module ) {
            if ( isset( self::$loaded[ $module ] ) ) {
                continue;
            }

            self::$loaded[ $module ] = true;
            require_once SFPF_PLUGIN_DIR . $module;
        }
    }

    private static function requestAction(): string {
        if ( ! isset( $_REQUEST['action'] ) || ! is_scalar( $_REQUEST['action'] ) ) {
            return '';
        }

        return sanitize_key( wp_unslash( (string) $_REQUEST['action'] ) );
    }
}
