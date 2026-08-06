<?php

declare( strict_types=1 );

namespace sfpf_person_website;

use SMC\OrganizationProfile\Compatibility\SfpfOrganizationAdapter;
use SMC\OrganizationProfile\Presentation\FrontendAssets;
use SMC\OrganizationProfile\Shortcodes\OrganizationShortcode;

defined( 'ABSPATH' ) || exit;

/**
 * Legacy SFPF Organization callbacks.
 *
 * Organization fields, presentation, shortcode registration, and schema are
 * owned by SMC. These functions remain only for templates or integrations that
 * called the historical SFPF callbacks directly.
 */

function sfpf_register_organization_profile_assets(): void {
    if ( class_exists( FrontendAssets::class ) ) {
        FrontendAssets::enqueue();
    }
}

function sfpf_resolve_organization_id( $requested_id = '' ): int {
    return class_exists( SfpfOrganizationAdapter::class )
        ? SfpfOrganizationAdapter::resolve_id( $requested_id )
        : 0;
}

function sfpf_get_organization_field( $field, $org_id ): mixed {
    return class_exists( SfpfOrganizationAdapter::class )
        ? SfpfOrganizationAdapter::field( (string) $field, absint( $org_id ) )
        : null;
}

function sfpf_render_organization_profile( $org_id, $atts = [] ): string {
    $org_id = absint( $org_id );
    if ( $org_id <= 0 || ! class_exists( OrganizationShortcode::class ) ) {
        return '';
    }

    $attributes = is_array( $atts ) ? $atts : [];
    $attributes['id'] = $org_id;
    $attributes['action'] = 'display_profile';

    return OrganizationShortcode::render( $attributes );
}

function organization_shortcode( $atts ): string {
    return class_exists( OrganizationShortcode::class )
        ? OrganizationShortcode::render( is_array( $atts ) ? $atts : [], null, 'organization' )
        : '';
}
