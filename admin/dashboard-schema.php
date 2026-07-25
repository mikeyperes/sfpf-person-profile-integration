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
$homepage_schema_type = get_option('sfpf_homepage_schema_type', 'person');

// RankMath disable options
$rankmath_disable_homepage = get_option('sfpf_rankmath_disable_homepage', false);
$rankmath_disable_biography = get_option('sfpf_rankmath_disable_biography', false);
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
    
    <div style="display:flex;flex-wrap:wrap;gap:10px;align-items:center;">
        <button type="button" class="button button-primary" id="sfpf-save-schema-type">💾 Save Schema Type</button>
        
        <a href="https://validator.schema.org/#url=<?php echo urlencode(home_url('/')); ?>" target="_blank" class="button button-secondary">
            🔍 Schema.org Validator
        </a>
        <a href="https://search.google.com/test/rich-results?url=<?php echo urlencode(home_url('/')); ?>" target="_blank" class="button button-secondary">
            📊 Google Rich Results Test
        </a>
    </div>
</div>

<!-- Biography Schema Configuration -->
<?php $biography_schema_type = get_option('sfpf_biography_schema_type', 'profile_page_only'); ?>
<div class="sfpf-card">
    <div class="sfpf-card-header">
        <span class="dashicons dashicons-id-alt" style="color:#8b5cf6;"></span>
        <h3>Biography Page Schema Configuration</h3>
    </div>
    
    <p style="color:#666;margin-bottom:15px;">Choose the schema type for your Biography page:</p>
    
    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(180px, 1fr));gap:12px;margin-bottom:20px;">
        <label style="display:flex;align-items:flex-start;gap:10px;padding:12px;background:<?php echo $biography_schema_type === 'none' ? '#f3f4f6' : '#fff'; ?>;border:2px solid <?php echo $biography_schema_type === 'none' ? '#6b7280' : '#e5e7eb'; ?>;border-radius:8px;cursor:pointer;">
            <input type="radio" name="biography_schema_type" value="none" <?php checked($biography_schema_type, 'none'); ?> class="sfpf-bio-schema-radio" style="margin-top:3px;">
            <div>
                <strong>None</strong>
                <div style="font-size:11px;color:#666;">No schema on biography</div>
            </div>
        </label>
        
        <label style="display:flex;align-items:flex-start;gap:10px;padding:12px;background:<?php echo $biography_schema_type === 'profile_page_only' ? '#dbeafe' : '#fff'; ?>;border:2px solid <?php echo $biography_schema_type === 'profile_page_only' ? '#2563eb' : '#e5e7eb'; ?>;border-radius:8px;cursor:pointer;">
            <input type="radio" name="biography_schema_type" value="profile_page_only" <?php checked($biography_schema_type, 'profile_page_only'); ?> class="sfpf-bio-schema-radio" style="margin-top:3px;">
            <div>
                <strong>ProfilePage Only</strong>
                <div style="font-size:11px;color:#666;">ProfilePage without Person</div>
            </div>
        </label>
        
        <label style="display:flex;align-items:flex-start;gap:10px;padding:12px;background:<?php echo $biography_schema_type === 'person' ? '#dbeafe' : '#fff'; ?>;border:2px solid <?php echo $biography_schema_type === 'person' ? '#2563eb' : '#e5e7eb'; ?>;border-radius:8px;cursor:pointer;">
            <input type="radio" name="biography_schema_type" value="person" <?php checked($biography_schema_type, 'person'); ?> class="sfpf-bio-schema-radio" style="margin-top:3px;">
            <div>
                <strong>Person Only</strong>
                <div style="font-size:11px;color:#666;">Simple person schema</div>
            </div>
        </label>
        
        <label style="display:flex;align-items:flex-start;gap:10px;padding:12px;background:<?php echo $biography_schema_type === 'profile_page' ? '#f3e8ff' : '#fff'; ?>;border:2px solid <?php echo $biography_schema_type === 'profile_page' ? '#8b5cf6' : '#e5e7eb'; ?>;border-radius:8px;cursor:pointer;">
            <input type="radio" name="biography_schema_type" value="profile_page" <?php checked($biography_schema_type, 'profile_page'); ?> class="sfpf-bio-schema-radio" style="margin-top:3px;">
            <div>
                <strong>ProfilePage + Person</strong>
                <div style="font-size:11px;color:#666;">Full profile (recommended)</div>
            </div>
        </label>
    </div>
    
    <?php $bio_page_id = get_option('sfpf_page_biography'); ?>
    <div style="display:flex;flex-wrap:wrap;gap:10px;align-items:center;">
        <button type="button" class="button button-primary" id="sfpf-save-bio-schema-type">💾 Save Biography Schema Type</button>
        
        <?php if ($bio_page_id): ?>
        <a href="https://validator.schema.org/#url=<?php echo urlencode(get_permalink($bio_page_id)); ?>" target="_blank" class="button button-secondary">
            🔍 Schema.org Validator
        </a>
        <a href="https://search.google.com/test/rich-results?url=<?php echo urlencode(get_permalink($bio_page_id)); ?>" target="_blank" class="button button-secondary">
            📊 Google Rich Results Test
        </a>
        <?php endif; ?>
    </div>
    
    <?php
    if ($bio_page_id):
        $bio_page = get_post($bio_page_id);
    ?>
    <div style="margin-top:12px;background:#f9fafb;padding:10px 14px;border-radius:6px;border:1px solid #e5e7eb;font-size:12px;color:#6b7280;">
        Biography page: <a href="<?php echo get_permalink($bio_page_id); ?>" target="_blank"><strong><?php echo esc_html($bio_page->post_title ?? 'Unknown'); ?></strong></a>
        (ID: <?php echo $bio_page_id; ?>)
    </div>
    <?php else: ?>
    <div style="margin-top:12px;background:#fef3cd;padding:10px 14px;border-radius:6px;border:1px solid #fbbf24;font-size:12px;color:#92400e;">
        ⚠ No biography page assigned. Go to the <strong>Critical Pages</strong> tab to set one.
    </div>
    <?php endif; ?>
</div>

<!-- Homepage Schema Preview - IMMEDIATELY after config -->
<div class="sfpf-card">
    <div class="sfpf-card-header">
        <span class="dashicons dashicons-visibility" style="color:#059669;"></span>
        <h3>Homepage Schema Preview</h3>
        <span style="margin-left:auto;font-size:12px;color:#666;">Live output — exactly what gets injected</span>
    </div>
    
    <?php
    $founder = get_founder_full_info();
    
    if ($homepage_schema_type === 'none'): ?>
        <p style="color:#666;text-align:center;padding:20px;">Schema injection is disabled. Select a schema type above.</p>
    <?php elseif (!$founder): ?>
        <p style="color:#dc2626;text-align:center;padding:20px;">No founder configured. Set the founder in Website Settings.</p>
    <?php else:
        // Use the REAL builder — same function that injects on the live site
        $hp_json = function_exists(__NAMESPACE__ . '\\build_homepage_schema_for_injection') 
            ? build_homepage_schema_for_injection($homepage_schema_type, get_front_page_id()) : null;
        if ($hp_json):
            $hp_decoded = json_decode($hp_json, true);
    ?>
        <div style="margin-bottom:20px;">
            <?php echo format_json_display($hp_decoded, true); ?>
        </div>
    <?php else: ?>
        <p style="color:#dc2626;text-align:center;padding:20px;">Schema builder returned empty. Check founder configuration.</p>
    <?php endif; endif; ?>
    
    <div style="display:flex;flex-wrap:wrap;gap:10px;align-items:center;">
        <button type="button" class="button button-primary" id="sfpf-reprocess-homepage">🔄 Reprocess & Save Homepage Schema</button>
        <a href="https://validator.schema.org/#url=<?php echo urlencode(home_url('/')); ?>" target="_blank" class="button button-secondary">
            🔍 Schema.org Validator
        </a>
        <a href="https://search.google.com/test/rich-results?url=<?php echo urlencode(home_url('/')); ?>" target="_blank" class="button button-secondary">
            📊 Google Rich Results Test
        </a>
        <span id="sfpf-homepage-reprocess-status" style="color:#666;font-size:13px;line-height:28px;"></span>
    </div>
</div>

<!-- Biography Schema Preview -->
<?php
$bio_page_id_preview = get_option('sfpf_page_biography');
if ($bio_page_id_preview && $biography_schema_type !== 'none'):
?>
<div class="sfpf-card">
    <div class="sfpf-card-header">
        <span class="dashicons dashicons-visibility" style="color:#8b5cf6;"></span>
        <h3>Biography Page Schema Preview</h3>
        <span style="margin-left:auto;font-size:12px;color:#666;">Live output — exactly what gets injected</span>
    </div>
    
    <?php if (!$founder): ?>
        <p style="color:#dc2626;text-align:center;padding:20px;">No founder configured.</p>
    <?php else:
        $bio_json = function_exists(__NAMESPACE__ . '\\build_homepage_schema_for_injection') 
            ? build_homepage_schema_for_injection($biography_schema_type, $bio_page_id_preview) : null;
        if ($bio_json):
            $bio_decoded = json_decode($bio_json, true);
    ?>
        <div style="margin-bottom:20px;">
            <?php echo format_json_display($bio_decoded, true); ?>
        </div>
    <?php else: ?>
        <p style="color:#dc2626;text-align:center;padding:20px;">Schema builder returned empty.</p>
    <?php endif; endif; ?>
    
    <div style="display:flex;flex-wrap:wrap;gap:10px;align-items:center;">
        <button type="button" class="button button-primary" id="sfpf-reprocess-biography">🔄 Reprocess & Save Biography Schema</button>
        <?php if ($bio_page_id_preview): ?>
        <a href="https://validator.schema.org/#url=<?php echo urlencode(get_permalink($bio_page_id_preview)); ?>" target="_blank" class="button button-secondary">
            🔍 Schema.org Validator
        </a>
        <a href="https://search.google.com/test/rich-results?url=<?php echo urlencode(get_permalink($bio_page_id_preview)); ?>" target="_blank" class="button button-secondary">
            📊 Google Rich Results Test
        </a>
        <?php endif; ?>
        <span id="sfpf-biography-reprocess-status" style="color:#666;font-size:13px;line-height:28px;"></span>
    </div>
</div>
<?php endif; ?>

<!-- Book Schema Preview -->
<?php if (is_snippet_enabled('sfpf_enable_book_cpt')):
    $books = get_posts(['post_type' => 'book', 'posts_per_page' => 1, 'post_status' => 'publish', 'orderby' => 'date', 'order' => 'ASC']);
?>
<div class="sfpf-card">
    <div class="sfpf-card-header">
        <span class="dashicons dashicons-book" style="color:#f59e0b;"></span>
        <h3>Book Schema Preview</h3>
        <span style="margin-left:auto;font-size:12px;color:#666;">
            <?php $all_books = wp_count_posts('book'); echo ((int)($all_books->publish ?? 0)); ?> published book(s)
            <?php if (!empty($books)): ?> — showing: <strong><?php echo esc_html($books[0]->post_title); ?></strong><?php endif; ?>
        </span>
    </div>
    
    <?php if (empty($books)): ?>
        <p style="color:#666;text-align:center;padding:20px;">No published books found.</p>
    <?php else:
        $book_schema = build_book_schema($books[0]->ID);
        if (!empty($book_schema)):
    ?>
        <div style="margin-bottom:20px;">
            <?php echo format_json_display($book_schema, true); ?>
        </div>
    <?php else: ?>
        <p style="color:#dc2626;text-align:center;padding:20px;">Schema builder returned empty.</p>
    <?php endif; endif; ?>
    
    <div style="display:flex;flex-wrap:wrap;gap:10px;align-items:center;">
        <button type="button" class="button button-primary" id="sfpf-reprocess-books">🔄 Reprocess All Book Schemas</button>
        <?php if (!empty($books)): ?>
        <a href="https://validator.schema.org/#url=<?php echo urlencode(get_permalink($books[0]->ID)); ?>" target="_blank" class="button button-secondary">
            🔍 Schema.org Validator
        </a>
        <a href="https://search.google.com/test/rich-results?url=<?php echo urlencode(get_permalink($books[0]->ID)); ?>" target="_blank" class="button button-secondary">
            📊 Google Rich Results Test
        </a>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<!-- Organization Schema Preview -->
<?php if (post_type_exists('organization')):
    $orgs = get_posts(['post_type' => 'organization', 'posts_per_page' => 1, 'post_status' => 'publish', 'orderby' => 'date', 'order' => 'ASC']);
?>
<div class="sfpf-card">
    <div class="sfpf-card-header">
        <span class="dashicons dashicons-building" style="color:#ec4899;"></span>
        <h3>Organization Schema Preview</h3>
        <span style="margin-left:auto;font-size:12px;color:#666;">
            <?php $all_orgs = wp_count_posts('organization'); echo ((int)($all_orgs->publish ?? 0)); ?> published org(s)
            <?php if (!empty($orgs)): ?> — showing: <strong><?php echo esc_html($orgs[0]->post_title); ?></strong><?php endif; ?>
        </span>
    </div>
    
    <?php if (empty($orgs)): ?>
        <p style="color:#666;text-align:center;padding:20px;">No published organizations found.</p>
    <?php else:
        $org_schema = build_organization_schema($orgs[0]->ID);
        if (!empty($org_schema)):
    ?>
        <div style="margin-bottom:20px;">
            <?php echo format_json_display($org_schema, true); ?>
        </div>
    <?php else: ?>
        <p style="color:#dc2626;text-align:center;padding:20px;">Schema builder returned empty.</p>
    <?php endif; endif; ?>
    
    <div style="display:flex;flex-wrap:wrap;gap:10px;align-items:center;">
        <button type="button" class="button button-primary" id="sfpf-reprocess-organizations">🔄 Reprocess All Organization Schemas</button>
        <?php if (!empty($orgs)): ?>
        <a href="https://validator.schema.org/#url=<?php echo urlencode(get_permalink($orgs[0]->ID)); ?>" target="_blank" class="button button-secondary">
            🔍 Schema.org Validator
        </a>
        <a href="https://search.google.com/test/rich-results?url=<?php echo urlencode(get_permalink($orgs[0]->ID)); ?>" target="_blank" class="button button-secondary">
            📊 Google Rich Results Test
        </a>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

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

        <label style="display:flex;align-items:center;gap:10px;cursor:pointer;">
            <input type="checkbox" name="rankmath_disable_biography" value="1" <?php checked($rankmath_disable_biography, true); ?> class="sfpf-rankmath-toggle">
            <span>Disable RankMath schema on <strong>Biography Page</strong></span>
        </label>
        
        <?php if (is_snippet_enabled('sfpf_enable_book_cpt')): ?>
        <label style="display:flex;align-items:center;gap:10px;cursor:pointer;">
            <input type="checkbox" name="rankmath_disable_books" value="1" <?php checked($rankmath_disable_books, true); ?> class="sfpf-rankmath-toggle">
            <span>Disable RankMath schema on <strong>Books</strong></span>
        </label>
        <?php endif; ?>
        
        <?php if (post_type_exists('organization')): ?>
        <label style="display:flex;align-items:center;gap:10px;cursor:pointer;">
            <input type="checkbox" name="rankmath_disable_organizations" value="1" <?php checked($rankmath_disable_organizations, true); ?> class="sfpf-rankmath-toggle">
            <span>Disable RankMath schema on <strong>Organizations</strong></span>
        </label>
        <?php endif; ?>
        
        <?php if (post_type_exists('testimonial')): ?>
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

<!-- Note: Schema Detection Tool has been moved to the Overview tab -->

<!-- Schema Templates - 2 per row -->
<div class="sfpf-card">
    <div class="sfpf-card-header">
        <span class="dashicons dashicons-media-code" style="color:#8b5cf6;"></span>
        <h3>Schema Templates</h3>
        <span style="margin-left:auto;font-size:12px;color:#666;">Your exact structures - <span style="color:#dc2626;font-weight:bold;">RED = needs ACF field</span></span>
    </div>
    
    <div style="display:grid;grid-template-columns:repeat(2, 1fr);gap:20px;">
        <!-- AboutPage Template - YOUR EXACT STRUCTURE -->
        <div>
            <h4 style="margin:0 0 10px 0;font-size:14px;">AboutPage Schema</h4>
            <pre style="background:#1e1e1e;color:#d4d4d4;padding:15px;border-radius:6px;font-size:11px;overflow-x:auto;white-space:pre-wrap;max-height:400px;overflow-y:auto;margin:0;">{
  "<span style="color:#9cdcfe;">@id</span>": "https://example.com/#schema-36584",
  "<span style="color:#9cdcfe;">@type</span>": "AboutPage",
  "<span style="color:#9cdcfe;">about</span>": {
    "@type": "Person",
    "@id": "https://example.com#person-personname",
    "name": "Person Name",
    "url": "https://example.com"
  },
  "<span style="color:#9cdcfe;">mainEntityOfPage</span>": "https://example.com/biography",
  "<span style="color:#9cdcfe;">isPartOf</span>": {
    "@id": "https://example.com/#webpage"
  },
  "<span style="color:#9cdcfe;">publisher</span>": {
    "@id": "https://example.com/#person"
  },
  "<span style="color:#9cdcfe;">image</span>": {
    "@id": "<span style="color:#dc2626;">ACF: headshot_url</span>"
  },
  "<span style="color:#9cdcfe;">inLanguage</span>": "en-US"
}</pre>
        </div>
        
        <!-- Person Template - YOUR EXACT STRUCTURE -->
        <div>
            <h4 style="margin:0 0 10px 0;font-size:14px;">Person Schema</h4>
            <pre style="background:#1e1e1e;color:#d4d4d4;padding:15px;border-radius:6px;font-size:11px;overflow-x:auto;white-space:pre-wrap;max-height:400px;overflow-y:auto;margin:0;">{
  "<span style="color:#9cdcfe;">@type</span>": "Person",
  "<span style="color:#9cdcfe;">@id</span>": "https://example.com#person-personname",
  "<span style="color:#9cdcfe;">name</span>": "Person Name",
  "<span style="color:#9cdcfe;">givenName</span>": "First",
  "<span style="color:#9cdcfe;">familyName</span>": "Last",
  "<span style="color:#9cdcfe;">additionalName</span>": "<span style="color:#dc2626;">ACF: additional_name</span>",
  "<span style="color:#9cdcfe;">alternateName</span>": "<span style="color:#dc2626;">ACF: alternate_name</span>",
  "<span style="color:#9cdcfe;">gender</span>": "<span style="color:#dc2626;">ACF: gender</span>",
  "<span style="color:#9cdcfe;">birthDate</span>": "<span style="color:#dc2626;">ACF: birth_date</span>",
  "<span style="color:#9cdcfe;">birthPlace</span>": "<span style="color:#dc2626;">ACF: birth_place</span>",
  "<span style="color:#9cdcfe;">nationality</span>": ["<span style="color:#dc2626;">ACF: nationality</span>"],
  "<span style="color:#9cdcfe;">jobTitle</span>": "Title",
  "<span style="color:#9cdcfe;">email</span>": "info@example.com",
  "<span style="color:#9cdcfe;">telephone</span>": "<span style="color:#dc2626;">ACF: telephone</span>",
  "<span style="color:#9cdcfe;">url</span>": "https://example.com/",
  "<span style="color:#9cdcfe;">mainEntityOfPage</span>": "https://example.com",
  "<span style="color:#9cdcfe;">alumniOf</span>": [
    {
      "@type": "CollegeOrUniversity",
      "name": "<span style="color:#dc2626;">ACF: alumni_name</span>",
      "sameAs": "<span style="color:#dc2626;">ACF: alumni_sameAs</span>"
    }
  ],
  "<span style="color:#9cdcfe;">image</span>": ["<span style="color:#dc2626;">ACF: knowledge_graph_images</span>"],
  "<span style="color:#9cdcfe;">worksFor</span>": [
    {
      "@type": "Organization",
      "@id": "https://example.com#organization-slug",
      "name": "<span style="color:#dc2626;">ACF: works_for_name</span>",
      "url": "<span style="color:#dc2626;">ACF: works_for_url</span>"
    }
  ],
  "<span style="color:#9cdcfe;">sameAs</span>": ["<span style="color:#dc2626;">ACF: sameAs URLs</span>"]
}</pre>
        </div>
        
        <!-- Book Template - YOUR EXACT STRUCTURE -->
        <div>
            <h4 style="margin:0 0 10px 0;font-size:14px;">Book Schema</h4>
            <pre style="background:#1e1e1e;color:#d4d4d4;padding:15px;border-radius:6px;font-size:11px;overflow-x:auto;white-space:pre-wrap;max-height:400px;overflow-y:auto;margin:0;">{
  "<span style="color:#9cdcfe;">@type</span>": "Book",
  "<span style="color:#9cdcfe;">@id</span>": "https://example.com#book-bookslug",
  "<span style="color:#9cdcfe;">name</span>": "Book Title by Author Name",
  "<span style="color:#9cdcfe;">url</span>": "https://books.google.com/books?id=XXXXX",
  "<span style="color:#9cdcfe;">author</span>": {
    "@type": "Person",
    "@id": "https://example.com#person-personname",
    "name": "Person Name",
    "url": "https://example.com"
  },
  "<span style="color:#9cdcfe;">image</span>": {
    "@type": "ImageObject",
    "url": "<span style="color:#dc2626;">ACF: book_cover_image</span>"
  },
  "<span style="color:#9cdcfe;">mainEntityOfPage</span>": "https://example.com/books/book-slug/",
  "<span style="color:#9cdcfe;">sameAs</span>": [
    "https://example.com/books/book-slug/",
    "https://books.google.com/books?id=XXXXX",
    "https://www.amazon.com/gp/product/XXXXX/"
  ]
}</pre>
        </div>
        
        <!-- Organization Template - YOUR EXACT STRUCTURE -->
        <div>
            <h4 style="margin:0 0 10px 0;font-size:14px;">Organization Schema</h4>
            <pre style="background:#1e1e1e;color:#d4d4d4;padding:15px;border-radius:6px;font-size:11px;overflow-x:auto;white-space:pre-wrap;max-height:400px;overflow-y:auto;margin:0;">{
  "<span style="color:#9cdcfe;">@type</span>": "Organization",
  "<span style="color:#9cdcfe;">@id</span>": "https://example.com#organization-orgslug",
  "<span style="color:#9cdcfe;">name</span>": "Company Name",
  "<span style="color:#9cdcfe;">legalName</span>": "Company Legal Name",
  "<span style="color:#9cdcfe;">url</span>": "https://company.com",
  "<span style="color:#9cdcfe;">naics</span>": "<span style="color:#dc2626;">ACF: naics_code</span>",
  "<span style="color:#9cdcfe;">email</span>": "info@company.com",
  "<span style="color:#9cdcfe;">description</span>": "Company description.",
  "<span style="color:#9cdcfe;">alternateName</span>": ["Alt Name 1", "Alt Name 2"],
  "<span style="color:#9cdcfe;">mainEntityOfPage</span>": ["permalink", "https://company.com"],
  "<span style="color:#9cdcfe;">logo</span>": "<span style="color:#dc2626;">ACF: org_logo</span>",
  "<span style="color:#9cdcfe;">image</span>": ["<span style="color:#dc2626;">ACF: org_images</span>"],
  "<span style="color:#9cdcfe;">award</span>": "<span style="color:#dc2626;">ACF: org_award</span>",
  "<span style="color:#9cdcfe;">brand</span>": ["<span style="color:#dc2626;">ACF: org_brand</span>"],
  "<span style="color:#9cdcfe;">address</span>": {
    "@type": "PostalAddress",
    "addressLocality": "Dover",
    "postalCode": "19901",
    "streetAddress": "8 The Green A"
  },
  "<span style="color:#9cdcfe;">contactPoint</span>": {
    "@type": "contactPoint",
    "contactType": "Help",
    "email": "info@company.com",
    "telephone": "+14152129449",
    "productSupported": "Support",
    "hoursAvailable": ["Mo-Fri 08:00-20:00"],
    "url": "https://example.com"
  },
  "<span style="color:#9cdcfe;">founder</span>": {
    "@type": "Person",
    "@id": "https://example.com#person-personname",
    "name": "Person Name",
    "url": "https://example.com"
  },
  "<span style="color:#9cdcfe;">foundingDate</span>": "2021-12-01",
  "<span style="color:#9cdcfe;">numberOfEmployees</span>": "20",
  "<span style="color:#9cdcfe;">seeks</span>": "<span style="color:#dc2626;">ACF: org_seeks</span>",
  "<span style="color:#9cdcfe;">sameAs</span>": ["https://linkedin.com/company/..."]
}</pre>
        </div>
        
        <!-- Review/Testimonial Template - FROM SCHEMA.ORG -->
        <div>
            <h4 style="margin:0 0 10px 0;font-size:14px;">Review/Testimonial Schema</h4>
            <pre style="background:#1e1e1e;color:#d4d4d4;padding:15px;border-radius:6px;font-size:11px;overflow-x:auto;white-space:pre-wrap;max-height:400px;overflow-y:auto;margin:0;">{
  "<span style="color:#9cdcfe;">@context</span>": "https://schema.org/",
  "<span style="color:#9cdcfe;">@type</span>": "Review",
  "<span style="color:#9cdcfe;">itemReviewed</span>": {
    "@type": "Person",
    "@id": "https://example.com#person-personname",
    "name": "Person Name"
  },
  "<span style="color:#9cdcfe;">reviewRating</span>": {
    "@type": "Rating",
    "ratingValue": "<span style="color:#dc2626;">ACF: rating_value</span>",
    "bestRating": "5",
    "worstRating": "1"
  },
  "<span style="color:#9cdcfe;">author</span>": {
    "@type": "Person",
    "name": "<span style="color:#dc2626;">ACF: reviewer_name</span>"
  },
  "<span style="color:#9cdcfe;">reviewBody</span>": "<span style="color:#dc2626;">ACF: testimonial_content</span>",
  "<span style="color:#9cdcfe;">datePublished</span>": "<span style="color:#dc2626;">ACF: review_date</span>"
}</pre>
        </div>
        
        <!-- ProfilePage Template -->
        <div>
            <h4 style="margin:0 0 10px 0;font-size:14px;">ProfilePage Schema (Homepage)</h4>
            <pre style="background:#1e1e1e;color:#d4d4d4;padding:15px;border-radius:6px;font-size:11px;overflow-x:auto;white-space:pre-wrap;max-height:400px;overflow-y:auto;margin:0;">{
  "<span style="color:#9cdcfe;">@context</span>": "https://schema.org",
  "<span style="color:#9cdcfe;">@graph</span>": [
    {
      "@type": "ProfilePage",
      "@id": "https://example.com/#profilepage",
      "url": "https://example.com/",
      "name": "Site Name",
      "description": "Site description",
      "inLanguage": "en-US",
      "isPartOf": {
        "@type": "WebSite",
        "@id": "https://example.com/#website",
        "url": "https://example.com/",
        "name": "Site Name"
      },
      "primaryImageOfPage": {
        "@type": "ImageObject",
        "@id": "https://example.com/#headshot",
        "url": "<span style="color:#dc2626;">ACF: headshot_url</span>"
      },
      "mainEntity": {
        "@id": "https://example.com/#person"
      }
    },
    { "... Person schema ..." }
  ]
}</pre>
        </div>
    </div>
</div>

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
    
    // Save biography schema type
    $('#sfpf-save-bio-schema-type').on('click', function() {
        var type = $('input[name="biography_schema_type"]:checked').val();
        var $btn = $(this);
        $btn.prop('disabled', true).text('Saving...');
        
        $.post(ajaxurl, {
            action: 'sfpf_save_biography_schema_type',
            schema_type: type,
            nonce: '<?php echo wp_create_nonce('sfpf_ajax'); ?>'
        }, function(response) {
            $btn.prop('disabled', false).html('💾 Save Biography Schema Type');
            if (response.success) {
                showToast('Biography schema type saved!');
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
            disable_biography: $('input[name="rankmath_disable_biography"]').is(':checked') ? 1 : 0,
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
            showToast(response.success ? 'Homepage schema reprocessed!' : 'Error: ' + (response.data || 'Unknown'), response.success ? 'success' : 'error');
        }).fail(function(xhr) {
            $btn.prop('disabled', false).html('🔄 Reprocess Homepage Schema');
            showToast('Request failed — check error log', 'error');
        });
    });
    
    $('#sfpf-reprocess-biography').on('click', function() {
        var $btn = $(this);
        $btn.prop('disabled', true).text('Processing...');
        $.post(ajaxurl, {action: 'sfpf_reprocess_schema', type: 'biography', nonce: '<?php echo wp_create_nonce('sfpf_ajax'); ?>'}, function(response) {
            $btn.prop('disabled', false).html('🔄 Reprocess Biography Schema');
            showToast(response.success ? 'Biography schema reprocessed!' : 'Error: ' + (response.data || 'Unknown'), response.success ? 'success' : 'error');
        }).fail(function(xhr) {
            $btn.prop('disabled', false).html('🔄 Reprocess Biography Schema');
            showToast('Request failed — check error log', 'error');
        });
    });
    
    $('#sfpf-reprocess-books').on('click', function() {
        var $btn = $(this);
        $btn.prop('disabled', true).text('Processing...');
        $.post(ajaxurl, {action: 'sfpf_reprocess_schema', type: 'books', nonce: '<?php echo wp_create_nonce('sfpf_ajax'); ?>'}, function(response) {
            $btn.prop('disabled', false).html('🔄 Reprocess All Book Schemas');
            showToast(response.success ? 'Book schemas reprocessed!' : 'Error', response.success ? 'success' : 'error');
        }).fail(function() {
            $btn.prop('disabled', false).html('🔄 Reprocess All Book Schemas');
            showToast('Request failed', 'error');
        });
    });
    
    $('#sfpf-reprocess-organizations').on('click', function() {
        var $btn = $(this);
        $btn.prop('disabled', true).text('Processing...');
        $.post(ajaxurl, {action: 'sfpf_reprocess_schema', type: 'organizations', nonce: '<?php echo wp_create_nonce('sfpf_ajax'); ?>'}, function(response) {
            $btn.prop('disabled', false).html('🔄 Reprocess All Organization Schemas');
            showToast(response.success ? 'Organization schemas reprocessed!' : 'Error', response.success ? 'success' : 'error');
        }).fail(function() {
            $btn.prop('disabled', false).html('🔄 Reprocess All Organization Schemas');
            showToast('Request failed', 'error');
        });
    });
    
    $('#sfpf-rebuild-all').on('click', function() {
        if (!confirm('Rebuild ALL schemas?')) return;
        var $btn = $(this);
        $btn.prop('disabled', true).text('Rebuilding...');
        $.post(ajaxurl, {action: 'sfpf_rebuild_all_schema', nonce: '<?php echo wp_create_nonce('sfpf_ajax'); ?>'}, function(response) {
            $btn.prop('disabled', false).html('🔄 Rebuild All Schemas');
            showToast(response.success ? 'All schemas rebuilt!' : 'Error', response.success ? 'success' : 'error');
        }).fail(function() {
            $btn.prop('disabled', false).html('🔄 Rebuild All Schemas');
            showToast('Request failed', 'error');
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
    
    // Bio radio button styling
    $('.sfpf-bio-schema-radio').on('change', function() {
        $('label:has(.sfpf-bio-schema-radio)').css({'background': '#fff', 'border-color': '#e5e7eb'});
        var $label = $(this).closest('label');
        if ($(this).val() === 'none') {
            $label.css({'background': '#f3f4f6', 'border-color': '#6b7280'});
        } else if ($(this).val() === 'profile_page') {
            $label.css({'background': '#f3e8ff', 'border-color': '#8b5cf6'});
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
