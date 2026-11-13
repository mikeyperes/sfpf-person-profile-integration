<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Schema\Structures;

use Hexa\PluginCore\DataNormalization\FieldReader;
use Hexa\PluginCore\DataNormalization\ValueNormalizer;
use Hexa\PluginCore\SchemaTools\SchemaContext;
use Hexa\PluginCore\SchemaTools\SchemaIds;
use Hexa\PluginCore\SchemaTools\SchemaNodes;
use Hexa\PluginCore\SchemaTools\SchemaSources;
use SFPF\PersonProfile\ContentTypes\Organization\OrganizationContentType;
use SFPF\PersonProfile\ContentTypes\Organization\OrganizationRecord;
use SFPF\PersonProfile\Person\Links;
use SFPF\PersonProfile\Person\PersonOrganizations;
use SFPF\PersonProfile\Person\PersonResolver;
use SFPF\PersonProfile\Schema\PageSchema;
use SFPF\PersonProfile\Schema\PersonSchema;

defined( 'ABSPATH' ) || exit;

/**
 * Each organization's page (an ItemPage about the Organization, with the
 * person as founder when they founded it) and the organizations list page.
 * The Organization's @id is the one the person's worksFor points to.
 */
final class OrganizationStructure implements SchemaStructure {
    public function definition(): array {
        return [
            'id'      => 'organization',
            'label'   => 'Organizations',
            'where'   => 'Each organization\'s page, and the list of organizations.',
            'types'   => 'ItemPage, Organization (founder: the person); CollectionPage, ItemList',
            'default' => 'on',
            'applies' => static fn( SchemaContext $context ): bool => $context->is_singular( OrganizationContentType::POST_TYPE ) || $context->is_archive_of( OrganizationContentType::POST_TYPE ),
            'build'   => [ $this, 'build' ],
            'sample'  => static fn(): ?SchemaContext => PageSchema::sample_post( OrganizationContentType::POST_TYPE ),
        ];
    }

    /** @return list<array<string,mixed>> */
    public function build( SchemaContext $context, string $mode ): array {
        if ( ! $context->is_singular( OrganizationContentType::POST_TYPE ) ) {
            return PageSchema::collection( $context, array_map( [ self::class, 'organization' ], PageSchema::latest( OrganizationContentType::POST_TYPE ) ) );
        }
        $organization = self::organization( $context->post() );
        if ( [] === $organization ) {
            return PageSchema::graph( $context, 'ItemPage' );
        }
        $graph   = PageSchema::graph( $context, 'ItemPage', [ 'mainEntity' => SchemaNodes::ref( (string) $organization['@id'] ), 'about' => SchemaNodes::ref( (string) $organization['@id'] ) ] );
        $graph[] = $organization;
        return $graph;
    }

    /** One organization record as an Organization node. */
    public static function organization( \WP_Post $post ): array {
        $record = OrganizationRecord::for_post( (int) $post->ID );
        if ( null === $record ) {
            return [];
        }
        $id      = $record->id;
        $url     = $record->permalink();
        $get     = static fn( string $name ): mixed => FieldReader::acf_value( $name, $id, '', true );
        $kgid    = Links::knowledge_graph_id( $get( 'knowledge_graph_id' ) );
        $hq      = $record->headquarters();
        $summary = ValueNormalizer::text( wp_strip_all_tags( $record->summary_html() ) );
        $founded = in_array( $id, PersonOrganizations::ids( PersonResolver::user_id() ), true ) && PersonSchema::exists();
        return SchemaNodes::organization(
            [
                '@id'              => SchemaIds::for_url( $url, 'organization' ),
                'name'             => $record->name(),
                'legalName'        => ValueNormalizer::text( $get( 'legal_name' ) ),
                'alternateName'    => ValueNormalizer::single_or_array( ValueNormalizer::strings( $get( 'alternate_names' ) ) ),
                'url'              => '' !== $record->website() ? $record->website() : $url,
                'description'      => '' !== $summary ? $summary : SchemaSources::post_description( $post ),
                'slogan'           => ValueNormalizer::text( $get( 'mission_statement' ) ),
                'logo'             => '' !== $record->logo_url( 'large' ) ? $record->logo_url( 'large' ) : null,
                'image'            => SchemaSources::post_image( $id ),
                'foundingDate'     => ValueNormalizer::date( $record->founding_date() ),
                'founder'          => $founded ? PersonSchema::ref() : null,
                'location'         => '' !== $hq['location'] ? SchemaNodes::place( [ 'name' => $hq['location'], 'wikipedia_url' => $hq['wikipedia_url'] ] ) : null,
                'address'          => SchemaNodes::postal_address( (array) $get( 'address' ) ),
                'contactPoint'     => SchemaNodes::contact_point( (array) $get( 'contact_point' ) ),
                'email'            => $get( 'email' ),
                'telephone'        => ValueNormalizer::text( $get( 'telephone' ) ),
                'numberOfEmployees' => null !== ValueNormalizer::number( $get( 'number_of_employees' ) ) ? [ '@type' => 'QuantitativeValue', 'value' => ValueNormalizer::number( $get( 'number_of_employees' ) ) ] : null,
                'naics'            => ValueNormalizer::text( $get( 'naics_code' ) ),
                'award'            => ValueNormalizer::single_or_array( ValueNormalizer::strings( $get( 'awards' ) ) ),
                'brand'            => array_values( array_filter( array_map( static fn( string $name ): array => SchemaNodes::brand( [ 'name' => $name ] ), ValueNormalizer::strings( $get( 'brands' ) ) ) ) ),
                'mainEntityOfPage' => SchemaNodes::ref( SchemaIds::webpage( $url ) ),
                'sameAs'           => SchemaNodes::same_as( $get( 'wikipedia_url' ), $get( 'url_wikipedia' ), $get( 'url_linkedin' ), $get( 'url_crunchbase' ), $get( 'url_facebook' ), $get( 'url_instagram' ), $get( 'url_x' ), $get( 'url_youtube' ), $get( 'url_tiktok' ), $get( 'url_github' ), Links::urls( $get( 'social_urls' ) ), Links::urls( $get( 'sameas_urls' ) ), '' !== $kgid ? Links::knowledge_panel_url( $kgid ) : '' ),
                'identifier'       => '' !== $kgid ? SchemaNodes::property_value( 'googleKgMID', $kgid, Links::knowledge_panel_url( $kgid ) ) : null,
            ]
        );
    }
}
