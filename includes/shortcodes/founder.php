<?php

declare( strict_types=1 );

namespace sfpf_person_website;

/**
 * Founder field, gallery, education, profession, and social shortcodes.
 *
 * Callback names remain in the legacy namespace for template and hook compatibility.
 */

defined( 'ABSPATH' ) || exit;

// =============================================================================
// FOUNDER SHORTCODE - [founder id="..."] or [founder action="..."]
// =============================================================================

/**
 * Founder shortcode handler
 *
 * [founder id="name"] - Get founder field value
 * [founder id="biography"] - Get biography
 * [founder action="display_education"] - Display formatted education
 * [founder action="display_professions_with_summary"] - Display professions with summaries
 * [founder action="display_socials"] - Display social links
 */
function founder_shortcode($atts) {
    $atts = shortcode_atts([
        'id' => '',
        'action' => '',
        'format' => 'html',
        'index' => '',
        'field' => '',
        'size' => 'full',
        'columns' => '3',
    ], $atts);

    $user_id = get_founder_user_id();
    if (!$user_id) {
        return '';
    }

    // Handle actions
    if (!empty($atts['action'])) {
        switch ($atts['action']) {
            case 'display_education':
                return founder_display_education($user_id);
            case 'display_gallery':
                return founder_display_gallery($user_id, $atts);
            case 'display_professions_with_summary':
                return founder_display_professions($user_id);
            case 'display_socials':
                return founder_display_socials($user_id);
            case 'display_articles':
                return founder_display_articles($user_id, $atts['format'] ?? 'titled');
            case 'display_additional_urls':
                return founder_display_additional_urls($user_id, $atts['format'] ?? 'titled');
            case 'display_faq':
                return founder_display_faq($user_id, $atts);
            case 'display_location_born':
                return founder_display_location_born($user_id, $atts['format'] ?? 'link');
            case 'display_knowledge_panel':
                $kgid = get_field('knowledge_graph_id', 'user_' . $user_id);
                $full_url = sfpf_knowledge_panel_url($kgid);
                if ($full_url === '') return '';
                return '<a href="' . esc_url($full_url) . '" target="_blank" rel="noopener" title="' . esc_attr($full_url) . '">' . esc_html($full_url) . '</a>';
            case 'display_nationality':
                $nationality = get_field('nationality', 'user_' . $user_id);
                if (empty($nationality)) return '';
                // Handle repeater
                if (is_array($nationality)) {
                    $values = array_filter(array_map(function($n) { return trim($n['value'] ?? ''); }, $nationality));
                } else {
                    $values = array_filter(array_map('trim', explode(',', $nationality)));
                }
                if (empty($values)) return '';
                return '<span class="founder-nationality">' . esc_html(implode(', ', $values)) . '</span>';
            case 'display_organizations_founded':
                return founder_display_organizations_founded($atts['format'] ?? 'cards');
            case 'display_bio_full':
                return founder_display_bio_full($user_id);
            default:
                return '';
        }
    }

    // Handle id-based retrieval
    $field_name = $atts['id'];
    if (empty($field_name)) {
        return '';
    }

    // Special handling for different fields
    switch ($field_name) {
        case 'name':
            $first = esc_html(get_user_meta($user_id, 'first_name', true));
            $last  = esc_html(get_user_meta($user_id, 'last_name', true));
            if ($first || $last) {
                $parts = [];
                if ($first) {
                    $parts[] = '<span class="first_name">' . $first . '</span>';
                }
                if ($last) {
                    $parts[] = '<span class="last_name">' . $last . '</span>';
                }
                return '<span class="founder-name">' . implode(' ', $parts) . '</span>';
            }
            $user = get_userdata($user_id);
            return $user ? '<span class="founder-name">' . rtrim(esc_html($user->display_name), '.') . '</span>' : '';

        case 'first_name':
            return esc_html(get_user_meta($user_id, 'first_name', true));

        case 'last_name':
            return esc_html(get_user_meta($user_id, 'last_name', true));

        case 'email':
            $user = get_userdata($user_id);
            return $user ? esc_html($user->user_email) : '';

        case 'website':
            $user = get_userdata($user_id);
            return $user ? esc_url($user->user_url) : '';

        case 'knowledge_graph_url':
            return esc_url(sfpf_knowledge_panel_url(get_field('knowledge_graph_id', 'user_' . $user_id)));

        case 'avatar':
            $size_map = [
                'thumbnail' => 150,
                'medium' => 300,
                'medium_large' => 768,
                'large' => 1024,
                'full' => 0,
            ];
            $size = $atts['size'];
            $px = isset($size_map[$size]) ? $size_map[$size] : 0;
            // Prefer real uploaded avatar (wp-user-avatars) to bypass Gravatar/LiteSpeed avatar-cache localization (which can serve a stale blank placeholder).
            $wpua_id = (int) get_user_meta($user_id, 'wp_user_avatar', true);
            if ($wpua_id) { $real = wp_get_attachment_image_url($wpua_id, 'full'); if ($real) { return esc_url($real); } }
            $wpua_meta = get_user_meta($user_id, 'wp_user_avatars', true);
            if (is_array($wpua_meta) && !empty($wpua_meta['full'])) { return esc_url($wpua_meta['full']); }
            if ($px > 0) {
                return esc_url(get_avatar_url($user_id, ['size' => $px]));
            }
            return esc_url(get_avatar_url($user_id));

        case 'professions':
            $professions = get_field('professions', 'user_' . $user_id);
            if (empty($professions)) return '';
            $names = [];
            foreach ($professions as $p) {
                if (!empty($p['name'])) {
                    $names[] = $p['name'];
                }
            }
            return $atts['format'] === 'json' ? json_encode($names) : implode(', ', $names);

        case 'education':
            $education = get_field('education', 'user_' . $user_id);
            if (empty($education)) return '';

            if (!empty($atts['index']) && is_numeric($atts['index'])) {
                $idx = intval($atts['index']);
                if (isset($education[$idx])) {
                    if (!empty($atts['field'])) {
                        return esc_html($education[$idx][$atts['field']] ?? '');
                    }
                    return esc_html($education[$idx]['college'] ?? '');
                }
                return '';
            }

            if ($atts['format'] === 'json') {
                return json_encode($education);
            }

            $output = '<ul class="founder-education">';
            foreach ($education as $edu) {
                $output .= '<li>';
                if (!empty($edu['college'])) {
                    $output .= '<span class="college">' . esc_html($edu['college']) . '</span>';
                }
                if (!empty($edu['designation'])) {
                    $output .= ' - <span class="designation">' . esc_html($edu['designation']) . '</span>';
                }
                if (!empty($edu['major'])) {
                    $output .= ' in <span class="major">' . esc_html($edu['major']) . '</span>';
                }
                if (!empty($edu['year'])) {
                    $output .= ' <span class="year">(' . esc_html($edu['year']) . ')</span>';
                }
                $output .= '</li>';
            }
            $output .= '</ul>';
            return $output;

        case 'gallery':
        case 'knowledge_graph_images':
            $gallery = get_field($field_name, 'user_' . $user_id);
            $images = sfpf_normalize_gallery_images($gallery, $atts['size'] ?? 'large');
            if (($atts['format'] ?? '') === 'json') return wp_json_encode($images);
            if (($atts['format'] ?? '') === 'urls') return esc_html(implode("\n", array_map(function($image) { return $image['url'] ?? ''; }, $images)));
            if (($atts['format'] ?? '') === 'count') return (string) count($images);
            return sfpf_render_gallery_html($images, 'sfpf-founder-gallery', (int) ($atts['columns'] ?? 3));

        case 'articles':
        case 'additional_urls':
            $links = sfpf_normalize_link_repeater(get_field($field_name, 'user_' . $user_id));
            if ($field_name === 'additional_urls') {
                $links = sfpf_filter_public_link_repeater($links);
            }
            if (empty($links)) return '';
            if ($atts['format'] === 'json') {
                return wp_json_encode($links);
            }
            if ($atts['format'] === 'count') {
                return (string) count($links);
            }
            $urls = array_column($links, 'url');
            return esc_html(implode("\n", $urls));

        case 'sameas':
            $urls = sfpf_filter_public_urls(get_field('sameas', 'user_' . $user_id));
            if (empty($urls)) return '';
            if ($atts['format'] === 'json') return wp_json_encode($urls);
            if ($atts['format'] === 'count') return (string) count($urls);
            return esc_html(implode("\n", $urls));

        case 'urls_wikidata':
            return '';

        case 'wikimedia_commons_urls':
            $links = sfpf_normalize_link_repeater(get_field($field_name, 'user_' . $user_id));
            $urls = array_values(array_unique(array_column($links, 'url')));
            if (empty($urls)) return '';
            if ($atts['format'] === 'json') return wp_json_encode($urls);
            if ($atts['format'] === 'count') return (string) count($urls);
            return esc_html(implode("\n", $urls));

        case 'faq':
            $faq = sfpf_normalize_faq_items(get_field('faq', 'user_' . $user_id));
            if ($atts['format'] === 'json') {
                return wp_json_encode($faq, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            }
            if ($atts['format'] === 'count') {
                return (string) count($faq);
            }
            return founder_display_faq($user_id, $atts);

        case 'location_born_location':
            $lb = get_field('location_born', 'user_' . $user_id);
            return esc_html($lb['location'] ?? '');

        case 'location_born_url':
            $lb = get_field('location_born', 'user_' . $user_id);
            return esc_url($lb['wikipedia_url'] ?? '');

        case 'nationality':
            $nationality = get_field('nationality', 'user_' . $user_id);
            if (empty($nationality) || !is_array($nationality)) {
                // Backward compat: if it's a string (old text field), return as-is
                if (is_string($nationality) && !empty($nationality)) {
                    return $atts['format'] === 'json' ? wp_json_encode(array_map('trim', explode(',', $nationality))) : esc_html($nationality);
                }
                return '';
            }
            $values = array_filter(array_map(function($n) { return trim($n['value'] ?? ''); }, $nationality));
            if (empty($values)) return '';
            if ($atts['format'] === 'json') return wp_json_encode(array_values($values));
            return esc_html(implode(', ', $values));

        case 'knows_language':
            $langs = get_field('knows_language', 'user_' . $user_id);
            if (empty($langs) || !is_array($langs)) return '';
            $lang_vals = array_filter(array_map(function($l) { return trim($l['value'] ?? ''); }, $langs));
            if (empty($lang_vals)) return '';
            if ($atts['format'] === 'json') return wp_json_encode(array_values($lang_vals));
            return esc_html(implode(', ', $lang_vals));

        case 'awards':
            $awards = get_field('awards', 'user_' . $user_id);
            if (empty($awards) || !is_array($awards)) return '';
            $award_vals = array_filter(array_map(function($a) { return trim($a['value'] ?? ''); }, $awards));
            if (empty($award_vals)) return '';
            if ($atts['format'] === 'json') return wp_json_encode(array_values($award_vals));
            return esc_html(implode(', ', $award_vals));

        default:
            // Handle url_* fields (pull from urls group)
            if (strpos($field_name, 'url_') === 0) {
                $platform = substr($field_name, 4);
                $urls = get_field('urls', 'user_' . $user_id);
                if (is_array($urls) && !empty($urls[$platform])) {
                    return esc_url($urls[$platform]);
                }
                return '';
            }

            // Try ACF field
            $value = get_field($field_name, 'user_' . $user_id);
            if ($value !== null && $value !== false && $value !== '') {
                return is_array($value) ? json_encode($value) : wp_kses_post($value);
            }
            return '';
    }
}

/**
 * Keep the person-profile implementation authoritative when generic tools also
 * provide a fallback [founder] shortcode.
 *
 * @return void
 */
function register_founder_shortcode() {
    add_shortcode('founder', __NAMESPACE__ . '\\founder_shortcode');
}
register_founder_shortcode();
add_action('init', __NAMESPACE__ . '\\register_founder_shortcode', 100);

/**
 * Display founder gallery in formatted HTML.
 */
function founder_display_gallery($user_id, $atts = []) {
    $gallery = function_exists('get_field') ? get_field('gallery', 'user_' . $user_id) : [];
    $images = sfpf_normalize_gallery_images($gallery, $atts['size'] ?? 'large');
    if (($atts['format'] ?? '') === 'json') return wp_json_encode($images);
    if (($atts['format'] ?? '') === 'urls') return esc_html(implode("\n", array_map(function($image) { return $image['url'] ?? ''; }, $images)));
    if (($atts['format'] ?? '') === 'count') return (string) count($images);
    return sfpf_render_gallery_html($images, 'sfpf-founder-gallery', (int) ($atts['columns'] ?? 3));
}

/**
 * Display founder education in formatted HTML
 */
function founder_display_education($user_id) {
    $education = get_field('education', 'user_' . $user_id);
    $education_keys = ['college', 'designation', 'major', 'year', 'wiki_url'];
    if (empty($education) || !sfpf_repeater_has_public_row($education, $education_keys)) {
        return '';
    }

    $output = '<div class="founder-education">';
    foreach ($education as $i => $edu) {
        if (!is_array($edu) || !sfpf_repeater_has_public_row([$edu], $education_keys)) {
            continue;
        }

        $output .= '<div class="education-item">';

        $school_name = esc_html($edu['college'] ?? '');
        $wiki_url = $edu['wiki_url'] ?? '';

        if ($school_name) {
            $output .= '<div class="college">';
            if ($wiki_url) {
                $output .= '<a href="' . esc_url($wiki_url) . '" target="_blank" rel="noopener">' . $school_name . '</a>';
            } else {
                $output .= $school_name;
            }
            $output .= '</div>';
        }

        $has_degree = !empty($edu['designation']) || !empty($edu['major']);
        if ($has_degree) {
            $output .= '<div class="degree">';
            if (!empty($edu['designation'])) {
                $output .= '<span class="designation">' . esc_html($edu['designation']) . '</span>';
            }
            if (!empty($edu['designation']) && !empty($edu['major'])) {
                $output .= ' in ';
            }
            if (!empty($edu['major'])) {
                $output .= '<span class="major">' . esc_html($edu['major']) . '</span>';
            }
            $output .= '</div>';
        }

        if (!empty($edu['year'])) {
            $output .= '<div class="year">' . esc_html($edu['year']) . '</div>';
        }

        $output .= '</div>';
    }
    $output .= '</div>';

    return $output;
}

/**
 * Display founder professions with summaries
 */
function founder_display_professions($user_id) {
    $professions = get_field('professions', 'user_' . $user_id);
    if (empty($professions)) {
        return '';
    }

    $output = '<div class="founder-professions">';
    foreach ($professions as $prof) {
        $prof_name = $prof['name'] ?? '';
        if (empty($prof_name)) continue;

        $output .= '<div class="profession-item">';
        $output .= '<div class="name">' . esc_html($prof_name) . '</div>';

        // If there's a linked page, show link and excerpt
        if (!empty($prof['page'])) {
            $page_id = is_array($prof['page']) ? $prof['page']['ID'] : $prof['page'];
            $page = get_post($page_id);
            if ($page) {
                $output .= '<a class="page-link" href="' . esc_url(get_permalink($page_id)) . '" target="_blank">View Details →</a>';
                if (!empty($page->post_content)) {
                    $output .= '<div class="page-content">' . apply_filters('the_content', $page->post_content) . '</div>';
                }
            }
        }

        // Show summary if available
        if (!empty($prof['summary'])) {
            $output .= '<div class="summary">' . wp_kses_post($prof['summary']) . '</div>';
        }

        $output .= '</div>';
    }
    $output .= '</div>';

    return $output;
}

/**
 * Display founder social links
 */
function founder_display_socials($user_id) {
    // Get socials from website settings (HWS Base Tools)
    if (!function_exists('get_field')) {
        return '';
    }

    $website = get_field('website', 'option');
    $socials = $website['social_media'] ?? [];

    if (empty($socials)) {
        return '';
    }

    $social_labels = [
        'facebook' => 'Facebook',
        'instagram' => 'Instagram',
        'twitter' => 'Twitter/X',
        'x' => 'X',
        'linkedin' => 'LinkedIn',
        'youtube' => 'YouTube',
        'tiktok' => 'TikTok',
        'github' => 'GitHub',
        'wikipedia' => 'Wikipedia',
        'imdb' => 'IMDb',
        'muckrack' => 'Muck Rack',
        'crunchbase' => 'Crunchbase',
    ];

    $output = '<div class="founder-socials">';
    $output .= '<ul class="social-list">';

    foreach ($socials as $platform => $url) {
        if (empty($url)) continue;
        $label = $social_labels[$platform] ?? ucfirst($platform);
        $output .= '<li class="social-item ' . esc_attr($platform) . '"><a class="social-link" href="' . esc_url($url) . '" target="_blank" rel="noopener">' . esc_html($label) . '</a></li>';
    }

    $output .= '</ul>';
    $output .= '</div>';

    return $output;
}
