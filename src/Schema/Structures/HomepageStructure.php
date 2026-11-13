<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Schema\Structures;

use Hexa\PluginCore\SchemaTools\SchemaContext;
use SFPF\PersonProfile\Schema\PageSchema;
use SFPF\PersonProfile\Schema\PersonSchema;
use SFPF\PersonProfile\Settings\Settings;

defined( 'ABSPATH' ) || exit;

/** The front page: a ProfilePage whose main entity is the person. */
final class HomepageStructure implements SchemaStructure {
    public function definition(): array {
        return [
            'id'      => 'homepage',
            'label'   => 'Homepage',
            'where'   => 'The front page: the person\'s official page, the one search engines and the Knowledge Panel tie the person to.',
            'modes'   => [
                'profile_page' => [ 'label' => 'Profile page about the person', 'types' => 'WebSite, ProfilePage, Person' ],
                'person'       => [ 'label' => 'Person only', 'types' => 'WebSite, Person' ],
            ],
            'default' => 'profile_page',
            'option'  => Settings::HOMEPAGE_SCHEMA_MODE,
            'applies' => static fn( SchemaContext $context ): bool => $context->is_front_page() && PersonSchema::exists(),
            'build'   => [ $this, 'build' ],
            'sample'  => static fn(): SchemaContext => SchemaContext::for_front_page(),
        ];
    }

    /** @return list<array<string,mixed>> */
    public function build( SchemaContext $context, string $mode ): array {
        if ( 'person' === $mode ) {
            return array_values( array_filter( [ PersonSchema::website(), PersonSchema::node() ] ) );
        }
        return PageSchema::graph( $context, 'ProfilePage', [ 'mainEntity' => PersonSchema::ref() ] );
    }
}
