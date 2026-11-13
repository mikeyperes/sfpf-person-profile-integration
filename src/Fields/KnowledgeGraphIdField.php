<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Fields;

use Hexa\PluginCore\CoreContracts\ModuleInterface;
use Hexa\PluginCore\Fields\Hooks;
use SFPF\PersonProfile\Person\Links;
use SFPF\PersonProfile\Person\PersonFields;

defined( 'ABSPATH' ) || exit;

/**
 * Every Knowledge Graph ID field (the person's and each book's) saves the ID
 * itself, even when a whole Google search link is pasted in.
 */
final class KnowledgeGraphIdField implements ModuleInterface {
    public function register(): void {
        Hooks::on( 'update_value/name=' . PersonFields::KNOWLEDGE_GRAPH_ID, [ $this, 'sanitize' ], 10, 3 );
    }

    public function sanitize( mixed $value, mixed $post_id = 0, array $field = [] ): mixed {
        if ( ! is_string( $value ) || '' === trim( $value ) ) {
            return $value;
        }
        $id = Links::knowledge_graph_id( $value );
        return '' !== $id ? $id : trim( $value );
    }
}
