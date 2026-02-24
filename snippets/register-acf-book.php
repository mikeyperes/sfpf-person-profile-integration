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
            
            // ═══════════════════════════════════════════════════════════
            // SCHEMA
            // ═══════════════════════════════════════════════════════════
            [
                'key' => 'field_sfpf_book_header_schema',
                'label' => '📋 Schema',
                'name' => '',
                'type' => 'accordion',
                'open' => 1,
                'multi_expand' => 1,
                'endpoint' => 0,
            ],
            [
                'key' => 'field_sfpf_book_schema',
                'label' => 'Schema Markup',
                'name' => 'schema_markup',
                'type' => 'textarea',
                'instructions' => 'Generated JSON-LD schema markup for this book. Auto-generated on save.',
                'required' => 0,
                'readonly' => 1,
                'rows' => 10,
            ],
            
            // ═══════════════════════════════════════════════════════════
            // BASIC INFO
            // ═══════════════════════════════════════════════════════════
            [
                'key' => 'field_sfpf_book_header_basic',
                'label' => '📝 Basic Info',
                'name' => '',
                'type' => 'accordion',
                'open' => 1,
                'multi_expand' => 1,
                'endpoint' => 0,
            ],
            [
                'key' => 'field_sfpf_book_featured',
                'label' => 'Featured',
                'name' => 'featured',
                'type' => 'true_false',
                'instructions' => 'Show this book in featured sections.<br><code>[book field="featured"]</code>',
                'required' => 0,
                'default_value' => 0,
                'ui' => 1,
            ],
            [
                'key' => 'field_sfpf_book_subtitle',
                'label' => 'Sub-Title',
                'name' => 'subtitle',
                'type' => 'text',
                'instructions' => 'Optional subtitle for the book.<br><code>[book field="subtitle"]</code>',
                'required' => 0,
            ],
            [
                'key' => 'field_sfpf_book_description',
                'label' => 'Description',
                'name' => 'description',
                'type' => 'wysiwyg',
                'instructions' => 'Full description or summary of the book.<br><code>[book field="description"]</code>',
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
                'instructions' => 'Biography of the book author.<br><code>[book field="author_bio"]</code>',
                'required' => 0,
                'tabs' => 'all',
                'toolbar' => 'full',
                'media_upload' => 1,
                'delay' => 1,
            ],
            [
                'key' => 'field_sfpf_book_alternate_names',
                'label' => 'Alternate Names',
                'name' => 'alternate_names',
                'type' => 'textarea',
                'instructions' => 'Other names or editions the book is known by (one per line, for schema.org alternateName).<br><code>[book field="alternate_names"]</code>',
                'required' => 0,
                'rows' => 3,
            ],
            
            // ═══════════════════════════════════════════════════════════
            // MEDIA
            // ═══════════════════════════════════════════════════════════
            [
                'key' => 'field_sfpf_book_header_media',
                'label' => '🖼️ Media',
                'name' => '',
                'type' => 'accordion',
                'open' => 1,
                'multi_expand' => 1,
                'endpoint' => 0,
            ],
            [
                'key' => 'field_sfpf_book_cover',
                'label' => 'Cover Image',
                'name' => 'cover',
                'type' => 'image',
                'instructions' => 'Book cover image (will also be used as featured image if not set).<br><code>[book field="cover"]</code>',
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
                'instructions' => 'Optional featured content or promotional material.<br><code>[book field="featured_content"]</code>',
                'required' => 0,
                'tabs' => 'all',
                'toolbar' => 'full',
                'media_upload' => 1,
                'delay' => 1,
            ],
            
            // ═══════════════════════════════════════════════════════════
            // URLs
            // ═══════════════════════════════════════════════════════════
            [
                'key' => 'field_sfpf_book_header_urls',
                'label' => '🔗 URLs',
                'name' => '',
                'type' => 'accordion',
                'open' => 1,
                'multi_expand' => 1,
                'endpoint' => 0,
            ],
            [
                'key' => 'field_sfpf_book_amazon_url',
                'label' => 'Amazon URL',
                'name' => 'amazon_url',
                'type' => 'url',
                'instructions' => 'Link to the book on Amazon.<br><code>[book field="amazon_url"]</code> or <code>[book field="amazon_url" link="true" target="_blank" pretty="true"]</code>',
                'required' => 0,
            ],
            [
                'key' => 'field_sfpf_book_audible_url',
                'label' => 'Audible URL',
                'name' => 'audible_url',
                'type' => 'url',
                'instructions' => 'Link to the audiobook on Audible.<br><code>[book field="audible_url"]</code>',
                'required' => 0,
            ],
            [
                'key' => 'field_sfpf_book_google_books_url',
                'label' => 'Google Books URL',
                'name' => 'google_books_url',
                'type' => 'url',
                'instructions' => 'Link to the book on Google Books.<br><code>[book field="google_books_url"]</code>',
                'required' => 0,
            ],
            [
                'key' => 'field_sfpf_book_goodreads_url',
                'label' => 'GoodReads URL',
                'name' => 'goodreads_url',
                'type' => 'url',
                'instructions' => 'Link to the book on GoodReads.<br><code>[book field="goodreads_url"]</code>',
                'required' => 0,
            ],
            [
                'key' => 'field_sfpf_book_knowledge_graph_url',
                'label' => 'Google Knowledge Graph URL',
                'name' => 'knowledge_graph_url',
                'type' => 'url',
                'instructions' => 'Google Knowledge Graph panel URL for this book.<br><code>[book field="knowledge_graph_url"]</code>',
                'required' => 0,
            ],
            [
                'key' => 'field_sfpf_book_knowledge_graph_id',
                'label' => 'Google Knowledge Graph ID',
                'name' => 'knowledge_graph_id',
                'type' => 'text',
                'instructions' => 'Enter the KGMID (e.g., <code>/g/11gyz2y3lp</code>). If you paste the full Google URL, the ID will be extracted automatically.<br>
<code>[book field="knowledge_graph_id"]</code> — Raw KGMID<br>
<span class="sfpf-kgid-book-link">Enter a KGMID above to see the full Knowledge Panel URL.</span>',
                'required' => 0,
                'placeholder' => '/g/11gyz2y3lp',
            ],
            [
                'key' => 'field_sfpf_book_sameas_urls',
                'label' => 'SameAs URLs',
                'name' => 'sameas_urls',
                'type' => 'textarea',
                'instructions' => 'Additional URLs that represent this book (one per line, for schema.org sameAs).<br><code>[book field="sameas_urls"]</code>',
                'required' => 0,
                'rows' => 5,
            ],
            
            // ═══════════════════════════════════════════════════════════
            // SOCIAL
            // ═══════════════════════════════════════════════════════════
            [
                'key' => 'field_sfpf_book_header_social',
                'label' => '📱 Social',
                'name' => '',
                'type' => 'accordion',
                'open' => 1,
                'multi_expand' => 1,
                'endpoint' => 0,
            ],
            [
                'key' => 'field_sfpf_book_instagram_url',
                'label' => 'Instagram URL',
                'name' => 'instagram_url',
                'type' => 'url',
                'instructions' => 'Instagram page for the book.<br><code>[book field="instagram_url"]</code>',
                'required' => 0,
            ],
            [
                'key' => 'field_sfpf_book_youtube_url',
                'label' => 'YouTube URL',
                'name' => 'youtube_url',
                'type' => 'url',
                'instructions' => 'YouTube channel or video for the book.<br><code>[book field="youtube_url"]</code>',
                'required' => 0,
            ],
            [
                'key' => 'field_sfpf_book_soundcloud_url',
                'label' => 'SoundCloud URL',
                'name' => 'soundcloud_url',
                'type' => 'url',
                'instructions' => 'Link to audio content on SoundCloud.<br><code>[book field="soundcloud_url"]</code>',
                'required' => 0,
            ],
            [
                'key' => 'field_sfpf_book_audio_url',
                'label' => 'Audio URL',
                'name' => 'audio_url',
                'type' => 'url',
                'instructions' => 'Direct link to audio file or player.<br><code>[book field="audio_url"]</code>',
                'required' => 0,
            ],
            
            // ═══════════════════════════════════════════════════════════
            // PUBLISHING
            // ═══════════════════════════════════════════════════════════
            [
                'key' => 'field_sfpf_book_header_publishing',
                'label' => '📚 Publishing',
                'name' => '',
                'type' => 'accordion',
                'open' => 1,
                'multi_expand' => 1,
                'endpoint' => 0,
            ],
            [
                'key' => 'field_sfpf_book_publishing_company',
                'label' => 'Publishing Company',
                'name' => 'publishing_company',
                'type' => 'text',
                'instructions' => 'Name of the publisher.<br><code>[book field="publishing_company"]</code>',
                'required' => 0,
            ],
            [
                'key' => 'field_sfpf_book_isbn',
                'label' => 'ISBN',
                'name' => 'isbn',
                'type' => 'text',
                'instructions' => 'International Standard Book Number (ISBN-10 or ISBN-13).<br><code>[book field="isbn"]</code>',
                'required' => 0,
                'placeholder' => '978-0-123456-78-9',
                'wrapper' => ['width' => '33'],
            ],
            [
                'key' => 'field_sfpf_book_number_of_pages',
                'label' => 'Number of Pages',
                'name' => 'number_of_pages',
                'type' => 'number',
                'instructions' => 'Total page count.<br><code>[book field="number_of_pages"]</code>',
                'required' => 0,
                'wrapper' => ['width' => '33'],
            ],
            [
                'key' => 'field_sfpf_book_date_published',
                'label' => 'Date Published',
                'name' => 'date_published',
                'type' => 'text',
                'instructions' => 'Publication date. Example: 2024-03-15<br><code>[book field="date_published"]</code>',
                'required' => 0,
                'placeholder' => '2024-03-15',
                'wrapper' => ['width' => '34'],
            ],
            [
                'key' => 'field_sfpf_book_edition',
                'label' => 'Book Edition',
                'name' => 'book_edition',
                'type' => 'text',
                'instructions' => 'Edition of the book (e.g. "First Edition", "2nd Revised Edition").<br><code>[book field="book_edition"]</code>',
                'required' => 0,
                'wrapper' => ['width' => '33'],
            ],
            [
                'key' => 'field_sfpf_book_format',
                'label' => 'Book Format',
                'name' => 'book_format',
                'type' => 'select',
                'instructions' => 'Schema.org BookFormatType.<br><code>[book field="book_format"]</code>',
                'required' => 0,
                'choices' => [
                    ''              => '— Select —',
                    'Hardcover'     => 'Hardcover',
                    'Paperback'     => 'Paperback',
                    'EBook'         => 'EBook',
                    'AudiobookFormat' => 'Audiobook',
                ],
                'default_value' => '',
                'return_format' => 'value',
                'wrapper' => ['width' => '33'],
            ],
            [
                'key' => 'field_sfpf_book_in_language',
                'label' => 'Language',
                'name' => 'in_language',
                'type' => 'text',
                'instructions' => 'Language code (e.g. "en", "es", "fr").<br><code>[book field="in_language"]</code>',
                'required' => 0,
                'placeholder' => 'en',
                'wrapper' => ['width' => '34'],
            ],
            [
                'key' => 'field_sfpf_book_genre',
                'label' => 'Genre',
                'name' => 'genre',
                'type' => 'text',
                'instructions' => 'Book genre or category.<br><code>[book field="genre"]</code>',
                'required' => 0,
            ],
            [
                'key' => 'field_sfpf_book_press',
                'label' => 'Press',
                'name' => 'press',
                'type' => 'wysiwyg',
                'instructions' => 'Press releases, reviews, or media mentions.<br><code>[book field="press"]</code>',
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
                'instructions' => 'Any additional resources, links, or information.<br><code>[book field="additional_resources"]</code>',
                'required' => 0,
                'tabs' => 'all',
                'toolbar' => 'full',
                'media_upload' => 1,
                'delay' => 1,
            ],
            
            // Close accordions
            [
                'key' => 'field_sfpf_book_accordion_end',
                'label' => '',
                'name' => '',
                'type' => 'accordion',
                'endpoint' => 1,
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

/**
 * Build Book schema on save — delegates to unified schema-builder.php
 */
add_action('acf/save_post', __NAMESPACE__ . '\\build_book_schema_on_save', 20);
function build_book_schema_on_save($post_id) {
    if (get_post_type($post_id) !== 'book') return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    
    $schema = build_book_schema($post_id);
    if (!empty($schema)) {
        update_field('schema_markup', json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), $post_id);
    }
}
