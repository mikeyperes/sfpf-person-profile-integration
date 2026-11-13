<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\ContentTypes\Book;

defined( 'ABSPATH' ) || exit;

/**
 * The Book content type. Books the person wrote, each with Book schema.
 * Off until a site turns it on; its fields are on with it.
 */
final class BookContentType {
    public const ID        = 'book';
    public const POST_TYPE = 'book';

    /** @return array<string,mixed> */
    public static function definition(): array {
        return [
            'id'              => self::ID,
            'owner'           => 'SFPF Person Profile Integration',
            'description'     => 'Books the person wrote, each with Book schema.',
            'enabled_default' => false,
            'legacy_enabled_option' => 'sfpf_enable_book_cpt',
            'post_type'       => [
                'key'          => self::POST_TYPE,
                'singular'     => 'Book',
                'plural'       => 'Books',
                'rewrite_slug' => 'book',
                'args'         => [
                    'public' => true, 'publicly_queryable' => true, 'show_ui' => true,
                    'show_in_menu' => true, 'show_in_nav_menus' => true, 'show_in_admin_bar' => true,
                    'show_in_rest' => true, 'menu_position' => 20, 'menu_icon' => 'dashicons-book-alt',
                    'capability_type' => 'post', 'hierarchical' => false,
                    'supports' => [ 'title', 'author', 'editor', 'thumbnail', 'custom-fields', 'excerpt' ],
                    'has_archive' => 'books', 'rewrite' => [ 'with_front' => false ],
                    'query_var' => true, 'delete_with_user' => false,
                ],
            ],
            'field_groups'    => [
                [
                    'id'              => 'book-details',
                    'label'           => 'Book Details',
                    'description'     => 'Publishing details, purchase and reference links, quotes, media and the Knowledge Graph ID.',
                    'group_key'       => 'group_sfpf_book',
                    'enabled_default' => true,
                    'legacy_option'   => 'sfpf_enable_book_acf',
                    'definition'      => [ BookFields::class, 'group' ],
                    'fields'          => [ 'Subtitle', 'Description', 'Author Bio', 'Alternate Names', 'Quotes', 'Featured Content', 'Retail URLs', 'Knowledge Graph ID', 'SameAs URLs', 'ISBN', 'Page Count', 'Publication Date', 'Edition', 'Format', 'Language', 'Genre' ],
                    'dependencies'    => [],
                ],
            ],
        ];
    }
}
