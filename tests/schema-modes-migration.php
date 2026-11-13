<?php
/** Old schema modes become today's; a homepage mode someone chose stays. */

declare( strict_types=1 );

require __DIR__ . '/bootstrap.php';

use SFPF\PersonProfile\Migrations\SchemaModesMigration;
use SFPF\PersonProfile\Settings\Settings;

$run = static function ( array $options ): array {
    $GLOBALS['sfpf_test_options'] = $options;
    ( new SchemaModesMigration() )->migrate();
    return [ get_option( Settings::HOMEPAGE_SCHEMA_MODE ), get_option( Settings::BIOGRAPHY_SCHEMA_MODE ) ];
};
sfpf_assert( [ 'profile_page', 'profile_page' ] === $run( [] ), 'A new site gets the profile page on both.' );
sfpf_assert( [ 'profile_page', 'profile_page' ] === $run( [ Settings::HOMEPAGE_SCHEMA_MODE => 'person', Settings::BIOGRAPHY_SCHEMA_MODE => 'profile_page_only' ] ), 'The old default moves to the profile page.' );
sfpf_assert( [ 'person', 'person' ] === $run( [ Settings::HOMEPAGE_SCHEMA_MODE => 'person', Settings::HOMEPAGE_SCHEMA_CHOSEN => true, Settings::BIOGRAPHY_SCHEMA_MODE => 'person' ] ), 'A chosen mode stays.' );
sfpf_assert( [ 'off', 'off' ] === $run( [ Settings::HOMEPAGE_SCHEMA_MODE => 'none', Settings::BIOGRAPHY_SCHEMA_MODE => 'none' ] ), 'None becomes Off.' );

echo "PASS: schema modes upgrade.\n";
