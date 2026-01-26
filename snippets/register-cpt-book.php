<?php
namespace sfpf_person_website;

/**
 * Book Custom Post Type Registration
 * 
 * Registers the 'book' post type for managing book entries.
 * Post type slug is 'book' (singular).
 * 
 * @package sfpf_person_website
 * @since 1.0.0
 */

defined('ABSPATH') || exit;

// Register immediately since this file is loaded during init
add_action('init', __NAMESPACE__ . '\\register_book_post_type', 0);

/**
 * Register the Book custom post type
 */
function register_book_post_type() {
    
    // Check if post type already exists
    if (post_type_exists('book')) {
        return;
    }
    
    register_post_type('book', [
        'labels' => [
            'name'                  => 'Books',
            'singular_name'         => 'Book',
            'menu_name'             => 'Books',
            'all_items'             => 'All Books',
            'edit_item'             => 'Edit Book',
            'view_item'             => 'View Book',
            'view_items'            => 'View Books',
            'add_new_item'          => 'Add New Book',
            'add_new'               => 'Add New Book',
            'new_item'              => 'New Book',
            'parent_item_colon'     => 'Parent Book:',
            'search_items'          => 'Search Books',
            'not_found'             => 'No books found',
            'not_found_in_trash'    => 'No books found in Trash',
            'archives'              => 'Book Archives',
            'attributes'            => 'Book Attributes',
            'insert_into_item'      => 'Insert into book',
            'uploaded_to_this_item' => 'Uploaded to this book',
            'filter_items_list'     => 'Filter books list',
            'filter_by_date'        => 'Filter books by date',
            'items_list_navigation' => 'Books list navigation',
            'items_list'            => 'Books list',
            'item_published'        => 'Book published.',
            'item_published_privately' => 'Book published privately.',
            'item_reverted_to_draft'   => 'Book reverted to draft.',
            'item_scheduled'        => 'Book scheduled.',
            'item_updated'          => 'Book updated.',
            'item_link'             => 'Book Link',
            'item_link_description' => 'A link to a book.',
        ],
        'public'             => true,
        'show_in_rest'       => true,
        'menu_icon'          => 'dashicons-book-alt',
        'supports'           => ['title', 'author', 'editor', 'thumbnail', 'custom-fields'],
        'has_archive'        => 'books',
        'rewrite'            => ['slug' => 'book', 'with_front' => false],
        'delete_with_user'   => false,
    ]);
}
