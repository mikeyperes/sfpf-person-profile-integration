<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Core;

use Hexa\PluginCore\CoreBootstrap\CoreBootstrap;
use Hexa\PluginCore\CoreRuntime\PluginContext;
use Hexa\PluginCore\PluginUpdates\GitHubPluginUpdater;
use Hexa\PluginCore\PluginUpdates\UpdaterAjaxController;
use Hexa\PluginCore\PluginUpdates\UpdaterConfig;
use Hexa\PluginCore\WpAdminTabs\CoreTabConfig;
use Hexa\PluginCore\WpAdminTabs\CoreTabModule;

defined( 'ABSPATH' ) || exit;

final class CoreIntegration {
    private static ?CoreBootstrap $bootstrap = null;
    private static ?PluginContext $context = null;
    private static ?UpdaterConfig $updater_config = null;

    public static function boot(): void {
        if ( self::$bootstrap instanceof CoreBootstrap || ! self::classes_available() ) {
            return;
        }

        $context = new PluginContext(
            [
                'slug'        => 'sfpf-person-profile-integration',
                'basename'    => SFPF_PLUGIN_BASENAME,
                'version'     => SFPF_PLUGIN_VERSION,
                'path'        => SFPF_PLUGIN_DIR,
                'url'         => SFPF_PLUGIN_URL,
                'github_repo' => 'mikeyperes/sfpf-person-profile-integration',
                'admin_page'  => admin_url( 'options-general.php?page=sfpf-person-profile' ),
                'capability'  => 'manage_options',
            ]
        );

        $bootstrap = new CoreBootstrap( $context );
        $updater   = self::updater_config();

        $bootstrap
            ->add_module( new GitHubPluginUpdater( $updater ) )
            ->add_module( new UpdaterAjaxController( $updater ) );

        if ( is_admin() || wp_doing_ajax() ) {
            $bootstrap->add_module(
                new CoreTabModule(
                    new CoreTabConfig(
                        [
                            'tabs_filter'   => 'sfpf_dashboard_tabs',
                            'render_filter' => 'sfpf_dashboard_render_tab',
                            'capability'    => 'manage_options',
                            'core_root'     => SFPF_PLUGIN_DIR . 'lib/hexa-wordpress-plugin-core',
                            'readme_path'   => SFPF_PLUGIN_DIR . 'lib/hexa-wordpress-plugin-core/README.md',
                            'library_path'  => SFPF_PLUGIN_DIR . 'HEXA_PLUGIN_CORE_LIBRARY.md',
                        ]
                    )
                )
            );
        }

        $bootstrap->boot();

        self::$context   = $context;
        self::$bootstrap = $bootstrap;

        do_action( 'sfpf_person_profile_core_booted', $context, $bootstrap );
    }

    public static function context(): ?PluginContext {
        self::boot();

        return self::$context;
    }

    public static function bootstrap(): ?CoreBootstrap {
        self::boot();

        return self::$bootstrap;
    }

    public static function updater_config(): UpdaterConfig {
        if ( self::$updater_config instanceof UpdaterConfig ) {
            return self::$updater_config;
        }

        self::$updater_config = UpdaterConfig::from_plugin_file(
            SFPF_PLUGIN_FILE,
            'mikeyperes/sfpf-person-profile-integration',
            [
                'plugin_slug'               => 'sfpf-person-profile-integration',
                'proper_folder_name'        => 'sfpf-person-profile-integration',
                'runtime_folder_name'       => dirname( SFPF_PLUGIN_BASENAME ),
                'plugin_basename'           => SFPF_PLUGIN_BASENAME,
                'canonical_plugin_basename' => 'sfpf-person-profile-integration/initialization.php',
                'plugin_starter_file'       => 'initialization.php',
                'github_branch'             => 'main',
                'requires'                  => '5.8',
                'tested'                    => get_bloginfo( 'version' ),
                'requires_php'              => '8.0',
                'nonce_action'              => 'sfpf_core_updater',
                'nonce_param'               => 'nonce',
                'ajax_action_prefix'        => 'sfpf_core_updater',
                'progress_key'              => 'sfpf_core_update_progress',
            ]
        );

        return self::$updater_config;
    }

    private static function classes_available(): bool {
        return class_exists( PluginContext::class )
            && class_exists( CoreBootstrap::class )
            && class_exists( UpdaterConfig::class );
    }
}
