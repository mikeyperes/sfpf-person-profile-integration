<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Person;

use Hexa\PluginCore\DataNormalization\FieldReader;
use Hexa\PluginCore\DataNormalization\MediaNormalizer;
use Hexa\PluginCore\DataNormalization\ValueNormalizer;
use Hexa\PluginCore\FaqSets\FaqSourceResolver;
use Hexa\PluginCore\Fields\Field;

defined( 'ABSPATH' ) || exit;

/**
 * One person's profile, read from their WordPress user: every field the
 * shortcodes, schema, Elementor and admin screens use, normalized once. This
 * is the only class that reads the person's stored fields.
 */
final class PersonProfile {
    /** @var array<int,self> */
    private static array $instances = [];

    /** @var array<string,mixed> */
    private array $values = [];

    private function __construct( private readonly \WP_User $user ) {
    }

    public static function for_user( int $user_id ): ?self {
        if ( isset( self::$instances[ $user_id ] ) ) {
            return self::$instances[ $user_id ];
        }
        $user = get_userdata( $user_id );
        return $user instanceof \WP_User ? self::$instances[ $user_id ] = new self( $user ) : null;
    }

    public static function forget(): void {
        self::$instances = [];
    }

    public function user_id(): int {
        return (int) $this->user->ID;
    }

    /** The field context of the person's user, as stored fields address it. */
    public function context(): string {
        return 'user_' . $this->user_id();
    }

    public function field( string $name, mixed $default = null ): mixed {
        $key = 'field:' . $name;
        if ( ! array_key_exists( $key, $this->values ) ) {
            $value = Field::available() ? Field::get( $name, $this->context() ) : get_user_meta( $this->user_id(), $name, true );
            $this->values[ $key ] = ValueNormalizer::present( $value ) ? $value : null;
        }
        return $this->values[ $key ] ?? $default;
    }

    // Names and identity.

    public function display_name(): string {
        return rtrim( trim( (string) $this->user->display_name ), '.' );
    }

    public function first_name(): string {
        return trim( (string) $this->user->first_name );
    }

    public function last_name(): string {
        return trim( (string) $this->user->last_name );
    }

    public function name(): string {
        $name = trim( $this->first_name() . ' ' . $this->last_name() );
        return '' !== $name ? $name : $this->display_name();
    }

    public function additional_name(): string {
        return ValueNormalizer::text( $this->field( PersonFields::ADDITIONAL_NAME, '' ) );
    }

    /** @return list<string> */
    public function alternate_names(): array {
        return ValueNormalizer::strings( $this->field( PersonFields::ALTERNATE_NAMES, [] ), [ 'name', 'value' ] );
    }

    public function honorific_prefix(): string {
        return ValueNormalizer::text( $this->field( PersonFields::HONORIFIC_PREFIX, '' ) );
    }

    public function honorific_suffix(): string {
        return ValueNormalizer::text( $this->field( PersonFields::HONORIFIC_SUFFIX, '' ) );
    }

    public function entity_type(): string {
        return sanitize_key( (string) $this->field( PersonFields::ENTITY_TYPE, '' ) );
    }

    // Role and text.

    /** The professional title as plain text, falling back to HWS Base Tools' contact title. */
    public function title(): string {
        $title = ValueNormalizer::text( $this->field( PersonFields::TITLE, '' ) );
        return '' !== $title ? $title : ValueNormalizer::text( $this->contact( 'title' ) );
    }

    /**
     * The professional title with its basic formatting. The editor wraps a
     * one-line title in a paragraph; it is unwrapped so the title fits inside
     * a heading or a line of text.
     */
    public function title_html(): string {
        $html = trim( (string) $this->field( PersonFields::TITLE, '' ) );
        if ( preg_match( '~^<p[^>]*>((?:(?!</?p[\s>]).)*)</p>$~is', $html, $match ) ) {
            $html = trim( $match[1] );
        }
        return '' !== trim( wp_strip_all_tags( $html ) ) ? wp_kses_post( $html ) : esc_html( $this->title() );
    }

    /** The full biography: a site-wide override, the person's field, then the WordPress bio. */
    public function biography_html(): string {
        $founder  = FieldReader::acf_value( 'founder', 'option', [] );
        $override = is_array( $founder ) ? (string) ( $founder['biography'] ?? '' ) : '';
        foreach ( [ $override, (string) $this->field( PersonFields::BIOGRAPHY, '' ), (string) $this->user->description ] as $html ) {
            if ( '' !== trim( wp_strip_all_tags( $html ) ) ) {
                return wp_kses_post( $html );
            }
        }
        return '';
    }

    public function biography_short_html(): string {
        return wp_kses_post( (string) $this->field( PersonFields::BIOGRAPHY_SHORT, '' ) );
    }

    public function mission_html(): string {
        return wp_kses_post( (string) $this->field( PersonFields::MISSION, '' ) );
    }

    /** A plain-text summary for search engines: the short bio, else the start of the full one. */
    public function summary( int $characters = 500 ): string {
        $text = ValueNormalizer::text( $this->biography_short_html() );
        $text = '' !== $text ? $text : ValueNormalizer::text( $this->biography_html() );
        return function_exists( 'mb_substr' ) ? mb_substr( $text, 0, $characters ) : substr( $text, 0, $characters );
    }

    // Contact.

    public function email(): string {
        return sanitize_email( (string) $this->user->user_email );
    }

    /** The email to show and publish: the public one when set, else the account email. */
    public function public_email(): string {
        $email = sanitize_email( $this->contact( 'public_email' ) );
        return '' !== $email ? $email : $this->email();
    }

    public function public_phone(): string {
        $phone = ValueNormalizer::text( $this->contact( 'public_phone' ) );
        return '' !== $phone ? $phone : ValueNormalizer::text( $this->field( PersonFields::TELEPHONE, '' ) );
    }

    public function telephone(): string {
        $phone = ValueNormalizer::text( $this->field( PersonFields::TELEPHONE, '' ) );
        return '' !== $phone ? $phone : ValueNormalizer::text( $this->contact( 'public_phone' ) );
    }

    public function website(): string {
        $url = $this->social_url( 'website' );
        return '' !== $url ? $url : ValueNormalizer::url( (string) $this->user->user_url );
    }

    /** @return array<string,string> Network => URL, from HWS Base Tools' URLs group. */
    public function social_urls(): array {
        if ( ! array_key_exists( 'social', $this->values ) ) {
            $urls = [];
            foreach ( (array) $this->field( PersonFields::SOCIAL_URLS, [] ) as $network => $url ) {
                $url = ValueNormalizer::url( is_scalar( $url ) ? (string) $url : '' );
                if ( '' !== $url && is_string( $network ) ) {
                    $urls[ sanitize_key( $network ) ] = $url;
                }
            }
            $this->values['social'] = $urls;
        }
        return $this->values['social'];
    }

    public function social_url( string $network ): string {
        $network = sanitize_key( $network );
        $urls    = $this->social_urls();
        if ( isset( $urls[ $network ] ) ) {
            return $urls[ $network ];
        }
        $twin = [ 'twitter' => 'x', 'x' => 'twitter' ][ $network ] ?? '';
        return '' !== $twin ? ( $urls[ $twin ] ?? '' ) : '';
    }

    // Birth and nationality.

    public function birth_date(): string {
        return ValueNormalizer::text( $this->field( PersonFields::BIRTH_DATE, '' ) );
    }

    /** @return array{location:string,wikipedia_url:string} */
    public function birth_place(): array {
        $place = (array) $this->field( PersonFields::LOCATION_BORN, [] );
        return [
            'location'      => ValueNormalizer::text( $place['location'] ?? '' ),
            'wikipedia_url' => ValueNormalizer::url( $place['wikipedia_url'] ?? '' ),
        ];
    }

    /** @return list<string> Older sites stored nationality as comma-separated text. */
    public function nationality(): array {
        return ValueNormalizer::strings( $this->field( PersonFields::NATIONALITY, [] ), [ 'value', 'name' ] );
    }

    public function gender(): string {
        return ValueNormalizer::text( $this->field( PersonFields::GENDER, '' ) );
    }

    /** @return list<string> */
    public function languages(): array {
        return ValueNormalizer::strings( $this->field( PersonFields::LANGUAGES, [] ), [ 'value', 'name' ] );
    }

    /** @return list<string> */
    public function awards(): array {
        return ValueNormalizer::strings( $this->field( PersonFields::AWARDS, [] ), [ 'value', 'name' ] );
    }

    // Career.

    /**
     * @return list<array{school:string,wikipedia_url:string,year:string,degree:string,field:string}>
     */
    public function education(): array {
        $rows = [];
        foreach ( ValueNormalizer::rows( $this->field( PersonFields::EDUCATION, [] ) ) as $row ) {
            $entry = [
                'school'        => ValueNormalizer::text( $row['college'] ?? '' ),
                'wikipedia_url' => ValueNormalizer::url( $row['wiki_url'] ?? '' ),
                'year'          => ValueNormalizer::text( $row['year'] ?? '' ),
                'degree'        => ValueNormalizer::text( $row['designation'] ?? '' ),
                'field'         => ValueNormalizer::text( $row['major'] ?? '' ),
            ];
            if ( '' !== $entry['school'] || '' !== $entry['degree'] || '' !== $entry['field'] ) {
                $rows[] = $entry;
            }
        }
        return $rows;
    }

    /**
     * @return list<array{name:string,page_id:int,summary_html:string,index:int}>
     */
    public function professions(): array {
        $rows = [];
        foreach ( ValueNormalizer::rows( $this->field( PersonFields::PROFESSIONS, [] ) ) as $index => $row ) {
            $page    = $row['page'] ?? 0;
            $page_id = absint( is_array( $page ) ? ( $page['ID'] ?? 0 ) : ( is_object( $page ) ? ( $page->ID ?? 0 ) : $page ) );
            $name    = ValueNormalizer::text( $row['name'] ?? $row['profession'] ?? '' );
            if ( '' === $name && 0 === $page_id ) {
                continue;
            }
            $rows[] = [
                'name'         => $name,
                'page_id'      => $page_id > 0 && 'publish' === get_post_status( $page_id ) ? $page_id : 0,
                'summary_html' => wp_kses_post( (string) ( $row['summary'] ?? '' ) ),
                'index'        => (int) $index,
            ];
        }
        return $rows;
    }

    /** @return list<int> Published Organization records the person founded. */
    public function organizations(): array {
        return PersonOrganizations::ids( $this->user_id() );
    }

    // Links.

    /** @return list<array{title:string,source:string,url:string}> */
    public function articles(): array {
        return Links::rows( $this->field( PersonFields::ARTICLES, [] ) );
    }

    /** @return list<array{title:string,source:string,url:string}> Wikidata rows are left out. */
    public function additional_urls(): array {
        return Links::public_rows( $this->field( PersonFields::ADDITIONAL_URLS, [] ) );
    }

    /** @return list<string> The profile links typed one per line, without Wikidata. */
    public function same_as(): array {
        return Links::public_urls( $this->field( PersonFields::SAME_AS, '' ) );
    }

    /** @return list<string> Wikidata links, which only schema uses. */
    public function wikidata_urls(): array {
        return Links::wikidata_urls(
            $this->field( PersonFields::SAME_AS, '' ),
            $this->social_url( 'wikidata' ),
            array_column( Links::rows( $this->field( PersonFields::ADDITIONAL_URLS, [] ) ), 'url' )
        );
    }

    public function knowledge_graph_id(): string {
        return Links::knowledge_graph_id( $this->field( PersonFields::KNOWLEDGE_GRAPH_ID, '' ) );
    }

    public function knowledge_panel_url(): string {
        return Links::knowledge_panel_url( $this->knowledge_graph_id() );
    }

    // Images.

    /** The person's portrait: an uploaded avatar when there is one, else WordPress's avatar. */
    public function avatar_url( int $size = 512 ): string {
        $attachment = absint( get_user_meta( $this->user_id(), 'wp_user_avatar', true ) );
        if ( $attachment > 0 ) {
            $url = wp_get_attachment_image_url( $attachment, $size <= 300 ? 'medium' : 'large' );
            if ( $url ) {
                return (string) $url;
            }
        }
        $stored = get_user_meta( $this->user_id(), 'wp_user_avatars', true );
        if ( is_array( $stored ) ) {
            foreach ( [ (string) $size, '512', '256', '192' ] as $key ) {
                if ( ! empty( $stored[ $key ] ) ) {
                    return (string) $stored[ $key ];
                }
            }
            foreach ( $stored as $key => $url ) {
                if ( is_numeric( $key ) && is_string( $url ) && '' !== $url ) {
                    return $url;
                }
            }
        }
        return (string) get_avatar_url( $this->user_id(), [ 'size' => max( 1, $size ) ] );
    }

    /** The uploaded avatar's media library ID, or 0. */
    public function avatar_attachment_id(): int {
        return absint( get_user_meta( $this->user_id(), 'wp_user_avatar', true ) );
    }

    /**
     * The person's public photos: the HWS Base Tools Photos field, or the
     * older SFPF gallery while a site still uses it.
     *
     * @return list<array<string,mixed>>
     */
    public function gallery( string $size = 'large' ): array {
        $photos = $this->field( PersonFields::PHOTOS, [] );
        $photos = ValueNormalizer::present( $photos ) ? $photos : $this->field( PersonFields::LEGACY_GALLERY, [] );
        return MediaNormalizer::gallery_records( $photos, $size );
    }

    /** @return list<int> The media library IDs of the public photos, in order. */
    public function gallery_ids(): array {
        return array_values( array_filter( array_map( static fn( array $image ): int => (int) ( $image['id'] ?? 0 ), $this->gallery() ) ) );
    }

    /** @return list<array<string,mixed>> */
    public function knowledge_graph_images( string $size = 'large' ): array {
        return MediaNormalizer::gallery_records( $this->field( PersonFields::KNOWLEDGE_IMAGES, [] ), $size );
    }

    /** @return list<string> */
    public function wikimedia_photo_urls(): array {
        return array_values( array_unique( array_column( Links::rows( $this->field( PersonFields::WIKIMEDIA_PHOTOS, [] ) ), 'url' ) ) );
    }

    // Questions.

    /** @return list<array{question:string,answer:string}> */
    public function faq(): array {
        return ( new FaqSourceResolver() )->acf( $this->context(), PersonFields::FAQ );
    }

    public function edit_url(): string {
        return (string) get_edit_user_link( $this->user_id() );
    }

    private function contact( string $key ): string {
        $contact = $this->field( PersonFields::CONTACT, [] );
        $value   = is_array( $contact ) ? ( $contact[ $key ] ?? '' ) : '';
        if ( ! ValueNormalizer::present( $value ) ) {
            $value = get_user_meta( $this->user_id(), PersonFields::CONTACT . '_' . $key, true );
        }
        return is_scalar( $value ) ? (string) $value : '';
    }
}
