<?php
namespace sfpf_person_website;

/**
 * Schema Builder
 * 
 * Builds schema objects by populating templates with dynamic content.
 * Pulls data from ACF fields, website settings, and post content.
 * 
 * @package sfpf_person_website
 * @since 1.0.0
 */

defined('ABSPATH') || exit;

/**
 * Build Person schema from website settings
 * 
 * @param bool $include_context Whether to include @context
 * @return array Person schema
 */
function build_person_schema_from_settings($include_context = false) {
    $site_url = get_site_url_clean();
    
    // Get website user info
    $user_id = get_website_user_info('ID');
    $user_data = $user_id ? get_userdata($user_id) : null;
    
    // Get founder info
    $founder = get_acf_field('founder', 'option', []);
    $founder_user_id = isset($founder['user']['ID']) ? $founder['user']['ID'] : null;
    $founder_user = $founder_user_id ? get_userdata($founder_user_id) : null;
    
    // Prioritize founder user if set, otherwise use website user
    $primary_user = $founder_user ?: $user_data;
    $primary_user_id = $founder_user_id ?: $user_id;
    
    if (!$primary_user) {
        write_log('No user found for Person schema', true, 'Schema Builder');
        return [];
    }
    
    // Build person data
    $person = [];
    
    if ($include_context) {
        $person['@context'] = 'https://schema.org';
    }
    
    $person['@type'] = 'Person';
    $person['@id'] = $site_url . '/#person';
    
    // Name
    $display_name = $primary_user->display_name;
    if ($display_name) {
        $person['name'] = sanitize_text_field($display_name);
    }
    
    // Given/Family name
    if ($primary_user->first_name) {
        $person['givenName'] = sanitize_text_field($primary_user->first_name);
    }
    if ($primary_user->last_name) {
        $person['familyName'] = sanitize_text_field($primary_user->last_name);
    }
    
    // URL - use site URL for homepage person
    $person['url'] = $site_url . '/';
    
    // Email from website settings
    $email = get_nested_acf_field('website.email', 'option');
    if ($email && is_email($email)) {
        $person['email'] = sanitize_email($email);
    }
    
    // Biography
    $bio = get_nested_acf_field('website.biography_short', 'option');
    if (empty($bio)) {
        $bio = get_nested_acf_field('website.biography', 'option');
    }
    if ($bio) {
        $person['description'] = wp_strip_all_tags($bio);
    }
    
    // Job title from user meta
    $job_title = get_field('job_title', 'user_' . $primary_user_id);
    if ($job_title) {
        $person['jobTitle'] = sanitize_text_field($job_title);
    }
    
    // Image - avatar or custom
    $avatar_url = get_avatar_url($primary_user_id, ['size' => 400]);
    if ($avatar_url) {
        $person['image'] = esc_url_raw($avatar_url);
    }
    
    // Social URLs as sameAs
    $social_urls = get_user_social_urls($primary_user_id);
    if (!empty($social_urls)) {
        $same_as = array_values(array_filter($social_urls, function($url) {
            return filter_var($url, FILTER_VALIDATE_URL);
        }));
        if (!empty($same_as)) {
            $person['sameAs'] = array_map('esc_url_raw', $same_as);
        }
    }
    
    return $person;
}

/**
 * Build Homepage schema (Person or ProfilePage)
 * 
 * @param int $post_id Post ID
 * @param string $schema_type 'person' or 'profile_page'
 * @return array Schema
 */
function build_homepage_schema($post_id, $schema_type = 'profile_page') {
    $site_url = get_site_url_clean();
    $site_name = get_bloginfo('name');
    
    if ($schema_type === 'person') {
        // Return just the Person schema
        return build_person_schema_from_settings(true);
    }
    
    // Build ProfilePage with Person
    $person = build_person_schema_from_settings(false);
    
    if (empty($person)) {
        return [];
    }
    
    // Build ProfilePage wrapper
    $profile_page = [
        '@type' => 'ProfilePage',
        '@id' => $site_url . '/#profilepage',
        'url' => $site_url . '/',
        'name' => isset($person['name']) ? $person['name'] : $site_name,
        'inLanguage' => get_bloginfo('language') ?: 'en-US',
        'isPartOf' => [
            '@type' => 'WebSite',
            '@id' => $site_url . '/#website',
            'url' => $site_url . '/',
            'name' => $site_name,
        ],
        'mainEntity' => [
            '@id' => $site_url . '/#person',
        ],
    ];
    
    // Add description if available
    if (isset($person['description'])) {
        $profile_page['description'] = $person['description'];
    }
    
    // Add primary image if person has image
    if (isset($person['image'])) {
        $profile_page['primaryImageOfPage'] = [
            '@type' => 'ImageObject',
            '@id' => $site_url . '/#headshot',
            'url' => $person['image'],
            'contentUrl' => $person['image'],
        ];
    }
    
    // Add dateModified from post
    $profile_page['dateModified'] = get_post_modified_time('c', true, $post_id);
    
    // Build the full graph schema
    $schema = [
        '@context' => 'https://schema.org',
        '@graph' => [
            $profile_page,
            $person,
        ],
    ];
    
    return $schema;
}

/**
 * Build Book schema
 * 
 * @param int $post_id Post ID
 * @return array Schema
 */
function build_book_schema($post_id) {
    $permalink = get_permalink($post_id);
    $title = get_the_title($post_id);
    $site_url = get_site_url_clean();
    
    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'Book',
        '@id' => $permalink . '#book',
        'name' => sanitize_text_field($title),
        'url' => $permalink,
    ];
    
    // Subtitle
    $subtitle = get_field('subtitle', $post_id);
    if ($subtitle) {
        $schema['alternativeHeadline'] = sanitize_text_field($subtitle);
    }
    
    // Description
    $description = get_field('description', $post_id);
    if ($description) {
        $schema['description'] = wp_strip_all_tags($description);
    }
    
    // Cover image
    $cover = get_field('cover', $post_id);
    if (is_array($cover) && !empty($cover['url'])) {
        $schema['image'] = [
            '@type' => 'ImageObject',
            'url' => esc_url_raw($cover['url']),
        ];
        if (!empty($cover['width'])) {
            $schema['image']['width'] = (int) $cover['width'];
        }
        if (!empty($cover['height'])) {
            $schema['image']['height'] = (int) $cover['height'];
        }
    } elseif ($thumbnail = get_the_post_thumbnail_url($post_id, 'full')) {
        $schema['image'] = [
            '@type' => 'ImageObject',
            'url' => esc_url_raw($thumbnail),
        ];
    }
    
    // Author - reference the site's person
    $schema['author'] = [
        '@id' => $site_url . '/#person',
    ];
    
    // Publishing company
    $publisher = get_field('publishing_company', $post_id);
    if ($publisher) {
        $schema['publisher'] = [
            '@type' => 'Organization',
            'name' => sanitize_text_field(wp_strip_all_tags($publisher)),
        ];
    }
    
    // Language
    $schema['inLanguage'] = 'en';
    
    // Build sameAs from URLs
    $same_as = [];
    $url_fields = [
        'amazon_url',
        'audible_url',
        'google_books_url',
        'goodreads_url',
    ];
    
    foreach ($url_fields as $field) {
        $url = get_field($field, $post_id);
        if ($url && filter_var($url, FILTER_VALIDATE_URL)) {
            $same_as[] = esc_url_raw($url);
        }
    }
    
    // Add permalink as sameAs
    $same_as[] = $permalink;
    
    if (!empty($same_as)) {
        $schema['sameAs'] = array_unique($same_as);
    }
    
    return $schema;
}

/**
 * Build Organization schema
 * 
 * @param int $post_id Post ID
 * @return array Schema
 */
function build_organization_schema($post_id) {
    $permalink = get_permalink($post_id);
    $title = get_the_title($post_id);
    $site_url = get_site_url_clean();
    
    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        '@id' => $permalink . '#organization',
        'name' => sanitize_text_field($title),
    ];
    
    // URL - use custom URL if set, otherwise permalink
    $url = get_field('url', $post_id);
    $schema['url'] = $url && filter_var($url, FILTER_VALIDATE_URL) 
        ? esc_url_raw($url) 
        : $permalink;
    
    // Description
    $description = get_field('short_summary', $post_id);
    if (empty($description)) {
        $description = get_field('mission_statement', $post_id);
    }
    if ($description) {
        $schema['description'] = wp_strip_all_tags($description);
    }
    
    // Logo/Image
    $logo = get_field('image_cropped', $post_id);
    if (is_array($logo) && !empty($logo['url'])) {
        $schema['logo'] = [
            '@type' => 'ImageObject',
            'url' => esc_url_raw($logo['url']),
        ];
    } elseif ($thumbnail = get_the_post_thumbnail_url($post_id, 'full')) {
        $schema['logo'] = [
            '@type' => 'ImageObject',
            'url' => esc_url_raw($thumbnail),
        ];
    }
    
    // Founder
    $founder = get_field('founder', $post_id);
    if ($founder) {
        // Check if it references the site person
        $schema['founder'] = [
            '@id' => $site_url . '/#person',
        ];
    }
    
    // Founding date
    $founding_date = get_field('founding_date', $post_id);
    if ($founding_date) {
        $schema['foundingDate'] = sanitize_text_field($founding_date);
    }
    
    // Headquarters as address
    $headquarters = get_field('headquarters', $post_id);
    if ($headquarters) {
        $schema['address'] = [
            '@type' => 'PostalAddress',
            'name' => sanitize_text_field(wp_strip_all_tags($headquarters)),
        ];
    }
    
    // Build sameAs from URLs
    $same_as = [];
    
    // Parse social URLs field (one per line)
    $social_urls = get_field('social_urls', $post_id);
    if ($social_urls) {
        $lines = preg_split('/[\r\n]+/', wp_strip_all_tags($social_urls));
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line && filter_var($line, FILTER_VALIDATE_URL)) {
                $same_as[] = esc_url_raw($line);
            }
        }
    }
    
    // Parse sameas URLs field
    $sameas_urls = get_field('sameas_urls', $post_id);
    if ($sameas_urls) {
        $lines = preg_split('/[\r\n]+/', wp_strip_all_tags($sameas_urls));
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line && filter_var($line, FILTER_VALIDATE_URL)) {
                $same_as[] = esc_url_raw($line);
            }
        }
    }
    
    if (!empty($same_as)) {
        $schema['sameAs'] = array_unique($same_as);
    }
    
    return $schema;
}

/**
 * Build schema for a post on save
 * 
 * Hook into save_post action.
 * 
 * @param int $post_id Post ID
 */
function enable_schema_on_save() {
    add_action('save_post', __NAMESPACE__ . '\\handle_schema_on_save', 20, 2);
}

/**
 * Handle schema generation on post save
 * 
 * @param int $post_id Post ID
 * @param WP_Post $post Post object
 */
function handle_schema_on_save($post_id, $post) {
    // Skip autosaves and revisions
    if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) {
        return;
    }
    
    // Skip if not a supported post type
    $supported_types = ['book', 'organization', 'page'];
    if (!in_array($post->post_type, $supported_types, true)) {
        return;
    }
    
    // For pages, only process the front page
    if ($post->post_type === 'page' && !is_front_page_id($post_id)) {
        return;
    }
    
    // Generate and save schema
    generate_and_save_schema($post_id);
}
