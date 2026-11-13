<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Admin\Tabs;

use SFPF\PersonProfile\Admin\Actions\SettingsActions;
use SFPF\PersonProfile\Admin\AdminUi;
use SFPF\PersonProfile\Faq\FaqSets;
use SFPF\PersonProfile\Person\PersonResolver;
use SFPF\PersonProfile\Settings\Settings;

defined( 'ABSPATH' ) || exit;

/**
 * Site-wide FAQ sets: named lists of questions shown with [faq_set]. The
 * person's own FAQ lives on their profile.
 */
final class FaqTab implements Tab {
    public function id(): string {
        return 'faq';
    }

    public function label(): string {
        return 'FAQ Sets';
    }

    public function group(): string {
        return 'Site';
    }

    public function render(): void {
        $profile = PersonResolver::profile();
        $person  = null !== $profile
            ? '<p>' . count( FaqSets::person_items() ) . ' question(s) on ' . esc_html( $profile->name() ) . '\'s profile. ' . AdminUi::shortcode( '[person_section id="faq"]' ) . ' ' . AdminUi::link_button( $profile->edit_url(), 'Edit them' ) . '</p>'
            : '<p>No person is chosen yet.</p>';
        echo AdminUi::card( 'The person\'s FAQ', $person, 'Questions about the person belong on their profile; they show with the FAQ section and as FAQ schema on its page.' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

        $primary = (string) get_option( Settings::PRIMARY_FAQ_SET, '' );
        $sets    = '';
        foreach ( array_merge( FaqSets::sets(), [ [ 'name' => '', 'slug' => '', 'items' => [] ] ] ) as $index => $set ) {
            $sets .= self::set( (int) $index, $set, $primary );
        }
        $fields = '<div class="sfpf-faq-sets" data-sfpf-faq-sets>' . $sets . '</div><p><button type="button" class="button" data-sfpf-add-set>Add an FAQ set</button></p>';
        echo AdminUi::card( 'FAQ sets', AdminUi::form( SettingsActions::FAQ_SETS, $fields, 'Save FAQ sets' ), 'Each set has a name, a slug used in [faq_set slug="…"] and its questions. The primary set shows when no slug is given. Empty questions are dropped on save.' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }

    /** @param array<string,mixed> $set */
    private static function set( int $index, array $set, string $primary ): string {
        $slug  = (string) ( $set['slug'] ?? '' );
        $items = '';
        foreach ( array_merge( (array) ( $set['items'] ?? [] ), [ [ 'question' => '', 'answer' => '' ] ] ) as $item_index => $item ) {
            $items .= '<div class="sfpf-faq-row"><input type="text" class="widefat" placeholder="Question" name="sets[' . $index . '][items][' . (int) $item_index . '][question]" value="' . esc_attr( (string) ( $item['question'] ?? '' ) ) . '"><textarea class="widefat" rows="3" placeholder="Answer" name="sets[' . $index . '][items][' . (int) $item_index . '][answer]">' . esc_textarea( (string) ( $item['answer'] ?? '' ) ) . '</textarea></div>';
        }
        return '<fieldset class="sfpf-faq-set" data-index="' . $index . '"><legend>' . ( '' !== (string) ( $set['name'] ?? '' ) ? esc_html( (string) $set['name'] ) : 'New FAQ set' ) . '</legend>'
            . '<p><label>Name <input type="text" name="sets[' . $index . '][name]" value="' . esc_attr( (string) ( $set['name'] ?? '' ) ) . '"></label> <label>Slug <input type="text" name="sets[' . $index . '][slug]" value="' . esc_attr( $slug ) . '"></label> '
            . '<label><input type="radio" name="primary" value="' . esc_attr( $slug ) . '"' . checked( '' !== $slug && $slug === $primary, true, false ) . '> Primary</label>'
            . ( '' !== $slug ? ' ' . AdminUi::shortcode( '[faq_set slug="' . $slug . '"]' ) : '' ) . '</p>'
            . '<div class="sfpf-faq-rows">' . $items . '</div><p><button type="button" class="button" data-sfpf-add-question>Add a question</button></p></fieldset>';
    }
}
