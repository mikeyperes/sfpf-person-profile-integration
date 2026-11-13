<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Schema\Structures;

use Hexa\PluginCore\SchemaTools\SchemaContext;
use SFPF\PersonProfile\Schema\PageSchema;
use SFPF\PersonProfile\Schema\PersonSchema;
use SFPF\PersonProfile\Settings\Settings;

defined( 'ABSPATH' ) || exit;

/** The biography page: the person's full profile. */
final class BiographyStructure implements SchemaStructure {
    public function definition(): array {
        return [
            'id'      => 'biography',
            'label'   => 'Biography page',
            'where'   => 'The page assigned as the Biography in Pages & Menus.',
            'modes'   => [
                'profile_page' => [ 'label' => 'Profile page about the person', 'types' => 'ProfilePage, Person, BreadcrumbList' ],
                'about_page'   => [ 'label' => 'About page about the person', 'types' => 'AboutPage, Person, BreadcrumbList' ],
                'person'       => [ 'label' => 'Person only', 'types' => 'Person' ],
            ],
            'default' => 'profile_page',
            'option'  => Settings::BIOGRAPHY_SCHEMA_MODE,
            'applies' => static fn( SchemaContext $context ): bool => PersonSchema::exists() && $context->is_singular( 'page' ) && $context->post_id() === Settings::assigned_page( 'biography' ),
            'build'   => [ $this, 'build' ],
            'sample'  => static fn(): ?SchemaContext => Settings::assigned_page( 'biography' ) > 0 ? SchemaContext::for_post( Settings::assigned_page( 'biography' ) ) : null,
        ];
    }

    /** @return list<array<string,mixed>> */
    public function build( SchemaContext $context, string $mode ): array {
        if ( 'person' === $mode ) {
            return array_values( array_filter( [ PersonSchema::website(), PersonSchema::node() ] ) );
        }
        return PageSchema::graph( $context, 'about_page' === $mode ? 'AboutPage' : 'ProfilePage', [ 'mainEntity' => PersonSchema::ref() ] );
    }
}
