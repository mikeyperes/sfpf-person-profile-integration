<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Migrations;

use SFPF\PersonProfile\Support\ActivityLog;

defined( 'ABSPATH' ) || exit;

/** Log entries from before Hexa WP Core kept the log (type, user) take Core's shape (level, actor). */
final class ActivityLogFormatMigration implements Migration {
    public function id(): string {
        return 'activity-log-format';
    }

    public function migrate(): bool {
        $entries = get_option( ActivityLog::OPTION, [] );
        if ( ! is_array( $entries ) ) {
            return true;
        }
        $changed = false;
        foreach ( $entries as &$entry ) {
            if ( ! is_array( $entry ) || isset( $entry['level'] ) ) {
                continue;
            }
            $entry   = [
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
            update_option( ActivityLog::OPTION, $entries, false );
        }
        return true;
    }
}
