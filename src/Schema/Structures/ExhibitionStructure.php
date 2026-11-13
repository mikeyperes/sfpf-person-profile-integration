<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Schema\Structures;

use Hexa\PluginCore\DataNormalization\FieldReader;
use Hexa\PluginCore\DataNormalization\ValueNormalizer;
use Hexa\PluginCore\SchemaTools\SchemaContext;
use Hexa\PluginCore\SchemaTools\SchemaIds;
use Hexa\PluginCore\SchemaTools\SchemaNodes;
use Hexa\PluginCore\SchemaTools\SchemaSources;
use SFPF\PersonProfile\ContentTypes\Exhibition\ExhibitionContentType;
use SFPF\PersonProfile\Schema\PageSchema;
use SFPF\PersonProfile\Schema\PersonSchema;

defined( 'ABSPATH' ) || exit;

/** Each exhibition as an ExhibitionEvent the person took part in, and the exhibitions list. */
final class ExhibitionStructure implements SchemaStructure {
    public function definition(): array {
        return [
            'id'      => 'exhibition',
            'label'   => 'Exhibitions',
            'where'   => 'Each exhibition\'s page, and the list of exhibitions.',
            'types'   => 'ItemPage, ExhibitionEvent (contributor: the person); CollectionPage, ItemList',
            'default' => 'on',
            'applies' => static fn( SchemaContext $context ): bool => $context->is_singular( ExhibitionContentType::POST_TYPE ) || $context->is_archive_of( ExhibitionContentType::POST_TYPE ),
            'build'   => [ $this, 'build' ],
            'sample'  => static fn(): ?SchemaContext => PageSchema::sample_post( ExhibitionContentType::POST_TYPE ),
        ];
    }

    /** @return list<array<string,mixed>> */
    public function build( SchemaContext $context, string $mode ): array {
        if ( ! $context->is_singular( ExhibitionContentType::POST_TYPE ) ) {
            return PageSchema::collection( $context, array_map( [ self::class, 'event' ], PageSchema::latest( ExhibitionContentType::POST_TYPE ) ) );
        }
        $event = self::event( $context->post() );
        $graph = PageSchema::graph( $context, 'ItemPage', [ 'mainEntity' => SchemaNodes::ref( (string) $event['@id'] ) ] );
        $graph[] = $event;
        return $graph;
    }

    public static function event( \WP_Post $post ): array {
        $id  = (int) $post->ID;
        $url = (string) get_permalink( $post );
        $get = static fn( string $name ): mixed => FieldReader::acf_value( $name, $id, '', true );
        $organizer_id = absint( $get( 'organizer_organization' ) );
        $organizer    = ValueNormalizer::text( $get( 'organizer' ) );
        return SchemaNodes::exhibition_event(
            [
                'name'          => (string) get_the_title( $post ),
                'start_date'    => $get( 'start_date' ),
                'end_date'      => $get( 'end_date' ),
                'description'   => SchemaSources::post_description( $post ),
                'url'           => '' !== ValueNormalizer::url( $get( 'url' ) ) ? $get( 'url' ) : $url,
                'venue'         => $get( 'venue' ),
                'city'          => $get( 'city' ),
                'address'       => $get( 'address' ),
                'organizer'     => '' !== $organizer ? $organizer : ( $organizer_id > 0 ? (string) get_the_title( $organizer_id ) : '' ),
                'organizer_url' => $organizer_id > 0 ? (string) get_permalink( $organizer_id ) : '',
                'works'         => ValueNormalizer::rows( $get( 'works_shown' ) ),
                'image'         => SchemaSources::post_image( $id ),
            ],
            SchemaIds::for_url( $url, 'event' ),
            [ 'contributor' => PersonSchema::exists() ? PersonSchema::ref() : null, 'mainEntityOfPage' => SchemaNodes::ref( SchemaIds::webpage( $url ) ) ]
        );
    }
}
