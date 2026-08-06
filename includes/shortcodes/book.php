<?php

declare( strict_types=1 );

namespace sfpf_person_website;

/**
 * Book field shortcode rendering.
 *
 * Callback names remain in the legacy namespace for template and hook compatibility.
 */

defined( 'ABSPATH' ) || exit;

// =============================================================================
// BOOK SHORTCODE
// =============================================================================

/**
 * Book shortcode: [book field="name"]
 *
 * Attributes:
 * - field: (required) The ACF field name to retrieve
 * - id: (optional) Specific book ID, defaults to primary book
 * - link: (optional) "true" to wrap URL in anchor tag
 * - target: (optional) Target for link (e.g., "_blank")
 * - pretty: (optional) "true" to strip protocol from URLs
 */
function book_shortcode($atts) {
    $atts = shortcode_atts([
        'field' => '',
        'id' => '',
        'link' => 'false',
        'target' => '',
        'pretty' => 'false',
    ], $atts, 'book');

    if (empty($atts['field'])) {
        return '';
    }

    // Get book ID
    $book_id = $atts['id'];
    if (empty($book_id)) {
        $primary_book = get_primary_book();
        if (!$primary_book) {
            return '';
        }
        $book_id = $primary_book->ID;
    }

    $field = $atts['field'];
    $value = '';

    // Handle special fields
    switch ($field) {
        case 'title':
        case 'name':
            $value = get_the_title($book_id);
            break;

        case 'cover':
        case 'featured_image':
        case 'featured_image_url':
            $value = get_the_post_thumbnail_url($book_id, 'full');
            break;

        case 'permalink':
            $value = get_permalink($book_id);
            break;

        default:
            $value = get_field($field, $book_id);
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
