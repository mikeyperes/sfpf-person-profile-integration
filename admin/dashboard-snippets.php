<?php
namespace sfpf_person_website;

/**
 * Dashboard Snippets Tab
 * 
 * Enable/disable CPT and ACF field snippets.
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
    
    <table class="sfpf-table">
        <thead>
            <tr>
                <th style="width:30%;">Snippet</th>
                <th style="width:45%;">Description</th>
                <th style="width:15%;">Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($cpt_snippets as $snippet): ?>
                <?php $enabled = is_snippet_enabled($snippet['id']); ?>
                <tr>
                    <td>
                        <strong><?php echo esc_html($snippet['name']); ?></strong>
                        <?php if (!empty($snippet['info'])): ?>
                            <div style="font-size:11px;color:#6b7280;margin-top:4px;"><?php echo esc_html($snippet['info']); ?></div>
                        <?php endif; ?>
                        <div style="font-size:10px;color:#9ca3af;margin-top:2px;font-family:monospace;">
                            📁 snippets/<?php echo esc_html($snippet['file']); ?>
                        </div>
                    </td>
                    <td style="color:#666;"><?php echo esc_html($snippet['description']); ?></td>
                    <td><?php echo render_status_badge($enabled); ?></td>
                    <td>
                        <button type="button" 
                                class="sfpf-btn <?php echo $enabled ? 'sfpf-btn-secondary' : 'sfpf-btn-primary'; ?> sfpf-toggle-snippet"
                                data-snippet="<?php echo esc_attr($snippet['id']); ?>"
                                data-enabled="<?php echo $enabled ? '1' : '0'; ?>">
                            <?php echo $enabled ? 'Disable' : 'Enable'; ?>
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
            
            <?php if (empty($cpt_snippets)): ?>
                <tr>
                    <td colspan="4" style="text-align:center;color:#666;">No CPT snippets available.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="sfpf-card">
    <div class="sfpf-card-header">
        <span class="dashicons dashicons-welcome-widgets-menus" style="color:#059669;"></span>
        <h3>ACF Field Groups</h3>
    </div>
    
    <p style="color:#666;margin-bottom:20px;">Enable or disable ACF field groups for enhanced content management.</p>
    
    <table class="sfpf-table">
        <thead>
            <tr>
                <th style="width:30%;">Snippet</th>
                <th style="width:45%;">Description</th>
                <th style="width:15%;">Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($acf_snippets as $snippet): ?>
                <?php $enabled = is_snippet_enabled($snippet['id']); ?>
                <tr>
                    <td>
                        <strong><?php echo esc_html($snippet['name']); ?></strong>
                        <?php if (!empty($snippet['info'])): ?>
                            <div style="font-size:11px;color:#9ca3af;margin-top:4px;"><?php echo esc_html($snippet['info']); ?></div>
                        <?php endif; ?>
                    </td>
                    <td style="color:#666;"><?php echo esc_html($snippet['description']); ?></td>
                    <td><?php echo render_status_badge($enabled); ?></td>
                    <td>
                        <button type="button" 
                                class="sfpf-btn <?php echo $enabled ? 'sfpf-btn-secondary' : 'sfpf-btn-primary'; ?> sfpf-toggle-snippet"
                                data-snippet="<?php echo esc_attr($snippet['id']); ?>"
                                data-enabled="<?php echo $enabled ? '1' : '0'; ?>">
                            <?php echo $enabled ? 'Disable' : 'Enable'; ?>
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
            
            <?php if (empty($acf_snippets)): ?>
                <tr>
                    <td colspan="4" style="text-align:center;color:#666;">No ACF snippets available.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="sfpf-alert sfpf-alert-info">
    <strong>Note:</strong> After enabling or disabling snippets, you may need to refresh your permalinks by going to 
    <a href="<?php echo admin_url('options-permalink.php'); ?>">Settings → Permalinks</a> and clicking "Save Changes".
</div>

<script>
jQuery(document).ready(function($) {
    $('.sfpf-toggle-snippet').on('click', function() {
        var $btn = $(this);
        var snippetId = $btn.data('snippet');
        var currentlyEnabled = $btn.data('enabled') === 1;
        var newState = currentlyEnabled ? 0 : 1;
        
        $btn.prop('disabled', true).text('Processing...');
        
        $.post(ajaxurl, {
            action: 'sfpf_toggle_snippet',
            snippet_id: snippetId,
            enabled: newState,
            nonce: '<?php echo wp_create_nonce('sfpf_ajax'); ?>'
        }, function(response) {
            if (response.success) {
                location.reload();
            } else {
                $btn.prop('disabled', false).text(currentlyEnabled ? 'Disable' : 'Enable');
                alert('Error: ' + (response.data || 'Unknown error'));
            }
        });
    });
});
</script>
