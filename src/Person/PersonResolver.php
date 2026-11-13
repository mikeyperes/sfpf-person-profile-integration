<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Person;

use Hexa\PluginCore\DataNormalization\FieldReader;
use Hexa\PluginCore\EntitySources\CanonicalEntityResolver;

defined( 'ABSPATH' ) || exit;

/**
 * Finds the person the website is about: the WordPress user set as the
 * primary Person in HWS Base Tools, or the user bound to a person post.
 * Sites set up before HWS Base Tools owned that choice keep working through
 * the historical founder and website options.
 */
final class PersonResolver {
    private static ?int $user_id = null;

    public static function user_id(): int {
        if ( null !== self::$user_id ) {
            return self::$user_id;
        }
        self::$user_id = (int) apply_filters( 'sfpf_person_user_id', self::resolve() );
        return self::$user_id;
    }

    public static function profile(): ?PersonProfile {
        $user_id = self::user_id();
        return $user_id > 0 ? PersonProfile::for_user( $user_id ) : null;
    }

    /** Forget the resolved person, for example after the primary entity changes. */
    public static function reset(): void {
        self::$user_id = null;
        PersonProfile::forget();
    }

    private static function resolve(): int {
        $entity = class_exists( CanonicalEntityResolver::class ) ? CanonicalEntityResolver::resolve() : null;
        if ( is_array( $entity ) && 'person' === ( $entity['entity_type'] ?? '' ) ) {
            $user_id = 'user' === ( $entity['kind'] ?? '' ) ? (int) ( $entity['id'] ?? 0 ) : (int) ( $entity['attached_user_id'] ?? 0 );
            if ( $user_id > 0 && get_userdata( $user_id ) ) {
                return $user_id;
            }
        }

        $founder = FieldReader::acf_value( 'founder', 'option', [] );
        foreach ( [ 'founder_user', 'user' ] as $key ) {
            $user_id = is_array( $founder ) ? self::user_from( $founder[ $key ] ?? null ) : 0;
            if ( $user_id > 0 ) {
                return $user_id;
            }
        }

        $website = FieldReader::acf_value( 'website', 'option', [] );
        return is_array( $website ) ? self::user_from( $website['company'] ?? null ) : 0;
    }

    private static function user_from( mixed $value ): int {
        if ( $value instanceof \WP_User ) {
            return (int) $value->ID;
        }
        if ( is_array( $value ) ) {
            $value = $value['ID'] ?? $value['id'] ?? 0;
        } elseif ( is_object( $value ) && isset( $value->ID ) ) {
            $value = $value->ID;
        }
        $user_id = absint( $value );
        return $user_id > 0 && get_userdata( $user_id ) ? $user_id : 0;
    }
}
