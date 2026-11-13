<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Schema\Structures;

use Hexa\PluginCore\SchemaTools\SchemaContext;
use Hexa\PluginCore\SchemaTools\SchemaIds;
use Hexa\PluginCore\SchemaTools\SchemaNodes;
use Hexa\PluginCore\SchemaTools\SchemaSources;
use SFPF\PersonProfile\Person\PersonResolver;
use SFPF\PersonProfile\Schema\PageSchema;
use SFPF\PersonProfile\Schema\PersonSchema;

defined( 'ABSPATH' ) || exit;

/**
 * Author pages: a ProfilePage about the author. The website's person is the
 * same Person node as everywhere else; other authors get their own.
 */
final class AuthorProfileStructure implements SchemaStructure {
    public function definition(): array {
        return [
            'id'      => 'author-profile',
            'label'   => 'Author pages',
            'where'   => 'Each author\'s page (/author/name/). Rank Math\'s schema is left off these pages.',
            'types'   => 'ProfilePage, Person, BreadcrumbList',
            'default' => 'on',
            'applies' => static fn( SchemaContext $context ): bool => SchemaContext::AUTHOR === $context->kind && $context->object instanceof \WP_User,
            'build'   => [ $this, 'build' ],
            'sample'  => static function (): ?SchemaContext {
                $user_id = PersonResolver::user_id();
                return $user_id > 0 ? new SchemaContext( SchemaContext::AUTHOR, get_userdata( $user_id ) ?: null, (string) get_author_posts_url( $user_id ) ) : null;
            },
        ];
    }

    /** @return list<array<string,mixed>> */
    public function build( SchemaContext $context, string $mode ): array {
        $user = $context->object;
        if ( ! $user instanceof \WP_User ) {
            return [];
        }
        $is_person = (int) $user->ID === PersonResolver::user_id() && PersonSchema::exists();
        $person    = $is_person ? PersonSchema::node() : SchemaSources::user_person( (int) $user->ID, SchemaIds::for_url( $context->url, 'person' ) );
        if ( [] === $person ) {
            return [];
        }
        $page = PageSchema::page( $context, 'ProfilePage', [ 'mainEntity' => SchemaNodes::ref( (string) $person['@id'] ), 'about' => SchemaNodes::ref( (string) $person['@id'] ), 'name' => $user->display_name ] );
        return array_values( array_filter( [ PersonSchema::website(), $page, $person, PageSchema::breadcrumb( $context ) ] ) );
    }
}
