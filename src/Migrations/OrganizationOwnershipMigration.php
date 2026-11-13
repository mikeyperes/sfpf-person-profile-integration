<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Migrations;

use SFPF\PersonProfile\Settings\Settings;

defined( 'ABSPATH' ) || exit;

/**
 * The Organization content type moved from HWS Base Tools to SFPF (3.1): its
 * on/off state, labels, address and field group switch are copied into
 * SFPF's content type settings. HWS Base Tools keeps the Testimonial type.
 */
final class OrganizationOwnershipMigration implements Migration {
    private const LEGACY_MARKER = 'sfpf_shared_content_type_ownership_migration';

    public function id(): string {
        return 'organization-ownership-3.1';
    }

    public function migrate(): bool {
        if ( '3.1.0' === get_option( self::LEGACY_MARKER, '' ) ) {
            return true;
        }
        if ( get_option( 'sfpf_enable_testimonial_cpt', false ) ) {
            update_option( 'enable_cpt_testimonial', 1, false );
        }
        $settings = Settings::map( Settings::CONTENT_TYPES );
        if ( ! isset( $settings['organization'] ) || ! is_array( $settings['organization'] ) ) {
            $hws          = (array) ( Settings::map( 'hws_content_type_settings' )['organization'] ?? [] );
            $standalone   = Settings::map( Settings::FIELD_GROUPS );
            $legacy_acf   = get_option( 'sfpf_enable_organization_acf', null );
            $legacy_acf   = null === $legacy_acf ? get_option( 'smp_enable_acf_organization', false ) : $legacy_acf;
            $fields_on    = array_key_exists( 'legacy-organization-profile', $standalone ) ? (bool) $standalone['legacy-organization-profile'] : (bool) $legacy_acf;
            $settings['organization'] = [
                'enabled'      => array_key_exists( 'enabled', $hws ) ? ( ! empty( $hws['enabled'] ) ? 1 : 0 ) : ( get_option( 'smp_enable_cpt_organization', get_option( 'sfpf_enable_organization_cpt', false ) ) ? 1 : 0 ),
                'singular'     => sanitize_text_field( (string) ( $hws['singular'] ?? 'Organization' ) ) ?: 'Organization',
                'plural'       => sanitize_text_field( (string) ( $hws['plural'] ?? 'Organizations' ) ) ?: 'Organizations',
                'rewrite_slug' => sanitize_title( (string) ( $hws['rewrite_slug'] ?? 'organization' ) ) ?: 'organization',
                'field_groups' => [ 'organization-details' => $fields_on ? 1 : 0 ],
            ];
            update_option( Settings::CONTENT_TYPES, $settings, false );
        }
        update_option( self::LEGACY_MARKER, '3.1.0', false );
        return true;
    }
}
