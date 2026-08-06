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
    'src/Autoloader.php',
    'src/Plugin.php',
    'src/Core/CoreIntegration.php',
    'src/Dependencies/PluginRequirements.php',
    'src/Shortcodes/ShortcodeRegistrar.php',
    'src/Support/ActivityLogAdapter.php',
    'src/Admin/Dashboard.php',
    'src/ContentTypes/PersonContentTypes.php',
    'src/Schema/SchemaProvider.php',
    'admin/dashboard-content-types.php',
    'assets/admin/dashboard.css',
    'includes/elementor-social-icons.php',
    'snippets/register-acf-user-schema.php',
    'snippets/register-acf-profile-content-types.php',
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
    'includes/frontend-query-bounds.php',
    'includes/runtime/profile-admin-script.php',
    'includes/frontend/author-archive.php',
    'tests/author-archive-elementor.php',
    'tests/additional-urls.php',
    'tests/wikimedia-commons.php',
    'tests/frontend-query-bounds.php',
    'tests/profile-content-types.php',
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
$plugin = $read( $root . '/src/Plugin.php' );
$requirements = $read( $root . '/src/Dependencies/PluginRequirements.php' );
$shortcodeRegistrar = $read( $root . '/src/Shortcodes/ShortcodeRegistrar.php' );
$activityLog = $read( $root . '/src/Support/ActivityLogAdapter.php' );
$logging = $read( $root . '/includes/logging.php' );
$dashboard = $read( $root . '/src/Admin/Dashboard.php' );
$dashboardCss = $read( $root . '/assets/admin/dashboard.css' );
$socialIcons = $read( $root . '/includes/elementor-social-icons.php' );
$profileDebug = $read( $root . '/includes/runtime/profile-debug.php' );
$userFields = $read( $root . '/snippets/register-acf-user-schema.php' );
$schemaBuilder = $read( $root . '/schema/schema-builder.php' );
$lifecycle = $read( $root . '/includes/runtime/lifecycle.php' );
$organizationShortcode = $read( $root . '/includes/shortcodes/organization.php' );
$schemaInjector = $read( $root . '/schema/schema-injector.php' );
$ajaxHandlers = $read( $root . '/admin/ajax-handlers.php' );
$ajaxSupport = $read( $root . '/admin/ajax/support.php' );
$runtimeLoader = $read( $root . '/src/Runtime/LegacyModuleLoader.php' );
$ajaxModuleLoader = $read( $root . '/src/Admin/Ajax/ModuleLoader.php' );
$contentTypes = $read( $root . '/src/ContentTypes/PersonContentTypes.php' );
$schemaProvider = $read( $root . '/src/Schema/SchemaProvider.php' );
$helperFunctions = $read( $root . '/includes/helper-functions.php' );
$faqShortcodes = $read( $root . '/includes/shortcodes/faq.php' );
$founderShortcodes = $read( $root . '/includes/shortcodes/founder.php' );
$founderArticles = $read( $root . '/includes/shortcodes/founder-articles.php' );
$acfUserProfile = $read( $root . '/includes/runtime/acf-user-profile.php' );
$authorArchive = $read( $root . '/includes/frontend/author-archive.php' );
$elementorConditions = $read( $root . '/includes/elementor-display-conditions.php' );

preg_match( '/Version:\s*([0-9.]+)/', $initialization, $headerMatch );
preg_match( "/define\\(\\s*['\"]SFPF_PLUGIN_VERSION['\"]\\s*,\\s*['\"]([^'\"]+)['\"]/", $initialization, $constantMatch );

$headerVersion = $headerMatch[1] ?? '';
$constantVersion = $constantMatch[1] ?? '';
$configVersion = false !== strpos( $initialization, 'public static $version = "' . $headerVersion . '"' ) ? $headerVersion : '';
$assert( '' !== $headerVersion, 'Plugin header version was not found.' );
$assert( $headerVersion === $constantVersion, 'Plugin header and constant versions differ.' );
$assert( $headerVersion === $configVersion, 'Plugin header and Config versions differ.' );
$assert( '3.0.3' === $headerVersion, 'Plugin version is not 3.0.3.' );
$assert( '3.0.3' === trim( $read( $root . '/lib/hexa-wordpress-plugin-core/VERSION' ) ), 'Bundled Hexa Plugin Core is not synchronized to canonical 3.0.3.' );

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

foreach ( [ 'initialization.php', 'src/Plugin.php', 'src/Core/CoreIntegration.php', 'src/Admin/Dashboard.php' ] as $relativePath ) {
    $source = $read( $root . '/' . $relativePath );
    $assert( false !== strpos( $source, 'declare( strict_types=1 );' ), 'Strict types missing from ' . $relativePath );
    $assert( false !== strpos( $source, 'namespace SFPF\\PersonProfile' ), 'SFPF namespace missing from ' . $relativePath );
}

$assert( false !== strpos( $coreIntegration, 'PluginContext' ), 'Core integration does not use PluginContext.' );
$assert( false !== strpos( $coreIntegration, 'CoreBootstrap' ), 'Core integration does not use CoreBootstrap.' );
$assert( false !== strpos( $coreIntegration, 'UpdaterAjaxController' ), 'Core updater controller is not registered.' );
$assert( false !== strpos( $dashboard, 'HostTabsRenderer' ), 'Dashboard does not use the shared tab renderer.' );
$assert( false !== strpos( $dashboard, 'TabRegistry' ) && false !== strpos( $dashboard, 'TabDefinition' ), 'Dashboard tab definitions do not use the Core registry.' );
$assert( false !== strpos( $dashboard, 'AjaxActionRegistry' ), 'Dashboard lazy-tab endpoint is not guarded by the shared AJAX registry.' );
$assert(
    1 === preg_match( "/'layout'\\s*=>\\s*'sidebar'/", $dashboard )
    && false !== strpos( $dashboard, "'groups'" )
    && false !== strpos( $dashboard, 'sidebar_identity' ),
    'Dashboard does not use the complete grouped Core sidebar structure.'
);
$assert(
    false === strpos( $dashboard, 'sfpf-primary-nav' )
    && false === strpos( $dashboardCss, '.sfpf-primary-nav' ),
    'Legacy primary tab navigation remains in the dashboard.'
);
$assert(
    false !== strpos( $dashboard, "wp_add_inline_style( 'common'" )
    && false === strpos( $dashboard, 'wp_enqueue_style(' ),
    'Dashboard CSS is not delivered inline through the existing admin stylesheet.'
);

foreach ( [ 'Overview', 'Profile', 'Site', 'System' ] as $area ) {
    $assert( false !== strpos( $dashboard, "'label' => '" . $area . "'" ), 'Dashboard area missing: ' . $area );
}

$assert(
    false !== strpos( $dashboardCss, '.sfpf-dashboard-header > div > .notice' )
    && false !== strpos( $dashboardCss, 'flex-wrap: wrap;' ),
    'Dashboard header does not isolate third-party admin notices.'
);
$assert(
    false !== strpos( $dashboardCss, '.sfpf-dashboard-shell .hpc-system-check-row' )
    && false !== strpos( $dashboardCss, 'overflow-wrap: anywhere;' ),
    'Dashboard system-check rows are not protected against mobile overflow.'
);

$assert( substr_count( $read( $root . '/admin/settings-dashboard.php' ), PHP_EOL ) < 80, 'Legacy settings dashboard shim is no longer thin.' );
$assert( substr_count( $read( $root . '/admin/dashboard-plugin-info.php' ), PHP_EOL ) < 80, 'Legacy updater panel shim is no longer thin.' );
$assert( substr_count( $initialization, PHP_EOL ) < 100, 'Plugin bootstrap is no longer thin.' );
$assert( substr_count( $ajaxHandlers, PHP_EOL ) < 30, 'Legacy AJAX loader is no longer thin.' );
$assert( false !== strpos( $runtimeLoader, 'final class LegacyModuleLoader' ), 'Runtime module loader is missing.' );
$assert( false !== strpos( $ajaxModuleLoader, 'final class ModuleLoader' ), 'AJAX module loader is missing.' );
$assert(
    false !== strpos( $lifecycle, "'sfpf_load_dashboard_tab' === \$ajax_action" )
    && false !== strpos( $lifecycle, 'if ($doing_ajax)' )
    && false === strpos( $lifecycle, "require_once SFPF_PLUGIN_DIR . 'admin/dashboard-plugin-info.php'" ),
    'Admin lifecycle still eagerly loads dashboard, AJAX, or updater presentation code.'
);
$assert(
    false !== strpos( $ajaxModuleLoader, 'private const ACTIONS' )
    && false !== strpos( $ajaxModuleLoader, "'sfpf_detect_schema'" )
    && false !== strpos( $ajaxModuleLoader, "'admin/ajax/schema-checklist.php'" )
    && false !== strpos( $ajaxModuleLoader, 'AjaxActionRegistry' )
    && false !== strpos( $ajaxModuleLoader, 'requestAction()' )
    && false === strpos( $ajaxModuleLoader, 'private const MODULES' ),
    'Legacy AJAX modules are not selected narrowly or registered through the Core AJAX registry.'
);
$assert(
    false === strpos( $ajaxModuleLoader, 'sfpf_add_pages_to_menu' )
    && false === strpos( $read( $root . '/admin/ajax/site-structure.php' ), 'sfpf_add_pages_to_menu' )
    && false === strpos( $read( $root . '/admin/dashboard-pages.php' ), 'sfpf_add_pages_to_menu' ),
    'Unsupported legacy site-structure actions remain advertised.'
);
$assert(
    false !== strpos( $dashboard, "if ( 'overview' === \$tab )" )
    && false !== strpos( $dashboard, "admin/dashboard-plugin-info.php" ),
    'Updater presentation is not lazy-loaded only for the overview panel.'
);

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
    false !== strpos( $ajaxSupport, 'AjaxGuard::require_nonce_or_error' )
    && false !== strpos( $ajaxSupport, 'AjaxGuard::require_capability_or_error' )
    && false === strpos( $ajaxSupport, 'wp_verify_nonce(' ),
    'Legacy AJAX guard does not delegate nonce and capability validation to Core.'
);
$assert(
    false !== strpos( $socialIcons, "get_option(SFPF_HIDE_EMPTY_ELEMENTOR_SOCIAL_ICONS_OPTION, '1')" )
    && false !== strpos( $socialIcons, "add_filter('elementor/widget/render_content'" ),
    'Empty social-icon filtering is not default-enabled and server-side.'
);
$assert(
    false !== strpos( $authorArchive, 'sfpf_author_archive_has_elementor_template()' )
    && false !== strpos( $authorArchive, "get_documents_for_location( 'archive' )" )
    && false !== strpos( $authorArchive, '!is_author() || sfpf_author_archive_has_elementor_template()' ),
    'Author archive fallback does not defer to a matching Elementor Theme Builder archive.'
);
$assert(
    false !== strpos( $elementorConditions, "case 'articles':" )
    && false !== strpos( $elementorConditions, 'sfpf_founder_has_public_articles()' )
    && false !== strpos( $elementorConditions, "case 'additional_urls':" )
    && false !== strpos( $elementorConditions, 'sfpf_founder_has_public_additional_urls()' )
    && false !== strpos( $elementorConditions, "case 'faq':" )
    && false !== strpos( $elementorConditions, 'sfpf_founder_has_public_faq()' ),
    'Elementor conditions do not cover founder articles, additional URLs, and person FAQs.'
);
$assert(
    false !== strpos( $userFields, "'key'               => 'field_sfpf_additional_urls'" )
    && false !== strpos( $userFields, "'name'              => 'additional_urls'" )
    && false !== strpos( $userFields, "'collapsed'         => 'field_sfpf_additional_url_title'" )
    && false !== strpos( $userFields, "'key'               => 'field_sfpf_additional_url_source'" )
    && false !== strpos( $userFields, "'key'               => 'field_sfpf_additional_url_url'" ),
    'Additional URLs does not mirror the article title/source/URL ACF repeater.'
);
$assert(
    false !== strpos( $founderArticles, 'function founder_display_additional_urls(' )
    && false !== strpos( $founderArticles, "sfpf_display_link_repeater(\$user_id, 'additional_urls'" )
    && false !== strpos( $founderArticles, 'sfpf_filter_public_link_repeater($articles)' )
    && false !== strpos( $founderShortcodes, "case 'display_additional_urls':" )
    && false !== strpos( $founderShortcodes, "case 'additional_urls':" )
    && false !== strpos( $founderShortcodes, "case 'knowledge_graph_url':" ),
    'Additional URLs public filtering or the dynamic Knowledge Graph URL shortcode is missing.'
);
$assert(
    false !== strpos( $acfUserProfile, "'field_sfpf_additional_urls'     => 'additional_urls'" )
    && false !== strpos( $schemaBuilder, "['articles', 'additional_urls']" )
    && false !== strpos( $schemaBuilder, "sfpf_collect_wikidata_urls(_sf('urls_wikidata', \$uk))" )
    && false !== strpos( $authorArchive, '<h2>Additional URLs</h2>' )
    && false !== strpos( $authorArchive, 'sfpf_filter_public_link_repeater(' )
    && false !== strpos( $helperFunctions, "'additional_urls' => [" )
    && false !== strpos( $helperFunctions, "'additional_urls' => '[founder action=\"display_additional_urls\"]'" ),
    'Additional URLs is not connected to schema-only Wikidata handling and public output.'
);
$educationOffset = strpos( $userFields, "'key'               => 'field_sfpf_education_repeater'" );
$educationSource = false === $educationOffset ? '' : substr( $userFields, $educationOffset, 3500 );
$assert(
    false !== strpos( $educationSource, "'layout'            => 'row'" )
    && 5 === substr_count( $educationSource, "'wrapper'           => ['width' => '100']" ),
    'Education History does not stack all five inputs one per row.'
);
$knowledgeGraphOffset = strpos( $userFields, "'key'               => 'field_sfpf_knowledge_graph_id'" );
$wikimediaOffset = strpos( $userFields, "'key'               => 'field_sfpf_wikimedia_commons_urls'" );
$assert(
    false !== $knowledgeGraphOffset
    && false !== $wikimediaOffset
    && $wikimediaOffset > $knowledgeGraphOffset
    && false !== strpos( $userFields, "'label'             => 'Wikimedia Commons URLs (Photos)'" )
    && false !== strpos( $userFields, "'wrapper'           => ['class' => 'sfpf-entity-person-or-org', 'width' => '100']" )
    && false !== strpos( $userFields, "'key'         => 'field_sfpf_wikimedia_commons_url'" ),
    'Shared Wikimedia Commons photo URL repeater is missing below the Knowledge Graph ID.'
);
$assert(
    false !== strpos( $acfUserProfile, "'field_sfpf_wikimedia_commons_urls' => 'wikimedia_commons_urls'" )
    && false !== strpos( $schemaBuilder, "_sf('wikimedia_commons_urls', \$uk, [])" )
    && false !== strpos( $founderShortcodes, "case 'wikimedia_commons_urls':" ),
    'Wikimedia Commons URLs are not connected to repeater hydration, Person schema, and shortcode output.'
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
$assert(
    ! is_file( $root . '/snippets/register-cpt-organization.php' )
    && ! is_file( $root . '/snippets/register-cpt-testimonial.php' ),
    'Person plugin still ships Organization or Testimonial CPT registration.'
);
$assert(
    false === strpos( $lifecycle, 'register-cpt-organization.php' )
    && false === strpos( $lifecycle, 'register-cpt-testimonial.php' )
    && false === strpos( $lifecycle, 'register-cpt-book.php' )
    && false !== strpos( $contentTypes, "'key' => 'book'" )
    && false !== strpos( $coreIntegration, 'PersonContentTypes::content_types( $context )' ),
    'Book registration does not run exclusively through the Core content-type registry.'
);
$assert(
    false !== strpos( $contentTypes, "'key' => 'press-release'" )
    && false !== strpos( $contentTypes, "'key' => 'interview'" )
    && false !== strpos( $contentTypes, "'key' => 'contributing-profile'" )
    && false !== strpos( $contentTypes, "'legacy_option' => 'sfpf_enable_press_release_acf'" )
    && false !== strpos( $contentTypes, "'legacy_option' => 'sfpf_enable_interview_acf'" )
    && false !== strpos( $contentTypes, "'legacy_option' => 'sfpf_enable_contributing_profile_acf'" ),
    'Profile content types and their ACF toggles are missing from the Core content-type registry.'
);
$assert(
    false !== strpos( $lifecycle, "'sfpf_enable_organization_cpt' => 'smp_enable_cpt_organization'" )
    && false !== strpos( $lifecycle, "'sfpf_enable_testimonial_cpt'  => 'enable_cpt_testimonial'" ),
    'Legacy Person CPT options are not migrated to HWS Base Tools.'
);
$assert(
    false === strpos( $initialization, "'organization_cpt'" )
    && false === strpos( $initialization, "'testimonial_cpt'" ),
    'Person Config still advertises shared CPT ownership.'
);
$assert(
    false !== strpos( $contentTypes, "'id' => 'legacy-organization-profile'" )
    && false !== strpos( $contentTypes, "'available_when' => static fn(): bool" )
    && false !== strpos( $contentTypes, "class_exists( '\\\\SMC\\\\OrganizationProfile\\\\Acf\\\\OrganizationFields' )" )
    && false !== strpos( $organizationShortcode, 'SfpfOrganizationAdapter' )
    && false !== strpos( $organizationShortcode, 'OrganizationShortcode' ),
    'Person still owns Organization fields or its compatibility callbacks do not delegate to SMC.'
);
$assert(
    false !== strpos( $organizationShortcode, 'function sfpf_resolve_organization_id' )
    && false !== strpos( $organizationShortcode, 'SfpfOrganizationAdapter::resolve_id' )
    && false !== strpos( $organizationShortcode, "'display_profile'" )
    && false === strpos( $organizationShortcode, 'add_shortcode(' ),
    'Historical Organization callbacks are not thin, non-registering SMC adapters.'
);
$assert(
    false !== strpos( $helperFunctions, 'SfpfOrganizationAdapter::primary_post()' )
    && false === strpos( $helperFunctions, "get_option('sfpf_primary_organization'" )
    && false === strpos( $helperFunctions, '// Fallback to first organization' ),
    'Primary organization compatibility still selects its own option or inventory fallback.'
);
$assert(
    false !== strpos( $schemaBuilder, 'SMC\\\\OrganizationProfile\\\\Schema\\\\OrganizationSchema' )
    && false === strpos( $schemaProvider, "is_singular( 'organization' )" )
    && false === strpos( $read( $root . '/schema/schema-manager.php' ), "case 'organization':" ),
    'Person still generates or injects Organization schema instead of leaving ownership with SMC.'
);
$assert(
    false === strpos( $schemaBuilder, "elseif (\$th = get_the_post_thumbnail_url(\$post_id, 'full')) {\n        \$s['logo']" ),
    'Organization schema incorrectly treats the featured image as the organization logo.'
);
$assert(
    false !== strpos( $coreIntegration, 'CoreSchemaInjector' )
    && false !== strpos( $coreIntegration, "[ SchemaProvider::class, 'current' ]" )
    && false !== strpos( $schemaInjector, 'SchemaDocumentRenderer' )
    && false !== strpos( $schemaBuilder, 'SchemaDocumentRenderer' ),
    'Person schema injection, rendering, and stored JSON do not use Hexa WP Core.'
);
$assert(
    false !== strpos( $contentTypes, 'AcfFieldGroupRegistry' )
    && false !== strpos( $read( $root . '/admin/dashboard-content-types.php' ), 'AcfFieldGroupRenderer' )
    && false !== strpos( $read( $root . '/admin/dashboard-content-types.php' ), 'ContentTypeRenderer' ),
    'CPT and standalone ACF registration/UI are not both delegated to Hexa WP Core.'
);
$assert(
    false !== strpos( $helperFunctions, 'CanonicalEntityResolver::resolve()' )
    && false !== strpos( $helperFunctions, "get_hws_primary_entity(['person'])" )
    && false !== strpos( $helperFunctions, "['attached_user_id']" ),
    'Founder resolution does not consume the optional canonical HWS entity and its bound author.'
);
$assert(
    false !== strpos( $faqShortcodes, 'FaqSourceResolver' )
    && false !== strpos( $faqShortcodes, 'FaqSetManager' ),
    'Person FAQ source, renderer, and schema paths do not use Hexa WP Core.'
);
$assert(
    false === strpos( $founderShortcodes, 'function register_founder_shortcode()' )
    && false !== strpos( $shortcodeRegistrar, "add_action( 'init', [ self::class, 'register_shortcodes' ], 100 )" )
    && false !== strpos( $shortcodeRegistrar, "'founder'" ),
    'Founder registration is not centralized in the late Core-backed shortcode registrar.'
);

// Architecture regressions: generic mechanics stay in Core and Organization stays in SMC.
$assert(
    false !== strpos( $plugin, 'Autoloader' ) || false !== strpos( $initialization, 'Autoloader::register' ),
    'The namespaced SFPF composition root is not loaded through the plugin autoloader.'
);
$assert(
    false !== strpos( $requirements, 'PluginRecommendationRegistry' )
    && false !== strpos( $requirements, 'PluginCheckService' )
    && false !== strpos( $requirements, "'smc-organization-profile-integration'" )
    && false !== strpos( $requirements, "'checks'      => [ 'installed' => true, 'active' => true ]" ),
    'SFPF dependency discovery does not use Core or recommend its canonical Organization owner.'
);
$assert(
    false !== strpos( $shortcodeRegistrar, 'ShortcodeRegistry' )
    && false !== strpos( $shortcodeRegistrar, 'ShortcodeDefinition' ),
    'SFPF shortcode registration and documentation do not use the Core registry.'
);
$assert(
    false !== strpos( $activityLog, 'ActivityLogger' )
    && false !== strpos( $activityLog, 'ActivityLogConfig::STORAGE_PERMANENT' )
    && false !== strpos( $logging, 'ActivityLogAdapter::add' )
    && false !== strpos( $logging, 'ActivityLogAdapter::legacy_entries' ),
    'SFPF activity logging does not delegate persistence to Core.'
);
$assert(
    is_file( $root . '/snippets/register-acf-organization.php' )
    && false !== strpos( $contentTypes, "'id' => 'legacy-organization-profile'" )
    && false !== strpos( $contentTypes, "'location' => 'Organization posts when SMC is unavailable'" )
    && ! is_file( $root . '/assets/frontend/organization-profile.css' )
    && false === strpos( $helperFunctions, "'sfpf_enable_organization_acf'" ),
    'Legacy SFPF Organization compatibility fields are not guarded while SMC is unavailable.'
);
$assert(
    false === strpos( $all_source = implode( "\n", array_map( $read, $sourceFiles ) ), "add_shortcode('organization'" )
    && false === strpos( $all_source, 'add_shortcode( \'organization\'' ),
    'SFPF still registers the Organization shortcode alias.'
);
$assert(
    false !== strpos( $helperFunctions, 'ValueNormalizer::url_values' )
    && false !== strpos( $helperFunctions, 'FieldReader::acf_value' )
    && false !== strpos( $helperFunctions, 'MediaNormalizer::attachment_image_record' )
    && false !== strpos( $helperFunctions, 'MediaNormalizer::gallery_records' )
    && false === strpos( $helperFunctions, 'wp_get_attachment_image_src($attachment_id' )
    && false === strpos( $helperFunctions, 'preg_match_all(\'#https?://[^\\s,<>]+#\'' ),
    'Legacy URL, ACF, or media callbacks still own generic normalization mechanics.'
);
$assert(
    false !== strpos( $schemaBuilder, 'FieldReader::acf_value' )
    && false !== strpos( $schemaBuilder, 'ValueNormalizer::url_values' )
    && false !== strpos( $schemaBuilder, 'ValueNormalizer::row_values' )
    && false !== strpos( $schemaBuilder, 'ValueNormalizer::single_or_array' ),
    'Schema compatibility helpers do not delegate generic ACF, URL, row, and cardinality normalization to Core.'
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

$canonicalTest = escapeshellarg( PHP_BINARY ) . ' ' . escapeshellarg( __DIR__ . '/canonical-entity.php' );
passthru( $canonicalTest, $canonicalStatus );
$assert( 0 === $canonicalStatus, 'Canonical HWS entity regression test failed.' );

$additionalUrlsTest = escapeshellarg( PHP_BINARY ) . ' ' . escapeshellarg( __DIR__ . '/additional-urls.php' );
passthru( $additionalUrlsTest, $additionalUrlsStatus );
$assert( 0 === $additionalUrlsStatus, 'Schema-only Wikidata visibility regression test failed.' );

$wikimediaTest = escapeshellarg( PHP_BINARY ) . ' ' . escapeshellarg( __DIR__ . '/wikimedia-commons.php' );
passthru( $wikimediaTest, $wikimediaStatus );
$assert( 0 === $wikimediaStatus, 'Wikimedia Commons Person-schema regression test failed.' );

$queryBoundsTest = escapeshellarg( PHP_BINARY ) . ' ' . escapeshellarg( __DIR__ . '/frontend-query-bounds.php' );
passthru( $queryBoundsTest, $queryBoundsStatus );
$assert( 0 === $queryBoundsStatus, 'Frontend query bounds regression test failed.' );

$activityLogTest = escapeshellarg( PHP_BINARY ) . ' ' . escapeshellarg( __DIR__ . '/activity-log.php' );
passthru( $activityLogTest, $activityLogStatus );
$assert( 0 === $activityLogStatus, 'Core-backed activity-log compatibility regression test failed.' );

$dataNormalizationTest = escapeshellarg( PHP_BINARY ) . ' ' . escapeshellarg( __DIR__ . '/data-normalization-compatibility.php' );
passthru( $dataNormalizationTest, $dataNormalizationStatus );
$assert( 0 === $dataNormalizationStatus, 'Core-backed data-normalization compatibility regression test failed.' );

$profileContentTypesTest = escapeshellarg( PHP_BINARY ) . ' ' . escapeshellarg( __DIR__ . '/profile-content-types.php' );
passthru( $profileContentTypesTest, $profileContentTypesStatus );
$assert( 0 === $profileContentTypesStatus, 'Profile content-type ACF structure regression test failed.' );

if ( [] !== $failures ) {
    foreach ( $failures as $failure ) {
        // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fwrite -- CLI test failure output.
        fwrite( STDERR, 'FAIL: ' . $failure . PHP_EOL );
    }
    exit( 1 );
}

echo 'PASS: SFPF standalone architecture, security, gallery, and social-icon regressions (' . count( $sourceFiles ) . ' PHP files scanned).' . PHP_EOL;
