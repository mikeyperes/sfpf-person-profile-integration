<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Shortcodes\Sections;

use SFPF\PersonProfile\Person\PersonProfile;

defined( 'ABSPATH' ) || exit;

/** A link to the person's Google Knowledge Panel. */
final class KnowledgePanelSection implements Section {
    public function id(): string {
        return 'knowledge_panel';
    }

    public function label(): string {
        return 'Knowledge Panel link';
    }

    public function layouts(): array {
        return [ 'link' => 'A link' ];
    }

    public function attributes(): array {
        return [ 'text' => '' ];
    }

    public function has_content( PersonProfile $profile ): bool {
        return '' !== $profile->knowledge_panel_url();
    }

    public function render( PersonProfile $profile, array $atts ): string {
        $url  = $profile->knowledge_panel_url();
        $text = '' !== ( $atts['text'] ?? '' ) ? (string) $atts['text'] : $url;
        return '<a class="sfpf-knowledge-panel__link" href="' . esc_url( $url ) . '" target="_blank" rel="noopener">' . esc_html( $text ) . '</a>';
    }
}
