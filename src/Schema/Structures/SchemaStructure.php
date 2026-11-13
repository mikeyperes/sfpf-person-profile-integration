<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Schema\Structures;

defined( 'ABSPATH' ) || exit;

/**
 * One schema structure of the person website, declared to Hexa WP Core's
 * SchemaStructures registry (see its definition keys).
 */
interface SchemaStructure {
    /** @return array<string,mixed> */
    public function definition(): array;
}
