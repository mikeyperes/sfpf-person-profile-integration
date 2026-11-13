<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Schema\Structures;

use Hexa\PluginCore\SchemaTools\SchemaContext;
use Hexa\PluginCore\SchemaTools\SchemaStructures;
use SFPF\PersonProfile\Pages\PageTypes;
use SFPF\PersonProfile\Schema\SchemaCatalog;
use SFPF\PersonProfile\Schema\PageSchema;
use SFPF\PersonProfile\Schema\PersonSchema;
use SFPF\PersonProfile\Settings\Settings;

defined( 'ABSPATH' ) || exit;

/**
 * The website's other pages: each one is part of the person's website and
 * about the person, with its breadcrumb. Person pages get their own page type
 * (a contact page, a photo gallery, a list page).
 */
final class PersonPagesStructure implements SchemaStructure {
    /** Page type for the plugin's page keys; the rest are ordinary pages. */
    private const PAGE_TYPES = [
        'connect'         => 'ContactPage',
        'gallery'         => 'ImageGallery',
        'recent_articles' => 'CollectionPage',
        'additional_urls' => 'CollectionPage',
        'media'           => 'CollectionPage',
        'interviews'      => 'CollectionPage',
        'podcasts'        => 'CollectionPage',
        'organizations_founded' => 'CollectionPage',
    ];

    public function definition(): array {
        return [
            'id'      => 'person-pages',
            'label'   => 'Other pages',
            'where'   => 'Every other page of the website (not posts): each is described as part of the person\'s website and about the person, with its breadcrumb.',
            'modes'   => [
                'all'      => [ 'label' => 'Every page', 'types' => 'WebPage, ContactPage, ImageGallery or CollectionPage, Person, BreadcrumbList' ],
                'assigned' => [ 'label' => 'Only the person pages in Pages & Menus', 'types' => 'WebPage, ContactPage, ImageGallery or CollectionPage, Person, BreadcrumbList' ],
            ],
            'default' => 'all',
            'applies' => [ $this, 'applies' ],
            'build'   => [ $this, 'build' ],
            'sample'  => [ $this, 'sample' ],
        ];
    }

    public function applies( SchemaContext $context ): bool {
        if ( ! PersonSchema::exists() || ! $context->is_singular( 'page' ) || $context->post_id() === Settings::assigned_page( 'biography' ) ) {
            return false;
        }
        return 'assigned' !== SchemaStructures::mode( SchemaCatalog::OWNER, 'person-pages' ) || '' !== PageTypes::key_for_page( $context->post_id() );
    }

    /** @return list<array<string,mixed>> */
    public function build( SchemaContext $context, string $mode ): array {
        $key  = PageTypes::key_for_page( $context->post_id() );
        $type = self::PAGE_TYPES[ $key ] ?? ( PageTypes::is_profession_page( $context->post_id() ) ? 'AboutPage' : 'WebPage' );
        return PageSchema::graph( $context, $type );
    }

    public function sample(): ?SchemaContext {
        foreach ( [ 'connect', 'education', 'gallery', 'professions' ] as $key ) {
            $page = Settings::assigned_page( $key );
            if ( $page > 0 && 'publish' === get_post_status( $page ) ) {
                return SchemaContext::for_post( $page );
            }
        }
        $pages = get_posts( [ 'post_type' => 'page', 'post_status' => 'publish', 'posts_per_page' => 1, 'exclude' => array_filter( [ (int) get_option( 'page_on_front' ), Settings::assigned_page( 'biography' ) ] ), 'no_found_rows' => true ] );
        return [] !== $pages ? SchemaContext::for_post( $pages[0] ) : null;
    }
}
