<?php

declare( strict_types=1 );

$root     = dirname( __DIR__ );
$failures = [];

$assert = static function ( bool $condition, string $message ) use ( &$failures ): void {
    if ( ! $condition ) {
        $failures[] = $message;
    }
};

$read = static function ( string $path ) use ( &$failures ): string {
    // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Local test fixture.
    $contents = is_readable( $path ) ? file_get_contents( $path ) : false;
    if ( false === $contents ) {
        $failures[] = 'Unable to read ' . $path;
        return '';
    }

    return $contents;
};

$requiredFiles = [
    'initialization.php',
    'src/Plugin.php',
    'src/Core/CoreIntegration.php',
    'src/Admin/Dashboard.php',
    'includes/elementor-social-icons.php',
    'snippets/register-acf-user-schema.php',
    'schema/schema-builder.php',
    'lib/hexa-wordpress-plugin-core/VERSION',
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
    'src/Runtime/LegacyModuleLoader.php',
    'src/Admin/Ajax/ModuleLoader.php',
];

foreach ( $requiredFiles as $relativePath ) {
    $assert( is_file( $root . '/' . $relativePath ), 'Missing required file: ' . $relativePath );
}

$initialization = $read( $root . '/initialization.php' );
$coreIntegration = $read( $root . '/src/Core/CoreIntegration.php' );
$dashboard = $read( $root . '/src/Admin/Dashboard.php' );
$socialIcons = $read( $root . '/includes/elementor-social-icons.php' );
$profileDebug = $read( $root . '/includes/runtime/profile-debug.php' );
$userFields = $read( $root . '/snippets/register-acf-user-schema.php' );
$schemaBuilder = $read( $root . '/schema/schema-builder.php' );
$ajaxHandlers = $read( $root . '/admin/ajax-handlers.php' );
$ajaxSupport = $read( $root . '/admin/ajax/support.php' );
$runtimeLoader = $read( $root . '/src/Runtime/LegacyModuleLoader.php' );
$ajaxModuleLoader = $read( $root . '/src/Admin/Ajax/ModuleLoader.php' );

preg_match( '/Version:\s*([0-9.]+)/', $initialization, $headerMatch );
preg_match( "/define\\(\\s*['\"]SFPF_PLUGIN_VERSION['\"]\\s*,\\s*['\"]([^'\"]+)['\"]/", $initialization, $constantMatch );

$headerVersion = $headerMatch[1] ?? '';
$constantVersion = $constantMatch[1] ?? '';
$configVersion = false !== strpos( $initialization, 'public static $version = "' . $headerVersion . '"' ) ? $headerVersion : '';

$assert( '' !== $headerVersion, 'Plugin header version was not found.' );
$assert( $headerVersion === $constantVersion, 'Plugin header and constant versions differ.' );
$assert( $headerVersion === $configVersion, 'Plugin header and Config versions differ.' );
$assert( version_compare( $headerVersion, '1.7.1', '>=' ), 'Plugin version is older than the audited 1.7.1 baseline.' );
$assert( '0.19.40' === trim( $read( $root . '/lib/hexa-wordpress-plugin-core/VERSION' ) ), 'Bundled Hexa Plugin Core version is not 0.19.40.' );

$sourceFiles = [];
$scanDirectories = [ 'admin', 'includes', 'schema', 'snippets', 'src' ];
foreach ( $scanDirectories as $directory ) {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator( $root . '/' . $directory, FilesystemIterator::SKIP_DOTS )
    );

    foreach ( $iterator as $file ) {
        if ( $file->isFile() && 'php' === strtolower( $file->getExtension() ) ) {
            $sourceFiles[] = $file->getPathname();
        }
    }
}
$sourceFiles[] = $root . '/initialization.php';
sort( $sourceFiles );

foreach ( $sourceFiles as $sourcePath ) {
    $source = $read( $sourcePath );
    $relativePath = ltrim( substr( $sourcePath, strlen( $root ) ), '/' );

    $assert( 0 === preg_match( '/\\beval\\s*\\(/i', $source ), 'Arbitrary PHP evaluation remains in ' . $relativePath );
    $assert( false === strpos( $source, 'wp_ajax_nopriv_sfpf_' ), 'Unauthenticated SFPF AJAX action remains in ' . $relativePath );
    $assert( false === strpos( $source, 'sfpf_execute_php' ), 'Legacy PHP execution action remains in ' . $relativePath );
}

foreach ( [ 'src/Plugin.php', 'src/Core/CoreIntegration.php', 'src/Admin/Dashboard.php' ] as $relativePath ) {
    $source = $read( $root . '/' . $relativePath );
    $assert( false !== strpos( $source, 'declare( strict_types=1 );' ), 'Strict types missing from ' . $relativePath );
    $assert( false !== strpos( $source, 'namespace SFPF\\PersonProfile' ), 'SFPF namespace missing from ' . $relativePath );
}

$assert( false !== strpos( $coreIntegration, 'PluginContext' ), 'Core integration does not use PluginContext.' );
$assert( false !== strpos( $coreIntegration, 'CoreBootstrap' ), 'Core integration does not use CoreBootstrap.' );
$assert( false !== strpos( $coreIntegration, 'UpdaterAjaxController' ), 'Core updater controller is not registered.' );
$assert( false !== strpos( $dashboard, 'HostTabsRenderer' ), 'Dashboard does not use the shared tab renderer.' );
$assert( false !== strpos( $dashboard, 'AjaxActionRegistry' ), 'Dashboard lazy-tab endpoint is not guarded by the shared AJAX registry.' );

foreach ( [ 'Overview', 'Profile', 'Site', 'Integrations', 'System' ] as $area ) {
    $assert( false !== strpos( $dashboard, "'label' => '" . $area . "'" ), 'Dashboard area missing: ' . $area );
}

$assert( substr_count( $read( $root . '/admin/settings-dashboard.php' ), PHP_EOL ) < 80, 'Legacy settings dashboard shim is no longer thin.' );
$assert( substr_count( $read( $root . '/admin/dashboard-plugin-info.php' ), PHP_EOL ) < 80, 'Legacy updater panel shim is no longer thin.' );
$assert( substr_count( $initialization, PHP_EOL ) < 100, 'Plugin bootstrap is no longer thin.' );
$assert( substr_count( $ajaxHandlers, PHP_EOL ) < 30, 'Legacy AJAX loader is no longer thin.' );
$assert( false !== strpos( $runtimeLoader, 'final class LegacyModuleLoader' ), 'Runtime module loader is missing.' );
$assert( false !== strpos( $ajaxModuleLoader, 'final class ModuleLoader' ), 'AJAX module loader is missing.' );

$boundedModules = [
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
foreach ( $boundedModules as $relativePath ) {
    $assert( substr_count( $read( $root . '/' . $relativePath ), PHP_EOL ) < 700, 'Module exceeds the 700-line ownership boundary: ' . $relativePath );
}


$assert(
    false !== strpos( $profileDebug, "!is_user_logged_in() || !current_user_can('manage_options')" )
    && false !== strpos( $profileDebug, 'status_header(404)' ),
    'Profile debug route is not restricted to authenticated administrators.'
);
$assert(
    false !== strpos( $ajaxSupport, 'wp_verify_nonce' )
    && false !== strpos( $ajaxSupport, "current_user_can('manage_options')" ),
    'Legacy AJAX guard is missing nonce or capability validation.'
);
$assert(
    false !== strpos( $socialIcons, "get_option(SFPF_HIDE_EMPTY_ELEMENTOR_SOCIAL_ICONS_OPTION, '1')" )
    && false !== strpos( $socialIcons, "add_filter('elementor/widget/render_content'" ),
    'Empty social-icon filtering is not default-enabled and server-side.'
);
$assert(
    false !== strpos( $userFields, "'name'              => 'gallery'" )
    && false !== strpos( $userFields, "'type'              => 'gallery'" ),
    'Founder ACF gallery field is missing.'
);
$assert(
    false !== strpos( $schemaBuilder, "sfpf_normalize_gallery_images(_sf('gallery'" )
    && false !== strpos( $schemaBuilder, "\$p['image']" ),
    'Founder gallery is not mapped into the Person schema image property.'
);
$assert(
    false !== strpos( $schemaBuilder, "add_action('save_post', __NAMESPACE__ . '\\\\handle_schema_on_save'" ),
    'Schema regeneration is not attached to save_post.'
);

if ( ! defined( 'ABSPATH' ) ) {
    define( 'ABSPATH', $root . '/' );
}

$GLOBALS['sfpf_test_options'] = [];

if ( ! function_exists( 'get_option' ) ) {
    function get_option( $name, $fallback = false ) {
        return array_key_exists( $name, $GLOBALS['sfpf_test_options'] )
            ? $GLOBALS['sfpf_test_options'][ $name ]
            : $fallback;
    }
}

if ( ! function_exists( 'add_filter' ) ) {
    function add_filter( $hook, $callback, $priority = 10, $acceptedArgs = 1 ) {
        unset( $hook, $callback, $priority, $acceptedArgs );
        return true;
    }
}

require_once $root . '/includes/elementor-social-icons.php';

$widget = new class() {
    public function get_name(): string {
        return 'social-icons';
    }
};

$html = '<div class="elementor-social-icons-wrapper">'
    . '<span class="elementor-grid-item"><a class="elementor-social-icon">Missing</a></span>'
    . '<span class="elementor-grid-item"><a class="elementor-social-icon" href="#">Hash</a></span>'
    . '<span class="elementor-grid-item"><a class="elementor-social-icon" href="%5Bfounder%20id%3Durl_linkedin%5D">Shortcode</a></span>'
    . '<span class="elementor-grid-item"><a class="elementor-social-icon" href="mailto:?subject=test">Mail</a></span>'
    . '<span class="elementor-grid-item"><a class="elementor-social-icon" href="tel:+">Phone</a></span>'
    . '<span class="elementor-grid-item"><a class="elementor-social-icon" href="https://example.com/profile">Valid</a></span>'
    . '</div>';

$GLOBALS['sfpf_test_options']['sfpf_hide_empty_elementor_social_icons'] = 1;
$filtered = sfpf_person_website\filter_empty_elementor_social_icons( $html, $widget );

$assert( 1 === substr_count( $filtered, 'elementor-grid-item' ), 'Social filter did not remove every invalid icon wrapper.' );
$assert( false !== strpos( $filtered, 'https://example.com/profile' ), 'Social filter removed the valid icon.' );
$assert( false === strpos( $filtered, '>Missing<' ), 'Social filter retained a missing href.' );
$assert( false === strpos( $filtered, '>Shortcode<' ), 'Social filter retained an unresolved shortcode.' );

$GLOBALS['sfpf_test_options']['sfpf_hide_empty_elementor_social_icons'] = 0;
$assert(
    sfpf_person_website\filter_empty_elementor_social_icons( $html, $widget ) === $html,
    'Social filter toggle does not preserve original HTML when disabled.'
);

if ( [] !== $failures ) {
    foreach ( $failures as $failure ) {
        // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fwrite -- CLI test failure output.
        fwrite( STDERR, 'FAIL: ' . $failure . PHP_EOL );
    }
    exit( 1 );
}

echo 'PASS: SFPF standalone architecture, security, gallery, and social-icon regressions (' . count( $sourceFiles ) . ' PHP files scanned).' . PHP_EOL;
