<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\PluginRuntime;

defined( 'ABSPATH' ) || exit;

/**
 * The plugin's identity. The version always matches the header of the main
 * file, which keeps the historical name initialization.php because sites and
 * Code's audit tool activate the plugin by that path.
 */
final class PluginMetadata {
    public const NAME               = 'SFPF Person Profile Integration';
    public const SLUG               = 'sfpf-person-profile-integration';
    public const PREFIX             = 'sfpf';
    public const ADMIN_PAGE_SLUG    = 'sfpf-person-profile';
    public const ADMIN_CAPABILITY   = 'manage_options';
    public const MAIN_FILE          = 'initialization.php';
    public const GITHUB_REPOSITORY  = 'mikeyperes/sfpf-person-profile-integration';
    public const GITHUB_BRANCH      = 'main';
    public const VERSION            = '4.0.2';
    public const REQUIRES_WORDPRESS = '6.0';
    public const REQUIRES_PHP       = '8.1';
    public const TESTED_WORDPRESS   = '7.0';

    public static function root_path(): string {
        return dirname( __DIR__, 2 );
    }

    public static function plugin_file(): string {
        return self::root_path() . '/' . self::MAIN_FILE;
    }

    public static function basename(): string {
        return plugin_basename( self::plugin_file() );
    }

    public static function url( string $path = '' ): string {
        return plugin_dir_url( self::plugin_file() ) . ltrim( $path, '/' );
    }

    public static function core_root(): string {
        return self::root_path() . '/lib/hexa-wordpress-plugin-core';
    }

    public static function admin_url( string $tab = '' ): string {
        $url = admin_url( 'options-general.php?page=' . self::ADMIN_PAGE_SLUG );
        return '' !== $tab ? add_query_arg( 'tab', $tab, $url ) : $url;
    }
}
