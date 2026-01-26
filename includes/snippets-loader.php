<?php
namespace sfpf_person_website;

/**
 * Snippets Loader
 * 
 * Manages snippet registration and loading.
 * 
 * @package sfpf_person_website
 * @since 1.0.0
 */

defined('ABSPATH') || exit;

/**
 * Get all snippets
 * 
 * @param string $type Type filter ('all', 'cpt', 'acf')
 * @return array Snippets array
 */
function get_snippets($type = 'all') {
    $snippets = [
        // CPT Snippets
        [
            'id' => 'sfpf_enable_book_cpt',
            'name' => 'Book Custom Post Type',
            'description' => 'Registers the "book" custom post type for managing publications.',
            'file' => 'register-cpt-book.php',
            'type' => 'cpt',
            'info' => 'Post type: book | Archive: /books/',
        ],
        [
            'id' => 'sfpf_enable_organization_cpt',
            'name' => 'Organization Custom Post Type',
            'description' => 'Registers the "organization" custom post type for companies/organizations.',
            'file' => 'register-cpt-organization.php',
            'type' => 'cpt',
            'info' => 'Post type: organization | Archive: /organizations/',
        ],
        
        // ACF Snippets
        [
            'id' => 'sfpf_enable_book_acf',
            'name' => 'Book ACF Fields',
            'description' => 'Adds custom fields to books: URLs, description, cover, author bio, etc.',
            'file' => 'register-acf-book.php',
            'type' => 'acf',
            'info' => 'Fields: audible_url, google_books_url, goodreads_url, amazon_url, cover, description, author_bio, subtitle, featured',
        ],
        [
            'id' => 'sfpf_enable_organization_acf',
            'name' => 'Organization ACF Fields',
            'description' => 'Adds custom fields to organizations: logo, description, URLs, founder, etc.',
            'file' => 'register-acf-organization.php',
            'type' => 'acf',
            'info' => 'Fields: logo, description, website, social URLs, founding_date, founder, naics, employees',
        ],
        [
            'id' => 'sfpf_enable_homepage_acf',
            'name' => 'Homepage ACF Fields',
            'description' => 'Adds schema field to the front page for managing homepage schema.',
            'file' => 'register-acf-homepage.php',
            'type' => 'acf',
            'info' => 'Detects front page and adds: schema (readonly), schema_type selection',
        ],
    ];
    
    if ($type === 'all') {
        return $snippets;
    }
    
    return array_filter($snippets, function($s) use ($type) {
        return ($s['type'] ?? '') === $type;
    });
}

/**
 * Get snippet by ID
 * 
 * @param string $snippet_id Snippet ID
 * @return array|null Snippet data or null
 */
function get_snippet($snippet_id) {
    $snippets = get_snippets('all');
    
    foreach ($snippets as $snippet) {
        if ($snippet['id'] === $snippet_id) {
            return $snippet;
        }
    }
    
    return null;
}

/**
 * Check if snippet file exists
 * 
 * @param string $snippet_id Snippet ID
 * @return bool True if file exists
 */
function snippet_file_exists($snippet_id) {
    $snippet = get_snippet($snippet_id);
    
    if (!$snippet) {
        return false;
    }
    
    $file = SFPF_PLUGIN_DIR . 'snippets/' . $snippet['file'];
    return file_exists($file);
}
