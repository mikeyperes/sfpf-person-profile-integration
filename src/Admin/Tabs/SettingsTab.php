<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Admin\Tabs;

use SFPF\PersonProfile\Admin\Actions\SettingsActions;
use SFPF\PersonProfile\Admin\AdminUi;
use SFPF\PersonProfile\ContentTypes\Book\BookContentType;
use SFPF\PersonProfile\Settings\Settings;

defined( 'ABSPATH' ) || exit;

/** The plugin's own settings. The person and the website type are chosen in HWS Base Tools. */
final class SettingsTab implements Tab {
    public function id(): string {
        return 'settings';
    }

    public function label(): string {
        return 'Settings';
    }

    public function group(): string {
        return 'Site';
    }

    public function render(): void {
        $books = post_type_exists( BookContentType::POST_TYPE ) ? get_posts( [ 'post_type' => BookContentType::POST_TYPE, 'post_status' => 'publish', 'posts_per_page' => 200, 'orderby' => 'title', 'order' => 'ASC' ] ) : [];
        $options = '<option value="0">The oldest book</option>';
        foreach ( $books as $book ) {
            $options .= '<option value="' . (int) $book->ID . '"' . selected( Settings::int( Settings::PRIMARY_BOOK ), (int) $book->ID, false ) . '>' . esc_html( $book->post_title ) . '</option>';
        }
        $fields = '<p class="sfpf-setting"><strong>The person and the website type</strong><br>' . AdminUi::link_button( admin_url( 'options-general.php?page=hws-core-tools&tab=website-types' ), 'Open HWS Base Tools, Website Profile', false ) . '<br><span class="description">The website\'s person is the Person chosen there; the website type should be Personal Website.</span></p>'
            . ( [] !== $books ? '<p class="sfpf-setting"><label><strong>Primary book</strong><br><select name="primary_book">' . $options . '</select></label><br><span class="description">The book [book field="…"] shows when no id is given and the page is not a book.</span></p>' : '' )
            . AdminUi::toggle( 'elementor_conditions', Settings::flag( Settings::ELEMENTOR_CONDITIONS, true ), 'Elementor display conditions', 'Adds "SFPF Person content" to Elementor Pro\'s display conditions, to show an element only when a person section has content.' )
            . AdminUi::toggle( 'hide_empty_social_icons', Settings::flag( Settings::HIDE_EMPTY_SOCIAL_ICONS, true ), 'Hide empty social icons', 'Elementor Social Icons widgets drop icons whose link is empty or did not resolve.' )
            . AdminUi::toggle( 'author_archive_profile', Settings::flag( Settings::AUTHOR_ARCHIVE_PROFILE, true ), 'Author profile pages', 'Author pages show the author\'s profile when no Elementor archive template covers them.' );
        echo AdminUi::card( 'Settings', AdminUi::form( SettingsActions::SETTINGS, $fields ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }
}
