<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Migrations;

defined( 'ABSPATH' ) || exit;

/** One upgrade of stored data, run once per site. Never deletes the data it moves. */
interface Migration {
    /** A stable id; a new id runs again. */
    public function id(): string;

    /** Runs the upgrade; false leaves it to run again on the next request. */
    public function migrate(): bool;
}
