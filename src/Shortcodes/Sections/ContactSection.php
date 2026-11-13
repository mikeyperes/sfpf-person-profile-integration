<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Shortcodes\Sections;

use SFPF\PersonProfile\Person\Links;
use SFPF\PersonProfile\Person\PersonProfile;

defined( 'ABSPATH' ) || exit;

/** How to reach the person: public email, phone and website, then their social profiles. */
final class ContactSection implements Section {
    public function id(): string {
        return 'contact';
    }

    public function label(): string {
        return 'Contact details';
    }

    public function layouts(): array {
        return [ 'list' => 'Labelled lines' ];
    }

    public function attributes(): array {
        return [ 'socials' => 'true' ];
    }

    public function has_content( PersonProfile $profile ): bool {
        return '' !== $profile->public_email() || '' !== $profile->public_phone() || '' !== $profile->website() || [] !== SocialsSection::links( $profile, [] );
    }

    public function render( PersonProfile $profile, array $atts ): string {
        $rows = array_filter(
            [
                'Email'   => '' !== $profile->public_email() ? '<a href="' . esc_url( 'mailto:' . $profile->public_email() ) . '">' . esc_html( $profile->public_email() ) . '</a>' : '',
                'Phone'   => '' !== $profile->public_phone() ? '<a href="' . esc_url( 'tel:' . preg_replace( '/[^0-9+]/', '', $profile->public_phone() ) ) . '">' . esc_html( $profile->public_phone() ) . '</a>' : '',
                'Website' => '' !== $profile->website() ? '<a href="' . esc_url( $profile->website() ) . '" target="_blank" rel="noopener me">' . esc_html( Links::pretty( $profile->website() ) ) . '</a>' : '',
            ]
        );
        $html = '<dl class="sfpf-contact__list">';
        foreach ( $rows as $label => $value ) {
            $html .= '<div class="sfpf-contact__row"><dt>' . esc_html( $label ) . '</dt><dd>' . $value . '</dd></div>';
        }
        $html .= '</dl>';
        if ( ! in_array( strtolower( (string) ( $atts['socials'] ?? 'true' ) ), [ '0', 'false', 'no' ], true ) ) {
            $html .= ( new SocialsSection() )->render( $profile, [] );
        }
        return $html;
    }
}
