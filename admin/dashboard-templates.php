<?php
namespace sfpf_person_website;

/**
 * Dashboard Templates Tab
 * 
 * Page templates with WYSIWYG editors and shortcode reference.
 * 
 * @package sfpf_person_website
 * @since 1.0.0
 */

defined('ABSPATH') || exit;

// Template definitions
$templates = [
    'biography' => [
        'title' => 'Biography Page',
        'description' => 'Template for the main biography page.',
        'default' => '<h2>About</h2>
<p>[founder id="biography"]</p>

<h3>Contact</h3>
<p>Email: [website_content field="email"]</p>

<h3>Connect</h3>
<ul>
<li><a href="[website_url social="linkedin"]">LinkedIn</a></li>
<li><a href="[website_url social="twitter"]">Twitter/X</a></li>
<li><a href="[website_url social="instagram"]">Instagram</a></li>
</ul>',
    ],
    'education' => [
        'title' => 'Education Page',
        'description' => 'Template for the education sub-page.',
        'default' => '<h2>Education</h2>
<p>Learn about the educational background and qualifications.</p>',
    ],
    'organizations_founded' => [
        'title' => 'Organizations Founded Page',
        'description' => 'Template for organizations founded sub-page.',
        'default' => '<h2>Organizations Founded</h2>
<p>A list of organizations and companies founded by [founder id="name"].</p>',
    ],
    'professions' => [
        'title' => 'Professions Page',
        'description' => 'Template for professions sub-page.',
        'default' => '<h2>Professions</h2>
<p>Professional roles and career information for [founder id="name"].</p>',
    ],
];

?>

<!-- Available Shortcodes (Chips Style) -->
<div class="sfpf-card">
    <div class="sfpf-card-header">
        <span class="dashicons dashicons-shortcode" style="color:#ec4899;"></span>
        <h3>Available Shortcodes</h3>
    </div>
    
    <p style="color:#666;margin-bottom:15px;">Use these shortcodes in your templates. They pull data from Website Settings.</p>
    
    <div class="sfpf-shortcode-chips">
        <span class="sfpf-shortcode-chip" data-shortcode='[website_content field="biography_short"]'>[website_content field="biography_short"]</span>
        <span class="sfpf-shortcode-chip" data-shortcode='[website_content field="email"]'>[website_content field="email"]</span>
        <span class="sfpf-shortcode-chip" data-shortcode='[founder id="name"]'>[founder id="name"]</span>
        <span class="sfpf-shortcode-chip" data-shortcode='[founder id="title"]'>[founder id="title"]</span>
        <span class="sfpf-shortcode-chip" data-shortcode='[founder id="biography"]'>[founder id="biography"]</span>
        <span class="sfpf-shortcode-chip" data-shortcode='[founder id="website"]'>[founder id="website"]</span>
        <span class="sfpf-shortcode-chip" data-shortcode='[website_url social="facebook"]'>[website_url social="facebook"]</span>
        <span class="sfpf-shortcode-chip" data-shortcode='[website_url social="instagram"]'>[website_url social="instagram"]</span>
        <span class="sfpf-shortcode-chip" data-shortcode='[website_url social="linkedin"]'>[website_url social="linkedin"]</span>
        <span class="sfpf-shortcode-chip" data-shortcode='[website_url social="twitter"]'>[website_url social="twitter"]</span>
        <span class="sfpf-shortcode-chip" data-shortcode='[website_url social="x"]'>[website_url social="x"]</span>
        <span class="sfpf-shortcode-chip" data-shortcode='[website_url social="youtube"]'>[website_url social="youtube"]</span>
        <span class="sfpf-shortcode-chip" data-shortcode='[website_url social="tiktok"]'>[website_url social="tiktok"]</span>
        <span class="sfpf-shortcode-chip" data-shortcode='[website_url social="github"]'>[website_url social="github"]</span>
        <span class="sfpf-shortcode-chip" data-shortcode='[website_url social="wikipedia"]'>[website_url social="wikipedia"]</span>
        <span class="sfpf-shortcode-chip" data-shortcode='[website_url social="imdb"]'>[website_url social="imdb"]</span>
        <span class="sfpf-shortcode-chip" data-shortcode='[website_url social="muckrack"]'>[website_url social="muckrack"]</span>
        <span class="sfpf-shortcode-chip" data-shortcode='[website_url social="crunchbase"]'>[website_url social="crunchbase"]</span>
        <span class="sfpf-shortcode-chip" data-shortcode='[website_url social="amazon"]'>[website_url social="amazon"]</span>
        <span class="sfpf-shortcode-chip" data-shortcode='[website_url social="audible"]'>[website_url social="audible"]</span>
    </div>
    
    <!-- Expandable full list -->
    <details style="margin-top:15px;">
        <summary style="cursor:pointer;color:#2563eb;font-size:13px;">▶ View All Shortcodes with Descriptions</summary>
        <div style="margin-top:15px;">
            <table class="sfpf-table" style="font-size:13px;">
                <thead>
                    <tr>
                        <th>Shortcode</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (get_all_shortcodes() as $category => $shortcodes): ?>
                        <tr>
                            <td colspan="2" style="background:#f3f4f6;font-weight:600;color:#4b5563;"><?php echo esc_html($category); ?></td>
                        </tr>
                        <?php foreach ($shortcodes as $sc): ?>
                            <tr>
                                <td><code style="font-size:11px;"><?php echo esc_html($sc['shortcode']); ?></code></td>
                                <td style="color:#666;"><?php echo esc_html($sc['description']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </details>
</div>

<!-- Page Templates -->
<div class="sfpf-card">
    <div class="sfpf-card-header">
        <span class="dashicons dashicons-welcome-write-blog" style="color:#f59e0b;"></span>
        <h3>Page Templates</h3>
    </div>
    
    <p style="color:#666;margin-bottom:20px;">Edit default templates for each page type. Use shortcodes to pull dynamic content.</p>
    
    <?php foreach ($templates as $template_key => $template): ?>
        <?php
        $saved_content = get_option('sfpf_template_' . $template_key, $template['default']);
        $editor_id = 'sfpf_template_' . $template_key;
        ?>
        
        <div class="sfpf-template-block" style="margin-bottom:30px;padding-bottom:30px;border-bottom:1px solid #e5e7eb;">
            <h4 style="margin:0 0 5px 0;font-size:16px;"><?php echo esc_html($template['title']); ?></h4>
            <p style="color:#666;font-size:13px;margin:0 0 15px 0;"><?php echo esc_html($template['description']); ?></p>
            
            <?php
            wp_editor($saved_content, $editor_id, [
                'textarea_name' => $editor_id,
                'textarea_rows' => 12,
                'media_buttons' => true,
                'teeny' => false,
                'tinymce' => [
                    'toolbar1' => 'formatselect,bold,italic,bullist,numlist,link,unlink,wp_more,spellchecker,fullscreen,wp_adv',
                    'toolbar2' => 'strikethrough,hr,forecolor,pastetext,removeformat,charmap,outdent,indent,undo,redo,wp_help',
                ],
                'quicktags' => true,
            ]);
            ?>
            
            <div style="margin-top:15px;display:flex;gap:10px;">
                <button type="button" class="sfpf-btn sfpf-btn-primary sfpf-save-template" data-template="<?php echo esc_attr($template_key); ?>">
                    💾 Save Template
                </button>
                <button type="button" class="sfpf-btn sfpf-btn-secondary sfpf-reset-template" data-template="<?php echo esc_attr($template_key); ?>" data-default="<?php echo esc_attr($template['default']); ?>">
                    ↺ Reset to Default
                </button>
                <button type="button" class="sfpf-btn sfpf-btn-secondary sfpf-apply-template" data-template="<?php echo esc_attr($template_key); ?>">
                    📄 Apply to Page
                </button>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<style>
.sfpf-shortcode-chips {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.sfpf-shortcode-chip {
    display: inline-block;
    padding: 6px 12px;
    background: #f3f4f6;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    font-family: 'Monaco', 'Menlo', 'Consolas', monospace;
    font-size: 11px;
    color: #374151;
    cursor: pointer;
    transition: all 0.15s ease;
}

.sfpf-shortcode-chip:hover {
    background: #dbeafe;
    border-color: #2563eb;
    color: #1d4ed8;
}

.sfpf-shortcode-chip:active {
    transform: scale(0.98);
}

/* Make editors look better */
.sfpf-template-block .wp-editor-wrap {
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    overflow: hidden;
}

.sfpf-template-block .wp-editor-tools {
    background: #f9fafb;
    border-bottom: 1px solid #e5e7eb;
}

.sfpf-template-block .wp-editor-area {
    min-height: 200px !important;
}

.sfpf-template-block iframe {
    min-height: 200px !important;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Copy shortcode on click
    $('.sfpf-shortcode-chip').on('click', function() {
        var shortcode = $(this).data('shortcode');
        
        // Try to copy to clipboard
        if (navigator.clipboard) {
            navigator.clipboard.writeText(shortcode).then(function() {
                // Visual feedback
                var $chip = $(this);
                $chip.css('background', '#dcfce7').css('border-color', '#22c55e');
                setTimeout(function() {
                    $chip.css('background', '').css('border-color', '');
                }, 300);
            }.bind(this));
        }
        
        // Also try to insert into active editor
        if (typeof tinyMCE !== 'undefined') {
            var activeEditor = tinyMCE.activeEditor;
            if (activeEditor && !activeEditor.isHidden()) {
                activeEditor.execCommand('mceInsertContent', false, shortcode);
                return;
            }
        }
        
        // Fallback to textarea
        var $activeTextarea = $('textarea.wp-editor-area:focus');
        if ($activeTextarea.length) {
            var cursorPos = $activeTextarea[0].selectionStart;
            var textBefore = $activeTextarea.val().substring(0, cursorPos);
            var textAfter = $activeTextarea.val().substring(cursorPos);
            $activeTextarea.val(textBefore + shortcode + textAfter);
        }
    });
    
    // Save template
    $('.sfpf-save-template').on('click', function() {
        var templateKey = $(this).data('template');
        var editorId = 'sfpf_template_' + templateKey;
        var content;
        
        // Get content from TinyMCE or textarea
        if (typeof tinyMCE !== 'undefined' && tinyMCE.get(editorId) && !tinyMCE.get(editorId).isHidden()) {
            content = tinyMCE.get(editorId).getContent();
        } else {
            content = $('#' + editorId).val();
        }
        
        var $btn = $(this);
        $btn.prop('disabled', true).text('Saving...');
        
        $.post(ajaxurl, {
            action: 'sfpf_save_template',
            template_key: templateKey,
            content: content,
            nonce: '<?php echo wp_create_nonce('sfpf_ajax'); ?>'
        }, function(response) {
            $btn.prop('disabled', false).html('💾 Save Template');
            alert(response.success ? 'Template saved!' : 'Error: ' + response.data);
        });
    });
    
    // Reset template
    $('.sfpf-reset-template').on('click', function() {
        if (!confirm('Reset this template to default? This cannot be undone.')) return;
        
        var templateKey = $(this).data('template');
        var defaultContent = $(this).data('default');
        var editorId = 'sfpf_template_' + templateKey;
        
        // Set content in TinyMCE or textarea
        if (typeof tinyMCE !== 'undefined' && tinyMCE.get(editorId) && !tinyMCE.get(editorId).isHidden()) {
            tinyMCE.get(editorId).setContent(defaultContent);
        } else {
            $('#' + editorId).val(defaultContent);
        }
        
        // Also save it
        $.post(ajaxurl, {
            action: 'sfpf_save_template',
            template_key: templateKey,
            content: defaultContent,
            nonce: '<?php echo wp_create_nonce('sfpf_ajax'); ?>'
        });
    });
    
    // Apply template to page
    $('.sfpf-apply-template').on('click', function() {
        var templateKey = $(this).data('template');
        
        if (!confirm('Apply this template to the assigned page? This will replace the page content.')) return;
        
        var $btn = $(this);
        $btn.prop('disabled', true).text('Applying...');
        
        $.post(ajaxurl, {
            action: 'sfpf_apply_template',
            template_key: templateKey,
            nonce: '<?php echo wp_create_nonce('sfpf_ajax'); ?>'
        }, function(response) {
            $btn.prop('disabled', false).html('📄 Apply to Page');
            alert(response.success ? 'Template applied to page!' : 'Error: ' + response.data);
        });
    });
});
</script>
