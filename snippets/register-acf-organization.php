<?php
namespace sfpf_person_website;

/**
 * Organization ACF Fields Registration
 * 
 * Registers Advanced Custom Fields for the Organization post type.
 * Includes company info, URLs, founding details, and schema fields.
 * 
 * @package sfpf_person_website
 * @since 1.0.0
 */

defined('ABSPATH') || exit;

/**
 * Enable Organization ACF fields
 * 
 * Called when the snippet is activated.
 */
function enable_organization_acf_fields() {
    if (!function_exists('acf_add_local_field_group')) {
        return;
    }
    
    register_organization_acf_group();
}

/**
 * Register the Organization ACF field group
 */
function register_organization_acf_group() {
    
    acf_add_local_field_group([
        'key' => 'group_sfpf_organization',
        'title' => 'Organization Details',
        'fields' => [
            
            // === Schema Tab ===
            [
                'key' => 'field_sfpf_org_tab_schema',
                'label' => 'Schema',
                'name' => '',
                'type' => 'tab',
                'placement' => 'top',
            ],
            [
                'key' => 'field_sfpf_org_schema',
                'label' => 'Schema Markup',
                'name' => 'schema_markup',
                'type' => 'textarea',
                'instructions' => 'Generated JSON-LD schema markup for this organization. This is auto-generated and should not be manually edited.',
                'required' => 0,
                'readonly' => 1,
                'rows' => 10,
                'wrapper' => ['class' => 'sfpf-schema-field'],
            ],
            [
                'key' => 'field_sfpf_org_schema_preview',
                'label' => 'Schema Preview',
                'name' => 'schema_preview',
                'type' => 'message',
                'message' => '<div id="sfpf-schema-preview-org"></div>',
                'new_lines' => '',
                'esc_html' => 0,
            ],
            
            // === Basic Info Tab ===
            [
                'key' => 'field_sfpf_org_tab_basic',
                'label' => 'Basic Info',
                'name' => '',
                'type' => 'tab',
                'placement' => 'top',
            ],
            [
                'key' => 'field_sfpf_org_subtitle',
                'label' => 'Sub-Title',
                'name' => 'sub-title',
                'type' => 'text',
                'instructions' => 'Tagline or subtitle for the organization.',
                'required' => 0,
            ],
            [
                'key' => 'field_sfpf_org_short_summary',
                'label' => 'Short Summary',
                'name' => 'short_summary',
                'type' => 'wysiwyg',
                'instructions' => 'Brief summary of the organization (2-3 sentences).',
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
                'instructions' => 'The organization\'s mission statement.',
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
                'instructions' => 'Full company description and information.',
                'required' => 0,
                'tabs' => 'all',
                'toolbar' => 'full',
                'media_upload' => 1,
                'delay' => 1,
            ],
            [
                'key' => 'field_sfpf_org_startup_type',
                'label' => 'Organization Type',
                'name' => 'startup_type',
                'type' => 'select',
                'instructions' => 'Type of organization.',
                'required' => 0,
                'choices' => [
                    'company' => 'Company',
                    'publication' => 'Publication',
                    'nonprofit' => 'Non-Profit',
                    'startup' => 'Startup',
                    'agency' => 'Agency',
                ],
                'default_value' => 'company',
                'return_format' => 'value',
                'allow_null' => 1,
            ],
            
            // === Founding Tab ===
            [
                'key' => 'field_sfpf_org_tab_founding',
                'label' => 'Founding',
                'name' => '',
                'type' => 'tab',
                'placement' => 'top',
            ],
            [
                'key' => 'field_sfpf_org_founder',
                'label' => 'Founder',
                'name' => 'founder',
                'type' => 'text',
                'instructions' => 'Name of the founder.',
                'required' => 0,
            ],
            [
                'key' => 'field_sfpf_org_founding_date',
                'label' => 'Founding Date',
                'name' => 'founding_date',
                'type' => 'text',
                'instructions' => 'Date the organization was founded (YYYY-MM-DD or YYYY-MM or YYYY).',
                'required' => 0,
                'placeholder' => '2020-01-15',
            ],
            [
                'key' => 'field_sfpf_org_headquarters',
                'label' => 'Headquarters',
                'name' => 'headquarters',
                'type' => 'wysiwyg',
                'instructions' => 'Primary headquarters location and address.',
                'required' => 0,
                'tabs' => 'all',
                'toolbar' => 'basic',
                'media_upload' => 0,
                'delay' => 1,
            ],
            [
                'key' => 'field_sfpf_org_secondary_location',
                'label' => 'Secondary Location',
                'name' => 'secondary_location',
                'type' => 'wysiwyg',
                'instructions' => 'Additional office locations.',
                'required' => 0,
                'tabs' => 'all',
                'toolbar' => 'basic',
                'media_upload' => 0,
                'delay' => 1,
            ],
            
            // === URLs Tab ===
            [
                'key' => 'field_sfpf_org_tab_urls',
                'label' => 'URLs',
                'name' => '',
                'type' => 'tab',
                'placement' => 'top',
            ],
            [
                'key' => 'field_sfpf_org_url',
                'label' => 'Website URL',
                'name' => 'url',
                'type' => 'url',
                'instructions' => 'Organization\'s main website.',
                'required' => 0,
            ],
            [
                'key' => 'field_sfpf_org_alternate_names',
                'label' => 'Alternate Names',
                'name' => 'alternate_names',
                'type' => 'text',
                'instructions' => 'Other names the organization is known by (comma-separated).',
                'required' => 0,
            ],
            [
                'key' => 'field_sfpf_org_social_urls',
                'label' => 'Social URLs',
                'name' => 'social_urls',
                'type' => 'wysiwyg',
                'instructions' => 'Social media profile URLs (one per line).',
                'required' => 0,
                'tabs' => 'all',
                'toolbar' => 'basic',
                'media_upload' => 0,
                'delay' => 1,
            ],
            [
                'key' => 'field_sfpf_org_sameas_urls',
                'label' => 'SameAs URLs',
                'name' => 'sameas_urls',
                'type' => 'wysiwyg',
                'instructions' => 'Additional URLs that represent the same organization (for schema.org sameAs property). One URL per line.',
                'required' => 0,
                'tabs' => 'all',
                'toolbar' => 'basic',
                'media_upload' => 0,
                'delay' => 1,
            ],
            [
                'key' => 'field_sfpf_org_additional_urls',
                'label' => 'Additional URLs',
                'name' => 'additional_urls',
                'type' => 'wysiwyg',
                'instructions' => 'Any other relevant URLs.',
                'required' => 0,
                'tabs' => 'all',
                'toolbar' => 'basic',
                'media_upload' => 0,
                'delay' => 1,
            ],
            
            // === Media Tab ===
            [
                'key' => 'field_sfpf_org_tab_media',
                'label' => 'Media',
                'name' => '',
                'type' => 'tab',
                'placement' => 'top',
            ],
            [
                'key' => 'field_sfpf_org_image_cropped',
                'label' => 'Logo (Cropped)',
                'name' => 'image_cropped',
                'type' => 'image',
                'instructions' => 'Square or cropped logo for listings.',
                'required' => 0,
                'return_format' => 'array',
                'library' => 'all',
                'preview_size' => 'thumbnail',
            ],
            
            // === Notes Tab ===
            [
                'key' => 'field_sfpf_org_tab_notes',
                'label' => 'Notes',
                'name' => '',
                'type' => 'tab',
                'placement' => 'top',
            ],
            [
                'key' => 'field_sfpf_org_notes',
                'label' => 'Internal Notes',
                'name' => 'notes',
                'type' => 'wysiwyg',
                'instructions' => 'Internal notes (not displayed publicly).',
                'required' => 0,
                'tabs' => 'all',
                'toolbar' => 'basic',
                'media_upload' => 0,
                'delay' => 1,
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
    
    write_log('Registered Organization ACF field group', false, 'ACF Registration');
}
