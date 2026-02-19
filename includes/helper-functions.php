<?php
namespace sfpf_person_website;

/**
 * Helper Functions
 * 
 * Common utility functions used throughout the plugin.
 * Includes proper founder/company user detection matching hws-base-tools.
 * 
 * @package sfpf_person_website
 * @since 1.0.0
 */

defined('ABSPATH') || exit;

/**
 * =============================================================================
 * FOUNDER USER DETECTION
 * Matches hws-base-tools logic exactly
 * =============================================================================
 */

/**
 * Resolve founder user ID from ACF options
 * 
 * Priority:
 * 1) option → founder → founder_user (new field name)
 * 2) option → founder → user (legacy)
 * 3) option → website → company (fallback)
 * 
 * @return int User ID or 0 if not found
 */
function get_founder_user_id() {
    if (!function_exists('get_field')) {
        return 0;
    }

    // 1) Primary: option → founder → founder_user (new field name)
    $founder = get_field('founder', 'option');
    if (is_array($founder) && !empty($founder['founder_user'])) {
        $uf = $founder['founder_user'];
        if (is_array($uf) && isset($uf['ID'])) return (int) $uf['ID'];
        if (is_object($uf) && isset($uf->ID)) return (int) $uf->ID;
        return (int) $uf;
    }

    // 2) Legacy: option → founder → user
    if (is_array($founder) && !empty($founder['user'])) {
        $uf = $founder['user'];
        if (is_array($uf) && isset($uf['ID'])) return (int) $uf['ID'];
        if (is_object($uf) && isset($uf->ID)) return (int) $uf->ID;
        return (int) $uf;
    }

    // 3) Fallback: option → website → company
    $website = get_field('website', 'option');
    if (is_array($website) && !empty($website['company'])) {
        $uf = $website['company'];
        if (is_array($uf) && isset($uf['ID'])) return (int) $uf['ID'];
        if (is_object($uf) && isset($uf->ID)) return (int) $uf->ID;
        return (int) $uf;
    }

    return 0;
}

/**
 * Resolve company user ID from ACF options
 * 
 * @return int User ID or 0 if not found
 */
function get_company_user_id() {
    if (!function_exists('get_field')) {
        return 0;
    }

    $website = get_field('website', 'option');
    if (is_array($website) && !empty($website['company'])) {
        $uf = $website['company'];
        if (is_array($uf) && isset($uf['ID'])) return (int) $uf['ID'];
        if (is_object($uf) && isset($uf->ID)) return (int) $uf->ID;
        return (int) $uf;
    }

    return 0;
}

/**
 * Get comprehensive founder information
 * 
 * @return array|null Founder data array or null if not found
 */
function get_founder_full_info() {
    $user_id = get_founder_user_id();
    if (!$user_id) {
        return null;
    }

    $userdata = get_userdata($user_id);
    if (!$userdata) {
        return null;
    }

    $user_key = 'user_' . $user_id;
    
    // Get ACF fields
    $urls = function_exists('get_field') ? get_field('urls', $user_key) : [];
    $biography = function_exists('get_field') ? get_field('biography', $user_key) : '';
    $website = function_exists('get_field') ? get_field('website', $user_key) : '';
    $additional = function_exists('get_field') ? get_field('additional', $user_key) : [];
    
    // Get founder group for option-level overrides
    $founder_group = function_exists('get_field') ? get_field('founder', 'option') : [];
    $founder_biography = (is_array($founder_group) && !empty($founder_group['biography'])) 
        ? $founder_group['biography'] : '';

    return [
        'id' => $user_id,
        'display_name' => $userdata->display_name,
        'first_name' => $userdata->first_name,
        'last_name' => $userdata->last_name,
        'email' => $userdata->user_email,
        'description' => $userdata->description,
        'user_url' => $userdata->user_url,
        'avatar_url' => get_avatar_url($user_id, ['size' => 200]),
        'edit_url' => get_edit_user_link($user_id),
        'view_url' => get_author_posts_url($user_id),
        'biography' => $founder_biography ?: ($biography ?: $userdata->description),
        'website' => $website ?: $userdata->user_url,
        'job_title' => is_array($additional) ? ($additional['title'] ?? '') : '',
        'public_email' => is_array($additional) ? ($additional['public_email'] ?? '') : '',
        'public_phone' => is_array($additional) ? ($additional['public_phone'] ?? '') : '',
        'urls' => is_array($urls) ? array_filter($urls) : [],
    ];
}

/**
 * Get comprehensive company information
 * 
 * @return array|null Company data array or null if not found
 */
function get_company_full_info() {
    $user_id = get_company_user_id();
    if (!$user_id) {
        return null;
    }

    $userdata = get_userdata($user_id);
    if (!$userdata) {
        return null;
    }

    $user_key = 'user_' . $user_id;
    
    // Get ACF fields
    $urls = function_exists('get_field') ? get_field('urls', $user_key) : [];
    $biography = function_exists('get_field') ? get_field('biography', $user_key) : '';
    $website = function_exists('get_field') ? get_field('website', $user_key) : '';
    $additional = function_exists('get_field') ? get_field('additional', $user_key) : [];

    return [
        'id' => $user_id,
        'display_name' => $userdata->display_name,
        'first_name' => $userdata->first_name,
        'last_name' => $userdata->last_name,
        'email' => $userdata->user_email,
        'description' => $userdata->description,
        'user_url' => $userdata->user_url,
        'avatar_url' => get_avatar_url($user_id, ['size' => 200]),
        'edit_url' => get_edit_user_link($user_id),
        'view_url' => get_author_posts_url($user_id),
        'biography' => $biography ?: $userdata->description,
        'website' => $website ?: $userdata->user_url,
        'job_title' => is_array($additional) ? ($additional['title'] ?? '') : '',
        'public_email' => is_array($additional) ? ($additional['public_email'] ?? '') : '',
        'public_phone' => is_array($additional) ? ($additional['public_phone'] ?? '') : '',
        'urls' => is_array($urls) ? array_filter($urls) : [],
    ];
}

/**
 * =============================================================================
 * HWS BASE TOOLS INTEGRATION
 * =============================================================================
 */

/**
 * Check if HWS Base Tools plugin is active
 * 
 * @return bool
 */
function is_hws_base_tools_active() {
    return defined('HWS_BASE_TOOLS_VERSION') || 
           is_plugin_active('hws-base-tools/initialization.php') ||
           class_exists('\\hws_base_tools\\Config');
}

/**
 * Get HWS Base Tools plugin information
 * 
 * @return array Plugin data
 */
function get_hws_base_tools_info() {
    if (!function_exists('get_plugin_data')) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }
    
    $plugin_file = WP_PLUGIN_DIR . '/hws-base-tools/initialization.php';
    
    if (!file_exists($plugin_file)) {
        return [
            'active' => false,
            'name' => 'HWS Base Tools',
            'version' => 'Not Installed',
            'author' => '',
            'description' => '',
        ];
    }
    
    $plugin_data = get_plugin_data($plugin_file);
    
    return [
        'active' => is_hws_base_tools_active(),
        'name' => $plugin_data['Name'] ?? 'HWS Base Tools',
        'version' => $plugin_data['Version'] ?? 'Unknown',
        'author' => $plugin_data['Author'] ?? '',
        'description' => $plugin_data['Description'] ?? '',
        'text_domain' => $plugin_data['TextDomain'] ?? '',
    ];
}

/**
 * Get the HWS Base Tools admin URL
 * 
 * @return string Admin URL for HWS Base Tools
 */
function get_hws_base_tools_url() {
    return admin_url('admin.php?page=hws-core-tools');
}

/**
 * Get the website settings admin URL
 * 
 * @return string Admin URL for website settings
 */
function get_website_settings_url() {
    return admin_url('admin.php?page=website-settings');
}

/**
 * Check if a HWS Base Tools snippet is enabled
 * 
 * @param string $snippet_id The snippet ID to check
 * @return bool True if enabled
 */
function is_hws_snippet_enabled($snippet_id) {
    return (bool) get_option($snippet_id, false);
}

/**
 * =============================================================================
 * URL HELPERS
 * =============================================================================
 */

/**
 * Get the current site URL (without trailing slash)
 * 
 * @return string Site URL
 */
function get_site_url_clean() {
    return rtrim(get_site_url(), '/');
}

/**
 * Build schema validator URL
 * 
 * @param string $url The URL to validate
 * @return string Schema validator URL
 */
function get_schema_validator_url($url) {
    return 'https://validator.schema.org/#url=' . urlencode($url);
}

/**
 * Build Google Rich Results Test URL
 * 
 * @param string $url The URL to test
 * @return string Google Rich Results Test URL
 */
function get_google_rich_results_url($url) {
    return 'https://search.google.com/test/rich-results?url=' . urlencode($url);
}

/**
 * =============================================================================
 * ACF HELPERS
 * =============================================================================
 */

/**
 * Get ACF field value safely
 * 
 * @param string $field_name Field name
 * @param mixed $post_id Post ID or 'option'
 * @param mixed $default Default value if field is empty
 * @return mixed Field value or default
 */
function get_acf_field($field_name, $post_id = false, $default = '') {
    if (!function_exists('get_field')) {
        return $default;
    }
    
    $value = get_field($field_name, $post_id);
    return ($value !== null && $value !== '' && $value !== false) ? $value : $default;
}

/**
 * =============================================================================
 * SHORTCODE HELPERS
 * =============================================================================
 */

/**
 * Get all available shortcodes organized by category
 * 
 * @return array Categorized shortcodes
 */
function get_all_shortcodes() {
    return [
        'Website Content' => [
            ['shortcode' => '[website_content field="dmca"]', 'description' => 'DMCA policy content'],
            ['shortcode' => '[website_content field="mission_statement"]', 'description' => 'Mission statement'],
            ['shortcode' => '[website_content field="biography"]', 'description' => 'Full biography'],
            ['shortcode' => '[website_content field="biography_short"]', 'description' => 'Short biography'],
            ['shortcode' => '[website_content field="email"]', 'description' => 'Contact email'],
        ],
        'Founder' => [
            ['shortcode' => '[founder id="name"]', 'description' => 'Founder name'],
            ['shortcode' => '[founder id="title"]', 'description' => 'Founder display name'],
            ['shortcode' => '[founder id="first_name"]', 'description' => 'First name'],
            ['shortcode' => '[founder id="last_name"]', 'description' => 'Last name'],
            ['shortcode' => '[founder id="email"]', 'description' => 'Email address'],
            ['shortcode' => '[founder id="biography"]', 'description' => 'Founder biography'],
            ['shortcode' => '[founder id="biography_short"]', 'description' => 'Short biography'],
            ['shortcode' => '[founder id="mission_statement"]', 'description' => 'Mission statement'],
            ['shortcode' => '[founder id="avatar"]', 'description' => 'Avatar URL'],
            ['shortcode' => '[founder id="website"]', 'description' => 'Website URL'],
            ['shortcode' => '[founder id="additional_public_email"]', 'description' => 'Public email'],
            ['shortcode' => '[founder id="additional_public_phone"]', 'description' => 'Public phone'],
            ['shortcode' => '[founder id="additional_title"]', 'description' => 'Job title/position'],
        ],
        'Company' => [
            ['shortcode' => '[company id="name"]', 'description' => 'Company name'],
            ['shortcode' => '[company id="title"]', 'description' => 'Company display name'],
            ['shortcode' => '[company id="email"]', 'description' => 'Company email'],
            ['shortcode' => '[company id="biography"]', 'description' => 'Company biography'],
            ['shortcode' => '[company id="biography_short"]', 'description' => 'Short biography'],
            ['shortcode' => '[company id="mission_statement"]', 'description' => 'Mission statement'],
            ['shortcode' => '[company id="website"]', 'description' => 'Company website'],
            ['shortcode' => '[company id="avatar"]', 'description' => 'Company logo URL'],
        ],
        'Social URLs' => [
            ['shortcode' => '[website_url social="facebook"]', 'description' => 'Facebook URL'],
            ['shortcode' => '[website_url social="instagram"]', 'description' => 'Instagram URL'],
            ['shortcode' => '[website_url social="linkedin"]', 'description' => 'LinkedIn URL'],
            ['shortcode' => '[website_url social="twitter"]', 'description' => 'Twitter URL'],
            ['shortcode' => '[website_url social="x"]', 'description' => 'X URL'],
            ['shortcode' => '[website_url social="youtube"]', 'description' => 'YouTube URL'],
            ['shortcode' => '[website_url social="tiktok"]', 'description' => 'TikTok URL'],
            ['shortcode' => '[website_url social="github"]', 'description' => 'GitHub URL'],
            ['shortcode' => '[website_url social="wikipedia"]', 'description' => 'Wikipedia URL'],
            ['shortcode' => '[website_url social="imdb"]', 'description' => 'IMDb URL'],
            ['shortcode' => '[website_url social="muckrack"]', 'description' => 'MuckRack URL'],
            ['shortcode' => '[website_url social="crunchbase"]', 'description' => 'Crunchbase URL'],
            ['shortcode' => '[website_url social="amazon"]', 'description' => 'Amazon URL'],
            ['shortcode' => '[website_url social="audible"]', 'description' => 'Audible URL'],
        ],
        'Founder URLs' => [
            ['shortcode' => '[founder id="url_facebook"]', 'description' => 'Founder Facebook'],
            ['shortcode' => '[founder id="url_instagram"]', 'description' => 'Founder Instagram'],
            ['shortcode' => '[founder id="url_linkedin"]', 'description' => 'Founder LinkedIn'],
            ['shortcode' => '[founder id="url_twitter"]', 'description' => 'Founder Twitter/X'],
            ['shortcode' => '[founder id="url_youtube"]', 'description' => 'Founder YouTube'],
            ['shortcode' => '[founder id="url_tiktok"]', 'description' => 'Founder TikTok'],
        ],
        'Organization URLs' => [
            ['shortcode' => '[organization field="url"]', 'description' => 'Website URL'],
            ['shortcode' => '[organization field="url_facebook"]', 'description' => 'Facebook'],
            ['shortcode' => '[organization field="url_instagram"]', 'description' => 'Instagram'],
            ['shortcode' => '[organization field="url_linkedin"]', 'description' => 'LinkedIn'],
            ['shortcode' => '[organization field="url_x"]', 'description' => 'X (Twitter)'],
            ['shortcode' => '[organization field="url_youtube"]', 'description' => 'YouTube'],
            ['shortcode' => '[organization field="url_tiktok"]', 'description' => 'TikTok'],
            ['shortcode' => '[organization field="url_github"]', 'description' => 'GitHub'],
            ['shortcode' => '[organization field="url_wikipedia"]', 'description' => 'Wikipedia'],
            ['shortcode' => '[organization field="url_crunchbase"]', 'description' => 'Crunchbase'],
        ],
    ];
}

/**
 * =============================================================================
 * JSON FORMATTING
 * =============================================================================
 */

/**
 * Format JSON for display with dark theme syntax highlighting
 * 
 * @param array|string $json JSON data or string
 * @param bool $dark_theme Use dark theme colors
 * @return string Formatted HTML
 */
function format_json_display($json, $dark_theme = true) {
    if (is_array($json)) {
        $json = json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
    
    $json = esc_html($json);
    
    if ($dark_theme) {
        // Dark theme colors (VS Code style)
        $json = preg_replace('/"([^"]+)"(\s*):/', '<span style="color:#9cdcfe;">"$1"</span>$2:', $json);
        $json = preg_replace('/:\s*"([^"]*)"/', ': <span style="color:#ce9178;">"$1"</span>', $json);
        $json = preg_replace('/:\s*(\d+)([,\n\r])/', ': <span style="color:#b5cea8;">$1</span>$2', $json);
        $json = preg_replace('/:\s*(true|false|null)([,\n\r])/i', ': <span style="color:#569cd6;">$1</span>$2', $json);
        
        return '<pre style="background:#1e1e1e;color:#d4d4d4;padding:20px;border-radius:6px;overflow:auto;max-height:500px;font-size:13px;line-height:1.6;font-family:\'Monaco\',\'Menlo\',\'Consolas\',monospace;margin:0;">' . $json . '</pre>';
    }
    
    // Light theme
    $json = preg_replace('/"([^"]+)"(\s*):/', '<span style="color:#881391;">"$1"</span>$2:', $json);
    $json = preg_replace('/:\s*"([^"]*)"/', ': <span style="color:#1a1aa6;">"$1"</span>', $json);
    $json = preg_replace('/:\s*(\d+)([,\n\r])/', ': <span style="color:#116644;">$1</span>$2', $json);
    $json = preg_replace('/:\s*(true|false|null)([,\n\r])/i', ': <span style="color:#ee7700;">$1</span>$2', $json);
    
    return '<pre style="background:#f8f8f8;padding:20px;border:1px solid #ddd;border-radius:6px;overflow:auto;max-height:500px;font-size:13px;line-height:1.6;font-family:\'Monaco\',\'Menlo\',\'Consolas\',monospace;margin:0;">' . $json . '</pre>';
}

/**
 * =============================================================================
 * PAGE HELPERS
 * =============================================================================
 */

/**
 * Get critical pages structure
 * 
 * @return array Pages structure with hierarchy
 */
function get_critical_pages_structure() {
    return [
        'biography' => [
            'title' => 'Biography',
            'slug' => 'biography',
            'parent' => null,
            'children' => [
                'education' => [
                    'title' => 'Education',
                    'slug' => 'education',
                ],
                'location_born' => [
                    'title' => 'Location Born',
                    'slug' => 'location-born',
                ],
                'organizations_founded' => [
                    'title' => 'Organizations Founded',
                    'slug' => 'organizations-founded',
                ],
                'alternate_names' => [
                    'title' => 'Alternate Names',
                    'slug' => 'alternate-names',
                ],
                'professions' => [
                    'title' => 'Professions',
                    'slug' => 'professions',
                ],
            ],
        ],
    ];
}

/**
 * Get default page template content
 * 
 * @param string $page_key The page key
 * @return string Default content for the page
 */
function get_default_page_template($page_key) {
    $templates = [
        'biography' => '<h2>Biography</h2>
<p>[founder id="biography"]</p>

<h3>Quick Facts</h3>
<ul>
<li><strong>Full Name:</strong> [founder id="name"]</li>
<li><strong>Position:</strong> [founder id="additional_title"]</li>
<li><strong>Email:</strong> [founder id="email"]</li>
</ul>',

        'education' => '<h2>Education</h2>
<p>Educational background and academic achievements of [founder id="name"].</p>',

        'location_born' => '<h2>Birthplace</h2>
<p>Information about where [founder id="name"] was born and raised.</p>',

        'organizations_founded' => '<h2>Organizations Founded</h2>
<p>Companies and organizations founded by [founder id="name"].</p>',

        'alternate_names' => '<h2>Also Known As</h2>
<p>Alternative names and aliases for [founder id="name"].</p>',

        'professions' => '<h2>Professional Background</h2>
<p>Career and professional roles of [founder id="name"].</p>

<h3>Current Position</h3>
<p>[founder id="additional_title"]</p>',
    ];
    
    return $templates[$page_key] ?? '';
}

/**
 * Render page action buttons
 * 
 * @param int $page_id Page ID
 * @param string $page_key Page key for template
 * @param bool $is_set Whether page is assigned
 * @param array $page_data Page data array (title, slug)
 * @param string $parent_key Parent key if child page
 * @return string HTML for action buttons
 */
function render_page_actions($page_id, $page_key, $is_set, $page_data, $parent_key = '') {
    $html = '';
    
    if ($is_set) {
        // Edit button - opens in new tab
        $html .= '<a href="' . esc_url(get_edit_post_link($page_id)) . '" target="_blank" class="button button-small">Edit</a> ';
        
        // View button - opens in new tab
        $html .= '<a href="' . esc_url(get_permalink($page_id)) . '" target="_blank" class="button button-small">View</a> ';
        
        // Apply Template button
        $html .= '<button type="button" class="button button-small sfpf-apply-template" ';
        $html .= 'data-page-id="' . esc_attr($page_id) . '" ';
        $html .= 'data-page-key="' . esc_attr($page_key) . '">';
        $html .= 'Apply Template</button>';
    } else {
        // Create button
        $html .= '<button class="button button-small button-primary sfpf-create-page" ';
        $html .= 'data-page="' . esc_attr($page_key) . '" ';
        $html .= 'data-title="' . esc_attr($page_data['title']) . '" ';
        $html .= 'data-slug="' . esc_attr($page_data['slug']) . '"';
        if ($parent_key) {
            $html .= ' data-parent="' . esc_attr($parent_key) . '"';
        }
        $html .= '>+ Create</button>';
    }
    
    return $html;
}

/**
 * Get front page ID
 * 
 * @return int|false Front page ID or false
 */
function get_front_page_id() {
    if (get_option('show_on_front') === 'page') {
        return (int) get_option('page_on_front');
    }
    return false;
}

/**
 * =============================================================================
 * ACF STRUCTURE DISPLAY
 * =============================================================================
 */

/**
 * Get ACF field structure from snippet file
 * 
 * @param string $snippet_id The snippet ID
 * @return array Field structure array
 */
function get_acf_field_structure($snippet_id) {
    $structures = [
        'sfpf_enable_book_acf' => [
            'group_key' => 'group_sfpf_book',
            'group_title' => 'Book Details',
            'location' => 'post_type == book',
            'tabs' => [
                'Schema' => [
                    ['label' => 'Schema Markup', 'name' => 'schema_markup', 'key' => 'field_sfpf_book_schema', 'type' => 'textarea', 'readonly' => true],
                    ['label' => 'Schema Preview', 'name' => 'schema_preview', 'key' => 'field_sfpf_book_schema_preview', 'type' => 'message'],
                ],
                'Basic Info' => [
                    ['label' => 'Sub-Title', 'name' => 'subtitle', 'key' => 'field_sfpf_book_subtitle', 'type' => 'text'],
                    ['label' => 'Description', 'name' => 'description', 'key' => 'field_sfpf_book_description', 'type' => 'wysiwyg'],
                    ['label' => 'Author Bio', 'name' => 'author_bio', 'key' => 'field_sfpf_book_author_bio', 'type' => 'wysiwyg'],
                    ['label' => 'Featured', 'name' => 'featured', 'key' => 'field_sfpf_book_featured', 'type' => 'true_false'],
                ],
                'Media' => [
                    ['label' => 'Cover Image', 'name' => 'cover', 'key' => 'field_sfpf_book_cover', 'type' => 'image'],
                    ['label' => 'Featured Content', 'name' => 'featured_content', 'key' => 'field_sfpf_book_featured_content', 'type' => 'wysiwyg'],
                ],
                'URLs' => [
                    ['label' => 'Amazon URL', 'name' => 'amazon_url', 'key' => 'field_sfpf_book_amazon_url', 'type' => 'url'],
                    ['label' => 'Audible URL', 'name' => 'audible_url', 'key' => 'field_sfpf_book_audible_url', 'type' => 'url'],
                    ['label' => 'Google Books URL', 'name' => 'google_books_url', 'key' => 'field_sfpf_book_google_books_url', 'type' => 'url'],
                    ['label' => 'GoodReads URL', 'name' => 'goodreads_url', 'key' => 'field_sfpf_book_goodreads_url', 'type' => 'url'],
                    ['label' => 'SoundCloud URL', 'name' => 'soundcloud_url', 'key' => 'field_sfpf_book_soundcloud_url', 'type' => 'url'],
                    ['label' => 'Audio URL', 'name' => 'audio_url', 'key' => 'field_sfpf_book_audio_url', 'type' => 'url'],
                ],
                'Social' => [
                    ['label' => 'Instagram URL', 'name' => 'instagram_url', 'key' => 'field_sfpf_book_instagram_url', 'type' => 'url'],
                    ['label' => 'YouTube URL', 'name' => 'youtube_url', 'key' => 'field_sfpf_book_youtube_url', 'type' => 'url'],
                ],
                'Publishing' => [
                    ['label' => 'Publishing Company', 'name' => 'publishing_company', 'key' => 'field_sfpf_book_publishing_company', 'type' => 'wysiwyg'],
                    ['label' => 'Press', 'name' => 'press', 'key' => 'field_sfpf_book_press', 'type' => 'wysiwyg'],
                    ['label' => 'Additional Resources', 'name' => 'additional_resources', 'key' => 'field_sfpf_book_additional_resources', 'type' => 'wysiwyg'],
                ],
            ],
        ],
        
        'sfpf_enable_organization_acf' => [
            'group_key' => 'group_sfpf_organization',
            'group_title' => 'Organization Details',
            'location' => 'post_type == organization',
            'tabs' => [
                'Schema' => [
                    ['label' => 'Schema Markup', 'name' => 'schema_markup', 'key' => 'field_sfpf_org_schema', 'type' => 'textarea', 'readonly' => true],
                ],
                'Basic Info' => [
                    ['label' => 'Logo', 'name' => 'logo', 'key' => 'field_sfpf_org_logo', 'type' => 'image'],
                    ['label' => 'Description', 'name' => 'description', 'key' => 'field_sfpf_org_description', 'type' => 'wysiwyg'],
                    ['label' => 'Website', 'name' => 'website', 'key' => 'field_sfpf_org_website', 'type' => 'url'],
                    ['label' => 'Founding Date', 'name' => 'founding_date', 'key' => 'field_sfpf_org_founding_date', 'type' => 'date_picker'],
                    ['label' => 'Founder', 'name' => 'founder', 'key' => 'field_sfpf_org_founder', 'type' => 'user'],
                    ['label' => 'Number of Employees', 'name' => 'employees', 'key' => 'field_sfpf_org_employees', 'type' => 'number'],
                    ['label' => 'NAICS Code', 'name' => 'naics', 'key' => 'field_sfpf_org_naics', 'type' => 'text'],
                ],
                'Social URLs' => [
                    ['label' => 'Facebook URL', 'name' => 'facebook_url', 'key' => 'field_sfpf_org_facebook', 'type' => 'url'],
                    ['label' => 'Twitter URL', 'name' => 'twitter_url', 'key' => 'field_sfpf_org_twitter', 'type' => 'url'],
                    ['label' => 'LinkedIn URL', 'name' => 'linkedin_url', 'key' => 'field_sfpf_org_linkedin', 'type' => 'url'],
                    ['label' => 'Instagram URL', 'name' => 'instagram_url', 'key' => 'field_sfpf_org_instagram', 'type' => 'url'],
                    ['label' => 'YouTube URL', 'name' => 'youtube_url', 'key' => 'field_sfpf_org_youtube', 'type' => 'url'],
                ],
            ],
        ],
        
        'sfpf_enable_homepage_acf' => [
            'group_key' => 'group_sfpf_homepage',
            'group_title' => 'Homepage Schema',
            'location' => 'page_type == front_page',
            'tabs' => [
                'Schema Settings' => [
                    ['label' => 'Schema Type', 'name' => 'schema_type', 'key' => 'field_sfpf_hp_schema_type', 'type' => 'select', 'choices' => ['profile_page' => 'ProfilePage', 'about_page' => 'AboutPage', 'web_page' => 'WebPage']],
                    ['label' => 'Schema Markup', 'name' => 'schema', 'key' => 'field_sfpf_hp_schema', 'type' => 'textarea', 'readonly' => true],
                ],
            ],
        ],
        
        'sfpf_enable_user_schema_acf' => [
            'group_key' => 'group_sfpf_user_schema_structures',
            'group_title' => 'Schema.org Structured Data',
            'location' => 'user_form == all',
            'tabs' => [
                'Entity & Content' => [
                    ['label' => 'Entity Type', 'name' => 'entity_type', 'key' => 'field_sfpf_entity_type', 'type' => 'button_group'],
                    ['label' => 'Biography', 'name' => 'biography', 'key' => 'field_sfpf_biography', 'type' => 'wysiwyg'],
                    ['label' => 'Biography (Short)', 'name' => 'biography_short', 'key' => 'field_sfpf_biography_short', 'type' => 'wysiwyg'],
                    ['label' => 'Mission Statement', 'name' => 'mission_statement', 'key' => 'field_sfpf_mission_statement', 'type' => 'wysiwyg'],
                ],
                'Person Fields' => [
                    ['label' => 'Professions', 'name' => 'professions', 'key' => 'field_sfpf_professions_repeater', 'type' => 'repeater'],
                    ['label' => 'Education History', 'name' => 'education', 'key' => 'field_sfpf_education_repeater', 'type' => 'repeater'],
                ],
                'Organization Fields' => [
                    ['label' => 'Inception Date', 'name' => 'inception_date', 'key' => 'field_sfpf_inception_date', 'type' => 'text'],
                    ['label' => 'Headquarters', 'name' => 'headquarters', 'key' => 'field_sfpf_headquarters_group', 'type' => 'group'],
                ],
                'Shared' => [
                    ['label' => 'SameAs URLs', 'name' => 'sameas', 'key' => 'field_sfpf_sameas', 'type' => 'textarea'],
                ],
            ],
        ],
    ];
    
    return $structures[$snippet_id] ?? [];
}

/**
 * Render ACF field structure as HTML
 * 
 * @param string $snippet_id The snippet ID
 * @return string HTML output
 */
function render_acf_structure_html($snippet_id) {
    $structure = get_acf_field_structure($snippet_id);
    
    if (empty($structure)) {
        return '<p style="color:#666;">No structure available.</p>';
    }
    
    $html = '<div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:6px;padding:15px;font-size:12px;">';
    $html .= '<div style="margin-bottom:10px;"><strong>Group Key:</strong> <code>' . esc_html($structure['group_key']) . '</code></div>';
    $html .= '<div style="margin-bottom:10px;"><strong>Location:</strong> <code>' . esc_html($structure['location']) . '</code></div>';
    $html .= '<div style="margin-bottom:15px;"><strong>Title:</strong> ' . esc_html($structure['group_title']) . '</div>';
    
    foreach ($structure['tabs'] as $tab_name => $fields) {
        $html .= '<div style="margin-bottom:10px;">';
        $html .= '<div style="background:#e5e7eb;padding:5px 10px;border-radius:4px;font-weight:600;margin-bottom:8px;">📁 ' . esc_html($tab_name) . '</div>';
        $html .= '<table style="width:100%;border-collapse:collapse;margin-left:15px;">';
        
        foreach ($fields as $field) {
            $html .= '<tr style="border-bottom:1px solid #e5e7eb;">';
            $html .= '<td style="padding:4px 8px;width:25%;"><strong>' . esc_html($field['label']) . '</strong></td>';
            $html .= '<td style="padding:4px 8px;width:25%;"><code style="background:#dbeafe;padding:2px 5px;border-radius:3px;">' . esc_html($field['name']) . '</code></td>';
            $html .= '<td style="padding:4px 8px;width:25%;color:#666;">' . esc_html($field['type']) . '</td>';
            $html .= '<td style="padding:4px 8px;width:25%;"><code style="font-size:10px;color:#9ca3af;">' . esc_html($field['key']) . '</code></td>';
            $html .= '</tr>';
        }
        
        $html .= '</table>';
        $html .= '</div>';
    }
    
    $html .= '</div>';
    
    return $html;
}

/**
 * Get CPT registration PHP code
 * 
 * @param string $snippet_id The snippet ID
 * @return string PHP code
 */
function get_cpt_php_code($snippet_id) {
    $codes = [
        'sfpf_enable_book_cpt' => "register_post_type('book', [
    'labels' => [
        'name'          => 'Books',
        'singular_name' => 'Book',
        'menu_name'     => 'Books',
        'add_new_item'  => 'Add New Book',
        'edit_item'     => 'Edit Book',
        'view_item'     => 'View Book',
    ],
    'public'        => true,
    'show_in_rest'  => true,
    'menu_icon'     => 'dashicons-book-alt',
    'supports'      => ['title', 'author', 'editor', 'thumbnail', 'custom-fields'],
    'has_archive'   => 'books',
    'rewrite'       => ['slug' => 'book', 'with_front' => false],
]);",

        'sfpf_enable_organization_cpt' => "register_post_type('organization', [
    'labels' => [
        'name'          => 'Organizations',
        'singular_name' => 'Organization',
        'menu_name'     => 'Organizations',
        'add_new_item'  => 'Add New Organization',
        'edit_item'     => 'Edit Organization',
        'view_item'     => 'View Organization',
    ],
    'public'        => true,
    'show_in_rest'  => true,
    'menu_icon'     => 'dashicons-building',
    'supports'      => ['title', 'author', 'editor', 'thumbnail', 'custom-fields'],
    'has_archive'   => 'organizations',
    'rewrite'       => ['slug' => 'organization', 'with_front' => false],
]);",
    ];
    
    return $codes[$snippet_id] ?? '';
}

/**
 * =============================================================================
 * STATUS BADGES
 * =============================================================================
 */

/**
 * Render a status badge
 * 
 * @param bool $status True for success, false for error
 * @param string $text Badge text
 * @return string HTML badge
 */
function render_status_badge($status, $text = '') {
    if ($status) {
        $class = 'sfpf-badge sfpf-badge-success';
        $bg = '#dcfce7';
        $color = '#166534';
        $icon = '<span class="dashicons dashicons-yes-alt" style="font-size:14px;vertical-align:middle;margin-right:3px;"></span>';
        $default_text = 'Enabled';
    } else {
        $class = 'sfpf-badge sfpf-badge-error';
        $bg = '#fee2e2';
        $color = '#991b1b';
        $icon = '<span class="dashicons dashicons-no-alt" style="font-size:14px;vertical-align:middle;margin-right:3px;"></span>';
        $default_text = 'Disabled';
    }
    
    $display_text = $text ?: $default_text;
    
    return sprintf(
        '<span class="%s" style="display:inline-block;background:%s;color:%s;padding:4px 10px;border-radius:4px;font-size:12px;font-weight:500;">%s%s</span>',
        $class,
        $bg,
        $color,
        $icon,
        esc_html($display_text)
    );
}

/**
 * Render an external check badge (for checks from other plugins)
 * 
 * @return string HTML badge
 */
function render_external_badge() {
    return '<span style="display:inline-block;background:#dbeafe;color:#1e40af;padding:2px 8px;border-radius:4px;font-size:10px;font-weight:600;margin-left:8px;text-transform:uppercase;">HWS</span>';
}

/**
 * =============================================================================
 * SNIPPET HELPERS
 * =============================================================================
 */

/**
 * Check if a snippet is enabled
 * 
 * @param string $snippet_id The snippet ID to check
 * @return bool True if enabled
 */
function is_snippet_enabled($snippet_id) {
    return (bool) get_option($snippet_id, false);
}

/**
 * Sanitize schema by removing empty values
 * 
 * @param array $schema Schema array
 * @return array Sanitized schema
 */
function sanitize_schema($schema) {
    if (!is_array($schema)) {
        return [];
    }
    
    $result = [];
    foreach ($schema as $key => $value) {
        if (is_array($value)) {
            $value = sanitize_schema($value);
            if (!empty($value)) {
                $result[$key] = $value;
            }
        } elseif ($value !== null && $value !== '') {
            $result[$key] = $value;
        }
    }
    
    return $result;
}

/**
 * =============================================================================
 * PRIMARY ORGANIZATION/BOOK HELPERS
 * =============================================================================
 */

/**
 * Get primary organization
 * 
 * @return WP_Post|null Primary organization post or null
 */
function get_primary_organization() {
    // Check for option setting first
    $primary_id = get_option('sfpf_primary_organization', 0);
    if ($primary_id) {
        $post = get_post($primary_id);
        if ($post && $post->post_status === 'publish') {
            return $post;
        }
    }
    
    // Fallback to first organization
    $args = [
        'post_type' => 'organization',
        'posts_per_page' => 1,
        'post_status' => 'publish',
        'orderby' => 'date',
        'order' => 'ASC',
    ];
    
    $posts = get_posts($args);
    return !empty($posts) ? $posts[0] : null;
}

/**
 * Get primary book
 * 
 * @return WP_Post|null Primary book post or null
 */
function get_primary_book() {
    // Check for option setting first
    $primary_id = get_option('sfpf_primary_book', 0);
    if ($primary_id) {
        $post = get_post($primary_id);
        if ($post && $post->post_status === 'publish') {
            return $post;
        }
    }
    
    // Fallback to first book
    $args = [
        'post_type' => 'book',
        'posts_per_page' => 1,
        'post_status' => 'publish',
        'orderby' => 'date',
        'order' => 'ASC',
    ];
    
    $posts = get_posts($args);
    return !empty($posts) ? $posts[0] : null;
}

/**
 * Get primary organization info array
 * 
 * @return array|null Organization data or null
 */
function get_primary_organization_info() {
    $org = get_primary_organization();
    if (!$org) {
        return null;
    }
    
    $logo = get_field('image_cropped', $org->ID);
    $hq = get_field('headquarters', $org->ID);
    
    return [
        'ID' => $org->ID,
        'title' => $org->post_title,
        'url' => get_field('url', $org->ID),
        'logo_url' => $logo['url'] ?? '',
        'short_summary' => get_field('short_summary', $org->ID),
        'headquarters_location' => $hq['location'] ?? '',
        'headquarters_wikipedia' => $hq['wikipedia_url'] ?? '',
        'founding_date' => get_field('founding_date', $org->ID),
        'edit_url' => get_edit_post_link($org->ID),
        'view_url' => get_permalink($org->ID),
    ];
}

/**
 * =============================================================================
 * FIELD CHECKLIST UTILITIES
 * =============================================================================
 */

/**
 * Check if a field has a non-empty value.
 * Works with ACF user fields, post fields, and WP user meta.
 * 
 * @param string     $field_name  Field name or dot-path (e.g., 'urls.facebook')
 * @param string|int $context     ACF context ('user_1', post ID, 'option')
 * @param string     $type        'acf', 'wp_user', or 'post_meta'
 * @return bool
 */
function is_field_populated($field_name, $context, $type = 'acf') {
    if ($type === 'wp_user' && is_numeric($context)) {
        return !empty(get_user_meta((int) $context, $field_name, true));
    }
    if ($type === 'post_meta' && is_numeric($context)) {
        return !empty(get_post_meta((int) $context, $field_name, true));
    }
    
    // ACF dot notation for nested group fields
    if (strpos($field_name, '.') !== false) {
        $parts = explode('.', $field_name, 2);
        $group = get_field($parts[0], $context);
        return is_array($group) && !empty($group[$parts[1]]);
    }
    
    $val = get_field($field_name, $context);
    if (is_array($val)) {
        return !empty(array_filter($val));
    }
    return !empty($val);
}

/**
 * Run a checklist of fields and return results.
 * 
 * @param array $checks [['label', 'field', 'context', 'type', 'shortcode']]
 * @return array ['passed', 'failed', 'items' => [['label', 'shortcode', 'status']]]
 */
function run_field_checklist($checks) {
    $results = ['passed' => 0, 'failed' => 0, 'items' => []];
    foreach ($checks as $c) {
        $ok = is_field_populated($c['field'], $c['context'], $c['type'] ?? 'acf');
        $results['items'][] = [
            'label'     => $c['label'],
            'shortcode' => $c['shortcode'] ?? '',
            'status'    => $ok,
        ];
        $ok ? $results['passed']++ : $results['failed']++;
    }
    return $results;
}

/**
 * Render a field checklist as HTML.
 * 
 * @param array  $results Output from run_field_checklist()
 * @param string $title   Checklist heading
 * @return string HTML
 */
function render_field_checklist($results, $title = 'Content Checklist') {
    $total = $results['passed'] + $results['failed'];
    $pct = $total > 0 ? round(($results['passed'] / $total) * 100) : 0;
    $bar_color = $pct === 100 ? '#dc2626' : '#dc2626';
    
    $html = '<div style="margin-top:15px;padding:15px;background:#f9fafb;border-radius:8px;border:1px solid #e5e7eb;">';
    $html .= '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">';
    $html .= '<strong style="font-size:13px;color:#374151;">' . esc_html($title) . '</strong>';
    $html .= '<span style="font-size:12px;color:#9ca3af;">' . $results['passed'] . '/' . $total . ' complete</span>';
    $html .= '</div>';
    
    // Progress bar
    $html .= '<div style="height:4px;background:#e5e7eb;border-radius:2px;margin-bottom:12px;overflow:hidden;">';
    $html .= '<div style="height:100%;width:' . $pct . '%;background:' . $bar_color . ';border-radius:2px;"></div>';
    $html .= '</div>';
    
    // Items — failures first
    $sorted = $results['items'];
    usort($sorted, function($a, $b) { return (int)$a['status'] - (int)$b['status']; });
    
    foreach ($sorted as $item) {
        $icon  = $item['status'] ? '✓' : '—';
        $color = $item['status'] ? '#6b7280' : '#9ca3af';
        $label_style = $item['status'] ? '' : 'font-style:italic;';
        $sc = !empty($item['shortcode']) 
            ? ' <code class="sfpf-copy-sc" style="font-size:10px;background:#f3f4f6;padding:1px 4px;border-radius:2px;cursor:pointer;color:#6b7280;" title="Click to copy">' . esc_html($item['shortcode']) . '</code>' 
            : '';
        
        $html .= '<div style="display:flex;align-items:center;gap:8px;padding:4px 0;">';
        $html .= '<span style="font-size:12px;color:' . $color . ';width:14px;text-align:center;">' . $icon . '</span>';
        $html .= '<span style="font-size:12px;color:' . $color . ';' . $label_style . '">' . esc_html($item['label']) . $sc . '</span>';
        $html .= '</div>';
    }
    
    $html .= '</div>';
    return $html;
}
