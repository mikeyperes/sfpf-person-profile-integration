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
    
    <div style="margin-top:15px;display:flex;gap:10px;flex-wrap:wrap;">
        <a href="<?php echo esc_url(get_hws_base_tools_url()); ?>" target="_blank" class="button button-secondary">
            Open HWS Base Tools →
        </a>
        <a href="<?php echo esc_url(get_website_settings_url()); ?>" target="_blank" class="button button-secondary">
            Website Settings →
        </a>
        <a href="https://search.google.com/search-console" target="_blank" class="button button-secondary">
            Google Search Console →
        </a>
    </div>
</div>

<!-- ════════════════════════════════════════════════════════════════════ -->
<!-- System Checks — comprehensive health audit                        -->
<!-- ════════════════════════════════════════════════════════════════════ -->
<?php
// ── Gather all check data up front ──
$sc_pass = 0;
$sc_fail = 0;
$sc_warn = 0;
$sc_items = [];

$uid = $founder ? $founder['id'] : 0;
$user_key = $uid ? 'user_' . $uid : '';

// Helper: add a check result
$sc_add = function($category, $label, $status, $detail = '', $action_url = '', $action_label = '') use (&$sc_items, &$sc_pass, &$sc_fail, &$sc_warn) {
    $sc_items[] = compact('category', 'label', 'status', 'detail', 'action_url', 'action_label');
    if ($status === 'pass') $sc_pass++;
    elseif ($status === 'fail') $sc_fail++;
    elseif ($status === 'warn') $sc_warn++;
};

// ────────────────────────────────────────
// CATEGORY: Plugin Dependencies
// ────────────────────────────────────────
$sc_add('Plugins', 'HWS Base Tools', $hws_info['active'] ? 'pass' : 'fail', 
    $hws_info['active'] ? 'v' . $hws_info['version'] : 'Required plugin not active',
    admin_url('plugins.php'), 'Plugins');

$sc_add('Plugins', 'ACF Pro', class_exists('ACF') ? 'pass' : 'fail',
    class_exists('ACF') ? 'Active' : 'Required for all data fields',
    admin_url('plugins.php'), 'Plugins');

$sc_add('Plugins', 'Elementor', defined('ELEMENTOR_VERSION') ? 'pass' : 'warn',
    defined('ELEMENTOR_VERSION') ? 'v' . ELEMENTOR_VERSION : 'Not active — templates won\'t work',
    admin_url('plugins.php'), 'Plugins');

$rm_active = is_plugin_active('seo-by-rank-math/rank-math.php');
$sc_add('Plugins', 'RankMath SEO', $rm_active ? 'pass' : 'warn',
    $rm_active ? 'Active' : 'Recommended for breadcrumbs & sitemaps',
    admin_url('plugins.php'), 'Plugins');

// ────────────────────────────────────────
// CATEGORY: WordPress Settings
// ────────────────────────────────────────

// Favicon
$favicon_url = get_site_icon_url();
$sc_add('WordPress', 'Favicon / Site Icon', !empty($favicon_url) ? 'pass' : 'fail',
    !empty($favicon_url) ? 'Set' : 'No favicon configured — looks unprofessional in browser tabs',
    admin_url('customize.php?autofocus[section]=title_tagline'), 'Customizer → Site Identity');

// Comments disabled
$comments_open = get_option('default_comment_status', 'open') === 'open';
$sc_add('WordPress', 'Default Comments Disabled', !$comments_open ? 'pass' : 'fail',
    !$comments_open ? 'Comments disabled by default' : 'Comments still open — personal sites should disable these',
    admin_url('options-discussion.php'), 'Discussion Settings');

// Pingbacks disabled
$pings_open = get_option('default_ping_status', 'open') === 'open';
$sc_add('WordPress', 'Default Pingbacks Disabled', !$pings_open ? 'pass' : 'fail',
    !$pings_open ? 'Pingbacks disabled' : 'Pingbacks still open — should be disabled',
    admin_url('options-discussion.php'), 'Discussion Settings');

// Search engine visibility
$blog_public = get_option('blog_public', '1');
$sc_add('WordPress', 'Search Engine Visibility', $blog_public == '1' ? 'pass' : 'fail',
    $blog_public == '1' ? 'Site is visible to search engines' : 'Site is hidden from search engines — fix immediately!',
    admin_url('options-reading.php'), 'Reading Settings');

// Permalink structure (not plain)
$permalink = get_option('permalink_structure', '');
$sc_add('WordPress', 'Pretty Permalinks', !empty($permalink) ? 'pass' : 'fail',
    !empty($permalink) ? esc_html($permalink) : 'Using plain permalinks — bad for SEO',
    admin_url('options-permalink.php'), 'Permalink Settings');

// Site title set
$site_title = get_bloginfo('name');
$sc_add('WordPress', 'Site Title', !empty($site_title) ? 'pass' : 'fail',
    !empty($site_title) ? esc_html($site_title) : 'No site title configured',
    admin_url('options-general.php'), 'General Settings');

// Tagline / description
$tagline = get_bloginfo('description');
$has_tagline = !empty($tagline) && $tagline !== 'Just another WordPress site';
$sc_add('WordPress', 'Site Tagline', $has_tagline ? 'pass' : 'warn',
    $has_tagline ? esc_html($tagline) : 'Default or empty tagline',
    admin_url('options-general.php'), 'General Settings');

// ────────────────────────────────────────
// CATEGORY: Founder / Person Setup
// ────────────────────────────────────────
$sc_add('Person', 'Founder User Assigned', !empty($founder) ? 'pass' : 'fail',
    !empty($founder) ? esc_html($founder['display_name']) : 'No founder user set — this is critical',
    get_website_settings_url(), 'Website Settings');

if ($founder && function_exists('get_field')) {
    $profile_url = get_edit_user_link($uid);
    
    // Profile photo (avatar or KG images)
    $avatar = get_avatar_url($uid, ['size' => 200]);
    $has_custom_avatar = !empty($avatar) && strpos($avatar, 'gravatar.com/avatar') === false;
    $kg_images = get_field('knowledge_graph_images', $user_key);
    $has_kg_images = !empty($kg_images) && is_array($kg_images) && count($kg_images) > 0;
    $sc_add('Person', 'Profile Photo', ($has_custom_avatar || $has_kg_images) ? 'pass' : 'fail',
        $has_kg_images ? count($kg_images) . ' Knowledge Graph image(s)' : ($has_custom_avatar ? 'Custom avatar set' : 'No profile photo — uses default Gravatar'),
        $profile_url, 'Edit Profile');
    
    // Public email
    $pub_email = get_field('additional', $user_key);
    $has_pub_email = is_array($pub_email) && !empty($pub_email['public_email']);
    $sc_add('Person', 'Public Email', $has_pub_email ? 'pass' : 'fail',
        $has_pub_email ? esc_html($pub_email['public_email']) : 'Not set — needed for schema & contact pages',
        $profile_url, 'Edit Profile');
    
    // Title
    $title_val = get_field('title', $user_key);
    $sc_add('Person', 'Title / Job Role', !empty($title_val) ? 'pass' : 'fail',
        !empty($title_val) ? esc_html(wp_strip_all_tags($title_val)) : 'Not set',
        $profile_url, 'Edit Profile');
    
    // Socials
    $urls_val = get_field('urls', $user_key);
    $socials = ['facebook' => 'Facebook', 'instagram' => 'Instagram', 'linkedin' => 'LinkedIn', 'crunchbase' => 'Crunchbase'];
    foreach ($socials as $key => $label) {
        $has_social = is_array($urls_val) && !empty($urls_val[$key]);
        $sc_add('Person', $label . ' URL', $has_social ? 'pass' : 'fail',
            $has_social ? '✓ Set' : 'Missing',
            $profile_url, 'Edit Profile');
    }
    
    // Knowledge Graph ID
    $kgid = get_field('knowledge_graph_id', $user_key);
    $sc_add('Person', 'Google Knowledge Graph ID', !empty($kgid) ? 'pass' : 'fail',
        !empty($kgid) ? esc_html($kgid) : 'Not set — important for Knowledge Panel',
        $profile_url, 'Edit Profile');
    
    // Location Born
    $loc_born = get_field('location_born', $user_key);
    $has_loc = is_array($loc_born) && !empty($loc_born['location']);
    $sc_add('Person', 'Location Born', $has_loc ? 'pass' : 'fail',
        $has_loc ? esc_html($loc_born['location']) : 'Not set',
        $profile_url, 'Edit Profile');
    
    // Language (at least one)
    $languages = get_field('knows_language', $user_key);
    $lang_count = is_array($languages) ? count(array_filter($languages, function($l) { return !empty($l['value']); })) : 0;
    $sc_add('Person', 'Languages', $lang_count > 0 ? 'pass' : 'fail',
        $lang_count > 0 ? $lang_count . ' language(s)' : 'None set — add at least one',
        $profile_url, 'Edit Profile');
    
    // Knowledge Graph Images (at least one)
    $sc_add('Person', 'Knowledge Graph Images', $has_kg_images ? 'pass' : 'fail',
        $has_kg_images ? count($kg_images) . ' image(s)' : 'None uploaded — needed for Knowledge Panel',
        $profile_url, 'Edit Profile');
    
    // Biography
    $bio = get_field('biography', $user_key);
    $sc_add('Person', 'Biography', !empty($bio) ? 'pass' : 'fail',
        !empty($bio) ? wp_trim_words(wp_strip_all_tags($bio), 8, '...') : 'Not set',
        $profile_url, 'Edit Profile');
    
    // Biography Short
    $bio_short = get_field('biography_short', $user_key);
    $sc_add('Person', 'Short Biography', !empty($bio_short) ? 'pass' : 'fail',
        !empty($bio_short) ? wp_trim_words(wp_strip_all_tags($bio_short), 8, '...') : 'Not set',
        $profile_url, 'Edit Profile');
    
    // Mission Statement
    $mission = get_field('mission_statement', $user_key);
    $sc_add('Person', 'Mission Statement', !empty($mission) ? 'pass' : 'fail',
        !empty($mission) ? wp_trim_words(wp_strip_all_tags($mission), 8, '...') : 'Not set',
        $profile_url, 'Edit Profile');
    
    // Education (at least one)
    $education = get_field('education', $user_key);
    $edu_count = is_array($education) ? count(array_filter($education, function($e) { return !empty($e['college']); })) : 0;
    $sc_add('Person', 'Education', $edu_count > 0 ? 'pass' : 'fail',
        $edu_count > 0 ? $edu_count . ' institution(s)' : 'None set — add at least one',
        $profile_url, 'Edit Profile');
    
    // Articles (at least one)
    $articles = get_field('articles', $user_key);
    $article_count = is_array($articles) ? count(array_filter($articles, function($a) { return !empty($a['url']); })) : 0;
    $sc_add('Person', 'Articles / Press', $article_count > 0 ? 'pass' : 'fail',
        $article_count > 0 ? $article_count . ' article(s)' : 'None set — add at least one for sameAs links',
        $profile_url, 'Edit Profile');

    // Additional URLs (at least one)
    $additional_urls = get_field('additional_urls', $user_key);
    $additional_url_count = is_array($additional_urls) ? count(array_filter($additional_urls, function($link) { return !empty($link['url']); })) : 0;
    $sc_add('Person', 'Additional URLs', $additional_url_count > 0 ? 'pass' : 'warn',
        $additional_url_count > 0 ? $additional_url_count . ' additional URL(s)' : 'None set — optional sameAs links',
        $profile_url, 'Edit Profile');
}

// ────────────────────────────────────────
// CATEGORY: Company / Organization Setup
// ────────────────────────────────────────
$primary_org_id = get_option('sfpf_primary_organization', 0);
$sc_add('Company', 'Primary Organization Set', !empty($primary_org_id) ? 'pass' : 'fail',
    !empty($primary_org_id) ? get_the_title($primary_org_id) : 'Not set — go to SFPF Settings → Set Primary Company',
    admin_url('admin.php?page=sfpf-settings'), 'SFPF Settings');

$org_count = wp_count_posts('organization');
$pub_orgs = isset($org_count->publish) ? (int)$org_count->publish : 0;
$sc_add('Company', 'Published Organizations', $pub_orgs > 0 ? 'pass' : 'warn',
    $pub_orgs > 0 ? $pub_orgs . ' organization(s)' : 'No published organizations',
    admin_url('edit.php?post_type=organization'), 'Organizations');

// ────────────────────────────────────────
// CATEGORY: Schema Configuration
// ────────────────────────────────────────
$hp_schema = get_option('sfpf_homepage_schema_type', 'person');
$sc_add('Schema', 'Homepage Schema Type', $hp_schema !== 'none' ? 'pass' : 'fail',
    $hp_schema !== 'none' ? ucfirst(str_replace('_', ' ', $hp_schema)) : 'Schema injection disabled — turn it on!',
    admin_url('admin.php?page=sfpf-dashboard&tab=schema'), 'Schema Tab');

$bio_schema = get_option('sfpf_biography_schema_type', 'profile_page_only');
$bio_page_id_sc = get_option('sfpf_page_biography');
if ($bio_page_id_sc) {
    $sc_add('Schema', 'Biography Schema Type', $bio_schema !== 'none' ? 'pass' : 'warn',
        $bio_schema !== 'none' ? ucfirst(str_replace('_', ' ', $bio_schema)) : 'Biography schema disabled',
        admin_url('admin.php?page=sfpf-dashboard&tab=schema'), 'Schema Tab');
}

// ────────────────────────────────────────
// CATEGORY: Critical Pages
// ────────────────────────────────────────
$critical_pages = [
    'sfpf_page_biography' => 'Biography Page',
    'sfpf_page_connect'   => 'Connect / Contact Page',
];
foreach ($critical_pages as $opt_key => $page_label) {
    $page_id = get_option($opt_key);
    $page_ok = $page_id && get_post_status($page_id) === 'publish';
    $sc_add('Pages', $page_label, $page_ok ? 'pass' : 'warn',
        $page_ok ? get_the_title($page_id) : 'Not assigned',
        admin_url('admin.php?page=sfpf-dashboard&tab=pages'), 'Critical Pages');
}

// ── Render system checks through Hexa Core ──
$cat_meta = [
    "Plugins"   => ["icon" => "dashicons-admin-plugins", "color" => "#6366f1"],
    "WordPress" => ["icon" => "dashicons-wordpress", "color" => "#0073aa"],
    "Person"    => ["icon" => "dashicons-admin-users", "color" => "#2563eb"],
    "Company"   => ["icon" => "dashicons-building", "color" => "#059669"],
    "Schema"    => ["icon" => "dashicons-editor-code", "color" => "#8b5cf6"],
    "Pages"     => ["icon" => "dashicons-admin-page", "color" => "#d97706"],
];

echo ( new \Hexa\PluginCore\SystemChecks\SystemChecksRenderer() )->render(
    $sc_items,
    [
        "id"            => "sfpf-system-checks",
        "title"         => "System Checks",
        "class"         => "sfpf-system-checks-core",
        "category_meta" => $cat_meta,
    ]
);
?>

<!-- Profile Cards -->
<div class="sfpf-grid-2">
    <!-- Founder/Person Profile -->
    <div class="sfpf-card">
        <div class="sfpf-card-header">
            <span class="dashicons dashicons-admin-users" style="color:#2563eb;"></span>
            <h3>Person Profile</h3>
        </div>
        
        <?php if ($founder): 
            $uid = $founder['id'];
            $user_key = 'user_' . $uid;
            $title_val = function_exists('get_field') ? get_field('title', $user_key) : '';
            $bio_val = function_exists('get_field') ? get_field('biography', $user_key) : '';
            $bio_short_val = function_exists('get_field') ? get_field('biography_short', $user_key) : '';
            $professions_val = function_exists('get_field') ? get_field('professions', $user_key) : [];
            $urls_val = function_exists('get_field') ? get_field('urls', $user_key) : [];
        ?>
            <div class="sfpf-profile-card">
                <div class="sfpf-profile-avatar">
                    <img src="<?php echo esc_url($founder['avatar_url']); ?>" alt="">
                </div>
                <div class="sfpf-profile-info" style="flex:1;">
                    <h4><?php echo esc_html($founder['display_name']); ?></h4>
                    <?php if ($title_val): ?>
                        <p style="color:#6b7280;font-size:14px;margin:0 0 5px;"><?php echo esc_html($title_val); ?></p>
                    <?php endif; ?>
                    
                    <div class="sfpf-profile-meta">
                        <?php if ($founder['email']): ?>
                            <span><span class="dashicons dashicons-email" style="font-size:14px;color:#6b7280;"></span> <?php echo esc_html($founder['email']); ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div style="margin-top:15px;display:flex;gap:10px;">
                        <a href="<?php echo esc_url($founder['edit_url']); ?>" target="_blank" class="button button-secondary">Edit Profile</a>
                        <a href="<?php echo esc_url($founder['view_url']); ?>" target="_blank" class="button button-secondary">View Profile</a>
                    </div>
                </div>
            </div>
            
            <!-- Extended Profile Info -->
            <div style="margin-top:15px;display:grid;gap:8px;">
                <?php if ($title_val): ?>
                <div style="padding:10px 14px;background:#f9fafb;border-radius:6px;border:1px solid #e5e7eb;">
                    <div style="font-size:11px;color:#9ca3af;margin-bottom:3px;">Title <code class="sfpf-copy-sc" style="font-size:10px;background:#f3f4f6;padding:1px 4px;border-radius:2px;cursor:pointer;" title="Click to copy">[founder id="title"]</code></div>
                    <div style="font-size:13px;color:#374151;"><?php echo esc_html($title_val); ?></div>
                </div>
                <?php endif; ?>
                
                <?php if ($bio_short_val): ?>
                <div style="padding:10px 14px;background:#f9fafb;border-radius:6px;border:1px solid #e5e7eb;">
                    <div style="font-size:11px;color:#9ca3af;margin-bottom:3px;">Short Bio <code class="sfpf-copy-sc" style="font-size:10px;background:#f3f4f6;padding:1px 4px;border-radius:2px;cursor:pointer;" title="Click to copy">[founder id="biography_short"]</code></div>
                    <div style="font-size:12px;color:#374151;line-height:1.5;"><?php echo wp_trim_words(wp_strip_all_tags($bio_short_val), 30, '...'); ?></div>
                </div>
                <?php endif; ?>
                
                <?php if ($bio_val): ?>
                <div style="padding:10px 14px;background:#f9fafb;border-radius:6px;border:1px solid #e5e7eb;">
                    <div style="font-size:11px;color:#9ca3af;margin-bottom:3px;">Biography <code class="sfpf-copy-sc" style="font-size:10px;background:#f3f4f6;padding:1px 4px;border-radius:2px;cursor:pointer;" title="Click to copy">[founder id="biography"]</code></div>
                    <div style="font-size:12px;color:#374151;line-height:1.5;"><?php echo wp_trim_words(wp_strip_all_tags($bio_val), 30, '...'); ?></div>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($professions_val) && is_array($professions_val)): 
                    $prof_names = array_filter(array_map(function($p) { return $p['name'] ?? ''; }, $professions_val));
                ?>
                <div style="padding:10px 14px;background:#f9fafb;border-radius:6px;border:1px solid #e5e7eb;">
                    <div style="font-size:11px;color:#9ca3af;margin-bottom:3px;">Professions <code class="sfpf-copy-sc" style="font-size:10px;background:#f3f4f6;padding:1px 4px;border-radius:2px;cursor:pointer;" title="Click to copy">[founder action="display_professions_with_summary"]</code></div>
                    <div style="font-size:12px;color:#374151;"><?php echo esc_html(implode(', ', $prof_names)); ?></div>
                </div>
                <?php endif; ?>
                
                <?php if (is_array($urls_val) && !empty(array_filter($urls_val))): ?>
                <div style="padding:10px 14px;background:#f9fafb;border-radius:6px;border:1px solid #e5e7eb;">
                    <div style="font-size:11px;color:#9ca3af;margin-bottom:5px;">Social URLs</div>
                    <div style="display:grid;gap:4px;">
                        <?php foreach ($urls_val as $platform => $url): if (!empty($url)): ?>
                            <div style="display:flex;align-items:center;gap:8px;font-size:12px;">
                                <span style="color:#6b7280;min-width:70px;"><?php echo esc_html(ucfirst($platform)); ?></span>
                                <a href="<?php echo esc_url($url); ?>" target="_blank" style="color:#374151;text-decoration:none;word-break:break-all;"><?php echo esc_html(preg_replace('#^https?://#', '', rtrim($url, '/'))); ?> ↗</a>
                            </div>
                        <?php endif; endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Person Profile Checklist -->
            <?php
            $person_checks = [
                ['label' => 'Avatar',             'field' => '__avatar',       'context' => $user_key, 'shortcode' => '[founder id="avatar" size="thumbnail"]'],
                ['label' => 'Title/Role',        'field' => 'title',          'context' => $user_key, 'shortcode' => '[founder id="title"]'],
                ['label' => 'Biography',          'field' => 'biography',      'context' => $user_key, 'shortcode' => '[founder id="biography"]'],
                ['label' => 'Short Biography',    'field' => 'biography_short','context' => $user_key, 'shortcode' => '[founder id="biography_short"]'],
                ['label' => 'Professions',        'field' => 'professions',    'context' => $user_key, 'shortcode' => '[founder action="display_professions_with_summary"]'],
                ['label' => 'Facebook URL',       'field' => 'urls.facebook',  'context' => $user_key, 'shortcode' => '[founder id="url_facebook"]'],
                ['label' => 'Instagram URL',      'field' => 'urls.instagram', 'context' => $user_key, 'shortcode' => '[founder id="url_instagram"]'],
                ['label' => 'LinkedIn URL',       'field' => 'urls.linkedin',  'context' => $user_key, 'shortcode' => '[founder id="url_linkedin"]'],
                ['label' => 'X (Twitter) URL',    'field' => 'urls.x',         'context' => $user_key, 'shortcode' => '[founder id="url_x"]'],
                ['label' => 'Crunchbase URL',      'field' => 'urls.crunchbase','context' => $user_key, 'shortcode' => '[founder id="url_crunchbase"]'],
            ];
            echo render_field_checklist(run_field_checklist($person_checks), 'Person Profile Completeness');
            ?>
            
        <?php else: ?>
            <div class="sfpf-alert sfpf-alert-warning">
                <strong>⚠ No user assigned</strong><br>
                Please assign a user in Website Settings.
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Primary Organization -->
    <div class="sfpf-card">
        <div class="sfpf-card-header">
            <span class="dashicons dashicons-building" style="color:#059669;"></span>
            <h3>Primary Organization</h3>
        </div>
        
        <?php 
        $primary_org = get_primary_organization();
        if ($primary_org): 
            $org_logo = get_field('image_cropped', $primary_org->ID);
            $org_url = get_field('url', $primary_org->ID);
            $org_hq = get_field('headquarters', $primary_org->ID);
        ?>
            <div class="sfpf-profile-card">
                <div class="sfpf-profile-avatar">
                    <?php if ($org_logo && isset($org_logo['url'])): ?>
                        <img src="<?php echo esc_url($org_logo['url']); ?>" alt="">
                    <?php else: ?>
                        <span class="dashicons dashicons-building" style="font-size:40px;color:#9ca3af;"></span>
                    <?php endif; ?>
                </div>
                <div class="sfpf-profile-info" style="flex:1;">
                    <h4><?php echo esc_html($primary_org->post_title); ?></h4>
                    
                    <?php if ($org_url): ?>
                    <div class="sfpf-profile-meta">
                        <span><span class="dashicons dashicons-admin-site" style="font-size:14px;color:#6b7280;"></span> 
                            <a href="<?php echo esc_url($org_url); ?>" target="_blank"><?php echo esc_html(preg_replace('#^https?://#', '', rtrim($org_url, '/'))); ?></a>
                        </span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($org_hq && !empty($org_hq['location'])): ?>
                    <div style="margin-top:5px;font-size:13px;color:#666;">
                        <span class="dashicons dashicons-location" style="font-size:14px;"></span> <?php echo esc_html($org_hq['location']); ?>
                    </div>
                    <?php endif; ?>
                    
                    <div style="margin-top:15px;display:flex;gap:10px;">
                        <a href="<?php echo esc_url(get_edit_post_link($primary_org->ID)); ?>" target="_blank" class="button button-secondary">Edit</a>
                        <a href="<?php echo esc_url(get_permalink($primary_org->ID)); ?>" target="_blank" class="button button-secondary">View</a>
                    </div>
                </div>
            </div>
            
            <!-- Extended Organization Info -->
            <?php
            $org_summary = get_field('short_summary', $primary_org->ID);
            $org_mission = get_field('mission_statement', $primary_org->ID);
            $org_founding = get_field('founding_date', $primary_org->ID);
            $org_fb = get_field('url_facebook', $primary_org->ID);
            $org_ig = get_field('url_instagram', $primary_org->ID);
            $org_li = get_field('url_linkedin', $primary_org->ID);
            $org_x = get_field('url_x', $primary_org->ID);
            $org_yt = get_field('url_youtube', $primary_org->ID);
            ?>
            <div style="margin-top:15px;display:grid;gap:8px;">
                <?php if ($org_founding): ?>
                <div style="padding:10px 14px;background:#f9fafb;border-radius:6px;border:1px solid #e5e7eb;">
                    <div style="font-size:11px;color:#9ca3af;margin-bottom:3px;">Founded <code class="sfpf-copy-sc" style="font-size:10px;background:#f3f4f6;padding:1px 4px;border-radius:2px;cursor:pointer;" title="Click to copy">[organization field="founding_date"]</code></div>
                    <div style="font-size:13px;color:#374151;"><?php echo esc_html($org_founding); ?></div>
                </div>
                <?php endif; ?>
                
                <?php if ($org_summary): ?>
                <div style="padding:10px 14px;background:#f9fafb;border-radius:6px;border:1px solid #e5e7eb;">
                    <div style="font-size:11px;color:#9ca3af;margin-bottom:3px;">Summary <code class="sfpf-copy-sc" style="font-size:10px;background:#f3f4f6;padding:1px 4px;border-radius:2px;cursor:pointer;" title="Click to copy">[organization field="short_summary"]</code></div>
                    <div style="font-size:12px;color:#374151;line-height:1.5;"><?php echo wp_trim_words(wp_strip_all_tags($org_summary), 25, '...'); ?></div>
                </div>
                <?php endif; ?>
                
                <?php 
                $org_social_urls = array_filter([
                    'Facebook' => $org_fb, 'Instagram' => $org_ig, 'LinkedIn' => $org_li,
                    'X' => $org_x, 'YouTube' => $org_yt,
                ]);
                if (!empty($org_social_urls)): ?>
                <div style="padding:10px 14px;background:#f9fafb;border-radius:6px;border:1px solid #e5e7eb;">
                    <div style="font-size:11px;color:#9ca3af;margin-bottom:5px;">Social URLs</div>
                    <div style="display:grid;gap:4px;">
                        <?php foreach ($org_social_urls as $label => $url): ?>
                            <div style="display:flex;align-items:center;gap:8px;font-size:12px;">
                                <span style="color:#6b7280;min-width:70px;"><?php echo esc_html($label); ?></span>
                                <a href="<?php echo esc_url($url); ?>" target="_blank" style="color:#374151;text-decoration:none;word-break:break-all;"><?php echo esc_html(preg_replace('#^https?://#', '', rtrim($url, '/'))); ?> ↗</a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Organization Checklist -->
            <?php
            $org_checks = [
                ['label' => 'Short Summary',    'field' => 'short_summary',     'context' => $primary_org->ID, 'shortcode' => '[organization field="short_summary"]'],
                ['label' => 'Mission Statement', 'field' => 'mission_statement', 'context' => $primary_org->ID, 'shortcode' => '[organization field="mission_statement"]'],
                ['label' => 'Founding Date',     'field' => 'founding_date',     'context' => $primary_org->ID, 'shortcode' => '[organization field="founding_date"]'],
                ['label' => 'HQ Location',       'field' => 'headquarters',      'context' => $primary_org->ID, 'shortcode' => '[organization field="headquarters_location"]'],
                ['label' => 'Website URL',       'field' => 'url',               'context' => $primary_org->ID, 'shortcode' => '[organization field="url"]'],
                ['label' => 'Facebook URL',      'field' => 'url_facebook',      'context' => $primary_org->ID, 'shortcode' => '[organization field="url_facebook"]'],
                ['label' => 'Instagram URL',     'field' => 'url_instagram',     'context' => $primary_org->ID, 'shortcode' => '[organization field="url_instagram"]'],
                ['label' => 'LinkedIn URL',      'field' => 'url_linkedin',      'context' => $primary_org->ID, 'shortcode' => '[organization field="url_linkedin"]'],
                ['label' => 'X (Twitter) URL',   'field' => 'url_x',            'context' => $primary_org->ID, 'shortcode' => '[organization field="url_x"]'],
                ['label' => 'Crunchbase URL',    'field' => 'url_crunchbase',    'context' => $primary_org->ID, 'shortcode' => '[organization field="url_crunchbase"]'],
            ];
            echo render_field_checklist(run_field_checklist($org_checks), 'Organization Completeness');
            ?>
        <?php else: ?>
            <div class="sfpf-alert sfpf-alert-warning">
                <strong>⚠ No primary organization</strong><br>
                Set one in Settings tab or <a href="<?php echo admin_url('post-new.php?post_type=organization'); ?>">create one</a>.
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- All Organizations Summary -->
<?php 
$all_orgs = get_posts(['post_type' => 'organization', 'posts_per_page' => -1, 'post_status' => 'publish', 'orderby' => 'title', 'order' => 'ASC']);
if (!empty($all_orgs)): 
?>
<div class="sfpf-card">
    <div class="sfpf-card-header">
        <span class="dashicons dashicons-building" style="color:#059669;"></span>
        <h3>All Organizations (<?php echo count($all_orgs); ?>)</h3>
        <span style="margin-left:auto;">
            <a href="<?php echo admin_url('edit.php?post_type=organization'); ?>" target="_blank" class="button button-small">Manage All →</a>
        </span>
    </div>
    
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:15px;">
        <?php foreach ($all_orgs as $org): 
            $org_logo = get_field('image_cropped', $org->ID);
            $org_permalink = get_permalink($org->ID);
        ?>
        <div style="background:#f9fafb;border-radius:8px;padding:15px;border:1px solid #e5e7eb;">
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:10px;">
                <?php if ($org_logo && isset($org_logo['url'])): ?>
                    <img src="<?php echo esc_url($org_logo['url']); ?>" style="width:40px;height:40px;object-fit:contain;border-radius:4px;" alt="">
                <?php else: ?>
                    <span class="dashicons dashicons-building" style="font-size:32px;color:#9ca3af;"></span>
                <?php endif; ?>
                <div>
                    <strong><a href="<?php echo esc_url($org_permalink); ?>" target="_blank"><?php echo esc_html($org->post_title); ?></a></strong>
                    <div style="font-size:11px;color:#6b7280;"><?php echo esc_html($org_permalink); ?></div>
                </div>
            </div>
            <div style="display:flex;gap:5px;flex-wrap:wrap;">
                <a href="<?php echo esc_url(get_edit_post_link($org->ID)); ?>" target="_blank" class="button button-small">Edit</a>
                <a href="https://validator.schema.org/#url=<?php echo urlencode($org_permalink); ?>" target="_blank" class="button button-small" title="Schema.org Validator">Schema</a>
                <a href="https://search.google.com/test/rich-results?url=<?php echo urlencode($org_permalink); ?>" target="_blank" class="button button-small" title="Google Rich Results">Google</a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- All Books Summary -->
<?php 
$all_books = get_posts(['post_type' => 'book', 'posts_per_page' => -1, 'post_status' => 'publish', 'orderby' => 'title', 'order' => 'ASC']);
if (!empty($all_books)): 
?>
<div class="sfpf-card">
    <div class="sfpf-card-header">
        <span class="dashicons dashicons-book" style="color:#8b5cf6;"></span>
        <h3>All Books (<?php echo count($all_books); ?>)</h3>
        <span style="margin-left:auto;">
            <a href="<?php echo admin_url('edit.php?post_type=book'); ?>" target="_blank" class="button button-small">Manage All →</a>
        </span>
    </div>
    
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:15px;">
        <?php foreach ($all_books as $book): 
            $book_cover_url = get_the_post_thumbnail_url($book->ID, 'thumbnail');
            $book_permalink = get_permalink($book->ID);
        ?>
        <div style="background:#f9fafb;border-radius:8px;padding:15px;border:1px solid #e5e7eb;">
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:10px;">
                <?php if ($book_cover_url): ?>
                    <img src="<?php echo esc_url($book_cover_url); ?>" style="width:40px;height:60px;object-fit:cover;border-radius:4px;" alt="">
                <?php else: ?>
                    <span class="dashicons dashicons-book" style="font-size:32px;color:#9ca3af;"></span>
                <?php endif; ?>
                <div>
                    <strong><a href="<?php echo esc_url($book_permalink); ?>" target="_blank"><?php echo esc_html($book->post_title); ?></a></strong>
                    <div style="font-size:11px;color:#6b7280;"><?php echo esc_html($book_permalink); ?></div>
                </div>
            </div>
            <div style="display:flex;gap:5px;flex-wrap:wrap;">
                <a href="<?php echo esc_url(get_edit_post_link($book->ID)); ?>" target="_blank" class="button button-small">Edit</a>
                <a href="https://validator.schema.org/#url=<?php echo urlencode($book_permalink); ?>" target="_blank" class="button button-small" title="Schema.org Validator">Schema</a>
                <a href="https://search.google.com/test/rich-results?url=<?php echo urlencode($book_permalink); ?>" target="_blank" class="button button-small" title="Google Rich Results">Google</a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- Schema Detection Tool -->
<div class="sfpf-card">
    <div class="sfpf-card-header">
        <span class="dashicons dashicons-search" style="color:#f59e0b;"></span>
        <h3>Schema Detection Tool</h3>
    </div>
    
    <p style="color:#6b7280;margin-bottom:15px;">Scan your pages to detect JSON-LD schema markup and identify sources.</p>
    
    <div style="display:flex;flex-wrap:wrap;gap:10px;margin-bottom:15px;">
        <button type="button" class="button button-primary sfpf-detect-schema" data-type="full_site" style="background:#6366f1;border-color:#6366f1;">
            🔍 Scan Entire Site
        </button>
        <button type="button" class="button button-secondary sfpf-detect-schema" data-type="homepage">
            🏠 Scan Homepage
        </button>
        <button type="button" class="button button-secondary sfpf-detect-schema" data-type="biography">
            📝 Scan Biography
        </button>
        <button type="button" class="button button-secondary sfpf-detect-schema" data-type="books">
            📚 Scan Books
        </button>
        <button type="button" class="button button-secondary sfpf-detect-schema" data-type="organizations">
            🏢 Scan Organizations
        </button>
        <button type="button" class="button button-secondary sfpf-detect-schema" data-type="testimonials">
            💬 Scan Testimonials
        </button>
        <label style="display:flex;align-items:center;gap:5px;margin-left:10px;">
            <input type="checkbox" id="sfpf-schema-debug" value="1">
            <span style="font-size:12px;color:#6b7280;">Show debug info</span>
        </label>
    </div>
    
    <div id="sfpf-schema-results" style="background:#1e1e2e;border-radius:6px;padding:15px;font-family:monospace;font-size:12px;color:#cdd6f4;min-height:100px;max-height:400px;overflow-y:auto;">
        <span style="color:#6b7280;">Click a button above to scan for schema markup...</span>
    </div>
</div>

<!-- RankMath Breadcrumbs Section -->
<div class="sfpf-card">
    <div class="sfpf-card-header">
        <span class="dashicons dashicons-admin-links" style="color:#e91e63;"></span>
        <h3>RankMath Breadcrumbs</h3>
    </div>
    
    <?php
    $rm_active = is_plugin_active('seo-by-rank-math/rank-math.php');
    $rm_pro_active = is_plugin_active('seo-by-rank-math-pro/rank-math-pro.php');
    
    $rm_version = '';
    $rm_pro_version = '';
    
    if ($rm_active && function_exists('rank_math')) {
        $rm_version = defined('RANK_MATH_VERSION') ? RANK_MATH_VERSION : 'Unknown';
    }
    if ($rm_pro_active) {
        $rm_pro_version = defined('RANK_MATH_PRO_VERSION') ? RANK_MATH_PRO_VERSION : 'Unknown';
    }
    
    $breadcrumbs_enabled = false;
    if ($rm_active && class_exists('RankMath\Helper')) {
        $breadcrumbs_enabled = \RankMath\Helper::is_breadcrumbs_enabled();
    }
    
    // Get saved breadcrumb settings
    $bc_hide_front = get_option('sfpf_breadcrumb_hide_frontpage', false);
    $bc_excluded_pages = get_option('sfpf_breadcrumb_excluded_pages', []);
    $bc_excluded_cpts = get_option('sfpf_breadcrumb_excluded_cpts', []);
    if (!is_array($bc_excluded_pages)) $bc_excluded_pages = [];
    if (!is_array($bc_excluded_cpts)) $bc_excluded_cpts = [];
    ?>
    
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:15px;margin-bottom:15px;">
        <div style="background:#f9fafb;padding:12px;border-radius:6px;border:1px solid #e5e7eb;">
            <strong>RankMath SEO</strong>
            <?php if ($rm_active): ?>
                <span style="background:#dcfce7;color:#166534;padding:2px 8px;border-radius:3px;font-size:11px;margin-left:8px;">Active</span>
                <div style="font-size:12px;color:#666;margin-top:5px;">Version: <?php echo esc_html($rm_version); ?></div>
            <?php else: ?>
                <span style="background:#fef2f2;color:#dc2626;padding:2px 8px;border-radius:3px;font-size:11px;margin-left:8px;">Not Active</span>
            <?php endif; ?>
        </div>
        
        <div style="background:#f9fafb;padding:12px;border-radius:6px;border:1px solid #e5e7eb;">
            <strong>RankMath PRO</strong>
            <?php if ($rm_pro_active): ?>
                <span style="background:#dcfce7;color:#166534;padding:2px 8px;border-radius:3px;font-size:11px;margin-left:8px;">Active</span>
                <div style="font-size:12px;color:#666;margin-top:5px;">Version: <?php echo esc_html($rm_pro_version); ?></div>
            <?php else: ?>
                <span style="background:#fef3cd;color:#856404;padding:2px 8px;border-radius:3px;font-size:11px;margin-left:8px;">Not Active</span>
            <?php endif; ?>
        </div>
        
        <div style="background:#f9fafb;padding:12px;border-radius:6px;border:1px solid #e5e7eb;">
            <strong>Breadcrumbs</strong>
            <?php if ($breadcrumbs_enabled): ?>
                <span style="background:#dcfce7;color:#166534;padding:2px 8px;border-radius:3px;font-size:11px;margin-left:8px;">Enabled</span>
            <?php else: ?>
                <span style="background:#fef2f2;color:#dc2626;padding:2px 8px;border-radius:3px;font-size:11px;margin-left:8px;">Disabled</span>
            <?php endif; ?>
        </div>
    </div>
    
    <?php if ($rm_active): ?>
    <!-- Breadcrumb Visibility Controls -->
    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:8px;padding:20px;margin-bottom:15px;">
        <strong style="display:block;margin-bottom:14px;color:#374151;font-size:14px;">Breadcrumb Visibility</strong>
        
        <!-- Hide from front page -->
        <div style="margin-bottom:16px;">
            <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;">
                <input type="checkbox" name="sfpf_bc_hide_frontpage" value="1" <?php checked($bc_hide_front); ?> style="margin:0;">
                <span>Hide breadcrumbs from <strong>Front Page</strong></span>
            </label>
            <div style="margin-left:26px;font-size:11px;color:#9ca3af;margin-top:2px;">Removes the breadcrumb output on your homepage/front page.</div>
        </div>
        
        <!-- Exclude specific pages -->
        <div style="margin-bottom:16px;">
            <label style="display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:6px;">Exclude Specific Pages</label>
            <select id="sfpf-bc-excluded-pages" name="sfpf_bc_excluded_pages[]" multiple="multiple" style="width:100%;min-height:100px;font-size:12px;">
                <?php
                $all_pages = get_pages(['sort_column' => 'post_title', 'sort_order' => 'ASC']);
                foreach ($all_pages as $page) {
                    $selected = in_array($page->ID, $bc_excluded_pages) ? 'selected' : '';
                    echo '<option value="' . esc_attr($page->ID) . '" ' . $selected . '>' . esc_html($page->post_title) . ' (/' . esc_html($page->post_name) . '/)</option>';
                }
                ?>
            </select>
            <div style="font-size:11px;color:#9ca3af;margin-top:4px;">Hold Ctrl/Cmd to select multiple pages. Breadcrumbs will be hidden on selected pages.</div>
        </div>
        
        <!-- Exclude CPT singles -->
        <div style="margin-bottom:16px;">
            <label style="display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:6px;">Hide on Single Posts for Custom Post Types</label>
            <?php
            $cpts = get_post_types(['public' => true, '_builtin' => false], 'objects');
            // Also include built-in post and page for completeness
            $builtin = get_post_types(['public' => true, '_builtin' => true], 'objects');
            $all_types = array_merge($builtin, $cpts);
            ?>
            <div style="display:flex;flex-wrap:wrap;gap:10px 20px;">
                <?php foreach ($all_types as $pt): ?>
                <label style="display:flex;align-items:center;gap:6px;cursor:pointer;font-size:13px;">
                    <input type="checkbox" name="sfpf_bc_excluded_cpts[]" value="<?php echo esc_attr($pt->name); ?>" <?php if (in_array($pt->name, $bc_excluded_cpts)) echo 'checked'; ?> style="margin:0;">
                    <span><?php echo esc_html($pt->labels->singular_name); ?></span>
                    <code style="font-size:10px;color:#9ca3af;"><?php echo esc_html($pt->name); ?></code>
                </label>
                <?php endforeach; ?>
            </div>
            <div style="font-size:11px;color:#9ca3af;margin-top:6px;">Breadcrumbs will be hidden on the single/detail page for checked post types.</div>
        </div>
        
        <button type="button" id="sfpf-save-breadcrumb-settings" class="button button-primary" style="margin-top:4px;">
            💾 Save Breadcrumb Settings
        </button>
        <span id="sfpf-bc-save-status" style="margin-left:10px;font-size:12px;display:none;"></span>
    </div>
    
    <div style="background:#f0f9ff;border:1px solid #0ea5e9;border-radius:6px;padding:15px;">
        <strong style="color:#0369a1;">Breadcrumb Usage:</strong>
        <div style="margin-top:10px;display:grid;gap:8px;">
            <div>
                <code style="background:#e0f2fe;padding:4px 8px;border-radius:3px;font-size:12px;">if (function_exists('rank_math_the_breadcrumbs')) rank_math_the_breadcrumbs();</code>
                <span style="color:#666;font-size:11px;margin-left:8px;">PHP function</span>
            </div>
            <div>
                <code style="background:#e0f2fe;padding:4px 8px;border-radius:3px;font-size:12px;">[rank_math_breadcrumb]</code>
                <span style="color:#666;font-size:11px;margin-left:8px;">Shortcode</span>
            </div>
        </div>
    </div>
    
    <!-- Breadcrumb Preview -->
    <div style="margin-top:15px;background:#f9fafb;border-radius:6px;padding:15px;border:1px solid #e5e7eb;">
        <strong style="display:block;margin-bottom:10px;color:#374151;">Breadcrumb Preview Examples:</strong>
        
        <div style="margin-bottom:12px;">
            <span style="color:#6b7280;font-size:11px;display:block;margin-bottom:4px;">Homepage:</span>
            <div style="background:#fff;padding:8px 12px;border-radius:4px;font-size:13px;">
                <span style="color:#2563eb;">Home</span>
            </div>
        </div>
        
        <?php 
        $sample_org = get_posts(['post_type' => 'organization', 'posts_per_page' => 1, 'post_status' => 'publish']);
        if (!empty($sample_org)): 
        ?>
        <div style="margin-bottom:12px;">
            <span style="color:#6b7280;font-size:11px;display:block;margin-bottom:4px;">Organization (<?php echo esc_html($sample_org[0]->post_title); ?>):</span>
            <div style="background:#fff;padding:8px 12px;border-radius:4px;font-size:13px;">
                <span style="color:#2563eb;">Home</span> <span style="color:#9ca3af;">»</span> 
                <span style="color:#2563eb;">Organizations</span> <span style="color:#9ca3af;">»</span> 
                <span style="color:#374151;"><?php echo esc_html($sample_org[0]->post_title); ?></span>
            </div>
        </div>
        <?php endif; ?>
        
        <?php 
        $sample_book = get_posts(['post_type' => 'book', 'posts_per_page' => 1, 'post_status' => 'publish']);
        if (!empty($sample_book)): 
        ?>
        <div style="margin-bottom:12px;">
            <span style="color:#6b7280;font-size:11px;display:block;margin-bottom:4px;">Book (<?php echo esc_html($sample_book[0]->post_title); ?>):</span>
            <div style="background:#fff;padding:8px 12px;border-radius:4px;font-size:13px;">
                <span style="color:#2563eb;">Home</span> <span style="color:#9ca3af;">»</span> 
                <span style="color:#2563eb;">Books</span> <span style="color:#9ca3af;">»</span> 
                <span style="color:#374151;"><?php echo esc_html($sample_book[0]->post_title); ?></span>
            </div>
        </div>
        <?php endif; ?>
        
        <div>
            <span style="color:#6b7280;font-size:11px;display:block;margin-bottom:4px;">Biography Page:</span>
            <div style="background:#fff;padding:8px 12px;border-radius:4px;font-size:13px;">
                <span style="color:#2563eb;">Home</span> <span style="color:#9ca3af;">»</span> 
                <span style="color:#374151;">Biography</span>
            </div>
        </div>
    </div>
    
    <div style="margin-top:15px;">
        <a href="<?php echo admin_url('admin.php?page=rank-math-options-general&view=breadcrumbs'); ?>" target="_blank" class="button button-secondary">
            Breadcrumb Settings →
        </a>
    </div>
    
    <script>
    jQuery(function($) {
        $('#sfpf-save-breadcrumb-settings').on('click', function() {
            var $btn = $(this);
            var $status = $('#sfpf-bc-save-status');
            $btn.prop('disabled', true).text('Saving...');
            
            var excludedPages = [];
            $('#sfpf-bc-excluded-pages option:selected').each(function() {
                excludedPages.push($(this).val());
            });
            
            var excludedCpts = [];
            $('input[name="sfpf_bc_excluded_cpts[]"]:checked').each(function() {
                excludedCpts.push($(this).val());
            });
            
            $.post(ajaxurl, {
                action: 'sfpf_save_breadcrumb_settings',
                nonce: '<?php echo wp_create_nonce('sfpf_ajax'); ?>',
                hide_frontpage: $('input[name="sfpf_bc_hide_frontpage"]').is(':checked') ? 1 : 0,
                excluded_pages: excludedPages,
                excluded_cpts: excludedCpts
            }, function(response) {
                $btn.prop('disabled', false).html('💾 Save Breadcrumb Settings');
                if (response.success) {
                    $status.css({display: 'inline', color: '#16a34a'}).text('✓ Saved!');
                    setTimeout(function() { $status.fadeOut(); }, 3000);
                } else {
                    $status.css({display: 'inline', color: '#dc2626'}).text('✗ Error');
                }
            });
        });
    });
    </script>
    <?php endif; ?>
</div>

<!-- RankMath Sitemaps Section -->
<?php if ($rm_active): ?>
<div class="sfpf-card">
    <div class="sfpf-card-header">
        <span class="dashicons dashicons-sitemap" style="color:#10b981;"></span>
        <h3>RankMath Sitemaps</h3>
    </div>
    
    <?php
    $sitemap_base = home_url('/sitemap_index.xml');
    $sitemaps = [
        'Index' => home_url('/sitemap_index.xml'),
        'Posts' => home_url('/post-sitemap.xml'),
        'Pages' => home_url('/page-sitemap.xml'),
        'Organizations' => home_url('/organization-sitemap.xml'),
        'Books' => home_url('/book-sitemap.xml'),
        'Testimonials' => home_url('/testimonial-sitemap.xml'),
    ];
    ?>
    
    <div style="display:grid;gap:8px;">
        <?php foreach ($sitemaps as $name => $url): ?>
        <div style="display:flex;align-items:center;justify-content:space-between;padding:10px;background:#f9fafb;border-radius:4px;">
            <div>
                <strong style="font-size:13px;"><?php echo esc_html($name); ?></strong>
                <div style="font-size:11px;color:#6b7280;"><?php echo esc_html($url); ?></div>
            </div>
            <a href="<?php echo esc_url($url); ?>" target="_blank" class="button button-small">View →</a>
        </div>
        <?php endforeach; ?>
    </div>
    
    <div style="margin-top:15px;display:flex;gap:10px;">
        <a href="<?php echo admin_url('admin.php?page=rank-math-options-sitemap'); ?>" target="_blank" class="button button-secondary">
            Sitemap Settings →
        </a>
        <a href="<?php echo admin_url('admin.php?page=rank-math-status&view=status'); ?>" target="_blank" class="button button-secondary">
            Status & Tools →
        </a>
    </div>
</div>
<?php endif; ?>

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
                
                <?php 
                $items = $set['items'] ?? [];
                foreach (array_slice($items, 0, 3) as $item): 
                    if (!empty($item['question'])):
                ?>
                <div style="margin-bottom:10px;padding:10px;background:#fff;border-radius:4px;border-left:3px solid #8b5cf6;">
                    <div style="font-weight:600;font-size:13px;color:#1f2937;margin-bottom:5px;">
                        Q: <?php echo esc_html($item['question']); ?>
                    </div>
                    <?php if (!empty($item['answer'])): ?>
                    <div style="font-size:12px;color:#6b7280;">
                        A: <?php echo esc_html(wp_trim_words(strip_tags($item['answer']), 25, '...')); ?>
                    </div>
                    <?php endif; ?>
                </div>
                <?php 
                    endif;
                endforeach; 
                if (count($items) > 3): 
                ?>
                    <div style="font-size:12px;color:#6b7280;font-style:italic;">+ <?php echo (count($items) - 3); ?> more questions...</div>
                <?php endif; ?>
                
                <code style="background:#e8f4fc;padding:3px 8px;border-radius:3px;font-size:11px;margin-top:10px;display:inline-block;">[sfpf_faq set="<?php echo esc_attr($set['slug']); ?>"]</code>
            </div>
        <?php endif; endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- SFPF Plugin Info Section -->
<?php sfpf_display_plugin_info(); ?>

<!-- Schema Detection AJAX Script -->
<script>
jQuery(document).ready(function($) {
    // Schema detection
    $('.sfpf-detect-schema').on('click', function() {
        var $btn = $(this);
        var type = $btn.data('type');
        var debug = $('#sfpf-schema-debug').is(':checked') ? 1 : 0;
        var $results = $('#sfpf-schema-results');
        
        $btn.prop('disabled', true);
        $results.html('<span style="color:#fbbf24;">🔄 Scanning ' + type + '...</span>');
        
        $.post(ajaxurl, {
            action: 'sfpf_detect_schema',
            nonce: '<?php echo wp_create_nonce('sfpf_ajax'); ?>',
            type: type,
            debug: debug
        }, function(response) {
            if (response.success) {
                $results.html(response.data.output);
            } else {
                $results.html('<span style="color:#f87171;">❌ Error: ' + (response.data || 'Unknown error') + '</span>');
            }
            $btn.prop('disabled', false);
        }).fail(function() {
            $results.html('<span style="color:#f87171;">❌ AJAX request failed</span>');
            $btn.prop('disabled', false);
        });
    });
    
    // Copy shortcode to clipboard
    $(document).on('click', '.sfpf-copy-sc', function() {
        var text = $(this).text();
        navigator.clipboard.writeText(text).then(function() {
            var $toast = $('<div style="position:fixed;top:50px;right:20px;z-index:9999;padding:10px 16px;background:#f3f4f6;border:1px solid #d1d5db;border-radius:6px;box-shadow:0 2px 8px rgba(0,0,0,0.1);font-size:12px;color:#374151;">Copied: ' + text.substring(0, 40) + '</div>');
            $('body').append($toast);
            setTimeout(function() { $toast.fadeOut(function() { $(this).remove(); }); }, 1500);
        });
    });
});
</script>
