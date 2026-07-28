<?php

declare( strict_types=1 );

namespace sfpf_person_website;

/**
 * Author archive profile rendering and schema suppression.
 *
 * Callback names remain in the legacy namespace for template and hook compatibility.
 */

defined( 'ABSPATH' ) || exit;

// =============================================================================
// AUTHOR ARCHIVE PROFILE RENDERER
// =============================================================================

add_filter("rank_math/json_ld", __NAMESPACE__ . "\sfpf_author_archive_disable_rankmath_schema", 99);
add_action("template_redirect", __NAMESPACE__ . "\sfpf_author_archive_template", 0);

function sfpf_author_archive_disable_rankmath_schema($data) {
    return is_author() ? [] : $data;
}

/**
 * Determine whether Elementor Pro owns the current archive request.
 *
 * Theme Builder evaluates its archive conditions against the current query. If
 * a document matches, SFPF must leave rendering to Elementor instead of
 * printing its fallback profile and exiting before template_include runs.
 */
function sfpf_author_archive_has_elementor_template() {
    $module_class = '\\ElementorPro\\Modules\\ThemeBuilder\\Module';

    if ( ! class_exists( $module_class ) || ! is_callable( [ $module_class, 'instance' ] ) ) {
        return false;
    }

    try {
        $module = $module_class::instance();
        if ( ! is_object( $module ) || ! is_callable( [ $module, 'get_conditions_manager' ] ) ) {
            return false;
        }

        $conditions_manager = $module->get_conditions_manager();
        if ( ! is_object( $conditions_manager ) || ! is_callable( [ $conditions_manager, 'get_documents_for_location' ] ) ) {
            return false;
        }

        return ! empty( $conditions_manager->get_documents_for_location( 'archive' ) );
    } catch ( \Throwable $exception ) {
        return false;
    }
}

function sfpf_author_archive_template() {
    if (is_admin() || wp_doing_ajax() || is_feed() || !is_author() || sfpf_author_archive_has_elementor_template()) {
        return;
    }
    $author = get_queried_object();
    if (!$author instanceof \WP_User) {
        return;
    }
    status_header(200);
    get_header();
    echo sfpf_render_author_archive_profile((int) $author->ID);
    get_footer();
    exit;
}

function sfpf_author_archive_field($user_id, $field, $default = "") {
    if (function_exists("get_field")) {
        $value = get_field($field, "user_" . $user_id);
        if ($value !== null && $value !== false && $value !== "") return $value;
    }
    $value = get_user_meta($user_id, $field, true);
    return ($value !== "" && $value !== null && $value !== false) ? $value : $default;
}

function sfpf_author_archive_plain($value) {
    if (is_array($value)) {
        $parts = [];
        foreach ($value as $item) {
            if (is_array($item)) {
                foreach ($item as $part) {
                    if (is_scalar($part) && trim((string) $part) !== "") $parts[] = trim((string) $part);
                }
            } elseif (is_scalar($item) && trim((string) $item) !== "") {
                $parts[] = trim((string) $item);
            }
        }
        return implode(", ", array_unique($parts));
    }
    return trim(wp_strip_all_tags((string) $value));
}

function sfpf_author_archive_lines($value) {
    if (empty($value)) return [];
    if (is_array($value)) {
        $lines = [];
        foreach ($value as $item) {
            $candidate = is_array($item) ? ($item["url"] ?? $item["value"] ?? "") : (is_scalar($item) ? (string) $item : "");
            if (trim($candidate) !== "") $lines[] = trim($candidate);
        }
        return array_values(array_filter(array_unique($lines)));
    }
    return array_values(array_filter(array_unique(array_map("trim", preg_split("/\r\n|\r|\n/", (string) $value)))));
}

function sfpf_author_archive_url_group($user_id) {
    $urls = sfpf_author_archive_field($user_id, "urls", []);
    return is_array($urls) ? array_filter($urls, function($url) { return is_string($url) && trim($url) !== ""; }) : [];
}

function sfpf_render_author_archive_profile($user_id) {
    $user = get_userdata($user_id);
    if (!$user) return "";

    $first = trim((string) get_user_meta($user_id, "first_name", true));
    $last = trim((string) get_user_meta($user_id, "last_name", true));
    $name = trim($first . " " . $last) ?: $user->display_name;
    $title = sfpf_author_archive_plain(sfpf_author_archive_field($user_id, "title") ?: sfpf_author_archive_field($user_id, "additional_title"));
    $bio = sfpf_author_archive_field($user_id, "biography") ?: sfpf_author_archive_field($user_id, "biography_short") ?: $user->description;
    $short_bio = sfpf_author_archive_field($user_id, "biography_short");
    $public_email = sfpf_author_archive_field($user_id, "additional_public_email") ?: $user->user_email;
    $birth_date = sfpf_author_archive_plain(sfpf_author_archive_field($user_id, "birth_date"));
    $gender = sfpf_author_archive_plain(sfpf_author_archive_field($user_id, "gender"));
    $nationality = sfpf_author_archive_plain(sfpf_author_archive_field($user_id, "nationality"));
    $kgid = sfpf_author_archive_plain(sfpf_author_archive_field($user_id, "knowledge_graph_id"));
    $avatar = get_avatar_url($user_id, ["size" => 300]);
    $urls = sfpf_author_archive_url_group($user_id);
    $sameas = sfpf_author_archive_lines(sfpf_author_archive_field($user_id, "sameas"));
    $education = sfpf_author_archive_field($user_id, "education", []);
    $articles = sfpf_author_archive_field($user_id, "articles", []);
    $labels = ["website" => "Website", "linkedin" => "LinkedIn", "crunchbase" => "Crunchbase", "wikipedia" => "Wikipedia", "facebook" => "Facebook", "instagram" => "Instagram", "x" => "X", "youtube" => "YouTube", "imdb" => "IMDb", "muckrack" => "Muck Rack"];

    ob_start();
    ?>
    <main id="content" class="site-main sfpf-author-archive">
        <style>.sfpf-author-archive{max-width:1120px;margin:0 auto;padding:48px 20px 64px;color:#111827;font-family:inherit}.sfpf-author-hero{display:grid;grid-template-columns:180px 1fr;gap:32px;align-items:start;background:linear-gradient(135deg,#f8fafc 0%,#eff6ff 100%);border:1px solid #dbeafe;border-radius:28px;padding:32px;box-shadow:0 20px 50px rgba(15,23,42,.08)}.sfpf-author-avatar{width:180px;height:180px;border-radius:24px;object-fit:cover;border:1px solid #dbeafe;background:#fff}.sfpf-author-kicker{font-size:12px;font-weight:800;letter-spacing:.14em;text-transform:uppercase;color:#2563eb;margin:0 0 10px}.sfpf-author-name{font-size:42px;line-height:1.05;margin:0 0 10px;color:#0f172a}.sfpf-author-title{font-size:18px;line-height:1.5;color:#475569;margin:0 0 18px}.sfpf-author-bio{font-size:16px;line-height:1.75;color:#334155}.sfpf-author-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px;margin-top:22px}.sfpf-author-card{background:#fff;border:1px solid #e5e7eb;border-radius:18px;padding:18px}.sfpf-author-label{font-size:11px;font-weight:900;letter-spacing:.1em;text-transform:uppercase;color:#64748b;margin:0 0 6px}.sfpf-author-value{font-size:15px;color:#111827;word-break:break-word}.sfpf-author-section{margin-top:28px;background:#fff;border:1px solid #e5e7eb;border-radius:22px;padding:24px}.sfpf-author-section h2{margin:0 0 16px;font-size:22px}.sfpf-author-list{display:grid;gap:12px;margin:0;padding:0;list-style:none}.sfpf-author-list li{border:1px solid #eef2f7;background:#f8fafc;border-radius:14px;padding:13px 14px}.sfpf-author-links{display:flex;flex-wrap:wrap;gap:10px}.sfpf-author-links a{border:1px solid #bfdbfe;border-radius:999px;padding:8px 12px;background:#eff6ff;color:#1d4ed8;text-decoration:none;font-weight:700;font-size:13px}.sfpf-author-links a:hover{background:#dbeafe}.sfpf-author-muted{color:#64748b}.sfpf-author-schema-link{font-size:13px;word-break:break-all;color:#2563eb}@media(max-width:760px){.sfpf-author-hero{grid-template-columns:1fr;padding:22px}.sfpf-author-avatar{width:132px;height:132px}.sfpf-author-name{font-size:32px}.sfpf-author-grid{grid-template-columns:1fr}}</style>
        <section class="sfpf-author-hero"><img class="sfpf-author-avatar" src="<?php echo esc_url($avatar); ?>" alt="<?php echo esc_attr($name); ?>"><div><p class="sfpf-author-kicker">Author Profile</p><h1 class="sfpf-author-name"><?php echo esc_html($name); ?></h1><?php if ($title): ?><p class="sfpf-author-title"><?php echo esc_html($title); ?></p><?php endif; ?><?php if ($short_bio): ?><div class="sfpf-author-bio"><?php echo wp_kses_post(wpautop($short_bio)); ?></div><?php endif; ?><?php if ($urls || $sameas): ?><div class="sfpf-author-links"><?php foreach ($urls as $key => $url): if (empty($url)) continue; ?><a href="<?php echo esc_url($url); ?>" target="_blank" rel="noopener"><?php echo esc_html($labels[$key] ?? ucwords(str_replace("_", " ", $key))); ?></a><?php endforeach; ?><?php foreach ($sameas as $url): ?><a href="<?php echo esc_url($url); ?>" target="_blank" rel="noopener"><?php echo esc_html(parse_url($url, PHP_URL_HOST) ?: $url); ?></a><?php endforeach; ?></div><?php endif; ?></div></section>
        <section class="sfpf-author-grid" aria-label="Author facts"><?php if ($public_email): ?><div class="sfpf-author-card"><p class="sfpf-author-label">Email</p><div class="sfpf-author-value"><a href="mailto:<?php echo esc_attr($public_email); ?>"><?php echo esc_html($public_email); ?></a></div></div><?php endif; ?><?php if ($birth_date): ?><div class="sfpf-author-card"><p class="sfpf-author-label">Birth Date</p><div class="sfpf-author-value"><?php echo esc_html($birth_date); ?></div></div><?php endif; ?><?php if ($nationality): ?><div class="sfpf-author-card"><p class="sfpf-author-label">Nationality</p><div class="sfpf-author-value"><?php echo esc_html($nationality); ?></div></div><?php endif; ?><?php if ($gender): ?><div class="sfpf-author-card"><p class="sfpf-author-label">Gender</p><div class="sfpf-author-value"><?php echo esc_html($gender); ?></div></div><?php endif; ?><?php if ($kgid): ?><div class="sfpf-author-card"><p class="sfpf-author-label">Knowledge Graph</p><div class="sfpf-author-value"><a class="sfpf-author-schema-link" href="<?php echo esc_url("https://www.google.com/search?kgmid=" . rawurlencode($kgid)); ?>" target="_blank" rel="noopener"><?php echo esc_html($kgid); ?></a></div></div><?php endif; ?></section>
        <?php if ($bio): ?><section class="sfpf-author-section"><h2>Biography</h2><div class="sfpf-author-bio"><?php echo wp_kses_post(wpautop($bio)); ?></div></section><?php endif; ?>
        <?php if (is_array($education) && !empty($education)): ?><section class="sfpf-author-section"><h2>Education</h2><ul class="sfpf-author-list"><?php foreach ($education as $row): $college = trim((string) ($row["college"] ?? "")); if (!$college) continue; ?><li><strong><?php echo esc_html($college); ?></strong><?php if (!empty($row["designation"]) || !empty($row["major"])): ?> <span class="sfpf-author-muted">— <?php echo esc_html(trim(($row["designation"] ?? "") . " " . ($row["major"] ?? ""))); ?></span><?php endif; ?><?php if (!empty($row["year"])): ?> <span class="sfpf-author-muted">(<?php echo esc_html($row["year"]); ?>)</span><?php endif; ?></li><?php endforeach; ?></ul></section><?php endif; ?>
        <?php if (is_array($articles) && !empty($articles)): ?><section class="sfpf-author-section"><h2>Articles and Press</h2><ul class="sfpf-author-list"><?php foreach ($articles as $article): $url = $article["url"] ?? ""; $article_title = $article["title"] ?? $url; if (!$url && !$article_title) continue; ?><li><?php if ($url): ?><a href="<?php echo esc_url($url); ?>" target="_blank" rel="noopener"><?php echo esc_html($article_title ?: $url); ?></a><?php else: ?><?php echo esc_html($article_title); ?><?php endif; ?><?php if (!empty($article["source"])): ?> <span class="sfpf-author-muted">— <?php echo esc_html($article["source"]); ?></span><?php endif; ?></li><?php endforeach; ?></ul></section><?php endif; ?>
    </main>
    <?php
    return ob_get_clean();
}
