<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Migrations;

use RuntimeException;
use SFPF\PersonProfile\Fields\FieldKeys;
use SFPF\PersonProfile\Settings\Settings;

defined( 'ABSPATH' ) || exit;

/** Migrates only SFPF's stored field references, leaving the named values intact. */
final class FieldKeyMigration {
    /** Returns the storage rows and proves the replacement on temporary copies when requested. */
    public function run( bool $dry_run ): array {
        global $wpdb;
        if ( is_multisite() ) {
            throw new RuntimeException( 'A multisite database needs a network-wide migration for its shared user metadata.' );
        }

        $lock = 'sfpf-field-keys-' . md5( DB_NAME . ':' . $wpdb->prefix );
        if ( '1' !== (string) $wpdb->get_var( $wpdb->prepare( 'SELECT GET_LOCK(%s, 0)', $lock ) ) ) {
            throw new RuntimeException( 'Another field-key migration is running.' );
        }

        try {
            return $dry_run ? $this->verify_on_copy() : $this->migrate();
        } finally {
            $wpdb->get_var( $wpdb->prepare( 'SELECT RELEASE_LOCK(%s)', $lock ) );
        }
    }

    /** Copies the exact candidate rows, applies the production update, and proves idempotence. */
    private function verify_on_copy(): array {
        $report = [];
        foreach ( $this->storage() as $type => $storage ) {
            $rows = $this->candidate_rows( $storage );
            $report[ $type ] = $rows;
            if ( [] === $rows ) {
                continue;
            }

            $copy = $storage;
            $copy['table'] = 'sfpf_field_key_dry_run_' . $type;
            $temp_table = $copy['table'];
            $original_table = $storage['table'];
            $id_column = $storage['id'];

            $this->execute( "CREATE TEMPORARY TABLE {$temp_table} LIKE {$original_table}" );
            try {
                foreach ( array_chunk( array_column( $rows, 'id' ), 200 ) as $ids ) {
                    $ids = implode( ',', array_map( 'intval', $ids ) );
                    $this->execute( "INSERT INTO {$temp_table} SELECT * FROM {$original_table} WHERE {$id_column} IN ({$ids})" );
                }
                if ( $rows !== $this->candidate_rows( $copy ) ) {
                    throw new RuntimeException( 'The source references changed while making the dry-run copy.' );
                }
                $this->replace_rows( $copy, $rows );
                if ( [] !== $this->candidate_rows( $copy ) ) {
                    throw new RuntimeException( 'Legacy references remain in the dry-run copy.' );
                }
            } finally {
                $this->execute( "DROP TEMPORARY TABLE {$temp_table}" );
            }
        }
        return $report;
    }

    /** Updates the references and activates the canonical definitions as one transaction. */
    private function migrate(): array {
        global $wpdb;
        $report = [];
        $storage = $this->storage();
        foreach ( $storage as $location ) {
            $engine = $wpdb->get_var( $wpdb->prepare( 'SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = %s', $location['table'] ) );
            if ( 'InnoDB' !== $engine ) {
                throw new RuntimeException( 'Field-key migration requires InnoDB tables so it can roll back safely.' );
            }
        }
        $this->execute( 'START TRANSACTION' );
        try {
            foreach ( $storage as $type => $location ) {
                $rows = $this->candidate_rows( $location, true );
                $report[ $type ] = $rows;
                $this->replace_rows( $location, $rows );
            }
            $this->execute(
                $wpdb->prepare(
                    "INSERT INTO {$wpdb->options} (option_name, option_value, autoload) VALUES (%s, '1', 'no') ON DUPLICATE KEY UPDATE option_value = '1'",
                    Settings::FIELD_KEYS_MIGRATED
                )
            );
            $this->execute( 'COMMIT' );
        } catch ( \Throwable $error ) {
            $wpdb->query( 'ROLLBACK' );
            throw $error;
        }

        $this->clear_caches( $report );
        return $report;
    }

    /** ACF-style hidden metadata and option references, with their exact row IDs. */
    private function candidate_rows( array $storage, bool $lock_rows = false ): array {
        global $wpdb;
        $keys = array_filter( FieldKeys::RENAMED, static fn( string $key ): bool => str_starts_with( $key, 'field_' ), ARRAY_FILTER_USE_KEY );
        $placeholders = implode( ',', array_fill( 0, count( $keys ), '%s' ) );
        $sql = "SELECT {$storage['id']} AS id, {$storage['owner']} AS owner, {$storage['name']} AS name, {$storage['value']} AS old_key "
            . "FROM {$storage['table']} WHERE {$storage['name']} LIKE %s AND BINARY {$storage['value']} IN ({$placeholders}) "
            . "ORDER BY {$storage['id']}" . ( $lock_rows ? ' FOR UPDATE' : '' );
        $rows = $wpdb->get_results(
            $wpdb->prepare( $sql, $wpdb->esc_like( '_' ) . '%', ...array_keys( $keys ) ),
            ARRAY_A
        );
        if ( null === $rows || '' !== $wpdb->last_error ) {
            throw new RuntimeException( 'Could not read the stored field references.' );
        }
        foreach ( $rows as &$row ) {
            $row['new_key'] = $keys[ $row['old_key'] ];
        }
        return $rows;
    }

    /** Updates exact rows only when their old key still matches. */
    private function replace_rows( array $storage, array $rows ): void {
        global $wpdb;
        foreach ( $rows as $row ) {
            $updated = $this->execute(
                $wpdb->prepare(
                    "UPDATE {$storage['table']} SET {$storage['value']} = %s WHERE {$storage['id']} = %d AND BINARY {$storage['value']} = %s",
                    $row['new_key'],
                    (int) $row['id'],
                    $row['old_key']
                )
            );
            if ( 1 !== $updated ) {
                throw new RuntimeException( 'A stored field reference changed during the migration.' );
            }
        }
    }

    /** Clear reference caches once the transaction is committed. */
    private function clear_caches( array $report ): void {
        foreach ( $report as $type => $rows ) {
            if ( 'option' === $type ) {
                foreach ( $rows as $row ) {
                    wp_cache_delete( $row['name'], 'options' );
                }
                continue;
            }
            foreach ( array_unique( array_column( $rows, 'owner' ) ) as $owner ) {
                wp_cache_delete( (int) $owner, $type . '_meta' );
            }
        }
        foreach ( [ Settings::FIELD_KEYS_MIGRATED, 'alloptions', 'notoptions' ] as $key ) {
            wp_cache_delete( $key, 'options' );
        }
    }

    /** WordPress storage locations supported by Hexa WP Core Fields. */
    private function storage(): array {
        global $wpdb;
        return [
            'user' => [ 'table' => $wpdb->usermeta, 'id' => 'umeta_id', 'owner' => 'user_id', 'name' => 'meta_key', 'value' => 'meta_value' ],
            'post' => [ 'table' => $wpdb->postmeta, 'id' => 'meta_id', 'owner' => 'post_id', 'name' => 'meta_key', 'value' => 'meta_value' ],
            'term' => [ 'table' => $wpdb->termmeta, 'id' => 'meta_id', 'owner' => 'term_id', 'name' => 'meta_key', 'value' => 'meta_value' ],
            'comment' => [ 'table' => $wpdb->commentmeta, 'id' => 'meta_id', 'owner' => 'comment_id', 'name' => 'meta_key', 'value' => 'meta_value' ],
            'option' => [ 'table' => $wpdb->options, 'id' => 'option_id', 'owner' => 'option_id', 'name' => 'option_name', 'value' => 'option_value' ],
        ];
    }

    /** Executes a write and stops on a failed query. */
    private function execute( string $sql ): int {
        global $wpdb;
        $result = $wpdb->query( $sql );
        if ( false === $result ) {
            throw new RuntimeException( 'A database operation failed during the field-key migration.' );
        }
        return (int) $result;
    }
}
