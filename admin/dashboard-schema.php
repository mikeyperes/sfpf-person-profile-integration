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
$homepage_schema_type = get_option('sfpf_homepage_schema_type', 'profile_page');

?>

<!-- Homepage Schema Type Selection -->
<div class="sfpf-card">
    <div class="sfpf-card-header">
        <span class="dashicons dashicons-admin-home" style="color:#2563eb;"></span>
        <h3>Homepage Schema Configuration</h3>
    </div>
    
    <p style="color:#666;margin-bottom:15px;">Choose the schema type for your homepage:</p>
    
    <div style="display:flex;gap:15px;flex-wrap:wrap;margin-bottom:20px;">
        <label style="display:flex;align-items:center;gap:8px;padding:15px 20px;background:<?php echo $homepage_schema_type === 'profile_page' ? '#dbeafe' : '#f9fafb'; ?>;border:2px solid <?php echo $homepage_schema_type === 'profile_page' ? '#2563eb' : '#e5e7eb'; ?>;border-radius:8px;cursor:pointer;">
            <input type="radio" name="homepage_schema_type" value="profile_page" <?php checked($homepage_schema_type, 'profile_page'); ?> class="sfpf-schema-type-radio">
            <div>
                <strong>ProfilePage + Person</strong>
                <div style="font-size:12px;color:#666;">Standard profile page schema (recommended)</div>
            </div>
        </label>
        
        <label style="display:flex;align-items:center;gap:8px;padding:15px 20px;background:<?php echo $homepage_schema_type === 'person' ? '#dbeafe' : '#f9fafb'; ?>;border:2px solid <?php echo $homepage_schema_type === 'person' ? '#2563eb' : '#e5e7eb'; ?>;border-radius:8px;cursor:pointer;">
            <input type="radio" name="homepage_schema_type" value="person" <?php checked($homepage_schema_type, 'person'); ?> class="sfpf-schema-type-radio">
            <div>
                <strong>Person Only</strong>
                <div style="font-size:12px;color:#666;">Simple person schema without profile wrapper</div>
            </div>
        </label>
    </div>
    
    <button type="button" class="button button-primary" id="sfpf-save-schema-type">💾 Save Schema Type</button>
</div>

<!-- Schema Templates -->
<div class="sfpf-card">
    <div class="sfpf-card-header">
        <span class="dashicons dashicons-media-code" style="color:#8b5cf6;"></span>
        <h3>Schema Templates</h3>
        <span style="margin-left:auto;font-size:12px;color:#666;">Raw skeleton structures - exact format from schema.org</span>
    </div>
    
    <div style="margin-bottom:25px;">
        <h4 style="margin:0 0 10px 0;font-size:14px;">ProfilePage Schema Template</h4>
        <?php
        $profile_page_template = [
            '@type' => 'ProfilePage',
            '@id' => 'https://example.com/#profilepage',
            'url' => 'https://example.com/',
            'name' => 'Person Name | Founder, Software Engineer, Journalist',
            'description' => 'Person description text here.',
            'inLanguage' => 'en-US',
            'isPartOf' => [
                '@type' => 'WebSite',
                '@id' => 'https://example.com/#website',
                'url' => 'https://example.com/',
                'name' => 'Person Name | Title',
            ],
            'primaryImageOfPage' => [
                '@type' => 'ImageObject',
                '@id' => 'https://example.com/#headshot',
                'url' => 'https://example.com/headshot.jpeg',
                'contentUrl' => 'https://example.com/headshot.jpeg',
                'width' => 560,
                'height' => 560,
            ],
            'mainEntity' => [
                '@id' => 'https://example.com/#person',
            ],
        ];
        echo format_json_display($profile_page_template, true);
        ?>
    </div>
    
    <div style="margin-bottom:25px;">
        <h4 style="margin:0 0 10px 0;font-size:14px;">Person Schema Template</h4>
        <?php
        $person_template = [
            '@type' => 'Person',
            '@id' => 'https://example.com/#person',
            'name' => 'Person Name',
            'alternateName' => ['Alternate Name', 'Other Name'],
            'givenName' => 'First',
            'familyName' => 'Last',
            'jobTitle' => ['Serial-Entrepreneur, Journalist, Software Engineer', 'Entrepreneur'],
            'url' => 'https://example.com/',
            'email' => 'info@example.com',
            'telephone' => '(415) 212-9449',
            'gender' => 'Male',
            'birthDate' => '1990-01-13',
            'birthPlace' => [
                '@type' => 'Place',
                'name' => 'Montreal, Quebec, Canada',
            ],
            'nationality' => ['Canadian', 'Israeli'],
            'image' => [
                'https://example.com/headshot.jpeg',
                'https://upload.wikimedia.org/commons/person.jpg',
            ],
            'alumniOf' => [
                [
                    '@type' => 'CollegeOrUniversity',
                    'name' => 'University Name',
                    'sameAs' => 'https://en.wikipedia.org/wiki/University_Name',
                ],
            ],
            'worksFor' => [
                [
                    '@type' => 'Organization',
                    '@id' => 'https://example.com/#org-company',
                    'name' => 'Company Name',
                    'url' => 'https://company.com',
                ],
            ],
            'sameAs' => [
                'https://example.com',
                'https://twitter.com/username',
                'https://instagram.com/username',
                'https://facebook.com/username',
                'https://linkedin.com/in/username',
                'https://www.imdb.com/name/nmXXXXXX/',
                'https://www.crunchbase.com/person/username',
            ],
        ];
        echo format_json_display($person_template, true);
        ?>
    </div>
    
    <div style="margin-bottom:25px;">
        <h4 style="margin:0 0 10px 0;font-size:14px;">Book Schema Template</h4>
        <?php
        $book_template = [
            '@type' => 'Book',
            '@id' => 'https://example.com/#book-slug',
            'name' => 'Book Title: Subtitle',
            'url' => 'https://example.com/books/book-slug/',
            'author' => [
                '@id' => 'https://example.com/#person',
            ],
            'image' => [
                '@type' => 'ImageObject',
                'url' => 'https://example.com/book-cover.jpeg',
            ],
            'sameAs' => [
                'https://example.com/books/book-slug/',
                'https://books.google.com/books?id=XXXXX',
                'https://www.amazon.com/gp/product/XXXXX/',
            ],
            'inLanguage' => 'en',
        ];
        echo format_json_display($book_template, true);
        ?>
    </div>
    
    <div>
        <h4 style="margin:0 0 10px 0;font-size:14px;">Organization Schema Template</h4>
        <?php
        $org_template = [
            '@type' => 'Organization',
            '@id' => 'https://example.com/#org-company',
            'name' => 'Company Name',
            'legalName' => 'Company Legal Name',
            'url' => 'https://company.com',
            'description' => 'Company description text.',
            'naics' => '519110',
            'email' => 'info@company.com',
            'logo' => [
                '@type' => 'ImageObject',
                'url' => 'https://example.com/logo.jpeg',
            ],
            'founder' => [
                '@id' => 'https://example.com/#person',
            ],
            'foundingDate' => '2021-12-01',
            'numberOfEmployees' => 20,
            'contactPoint' => [
                '@type' => 'ContactPoint',
                'contactType' => 'Contributor Request',
                'email' => 'info@company.com',
                'telephone' => '+14152129449',
                'url' => 'https://example.com',
            ],
            'sameAs' => [
                'https://company.com/',
                'https://www.instagram.com/company/',
                'https://www.linkedin.com/company/company/',
                'https://twitter.com/company',
                'https://www.crunchbase.com/organization/company',
            ],
        ];
        echo format_json_display($org_template, true);
        ?>
    </div>
</div>

<!-- Built Schema with Content -->
<div class="sfpf-card">
    <div class="sfpf-card-header">
        <span class="dashicons dashicons-visibility" style="color:#059669;"></span>
        <h3>Homepage Schema Preview</h3>
        <span style="margin-left:auto;font-size:12px;color:#666;">Built with actual website content</span>
    </div>
    
    <?php
    $founder = get_founder_full_info();
    $built_schema = ['@context' => 'https://schema.org', '@graph' => []];
    
    if ($founder) {
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
        if ($homepage_schema_type === 'profile_page') {
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
                'mainEntity' => ['@id' => $site_url . '/#person'],
            ];
            
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
        
        $built_schema['@graph'][] = $person;
    }
    
    $built_schema = sanitize_schema($built_schema);
    ?>
    
    <div style="margin-bottom:20px;">
        <?php echo format_json_display($built_schema, true); ?>
    </div>
    
    <button type="button" class="button button-primary" id="sfpf-reprocess-homepage">🔄 Reprocess Homepage Schema</button>
</div>

<!-- CPT Schema Reprocessing -->
<?php if (is_snippet_enabled('sfpf_enable_book_cpt')): ?>
<div class="sfpf-card">
    <div class="sfpf-card-header">
        <span class="dashicons dashicons-book" style="color:#f59e0b;"></span>
        <h3>Book Schema</h3>
    </div>
    
    <?php
    $books = get_posts(['post_type' => 'book', 'posts_per_page' => -1, 'post_status' => 'publish']);
    ?>
    
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
    
    <?php
    $orgs = get_posts(['post_type' => 'organization', 'posts_per_page' => -1, 'post_status' => 'publish']);
    ?>
    
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
                alert('Schema type saved!');
                location.reload();
            } else {
                alert('Error: ' + (response.data || 'Unknown error'));
            }
        });
    });
    
    $('#sfpf-reprocess-homepage').on('click', function() {
        var $btn = $(this);
        $btn.prop('disabled', true).text('Processing...');
        $.post(ajaxurl, {action: 'sfpf_reprocess_schema', type: 'homepage', nonce: '<?php echo wp_create_nonce('sfpf_ajax'); ?>'}, function(response) {
            $btn.prop('disabled', false).html('🔄 Reprocess Homepage Schema');
            alert(response.success ? 'Done!' : 'Error');
            location.reload();
        });
    });
    
    $('#sfpf-reprocess-books').on('click', function() {
        var $btn = $(this);
        $btn.prop('disabled', true).text('Processing...');
        $.post(ajaxurl, {action: 'sfpf_reprocess_schema', type: 'books', nonce: '<?php echo wp_create_nonce('sfpf_ajax'); ?>'}, function(response) {
            $btn.prop('disabled', false).html('🔄 Reprocess All Book Schemas');
            alert(response.success ? 'Done!' : 'Error');
        });
    });
    
    $('#sfpf-reprocess-organizations').on('click', function() {
        var $btn = $(this);
        $btn.prop('disabled', true).text('Processing...');
        $.post(ajaxurl, {action: 'sfpf_reprocess_schema', type: 'organizations', nonce: '<?php echo wp_create_nonce('sfpf_ajax'); ?>'}, function(response) {
            $btn.prop('disabled', false).html('🔄 Reprocess All Organization Schemas');
            alert(response.success ? 'Done!' : 'Error');
        });
    });
    
    $('#sfpf-rebuild-all').on('click', function() {
        if (!confirm('Rebuild ALL schemas?')) return;
        var $btn = $(this);
        $btn.prop('disabled', true).text('Rebuilding...');
        $.post(ajaxurl, {action: 'sfpf_rebuild_all_schema', nonce: '<?php echo wp_create_nonce('sfpf_ajax'); ?>'}, function(response) {
            $btn.prop('disabled', false).html('🔄 Rebuild All Schemas');
            alert(response.success ? 'Done!' : 'Error');
            location.reload();
        });
    });
    
    $('.sfpf-schema-type-radio').on('change', function() {
        $('label:has(.sfpf-schema-type-radio)').css({'background': '#f9fafb', 'border-color': '#e5e7eb'});
        $(this).closest('label').css({'background': '#dbeafe', 'border-color': '#2563eb'});
    });
});
</script>
