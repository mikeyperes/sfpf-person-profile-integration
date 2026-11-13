<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Schema;

use Hexa\PluginCore\CoreContracts\ModuleInterface;
use Hexa\PluginCore\SchemaTools\SchemaContext;
use Hexa\PluginCore\SchemaTools\SchemaStructures;
use SFPF\PersonProfile\PluginRuntime\PluginMetadata;
use SFPF\PersonProfile\Schema\Structures\AuthorProfileStructure;
use SFPF\PersonProfile\Schema\Structures\AwardStructure;
use SFPF\PersonProfile\Schema\Structures\BiographyStructure;
use SFPF\PersonProfile\Schema\Structures\BookStructure;
use SFPF\PersonProfile\Schema\Structures\ContributingProfileStructure;
use SFPF\PersonProfile\Schema\Structures\ExhibitionStructure;
use SFPF\PersonProfile\Schema\Structures\FaqStructure;
use SFPF\PersonProfile\Schema\Structures\HomepageStructure;
use SFPF\PersonProfile\Schema\Structures\InterviewStructure;
use SFPF\PersonProfile\Schema\Structures\OrganizationStructure;
use SFPF\PersonProfile\Schema\Structures\PersonPagesStructure;
use SFPF\PersonProfile\Schema\Structures\PressReleaseStructure;
use SFPF\PersonProfile\Schema\Structures\QuoteStructure;
use SFPF\PersonProfile\Schema\Structures\SchemaStructure;

defined( 'ABSPATH' ) || exit;

/**
 * Every schema structure of the person website, declared to Hexa WP Core's
 * SchemaStructures registry. Core prints them in the one shared schema block
 * of each page (source "sfpf-person"), keeps each structure's mode, and gives
 * the plugin its Schema panel. Every structure is on when the plugin is.
 */
final class SchemaCatalog implements ModuleInterface {
    public const OWNER    = 'sfpf-person';
    public const PRIORITY = 10;

    /** @return list<SchemaStructure> */
    public static function structures(): array {
        return [
            new HomepageStructure(),
            new BiographyStructure(),
            new PersonPagesStructure(),
            new FaqStructure(),
            new BookStructure(),
            new OrganizationStructure(),
            new AwardStructure(),
            new ExhibitionStructure(),
            new QuoteStructure(),
            new PressReleaseStructure(),
            new InterviewStructure(),
            new ContributingProfileStructure(),
            new AuthorProfileStructure(),
        ];
    }

    public function register(): void {
        SchemaStructures::register(
            self::OWNER,
            [
                'plugin'       => PluginMetadata::NAME,
                'settings_url' => PluginMetadata::admin_url( 'schema' ),
                'priority'     => self::PRIORITY,
                'structures'   => array_map( static fn( SchemaStructure $structure ): array => $structure->definition(), self::structures() ),
            ]
        );
    }

    /**
     * The schema this plugin gives one post's page, as printed there.
     *
     * @return array<string,mixed>
     */
    public static function document_for_post( int $post_id ): array {
        $context = SchemaContext::for_post( $post_id );
        return null === $context ? [] : SchemaStructures::document( self::OWNER, $context );
    }
}
