<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Migrations;

use Hexa\PluginCore\CoreContracts\ModuleInterface;
use SFPF\PersonProfile\Fields\FieldKeys;

defined( 'ABSPATH' ) || exit;

/** Registers the operator-run field-key migration and supports forms opened before it. */
final class FieldKeyMigrationModule implements ModuleInterface {
    /** Adds WordPress hooks for the migration and its form compatibility. */
    public function register(): void {
        add_action( 'init', [ FieldKeys::class, 'normalize_submission' ], 0 );
        if ( defined( 'WP_CLI' ) && WP_CLI ) {
            add_action( 'init', [ $this, 'register_command' ] );
        }
    }

    /** Registers the dry run and the backup-gated application command. */
    public function register_command(): void {
        \WP_CLI::add_command(
            'sfpf field-keys',
            [ $this, 'run' ],
            [
                'shortdesc' => 'Dry run or apply the SFPF field-key migration after verifying the site backup.',
                'synopsis' => [
                    [ 'type' => 'flag', 'name' => 'dry-run', 'optional' => true ],
                    [ 'type' => 'flag', 'name' => 'backup-verified', 'optional' => true ],
                ],
            ]
        );
    }

    /** Runs the selected mode and reports the exact reference rows. */
    public function run( array $args, array $options ): void {
        $dry_run = isset( $options['dry-run'] );
        if ( $dry_run === isset( $options['backup-verified'] ) ) {
            \WP_CLI::error( 'Select --dry-run or --backup-verified after Backup Manager verifies both site backups.' );
        }

        try {
            $report = ( new FieldKeyMigration() )->run( $dry_run );
            \WP_CLI::line( (string) wp_json_encode( $report ) );
        } catch ( \Throwable $error ) {
            \WP_CLI::error( $error->getMessage() );
        }
    }
}
