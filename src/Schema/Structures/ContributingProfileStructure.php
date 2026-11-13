<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Schema\Structures;

use Hexa\PluginCore\DataNormalization\FieldReader;
use Hexa\PluginCore\DataNormalization\ValueNormalizer;
use Hexa\PluginCore\SchemaTools\SchemaContext;
use SFPF\PersonProfile\ContentTypes\ContributingProfile\ContributingProfileContentType;
use SFPF\PersonProfile\Schema\PageSchema;

defined( 'ABSPATH' ) || exit;

/**
 * Each contributing profile's page, about the person and pointing at the
 * publication's profile. The profile links also join the person's profile
 * links on every page.
 */
final class ContributingProfileStructure implements SchemaStructure {
    public function definition(): array {
        return [
            'id'      => 'contributing-profile',
            'label'   => 'Contributing profiles',
            'where'   => 'Each contributing profile\'s page. Their links are also added to the person\'s profile links.',
            'types'   => 'WebPage about the person',
            'default' => 'on',
            'applies' => static fn( SchemaContext $context ): bool => $context->is_singular( ContributingProfileContentType::POST_TYPE ),
            'build'   => [ $this, 'build' ],
            'sample'  => static fn(): ?SchemaContext => PageSchema::sample_post( ContributingProfileContentType::POST_TYPE ),
        ];
    }

    /** @return list<array<string,mixed>> */
    public function build( SchemaContext $context, string $mode ): array {
        $link = ValueNormalizer::url( FieldReader::acf_value( 'url', $context->post_id(), '', true ) );
        return PageSchema::graph( $context, 'WebPage', [ 'significantLink' => '' !== $link ? $link : null ] );
    }
}
