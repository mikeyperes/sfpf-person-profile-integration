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
$flat_pages_structure = get_flat_critical_pages_structure($pages_structure);
$menu_structures = get_navigation_menu_structures();
$nav_menus = wp_get_nav_menus();
$menu_inventory = [];
foreach ($nav_menus as $menu) {
    $items = wp_get_nav_menu_items($menu->term_id) ?: [];
    $menu_inventory[] = [
        "menu" => $menu,
        "items" => get_menu_item_labels($items),
    ];
}

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
    
    <div style="margin-top:15px;padding-top:15px;border-top:1px solid #e5e7eb;text-align:right;">
        <button type="button" class="button button-secondary" onclick="location.reload();" style="display:inline-flex;align-items:center;gap:4px;">
            <span class="dashicons dashicons-update" style="font-size:16px;width:16px;height:16px;"></span> Refresh Page
        </button>
    </div>
</div>

<!-- Navigation Menu Manager -->
<div class="sfpf-card" id="sfpf-navigation-menu-manager">
    <div class="sfpf-card-header">
        <span class="dashicons dashicons-menu" style="color:#059669;"></span>
        <h3>Navigation Menus</h3>
    </div>

    <p style="color:#666;margin-bottom:15px;">
        Create and map the required SFPF menu structures: Header, Footer, and Sub-Footer. Attach assigned pages to menus or to existing WordPress menu items.
    </p>

    <div style="display:grid;grid-template-columns:minmax(260px,1fr) auto;gap:10px;align-items:end;margin-bottom:18px;max-width:720px;">
        <label style="display:block;">
            <span style="display:block;font-weight:600;margin-bottom:4px;">Create new menu</span>
            <input type="text" id="sfpf-new-menu-name" class="regular-text" placeholder="Header, Footer, Sub-Footer" style="width:100%;max-width:none;">
        </label>
        <button type="button" class="button button-primary" id="sfpf-create-navigation-menu">Create Menu</button>
        <span id="sfpf-create-menu-status" style="grid-column:1 / -1;font-size:13px;color:#666;"></span>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:14px;margin-bottom:18px;">
        <?php foreach ($menu_structures as $structure_key => $structure): ?>
            <?php $guessed_menu_id = guess_menu_id_for_structure($structure_key, $nav_menus); ?>
            <div class="sfpf-menu-structure-card" data-structure="<?php echo esc_attr($structure_key); ?>" style="border:1px solid #e5e7eb;border-radius:8px;padding:14px;background:#f9fafb;">
                <h4 style="margin:0 0 4px;font-size:15px;"><?php echo esc_html($structure["title"]); ?></h4>
                <p style="margin:0 0 10px;color:#666;font-size:13px;"><?php echo esc_html($structure["description"]); ?></p>

                <label style="display:block;margin-bottom:8px;">
                    <span style="display:block;font-weight:600;margin-bottom:4px;">WordPress menu</span>
                    <select class="sfpf-structure-menu" style="width:100%;" <?php disabled(empty($nav_menus)); ?>>
                        <?php if (empty($nav_menus)): ?>
                            <option value="">No menus found</option>
                        <?php else: ?>
                            <?php foreach ($nav_menus as $menu): ?>
                                <option value="<?php echo esc_attr($menu->term_id); ?>" <?php selected($guessed_menu_id, $menu->term_id); ?>><?php echo esc_html($menu->name); ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </label>

                <label style="display:block;margin-bottom:10px;">
                    <span style="display:block;font-weight:600;margin-bottom:4px;">Attach under menu item</span>
                    <select class="sfpf-structure-parent" style="width:100%;" <?php disabled(empty($nav_menus)); ?>>
                        <option value="0" data-menu-id="0">Top level</option>
                        <?php foreach ($menu_inventory as $inventory): ?>
                            <?php foreach ($inventory["items"] as $item): ?>
                                <option value="<?php echo esc_attr($item["id"]); ?>" data-menu-id="<?php echo esc_attr($inventory["menu"]->term_id); ?>">
                                    <?php echo esc_html($inventory["menu"]->name . ": " . $item["label"]); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    </select>
                </label>

                <div style="font-size:12px;color:#4b5563;margin-bottom:10px;">
                    <?php foreach ($structure["page_keys"] as $page_key): ?>
                        <?php $page_data = $flat_pages_structure[$page_key] ?? null; $assigned_id = get_option("sfpf_page_" . $page_key, 0); ?>
                        <?php if ($page_data): ?>
                            <span style="display:inline-block;margin:0 4px 4px 0;padding:2px 7px;border-radius:999px;background:<?php echo $assigned_id ? "#dcfce7" : "#f3f4f6"; ?>;color:<?php echo $assigned_id ? "#166534" : "#6b7280"; ?>;">
                                <?php echo esc_html($page_data["title"]); ?><?php echo $assigned_id ? "" : " (not set)"; ?>
                            </span>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>

                <button type="button" class="button button-secondary sfpf-attach-menu-structure" <?php disabled(empty($nav_menus)); ?>>Attach <?php echo esc_html($structure["title"]); ?></button>
                <span class="sfpf-structure-status" style="display:block;margin-top:8px;font-size:13px;color:#666;"></span>
            </div>
        <?php endforeach; ?>
    </div>

    <div style="border:1px solid #e5e7eb;border-radius:8px;padding:14px;margin-bottom:18px;background:#fff;">
        <h4 style="margin:0 0 10px;font-size:15px;">Attach a page to a specific menu item</h4>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:10px;align-items:end;">
            <label>
                <span style="display:block;font-weight:600;margin-bottom:4px;">Menu</span>
                <select id="sfpf-attach-menu" style="width:100%;" <?php disabled(empty($nav_menus)); ?>>
                    <?php if (empty($nav_menus)): ?>
                        <option value="">No menus found</option>
                    <?php else: ?>
                        <?php foreach ($nav_menus as $menu): ?>
                            <option value="<?php echo esc_attr($menu->term_id); ?>"><?php echo esc_html($menu->name); ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </label>
            <label>
                <span style="display:block;font-weight:600;margin-bottom:4px;">Parent menu item</span>
                <select id="sfpf-attach-parent-item" style="width:100%;" <?php disabled(empty($nav_menus)); ?>>
                    <option value="0" data-menu-id="0">Top level</option>
                    <?php foreach ($menu_inventory as $inventory): ?>
                        <?php foreach ($inventory["items"] as $item): ?>
                            <option value="<?php echo esc_attr($item["id"]); ?>" data-menu-id="<?php echo esc_attr($inventory["menu"]->term_id); ?>"><?php echo esc_html($item["label"]); ?></option>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>
                <span style="display:block;font-weight:600;margin-bottom:4px;">Assigned SFPF page</span>
                <select id="sfpf-attach-page-key" style="width:100%;" <?php disabled(empty($nav_menus)); ?>>
                    <?php foreach ($flat_pages_structure as $page_key => $page_data): ?>
                        <?php $assigned_id = get_option("sfpf_page_" . $page_key, 0); ?>
                        <option value="<?php echo esc_attr($page_key); ?>" <?php disabled(!$assigned_id); ?>><?php echo esc_html($page_data["title"] . ($assigned_id ? "" : " (not set)")); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <button type="button" class="button button-primary" id="sfpf-attach-page-to-menu-item" <?php disabled(empty($nav_menus)); ?>>Attach Page</button>
        </div>
        <span id="sfpf-attach-page-status" style="display:block;margin-top:8px;font-size:13px;color:#666;"></span>
    </div>

    <h4 style="margin:0 0 10px;font-size:15px;">WordPress Menu Items</h4>
    <?php if (empty($nav_menus)): ?>
        <p style="color:#666;margin:0;">No WordPress menus exist yet. Create Header, Footer, and Sub-Footer menus above.</p>
    <?php else: ?>
        <table class="sfpf-table">
            <thead>
                <tr>
                    <th style="width:24%;">Menu</th>
                    <th>Items</th>
                    <th style="width:20%;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($menu_inventory as $inventory): ?>
                    <tr>
                        <td><strong><?php echo esc_html($inventory["menu"]->name); ?></strong><div><code><?php echo esc_html($inventory["menu"]->slug); ?></code></div></td>
                        <td>
                            <?php if (empty($inventory["items"])): ?>
                                <span style="color:#6b7280;">No items</span>
                            <?php else: ?>
                                <?php foreach ($inventory["items"] as $item): ?>
                                    <div style="margin-bottom:3px;"><code>#<?php echo esc_html($item["id"]); ?></code> <?php echo esc_html($item["label"]); ?></div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a class="button button-small" href="<?php echo esc_url(admin_url("nav-menus.php?menu=" . (int) $inventory["menu"]->term_id)); ?>" target="_blank">Edit</a>
                            <button type="button" class="button button-small sfpf-delete-navigation-menu" data-menu-id="<?php echo esc_attr($inventory["menu"]->term_id); ?>" data-menu-name="<?php echo esc_attr($inventory["menu"]->name); ?>">Delete</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<!-- Founder Professions Section -->
<?php 
$founder_user_id = get_founder_user_id();
$entity_type = $founder_user_id ? get_field('entity_type', 'user_' . $founder_user_id) : '';
$professions = ($entity_type === 'person' && $founder_user_id) ? get_field('professions', 'user_' . $founder_user_id) : [];
$professions_page_id = get_option('sfpf_page_professions', 0);
$professions_page_exists = $professions_page_id && get_post($professions_page_id) && get_post_status($professions_page_id) === 'publish';

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
        Professions defined for the founder. Pages are created as children of the <strong>Professions</strong> parent page.
    </p>
    
    <?php if (!$professions_page_exists): ?>
    <div class="sfpf-alert sfpf-alert-warning" style="background:#fef3c7;border:1px solid #f59e0b;padding:12px;border-radius:6px;margin-bottom:15px;">
        <strong>⚠ Professions parent page not created.</strong><br>
        Create the "Professions" page in Critical Pages above before creating profession sub-pages.
    </div>
    <?php endif; ?>
    
    <?php if (!empty($professions) && is_array($professions)): ?>
    <table class="sfpf-table">
        <thead>
            <tr>
                <th style="width:25%;">Profession</th>
                <th style="width:30%;">Linked Page</th>
                <th style="width:15%;">Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($professions as $idx => $prof): 
                $prof_name = '';
                if (is_array($prof)) {
                    $prof_name = $prof['name'] ?? $prof['profession'] ?? $prof['title'] ?? '';
                } elseif (is_string($prof)) {
                    $prof_name = $prof;
                }
                
                if (empty($prof_name) && (is_array($prof) && empty($prof['page']))) {
                    continue;
                }
                
                $linked_page = is_array($prof) ? ($prof['page'] ?? null) : null;
                $page_id = $linked_page ? (is_array($linked_page) ? ($linked_page['ID'] ?? $linked_page) : $linked_page) : 0;
                $page_obj = $page_id ? get_post($page_id) : null;
                $is_set = $page_obj && $page_obj->post_status === 'publish';
            ?>
            <tr>
                <td>
                    <strong><?php echo esc_html($prof_name ?: 'Untitled'); ?></strong>
                    <div style="margin-top:3px;"><code style="font-size:11px;background:#e5e7eb;padding:2px 6px;border-radius:3px;"><?php echo esc_html(sanitize_title($prof_name)); ?></code></div>
                </td>
                <td>
                    <?php if ($is_set): ?>
                        <a href="<?php echo esc_url(get_permalink($page_id)); ?>" target="_blank"><?php echo esc_html($page_obj->post_title); ?></a>
                    <?php else: ?>
                        <span style="color:#9ca3af;">— Not created —</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($is_set): ?>
                        <span style="background:#dcfce7;color:#166534;padding:3px 10px;border-radius:4px;font-size:12px;">✓ Set</span>
                    <?php else: ?>
                        <span style="background:#fef2f2;color:#dc2626;padding:3px 10px;border-radius:4px;font-size:12px;">✗ Not Set</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($is_set): ?>
                        <a href="<?php echo esc_url(get_edit_post_link($page_id)); ?>" target="_blank" class="button button-small">Edit</a>
                        <a href="<?php echo esc_url(get_permalink($page_id)); ?>" target="_blank" class="button button-small">View</a>
                        <button type="button" class="button button-small sfpf-delete-profession-page" 
                                data-page-id="<?php echo esc_attr($page_id); ?>"
                                data-index="<?php echo esc_attr($idx); ?>"
                                style="color:#dc2626;border-color:#fca5a5;">Delete</button>
                    <?php elseif ($professions_page_exists): ?>
                        <button type="button" class="button button-small button-primary sfpf-create-profession-page" 
                                data-profession="<?php echo esc_attr($prof_name); ?>"
                                data-index="<?php echo esc_attr($idx); ?>">
                            + Create
                        </button>
                    <?php else: ?>
                        <span style="color:#9ca3af;font-size:12px;">Create parent page first</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
    <div class="sfpf-alert sfpf-alert-info" style="background:#f0f6fc;border:1px solid #93c5fd;padding:12px;border-radius:6px;">
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
                var editUrl = response.data.edit_url || ('<?php echo admin_url('post.php?post='); ?>' + response.data.page_id + '&action=edit');
                var viewUrl = response.data.permalink;
                $btn.replaceWith('<a href="' + editUrl + '" target="_blank" class="button button-small">Edit</a> <a href="' + viewUrl + '" target="_blank" class="button button-small">View</a> <button type="button" class="button button-small sfpf-apply-page-template" data-page-id="' + response.data.page_id + '" data-page-key="' + pageKey + '">Apply Template</button>');
                
                showToast('✅ Page created: ' + title, 'success');
            } else {
                showToast('❌ Error: ' + (response.data || 'Failed to create page'), 'error');
                $btn.prop('disabled', false).text('+ Create');
            }
        });
    });
    
    // Handle profession page creation
    $(document).on('click', '.sfpf-create-profession-page', function() {
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
                $row.find('td:eq(1)').html('<a href="' + response.data.permalink + '" target="_blank">' + response.data.title + '</a>');
                
                // Update status
                $row.find('td:eq(2)').html('<span style="background:#dcfce7;color:#166534;padding:3px 10px;border-radius:4px;font-size:12px;">✓ Set</span>');
                
                // Replace button with Edit/View/Delete
                var editUrl = response.data.edit_url;
                var viewUrl = response.data.permalink;
                var pageId = response.data.page_id;
                $btn.closest('td').html(
                    '<a href="' + editUrl + '" target="_blank" class="button button-small">Edit</a> ' +
                    '<a href="' + viewUrl + '" target="_blank" class="button button-small">View</a> ' +
                    '<button type="button" class="button button-small sfpf-delete-profession-page" data-page-id="' + pageId + '" data-index="' + index + '" style="color:#dc2626;border-color:#fca5a5;">Delete</button>'
                );
                
                showToast('✅ Page created: ' + response.data.title, 'success');
            } else {
                showToast('❌ Error: ' + (response.data || 'Failed to create page'), 'error');
                $btn.prop('disabled', false).text('+ Create');
            }
        });
    });
    
    // Handle profession page deletion
    $(document).on('click', '.sfpf-delete-profession-page', function() {
        var $btn = $(this);
        var pageId = $btn.data('page-id');
        var index = $btn.data('index');
        var $row = $btn.closest('tr');
        
        if (!confirm('Delete this profession page? It will be moved to trash.')) return;
        
        $btn.prop('disabled', true).text('Deleting...');
        
        $.post(ajaxurl, {
            action: 'sfpf_delete_profession_page',
            page_id: pageId,
            index: index,
            nonce: '<?php echo wp_create_nonce('sfpf_ajax'); ?>'
        }, function(response) {
            if (response.success) {
                var profName = $row.find('td:first strong').text();
                $row.find('td:eq(1)').html('<span style="color:#9ca3af;">— Not created —</span>');
                $row.find('td:eq(2)').html('<span style="background:#fef2f2;color:#dc2626;padding:3px 10px;border-radius:4px;font-size:12px;">✗ Not Set</span>');
                $btn.closest('td').html(
                    '<button type="button" class="button button-small button-primary sfpf-create-profession-page" data-profession="' + profName + '" data-index="' + index + '">+ Create</button>'
                );
                showToast('✅ Profession page deleted', 'success');
            } else {
                showToast('❌ Error: ' + (response.data || 'Failed'), 'error');
                $btn.prop('disabled', false).text('Delete');
            }
        });
    });
    
    // Navigation menu manager helpers
    function sfpfFilterParentItems($select, menuId) {
        $select.find("option").each(function() {
            var optionMenuId = String($(this).data("menu-id") || "0");
            var isTop = optionMenuId === "0";
            $(this).toggle(isTop || optionMenuId === String(menuId));
        });
        if (!$select.find("option:selected").is(":visible")) {
            $select.val("0");
        }
    }

    function sfpfSetMenuStatus($el, message, success) {
        $el.text(message).css("color", success ? "#059669" : "#dc2626");
    }

    $(".sfpf-structure-menu").each(function() {
        var $card = $(this).closest(".sfpf-menu-structure-card");
        sfpfFilterParentItems($card.find(".sfpf-structure-parent"), $(this).val());
    });

    $("#sfpf-attach-menu").on("change", function() {
        sfpfFilterParentItems($("#sfpf-attach-parent-item"), $(this).val());
    }).trigger("change");

    $(document).on("change", ".sfpf-structure-menu", function() {
        var $card = $(this).closest(".sfpf-menu-structure-card");
        sfpfFilterParentItems($card.find(".sfpf-structure-parent"), $(this).val());
    });

    $("#sfpf-create-navigation-menu").on("click", function() {
        var $btn = $(this);
        var $status = $("#sfpf-create-menu-status");
        var menuName = $("#sfpf-new-menu-name").val();
        if (!menuName) {
            sfpfSetMenuStatus($status, "Enter a menu name.", false);
            return;
        }
        $btn.prop("disabled", true).text("Creating...");
        $.post(ajaxurl, {
            action: "sfpf_create_navigation_menu",
            nonce: "<?php echo wp_create_nonce("sfpf_ajax"); ?>",
            menu_name: menuName
        }, function(response) {
            if (response.success) {
                sfpfSetMenuStatus($status, "Created menu: " + response.data.name, true);
                setTimeout(function() { location.reload(); }, 700);
            } else {
                sfpfSetMenuStatus($status, response.data || "Menu creation failed.", false);
                $btn.prop("disabled", false).text("Create Menu");
            }
        }).fail(function() {
            sfpfSetMenuStatus($status, "Menu creation request failed.", false);
            $btn.prop("disabled", false).text("Create Menu");
        });
    });

    $(document).on("click", ".sfpf-delete-navigation-menu", function() {
        var $btn = $(this);
        var menuId = $btn.data("menu-id");
        var menuName = $btn.data("menu-name");
        if (!confirm("Delete the " + menuName + " menu?")) return;
        $btn.prop("disabled", true).text("Deleting...");
        $.post(ajaxurl, {
            action: "sfpf_delete_navigation_menu",
            nonce: "<?php echo wp_create_nonce("sfpf_ajax"); ?>",
            menu_id: menuId
        }, function(response) {
            if (response.success) {
                showToast("Menu deleted", "success");
                setTimeout(function() { location.reload(); }, 700);
            } else {
                showToast("Error: " + (response.data || "Menu deletion failed"), "error");
                $btn.prop("disabled", false).text("Delete");
            }
        }).fail(function() {
            showToast("Menu deletion request failed", "error");
            $btn.prop("disabled", false).text("Delete");
        });
    });

    $(document).on("click", ".sfpf-attach-menu-structure", function() {
        var $btn = $(this);
        var $card = $btn.closest(".sfpf-menu-structure-card");
        var $status = $card.find(".sfpf-structure-status");
        var menuId = $card.find(".sfpf-structure-menu").val();
        var parentItemId = $card.find(".sfpf-structure-parent").val() || "0";
        var structure = $card.data("structure");
        if (!menuId) {
            sfpfSetMenuStatus($status, "Select a menu first.", false);
            return;
        }
        $btn.prop("disabled", true).text("Attaching...");
        $.post(ajaxurl, {
            action: "sfpf_attach_menu_structure",
            nonce: "<?php echo wp_create_nonce("sfpf_ajax"); ?>",
            menu_id: menuId,
            parent_item_id: parentItemId,
            structure: structure
        }, function(response) {
            if (response.success) {
                sfpfSetMenuStatus($status, response.data.message, true);
                setTimeout(function() { location.reload(); }, 900);
            } else {
                sfpfSetMenuStatus($status, response.data || "Structure attach failed.", false);
                $btn.prop("disabled", false).text("Attach " + $card.find("h4").text());
            }
        }).fail(function() {
            sfpfSetMenuStatus($status, "Structure attach request failed.", false);
            $btn.prop("disabled", false).text("Attach " + $card.find("h4").text());
        });
    });

    $("#sfpf-attach-page-to-menu-item").on("click", function() {
        var $btn = $(this);
        var $status = $("#sfpf-attach-page-status");
        var menuId = $("#sfpf-attach-menu").val();
        var parentItemId = $("#sfpf-attach-parent-item").val() || "0";
        var pageKey = $("#sfpf-attach-page-key").val();
        if (!menuId || !pageKey) {
            sfpfSetMenuStatus($status, "Select a menu and assigned page.", false);
            return;
        }
        $btn.prop("disabled", true).text("Attaching...");
        $.post(ajaxurl, {
            action: "sfpf_attach_page_to_menu_item",
            nonce: "<?php echo wp_create_nonce("sfpf_ajax"); ?>",
            menu_id: menuId,
            parent_item_id: parentItemId,
            page_key: pageKey
        }, function(response) {
            if (response.success) {
                sfpfSetMenuStatus($status, response.data.message, true);
                setTimeout(function() { location.reload(); }, 900);
            } else {
                sfpfSetMenuStatus($status, response.data || "Page attach failed.", false);
                $btn.prop("disabled", false).text("Attach Page");
            }
        }).fail(function() {
            sfpfSetMenuStatus($status, "Page attach request failed.", false);
            $btn.prop("disabled", false).text("Attach Page");
        });
    });
    
    // Handle page deletion
    $(document).on('click', '.sfpf-delete-page', function() {
        var pageKey = $(this).data('page');
        var pageId = $(this).data('page-id');
        var $btn = $(this);
        var $row = $btn.closest('tr');
        
        if (!confirm('Delete this page? It will be moved to trash.')) return;
        
        $btn.prop('disabled', true).text('Deleting...');
        
        $.post(ajaxurl, {
            action: 'sfpf_delete_page',
            page_key: pageKey,
            page_id: pageId,
            nonce: '<?php echo wp_create_nonce('sfpf_ajax'); ?>'
        }, function(response) {
            if (response.success) {
                // Reset select
                $row.find('select').val('');
                // Update status
                $row.find('td:eq(2)').html('<span style="background:#fef2f2;color:#dc2626;padding:3px 10px;border-radius:4px;font-size:12px;">Not Set</span>');
                // Replace actions with create button
                var title = $row.find('td:first strong').text();
                var slug = $row.find('td:first code').text();
                var parentKey = $row.find('select').data('parent') || '';
                var createBtn = '<button class="button button-small button-primary sfpf-create-page" data-page="' + pageKey + '" data-title="' + title + '" data-slug="' + slug + '"';
                if (parentKey) createBtn += ' data-parent="' + parentKey + '"';
                createBtn += '>+ Create</button>';
                $btn.closest('td').html(createBtn);
                showToast('✅ Page deleted', 'success');
            } else {
                showToast('❌ Error: ' + (response.data || 'Failed'), 'error');
                $btn.prop('disabled', false).text('Delete');
            }
        });
    });
    
    // Apply Template handler (delegated for dynamic buttons)
    $(document).on('click', '.sfpf-apply-page-template', function(e) {
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
