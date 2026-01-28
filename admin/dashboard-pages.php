<?php
namespace sfpf_person_website;

/**
 * Dashboard Pages Tab
 * 
 * @package sfpf_person_website
 * @since 1.0.0
 */

defined('ABSPATH') || exit;

$pages_structure = get_critical_pages_structure();

// Get all pages for dropdown
$all_pages = get_posts([
    'post_type' => 'page',
    'posts_per_page' => -1,
    'post_status' => 'publish',
    'orderby' => 'title',
    'order' => 'ASC',
]);

?>

<div class="sfpf-card">
    <div class="sfpf-card-header">
        <span class="dashicons dashicons-admin-page" style="color:#8b5cf6;"></span>
        <h3>Critical Pages</h3>
    </div>
    
    <p style="color:#666;margin-bottom:20px;">
        Assign or create pages for the personal website structure. Pages maintain hierarchy (Biography → Education, etc.)
    </p>
    
    <table class="sfpf-table">
        <thead>
            <tr>
                <th style="width:25%;">Page</th>
                <th style="width:30%;">Assign Page</th>
                <th style="width:15%;">Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($pages_structure as $page_key => $page): ?>
                <?php 
                $page_id = get_option('sfpf_page_' . $page_key, 0);
                $page_obj = $page_id ? get_post($page_id) : null;
                $is_set = $page_obj && $page_obj->post_status === 'publish';
                ?>
                <tr style="background:#f9fafb;">
                    <td>
                        <strong><?php echo esc_html($page['title']); ?></strong>
                        <div style="margin-top:3px;"><code style="font-size:11px;background:#e5e7eb;padding:2px 6px;border-radius:3px;"><?php echo esc_html($page['slug']); ?></code></div>
                    </td>
                    <td>
                        <select class="sfpf-page-select" data-page="<?php echo esc_attr($page_key); ?>" style="width:100%;max-width:250px;">
                            <option value="">— Select Page —</option>
                            <?php foreach ($all_pages as $p): ?>
                                <option value="<?php echo esc_attr($p->ID); ?>" <?php selected($page_id, $p->ID); ?>>
                                    <?php echo esc_html($p->post_title); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td><?php echo render_status_badge($is_set, $is_set ? 'Set' : 'Not Set'); ?></td>
                    <td><?php echo render_page_actions($page_id, $page_key, $is_set, $page, ''); ?></td>
                </tr>
                
                <?php if (!empty($page['children'])): ?>
                    <?php foreach ($page['children'] as $child_key => $child): ?>
                        <?php 
                        $child_id = get_option('sfpf_page_' . $child_key, 0);
                        $child_obj = $child_id ? get_post($child_id) : null;
                        $child_is_set = $child_obj && $child_obj->post_status === 'publish';
                        ?>
                        <tr>
                            <td style="padding-left:30px;">
                                <span style="color:#9ca3af;margin-right:8px;">└─</span>
                                <strong><?php echo esc_html($child['title']); ?></strong>
                                <div style="margin-top:3px;margin-left:22px;"><code style="font-size:11px;background:#e5e7eb;padding:2px 6px;border-radius:3px;"><?php echo esc_html($child['slug']); ?></code></div>
                            </td>
                            <td>
                                <select class="sfpf-page-select" data-page="<?php echo esc_attr($child_key); ?>" data-parent="<?php echo esc_attr($page_key); ?>" style="width:100%;max-width:250px;">
                                    <option value="">— Select Page —</option>
                                    <?php foreach ($all_pages as $p): ?>
                                        <option value="<?php echo esc_attr($p->ID); ?>" <?php selected($child_id, $p->ID); ?>>
                                            <?php echo esc_html($p->post_title); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td><?php echo render_status_badge($child_is_set, $child_is_set ? 'Set' : 'Not Set'); ?></td>
                            <td><?php echo render_page_actions($child_id, $child_key, $child_is_set, $child, $page_key); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
jQuery(document).ready(function($) {
    // Handle page assignment changes
    $('.sfpf-page-select').on('change', function() {
        var pageKey = $(this).data('page');
        var pageId = $(this).val();
        var parentKey = $(this).data('parent') || '';
        var $select = $(this);
        
        $select.prop('disabled', true);
        
        $.post(ajaxurl, {
            action: 'sfpf_assign_page',
            page_key: pageKey,
            page_id: pageId,
            parent_key: parentKey,
            nonce: '<?php echo wp_create_nonce('sfpf_ajax'); ?>'
        }, function(response) {
            $select.prop('disabled', false);
            if (response.success) {
                location.reload();
            } else {
                alert('Error: ' + (response.data || 'Failed to assign page'));
            }
        });
    });
    
    // Handle page creation - uses sfpf-create-page class from helper function
    $('.sfpf-create-page').on('click', function() {
        var pageKey = $(this).data('page');
        var title = $(this).data('title');
        var slug = $(this).data('slug');
        var parentKey = $(this).data('parent') || '';
        var $btn = $(this);
        
        $btn.prop('disabled', true).text('Creating...');
        
        $.post(ajaxurl, {
            action: 'sfpf_create_page',
            page_key: pageKey,
            title: title,
            slug: slug,
            parent_key: parentKey,
            nonce: '<?php echo wp_create_nonce('sfpf_ajax'); ?>'
        }, function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert('Error: ' + (response.data || 'Failed to create page'));
                $btn.prop('disabled', false).text('+ Create');
            }
        });
    });
    
    // Apply Template handler
    $('.sfpf-apply-template').on('click', function(e) {
        e.preventDefault();
        
        var $btn = $(this);
        var pageId = $btn.data('page-id');
        var pageKey = $btn.data('page-key');
        
        $btn.prop('disabled', true).text('Applying...');
        
        $.post(ajaxurl, {
            action: 'sfpf_apply_default_template',
            nonce: '<?php echo wp_create_nonce('sfpf_ajax'); ?>',
            page_id: pageId,
            page_key: pageKey,
            force: 'false'
        }, function(response) {
            if (response.success) {
                alert('Template applied successfully!');
                $btn.prop('disabled', false).text('Apply Template');
            } else {
                if (response.data && response.data.code === 'has_content') {
                    if (confirm('Page already has content. Overwrite with default template?')) {
                        $.post(ajaxurl, {
                            action: 'sfpf_apply_default_template',
                            nonce: '<?php echo wp_create_nonce('sfpf_ajax'); ?>',
                            page_id: pageId,
                            page_key: pageKey,
                            force: 'true'
                        }, function(resp) {
                            if (resp.success) {
                                alert('Template applied successfully!');
                            } else {
                                alert('Error: ' + (resp.data.message || resp.data || 'Unknown error'));
                            }
                            $btn.prop('disabled', false).text('Apply Template');
                        });
                    } else {
                        $btn.prop('disabled', false).text('Apply Template');
                    }
                } else {
                    alert('Error: ' + (response.data.message || response.data || 'Unknown error'));
                    $btn.prop('disabled', false).text('Apply Template');
                }
            }
        }).fail(function() {
            alert('AJAX request failed');
            $btn.prop('disabled', false).text('Apply Template');
        });
    });
});
</script>
