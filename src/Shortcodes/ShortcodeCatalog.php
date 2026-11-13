<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Shortcodes;

use Hexa\PluginCore\CoreContracts\ModuleInterface;
use Hexa\PluginCore\ShortcodeRegistry\ShortcodeDefinition;
use Hexa\PluginCore\ShortcodeRegistry\ShortcodeRegistry;
use SFPF\PersonProfile\Person\PersonFieldValues;
use SFPF\PersonProfile\Shortcodes\Sections\SectionRegistry;

defined( 'ABSPATH' ) || exit;

/**
 * Every SFPF shortcode: the tags and their handlers, the older tags kept for
 * existing pages, and the catalog the Shortcodes tab shows. All shortcodes
 * share one attribute set (see Attributes).
 */
final class ShortcodeCatalog implements ModuleInterface {
    /** Tag => handler. */
    private const TAGS = [
        PersonShortcode::TAG        => [ PersonShortcode::class, 'render' ],
        PersonSectionShortcode::TAG => [ PersonSectionShortcode::class, 'render' ],
        BookShortcode::TAG          => [ BookShortcode::class, 'render' ],
        OrganizationShortcode::TAG  => [ OrganizationShortcode::class, 'render' ],
        ContentLoopShortcode::TAG   => [ ContentLoopShortcode::class, 'render' ],
        FaqSetShortcode::TAG        => [ FaqSetShortcode::class, 'render' ],
        // Older tags, kept for the pages that use them.
        'founder'                   => [ LegacyShortcodes::class, 'founder' ],
        'person_faq'                => [ LegacyShortcodes::class, 'person_faq' ],
        'sfpf_person_faq'           => [ LegacyShortcodes::class, 'person_faq' ],
        'sfpf_faq'                  => [ LegacyShortcodes::class, 'faq_set' ],
        'sfpf_faq_schema'           => [ LegacyShortcodes::class, 'faq_schema' ],
        'sfpf_loop'                 => [ LegacyShortcodes::class, 'loop' ],
        ElementorFaqShortcode::TAG  => [ ElementorFaqShortcode::class, 'render' ],
    ];

    public function register(): void {
        // Late, so SFPF's [organization] replaces any older registration of the tag.
        add_action( 'init', [ self::class, 'add_shortcodes' ], 100 );
    }

    public static function add_shortcodes(): void {
        foreach ( self::TAGS as $tag => $handler ) {
            remove_shortcode( $tag );
            add_shortcode( $tag, $handler );
        }
    }

    /** @return list<string> */
    public static function tags(): array {
        return array_keys( self::TAGS );
    }

    /** The shortcodes as Core definitions, for the shortcode tester. */
    public static function registry(): ShortcodeRegistry {
        $registry = new ShortcodeRegistry();
        foreach ( self::catalog() as $group ) {
            foreach ( $group['items'] as $item ) {
                $registry->add( new ShortcodeDefinition( sanitize_key( $item['code'] ), (string) $item['desc'], (string) $item['code'], (string) $item['desc'], 'render' ) );
            }
        }
        return $registry;
    }

    /**
     * The Shortcodes tab: groups of shortcodes with their attributes,
     * variations and older forms.
     *
     * @return list<array<string,mixed>>
     */
    public static function catalog(): array {
        $fields = [];
        foreach ( PersonFieldValues::FIELDS as $field => [ $label, $group ] ) {
            $fields[] = [
                'tag'  => PersonShortcode::TAG,
                'code' => 'social' === $field ? '[person field="social" network="linkedin"]' : '[person field="' . $field . '"]',
                'desc' => $label . ' (' . $group . ').',
                'type' => 'value',
            ];
        }
        $sections = [];
        foreach ( SectionRegistry::all() as $section ) {
            $layouts    = $section->layouts();
            $variations = [];
            foreach ( array_slice( $layouts, 1, null, true ) as $layout => $description ) {
                $variations[] = [ 'code' => '[person_section id="' . $section->id() . '" layout="' . $layout . '"]', 'desc' => $description ];
            }
            $own = array_diff_key( $section->attributes(), [ 'layout' => '' ] );
            $sections[] = [
                'tag'        => PersonSectionShortcode::TAG,
                'code'       => '[person_section id="' . $section->id() . '"]',
                'desc'       => $section->label() . ': ' . (string) reset( $layouts ) . '.',
                'type'       => 'html',
                'params'     => implode( ', ', array_merge( [ 'layout="' . implode( '|', array_keys( $layouts ) ) . '"' ], array_map( static fn( string $key, string $value ): string => $key . '="' . $value . '"', array_keys( $own ), $own ), [ 'heading', 'class', 'style="none"' ] ) ),
                'variations' => $variations,
            ];
        }
        return [
            [
                'key'   => 'person',
                'title' => 'Person values: [person field="…"]',
                'blurb' => 'One value of the person. Links print as plain addresses for Elementor link fields; link="true" makes a link. Lists print comma-separated; format="list", "json" or "count" changes that. index="0" and key="…" pick one item. fallback="…" prints when empty.',
                'live'  => 'person',
                'items' => $fields,
            ],
            [
                'key'   => 'sections',
                'title' => 'Person sections: [person_section id="…"]',
                'blurb' => 'Ready-made blocks of the person\'s page. A section with no content prints nothing, heading included. heading="…" adds a heading; style="none" leaves the plugin\'s styles off with the same HTML.',
                'live'  => 'person',
                'items' => $sections,
            ],
            [
                'key'   => 'content',
                'title' => 'Books, organizations and lists',
                'blurb' => 'Values of a book or an organization (the one whose page is open, id="…", or the primary one), and lists of a content type.',
                'live'  => 'none',
                'items' => [
                    [ 'tag' => BookShortcode::TAG, 'code' => '[book field="title"]', 'desc' => 'A book\'s value: title, cover, permalink or any book field (subtitle, amazon_url, isbn…).', 'params' => 'field, id, link, target, pretty, format' ],
                    [ 'tag' => OrganizationShortcode::TAG, 'code' => '[organization field="url"]', 'desc' => 'An organization\'s value: name, url, logo, headquarters_location, founding_date or any organization field.', 'params' => 'field, id, link, target, pretty, format', 'aliases' => [ '[organization id="url"]' ] ],
                    [ 'tag' => OrganizationShortcode::TAG, 'code' => '[organization section="profile"]', 'desc' => 'An organization\'s profile block: logo, name, summary and website.', 'variations' => [ [ 'code' => '[organization section="gallery" columns="3"]', 'desc' => 'Its photo gallery.' ] ], 'aliases' => [ '[organization action="display_profile"]' ] ],
                    [ 'tag' => ContentLoopShortcode::TAG, 'code' => '[person_loop type="book" columns="3" rows="2"]', 'desc' => 'The newest posts of a content type, each drawn with its Elementor loop template from the Templates tab.', 'aliases' => [ '[sfpf_loop cpt="book"]' ] ],
                    [ 'tag' => FaqSetShortcode::TAG, 'code' => '[faq_set slug="main"]', 'desc' => 'A site-wide FAQ set from the FAQ tab (the primary set without a slug). Its FAQ schema is added to the page automatically.', 'aliases' => [ '[sfpf_faq set="main"]' ] ],
                ],
            ],
            [
                'key'   => 'older',
                'title' => 'Older shortcodes',
                'blurb' => 'Still work on the pages that use them; use the shortcode on the right for new pages.',
                'live'  => 'none',
                'items' => [
                    [ 'tag' => 'founder', 'code' => '[founder id="title"]', 'desc' => 'A person value.', 'deprecated' => true, 'use_instead' => '[person field="title"]' ],
                    [ 'tag' => 'founder', 'code' => '[founder action="display_education"]', 'desc' => 'A person section.', 'deprecated' => true, 'use_instead' => '[person_section id="education"]' ],
                    [ 'tag' => 'sfpf_person_faq', 'code' => '[sfpf_person_faq]', 'desc' => 'The person\'s FAQ.', 'deprecated' => true, 'use_instead' => '[person_section id="faq"]' ],
                    [ 'tag' => 'sfpf_faq', 'code' => '[sfpf_faq set="main"]', 'desc' => 'An FAQ set.', 'deprecated' => true, 'use_instead' => '[faq_set slug="main"]' ],
                    [ 'tag' => 'sfpf_faq_schema', 'code' => '[sfpf_faq_schema set="main"]', 'desc' => 'Prints nothing now: FAQ schema is added to the page\'s schema block automatically.', 'deprecated' => true, 'use_instead' => '[faq_set slug="main"]' ],
                    [ 'tag' => 'sfpf_elementor_faq', 'code' => '[sfpf_elementor_faq set="main"]', 'desc' => 'Fills an Elementor Accordion widget with an FAQ set.', 'deprecated' => true, 'use_instead' => '[faq_set slug="main"]' ],
                    [ 'tag' => 'sfpf_loop', 'code' => '[sfpf_loop cpt="book"]', 'desc' => 'A content type list.', 'deprecated' => true, 'use_instead' => '[person_loop type="book"]' ],
                ],
            ],
        ];
    }
}
