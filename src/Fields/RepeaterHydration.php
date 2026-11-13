<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Fields;

use Hexa\PluginCore\CoreContracts\ModuleInterface;
use Hexa\PluginCore\Fields\Hooks;
use SFPF\PersonProfile\Person\PersonFields;

defined( 'ABSPATH' ) || exit;

/**
 * Keeps the person's repeaters intact on the user profile screens.
 *
 * On profile.php and user-edit.php ACF can load a repeater with the right
 * number of rows but every value empty ("shell rows"); saving that form would
 * overwrite the real rows with blanks. Three steps prevent it:
 * 1. load_value rebuilds shell rows from the stored user meta;
 * 2. prepare_field does the same when ACF skips load_value at render time;
 * 3. update_value keeps a stored value when its form value arrives empty and
 *    drops rows that are fully empty.
 *
 * The meta name comes from the field key, never from $field['name'], which
 * ACF rewrites to "acf[field_key]" while preparing a field.
 */
final class RepeaterHydration implements ModuleInterface {
    public function register(): void {
        foreach ( array_keys( PersonFields::REPEATERS ) as $key ) {
            Hooks::on( 'load_value/key=' . $key, [ $this, 'load_value' ], 20, 3 );
            Hooks::on( 'prepare_field/key=' . $key, [ $this, 'prepare_field' ], 50 );
            Hooks::on( 'update_value/key=' . $key, [ $this, 'update_value' ], 1, 3 );
        }
    }

    public function load_value( mixed $value, mixed $post_id, array $field ): mixed {
        $user_id = self::user_id( $post_id );
        $meta    = PersonFields::REPEATERS[ $field['key'] ?? '' ] ?? '';
        if ( ! self::on_profile_screen() || $user_id <= 0 || '' === $meta || self::has_data( $value, $field ) ) {
            return $value;
        }
        return self::rebuild( $user_id, $meta, $field );
    }

    /** @return array<string,mixed>|false */
    public function prepare_field( mixed $field ): array|false {
        if ( ! is_array( $field ) ) {
            return false === $field ? false : [];
        }
        $user_id = self::profile_user_id();
        $meta    = PersonFields::REPEATERS[ $field['key'] ?? '' ] ?? '';
        if ( ! self::on_profile_screen() || $user_id <= 0 || '' === $meta || self::has_data( $field['value'] ?? null, $field ) ) {
            return $field;
        }
        $rebuilt = self::rebuild( $user_id, $meta, $field );
        if ( [] !== $rebuilt ) {
            $field['value'] = $rebuilt;
        }
        return $field;
    }

    public function update_value( mixed $value, mixed $post_id, array $field ): mixed {
        $user_id = self::user_id( $post_id );
        $meta    = PersonFields::REPEATERS[ $field['key'] ?? '' ] ?? '';
        $subs    = (array) ( $field['sub_fields'] ?? [] );
        if ( ! self::on_profile_screen() || $user_id <= 0 || '' === $meta || ! is_array( $value ) || [] === $subs ) {
            return $value;
        }
        $kept = [];
        // Rows posted from the form are keyed "row-0", "row-1"…, which never match stored
        // positions, so only rows saved by code (numeric keys) restore emptied values.
        foreach ( $value as $index => $row ) {
            $row = is_array( $row ) ? $row : [];
            $filled = false;
            foreach ( $subs as $sub ) {
                $incoming = $row[ $sub['key'] ] ?? '';
                if ( '' === $incoming || null === $incoming ) {
                    $stored = get_user_meta( $user_id, $meta . '_' . $index . '_' . $sub['name'], true );
                    if ( '' !== $stored && null !== $stored ) {
                        $row[ $sub['key'] ] = $stored;
                    }
                }
                $filled = $filled || '' !== ( $row[ $sub['key'] ] ?? '' );
            }
            if ( $filled ) {
                $kept[] = $row;
            }
        }
        return $kept;
    }

    private static function has_data( mixed $value, array $field ): bool {
        if ( ! is_array( $value ) || [] === $value ) {
            return false;
        }
        foreach ( $value as $row ) {
            foreach ( is_array( $row ) ? (array) ( $field['sub_fields'] ?? [] ) : [] as $sub ) {
                $item = $row[ $sub['key'] ] ?? ( $row[ $sub['name'] ] ?? '' );
                if ( '' !== $item && null !== $item ) {
                    return true;
                }
            }
        }
        return false;
    }

    /** @return list<array<string,mixed>> */
    private static function rebuild( int $user_id, string $meta, array $field ): array {
        $count = (int) get_user_meta( $user_id, $meta, true );
        $subs  = (array) ( $field['sub_fields'] ?? [] );
        $rows  = [];
        for ( $index = 0; $index < $count && [] !== $subs; $index++ ) {
            $row = [];
            foreach ( $subs as $sub ) {
                $row[ $sub['key'] ] = get_user_meta( $user_id, $meta . '_' . $index . '_' . $sub['name'], true );
            }
            $rows[] = $row;
        }
        return $rows;
    }

    private static function on_profile_screen(): bool {
        if ( ! is_admin() ) {
            return false;
        }
        if ( in_array( $GLOBALS['pagenow'] ?? '', [ 'profile.php', 'user-edit.php' ], true ) || ( defined( 'IS_PROFILE_PAGE' ) && IS_PROFILE_PAGE ) ) {
            return true;
        }
        return in_array( current_action(), [ 'show_user_profile', 'edit_user_profile', 'personal_options_update', 'edit_user_profile_update' ], true );
    }

    private static function profile_user_id(): int {
        if ( 'profile.php' === ( $GLOBALS['pagenow'] ?? '' ) ) {
            return (int) get_current_user_id();
        }
        return isset( $_GET['user_id'] ) ? absint( wp_unslash( $_GET['user_id'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only screen context.
    }

    private static function user_id( mixed $post_id ): int {
        if ( is_string( $post_id ) && str_starts_with( $post_id, 'user_' ) ) {
            return absint( substr( $post_id, 5 ) );
        }
        return is_numeric( $post_id ) ? absint( $post_id ) : 0;
    }
}
