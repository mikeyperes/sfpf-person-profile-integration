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
    
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:15px;">
        <div>
            <strong style="font-size:15px;">HWS Base Tools</strong>
            <span style="color:#666;margin-left:10px;">Required for website settings</span>
            <?php if ($hws_info['active']): ?>
                <div style="margin-top:8px;display:flex;gap:20px;font-size:13px;color:#666;">
                    <span><strong>Version:</strong> <?php echo esc_html($hws_info['version']); ?></span>
                    <?php if ($hws_info['author']): ?>
                        <span><strong>Author:</strong> <?php echo esc_html(strip_tags($hws_info['author'])); ?></span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        <div style="display:flex;gap:10px;">
            <a href="<?php echo esc_url(get_hws_base_tools_url()); ?>" target="_blank" class="sfpf-btn sfpf-btn-secondary">
                Open HWS Base Tools →
            </a>
            <a href="<?php echo esc_url(get_website_settings_url()); ?>" target="_blank" class="sfpf-btn sfpf-btn-secondary">
                Website Settings →
            </a>
        </div>
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
                        <div class="sfpf-url-chips">
                            <?php foreach ($founder['urls'] as $platform => $url): if ($url): ?>
                                <a href="<?php echo esc_url($url); ?>" target="_blank" class="sfpf-url-chip"><?php echo esc_html(ucfirst($platform)); ?></a>
                            <?php endif; endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <div style="margin-top:15px;display:flex;gap:10px;">
                        <a href="<?php echo esc_url($founder['edit_url']); ?>" class="button button-secondary">Edit Profile</a>
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
                        <a href="<?php echo esc_url($company['edit_url']); ?>" class="button button-secondary">Edit Profile</a>
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
                    <td><?php if ($is_set): ?><a href="<?php echo get_edit_post_link($page_id); ?>" class="button button-small">Edit</a><?php else: ?><button class="button button-small button-primary sfpf-create-page" data-page="<?php echo esc_attr($page_key); ?>" data-title="<?php echo esc_attr($page['title']); ?>" data-slug="<?php echo esc_attr($page['slug']); ?>">+ Create</button><?php endif; ?></td>
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
                    <td><?php if ($child_is_set): ?><a href="<?php echo get_edit_post_link($child_id); ?>" class="button button-small">Edit</a><?php else: ?><button class="button button-small button-primary sfpf-create-page" data-page="<?php echo esc_attr($child_key); ?>" data-title="<?php echo esc_attr($child['title']); ?>" data-slug="<?php echo esc_attr($child['slug']); ?>" data-parent="<?php echo esc_attr($page_key); ?>">+ Create</button><?php endif; ?></td>
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

<!-- SFPF Plugin Info Section -->
<?php sfpf_display_plugin_info(); ?>

<!-- Create Page AJAX Script -->
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
});
</script>
