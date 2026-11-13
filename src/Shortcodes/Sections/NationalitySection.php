<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Shortcodes\Sections;

use SFPF\PersonProfile\Person\PersonProfile;

defined( 'ABSPATH' ) || exit;

/** The person's nationalities. */
final class NationalitySection implements Section {
    public function id(): string {
        return 'nationality';
    }

    public function label(): string {
        return 'Nationality';
    }

    public function layouts(): array {
        return [ 'inline' => 'Comma-separated' ];
    }

    public function attributes(): array {
        return [];
    }

    public function has_content( PersonProfile $profile ): bool {
        return [] !== $profile->nationality();
    }

    public function render( PersonProfile $profile, array $atts ): string {
        return '<span class="sfpf-nationality__value founder-nationality">' . esc_html( implode( ', ', $profile->nationality() ) ) . '</span>';
    }
}
