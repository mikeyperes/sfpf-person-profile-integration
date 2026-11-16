<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\PressReleases;

use SFPF\PersonProfile\Person\PersonResolver;
use SFPF\PersonProfile\Settings\Settings;

defined( 'ABSPATH' ) || exit;

/** The two press-release sections belong to the website's selected person. */
final class PressReleaseSettings {
    public const DEFAULT_ORIGIN = 'https://hexaprwire.com';
    public const MODES          = [ 'by' => 'By you', 'about' => 'About you' ];

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
