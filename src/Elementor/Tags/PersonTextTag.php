<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Elementor\Tags;

use SFPF\PersonProfile\Elementor\ElementorIntegration;
use SFPF\PersonProfile\Person\FieldValue;
use SFPF\PersonProfile\Person\PersonFieldValues;
use SFPF\PersonProfile\Person\PersonResolver;

defined( 'ABSPATH' ) || exit;

/** Any person value as text: a name, the title, the biography, a list. */
final class PersonTextTag extends \Elementor\Core\DynamicTags\Tag {
    public function get_name(): string {
        return 'sfpf-person-text';
    }

    public function get_title(): string {
        return 'Person value';
    }

    public function get_group(): string {
        return ElementorIntegration::TAG_GROUP;
    }

    public function get_categories(): array {
        return [ 'text' ];
    }

    protected function register_controls(): void {
        $options = [];
        foreach ( PersonFieldValues::FIELDS as $field => [ $label, $group ] ) {
            $options[ $field ] = $group . ': ' . $label;
        }
        $this->add_control( 'field', [ 'label' => 'Value', 'type' => \Elementor\Controls_Manager::SELECT, 'options' => $options, 'default' => 'name' ] );
        $this->add_control( 'network', [ 'label' => 'Network', 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'linkedin', 'condition' => [ 'field' => 'social' ] ] );
        $this->add_control( 'separator', [ 'label' => 'Between items', 'type' => \Elementor\Controls_Manager::TEXT, 'default' => ', ' ] );
    }

    public function render(): void {
        $profile = PersonResolver::profile();
        if ( null === $profile ) {
            return;
        }
        $value = PersonFieldValues::get( $profile, sanitize_key( (string) $this->get_settings( 'field' ) ), [ 'network' => (string) $this->get_settings( 'network' ) ] );
        echo FieldValue::HTML === $value->type ? wp_kses_post( (string) $value->value ) : esc_html( $value->plain( (string) $this->get_settings( 'separator' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by type.
    }
}
