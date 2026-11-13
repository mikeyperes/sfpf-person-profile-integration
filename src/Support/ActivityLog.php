<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Support;

use Hexa\PluginCore\ActivityLog\ActivityLogConfig;
use Hexa\PluginCore\ActivityLog\ActivityLogEntry;
use Hexa\PluginCore\ActivityLog\ActivityLogger;

defined( 'ABSPATH' ) || exit;

/** The plugin's activity log (the last 100 changes), kept by Hexa WP Core. */
final class ActivityLog {
    public const OPTION = 'sfpf_activity_log';

    private static ?ActivityLogger $logger = null;

    public static function add( string $message, string $level = 'info' ): void {
        self::logger()->add(
            new ActivityLogEntry(
                $message,
                [],
                (string) get_current_user_id(),
                'sfpf-person-profile',
                current_time( 'mysql' ),
                in_array( $level, [ 'info', 'success', 'warning', 'error' ], true ) ? $level : 'info'
            )
        );
    }

    /** @return list<ActivityLogEntry> Newest first. */
    public static function entries( int $limit = 50 ): array {
        return array_slice( array_reverse( self::logger()->all() ), 0, max( 0, $limit ) );
    }

    public static function clear(): void {
        self::logger()->clear();
    }

    public static function config(): ActivityLogConfig {
        return new ActivityLogConfig(
            [
                'id'          => 'sfpf-activity-log',
                'title'       => 'SFPF Activity Log',
                'storage'     => ActivityLogConfig::STORAGE_PERMANENT,
                'storage_key' => self::OPTION,
                'max_entries' => 100,
            ]
        );
    }

    private static function logger(): ActivityLogger {
        return self::$logger ??= new ActivityLogger( self::config() );
    }
}
