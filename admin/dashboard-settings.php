<?php
namespace sfpf_person_website;

/**
 * Dashboard Settings Tab
 * 
 * Primary Organization and Primary Book selection.
 * 
 * @package sfpf_person_website
 * @since 1.3.9
 */

defined('ABSPATH') || exit;

// Handle form submission
if (isset($_POST['sfpf_save_settings']) && check_admin_referer('sfpf_settings_nonce')) {
    $primary_org = isset($_POST['sfpf_primary_organization']) ? intval($_POST['sfpf_primary_organization']) : 0;
    $primary_book = isset($_POST['sfpf_primary_book']) ? intval($_POST['sfpf_primary_book']) : 0;
    
    update_option('sfpf_primary_organization', $primary_org);
    update_option('sfpf_primary_book', $primary_book);
    
    echo '<div class="notice notice-success is-dismissible"><p>Settings saved successfully.</p></div>';
}

// Get current values
$current_primary_org = get_option('sfpf_primary_organization', 0);
$current_primary_book = get_option('sfpf_primary_book', 0);

// Get all organizations
$organizations = get_posts([
    'post_type' => 'organization',
    'posts_per_page' => -1,
    'post_status' => 'publish',
    'orderby' => 'title',
    'order' => 'ASC',
]);

// Get all books
$books = get_posts([
    'post_type' => 'book',
    'posts_per_page' => -1,
    'post_status' => 'publish',
    'orderby' => 'title',
    'order' => 'ASC',
]);
?>

<div class="sfpf-card">
    <div class="sfpf-card-header">
        <span class="dashicons dashicons-admin-settings" style="color:#6366f1;"></span>
        <h3>Primary Content Settings</h3>
    </div>
    
    <p style="color:#666;margin-bottom:20px;">
        Select the primary organization and book for your website. These will be used by shortcodes when no specific ID is provided.
    </p>
    
    <form method="post" action="">
        <?php wp_nonce_field('sfpf_settings_nonce'); ?>
        
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="sfpf_primary_organization">Primary Organization</label>
                </th>
                <td>
                    <select name="sfpf_primary_organization" id="sfpf_primary_organization" style="min-width:300px;">
                        <option value="0">— Select Organization —</option>
                        <?php foreach ($organizations as $org): ?>
                            <option value="<?php echo esc_attr($org->ID); ?>" <?php selected($current_primary_org, $org->ID); ?>>
                                <?php echo esc_html($org->post_title); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="description">
                        Used by <code>[organization field="name"]</code> shortcodes when no ID specified.
                        <?php if (empty($organizations)): ?>
                            <br><strong style="color:#dc2626;">No organizations found.</strong> 
                            <a href="<?php echo admin_url('post-new.php?post_type=organization'); ?>">Create one →</a>
                        <?php endif; ?>
                    </p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="sfpf_primary_book">Primary Book</label>
                </th>
                <td>
                    <select name="sfpf_primary_book" id="sfpf_primary_book" style="min-width:300px;">
                        <option value="0">— Select Book —</option>
                        <?php foreach ($books as $book): ?>
                            <option value="<?php echo esc_attr($book->ID); ?>" <?php selected($current_primary_book, $book->ID); ?>>
                                <?php echo esc_html($book->post_title); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="description">
                        Used by <code>[book field="name"]</code> shortcodes when no ID specified.
                        <?php if (empty($books)): ?>
                            <br><strong style="color:#dc2626;">No books found.</strong> 
                            <a href="<?php echo admin_url('post-new.php?post_type=book'); ?>">Create one →</a>
                        <?php endif; ?>
                    </p>
                </td>
            </tr>
        </table>
        
        <p class="submit">
            <input type="submit" name="sfpf_save_settings" class="button button-primary" value="Save Settings">
        </p>
    </form>
</div>

<!-- Shortcode Reference -->
<div class="sfpf-card">
    <div class="sfpf-card-header">
        <span class="dashicons dashicons-shortcode" style="color:#059669;"></span>
        <h3>Shortcode Quick Reference</h3>
        <span style="margin-left:auto;font-size:12px;"><a href="#shortcodes" class="sfpf-tab-link" data-tab="shortcodes">View All Shortcodes →</a></span>
    </div>
    
    <!-- Founder Shortcodes -->
    <div style="margin-bottom:20px;">
        <h4 style="margin-top:0;color:#2563eb;">Founder Shortcodes</h4>
        <table class="widefat striped" style="font-size:13px;">
            <thead>
                <tr>
                    <th>Shortcode</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                <tr><td><code>[founder id="name"]</code></td><td>Founder display name</td></tr>
                <tr><td><code>[founder id="title"]</code></td><td>Professional title</td></tr>
                <tr><td><code>[founder id="biography"]</code></td><td>Full biography (WYSIWYG)</td></tr>
                <tr><td><code>[founder id="biography_short"]</code></td><td>Short biography</td></tr>
                <tr><td><code>[founder id="professions"]</code></td><td>Professions list (comma-separated)</td></tr>
                <tr><td><code>[founder id="education"]</code></td><td>Education as HTML list</td></tr>
            </tbody>
        </table>
    </div>
    
    <!-- Founder Action Shortcodes -->
    <div style="margin-bottom:20px;">
        <h4 style="margin-top:0;color:#059669;">Founder Action Shortcodes</h4>
        <table class="widefat striped" style="font-size:13px;">
            <thead>
                <tr>
                    <th style="width:45%;">Shortcode</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><code>[founder action="display_professions_with_summary"]</code></td>
                    <td>Professions with H3 headers, linked pages (new tab), and content</td>
                </tr>
                <tr>
                    <td><code>[founder action="display_socials"]</code></td>
                    <td>Social links as list (all open in new tabs)</td>
                </tr>
                <tr>
                    <td><code>[founder action="display_education"]</code></td>
                    <td>Education with school linked to Wikipedia URL (new tab), degree, major, year</td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
        <!-- Organization Shortcodes -->
        <div>
            <h4 style="margin-top:0;color:#f59e0b;">Organization Shortcodes</h4>
            <table class="widefat striped" style="font-size:13px;">
                <thead>
                    <tr>
                        <th>Shortcode</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td><code>[organization field="name"]</code></td><td>Organization name/title</td></tr>
                    <tr><td><code>[organization field="sub_title"]</code></td><td>Subtitle/tagline</td></tr>
                    <tr><td><code>[organization field="short_summary"]</code></td><td>Short summary</td></tr>
                    <tr><td><code>[organization field="mission_statement"]</code></td><td>Mission statement</td></tr>
                    <tr><td><code>[organization field="company_info"]</code></td><td>Full company info</td></tr>
                    <tr><td><code>[organization field="founding_date"]</code></td><td>Founding date</td></tr>
                    <tr><td><code>[organization field="headquarters_location"]</code></td><td>HQ location</td></tr>
                    <tr><td><code>[organization field="url"]</code></td><td>Website URL</td></tr>
                    <tr><td><code>[organization field="logo"]</code></td><td>Logo URL</td></tr>
                </tbody>
            </table>
            
            <p style="margin-top:10px;font-size:12px;color:#666;">
                <strong>URL Options:</strong><br>
                <code>[organization field="url" link="true" target="_blank" pretty="true"]</code>
            </p>
        </div>
        
        <!-- Book Shortcodes -->
        <div>
            <h4 style="margin-top:0;color:#8b5cf6;">Book Shortcodes</h4>
            <table class="widefat striped" style="font-size:13px;">
                <thead>
                    <tr>
                        <th>Shortcode</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td><code>[book field="name"]</code></td><td>Book title</td></tr>
                    <tr><td><code>[book field="subtitle"]</code></td><td>Subtitle</td></tr>
                    <tr><td><code>[book field="description"]</code></td><td>Description</td></tr>
                    <tr><td><code>[book field="author_bio"]</code></td><td>Author bio</td></tr>
                    <tr><td><code>[book field="cover"]</code></td><td>Cover image URL</td></tr>
                    <tr><td><code>[book field="amazon_url"]</code></td><td>Amazon link</td></tr>
                    <tr><td><code>[book field="audible_url"]</code></td><td>Audible link</td></tr>
                    <tr><td><code>[book field="google_books_url"]</code></td><td>Google Books link</td></tr>
                    <tr><td><code>[book field="publishing_company"]</code></td><td>Publisher</td></tr>
                </tbody>
            </table>
            
            <p style="margin-top:10px;font-size:12px;color:#666;">
                <strong>URL Options:</strong><br>
                <code>[book field="amazon_url" link="true" target="_blank" pretty="true"]</code>
            </p>
        </div>
    </div>
    
    <!-- Loop and FAQ Shortcodes -->
    <div style="margin-top:20px;">
        <h4 style="margin-top:0;color:#10b981;">Loop & FAQ Shortcodes</h4>
        <table class="widefat striped" style="font-size:13px;">
            <thead>
                <tr>
                    <th style="width:45%;">Shortcode</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                <tr><td><code>[sfpf_loop cpt="organization"]</code></td><td>Display organizations with Elementor loop template</td></tr>
                <tr><td><code>[sfpf_loop cpt="book"]</code></td><td>Display books with Elementor loop template</td></tr>
                <tr><td><code>[sfpf_loop cpt="testimonial"]</code></td><td>Display testimonials with Elementor loop template</td></tr>
                <tr><td><code>[sfpf_faq set="faq-set-slug"]</code></td><td>Display FAQ set as accordion</td></tr>
                <tr><td><code>[sfpf_faq_schema set="faq-set-slug"]</code></td><td>Output FAQ schema JSON-LD only</td></tr>
            </tbody>
        </table>
    </div>
</div>
