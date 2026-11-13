<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Shortcodes\Sections;

use SFPF\PersonProfile\ContentTypes\Organization\OrganizationRecord;
use SFPF\PersonProfile\Person\Links;
use SFPF\PersonProfile\Person\PersonProfile;

defined( 'ABSPATH' ) || exit;

/** The organizations the person founded, each linked to its page on the site. */
final class OrganizationsSection implements Section {
    public function id(): string {
        return 'organizations';
    }

    public function label(): string {
        return 'Organizations founded';
    }

    public function layouts(): array {
        return [ 'cards' => 'A card with logo, facts and summary', 'list' => 'Name, facts and summary', 'compact' => 'One line per organization' ];
    }

    public function attributes(): array {
        return [ 'limit' => '0' ];
    }

    public function has_content( PersonProfile $profile ): bool {
        return [] !== $profile->organizations();
    }

    public function render( PersonProfile $profile, array $atts ): string {
        $layout = (string) ( $atts['layout'] ?? 'cards' );
        $ids    = $profile->organizations();
        $limit  = absint( $atts['limit'] ?? 0 );
        $ids    = $limit > 0 ? array_slice( $ids, 0, $limit ) : $ids;
        $html   = '<ul class="sfpf-organizations__list founder-organizations-founded format-' . esc_attr( $layout ) . '">';
        foreach ( $ids as $id ) {
            $org = OrganizationRecord::for_post( $id );
            if ( null === $org ) {
                continue;
            }
            $facts = array_filter( [ $org->founding_date(), $org->headquarters()['location'] ] );
            $html .= '<li class="sfpf-organizations__item org-item org-' . esc_attr( 'compact' === $layout ? 'compact' : ( 'list' === $layout ? 'list-item' : 'card' ) ) . '">';
            if ( 'cards' === $layout && '' !== $org->logo_url() ) {
                $html .= '<img class="sfpf-organizations__logo org-logo" src="' . esc_url( $org->logo_url() ) . '" alt="' . esc_attr( $org->name() ) . '" loading="lazy">';
            }
            $html .= '<div class="sfpf-organizations__body"><a class="sfpf-organizations__name org-name" href="' . esc_url( $org->permalink() ) . '">' . esc_html( $org->name() ) . '</a>';
            $html .= [] !== $facts ? '<span class="sfpf-organizations__facts org-meta">' . esc_html( implode( ' · ', $facts ) ) . '</span>' : '';
            if ( 'compact' !== $layout ) {
                $summary = $org->summary_html();
                $html   .= '' !== trim( wp_strip_all_tags( $summary ) ) ? '<div class="sfpf-organizations__summary org-summary">' . wpautop( $summary ) . '</div>' : '';
                $html   .= '' !== $org->website() ? '<a class="sfpf-organizations__website org-url" href="' . esc_url( $org->website() ) . '" target="_blank" rel="noopener">' . esc_html( Links::pretty( $org->website() ) ) . '</a>' : '';
            }
            $html .= '</div></li>';
        }
        return $html . '</ul>';
    }
}
