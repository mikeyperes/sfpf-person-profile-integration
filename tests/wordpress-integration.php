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
$assert( '1.7.0' === SFPF_PLUGIN_VERSION, 'Unexpected plugin version.' );
$assert( '0.19.40' === (string) ( $report['selected']['version'] ?? '' ), 'Unexpected selected Core version.' );
$assert( ! empty( $report['healthy'] ), 'Core package registry is not healthy.' );
$assert( false !== has_action( 'wp_ajax_sfpf_load_dashboard_tab' ), 'Lazy dashboard AJAX action is missing.' );
$assert( false !== has_action( 'wp_ajax_sfpf_core_updater_force_update_check' ), 'Core updater AJAX action is missing.' );
$assert( false !== has_action( 'save_post', 'sfpf_person_website\handle_schema_on_save' ), 'Schema save hook is missing.' );

if ( [] !== $failures ) {
    foreach ( $failures as $failure ) {
        // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fwrite -- CLI test failure output.
        fwrite( STDERR, 'FAIL: ' . $failure . PHP_EOL );
    }
    exit( 1 );
}

echo 'PASS: WordPress loaded SFPF 1.7.0 with healthy Core and guarded runtime hooks.' . PHP_EOL;
