<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\PressReleases;

use SFPF\PersonProfile\Person\PersonResolver;
use SFPF\PersonProfile\Settings\Settings;

defined( 'ABSPATH' ) || exit;

/** The two press-release sections belong to the website's selected person. */
final class PressReleaseSettings {
    public const MODES          = [ 'by' => 'By you', 'about' => 'About you' ];

    /** Read this site's initial origin for a new press-release connection. */
    public static function default_origin(): string {
        return (string) get_option( Settings::PRESS_RELEASE_DEFAULT_ORIGIN, '' );
    }

    /** Create the initial origin only when no setting exists. */
    public static function seed_default_origin( string $origin ): bool {
        $option = Settings::PRESS_RELEASE_DEFAULT_ORIGIN;
        if ( null !== get_option( $option, null ) ) {
            return true;
        }
        $origin = esc_url_raw( $origin, [ 'https' ] );
        return '' !== $origin && ( add_option( $option, $origin, '', false ) || null !== get_option( $option, null ) );
    }

    /** @return array<string,mixed> */
    public static function current(): array {
        $profile = PersonResolver::profile();
        $saved   = Settings::map( Settings::PRESS_RELEASES );
        return null !== $profile && $profile->user_id() === (int) ( $saved['person_id'] ?? 0 ) ? $saved : [];
    }

    public static function enabled( string $mode ): bool {
        return isset( self::MODES[ $mode ] ) && ! empty( self::current()[ $mode ] );
    }
}
