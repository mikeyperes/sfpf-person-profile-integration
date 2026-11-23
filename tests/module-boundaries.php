<?php
/** Checks shortcode hook ownership, lazy tab definitions and the settings asset boundary. */

declare( strict_types=1 );

use Hexa\PluginCore\WpAdminTabs\TabRegistry;
use SFPF\PersonProfile\Admin\DashboardAssets;
use SFPF\PersonProfile\Admin\Tabs\DashboardTabs;
use SFPF\PersonProfile\Shortcodes\ShortcodeCatalog;
use SFPF\PersonProfile\Shortcodes\ShortcodeModule;

require __DIR__ . '/bootstrap.php';

function add_shortcode( string $tag, callable $callback ): void {
    $GLOBALS['sfpf_test_shortcodes'][ $tag ] = $callback;
}

function remove_shortcode( string $tag ): void {
    unset( $GLOBALS['sfpf_test_shortcodes'][ $tag ] );
}

$modules = ShortcodeCatalog::modules();
sfpf_assert( count( ShortcodeCatalog::tags() ) === count( $modules ), 'Each public and legacy shortcode has exactly one module.' );
foreach ( $modules as $module ) {
    sfpf_assert( $module instanceof ShortcodeModule, 'Every shortcode adapter implements the same Core module boundary.' );
    $module->add_shortcode();
}
sfpf_assert( ShortcodeCatalog::tags() === array_keys( $GLOBALS['sfpf_test_shortcodes'] ), 'Each adapter registers the existing tag and its callable.' );

$registry = DashboardTabs::registry();
sfpf_assert( $registry instanceof TabRegistry, 'The settings shell uses the Core tab registry.' );
sfpf_assert( 16 === count( $registry->all() ), 'The settings shell lists all existing plugin tabs.' );
sfpf_assert( ! class_exists( SFPF\PersonProfile\Admin\Tabs\UpdateCenterTab::class, false ), 'Creating the tab list leaves the Update Center renderer unloaded.' );
sfpf_assert( is_callable( $registry->get( 'update-center' )?->renderer ), 'The Update Center has a lazy renderer.' );
sfpf_assert( [ 'System' => [ 'update-center', 'hexa-core' ] ] === array_column( DashboardTabs::groups( [ 'update-center' => [], 'hexa-core' => [] ] ), 'tabs', 'label' ), 'The Core and plugin updater tabs share the System group.' );

foreach ( [
    '<div>Read only content</div>' => [],
    '<code class="sfpf-shortcode">[person]</code>' => [ 'dashboard.js' ],
    '<form class="sfpf-ajax-form"><button data-sfpf-press-check="list">Press</button>' => [ 'dashboard.js', 'press-releases.js' ],
    '<button data-sfpf-scan>Scan</button>' => [ 'schema.js' ],
    '<button data-sfpf-profession="create">Create</button>' => [ 'profession-pages.js' ],
    '<button data-sfpf-add-set>Add set</button>' => [ 'faq.js' ],
] as $markup => $files ) {
    sfpf_assert( $files === DashboardAssets::for_tab( $markup ), 'Only assets for rendered controls are selected.' );
}

echo "PASS: shortcode modules, Core tab registration and dashboard asset scopes.\n";
