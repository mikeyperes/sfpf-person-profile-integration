<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Migrations;

use SFPF\PersonProfile\PressReleases\PressReleaseSettings;

defined( 'ABSPATH' ) || exit;

/** Preserve the prior press-release form origin for sites upgrading without it. */
final class PressReleaseOriginMigration implements Migration {
    /** Identify this one-time migration. */
    public function id(): string {
        return 'press-release-default-origin';
    }

    /** Preserve the previous origin if the site has never saved one. */
    public function migrate(): bool {
        // Bug: older installations without a saved origin showed an empty form after upgrade.
        // Fix: seed the previously displayed origin if the option is absent.
        // Why this way: WordPress preserves existing values and the person's switches stay untouched.
        return PressReleaseSettings::seed_default_origin( 'https://hexaprwire.com' );
    }
}
