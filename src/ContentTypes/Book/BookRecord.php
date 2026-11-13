<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\ContentTypes\Book;

use Hexa\PluginCore\DataNormalization\FieldReader;
use SFPF\PersonProfile\Settings\Settings;

defined( 'ABSPATH' ) || exit;

/** One Book record's values, and which book "the book" means on a page. */
final class BookRecord {
    private function __construct( public readonly int $id ) {
    }

    public static function for_post( int $id ): ?self {
        return $id > 0 && BookContentType::POST_TYPE === get_post_type( $id ) ? new self( $id ) : null;
    }

    /** The book a shortcode means: the one asked for, else the one whose page is open, else the primary book. */
    public static function resolve( int $requested = 0 ): ?self {
        if ( $requested > 0 ) {
            return self::for_post( $requested );
        }
        return self::for_post( (int) get_queried_object_id() ) ?? self::primary();
    }

    /** The primary book chosen in Settings, else the oldest published book. */
    public static function primary(): ?self {
        $chosen = Settings::int( Settings::PRIMARY_BOOK );
        if ( $chosen > 0 && 'publish' === get_post_status( $chosen ) && null !== self::for_post( $chosen ) ) {
            return new self( $chosen );
        }
        if ( ! post_type_exists( BookContentType::POST_TYPE ) ) {
            return null;
        }
        $oldest = get_posts( [ 'post_type' => BookContentType::POST_TYPE, 'post_status' => 'publish', 'posts_per_page' => 1, 'orderby' => 'date', 'order' => 'ASC', 'fields' => 'ids', 'no_found_rows' => true ] );
        return [] !== $oldest ? new self( (int) $oldest[0] ) : null;
    }

    public function field( string $name ): mixed {
        return FieldReader::acf_value( sanitize_key( $name ), $this->id, null );
    }
}
