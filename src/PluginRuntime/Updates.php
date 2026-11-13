<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\PluginRuntime;

use Hexa\PluginCore\CorePackageUpdates\CorePackageConfig;
use Hexa\PluginCore\PluginUpdates\UpdaterConfig;

defined( 'ABSPATH' ) || exit;

/** The plugin's GitHub updater and its bundled Hexa WP Core package, as Core configures them. */
final class Updates {
    private static ?UpdaterConfig $plugin = null;
    private static ?CorePackageConfig $core = null;

    public static function plugin(): UpdaterConfig {
        return self::$plugin ??= UpdaterConfig::from_plugin_file(
            PluginMetadata::plugin_file(),
            PluginMetadata::GITHUB_REPOSITORY,
            [
                'plugin_slug'               => PluginMetadata::SLUG,
                'proper_folder_name'        => PluginMetadata::SLUG,
                'runtime_folder_name'       => dirname( PluginMetadata::basename() ),
                'plugin_basename'           => PluginMetadata::basename(),
                'canonical_plugin_basename' => PluginMetadata::SLUG . '/' . PluginMetadata::MAIN_FILE,
                'plugin_starter_file'       => PluginMetadata::MAIN_FILE,
                'github_branch'             => PluginMetadata::GITHUB_BRANCH,
                'requires'                  => PluginMetadata::REQUIRES_WORDPRESS,
                'tested'                    => PluginMetadata::TESTED_WORDPRESS,
                'requires_php'              => PluginMetadata::REQUIRES_PHP,
                'nonce_action'              => 'sfpf_core_updater',
                'nonce_param'               => 'nonce',
                'ajax_action_prefix'        => 'sfpf_core_updater',
                'progress_key'              => 'sfpf_core_update_progress',
            ]
        );
    }

    public static function core(): CorePackageConfig {
        return self::$core ??= CorePackageConfig::from_core_root(
            PluginMetadata::core_root(),
            [
                'github_repo'        => 'mikeyperes/hexa-wordpress-plugin-core',
                'github_branch'      => 'main',
                'nonce_action'       => 'sfpf_core_package',
                'nonce_param'        => 'nonce',
                'ajax_action_prefix' => 'sfpf_core_package',
                'cache_key'          => 'sfpf_hexa_core_package',
            ]
        );
    }
}
