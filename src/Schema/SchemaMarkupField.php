<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Schema;

use Hexa\PluginCore\CoreContracts\ModuleInterface;
use Hexa\PluginCore\Fields\Hooks;

defined( 'ABSPATH' ) || exit;

/**
 * The read-only "Schema Markup" field on books shows the schema the book's
 * page carries, built when the editor opens. Nothing is stored.
 */
final class SchemaMarkupField implements ModuleInterface {
    public function register(): void {
        Hooks::on( 'load_value/key=field_sfpf_book_schema', [ $this, 'load' ], 10, 3 );
    }

    public function load( mixed $value, mixed $post_id = 0, array $field = [] ): string {
        $post_id = is_numeric( $post_id ) ? (int) $post_id : 0;
        if ( $post_id <= 0 || ! is_admin() ) {
            return '';
        }
        $document = SchemaCatalog::document_for_post( $post_id );
        return [] === $document ? '' : (string) wp_json_encode( $document, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
    }
}
