<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Person;

use Hexa\PluginCore\DataNormalization\ValueNormalizer;

defined( 'ABSPATH' ) || exit;

/**
 * Every person field a shortcode or Elementor tag can show, by its public
 * name, with its label and kind. Any other stored field name still works and
 * prints as it is stored.
 */
final class PersonFieldValues {
    /** Public field name => [label, group]. */
    public const FIELDS = [
        'name'                   => [ 'Full name', 'Identity' ],
        'first_name'             => [ 'First name', 'Identity' ],
        'last_name'              => [ 'Last name', 'Identity' ],
        'display_name'           => [ 'Display name', 'Identity' ],
        'additional_name'        => [ 'Additional name', 'Identity' ],
        'alternate_names'        => [ 'Alternate names', 'Identity' ],
        'honorific_prefix'       => [ 'Honorific prefix', 'Identity' ],
        'honorific_suffix'       => [ 'Honorific suffix', 'Identity' ],
        'title'                  => [ 'Title or role', 'About' ],
        'biography'              => [ 'Biography', 'About' ],
        'biography_short'        => [ 'Short biography', 'About' ],
        'mission_statement'      => [ 'Mission statement', 'About' ],
        'professions'            => [ 'Professions', 'About' ],
        'education'              => [ 'Education (schools)', 'About' ],
        'awards'                 => [ 'Awards', 'About' ],
        'languages'              => [ 'Languages', 'About' ],
        'birth_date'             => [ 'Birth date', 'Birth' ],
        'birthplace'             => [ 'Birthplace', 'Birth' ],
        'birthplace_url'         => [ 'Birthplace Wikipedia link', 'Birth' ],
        'nationality'            => [ 'Nationality', 'Birth' ],
        'gender'                 => [ 'Gender', 'Birth' ],
        'email'                  => [ 'Public email', 'Contact' ],
        'phone'                  => [ 'Public phone', 'Contact' ],
        'telephone'              => [ 'Telephone', 'Contact' ],
        'website'                => [ 'Website', 'Contact' ],
        'social'                 => [ 'A social profile link (network="linkedin")', 'Links' ],
        'same_as'                => [ 'Profile links', 'Links' ],
        'articles'               => [ 'Article links', 'Links' ],
        'additional_urls'        => [ 'Additional links', 'Links' ],
        'knowledge_graph_id'     => [ 'Knowledge Graph ID', 'Links' ],
        'knowledge_panel_url'    => [ 'Knowledge Panel link', 'Links' ],
        'avatar'                 => [ 'Portrait image link', 'Images' ],
        'gallery'                => [ 'Photo links', 'Images' ],
        'knowledge_graph_images' => [ 'Knowledge Panel photo links', 'Images' ],
        'wikimedia_photos'       => [ 'Wikimedia Commons photo links', 'Images' ],
        'faq'                    => [ 'FAQ questions', 'FAQ' ],
    ];

    /**
     * @param array<string,string> $options network (for social), size (for avatar and images)
     */
    public static function get( PersonProfile $profile, string $field, array $options = [] ): FieldValue {
        $size = (string) ( $options['size'] ?? '' );
        return match ( $field ) {
            'name'                   => FieldValue::text( $profile->name() ),
            'first_name'             => FieldValue::text( $profile->first_name() ),
            'last_name'              => FieldValue::text( $profile->last_name() ),
            'display_name'           => FieldValue::text( $profile->display_name() ),
            'additional_name'        => FieldValue::text( $profile->additional_name() ),
            'alternate_names'        => FieldValue::items( $profile->alternate_names() ),
            'honorific_prefix'       => FieldValue::text( $profile->honorific_prefix() ),
            'honorific_suffix'       => FieldValue::text( $profile->honorific_suffix() ),
            'title'                  => FieldValue::html( $profile->title_html() ),
            'biography'              => FieldValue::html( $profile->biography_html() ),
            'biography_short'        => FieldValue::html( $profile->biography_short_html() ),
            'mission_statement'      => FieldValue::html( $profile->mission_html() ),
            'professions'            => FieldValue::items( array_column( $profile->professions(), 'name' ), $profile->professions() ),
            'education'              => FieldValue::items( array_column( $profile->education(), 'school' ), $profile->education() ),
            'awards'                 => FieldValue::items( $profile->awards() ),
            'languages'              => FieldValue::items( $profile->languages() ),
            'birth_date'             => FieldValue::text( $profile->birth_date() ),
            'birthplace'             => FieldValue::text( $profile->birth_place()['location'] ),
            'birthplace_url'         => FieldValue::url( $profile->birth_place()['wikipedia_url'], $profile->birth_place()['location'] ),
            'nationality'            => FieldValue::items( $profile->nationality() ),
            'gender'                 => FieldValue::text( $profile->gender() ),
            'email'                  => FieldValue::email( $profile->public_email() ),
            'phone'                  => FieldValue::text( $profile->public_phone() ),
            'telephone'              => FieldValue::text( $profile->telephone() ),
            'website'                => FieldValue::url( $profile->website() ),
            'social'                 => FieldValue::url( $profile->social_url( (string) ( $options['network'] ?? '' ) ), PersonFields::SOCIAL_NETWORKS[ sanitize_key( (string) ( $options['network'] ?? '' ) ) ] ?? '' ),
            'same_as'                => FieldValue::urls( $profile->same_as() ),
            'articles'               => FieldValue::urls( array_column( $profile->articles(), 'url' ), $profile->articles() ),
            'additional_urls'        => FieldValue::urls( array_column( $profile->additional_urls(), 'url' ), $profile->additional_urls() ),
            'knowledge_graph_id'     => FieldValue::text( $profile->knowledge_graph_id() ),
            'knowledge_panel_url'    => FieldValue::url( $profile->knowledge_panel_url() ),
            'avatar'                 => FieldValue::url( $profile->avatar_url( is_numeric( $size ) ? (int) $size : 512 ) ),
            'gallery'                => self::images( $profile->gallery( self::image_size( $size ) ) ),
            'knowledge_graph_images' => self::images( $profile->knowledge_graph_images( self::image_size( $size ) ) ),
            'wikimedia_photos'       => FieldValue::urls( $profile->wikimedia_photo_urls() ),
            'faq'                    => FieldValue::items( array_column( $profile->faq(), 'question' ), $profile->faq() ),
            default                  => self::stored( $profile, $field ),
        };
    }

    /** @param list<array<string,mixed>> $images */
    private static function images( array $images ): FieldValue {
        return FieldValue::urls( array_column( $images, 'url' ), $images );
    }

    private static function image_size( string $size ): string {
        $size = sanitize_key( $size );
        return in_array( $size, get_intermediate_image_sizes(), true ) ? $size : 'large';
    }

    /** A field this list does not name, read as stored. */
    private static function stored( PersonProfile $profile, string $field ): FieldValue {
        if ( 1 === preg_match( '/^url_([a-z0-9_]+)$/', $field, $match ) ) {
            return FieldValue::url( $profile->social_url( $match[1] ) );
        }
        $value = '' !== $field ? $profile->field( sanitize_key( $field ) ) : null;
        if ( is_array( $value ) ) {
            return FieldValue::items( ValueNormalizer::strings( $value, [ 'value', 'name', 'url' ] ), ValueNormalizer::rows( $value ), $value );
        }
        return is_scalar( $value ) ? FieldValue::html( wp_kses_post( (string) $value ) ) : FieldValue::none();
    }
}
