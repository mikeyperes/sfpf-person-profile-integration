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
    ], $atts, 'organization');

    // Get organization ID
    $org_id = $atts['id'];
    if (empty($org_id)) {
        $primary_org = get_primary_organization();
        if (!$primary_org) {
            return '';
        }
        $org_id = $primary_org->ID;
    }

    if (($atts['action'] ?? '') === 'display_gallery') {
        $gallery = function_exists('get_field') ? get_field('gallery', $org_id) : [];
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
            $hq = get_field('headquarters', $org_id);
            $value = $hq['location'] ?? '';
            break;

        case 'headquarters_wikipedia':
            $hq = get_field('headquarters', $org_id);
            $value = $hq['wikipedia_url'] ?? '';
            break;

        case 'logo':
            $logo = get_field('image_cropped', $org_id);
            $value = isset($logo['url']) ? $logo['url'] : '';
            break;

        case 'permalink':
            $value = get_permalink($org_id);
            break;

        case 'gallery':
            $gallery = get_field('gallery', $org_id);
            $images = sfpf_normalize_gallery_images($gallery, $atts['size'] ?? 'large');
            if (($atts['format'] ?? '') === 'json') return wp_json_encode($images);
            if (($atts['format'] ?? '') === 'urls') return esc_html(implode("\n", array_map(function($image) { return $image['url'] ?? ''; }, $images)));
            if (($atts['format'] ?? '') === 'count') return (string) count($images);
            return sfpf_render_gallery_html($images, 'sfpf-organization-gallery', (int) ($atts['columns'] ?? 3));

        default:
            // Handle url_* fields (individual social URL fields)
            if (strpos($field, 'url_') === 0) {
                $platform = substr($field, 4); // strip 'url_'
                $value = get_field('url_' . $platform, $org_id);
                break;
            }

            $value = get_field($field, $org_id);
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
add_shortcode('organization', __NAMESPACE__ . '\\organization_shortcode');
