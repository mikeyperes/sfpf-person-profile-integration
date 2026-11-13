<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Elementor\Tags;

use SFPF\PersonProfile\Elementor\ElementorIntegration;
use SFPF\PersonProfile\Person\PersonFields;
use SFPF\PersonProfile\Person\PersonResolver;

defined( 'ABSPATH' ) || exit;

/** A person link for any Elementor link field: a social profile, the website, email or the Knowledge Panel. */
final class PersonUrlTag extends \Elementor\Core\DynamicTags\Data_Tag {
    public function get_name(): string {
        return 'sfpf-person-url';
    }

    public function get_title(): string {
        return 'Person link';
    }

    public function get_group(): string {
        return ElementorIntegration::TAG_GROUP;
    }

    public function get_categories(): array {
        return [ 'url' ];
    }

    protected function register_controls(): void {
        $options = [ 'email' => 'Email (mailto)', 'phone' => 'Phone (tel)', 'knowledge_panel' => 'Knowledge Panel', 'birthplace' => 'Birthplace Wikipedia page' ];
        foreach ( PersonFields::SOCIAL_NETWORKS as $network => $label ) {
            $options[ 'social:' . $network ] = $label;
        }
        $this->add_control( 'link', [ 'label' => 'Link', 'type' => \Elementor\Controls_Manager::SELECT, 'options' => $options, 'default' => 'social:linkedin' ] );
    }

    /** @param array<string,mixed> $options */
    public function get_value( array $options = [] ): string {
        $profile = PersonResolver::profile();
        if ( null === $profile ) {
            return '';
        }
        $link = (string) $this->get_settings( 'link' );
        return match ( true ) {
            'email' === $link           => '' !== $profile->public_email() ? 'mailto:' . $profile->public_email() : '',
            'phone' === $link           => '' !== $profile->public_phone() ? 'tel:' . preg_replace( '/[^0-9+]/', '', $profile->public_phone() ) : '',
            'knowledge_panel' === $link => $profile->knowledge_panel_url(),
            'birthplace' === $link      => $profile->birth_place()['wikipedia_url'],
            str_starts_with( $link, 'social:' ) => 'website' === substr( $link, 7 ) ? $profile->website() : $profile->social_url( substr( $link, 7 ) ),
            default                     => '',
        };
    }
}
