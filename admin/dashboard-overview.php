<?php
namespace sfpf_person_website;

/**
 * Dashboard Overview Tab
 * 
 * @package sfpf_person_website
 * @since 1.0.0
 */

defined('ABSPATH') || exit;

// Get data
$founder = get_founder_full_info();
$company = get_company_full_info();
$hws_info = get_hws_base_tools_info();
$site_url = get_site_url_clean();

?>

<!-- Plugin Dependencies -->
<div class="sfpf-card">
    <div class="sfpf-card-header">
        <span class="dashicons dashicons-admin-plugins" style="color:#6366f1;"></span>
        <h3>Plugin Dependencies</h3>
    </div>
    
    <div style="display:flex;align-items:center;justify-content:space-between;padding:15px;background:#f9fafb;border-radius:6px;">
        <div style="display:flex;align-items:center;gap:12px;">
            <span class="dashicons dashicons-admin-tools" style="font-size:24px;color:#6366f1;"></span>
            <div>
                <strong style="font-size:15px;">HWS Base Tools</strong>
                <span style="color:#666;font-size:12px;margin-left:8px;">Required for website settings</span>
                <?php if ($hws_info['active']): ?>
                    <div style="margin-top:5px;font-size:13px;color:#666;">
                        Version: <?php echo esc_html($hws_info['version']); ?>
                    </div>
                    <?php if ($hws_info['author']): ?>
                    <div style="font-size:13px;color:#666;">
                        Author: <?php echo esc_html(strip_tags($hws_info['author'])); ?>
                    </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
        <?php if ($hws_info['active']): ?>
            <span style="background:#dcfce7;color:#166534;padding:4px 12px;border-radius:4px;font-size:12px;font-weight:600;">Active</span>
        <?php else: ?>
            <span style="background:#fef2f2;color:#dc2626;padding:4px 12px;border-radius:4px;font-size:12px;font-weight:600;">Not Active</span>
        <?php endif; ?>
    </div>
    
    <div style="margin-top:15px;display:flex;gap:10px;">
        <a href="<?php echo esc_url(get_hws_base_tools_url()); ?>" target="_blank" class="button button-secondary">
            Open HWS Base Tools →
        </a>
        <a href="<?php echo esc_url(get_website_settings_url()); ?>" target="_blank" class="button button-secondary">
            Website Settings →
        </a>
    </div>
</div>

<!-- Profile Cards -->
<div class="sfpf-grid-2">
    <!-- Founder/Person Profile -->
    <div class="sfpf-card">
        <div class="sfpf-card-header">
            <span class="dashicons dashicons-admin-users" style="color:#2563eb;"></span>
            <h3>Person Profile</h3>
        </div>
        
        <?php if ($founder): ?>
            <div class="sfpf-profile-card">
                <div class="sfpf-profile-avatar">
                    <img src="<?php echo esc_url($founder['avatar_url']); ?>" alt="">
                </div>
                <div class="sfpf-profile-info" style="flex:1;">
                    <h4><?php echo esc_html($founder['display_name']); ?></h4>
                    <?php if ($founder['job_title']): ?>
                        <p style="color:#6b7280;font-size:14px;margin:0 0 10px;"><?php echo esc_html($founder['job_title']); ?></p>
                    <?php endif; ?>
                    
                    <div class="sfpf-profile-meta">
                        <?php if ($founder['email']): ?>
                            <span><span class="dashicons dashicons-email" style="font-size:14px;color:#6b7280;"></span> <?php echo esc_html($founder['email']); ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (!empty($founder['urls'])): ?>
                        <div class="sfpf-url-list" style="margin-top:12px;font-size:13px;">
                            <?php foreach ($founder['urls'] as $platform => $url): if ($url): ?>
                                <div style="margin-bottom:6px;">
                                    <strong style="display:inline-block;width:100px;color:#4b5563;"><?php echo esc_html(ucfirst(str_replace('_', ' ', $platform))); ?>:</strong>
                                    <a href="<?php echo esc_url($url); ?>" target="_blank" style="color:#2563eb;word-break:break-all;"><?php echo esc_html($url); ?></a>
                                </div>
                            <?php endif; endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <div style="margin-top:15px;display:flex;gap:10px;">
                        <a href="<?php echo esc_url($founder['edit_url']); ?>" target="_blank" class="button button-secondary">Edit Profile</a>
                        <a href="<?php echo esc_url($founder['view_url']); ?>" target="_blank" class="button button-secondary">View Profile</a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="sfpf-alert sfpf-alert-warning">
                <strong>⚠ No user assigned</strong><br>
                Please assign a user in Website Settings.
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Company Info -->
    <div class="sfpf-card">
        <div class="sfpf-card-header">
            <span class="dashicons dashicons-building" style="color:#059669;"></span>
            <h3>Company Info</h3>
        </div>
        
        <?php if ($company): ?>
            <div class="sfpf-profile-card">
                <div class="sfpf-profile-avatar">
                    <img src="<?php echo esc_url($company['avatar_url']); ?>" alt="">
                </div>
                <div class="sfpf-profile-info" style="flex:1;">
                    <h4><?php echo esc_html($company['display_name']); ?></h4>
                    <div class="sfpf-profile-meta">
                        <?php if ($company['email']): ?>
                            <span><span class="dashicons dashicons-email" style="font-size:14px;color:#6b7280;"></span> <?php echo esc_html($company['email']); ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div style="margin-top:15px;display:flex;gap:10px;">
                        <a href="<?php echo esc_url($company['edit_url']); ?>" target="_blank" class="button button-secondary">Edit Profile</a>
                        <a href="<?php echo esc_url($company['view_url']); ?>" target="_blank" class="button button-secondary">View Profile</a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="sfpf-alert sfpf-alert-warning">
                <strong>⚠ Company not configured</strong>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Schema Validators -->
<div class="sfpf-card">
    <div class="sfpf-card-header">
        <span class="dashicons dashicons-yes-alt" style="color:#059669;"></span>
        <h3>Schema Validators</h3>
    </div>
    
    <div style="background:#f9fafb;border-radius:8px;padding:20px;">
        <h4 style="margin:0 0 10px 0;font-size:14px;">Home Page</h4>
        <div style="display:flex;gap:15px;flex-wrap:wrap;margin-bottom:10px;">
            <a href="<?php echo esc_url(get_schema_validator_url($site_url)); ?>" target="_blank" class="button button-primary">📋 Schema.org Validator</a>
            <a href="<?php echo esc_url(get_google_rich_results_url($site_url)); ?>" target="_blank" class="button button-secondary">🔍 Google Rich Results Test</a>
        </div>
        <div style="font-size:12px;color:#666;">
            <div><strong>Schema.org:</strong> <a href="<?php echo esc_url(get_schema_validator_url($site_url)); ?>" target="_blank"><?php echo esc_html(get_schema_validator_url($site_url)); ?></a></div>
            <div><strong>Google:</strong> <a href="<?php echo esc_url(get_google_rich_results_url($site_url)); ?>" target="_blank"><?php echo esc_html(get_google_rich_results_url($site_url)); ?></a></div>
        </div>
    </div>
</div>

<!-- System Checks -->
<div class="sfpf-card">
    <div class="sfpf-card-header">
        <span class="dashicons dashicons-shield" style="color:#f59e0b;"></span>
        <h3>System Checks</h3>
    </div>
    
    <table class="sfpf-table">
        <thead>
            <tr>
                <th style="width:40%;">Check</th>
                <th style="width:20%;">Status</th>
                <th>Source</th>
            </tr>
        </thead>
        <tbody>
            <tr><td>HWS Base Tools Plugin</td><td><?php echo render_status_badge($hws_info['active']); ?></td><td><?php echo render_external_badge(); ?></td></tr>
            <tr><td>Website Settings Snippet</td><td><?php echo render_status_badge(is_hws_snippet_enabled('hws_enable_website_settings')); ?></td><td><?php echo render_external_badge(); ?></td></tr>
            <tr><td>Website Settings Functionality</td><td><?php echo render_status_badge(is_hws_snippet_enabled('hws_enable_website_settings_functionality')); ?></td><td><?php echo render_external_badge(); ?></td></tr>
            <tr><td>Testimonial CPT</td><td><?php echo render_status_badge(is_hws_snippet_enabled('hws_enable_testimonial_cpt')); ?></td><td><?php echo render_external_badge(); ?></td></tr>
            <tr><td>Testimonial ACF Fields</td><td><?php echo render_status_badge(is_hws_snippet_enabled('hws_enable_testimonial_acf')); ?></td><td><?php echo render_external_badge(); ?></td></tr>
            <tr><td>Book CPT</td><td><?php echo render_status_badge(is_snippet_enabled('sfpf_enable_book_cpt')); ?></td><td>Internal</td></tr>
            <tr><td>Book ACF Fields</td><td><?php echo render_status_badge(is_snippet_enabled('sfpf_enable_book_acf')); ?></td><td>Internal</td></tr>
            <tr><td>Organization CPT</td><td><?php echo render_status_badge(is_snippet_enabled('sfpf_enable_organization_cpt')); ?></td><td>Internal</td></tr>
            <tr><td>Organization ACF Fields</td><td><?php echo render_status_badge(is_snippet_enabled('sfpf_enable_organization_acf')); ?></td><td>Internal</td></tr>
            <tr><td>User Schema ACF Fields</td><td><?php echo render_status_badge(is_snippet_enabled('sfpf_enable_user_schema_acf')); ?></td><td>Internal</td></tr>
            <tr><td>Testimonial CPT (Internal)</td><td><?php echo render_status_badge(is_snippet_enabled('sfpf_enable_testimonial_cpt')); ?></td><td>Internal</td></tr>
            <tr><td>Homepage ACF Fields</td><td><?php echo render_status_badge(is_snippet_enabled('sfpf_enable_homepage_acf')); ?></td><td>Internal</td></tr>
            <tr><td>Founder User Configured</td><td><?php echo render_status_badge($founder !== null); ?></td><td>Internal</td></tr>
            <tr><td>Company User Configured</td><td><?php echo render_status_badge($company !== null); ?></td><td>Internal</td></tr>
        </tbody>
    </table>
</div>

<!-- Pages Overview -->
<div class="sfpf-card">
    <div class="sfpf-card-header">
        <span class="dashicons dashicons-admin-page" style="color:#8b5cf6;"></span>
        <h3>Pages Overview</h3>
    </div>
    
    <?php $pages_structure = get_critical_pages_structure(); ?>
    
    <table class="sfpf-table">
        <thead><tr><th>Page</th><th>Status</th><th>Slug</th><th>Actions</th></tr></thead>
        <tbody>
            <?php foreach ($pages_structure as $page_key => $page): ?>
                <?php
                $page_id = get_option('sfpf_page_' . $page_key, 0);
                $page_obj = $page_id ? get_post($page_id) : null;
                $is_set = $page_obj && $page_obj->post_status === 'publish';
                ?>
                <tr>
                    <td><strong><?php echo esc_html($page['title']); ?></strong></td>
                    <td><?php echo render_status_badge($is_set, $is_set ? 'Set' : 'Not Set'); ?></td>
                    <td><code>/<?php echo $is_set ? esc_html($page_obj->post_name) : esc_html($page['slug']); ?>/</code><?php if (!$is_set): ?><span style="color:#999;font-size:11px;margin-left:5px;">(expected)</span><?php endif; ?></td>
                    <td><?php echo render_page_actions($page_id, $page_key, $is_set, $page, ''); ?></td>
                </tr>
                <?php if (!empty($page['children'])): foreach ($page['children'] as $child_key => $child):
                    $child_id = get_option('sfpf_page_' . $child_key, 0);
                    $child_obj = $child_id ? get_post($child_id) : null;
                    $child_is_set = $child_obj && $child_obj->post_status === 'publish';
                ?>
                <tr>
                    <td style="padding-left:35px;">— <?php echo esc_html($child['title']); ?></td>
                    <td><?php echo render_status_badge($child_is_set, $child_is_set ? 'Set' : 'Not Set'); ?></td>
                    <td><code>/<?php echo $child_is_set ? esc_html($child_obj->post_name) : esc_html($child['slug']); ?>/</code><?php if (!$child_is_set): ?><span style="color:#999;font-size:11px;margin-left:5px;">(expected)</span><?php endif; ?></td>
                    <td><?php echo render_page_actions($child_id, $child_key, $child_is_set, $child, $page_key); ?></td>
                </tr>
                <?php endforeach; endif; ?>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Available Shortcodes -->
<div class="sfpf-card">
    <div class="sfpf-card-header">
        <span class="dashicons dashicons-shortcode" style="color:#ec4899;"></span>
        <h3>Available Shortcodes</h3>
    </div>
    
    <?php $all_shortcodes = get_all_shortcodes(); ?>
    
    <table class="sfpf-table">
        <thead><tr><th style="width:45%;">Shortcode</th><th style="width:35%;">Description</th><th>Category</th></tr></thead>
        <tbody>
            <?php foreach ($all_shortcodes as $category => $shortcodes): foreach ($shortcodes as $sc): ?>
                <tr>
                    <td><code><?php echo esc_html($sc['shortcode']); ?></code></td>
                    <td><?php echo esc_html($sc['description']); ?></td>
                    <td><span style="display:inline-block;background:#f3f4f6;padding:3px 8px;border-radius:4px;font-size:11px;"><?php echo esc_html($category); ?></span></td>
                </tr>
            <?php endforeach; endforeach; ?>
        </tbody>
    </table>
</div>

<!-- FAQs Overview -->
<?php
$faq_sets = get_option('sfpf_faq_sets', []);
if (!empty($faq_sets)):
?>
<div class="sfpf-card">
    <div class="sfpf-card-header">
        <span class="dashicons dashicons-editor-help" style="color:#8b5cf6;"></span>
        <h3>FAQ Sets</h3>
        <span style="margin-left:auto;font-size:12px;"><a href="#faq" class="sfpf-tab-link" data-tab="faq">Manage FAQs →</a></span>
    </div>
    
    <div style="display:flex;flex-direction:column;gap:15px;">
        <?php foreach ($faq_sets as $set): if (!empty($set['name'])): ?>
            <div style="background:#f9fafb;border-radius:6px;padding:15px;border:1px solid #e5e7eb;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
                    <strong style="font-size:14px;"><?php echo esc_html($set['name']); ?></strong>
                    <span style="background:#dbeafe;color:#1d4ed8;padding:3px 8px;border-radius:4px;font-size:11px;">
                        <?php echo count($set['items'] ?? []); ?> items
                    </span>
                </div>
                <div style="font-size:12px;color:#6b7280;margin-bottom:10px;">
                    <?php 
                    $items = $set['items'] ?? [];
                    foreach (array_slice($items, 0, 3) as $item) {
                        if (!empty($item['question'])) {
                            echo '• ' . esc_html(substr($item['question'], 0, 60)) . (strlen($item['question']) > 60 ? '...' : '') . '<br>';
                        }
                    }
                    if (count($items) > 3) {
                        echo '<em>+ ' . (count($items) - 3) . ' more...</em>';
                    }
                    ?>
                </div>
                <code style="background:#e8f4fc;padding:3px 8px;border-radius:3px;font-size:11px;">[sfpf_faq set="<?php echo esc_attr($set['slug']); ?>"]</code>
            </div>
        <?php endif; endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- SFPF Plugin Info Section -->
<?php sfpf_display_plugin_info(); ?>

<!-- Page Actions AJAX Script -->
<script>
jQuery(document).ready(function($) {
    // Create page AJAX handler
    $('.sfpf-create-page').on('click', function(e) {
        e.preventDefault();
        
        var $btn = $(this);
        var pageKey = $btn.data('page');
        var title = $btn.data('title');
        var slug = $btn.data('slug');
        var parentKey = $btn.data('parent') || '';
        
        $btn.prop('disabled', true).text('Creating...');
        
        $.post(ajaxurl, {
            action: 'sfpf_create_page',
            nonce: '<?php echo wp_create_nonce('sfpf_ajax'); ?>',
            page_key: pageKey,
            title: title,
            slug: slug,
            parent_key: parentKey
        }, function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert('Error: ' + (response.data || 'Unknown error'));
                $btn.prop('disabled', false).text('+ Create');
            }
        }).fail(function() {
            alert('AJAX request failed');
            $btn.prop('disabled', false).text('+ Create');
        });
    });
    
    // Apply Template AJAX handler - use .off() to prevent duplicate bindings
    $(document).off('click', '.sfpf-apply-template').on('click', '.sfpf-apply-template', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        var $btn = $(this);
        
        // Prevent double-click
        if ($btn.data('processing')) {
            return false;
        }
        
        var pageId = $btn.data('page-id');
        var pageKey = $btn.data('page-key');
        
        $btn.data('processing', true).prop('disabled', true).text('Applying...');
        
        $.post(ajaxurl, {
            action: 'sfpf_apply_default_template',
            nonce: '<?php echo wp_create_nonce('sfpf_ajax'); ?>',
            page_id: pageId,
            page_key: pageKey,
            force: 'false'
        }, function(response) {
            if (response.success) {
                // Success toast
                var $notice = $('<div style="position:fixed;top:50px;right:20px;z-index:9999;padding:12px 20px;background:#dcfce7;border:1px solid #16a34a;border-radius:6px;box-shadow:0 4px 12px rgba(0,0,0,0.15);"><p style="margin:0;">✅ Template applied!</p></div>');
                $('body').append($notice);
                setTimeout(function() { $notice.fadeOut(function() { $(this).remove(); }); }, 3000);
                $btn.data('processing', false).prop('disabled', false).text('Apply Template');
            } else {
                if (response.data && response.data.code === 'has_content') {
                    // Show custom confirm dialog - don't use native confirm()
                    var doOverwrite = window.confirm('Page already has content. Overwrite with default template?');
                    
                    if (doOverwrite) {
                        $.post(ajaxurl, {
                            action: 'sfpf_apply_default_template',
                            nonce: '<?php echo wp_create_nonce('sfpf_ajax'); ?>',
                            page_id: pageId,
                            page_key: pageKey,
                            force: 'true'
                        }, function(resp) {
                            if (resp.success) {
                                var $notice = $('<div style="position:fixed;top:50px;right:20px;z-index:9999;padding:12px 20px;background:#dcfce7;border:1px solid #16a34a;border-radius:6px;box-shadow:0 4px 12px rgba(0,0,0,0.15);"><p style="margin:0;">✅ Template applied!</p></div>');
                                $('body').append($notice);
                                setTimeout(function() { $notice.fadeOut(function() { $(this).remove(); }); }, 3000);
                            } else {
                                var $notice = $('<div style="position:fixed;top:50px;right:20px;z-index:9999;padding:12px 20px;background:#fef2f2;border:1px solid #dc2626;border-radius:6px;box-shadow:0 4px 12px rgba(0,0,0,0.15);"><p style="margin:0;">❌ Error: ' + (resp.data.message || resp.data || 'Unknown error') + '</p></div>');
                                $('body').append($notice);
                                setTimeout(function() { $notice.fadeOut(function() { $(this).remove(); }); }, 5000);
                            }
                            $btn.data('processing', false).prop('disabled', false).text('Apply Template');
                        });
                    } else {
                        // User cancelled
                        $btn.data('processing', false).prop('disabled', false).text('Apply Template');
                    }
                } else {
                    var $notice = $('<div style="position:fixed;top:50px;right:20px;z-index:9999;padding:12px 20px;background:#fef2f2;border:1px solid #dc2626;border-radius:6px;box-shadow:0 4px 12px rgba(0,0,0,0.15);"><p style="margin:0;">❌ Error: ' + (response.data.message || response.data || 'Unknown error') + '</p></div>');
                    $('body').append($notice);
                    setTimeout(function() { $notice.fadeOut(function() { $(this).remove(); }); }, 5000);
                    $btn.data('processing', false).prop('disabled', false).text('Apply Template');
                }
            }
        }).fail(function() {
            var $notice = $('<div style="position:fixed;top:50px;right:20px;z-index:9999;padding:12px 20px;background:#fef2f2;border:1px solid #dc2626;border-radius:6px;box-shadow:0 4px 12px rgba(0,0,0,0.15);"><p style="margin:0;">❌ AJAX request failed</p></div>');
            $('body').append($notice);
            setTimeout(function() { $notice.fadeOut(function() { $(this).remove(); }); }, 5000);
            $btn.data('processing', false).prop('disabled', false).text('Apply Template');
        });
        
        return false;
    });
});
</script>
