<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Schema\Structures;

use Hexa\PluginCore\DataNormalization\FieldReader;
use Hexa\PluginCore\DataNormalization\ValueNormalizer;
use Hexa\PluginCore\SchemaTools\SchemaContext;
use Hexa\PluginCore\SchemaTools\SchemaIds;
use Hexa\PluginCore\SchemaTools\SchemaNodes;
use Hexa\PluginCore\SchemaTools\SchemaSources;
use SFPF\PersonProfile\ContentTypes\Interview\InterviewContentType;
use SFPF\PersonProfile\Person\Links;
use SFPF\PersonProfile\Schema\PageSchema;
use SFPF\PersonProfile\Schema\PersonSchema;

defined( 'ABSPATH' ) || exit;

/** Each interview as a creative work about the person, linked to where it was published. */
final class InterviewStructure implements SchemaStructure {
    public function definition(): array {
        return [
            'id'      => 'interview',
            'label'   => 'Interviews',
            'where'   => 'Each interview\'s page.',
            'types'   => 'ItemPage, CreativeWork (about the person)',
            'default' => 'on',
            'applies' => static fn( SchemaContext $context ): bool => $context->is_singular( InterviewContentType::POST_TYPE ),
            'build'   => [ $this, 'build' ],
            'sample'  => static fn(): ?SchemaContext => PageSchema::sample_post( InterviewContentType::POST_TYPE ),
        ];
    }

    /** @return list<array<string,mixed>> */
    public function build( SchemaContext $context, string $mode ): array {
        $post  = $context->post();
        $id    = (int) $post->ID;
        $get   = static fn( string $name ): string => ValueNormalizer::text( FieldReader::acf_value( $name, $id, '', true ) );
        $show  = $get( 'podcast_name' );
        $host  = $get( 'host_name' );
        $work  = SchemaNodes::creative_work(
            [
                '@id'         => SchemaIds::for_url( $context->url, 'interview' ),
                'name'        => (string) get_the_title( $post ),
                'description' => SchemaSources::post_description( $post ),
                'about'       => PersonSchema::exists() ? PersonSchema::ref() : null,
                'contributor' => PersonSchema::exists() ? PersonSchema::ref() : null,
                'creator'     => '' !== $host ? SchemaNodes::thing( $host, '', 'Person' ) : null,
                'isPartOf'    => '' !== $show ? SchemaNodes::thing( $show, '', 'CreativeWorkSeries' ) : null,
                'sameAs'      => array_merge( Links::urls( FieldReader::acf_value( 'primary_url', $id, '', true ) ), Links::urls( FieldReader::acf_value( 'additional_links', $id, '', true ) ) ),
                'image'       => SchemaSources::post_image( $id ),
                'datePublished' => get_the_date( DATE_W3C, $post ),
                'mainEntityOfPage' => SchemaNodes::ref( SchemaIds::webpage( $context->url ) ),
            ]
        );
        $graph = PageSchema::graph( $context, 'ItemPage', [ 'mainEntity' => [] !== $work ? SchemaNodes::ref( (string) $work['@id'] ) : null ] );
        if ( [] !== $work ) {
            $graph[] = $work;
        }
        return $graph;
    }
}
