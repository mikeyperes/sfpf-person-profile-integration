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

<!-- Profile Shortcodes (HWS Base Tools) -->
<div class="sfpf-card">
    <div class="sfpf-card-header">
        <span class="dashicons dashicons-id" style="color:#8b5cf6;"></span>
        <h3>Profile Shortcodes (Social URLs &amp; Fields)</h3>
    </div>
    
    <p style="color:#666;margin-bottom:15px;">Shortcodes for user profile fields sourced from the HWS Base Tools plugin. Both <code>[founder]</code> and <code>[company]</code> work — they pull from different user assignments.</p>
    
    <table class="sfpf-table">
        <thead>
            <tr>
                <th style="width:50%;">Founder</th>
                <th style="width:50%;">Company</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $profile_shortcodes = [
                ['founder' => '[founder id="avatar"]', 'company' => '[company id="avatar"]', 'note' => 'Avatar URL (add size="thumbnail|medium|medium_large|large|full")'],
                ['founder' => '[founder id="subtitle"]', 'company' => '[company id="subtitle"]'],
                ['founder' => '[founder id="entity_type"]', 'company' => '[company id="entity_type"]'],
                ['founder' => '[founder id="additional_public_email"]', 'company' => '[company id="additional_public_email"]'],
                ['founder' => '[founder id="additional_public_phone"]', 'company' => '[company id="additional_public_phone"]'],
                ['founder' => '[founder id="additional_title"]', 'company' => '[company id="additional_title"]'],
                ['founder' => '[founder id="education"]', 'company' => '[company id="education"]'],
                ['founder' => '[founder id="inception_date"]', 'company' => '[company id="inception_date"]'],
                ['founder' => '[founder id="headquarters_location"]', 'company' => '[company id="headquarters_location"]'],
                ['founder' => '[founder id="headquarters_wiki"]', 'company' => '[company id="headquarters_wiki"]'],
                ['founder' => '[founder id="sameas"]', 'company' => '[company id="sameas"]'],
                ['founder' => '[founder id="location_born_location"]', 'company' => ''],
                ['founder' => '[founder id="location_born_url"]', 'company' => ''],
                ['founder' => '[founder id="birth_date"]', 'company' => ''],
                ['founder' => '[founder id="nationality"]', 'company' => ''],
                ['founder' => '[founder action="display_nationality"]', 'company' => '', 'note' => 'Formatted nationality display'],
                ['founder' => '[founder id="knowledge_graph_id"]', 'company' => '[company id="knowledge_graph_id"]', 'note' => 'Raw KGMID (e.g. /g/11gyz2y3lp)'],
                ['founder' => '[founder action="display_knowledge_panel"]', 'company' => '', 'note' => 'Full Google Knowledge Panel URL as link'],
                ['founder' => '[founder id="url_facebook"]', 'company' => '[company id="url_facebook"]'],
                ['founder' => '[founder id="url_instagram"]', 'company' => '[company id="url_instagram"]'],
                ['founder' => '[founder id="url_linkedin"]', 'company' => '[company id="url_linkedin"]'],
                ['founder' => '[founder id="url_x"]', 'company' => '[company id="url_x"]'],
                ['founder' => '[founder id="url_youtube"]', 'company' => '[company id="url_youtube"]'],
                ['founder' => '[founder id="url_tiktok"]', 'company' => '[company id="url_tiktok"]'],
                ['founder' => '[founder id="url_github"]', 'company' => '[company id="url_github"]'],
                ['founder' => '[founder id="url_crunchbase"]', 'company' => '[company id="url_crunchbase"]'],
                ['founder' => '[founder id="url_wikipedia"]', 'company' => '[company id="url_wikipedia"]'],
                ['founder' => '[founder id="url_imdb"]', 'company' => '[company id="url_imdb"]'],
                ['founder' => '[founder id="url_muckrack"]', 'company' => '[company id="url_muckrack"]'],
                ['founder' => '[founder id="url_f6s"]', 'company' => '[company id="url_f6s"]'],
                ['founder' => '[founder id="url_soundcloud"]', 'company' => '[company id="url_soundcloud"]'],
                ['founder' => '[founder id="url_the_org"]', 'company' => '[company id="url_the_org"]'],
                ['founder' => '[founder id="url_whatsapp"]', 'company' => '[company id="url_whatsapp"]'],
                ['founder' => '[founder id="url_telegram"]', 'company' => '[company id="url_telegram"]'],
                ['founder' => '[founder id="url_signal"]', 'company' => '[company id="url_signal"]'],
                ['founder' => '[founder id="url_calendly"]', 'company' => '[company id="url_calendly"]'],
                ['founder' => '[founder id="url_amazon"]', 'company' => '[company id="url_amazon"]'],
                ['founder' => '[founder id="url_audible"]', 'company' => '[company id="url_audible"]'],
                ['founder' => '[founder id="url_threads"]', 'company' => '[company id="url_threads"]'],
                ['founder' => '[founder id="url_website"]', 'company' => '[company id="url_website"]'],
            ];
            foreach ($profile_shortcodes as $sc):
                $note = isset($sc['note']) ? ' <span style="color:#6b7280;font-size:11px;">(' . $sc['note'] . ')</span>' : '';
            ?>
            <tr>
                <td><code class="sfpf-copy-code"><?php echo esc_html($sc['founder']); ?></code><?php echo $note; ?></td>
                <td><code class="sfpf-copy-code"><?php echo esc_html($sc['company']); ?></code></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
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
            <tr>
                <td><code class="sfpf-copy-code">[founder action="display_articles"]</code></td>
                <td>
                    <strong>Displays articles from the Articles repeater (title, source, URL).</strong> Supports old textarea format as fallback.<br>
                    <div style="margin-top:6px;font-size:12px;color:#6b7280;">
                        <strong>Formats:</strong>
                        <code class="sfpf-copy-code">[founder action="display_articles" format="titled"]</code> — Titles as links with source badge (default)<br>
                        <code class="sfpf-copy-code">[founder action="display_articles" format="cards"]</code> — Rich cards with title, source pill, full URL<br>
                        <code class="sfpf-copy-code">[founder action="display_articles" format="sources"]</code> — Grouped by source domain with counts<br>
                        <code class="sfpf-copy-code">[founder action="display_articles" format="compact"]</code> — One-line per article: title — source
                    </div>
                    <div style="background:#f9fafb;padding:10px;border-radius:4px;margin-top:8px;border:1px solid #e5e7eb;">
                        <div style="font-size:12px;font-weight:600;margin-bottom:8px;">Preview (titled — default):</div>
                        <div style="margin-bottom:6px;">
                            <a href="#" style="color:#2563eb;font-weight:500;text-decoration:none;">SCRA Announces Investment in Cybersecurity Startup</a>
                            <span style="font-size:11px;background:#f3f4f6;color:#6b7280;padding:2px 8px;border-radius:10px;margin-left:6px;">scra.org</span>
                        </div>
                        <div style="margin-bottom:6px;">
                            <a href="#" style="color:#2563eb;font-weight:500;text-decoration:none;">Why Your Company's Security Starts With Culture</a>
                            <span style="font-size:11px;background:#f3f4f6;color:#6b7280;padding:2px 8px;border-radius:10px;margin-left:6px;">forbes.com</span>
                        </div>
                    </div>
                    <details style="margin-top:8px;">
                        <summary style="cursor:pointer;font-size:12px;color:#6b7280;">View HTML Structure (titled)</summary>
                        <pre style="background:#0d1117;color:#e6edf3;padding:12px;border-radius:6px;font-size:11px;line-height:1.6;margin-top:6px;overflow-x:auto;"><span style="color:#7ee787;">&lt;div</span> <span style="color:#79c0ff;">class=</span><span style="color:#a5d6ff;">"founder-articles format-titled"</span><span style="color:#7ee787;">&gt;</span>
  <span style="color:#7ee787;">&lt;ul</span> <span style="color:#79c0ff;">class=</span><span style="color:#a5d6ff;">"articles-list"</span><span style="color:#7ee787;">&gt;</span>
    <span style="color:#7ee787;">&lt;li</span> <span style="color:#79c0ff;">class=</span><span style="color:#a5d6ff;">"article-item"</span><span style="color:#7ee787;">&gt;</span>
      <span style="color:#7ee787;">&lt;a</span> <span style="color:#79c0ff;">class=</span><span style="color:#a5d6ff;">"article-link"</span> <span style="color:#79c0ff;">href=</span><span style="color:#a5d6ff;">"..."</span> <span style="color:#79c0ff;">target=</span><span style="color:#a5d6ff;">"_blank"</span><span style="color:#7ee787;">&gt;</span>Title<span style="color:#7ee787;">&lt;/a&gt;</span>
      <span style="color:#7ee787;">&lt;span</span> <span style="color:#79c0ff;">class=</span><span style="color:#a5d6ff;">"article-source-badge"</span><span style="color:#7ee787;">&gt;</span>source.com<span style="color:#7ee787;">&lt;/span&gt;</span>
    <span style="color:#7ee787;">&lt;/li&gt;</span>
  <span style="color:#7ee787;">&lt;/ul&gt;</span>
<span style="color:#7ee787;">&lt;/div&gt;</span></pre>
                    </details>
                    <details style="margin-top:4px;">
                        <summary style="cursor:pointer;font-size:12px;color:#6b7280;">View HTML Structure (cards)</summary>
                        <pre style="background:#0d1117;color:#e6edf3;padding:12px;border-radius:6px;font-size:11px;line-height:1.6;margin-top:6px;overflow-x:auto;"><span style="color:#7ee787;">&lt;div</span> <span style="color:#79c0ff;">class=</span><span style="color:#a5d6ff;">"founder-articles format-cards"</span><span style="color:#7ee787;">&gt;</span>
  <span style="color:#7ee787;">&lt;div</span> <span style="color:#79c0ff;">class=</span><span style="color:#a5d6ff;">"article-card"</span><span style="color:#7ee787;">&gt;</span>
    <span style="color:#7ee787;">&lt;div</span> <span style="color:#79c0ff;">class=</span><span style="color:#a5d6ff;">"article-title"</span><span style="color:#7ee787;">&gt;</span><span style="color:#7ee787;">&lt;a</span> <span style="color:#79c0ff;">href=</span><span style="color:#a5d6ff;">"..."</span><span style="color:#7ee787;">&gt;</span>Title<span style="color:#7ee787;">&lt;/a&gt;</span><span style="color:#7ee787;">&lt;/div&gt;</span>
    <span style="color:#7ee787;">&lt;div</span> <span style="color:#79c0ff;">class=</span><span style="color:#a5d6ff;">"article-source"</span><span style="color:#7ee787;">&gt;</span><span style="color:#7ee787;">&lt;span&gt;</span>source.com<span style="color:#7ee787;">&lt;/span&gt;</span><span style="color:#7ee787;">&lt;/div&gt;</span>
    <span style="color:#7ee787;">&lt;div</span> <span style="color:#79c0ff;">class=</span><span style="color:#a5d6ff;">"article-url"</span><span style="color:#7ee787;">&gt;</span><span style="color:#7ee787;">&lt;a</span> <span style="color:#79c0ff;">href=</span><span style="color:#a5d6ff;">"..."</span><span style="color:#7ee787;">&gt;</span>full-url<span style="color:#7ee787;">&lt;/a&gt;</span><span style="color:#7ee787;">&lt;/div&gt;</span>
  <span style="color:#7ee787;">&lt;/div&gt;</span>
<span style="color:#7ee787;">&lt;/div&gt;</span></pre>
                    </details>
                    <details style="margin-top:4px;">
                        <summary style="cursor:pointer;font-size:12px;color:#6b7280;">View HTML Structure (sources)</summary>
                        <pre style="background:#0d1117;color:#e6edf3;padding:12px;border-radius:6px;font-size:11px;line-height:1.6;margin-top:6px;overflow-x:auto;"><span style="color:#7ee787;">&lt;div</span> <span style="color:#79c0ff;">class=</span><span style="color:#a5d6ff;">"founder-articles format-sources"</span><span style="color:#7ee787;">&gt;</span>
  <span style="color:#7ee787;">&lt;div</span> <span style="color:#79c0ff;">class=</span><span style="color:#a5d6ff;">"article-source-group"</span><span style="color:#7ee787;">&gt;</span>
    <span style="color:#7ee787;">&lt;h4</span> <span style="color:#79c0ff;">class=</span><span style="color:#a5d6ff;">"source-heading"</span><span style="color:#7ee787;">&gt;</span>forbes.com (2)<span style="color:#7ee787;">&lt;/h4&gt;</span>
    <span style="color:#7ee787;">&lt;ul</span> <span style="color:#79c0ff;">class=</span><span style="color:#a5d6ff;">"source-articles"</span><span style="color:#7ee787;">&gt;</span>
      <span style="color:#7ee787;">&lt;li&gt;</span><span style="color:#7ee787;">&lt;a</span> <span style="color:#79c0ff;">href=</span><span style="color:#a5d6ff;">"..."</span><span style="color:#7ee787;">&gt;</span>Title<span style="color:#7ee787;">&lt;/a&gt;</span><span style="color:#7ee787;">&lt;/li&gt;</span>
    <span style="color:#7ee787;">&lt;/ul&gt;</span>
  <span style="color:#7ee787;">&lt;/div&gt;</span>
<span style="color:#7ee787;">&lt;/div&gt;</span></pre>
                    </details>
                </td>
            </tr>
            <!-- Location Born -->
            <tr>
                <td><code class="sfpf-copy-code">[founder action="display_location_born"]</code></td>
                <td>
                    <strong>Displays birthplace with Wikipedia link (opens new tab).</strong><br>
                    <div style="margin-top:6px;font-size:12px;color:#6b7280;">
                        <strong>Formats:</strong>
                        <code class="sfpf-copy-code">[founder action="display_location_born" format="link"]</code> — With link (default)<br>
                        <code class="sfpf-copy-code">[founder action="display_location_born" format="text"]</code> — Plain text, no link<br>
                        <code class="sfpf-copy-code">[founder action="display_location_born" format="inline"]</code> — Inline span only
                    </div>
                    <div style="background:#f9fafb;padding:10px;border-radius:4px;margin-top:8px;border:1px solid #e5e7eb;">
                        <div><span style="color:#6b7280;">Location Born:</span> <a href="#" style="color:#2563eb;">Chicago, Illinois</a></div>
                    </div>
                    <details style="margin-top:8px;">
                        <summary style="cursor:pointer;font-size:12px;color:#6b7280;">View HTML Structure</summary>
                        <pre style="background:#0d1117;color:#e6edf3;padding:12px;border-radius:6px;font-size:11px;line-height:1.6;margin-top:6px;overflow-x:auto;"><span style="color:#8b949e;">&lt;!-- format="link" (default) --&gt;</span>
<span style="color:#7ee787;">&lt;div</span> <span style="color:#79c0ff;">class=</span><span style="color:#a5d6ff;">"founder-location-born"</span><span style="color:#7ee787;">&gt;</span>
  <span style="color:#7ee787;">&lt;span</span> <span style="color:#79c0ff;">class=</span><span style="color:#a5d6ff;">"location-born-label"</span><span style="color:#7ee787;">&gt;</span>Location Born:<span style="color:#7ee787;">&lt;/span&gt;</span>
  <span style="color:#7ee787;">&lt;span</span> <span style="color:#79c0ff;">class=</span><span style="color:#a5d6ff;">"location-born-value"</span><span style="color:#7ee787;">&gt;</span>
    <span style="color:#7ee787;">&lt;a</span> <span style="color:#79c0ff;">href=</span><span style="color:#a5d6ff;">"wiki-url"</span> <span style="color:#79c0ff;">target=</span><span style="color:#a5d6ff;">"_blank"</span><span style="color:#7ee787;">&gt;</span>Location<span style="color:#7ee787;">&lt;/a&gt;</span>
  <span style="color:#7ee787;">&lt;/span&gt;</span>
<span style="color:#7ee787;">&lt;/div&gt;</span>

<span style="color:#8b949e;">&lt;!-- format="inline" --&gt;</span>
<span style="color:#7ee787;">&lt;span</span> <span style="color:#79c0ff;">class=</span><span style="color:#a5d6ff;">"founder-location-born-inline"</span><span style="color:#7ee787;">&gt;</span>
  <span style="color:#7ee787;">&lt;a</span> <span style="color:#79c0ff;">href=</span><span style="color:#a5d6ff;">"..."</span> <span style="color:#79c0ff;">target=</span><span style="color:#a5d6ff;">"_blank"</span><span style="color:#7ee787;">&gt;</span>Location<span style="color:#7ee787;">&lt;/a&gt;</span>
<span style="color:#7ee787;">&lt;/span&gt;</span></pre>
                    </details>
                </td>
            </tr>
            <!-- Knowledge Panel -->
            <tr>
                <td><code class="sfpf-copy-code">[founder action="display_knowledge_panel"]</code></td>
                <td>
                    Outputs the full Google Knowledge Panel URL as a clickable link that opens in a new tab.<br>
                    <span style="color:#6b7280;font-size:11px;">Also: <code>[founder id="knowledge_graph_id"]</code> — Raw KGMID string (e.g., /g/11gyz2y3lp)</span>
                </td>
            </tr>
            
            <!-- Organizations Founded -->
            <tr>
                <td><code class="sfpf-copy-code">[founder action="display_organizations_founded"]</code></td>
                <td>
                    <strong>Displays all Organization CPT posts with logo, name, summary, date, HQ, and URL.</strong><br>
                    <div style="margin-top:6px;font-size:12px;color:#6b7280;">
                        <strong>Formats:</strong>
                        <code class="sfpf-copy-code">[founder action="display_organizations_founded" format="cards"]</code> — Rich cards with logo (default)<br>
                        <code class="sfpf-copy-code">[founder action="display_organizations_founded" format="list"]</code> — List with meta and summary<br>
                        <code class="sfpf-copy-code">[founder action="display_organizations_founded" format="compact"]</code> — One-line per org
                    </div>
                    <div style="background:#f9fafb;padding:10px;border-radius:4px;margin-top:8px;border:1px solid #e5e7eb;">
                        <div style="display:flex;gap:12px;padding:12px;background:#fff;border-radius:6px;border:1px solid #e5e7eb;">
                            <div style="width:50px;height:50px;background:#e5e7eb;border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:20px;">🏢</div>
                            <div>
                                <div style="font-weight:700;font-size:15px;">Acme Corp</div>
                                <div style="font-size:12px;color:#6b7280;">September 1, 2021 · Dover, DE</div>
                                <div style="font-size:12px;color:#374151;margin-top:4px;">Building the future of...</div>
                            </div>
                        </div>
                    </div>
                    <details style="margin-top:8px;">
                        <summary style="cursor:pointer;font-size:12px;color:#6b7280;">View HTML Structure (cards)</summary>
                        <pre style="background:#0d1117;color:#e6edf3;padding:12px;border-radius:6px;font-size:11px;line-height:1.6;margin-top:6px;overflow-x:auto;"><span style="color:#7ee787;">&lt;div</span> <span style="color:#79c0ff;">class=</span><span style="color:#a5d6ff;">"founder-organizations-founded format-cards"</span><span style="color:#7ee787;">&gt;</span>
  <span style="color:#7ee787;">&lt;div</span> <span style="color:#79c0ff;">class=</span><span style="color:#a5d6ff;">"org-item org-card"</span><span style="color:#7ee787;">&gt;</span>
    <span style="color:#7ee787;">&lt;div</span> <span style="color:#79c0ff;">class=</span><span style="color:#a5d6ff;">"org-logo"</span><span style="color:#7ee787;">&gt;</span><span style="color:#7ee787;">&lt;img</span> <span style="color:#79c0ff;">src=</span><span style="color:#a5d6ff;">"..."</span><span style="color:#7ee787;">/&gt;</span><span style="color:#7ee787;">&lt;/div&gt;</span>
    <span style="color:#7ee787;">&lt;div</span> <span style="color:#79c0ff;">class=</span><span style="color:#a5d6ff;">"org-details"</span><span style="color:#7ee787;">&gt;</span>
      <span style="color:#7ee787;">&lt;div</span> <span style="color:#79c0ff;">class=</span><span style="color:#a5d6ff;">"org-name"</span><span style="color:#7ee787;">&gt;</span><span style="color:#7ee787;">&lt;a</span> <span style="color:#79c0ff;">href=</span><span style="color:#a5d6ff;">"permalink"</span><span style="color:#7ee787;">&gt;</span>Name<span style="color:#7ee787;">&lt;/a&gt;</span><span style="color:#7ee787;">&lt;/div&gt;</span>
      <span style="color:#7ee787;">&lt;div</span> <span style="color:#79c0ff;">class=</span><span style="color:#a5d6ff;">"org-meta"</span><span style="color:#7ee787;">&gt;</span>Date · HQ<span style="color:#7ee787;">&lt;/div&gt;</span>
      <span style="color:#7ee787;">&lt;div</span> <span style="color:#79c0ff;">class=</span><span style="color:#a5d6ff;">"org-summary"</span><span style="color:#7ee787;">&gt;</span>...<span style="color:#7ee787;">&lt;/div&gt;</span>
      <span style="color:#7ee787;">&lt;div</span> <span style="color:#79c0ff;">class=</span><span style="color:#a5d6ff;">"org-url"</span><span style="color:#7ee787;">&gt;</span><span style="color:#7ee787;">&lt;a</span> <span style="color:#79c0ff;">href=</span><span style="color:#a5d6ff;">"..."</span><span style="color:#7ee787;">&gt;</span>domain.com<span style="color:#7ee787;">&lt;/a&gt;</span><span style="color:#7ee787;">&lt;/div&gt;</span>
    <span style="color:#7ee787;">&lt;/div&gt;</span>
  <span style="color:#7ee787;">&lt;/div&gt;</span>
<span style="color:#7ee787;">&lt;/div&gt;</span></pre>
                    </details>
                </td>
            </tr>
            <!-- Bio Full -->
            <tr>
                <td><code class="sfpf-copy-code">[founder action="display_bio_full"]</code></td>
                <td>
                    <strong>Complete biography structure — calls multiple nested shortcodes.</strong> Each section only renders if data exists. Wraps each in a titled section with <code>&lt;h3&gt;</code>.<br>
                    <div style="margin-top:8px;background:#f0f6fc;padding:12px;border-radius:6px;border:1px solid #93c5fd;font-size:12px;">
                        <strong>Nested shortcodes called (in order):</strong>
                        <table style="width:100%;margin-top:8px;font-size:11px;border-collapse:collapse;">
                            <tr style="border-bottom:1px solid #dbeafe;">
                                <td style="padding:4px 6px;font-weight:600;width:25%;">Section</td>
                                <td style="padding:4px 6px;font-weight:600;">Source</td>
                            </tr>
                            <tr style="border-bottom:1px solid #eff6ff;">
                                <td style="padding:4px 6px;">Biography</td>
                                <td style="padding:4px 6px;"><code>[founder id="biography"]</code></td>
                            </tr>
                            <tr style="border-bottom:1px solid #eff6ff;">
                                <td style="padding:4px 6px;">Also Known As</td>
                                <td style="padding:4px 6px;"><code>[founder id="alternate_names"]</code></td>
                            </tr>
                            <tr style="border-bottom:1px solid #eff6ff;">
                                <td style="padding:4px 6px;">Education</td>
                                <td style="padding:4px 6px;"><code>[founder action="display_education"]</code></td>
                            </tr>
                            <tr style="border-bottom:1px solid #eff6ff;">
                                <td style="padding:4px 6px;">Birthplace</td>
                                <td style="padding:4px 6px;"><code>[founder action="display_location_born"]</code></td>
                            </tr>
                            <tr style="border-bottom:1px solid #eff6ff;">
                                <td style="padding:4px 6px;">Birth Date</td>
                                <td style="padding:4px 6px;"><code>[founder id="birth_date"]</code></td>
                            </tr>
                            <tr style="border-bottom:1px solid #eff6ff;">
                                <td style="padding:4px 6px;">Nationality</td>
                                <td style="padding:4px 6px;"><code>[founder id="nationality"]</code></td>
                            </tr>
                            <tr style="border-bottom:1px solid #eff6ff;">
                                <td style="padding:4px 6px;">Knowledge Panel</td>
                                <td style="padding:4px 6px;"><code>[founder action="display_knowledge_panel"]</code></td>
                            </tr>
                            <tr style="border-bottom:1px solid #eff6ff;">
                                <td style="padding:4px 6px;">Organizations Founded</td>
                                <td style="padding:4px 6px;"><code>[founder action="display_organizations_founded" format="cards"]</code></td>
                            </tr>
                            <tr style="border-bottom:1px solid #eff6ff;">
                                <td style="padding:4px 6px;">Professions</td>
                                <td style="padding:4px 6px;"><code>[founder action="display_professions_with_summary"]</code></td>
                            </tr>
                            <tr>
                                <td style="padding:4px 6px;">Connect</td>
                                <td style="padding:4px 6px;"><code>[founder action="display_socials"]</code></td>
                            </tr>
                        </table>
                    </div>
                    <details style="margin-top:8px;">
                        <summary style="cursor:pointer;font-size:12px;color:#6b7280;">View HTML Structure</summary>
                        <pre style="background:#0d1117;color:#e6edf3;padding:12px;border-radius:6px;font-size:11px;line-height:1.6;margin-top:6px;overflow-x:auto;"><span style="color:#7ee787;">&lt;div</span> <span style="color:#79c0ff;">class=</span><span style="color:#a5d6ff;">"founder-bio-full"</span><span style="color:#7ee787;">&gt;</span>
  <span style="color:#8b949e;">&lt;!-- Each section only if data exists --&gt;</span>
  <span style="color:#7ee787;">&lt;div</span> <span style="color:#79c0ff;">class=</span><span style="color:#a5d6ff;">"bio-section bio-section-biography"</span><span style="color:#7ee787;">&gt;</span>
    <span style="color:#7ee787;">&lt;h3&gt;</span>Biography<span style="color:#7ee787;">&lt;/h3&gt;</span>
    <span style="color:#7ee787;">&lt;div</span> <span style="color:#79c0ff;">class=</span><span style="color:#a5d6ff;">"bio-content"</span><span style="color:#7ee787;">&gt;</span>...<span style="color:#7ee787;">&lt;/div&gt;</span>
  <span style="color:#7ee787;">&lt;/div&gt;</span>
  <span style="color:#7ee787;">&lt;div</span> <span style="color:#79c0ff;">class=</span><span style="color:#a5d6ff;">"bio-section bio-section-alternate-names"</span><span style="color:#7ee787;">&gt;</span>
    <span style="color:#7ee787;">&lt;h3&gt;</span>Also Known As<span style="color:#7ee787;">&lt;/h3&gt;</span>
    <span style="color:#7ee787;">&lt;div</span> <span style="color:#79c0ff;">class=</span><span style="color:#a5d6ff;">"bio-content"</span><span style="color:#7ee787;">&gt;</span>...<span style="color:#7ee787;">&lt;/div&gt;</span>
  <span style="color:#7ee787;">&lt;/div&gt;</span>
  <span style="color:#7ee787;">&lt;div</span> <span style="color:#79c0ff;">class=</span><span style="color:#a5d6ff;">"bio-section bio-section-education"</span><span style="color:#7ee787;">&gt;</span>...<span style="color:#7ee787;">&lt;/div&gt;</span>
  <span style="color:#7ee787;">&lt;div</span> <span style="color:#79c0ff;">class=</span><span style="color:#a5d6ff;">"bio-section bio-section-location-born"</span><span style="color:#7ee787;">&gt;</span>...<span style="color:#7ee787;">&lt;/div&gt;</span>
  <span style="color:#7ee787;">&lt;div</span> <span style="color:#79c0ff;">class=</span><span style="color:#a5d6ff;">"bio-section bio-section-organizations"</span><span style="color:#7ee787;">&gt;</span>...<span style="color:#7ee787;">&lt;/div&gt;</span>
  <span style="color:#7ee787;">&lt;div</span> <span style="color:#79c0ff;">class=</span><span style="color:#a5d6ff;">"bio-section bio-section-professions"</span><span style="color:#7ee787;">&gt;</span>...<span style="color:#7ee787;">&lt;/div&gt;</span>
  <span style="color:#7ee787;">&lt;div</span> <span style="color:#79c0ff;">class=</span><span style="color:#a5d6ff;">"bio-section bio-section-socials"</span><span style="color:#7ee787;">&gt;</span>...<span style="color:#7ee787;">&lt;/div&gt;</span>
<span style="color:#7ee787;">&lt;/div&gt;</span></pre>
                    </details>
                </td>
            </tr>
        </tbody>
    </table>
</div>


<!-- FAQ Shortcodes -->
<div class="sfpf-card">
    <div class="sfpf-card-header">
        <span class="dashicons dashicons-editor-help" style="color:#8b5cf6;"></span>
        <h3>FAQ Shortcodes</h3>
    </div>
    
    <p style="color:#666;margin-bottom:15px;">FAQ shortcodes pull from FAQ sets created in the <strong>FAQ Structures</strong> tab. They output rich HTML with <a href="https://schema.org/FAQPage" target="_blank">FAQPage schema</a> for Google Rich Results indexing.</p>
    
    <?php
    $faq_sets_ref = get_option('sfpf_faq_sets', []);
    $primary_faq_ref = get_option('sfpf_primary_faq_set', '');
    $example_slug = !empty($faq_sets_ref) ? ($faq_sets_ref[0]['slug'] ?? 'your-set') : 'your-set';
    ?>
    
    <?php if (!empty($faq_sets_ref)): ?>
    <div style="background:#f0f6fc;padding:12px;border-radius:6px;margin-bottom:15px;font-size:12px;">
        <strong>Your FAQ Sets:</strong>
        <?php foreach ($faq_sets_ref as $fset): 
            $is_primary = ($fset['slug'] === $primary_faq_ref) || (empty($primary_faq_ref) && $fset === $faq_sets_ref[0]);
            $item_count = count($fset['items'] ?? []);
        ?>
            <code style="margin-left:8px;<?php echo $is_primary ? 'background:#dcfce7;color:#166534;' : ''; ?>"><?php echo esc_html($fset['slug']); ?> (<?php echo $item_count; ?> items)<?php echo $is_primary ? ' ★ primary' : ''; ?></code>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
    
    <!-- Parameters Reference -->
    <div style="background:#f9fafb;padding:15px;border-radius:6px;margin-bottom:20px;border:1px solid #e5e7eb;">
        <h4 style="margin:0 0 10px;font-size:14px;">📋 Parameters Reference</h4>
        <table style="width:100%;font-size:12px;border-collapse:collapse;">
            <tr style="border-bottom:1px solid #e5e7eb;">
                <td style="padding:6px 8px;font-weight:600;width:15%;">Parameter</td>
                <td style="padding:6px 8px;font-weight:600;width:20%;">Values</td>
                <td style="padding:6px 8px;font-weight:600;width:15%;">Default</td>
                <td style="padding:6px 8px;font-weight:600;">Description</td>
            </tr>
            <tr style="border-bottom:1px solid #f3f4f6;">
                <td style="padding:6px 8px;"><code>set</code></td>
                <td style="padding:6px 8px;"><code>"primary"</code>, <code>"slug-name"</code></td>
                <td style="padding:6px 8px;">— <em>required</em></td>
                <td style="padding:6px 8px;">FAQ set slug. Use <code>"primary"</code> for the designated primary set.</td>
            </tr>
            <tr style="border-bottom:1px solid #f3f4f6;">
                <td style="padding:6px 8px;"><code>style</code></td>
                <td style="padding:6px 8px;"><code>"list"</code>, <code>"accordion"</code></td>
                <td style="padding:6px 8px;"><code>"list"</code></td>
                <td style="padding:6px 8px;">Display style. List shows all open; accordion is collapsible.</td>
            </tr>
            <tr style="border-bottom:1px solid #f3f4f6;">
                <td style="padding:6px 8px;"><code>index</code></td>
                <td style="padding:6px 8px;"><code>"0"</code>, <code>"1"</code>, <code>"2"</code>, ...</td>
                <td style="padding:6px 8px;"><em>all</em></td>
                <td style="padding:6px 8px;">Display a single FAQ item by 0-based index.</td>
            </tr>
            <tr>
                <td style="padding:6px 8px;"><code>target</code></td>
                <td style="padding:6px 8px;">CSS class you added</td>
                <td style="padding:6px 8px;"><em>none</em></td>
                <td style="padding:6px 8px;">For <code>[sfpf_elementor_faq]</code> only. The CSS class you typed in the Elementor Accordion's Advanced → CSS Classes field, with a dot in front. Example: you typed <code>my-faq</code> → use <code>target=".my-faq"</code></td>
            </tr>
        </table>
    </div>
    
    <table class="sfpf-table">
        <thead>
            <tr>
                <th style="width:40%;">Shortcode</th>
                <th>Description &amp; Output</th>
            </tr>
        </thead>
        <tbody>
            <!-- List Style -->
            <tr>
                <td>
                    <code class="sfpf-copy-code">[sfpf_faq set="primary"]</code>
                    <div style="margin-top:6px;font-size:11px;color:#6b7280;">Also: <code class="sfpf-copy-code">[sfpf_faq set="<?php echo esc_attr($example_slug); ?>"]</code></div>
                </td>
                <td>
                    <strong>Display all FAQs as collapsible cards.</strong> All items start closed. Click to expand. FAQPage JSON-LD schema is automatically injected.<br>
                    <div style="background:#f9fafb;padding:10px;border-radius:4px;margin-top:8px;border:1px solid #e5e7eb;">
                        <div style="margin-bottom:8px;padding:12px;background:#fff;border-radius:6px;border:1px solid #e5e7eb;">
                            <div style="font-weight:600;font-size:14px;margin-bottom:6px;">What services do you offer?</div>
                            <div style="color:#4b5563;font-size:13px;">We offer consulting, development, and strategic advisory services...</div>
                        </div>
                        <div style="padding:12px;background:#fff;border-radius:6px;border:1px solid #e5e7eb;">
                            <div style="font-weight:600;font-size:14px;margin-bottom:6px;">How can I get in touch?</div>
                            <div style="color:#4b5563;font-size:13px;">Visit our connect page or email us directly...</div>
                        </div>
                    </div>
                    <details style="margin-top:8px;">
                        <summary style="cursor:pointer;font-size:12px;color:#6b7280;">View HTML Structure</summary>
                        <pre style="background:#0d1117;color:#e6edf3;padding:12px;border-radius:6px;font-size:11px;line-height:1.6;margin-top:6px;overflow-x:auto;"><span style="color:#7ee787;">&lt;div</span> <span style="color:#79c0ff;">class=</span><span style="color:#a5d6ff;">"sfpf-faq-list"</span> <span style="color:#79c0ff;">data-set=</span><span style="color:#a5d6ff;">"set-slug"</span><span style="color:#7ee787;">&gt;</span>
  <span style="color:#7ee787;">&lt;div</span> <span style="color:#79c0ff;">class=</span><span style="color:#a5d6ff;">"sfpf-faq-item"</span><span style="color:#7ee787;">&gt;</span>
    <span style="color:#7ee787;">&lt;div</span> <span style="color:#79c0ff;">class=</span><span style="color:#a5d6ff;">"sfpf-faq-question"</span><span style="color:#7ee787;">&gt;</span>Question text<span style="color:#7ee787;">&lt;/div&gt;</span>
    <span style="color:#7ee787;">&lt;div</span> <span style="color:#79c0ff;">class=</span><span style="color:#a5d6ff;">"sfpf-faq-answer"</span><span style="color:#7ee787;">&gt;</span>Answer HTML<span style="color:#7ee787;">&lt;/div&gt;</span>
  <span style="color:#7ee787;">&lt;/div&gt;</span>
  <span style="color:#8b949e;">&lt;!-- more items... --&gt;</span>
<span style="color:#7ee787;">&lt;/div&gt;</span>
<span style="color:#7ee787;">&lt;script</span> <span style="color:#79c0ff;">type=</span><span style="color:#a5d6ff;">"application/ld+json"</span><span style="color:#7ee787;">&gt;</span>
<span style="color:#8b949e;">{ "@context":"https://schema.org", "@type":"FAQPage",
  "mainEntity":[{ "@type":"Question", "name":"...",
    "acceptedAnswer":{ "@type":"Answer", "text":"..." }}]}</span>
<span style="color:#7ee787;">&lt;/script&gt;</span></pre>
                    </details>
                </td>
            </tr>
            
            <!-- Accordion Style -->
            <tr>
                <td><code class="sfpf-copy-code">[sfpf_faq set="primary" style="accordion"]</code></td>
                <td>
                    <strong>Collapsible accordion.</strong> Click-to-expand Q&A pairs. Includes FAQPage schema. Best for long FAQ lists.<br>
                    <div style="background:#f9fafb;padding:10px;border-radius:4px;margin-top:8px;border:1px solid #e5e7eb;">
                        <div style="border:1px solid #e5e7eb;border-radius:6px;overflow:hidden;">
                            <div style="padding:10px 15px;background:#fff;font-weight:600;font-size:13px;display:flex;justify-content:space-between;cursor:pointer;border-bottom:1px solid #e5e7eb;">
                                <span>What services do you offer?</span><span>+</span>
                            </div>
                            <div style="padding:10px 15px;background:#f9fafb;font-size:12px;color:#4b5563;border-bottom:1px solid #e5e7eb;">We offer consulting, development, and strategic advisory...</div>
                            <div style="padding:10px 15px;background:#fff;font-weight:600;font-size:13px;display:flex;justify-content:space-between;cursor:pointer;">
                                <span>How can I get in touch?</span><span>+</span>
                            </div>
                        </div>
                    </div>
                    <details style="margin-top:8px;">
                        <summary style="cursor:pointer;font-size:12px;color:#6b7280;">View HTML Structure</summary>
                        <pre style="background:#0d1117;color:#e6edf3;padding:12px;border-radius:6px;font-size:11px;line-height:1.6;margin-top:6px;overflow-x:auto;"><span style="color:#7ee787;">&lt;div</span> <span style="color:#79c0ff;">class=</span><span style="color:#a5d6ff;">"sfpf-faq-accordion"</span> <span style="color:#79c0ff;">data-set=</span><span style="color:#a5d6ff;">"set-slug"</span><span style="color:#7ee787;">&gt;</span>
  <span style="color:#7ee787;">&lt;div</span> <span style="color:#79c0ff;">class=</span><span style="color:#a5d6ff;">"sfpf-accordion-item"</span><span style="color:#7ee787;">&gt;</span>
    <span style="color:#7ee787;">&lt;button</span> <span style="color:#79c0ff;">class=</span><span style="color:#a5d6ff;">"sfpf-accordion-trigger"</span><span style="color:#7ee787;">&gt;</span>
      <span style="color:#7ee787;">&lt;span&gt;</span>Question<span style="color:#7ee787;">&lt;/span&gt;</span>
      <span style="color:#7ee787;">&lt;span</span> <span style="color:#79c0ff;">class=</span><span style="color:#a5d6ff;">"sfpf-accordion-icon"</span><span style="color:#7ee787;">&gt;</span>+<span style="color:#7ee787;">&lt;/span&gt;</span>
    <span style="color:#7ee787;">&lt;/button&gt;</span>
    <span style="color:#7ee787;">&lt;div</span> <span style="color:#79c0ff;">class=</span><span style="color:#a5d6ff;">"sfpf-accordion-content"</span><span style="color:#7ee787;">&gt;</span>Answer<span style="color:#7ee787;">&lt;/div&gt;</span>
  <span style="color:#7ee787;">&lt;/div&gt;</span>
<span style="color:#7ee787;">&lt;/div&gt;</span>
<span style="color:#7ee787;">&lt;script</span> <span style="color:#79c0ff;">type=</span><span style="color:#a5d6ff;">"application/ld+json"</span><span style="color:#7ee787;">&gt;</span><span style="color:#8b949e;">...FAQPage schema...</span><span style="color:#7ee787;">&lt;/script&gt;</span></pre>
                    </details>
                </td>
            </tr>
            
            <!-- Single Item -->
            <tr>
                <td>
                    <code class="sfpf-copy-code">[sfpf_faq set="<?php echo esc_attr($example_slug); ?>" index="0"]</code>
                    <div style="margin-top:6px;font-size:11px;color:#6b7280;">Index: <code>0</code> = first, <code>1</code> = second, etc.</div>
                </td>
                <td>
                    <strong>Single FAQ item by index.</strong> Embed one specific Q&A anywhere. No schema injected (use <code>[sfpf_faq_schema]</code> separately if needed).<br>
                    <div style="background:#f9fafb;padding:10px;border-radius:4px;margin-top:8px;border:1px solid #e5e7eb;">
                        <div style="font-weight:600;font-size:14px;margin-bottom:6px;">What services do you offer?</div>
                        <div style="color:#4b5563;font-size:13px;">We offer consulting, development, and strategic advisory services...</div>
                    </div>
                    <details style="margin-top:8px;">
                        <summary style="cursor:pointer;font-size:12px;color:#6b7280;">View HTML Structure</summary>
                        <pre style="background:#0d1117;color:#e6edf3;padding:12px;border-radius:6px;font-size:11px;line-height:1.6;margin-top:6px;overflow-x:auto;"><span style="color:#7ee787;">&lt;div</span> <span style="color:#79c0ff;">class=</span><span style="color:#a5d6ff;">"sfpf-faq-single"</span> <span style="color:#79c0ff;">data-set=</span><span style="color:#a5d6ff;">"slug"</span> <span style="color:#79c0ff;">data-index=</span><span style="color:#a5d6ff;">"0"</span><span style="color:#7ee787;">&gt;</span>
  <span style="color:#7ee787;">&lt;div</span> <span style="color:#79c0ff;">class=</span><span style="color:#a5d6ff;">"sfpf-faq-question"</span><span style="color:#7ee787;">&gt;</span>Question<span style="color:#7ee787;">&lt;/div&gt;</span>
  <span style="color:#7ee787;">&lt;div</span> <span style="color:#79c0ff;">class=</span><span style="color:#a5d6ff;">"sfpf-faq-answer"</span><span style="color:#7ee787;">&gt;</span>Answer<span style="color:#7ee787;">&lt;/div&gt;</span>
<span style="color:#7ee787;">&lt;/div&gt;</span></pre>
                    </details>
                </td>
            </tr>
            
            <!-- Schema Only -->
            <tr>
                <td><code class="sfpf-copy-code">[sfpf_faq_schema set="<?php echo esc_attr($example_slug); ?>"]</code></td>
                <td>
                    <strong>Invisible — injects FAQPage JSON-LD schema only.</strong> Use when you've built your own FAQ layout but still want Google Rich Results. Outputs a hidden <code>&lt;script type="application/ld+json"&gt;</code> block.<br>
                    <details style="margin-top:8px;">
                        <summary style="cursor:pointer;font-size:12px;color:#6b7280;">View Schema Output</summary>
                        <pre style="background:#0d1117;color:#e6edf3;padding:12px;border-radius:6px;font-size:11px;line-height:1.6;margin-top:6px;overflow-x:auto;"><span style="color:#7ee787;">&lt;script</span> <span style="color:#79c0ff;">type=</span><span style="color:#a5d6ff;">"application/ld+json"</span><span style="color:#7ee787;">&gt;</span>
<span style="color:#8b949e;">{
  "@context": "https://schema.org",
  "@type": "FAQPage",
  "mainEntity": [{
    "@type": "Question",
    "name": "What services do you offer?",
    "acceptedAnswer": {
      "@type": "Answer",
      "text": "We offer consulting..."
    }
  }]
}</span>
<span style="color:#7ee787;">&lt;/script&gt;</span></pre>
                    </details>
                </td>
            </tr>
            
            <!-- Elementor Integration -->
            <tr>
                <td><code class="sfpf-copy-code">[sfpf_elementor_faq set="<?php echo esc_attr($example_slug); ?>" target=".my-faq"]</code></td>
                <td>
                    <strong>Replaces the text inside an Elementor Accordion widget with your FAQ data.</strong><br>
                    <div style="background:#f9fafb;padding:12px;border-radius:6px;margin-top:8px;border:1px solid #e5e7eb;font-size:12px;">
                        <strong>How to set up:</strong>
                        <ol style="margin:8px 0 0;padding-left:20px;line-height:1.8;">
                            <li>Drag an Elementor <strong>Accordion</strong> widget onto your page</li>
                            <li>Add placeholder items (one per FAQ) — the text gets replaced</li>
                            <li>Click the Accordion → <strong>Advanced</strong> tab → <strong>CSS Classes</strong> → type <code>my-faq</code></li>
                            <li>Above it, add a <strong>Shortcode</strong> widget containing this shortcode</li>
                            <li>The <code>target</code> value = the CSS class you typed, with a dot: <code>.my-faq</code></li>
                        </ol>
                    </div>
                    <div style="background:#fffbeb;padding:8px 12px;border-radius:4px;margin-top:8px;border:1px solid #fde68a;font-size:11px;">
                        <strong>⚠</strong> The number of accordion items must match your FAQ count. The <code>target</code> is YOUR custom class — not a built-in Elementor class.
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
    
    <!-- Quick Examples -->
    <div style="margin-top:20px;padding:15px;background:#f0fdf4;border-radius:6px;border:1px solid #bbf7d0;">
        <h4 style="margin:0 0 10px;font-size:14px;">💡 Common Examples</h4>
        <table style="width:100%;font-size:12px;border-collapse:collapse;">
            <tr style="border-bottom:1px solid #d1fae5;">
                <td style="padding:6px 8px;width:30%;"><strong>Full FAQ page</strong></td>
                <td style="padding:6px 8px;"><code class="sfpf-copy-code">[sfpf_faq set="primary" style="accordion"]</code></td>
            </tr>
            <tr style="border-bottom:1px solid #d1fae5;">
                <td style="padding:6px 8px;"><strong>Show 1st FAQ on homepage</strong></td>
                <td style="padding:6px 8px;"><code class="sfpf-copy-code">[sfpf_faq set="primary" index="0"]</code></td>
            </tr>
            <tr style="border-bottom:1px solid #d1fae5;">
                <td style="padding:6px 8px;"><strong>Schema only (custom layout)</strong></td>
                <td style="padding:6px 8px;"><code class="sfpf-copy-code">[sfpf_faq_schema set="primary"]</code></td>
            </tr>
            <tr style="border-bottom:1px solid #d1fae5;">
                <td style="padding:6px 8px;"><strong>Specific set as list</strong></td>
                <td style="padding:6px 8px;"><code class="sfpf-copy-code">[sfpf_faq set="<?php echo esc_attr($example_slug); ?>"]</code></td>
            </tr>
            <tr>
                <td style="padding:6px 8px;"><strong>Elementor accordion</strong></td>
                <td style="padding:6px 8px;"><code class="sfpf-copy-code">[sfpf_elementor_faq set="primary" target=".my-faq"]</code> <span style="color:#6b7280;font-size:11px;">(add <code>my-faq</code> as CSS class on your Elementor Accordion)</span></td>
            </tr>
        </table>
    </div>
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
