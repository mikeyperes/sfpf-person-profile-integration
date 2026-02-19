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
        $schema_type = get_option('sfpf_homepage_schema_type', 'none');
        
        if ($schema_type === 'none') {
            return; // Schema injection disabled
        }
        
        // Build the schema
        $schema = build_homepage_schema_for_injection($schema_type);
        
    } elseif (is_singular('book')) {
        // Single book page
        global $post;
        $schema = get_post_meta($post->ID, 'schema_markup', true);
        if (empty($schema)) {
            // Try ACF field
            $schema = function_exists('get_field') ? get_field('schema_markup', $post->ID) : null;
        }
        
    } elseif (is_singular('organization')) {
        // Single organization page
        global $post;
        $schema = get_post_meta($post->ID, 'schema_markup', true);
        if (empty($schema)) {
            $schema = function_exists('get_field') ? get_field('schema_markup', $post->ID) : null;
        }
    }
    
    // Output schema if we have it
    if ($schema && !empty($schema)) {
        output_schema_script($schema);
    }
}

/**
 * Build homepage schema dynamically for injection
 * 
 * @param string $schema_type Schema type setting
 * @return string|null JSON schema string or null
 */
function build_homepage_schema_for_injection($schema_type) {
    $site_url = rtrim(get_site_url(), '/');
    $site_name = get_bloginfo('name');
    $site_description = get_bloginfo('description');
    
    // Get founder info
    $founder = get_founder_full_info();
    
    if (!$founder) {
        return null;
    }
    
    $schema = [
        '@context' => 'https://schema.org',
        '@graph' => [],
    ];
    
    // Build Person schema
    $person = [
        '@type' => 'Person',
        '@id' => $site_url . '/#person',
        'name' => $founder['display_name'],
        'url' => $site_url . '/',
    ];
    
    if (!empty($founder['first_name'])) {
        $person['givenName'] = $founder['first_name'];
    }
    if (!empty($founder['last_name'])) {
        $person['familyName'] = $founder['last_name'];
    }
    if (!empty($founder['job_title'])) {
        $person['jobTitle'] = $founder['job_title'];
    }
    if (!empty($founder['email'])) {
        $person['email'] = $founder['email'];
    }
    if (!empty($founder['avatar_url'])) {
        $person['image'] = $founder['avatar_url'];
    }
    
    // Build sameAs from URLs
    $same_as = [];
    if (!empty($founder['urls'])) {
        foreach ($founder['urls'] as $url) {
            if (!empty($url) && filter_var($url, FILTER_VALIDATE_URL)) {
                $same_as[] = $url;
            }
        }
    }
    if (!empty($same_as)) {
        $person['sameAs'] = $same_as;
    }
    
    // Build ProfilePage schema
    $profile_page = [
        '@type' => 'ProfilePage',
        '@id' => $site_url . '/#profilepage',
        'url' => $site_url . '/',
        'name' => $site_name,
        'inLanguage' => 'en-US',
        'isPartOf' => [
            '@type' => 'WebSite',
            '@id' => $site_url . '/#website',
            'url' => $site_url . '/',
            'name' => $site_name,
        ],
    ];
    
    if (!empty($site_description)) {
        $profile_page['description'] = $site_description;
    }
    
    if (!empty($founder['avatar_url'])) {
        $profile_page['primaryImageOfPage'] = [
            '@type' => 'ImageObject',
            '@id' => $site_url . '/#headshot',
            'url' => $founder['avatar_url'],
            'contentUrl' => $founder['avatar_url'],
        ];
    }
    
    // Add to graph based on schema type
    switch ($schema_type) {
        case 'person':
            // Person only
            $schema['@graph'][] = $person;
            break;
            
        case 'profile_page_only':
            // ProfilePage without Person reference
            $schema['@graph'][] = $profile_page;
            break;
            
        case 'profile_page':
        default:
            // ProfilePage + Person (recommended)
            $profile_page['mainEntity'] = ['@id' => $site_url . '/#person'];
            $schema['@graph'][] = $profile_page;
            $schema['@graph'][] = $person;
            break;
    }
    
    return json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
}

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
    $schema = function_exists('get_field') ? get_field('schema_markup', $post_id) : get_post_meta($post_id, 'schema_markup', true);
    
    return [
        'raw' => $schema,
        'formatted' => $schema ? format_json_display($schema) : '<em>No schema generated</em>',
        'valid' => $schema ? (json_decode($schema) !== null) : false,
        'validator_url' => get_schema_validator_url(get_permalink($post_id)),
        'google_url' => get_google_rich_results_url(get_permalink($post_id)),
    ];
}
