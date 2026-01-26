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
    
    <div class="sfpf-pages-grid">
        <?php foreach ($pages_structure as $page_key => $page): ?>
            <?php $page_id = get_option('sfpf_page_' . $page_key, 0); ?>
            
            <!-- Parent Page Row -->
            <div class="sfpf-page-row sfpf-page-row-parent">
                <div class="sfpf-page-info">
                    <div class="sfpf-page-title"><?php echo esc_html($page['title']); ?></div>
                    <div class="sfpf-page-slug"><code><?php echo esc_html($page['slug']); ?></code></div>
                </div>
                <div class="sfpf-page-controls">
                    <select name="sfpf_page_<?php echo esc_attr($page_key); ?>" class="sfpf-page-select" data-page="<?php echo esc_attr($page_key); ?>">
                        <option value="">— Select Page —</option>
                        <?php foreach ($all_pages as $p): ?>
                            <option value="<?php echo esc_attr($p->ID); ?>" <?php selected($page_id, $p->ID); ?>>
                                <?php echo esc_html($p->post_title); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="button" class="button button-primary sfpf-create-page-btn" data-page="<?php echo esc_attr($page_key); ?>" data-title="<?php echo esc_attr($page['title']); ?>" data-slug="<?php echo esc_attr($page['slug']); ?>">
                        + Create
                    </button>
                </div>
            </div>
            
            <?php if (!empty($page['children'])): ?>
                <?php foreach ($page['children'] as $child_key => $child): ?>
                    <?php $child_id = get_option('sfpf_page_' . $child_key, 0); ?>
                    
                    <!-- Child Page Row -->
                    <div class="sfpf-page-row sfpf-page-row-child">
                        <div class="sfpf-page-info">
                            <div class="sfpf-page-title">
                                <span class="sfpf-page-indent">└─</span>
                                <?php echo esc_html($child['title']); ?>
                            </div>
                            <div class="sfpf-page-slug"><code><?php echo esc_html($child['slug']); ?></code></div>
                        </div>
                        <div class="sfpf-page-controls">
                            <select name="sfpf_page_<?php echo esc_attr($child_key); ?>" class="sfpf-page-select" data-page="<?php echo esc_attr($child_key); ?>" data-parent="<?php echo esc_attr($page_key); ?>">
                                <option value="">— Select Page —</option>
                                <?php foreach ($all_pages as $p): ?>
                                    <option value="<?php echo esc_attr($p->ID); ?>" <?php selected($child_id, $p->ID); ?>>
                                        <?php echo esc_html($p->post_title); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="button" class="button button-primary sfpf-create-page-btn" data-page="<?php echo esc_attr($child_key); ?>" data-title="<?php echo esc_attr($child['title']); ?>" data-slug="<?php echo esc_attr($child['slug']); ?>" data-parent="<?php echo esc_attr($page_key); ?>">
                                + Create
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
</div>

<style>
.sfpf-pages-grid {
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    overflow: hidden;
    max-width: 900px;
}

.sfpf-page-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 20px;
    border-bottom: 1px solid #e5e7eb;
    gap: 20px;
}

.sfpf-page-row:last-child {
    border-bottom: none;
}

.sfpf-page-row-parent {
    background: #f9fafb;
}

.sfpf-page-row-child {
    background: #fff;
    padding-left: 40px;
}

.sfpf-page-info {
    display: flex;
    align-items: center;
    gap: 15px;
    min-width: 280px;
}

.sfpf-page-title {
    font-weight: 500;
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.sfpf-page-indent {
    color: #9ca3af;
    font-family: monospace;
}

.sfpf-page-slug code {
    background: #e5e7eb;
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 11px;
    color: #4b5563;
}

.sfpf-page-controls {
    display: flex;
    align-items: center;
    gap: 10px;
}

.sfpf-page-select {
    width: 200px;
    padding: 6px 10px;
    border: 1px solid #d1d5db;
    border-radius: 4px;
    font-size: 13px;
}

.sfpf-create-page-btn {
    white-space: nowrap;
    padding: 6px 12px !important;
    font-size: 12px !important;
}
</style>

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
                // Reload to show updated hierarchy
                location.reload();
            } else {
                alert('Error: ' + (response.data || 'Failed to assign page'));
            }
        });
    });
    
    // Handle page creation
    $('.sfpf-create-page-btn').on('click', function() {
        var pageKey = $(this).data('page');
        var title = $(this).data('title');
        var slug = $(this).data('slug');
        var parentKey = $(this).data('parent') || '';
        var $btn = $(this);
        
        if (!confirm('Create page "' + title + '" with slug "' + slug + '"?')) return;
        
        $btn.prop('disabled', true).text('Creating...');
        
        $.post(ajaxurl, {
            action: 'sfpf_create_page',
            page_key: pageKey,
            title: title,
            slug: slug,
            parent_key: parentKey,
            nonce: '<?php echo wp_create_nonce('sfpf_ajax'); ?>'
        }, function(response) {
            $btn.prop('disabled', false).text('+ Create');
            
            if (response.success && response.data.page_id) {
                alert('Page created successfully!');
                location.reload();
            } else {
                alert('Error: ' + (response.data || 'Failed to create page'));
            }
        });
    });
});
</script>
