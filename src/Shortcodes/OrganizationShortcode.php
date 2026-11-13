<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Shortcodes;

use SFPF\PersonProfile\ContentTypes\Organization\OrganizationRecord;
use SFPF\PersonProfile\Person\FieldValue;
use SFPF\PersonProfile\Shortcodes\Sections\GalleryGrid;

defined( 'ABSPATH' ) || exit;

/**
 * [organization field="url"] or [organization section="profile"]
 *
 * One value of an organization, or one of its blocks (profile, gallery): the
 * organization whose page is open, the one named with id, else the primary
 * organization. Older forms still work: [organization id="url"] and
 * [organization action="display_profile"].
 */
final class OrganizationShortcode {
    public const TAG = 'organization';

    /** @param array<string,string>|string $atts */
    public static function render( mixed $atts ): string {
        $atts = Attributes::parse( $atts, [ 'field' => '', 'id' => '', 'post_id' => '', 'section' => '', 'action' => '', 'size' => 'large', 'columns' => '3' ], self::TAG );
        // [organization id="url" post_id="12"] is the older field syntax.
        if ( '' === $atts['field'] && '' !== $atts['id'] && ! is_numeric( $atts['id'] ) ) {
            $atts['field'] = $atts['id'];
            $atts['id']    = $atts['post_id'];
        }
        $section = '' !== $atts['section'] ? $atts['section'] : [ 'display_profile' => 'profile', 'display_gallery' => 'gallery' ][ $atts['action'] ] ?? '';
        $org     = OrganizationRecord::resolve( absint( '' !== $atts['id'] ? $atts['id'] : $atts['post_id'] ) );
        if ( null === $org ) {
            return esc_html( $atts['fallback'] );
        }
        if ( 'profile' === $section ) {
            return self::profile( $org, $atts );
        }
        $field = 'gallery' === $section ? 'gallery' : sanitize_key( $atts['field'] );
        if ( 'gallery' === $field && in_array( $atts['format'], [ 'auto', 'html' ], true ) ) {
            return self::gallery( $org, $atts );
        }
        return '' === $field ? '' : Output::value( self::value( $org, $field, $atts ), $atts );
    }

    /** @param array<string,string> $atts */
    public static function value( OrganizationRecord $org, string $field, array $atts ): FieldValue {
        $size = in_array( $atts['size'] ?? '', get_intermediate_image_sizes(), true ) ? (string) $atts['size'] : 'large';
        return match ( $field ) {
            'title', 'name'          => FieldValue::text( $org->name() ),
            'subtitle'               => FieldValue::text( (string) $org->field( 'sub_title' ) ),
            'url', 'website'         => FieldValue::url( $org->website() ),
            'headquarters_location'  => FieldValue::text( $org->headquarters()['location'] ),
            'headquarters_wikipedia' => FieldValue::url( $org->headquarters()['wikipedia_url'] ),
            'logo', 'image_cropped'  => FieldValue::url( $org->logo_url( $size ) ),
            'featured_image', 'featured_image_url' => FieldValue::url( (string) get_the_post_thumbnail_url( $org->id, $size ) ),
            'permalink'              => FieldValue::url( $org->permalink(), $org->name() ),
            'gallery'                => FieldValue::urls( array_column( $org->gallery( $size ), 'url' ), $org->gallery( $size ) ),
            default                  => BookShortcode::stored( $org->field( $field ) ),
        };
    }

    /** @param array<string,string> $atts */
    private static function profile( OrganizationRecord $org, array $atts ): string {
        if ( Attributes::styled( $atts ) ) {
            SectionStyles::enqueue();
        }
        $logo    = $org->logo_url( in_array( $atts['size'], get_intermediate_image_sizes(), true ) ? $atts['size'] : 'medium' );
        $summary = $org->summary_html();
        $html    = '<article class="' . esc_attr( trim( 'sfpf-section sfpf-organization-profile ' . Attributes::classes( $atts ) ) ) . '">';
        $html   .= '' !== $logo ? '<img class="sfpf-organization-profile__logo" src="' . esc_url( $logo ) . '" alt="' . esc_attr( $org->name() ) . '">' : '';
        $html   .= '<h2 class="sfpf-organization-profile__name">' . esc_html( $org->name() ) . '</h2>';
        $html   .= '' !== trim( wp_strip_all_tags( $summary ) ) ? '<div class="sfpf-organization-profile__summary">' . wpautop( $summary ) . '</div>' : '';
        $html   .= '' !== $org->website() ? '<p><a href="' . esc_url( $org->website() ) . '" target="_blank" rel="noopener">' . esc_html( (string) preg_replace( '#^https?://#i', '', rtrim( $org->website(), '/' ) ) ) . '</a></p>' : '';
        return $html . '</article>';
    }

    /** @param array<string,string> $atts */
    private static function gallery( OrganizationRecord $org, array $atts ): string {
        $images = $org->gallery( in_array( $atts['size'], get_intermediate_image_sizes(), true ) ? $atts['size'] : 'large' );
        if ( [] === $images ) {
            return '';
        }
        if ( Attributes::styled( $atts ) ) {
            SectionStyles::enqueue();
        }
        return '<div class="' . esc_attr( trim( 'sfpf-section sfpf-gallery sfpf-organization-gallery ' . Attributes::classes( $atts ) ) ) . '">' . GalleryGrid::render( $images, absint( $atts['columns'] ), $org->name() ) . '</div>';
    }
}
