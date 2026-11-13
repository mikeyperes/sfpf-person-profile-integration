<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Schema\Structures;

use Hexa\PluginCore\DataNormalization\FieldReader;
use Hexa\PluginCore\DataNormalization\ValueNormalizer;
use Hexa\PluginCore\SchemaTools\SchemaContext;
use Hexa\PluginCore\SchemaTools\SchemaIds;
use Hexa\PluginCore\SchemaTools\SchemaNodes;
use Hexa\PluginCore\SchemaTools\SchemaSources;
use SFPF\PersonProfile\ContentTypes\Book\BookContentType;
use SFPF\PersonProfile\Person\Links;
use SFPF\PersonProfile\Schema\PageSchema;
use SFPF\PersonProfile\Schema\PersonSchema;

defined( 'ABSPATH' ) || exit;

/** Each book's page (an ItemPage about the Book) and the books list page. */
final class BookStructure implements SchemaStructure {
    public function definition(): array {
        return [
            'id'      => 'book',
            'label'   => 'Books',
            'where'   => 'Each book\'s page, and the list of books.',
            'types'   => 'ItemPage, Book (author: the person); CollectionPage, ItemList',
            'default' => 'on',
            'applies' => static fn( SchemaContext $context ): bool => $context->is_singular( BookContentType::POST_TYPE ) || $context->is_archive_of( BookContentType::POST_TYPE ),
            'build'   => [ $this, 'build' ],
            'sample'  => static fn(): ?SchemaContext => PageSchema::sample_post( BookContentType::POST_TYPE ),
        ];
    }

    /** @return list<array<string,mixed>> */
    public function build( SchemaContext $context, string $mode ): array {
        if ( ! $context->is_singular( BookContentType::POST_TYPE ) ) {
            return PageSchema::collection( $context, array_map( [ self::class, 'book' ], PageSchema::latest( BookContentType::POST_TYPE ) ) );
        }
        $book  = self::book( $context->post() );
        $graph = PageSchema::graph( $context, 'ItemPage', [ 'mainEntity' => SchemaNodes::ref( (string) $book['@id'] ), 'about' => SchemaNodes::ref( (string) $book['@id'] ) ] );
        $graph[] = $book;
        return $graph;
    }

    /** One book as a Book node. */
    public static function book( \WP_Post $post ): array {
        $id   = (int) $post->ID;
        $url  = (string) get_permalink( $post );
        $get  = static fn( string $name ): mixed => FieldReader::acf_value( $name, $id, '', true );
        $kgid = Links::knowledge_graph_id( $get( 'knowledge_graph_id' ) );
        $publisher = ValueNormalizer::text( $get( 'publishing_company' ) );
        return SchemaNodes::book(
            [
                '@id'                 => SchemaIds::for_url( $url, 'book' ),
                'name'                => ValueNormalizer::text( get_the_title( $post ) ),
                'url'                 => $url,
                'alternativeHeadline' => ValueNormalizer::text( $get( 'subtitle' ) ),
                'alternateName'       => ValueNormalizer::single_or_array( ValueNormalizer::strings( $get( 'alternate_names' ) ) ),
                'description'         => ValueNormalizer::text( '' !== ValueNormalizer::text( $get( 'description' ) ) ? $get( 'description' ) : SchemaSources::post_description( $post ) ),
                'author'              => PersonSchema::exists() ? PersonSchema::ref() : null,
                'publisher'           => '' !== $publisher ? SchemaNodes::thing( $publisher, '', 'Organization' ) : null,
                'image'               => SchemaSources::post_image( $id ),
                'isbn'                => ValueNormalizer::text( $get( 'isbn' ) ),
                'numberOfPages'       => ValueNormalizer::number( $get( 'number_of_pages' ) ),
                'datePublished'       => ValueNormalizer::date( $get( 'date_published' ) ),
                'bookEdition'         => ValueNormalizer::text( $get( 'book_edition' ) ),
                'bookFormat'          => ValueNormalizer::text( $get( 'book_format' ) ),
                'inLanguage'          => '' !== ValueNormalizer::text( $get( 'in_language' ) ) ? ValueNormalizer::text( $get( 'in_language' ) ) : SchemaSources::language(),
                'genre'               => ValueNormalizer::text( $get( 'genre' ) ),
                'mainEntityOfPage'    => SchemaNodes::ref( SchemaIds::webpage( $url ) ),
                'sameAs'              => SchemaNodes::same_as( $get( 'amazon_url' ), $get( 'audible_url' ), $get( 'google_books_url' ), $get( 'goodreads_url' ), $get( 'knowledge_graph_url' ), Links::urls( $get( 'sameas_urls' ) ) ),
                'identifier'          => '' !== $kgid ? SchemaNodes::property_value( 'googleKgMID', $kgid, Links::knowledge_panel_url( $kgid ) ) : null,
            ]
        );
    }
}
