<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Shortcodes\Sections;

use SFPF\PersonProfile\Person\PersonProfile;

defined( 'ABSPATH' ) || exit;

/** The full biography, or the short one with part="short". */
final class BiographySection implements Section {
    public function id(): string {
        return 'biography';
    }

    public function label(): string {
        return 'Biography';
    }

    public function layouts(): array {
        return [ 'text' => 'The biography text' ];
    }

    public function attributes(): array {
        return [ 'part' => 'full' ];
    }

    public function has_content( PersonProfile $profile ): bool {
        return '' !== trim( wp_strip_all_tags( $profile->biography_html() . $profile->biography_short_html() ) );
    }

    public function render( PersonProfile $profile, array $atts ): string {
        $html = 'short' === ( $atts['part'] ?? 'full' ) ? $profile->biography_short_html() : $profile->biography_html();
        return '' === trim( wp_strip_all_tags( $html ) ) ? '' : '<div class="sfpf-biography__text">' . wpautop( $html ) . '</div>';
    }
}
