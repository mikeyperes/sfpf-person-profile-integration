<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\ContentTypes\Quote;

defined( 'ABSPATH' ) || exit;

/**
 * The Quote fields: the quote, who said it, the source link, publication logos and details.
 * The keys are stored data and never change.
 */
final class QuoteFields {
    /** @return array<string,mixed> */
    public static function group(): array {
        return [
            'key' => 'group_sfpf_quote',
            'title' => 'Quote Fields',
            'fields' => [
                [
                    'key' => 'field_sfpf_quote',
                    'label' => 'Quote',
                    'name' => 'quote',
                    'type' => 'textarea',
                    'instructions' => 'The complete quotation text.',
                    'required' => 1,
                    'rows' => 6,
                    'new_lines' => '',
                ],
                [
                    'key' => 'field_sfpf_quote_assigned_name',
                    'label' => 'Assigned Name',
                    'name' => 'assigned_name',
                    'type' => 'text',
                    'instructions' => 'The person or entity to whom the quote is attributed.',
                    'required' => 1,
                ],
                [
                    'key' => 'field_sfpf_quote_url',
                    'label' => 'URL',
                    'name' => 'url',
                    'type' => 'url',
                    'instructions' => 'The original publication or source URL.',
                ],
                [
                    'key' => 'field_sfpf_quote_logos',
                    'label' => 'Logos',
                    'name' => 'logos',
                    'type' => 'gallery',
                    'instructions' => 'One or more publication logos. SVG files require an active sanitizer such as SVG Support.',
                    'return_format' => 'id',
                    'preview_size' => 'medium',
                    'insert' => 'append',
                    'library' => 'all',
                    'min' => 0,
                    'max' => 0,
                    'mime_types' => 'svg,png,jpg,jpeg,webp',
                ],
                [
                    'key' => 'field_sfpf_quote_publication_name',
                    'label' => 'Publication Name',
                    'name' => 'publication_name',
                    'type' => 'text',
                ],
                [
                    'key' => 'field_sfpf_quote_publication_info',
                    'label' => 'Publication Info',
                    'name' => 'publication_info',
                    'type' => 'textarea',
                    'rows' => 4,
                    'new_lines' => '',
                ],
            ],
            'location' => [
                [
                    [
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => '@post_type',
                    ],
                ],
            ],
            'menu_order' => 0,
            'position' => 'normal',
            'style' => 'default',
            'label_placement' => 'top',
            'instruction_placement' => 'label',
            'active' => true,
            'show_in_rest' => 0,
        ];
    }
}
