<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Elementor\Tags;

use SFPF\PersonProfile\Elementor\ElementorIntegration;
use SFPF\PersonProfile\Person\PersonResolver;

defined( 'ABSPATH' ) || exit;

/** The person's portrait, or one of their photos, for any Elementor image field. */
final class PersonImageTag extends \Elementor\Core\DynamicTags\Data_Tag {
    public function get_name(): string {
        return 'sfpf-person-image';
    }

    public function get_title(): string {
        return 'Person photo';
    }

    public function get_group(): string {
        return ElementorIntegration::TAG_GROUP;
    }

    public function get_categories(): array {
        return [ 'image' ];
    }

    protected function register_controls(): void {
        $this->add_control( 'photo', [ 'label' => 'Photo', 'type' => \Elementor\Controls_Manager::NUMBER, 'min' => 0, 'default' => 0, 'description' => '0 is the portrait; 1, 2… are the photos in the gallery\'s order.' ] );
    }

    /** @param array<string,mixed> $options @return array{id:int,url:string} */
    public function get_value( array $options = [] ): array {
        $profile = PersonResolver::profile();
        if ( null === $profile ) {
            return [ 'id' => 0, 'url' => '' ];
        }
        $index = absint( $this->get_settings( 'photo' ) );
        if ( $index > 0 ) {
            $image = $profile->gallery()[ $index - 1 ] ?? null;
            if ( is_array( $image ) ) {
                return [ 'id' => (int) $image['id'], 'url' => (string) $image['url'] ];
            }
        }
        return [ 'id' => $profile->avatar_attachment_id(), 'url' => $profile->avatar_url( 512 ) ];
    }
}
