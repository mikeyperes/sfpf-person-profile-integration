<?php
/**
 * Every field and group key the plugin ever stored data under is still
 * defined, so no site loses data after an update.
 */

declare( strict_types=1 );

require __DIR__ . '/bootstrap.php';

$defined = [];
$collect = static function ( array $node ) use ( &$collect, &$defined ): void {
    if ( isset( $node['key'] ) && is_string( $node['key'] ) ) {
        $defined[ $node['key'] ] = true;
    }
    foreach ( $node as $value ) {
        if ( is_array( $value ) ) {
            $collect( $value );
        }
    }
};
$collect( \SFPF\PersonProfile\Fields\PersonProfileFields::group() );
foreach ( \SFPF\PersonProfile\ContentTypes\ContentTypeCatalog::TYPES as $type ) {
    foreach ( $type::definition()['field_groups'] as $group ) {
        $collect( call_user_func( $group['definition'] ) );
    }
}
$stored = json_decode( (string) file_get_contents( __DIR__ . '/fixtures/stored-field-keys.json' ), true );
// The pre-4.0 homepage group only held a schema preview and a type no code read; schema settings live in the Schema tab.
$retired = [ 'group_sfpf_homepage', 'field_sfpf_homepage_schema_type', 'field_sfpf_homepage_schema', 'field_sfpf_homepage_schema_preview', 'field_sfpf_homepage_schema_info' ];
$missing = array_values( array_diff( $stored, array_keys( $defined ), $retired ) );
sfpf_assert( [] === $missing, 'Every stored key is still defined; missing: ' . implode( ', ', $missing ) );
sfpf_assert( isset( $defined[ \SFPF\PersonProfile\Fields\PersonProfileFields::GROUP_KEY ] ), 'The person group keeps its key.' );
foreach ( array_keys( \SFPF\PersonProfile\Person\PersonFields::REPEATERS ) as $key ) {
    sfpf_assert( isset( $defined[ $key ] ), "The repeater {$key} the profile screen protects is defined." );
}

echo 'PASS: all ' . count( $stored ) . " stored field keys are still defined.\n";
