<?php
namespace sfpf_person_website;

/**
 * Dashboard Pages Tab
 *
 * @package sfpf_person_website
 * @since 1.0.0
 */

defined('ABSPATH') || exit;

$site_structure_manager = sfpf_site_structure_manager();

if (class_exists('\\Hexa\\PluginCore\\SiteStructure\\SiteStructureRenderer')) {
    echo (new \Hexa\PluginCore\SiteStructure\SiteStructureRenderer($site_structure_manager, [
        'instance_id' => 'sfpf-site-structure',
        'nonce' => wp_create_nonce('sfpf_ajax'),
        'card_class' => 'sfpf-card',
        'table_class' => 'sfpf-table',
        'enable_templates' => true,
        'show_menus' => false,
        'apply_template_action' => 'sfpf_apply_default_template',
        'actions' => [
            'assign_page' => 'sfpf_assign_page',
            'create_page' => 'sfpf_create_page',
            'delete_page' => 'sfpf_delete_page',
            'create_navigation_menu' => 'sfpf_create_navigation_menu',
            'delete_navigation_menu' => 'sfpf_delete_navigation_menu',
            'attach_page_to_menu_item' => 'sfpf_attach_page_to_menu_item',
            'attach_menu_structure' => 'sfpf_attach_menu_structure',
            'add_pages_to_menu' => 'sfpf_add_pages_to_menu',
        ],
        'labels' => [
            'pages_title' => 'Critical Pages',
            'pages_heading' => 'Required Person Website Pages',
            'pages_description' => 'Assign or create the required person website pages. Child pages keep their WordPress parent hierarchy under Biography.',
            'menus_title' => 'Navigation Menus',
            'menus_heading' => 'Navigation Blueprint Manager',
            'menus_description' => 'Create Header, Footer, and Sub-Footer menus. Attach assigned pages as full menu structures or place one assigned page under a specific menu item.',
        ],
    ]))->render();
} else {
    echo '<div class="notice notice-error"><p>Hexa WordPress Plugin Core site structure tools are not loaded.</p></div>';
}

// Founder Professions Section
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
            <a href="<?php echo esc_url(admin_url('user-edit.php?user_id=' . $founder_user_id . '#acf-group_sfpf_user_schema_structures')); ?>" target="_blank" rel="noopener">Edit in Profile &rarr;</a>
        </span>
    </div>

    <p style="color:#666;margin-bottom:15px;">
        Professions defined for the founder. Pages are created as children of the <strong>Professions</strong> parent page.
    </p>

    <?php if (!$professions_page_exists): ?>
    <div class="sfpf-alert sfpf-alert-warning" style="background:#fef3c7;border:1px solid #f59e0b;padding:12px;border-radius:6px;margin-bottom:15px;">
        <strong>Professions parent page not created.</strong><br>
        Create the "Professions" page in Critical Pages before creating profession sub-pages.
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
                    <div style="margin-top:3px;"><code><?php echo esc_html(sanitize_title($prof_name)); ?></code></div>
                </td>
                <td>
                    <?php if ($is_set): ?>
                        <a href="<?php echo esc_url(get_permalink($page_id)); ?>" target="_blank" rel="noopener"><?php echo esc_html($page_obj->post_title); ?></a>
                    <?php else: ?>
                        <span style="color:#9ca3af;">Not created</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($is_set): ?>
                        <span style="background:#dcfce7;color:#166534;padding:3px 10px;border-radius:4px;font-size:12px;">Set</span>
                    <?php else: ?>
                        <span style="background:#fef2f2;color:#dc2626;padding:3px 10px;border-radius:4px;font-size:12px;">Not Set</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($is_set): ?>
                        <a href="<?php echo esc_url(get_edit_post_link($page_id)); ?>" target="_blank" rel="noopener" class="button button-small">Edit</a>
                        <a href="<?php echo esc_url(get_permalink($page_id)); ?>" target="_blank" rel="noopener" class="button button-small">View</a>
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
        Add professions in the founder profile to see them here.
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<script>
jQuery(function($) {
    function showToast(message, type) {
        var bgColor = type === 'success' ? '#dcfce7' : '#fef2f2';
        var borderColor = type === 'success' ? '#16a34a' : '#dc2626';
        var $toast = $('<div style="position:fixed;top:50px;right:20px;z-index:9999;padding:12px 20px;background:' + bgColor + ';border:1px solid ' + borderColor + ';border-radius:6px;box-shadow:0 4px 12px rgba(0,0,0,0.15);"><p style="margin:0;"></p></div>');
        $toast.find('p').text(message);
        $('body').append($toast);
        setTimeout(function() { $toast.fadeOut(function() { $(this).remove(); }); }, 3000);
    }

    $(document).on('click', '.sfpf-create-profession-page', function() {
        var profession = $(this).data('profession');
        var index = $(this).data('index');
        var $btn = $(this);
        var $row = $btn.closest('tr');

        if (!profession) {
            showToast('Profession name is empty.', 'error');
            return;
        }

        $btn.prop('disabled', true).text('Creating...');

        $.post(ajaxurl, {
            action: 'sfpf_create_profession_page',
            profession: profession,
            index: index,
            nonce: '<?php echo esc_js(wp_create_nonce('sfpf_ajax')); ?>'
        }, function(response) {
            if (response.success) {
                $row.find('td:eq(1)').html('<a href="' + response.data.permalink + '" target="_blank" rel="noopener">' + response.data.title + '</a>');
                $row.find('td:eq(2)').html('<span style="background:#dcfce7;color:#166534;padding:3px 10px;border-radius:4px;font-size:12px;">Set</span>');
                $btn.closest('td').html(
                    '<a href="' + response.data.edit_url + '" target="_blank" rel="noopener" class="button button-small">Edit</a> ' +
                    '<a href="' + response.data.permalink + '" target="_blank" rel="noopener" class="button button-small">View</a> ' +
                    '<button type="button" class="button button-small sfpf-delete-profession-page" data-page-id="' + response.data.page_id + '" data-index="' + index + '" style="color:#dc2626;border-color:#fca5a5;">Delete</button>'
                );
                showToast('Page created: ' + response.data.title, 'success');
            } else {
                showToast('Failed to create page.', 'error');
                $btn.prop('disabled', false).text('+ Create');
            }
        }).fail(function() {
            showToast('Profession page request failed.', 'error');
            $btn.prop('disabled', false).text('+ Create');
        });
    });

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
            nonce: '<?php echo esc_js(wp_create_nonce('sfpf_ajax')); ?>'
        }, function(response) {
            if (response.success) {
                var profName = $row.find('td:first strong').text();
                $row.find('td:eq(1)').html('<span style="color:#9ca3af;">Not created</span>');
                $row.find('td:eq(2)').html('<span style="background:#fef2f2;color:#dc2626;padding:3px 10px;border-radius:4px;font-size:12px;">Not Set</span>');
                $btn.closest('td').html(
                    '<button type="button" class="button button-small button-primary sfpf-create-profession-page" data-profession="' + profName + '" data-index="' + index + '">+ Create</button>'
                );
                showToast('Profession page deleted.', 'success');
            } else {
                showToast('Failed to delete page.', 'error');
                $btn.prop('disabled', false).text('Delete');
            }
        }).fail(function() {
            showToast('Profession delete request failed.', 'error');
            $btn.prop('disabled', false).text('Delete');
        });
    });
});
</script>
