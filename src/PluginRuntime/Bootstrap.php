<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\PluginRuntime;

use Hexa\PluginCore\CoreBootstrap\CoreBootstrap;
use Hexa\PluginCore\CorePackageUpdates\CorePackageAjaxController;
use Hexa\PluginCore\CoreRuntime\PluginContext;
use Hexa\PluginCore\PluginUpdates\GitHubPluginUpdater;
use Hexa\PluginCore\PluginUpdates\UpdaterAjaxController;
use Hexa\PluginCore\WpAdminTabs\CoreTabConfig;
use Hexa\PluginCore\WpAdminTabs\CoreTabModule;
use SFPF\PersonProfile\Admin\Actions\PagesActions;
use SFPF\PersonProfile\Admin\Actions\SchemaActions;
use SFPF\PersonProfile\Admin\Actions\SettingsActions;
use SFPF\PersonProfile\Admin\Dashboard;
use SFPF\PersonProfile\Admin\Profile\ProfileScreen;
use SFPF\PersonProfile\ContentTypes\ContentTypeCatalog;
use SFPF\PersonProfile\Diagnostics\DebugEndpoint;
use SFPF\PersonProfile\Elementor\ElementorIntegration;
use SFPF\PersonProfile\Fields\DuplicateGroupGuard;
use SFPF\PersonProfile\Fields\EducationProfileLinks;
use SFPF\PersonProfile\Fields\KnowledgeGraphIdField;
use SFPF\PersonProfile\Fields\RepeaterHydration;
use SFPF\PersonProfile\Frontend\AuthorArchive;
use SFPF\PersonProfile\Frontend\BreadcrumbVisibility;
use SFPF\PersonProfile\Migrations\MigrationRunner;
use SFPF\PersonProfile\Schema\SchemaCatalog;
use SFPF\PersonProfile\Schema\SchemaMarkupField;
use SFPF\PersonProfile\Shortcodes\ShortcodeCatalog;
use SFPF\PersonProfile\Sitemaps\SitemapTypes;
use SFPF\PersonProfile\Support\Requirements;

defined( 'ABSPATH' ) || exit;

/**
 * Builds the plugin on Hexa WP Core: one context, one Core bootstrap, and a
 * module per feature. Each module's register() only adds hooks.
 */
final class Bootstrap {
    private static bool $booted = false;

    public static function boot(): void {
        if ( self::$booted || ! class_exists( CoreBootstrap::class ) ) {
            return;
        }
        self::$booted = true;
        $core = new CoreBootstrap(
            new PluginContext(
                [
                    'slug'        => PluginMetadata::SLUG,
                    'basename'    => PluginMetadata::basename(),
                    'version'     => PluginMetadata::VERSION,
                    'path'        => PluginMetadata::root_path() . '/',
                    'url'         => PluginMetadata::url(),
                    'github_repo' => PluginMetadata::GITHUB_REPOSITORY,
                    'admin_page'  => PluginMetadata::admin_url(),
                    'capability'  => PluginMetadata::ADMIN_CAPABILITY,
                ]
            )
        );

        // The person's data and content.
        $core->add_module( ContentTypeCatalog::content_types() )
            ->add_module( ContentTypeCatalog::field_groups() )
            ->add_module( new RepeaterHydration() )
            ->add_module( new DuplicateGroupGuard() )
            ->add_module( new KnowledgeGraphIdField() )
            ->add_module( new MigrationRunner() );

        // What visitors and search engines see.
        $core->add_module( new SchemaCatalog() )
            ->add_module( new ShortcodeCatalog() )
            ->add_module( new ElementorIntegration() )
            ->add_module( new AuthorArchive() )
            ->add_module( new BreadcrumbVisibility() )
            ->add_module( new SitemapTypes() )
            ->add_module( new DebugEndpoint() );

        $updater = Updates::plugin();
        if ( is_admin() || wp_doing_ajax() || wp_doing_cron() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
            $core->add_module( new GitHubPluginUpdater( $updater ) );
        }
        if ( is_admin() || wp_doing_ajax() ) {
            $core->add_module( new UpdaterAjaxController( $updater ) )
                ->add_module( new CorePackageAjaxController( Updates::core() ) )
                ->add_module( new Requirements() )
                ->add_module( new Dashboard() )
                ->add_module( new SettingsActions() )
                ->add_module( new PagesActions() )
                ->add_module( new SchemaActions() )
                ->add_module( new ProfileScreen() )
                ->add_module( new EducationProfileLinks() )
                ->add_module( new SchemaMarkupField() )
                ->add_module(
                    new CoreTabModule(
                        new CoreTabConfig(
                            [
                                'tabs_filter'   => Dashboard::TABS_FILTER,
                                'render_filter' => Dashboard::RENDER_FILTER,
                                'capability'    => PluginMetadata::ADMIN_CAPABILITY,
                                'core_root'     => PluginMetadata::core_root(),
                                'readme_path'   => PluginMetadata::core_root() . '/README.md',
                                'library_path'  => PluginMetadata::root_path() . '/HEXA_PLUGIN_CORE_LIBRARY.md',
                            ]
                        )
                    )
                );
        }
        $core->boot();
        do_action( 'sfpf_person_profile_booted', $core );
    }
}
