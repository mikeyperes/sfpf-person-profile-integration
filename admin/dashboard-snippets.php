<?php
namespace sfpf_person_website;

/**
 * Dashboard Snippets Tab
 * 
 * Enable/disable CPT and ACF field snippets with full details.
 * 
 * @package sfpf_person_website
 * @since 1.0.0
 */

defined('ABSPATH') || exit;

// Get snippets from loader
$snippets = function_exists(__NAMESPACE__ . '\\get_snippets') ? get_snippets('all') : [];

// Group by type
$cpt_snippets = array_filter($snippets, fn($s) => ($s['type'] ?? '') === 'cpt');
$acf_snippets = array_filter($snippets, fn($s) => ($s['type'] ?? '') === 'acf');

?>

<div class="sfpf-card">
    <div class="sfpf-card-header">
        <span class="dashicons dashicons-admin-plugins" style="color:#6366f1;"></span>
        <h3>Custom Post Types</h3>
    </div>
    
    <p style="color:#666;margin-bottom:20px;">Enable or disable custom post types for your website.</p>
    
    <?php foreach ($cpt_snippets as $snippet): ?>
        <?php 
        $enabled = is_snippet_enabled($snippet['id']); 
        $php_code = get_cpt_php_code($snippet['id']);
        ?>
        <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:8px;padding:20px;margin-bottom:15px;">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:15px;">
                <div>
                    <h4 style="margin:0 0 5px 0;font-size:16px;"><?php echo esc_html($snippet['name']); ?></h4>
                    <p style="margin:0;color:#666;font-size:13px;"><?php echo esc_html($snippet['description']); ?></p>
                </div>
                <div style="display:flex;align-items:center;gap:10px;">
                    <?php echo render_status_badge($enabled); ?>
                    <button type="button" 
                            class="button <?php echo $enabled ? '' : 'button-primary'; ?> sfpf-toggle-snippet"
                            data-snippet="<?php echo esc_attr($snippet['id']); ?>"
                            data-enabled="<?php echo $enabled ? '1' : '0'; ?>">
                        <?php echo $enabled ? 'Disable' : 'Enable'; ?>
                    </button>
                </div>
            </div>
            
            <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));gap:15px;margin-bottom:15px;">
                <div style="background:white;padding:10px;border-radius:4px;border:1px solid #e5e7eb;">
                    <div style="font-size:11px;color:#6b7280;margin-bottom:3px;">Post Type</div>
                    <code style="background:#dbeafe;padding:2px 6px;border-radius:3px;"><?php echo esc_html(str_replace('sfpf_enable_', '', str_replace('_cpt', '', $snippet['id']))); ?></code>
                </div>
                <div style="background:white;padding:10px;border-radius:4px;border:1px solid #e5e7eb;">
                    <div style="font-size:11px;color:#6b7280;margin-bottom:3px;">Archive URL</div>
                    <code style="background:#dcfce7;padding:2px 6px;border-radius:3px;">/<?php echo esc_html(str_replace('sfpf_enable_', '', str_replace('_cpt', '', $snippet['id']))); ?>s/</code>
                </div>
                <div style="background:white;padding:10px;border-radius:4px;border:1px solid #e5e7eb;">
                    <div style="font-size:11px;color:#6b7280;margin-bottom:3px;">File</div>
                    <code style="font-size:11px;color:#666;">snippets/<?php echo esc_html($snippet['file']); ?></code>
                </div>
            </div>
            
            <?php if ($php_code): ?>
            <details style="margin-top:10px;">
                <summary style="cursor:pointer;color:#6366f1;font-weight:500;font-size:13px;">📄 View PHP Registration Code</summary>
                <pre style="background:#1e1e1e;color:#d4d4d4;padding:15px;border-radius:6px;overflow-x:auto;font-size:12px;margin-top:10px;line-height:1.5;"><?php echo esc_html($php_code); ?></pre>
            </details>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
    
    <?php if (empty($cpt_snippets)): ?>
        <p style="color:#666;text-align:center;">No CPT snippets available.</p>
    <?php endif; ?>
</div>

<div class="sfpf-card">
    <div class="sfpf-card-header">
        <span class="dashicons dashicons-welcome-widgets-menus" style="color:#059669;"></span>
        <h3>ACF Field Groups</h3>
    </div>
    
    <p style="color:#666;margin-bottom:20px;">Enable or disable ACF field groups for enhanced content management.</p>
    
    <?php foreach ($acf_snippets as $snippet): ?>
        <?php 
        $enabled = is_snippet_enabled($snippet['id']);
        $acf_structure = get_acf_field_structure($snippet['id']);
        ?>
        <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:8px;padding:20px;margin-bottom:15px;">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:15px;">
                <div>
                    <h4 style="margin:0 0 5px 0;font-size:16px;"><?php echo esc_html($snippet['name']); ?></h4>
                    <p style="margin:0;color:#666;font-size:13px;"><?php echo esc_html($snippet['description']); ?></p>
                </div>
                <div style="display:flex;align-items:center;gap:10px;">
                    <?php echo render_status_badge($enabled); ?>
                    <button type="button" 
                            class="button <?php echo $enabled ? '' : 'button-primary'; ?> sfpf-toggle-snippet"
                            data-snippet="<?php echo esc_attr($snippet['id']); ?>"
                            data-enabled="<?php echo $enabled ? '1' : '0'; ?>">
                        <?php echo $enabled ? 'Disable' : 'Enable'; ?>
                    </button>
                </div>
            </div>
            
            <?php if (!empty($acf_structure)): ?>
            <details style="margin-top:10px;">
                <summary style="cursor:pointer;color:#059669;font-weight:500;font-size:13px;">📋 View ACF Field Structure</summary>
                <div style="margin-top:15px;background:white;border:1px solid #e5e7eb;border-radius:6px;overflow:hidden;">
                    <!-- Header Info -->
                    <div style="background:#f3f4f6;padding:12px 15px;border-bottom:1px solid #e5e7eb;">
                        <div style="display:grid;grid-template-columns:repeat(3, 1fr);gap:15px;font-size:12px;">
                            <div><strong>Group Key:</strong> <code><?php echo esc_html($acf_structure['group_key']); ?></code></div>
                            <div><strong>Title:</strong> <?php echo esc_html($acf_structure['group_title']); ?></div>
                            <div><strong>Location:</strong> <code><?php echo esc_html($acf_structure['location']); ?></code></div>
                        </div>
                    </div>
                    
                    <!-- Fields Table -->
                    <table style="width:100%;border-collapse:collapse;font-size:12px;">
                        <thead>
                            <tr style="background:#f9fafb;">
                                <th style="padding:10px 15px;text-align:left;border-bottom:1px solid #e5e7eb;width:25%;">Label</th>
                                <th style="padding:10px 15px;text-align:left;border-bottom:1px solid #e5e7eb;width:25%;">Field Name</th>
                                <th style="padding:10px 15px;text-align:left;border-bottom:1px solid #e5e7eb;width:15%;">Type</th>
                                <th style="padding:10px 15px;text-align:left;border-bottom:1px solid #e5e7eb;">Field Key</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($acf_structure['tabs'] as $tab_name => $fields): ?>
                                <tr style="background:#eef2ff;">
                                    <td colspan="4" style="padding:8px 15px;font-weight:600;color:#4f46e5;">
                                        <span class="dashicons dashicons-category" style="font-size:14px;vertical-align:middle;margin-right:5px;"></span>
                                        <?php echo esc_html($tab_name); ?>
                                    </td>
                                </tr>
                                <?php foreach ($fields as $field): ?>
                                    <tr>
                                        <td style="padding:8px 15px;border-bottom:1px solid #f3f4f6;padding-left:30px;">
                                            <?php echo esc_html($field['label']); ?>
                                            <?php if (!empty($field['readonly'])): ?>
                                                <span style="background:#fef3c7;color:#92400e;padding:1px 4px;border-radius:3px;font-size:10px;margin-left:5px;">readonly</span>
                                            <?php endif; ?>
                                        </td>
                                        <td style="padding:8px 15px;border-bottom:1px solid #f3f4f6;">
                                            <code style="background:#dbeafe;padding:2px 6px;border-radius:3px;"><?php echo esc_html($field['name']); ?></code>
                                        </td>
                                        <td style="padding:8px 15px;border-bottom:1px solid #f3f4f6;">
                                            <span style="background:#f3f4f6;padding:2px 6px;border-radius:3px;font-size:11px;"><?php echo esc_html($field['type']); ?></span>
                                        </td>
                                        <td style="padding:8px 15px;border-bottom:1px solid #f3f4f6;">
                                            <code style="font-size:10px;color:#9ca3af;"><?php echo esc_html($field['key']); ?></code>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </details>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
    
    <?php if (empty($acf_snippets)): ?>
        <p style="color:#666;text-align:center;">No ACF snippets available.</p>
    <?php endif; ?>
</div>

<div class="sfpf-alert sfpf-alert-info">
    <strong>Note:</strong> After enabling or disabling snippets, you may need to refresh your permalinks by going to 
    <a href="<?php echo admin_url('options-permalink.php'); ?>">Settings → Permalinks</a> and clicking "Save Changes".
</div>

<script>
jQuery(document).ready(function($) {
    $('.sfpf-toggle-snippet').on('click', function() {
        var $btn = $(this);
        var $card = $btn.closest('[style*="background:#f9fafb"]');
        var $badge = $card.find('.sfpf-badge');
        var snippetId = $btn.data('snippet');
        var currentlyEnabled = $btn.data('enabled') === 1 || $btn.data('enabled') === '1';
        var newState = currentlyEnabled ? 0 : 1;
        
        $btn.prop('disabled', true).text('Processing...');
        
        $.post(ajaxurl, {
            action: 'sfpf_toggle_snippet',
            snippet_id: snippetId,
            enabled: newState,
            nonce: '<?php echo wp_create_nonce('sfpf_ajax'); ?>'
        }, function(response) {
            if (response.success) {
                // Update button
                $btn.data('enabled', newState);
                $btn.text(newState ? 'Disable' : 'Enable');
                $btn.toggleClass('button-primary', !newState);
                
                // Update badge
                if (newState) {
                    $badge.removeClass('sfpf-badge-error').addClass('sfpf-badge-success')
                          .html('<span class="dashicons dashicons-yes-alt" style="font-size:14px;vertical-align:middle;margin-right:3px;"></span>Enabled');
                } else {
                    $badge.removeClass('sfpf-badge-success').addClass('sfpf-badge-error')
                          .html('<span class="dashicons dashicons-no-alt" style="font-size:14px;vertical-align:middle;margin-right:3px;"></span>Disabled');
                }
                
                // Show notice
                var $notice = $('<div class="notice notice-success is-dismissible" style="position:fixed;top:50px;right:20px;z-index:9999;padding:10px 15px;"><p>' + 
                    (newState ? '✅ Snippet enabled!' : '⛔ Snippet disabled!') + 
                    ' <strong>Note:</strong> Please refresh permalinks if this is a CPT snippet.</p></div>');
                $('body').append($notice);
                setTimeout(function() { $notice.fadeOut(function() { $(this).remove(); }); }, 4000);
                
                $btn.prop('disabled', false);
            } else {
                $btn.prop('disabled', false).text(currentlyEnabled ? 'Disable' : 'Enable');
                alert('Error: ' + (response.data || 'Unknown error'));
            }
        }).fail(function() {
            $btn.prop('disabled', false).text(currentlyEnabled ? 'Disable' : 'Enable');
            alert('AJAX request failed');
        });
    });
});
</script>
