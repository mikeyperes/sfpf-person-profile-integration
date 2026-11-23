<?php
/** Proves legacy definitions survive until migration, with a canonical target for each retained field. */

declare( strict_types=1 );

use SFPF\PersonProfile\ContentTypes\ContentTypeCatalog;
use SFPF\PersonProfile\Fields\FieldKeys;
use SFPF\PersonProfile\Fields\PersonProfileFields;
use SFPF\PersonProfile\Person\PersonFields;
use SFPF\PersonProfile\Settings\Settings;

require __DIR__ . '/bootstrap.php';

$collect = static function ( array $node ) use ( &$collect ): array {
    $keys = isset( $node['key'] ) && is_string( $node['key'] ) ? [ $node['key'] => $node['name'] ?? '' ] : [];
    foreach ( $node as $value ) {
        if ( is_array( $value ) ) {
            $keys += $collect( $value );
        }
    }
    return $keys;
};
$definitions = static function () use ( $collect ): array {
    $keys = $collect( PersonProfileFields::group() );
    foreach ( ContentTypeCatalog::TYPES as $type ) {
        foreach ( $type::definition()['field_groups'] as $group ) {
            $definition = call_user_func( $group['definition'] );
            sfpf_assert( $group['group_key'] === $definition['key'], 'Core knows the active group key for every content type.' );
            $keys += $collect( $definition );
        }
    }
    return $keys;
};
$stored = json_decode( (string) file_get_contents( __DIR__ . '/fixtures/stored-field-keys.json' ), true );
$retired = [ 'group_sfpf_homepage', 'field_sfpf_homepage_schema_type', 'field_sfpf_homepage_schema', 'field_sfpf_homepage_schema_preview', 'field_sfpf_homepage_schema_info' ];
$legacy = $definitions();
$missing = array_diff( $stored, array_keys( $legacy ), $retired );
sfpf_assert( [] === $missing, 'Every retained key works before migration: ' . implode( ', ', $missing ) );

update_option( Settings::FIELD_KEYS_MIGRATED, '1' );
$canonical = $definitions();
foreach ( FieldKeys::RENAMED as $from => $to ) {
    sfpf_assert( isset( $legacy[ $from ], $canonical[ $to ] ), "Both definitions exist for {$from}." );
    sfpf_assert( $legacy[ $from ] === $canonical[ $to ], "The value name for {$from} is preserved." );
    sfpf_assert( ! array_key_exists( $from, $canonical ), "The old key {$from} is inactive after migration." );
}
sfpf_assert( count( $legacy ) === count( $canonical ), 'Both key sets contain the same number of definitions.' );
sfpf_assert( isset( $canonical[ PersonProfileFields::GROUP_KEY ] ), 'The person group uses its canonical identifier.' );
foreach ( array_keys( PersonFields::REPEATERS ) as $key ) {
    sfpf_assert( isset( $canonical[ $key ] ), "The protected repeater {$key} exists." );
}

$_POST['acf'] = [ 'field_sfpf_professions_repeater' => [ 0 => [ 'field_sfpf_profession_page' => '0', 'field_sfpf_profession_summary' => 'field_sfpf_profession_page' ] ] ];
FieldKeys::normalize_submission();
sfpf_assert(
    [ 'field_sfpf_person_professions_repeater' => [ 0 => [ 'field_sfpf_person_profession_page' => '0', 'field_sfpf_person_profession_summary' => 'field_sfpf_profession_page' ] ] ] === $_POST['acf'],
    'A form opened before migration keeps its exact field values.'
);

echo 'PASS: ' . count( FieldKeys::RENAMED ) . " key translations preserve data names and stale form values.\n";
