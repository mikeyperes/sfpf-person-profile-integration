<?php

declare( strict_types=1 );

namespace sfpf_person_website;

/**
 * Guarded diagnostics, repair tools, and debug export actions.
 *
 * Callback names remain in the legacy namespace for template and hook compatibility.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Run debug action
 */
function ajax_run_debug() {
    verify_ajax_nonce();

    $action = sanitize_key($_POST['debug_action'] ?? '');
    $output = '';

    switch ($action) {
        case 'check_homepage_schema':
            $output = debug_homepage_schema();
            break;
        case 'check_founder_data':
            $output = debug_founder_data();
            break;
        case 'check_injection_hook':
            $output = debug_injection_hook();
            break;
        case 'test_schema_build':
            $output = debug_test_schema_build();
            break;
        case 'check_elementor_templates':
            $output = debug_elementor_templates();
            break;
        case 'check_loop_items':
            $output = debug_loop_items();
            break;
        case 'check_template_meta':
            $output = debug_template_meta();
            break;
        case 'repair_elementor_templates':
            $output = debug_repair_elementor_templates();
            break;
        case 'check_professions':
            $output = debug_professions();
            break;
        case 'check_user_meta':
            $output = debug_user_meta();
            break;
        case 'list_acf_fields':
            $output = debug_acf_fields();
            break;
        default:
            $output = "Unknown debug action: {$action}";
    }

    wp_send_json_success(['output' => $output]);
}
add_action('wp_ajax_sfpf_run_debug', __NAMESPACE__ . '\\ajax_run_debug');

/**
 * Debug: Repair Elementor templates by re-importing data
 */
function debug_repair_elementor_templates() {
    $output = "=== REPAIR ELEMENTOR TEMPLATES ===\n\n";

    // Template definitions
    $templates_to_repair = [
        'hexa-book-default-loop' => [
            'file' => 'hexa-book-default-loop.json',
            'post_type' => 'book',
        ],
        'hexa-organization-default-loop' => [
            'file' => 'hexa-organization-default-loop.json',
            'post_type' => 'organization',
        ],
        'hexa-testimonial-default-loop' => [
            'file' => 'hexa-testimonial-default-loop.json',
            'post_type' => 'testimonial',
        ],
    ];

    // Find existing templates that need repair
    $templates = get_posts([
        'post_type' => 'elementor_library',
        'posts_per_page' => -1,
        'post_status' => 'any',
        'meta_key' => '_elementor_template_type',
        'meta_value' => 'loop-item',
    ]);

    $output .= "Found " . count($templates) . " loop-item templates\n\n";

    $repaired = 0;
    foreach ($templates as $t) {
        $output .= "Processing: {$t->post_title} (ID: {$t->ID})\n";

        // Check if _elementor_data is empty
        $current_data = get_post_meta($t->ID, '_elementor_data', true);

        if (!empty($current_data)) {
            $decoded = json_decode($current_data, true);
            if (!empty($decoded)) {
                $output .= "  ✅ Already has valid data (" . count($decoded) . " elements)\n\n";
                continue;
            }
        }

        $output .= "  ⚠️ Empty or invalid _elementor_data, attempting repair...\n";

        // Try to find matching JSON file
        $json_dir = SFPF_PLUGIN_DIR . 'assets/elementor-templates/';
        $matched_file = null;

        foreach ($templates_to_repair as $key => $info) {
            if (stripos($t->post_title, 'book') !== false && stripos($info['file'], 'book') !== false) {
                $matched_file = $json_dir . $info['file'];
                break;
            } elseif (stripos($t->post_title, 'organization') !== false && stripos($info['file'], 'organization') !== false) {
                $matched_file = $json_dir . $info['file'];
                break;
            } elseif (stripos($t->post_title, 'testimonial') !== false && stripos($info['file'], 'testimonial') !== false) {
                $matched_file = $json_dir . $info['file'];
                break;
            }
        }

        if ($matched_file && file_exists($matched_file)) {
            $json_content = file_get_contents($matched_file);
            $template_data = json_decode($json_content, true);

            if ($template_data && isset($template_data['content'])) {
                // Update the _elementor_data
                update_post_meta($t->ID, '_elementor_data', wp_json_encode($template_data['content']));

                // Also ensure other meta is set
                update_post_meta($t->ID, '_elementor_template_type', 'loop-item');
                update_post_meta($t->ID, '_elementor_edit_mode', 'builder');
                update_post_meta($t->ID, '_elementor_version', defined('ELEMENTOR_VERSION') ? ELEMENTOR_VERSION : '3.25.0');

                // Set taxonomy
                wp_set_object_terms($t->ID, 'loop-item', 'elementor_library_type');

                $output .= "  ✅ Repaired! Imported " . count($template_data['content']) . " elements from " . basename($matched_file) . "\n";
                $repaired++;
            } else {
                $output .= "  ❌ Could not parse JSON file\n";
            }
        } else {
            $output .= "  ❌ No matching JSON file found\n";
        }

        $output .= "\n";
    }

    $output .= "=== SUMMARY ===\n";
    $output .= "Repaired: {$repaired} templates\n";
    $output .= "\nNote: After repair, you may need to:\n";
    $output .= "1. Edit the template in Elementor\n";
    $output .= "2. Save/Update it once to regenerate CSS\n";

    return $output;
}

/**
 * Debug: Check homepage schema
 */
function debug_homepage_schema() {
    $output = "=== HOMEPAGE SCHEMA DEBUG ===\n\n";

    // Check front page settings
    $show_on_front = get_option('show_on_front');
    $page_on_front = get_option('page_on_front');
    $output .= "show_on_front: {$show_on_front}\n";
    $output .= "page_on_front: {$page_on_front}\n\n";

    // Check schema type option
    $schema_type = get_option('sfpf_homepage_schema_type', 'person');
    $output .= "sfpf_homepage_schema_type: {$schema_type}\n\n";

    if ($show_on_front !== 'page') {
        $output .= "❌ PROBLEM: WordPress is not set to use a static homepage.\n";
        $output .= "   Go to Settings > Reading and set 'Your homepage displays' to 'A static page'\n";
        return $output;
    }

    if (!$page_on_front) {
        $output .= "❌ PROBLEM: No homepage is set.\n";
        return $output;
    }

    // Check if schema is stored
    $schema = function_exists(__NAMESPACE__ . '\\get_post_schema')
        ? get_post_schema($page_on_front)
        : get_post_meta($page_on_front, 'schema_markup', true);
    $schema_source = function_exists(__NAMESPACE__ . '\\get_post_schema_source')
        ? get_post_schema_source($page_on_front)
        : null;

    if ($schema) {
        $output .= "✅ Schema is stored in " . ($schema_source ?: 'post meta') . "\n";
        $output .= "Schema length: " . strlen($schema) . " bytes\n\n";

        // Validate JSON
        $decoded = json_decode($schema);
        if (json_last_error() === JSON_ERROR_NONE) {
            $output .= "✅ Schema is valid JSON\n\n";
            $output .= "Schema preview:\n" . substr($schema, 0, 500) . "...\n";
        } else {
            $output .= "❌ Schema is invalid JSON: " . json_last_error_msg() . "\n";
        }
    } else {
        $output .= "❌ No schema stored in canonical or legacy schema storage\n";
        $output .= "   Click 'Reprocess Homepage Schema' button to generate\n";
    }

    return $output;
}

/**
 * Debug: Check founder data
 */
function debug_founder_data() {
    $output = "=== FOUNDER DATA DEBUG ===\n\n";

    $founder_id = get_founder_user_id();
    $output .= "Founder User ID: " . ($founder_id ?: 'NOT SET') . "\n\n";

    if (!$founder_id) {
        $output .= "❌ No founder configured.\n";
        $output .= "   Go to Website Settings and set the Founder user.\n";
        return $output;
    }

    $user = get_userdata($founder_id);
    if (!$user) {
        $output .= "❌ User ID {$founder_id} not found!\n";
        return $output;
    }

    $output .= "User Data:\n";
    $output .= "  - display_name: {$user->display_name}\n";
    $output .= "  - user_email: {$user->user_email}\n";
    $output .= "  - first_name: " . get_user_meta($founder_id, 'first_name', true) . "\n";
    $output .= "  - last_name: " . get_user_meta($founder_id, 'last_name', true) . "\n\n";

    // Check entity type
    $entity_type = get_field('entity_type', 'user_' . $founder_id);
    $output .= "Entity Type: " . ($entity_type ?: 'NOT SET') . "\n";

    // Check title
    $title = get_field('title', 'user_' . $founder_id);
    $output .= "Title: " . ($title ?: 'NOT SET') . "\n";

    // Check biography
    $bio = get_field('biography', 'user_' . $founder_id);
    $output .= "Biography: " . ($bio ? strlen($bio) . ' chars' : 'NOT SET') . "\n";

    return $output;
}

/**
 * Debug: Check injection hook
 */
function debug_injection_hook() {
    $output = "=== SCHEMA INJECTION HOOK DEBUG ===\n\n";

    $callback = __NAMESPACE__ . '\\inject_schema_markup';
    $hook_priority = has_action('wp_head', $callback);

    if (is_admin()) {
        $output .= "ℹ️ This debug action runs in admin/AJAX context.\n";
        $output .= "   SFPF only attaches schema injection during frontend requests, so a missing wp_head callback here is expected.\n\n";
    } elseif ($hook_priority !== false) {
        $output .= "✅ Hook found at priority {$hook_priority}\n";
        $output .= "   Function: {$callback}\n";
    } else {
        $output .= "❌ inject_schema_markup hook NOT found in wp_head during a frontend request.\n";
        $output .= "   This means schema will not be injected.\n\n";
    }

    $output .= "Runtime context:\n";
    $output .= "  - is_admin(): " . (is_admin() ? 'Yes' : 'No') . "\n";
    $output .= "  - front page configured: " . (get_front_page_id() ? 'Yes' : 'No') . "\n";
    $output .= "  - biography page configured: " . (get_option('sfpf_page_biography') ? 'Yes' : 'No') . "\n";

    // Check if function exists
    $output .= "\nFunction exists:\n";
    $output .= "  - enable_schema_injection: " . (function_exists(__NAMESPACE__ . '\\enable_schema_injection') ? '✅ Yes' : '❌ No') . "\n";
    $output .= "  - inject_schema_markup: " . (function_exists(__NAMESPACE__ . '\\inject_schema_markup') ? '✅ Yes' : '❌ No') . "\n";
    $output .= "  - get_post_schema: " . (function_exists(__NAMESPACE__ . '\\get_post_schema') ? '✅ Yes' : '❌ No') . "\n";

    return $output;
}

/**
 * Debug: Test schema build
 */
function debug_test_schema_build() {
    $output = "=== TEST SCHEMA BUILD ===\n\n";

    $front_page_id = get_front_page_id();
    if (!$front_page_id) {
        $output .= "❌ No front page set\n";
        return $output;
    }

    $schema_type = get_option('sfpf_homepage_schema_type', 'person');
    $output .= "Schema type setting: {$schema_type}\n\n";

    if ($schema_type === 'none') {
        $output .= "Schema injection is disabled.\n";
        return $output;
    }

    // Try to build schema
    if (function_exists(__NAMESPACE__ . '\\build_homepage_schema')) {
        $schema = build_homepage_schema($front_page_id, $schema_type);
        if ($schema) {
            $json = json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            $output .= "✅ Schema built successfully!\n\n";
            $output .= "Schema:\n{$json}\n";
        } else {
            $output .= "❌ build_homepage_schema returned empty\n";
        }
    } else {
        $output .= "❌ build_homepage_schema function not found\n";
    }

    return $output;
}

/**
 * Debug: Check Elementor templates
 */
function debug_elementor_templates() {
    $output = "=== ELEMENTOR TEMPLATES DEBUG ===\n\n";

    $templates = get_posts([
        'post_type' => 'elementor_library',
        'posts_per_page' => -1,
        'post_status' => 'any',
    ]);

    $output .= "Total Elementor templates: " . count($templates) . "\n\n";

    foreach ($templates as $t) {
        $type = get_post_meta($t->ID, '_elementor_template_type', true);
        $output .= "ID: {$t->ID} | Type: {$type} | Title: {$t->post_title}\n";
    }

    return $output;
}

/**
 * Debug: Check loop items
 */
function debug_loop_items() {
    $output = "=== ELEMENTOR LOOP ITEMS DEBUG ===\n\n";

    $loop_items = get_posts([
        'post_type' => 'elementor_library',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'tax_query' => [
            [
                'taxonomy' => 'elementor_library_type',
                'field' => 'slug',
                'terms' => 'loop-item',
            ],
        ],
    ]);

    $output .= "Loop items found: " . count($loop_items) . "\n\n";

    if (empty($loop_items)) {
        // Try alternative query
        $output .= "Trying alternative query (by meta)...\n";
        $loop_items = get_posts([
            'post_type' => 'elementor_library',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'meta_query' => [
                [
                    'key' => '_elementor_template_type',
                    'value' => 'loop-item',
                ],
            ],
        ]);
        $output .= "Found by meta: " . count($loop_items) . "\n\n";
    }

    foreach ($loop_items as $item) {
        $output .= "ID: {$item->ID} | {$item->post_title}\n";
        $data = get_post_meta($item->ID, '_elementor_data', true);
        $output .= "  - Has _elementor_data: " . ($data ? 'Yes (' . strlen($data) . ' bytes)' : 'NO') . "\n";
    }

    return $output;
}

/**
 * Debug: Check template metadata
 */
function debug_template_meta() {
    $output = "=== ELEMENTOR TEMPLATE METADATA DEBUG ===\n\n";

    // Get templates imported by our plugin
    $templates = get_posts([
        'post_type' => 'elementor_library',
        'posts_per_page' => 10,
        'post_status' => 'any',
        'meta_key' => '_elementor_template_type',
        'meta_value' => 'loop-item',
    ]);

    if (empty($templates)) {
        $output .= "No loop-item templates found.\n";
        return $output;
    }

    foreach ($templates as $t) {
        $output .= "=== Template ID: {$t->ID} ===\n";
        $output .= "Title: {$t->post_title}\n";
        $output .= "Status: {$t->post_status}\n\n";

        // Get all meta
        $meta = get_post_meta($t->ID);
        foreach ($meta as $key => $values) {
            if (strpos($key, '_elementor') !== false || strpos($key, 'sfpf') !== false) {
                $value = $values[0];
                if (strlen($value) > 200) {
                    $value = substr($value, 0, 200) . '...';
                }
                $output .= "  {$key}: {$value}\n";
            }
        }

        // Check _elementor_data specifically
        $data = get_post_meta($t->ID, '_elementor_data', true);
        $output .= "\n_elementor_data analysis:\n";
        if (empty($data)) {
            $output .= "  ❌ EMPTY - This is why the template appears blank!\n";
        } else {
            $output .= "  Length: " . strlen($data) . " bytes\n";
            $decoded = json_decode($data, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $output .= "  ❌ Invalid JSON: " . json_last_error_msg() . "\n";
            } else {
                $output .= "  ✅ Valid JSON\n";
                $output .= "  Elements count: " . (is_array($decoded) ? count($decoded) : 'N/A') . "\n";
                // Show first element structure
                if (is_array($decoded) && !empty($decoded[0])) {
                    $output .= "  First element type: " . ($decoded[0]['elType'] ?? 'unknown') . "\n";
                }
            }
        }

        // Check taxonomy
        $terms = wp_get_object_terms($t->ID, 'elementor_library_type', ['fields' => 'names']);
        $output .= "\nTaxonomy terms: " . (is_array($terms) ? implode(', ', $terms) : 'none') . "\n";

        $output .= "\n";
    }

    // Add JSON file analysis
    $output .= "\n=== SOURCE JSON FILES ANALYSIS ===\n\n";
    $json_dir = SFPF_PLUGIN_DIR . 'assets/elementor-templates/';

    if (is_dir($json_dir)) {
        $files = glob($json_dir . '*.json');
        foreach ($files as $file) {
            $filename = basename($file);
            $content = file_get_contents($file);
            $data = json_decode($content, true);

            $output .= "{$filename}:\n";
            $output .= "  Size: " . strlen($content) . " bytes\n";
            $output .= "  Has 'content': " . (isset($data['content']) ? 'Yes (' . count($data['content']) . ' elements)' : 'No') . "\n";
            $output .= "\n";
        }
    }

    return $output;
}

/**
 * Debug: Check professions field
 */
function debug_professions() {
    $output = "=== PROFESSIONS FIELD DEBUG ===\n\n";

    $founder_id = get_founder_user_id();
    if (!$founder_id) {
        $output .= "❌ No founder user ID\n";
        return $output;
    }

    $output .= "Founder User ID: {$founder_id}\n\n";

    // Get professions using get_field
    $profs = get_field('professions', 'user_' . $founder_id);

    $output .= "get_field('professions', 'user_{$founder_id}'):\n";
    $output .= "Type: " . gettype($profs) . "\n";

    if ($profs === null || $profs === false) {
        $output .= "Value: " . var_export($profs, true) . "\n\n";
        $output .= "❌ Field returned null/false - field may not exist\n";
    } elseif (empty($profs)) {
        $output .= "Value: empty array/string\n";
        $output .= "Field exists but is empty.\n";
    } else {
        $output .= "Count: " . (is_array($profs) ? count($profs) : 'N/A') . "\n\n";
        $output .= "Raw data:\n" . print_r($profs, true) . "\n";
    }

    // Also check direct user meta
    $output .= "\n=== Direct User Meta Check ===\n";
    $meta_value = get_user_meta($founder_id, 'professions', true);
    $output .= "get_user_meta() result:\n";
    $output .= "Type: " . gettype($meta_value) . "\n";
    if ($meta_value) {
        $output .= "Value: " . print_r($meta_value, true) . "\n";
    } else {
        $output .= "Value: empty\n";
    }

    return $output;
}

/**
 * Debug: Check user meta
 */
function debug_user_meta() {
    $output = "=== USER META DEBUG ===\n\n";

    $founder_id = get_founder_user_id();
    if (!$founder_id) {
        $output .= "❌ No founder user ID\n";
        return $output;
    }

    $output .= "All user meta for user {$founder_id}:\n\n";

    $all_meta = get_user_meta($founder_id);
    foreach ($all_meta as $key => $values) {
        // Skip internal WP fields
        if (in_array($key, ['session_tokens', 'wp_capabilities', 'wp_user_level', 'rich_editing', 'syntax_highlighting'])) {
            continue;
        }

        $value = $values[0];
        if (is_serialized($value)) {
            $value = '[serialized] ' . substr($value, 0, 100);
        } elseif (strlen($value) > 100) {
            $value = substr($value, 0, 100) . '...';
        }
        $output .= "{$key}: {$value}\n";
    }

    return $output;
}

/**
 * Debug: List ACF fields for user
 */
function debug_acf_fields() {
    $output = "=== ACF FIELDS FOR USER ===\n\n";

    $founder_id = get_founder_user_id();
    if (!$founder_id) {
        $output .= "❌ No founder user ID\n";
        return $output;
    }

    $output .= "Checking ACF fields for user_{$founder_id}:\n\n";

    // List of expected fields
    $fields = [
        'entity_type', 'title', 'biography', 'biography_short',
        'professions', 'education', 'job_title', 'sameas'
    ];

    foreach ($fields as $field) {
        $value = get_field($field, 'user_' . $founder_id);
        $type = gettype($value);

        if ($value === null || $value === false) {
            $output .= "❌ {$field}: NOT SET\n";
        } elseif (is_array($value)) {
            $output .= "✅ {$field}: array with " . count($value) . " items\n";
        } elseif (is_string($value)) {
            $output .= "✅ {$field}: string (" . strlen($value) . " chars)\n";
        } else {
            $output .= "✅ {$field}: {$type}\n";
        }
    }

    return $output;
}

/**
 * Export debug report
 */
function ajax_export_debug_report() {
    verify_ajax_nonce();

    $report = "=== SFPF Person Profile Debug Report ===\n";
    $report .= "Generated: " . current_time('Y-m-d H:i:s') . "\n\n";

    $report .= debug_homepage_schema() . "\n\n";
    $report .= debug_founder_data() . "\n\n";
    $report .= debug_injection_hook() . "\n\n";
    $report .= debug_professions() . "\n\n";
    $report .= debug_acf_fields() . "\n\n";

    wp_send_json_success(['report' => $report]);
}
add_action('wp_ajax_sfpf_export_debug_report', __NAMESPACE__ . '\\ajax_export_debug_report');
