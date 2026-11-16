<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Admin\Tabs;

use SFPF\PersonProfile\Admin\Actions\PressReleaseActions;
use SFPF\PersonProfile\Admin\AdminUi;
use SFPF\PersonProfile\Person\PersonProfile;
use SFPF\PersonProfile\Person\PersonResolver;
use SFPF\PersonProfile\PressReleases\PressReleaseSettings;

defined( 'ABSPATH' ) || exit;

/** Link the person's author and Verified Profile without enabling visitor output. */
final class PressReleasesTab implements Tab {
    public function id(): string {
        return 'press-releases';
    }

    public function label(): string {
        return 'Press Releases';
    }

    public function group(): string {
        return 'Content';
    }

    public function render(): void {
        $person = PersonResolver::profile();
        if ( null === $person ) {
            echo AdminUi::card( 'Press Releases', '<p>Choose the website\'s person in HWS Base Tools first.</p>' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            return;
        }
        $saved = PressReleaseSettings::current();
        $fields = '<input type="hidden" name="person_id" value="' . $person->user_id() . '">'
            . $this->origin_field( $saved )
            . $this->by_you_block( $saved )
            . $this->about_you_block( $saved, $person );
        $intro = 'Connections for ' . $person->name() . '. Both switches start off. '
            . 'Checking a connection does not save settings or turn on output.';
        echo AdminUi::card( 'Press Releases', AdminUi::form( PressReleaseActions::SAVE, $fields ), $intro ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo $this->profile_links( $saved ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo $this->shortcodes_card(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }

    /** @param array<string,mixed> $saved */
    private function origin_field( array $saved ): string {
        $origin = (string) ( $saved['origin'] ?? PressReleaseSettings::DEFAULT_ORIGIN );
        return '<p><label><strong>Publication origin</strong><br><input type="url" class="large-text" name="origin" value="' . esc_attr( $origin ) . '" required></label></p>';
    }

    /** @param array<string,mixed> $saved */
    private function by_you_block( array $saved ): string {
        $placeholder = PressReleaseSettings::DEFAULT_ORIGIN . '/author/…/';
        return AdminUi::toggle( 'by', PressReleaseSettings::enabled( 'by' ), PressReleaseSettings::MODES['by'], 'Show releases authored by this person on Hexa PR Wire.' )
            . '<p><label><strong>Author profile URL</strong><br><input type="url" class="large-text" name="author_url" value="' . esc_attr( (string) ( $saved['author_url'] ?? '' ) ) . '" placeholder="' . esc_attr( $placeholder ) . '"></label></p>'
            . $this->check_button( 'author', 'Check author connection' );
    }

    /** @param array<string,mixed> $saved */
    private function about_you_block( array $saved, PersonProfile $person ): string {
        return AdminUi::toggle( 'about', PressReleaseSettings::enabled( 'about' ), PressReleaseSettings::MODES['about'], 'Show releases linked to this person through Verified Profiles on Hexa PR Wire.' )
            . '<p><label><strong>Find your Verified Profile</strong><br><input type="search" class="regular-text" name="profile_name" value="' . esc_attr( $person->name() ) . '"></label></p>'
            . $this->check_button( 'profiles', 'Find Verified Profiles' )
            . $this->profile_picker( $saved );
    }

    /** @param array<string,mixed> $saved */
    private function profile_picker( array $saved ): string {
        $html = '<p><label><strong>Verified Profile</strong><br><select name="profile_id" data-sfpf-press-profiles><option value="0">Choose a profile</option>';
        if ( ! empty( $saved['profile_id'] ) ) {
            $html .= '<option value="' . absint( $saved['profile_id'] ) . '" selected>' . esc_html( (string) ( $saved['profile_name'] ?? '' ) ) . '</option>';
        }
        return $html . '</select></label></p><input type="hidden" name="profile_label" value="' . esc_attr( (string) ( $saved['profile_name'] ?? '' ) ) . '"><p>'
            . '<button type="button" class="button" data-sfpf-press-check="profiles" data-sfpf-press-page="previous" disabled>Previous profiles</button> '
            . '<button type="button" class="button" data-sfpf-press-check="profiles" data-sfpf-press-page="next" disabled>Next profiles</button>'
            . '<span class="sfpf-form-status" role="status" aria-live="polite"></span></p>'
            . $this->check_button( 'about', 'Check profile connection' );
    }

    /** @param array<string,mixed> $saved */
    private function profile_links( array $saved ): string {
        $html = '';
        foreach ( [ 'author' => 'Author profile', 'profile' => 'Verified Profile' ] as $prefix => $label ) {
            if ( ! empty( $saved[ $prefix . '_url' ] ) ) {
                $name = trim( (string) ( $saved[ $prefix . '_name' ] ?? '' ) );
                $html .= '<p><strong>' . esc_html( $label ) . ':</strong> <a href="' . esc_url( (string) $saved[ $prefix . '_url' ] ) . '" target="_blank" rel="noopener">' . esc_html( '' !== $name ? $name : (string) $saved[ $prefix . '_url' ] ) . '</a></p>';
            }
        }
        return $html;
    }

    private function shortcodes_card(): string {
        $shortcodes = '<p>' . AdminUi::shortcode( '[person_section id="press_releases_by" heading="Press releases by me"]' ) . '</p>'
            . '<p>' . AdminUi::shortcode( '[person_section id="press_releases_about" heading="Press releases about me"]' ) . '</p>';
        return AdminUi::card( 'Show on a page', $shortcodes, 'Add a shortcode to the page where its list belongs. Each list stays empty until its switch is saved on.' );
    }

    private function check_button( string $mode, string $label ): string {
        return '<p><button type="button" class="button" data-sfpf-press-check="' . esc_attr( $mode ) . '">' . esc_html( $label ) . '</button> <span class="sfpf-form-status" role="status" aria-live="polite"></span></p>';
    }
}
