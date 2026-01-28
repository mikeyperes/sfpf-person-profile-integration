<?php
namespace sfpf_person_website;

/**
 * Dashboard FAQ Structures Tab
 * 
 * FAQ Sets with repeater items and WYSIWYG answers
 * 
 * @package sfpf_person_website
 * @since 1.3.0
 */

defined('ABSPATH') || exit;

// Enqueue WP editor scripts
wp_enqueue_editor();
wp_enqueue_media();

// Get FAQ sets
$faq_sets = get_option('sfpf_faq_sets', []);
if (!is_array($faq_sets)) $faq_sets = [];

?>

<div class="sfpf-card">
    <div class="sfpf-card-header">
        <span class="dashicons dashicons-editor-help" style="color:#8b5cf6;"></span>
        <h3>FAQ Sets Manager</h3>
    </div>
    
    <p style="color:#666;margin-bottom:20px;">Create named FAQ sets, each containing multiple Q&A items. FAQs automatically generate FAQPage schema when displayed.</p>
    
    <div id="sfpf-faq-sets-container">
        <?php if (empty($faq_sets)): ?>
            <div class="sfpf-no-faq-sets" style="text-align:center;padding:40px;background:#f9fafb;border-radius:8px;color:#666;">
                <span class="dashicons dashicons-format-status" style="font-size:48px;color:#d1d5db;"></span>
                <p style="margin:10px 0 0;">No FAQ sets created yet. Click "Add FAQ Set" to get started.</p>
            </div>
        <?php else: ?>
            <?php foreach ($faq_sets as $set_index => $set): ?>
                <?php render_faq_set_panel($set, $set_index); ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    
    <div style="margin-top:20px;display:flex;gap:10px;">
        <button type="button" class="button button-secondary" id="sfpf-add-faq-set">
            <span class="dashicons dashicons-plus-alt" style="vertical-align:middle;"></span> Add FAQ Set
        </button>
        <button type="button" class="button button-primary" id="sfpf-save-all-faqs">
            💾 Save All FAQ Sets
        </button>
    </div>
</div>

<!-- FAQ Schema Settings -->
<div class="sfpf-card">
    <div class="sfpf-card-header">
        <span class="dashicons dashicons-media-code" style="color:#059669;"></span>
        <h3>FAQ Schema Settings</h3>
    </div>
    
    <?php $inject_faq_schema = get_option('sfpf_inject_faq_schema', true); ?>
    
    <label style="display:flex;align-items:center;gap:10px;cursor:pointer;margin-bottom:15px;">
        <input type="checkbox" id="sfpf-inject-faq-schema" value="1" <?php checked($inject_faq_schema, true); ?>>
        <span>Automatically inject FAQPage schema when FAQ shortcodes are used on a page</span>
    </label>
</div>

<!-- Shortcode Reference -->
<div class="sfpf-card">
    <div class="sfpf-card-header">
        <span class="dashicons dashicons-shortcode" style="color:#f59e0b;"></span>
        <h3>Shortcode Reference</h3>
    </div>
    
    <table class="sfpf-table">
        <thead>
            <tr>
                <th>Shortcode</th>
                <th>Description</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><code>[sfpf_faq set="set-slug"]</code></td>
                <td>Display all FAQs from a specific set</td>
            </tr>
            <tr>
                <td><code>[sfpf_faq set="set-slug" index="0"]</code></td>
                <td>Display single FAQ item from a set (0-indexed)</td>
            </tr>
            <tr>
                <td><code>[sfpf_faq set="set-slug" style="accordion"]</code></td>
                <td>Display as collapsible accordion</td>
            </tr>
            <tr>
                <td><code>[sfpf_faq_schema set="set-slug"]</code></td>
                <td>Output FAQ schema only (hidden)</td>
            </tr>
            <tr>
                <td><code>[sfpf_elementor_faq set="set-slug" target=".selector"]</code></td>
                <td>Inject FAQ content into Elementor accordion</td>
            </tr>
        </tbody>
    </table>
</div>

<!-- Elementor Integration -->
<div class="sfpf-card">
    <div class="sfpf-card-header">
        <span class="dashicons dashicons-admin-customizer" style="color:#ec4899;"></span>
        <h3>Elementor FAQ Integration</h3>
    </div>
    
    <p style="color:#666;margin-bottom:15px;">Inject FAQ content into Elementor accordion widgets:</p>
    
    <div style="background:#f0f6fc;padding:15px;border-radius:6px;margin-bottom:15px;">
        <h4 style="margin:0 0 10px;font-size:13px;">Auto-populate Elementor Accordion</h4>
        <p style="font-size:12px;color:#666;margin:0 0 10px;">Add this shortcode BEFORE your Elementor accordion widget:</p>
        <code style="display:block;background:#1e1e1e;color:#d4d4d4;padding:10px;border-radius:4px;font-size:12px;">
            [sfpf_elementor_faq set="your-set-slug" target=".elementor-accordion"]
        </code>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    var setIndex = <?php echo count($faq_sets); ?>;
    var editorCounter = 1000;
    
    // Generate slug from name
    function generateSlug(name) {
        return name.toLowerCase()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            .substring(0, 50) || 'faq-set-' + Date.now();
    }
    
    // Initialize TinyMCE for a textarea - using direct tinymce.init for reliability
    function initTinyMCE($textarea) {
        var id = $textarea.attr('id');
        if (!id) {
            id = 'sfpf-editor-' + (editorCounter++);
            $textarea.attr('id', id);
        }
        
        // Remove existing editor if any
        if (typeof tinymce !== 'undefined' && tinymce.get(id)) {
            tinymce.get(id).remove();
        }
        
        // Use direct tinymce.init for better control with dynamic elements
        if (typeof tinymce !== 'undefined') {
            tinymce.init({
                selector: '#' + id,
                height: 200,
                menubar: false,
                statusbar: false,
                plugins: 'lists link paste wordpress wpautoresize',
                toolbar: 'bold italic underline | bullist numlist | link | removeformat',
                branding: false,
                convert_urls: false,
                relative_urls: false,
                remove_script_host: false,
                entity_encoding: 'raw',
                setup: function(editor) {
                    editor.on('change', function() {
                        editor.save(); // Sync to textarea
                    });
                    editor.on('blur', function() {
                        editor.save();
                    });
                }
            });
        } else {
            console.log('TinyMCE not available, textarea will remain as-is');
        }
    }
    
    // Add FAQ Set
    $('#sfpf-add-faq-set').on('click', function() {
        var slug = 'faq-set-' + setIndex;
        var html = `
        <div class="sfpf-faq-set" data-set-index="${setIndex}" style="background:#fff;border:2px solid #e5e7eb;border-radius:8px;margin-bottom:20px;overflow:hidden;">
            <div class="sfpf-faq-set-header" style="background:linear-gradient(135deg, #667eea 0%, #764ba2 100%);padding:15px;display:flex;justify-content:space-between;align-items:center;">
                <div style="display:flex;align-items:center;gap:15px;flex:1;">
                    <span class="dashicons dashicons-list-view" style="color:#fff;font-size:24px;"></span>
                    <input type="text" class="sfpf-faq-set-name" value="" placeholder="FAQ Set Name (e.g., Product FAQs)" style="flex:1;padding:8px 12px;border:none;border-radius:4px;font-size:14px;font-weight:600;">
                </div>
                <div style="display:flex;gap:10px;">
                    <button type="button" class="button sfpf-toggle-set" style="background:#fff;">
                        <span class="dashicons dashicons-arrow-down-alt2" style="vertical-align:middle;"></span>
                    </button>
                    <button type="button" class="button sfpf-delete-set" style="background:#fee2e2;color:#dc2626;border-color:#fecaca;">
                        <span class="dashicons dashicons-trash" style="vertical-align:middle;"></span>
                    </button>
                </div>
            </div>
            
            <div class="sfpf-faq-set-content" style="padding:20px;">
                <div class="sfpf-faq-set-info" style="background:#f0f6fc;padding:12px;border-radius:6px;margin-bottom:20px;font-size:12px;">
                    <strong>Set Slug:</strong> <code class="sfpf-set-slug">${slug}</code>
                    <span style="margin-left:15px;"><strong>Shortcode:</strong> <code>[sfpf_faq set="${slug}"]</code></span>
                </div>
                
                <div class="sfpf-faq-items-container"></div>
                
                <button type="button" class="button sfpf-add-faq-item" style="margin-top:15px;">
                    <span class="dashicons dashicons-plus" style="vertical-align:middle;"></span> Add FAQ Item
                </button>
            </div>
        </div>`;
        
        $('.sfpf-no-faq-sets').remove();
        $('#sfpf-faq-sets-container').append(html);
        setIndex++;
    });
    
    // Update slug when name changes
    $(document).on('blur', '.sfpf-faq-set-name', function() {
        var $set = $(this).closest('.sfpf-faq-set');
        var name = $(this).val();
        var slug = generateSlug(name);
        $set.find('.sfpf-set-slug').text(slug);
        $set.find('.sfpf-faq-set-info code:last').text('[sfpf_faq set="' + slug + '"]');
    });
    
    // Toggle set content
    $(document).on('click', '.sfpf-toggle-set', function() {
        var $content = $(this).closest('.sfpf-faq-set').find('.sfpf-faq-set-content');
        var $icon = $(this).find('.dashicons');
        $content.slideToggle(200);
        $icon.toggleClass('dashicons-arrow-down-alt2 dashicons-arrow-up-alt2');
    });
    
    // Delete FAQ Set
    $(document).on('click', '.sfpf-delete-set', function() {
        if (confirm('Delete this entire FAQ set and all its items?')) {
            $(this).closest('.sfpf-faq-set').fadeOut(300, function() {
                $(this).remove();
            });
        }
    });
    
    // Add FAQ Item with WYSIWYG
    $(document).on('click', '.sfpf-add-faq-item', function() {
        var $container = $(this).siblings('.sfpf-faq-items-container');
        var editorId = 'sfpf-editor-' + (editorCounter++);
        
        var html = `
        <div class="sfpf-faq-item" style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:6px;padding:15px;margin-bottom:15px;">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:15px;">
                <div style="flex:1;margin-right:15px;">
                    <label style="display:block;font-size:12px;color:#6b7280;margin-bottom:5px;font-weight:600;">Question</label>
                    <input type="text" class="sfpf-faq-question widefat" value="" placeholder="Enter your question..." style="font-size:14px;padding:10px;">
                </div>
                <button type="button" class="button sfpf-delete-faq-item" style="color:#dc2626;">
                    <span class="dashicons dashicons-no-alt" style="vertical-align:middle;"></span>
                </button>
            </div>
            
            <div>
                <label style="display:block;font-size:12px;color:#6b7280;margin-bottom:5px;font-weight:600;">Answer (Rich Text)</label>
                <div class="sfpf-faq-answer-wrapper">
                    <textarea id="${editorId}" class="sfpf-faq-answer" rows="8" style="width:100%;"><?php // Start empty ?></textarea>
                </div>
            </div>
        </div>`;
        
        $container.append(html);
        
        // Initialize TinyMCE with longer delay to ensure DOM is ready
        setTimeout(function() {
            var $textarea = $('#' + editorId);
            if ($textarea.length) {
                initTinyMCE($textarea);
            }
        }, 200);
    });
    
    // Delete FAQ Item
    $(document).on('click', '.sfpf-delete-faq-item', function() {
        if (confirm('Delete this FAQ item?')) {
            var $item = $(this).closest('.sfpf-faq-item');
            var $textarea = $item.find('.sfpf-faq-answer');
            var id = $textarea.attr('id');
            
            // Remove TinyMCE instance
            if (id && typeof tinymce !== 'undefined' && tinymce.get(id)) {
                tinymce.get(id).remove();
            }
            
            $item.fadeOut(200, function() {
                $(this).remove();
            });
        }
    });
    
    // Save all FAQs
    $('#sfpf-save-all-faqs').on('click', function() {
        var $btn = $(this);
        $btn.prop('disabled', true).text('Saving...');
        
        // Sync TinyMCE content back to textareas
        if (typeof tinyMCE !== 'undefined') {
            tinyMCE.triggerSave();
        }
        
        var faqSets = [];
        
        $('.sfpf-faq-set').each(function() {
            var $set = $(this);
            var setName = $set.find('.sfpf-faq-set-name').val();
            var setSlug = generateSlug(setName);
            
            var items = [];
            $set.find('.sfpf-faq-item').each(function() {
                var $item = $(this);
                var $textarea = $item.find('.sfpf-faq-answer');
                var id = $textarea.attr('id');
                var answer = '';
                
                // Get content from TinyMCE or textarea
                if (id && typeof tinymce !== 'undefined' && tinymce.get(id)) {
                    answer = tinymce.get(id).getContent();
                } else {
                    answer = $textarea.val();
                }
                
                items.push({
                    question: $item.find('.sfpf-faq-question').val(),
                    answer: answer
                });
            });
            
            if (setName || items.length > 0) {
                faqSets.push({
                    name: setName,
                    slug: setSlug,
                    items: items
                });
            }
        });
        
        var injectSchema = $('#sfpf-inject-faq-schema').is(':checked');
        
        $.post(ajaxurl, {
            action: 'sfpf_save_faq_sets',
            faq_sets: JSON.stringify(faqSets),
            inject_schema: injectSchema ? 1 : 0,
            nonce: '<?php echo wp_create_nonce('sfpf_ajax'); ?>'
        }, function(response) {
            $btn.prop('disabled', false).html('💾 Save All FAQ Sets');
            if (response.success) {
                var $notice = $('<div style="position:fixed;top:50px;right:20px;z-index:9999;padding:12px 20px;background:#dcfce7;border:1px solid #16a34a;border-radius:6px;box-shadow:0 4px 12px rgba(0,0,0,0.15);"><p style="margin:0;">✅ FAQ sets saved!</p></div>');
                $('body').append($notice);
                setTimeout(function() { $notice.fadeOut(function() { $(this).remove(); }); }, 3000);
            } else {
                alert('Error: ' + (response.data || 'Unknown error'));
            }
        });
    });
    
    // Initialize existing editors on page load
    $('.sfpf-faq-answer').each(function() {
        initTinyMCE($(this));
    });
});
</script>

<?php
/**
 * Render a single FAQ set panel
 */
function render_faq_set_panel($set, $set_index) {
    $name = $set['name'] ?? '';
    $slug = $set['slug'] ?? 'faq-set-' . $set_index;
    $items = $set['items'] ?? [];
    ?>
    <div class="sfpf-faq-set" data-set-index="<?php echo $set_index; ?>" style="background:#fff;border:2px solid #e5e7eb;border-radius:8px;margin-bottom:20px;overflow:hidden;">
        <div class="sfpf-faq-set-header" style="background:linear-gradient(135deg, #667eea 0%, #764ba2 100%);padding:15px;display:flex;justify-content:space-between;align-items:center;">
            <div style="display:flex;align-items:center;gap:15px;flex:1;">
                <span class="dashicons dashicons-list-view" style="color:#fff;font-size:24px;"></span>
                <input type="text" class="sfpf-faq-set-name" value="<?php echo esc_attr($name); ?>" placeholder="FAQ Set Name (e.g., Product FAQs)" style="flex:1;padding:8px 12px;border:none;border-radius:4px;font-size:14px;font-weight:600;">
            </div>
            <div style="display:flex;gap:10px;">
                <button type="button" class="button sfpf-toggle-set" style="background:#fff;">
                    <span class="dashicons dashicons-arrow-down-alt2" style="vertical-align:middle;"></span>
                </button>
                <button type="button" class="button sfpf-delete-set" style="background:#fee2e2;color:#dc2626;border-color:#fecaca;">
                    <span class="dashicons dashicons-trash" style="vertical-align:middle;"></span>
                </button>
            </div>
        </div>
        
        <div class="sfpf-faq-set-content" style="padding:20px;">
            <div class="sfpf-faq-set-info" style="background:#f0f6fc;padding:12px;border-radius:6px;margin-bottom:20px;font-size:12px;">
                <strong>Set Slug:</strong> <code class="sfpf-set-slug"><?php echo esc_html($slug); ?></code>
                <span style="margin-left:15px;"><strong>Shortcode:</strong> <code>[sfpf_faq set="<?php echo esc_attr($slug); ?>"]</code></span>
            </div>
            
            <div class="sfpf-faq-items-container">
                <?php foreach ($items as $item_index => $item): 
                    $editor_id = 'sfpf-faq-editor-' . $set_index . '-' . $item_index;
                ?>
                    <div class="sfpf-faq-item" style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:6px;padding:15px;margin-bottom:15px;">
                        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:15px;">
                            <div style="flex:1;margin-right:15px;">
                                <label style="display:block;font-size:12px;color:#6b7280;margin-bottom:5px;font-weight:600;">Question</label>
                                <input type="text" class="sfpf-faq-question widefat" value="<?php echo esc_attr($item['question'] ?? ''); ?>" placeholder="Enter your question..." style="font-size:14px;padding:10px;">
                            </div>
                            <button type="button" class="button sfpf-delete-faq-item" style="color:#dc2626;">
                                <span class="dashicons dashicons-no-alt" style="vertical-align:middle;"></span>
                            </button>
                        </div>
                        
                        <div>
                            <label style="display:block;font-size:12px;color:#6b7280;margin-bottom:5px;font-weight:600;">Answer (Rich Text)</label>
                            <div class="sfpf-faq-answer-wrapper">
                                <textarea id="<?php echo $editor_id; ?>" class="sfpf-faq-answer" rows="6"><?php echo esc_textarea($item['answer'] ?? ''); ?></textarea>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <button type="button" class="button sfpf-add-faq-item" style="margin-top:15px;">
                <span class="dashicons dashicons-plus" style="vertical-align:middle;"></span> Add FAQ Item
            </button>
        </div>
    </div>
    <?php
}
?>
