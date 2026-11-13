<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Shortcodes\Sections;

use SFPF\PersonProfile\Person\PersonProfile;

defined( 'ABSPATH' ) || exit;

/**
 * The whole profile on one page: biography, other names, education,
 * birthplace, organizations, professions and social profiles, each with its
 * heading and only when it has content. parts="biography,education" picks and
 * orders them.
 */
final class ProfileSection implements Section {
    private const PARTS = [
        'biography'       => 'Biography',
        'alternate_names' => 'Also Known As',
        'education'       => 'Education',
        'birthplace'      => 'Birthplace',
        'organizations'   => 'Organizations Founded',
        'professions'     => 'Professions',
        'socials'         => 'Connect',
    ];

    public function id(): string {
        return 'profile';
    }

    public function label(): string {
        return 'Full profile';
    }

    public function layouts(): array {
        return [ 'sections' => 'One headed block per part' ];
    }

    public function attributes(): array {
        return [ 'parts' => implode( ',', array_keys( self::PARTS ) ), 'part_heading_tag' => 'h3' ];
    }

    public function has_content( PersonProfile $profile ): bool {
        foreach ( array_keys( self::PARTS ) as $part ) {
            if ( '' !== $this->part( $profile, $part ) ) {
                return true;
            }
        }
        return false;
    }

    public function render( PersonProfile $profile, array $atts ): string {
        $tag  = in_array( $atts['part_heading_tag'] ?? 'h3', [ 'h2', 'h3', 'h4', 'h5', 'h6' ], true ) ? (string) $atts['part_heading_tag'] : 'h3';
        $html = '';
        foreach ( array_filter( array_map( 'sanitize_key', explode( ',', (string) ( $atts['parts'] ?? '' ) ) ) ) as $part ) {
            $body = isset( self::PARTS[ $part ] ) ? $this->part( $profile, $part ) : '';
            if ( '' !== $body ) {
                $html .= '<div class="sfpf-profile__part sfpf-profile__part--' . esc_attr( $part ) . ' bio-section bio-section-' . esc_attr( str_replace( '_', '-', $part ) ) . '"><' . $tag . ' class="sfpf-profile__heading">' . esc_html( self::PARTS[ $part ] ) . '</' . $tag . '><div class="sfpf-profile__body bio-content">' . $body . '</div></div>';
            }
        }
        return $html;
    }

    private function part( PersonProfile $profile, string $part ): string {
        if ( 'alternate_names' === $part ) {
            return [] !== $profile->alternate_names() ? esc_html( implode( ', ', $profile->alternate_names() ) ) : '';
        }
        $section = SectionRegistry::get( $part );
        return null !== $section && $section->has_content( $profile ) ? $section->render( $profile, [] ) : '';
    }
}
