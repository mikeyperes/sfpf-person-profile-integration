<?php

declare( strict_types=1 );

namespace sfpf_person_website;

/**
 * Organization field and gallery shortcode rendering.
 *
 * Callback names remain in the legacy namespace for template and hook compatibility.
 */

defined( 'ABSPATH' ) || exit;

// =============================================================================
// ORGANIZATION SHORTCODE
// =============================================================================

/**
 * Register the scoped organization profile stylesheet.
 */
function sfpf_register_organization_profile_assets() {
    wp_register_style(
        'sfpf-organization-profile',
        SFPF_PLUGIN_URL . 'assets/frontend/organization-profile.css',
        [],
        SFPF_PLUGIN_VERSION
    );

    if (function_exists('is_singular') && is_singular('organization')) {
        wp_enqueue_style('sfpf-organization-profile');
    }
}
add_action('wp_enqueue_scripts', __NAMESPACE__ . '\\sfpf_register_organization_profile_assets');

/**
 * Resolve an organization explicitly, from the current CPT request, or from
 * the configured primary organization, in that order.
 */
function sfpf_resolve_organization_id($requested_id = '') {
    if ($requested_id !== '' && is_numeric($requested_id)) {
        $requested_id = absint($requested_id);
        return get_post_type($requested_id) === 'organization' ? $requested_id : 0;
    }

    if (function_exists('is_singular') && is_singular('organization')) {
        $queried_id = get_queried_object_id();
        if ($queried_id && get_post_type($queried_id) === 'organization') {
            return (int) $queried_id;
        }
    }

    $primary_org = get_primary_organization();
    return $primary_org ? (int) $primary_org->ID : 0;
}

/**
 * Read an organization field while retaining a post-meta fallback for sites
 * where ACF is temporarily unavailable.
 */
function sfpf_get_organization_field($field, $org_id) {
    if (function_exists('get_field')) {
        return get_field($field, $org_id);
    }

    return get_post_meta($org_id, $field, true);
}

/**
 * Render a complete, reusable organization profile from the Organization CPT.
 */
function sfpf_render_organization_profile($org_id, $atts = []) {
    $org_id = absint($org_id);
    if (!$org_id || get_post_type($org_id) !== 'organization') {
        return '';
    }

    $atts = wp_parse_args($atts, [
        'show_title' => 'true',
        'show_logo' => 'true',
        'heading' => 'h1',
    ]);

    $heading = strtolower((string) $atts['heading']);
    if (!in_array($heading, ['h1', 'h2'], true)) {
        $heading = 'h1';
    }

    $show_title = filter_var($atts['show_title'], FILTER_VALIDATE_BOOLEAN);
    $show_logo = filter_var($atts['show_logo'], FILTER_VALIDATE_BOOLEAN);
    $name = get_the_title($org_id);
    $subtitle = sfpf_get_organization_field('sub_title', $org_id);
    $summary = sfpf_get_organization_field('short_summary', $org_id);
    $company_info = sfpf_get_organization_field('company_info', $org_id);
    $mission = sfpf_get_organization_field('mission_statement', $org_id);
    $focus = sfpf_get_organization_field('seeks', $org_id);
    $founding_date = sfpf_get_organization_field('founding_date', $org_id);
    $headquarters = sfpf_get_organization_field('headquarters', $org_id);
    $legal_name = sfpf_get_organization_field('legal_name', $org_id);
    $website = sfpf_get_organization_field('url', $org_id);
    $email = sfpf_get_organization_field('email', $org_id);
    $logo = sfpf_get_organization_field('image_cropped', $org_id);

    $logo_url = '';
    $logo_alt = $name;
    $logo_id = 0;
    if (is_array($logo)) {
        $logo_id = absint($logo['ID'] ?? $logo['id'] ?? 0);
        $logo_url = (string) ($logo['sizes']['medium'] ?? $logo['url'] ?? '');
        $logo_alt = (string) ($logo['alt'] ?? $name);
    } elseif (is_numeric($logo)) {
        $logo_id = absint($logo);
        $logo_url = (string) wp_get_attachment_image_url($logo_id, 'medium');
        $logo_alt = (string) get_post_meta($logo_id, '_wp_attachment_image_alt', true) ?: $name;
    } elseif (is_string($logo) && filter_var($logo, FILTER_VALIDATE_URL)) {
        $logo_url = $logo;
    }

    $logo_html = '';
    if ($logo_id) {
        $logo_html = wp_get_attachment_image(
            $logo_id,
            'medium',
            false,
            [
                'class' => 'sfpf-organization-profile__logo',
                'alt' => $logo_alt,
            ]
        );
    } elseif ($logo_url) {
        $logo_html = '<img class="sfpf-organization-profile__logo" src="' . esc_url($logo_url) . '" alt="' . esc_attr($logo_alt) . '" loading="lazy" decoding="async">';
    }

    $details = [];
    if ($legal_name) {
        $details[] = ['label' => __('Legal name', 'sfpf-person-profile-integration'), 'value' => esc_html($legal_name)];
    }
    if ($founding_date) {
        $details[] = ['label' => __('Founded', 'sfpf-person-profile-integration'), 'value' => esc_html($founding_date)];
    }
    if (is_array($headquarters) && !empty($headquarters['location'])) {
        $location = esc_html($headquarters['location']);
        if (!empty($headquarters['wikipedia_url'])) {
            $location = '<a href="' . esc_url($headquarters['wikipedia_url']) . '" target="_blank" rel="noopener noreferrer">' . $location . '</a>';
        }
        $details[] = ['label' => __('Headquarters', 'sfpf-person-profile-integration'), 'value' => $location];
    }

    $links = [];
    if ($website && filter_var($website, FILTER_VALIDATE_URL)) {
        $links[] = ['label' => __('Website', 'sfpf-person-profile-integration'), 'url' => $website, 'primary' => true];
    }
    $social_fields = [
        'url_linkedin' => 'LinkedIn',
        'url_facebook' => 'Facebook',
        'url_instagram' => 'Instagram',
        'url_x' => 'X',
        'url_youtube' => 'YouTube',
        'url_tiktok' => 'TikTok',
        'url_github' => 'GitHub',
    ];
    foreach ($social_fields as $field => $label) {
        $url = sfpf_get_organization_field($field, $org_id);
        if ($url && filter_var($url, FILTER_VALIDATE_URL)) {
            $links[] = ['label' => $label, 'url' => $url, 'primary' => false];
        }
    }

    if (!wp_style_is('sfpf-organization-profile', 'registered')) {
        sfpf_register_organization_profile_assets();
    }
    wp_enqueue_style('sfpf-organization-profile');

    $title_id = 'sfpf-organization-title-' . $org_id;
    $output = '<article class="sfpf-organization-profile" data-organization-id="' . esc_attr((string) $org_id) . '"';
    if ($show_title) {
        $output .= ' aria-labelledby="' . esc_attr($title_id) . '"';
    }
    $output .= '>';

    $output .= '<header class="sfpf-organization-profile__hero">';
    if ($show_logo && $logo_html) {
        $output .= '<div class="sfpf-organization-profile__logo-wrap">' . $logo_html . '</div>';
    }
    $output .= '<div class="sfpf-organization-profile__hero-copy">';
    $output .= '<span class="sfpf-organization-profile__eyebrow">' . esc_html__('Organization', 'sfpf-person-profile-integration') . '</span>';
    if ($show_title) {
        $output .= '<' . $heading . ' id="' . esc_attr($title_id) . '" class="sfpf-organization-profile__title">' . esc_html($name) . '</' . $heading . '>';
    }
    if ($subtitle) {
        $output .= '<p class="sfpf-organization-profile__subtitle">' . esc_html($subtitle) . '</p>';
    }
    if ($summary) {
        $output .= '<div class="sfpf-organization-profile__summary">' . wp_kses_post($summary) . '</div>';
    }
    if ($links) {
        $output .= '<nav class="sfpf-organization-profile__links" aria-label="' . esc_attr(sprintf(__('%s links', 'sfpf-person-profile-integration'), $name)) . '">';
        foreach ($links as $link) {
            $class = $link['primary'] ? ' sfpf-organization-profile__link--primary' : '';
            $output .= '<a class="sfpf-organization-profile__link' . esc_attr($class) . '" href="' . esc_url($link['url']) . '" target="_blank" rel="noopener noreferrer">' . esc_html($link['label']) . '<span aria-hidden="true"> &rarr;</span></a>';
        }
        $output .= '</nav>';
    }
    $output .= '</div></header>';

    $has_main_content = !empty($company_info) || !empty($mission) || !empty($focus);
    if ($has_main_content || $details || $email) {
        $output .= '<div class="sfpf-organization-profile__body">';
        if ($has_main_content) {
            $output .= '<div class="sfpf-organization-profile__main">';
            if ($company_info) {
                $output .= '<section class="sfpf-organization-profile__section"><h2>' . esc_html(sprintf(__('About %s', 'sfpf-person-profile-integration'), $name)) . '</h2><div class="sfpf-organization-profile__rich-text">' . wp_kses_post($company_info) . '</div></section>';
            }
            if ($mission) {
                $output .= '<section class="sfpf-organization-profile__section"><h2>' . esc_html__('Mission', 'sfpf-person-profile-integration') . '</h2><div class="sfpf-organization-profile__rich-text">' . wp_kses_post($mission) . '</div></section>';
            }
            if ($focus) {
                $output .= '<section class="sfpf-organization-profile__section"><h2>' . esc_html__('Focus', 'sfpf-person-profile-integration') . '</h2><div class="sfpf-organization-profile__rich-text">' . wp_kses_post($focus) . '</div></section>';
            }
            $output .= '</div>';
        }
        if ($details || $email) {
            $output .= '<aside class="sfpf-organization-profile__facts" aria-label="' . esc_attr__('Organization details', 'sfpf-person-profile-integration') . '"><h2>' . esc_html__('Company details', 'sfpf-person-profile-integration') . '</h2><dl>';
            foreach ($details as $detail) {
                $output .= '<div class="sfpf-organization-profile__fact"><dt>' . esc_html($detail['label']) . '</dt><dd>' . wp_kses_post($detail['value']) . '</dd></div>';
            }
            if ($email && is_email($email)) {
                $output .= '<div class="sfpf-organization-profile__fact"><dt>' . esc_html__('Contact', 'sfpf-person-profile-integration') . '</dt><dd><a href="mailto:' . esc_attr($email) . '">' . esc_html($email) . '</a></dd></div>';
            }
            $output .= '</dl></aside>';
        }
        $output .= '</div>';
    }

    $output .= '</article>';
    return $output;
}

/**
 * Organization shortcode: [organization field="name"]
 *
 * Attributes:
 * - field: (required) The ACF field name to retrieve
 * - id: (optional) Specific organization ID, defaults to primary organization
 * - link: (optional) "true" to wrap URL in anchor tag
 * - target: (optional) Target for link (e.g., "_blank")
 * - pretty: (optional) "true" to strip protocol from URLs
 */
function organization_shortcode($atts) {
    $atts = shortcode_atts([
        'field' => '',
        'id' => '',
        'link' => 'false',
        'target' => '',
        'pretty' => 'false',
        'format' => 'html',
        'size' => 'large',
        'columns' => '3',
        'action' => '',
        'show_title' => 'true',
        'show_logo' => 'true',
        'heading' => 'h1',
    ], $atts, 'organization');

    $org_id = sfpf_resolve_organization_id($atts['id']);
    if (!$org_id) {
        return '';
    }

    if (($atts['action'] ?? '') === 'display_profile') {
        return sfpf_render_organization_profile($org_id, $atts);
    }

    if (($atts['action'] ?? '') === 'display_gallery') {
        $gallery = sfpf_get_organization_field('gallery', $org_id);
        $images = sfpf_normalize_gallery_images($gallery, $atts['size'] ?? 'large');
        if (($atts['format'] ?? '') === 'json') return wp_json_encode($images);
        if (($atts['format'] ?? '') === 'urls') return esc_html(implode("\n", array_map(function($image) { return $image['url'] ?? ''; }, $images)));
        if (($atts['format'] ?? '') === 'count') return (string) count($images);
        return sfpf_render_gallery_html($images, 'sfpf-organization-gallery', (int) ($atts['columns'] ?? 3));
    }

    $field = $atts['field'];
    if (empty($field)) {
        return '';
    }
    $value = '';

    // Handle special fields
    switch ($field) {
        case 'title':
        case 'name':
            $value = get_the_title($org_id);
            break;

        case 'headquarters_location':
            $hq = sfpf_get_organization_field('headquarters', $org_id);
            $value = $hq['location'] ?? '';
            break;

        case 'headquarters_wikipedia':
            $hq = sfpf_get_organization_field('headquarters', $org_id);
            $value = $hq['wikipedia_url'] ?? '';
            break;

        case 'logo':
            $logo = sfpf_get_organization_field('image_cropped', $org_id);
            $value = isset($logo['url']) ? $logo['url'] : '';
            break;

        case 'permalink':
            $value = get_permalink($org_id);
            break;

        case 'gallery':
            $gallery = sfpf_get_organization_field('gallery', $org_id);
            $images = sfpf_normalize_gallery_images($gallery, $atts['size'] ?? 'large');
            if (($atts['format'] ?? '') === 'json') return wp_json_encode($images);
            if (($atts['format'] ?? '') === 'urls') return esc_html(implode("\n", array_map(function($image) { return $image['url'] ?? ''; }, $images)));
            if (($atts['format'] ?? '') === 'count') return (string) count($images);
            return sfpf_render_gallery_html($images, 'sfpf-organization-gallery', (int) ($atts['columns'] ?? 3));

        default:
            // Handle url_* fields (individual social URL fields)
            if (strpos($field, 'url_') === 0) {
                $platform = substr($field, 4); // strip 'url_'
                $value = sfpf_get_organization_field('url_' . $platform, $org_id);
                break;
            }

            $value = sfpf_get_organization_field($field, $org_id);
            break;
    }

    if (empty($value)) {
        return '';
    }

    // Format URL if needed
    if (filter_var($value, FILTER_VALIDATE_URL)) {
        return format_url_output($value, $atts);
    }

    return $value;
}
if ( ! class_exists( '\\SMC\\OrganizationProfile\\Shortcodes\\OrganizationShortcode' ) && ! shortcode_exists( 'organization' ) ) {
    add_shortcode('organization', __NAMESPACE__ . '\\organization_shortcode');
}
