<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Admin\Ajax;

use Hexa\PluginCore\WpAdminAjax\AjaxActionRegistry;

defined( 'ABSPATH' ) || exit;

final class ModuleLoader {
    private const ACTIONS = [
        'sfpf_toggle_snippet'             => [ [ 'admin/ajax/settings.php' ], 'ajax_toggle_snippet' ],
        'sfpf_save_schema_type'           => [ [ 'admin/ajax/settings.php' ], 'ajax_save_schema_type' ],
        'sfpf_save_biography_schema_type' => [ [ 'admin/ajax/settings.php' ], 'ajax_save_biography_schema_type' ],
        'sfpf_save_rankmath_settings'     => [ [ 'admin/ajax/settings.php' ], 'ajax_save_rankmath_settings' ],
        'sfpf_save_breadcrumb_settings'   => [ [ 'admin/ajax/settings.php' ], 'ajax_save_breadcrumb_settings' ],
        'sfpf_detect_schema'              => [ [ 'admin/ajax/schema-checklist.php', 'admin/ajax/schema-detection.php' ], 'ajax_detect_schema' ],
        'sfpf_reprocess_schema'           => [ [ 'admin/ajax/schema-reprocess.php' ], 'ajax_reprocess_schema' ],
        'sfpf_rebuild_all_schema'         => [ [ 'admin/ajax/schema-reprocess.php' ], 'ajax_rebuild_all_schema' ],
        'sfpf_assign_page'                => [ [ 'admin/ajax/site-structure.php' ], '' ],
        'sfpf_create_page'                => [ [ 'admin/ajax/site-structure.php' ], '' ],
        'sfpf_delete_page'                => [ [ 'admin/ajax/site-structure.php' ], '' ],
        'sfpf_create_navigation_menu'     => [ [ 'admin/ajax/site-structure.php' ], '' ],
        'sfpf_delete_navigation_menu'     => [ [ 'admin/ajax/site-structure.php' ], '' ],
        'sfpf_attach_page_to_menu_item'   => [ [ 'admin/ajax/site-structure.php' ], '' ],
        'sfpf_attach_menu_structure'      => [ [ 'admin/ajax/site-structure.php' ], '' ],
        'sfpf_save_template'              => [ [ 'admin/ajax/templates.php' ], 'ajax_save_template' ],
        'sfpf_apply_template'             => [ [ 'admin/ajax/templates.php' ], 'ajax_apply_template' ],
        'sfpf_apply_default_template'     => [ [ 'admin/ajax/templates.php' ], 'ajax_apply_default_template' ],
        'sfpf_clear_log'                  => [ [ 'admin/ajax/maintenance.php' ], 'ajax_clear_log' ],
        'sfpf_save_faq_sets'              => [ [ 'admin/ajax/faq.php' ], 'ajax_save_faq_sets' ],
        'sfpf_save_elementor_loops'       => [ [ 'admin/ajax/elementor.php' ], 'ajax_save_elementor_loops' ],
        'sfpf_import_elementor_templates' => [ [ 'admin/ajax/elementor.php' ], 'ajax_import_elementor_templates' ],
        'sfpf_delete_elementor_template'  => [ [ 'admin/ajax/elementor.php' ], 'ajax_delete_elementor_template' ],
        'sfpf_create_profession_page'     => [ [ 'admin/ajax/professions.php' ], 'ajax_create_profession_page' ],
        'sfpf_delete_profession_page'     => [ [ 'admin/ajax/professions.php' ], 'ajax_delete_profession_page' ],
        'sfpf_run_debug'                  => [ [ 'admin/ajax/debug.php' ], 'ajax_run_debug' ],
        'sfpf_export_debug_report'        => [ [ 'admin/ajax/debug.php' ], 'ajax_export_debug_report' ],
        'sfpf_process_articles'           => [ [ 'admin/ajax/articles.php' ], 'ajax_process_articles' ],
    ];

    /** @var array<string,bool> */
    private static array $loaded = [];

    public static function load( ?string $action = null ): void {
        $action = null === $action ? self::requestAction() : sanitize_key( $action );
        if ( '' === $action || ! isset( self::ACTIONS[ $action ] ) ) {
            return;
        }

        [ $action_modules, $callback_name ] = self::ACTIONS[ $action ];
        $modules = [ 'admin/ajax/support.php', ...$action_modules ];
        foreach ( array_unique( $modules ) as $module ) {
            if ( isset( self::$loaded[ $module ] ) ) {
                continue;
            }

            self::$loaded[ $module ] = true;
            require_once SFPF_PLUGIN_DIR . $module;
        }

        // SiteStructureAjaxController registers its full related action family.
        if ( '' === $callback_name || ! class_exists( AjaxActionRegistry::class ) ) {
            return;
        }

        $callback = 'sfpf_person_website\\' . $callback_name;
        if ( ! is_callable( $callback ) ) {
            return;
        }

        ( new AjaxActionRegistry(
            [
                'capability'    => 'manage_options',
                'nonce_action'  => 'sfpf_ajax',
                'nonce_field'   => 'nonce',
                'logger'        => static function ( \Throwable $error ): void {
                    if ( function_exists( 'sfpf_person_website\\write_log' ) ) {
                        \sfpf_person_website\write_log( 'AJAX error: ' . $error->getMessage(), 'error' );
                    }
                },
            ]
        ) )->register(
            [
                $action => [ 'callback' => $callback ],
            ]
        );
    }

    private static function requestAction(): string {
        if ( ! isset( $_REQUEST['action'] ) || ! is_scalar( $_REQUEST['action'] ) ) {
            return '';
        }

        return sanitize_key( wp_unslash( (string) $_REQUEST['action'] ) );
    }
}
