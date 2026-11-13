<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Schema\Structures;

use Hexa\PluginCore\DataNormalization\FieldReader;
use Hexa\PluginCore\DataNormalization\ValueNormalizer;
use Hexa\PluginCore\SchemaTools\SchemaContext;
use Hexa\PluginCore\SchemaTools\SchemaNodes;
use SFPF\PersonProfile\ContentTypes\Award\AwardContentType;
use SFPF\PersonProfile\Schema\PageSchema;

defined( 'ABSPATH' ) || exit;

/**
 * Each award's page and the awards list. schema.org has no Award type: the
 * award is text on the Person (added from every award record), and its page
 * is an ItemPage about the person.
 */
final class AwardStructure implements SchemaStructure {
    public function definition(): array {
        return [
            'id'      => 'award',
            'label'   => 'Awards',
            'where'   => 'Each award\'s page, and the list of awards. Every award is also added to the person\'s awards.',
            'types'   => 'ItemPage about the person; CollectionPage, ItemList',
            'default' => 'on',
            'applies' => static fn( SchemaContext $context ): bool => $context->is_singular( AwardContentType::POST_TYPE ) || $context->is_archive_of( AwardContentType::POST_TYPE ),
            'build'   => [ $this, 'build' ],
            'sample'  => static fn(): ?SchemaContext => PageSchema::sample_post( AwardContentType::POST_TYPE ),
        ];
    }

    /** @return list<array<string,mixed>> */
    public function build( SchemaContext $context, string $mode ): array {
        if ( ! $context->is_singular( AwardContentType::POST_TYPE ) ) {
            return PageSchema::collection( $context, array_map( static fn( \WP_Post $post ): array => [ 'name' => self::text( $post ), 'url' => (string) get_permalink( $post ) ], PageSchema::latest( AwardContentType::POST_TYPE ) ) );
        }
        $source = ValueNormalizer::url( FieldReader::acf_value( 'url', $context->post_id(), '', true ) );
        return PageSchema::graph( $context, 'ItemPage', [ 'description' => self::text( $context->post() ), 'citation' => '' !== $source ? $source : null ] );
    }

    /** The award as text: "Name, Given By (Year)". */
    public static function text( \WP_Post $post ): string {
        $id       = (int) $post->ID;
        $given_by = ValueNormalizer::text( FieldReader::acf_value( 'given_by', $id, '', true ) );
        $org      = absint( FieldReader::acf_value( 'given_by_organization', $id, 0, true ) );
        return SchemaNodes::award_text(
            [
                'name'     => (string) get_the_title( $post ),
                'given_by' => '' !== $given_by ? $given_by : ( $org > 0 ? (string) get_the_title( $org ) : '' ),
                'year'     => (string) FieldReader::acf_value( 'year', $id, '', true ),
            ]
        );
    }
}
