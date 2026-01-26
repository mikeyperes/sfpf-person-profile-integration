<?php
namespace sfpf_person_website;

/**
 * Schema Injector
 * 
 * Injects schema markup into the page head via wp_head hook.
 * 
 * @package sfpf_person_website
 * @since 1.0.0
 */

defined('ABSPATH') || exit;

/**
 * Enable schema injection into page head
 * 
 * Called when the snippet is activated.
 */
function enable_schema_injection() {
    add_action('wp_head', __NAMESPACE__ . '\\inject_schema_markup', 1);
}

/**
 * Inject schema markup into page head
 * 
 * Checks the current page type and injects appropriate schema.
 */
function inject_schema_markup() {
    $schema = null;
    $post_id = null;
    
    // Check what type of page we're on
    if (is_front_page() && get_option('show_on_front') === 'page') {
        // Homepage
        $post_id = get_option('page_on_front');
        $schema = get_post_schema($post_id);
        
    } elseif (is_singular('book')) {
        // Single book page
        global $post;
        $post_id = $post->ID;
        $schema = get_post_schema($post_id);
        
    } elseif (is_singular('organization')) {
        // Single organization page
        global $post;
        $post_id = $post->ID;
        $schema = get_post_schema($post_id);
    }
    
    // Output schema if we have it
    if ($schema && !empty($schema)) {
        output_schema_script($schema, $post_id);
    }
}

/**
 * Output schema as JSON-LD script tag
 * 
 * @param string $schema JSON schema string
 * @param int $post_id Optional post ID for logging
 */
function output_schema_script($schema, $post_id = null) {
    // Validate JSON
    $decoded = json_decode($schema);
    if (json_last_error() !== JSON_ERROR_NONE) {
        write_log("Invalid JSON schema for post {$post_id}: " . json_last_error_msg(), true, 'Schema Injector');
        return;
    }
    
    // Output the schema
    echo "\n<!-- SFPF Person Website Schema -->\n";
    echo '<script type="application/ld+json">' . "\n";
    echo $schema;
    echo "\n</script>\n";
    echo "<!-- /SFPF Person Website Schema -->\n\n";
}

/**
 * Get schema for display in admin
 * 
 * @param int $post_id Post ID
 * @return array Schema info
 */
function get_schema_for_display($post_id) {
    $schema = get_post_schema($post_id);
    
    return [
        'raw' => $schema,
        'formatted' => $schema ? format_json_display($schema) : '<em>No schema generated</em>',
        'valid' => $schema ? (json_decode($schema) !== null) : false,
        'validator_url' => get_schema_validator_url(get_permalink($post_id)),
        'google_url' => get_google_rich_results_url(get_permalink($post_id)),
    ];
}
