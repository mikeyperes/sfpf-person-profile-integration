<?php

declare( strict_types=1 );

namespace sfpf_person_website;

defined( 'ABSPATH' ) || exit;

/** @return array<string,mixed> */
function award_acf_field_group(): array {
    return [
        'key' => 'group_sfpf_award',
        'title' => 'Award Fields',
        'fields' => [
            [
                'key' => 'field_sfpf_award_year',
                'label' => 'Year',
                'name' => 'year',
                'type' => 'number',
                'instructions' => 'The year the award was received. The award name is the post title.',
                'min' => 1900,
                'max' => 2100,
            ],
            [
                'key' => 'field_sfpf_award_given_by',
                'label' => 'Given By',
                'name' => 'given_by',
                'type' => 'text',
                'instructions' => 'The organization or event that gave the award.',
            ],
            [
                'key' => 'field_sfpf_award_given_by_organization',
                'label' => 'Given By (Organization Record)',
                'name' => 'given_by_organization',
                'type' => 'post_object',
                'instructions' => 'Optional. Link the giver to an Organization record when one exists.',
                'post_type' => [ 'organization' ],
                'return_format' => 'id',
                'allow_null' => 1,
                'multiple' => 0,
            ],
            [
                'key' => 'field_sfpf_award_url',
                'label' => 'URL',
                'name' => 'url',
                'type' => 'url',
                'instructions' => 'The article or announcement that confirms the award.',
            ],
            [
                'key' => 'field_sfpf_award_featured',
                'label' => 'Featured',
                'name' => 'featured',
                'type' => 'true_false',
                'instructions' => 'Show this award in featured lists, such as the homepage.',
                'ui' => 1,
                'default_value' => 0,
            ],
        ],
        'location' => [ [ [ 'param' => 'post_type', 'operator' => '==', 'value' => '@post_type' ] ] ],
        'menu_order' => 0,
        'position' => 'normal',
        'style' => 'default',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
        'active' => true,
        'show_in_rest' => 0,
    ];
}
