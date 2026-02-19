<?php
namespace sfpf_person_website;

/**
 * Dashboard Shortcodes Tab
 * 
 * Complete reference for all plugin shortcodes.
 * 
 * @package sfpf_person_website
 * @since 1.3.11
 */

defined('ABSPATH') || exit;

?>

<div class="sfpf-card">
    <div class="sfpf-card-header">
        <span class="dashicons dashicons-shortcode" style="color:#6366f1;"></span>
        <h3>Plugin Shortcodes Reference</h3>
    </div>
    <p style="color:#666;">Complete reference for all shortcodes available in this plugin. Click any shortcode to copy it.</p>
</div>

<!-- Founder Shortcodes -->
<div class="sfpf-card">
    <div class="sfpf-card-header">
        <span class="dashicons dashicons-admin-users" style="color:#2563eb;"></span>
        <h3>Founder Shortcodes</h3>
    </div>
    
    <p style="color:#666;margin-bottom:15px;">Get information about the founder/person configured in Website Settings.</p>
    
    <table class="sfpf-table">
        <thead>
            <tr>
                <th style="width:35%;">Shortcode</th>
                <th style="width:40%;">Description</th>
                <th>Output Example</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><code class="sfpf-copy-code">[founder id="name"]</code></td>
                <td>Full name with <code>.founder-name .first_name</code> &amp; <code>.last_name</code> spans</td>
                <td><em><?php 
                    $user_id = get_founder_user_id();
                    if ($user_id) {
                        $first = esc_html(get_user_meta($user_id, 'first_name', true));
                        $last  = esc_html(get_user_meta($user_id, 'last_name', true));
                        echo ($first || $last) ? trim($first . ' ' . $last) : 'N/A';
                    } else {
                        echo 'N/A';
                    }
                ?></em></td>
            </tr>
            <tr>
                <td><code class="sfpf-copy-code">[founder id="first_name"]</code></td>
                <td>First name</td>
                <td><em><?php echo esc_html($user_id ? get_user_meta($user_id, 'first_name', true) : 'N/A'); ?></em></td>
            </tr>
            <tr>
                <td><code class="sfpf-copy-code">[founder id="last_name"]</code></td>
                <td>Last name</td>
                <td><em><?php echo esc_html($user_id ? get_user_meta($user_id, 'last_name', true) : 'N/A'); ?></em></td>
            </tr>
            <tr>
                <td><code class="sfpf-copy-code">[founder id="title"]</code></td>
                <td>Professional title (Person entity only)</td>
                <td><em><?php echo esc_html($user_id ? get_field('title', 'user_' . $user_id) : 'N/A'); ?></em></td>
            </tr>
            <tr>
                <td><code class="sfpf-copy-code">[founder id="email"]</code></td>
                <td>Email address</td>
                <td><em><?php 
                    if ($user_id) {
                        $user = get_userdata($user_id);
                        echo esc_html($user ? $user->user_email : 'N/A');
                    } else {
                        echo 'N/A';
                    }
                ?></em></td>
            </tr>
            <tr>
                <td><code class="sfpf-copy-code">[founder id="website"]</code></td>
                <td>Website URL</td>
                <td><em><?php 
                    if ($user_id) {
                        $user = get_userdata($user_id);
                        echo esc_html($user && $user->user_url ? $user->user_url : 'N/A');
                    } else {
                        echo 'N/A';
                    }
                ?></em></td>
            </tr>
            <tr>
                <td><code class="sfpf-copy-code">[founder id="biography"]</code></td>
                <td>Full biography (WYSIWYG)</td>
                <td><em>(HTML content)</em></td>
            </tr>
            <tr>
                <td><code class="sfpf-copy-code">[founder id="biography_short"]</code></td>
                <td>Short biography excerpt</td>
                <td><em>(HTML content)</em></td>
            </tr>
            <tr>
                <td><code class="sfpf-copy-code">[founder id="professions"]</code></td>
                <td>List of professions (comma-separated)</td>
                <td><em><?php 
                    if ($user_id) {
                        $profs = get_field('professions', 'user_' . $user_id);
                        if (!empty($profs)) {
                            $names = array_map(function($p) { return $p['name'] ?? ''; }, $profs);
                            echo esc_html(implode(', ', array_filter($names)));
                        } else {
                            echo 'N/A';
                        }
                    } else {
                        echo 'N/A';
                    }
                ?></em></td>
            </tr>
            <tr>
                <td><code class="sfpf-copy-code">[founder id="professions" format="json"]</code></td>
                <td>Professions as JSON array</td>
                <td><em>["Author", "Entrepreneur"]</em></td>
            </tr>
            <tr>
                <td><code class="sfpf-copy-code">[founder id="education"]</code></td>
                <td>Education history as HTML list</td>
                <td><em>(HTML list)</em></td>
            </tr>
            <tr>
                <td><code class="sfpf-copy-code">[founder id="education" format="json"]</code></td>
                <td>Education as JSON</td>
                <td><em>(JSON array)</em></td>
            </tr>
            <tr>
                <td><code class="sfpf-copy-code">[founder id="education" index="0" field="college"]</code></td>
                <td>Specific education field</td>
                <td><em>Harvard University</em></td>
            </tr>
            <tr>
                <td><code class="sfpf-copy-code">[founder id="entity_type"]</code></td>
                <td>Entity type (person/organization/none)</td>
                <td><em><?php echo esc_html($user_id ? get_field('entity_type', 'user_' . $user_id) : 'N/A'); ?></em></td>
            </tr>
            <tr>
                <td><code class="sfpf-copy-code">[founder id="sameas"]</code></td>
                <td>SameAs URLs (one per line)</td>
                <td><em>(URL list)</em></td>
            </tr>
        </tbody>
    </table>
</div>

<!-- Founder Action Shortcodes -->
<div class="sfpf-card">
    <div class="sfpf-card-header">
        <span class="dashicons dashicons-controls-play" style="color:#059669;"></span>
        <h3>Founder Action Shortcodes</h3>
    </div>
    
    <p style="color:#666;margin-bottom:15px;">Action shortcodes display formatted content with styling and links.</p>
    
    <table class="sfpf-table">
        <thead>
            <tr>
                <th style="width:40%;">Shortcode</th>
                <th>Description & Output</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><code class="sfpf-copy-code">[founder action="display_professions_with_summary"]</code></td>
                <td>
                    <strong>Displays professions with H3 headers, links (open in new tab), and content.</strong><br>
                    <div style="background:#f9fafb;padding:10px;border-radius:4px;margin-top:8px;border:1px solid #e5e7eb;">
                        <div style="font-weight:bold;font-size:16px;margin-bottom:5px;">Author</div>
                        <a href="#" style="color:#2563eb;font-size:13px;">View Details →</a>
                        <p style="color:#666;font-size:12px;margin:5px 0 0;">Page content excerpt...</p>
                    </div>
                    <details style="margin-top:8px;">
                        <summary style="cursor:pointer;font-size:12px;color:#6b7280;">View HTML Structure</summary>
                        <pre style="background:#0d1117;color:#e6edf3;padding:12px;border-radius:6px;font-size:11px;line-height:1.6;margin-top:6px;overflow-x:auto;"><span style="color:#7ee787;">&lt;div</span> <span style="color:#79c0ff;">class=</span><span style="color:#a5d6ff;">"founder-professions"</span><span style="color:#7ee787;">&gt;</span>
  <span style="color:#7ee787;">&lt;div</span> <span style="color:#79c0ff;">class=</span><span style="color:#a5d6ff;">"profession-item"</span><span style="color:#7ee787;">&gt;</span>
    <span style="color:#7ee787;">&lt;div</span> <span style="color:#79c0ff;">class=</span><span style="color:#a5d6ff;">"name"</span><span style="color:#7ee787;">&gt;</span>Author<span style="color:#7ee787;">&lt;/div&gt;</span>
    <span style="color:#7ee787;">&lt;a</span> <span style="color:#79c0ff;">class=</span><span style="color:#a5d6ff;">"page-link"</span> <span style="color:#79c0ff;">href=</span><span style="color:#a5d6ff;">"..."</span> <span style="color:#79c0ff;">target=</span><span style="color:#a5d6ff;">"_blank"</span><span style="color:#7ee787;">&gt;</span>View Details →<span style="color:#7ee787;">&lt;/a&gt;</span>
    <span style="color:#7ee787;">&lt;div</span> <span style="color:#79c0ff;">class=</span><span style="color:#a5d6ff;">"page-content"</span><span style="color:#7ee787;">&gt;</span>...<span style="color:#7ee787;">&lt;/div&gt;</span>
    <span style="color:#7ee787;">&lt;div</span> <span style="color:#79c0ff;">class=</span><span style="color:#a5d6ff;">"summary"</span><span style="color:#7ee787;">&gt;</span>...<span style="color:#7ee787;">&lt;/div&gt;</span>
  <span style="color:#7ee787;">&lt;/div&gt;</span>
<span style="color:#7ee787;">&lt;/div&gt;</span></pre>
                    </details>
                </td>
            </tr>
            <tr>
                <td><code class="sfpf-copy-code">[founder action="display_socials"]</code></td>
                <td>
                    <strong>Displays social links as text list (all open in new tabs).</strong><br>
                    <div style="background:#f9fafb;padding:10px;border-radius:4px;margin-top:8px;border:1px solid #e5e7eb;">
                        <ul style="margin:0;padding-left:20px;font-size:13px;">
                            <li><a href="#" style="color:#2563eb;">LinkedIn</a></li>
                            <li><a href="#" style="color:#2563eb;">Twitter/X</a></li>
                            <li><a href="#" style="color:#2563eb;">Instagram</a></li>
                        </ul>
                    </div>
                    <details style="margin-top:8px;">
                        <summary style="cursor:pointer;font-size:12px;color:#6b7280;">View HTML Structure</summary>
                        <pre style="background:#0d1117;color:#e6edf3;padding:12px;border-radius:6px;font-size:11px;line-height:1.6;margin-top:6px;overflow-x:auto;"><span style="color:#7ee787;">&lt;div</span> <span style="color:#79c0ff;">class=</span><span style="color:#a5d6ff;">"founder-socials"</span><span style="color:#7ee787;">&gt;</span>
  <span style="color:#7ee787;">&lt;ul</span> <span style="color:#79c0ff;">class=</span><span style="color:#a5d6ff;">"social-list"</span><span style="color:#7ee787;">&gt;</span>
    <span style="color:#7ee787;">&lt;li</span> <span style="color:#79c0ff;">class=</span><span style="color:#a5d6ff;">"social-item linkedin"</span><span style="color:#7ee787;">&gt;</span>
      <span style="color:#7ee787;">&lt;a</span> <span style="color:#79c0ff;">class=</span><span style="color:#a5d6ff;">"social-link"</span> <span style="color:#79c0ff;">href=</span><span style="color:#a5d6ff;">"..."</span> <span style="color:#79c0ff;">target=</span><span style="color:#a5d6ff;">"_blank"</span><span style="color:#7ee787;">&gt;</span>LinkedIn<span style="color:#7ee787;">&lt;/a&gt;</span>
    <span style="color:#7ee787;">&lt;/li&gt;</span>
    <span style="color:#7ee787;">&lt;li</span> <span style="color:#79c0ff;">class=</span><span style="color:#a5d6ff;">"social-item twitter"</span><span style="color:#7ee787;">&gt;</span>
      <span style="color:#7ee787;">&lt;a</span> <span style="color:#79c0ff;">class=</span><span style="color:#a5d6ff;">"social-link"</span> <span style="color:#79c0ff;">href=</span><span style="color:#a5d6ff;">"..."</span> <span style="color:#79c0ff;">target=</span><span style="color:#a5d6ff;">"_blank"</span><span style="color:#7ee787;">&gt;</span>Twitter/X<span style="color:#7ee787;">&lt;/a&gt;</span>
    <span style="color:#7ee787;">&lt;/li&gt;</span>
  <span style="color:#7ee787;">&lt;/ul&gt;</span>
<span style="color:#7ee787;">&lt;/div&gt;</span></pre>
                    </details>
                </td>
            </tr>
            <tr>
                <td><code class="sfpf-copy-code">[founder action="display_education"]</code></td>
                <td>
                    <strong>Displays education with school name linked to Wikipedia URL (opens in new tab), degree, major, year.</strong><br>
                    <div style="background:#f9fafb;padding:10px;border-radius:4px;margin-top:8px;border:1px solid #e5e7eb;">
                        <div style="font-weight:bold;font-size:14px;margin-bottom:3px;">
                            <a href="https://en.wikipedia.org/wiki/Harvard" target="_blank" style="color:#2563eb;">Harvard University</a>
                        </div>
                        <p style="color:#666;font-size:12px;margin:0;">B.S. • Computer Science • 2015</p>
                    </div>
                    <details style="margin-top:8px;">
                        <summary style="cursor:pointer;font-size:12px;color:#6b7280;">View HTML Structure</summary>
                        <pre style="background:#0d1117;color:#e6edf3;padding:12px;border-radius:6px;font-size:11px;line-height:1.6;margin-top:6px;overflow-x:auto;"><span style="color:#7ee787;">&lt;div</span> <span style="color:#79c0ff;">class=</span><span style="color:#a5d6ff;">"founder-education"</span><span style="color:#7ee787;">&gt;</span>
  <span style="color:#7ee787;">&lt;div</span> <span style="color:#79c0ff;">class=</span><span style="color:#a5d6ff;">"education-item"</span><span style="color:#7ee787;">&gt;</span>
    <span style="color:#7ee787;">&lt;div</span> <span style="color:#79c0ff;">class=</span><span style="color:#a5d6ff;">"college"</span><span style="color:#7ee787;">&gt;</span>
      <span style="color:#7ee787;">&lt;a</span> <span style="color:#79c0ff;">href=</span><span style="color:#a5d6ff;">"https://..."</span> <span style="color:#79c0ff;">target=</span><span style="color:#a5d6ff;">"_blank"</span><span style="color:#7ee787;">&gt;</span>Harvard University<span style="color:#7ee787;">&lt;/a&gt;</span>
    <span style="color:#7ee787;">&lt;/div&gt;</span>
    <span style="color:#7ee787;">&lt;div</span> <span style="color:#79c0ff;">class=</span><span style="color:#a5d6ff;">"degree"</span><span style="color:#7ee787;">&gt;</span>
      <span style="color:#7ee787;">&lt;span</span> <span style="color:#79c0ff;">class=</span><span style="color:#a5d6ff;">"designation"</span><span style="color:#7ee787;">&gt;</span>B.S.<span style="color:#7ee787;">&lt;/span&gt;</span> in
      <span style="color:#7ee787;">&lt;span</span> <span style="color:#79c0ff;">class=</span><span style="color:#a5d6ff;">"major"</span><span style="color:#7ee787;">&gt;</span>Computer Science<span style="color:#7ee787;">&lt;/span&gt;</span>
    <span style="color:#7ee787;">&lt;/div&gt;</span>
    <span style="color:#7ee787;">&lt;div</span> <span style="color:#79c0ff;">class=</span><span style="color:#a5d6ff;">"year"</span><span style="color:#7ee787;">&gt;</span>2015<span style="color:#7ee787;">&lt;/div&gt;</span>
  <span style="color:#7ee787;">&lt;/div&gt;</span>
<span style="color:#7ee787;">&lt;/div&gt;</span></pre>
                    </details>
                </td>
            </tr>
        </tbody>
    </table>
</div>

<!-- Organization Shortcodes -->
<div class="sfpf-card">
    <div class="sfpf-card-header">
        <span class="dashicons dashicons-building" style="color:#f59e0b;"></span>
        <h3>Organization Shortcodes</h3>
    </div>
    
    <p style="color:#666;margin-bottom:15px;">Get data from organization posts. Use <code>id</code> attribute to specify a post ID, or leave empty for primary organization.</p>
    
    <table class="sfpf-table">
        <thead>
            <tr>
                <th style="width:40%;">Shortcode</th>
                <th>Description</th>
            </tr>
        </thead>
        <tbody>
            <tr><td><code class="sfpf-copy-code">[organization field="name"]</code></td><td>Organization name (post title)</td></tr>
            <tr><td><code class="sfpf-copy-code">[organization field="title"]</code></td><td>Same as name</td></tr>
            <tr><td><code class="sfpf-copy-code">[organization field="subtitle"]</code></td><td>Subtitle/tagline</td></tr>
            <tr><td><code class="sfpf-copy-code">[organization field="short_summary"]</code></td><td>Short description</td></tr>
            <tr><td><code class="sfpf-copy-code">[organization field="mission_statement"]</code></td><td>Mission statement</td></tr>
            <tr><td><code class="sfpf-copy-code">[organization field="url"]</code></td><td>Website URL</td></tr>
            <tr><td><code class="sfpf-copy-code">[organization field="url" link="true" target="_blank"]</code></td><td>Website as clickable link (new tab)</td></tr>
            <tr><td><code class="sfpf-copy-code">[organization field="url" link="true" pretty="true"]</code></td><td>Pretty URL link (no https://)</td></tr>
            <tr><td><code class="sfpf-copy-code">[organization field="founding_date"]</code></td><td>Founding date</td></tr>
            <tr><td><code class="sfpf-copy-code">[organization field="headquarters_location"]</code></td><td>HQ location</td></tr>
            <tr><td><code class="sfpf-copy-code">[organization field="headquarters_wikipedia"]</code></td><td>HQ Wikipedia URL</td></tr>
            <tr><td><code class="sfpf-copy-code">[organization field="logo"]</code></td><td>Logo image URL</td></tr>
            <tr><td><code class="sfpf-copy-code">[organization field="company_info"]</code></td><td>Company information</td></tr>
            <tr><td><code class="sfpf-copy-code">[organization id="123" field="name"]</code></td><td>Specific organization by ID</td></tr>
        </tbody>
    </table>
</div>

<!-- Book Shortcodes -->
<div class="sfpf-card">
    <div class="sfpf-card-header">
        <span class="dashicons dashicons-book" style="color:#8b5cf6;"></span>
        <h3>Book Shortcodes</h3>
    </div>
    
    <p style="color:#666;margin-bottom:15px;">Get data from book posts. Use <code>id</code> attribute to specify a post ID, or leave empty for primary book.</p>
    
    <table class="sfpf-table">
        <thead>
            <tr>
                <th style="width:40%;">Shortcode</th>
                <th>Description</th>
            </tr>
        </thead>
        <tbody>
            <tr><td><code class="sfpf-copy-code">[book field="name"]</code></td><td>Book name (post title)</td></tr>
            <tr><td><code class="sfpf-copy-code">[book field="title"]</code></td><td>Same as name</td></tr>
            <tr><td><code class="sfpf-copy-code">[book field="subtitle"]</code></td><td>Book subtitle</td></tr>
            <tr><td><code class="sfpf-copy-code">[book field="description"]</code></td><td>Book description</td></tr>
            <tr><td><code class="sfpf-copy-code">[book field="author_bio"]</code></td><td>Author biography</td></tr>
            <tr><td><code class="sfpf-copy-code">[book field="cover"]</code></td><td>Cover image URL</td></tr>
            <tr><td><code class="sfpf-copy-code">[book field="amazon_url"]</code></td><td>Amazon URL</td></tr>
            <tr><td><code class="sfpf-copy-code">[book field="amazon_url" link="true" target="_blank"]</code></td><td>Amazon link (new tab)</td></tr>
            <tr><td><code class="sfpf-copy-code">[book field="audible_url"]</code></td><td>Audible URL</td></tr>
            <tr><td><code class="sfpf-copy-code">[book field="google_books_url"]</code></td><td>Google Books URL</td></tr>
            <tr><td><code class="sfpf-copy-code">[book field="goodreads_url"]</code></td><td>Goodreads URL</td></tr>
            <tr><td><code class="sfpf-copy-code">[book field="publishing_company"]</code></td><td>Publisher name</td></tr>
            <tr><td><code class="sfpf-copy-code">[book id="456" field="name"]</code></td><td>Specific book by ID</td></tr>
        </tbody>
    </table>
</div>

<!-- Loop Shortcodes -->
<div class="sfpf-card">
    <div class="sfpf-card-header">
        <span class="dashicons dashicons-admin-links" style="color:#059669;"></span>
        <h3>Organization URL Shortcodes</h3>
    </div>
    
    <p style="color:#666;margin-bottom:15px;">Get social media and web URLs from the primary (or specified) organization.</p>
    
    <table class="sfpf-table">
        <thead>
            <tr>
                <th style="width:45%;">Shortcode</th>
                <th>Description</th>
            </tr>
        </thead>
        <tbody>
            <tr><td><code class="sfpf-copy-code">[organization field="url"]</code></td><td>Website URL</td></tr>
            <tr><td><code class="sfpf-copy-code">[organization field="url_facebook"]</code></td><td>Facebook</td></tr>
            <tr><td><code class="sfpf-copy-code">[organization field="url_instagram"]</code></td><td>Instagram</td></tr>
            <tr><td><code class="sfpf-copy-code">[organization field="url_linkedin"]</code></td><td>LinkedIn</td></tr>
            <tr><td><code class="sfpf-copy-code">[organization field="url_x"]</code></td><td>X (Twitter)</td></tr>
            <tr><td><code class="sfpf-copy-code">[organization field="url_youtube"]</code></td><td>YouTube</td></tr>
            <tr><td><code class="sfpf-copy-code">[organization field="url_tiktok"]</code></td><td>TikTok</td></tr>
            <tr><td><code class="sfpf-copy-code">[organization field="url_github"]</code></td><td>GitHub</td></tr>
            <tr><td><code class="sfpf-copy-code">[organization field="url_wikipedia"]</code></td><td>Wikipedia</td></tr>
            <tr><td><code class="sfpf-copy-code">[organization field="url_crunchbase"]</code></td><td>Crunchbase</td></tr>
            <tr><td><code class="sfpf-copy-code">[organization field="url_linkedin" link="true" target="_blank"]</code></td><td>As clickable link</td></tr>
        </tbody>
    </table>
</div>

<!-- Loop Shortcodes -->
<div class="sfpf-card">
    <div class="sfpf-card-header">
        <span class="dashicons dashicons-update" style="color:#10b981;"></span>
        <h3>Loop Shortcodes</h3>
    </div>
    
    <p style="color:#666;margin-bottom:15px;">Display loops of posts using Elementor templates.</p>
    
    <table class="sfpf-table">
        <thead>
            <tr>
                <th style="width:45%;">Shortcode</th>
                <th>Description</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><code class="sfpf-copy-code">[sfpf_loop cpt="organization"]</code></td>
                <td>Display all organizations using assigned Elementor loop template</td>
            </tr>
            <tr>
                <td><code class="sfpf-copy-code">[sfpf_loop cpt="book"]</code></td>
                <td>Display all books using assigned Elementor loop template</td>
            </tr>
            <tr>
                <td><code class="sfpf-copy-code">[sfpf_loop cpt="testimonial"]</code></td>
                <td>Display all testimonials using assigned Elementor loop template</td>
            </tr>
        </tbody>
    </table>
</div>

<!-- FAQ Shortcodes -->
<div class="sfpf-card">
    <div class="sfpf-card-header">
        <span class="dashicons dashicons-editor-help" style="color:#ec4899;"></span>
        <h3>FAQ Shortcodes</h3>
    </div>
    
    <p style="color:#666;margin-bottom:15px;">Display FAQ sets created in the FAQ Structures tab.</p>
    
    <table class="sfpf-table">
        <thead>
            <tr>
                <th style="width:45%;">Shortcode</th>
                <th>Description</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><code class="sfpf-copy-code">[sfpf_faq set="my-faq-set"]</code></td>
                <td>Display FAQ set as accordion</td>
            </tr>
            <tr>
                <td><code class="sfpf-copy-code">[sfpf_faq_schema set="my-faq-set"]</code></td>
                <td>Output FAQ schema JSON-LD only (no visible content)</td>
            </tr>
            <tr>
                <td><code class="sfpf-copy-code">[sfpf_elementor_faq set="my-faq-set"]</code></td>
                <td>FAQ formatted for Elementor integration</td>
            </tr>
        </tbody>
    </table>
    
    <?php 
    $faq_sets = get_option('sfpf_faq_sets', []);
    if (!empty($faq_sets)): 
    ?>
    <div style="margin-top:15px;padding:15px;background:#f0fdf4;border:1px solid #86efac;border-radius:6px;">
        <strong style="color:#166534;">Your FAQ Sets:</strong>
        <div style="margin-top:8px;display:flex;flex-wrap:wrap;gap:8px;">
            <?php foreach ($faq_sets as $set): if (!empty($set['slug'])): ?>
                <code style="background:#dcfce7;color:#166534;padding:4px 8px;border-radius:4px;font-size:12px;">[sfpf_faq set="<?php echo esc_attr($set['slug']); ?>"]</code>
            <?php endif; endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Website Settings Shortcodes -->
<div class="sfpf-card">
    <div class="sfpf-card-header">
        <span class="dashicons dashicons-admin-site" style="color:#0ea5e9;"></span>
        <h3>Website Settings Shortcodes</h3>
    </div>
    
    <p style="color:#666;margin-bottom:15px;">Get data from HWS Base Tools website settings.</p>
    
    <table class="sfpf-table">
        <thead>
            <tr>
                <th style="width:45%;">Shortcode</th>
                <th>Description</th>
            </tr>
        </thead>
        <tbody>
            <tr><td><code class="sfpf-copy-code">[website_content field="biography_short"]</code></td><td>Short biography from settings</td></tr>
            <tr><td><code class="sfpf-copy-code">[website_content field="email"]</code></td><td>Contact email</td></tr>
            <tr><td><code class="sfpf-copy-code">[website_url social="facebook"]</code></td><td>Facebook URL</td></tr>
            <tr><td><code class="sfpf-copy-code">[website_url social="instagram"]</code></td><td>Instagram URL</td></tr>
            <tr><td><code class="sfpf-copy-code">[website_url social="linkedin"]</code></td><td>LinkedIn URL</td></tr>
            <tr><td><code class="sfpf-copy-code">[website_url social="twitter"]</code></td><td>Twitter URL</td></tr>
            <tr><td><code class="sfpf-copy-code">[website_url social="youtube"]</code></td><td>YouTube URL</td></tr>
            <tr><td><code class="sfpf-copy-code">[website_url social="tiktok"]</code></td><td>TikTok URL</td></tr>
            <tr><td><code class="sfpf-copy-code">[website_url social="github"]</code></td><td>GitHub URL</td></tr>
            <tr><td><code class="sfpf-copy-code">[website_url social="wikipedia"]</code></td><td>Wikipedia URL</td></tr>
        </tbody>
    </table>
</div>

<!-- RankMath Shortcodes -->
<div class="sfpf-card">
    <div class="sfpf-card-header">
        <span class="dashicons dashicons-admin-links" style="color:#e91e63;"></span>
        <h3>RankMath Shortcodes</h3>
    </div>
    
    <p style="color:#666;margin-bottom:15px;">Native RankMath shortcodes (requires RankMath SEO plugin).</p>
    
    <table class="sfpf-table">
        <thead>
            <tr>
                <th style="width:45%;">Shortcode</th>
                <th>Description</th>
            </tr>
        </thead>
        <tbody>
            <tr><td><code class="sfpf-copy-code">[rank_math_breadcrumb]</code></td><td>Display breadcrumbs</td></tr>
        </tbody>
    </table>
</div>

<script>
jQuery(document).ready(function($) {
    // Copy to clipboard on click
    $('.sfpf-copy-code').on('click', function() {
        var text = $(this).text();
        navigator.clipboard.writeText(text).then(function() {
            // Show toast
            var $toast = $('<div style="position:fixed;top:50px;right:20px;z-index:9999;padding:12px 20px;background:#dcfce7;border:1px solid #16a34a;border-radius:6px;box-shadow:0 4px 12px rgba(0,0,0,0.15);"><p style="margin:0;">✅ Copied: ' + text.substring(0, 30) + (text.length > 30 ? '...' : '') + '</p></div>');
            $('body').append($toast);
            setTimeout(function() { $toast.fadeOut(function() { $(this).remove(); }); }, 2000);
        });
    });
    
    // Add hover effect
    $('.sfpf-copy-code').css('cursor', 'pointer').attr('title', 'Click to copy');
});
</script>

<style>
.sfpf-copy-code:hover {
    background: #dbeafe !important;
    color: #1d4ed8 !important;
}
</style>
