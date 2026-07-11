<?php

declare( strict_types=1 );

namespace sfpf_person_website;

/**
 * Reusable post loop shortcode rendering.
 *
 * Callback names remain in the legacy namespace for template and hook compatibility.
 */

defined( 'ABSPATH' ) || exit;

// ============================================================================
// ELEMENTOR LOOP SHORTCODE
// ============================================================================

/**
 * Elementor Loop Shortcode
 * [sfpf_loop cpt="book" columns="3" rows="2" responsive="true"]
 *
 * Displays posts using assigned Elementor Loop Item template
 */
function sfpf_loop_shortcode($atts) {
    $atts = shortcode_atts([
        'cpt' => 'book',
        'columns' => '3',
        'rows' => '',
        'responsive' => 'true',
    ], $atts);

    $cpt = sanitize_key($atts['cpt']);
    $columns = intval($atts['columns']) ?: 3;
    $responsive = $atts['responsive'] === 'true';
    $rows = !empty($atts['rows']) ? intval($atts['rows']) : 0;

    // Get assigned loop template
    $assignments = get_option('sfpf_elementor_loop_assignments', []);
    $template_id = $assignments[$cpt] ?? 0;

    if (!$template_id) {
        return '<!-- SFPF Loop: No template assigned for ' . esc_html($cpt) . ' -->';
    }

    // Calculate posts per page
    $posts_per_page = $rows > 0 ? ($columns * $rows) : -1;

    // Get posts
    $posts = get_posts([
        'post_type' => $cpt,
        'posts_per_page' => $posts_per_page,
        'post_status' => 'publish',
        'orderby' => 'date',
        'order' => 'DESC',
    ]);

    if (empty($posts)) {
        return '<!-- SFPF Loop: No ' . esc_html($cpt) . ' posts found -->';
    }

    // Build responsive styles
    $grid_styles = "display:grid;grid-template-columns:repeat({$columns}, 1fr);gap:20px;";

    if ($responsive) {
        $grid_styles .= "max-width:100%;";
    }

    $html = '<div class="sfpf-loop sfpf-loop-' . esc_attr($cpt) . '" style="' . $grid_styles . '">';

    // Check if Elementor is available
    if (class_exists('\\Elementor\\Plugin')) {
        $elementor = \Elementor\Plugin::instance();

        foreach ($posts as $post) {
            // Set up post data
            setup_postdata($post);

            // Render the loop item template
            $html .= '<div class="sfpf-loop-item">';

            // Use Elementor to render the template with current post context
            if (method_exists($elementor->frontend, 'get_builder_content_for_display')) {
                $html .= $elementor->frontend->get_builder_content_for_display($template_id, true);
            } else {
                $html .= $elementor->frontend->get_builder_content($template_id, true);
            }

            $html .= '</div>';
        }

        wp_reset_postdata();
    } else {
        // Fallback without Elementor
        foreach ($posts as $post) {
            $html .= '<div class="sfpf-loop-item" style="padding:15px;background:#f9fafb;border-radius:6px;">';
            $html .= '<h4 style="margin:0 0 10px;">' . esc_html($post->post_title) . '</h4>';
            $html .= '<p style="margin:0;color:#666;font-size:13px;">' . wp_trim_words($post->post_excerpt ?: $post->post_content, 20) . '</p>';
            $html .= '</div>';
        }
    }

    $html .= '</div>';

    // Add responsive CSS
    if ($responsive) {
        $html .= '<style>
        @media (max-width: 768px) {
            .sfpf-loop-' . esc_attr($cpt) . ' { grid-template-columns: repeat(2, 1fr) !important; }
        }
        @media (max-width: 480px) {
            .sfpf-loop-' . esc_attr($cpt) . ' { grid-template-columns: 1fr !important; }
        }
        </style>';
    }

    return $html;
}
add_shortcode('sfpf_loop', __NAMESPACE__ . '\\sfpf_loop_shortcode');
