<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Schema;

use Hexa\PluginCore\SchemaTools\SchemaContext;
use Hexa\PluginCore\SchemaTools\SchemaIds;
use Hexa\PluginCore\SchemaTools\SchemaNodes;
use Hexa\PluginCore\SchemaTools\SchemaSources;

defined( 'ABSPATH' ) || exit;

/**
 * The page-level nodes every person website page shares: the WebSite, the
 * page itself (linked to the person), its breadcrumb and its main image.
 */
final class PageSchema {
    /**
     * The page node for a context.
     *
     * @param array<string,mixed> $properties Extra or overriding page properties.
     */
    public static function page( SchemaContext $context, string $type = 'WebPage', array $properties = [] ): array {
        $post = $context->post();
        $url  = '' !== $context->url ? $context->url : SchemaIds::home();
        $breadcrumb = self::breadcrumb( $context );
        $properties += [
            'about'      => PersonSchema::exists() ? PersonSchema::ref() : null,
            'breadcrumb' => [] !== $breadcrumb ? SchemaNodes::ref( (string) $breadcrumb['@id'] ) : null,
        ];
        if ( null !== $post ) {
            $image = SchemaSources::post_image( (int) $post->ID, SchemaIds::primary_image( $url ) );
            if ( [] !== $image ) {
                $properties += [ 'primaryImageOfPage' => SchemaNodes::ref( (string) $image['@id'] ), 'image' => SchemaNodes::ref( (string) $image['@id'] ) ];
            }
            $node = SchemaSources::post_page( $post, $type, $properties + [ '@id' => SchemaIds::webpage( $url ), 'url' => $url ] );
            return $node;
        }
        return SchemaNodes::web_page(
            $properties + [
                '@id'        => SchemaIds::webpage( $url ),
                'url'        => $url,
                'name'       => $context->is_front_page() ? (string) get_bloginfo( 'name' ) : SchemaSources::archive_title(),
                'isPartOf'   => SchemaNodes::ref( SchemaIds::website() ),
                'inLanguage' => SchemaSources::language(),
            ],
            $type
        );
    }

    /**
     * The website, the page, the person, the breadcrumb and the page's
     * featured image: the base graph of a page.
     *
     * @param array<string,mixed> $properties
     * @return list<array<string,mixed>>
     */
    public static function graph( SchemaContext $context, string $type = 'WebPage', array $properties = [] ): array {
        $post = $context->post();
        $url  = '' !== $context->url ? $context->url : SchemaIds::home();
        return array_values(
            array_filter(
                [
                    PersonSchema::website(),
                    self::page( $context, $type, $properties ),
                    PersonSchema::node(),
                    self::breadcrumb( $context ),
                    null !== $post ? SchemaSources::post_image( (int) $post->ID, SchemaIds::primary_image( $url ) ) : [],
                ]
            )
        );
    }

    /** Home, the page's parents and the page; or home and a list page. */
    public static function breadcrumb( SchemaContext $context ): array {
        if ( $context->is_front_page() ) {
            return [];
        }
        $id   = SchemaIds::breadcrumb( '' !== $context->url ? $context->url : SchemaIds::home() );
        $post = $context->post();
        if ( null !== $post ) {
            return SchemaSources::post_breadcrumb( (int) $post->ID, $id );
        }
        $name = match ( true ) {
            $context->object instanceof \WP_Post_Type => (string) $context->object->labels->name,
            $context->object instanceof \WP_User      => (string) $context->object->display_name,
            $context->object instanceof \WP_Term      => (string) $context->object->name,
            default                                   => '',
        };
        return '' === $name ? [] : SchemaNodes::breadcrumb(
            [
                [ 'name' => (string) get_bloginfo( 'name' ), 'url' => SchemaIds::home() ],
                [ 'name' => $name, 'url' => $context->url ],
            ],
            $id
        );
    }

    /**
     * A list page of a content type: a CollectionPage with its items.
     *
     * @param list<array<string,mixed>> $items Nodes or url/name pairs.
     * @return list<array<string,mixed>>
     */
    public static function collection( SchemaContext $context, array $items ): array {
        $list = SchemaNodes::item_list( $items, SchemaIds::for_url( $context->url, 'itemlist' ) );
        $graph = self::graph( $context, 'CollectionPage', [ 'mainEntity' => [] !== $list ? SchemaNodes::ref( (string) $list['@id'] ) : null ] );
        if ( [] !== $list ) {
            $graph[] = $list;
        }
        return $graph;
    }

    /**
     * The newest published posts of a type, for a list page.
     *
     * @return list<\WP_Post>
     */
    public static function latest( string $post_type, int $limit = 50 ): array {
        return get_posts(
            [
                'post_type'      => $post_type,
                'post_status'    => 'publish',
                'posts_per_page' => $limit,
                'orderby'        => 'date',
                'order'          => 'DESC',
                'no_found_rows'  => true,
            ]
        );
    }

    /** The first published post of a type, to preview its structure on. */
    public static function sample_post( string $post_type ): ?SchemaContext {
        if ( ! post_type_exists( $post_type ) ) {
            return null;
        }
        $posts = self::latest( $post_type, 1 );
        return [] !== $posts ? SchemaContext::for_post( $posts[0] ) : null;
    }
}
