<?php
namespace sfpf_person_website;

/**
 * Schema Manager
 *
 * Previews and checks of the person site's schema for the dashboard. Schema
 * is never stored: every value here is built live from the same builders the
 * page uses, so the dashboard always shows what visitors get.
 *
 * @package sfpf_person_website
 */

defined('ABSPATH') || exit;

/**
 * The schema JSON a post's page carries, built now.
 *
 * @param int $post_id Post ID
 * @return string|null Schema JSON string or null
 */
function get_post_schema($post_id) {
    $result = generate_post_schema((int) $post_id);
    return $result['success'] ? $result['schema'] : null;
}

/**
 * Where a post's schema comes from.
 *
 * @param int $post_id Post ID
 * @return string|null
 */
function get_post_schema_source($post_id) {
    return null !== get_post_schema($post_id) ? 'live: built when the page is viewed' : null;
}

/**
 * Log schema activity without hard depending on a missing helper.
 */
function log_schema_processing($post_id, $post_type, $success, $message) {
    if (!function_exists(__NAMESPACE__ . '\\write_log')) {
        return;
    }

    $status = $success ? 'success' : 'error';
    $label = $post_type ?: 'unknown';
    write_log("Schema {$label} #{$post_id}: {$message}", $status);
}

/**
 * Build the schema a post's page carries.
 *
 * @param int $post_id Post ID
 * @return array{success:bool,schema:?string,message:string}
 */
function generate_post_schema($post_id) {
    $post_type = get_post_type($post_id);
    $result = [
        'success' => false,
        'schema' => null,
        'message' => '',
    ];

    switch ($post_type) {
        case 'book':
            $nodes = build_book_nodes($post_id);
            break;

        case 'page':
            if (is_front_page_id($post_id)) {
                $schema_type = get_option('sfpf_homepage_schema_type', 'person');
            } elseif (is_biography_page_id($post_id)) {
                $schema_type = get_option('sfpf_biography_schema_type', 'profile_page_only');
            } else {
                $result['message'] = 'Page is not configured for SFPF page schema';
                return $result;
            }

            if ($schema_type === 'none') {
                $result['message'] = 'Schema output is turned off for this page';
                return $result;
            }

            $nodes = build_profile_page_nodes($schema_type, $post_id);
            break;

        default:
            $result['message'] = "Unknown post type: {$post_type}";
            return $result;
    }

    if (empty($nodes)) {
        $result['message'] = 'Schema generation returned empty result';
        return $result;
    }

    $result['success'] = true;
    $result['schema'] = schema_json(['@context' => 'https://schema.org', '@graph' => $nodes]);
    $result['message'] = 'Schema built from the current data';
    return $result;
}

/**
 * Check the schema of one batch of posts of a type.
 *
 * @param string $post_type Post type to process
 * @param int $batch_size Number of posts per batch
 * @param int $offset Offset for pagination
 * @return array Processing results
 */
function batch_reprocess_schemas($post_type, $batch_size = 20, $offset = 0) {
    $results = [
        'processed' => [],
        'errors' => [],
        'total' => 0,
        'batch' => 0,
        'offset' => $offset,
    ];

    $counts = wp_count_posts($post_type);
    $results['total'] = isset($counts->publish) ? $counts->publish : 0;

    $query = new \WP_Query([
        'post_type' => $post_type,
        'posts_per_page' => $batch_size,
        'offset' => $offset,
        'post_status' => 'publish',
        'fields' => 'ids',
        'no_found_rows' => true,
    ]);

    $results['batch'] = count($query->posts);

    foreach ($query->posts as $post_id) {
        $gen_result = generate_post_schema($post_id);

        $item = [
            'post_id' => $post_id,
            'title' => get_the_title($post_id),
            'success' => $gen_result['success'],
            'message' => $gen_result['message'],
            'schema' => $gen_result['schema'],
            'edit_link' => get_edit_post_link($post_id, 'raw'),
            'view_link' => get_permalink($post_id),
            'validator_link' => get_schema_validator_url(get_permalink($post_id)),
        ];

        if ($gen_result['success']) {
            $results['processed'][] = $item;
        } else {
            $results['errors'][] = $item;
        }
    }

    return $results;
}

/**
 * The homepage's schema, built now.
 */
function reprocess_homepage_schema() {
    $front_page_id = get_front_page_id();

    if (!$front_page_id) {
        return [
            'success' => false,
            'message' => 'No front page is set',
        ];
    }

    return generate_post_schema($front_page_id);
}

/**
 * The biography page's schema, built now.
 */
function reprocess_biography_schema() {
    $bio_page_id = (int) get_option('sfpf_page_biography', 0);

    if (!$bio_page_id) {
        return [
            'success' => false,
            'message' => 'No biography page is set',
        ];
    }

    return generate_post_schema($bio_page_id);
}

/**
 * Schema status for the dashboard.
 *
 * @return array Schema status summary
 */
function get_schema_status_summary() {
    $status = [
        'homepage' => [
            'enabled' => false,
            'has_schema' => false,
            'post_id' => null,
            'schema_type' => null,
        ],
        'books' => [
            'enabled' => is_snippet_enabled('sfpf_enable_book_cpt'),
            'total' => 0,
            'with_schema' => 0,
        ],
        'organizations' => [
            'enabled' => post_type_exists('organization'),
            'total' => 0,
            'with_schema' => 0,
        ],
    ];

    $front_page_id = get_front_page_id();
    if ($front_page_id) {
        $status['homepage']['enabled'] = true;
        $status['homepage']['post_id'] = $front_page_id;
        $status['homepage']['schema_type'] = get_option('sfpf_homepage_schema_type', 'person');
        $status['homepage']['has_schema'] = null !== get_post_schema($front_page_id);
    }

    // Every published book gets schema when its page is viewed.
    if ($status['books']['enabled']) {
        $counts = wp_count_posts('book');
        $status['books']['total'] = isset($counts->publish) ? (int) $counts->publish : 0;
        $status['books']['with_schema'] = $status['books']['total'];
    }

    // Organization pages get schema from SMC when it is active.
    if ($status['organizations']['enabled']) {
        $counts = wp_count_posts('organization');
        $status['organizations']['total'] = isset($counts->publish) ? (int) $counts->publish : 0;
        $status['organizations']['with_schema'] = class_exists('\\SMC\\OrganizationProfile\\Schema\\OrganizationSchema') ? $status['organizations']['total'] : 0;
    }

    return $status;
}

/**
 * The read-only "Schema Markup" fields on books and pages show the live schema.
 */
function load_live_schema_markup($value, $post_id = 0, $field = []) {
    $post_id = is_numeric($post_id) ? (int) $post_id : 0;
    if ($post_id <= 0 || !in_array(get_post_type($post_id), ['book', 'page'], true)) {
        return $value;
    }
    $schema = get_post_schema($post_id);
    return null !== $schema ? $schema : '';
}
\hexa_fields_on('load_value/name=schema_markup', __NAMESPACE__ . '\\load_live_schema_markup', 10, 3);
