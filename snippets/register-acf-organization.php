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
function organization_acf_field_group(): array {
    return [
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
                'key' => 'field_sfpf_org_knowledge_graph_id',
                'label' => 'Google Knowledge Graph ID',
                'name' => 'knowledge_graph_id',
                'type' => 'text',
                'instructions' => 'Enter the KGMID (e.g., <code>/g/11gyz2y3lp</code>). If you paste the full Google URL, the ID will be extracted automatically.<br>
<code>[organization field="knowledge_graph_id"]</code> — Raw KGMID<br>
<span class="sfpf-kgid-org-link">Enter a KGMID above to see the full Knowledge Panel URL.</span>',
                'required' => 0,
                'placeholder' => '/g/11gyz2y3lp',
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
            // DETAILS
            // ═══════════════════════════════════════════════════════════
            [
                'key' => 'field_sfpf_org_header_details',
                'label' => '🏢 Details',
                'name' => '',
                'type' => 'accordion',
                'open' => 1,
                'multi_expand' => 1,
                'endpoint' => 0,
            ],
            [
                'key' => 'field_sfpf_org_legal_name',
                'label' => 'Legal Name',
                'name' => 'legal_name',
                'type' => 'text',
                'instructions' => 'Registered legal name if different from display name.<br><code>[organization field="legal_name"]</code>',
                'required' => 0,
                'placeholder' => 'Company Name, Inc.',
            ],
            [
                'key' => 'field_sfpf_org_email',
                'label' => 'Email',
                'name' => 'email',
                'type' => 'email',
                'instructions' => 'Primary contact email.<br><code>[organization field="email"]</code>',
                'required' => 0,
                'placeholder' => 'info@company.com',
                'wrapper' => ['width' => '50'],
            ],
            [
                'key' => 'field_sfpf_org_telephone',
                'label' => 'Telephone',
                'name' => 'telephone',
                'type' => 'text',
                'instructions' => 'Primary phone number in international format.<br><code>[organization field="telephone"]</code>',
                'required' => 0,
                'placeholder' => '+1-555-123-4567',
                'wrapper' => ['width' => '50'],
            ],
            [
                'key' => 'field_sfpf_org_naics_code',
                'label' => 'NAICS Code',
                'name' => 'naics_code',
                'type' => 'text',
                'instructions' => 'North American Industry Classification System code.<br><code>[organization field="naics_code"]</code>',
                'required' => 0,
                'placeholder' => '541512',
                'wrapper' => ['width' => '33'],
            ],
            [
                'key' => 'field_sfpf_org_number_of_employees',
                'label' => 'Number of Employees',
                'name' => 'number_of_employees',
                'type' => 'text',
                'instructions' => 'Approximate number of employees.<br><code>[organization field="number_of_employees"]</code>',
                'required' => 0,
                'placeholder' => '50',
                'wrapper' => ['width' => '33'],
            ],
            [
                'key' => 'field_sfpf_org_awards',
                'label' => 'Awards',
                'name' => 'awards',
                'type' => 'text',
                'instructions' => 'Awards or recognitions received.<br><code>[organization field="awards"]</code>',
                'required' => 0,
                'wrapper' => ['width' => '33'],
            ],
            [
                'key' => 'field_sfpf_org_brands',
                'label' => 'Brands',
                'name' => 'brands',
                'type' => 'text',
                'instructions' => 'Brands owned (comma-separated).<br><code>[organization field="brands"]</code>',
                'required' => 0,
            ],
            [
                'key' => 'field_sfpf_org_seeks',
                'label' => 'Seeks',
                'name' => 'seeks',
                'type' => 'text',
                'instructions' => 'What the organization is looking for (hiring, partnerships, etc).<br><code>[organization field="seeks"]</code>',
                'required' => 0,
            ],

            // ═══════════════════════════════════════════════════════════
            // ADDRESS
            // ═══════════════════════════════════════════════════════════
            [
                'key' => 'field_sfpf_org_header_address',
                'label' => '📍 Address',
                'name' => '',
                'type' => 'accordion',
                'open' => 1,
                'multi_expand' => 1,
                'endpoint' => 0,
            ],
            [
                'key' => 'field_sfpf_org_address',
                'label' => 'Address',
                'name' => 'address',
                'type' => 'group',
                'instructions' => 'Structured postal address for schema.org. If empty, falls back to HQ location above.',
                'required' => 0,
                'layout' => 'block',
                'sub_fields' => [
                    [
                        'key' => 'field_sfpf_org_addr_street',
                        'label' => 'Street Address',
                        'name' => 'street_address',
                        'type' => 'text',
                        'instructions' => '<code>[organization field="address_street"]</code>',
                        'placeholder' => '8 The Green, Suite A',
                        'wrapper' => ['width' => '100'],
                    ],
                    [
                        'key' => 'field_sfpf_org_addr_locality',
                        'label' => 'City',
                        'name' => 'address_locality',
                        'type' => 'text',
                        'instructions' => '<code>[organization field="address_city"]</code>',
                        'placeholder' => 'Dover',
                        'wrapper' => ['width' => '34'],
                    ],
                    [
                        'key' => 'field_sfpf_org_addr_region',
                        'label' => 'State / Region',
                        'name' => 'address_region',
                        'type' => 'text',
                        'instructions' => '<code>[organization field="address_region"]</code>',
                        'placeholder' => 'DE',
                        'wrapper' => ['width' => '22'],
                    ],
                    [
                        'key' => 'field_sfpf_org_addr_postal',
                        'label' => 'Postal Code',
                        'name' => 'postal_code',
                        'type' => 'text',
                        'instructions' => '<code>[organization field="address_postal"]</code>',
                        'placeholder' => '19901',
                        'wrapper' => ['width' => '22'],
                    ],
                    [
                        'key' => 'field_sfpf_org_addr_country',
                        'label' => 'Country',
                        'name' => 'address_country',
                        'type' => 'text',
                        'instructions' => '<code>[organization field="address_country"]</code>',
                        'placeholder' => 'US',
                        'wrapper' => ['width' => '22'],
                    ],
                ],
            ],

            // ═══════════════════════════════════════════════════════════
            // CONTACT POINT
            // ═══════════════════════════════════════════════════════════
            [
                'key' => 'field_sfpf_org_header_contact',
                'label' => '📞 Contact Point',
                'name' => '',
                'type' => 'accordion',
                'open' => 1,
                'multi_expand' => 1,
                'endpoint' => 0,
            ],
            [
                'key' => 'field_sfpf_org_contact_point',
                'label' => 'Contact Point',
                'name' => 'contact_point',
                'type' => 'group',
                'instructions' => 'Structured contact information for schema.org ContactPoint.',
                'required' => 0,
                'layout' => 'block',
                'sub_fields' => [
                    [
                        'key' => 'field_sfpf_org_cp_type',
                        'label' => 'Contact Type',
                        'name' => 'contact_type',
                        'type' => 'text',
                        'instructions' => 'E.g. "Help", "Sales", "Support"',
                        'placeholder' => 'Help',
                        'wrapper' => ['width' => '33'],
                    ],
                    [
                        'key' => 'field_sfpf_org_cp_email',
                        'label' => 'Email',
                        'name' => 'email',
                        'type' => 'email',
                        'placeholder' => 'support@company.com',
                        'wrapper' => ['width' => '33'],
                    ],
                    [
                        'key' => 'field_sfpf_org_cp_telephone',
                        'label' => 'Telephone',
                        'name' => 'telephone',
                        'type' => 'text',
                        'placeholder' => '+14152129449',
                        'wrapper' => ['width' => '34'],
                    ],
                    [
                        'key' => 'field_sfpf_org_cp_product',
                        'label' => 'Product Supported',
                        'name' => 'product_supported',
                        'type' => 'text',
                        'placeholder' => 'Support',
                        'wrapper' => ['width' => '33'],
                    ],
                    [
                        'key' => 'field_sfpf_org_cp_hours',
                        'label' => 'Hours Available',
                        'name' => 'hours_available',
                        'type' => 'text',
                        'instructions' => 'E.g. "Mo-Fri 08:00-20:00"',
                        'placeholder' => 'Mo-Fri 08:00-20:00',
                        'wrapper' => ['width' => '33'],
                    ],
                    [
                        'key' => 'field_sfpf_org_cp_url',
                        'label' => 'Contact URL',
                        'name' => 'url',
                        'type' => 'url',
                        'placeholder' => 'https://company.com/contact',
                        'wrapper' => ['width' => '34'],
                    ],
                ],
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
                'instructions' => 'Square or cropped logo for listings.<br><code>[organization field="logo"]</code>. The WordPress featured image remains separate and is available through <code>[organization field="featured_image_url"]</code>.',
                'required' => 0,
                'return_format' => 'array',
                'library' => 'all',
                'preview_size' => 'thumbnail',
            ],

            [
                'key' => 'field_sfpf_org_gallery',
                'label' => 'Gallery',
                'name' => 'gallery',
                'type' => 'gallery',
                'instructions' => 'Public, indexable image gallery imported from the Notion <code>Gallery</code> Google Drive URL.<br><code>[organization field="gallery"]</code> - Pretty gallery<br><code>[organization field="gallery" format="json"]</code> - Full image data',
                'required' => 0,
                'return_format' => 'array',
                'library' => 'all',
                'min' => 0,
                'max' => 60,
                'mime_types' => 'jpg,jpeg,png,webp',
                'insert' => 'append',
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
    ];
}

/**
 * Build Organization schema on save
 */
add_action('acf/save_post', __NAMESPACE__ . '\\build_organization_schema_on_save', 20);
function build_organization_schema_on_save($post_id) {
    if (get_post_type($post_id) !== 'organization') return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

    $schema = build_organization_schema($post_id);
    if (!empty($schema)) {
        update_field('schema_markup', schema_json($schema), $post_id);
    }
}
