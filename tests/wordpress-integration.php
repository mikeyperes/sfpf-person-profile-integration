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
$assert( '3.0.1' === SFPF_PLUGIN_VERSION, 'Unexpected plugin version.' );
$assert( version_compare( (string) ( $report['selected']['version'] ?? '0' ), '3.0.1', '>=' ), 'Selected Core version is older than the bundled release.' );
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
    'sfpf_person_website\\render_public_profile_debug_page',
    'sfpf_person_website\\migrate_articles_textarea_to_repeater',
    'sfpf_person_website\\sfpf_fix_repeater_load_value',
    'sfpf_person_website\\sfpf_faq_shortcode',
    'sfpf_person_website\\sanitize_kgid_on_save',
    'sfpf_person_website\\sfpf_loop_shortcode',
    'sfpf_person_website\\organization_shortcode',
    'sfpf_person_website\\sfpf_resolve_organization_id',
    'sfpf_person_website\\sfpf_render_organization_profile',
    'sfpf_person_website\\book_shortcode',
    'sfpf_person_website\\founder_shortcode',
    'sfpf_person_website\\founder_display_articles',
    'sfpf_person_website\\founder_display_additional_urls',
    'sfpf_person_website\\sfpf_normalize_link_repeater',
    'sfpf_person_website\\sfpf_is_wikidata_url',
    'sfpf_person_website\\sfpf_filter_public_urls',
    'sfpf_person_website\\sfpf_knowledge_panel_url',
    'sfpf_person_website\\founder_display_location_born',
    'sfpf_person_website\\sfpf_founder_organization_ids',
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
    'book',
    'founder',
];
foreach ( $requiredShortcodes as $shortcode ) {
    $assert( shortcode_exists( $shortcode ), 'Shortcode is missing: ' . $shortcode );
}

if ( ! defined( 'SMC_VERSION' ) ) {
    $assert( ! shortcode_exists( 'organization' ), 'SFPF registered the SMC-owned Organization shortcode alias.' );
}

$profileFieldGroup = function_exists( 'sfpf_person_website\\user_schema_acf_field_group' )
    ? sfpf_person_website\user_schema_acf_field_group()
    : [];
$profileFields = is_array( $profileFieldGroup['fields'] ?? null ) ? $profileFieldGroup['fields'] : [];
$additionalUrlFields = array_values(
    array_filter(
        $profileFields,
        static fn ( $field ): bool => is_array( $field ) && 'field_sfpf_additional_urls' === ( $field['key'] ?? '' )
    )
);
$additionalUrlField = $additionalUrlFields[0] ?? [];
$additionalUrlSubfieldNames = array_values(
    array_filter(
        array_map(
            static fn ( $field ): string => is_array( $field ) ? (string) ( $field['name'] ?? '' ) : '',
            is_array( $additionalUrlField['sub_fields'] ?? null ) ? $additionalUrlField['sub_fields'] : []
        )
    )
);
$assert( 'repeater' === ( $additionalUrlField['type'] ?? '' ), 'Additional URLs ACF repeater is missing.' );
$assert( [ 'title', 'source', 'url' ] === $additionalUrlSubfieldNames, 'Additional URLs does not mirror the Recent Articles subfields.' );

$profileFieldsByKey = [];
foreach ( $profileFields as $profileField ) {
    if ( is_array( $profileField ) && isset( $profileField['key'] ) ) {
        $profileFieldsByKey[ (string) $profileField['key'] ] = $profileField;
    }
}
$educationField = $profileFieldsByKey['field_sfpf_education_repeater'] ?? [];
$educationWidths = array_map(
    static fn ( $field ): string => is_array( $field ) ? (string) ( $field['wrapper']['width'] ?? '' ) : '',
    is_array( $educationField['sub_fields'] ?? null ) ? $educationField['sub_fields'] : []
);
$assert( 'row' === ( $educationField['layout'] ?? '' ), 'Education History does not use the one-field-per-row layout.' );
$assert( [ '100', '100', '100', '100', '100' ] === $educationWidths, 'Education History subfields do not each span a full row.' );

$wikimediaField = $profileFieldsByKey['field_sfpf_wikimedia_commons_urls'] ?? [];
$wikimediaSubfields = is_array( $wikimediaField['sub_fields'] ?? null ) ? $wikimediaField['sub_fields'] : [];
$profileFieldKeys = array_values(
    array_map(
        static fn ( $field ): string => is_array( $field ) ? (string) ( $field['key'] ?? '' ) : '',
        $profileFields
    )
);
$knowledgeGraphIndex = array_search( 'field_sfpf_knowledge_graph_id', $profileFieldKeys, true );
$wikimediaIndex = array_search( 'field_sfpf_wikimedia_commons_urls', $profileFieldKeys, true );
$assert( 'repeater' === ( $wikimediaField['type'] ?? '' ), 'Wikimedia Commons photo URL repeater is missing.' );
$assert( 'sfpf-entity-person-or-org' === ( $wikimediaField['wrapper']['class'] ?? '' ), 'Wikimedia Commons URLs are not shared by Person and Organization.' );
$assert( 'url' === ( $wikimediaSubfields[0]['type'] ?? '' ) && 'url' === ( $wikimediaSubfields[0]['name'] ?? '' ), 'Wikimedia Commons repeater does not contain a URL field.' );
$assert( false !== $knowledgeGraphIndex && $wikimediaIndex === $knowledgeGraphIndex + 1, 'Wikimedia Commons URLs are not directly below the Knowledge Graph ID.' );

$managedPages = function_exists( 'sfpf_person_website\\sfpf_site_structure_pages_definition' )
    ? sfpf_person_website\sfpf_site_structure_pages_definition()
    : [];
$assert( 'additional-urls' === ( $managedPages['additional_urls']['slug'] ?? '' ), 'Managed Additional URLs page is missing.' );

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

echo 'PASS: WordPress loaded SFPF 3.0.1 with bounded founder-organization and loop queries.' . PHP_EOL;
