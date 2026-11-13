<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Migrations;

use Hexa\PluginCore\CoreContracts\ModuleInterface;
use Hexa\PluginCore\Fields\Hooks;
use SFPF\PersonProfile\Settings\Settings;
use SFPF\PersonProfile\Support\ActivityLog;

defined( 'ABSPATH' ) || exit;

/**
 * Runs each upgrade once, after the fields are ready, on an admin, cron or
 * command-line request (never on a visitor's page load), and remembers it.
 */
final class MigrationRunner implements ModuleInterface {
    /** @return list<Migration> */
    public static function migrations(): array {
        return [
            new OrganizationOwnershipMigration(),
            new ArticlesRepeaterMigration(),
            new ActivityLogFormatMigration(),
            new SchemaModesMigration(),
            new BookQuoteRepeaterMigration(),
            new RewriteRulesMigration(),
        ];
    }

    public function register(): void {
        if ( is_admin() || wp_doing_cron() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
            Hooks::on( 'init', [ self::class, 'run' ], 20 );
        }
    }

    public static function run(): void {
        $done = array_flip( (array) get_option( Settings::MIGRATIONS, [] ) );
        $changed = false;
        foreach ( self::migrations() as $migration ) {
            if ( isset( $done[ $migration->id() ] ) ) {
                continue;
            }
            if ( $migration->migrate() ) {
                $done[ $migration->id() ] = true;
                $changed = true;
                ActivityLog::add( 'Upgrade done: ' . $migration->id(), 'success' );
            }
        }
        if ( $changed ) {
            update_option( Settings::MIGRATIONS, array_keys( $done ), false );
        }
    }
}
