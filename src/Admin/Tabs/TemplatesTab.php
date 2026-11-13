<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Admin\Tabs;

use SFPF\PersonProfile\Admin\Actions\SettingsActions;
use SFPF\PersonProfile\Admin\AdminUi;
use SFPF\PersonProfile\ContentTypes\ContentTypeCatalog;
use SFPF\PersonProfile\Settings\Settings;

defined( 'ABSPATH' ) || exit;

/** The Elementor loop item template each content type's [person_loop] list uses. */
final class TemplatesTab implements Tab {
    public function id(): string {
        return 'templates';
    }

    public function label(): string {
        return 'Loop Templates';
    }

    public function group(): string {
        return 'Site';
    }

    public function render(): void {
        $templates = get_posts( [ 'post_type' => 'elementor_library', 'post_status' => 'publish', 'posts_per_page' => 200, 'orderby' => 'title', 'order' => 'ASC', 'meta_query' => [ [ 'key' => '_elementor_template_type', 'value' => 'loop-item' ] ] ] );
        $chosen    = Settings::map( Settings::LOOP_TEMPLATES );
        $fields    = '';
        foreach ( ContentTypeCatalog::TYPES as $type ) {
            if ( ! post_type_exists( $type::POST_TYPE ) ) {
                continue;
            }
            $options = '<option value="0">Title and summary (no template)</option>';
            foreach ( $templates as $template ) {
                $options .= '<option value="' . (int) $template->ID . '"' . selected( (int) ( $chosen[ $type::POST_TYPE ] ?? 0 ), (int) $template->ID, false ) . '>' . esc_html( $template->post_title ) . '</option>';
            }
            $object  = get_post_type_object( $type::POST_TYPE );
            $fields .= '<p class="sfpf-setting"><label><strong>' . esc_html( $object ? $object->labels->name : $type::POST_TYPE ) . '</strong><br><select name="loop[' . esc_attr( $type::POST_TYPE ) . ']">' . $options . '</select></label><br>' . AdminUi::shortcode( '[person_loop type="' . $type::POST_TYPE . '" columns="3"]' ) . '</p>';
        }
        $body = '' === $fields ? '<p>Turn on a content type first; its list can then use a loop template.</p>' : AdminUi::form( SettingsActions::LOOPS, $fields );
        echo AdminUi::card( 'Loop templates', $body, 'Each [person_loop] list draws its items with the Elementor loop item template chosen here. Create loop item templates in Elementor (Templates, Loop Item).' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }
}
