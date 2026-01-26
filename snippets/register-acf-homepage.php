<?php
namespace sfpf_person_website;

/**
 * Homepage ACF Fields Registration
 * 
 * Registers Advanced Custom Fields for the front page/homepage.
 * Adds schema markup field for Person/ProfilePage schema.
 * 
 * @package sfpf_person_website
 * @since 1.0.0
 */

defined('ABSPATH') || exit;

/**
 * Enable Homepage ACF fields
 * 
 * Called when the snippet is activated.
 */
function enable_homepage_acf_fields() {
    if (!function_exists('acf_add_local_field_group')) {
        return;
    }
    
    register_homepage_acf_group();
    
    // Add admin footer scripts for the homepage editor
    add_action('admin_footer', __NAMESPACE__ . '\\homepage_schema_admin_scripts');
}

/**
 * Register the Homepage ACF field group
 */
function register_homepage_acf_group() {
    
    // Use page_type location rule for front page
    $location = [
        [
            [
                'param' => 'page_type',
                'operator' => '==',
                'value' => 'front_page',
            ],
        ],
    ];
    
    acf_add_local_field_group([
        'key' => 'group_sfpf_homepage',
        'title' => 'Homepage Schema',
        'fields' => [
            
            // === Schema Configuration ===
            [
                'key' => 'field_sfpf_homepage_schema_type',
                'label' => 'Schema Type',
                'name' => 'schema_type',
                'type' => 'select',
                'instructions' => 'Choose the primary schema type for the homepage. "Person" is for personal websites, "ProfilePage" wraps a Person entity in a ProfilePage structure (better for SEO).',
                'required' => 0,
                'choices' => [
                    'person' => 'Person',
                    'profile_page' => 'ProfilePage (with Person)',
                ],
                'default_value' => 'profile_page',
                'return_format' => 'value',
            ],
            
            [
                'key' => 'field_sfpf_homepage_schema',
                'label' => 'Schema Markup',
                'name' => 'schema_markup',
                'type' => 'textarea',
                'instructions' => 'Generated JSON-LD schema markup for the homepage. This is auto-generated based on Website Settings and should not be manually edited.',
                'required' => 0,
                'readonly' => 1,
                'rows' => 15,
                'wrapper' => ['class' => 'sfpf-schema-field'],
            ],
            
            [
                'key' => 'field_sfpf_homepage_schema_preview',
                'label' => 'Schema Preview',
                'name' => 'schema_preview',
                'type' => 'message',
                'message' => '<div id="sfpf-schema-preview-homepage" style="background:#f5f5f5;padding:15px;border:1px solid #ddd;border-radius:4px;font-family:monospace;font-size:12px;max-height:400px;overflow:auto;"></div>
                <p style="margin-top:10px;">
                    <button type="button" id="sfpf-reprocess-homepage-schema" class="button button-secondary">🔄 Reprocess Schema</button>
                    <a href="https://validator.schema.org/" target="_blank" class="button">📋 Schema Validator</a>
                    <a href="https://search.google.com/test/rich-results" target="_blank" class="button">🔍 Google Rich Results</a>
                </p>',
                'new_lines' => '',
                'esc_html' => 0,
            ],
            
            // === Schema Source Info ===
            [
                'key' => 'field_sfpf_homepage_schema_info',
                'label' => 'Schema Data Sources',
                'name' => '',
                'type' => 'message',
                'message' => '<div style="background:#fff8e5;border:1px solid #f0c36d;padding:15px;border-radius:4px;">
                    <strong>ℹ️ Schema data is pulled from:</strong>
                    <ul style="margin:10px 0 0 20px;">
                        <li><strong>Website Settings</strong> (HWS Base Tools) - Name, Email, Biography</li>
                        <li><strong>Founder Information</strong> - User profile fields and social URLs</li>
                        <li><strong>Site Settings</strong> - Site title, URL, description</li>
                    </ul>
                    <p style="margin:10px 0 0;"><a href="' . esc_url(admin_url('admin.php?page=website-settings')) . '" target="_blank" class="button button-small">Edit Website Settings →</a></p>
                </div>',
                'new_lines' => '',
                'esc_html' => 0,
            ],
        ],
        'location' => $location,
        'menu_order' => 100,  // Show after other meta boxes
        'position' => 'normal',
        'style' => 'default',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
        'active' => true,
        'show_in_rest' => 0,
    ]);
    
    write_log('Registered Homepage ACF field group', false, 'ACF Registration');
}

/**
 * Add JavaScript for schema preview and reprocessing on homepage editor
 */
function homepage_schema_admin_scripts() {
    $screen = get_current_screen();
    if (!$screen || $screen->base !== 'post') {
        return;
    }
    
    // Check if we're on the front page
    global $post;
    $front_page_id = get_option('page_on_front');
    
    if (!$post || !$front_page_id || (int) $post->ID !== (int) $front_page_id) {
        return;
    }
    
    $nonce = wp_create_nonce('sfpf_reprocess_schema');
    ?>
    <script type="text/javascript">
    jQuery(document).ready(function($) {
        // Format and display schema preview
        function updateSchemaPreview() {
            var schemaField = $('textarea[name="acf[field_sfpf_homepage_schema]"]');
            var previewDiv = $('#sfpf-schema-preview-homepage');
            
            if (schemaField.length && previewDiv.length) {
                var schemaText = schemaField.val();
                if (schemaText) {
                    try {
                        var formatted = JSON.stringify(JSON.parse(schemaText), null, 2);
                        previewDiv.html('<pre style="margin:0;white-space:pre-wrap;">' + $('<div>').text(formatted).html() + '</pre>');
                    } catch (e) {
                        previewDiv.html('<pre style="margin:0;color:#dc3232;">' + $('<div>').text(schemaText).html() + '</pre>');
                    }
                } else {
                    previewDiv.html('<em>No schema generated yet. Click "Reprocess Schema" to generate.</em>');
                }
            }
        }
        
        updateSchemaPreview();
        
        // Reprocess schema button
        $('#sfpf-reprocess-homepage-schema').on('click', function(e) {
            e.preventDefault();
            var $btn = $(this);
            var originalText = $btn.text();
            
            $btn.text('Processing...').prop('disabled', true);
            
            $.post(ajaxurl, {
                action: 'sfpf_reprocess_homepage_schema',
                post_id: <?php echo (int) $post->ID; ?>,
                _wpnonce: '<?php echo esc_js($nonce); ?>'
            }, function(response) {
                if (response.success) {
                    $('textarea[name="acf[field_sfpf_homepage_schema]"]').val(response.data.schema);
                    updateSchemaPreview();
                    $btn.text('✓ Done!');
                    setTimeout(function() {
                        $btn.text(originalText).prop('disabled', false);
                    }, 2000);
                } else {
                    alert('Error: ' + (response.data.message || 'Unknown error'));
                    $btn.text(originalText).prop('disabled', false);
                }
            }).fail(function() {
                alert('AJAX request failed');
                $btn.text(originalText).prop('disabled', false);
            });
        });
    });
    </script>
    <?php
}
