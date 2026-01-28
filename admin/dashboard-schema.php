<?php
namespace sfpf_person_website;

/**
 * Dashboard Schema Tab - Uses exact schema structures provided
 * 
 * @package sfpf_person_website
 * @since 1.0.0
 */

defined('ABSPATH') || exit;

$site_url = get_site_url_clean();
$homepage_schema_type = get_option('sfpf_homepage_schema_type', 'none');

// RankMath disable options
$rankmath_disable_homepage = get_option('sfpf_rankmath_disable_homepage', false);
$rankmath_disable_books = get_option('sfpf_rankmath_disable_books', false);
$rankmath_disable_organizations = get_option('sfpf_rankmath_disable_organizations', false);
$rankmath_disable_testimonials = get_option('sfpf_rankmath_disable_testimonials', false);

?>

<!-- Homepage Schema Type Selection -->
<div class="sfpf-card">
    <div class="sfpf-card-header">
        <span class="dashicons dashicons-admin-home" style="color:#2563eb;"></span>
        <h3>Homepage Schema Configuration</h3>
    </div>
    
    <p style="color:#666;margin-bottom:15px;">Choose the schema type for your homepage:</p>
    
    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(180px, 1fr));gap:12px;margin-bottom:20px;">
        <label style="display:flex;align-items:flex-start;gap:10px;padding:12px;background:<?php echo $homepage_schema_type === 'none' ? '#f3f4f6' : '#fff'; ?>;border:2px solid <?php echo $homepage_schema_type === 'none' ? '#6b7280' : '#e5e7eb'; ?>;border-radius:8px;cursor:pointer;">
            <input type="radio" name="homepage_schema_type" value="none" <?php checked($homepage_schema_type, 'none'); ?> class="sfpf-schema-type-radio" style="margin-top:3px;">
            <div>
                <strong>None</strong>
                <div style="font-size:11px;color:#666;">Disable schema injection</div>
            </div>
        </label>
        
        <label style="display:flex;align-items:flex-start;gap:10px;padding:12px;background:<?php echo $homepage_schema_type === 'profile_page_only' ? '#dbeafe' : '#fff'; ?>;border:2px solid <?php echo $homepage_schema_type === 'profile_page_only' ? '#2563eb' : '#e5e7eb'; ?>;border-radius:8px;cursor:pointer;">
            <input type="radio" name="homepage_schema_type" value="profile_page_only" <?php checked($homepage_schema_type, 'profile_page_only'); ?> class="sfpf-schema-type-radio" style="margin-top:3px;">
            <div>
                <strong>ProfilePage Only</strong>
                <div style="font-size:11px;color:#666;">ProfilePage without Person</div>
            </div>
        </label>
        
        <label style="display:flex;align-items:flex-start;gap:10px;padding:12px;background:<?php echo $homepage_schema_type === 'person' ? '#dbeafe' : '#fff'; ?>;border:2px solid <?php echo $homepage_schema_type === 'person' ? '#2563eb' : '#e5e7eb'; ?>;border-radius:8px;cursor:pointer;">
            <input type="radio" name="homepage_schema_type" value="person" <?php checked($homepage_schema_type, 'person'); ?> class="sfpf-schema-type-radio" style="margin-top:3px;">
            <div>
                <strong>Person Only</strong>
                <div style="font-size:11px;color:#666;">Simple person schema</div>
            </div>
        </label>
        
        <label style="display:flex;align-items:flex-start;gap:10px;padding:12px;background:<?php echo $homepage_schema_type === 'profile_page' ? '#dcfce7' : '#fff'; ?>;border:2px solid <?php echo $homepage_schema_type === 'profile_page' ? '#16a34a' : '#e5e7eb'; ?>;border-radius:8px;cursor:pointer;">
            <input type="radio" name="homepage_schema_type" value="profile_page" <?php checked($homepage_schema_type, 'profile_page'); ?> class="sfpf-schema-type-radio" style="margin-top:3px;">
            <div>
                <strong>ProfilePage + Person</strong>
                <div style="font-size:11px;color:#666;">Full profile</div>
            </div>
        </label>
    </div>
    
    <button type="button" class="button button-primary" id="sfpf-save-schema-type">💾 Save Schema Type</button>
</div>

<!-- RankMath Schema Control -->
<div class="sfpf-card">
    <div class="sfpf-card-header">
        <span class="dashicons dashicons-shield" style="color:#f59e0b;"></span>
        <h3>RankMath Schema Control</h3>
    </div>
    
    <p style="color:#666;margin-bottom:15px;">Disable RankMath from injecting its own schema on specific post types:</p>
    
    <div style="display:flex;flex-direction:column;gap:12px;">
        <label style="display:flex;align-items:center;gap:10px;cursor:pointer;">
            <input type="checkbox" name="rankmath_disable_homepage" value="1" <?php checked($rankmath_disable_homepage, true); ?> class="sfpf-rankmath-toggle">
            <span>Disable RankMath schema on <strong>Homepage</strong></span>
        </label>
        
        <?php if (is_snippet_enabled('sfpf_enable_book_cpt')): ?>
        <label style="display:flex;align-items:center;gap:10px;cursor:pointer;">
            <input type="checkbox" name="rankmath_disable_books" value="1" <?php checked($rankmath_disable_books, true); ?> class="sfpf-rankmath-toggle">
            <span>Disable RankMath schema on <strong>Books</strong></span>
        </label>
        <?php endif; ?>
        
        <?php if (is_snippet_enabled('sfpf_enable_organization_cpt')): ?>
        <label style="display:flex;align-items:center;gap:10px;cursor:pointer;">
            <input type="checkbox" name="rankmath_disable_organizations" value="1" <?php checked($rankmath_disable_organizations, true); ?> class="sfpf-rankmath-toggle">
            <span>Disable RankMath schema on <strong>Organizations</strong></span>
        </label>
        <?php endif; ?>
        
        <?php if (is_snippet_enabled('sfpf_enable_testimonial_cpt')): ?>
        <label style="display:flex;align-items:center;gap:10px;cursor:pointer;">
            <input type="checkbox" name="rankmath_disable_testimonials" value="1" <?php checked($rankmath_disable_testimonials, true); ?> class="sfpf-rankmath-toggle">
            <span>Disable RankMath schema on <strong>Testimonials</strong></span>
        </label>
        <?php endif; ?>
    </div>
    
    <div style="margin-top:15px;">
        <button type="button" class="button button-primary" id="sfpf-save-rankmath-settings">💾 Save RankMath Settings</button>
    </div>
</div>

<!-- Schema Detection Tool -->
<div class="sfpf-card">
    <div class="sfpf-card-header">
        <span class="dashicons dashicons-search" style="color:#8b5cf6;"></span>
        <h3>Schema Detection Tool</h3>
    </div>
    
    <p style="color:#666;margin-bottom:15px;">Detect and analyze schema objects on your site:</p>
    
    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(220px, 1fr));gap:12px;margin-bottom:15px;">
        <button type="button" class="button sfpf-detect-schema" data-type="homepage">
            <span class="dashicons dashicons-admin-home" style="vertical-align:middle;"></span> Detect Homepage Schema
        </button>
        
        <?php if (is_snippet_enabled('sfpf_enable_book_cpt')): ?>
        <button type="button" class="button sfpf-detect-schema" data-type="books">
            <span class="dashicons dashicons-book" style="vertical-align:middle;"></span> Detect Book Schemas
        </button>
        <?php endif; ?>
        
        <?php if (is_snippet_enabled('sfpf_enable_organization_cpt')): ?>
        <button type="button" class="button sfpf-detect-schema" data-type="organizations">
            <span class="dashicons dashicons-building" style="vertical-align:middle;"></span> Detect Organization Schemas
        </button>
        <?php endif; ?>
        
        <?php if (is_snippet_enabled('sfpf_enable_testimonial_cpt')): ?>
        <button type="button" class="button sfpf-detect-schema" data-type="testimonials">
            <span class="dashicons dashicons-format-quote" style="vertical-align:middle;"></span> Detect Testimonial Schemas
        </button>
        <?php endif; ?>
    </div>
    
    <label style="display:flex;align-items:center;gap:10px;margin-bottom:15px;cursor:pointer;">
        <input type="checkbox" id="sfpf-debug-mode" value="1">
        <span>🐛 <strong>Debug Mode</strong> - Show detailed output including schema source detection</span>
    </label>
    
    <div id="sfpf-schema-detection-results" style="display:none;background:#1e1e1e;color:#d4d4d4;padding:15px;border-radius:6px;font-family:monospace;font-size:12px;max-height:400px;overflow:auto;"></div>
</div>

<!-- Schema Templates - 2 per row -->
<div class="sfpf-card">
    <div class="sfpf-card-header">
        <span class="dashicons dashicons-media-code" style="color:#8b5cf6;"></span>
        <h3>Schema Templates</h3>
        <span style="margin-left:auto;font-size:12px;color:#666;">Raw skeleton structures - exact format from schema.org</span>
    </div>
    
    <div style="display:grid;grid-template-columns:repeat(2, 1fr);gap:20px;">
        <!-- ProfilePage Template -->
        <div>
            <h4 style="margin:0 0 10px 0;font-size:14px;">ProfilePage Schema</h4>
            <?php
            $profile_page_template = [
                '@type' => 'ProfilePage',
                '@id' => 'https://example.com/#profilepage',
                'url' => 'https://example.com/',
                'name' => 'Person Name | Title',
                'description' => 'Person description.',
                'inLanguage' => 'en-US',
                'isPartOf' => [
                    '@type' => 'WebSite',
                    '@id' => 'https://example.com/#website',
                    'url' => 'https://example.com/',
                    'name' => 'Site Name',
                ],
                'primaryImageOfPage' => [
                    '@type' => 'ImageObject',
                    '@id' => 'https://example.com/#headshot',
                    'url' => 'https://example.com/headshot.jpeg',
                ],
                'mainEntity' => ['@id' => 'https://example.com/#person'],
            ];
            echo format_json_display($profile_page_template, true);
            ?>
        </div>
        
        <!-- Person Template -->
        <div>
            <h4 style="margin:0 0 10px 0;font-size:14px;">Person Schema</h4>
            <?php
            $person_template = [
                '@type' => 'Person',
                '@id' => 'https://example.com/#person',
                'name' => 'Person Name',
                'givenName' => 'First',
                'familyName' => 'Last',
                'jobTitle' => 'Title',
                'url' => 'https://example.com/',
                'email' => 'info@example.com',
                'image' => ['https://example.com/headshot.jpeg'],
                'sameAs' => ['https://twitter.com/username'],
            ];
            echo format_json_display($person_template, true);
            ?>
        </div>
        
        <!-- Book Template -->
        <div>
            <h4 style="margin:0 0 10px 0;font-size:14px;">Book Schema</h4>
            <?php
            $book_template = [
                '@type' => 'Book',
                '@id' => 'https://example.com/#book-slug',
                'name' => 'Book Title',
                'url' => 'https://example.com/books/book-slug/',
                'author' => ['@id' => 'https://example.com/#person'],
                'image' => ['@type' => 'ImageObject', 'url' => 'https://example.com/cover.jpeg'],
                'sameAs' => ['https://amazon.com/dp/XXXXX'],
                'inLanguage' => 'en',
            ];
            echo format_json_display($book_template, true);
            ?>
        </div>
        
        <!-- Organization Template -->
        <div>
            <h4 style="margin:0 0 10px 0;font-size:14px;">Organization Schema</h4>
            <?php
            $org_template = [
                '@type' => 'Organization',
                '@id' => 'https://example.com/#org-company',
                'name' => 'Company Name',
                'legalName' => 'Company Legal Name',
                'url' => 'https://company.com',
                'description' => 'Company description.',
                'logo' => ['@type' => 'ImageObject', 'url' => 'https://example.com/logo.jpeg'],
                'founder' => ['@id' => 'https://example.com/#person'],
                'foundingDate' => '2021-12-01',
                'sameAs' => ['https://linkedin.com/company/company/'],
            ];
            echo format_json_display($org_template, true);
            ?>
        </div>
    </div>
</div>

<!-- Homepage Schema Preview -->
<div class="sfpf-card">
    <div class="sfpf-card-header">
        <span class="dashicons dashicons-visibility" style="color:#059669;"></span>
        <h3>Homepage Schema Preview</h3>
        <span style="margin-left:auto;font-size:12px;color:#666;">Built with actual website content</span>
    </div>
    
    <?php
    $founder = get_founder_full_info();
    $built_schema = ['@context' => 'https://schema.org', '@graph' => []];
    
    if ($founder && $homepage_schema_type !== 'none') {
        // Build Person
        $person = [
            '@type' => 'Person',
            '@id' => $site_url . '/#person',
            'name' => $founder['display_name'],
            'url' => $site_url . '/',
        ];
        
        if ($founder['first_name']) $person['givenName'] = $founder['first_name'];
        if ($founder['last_name']) $person['familyName'] = $founder['last_name'];
        if ($founder['job_title']) $person['jobTitle'] = $founder['job_title'];
        if ($founder['email']) $person['email'] = $founder['email'];
        if ($founder['avatar_url']) $person['image'] = $founder['avatar_url'];
        
        $same_as = array_values(array_filter($founder['urls'] ?? []));
        if (!empty($same_as)) $person['sameAs'] = $same_as;
        
        // Add ProfilePage if selected
        if (in_array($homepage_schema_type, ['profile_page', 'profile_page_only'])) {
            $profile_page = [
                '@type' => 'ProfilePage',
                '@id' => $site_url . '/#profilepage',
                'url' => $site_url . '/',
                'name' => get_bloginfo('name'),
                'description' => get_bloginfo('description'),
                'inLanguage' => 'en-US',
                'isPartOf' => [
                    '@type' => 'WebSite',
                    '@id' => $site_url . '/#website',
                    'url' => $site_url . '/',
                    'name' => get_bloginfo('name'),
                ],
            ];
            
            if ($homepage_schema_type === 'profile_page') {
                $profile_page['mainEntity'] = ['@id' => $site_url . '/#person'];
            }
            
            if ($founder['avatar_url']) {
                $profile_page['primaryImageOfPage'] = [
                    '@type' => 'ImageObject',
                    '@id' => $site_url . '/#headshot',
                    'url' => $founder['avatar_url'],
                    'contentUrl' => $founder['avatar_url'],
                ];
            }
            
            $built_schema['@graph'][] = $profile_page;
        }
        
        // Add Person if not profile_page_only
        if ($homepage_schema_type !== 'profile_page_only') {
            $built_schema['@graph'][] = $person;
        }
    }
    
    $built_schema = function_exists(__NAMESPACE__ . '\\sanitize_schema') ? sanitize_schema($built_schema) : $built_schema;
    ?>
    
    <?php if ($homepage_schema_type === 'none'): ?>
        <p style="color:#666;text-align:center;padding:20px;">Schema injection is disabled.</p>
    <?php else: ?>
        <div style="margin-bottom:20px;">
            <?php echo format_json_display($built_schema, true); ?>
        </div>
    <?php endif; ?>
    
    <button type="button" class="button button-primary" id="sfpf-reprocess-homepage">🔄 Reprocess Homepage Schema</button>
</div>

<!-- CPT Schema Reprocessing -->
<?php if (is_snippet_enabled('sfpf_enable_book_cpt')): ?>
<div class="sfpf-card">
    <div class="sfpf-card-header">
        <span class="dashicons dashicons-book" style="color:#f59e0b;"></span>
        <h3>Book Schema</h3>
    </div>
    
    <?php $books = get_posts(['post_type' => 'book', 'posts_per_page' => -1, 'post_status' => 'publish']); ?>
    
    <p style="color:#666;margin-bottom:15px;">Found <strong><?php echo count($books); ?></strong> published books.</p>
    <button type="button" class="button button-primary" id="sfpf-reprocess-books">🔄 Reprocess All Book Schemas</button>
</div>
<?php endif; ?>

<?php if (is_snippet_enabled('sfpf_enable_organization_cpt')): ?>
<div class="sfpf-card">
    <div class="sfpf-card-header">
        <span class="dashicons dashicons-building" style="color:#ec4899;"></span>
        <h3>Organization Schema</h3>
    </div>
    
    <?php $orgs = get_posts(['post_type' => 'organization', 'posts_per_page' => -1, 'post_status' => 'publish']); ?>
    
    <p style="color:#666;margin-bottom:15px;">Found <strong><?php echo count($orgs); ?></strong> published organizations.</p>
    <button type="button" class="button button-primary" id="sfpf-reprocess-organizations">🔄 Reprocess All Organization Schemas</button>
</div>
<?php endif; ?>

<!-- Rebuild All -->
<div class="sfpf-card">
    <div class="sfpf-card-header">
        <span class="dashicons dashicons-update" style="color:#dc2626;"></span>
        <h3>Rebuild All Schemas</h3>
    </div>
    
    <p style="color:#666;margin-bottom:15px;">Rebuild all schema objects across the entire site.</p>
    <button type="button" class="button" id="sfpf-rebuild-all" style="background:#dc2626;border-color:#dc2626;color:#fff;">🔄 Rebuild All Schemas</button>
</div>

<script>
jQuery(document).ready(function($) {
    // Toast notification helper
    function showToast(message, type) {
        type = type || 'success';
        var bgColor = type === 'success' ? '#dcfce7' : '#fef2f2';
        var borderColor = type === 'success' ? '#16a34a' : '#dc2626';
        var icon = type === 'success' ? '✅' : '❌';
        var $notice = $('<div style="position:fixed;top:50px;right:20px;z-index:9999;padding:12px 20px;background:' + bgColor + ';border:1px solid ' + borderColor + ';border-radius:6px;box-shadow:0 4px 12px rgba(0,0,0,0.15);"><p style="margin:0;">' + icon + ' ' + message + '</p></div>');
        $('body').append($notice);
        setTimeout(function() { $notice.fadeOut(function() { $(this).remove(); }); }, 3000);
    }
    
    // Save schema type (no page reload)
    $('#sfpf-save-schema-type').on('click', function() {
        var type = $('input[name="homepage_schema_type"]:checked').val();
        var $btn = $(this);
        $btn.prop('disabled', true).text('Saving...');
        
        $.post(ajaxurl, {
            action: 'sfpf_save_schema_type',
            schema_type: type,
            nonce: '<?php echo wp_create_nonce('sfpf_ajax'); ?>'
        }, function(response) {
            $btn.prop('disabled', false).html('💾 Save Schema Type');
            if (response.success) {
                showToast('Schema type saved!');
            } else {
                showToast('Error: ' + (response.data || 'Unknown error'), 'error');
            }
        });
    });
    
    // Save RankMath settings (no page reload)
    $('#sfpf-save-rankmath-settings').on('click', function() {
        var $btn = $(this);
        $btn.prop('disabled', true).text('Saving...');
        
        $.post(ajaxurl, {
            action: 'sfpf_save_rankmath_settings',
            disable_homepage: $('input[name="rankmath_disable_homepage"]').is(':checked') ? 1 : 0,
            disable_books: $('input[name="rankmath_disable_books"]').is(':checked') ? 1 : 0,
            disable_organizations: $('input[name="rankmath_disable_organizations"]').is(':checked') ? 1 : 0,
            disable_testimonials: $('input[name="rankmath_disable_testimonials"]').is(':checked') ? 1 : 0,
            nonce: '<?php echo wp_create_nonce('sfpf_ajax'); ?>'
        }, function(response) {
            $btn.prop('disabled', false).html('💾 Save RankMath Settings');
            if (response.success) {
                showToast('RankMath settings saved!');
            } else {
                showToast('Error: ' + (response.data || 'Unknown error'), 'error');
            }
        });
    });
    
    // Schema detection
    $('.sfpf-detect-schema').on('click', function() {
        var $btn = $(this);
        var type = $btn.data('type');
        var debug = $('#sfpf-debug-mode').is(':checked');
        
        $btn.prop('disabled', true);
        var originalText = $btn.html();
        $btn.html('<span class="dashicons dashicons-update" style="vertical-align:middle;animation:spin 1s linear infinite;"></span> Detecting...');
        
        $.post(ajaxurl, {
            action: 'sfpf_detect_schema',
            type: type,
            debug: debug ? 1 : 0,
            nonce: '<?php echo wp_create_nonce('sfpf_ajax'); ?>'
        }, function(response) {
            $btn.prop('disabled', false).html(originalText);
            if (response.success) {
                $('#sfpf-schema-detection-results').show().html(response.data.output);
            } else {
                $('#sfpf-schema-detection-results').show().html('<span style="color:#f87171;">Error: ' + (response.data || 'Unknown error') + '</span>');
            }
        });
    });
    
    // Reprocess handlers - use toast notifications
    $('#sfpf-reprocess-homepage').on('click', function() {
        var $btn = $(this);
        $btn.prop('disabled', true).text('Processing...');
        $.post(ajaxurl, {action: 'sfpf_reprocess_schema', type: 'homepage', nonce: '<?php echo wp_create_nonce('sfpf_ajax'); ?>'}, function(response) {
            $btn.prop('disabled', false).html('🔄 Reprocess Homepage Schema');
            showToast(response.success ? 'Homepage schema reprocessed!' : 'Error', response.success ? 'success' : 'error');
        });
    });
    
    $('#sfpf-reprocess-books').on('click', function() {
        var $btn = $(this);
        $btn.prop('disabled', true).text('Processing...');
        $.post(ajaxurl, {action: 'sfpf_reprocess_schema', type: 'books', nonce: '<?php echo wp_create_nonce('sfpf_ajax'); ?>'}, function(response) {
            $btn.prop('disabled', false).html('🔄 Reprocess All Book Schemas');
            showToast(response.success ? 'Book schemas reprocessed!' : 'Error', response.success ? 'success' : 'error');
        });
    });
    
    $('#sfpf-reprocess-organizations').on('click', function() {
        var $btn = $(this);
        $btn.prop('disabled', true).text('Processing...');
        $.post(ajaxurl, {action: 'sfpf_reprocess_schema', type: 'organizations', nonce: '<?php echo wp_create_nonce('sfpf_ajax'); ?>'}, function(response) {
            $btn.prop('disabled', false).html('🔄 Reprocess All Organization Schemas');
            showToast(response.success ? 'Organization schemas reprocessed!' : 'Error', response.success ? 'success' : 'error');
        });
    });
    
    $('#sfpf-rebuild-all').on('click', function() {
        if (!confirm('Rebuild ALL schemas?')) return;
        var $btn = $(this);
        $btn.prop('disabled', true).text('Rebuilding...');
        $.post(ajaxurl, {action: 'sfpf_rebuild_all_schema', nonce: '<?php echo wp_create_nonce('sfpf_ajax'); ?>'}, function(response) {
            $btn.prop('disabled', false).html('🔄 Rebuild All Schemas');
            showToast(response.success ? 'All schemas rebuilt!' : 'Error', response.success ? 'success' : 'error');
        });
    });
    
    // Radio button styling
    $('.sfpf-schema-type-radio').on('change', function() {
        $('label:has(.sfpf-schema-type-radio)').css({'background': '#fff', 'border-color': '#e5e7eb'});
        var $label = $(this).closest('label');
        if ($(this).val() === 'none') {
            $label.css({'background': '#f3f4f6', 'border-color': '#6b7280'});
        } else if ($(this).val() === 'profile_page') {
            $label.css({'background': '#dcfce7', 'border-color': '#16a34a'});
        } else {
            $label.css({'background': '#dbeafe', 'border-color': '#2563eb'});
        }
    });
});
</script>

<style>
@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
</style>
