<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Schema\Structures;

use Hexa\PluginCore\SchemaTools\SchemaContext;
use Hexa\PluginCore\SchemaTools\SchemaIds;
use SFPF\PersonProfile\Faq\FaqSets;
use SFPF\PersonProfile\Pages\PageTypes;
use SFPF\PersonProfile\Schema\ShortcodeUsage;
use SFPF\PersonProfile\Settings\Settings;

defined( 'ABSPATH' ) || exit;

/**
 * FAQ schema for the questions a page shows: the person's FAQ on the FAQs
 * page and on any page with a person FAQ shortcode, and every FAQ set a page
 * shows. One FAQPage per page, with all of its questions.
 */
final class FaqStructure implements SchemaStructure {
    private const SET_TAGS = [ 'faq_set', 'sfpf_faq', 'sfpf_faq_schema', 'sfpf_elementor_faq' ];

    public function definition(): array {
        return [
            'id'      => 'faq',
            'label'   => 'FAQs',
            'where'   => 'The FAQs page and every page that shows the person\'s FAQ or an FAQ set.',
            'types'   => 'FAQPage, Question, Answer',
            'default' => 'on',
            'applies' => static fn( SchemaContext $context ): bool => [] !== self::items( $context ),
            'build'   => [ $this, 'build' ],
            'sample'  => static function (): ?SchemaContext {
                $page = Settings::assigned_page( 'faqs' ) ?: PageTypes::base_tools_page( 'faqs' );
                return $page > 0 ? SchemaContext::for_post( $page ) : null;
            },
        ];
    }

    /** @return list<array<string,mixed>> */
    public function build( SchemaContext $context, string $mode ): array {
        $items = self::items( $context );
        $url   = $context->url;
        $node  = FaqSets::manager()->buildSchemaNode(
            $items,
            [ 'id' => SchemaIds::for_url( $url, 'faq' ), 'url' => $url, 'is_part_of' => SchemaIds::webpage( $url ) ]
        );
        return empty( $node['mainEntity'] ) ? [] : [ $node ];
    }

    /** @return list<array{question:string,answer:string}> Every question the page shows. */
    public static function items( SchemaContext $context ): array {
        $post_id = $context->post_id();
        if ( $post_id <= 0 ) {
            return [];
        }
        $items = [];
        $uses  = ShortcodeUsage::all( $post_id );
        $faq_page = in_array( $post_id, [ Settings::assigned_page( 'faqs' ), PageTypes::base_tools_page( 'faqs' ) ], true );
        if ( $faq_page || [] !== array_filter( $uses, [ self::class, 'shows_person_faq' ] ) ) {
            $items = FaqSets::person_items();
        }
        foreach ( $uses as $use ) {
            if ( in_array( $use['tag'], self::SET_TAGS, true ) ) {
                $index = isset( $use['atts']['index'] ) && is_numeric( $use['atts']['index'] ) ? (int) $use['atts']['index'] : null;
                $items = array_merge( $items, FaqSets::set_items( (string) ( $use['atts']['slug'] ?? $use['atts']['set'] ?? '' ), $index ) );
            }
        }
        $unique = [];
        foreach ( $items as $item ) {
            $unique[ strtolower( $item['question'] ) ] = $item;
        }
        return array_values( $unique );
    }

    /** @param array{tag:string,atts:array<string,string>} $use */
    public static function shows_person_faq( array $use ): bool {
        $atts = $use['atts'];
        return match ( $use['tag'] ) {
            'person_section'          => 'faq' === ( $atts['id'] ?? '' ),
            'person_faq', 'sfpf_person_faq' => true,
            'founder'                 => 'display_faq' === ( $atts['action'] ?? '' ) || ( 'faq' === ( $atts['id'] ?? '' ) && ! in_array( $atts['format'] ?? '', [ 'json', 'count' ], true ) ),
            default                   => false,
        };
    }
}
