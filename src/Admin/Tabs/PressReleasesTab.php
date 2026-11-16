<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Admin\Tabs;

use SFPF\PersonProfile\Admin\Actions\PressReleaseActions;
use SFPF\PersonProfile\Admin\AdminUi;
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
            . '<p><label><strong>Publication origin</strong><br><input type="url" class="large-text" name="origin" value="' . esc_attr( (string) ( $saved['origin'] ?? 'https://hexaprwire.com' ) ) . '" required></label></p>'
            . AdminUi::toggle( 'by', PressReleaseSettings::enabled( 'by' ), 'By you', 'Show releases authored by this person on Hexa PR Wire.' )
            . '<p><label><strong>Author profile URL</strong><br><input type="url" class="large-text" name="author_url" value="' . esc_attr( (string) ( $saved['author_url'] ?? '' ) ) . '" placeholder="https://hexaprwire.com/author/…/"></label></p>'
            . $this->check_button( 'author', 'Check author connection' )
            . AdminUi::toggle( 'about', PressReleaseSettings::enabled( 'about' ), 'About you', 'Show releases linked to this person through Verified Profiles on Hexa PR Wire.' )
            . '<p><label><strong>Find your Verified Profile</strong><br><input type="search" class="regular-text" name="profile_name" value="' . esc_attr( $person->name() ) . '"></label></p>'
            . $this->check_button( 'profiles', 'Find Verified Profiles' )
            . '<p><label><strong>Verified Profile</strong><br><select name="profile_id" data-sfpf-press-profiles><option value="0">Choose a profile</option>';
        if ( ! empty( $saved['profile_id'] ) ) {
            $fields .= '<option value="' . absint( $saved['profile_id'] ) . '" selected>' . esc_html( (string) ( $saved['profile_name'] ?? '' ) ) . '</option>';
        }
        $fields .= '</select></label></p><input type="hidden" name="profile_label" value="' . esc_attr( (string) ( $saved['profile_name'] ?? '' ) ) . '"><p>'
            . '<button type="button" class="button" data-sfpf-press-check="profiles" data-sfpf-press-page="previous" disabled>Previous profiles</button> '
            . '<button type="button" class="button" data-sfpf-press-check="profiles" data-sfpf-press-page="next" disabled>Next profiles</button>'
            . '<span class="sfpf-form-status" role="status" aria-live="polite"></span></p>'
            . $this->check_button( 'about', 'Check profile connection' );
        echo AdminUi::card( 'Press Releases', AdminUi::form( PressReleaseActions::SAVE, $fields ), 'Connections for ' . $person->name() . '. Both switches start off. Checking a connection does not save settings or turn on output.' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        foreach ( [ 'author' => 'Author profile', 'profile' => 'Verified Profile' ] as $prefix => $label ) {
            if ( ! empty( $saved[ $prefix . '_url' ] ) ) {
                $name = trim( (string) ( $saved[ $prefix . '_name' ] ?? '' ) );
                echo '<p><strong>' . esc_html( $label ) . ':</strong> <a href="' . esc_url( (string) $saved[ $prefix . '_url' ] ) . '" target="_blank" rel="noopener">' . esc_html( '' !== $name ? $name : (string) $saved[ $prefix . '_url' ] ) . '</a></p>';
            }
        }
        $shortcodes = '<p>' . AdminUi::shortcode( '[person_section id="press_releases_by" heading="Press releases by me"]' ) . '</p>'
            . '<p>' . AdminUi::shortcode( '[person_section id="press_releases_about" heading="Press releases about me"]' ) . '</p>';
        echo AdminUi::card( 'Show on a page', $shortcodes, 'Add a shortcode to the page where its list belongs. Each list stays empty until its switch is saved on.' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }

    private function check_button( string $mode, string $label ): string {
        return '<p><button type="button" class="button" data-sfpf-press-check="' . esc_attr( $mode ) . '">' . esc_html( $label ) . '</button> <span class="sfpf-form-status" role="status" aria-live="polite"></span></p>';
    }
}
