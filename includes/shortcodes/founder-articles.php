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
// ARTICLES DISPLAY
// =============================================================================

/**
 * Display articles with multiple format options
 * Supports new repeater format (title/source/url) with fallback to old textarea
 *
 * Formats: titled (default), cards, sources, compact
 */
function founder_display_articles($user_id, $format = 'titled') {
    $articles_raw = get_field('articles', 'user_' . $user_id);

    // Normalize to array of {title, source, url}
    $articles = [];
    if (is_array($articles_raw) && !empty($articles_raw)) {
        foreach ($articles_raw as $item) {
            if (empty($item['url'])) continue;
            $url = $item['url'];
            $source = $item['source'] ?? '';
            if (empty($source)) {
                $parsed = wp_parse_url($url);
                $source = preg_replace('/^www\./', '', $parsed['host'] ?? '');
            }
            $articles[] = [
                'title' => $item['title'] ?? '',
                'source' => $source,
                'url' => $url,
            ];
        }
    } elseif (is_string($articles_raw) && !empty(trim($articles_raw))) {
        $urls = array_filter(array_map('trim', explode("\n", $articles_raw)));
        foreach ($urls as $url) {
            if (!filter_var($url, FILTER_VALIDATE_URL)) continue;
            $parsed = wp_parse_url($url);
            $source = preg_replace('/^www\./', '', $parsed['host'] ?? '');
            $articles[] = [
                'title' => '',
                'source' => $source,
                'url' => $url,
            ];
        }
    }

    if (empty($articles)) {
        return '';
    }

    $output = '<div class="founder-articles format-' . esc_attr($format) . '">';

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
