<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Fields;

use Hexa\PluginCore\CoreContracts\ModuleInterface;
use Hexa\PluginCore\Fields\Hooks;

defined( 'ABSPATH' ) || exit;

/**
 * Field groups saved to the database under the plugin's own group keys (old
 * exports, ACF imports) would override the groups the code registers. They
 * are left out, so the code is always the field definition.
 */
final class DuplicateGroupGuard implements ModuleInterface {
    private const OWNED_PREFIXES = [ 'group_hws_', 'group_sfpf_' ];

    public function register(): void {
        Hooks::on( 'load_field_groups', [ $this, 'filter' ] );
    }

    /**
     * @param mixed $groups
     * @return mixed
     */
    public function filter( mixed $groups ): mixed {
        if ( ! is_array( $groups ) ) {
            return $groups;
        }
        return array_filter(
            $groups,
            static function ( mixed $group ): bool {
                if ( ! is_array( $group ) || empty( $group['ID'] ) || ! isset( $group['key'] ) ) {
                    return true;
                }
                foreach ( self::OWNED_PREFIXES as $prefix ) {
                    if ( str_starts_with( (string) $group['key'], $prefix ) ) {
                        return false;
                    }
                }
                return true;
            }
        );
    }
}
