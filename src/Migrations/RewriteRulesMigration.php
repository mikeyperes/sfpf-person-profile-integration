<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Migrations;

defined( 'ABSPATH' ) || exit;

/** Refreshes the site's addresses once after 4.0, for the profile report page and the content types. */
final class RewriteRulesMigration implements Migration {
    public function id(): string {
        return 'rewrite-rules-4.0';
    }

    public function migrate(): bool {
        if ( ! did_action( 'init' ) ) {
            return false;
        }
        add_action( 'wp_loaded', static fn() => flush_rewrite_rules( false ) );
        return true;
    }
}
