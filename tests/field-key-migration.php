<?php
/** Checks exact WordPress metadata row selection and replacement for the backed-up migration. */

declare( strict_types=1 );

use SFPF\PersonProfile\Migrations\FieldKeyMigration;

require __DIR__ . '/bootstrap.php';

define( 'ARRAY_A', 'ARRAY_A' );
$wpdb = new class {
    public string $last_error = '';
    public array $calls = [];

    public function esc_like( string $value ): string {
        return addcslashes( $value, '_%\\' );
    }

    public function prepare( string $sql, mixed ...$arguments ): string {
        $this->calls[] = [ $sql, $arguments ];
        return $sql;
    }

    public function get_results( string $sql, string $format ): array {
        return [ [ 'id' => '17', 'owner' => '6', 'name' => '_education_0_college', 'old_key' => 'field_sfpf_edu_college' ] ];
    }

    public function query( string $sql ): int {
        return 1;
    }
};

$migration = new FieldKeyMigration();
$storage = [ 'table' => 'wp_usermeta', 'id' => 'umeta_id', 'owner' => 'user_id', 'name' => 'meta_key', 'value' => 'meta_value' ];
$candidates = new ReflectionMethod( $migration, 'candidate_rows' );
$rows = $candidates->invoke( $migration, $storage, true );
sfpf_assert( 'field_sfpf_person_edu_college' === $rows[0]['new_key'], 'A nested user-meta reference maps to the canonical key.' );
[ $query, $arguments ] = $wpdb->calls[0];
sfpf_assert( str_contains( $query, 'BINARY meta_value IN' ) && str_ends_with( $query, 'FOR UPDATE' ), 'The live query locks exact old reference values.' );
sfpf_assert( '\_%' === $arguments[0] && in_array( 'field_64bb58d291399', $arguments, true ), 'Only hidden references qualify, including older numeric keys.' );
sfpf_assert( ! in_array( 'group_sfpf_user_schema_structures', $arguments, true ), 'Group IDs are definition identifiers, outside field-value storage.' );

$replace = new ReflectionMethod( $migration, 'replace_rows' );
$replace->invoke( $migration, $storage, $rows );
[ $query, $arguments ] = $wpdb->calls[1];
sfpf_assert( str_contains( $query, 'SET meta_value = %s WHERE umeta_id = %d AND BINARY meta_value = %s' ), 'Updating a reference targets its exact row and previous value.' );
sfpf_assert( [ 'field_sfpf_person_edu_college', 17, 'field_sfpf_edu_college' ] === $arguments, 'The migration changes a reference, without reserializing its paired field value.' );

echo "PASS: exact, hidden field-reference selection and update.\n";
