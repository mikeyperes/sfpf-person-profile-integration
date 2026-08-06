<?php
namespace sfpf_person_website;

/**
 * Schema Manager
 * 
 * Central management for all schema operations.
 * Coordinates schema building, storage, and retrieval.
 * 
 * @package sfpf_person_website
 * @since 1.0.0
 */

defined('ABSPATH') || exit;

/**
 * Get schema for a post
 * 
 * @param int $post_id Post ID
 * @return string|null Schema JSON string or null
 */
function get_post_schema($post_id) {
    $schema = function_exists('get_field') ? get_field('schema_markup', $post_id) : '';

    if (empty($schema)) {
        $schema = get_post_meta($post_id, 'schema_markup', true);
    }

    if (empty($schema)) {
        $schema = get_post_meta($post_id, '_sfpf_schema', true);
    }

    return !empty($schema) ? $schema : null;
}

/**
 * Get the storage source currently holding schema for a post.
 *
 * @param int $post_id Post ID
 * @return string|null
 */
function get_post_schema_source($post_id) {
    $acf_schema = function_exists('get_field') ? get_field('schema_markup', $post_id) : '';
    if (!empty($acf_schema)) {
        return 'acf: schema_markup';
    }

    $meta_schema = get_post_meta($post_id, 'schema_markup', true);
    if (!empty($meta_schema)) {
        return 'post_meta: schema_markup';
    }

    $legacy_schema = get_post_meta($post_id, '_sfpf_schema', true);
    if (!empty($legacy_schema)) {
        return 'legacy post_meta: _sfpf_schema';
    }

    return null;
}

/**
 * Log schema processing activity without hard depending on a missing helper.
 *
 * @param int $post_id Post ID
 * @param string $post_type Post type
 * @param bool $success Whether processing succeeded
 * @param string $message Log message
 * @return void
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
 * Save schema for a post
 * 
 * @param int $post_id Post ID
 * @param array|string $schema Schema data (array will be JSON encoded)
 * @return bool Success
 */
function save_post_schema($post_id, $schema) {
    if (is_array($schema)) {
        // Remove empty values before encoding
        $schema = sanitize_schema($schema);
        $schema = schema_json($schema);
    }

    if (!is_string($schema) || trim($schema) === '') {
        log_schema_processing($post_id, get_post_type($post_id), false, 'Failed to save schema: empty payload');
        return false;
    }

    $schema_to_store = wp_slash($schema);

    if (function_exists('update_field')) {
        update_field('schema_markup', $schema_to_store, $post_id);
    }

    update_post_meta($post_id, 'schema_markup', $schema_to_store);
    update_post_meta($post_id, '_sfpf_schema', $schema_to_store);

    $stored_schema = get_post_schema($post_id);
    $success = ($stored_schema === $schema);

    if ($success) {
        log_schema_processing($post_id, get_post_type($post_id), true, 'Schema saved');
    } else {
        log_schema_processing($post_id, get_post_type($post_id), false, 'Failed to save schema');
    }

    return $success;
}

/**
 * Generate and save schema for a post based on its type
 * 
 * @param int $post_id Post ID
 * @return array Result with schema and status
 */
function generate_and_save_schema($post_id) {
    $post_type = get_post_type($post_id);
    $result = [
        'success' => false,
        'schema' => null,
        'message' => '',
    ];
    
    // Determine which builder to use
    switch ($post_type) {
        case 'book':
            $schema = build_book_schema($post_id);
            break;
            
        case 'page':
            if (is_front_page_id($post_id)) {
                $schema_type = get_option('sfpf_homepage_schema_type', 'person');
                $schema = build_homepage_schema_for_injection($schema_type, $post_id);
            } elseif (is_biography_page_id($post_id)) {
                $schema_type = get_option('sfpf_biography_schema_type', 'profile_page_only');
                $schema = build_homepage_schema_for_injection($schema_type, $post_id);
            } else {
                $result['message'] = 'Page is not configured for SFPF page schema';
                return $result;
            }

            if ($schema_type === 'none') {
                $result['message'] = 'Schema generation disabled for this page';
                return $result;
            }

            break;
            
        default:
            $result['message'] = "Unknown post type: {$post_type}";
            return $result;
    }
    
    if (empty($schema)) {
        $result['message'] = 'Schema generation returned empty result';
        return $result;
    }
    
    // Save the schema
    if (save_post_schema($post_id, $schema)) {
        $result['success'] = true;
        $result['schema'] = is_array($schema) ? schema_json($schema) : $schema;
        $result['message'] = 'Schema generated successfully';
    } else {
        $result['message'] = 'Failed to save schema';
    }
    
    return $result;
}

/**
 * Batch reprocess schemas for a post type
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
    
    // Get total count
    $counts = wp_count_posts($post_type);
    $results['total'] = isset($counts->publish) ? $counts->publish : 0;
    
    // Query posts
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
        $gen_result = generate_and_save_schema($post_id);
        
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
 * Reprocess all schemas for homepage
 * 
 * @return array Result
 */
function reprocess_homepage_schema() {
    $front_page_id = get_front_page_id();
    
    if (!$front_page_id) {
        return [
            'success' => false,
            'message' => 'No front page is set',
        ];
    }
    
    return generate_and_save_schema($front_page_id);
}

/**
 * Reprocess biography page schema.
 *
 * @return array Result
 */
function reprocess_biography_schema() {
    $bio_page_id = (int) get_option('sfpf_page_biography', 0);

    if (!$bio_page_id) {
        return [
            'success' => false,
            'message' => 'No biography page is set',
        ];
    }

    return generate_and_save_schema($bio_page_id);
}

/**
 * Get all schema statuses for dashboard
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
    
    // Homepage status
    $front_page_id = get_front_page_id();
    if ($front_page_id) {
        $status['homepage']['enabled'] = true;
        $status['homepage']['post_id'] = $front_page_id;
        $status['homepage']['schema_type'] = get_option('sfpf_homepage_schema_type', 'person');
        $schema = get_post_schema($front_page_id);
        $status['homepage']['has_schema'] = !empty($schema);
    }
    
    // Books status
    if ($status['books']['enabled']) {
        $counts = wp_count_posts('book');
        $status['books']['total'] = isset($counts->publish) ? $counts->publish : 0;
        
        // Count posts with schema
        $with_schema = get_posts([
            'post_type' => 'book',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'meta_query' => [
                [
                    'key' => 'schema_markup',
                    'value' => '',
                    'compare' => '!=',
                ],
            ],
        ]);
        $status['books']['with_schema'] = count($with_schema);
    }
    
    // Organizations status
    if ($status['organizations']['enabled']) {
        $counts = wp_count_posts('organization');
        $status['organizations']['total'] = isset($counts->publish) ? $counts->publish : 0;
        
        // Count posts with schema
        $with_schema = get_posts([
            'post_type' => 'organization',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'meta_query' => [
                [
                    'key' => 'schema_markup',
                    'value' => '',
                    'compare' => '!=',
                ],
            ],
        ]);
        $status['organizations']['with_schema'] = count($with_schema);
    }
    
    return $status;
}
