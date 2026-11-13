<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Fields;

use Hexa\PluginCore\CoreContracts\ModuleInterface;
use Hexa\PluginCore\Fields\Hooks;
use SFPF\PersonProfile\Person\PersonProfile;

defined( 'ABSPATH' ) || exit;

/**
 * Shows the person's LinkedIn and Crunchbase links under their education
 * field on the profile screen, where schools and degrees are copied from.
 */
final class EducationProfileLinks implements ModuleInterface {
    public function register(): void {
        Hooks::on( 'prepare_field/key=field_sfpf_education_repeater', [ $this, 'prepare' ] );
    }

    /** @return array<string,mixed>|false */
    public function prepare( mixed $field ): array|false {
        if ( ! is_array( $field ) ) {
            return false === $field ? false : [];
        }
        $screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
        if ( ! $screen || ! in_array( $screen->id, [ 'profile', 'user-edit' ], true ) ) {
            return $field;
        }
        $user_id = defined( 'IS_PROFILE_PAGE' ) && IS_PROFILE_PAGE ? get_current_user_id() : ( isset( $_GET['user_id'] ) ? absint( wp_unslash( $_GET['user_id'] ) ) : 0 ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only screen context.
        $profile = $user_id > 0 ? PersonProfile::for_user( $user_id ) : null;
        if ( null === $profile ) {
            return $field;
        }
        $links = [];
        foreach ( [ 'linkedin' => 'LinkedIn', 'crunchbase' => 'Crunchbase' ] as $network => $label ) {
            $url = $profile->social_url( $network );
            if ( '' !== $url ) {
                $links[] = '<a href="' . esc_url( $url ) . '" target="_blank" rel="noopener">' . esc_html( $label ) . ' ↗</a>';
            }
        }
        if ( [] !== $links ) {
            $field['instructions'] = (string) ( $field['instructions'] ?? '' ) . '<br><span class="sfpf-profile-hint">Copy from: ' . implode( ' &nbsp;|&nbsp; ', $links ) . '</span>';
        }
        return $field;
    }
}
