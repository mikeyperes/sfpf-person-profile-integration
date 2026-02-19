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

<!-- Founder Professions Section -->
<?php 
$founder_user_id = get_founder_user_id();
$entity_type = $founder_user_id ? get_field('entity_type', 'user_' . $founder_user_id) : '';
$professions = ($entity_type === 'person' && $founder_user_id) ? get_field('professions', 'user_' . $founder_user_id) : [];

// Debug: check the raw professions data
$debug_professions = false; // Set to true to debug
if ($debug_professions && !empty($professions)) {
    echo '<pre style="background:#f0f0f0;padding:10px;font-size:11px;max-height:200px;overflow:auto;">';
    echo 'Professions data: ' . print_r($professions, true);
    echo '</pre>';
}

if ($entity_type === 'person'): 
?>
<div class="sfpf-card">
    <div class="sfpf-card-header">
        <span class="dashicons dashicons-businessman" style="color:#f59e0b;"></span>
        <h3>Founder Professions</h3>
        <span style="margin-left:auto;font-size:12px;color:#666;">
            <a href="<?php echo admin_url('user-edit.php?user_id=' . $founder_user_id . '#acf-group_sfpf_user_schema_structures'); ?>" target="_blank">Edit in Profile →</a>
        </span>
    </div>
    
    <p style="color:#666;margin-bottom:15px;">
        Professions defined for the founder. Each can be linked to a page.
    </p>
    
    <?php if (!empty($professions) && is_array($professions)): ?>
    <table class="sfpf-table">
        <thead>
            <tr>
                <th>Profession</th>
                <th>Linked Page</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($professions as $idx => $prof): 
                // Try multiple possible field names for profession name
                $prof_name = '';
                if (is_array($prof)) {
                    $prof_name = $prof['name'] ?? $prof['profession'] ?? $prof['title'] ?? '';
                } elseif (is_string($prof)) {
                    $prof_name = $prof;
                }
                
                // Skip completely empty entries
                if (empty($prof_name) && (is_array($prof) && empty($prof['page']))) {
                    continue;
                }
                
                $linked_page = is_array($prof) ? ($prof['page'] ?? null) : null;
            ?>
            <tr>
                <td><strong><?php echo esc_html($prof_name ?: 'Untitled'); ?></strong></td>
                <td>
                    <?php 
                    if ($linked_page):
                        $page_id = is_array($linked_page) ? ($linked_page['ID'] ?? $linked_page) : $linked_page;
                        $page = get_post($page_id);
                        if ($page):
                    ?>
                        <a href="<?php echo esc_url(get_permalink($page_id)); ?>" target="_blank"><?php echo esc_html($page->post_title); ?></a>
                    <?php else: ?>
                        <span style="color:#dc2626;">Page not found</span>
                    <?php endif; ?>
                    <?php else: ?>
                        <span style="color:#9ca3af;">— Not linked —</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($linked_page): 
                        $page_id = is_array($linked_page) ? ($linked_page['ID'] ?? $linked_page) : $linked_page;
                    ?>
                        <a href="<?php echo esc_url(get_edit_post_link($page_id)); ?>" target="_blank" class="button button-small">Edit Page</a>
                    <?php else: ?>
                        <button type="button" class="button button-small sfpf-create-profession-page" 
                                data-profession="<?php echo esc_attr($prof_name); ?>"
                                data-index="<?php echo esc_attr($idx); ?>">
                            + Create Page
                        </button>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
    <div class="sfpf-alert sfpf-alert-info">
        <strong>No professions defined.</strong><br>
        Add professions in the founder's profile to see them here.
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<script>
jQuery(document).ready(function($) {
    // Handle page assignment changes
    $('.sfpf-page-select').on('change', function() {
        var pageKey = $(this).data('page');
        var pageId = $(this).val();
        var parentKey = $(this).data('parent') || '';
        var $select = $(this);
        var $row = $select.closest('tr');
        
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
                // Update status badge without reload
                var $status = $row.find('td:eq(2)');
                if (pageId) {
                    $status.html('<span style="background:#dcfce7;color:#166534;padding:3px 10px;border-radius:4px;font-size:12px;">Set</span>');
                } else {
                    $status.html('<span style="background:#fef2f2;color:#dc2626;padding:3px 10px;border-radius:4px;font-size:12px;">Not Set</span>');
                }
                // Show success toast
                showToast('✅ Page assigned successfully!', 'success');
            } else {
                showToast('❌ Error: ' + (response.data || 'Failed to assign page'), 'error');
            }
        });
    });
    
    // Handle page creation - NO page refresh
    $('.sfpf-create-page').on('click', function() {
        var pageKey = $(this).data('page');
        var title = $(this).data('title');
        var slug = $(this).data('slug');
        var parentKey = $(this).data('parent') || '';
        var $btn = $(this);
        var $row = $btn.closest('tr');
        
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
                // Update dropdown and status without reload
                var $select = $row.find('select');
                $select.append('<option value="' + response.data.page_id + '" selected>' + title + '</option>');
                $select.val(response.data.page_id);
                
                // Update status
                var $status = $row.find('td:eq(2)');
                $status.html('<span style="background:#dcfce7;color:#166534;padding:3px 10px;border-radius:4px;font-size:12px;">Set</span>');
                
                // Replace create button with edit/view links
                var editUrl = '<?php echo admin_url('post.php?post='); ?>' + response.data.page_id + '&action=edit';
                var viewUrl = '<?php echo home_url('/'); ?>' + slug + '/';
                $btn.replaceWith('<a href="' + editUrl + '" target="_blank" class="button button-small">Edit</a> <a href="' + viewUrl + '" target="_blank" class="button button-small">View</a>');
                
                showToast('✅ Page created: ' + title, 'success');
            } else {
                showToast('❌ Error: ' + (response.data || 'Failed to create page'), 'error');
                $btn.prop('disabled', false).text('+ Create');
            }
        });
    });
    
    // Handle profession page creation
    $('.sfpf-create-profession-page').on('click', function() {
        var profession = $(this).data('profession');
        var index = $(this).data('index');
        var $btn = $(this);
        var $row = $btn.closest('tr');
        
        if (!profession) {
            showToast('❌ Profession name is empty', 'error');
            return;
        }
        
        $btn.prop('disabled', true).text('Creating...');
        
        $.post(ajaxurl, {
            action: 'sfpf_create_profession_page',
            profession: profession,
            index: index,
            nonce: '<?php echo wp_create_nonce('sfpf_ajax'); ?>'
        }, function(response) {
            if (response.success) {
                // Update linked page column
                var $linkedCell = $row.find('td:eq(1)');
                $linkedCell.html('<a href="' + response.data.permalink + '" target="_blank">' + response.data.title + '</a>');
                
                // Replace button with edit link
                $btn.replaceWith('<a href="' + response.data.edit_url + '" target="_blank" class="button button-small">Edit Page</a>');
                
                showToast('✅ Page created: ' + response.data.title, 'success');
            } else {
                showToast('❌ Error: ' + (response.data || 'Failed to create page'), 'error');
                $btn.prop('disabled', false).text('+ Create Page');
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
                showToast('✅ Template applied!', 'success');
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
                                showToast('✅ Template applied!', 'success');
                            } else {
                                showToast('❌ Error: ' + (resp.data.message || resp.data || 'Unknown error'), 'error');
                            }
                            $btn.prop('disabled', false).text('Apply Template');
                        });
                    } else {
                        $btn.prop('disabled', false).text('Apply Template');
                    }
                } else {
                    showToast('❌ Error: ' + (response.data.message || response.data || 'Unknown error'), 'error');
                    $btn.prop('disabled', false).text('Apply Template');
                }
            }
        }).fail(function() {
            showToast('❌ AJAX request failed', 'error');
            $btn.prop('disabled', false).text('Apply Template');
        });
    });
    
    // Toast notification helper
    function showToast(message, type) {
        var bgColor = type === 'success' ? '#dcfce7' : '#fef2f2';
        var borderColor = type === 'success' ? '#16a34a' : '#dc2626';
        var $toast = $('<div style="position:fixed;top:50px;right:20px;z-index:9999;padding:12px 20px;background:' + bgColor + ';border:1px solid ' + borderColor + ';border-radius:6px;box-shadow:0 4px 12px rgba(0,0,0,0.15);"><p style="margin:0;">' + message + '</p></div>');
        $('body').append($toast);
        setTimeout(function() { $toast.fadeOut(function() { $(this).remove(); }); }, 3000);
    }
});
</script>
