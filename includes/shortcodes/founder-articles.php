<?php

declare( strict_types=1 );

namespace sfpf_person_website;

/**
 * Founder URL formatting and article rendering.
 *
 * Callback names remain in the legacy namespace for template and hook compatibility.
 */

defined( 'ABSPATH' ) || exit;

// =============================================================================
// URL FORMATTING HELPER
// =============================================================================

/**
 * Format URL output with link/pretty/target options
 */
function format_url_output($url, $atts) {
    $link = filter_var($atts['link'], FILTER_VALIDATE_BOOLEAN);
    $pretty = filter_var($atts['pretty'], FILTER_VALIDATE_BOOLEAN);
    $target = !empty($atts['target']) ? $atts['target'] : '';

    // Get display text
    $display = $url;
    if ($pretty) {
        $display = preg_replace('#^https?://#', '', $url);
        $display = rtrim($display, '/');
    }

    if ($link) {
        $target_attr = $target ? ' target="' . esc_attr($target) . '"' : '';
        return '<a href="' . esc_url($url) . '"' . $target_attr . '>' . esc_html($display) . '</a>';
    }

    return esc_html($display);
}

// =============================================================================
// LINK REPEATER DISPLAY
// =============================================================================

/**
 * Display articles with multiple format options.
 *
 * Formats: titled (default), cards, sources, compact
 */
function founder_display_articles($user_id, $format = 'titled') {
    return sfpf_display_link_repeater($user_id, 'articles', $format, 'founder-article-links');
}

/**
 * Display additional profile URLs with the same presentation contract as articles.
 *
 * @param int    $user_id WordPress user ID.
 * @param string $format  titled, cards, sources, or compact.
 * @return string
 */
function founder_display_additional_urls($user_id, $format = 'titled') {
    return sfpf_display_link_repeater($user_id, 'additional_urls', $format, 'founder-additional-urls');
}

/**
 * Normalize a link repeater or its legacy newline-delimited value.
 *
 * @param mixed $links_raw Raw ACF value.
 * @return array<int,array{title:string,source:string,url:string}>
 */
function sfpf_normalize_link_repeater($links_raw) {
    $links = [];

    if (is_array($links_raw) && !empty($links_raw)) {
        foreach ($links_raw as $item) {
            if (!is_array($item)) continue;
            $url = trim((string) ($item['url'] ?? ''));
            if (!filter_var($url, FILTER_VALIDATE_URL)) continue;

            $source = trim((string) ($item['source'] ?? ''));
            if ($source === '') {
                $parsed = wp_parse_url($url);
                $source = preg_replace('/^www\./', '', $parsed['host'] ?? '');
            }

            $links[] = [
                'title' => trim((string) ($item['title'] ?? '')),
                'source' => $source,
                'url' => $url,
            ];
        }
    } elseif (is_string($links_raw) && trim($links_raw) !== '') {
        $urls = array_filter(array_map('trim', preg_split('/\R/', $links_raw) ?: []));
        foreach ($urls as $url) {
            if (!filter_var($url, FILTER_VALIDATE_URL)) continue;
            $parsed = wp_parse_url($url);
            $links[] = [
                'title' => '',
                'source' => preg_replace('/^www\./', '', $parsed['host'] ?? ''),
                'url' => $url,
            ];
        }
    }

    return $links;
}

/**
 * Render a user link repeater through the shared article presentation.
 *
 * @param int    $user_id      WordPress user ID.
 * @param string $field_name   ACF field name.
 * @param string $format       Display format.
 * @param string $context_class Context-specific CSS class.
 * @return string
 */
function sfpf_display_link_repeater($user_id, $field_name, $format = 'titled', $context_class = '') {
    $articles_raw = get_field($field_name, 'user_' . $user_id);

    $articles = sfpf_normalize_link_repeater($articles_raw);

    if (empty($articles)) {
        return '';
    }

    $allowed_formats = ['titled', 'cards', 'sources', 'compact'];
    $format = in_array($format, $allowed_formats, true) ? $format : 'titled';
    $context_class = preg_replace('/[^a-z0-9_-]/', '', strtolower((string) $context_class));
    $output = '<div class="founder-articles' . ($context_class !== '' ? ' ' . esc_attr($context_class) : '') . ' format-' . esc_attr($format) . '">';

    // Shared styles block (injected once)
    static $articles_styles_injected = false;
    if (!$articles_styles_injected) {
        $articles_styles_injected = true;
        $output .= '<style>
.founder-articles a.article-link {
    color: inherit;
    text-decoration: none;
    transition: color 0.15s ease;
}
.founder-articles a.article-link:hover {
    color: #2563eb;
}
.founder-articles a.article-link:hover .article-arrow {
    opacity: 1;
    transform: translateX(2px);
}
.founder-articles .article-arrow {
    display: inline-block;
    opacity: 0.35;
    transition: opacity 0.15s ease, transform 0.15s ease;
    font-size: 0.8em;
    margin-left: 6px;
    vertical-align: baseline;
}
.founder-articles .article-source-tag {
    display: inline-block;
    font-size: 11px;
    color: #9ca3af;
    margin-left: 10px;
    font-weight: 400;
    vertical-align: baseline;
}
.founder-articles .article-card-wrap {
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 16px 20px;
    margin-bottom: 10px;
    transition: border-color 0.15s ease, box-shadow 0.15s ease;
    background: #fff;
}
.founder-articles .article-card-wrap:hover {
    border-color: #cbd5e1;
    box-shadow: 0 1px 4px rgba(0,0,0,0.04);
}
.founder-articles .article-card-wrap a {
    text-decoration: none;
    color: inherit;
}
.founder-articles .article-card-wrap .card-url {
    font-size: 12px;
    color: #9ca3af;
    margin-top: 6px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
</style>';
    }

    switch ($format) {
        case 'cards':
            foreach ($articles as $a) {
                $title = esc_html($a['title'] ?: preg_replace('#^https?://#', '', rtrim($a['url'], '/')));
                $source = esc_html($a['source']);
                $url = esc_url($a['url']);
                $pretty_url = preg_replace('#^https?://#', '', rtrim($a['url'], '/'));
                $output .= '<div class="article-card-wrap">';
                $output .= '<a href="' . $url . '" target="_blank" rel="noopener">';
                $output .= '<div style="font-weight:600;font-size:15px;line-height:1.4;">' . $title;
                if ($source) {
                    $output .= '<span class="article-source-tag">' . $source . '</span>';
                }
                $output .= '</div>';
                $output .= '<div class="card-url">' . esc_html($pretty_url) . '</div>';
                $output .= '</a>';
                $output .= '</div>';
            }
            break;

        case 'sources':
            $grouped = [];
            foreach ($articles as $a) {
                $key = $a['source'] ?: 'Other';
                $grouped[$key][] = $a;
            }
            ksort($grouped);
            foreach ($grouped as $source => $items) {
                $output .= '<div style="margin-bottom:24px;">';
                $output .= '<div style="font-size:12px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:10px;padding-bottom:6px;border-bottom:1px solid #f3f4f6;">' . esc_html($source) . ' <span style="font-weight:400;">(' . count($items) . ')</span></div>';
                foreach ($items as $a) {
                    $title = esc_html($a['title'] ?: preg_replace('#^https?://#', '', rtrim($a['url'], '/')));
                    $output .= '<div style="padding:6px 0;"><a class="article-link" href="' . esc_url($a['url']) . '" target="_blank" rel="noopener"><span class="article-arrow" style="margin-left:0;margin-right:6px;">↗</span>' . $title . '</a></div>';
                }
                $output .= '</div>';
            }
            break;

        case 'compact':
            $output .= '<div style="border-top:1px solid #f3f4f6;">';
            foreach ($articles as $a) {
                $title = esc_html($a['title'] ?: preg_replace('#^https?://#', '', rtrim($a['url'], '/')));
                $source = esc_html($a['source']);
                $output .= '<div style="padding:10px 0;border-bottom:1px solid #f3f4f6;display:flex;justify-content:space-between;align-items:center;gap:12px;">';
                $output .= '<a class="article-link" href="' . esc_url($a['url']) . '" target="_blank" rel="noopener" style="font-weight:500;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">' . $title . '</a>';
                if ($source) {
                    $output .= '<span class="article-source-tag" style="margin-left:auto;flex-shrink:0;">' . $source . '</span>';
                }
                $output .= '</div>';
            }
            $output .= '</div>';
            break;

        case 'titled':
        default:
            foreach ($articles as $a) {
                $title = esc_html($a['title'] ?: preg_replace('#^https?://#', '', rtrim($a['url'], '/')));
                $source = esc_html($a['source']);
                $output .= '<div style="padding:8px 0;">';
                $output .= '<a class="article-link" href="' . esc_url($a['url']) . '" target="_blank" rel="noopener" style="font-weight:500;font-size:15px;line-height:1.5;">';
                $output .= '<span class="article-arrow" style="margin-left:0;margin-right:6px;">↗</span>';
                $output .= $title;
                $output .= '</a>';
                if ($source) {
                    $output .= '<span class="article-source-tag">' . $source . '</span>';
                }
                $output .= '</div>';
            }
            break;
    }

    $output .= '</div>';

    return $output;
}

// =============================================================================
// LOCATION BORN DISPLAY
