<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Schema\Structures;

use Hexa\PluginCore\DataNormalization\FieldReader;
use Hexa\PluginCore\DataNormalization\ValueNormalizer;
use Hexa\PluginCore\SchemaTools\SchemaContext;
use Hexa\PluginCore\SchemaTools\SchemaIds;
use Hexa\PluginCore\SchemaTools\SchemaNodes;
use Hexa\PluginCore\SchemaTools\SchemaSources;
use SFPF\PersonProfile\ContentTypes\PressRelease\PressReleaseContentType;
use SFPF\PersonProfile\Schema\PageSchema;
use SFPF\PersonProfile\Schema\PersonSchema;

defined( 'ABSPATH' ) || exit;

/** Each press release as a NewsArticle about the person, published on the person's site. */
final class PressReleaseStructure implements SchemaStructure {
    public function definition(): array {
        return [
            'id'      => 'press-release',
            'label'   => 'Press releases',
            'where'   => 'Each press release\'s page.',
            'types'   => 'WebPage, NewsArticle (about and published by the person)',
            'default' => 'on',
            'applies' => static fn( SchemaContext $context ): bool => $context->is_singular( PressReleaseContentType::POST_TYPE ),
            'build'   => [ $this, 'build' ],
            'sample'  => static fn(): ?SchemaContext => PageSchema::sample_post( PressReleaseContentType::POST_TYPE ),
        ];
    }

    /** @return list<array<string,mixed>> */
    public function build( SchemaContext $context, string $mode ): array {
        $post   = $context->post();
        $id     = (int) $post->ID;
        $url    = $context->url;
        $source = ValueNormalizer::text( FieldReader::acf_value( 'sfpf_press_release_source_name', $id, '', true ) );
        $date   = ValueNormalizer::date( FieldReader::acf_value( 'sfpf_press_release_date', $id, '', true ) );
        $article = SchemaNodes::article(
            [
                '@id'              => SchemaIds::article( $url ),
                'headline'         => (string) get_the_title( $post ),
                'url'              => $url,
                'description'      => SchemaSources::post_description( $post ),
                'datePublished'    => '' !== $date ? $date : get_the_date( DATE_W3C, $post ),
                'dateModified'     => get_the_modified_date( DATE_W3C, $post ),
                'image'            => SchemaSources::post_image( $id ),
                'about'            => PersonSchema::exists() ? PersonSchema::ref() : null,
                'author'           => '' !== $source ? SchemaNodes::thing( $source, '', 'Organization' ) : ( PersonSchema::exists() ? PersonSchema::ref() : null ),
                'publisher'        => PersonSchema::exists() ? PersonSchema::ref() : null,
                'isBasedOn'        => ValueNormalizer::url( FieldReader::acf_value( 'sfpf_press_release_original_url', $id, '', true ) ),
                'mainEntityOfPage' => SchemaNodes::ref( SchemaIds::webpage( $url ) ),
                'inLanguage'       => SchemaSources::language(),
            ],
            'NewsArticle'
        );
        $graph   = PageSchema::graph( $context );
        $graph[] = $article;
        return $graph;
    }
}
