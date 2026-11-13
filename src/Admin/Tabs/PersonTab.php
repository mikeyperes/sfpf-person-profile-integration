<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Admin\Tabs;

use SFPF\PersonProfile\Admin\AdminUi;
use SFPF\PersonProfile\Person\PersonFieldValues;
use SFPF\PersonProfile\Person\PersonResolver;

defined( 'ABSPATH' ) || exit;

/** Every person value with what it holds now and the shortcode that prints it. */
final class PersonTab implements Tab {
    public function id(): string {
        return 'person';
    }

    public function label(): string {
        return 'Person Values';
    }

    public function group(): string {
        return 'Person';
    }

    public function render(): void {
        $profile = PersonResolver::profile();
        if ( null === $profile ) {
            echo AdminUi::card( 'Person Values', '', 'No person is chosen yet. Choose the person in HWS Base Tools, Website Profile.' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            return;
        }
        $rows = '';
        foreach ( PersonFieldValues::FIELDS as $field => [ $label, $group ] ) {
            $value = PersonFieldValues::get( $profile, $field, [ 'network' => 'linkedin' ] );
            $code  = 'social' === $field ? '[person field="social" network="linkedin"]' : '[person field="' . $field . '"]';
            $rows .= '<tr><td>' . esc_html( $group ) . '</td><td><strong>' . esc_html( $label ) . '</strong></td><td>' . AdminUi::shortcode( $code ) . '</td><td class="' . ( $value->is_empty() ? 'sfpf-empty' : '' ) . '">' . ( $value->is_empty() ? 'Empty' : esc_html( wp_trim_words( $value->plain( ', ' ), 18 ) ) ) . '</td></tr>';
        }
        $table = '<table class="widefat striped sfpf-values"><thead><tr><th>Group</th><th>Value</th><th>Shortcode</th><th>Now</th></tr></thead><tbody>' . $rows . '</tbody></table>';
        echo AdminUi::card( 'Person Values', $table . '<p>' . AdminUi::link_button( $profile->edit_url(), 'Edit ' . $profile->name() . '\'s profile' ) . '</p>', 'What each value holds for ' . $profile->name() . ' right now. Click a shortcode to copy it; Elementor\'s SFPF Person dynamic tags offer the same values.' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }
}
