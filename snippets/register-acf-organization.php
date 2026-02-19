<?php
namespace sfpf_person_website;

/**
 * Organization ACF Fields Registration
 * 
 * Registers Advanced Custom Fields for the Organization post type.
 * 
 * @package sfpf_person_website
 * @since 1.0.0
 */

defined('ABSPATH') || exit;

/**
 * Register the Organization ACF field group
 */
function register_organization_acf_fields() {
    
    if (!function_exists('acf_add_local_field_group')) {
        return;
    }
    
    acf_add_local_field_group([
        'key' => 'group_sfpf_organization',
        'title' => 'Organization Details',
        'fields' => [
            
            // ═══════════════════════════════════════════════════════════
            // SCHEMA
            // ═══════════════════════════════════════════════════════════
            [
                'key' => 'field_sfpf_org_header_schema',
                'label' => '📋 Schema',
                'name' => '',
                'type' => 'accordion',
                'open' => 1,
                'multi_expand' => 1,
                'endpoint' => 0,
            ],
            [
                'key' => 'field_sfpf_org_schema',
                'label' => 'Schema Markup',
                'name' => 'schema_markup',
                'type' => 'textarea',
                'instructions' => 'Generated JSON-LD schema markup for this organization. Auto-generated on save.',
                'required' => 0,
                'readonly' => 1,
                'rows' => 10,
            ],
            
            // ═══════════════════════════════════════════════════════════
            // BASIC INFO
            // ═══════════════════════════════════════════════════════════
            [
                'key' => 'field_sfpf_org_header_basic',
                'label' => '📝 Basic Info',
                'name' => '',
                'type' => 'accordion',
                'open' => 1,
                'multi_expand' => 1,
                'endpoint' => 0,
            ],
            [
                'key' => 'field_sfpf_org_subtitle',
                'label' => 'Sub-Title',
                'name' => 'sub_title',
                'type' => 'text',
                'instructions' => 'Tagline or subtitle for the organization.<br><code>[organization field="sub_title"]</code>',
                'required' => 0,
            ],
            [
                'key' => 'field_sfpf_org_short_summary',
                'label' => 'Short Summary',
                'name' => 'short_summary',
                'type' => 'wysiwyg',
                'instructions' => 'Brief summary of the organization (2-3 sentences).<br><code>[organization field="short_summary"]</code>',
                'required' => 0,
                'tabs' => 'all',
                'toolbar' => 'basic',
                'media_upload' => 0,
                'delay' => 1,
            ],
            [
                'key' => 'field_sfpf_org_mission_statement',
                'label' => 'Mission Statement',
                'name' => 'mission_statement',
                'type' => 'wysiwyg',
                'instructions' => 'The organization\'s mission statement.<br><code>[organization field="mission_statement"]</code>',
                'required' => 0,
                'tabs' => 'all',
                'toolbar' => 'full',
                'media_upload' => 0,
                'delay' => 1,
            ],
            [
                'key' => 'field_sfpf_org_company_info',
                'label' => 'Company Info',
                'name' => 'company_info',
                'type' => 'wysiwyg',
                'instructions' => 'Full company description and information.<br><code>[organization field="company_info"]</code>',
                'required' => 0,
                'tabs' => 'all',
                'toolbar' => 'full',
                'media_upload' => 1,
                'delay' => 1,
            ],
            [
                'key' => 'field_sfpf_org_alternate_names',
                'label' => 'Alternate Names',
                'name' => 'alternate_names',
                'type' => 'textarea',
                'instructions' => 'Other names the organization is known by (one per line, for schema.org alternateName).<br><code>[organization field="alternate_names"]</code>',
                'required' => 0,
                'rows' => 3,
            ],
            
            // ═══════════════════════════════════════════════════════════
            // FOUNDING
            // ═══════════════════════════════════════════════════════════
            [
                'key' => 'field_sfpf_org_header_founding',
                'label' => '🏛️ Founding',
                'name' => '',
                'type' => 'accordion',
                'open' => 1,
                'multi_expand' => 1,
                'endpoint' => 0,
            ],
            [
                'key' => 'field_sfpf_org_founding_date',
                'label' => 'Founding Date',
                'name' => 'founding_date',
                'type' => 'text',
                'instructions' => 'Date the organization was founded. Example: September 1, 2021<br><code>[organization field="founding_date"]</code>',
                'required' => 0,
                'placeholder' => 'September 1, 2021',
            ],
            [
                'key' => 'field_sfpf_org_headquarters',
                'label' => 'Headquarters',
                'name' => 'headquarters',
                'type' => 'group',
                'instructions' => 'Primary headquarters location.',
                'required' => 0,
                'layout' => 'block',
                'sub_fields' => [
                    [
                        'key' => 'field_sfpf_org_hq_location',
                        'label' => 'Location',
                        'name' => 'location',
                        'type' => 'text',
                        'instructions' => 'City, State/Country. Example: Dover, DE<br><code>[organization field="headquarters_location"]</code>',
                        'required' => 0,
                        'placeholder' => 'Dover, DE',
                    ],
                    [
                        'key' => 'field_sfpf_org_hq_wikipedia',
                        'label' => 'Wikipedia URL',
                        'name' => 'wikipedia_url',
                        'type' => 'url',
                        'instructions' => 'Wikipedia URL for the headquarters location.<br><code>[organization field="headquarters_wikipedia"]</code>',
                        'required' => 0,
                        'placeholder' => 'https://en.wikipedia.org/wiki/Dover,_Delaware',
                    ],
                ],
            ],
            [
                'key' => 'field_sfpf_org_secondary_location',
                'label' => 'Secondary Location',
                'name' => 'secondary_location',
                'type' => 'text',
                'instructions' => 'Additional office location.<br><code>[organization field="secondary_location"]</code>',
                'required' => 0,
            ],
            
            // ═══════════════════════════════════════════════════════════
            // URLs
            // ═══════════════════════════════════════════════════════════
            [
                'key' => 'field_sfpf_org_header_urls',
                'label' => '🔗 URLs',
                'name' => '',
                'type' => 'accordion',
                'open' => 1,
                'multi_expand' => 1,
                'endpoint' => 0,
            ],
            [
                'key' => 'field_sfpf_org_url',
                'label' => 'Website URL',
                'name' => 'url',
                'type' => 'url',
                'instructions' => 'Organization\'s main website.<br><code>[organization field="url"]</code> or <code>[organization field="url" link="true" target="_blank" pretty="true"]</code>',
                'required' => 0,
            ],
            [
                'key' => 'field_sfpf_org_url_facebook',
                'label' => 'Facebook',
                'name' => 'url_facebook',
                'type' => 'url',
                'instructions' => 'Shortcode: <code>[organization field="url_facebook"]</code>',
                'required' => 0,
                'placeholder' => 'https://facebook.com/...',
                'wrapper' => ['width' => '50'],
            ],
            [
                'key' => 'field_sfpf_org_url_instagram',
                'label' => 'Instagram',
                'name' => 'url_instagram',
                'type' => 'url',
                'instructions' => 'Shortcode: <code>[organization field="url_instagram"]</code>',
                'required' => 0,
                'placeholder' => 'https://instagram.com/...',
                'wrapper' => ['width' => '50'],
            ],
            [
                'key' => 'field_sfpf_org_url_linkedin',
                'label' => 'LinkedIn',
                'name' => 'url_linkedin',
                'type' => 'url',
                'instructions' => 'Shortcode: <code>[organization field="url_linkedin"]</code>',
                'required' => 0,
                'placeholder' => 'https://linkedin.com/company/...',
                'wrapper' => ['width' => '50'],
            ],
            [
                'key' => 'field_sfpf_org_url_x',
                'label' => 'X (Twitter)',
                'name' => 'url_x',
                'type' => 'url',
                'instructions' => 'Shortcode: <code>[organization field="url_x"]</code>',
                'required' => 0,
                'placeholder' => 'https://x.com/...',
                'wrapper' => ['width' => '50'],
            ],
            [
                'key' => 'field_sfpf_org_url_youtube',
                'label' => 'YouTube',
                'name' => 'url_youtube',
                'type' => 'url',
                'instructions' => 'Shortcode: <code>[organization field="url_youtube"]</code>',
                'required' => 0,
                'placeholder' => 'https://youtube.com/@...',
                'wrapper' => ['width' => '50'],
            ],
            [
                'key' => 'field_sfpf_org_url_tiktok',
                'label' => 'TikTok',
                'name' => 'url_tiktok',
                'type' => 'url',
                'instructions' => 'Shortcode: <code>[organization field="url_tiktok"]</code>',
                'required' => 0,
                'placeholder' => 'https://tiktok.com/@...',
                'wrapper' => ['width' => '50'],
            ],
            [
                'key' => 'field_sfpf_org_url_github',
                'label' => 'GitHub',
                'name' => 'url_github',
                'type' => 'url',
                'instructions' => 'Shortcode: <code>[organization field="url_github"]</code>',
                'required' => 0,
                'placeholder' => 'https://github.com/...',
                'wrapper' => ['width' => '50'],
            ],
            [
                'key' => 'field_sfpf_org_url_wikipedia',
                'label' => 'Wikipedia',
                'name' => 'url_wikipedia',
                'type' => 'url',
                'instructions' => 'Shortcode: <code>[organization field="url_wikipedia"]</code>',
                'required' => 0,
                'placeholder' => 'https://en.wikipedia.org/wiki/...',
                'wrapper' => ['width' => '50'],
            ],
            [
                'key' => 'field_sfpf_org_url_crunchbase',
                'label' => 'Crunchbase',
                'name' => 'url_crunchbase',
                'type' => 'url',
                'instructions' => 'Shortcode: <code>[organization field="url_crunchbase"]</code>',
                'required' => 0,
                'placeholder' => 'https://crunchbase.com/organization/...',
                'wrapper' => ['width' => '50'],
            ],
            [
                'key' => 'field_sfpf_org_social_urls',
                'label' => 'Social URLs',
                'name' => 'social_urls',
                'type' => 'textarea',
                'instructions' => 'Social media profile URLs (one per line).<br><code>[organization field="social_urls"]</code>',
                'required' => 0,
                'rows' => 5,
            ],
            [
                'key' => 'field_sfpf_org_sameas_urls',
                'label' => 'SameAs URLs',
                'name' => 'sameas_urls',
                'type' => 'textarea',
                'instructions' => 'Additional URLs that represent the same organization (for schema.org sameAs). One URL per line.<br><code>[organization field="sameas_urls"]</code>',
                'required' => 0,
                'rows' => 5,
            ],
            
            // ═══════════════════════════════════════════════════════════
            // MEDIA
            // ═══════════════════════════════════════════════════════════
            [
                'key' => 'field_sfpf_org_header_media',
                'label' => '🖼️ Media',
                'name' => '',
                'type' => 'accordion',
                'open' => 1,
                'multi_expand' => 1,
                'endpoint' => 0,
            ],
            [
                'key' => 'field_sfpf_org_image_cropped',
                'label' => 'Logo (Cropped)',
                'name' => 'image_cropped',
                'type' => 'image',
                'instructions' => 'Square or cropped logo for listings.<br><code>[organization field="logo"]</code>',
                'required' => 0,
                'return_format' => 'array',
                'library' => 'all',
                'preview_size' => 'thumbnail',
            ],
            
            // Close accordions
            [
                'key' => 'field_sfpf_org_accordion_end',
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
                    'value' => 'organization',
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
 * Build Organization schema on save
 */
add_action('acf/save_post', __NAMESPACE__ . '\\build_organization_schema_on_save', 20);
function build_organization_schema_on_save($post_id) {
    // Skip if not organization
    if (get_post_type($post_id) !== 'organization') {
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
        '@type' => 'Organization',
        '@id' => $site_url . '#organization-' . $slug,
        'name' => get_the_title($post_id),
    ];
    
    // Legal Name (use title if not set)
    $schema['legalName'] = get_the_title($post_id);
    
    // URL
    $url = get_field('url', $post_id);
    if ($url) {
        $schema['url'] = $url;
    }
    
    // Description
    $short_summary = get_field('short_summary', $post_id);
    if ($short_summary) {
        $schema['description'] = wp_strip_all_tags($short_summary);
    }
    
    // Alternate Names (from textarea, one per line)
    $alt_names_raw = get_field('alternate_names', $post_id);
    if ($alt_names_raw) {
        $names = array_filter(array_map('trim', explode("\n", $alt_names_raw)));
        if (!empty($names)) {
            $schema['alternateName'] = array_values($names);
        }
    }
    
    // Main Entity Of Page
    $schema['mainEntityOfPage'] = [
        get_permalink($post_id),
    ];
    if ($url) {
        $schema['mainEntityOfPage'][] = $url;
    }
    
    // Logo
    $logo = get_field('image_cropped', $post_id);
    if ($logo && isset($logo['url'])) {
        $schema['logo'] = $logo['url'];
        $schema['image'] = [$logo['url']];
    }
    
    // Headquarters / Address
    $hq = get_field('headquarters', $post_id);
    if ($hq && !empty($hq['location'])) {
        $schema['address'] = [
            '@type' => 'PostalAddress',
            'addressLocality' => $hq['location'],
        ];
    }
    
    // Founder (from site owner)
    if ($founder) {
        $schema['founder'] = [
            '@type' => 'Person',
            '@id' => $site_url . '#person-' . sanitize_title($founder['display_name']),
            'name' => $founder['display_name'],
            'url' => $site_url,
        ];
    }
    
    // Founding Date
    $founding_date = get_field('founding_date', $post_id);
    if ($founding_date) {
        $timestamp = strtotime($founding_date);
        if ($timestamp) {
            $schema['foundingDate'] = date('Y-m-d', $timestamp);
        } else {
            $schema['foundingDate'] = $founding_date;
        }
    }
    
    // SameAs URLs (from textarea, one per line, plus individual URL fields)
    $sameas_raw = get_field('sameas_urls', $post_id);
    $social_raw = get_field('social_urls', $post_id);
    $sameas_array = [];
    
    if ($sameas_raw) {
        $sameas_array = array_merge($sameas_array, array_filter(array_map('trim', explode("\n", $sameas_raw))));
    }
    if ($social_raw) {
        $sameas_array = array_merge($sameas_array, array_filter(array_map('trim', explode("\n", $social_raw))));
    }
    
    // Include individual social URL fields
    $social_platforms = ['facebook', 'instagram', 'linkedin', 'x', 'youtube', 'tiktok', 'github', 'wikipedia', 'crunchbase'];
    foreach ($social_platforms as $platform) {
        $platform_url = get_field('url_' . $platform, $post_id);
        if (!empty($platform_url)) {
            $sameas_array[] = $platform_url;
        }
    }
    
    if (!empty($sameas_array)) {
        $schema['sameAs'] = array_values(array_unique($sameas_array));
    }
    
    // Save to ACF field
    update_field('schema_markup', json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), $post_id);
}
