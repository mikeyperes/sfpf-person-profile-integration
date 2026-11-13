<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Migrations;

use SFPF\PersonProfile\Settings\Settings;

defined( 'ABSPATH' ) || exit;

/**
 * Schema modes before 4.0 become today's modes. A homepage left on the old
 * default ("Person only", never chosen) moves to the profile page, which is
 * what search engines expect for a person's site; a mode someone chose stays.
 * "None" becomes "Off"; "ProfilePage only" becomes the profile page, which
 * now always names the person as its main subject.
 */
final class SchemaModesMigration implements Migration {
    public function id(): string {
        return 'schema-modes-4.0';
    }

    public function migrate(): bool {
        $home   = (string) get_option( Settings::HOMEPAGE_SCHEMA_MODE, '' );
        $chosen = (bool) get_option( Settings::HOMEPAGE_SCHEMA_CHOSEN, false );
        $map    = [ 'none' => 'off', 'profile_page_only' => 'profile_page' ];
        if ( '' === $home || ( 'person' === $home && ! $chosen ) ) {
            update_option( Settings::HOMEPAGE_SCHEMA_MODE, 'profile_page', false );
        } elseif ( isset( $map[ $home ] ) ) {
            update_option( Settings::HOMEPAGE_SCHEMA_MODE, $map[ $home ], false );
        }
        $biography = (string) get_option( Settings::BIOGRAPHY_SCHEMA_MODE, '' );
        if ( '' === $biography || isset( $map[ $biography ] ) ) {
            update_option( Settings::BIOGRAPHY_SCHEMA_MODE, $map[ $biography ] ?? 'profile_page', false );
        }
        return true;
    }
}
