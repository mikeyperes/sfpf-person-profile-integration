<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\ContentTypes\Organization;

use Hexa\PluginCore\DataNormalization\FieldReader;
use Hexa\PluginCore\DataNormalization\MediaNormalizer;
use Hexa\PluginCore\DataNormalization\ValueNormalizer;
use Hexa\PluginCore\EntitySources\CanonicalEntityResolver;
use SFPF\PersonProfile\Settings\Settings;

defined( 'ABSPATH' ) || exit;

/** One Organization record's values, and which record "the organization" means on a page. */
final class OrganizationRecord {
    private function __construct( public readonly int $id ) {
    }

    public static function for_post( int $id ): ?self {
        return $id > 0 && OrganizationContentType::POST_TYPE === get_post_type( $id ) ? new self( $id ) : null;
    }

    /**
     * The organization a shortcode means: the one asked for, else the one
     * whose page is open, else the primary organization.
     */
    public static function resolve( int $requested = 0 ): ?self {
        if ( $requested > 0 ) {
            return self::for_post( $requested );
        }
        $current = self::for_post( (int) get_queried_object_id() );
        return $current ?? self::primary();
    }

    /** The primary organization: HWS Base Tools' primary entity, the older SFPF setting, then the oldest record. */
    public static function primary(): ?self {
        $entity = class_exists( CanonicalEntityResolver::class ) ? CanonicalEntityResolver::resolve() : null;
        if ( is_array( $entity ) && 'post' === ( $entity['kind'] ?? '' ) && OrganizationContentType::POST_TYPE === ( $entity['post_type'] ?? '' ) && 'publish' === get_post_status( (int) $entity['id'] ) ) {
            return new self( (int) $entity['id'] );
        }
        $legacy = Settings::int( Settings::PRIMARY_ORGANIZATION );
        if ( $legacy > 0 && 'publish' === get_post_status( $legacy ) && null !== self::for_post( $legacy ) ) {
            return new self( $legacy );
        }
        if ( ! post_type_exists( OrganizationContentType::POST_TYPE ) ) {
            return null;
        }
        $oldest = get_posts( [ 'post_type' => OrganizationContentType::POST_TYPE, 'post_status' => 'publish', 'posts_per_page' => 1, 'orderby' => 'date', 'order' => 'ASC', 'fields' => 'ids', 'no_found_rows' => true ] );
        return [] !== $oldest ? new self( (int) $oldest[0] ) : null;
    }

    public function field( string $name ): mixed {
        return FieldReader::acf_value( sanitize_key( $name ), $this->id, null );
    }

    public function name(): string {
        return (string) get_the_title( $this->id );
    }

    public function permalink(): string {
        return (string) get_permalink( $this->id );
    }

    public function website(): string {
        return ValueNormalizer::url( $this->field( 'url' ) );
    }

    public function summary_html(): string {
        return wp_kses_post( (string) $this->field( 'short_summary' ) );
    }

    public function founding_date(): string {
        return ValueNormalizer::text( $this->field( 'founding_date' ) );
    }

    /** @return array{location:string,wikipedia_url:string} */
    public function headquarters(): array {
        $hq = (array) $this->field( 'headquarters' );
        return [ 'location' => ValueNormalizer::text( $hq['location'] ?? '' ), 'wikipedia_url' => ValueNormalizer::url( $hq['wikipedia_url'] ?? '' ) ];
    }

    public function logo_url( string $size = 'medium' ): string {
        $image = MediaNormalizer::image( $this->field( 'image_cropped' ), $size );
        return (string) ( $image['url'] ?? '' );
    }

    /** @return list<array<string,mixed>> */
    public function gallery( string $size = 'large' ): array {
        return MediaNormalizer::gallery_records( $this->field( 'gallery' ), $size );
    }
}
