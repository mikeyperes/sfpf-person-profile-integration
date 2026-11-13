<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Schema\Structures;

use Hexa\PluginCore\DataNormalization\FieldReader;
use Hexa\PluginCore\DataNormalization\ValueNormalizer;
use Hexa\PluginCore\SchemaTools\SchemaContext;
use Hexa\PluginCore\SchemaTools\SchemaIds;
use Hexa\PluginCore\SchemaTools\SchemaNodes;
use SFPF\PersonProfile\ContentTypes\Quote\QuoteContentType;
use SFPF\PersonProfile\Person\PersonResolver;
use SFPF\PersonProfile\Schema\PageSchema;
use SFPF\PersonProfile\Schema\PersonSchema;

defined( 'ABSPATH' ) || exit;

/**
 * Each quote as a Quotation. A quote attributed to the person links to the
 * person; one attributed to someone else names them.
 */
final class QuoteStructure implements SchemaStructure {
    public function definition(): array {
        return [
            'id'      => 'quote',
            'label'   => 'Quotes',
            'where'   => 'Each quote\'s page.',
            'types'   => 'ItemPage, Quotation',
            'default' => 'on',
            'applies' => static fn( SchemaContext $context ): bool => $context->is_singular( QuoteContentType::POST_TYPE ),
            'build'   => [ $this, 'build' ],
            'sample'  => static fn(): ?SchemaContext => PageSchema::sample_post( QuoteContentType::POST_TYPE ),
        ];
    }

    /** @return list<array<string,mixed>> */
    public function build( SchemaContext $context, string $mode ): array {
        $post    = $context->post();
        $id      = (int) $post->ID;
        $name    = ValueNormalizer::text( FieldReader::acf_value( 'assigned_name', $id, '', true ) );
        $person  = PersonResolver::profile();
        $by_person = '' === $name || ( null !== $person && 0 === strcasecmp( $name, $person->name() ) );
        $quote   = SchemaNodes::quotation(
            [
                'text'         => FieldReader::acf_value( 'quote', $id, '', true ),
                'creator'      => $by_person && PersonSchema::exists() ? PersonSchema::ref() : null,
                'creator_name' => $by_person ? '' : $name,
                'url'          => FieldReader::acf_value( 'url', $id, '', true ),
                'publication'  => FieldReader::acf_value( 'publication_name', $id, '', true ),
                'date'         => get_the_date( 'Y-m-d', $post ),
            ],
            SchemaIds::for_url( $context->url, 'quote' )
        );
        $graph = PageSchema::graph( $context, 'ItemPage', [ 'mainEntity' => [] !== $quote ? SchemaNodes::ref( (string) $quote['@id'] ) : null ] );
        if ( [] !== $quote ) {
            $graph[] = $quote;
        }
        return $graph;
    }
}
