<?php
namespace sfpf_person_website;

/**
 * Organization Custom Post Type Registration
 * 
 * Registers the 'organization' post type for managing organization entries.
 * Post type slug is 'organization' (singular).
 * 
 * @package sfpf_person_website
 * @since 1.0.0
 */

defined('ABSPATH') || exit;

// Register immediately since this file is loaded during init
add_action('init', __NAMESPACE__ . '\\register_organization_post_type', 0);

/**
 * Register the Organization custom post type
 */
function register_organization_post_type() {
    
    // Check if post type already exists
    if (post_type_exists('organization')) {
        return;
    }
    
    register_post_type('organization', [
        'labels' => [
            'name'                  => 'Organizations',
            'singular_name'         => 'Organization',
            'menu_name'             => 'Organizations',
            'all_items'             => 'All Organizations',
            'edit_item'             => 'Edit Organization',
            'view_item'             => 'View Organization',
            'view_items'            => 'View Organizations',
            'add_new_item'          => 'Add New Organization',
            'add_new'               => 'Add New Organization',
            'new_item'              => 'New Organization',
            'parent_item_colon'     => 'Parent Organization:',
            'search_items'          => 'Search Organizations',
            'not_found'             => 'No organizations found',
            'not_found_in_trash'    => 'No organizations found in Trash',
            'archives'              => 'Organization Archives',
            'attributes'            => 'Organization Attributes',
            'insert_into_item'      => 'Insert into organization',
            'uploaded_to_this_item' => 'Uploaded to this organization',
            'filter_items_list'     => 'Filter organizations list',
            'items_list_navigation' => 'Organizations list navigation',
            'items_list'            => 'Organizations list',
            'item_published'        => 'Organization published.',
            'item_published_privately' => 'Organization published privately.',
            'item_reverted_to_draft'   => 'Organization reverted to draft.',
            'item_scheduled'        => 'Organization scheduled.',
            'item_updated'          => 'Organization updated.',
            'item_link'             => 'Organization Link',
            'item_link_description' => 'A link to an organization.',
        ],
        'public'             => true,
        'show_in_rest'       => true,
        'menu_icon'          => 'dashicons-building',
        'supports'           => ['title', 'author', 'editor', 'thumbnail', 'custom-fields'],
        'has_archive'        => 'organizations',
        'rewrite'            => ['slug' => 'organization', 'with_front' => false],
        'delete_with_user'   => false,
    ]);
}
