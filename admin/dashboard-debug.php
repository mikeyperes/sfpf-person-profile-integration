<?php
namespace sfpf_person_website;

/**
 * Dashboard Debug Tab
 * 
 * Run diagnostic scripts and view debug output.
 * 
 * @package sfpf_person_website
 * @since 1.3.11
 */

defined('ABSPATH') || exit;

$founder_user_id = get_founder_user_id();
?>

<div class="sfpf-card">
    <div class="sfpf-card-header">
        <span class="dashicons dashicons-admin-tools" style="color:#dc2626;"></span>
        <h3>Debug Tools</h3>
    </div>
    <p style="color:#666;">Run diagnostic scripts to troubleshoot issues. Share the output with support.</p>
</div>

<!-- System Info -->
<div class="sfpf-card">
    <div class="sfpf-card-header">
        <span class="dashicons dashicons-info" style="color:#2563eb;"></span>
        <h3>System Information</h3>
    </div>
    
    <table class="sfpf-table" style="font-size:13px;">
        <tr><td style="width:200px;"><strong>WordPress Version</strong></td><td><?php echo get_bloginfo('version'); ?></td></tr>
        <tr><td><strong>PHP Version</strong></td><td><?php echo PHP_VERSION; ?></td></tr>
        <tr><td><strong>Plugin Version</strong></td><td><?php echo SFPF_PLUGIN_VERSION; ?></td></tr>
        <tr><td><strong>ACF Version</strong></td><td><?php echo defined('ACF_VERSION') ? ACF_VERSION : 'Not Active'; ?></td></tr>
        <tr><td><strong>RankMath Active</strong></td><td><?php echo is_plugin_active('seo-by-rank-math/rank-math.php') ? '✅ Yes' : '❌ No'; ?></td></tr>
        <tr><td><strong>Elementor Active</strong></td><td><?php echo is_plugin_active('elementor/elementor.php') ? '✅ Yes' : '❌ No'; ?></td></tr>
        <tr><td><strong>HWS Base Tools</strong></td><td><?php echo is_hws_base_tools_active() ? '✅ Active' : '❌ Not Active'; ?></td></tr>
        <tr><td><strong>Homepage ID</strong></td><td><?php $hp = get_front_page_id(); echo $hp ?: 'Not Set'; ?></td></tr>
        <tr><td><strong>Homepage URL</strong></td><td><a href="<?php echo home_url('/'); ?>" target="_blank"><?php echo home_url('/'); ?></a></td></tr>
        <tr><td><strong>Founder User ID</strong></td><td><?php echo $founder_user_id ?: 'Not Set'; ?></td></tr>
    </table>
</div>

<!-- Schema Debug -->
<div class="sfpf-card">
    <div class="sfpf-card-header">
        <span class="dashicons dashicons-media-code" style="color:#8b5cf6;"></span>
        <h3>Schema Debug</h3>
    </div>
    
    <div style="display:flex;flex-wrap:wrap;gap:10px;margin-bottom:15px;">
        <button type="button" class="button sfpf-debug-btn" data-action="check_homepage_schema">Check Homepage Schema</button>
        <button type="button" class="button sfpf-debug-btn" data-action="check_founder_data">Check Founder Data</button>
        <button type="button" class="button sfpf-debug-btn" data-action="check_injection_hook">Check Schema Injection Hook</button>
        <button type="button" class="button sfpf-debug-btn" data-action="test_schema_build">Test Schema Build</button>
    </div>
    
    <div id="sfpf-debug-schema-output" style="background:#1e1e2e;border-radius:6px;padding:15px;font-family:monospace;font-size:12px;color:#cdd6f4;min-height:150px;max-height:500px;overflow-y:auto;white-space:pre-wrap;">
Click a button above to run debug...
    </div>
</div>

<!-- Elementor Debug -->
<div class="sfpf-card">
    <div class="sfpf-card-header">
        <span class="dashicons dashicons-editor-code" style="color:#f59e0b;"></span>
        <h3>Elementor Loop Debug</h3>
    </div>
    
    <div style="display:flex;flex-wrap:wrap;gap:10px;margin-bottom:15px;">
        <button type="button" class="button sfpf-debug-btn" data-action="check_elementor_templates">List Elementor Templates</button>
        <button type="button" class="button sfpf-debug-btn" data-action="check_loop_items">Check Loop Items</button>
        <button type="button" class="button sfpf-debug-btn" data-action="check_template_meta">Check Template Metadata</button>
        <button type="button" class="button sfpf-debug-btn" data-action="repair_elementor_templates">🔧 Repair Templates</button>
    </div>
    
    <div id="sfpf-debug-elementor-output" style="background:#1e1e2e;border-radius:6px;padding:15px;font-family:monospace;font-size:12px;color:#cdd6f4;min-height:150px;max-height:500px;overflow-y:auto;white-space:pre-wrap;">
Click a button above to run debug...
    </div>
</div>

<!-- ACF Debug -->
<div class="sfpf-card">
    <div class="sfpf-card-header">
        <span class="dashicons dashicons-database" style="color:#10b981;"></span>
        <h3>ACF / User Debug</h3>
    </div>
    
    <div style="display:flex;flex-wrap:wrap;gap:10px;margin-bottom:15px;">
        <button type="button" class="button sfpf-debug-btn" data-action="check_professions">Check Professions Field</button>
        <button type="button" class="button sfpf-debug-btn" data-action="check_user_meta">Check User Meta</button>
        <button type="button" class="button sfpf-debug-btn" data-action="list_acf_fields">List ACF Fields for User</button>
    </div>
    
    <div id="sfpf-debug-acf-output" style="background:#1e1e2e;border-radius:6px;padding:15px;font-family:monospace;font-size:12px;color:#cdd6f4;min-height:150px;max-height:500px;overflow-y:auto;white-space:pre-wrap;">
Click a button above to run debug...
    </div>
</div>

<!-- Custom Debug Script -->
<div class="sfpf-card">
    <div class="sfpf-card-header">
        <span class="dashicons dashicons-editor-paste-text" style="color:#ec4899;"></span>
        <h3>Custom Debug Script</h3>
    </div>
    
    <p style="color:#666;margin-bottom:10px;">Enter a custom PHP debug script to execute:</p>
    
    <textarea id="sfpf-custom-debug-script" style="width:100%;height:150px;font-family:monospace;font-size:12px;background:#f9fafb;border:1px solid #d1d5db;border-radius:4px;padding:10px;" placeholder="// Example:
$founder_id = \sfpf_person_website\get_founder_user_id();
echo 'Founder ID: ' . $founder_id . PHP_EOL;
$profs = get_field('professions', 'user_' . $founder_id);
print_r($profs);"></textarea>
    
    <div style="margin-top:10px;display:flex;gap:10px;">
        <button type="button" class="button button-primary" id="sfpf-run-custom-debug">▶ Run Debug Script</button>
        <span style="color:#666;font-size:12px;line-height:28px;">⚠️ Only run scripts you understand</span>
    </div>
    
    <div id="sfpf-debug-custom-output" style="margin-top:15px;background:#1e1e2e;border-radius:6px;padding:15px;font-family:monospace;font-size:12px;color:#cdd6f4;min-height:100px;max-height:500px;overflow-y:auto;white-space:pre-wrap;display:none;">
    </div>
</div>

<!-- Export Debug Log -->
<div class="sfpf-card">
    <div class="sfpf-card-header">
        <span class="dashicons dashicons-download" style="color:#0ea5e9;"></span>
        <h3>Export Debug Data</h3>
    </div>
    
    <button type="button" class="button button-secondary" id="sfpf-export-debug">📋 Copy Full Debug Report to Clipboard</button>
</div>

<script>
jQuery(document).ready(function($) {
    // Debug buttons
    $('.sfpf-debug-btn').on('click', function() {
        var action = $(this).data('action');
        var $btn = $(this);
        var $output;
        
        // Determine output area
        if (['check_homepage_schema', 'check_founder_data', 'check_injection_hook', 'test_schema_build'].includes(action)) {
            $output = $('#sfpf-debug-schema-output');
        } else if (['check_elementor_templates', 'check_loop_items', 'check_template_meta'].includes(action)) {
            $output = $('#sfpf-debug-elementor-output');
        } else {
            $output = $('#sfpf-debug-acf-output');
        }
        
        $btn.prop('disabled', true);
        $output.html('<span style="color:#fbbf24;">Running debug...</span>');
        
        $.post(ajaxurl, {
            action: 'sfpf_run_debug',
            debug_action: action,
            nonce: '<?php echo wp_create_nonce('sfpf_ajax'); ?>'
        }, function(response) {
            $btn.prop('disabled', false);
            if (response.success) {
                $output.html(response.data.output);
            } else {
                $output.html('<span style="color:#f87171;">Error: ' + (response.data || 'Unknown error') + '</span>');
            }
        }).fail(function() {
            $btn.prop('disabled', false);
            $output.html('<span style="color:#f87171;">AJAX request failed</span>');
        });
    });
    
    // Custom debug script
    $('#sfpf-run-custom-debug').on('click', function() {
        var script = $('#sfpf-custom-debug-script').val();
        var $btn = $(this);
        var $output = $('#sfpf-debug-custom-output');
        
        if (!script.trim()) {
            alert('Please enter a debug script');
            return;
        }
        
        $btn.prop('disabled', true).text('Running...');
        $output.show().html('<span style="color:#fbbf24;">Executing script...</span>');
        
        $.post(ajaxurl, {
            action: 'sfpf_run_custom_debug',
            script: script,
            nonce: '<?php echo wp_create_nonce('sfpf_ajax'); ?>'
        }, function(response) {
            $btn.prop('disabled', false).text('▶ Run Debug Script');
            if (response.success) {
                $output.html(response.data.output);
            } else {
                $output.html('<span style="color:#f87171;">Error: ' + (response.data || 'Unknown error') + '</span>');
            }
        }).fail(function() {
            $btn.prop('disabled', false).text('▶ Run Debug Script');
            $output.html('<span style="color:#f87171;">AJAX request failed</span>');
        });
    });
    
    // Export debug report
    $('#sfpf-export-debug').on('click', function() {
        var $btn = $(this);
        $btn.prop('disabled', true).text('Generating...');
        
        $.post(ajaxurl, {
            action: 'sfpf_export_debug_report',
            nonce: '<?php echo wp_create_nonce('sfpf_ajax'); ?>'
        }, function(response) {
            $btn.prop('disabled', false).text('📋 Copy Full Debug Report to Clipboard');
            if (response.success) {
                navigator.clipboard.writeText(response.data.report).then(function() {
                    alert('Debug report copied to clipboard!');
                });
            } else {
                alert('Error: ' + (response.data || 'Unknown error'));
            }
        });
    });
});
</script>
