<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Shortcodes\Sections;

use SFPF\PersonProfile\Person\PersonProfile;

defined( 'ABSPATH' ) || exit;

/** Each school with its degree, field of study and year; the school links to its Wikipedia page. */
final class EducationSection implements Section {
    public function id(): string {
        return 'education';
    }

    public function label(): string {
        return 'Education';
    }

    public function layouts(): array {
        return [ 'list' => 'One block per school', 'inline' => 'One line per school' ];
    }

    public function attributes(): array {
        return [];
    }

    public function has_content( PersonProfile $profile ): bool {
        return [] !== $profile->education();
    }

    public function render( PersonProfile $profile, array $atts ): string {
        $inline = 'inline' === ( $atts['layout'] ?? '' );
        $html   = '<ul class="sfpf-education__list founder-education">';
        foreach ( $profile->education() as $row ) {
            $school = '' !== $row['wikipedia_url']
                ? '<a href="' . esc_url( $row['wikipedia_url'] ) . '" target="_blank" rel="noopener">' . esc_html( $row['school'] ) . '</a>'
                : esc_html( $row['school'] );
            $degree = implode( ' in ', array_map( 'esc_html', array_filter( [ $row['degree'], $row['field'] ] ) ) );
            $html  .= '<li class="sfpf-education__item education-item">';
            if ( $inline ) {
                $html .= implode( ', ', array_filter( [ '' !== $row['school'] ? '<span class="sfpf-education__school college">' . $school . '</span>' : '', '' !== $degree ? '<span class="sfpf-education__degree degree">' . $degree . '</span>' : '', '' !== $row['year'] ? '<span class="sfpf-education__year year">' . esc_html( $row['year'] ) . '</span>' : '' ] ) );
            } else {
                $html .= '' !== $row['school'] ? '<div class="sfpf-education__school college">' . $school . '</div>' : '';
                $html .= '' !== $degree ? '<div class="sfpf-education__degree degree">' . $degree . '</div>' : '';
                $html .= '' !== $row['year'] ? '<div class="sfpf-education__year year">' . esc_html( $row['year'] ) . '</div>' : '';
            }
            $html .= '</li>';
        }
        return $html . '</ul>';
    }
}
