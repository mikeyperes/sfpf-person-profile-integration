<?php
namespace sfpf_person_website;

/**
 * Settings Dashboard
 * 
 * Main admin dashboard with JavaScript-based tab switching (NO page reload).
 * 
 * @package sfpf_person_website
 * @since 1.0.0
 */

defined('ABSPATH') || exit;

/**
 * Register admin menu under Settings
 */
function register_admin_menu() {
    add_options_page(
        'HWS Person Profile Setup',
        'HWS Person Profile',
        'manage_options',
        'sfpf-person-profile',
        __NAMESPACE__ . '\\render_dashboard'
    );
}
add_action('admin_menu', __NAMESPACE__ . '\\register_admin_menu');

/**
 * Render the main dashboard
 */
function render_dashboard() {
    // Define tabs
    $tabs = [
        'overview' => [
            'label' => 'Overview',
            'icon' => 'dashicons-dashboard',
        ],
        'settings' => [
            'label' => 'Settings',
            'icon' => 'dashicons-admin-settings',
        ],
        'shortcodes' => [
            'label' => 'Shortcodes',
            'icon' => 'dashicons-shortcode',
        ],
        'schema' => [
            'label' => 'Schema',
            'icon' => 'dashicons-media-code',
        ],
        'pages' => [
            'label' => 'Pages',
            'icon' => 'dashicons-admin-page',
        ],
        'templates' => [
            'label' => 'Templates',
            'icon' => 'dashicons-welcome-write-blog',
        ],
        'faq' => [
            'label' => 'FAQ Structures',
            'icon' => 'dashicons-editor-help',
        ],
        'snippets' => [
            'label' => 'Snippets',
            'icon' => 'dashicons-admin-plugins',
        ],
        'debug' => [
            'label' => 'Debug',
            'icon' => 'dashicons-admin-tools',
        ],
    ];
    
    ?>
    <div class="wrap sfpf-dashboard">
        <h1 style="display:flex;align-items:center;gap:10px;">
            <span class="dashicons dashicons-id-alt" style="font-size:30px;width:30px;height:30px;"></span>
            SFPF Person Profile Integration
        </h1>
        
        <p style="color:#666;margin-bottom:20px;">Manage personal website schema, pages, and templates.</p>
        
        <!-- Tab Navigation -->
        <nav class="nav-tab-wrapper sfpf-nav-tabs" style="margin-bottom:20px;">
            <?php foreach ($tabs as $tab_id => $tab): ?>
                <a href="#<?php echo esc_attr($tab_id); ?>" 
                   class="nav-tab sfpf-tab-link<?php echo $tab_id === 'overview' ? ' nav-tab-active' : ''; ?>" 
                   data-tab="<?php echo esc_attr($tab_id); ?>">
                    <span class="dashicons <?php echo esc_attr($tab['icon']); ?>" style="font-size:16px;line-height:1.5;"></span>
                    <?php echo esc_html($tab['label']); ?>
                </a>
            <?php endforeach; ?>
        </nav>
        
        <!-- Tab Content Panels -->
        <div class="sfpf-tab-content">
            <?php foreach ($tabs as $tab_id => $tab): ?>
                <div id="sfpf-tab-<?php echo esc_attr($tab_id); ?>" 
                     class="sfpf-tab-panel<?php echo $tab_id === 'overview' ? ' active' : ''; ?>"
                     style="<?php echo $tab_id !== 'overview' ? 'display:none;' : ''; ?>">
                    <?php
                    $file = SFPF_PLUGIN_DIR . 'admin/dashboard-' . $tab_id . '.php';
                    if (file_exists($file)) {
                        include $file;
                    } else {
                        echo '<div class="notice notice-warning"><p>Tab content not found: ' . esc_html($tab_id) . '</p></div>';
                    }
                    ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <style>
        .sfpf-dashboard {
            max-width: 1400px;
        }
        
        .sfpf-nav-tabs {
            border-bottom: 1px solid #c3c4c7;
            padding-bottom: 0;
        }
        
        .sfpf-nav-tabs .nav-tab {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border: 1px solid transparent;
            border-bottom: none;
            background: transparent;
            cursor: pointer;
            text-decoration: none;
            color: #50575e;
            margin-left: 4px;
            margin-bottom: -1px;
        }
        
        .sfpf-nav-tabs .nav-tab:hover {
            background: #f6f7f7;
            color: #1d2327;
        }
        
        .sfpf-nav-tabs .nav-tab-active {
            background: #fff;
            border-color: #c3c4c7;
            border-bottom-color: #fff;
            color: #1d2327;
        }
        
        .sfpf-tab-panel {
            background: #fff;
            padding: 25px;
            border: 1px solid #c3c4c7;
            border-top: none;
        }
        
        /* Cards */
        .sfpf-card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .sfpf-card-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .sfpf-card-header h3 {
            margin: 0;
            font-size: 16px;
            font-weight: 600;
        }
        
        /* Grid layouts */
        .sfpf-grid-2 {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }
        
        @media (max-width: 1200px) {
            .sfpf-grid-2 {
                grid-template-columns: 1fr;
            }
        }
        
        /* Profile cards */
        .sfpf-profile-card {
            display: flex;
            gap: 20px;
            padding: 20px;
            background: #f9fafb;
            border-radius: 8px;
        }
        
        .sfpf-profile-avatar img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .sfpf-profile-info h4 {
            margin: 0 0 5px 0;
            font-size: 18px;
        }
        
        .sfpf-profile-info p {
            margin: 0 0 10px 0;
            color: #666;
        }
        
        .sfpf-profile-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-top: 10px;
            font-size: 13px;
        }
        
        .sfpf-profile-meta span {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .sfpf-url-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 12px;
        }
        
        .sfpf-url-chip {
            display: inline-flex;
            align-items: center;
            padding: 4px 10px;
            background: #e5e7eb;
            border-radius: 15px;
            font-size: 12px;
            text-decoration: none;
            color: #374151;
        }
        
        .sfpf-url-chip:hover {
            background: #d1d5db;
            color: #111827;
        }
        
        /* Buttons */
        .sfpf-btn {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 8px 14px;
            border-radius: 4px;
            font-size: 13px;
            text-decoration: none;
            cursor: pointer;
            border: 1px solid;
        }
        
        .sfpf-btn-primary {
            background: #2563eb;
            border-color: #2563eb;
            color: #fff;
        }
        
        .sfpf-btn-primary:hover {
            background: #1d4ed8;
            color: #fff;
        }
        
        .sfpf-btn-secondary {
            background: #fff;
            border-color: #d1d5db;
            color: #374151;
        }
        
        .sfpf-btn-secondary:hover {
            background: #f3f4f6;
            color: #111827;
        }
        
        /* Tables */
        .sfpf-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .sfpf-table th,
        .sfpf-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .sfpf-table th {
            background: #f9fafb;
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
            color: #6b7280;
        }
        
        .sfpf-table tr:hover td {
            background: #f9fafb;
        }
        
        .sfpf-table code {
            background: #f3f4f6;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 12px;
        }
        
        /* Alert boxes */
        .sfpf-alert {
            padding: 15px 20px;
            border-radius: 6px;
            margin-bottom: 15px;
        }
        
        .sfpf-alert-warning {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            color: #92400e;
        }
        
        .sfpf-alert-error {
            background: #fee2e2;
            border-left: 4px solid #ef4444;
            color: #991b1b;
        }
        
        .sfpf-alert-success {
            background: #dcfce7;
            border-left: 4px solid #22c55e;
            color: #166534;
        }
        
        .sfpf-alert-info {
            background: #dbeafe;
            border-left: 4px solid #3b82f6;
            color: #1e40af;
        }
    </style>
    
    <script>
    jQuery(document).ready(function($) {
        // Tab switching - NO PAGE RELOAD
        $('.sfpf-tab-link').on('click', function(e) {
            e.preventDefault();
            
            var tabId = $(this).data('tab');
            
            // Update tab navigation
            $('.sfpf-tab-link').removeClass('nav-tab-active');
            $(this).addClass('nav-tab-active');
            
            // Update tab panels
            $('.sfpf-tab-panel').removeClass('active').hide();
            $('#sfpf-tab-' + tabId).addClass('active').show();
            
            // Update URL hash without reload
            if (history.pushState) {
                history.pushState(null, null, '#' + tabId);
            } else {
                location.hash = '#' + tabId;
            }
        });
        
        // Handle initial hash on page load
        var hash = window.location.hash.replace('#', '');
        if (hash && $('.sfpf-tab-link[data-tab="' + hash + '"]').length) {
            $('.sfpf-tab-link[data-tab="' + hash + '"]').trigger('click');
        }
        
        // Handle browser back/forward
        $(window).on('hashchange', function() {
            var hash = window.location.hash.replace('#', '');
            if (hash && $('.sfpf-tab-link[data-tab="' + hash + '"]').length) {
                $('.sfpf-tab-link[data-tab="' + hash + '"]').trigger('click');
            }
        });
    });
    </script>
    <?php
}
