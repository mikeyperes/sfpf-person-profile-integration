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
    
    // Check what type of page we're on
    if (is_front_page()) {
        // Homepage - build schema dynamically based on settings
        $schema_type = get_option('sfpf_homepage_schema_type', 'person');
        
        if ($schema_type === 'none') {
            return; // Schema injection disabled
        }
        
        // Build the schema
        $schema = build_homepage_schema_for_injection($schema_type, get_front_page_id());
        
    } elseif (is_singular('book')) {
        // Single book page — generate live from unified builder
        global $post;
        $schema_arr = build_book_schema($post->ID);
        $schema = !empty($schema_arr) ? json_encode($schema_arr, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : null;
        
    } elseif (is_singular('organization')) {
        // Single organization page — generate live from unified builder
        global $post;
        $schema_arr = build_organization_schema($post->ID);
        $schema = !empty($schema_arr) ? json_encode($schema_arr, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : null;
        
    } elseif (is_page()) {
        // Check if this is the biography page
        global $post;
            $bio_schema_type = get_option('sfpf_biography_schema_type', 'profile_page_only');
        if ($bio_schema_type !== 'none') {
            $bio_page_id = get_option('sfpf_page_biography');
            if ($bio_page_id && $post->ID == $bio_page_id) {
                $schema = build_homepage_schema_for_injection($bio_schema_type, $post->ID);
            }
        }
    }
    
    // Output schema if we have it
    if ($schema && !empty($schema)) {
        output_schema_script($schema);
    }
}
// build_homepage_schema_for_injection() moved to schema-builder.php

/**
 * Output schema as JSON-LD script tag
 * 
 * @param string $schema JSON schema string
 */
function output_schema_script($schema) {
    // Handle if schema is an array
    if (is_array($schema)) {
        $schema = json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
    
    // Validate JSON
    $decoded = json_decode($schema);
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "\n<!-- SFPF Schema Error: Invalid JSON - " . esc_html(json_last_error_msg()) . " -->\n";
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
    $schema = function_exists(__NAMESPACE__ . '\\get_post_schema')
        ? get_post_schema($post_id)
        : get_post_meta($post_id, 'schema_markup', true);
    
    return [
        'raw' => $schema,
        'formatted' => $schema ? format_json_display($schema) : '<em>No schema generated</em>',
        'valid' => $schema ? (json_decode($schema) !== null) : false,
        'validator_url' => get_schema_validator_url(get_permalink($post_id)),
        'google_url' => get_google_rich_results_url(get_permalink($post_id)),
    ];
}
