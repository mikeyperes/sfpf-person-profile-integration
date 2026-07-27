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
    // CPT and ACF controls now live exclusively in the Core-rendered
    // Custom Post Types tab. The legacy option names remain supported there.
    $snippets = [];
    
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
