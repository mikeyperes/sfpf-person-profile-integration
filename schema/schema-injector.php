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
        
    } elseif (is_page()) {
        // Check if this is the biography page
        global $post;
        $bio_schema_type = get_option('sfpf_biography_schema_type', 'profile_page_only');
        if ($bio_schema_type !== 'none') {
            $bio_page_id = get_option('sfpf_page_biography');
            if ($bio_page_id && $post->ID == $bio_page_id) {
                $schema = build_homepage_schema_for_injection($bio_schema_type);
            }
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
    
    $user_id = $founder['id'];
    $user_key = 'user_' . $user_id;
    
    $schema = [
        '@context' => 'https://schema.org',
        '@graph' => [],
    ];
    
    // ── Build Person schema with ALL available fields ──
    $person = [
        '@type' => 'Person',
        '@id' => $site_url . '/#person',
        'name' => $founder['display_name'],
        'url' => $site_url . '/',
        'mainEntityOfPage' => $site_url,
    ];
    
    // Given/Family name
    if (!empty($founder['first_name'])) {
        $person['givenName'] = $founder['first_name'];
    }
    if (!empty($founder['last_name'])) {
        $person['familyName'] = $founder['last_name'];
    }
    
    // Additional name
    $additional_name = function_exists('get_field') ? get_field('additional_name', $user_key) : '';
    if (!empty($additional_name)) {
        $person['additionalName'] = sanitize_text_field($additional_name);
    }
    
    // Alternate names
    $alt_names = function_exists('get_field') ? get_field('alternate_names', $user_key) : [];
    if (!empty($alt_names) && is_array($alt_names)) {
        $names = array_filter(array_map(function($n) { return $n['name'] ?? ''; }, $alt_names));
        if (count($names) === 1) {
            $person['alternateName'] = sanitize_text_field(reset($names));
        } elseif (count($names) > 1) {
            $person['alternateName'] = array_map('sanitize_text_field', array_values($names));
        }
    }
    
    // Job title
    $title_field = function_exists('get_field') ? get_field('title', $user_key) : '';
    $job_title = !empty($title_field) ? wp_strip_all_tags($title_field) : ($founder['job_title'] ?? '');
    if (!empty($job_title)) {
        $person['jobTitle'] = sanitize_text_field($job_title);
    }
    
    // Email
    if (!empty($founder['email'])) {
        $person['email'] = $founder['email'];
    }
    
    // Birth date
    $birth_date = function_exists('get_field') ? get_field('birth_date', $user_key) : '';
    if (!empty($birth_date)) {
        $person['birthDate'] = sanitize_text_field($birth_date);
    }
    
    // Birth place
    $location_born = function_exists('get_field') ? get_field('location_born', $user_key) : [];
    if (!empty($location_born['location'])) {
        $person['birthPlace'] = [
            '@type' => 'Place',
            'name' => sanitize_text_field($location_born['location']),
        ];
    }
    
    // Nationality (repeater or legacy text)
    $nationality = function_exists('get_field') ? get_field('nationality', $user_key) : '';
    if (!empty($nationality)) {
        $nats = [];
        if (is_array($nationality)) {
            // Repeater format: array of ['value' => '...']
            $nats = array_filter(array_map(function($n) { return trim($n['value'] ?? ''); }, $nationality));
        } elseif (is_string($nationality)) {
            // Legacy text format: comma-separated
            $nats = array_filter(array_map('trim', explode(',', $nationality)));
        }
        if (count($nats) === 1) {
            $person['nationality'] = sanitize_text_field(reset($nats));
        } elseif (count($nats) > 1) {
            $person['nationality'] = array_map('sanitize_text_field', array_values($nats));
        }
    }
    
    // Image - Knowledge Graph images first, then avatar
    $kg_images = function_exists('get_field') ? get_field('knowledge_graph_images', $user_key) : [];
    if (!empty($kg_images) && is_array($kg_images)) {
        $image_urls = [];
        foreach ($kg_images as $img) {
            if (is_array($img) && !empty($img['url'])) {
                $image_urls[] = esc_url_raw($img['url']);
            }
        }
        if (!empty($image_urls)) {
            $person['image'] = count($image_urls) === 1 ? $image_urls[0] : $image_urls;
        }
    } elseif (!empty($founder['avatar_url'])) {
        $person['image'] = $founder['avatar_url'];
    }
    
    // Education / alumniOf
    $education = function_exists('get_field') ? get_field('education', $user_key) : [];
    if (!empty($education) && is_array($education)) {
        $alumni = [];
        foreach ($education as $edu) {
            if (empty($edu['college'])) continue;
            $entry = [
                '@type' => 'CollegeOrUniversity',
                'name' => sanitize_text_field($edu['college']),
            ];
            if (!empty($edu['wiki_url'])) {
                $entry['sameAs'] = esc_url_raw($edu['wiki_url']);
            }
            $alumni[] = $entry;
        }
        if (!empty($alumni)) {
            $person['alumniOf'] = $alumni;
        }
    }
    
    // worksFor - from Organization CPT
    $orgs = get_posts([
        'post_type' => 'organization',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'orderby' => 'date',
        'order' => 'ASC',
    ]);
    if (!empty($orgs)) {
        $works_for = [];
        foreach ($orgs as $org) {
            $org_url = get_field('url', $org->ID);
            $entry = [
                '@type' => 'Organization',
                'name' => sanitize_text_field($org->post_title),
            ];
            if (!empty($org_url) && filter_var($org_url, FILTER_VALIDATE_URL)) {
                $entry['url'] = esc_url_raw($org_url);
            }
            $works_for[] = $entry;
        }
        if (!empty($works_for)) {
            $person['worksFor'] = $works_for;
        }
    }
    
    // sameAs
    $same_as = [];
    if (!empty($founder['urls'])) {
        foreach ($founder['urls'] as $url) {
            if (!empty($url) && filter_var($url, FILTER_VALIDATE_URL)) {
                $same_as[] = $url;
            }
        }
    }
    // Also pull from sameas textarea field
    $sameas_text = function_exists('get_field') ? get_field('sameas', $user_key) : '';
    if (!empty($sameas_text)) {
        $lines = array_filter(array_map('trim', preg_split('/[\r\n]+/', $sameas_text)));
        foreach ($lines as $line) {
            if (filter_var($line, FILTER_VALIDATE_URL) && !in_array($line, $same_as)) {
                $same_as[] = $line;
            }
        }
    }
    // Social URLs from website options (social_media group)
    if (function_exists('get_field')) {
        $website_opts = get_field('website', 'option');
        $social_media = is_array($website_opts) ? ($website_opts['social_media'] ?? []) : [];
        if (is_array($social_media)) {
            foreach ($social_media as $platform => $url) {
                if (!empty($url) && filter_var($url, FILTER_VALIDATE_URL) && !in_array($url, $same_as)) {
                    $same_as[] = $url;
                }
            }
        }
    }
    // Article URLs as sameAs
    $articles = function_exists('get_field') ? get_field('articles', $user_key) : [];
    if (!empty($articles) && is_array($articles)) {
        foreach ($articles as $article) {
            $article_url = $article['url'] ?? '';
            if (!empty($article_url) && filter_var($article_url, FILTER_VALIDATE_URL) && !in_array($article_url, $same_as)) {
                $same_as[] = $article_url;
            }
        }
    }
    if (!empty($same_as)) {
        $person['sameAs'] = array_map('esc_url_raw', array_unique($same_as));
    }
    
    // Google Knowledge Graph ID
    $kgid = function_exists('get_field') ? get_field('knowledge_graph_id', $user_key) : '';
    if (!empty($kgid)) {
        // Add as identifier for Google to match
        $person['identifier'] = [
            '@type' => 'PropertyValue',
            'propertyID' => 'googleKgMID',
            'value' => sanitize_text_field($kgid),
        ];
    }
    
    // ── Build ProfilePage schema ──
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
    
    // Primary image
    $primary_image = isset($person['image']) 
        ? (is_array($person['image']) && !is_string($person['image'][0] ?? null) ? ($person['image'][0] ?? '') : (is_string($person['image']) ? $person['image'] : ($person['image'][0] ?? '')))
        : ($founder['avatar_url'] ?? '');
    if (!empty($primary_image)) {
        $profile_page['primaryImageOfPage'] = [
            '@type' => 'ImageObject',
            '@id' => $site_url . '/#headshot',
            'url' => $primary_image,
            'contentUrl' => $primary_image,
        ];
    }
    
    // Add to graph based on schema type
    switch ($schema_type) {
        case 'person':
            $schema['@graph'][] = $person;
            break;
            
        case 'profile_page_only':
            $schema['@graph'][] = $profile_page;
            break;
            
        case 'profile_page':
        default:
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
