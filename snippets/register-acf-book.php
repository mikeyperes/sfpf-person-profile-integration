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
 * Build Book schema on save
 */
add_action('acf/save_post', __NAMESPACE__ . '\\build_book_schema_on_save', 20);
function build_book_schema_on_save($post_id) {
    // Skip if not book
    if (get_post_type($post_id) !== 'book') {
        return;
    }
    
    // Skip autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    // Build schema inline
    $site_url = rtrim(get_site_url(), '/');
    $post = get_post($post_id);
    $slug = $post->post_name;
    $founder = get_founder_full_info();
    
    $schema = [
        '@type' => 'Book',
        '@id' => $site_url . '#book-' . $slug,
        'name' => get_the_title($post_id),
    ];
    
    // URL (Google Books preferred)
    $google_books = get_field('google_books_url', $post_id);
    if ($google_books) {
        $schema['url'] = $google_books;
    }
    
    // Author (from site owner)
    if ($founder) {
        $schema['author'] = [
            '@type' => 'Person',
            '@id' => $site_url . '#person-' . sanitize_title($founder['display_name']),
            'name' => $founder['display_name'],
            'url' => $site_url,
        ];
    }
    
    // Cover Image
    $cover = get_field('cover', $post_id);
    if ($cover && isset($cover['url'])) {
        $schema['image'] = [
            '@type' => 'ImageObject',
            'url' => $cover['url'],
        ];
    }
    
    // Main Entity Of Page
    $schema['mainEntityOfPage'] = get_permalink($post_id);
    
    // SameAs URLs
    $sameas_array = [];
    $amazon = get_field('amazon_url', $post_id);
    $audible = get_field('audible_url', $post_id);
    $goodreads = get_field('goodreads_url', $post_id);
    
    if ($google_books) $sameas_array[] = $google_books;
    if ($amazon) $sameas_array[] = $amazon;
    if ($audible) $sameas_array[] = $audible;
    if ($goodreads) $sameas_array[] = $goodreads;
    
    // Add custom sameAs (from textarea, one per line)
    $sameas_raw = get_field('sameas_urls', $post_id);
    if ($sameas_raw) {
        $sameas_array = array_merge($sameas_array, array_filter(array_map('trim', explode("\n", $sameas_raw))));
    }
    
    $sameas_array[] = get_permalink($post_id);
    
    if (!empty($sameas_array)) {
        $schema['sameAs'] = array_values(array_unique($sameas_array));
    }
    
    // Save to ACF field
    update_field('schema_markup', json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), $post_id);
}
