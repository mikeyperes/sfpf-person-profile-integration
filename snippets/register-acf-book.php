<?php
namespace sfpf_person_website;

/**
 * Book ACF Fields Registration
 * 
 * Registers Advanced Custom Fields for the Book post type.
 * 
 * @package sfpf_person_website
 * @since 1.0.0
 */

defined('ABSPATH') || exit;

/**
 * Register the Book ACF field group
 */
function register_book_acf_fields() {
    
    if (!function_exists('acf_add_local_field_group')) {
        return;
    }
    
    acf_add_local_field_group([
        'key' => 'group_sfpf_book',
        'title' => 'Book Details',
        'fields' => [
            
            // === Schema Tab ===
            [
                'key' => 'field_sfpf_book_tab_schema',
                'label' => 'Schema',
                'name' => '',
                'type' => 'tab',
                'placement' => 'top',
            ],
            [
                'key' => 'field_sfpf_book_schema',
                'label' => 'Schema Markup',
                'name' => 'schema_markup',
                'type' => 'textarea',
                'instructions' => 'Generated JSON-LD schema markup for this book. This is auto-generated and should not be manually edited.',
                'required' => 0,
                'readonly' => 1,
                'rows' => 10,
                'wrapper' => ['class' => 'sfpf-schema-field'],
            ],
            [
                'key' => 'field_sfpf_book_schema_preview',
                'label' => 'Schema Preview',
                'name' => 'schema_preview',
                'type' => 'message',
                'message' => '<div id="sfpf-schema-preview-book"></div>',
                'new_lines' => '',
                'esc_html' => 0,
            ],
            
            // === Basic Info Tab ===
            [
                'key' => 'field_sfpf_book_tab_basic',
                'label' => 'Basic Info',
                'name' => '',
                'type' => 'tab',
                'placement' => 'top',
            ],
            [
                'key' => 'field_sfpf_book_subtitle',
                'label' => 'Sub-Title',
                'name' => 'subtitle',
                'type' => 'text',
                'instructions' => 'Optional subtitle for the book.',
                'required' => 0,
            ],
            [
                'key' => 'field_sfpf_book_description',
                'label' => 'Description',
                'name' => 'description',
                'type' => 'wysiwyg',
                'instructions' => 'Full description or summary of the book.',
                'required' => 0,
                'tabs' => 'all',
                'toolbar' => 'full',
                'media_upload' => 1,
                'delay' => 1,
            ],
            [
                'key' => 'field_sfpf_book_author_bio',
                'label' => 'Author Bio',
                'name' => 'author_bio',
                'type' => 'wysiwyg',
                'instructions' => 'Biography of the book author.',
                'required' => 0,
                'tabs' => 'all',
                'toolbar' => 'full',
                'media_upload' => 1,
                'delay' => 1,
            ],
            [
                'key' => 'field_sfpf_book_featured',
                'label' => 'Featured',
                'name' => 'featured',
                'type' => 'true_false',
                'instructions' => 'Mark this book as featured.',
                'required' => 0,
                'default_value' => 0,
                'ui' => 1,
            ],
            
            // === Media Tab ===
            [
                'key' => 'field_sfpf_book_tab_media',
                'label' => 'Media',
                'name' => '',
                'type' => 'tab',
                'placement' => 'top',
            ],
            [
                'key' => 'field_sfpf_book_cover',
                'label' => 'Cover Image',
                'name' => 'cover',
                'type' => 'image',
                'instructions' => 'Book cover image (will also be used as featured image if not set).',
                'required' => 0,
                'return_format' => 'array',
                'library' => 'all',
                'preview_size' => 'medium',
            ],
            [
                'key' => 'field_sfpf_book_featured_content',
                'label' => 'Featured Content',
                'name' => 'featured_content',
                'type' => 'wysiwyg',
                'instructions' => 'Optional featured content or promotional material.',
                'required' => 0,
                'tabs' => 'all',
                'toolbar' => 'full',
                'media_upload' => 1,
                'delay' => 1,
            ],
            
            // === URLs Tab ===
            [
                'key' => 'field_sfpf_book_tab_urls',
                'label' => 'URLs',
                'name' => '',
                'type' => 'tab',
                'placement' => 'top',
            ],
            [
                'key' => 'field_sfpf_book_amazon_url',
                'label' => 'Amazon URL',
                'name' => 'amazon_url',
                'type' => 'url',
                'instructions' => 'Link to the book on Amazon.',
                'required' => 0,
            ],
            [
                'key' => 'field_sfpf_book_audible_url',
                'label' => 'Audible URL',
                'name' => 'audible_url',
                'type' => 'url',
                'instructions' => 'Link to the audiobook on Audible.',
                'required' => 0,
            ],
            [
                'key' => 'field_sfpf_book_google_books_url',
                'label' => 'Google Books URL',
                'name' => 'google_books_url',
                'type' => 'url',
                'instructions' => 'Link to the book on Google Books.',
                'required' => 0,
            ],
            [
                'key' => 'field_sfpf_book_goodreads_url',
                'label' => 'GoodReads URL',
                'name' => 'goodreads_url',
                'type' => 'url',
                'instructions' => 'Link to the book on GoodReads.',
                'required' => 0,
            ],
            [
                'key' => 'field_sfpf_book_soundcloud_url',
                'label' => 'SoundCloud URL',
                'name' => 'soundcloud_url',
                'type' => 'url',
                'instructions' => 'Link to audio content on SoundCloud.',
                'required' => 0,
            ],
            [
                'key' => 'field_sfpf_book_audio_url',
                'label' => 'Audio URL',
                'name' => 'audio_url',
                'type' => 'url',
                'instructions' => 'Direct link to audio file or player.',
                'required' => 0,
            ],
            
            // === Social Tab ===
            [
                'key' => 'field_sfpf_book_tab_social',
                'label' => 'Social',
                'name' => '',
                'type' => 'tab',
                'placement' => 'top',
            ],
            [
                'key' => 'field_sfpf_book_instagram_url',
                'label' => 'Instagram URL',
                'name' => 'instagram_url',
                'type' => 'url',
                'instructions' => 'Instagram page for the book.',
                'required' => 0,
            ],
            [
                'key' => 'field_sfpf_book_youtube_url',
                'label' => 'YouTube URL',
                'name' => 'youtube_url',
                'type' => 'url',
                'instructions' => 'YouTube channel or video for the book.',
                'required' => 0,
            ],
            
            // === Publishing Tab ===
            [
                'key' => 'field_sfpf_book_tab_publishing',
                'label' => 'Publishing',
                'name' => '',
                'type' => 'tab',
                'placement' => 'top',
            ],
            [
                'key' => 'field_sfpf_book_publishing_company',
                'label' => 'Publishing Company',
                'name' => 'publishing_company',
                'type' => 'wysiwyg',
                'instructions' => 'Information about the publisher.',
                'required' => 0,
                'tabs' => 'all',
                'toolbar' => 'basic',
                'media_upload' => 0,
                'delay' => 1,
            ],
            [
                'key' => 'field_sfpf_book_press',
                'label' => 'Press',
                'name' => 'press',
                'type' => 'wysiwyg',
                'instructions' => 'Press releases, reviews, or media mentions.',
                'required' => 0,
                'tabs' => 'all',
                'toolbar' => 'full',
                'media_upload' => 1,
                'delay' => 1,
            ],
            [
                'key' => 'field_sfpf_book_additional_resources',
                'label' => 'Additional Resources',
                'name' => 'additional_resources',
                'type' => 'wysiwyg',
                'instructions' => 'Any additional resources, links, or information.',
                'required' => 0,
                'tabs' => 'all',
                'toolbar' => 'full',
                'media_upload' => 1,
                'delay' => 1,
            ],
        ],
        'location' => [
            [
                [
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => 'book',
                ],
            ],
        ],
        'menu_order' => 0,
        'position' => 'normal',
        'style' => 'default',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
        'active' => true,
        'show_in_rest' => 1,
    ]);
}
