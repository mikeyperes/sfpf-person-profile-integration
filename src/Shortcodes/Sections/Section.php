<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Shortcodes\Sections;

use SFPF\PersonProfile\Person\PersonProfile;

defined( 'ABSPATH' ) || exit;

/**
 * One block of the person's page, printed by [person_section id="…"]. A
 * section prints nothing when it has no content, so a page can place every
 * section and only the filled ones show.
 */
interface Section {
    public function id(): string;

    public function label(): string;

    /** @return array<string,string> Layout => what it looks like; the first is the default. */
    public function layouts(): array;

    /** @return array<string,string> The section's own attributes and their defaults. */
    public function attributes(): array;

    public function has_content( PersonProfile $profile ): bool;

    /** @param array<string,string> $atts */
    public function render( PersonProfile $profile, array $atts ): string;
}
