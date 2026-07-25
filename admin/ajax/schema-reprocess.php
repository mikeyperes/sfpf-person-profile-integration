<?php

declare( strict_types=1 );

namespace sfpf_person_website;

/**
 * Schema reprocessing and full rebuild actions.
 *
 * Callback names remain in the legacy namespace for template and hook compatibility.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Reprocess schema
 */
function ajax_reprocess_schema() {
    verify_ajax_nonce();

    $type = sanitize_key($_POST['type'] ?? '');
    $count = 0;

    switch ($type) {
        case 'homepage':
            $schema_type = get_option('sfpf_homepage_schema_type', 'person');
            if ($schema_type !== 'none' && function_exists(__NAMESPACE__ . '\\reprocess_homepage_schema')) {
                $result = reprocess_homepage_schema();
                if (!empty($result['success'])) {
                    $count = 1;
                } else {
                    wp_send_json_error($result['message'] ?? 'Failed to reprocess homepage schema');
                }
            }
            write_log("Reprocessed homepage schema");
            break;

        case 'biography':
            $bio_schema_type = get_option('sfpf_biography_schema_type', 'profile_page_only');
            if ($bio_schema_type !== 'none' && function_exists(__NAMESPACE__ . '\\reprocess_biography_schema')) {
                $result = reprocess_biography_schema();
                if (!empty($result['success'])) {
                    $count = 1;
                } else {
                    wp_send_json_error($result['message'] ?? 'Failed to reprocess biography schema');
                }
            }
            write_log("Reprocessed biography schema");
            break;

        case 'books':
            $books = get_posts([
                'post_type' => 'book',
                'posts_per_page' => -1,
                'post_status' => 'publish',
            ]);

            foreach ($books as $book) {
                $result = function_exists(__NAMESPACE__ . '\\generate_and_save_schema')
                    ? generate_and_save_schema($book->ID)
                    : ['success' => false];
                if (!empty($result['success'])) {
                    $count++;
                }
            }
            write_log("Reprocessed {$count} book schemas");
            break;

        case 'organizations':
            $orgs = get_posts([
                'post_type' => 'organization',
                'posts_per_page' => -1,
                'post_status' => 'publish',
            ]);

            foreach ($orgs as $org) {
                $result = function_exists(__NAMESPACE__ . '\\generate_and_save_schema')
                    ? generate_and_save_schema($org->ID)
                    : ['success' => false];
                if (!empty($result['success'])) {
                    $count++;
                }
            }
            write_log("Reprocessed {$count} organization schemas");
            break;

        default:
            wp_send_json_error('Invalid schema type');
    }

    wp_send_json_success(['type' => $type, 'count' => $count]);
}
add_action('wp_ajax_sfpf_reprocess_schema', __NAMESPACE__ . '\\ajax_reprocess_schema');

/**
 * Rebuild all schemas
 */
function ajax_rebuild_all_schema() {
    verify_ajax_nonce();

    $counts = ['homepage' => 0, 'biography' => 0, 'books' => 0, 'organizations' => 0];

    // Homepage
    $hp_schema_type = get_option('sfpf_homepage_schema_type', 'person');
    if ($hp_schema_type !== 'none' && function_exists(__NAMESPACE__ . '\\reprocess_homepage_schema')) {
        $result = reprocess_homepage_schema();
        if (!empty($result['success'])) {
            $counts['homepage'] = 1;
        }
    }

    // Biography
    $bio_schema_type = get_option('sfpf_biography_schema_type', 'profile_page_only');
    if ($bio_schema_type !== 'none' && function_exists(__NAMESPACE__ . '\\reprocess_biography_schema')) {
        $result = reprocess_biography_schema();
        if (!empty($result['success'])) {
            $counts['biography'] = 1;
        }
    }

    // Books
    if (is_snippet_enabled('sfpf_enable_book_cpt')) {
        $books = get_posts([
            'post_type' => 'book',
            'posts_per_page' => -1,
            'post_status' => 'publish',
        ]);

        foreach ($books as $book) {
            $result = function_exists(__NAMESPACE__ . '\\generate_and_save_schema')
                ? generate_and_save_schema($book->ID)
                : ['success' => false];
            if (!empty($result['success'])) {
                $counts['books']++;
            }
        }
    }

    // Organizations
    if (post_type_exists('organization')) {
        $orgs = get_posts([
            'post_type' => 'organization',
            'posts_per_page' => -1,
            'post_status' => 'publish',
        ]);

        foreach ($orgs as $org) {
            $result = function_exists(__NAMESPACE__ . '\\generate_and_save_schema')
                ? generate_and_save_schema($org->ID)
                : ['success' => false];
            if (!empty($result['success'])) {
                $counts['organizations']++;
            }
        }
    }

    write_log("Rebuilt all schemas: homepage={$counts['homepage']}, biography={$counts['biography']}, books={$counts['books']}, orgs={$counts['organizations']}");

    wp_send_json_success($counts);
}
add_action('wp_ajax_sfpf_rebuild_all_schema', __NAMESPACE__ . '\\ajax_rebuild_all_schema');
