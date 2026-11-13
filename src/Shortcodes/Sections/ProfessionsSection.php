<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Shortcodes\Sections;

use SFPF\PersonProfile\Person\PersonProfile;

defined( 'ABSPATH' ) || exit;

/**
 * Each profession with its summary and a link to its own page. With
 * page="content" the profession page's text is shown too.
 */
final class ProfessionsSection implements Section {
    private static int $depth = 0;

    public function id(): string {
        return 'professions';
    }

    public function label(): string {
        return 'Professions';
    }

    public function layouts(): array {
        return [ 'list' => 'Name, summary and link', 'names' => 'Names only' ];
    }

    public function attributes(): array {
        return [ 'page' => 'link', 'link_text' => 'View details' ];
    }

    public function has_content( PersonProfile $profile ): bool {
        return [] !== array_filter( array_column( $profile->professions(), 'name' ) );
    }

    public function render( PersonProfile $profile, array $atts ): string {
        $names = 'names' === ( $atts['layout'] ?? '' );
        $html  = '<ul class="sfpf-professions__list founder-professions">';
        foreach ( $profile->professions() as $row ) {
            if ( '' === $row['name'] ) {
                continue;
            }
            $html .= '<li class="sfpf-professions__item profession-item"><div class="sfpf-professions__name name">' . esc_html( $row['name'] ) . '</div>';
            if ( ! $names ) {
                $html .= '' !== trim( wp_strip_all_tags( $row['summary_html'] ) ) ? '<div class="sfpf-professions__summary summary">' . wpautop( $row['summary_html'] ) . '</div>' : '';
                if ( $row['page_id'] > 0 && 'none' !== ( $atts['page'] ?? 'link' ) ) {
                    if ( 'content' === ( $atts['page'] ?? '' ) && 0 === self::$depth ) {
                        self::$depth++;
                        $html .= '<div class="sfpf-professions__page page-content">' . apply_filters( 'the_content', (string) get_post_field( 'post_content', $row['page_id'] ) ) . '</div>';
                        self::$depth--;
                    }
                    $html .= '<a class="sfpf-professions__link page-link" href="' . esc_url( (string) get_permalink( $row['page_id'] ) ) . '">' . esc_html( (string) ( $atts['link_text'] ?? 'View details' ) ) . '</a>';
                }
            }
            $html .= '</li>';
        }
        return $html . '</ul>';
    }
}
