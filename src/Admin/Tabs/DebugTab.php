<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Admin\Tabs;

use SFPF\PersonProfile\Admin\Actions\SettingsActions;
use SFPF\PersonProfile\Admin\AdminUi;
use SFPF\PersonProfile\Diagnostics\ProfileReport;
use SFPF\PersonProfile\Support\ActivityLog;

defined( 'ABSPATH' ) || exit;

/** The profile report in short, and the plugin's activity log. */
final class DebugTab implements Tab {
    public function id(): string {
        return 'debug';
    }

    public function label(): string {
        return 'Debug';
    }

    public function group(): string {
        return 'System';
    }

    public function render(): void {
        $report   = ProfileReport::build();
        $sections = '';
        foreach ( $report['sections'] as $id => $has ) {
            $sections .= '<li>' . esc_html( $id ) . ': <span class="' . ( $has ? 'sfpf-ok">has content' : 'sfpf-empty">empty' ) . '</span></li>';
        }
        $body = '<p>' . AdminUi::link_button( home_url( '/sfpf-profile-debug/' ), 'Open the full profile report' ) . ' ' . AdminUi::link_button( home_url( '/sfpf-profile-debug/?format=json' ), 'As JSON' ) . '</p><ul class="sfpf-columns">' . $sections . '</ul>';
        echo AdminUi::card( 'Profile report', $body, 'Every person value, section, linked page and the schema of the key pages. Visible to administrators only.' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        $rows = '';
        foreach ( ActivityLog::entries( 50 ) as $entry ) {
            $data  = $entry->to_array();
            $user  = get_userdata( (int) $data['actor'] );
            $rows .= '<tr><td>' . esc_html( (string) $data['timestamp'] ) . '</td><td>' . esc_html( (string) $data['level'] ) . '</td><td>' . esc_html( (string) $data['message'] ) . '</td><td>' . esc_html( $user ? $user->display_name : 'System' ) . '</td></tr>';
        }
        $log = '' === $rows ? '<p>Nothing logged yet.</p>' : '<table class="widefat striped"><tbody>' . $rows . '</tbody></table>' . AdminUi::form( SettingsActions::CLEAR_LOG, '', 'Clear the log' );
        echo AdminUi::card( 'Activity log', $log ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }
}
