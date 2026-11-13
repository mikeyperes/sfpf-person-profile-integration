<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Shortcodes\Sections;

use SFPF\PersonProfile\Person\PersonProfile;

defined( 'ABSPATH' ) || exit;

/** Where the person was born, linked to its Wikipedia page. */
final class BirthplaceSection implements Section {
    public function id(): string {
        return 'birthplace';
    }

    public function label(): string {
        return 'Birthplace';
    }

    public function layouts(): array {
        return [ 'link' => 'Label and linked place', 'text' => 'Label and place, no link', 'inline' => 'The linked place only' ];
    }

    public function attributes(): array {
        return [ 'label' => 'Birthplace:' ];
    }

    public function has_content( PersonProfile $profile ): bool {
        return '' !== $profile->birth_place()['location'];
    }

    public function render( PersonProfile $profile, array $atts ): string {
        $place  = $profile->birth_place();
        $layout = (string) ( $atts['layout'] ?? 'link' );
        $value  = '' !== $place['wikipedia_url'] && 'text' !== $layout
            ? '<a href="' . esc_url( $place['wikipedia_url'] ) . '" target="_blank" rel="noopener">' . esc_html( $place['location'] ) . '</a>'
            : esc_html( $place['location'] );
        if ( 'inline' === $layout ) {
            return '<span class="sfpf-birthplace__value location-born-value">' . $value . '</span>';
        }
        $label = (string) ( $atts['label'] ?? 'Birthplace:' );
        return ( '' !== $label ? '<span class="sfpf-birthplace__label location-born-label">' . esc_html( $label ) . '</span> ' : '' ) . '<span class="sfpf-birthplace__value location-born-value">' . $value . '</span>';
    }
}
