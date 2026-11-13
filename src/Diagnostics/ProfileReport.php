<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Diagnostics;

use Hexa\PluginCore\SchemaTools\SchemaContext;
use Hexa\PluginCore\SchemaTools\SchemaStructures;
use SFPF\PersonProfile\ContentTypes\ContentTypeCatalog;
use SFPF\PersonProfile\Pages\PageTypes;
use SFPF\PersonProfile\Person\PersonFieldValues;
use SFPF\PersonProfile\Person\PersonResolver;
use SFPF\PersonProfile\PluginRuntime\PluginMetadata;
use SFPF\PersonProfile\Schema\SchemaCatalog;
use SFPF\PersonProfile\Shortcodes\Sections\SectionRegistry;

defined( 'ABSPATH' ) || exit;

/**
 * Everything the plugin knows about the site in one report: the person and
 * every value, each section's state, the linked pages, the content types and
 * the schema each key page carries. The Debug tab and the debug page show it.
 */
final class ProfileReport {
    /** @return array<string,mixed> */
    public static function build(): array {
        $profile = PersonResolver::profile();
        $values  = [];
        $sections = [];
        if ( null !== $profile ) {
            foreach ( array_keys( PersonFieldValues::FIELDS ) as $field ) {
                $value            = PersonFieldValues::get( $profile, $field, [ 'network' => 'linkedin' ] );
                $values[ $field ] = [ 'type' => $value->type, 'count' => $value->count(), 'text' => mb_substr( $value->plain( ' | ' ), 0, 300 ) ];
            }
            foreach ( SectionRegistry::all() as $id => $section ) {
                $sections[ $id ] = $section->has_content( $profile );
            }
        }
        $pages = [];
        foreach ( PageTypes::linked_pages() as $key => $page_id ) {
            $pages[ $key ] = [ 'id' => $page_id, 'url' => (string) get_permalink( $page_id ), 'status' => (string) get_post_status( $page_id ) ];
        }
        $types = [];
        foreach ( ContentTypeCatalog::TYPES as $type ) {
            $types[ $type::ID ] = post_type_exists( $type::POST_TYPE ) ? (int) ( wp_count_posts( $type::POST_TYPE )->publish ?? 0 ) : null;
        }
        $schema = [];
        foreach ( self::schema_pages() as $label => $context ) {
            $schema[ $label ] = [ 'url' => $context->url, 'document' => SchemaStructures::document( SchemaCatalog::OWNER, $context ) ];
        }
        return [
            'generated_at'  => gmdate( 'c' ),
            'plugin'        => PluginMetadata::VERSION,
            'person'        => null === $profile ? null : [ 'user_id' => $profile->user_id(), 'name' => $profile->name(), 'edit_url' => $profile->edit_url() ],
            'values'        => $values,
            'sections'      => $sections,
            'pages'         => $pages,
            'content_types' => $types,
            'structures'    => array_map( static fn( array $row ): array => [ 'id' => $row['id'], 'mode' => $row['mode'] ], \Hexa\PluginCore\SchemaTools\SchemaStructuresModule::rows( SchemaCatalog::OWNER ) ),
            'schema'        => $schema,
        ];
    }

    /** @return array<string,SchemaContext> The key pages to show schema for. */
    public static function schema_pages(): array {
        $pages = [ 'Homepage' => SchemaContext::for_front_page() ];
        foreach ( [ 'biography' => 'Biography', 'faqs' => 'FAQs', 'connect' => 'Connect' ] as $key => $label ) {
            $id = PageTypes::linked_pages()[ $key ] ?? 0;
            $context = $id > 0 ? SchemaContext::for_post( $id ) : null;
            if ( null !== $context ) {
                $pages[ $label ] = $context;
            }
        }
        return $pages;
    }
}
