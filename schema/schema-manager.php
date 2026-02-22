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
    return get_field('schema_markup', $post_id);
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
        $schema = json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
    
    $result = update_field('schema_markup', $schema, $post_id);
    
    if ($result !== false) {
        log_schema_processing($post_id, get_post_type($post_id), true, 'Schema saved');
    } else {
        log_schema_processing($post_id, get_post_type($post_id), false, 'Failed to save schema');
    }
    
    return $result !== false;
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
            
        case 'organization':
            $schema = build_organization_schema($post_id);
            break;
            
        case 'page':
            // Check if this is the front page
            if (is_front_page_id($post_id)) {
                $schema_type = get_field('schema_type', $post_id) ?: 'profile_page';
                $schema = build_homepage_schema($post_id, $schema_type);
            } else {
                $result['message'] = 'Page is not the front page';
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
        $result['schema'] = is_array($schema) 
            ? json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
            : $schema;
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
            'enabled' => is_snippet_enabled('sfpf_enable_organization_cpt'),
            'total' => 0,
            'with_schema' => 0,
        ],
    ];
    
    // Homepage status
    $front_page_id = get_front_page_id();
    if ($front_page_id) {
        $status['homepage']['enabled'] = true;
        $status['homepage']['post_id'] = $front_page_id;
        $status['homepage']['schema_type'] = get_field('schema_type', $front_page_id) ?: 'profile_page';
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
