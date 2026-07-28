<?php

declare( strict_types=1 );

$root = dirname( __DIR__ );

if ( function_exists( 'set_current_screen' ) ) {
    set_current_screen( 'dashboard' );
}

if ( ! defined( 'SFPF_PLUGIN_VERSION' ) ) {
    require $root . '/initialization.php';
}

if ( function_exists( 'sfpf_person_website\init_plugin' ) ) {
    sfpf_person_website\init_plugin();
}

require_once $root . '/admin/settings-dashboard.php';

SFPF\PersonProfile\Core\CoreIntegration::boot();
SFPF\PersonProfile\Admin\Dashboard::register();

$failures = [];
$assert   = static function ( bool $condition, string $message ) use ( &$failures ): void {
    if ( ! $condition ) {
        $failures[] = $message;
    }
};

$context = SFPF\PersonProfile\Core\CoreIntegration::context();
$report  = HexaPluginCorePackageRegistry::report();

$assert( $context instanceof Hexa\PluginCore\CoreRuntime\PluginContext, 'PluginContext was not created.' );
$assert( '2.0.2' === SFPF_PLUGIN_VERSION, 'Unexpected plugin version.' );
$assert( version_compare( (string) ( $report['selected']['version'] ?? '0' ), '1.0.0', '>=' ), 'Selected Core version is older than 1.0.0.' );
$assert( ! empty( $report['healthy'] ), 'Core package registry is not healthy.' );
$assert( false !== has_action( 'wp_ajax_sfpf_load_dashboard_tab' ), 'Lazy dashboard AJAX action is missing.' );
$assert( false !== has_action( 'wp_ajax_sfpf_core_updater_force_update_check' ), 'Core updater AJAX action is missing.' );
$assert( false !== has_action( 'save_post', 'sfpf_person_website\handle_schema_on_save' ), 'Schema save hook is missing.' );

require_once $root . '/src/Admin/Ajax/ModuleLoader.php';

$assert( ! function_exists( 'sfpf_person_website\verify_ajax_nonce' ), 'Legacy AJAX modules loaded during an ordinary admin request.' );
SFPF\PersonProfile\Admin\Ajax\ModuleLoader::load( 'sfpf_load_dashboard_tab' );
$assert( ! function_exists( 'sfpf_person_website\verify_ajax_nonce' ), 'Dashboard tab AJAX loaded unrelated legacy modules.' );

$legacyAjaxActions = [
    'sfpf_toggle_snippet',
    'sfpf_save_schema_type',
    'sfpf_save_biography_schema_type',
    'sfpf_save_rankmath_settings',
    'sfpf_save_breadcrumb_settings',
    'sfpf_detect_schema',
    'sfpf_reprocess_schema',
    'sfpf_rebuild_all_schema',
    'sfpf_assign_page',
    'sfpf_create_page',
    'sfpf_delete_page',
    'sfpf_create_navigation_menu',
    'sfpf_delete_navigation_menu',
    'sfpf_attach_page_to_menu_item',
    'sfpf_attach_menu_structure',
    'sfpf_save_template',
    'sfpf_apply_template',
    'sfpf_apply_default_template',
    'sfpf_clear_log',
    'sfpf_save_faq_sets',
    'sfpf_save_elementor_loops',
    'sfpf_import_elementor_templates',
    'sfpf_create_profession_page',
    'sfpf_delete_profession_page',
    'sfpf_delete_elementor_template',
    'sfpf_run_debug',
    'sfpf_export_debug_report',
    'sfpf_process_articles',
];
foreach ( $legacyAjaxActions as $legacyAjaxAction ) {
    SFPF\PersonProfile\Admin\Ajax\ModuleLoader::load( $legacyAjaxAction );
}

$requiredFunctions = [
    'sfpf_person_website\\load_cpt_snippets',
    'sfpf_person_website\\render_public_profile_debug_page',
    'sfpf_person_website\\migrate_articles_textarea_to_repeater',
    'sfpf_person_website\\sfpf_fix_repeater_load_value',
    'sfpf_person_website\\sfpf_faq_shortcode',
    'sfpf_person_website\\sanitize_kgid_on_save',
    'sfpf_person_website\\sfpf_loop_shortcode',
    'sfpf_person_website\\organization_shortcode',
    'sfpf_person_website\\book_shortcode',
    'sfpf_person_website\\founder_shortcode',
    'sfpf_person_website\\founder_display_articles',
    'sfpf_person_website\\founder_display_location_born',
    'sfpf_person_website\\sfpf_render_author_archive_profile',
    'sfpf_person_website\\sfpf_author_archive_has_elementor_template',
    'sfpf_person_website\\verify_ajax_nonce',
    'sfpf_person_website\\ajax_save_schema_type',
    'sfpf_person_website\\ajax_detect_schema',
    'sfpf_person_website\\sfpf_run_full_site_checklist',
    'sfpf_person_website\\ajax_reprocess_schema',
    'sfpf_person_website\\register_site_structure_ajax',
    'sfpf_person_website\\ajax_save_template',
    'sfpf_person_website\\ajax_clear_log',
    'sfpf_person_website\\ajax_save_faq_sets',
    'sfpf_person_website\\ajax_save_elementor_loops',
    'sfpf_person_website\\ajax_create_profession_page',
    'sfpf_person_website\\ajax_run_debug',
    'sfpf_person_website\\ajax_process_articles',
];
foreach ( $requiredFunctions as $callback ) {
    $assert( function_exists( $callback ), 'Module callback is missing: ' . $callback );
}

$requiredShortcodes = [
    'sfpf_faq',
    'sfpf_faq_schema',
    'sfpf_person_faq',
    'sfpf_elementor_faq',
    'sfpf_loop',
    'organization',
    'book',
    'founder',
];
foreach ( $requiredShortcodes as $shortcode ) {
    $assert( shortcode_exists( $shortcode ), 'Shortcode is missing: ' . $shortcode );
}

foreach ( $legacyAjaxActions as $ajaxAction ) {
    $assert( false !== has_action( 'wp_ajax_' . $ajaxAction ), 'AJAX action is missing: ' . $ajaxAction );
}

$assert(
    false !== has_action( 'activate_' . SFPF_PLUGIN_BASENAME, 'sfpf_person_website\\activate_plugin' ),
    'Plugin activation callback is not bound to the main plugin file.'
);
$assert(
    false !== has_action( 'deactivate_' . SFPF_PLUGIN_BASENAME, 'sfpf_person_website\\deactivate_plugin' ),
    'Plugin deactivation callback is not bound to the main plugin file.'
);

if ( [] !== $failures ) {
    foreach ( $failures as $failure ) {
        // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fwrite -- CLI test failure output.
        fwrite( STDERR, 'FAIL: ' . $failure . PHP_EOL );
    }
    exit( 1 );
}

echo 'PASS: WordPress loaded SFPF 2.0.2 with grouped Core tabs, action-scoped AJAX modules, and guarded runtime hooks.' . PHP_EOL;
