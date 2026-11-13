<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Pages;

use Hexa\PluginCore\SiteStructure\PageStructureManager;
use SFPF\PersonProfile\Settings\Settings;
use SFPF\PersonProfile\Support\ActivityLog;

defined( 'ABSPATH' ) || exit;

/**
 * The person pages and menus through Hexa WP Core's page manager: linking,
 * creating and deleting pages, applying their templates, and building the
 * header, footer and sub-footer menus from them.
 */
final class SiteStructure {
    private static ?PageStructureManager $manager = null;

    public static function manager(): PageStructureManager {
        return self::$manager ??= new PageStructureManager(
            [
                'pages'                  => PageTypes::structure(),
                'menu_structures'        => self::menus(),
                'option_prefix'          => Settings::PAGE_ASSIGNMENT_PREFIX,
                'template_option_prefix' => Settings::PAGE_TEMPLATE_PREFIX,
                'managed_meta_key'       => '_sfpf_managed_page',
                'managed_key_meta_key'   => '_sfpf_page_key',
                'default_templates'      => PageTemplates::contents(),
                'logger'                 => static fn( mixed $message ) => ActivityLog::add( (string) $message ),
            ]
        );
    }

    /** @return array<string,array{title:string,description:string,page_keys:list<string>}> */
    public static function menus(): array {
        return [
            'header'     => [ 'title' => 'Header Menu', 'description' => 'The main navigation: the person\'s key pages.', 'page_keys' => [ 'biography', 'professions', 'recent_articles', 'additional_urls', 'gallery', 'connect' ] ],
            'footer'     => [ 'title' => 'Footer Menu', 'description' => 'Every profile page, for visitors and search engines.', 'page_keys' => [ 'biography', 'education', 'organizations_founded', 'professions', 'recent_articles', 'additional_urls', 'gallery', 'faqs', 'connect' ] ],
            'sub_footer' => [ 'title' => 'Sub-Footer Menu', 'description' => 'Small supporting profile pages.', 'page_keys' => [ 'alternate_names', 'location_born' ] ],
        ];
    }
}
