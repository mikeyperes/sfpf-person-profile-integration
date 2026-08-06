<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Support;

use Hexa\PluginCore\ActivityLog\ActivityLogConfig;
use Hexa\PluginCore\ActivityLog\ActivityLogEntry;
use Hexa\PluginCore\ActivityLog\ActivityLogger;

defined( 'ABSPATH' ) || exit;

final class ActivityLogAdapter {
    private const OPTION = 'sfpf_activity_log';

    private static ?ActivityLogger $logger = null;

    public static function add( string $message, string $level = 'info' ): void {
        $level = in_array( $level, [ 'info', 'success', 'warning', 'error' ], true ) ? $level : 'info';

        self::logger()->add(
            new ActivityLogEntry(
                $message,
                [],
                (string) get_current_user_id(),
                'sfpf-person-profile',
                current_time( 'mysql' ),
                $level
            )
        );
    }

    /** @return list<array<string,mixed>> */
    public static function legacy_entries( int $limit = 50 ): array {
        $entries = array_reverse( self::logger()->all() );
        $entries = array_slice( $entries, 0, max( 0, $limit ) );

        return array_map(
            static function ( ActivityLogEntry $entry ): array {
                $data = $entry->to_array();
                return [
                    'timestamp' => (string) $data['timestamp'],
                    'message'   => (string) $data['message'],
                    'type'      => (string) $data['level'],
                    'user'      => absint( $data['actor'] ?? 0 ),
                ];
            },
            $entries
        );
    }

    public static function clear(): void {
        self::logger()->clear();
        self::$logger = null;
    }

    private static function logger(): ActivityLogger {
        if ( self::$logger instanceof ActivityLogger ) {
            return self::$logger;
        }

        self::migrate_legacy_entries();
        self::$logger = new ActivityLogger(
            new ActivityLogConfig(
                [
                    'id'          => 'sfpf-activity-log',
                    'title'       => 'SFPF Activity Log',
                    'storage'     => ActivityLogConfig::STORAGE_PERMANENT,
                    'storage_key' => self::OPTION,
                    'max_entries' => 100,
                ]
            )
        );

        return self::$logger;
    }

    private static function migrate_legacy_entries(): void {
        $entries = get_option( self::OPTION, [] );
        if ( ! is_array( $entries ) || [] === $entries ) {
            return;
        }

        $changed = false;
        foreach ( $entries as &$entry ) {
            if ( ! is_array( $entry ) || isset( $entry['level'] ) ) {
                continue;
            }

            $entry = [
                'message'   => (string) ( $entry['message'] ?? '' ),
                'context'   => [],
                'actor'     => (string) absint( $entry['user'] ?? 0 ),
                'source'    => 'sfpf-person-profile',
                'timestamp' => (string) ( $entry['timestamp'] ?? current_time( 'mysql' ) ),
                'level'     => (string) ( $entry['type'] ?? 'info' ),
                'detail'    => '',
            ];
            $changed = true;
        }
        unset( $entry );

        if ( $changed ) {
            update_option( self::OPTION, $entries, false );
        }
    }
}
