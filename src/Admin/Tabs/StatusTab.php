<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Admin\Tabs;

use Hexa\PluginCore\SystemChecks\SystemChecksRenderer;
use SFPF\PersonProfile\Admin\AdminUi;
use SFPF\PersonProfile\Admin\Checks\ReadinessChecks;
use SFPF\PersonProfile\Person\PersonResolver;

defined( 'ABSPATH' ) || exit;

/** Whether the person website is ready, with the person at a glance. */
final class StatusTab implements Tab {
    public function id(): string {
        return 'overview';
    }

    public function label(): string {
        return 'Status';
    }

    public function group(): string {
        return 'Overview';
    }

    public function render(): void {
        $profile = PersonResolver::profile();
        if ( null !== $profile ) {
            $body = '<div class="sfpf-person-card"><img src="' . esc_url( $profile->avatar_url( 160 ) ) . '" alt="" width="80" height="80"><div><strong>' . esc_html( $profile->name() ) . '</strong>'
                . ( '' !== $profile->title() ? '<br><span>' . esc_html( $profile->title() ) . '</span>' : '' )
                . '<p>' . AdminUi::link_button( $profile->edit_url(), 'Edit profile' ) . ' ' . AdminUi::link_button( home_url( '/' ), 'View website' ) . ' ' . AdminUi::link_button( home_url( '/sfpf-profile-debug/' ), 'Profile report' ) . '</p></div></div>';
            echo AdminUi::card( 'Person', $body ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Built from escaped parts.
        }
        echo ( new SystemChecksRenderer() )->render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Core renderer escapes.
            ReadinessChecks::items(),
            [
                'id'            => 'sfpf-readiness',
                'title'         => 'Readiness',
                'category_meta' => [
                    'Plugins'         => [ 'icon' => 'dashicons-admin-plugins', 'color' => '#6366f1' ],
                    'WordPress'       => [ 'icon' => 'dashicons-wordpress', 'color' => '#0073aa' ],
                    'Person'          => [ 'icon' => 'dashicons-admin-users', 'color' => '#2563eb' ],
                    'Knowledge Panel' => [ 'icon' => 'dashicons-google', 'color' => '#16a34a' ],
                    'Schema'          => [ 'icon' => 'dashicons-editor-code', 'color' => '#8b5cf6' ],
                    'Pages'           => [ 'icon' => 'dashicons-admin-page', 'color' => '#d97706' ],
                ],
            ]
        );
    }
}
