<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Shortcodes\Sections;

use Hexa\PluginCore\DataNormalization\FieldReader;
use Hexa\PluginCore\DataNormalization\ValueNormalizer;
use SFPF\PersonProfile\Person\PersonFields;
use SFPF\PersonProfile\Person\PersonProfile;

defined( 'ABSPATH' ) || exit;

/**
 * The person's social profiles as a list of named links: the profile links
 * on the person's user, or the website's own social links with source="site".
 * networks="linkedin,x" limits and orders them.
 */
final class SocialsSection implements Section {
    public function id(): string {
        return 'socials';
    }

    public function label(): string {
        return 'Social profiles';
    }

    public function layouts(): array {
        return [ 'list' => 'A list of named links', 'inline' => 'Named links on one line' ];
    }

    public function attributes(): array {
        return [ 'source' => 'person', 'networks' => '' ];
    }

    public function has_content( PersonProfile $profile ): bool {
        return [] !== self::links( $profile, [] );
    }

    public function render( PersonProfile $profile, array $atts ): string {
        $links = self::links( $profile, $atts );
        if ( [] === $links ) {
            return '';
        }
        $html = '<ul class="sfpf-socials__list social-list">';
        foreach ( $links as $network => $url ) {
            $html .= '<li class="sfpf-socials__item social-item ' . esc_attr( $network ) . '"><a class="sfpf-socials__link social-link" href="' . esc_url( $url ) . '" target="_blank" rel="noopener me">' . esc_html( PersonFields::SOCIAL_NETWORKS[ $network ] ?? ucwords( str_replace( '_', ' ', $network ) ) ) . '</a></li>';
        }
        return $html . '</ul>';
    }

    /**
     * @param array<string,string> $atts
     * @return array<string,string> Network => URL.
     */
    public static function links( PersonProfile $profile, array $atts ): array {
        $links = 'site' === ( $atts['source'] ?? 'person' ) ? [] : $profile->social_urls();
        unset( $links['wikidata'] );
        if ( [] === $links ) {
            $website = FieldReader::acf_value( 'website', 'option', [] );
            foreach ( is_array( $website ) ? (array) ( $website['social_media'] ?? [] ) : [] as $network => $url ) {
                $url = ValueNormalizer::url( is_scalar( $url ) ? (string) $url : '' );
                if ( '' !== $url && is_string( $network ) ) {
                    $links[ sanitize_key( $network ) ] = $url;
                }
            }
        }
        $only = array_filter( array_map( 'sanitize_key', explode( ',', (string) ( $atts['networks'] ?? '' ) ) ) );
        if ( [] !== $only ) {
            $links = array_filter( array_replace( array_fill_keys( $only, '' ), array_intersect_key( $links, array_flip( $only ) ) ) );
        }
        return $links;
    }
}
