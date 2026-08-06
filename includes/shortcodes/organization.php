<?php

declare( strict_types=1 );

namespace sfpf_person_website;

defined( 'ABSPATH' ) || exit;

/**
 * Organization field and gallery shortcode rendering.
 *
 * Callback names remain in the legacy namespace for template compatibility.
 */

function sfpf_register_organization_profile_assets(): void {
    // Reserved for compatibility with templates that called the old asset hook.
}

function sfpf_resolve_organization_id( $requested_id = '' ): int {
    $requested_id = absint( $requested_id );
    if ( $requested_id > 0 ) {
        return 'organization' === get_post_type( $requested_id ) ? $requested_id : 0;
    }

    $current_id = function_exists( 'get_queried_object_id' ) ? absint( get_queried_object_id() ) : 0;
    if ( $current_id > 0 && 'organization' === get_post_type( $current_id ) ) {
        return $current_id;
    }

    $organization = function_exists( __NAMESPACE__ . '\\get_primary_organization' )
        ? get_primary_organization()
        : null;

    return is_object( $organization ) && isset( $organization->ID )
        && 'organization' === get_post_type( (int) $organization->ID )
            ? (int) $organization->ID
            : 0;
}

function sfpf_get_organization_field( $field, $org_id ): mixed {
    $org_id = absint( $org_id );
    $field  = sanitize_key( (string) $field );
    if ( $org_id <= 0 || '' === $field ) {
        return null;
    }

    if ( function_exists( 'get_field' ) ) {
        return get_field( $field, $org_id );
    }

    return get_post_meta( $org_id, $field, true );
}

function sfpf_organization_image_url( mixed $image, string $size = 'full' ): string {
    $size = sanitize_key( $size ) ?: 'full';

    if ( is_array( $image ) ) {
        $sizes = is_array( $image['sizes'] ?? null ) ? $image['sizes'] : [];
        $sized_url = $sizes[ $size ] ?? '';
        if ( is_string( $sized_url ) && '' !== $sized_url ) {
            return $sized_url;
        }

        $url = $image['url'] ?? '';
        if ( is_string( $url ) && '' !== $url ) {
            return $url;
        }

        $image = $image['ID'] ?? $image['id'] ?? 0;
    }

    if ( is_string( $image ) && filter_var( $image, FILTER_VALIDATE_URL ) ) {
        return $image;
    }

    $attachment_id = absint( $image );
    if ( $attachment_id <= 0 ) {
        return '';
    }

    $url = wp_get_attachment_image_url( $attachment_id, $size );
    return is_string( $url ) ? $url : '';
}

function sfpf_render_organization_profile( $org_id, $atts = [] ): string {
    $org_id = sfpf_resolve_organization_id( $org_id );
    if ( $org_id <= 0 ) {
        return '';
    }

    $atts = is_array( $atts ) ? $atts : [];
    $logo = sfpf_organization_image_url(
        sfpf_get_organization_field( 'image_cropped', $org_id ),
        (string) ( $atts['size'] ?? 'large' )
    );
    $summary = sfpf_get_organization_field( 'short_summary', $org_id );
    $website = sfpf_get_organization_field( 'url', $org_id );

    $html = '<article class="sfpf-organization-profile">';
    if ( '' !== $logo ) {
        $html .= '<img class="sfpf-organization-profile__logo" src="' . esc_url( $logo ) . '" alt="' . esc_attr( get_the_title( $org_id ) ) . '">';
    }
    $html .= '<h2>' . esc_html( get_the_title( $org_id ) ) . '</h2>';
    if ( is_scalar( $summary ) && '' !== trim( (string) $summary ) ) {
        $html .= '<div class="sfpf-organization-profile__summary">' . wp_kses_post( (string) $summary ) . '</div>';
    }
    if ( is_string( $website ) && filter_var( $website, FILTER_VALIDATE_URL ) ) {
        $html .= '<p><a href="' . esc_url( $website ) . '">' . esc_html( preg_replace( '#^https?://#i', '', $website ) ) . '</a></p>';
    }
    $html .= '</article>';

    return $html;
}

/**
 * Render Organization data.
 *
 * Canonical syntax: [organization field="url" id="123"]
 * Legacy Code Snippets syntax remains accepted: [organization id="url" post_id="123"]
 */
function organization_shortcode( $atts ): string {
    $atts = shortcode_atts(
        [
            'field'   => '',
            'id'      => '',
            'post_id' => '',
            'link'    => 'false',
            'target'  => '',
            'pretty'  => 'false',
            'format'  => 'html',
            'size'    => 'large',
            'columns' => '3',
            'action'  => '',
        ],
        is_array( $atts ) ? $atts : [],
        'organization'
    );

    $field = sanitize_key( (string) $atts['field'] );
    $requested_id = $atts['post_id'];

    if ( '' === $field && '' !== (string) $atts['id'] && ! is_numeric( $atts['id'] ) ) {
        $field = sanitize_key( (string) $atts['id'] );
    } elseif ( '' === (string) $requested_id ) {
        $requested_id = $atts['id'];
    }

    $org_id = sfpf_resolve_organization_id( $requested_id );
    if ( $org_id <= 0 ) {
        return '';
    }

    $action = sanitize_key( (string) $atts['action'] );
    if ( 'display_profile' === $action ) {
        return sfpf_render_organization_profile( $org_id, $atts );
    }

    if ( 'display_gallery' === $action ) {
        $field = 'gallery';
    }

    if ( '' === $field ) {
        return '';
    }

    $value = null;
    switch ( $field ) {
        case 'title':
        case 'name':
            $value = get_the_title( $org_id );
            break;

        case 'subtitle':
            $value = sfpf_get_organization_field( 'sub_title', $org_id );
            break;

        case 'headquarters_location':
        case 'headquarters_wikipedia':
            $headquarters = sfpf_get_organization_field( 'headquarters', $org_id );
            $headquarters = is_array( $headquarters ) ? $headquarters : [];
            $value = 'headquarters_location' === $field
                ? ( $headquarters['location'] ?? '' )
                : ( $headquarters['wikipedia_url'] ?? '' );
            break;

        case 'logo':
        case 'image_cropped':
            $value = sfpf_organization_image_url(
                sfpf_get_organization_field( 'image_cropped', $org_id ),
                (string) $atts['size']
            );
            break;

        case 'featured_image':
        case 'featured_image_url':
            $value = get_the_post_thumbnail_url( $org_id, sanitize_key( (string) $atts['size'] ) ?: 'full' );
            break;

        case 'permalink':
            $value = get_permalink( $org_id );
            break;

        case 'gallery':
            $images = sfpf_normalize_gallery_images(
                sfpf_get_organization_field( 'gallery', $org_id ),
                (string) $atts['size']
            );
            if ( 'json' === $atts['format'] ) {
                return wp_json_encode( $images );
            }
            if ( 'urls' === $atts['format'] ) {
                return esc_html( implode( "\n", array_column( $images, 'url' ) ) );
            }
            if ( 'count' === $atts['format'] ) {
                return (string) count( $images );
            }
            return sfpf_render_gallery_html( $images, 'sfpf-organization-gallery', (int) $atts['columns'] );

        default:
            $value = sfpf_get_organization_field( $field, $org_id );
            break;
    }

    if ( null === $value || false === $value || '' === $value ) {
        return '';
    }

    if ( is_array( $value ) || is_object( $value ) ) {
        return 'json' === $atts['format'] ? wp_json_encode( $value ) : '';
    }

    $value = (string) $value;
    if ( filter_var( $value, FILTER_VALIDATE_URL ) ) {
        if ( function_exists( __NAMESPACE__ . '\\format_url_output' ) ) {
            return (string) format_url_output( $value, $atts );
        }

        $label = 'true' === $atts['pretty'] ? preg_replace( '#^https?://#i', '', $value ) : $value;
        if ( 'true' === $atts['link'] ) {
            $target = in_array( $atts['target'], [ '_blank', '_self', '_parent', '_top' ], true ) ? $atts['target'] : '';
            return '<a href="' . esc_url( $value ) . '"' . ( $target ? ' target="' . esc_attr( $target ) . '"' : '' ) . '>' . esc_html( $label ) . '</a>';
        }
        return esc_url( $value );
    }

    return wp_kses_post( $value );
}
