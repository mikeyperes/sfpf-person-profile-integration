<?php
namespace sfpf_person_website;

/**
 * Testimonial Custom Post Type Registration
 * 
 * Registers the 'testimonial' post type for managing testimonials.
 * 
 * @package sfpf_person_website
 * @since 1.1.0
 */

defined('ABSPATH') || exit;

// Register immediately since this file is loaded during init
add_action('init', __NAMESPACE__ . '\\register_testimonial_post_type', 0);

/**
 * Register the Testimonial custom post type
 */
function register_testimonial_post_type() {
    
    // Check if post type already exists (might be registered in HWS Base Tools)
    if (post_type_exists('testimonial')) {
        return;
    }
    
    register_post_type('testimonial', [
        'labels' => [
            'name'                  => 'Testimonials',
            'singular_name'         => 'Testimonial',
            'menu_name'             => 'Testimonials',
            'all_items'             => 'All Testimonials',
            'edit_item'             => 'Edit Testimonial',
            'view_item'             => 'View Testimonial',
            'view_items'            => 'View Testimonials',
            'add_new_item'          => 'Add New Testimonial',
            'add_new'               => 'Add New Testimonial',
            'new_item'              => 'New Testimonial',
            'parent_item_colon'     => 'Parent Testimonial:',
            'search_items'          => 'Search Testimonials',
            'not_found'             => 'No testimonials found',
            'not_found_in_trash'    => 'No testimonials found in Trash',
            'archives'              => 'Testimonial Archives',
            'attributes'            => 'Testimonial Attributes',
            'insert_into_item'      => 'Insert into testimonial',
            'uploaded_to_this_item' => 'Uploaded to this testimonial',
            'filter_items_list'     => 'Filter testimonials list',
            'items_list_navigation' => 'Testimonials list navigation',
            'items_list'            => 'Testimonials list',
            'item_published'        => 'Testimonial published.',
            'item_published_privately' => 'Testimonial published privately.',
            'item_reverted_to_draft'   => 'Testimonial reverted to draft.',
            'item_scheduled'        => 'Testimonial scheduled.',
            'item_updated'          => 'Testimonial updated.',
            'item_link'             => 'Testimonial Link',
            'item_link_description' => 'A link to a testimonial.',
        ],
        'public'             => true,
        'show_in_rest'       => true,
        'menu_icon'          => 'dashicons-format-quote',
        'supports'           => ['title', 'editor', 'thumbnail', 'custom-fields'],
        'has_archive'        => 'testimonials',
        'rewrite'            => ['slug' => 'testimonial', 'with_front' => false],
        'delete_with_user'   => false,
    ]);
}
