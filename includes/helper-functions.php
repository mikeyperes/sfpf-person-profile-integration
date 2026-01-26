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
            ['shortcode' => '[founder id="url_twitter"]', 'description' => 'Founder Twitter'],
            ['shortcode' => '[founder id="url_youtube"]', 'description' => 'Founder YouTube'],
            ['shortcode' => '[founder id="url_tiktok"]', 'description' => 'Founder TikTok'],
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
        $bg = '#dcfce7';
        $color = '#166534';
        $icon = '✓';
        $default_text = 'Enabled';
    } else {
        $bg = '#fee2e2';
        $color = '#991b1b';
        $icon = '✗';
        $default_text = 'Disabled';
    }
    
    $display_text = $text ?: $default_text;
    
    return sprintf(
        '<span style="display:inline-block;background:%s;color:%s;padding:4px 10px;border-radius:4px;font-size:12px;font-weight:500;">%s %s</span>',
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
