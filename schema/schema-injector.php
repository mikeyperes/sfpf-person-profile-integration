<?php
namespace sfpf_person_website;

/**
 * Schema display helpers for the dashboard.
 *
 * Front-end output goes through HexaWP Core's shared schema block; see
 * src/Schema/SchemaProvider.php.
 *
 * @package sfpf_person_website
 */

defined('ABSPATH') || exit;

/**
 * Get schema for display in admin
 *
 * @param int $post_id Post ID
 * @return array Schema info
 */
function get_schema_for_display($post_id) {
    $normalized = (string) get_post_schema($post_id);
    return [
        'raw' => $normalized,
        'formatted' => $normalized ? format_json_display($normalized) : '<em>No schema generated</em>',
        'valid' => '' !== $normalized,
        'validator_url' => get_schema_validator_url(get_permalink($post_id)),
        'google_url' => get_google_rich_results_url(get_permalink($post_id)),
    ];
}
