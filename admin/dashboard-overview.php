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
            $book_cover = get_field('cover', $book->ID);
            $book_permalink = get_permalink($book->ID);
        ?>
        <div style="background:#f9fafb;border-radius:8px;padding:15px;border:1px solid #e5e7eb;">
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:10px;">
                <?php if ($book_cover && isset($book_cover['url'])): ?>
                    <img src="<?php echo esc_url($book_cover['url']); ?>" style="width:40px;height:60px;object-fit:cover;border-radius:4px;" alt="">
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
        <button type="button" class="button button-secondary sfpf-detect-schema" data-type="homepage">
            🏠 Scan Homepage
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
        // Get a sample organization for preview
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
        // Get a sample book for preview
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

<!-- System Checks -->
<div class="sfpf-card">
    <div class="sfpf-card-header">
        <span class="dashicons dashicons-yes-alt" style="color:#10b981;"></span>
        <h3>System Checks</h3>
    </div>
    
    <table class="widefat striped" style="margin-top:10px;">
        <tbody>
            <?php
            $checks = [
                'HWS Base Tools' => $hws_info['active'],
                'Founder User Configured' => !empty($founder),
                'Primary Organization Set' => !empty(get_option('sfpf_primary_organization', 0)),
                'Primary Book Set' => !empty(get_option('sfpf_primary_book', 0)),
                'ACF Pro Active' => class_exists('ACF'),
                'Elementor Active' => defined('ELEMENTOR_VERSION'),
                'RankMath Active' => $rm_active,
            ];
            
            foreach ($checks as $label => $status):
            ?>
            <tr>
                <td style="width:250px;font-weight:500;"><?php echo esc_html($label); ?></td>
                <td>
                    <?php if ($status): ?>
                        <span style="color:#059669;">✓ OK</span>
                    <?php else: ?>
                        <span style="color:#dc2626;">✗ Not configured</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

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
