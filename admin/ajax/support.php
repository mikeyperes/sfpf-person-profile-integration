<?php

declare( strict_types=1 );

namespace sfpf_person_website;

use Hexa\PluginCore\WpAdminAjax\AjaxGuard;

/**
 * Shared nonce, capability, managed-page, and FAQ schema helpers.
 *
 * Callback names remain in the legacy namespace for template and hook compatibility.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Verify AJAX nonce
 */
function verify_ajax_nonce() {
    AjaxGuard::require_capability_or_error( 'manage_options', 'Permission denied' );
    AjaxGuard::require_nonce_or_error( 'sfpf_ajax', 'nonce', 'Invalid security token' );
}

/**
 * Mark a page as SFPF-managed so destructive actions only touch plugin-owned pages.
 *
 * @param int $page_id Page ID.
 * @param string $page_key Critical page key.
 * @return void
 */
function mark_sfpf_managed_page($page_id, $page_key) {
    sfpf_site_structure_manager()->mark_managed_page((int) $page_id, (string) $page_key);
}

/**
 * Check whether a page was created and owned by SFPF.
 *
 * @param int $page_id Page ID.
 * @param string $page_key Optional expected page key.
 * @return bool
 */
function is_sfpf_managed_page($page_id, $page_key = '') {
    return sfpf_site_structure_manager()->is_managed_page((int) $page_id, (string) $page_key);
}

/**
 * Build a standard page payload for AJAX responses.
 *
 * @param int $page_id Page ID.
 * @param bool $existing Whether the page already existed.
 * @param string $message Optional message.
 * @return array
 */
function get_page_ajax_payload($page_id, $existing = false, $message = '') {
    return sfpf_site_structure_manager()->page_payload((int) $page_id, (bool) $existing, (string) $message);
}

/**
 * Extract FAQPage nodes from a decoded schema block.
 *
 * @param array $schema Schema block.
 * @return array
 */
function sfpf_get_faq_nodes($schema) {
    if (!is_array($schema)) {
        return [];
    }

    $nodes = [];
    $top_level_type = $schema['@type'] ?? null;
    $top_level_types = is_array($top_level_type) ? $top_level_type : [$top_level_type];
    if (in_array('FAQPage', array_filter($top_level_types), true)) {
        $nodes[] = $schema;
    }

    if (!empty($schema['@graph']) && is_array($schema['@graph'])) {
        foreach ($schema['@graph'] as $node) {
            if (!is_array($node)) {
                continue;
            }

            $node_type = $node['@type'] ?? null;
            $node_types = is_array($node_type) ? $node_type : [$node_type];
            if (in_array('FAQPage', array_filter($node_types), true)) {
                $nodes[] = $node;
            }
        }
    }

    return $nodes;
}

/**
 * Find invalid FAQ schema details inside decoded schema blocks.
 *
 * @param array $blocks Schema blocks.
 * @return array
 */
function sfpf_get_faq_schema_issues($blocks) {
    $issues = [];

    foreach ($blocks as $block) {
        foreach (sfpf_get_faq_nodes($block) as $faq_node) {
            $questions = $faq_node['mainEntity'] ?? [];

            if (empty($questions)) {
                $issues[] = 'FAQPage is missing mainEntity';
                continue;
            }

            if (isset($questions['@type']) || isset($questions['name'])) {
                $questions = [$questions];
            }

            foreach ($questions as $index => $question) {
                if (!is_array($question)) {
                    $issues[] = 'FAQ question ' . ($index + 1) . ' is malformed';
                    continue;
                }

                $question_name = trim(wp_strip_all_tags((string) ($question['name'] ?? '')));
                if ($question_name === '' || preg_match('/^Item #\d+$/i', $question_name)) {
                    $issues[] = 'FAQ question ' . ($index + 1) . ' uses a placeholder or empty name';
                }

                $accepted_answer = $question['acceptedAnswer'] ?? [];
                $answer_text = is_array($accepted_answer) ? ($accepted_answer['text'] ?? '') : '';
                $answer_text = trim(wp_strip_all_tags((string) $answer_text));

                if ($answer_text === '') {
                    $issues[] = 'FAQ question ' . ($index + 1) . ' has an empty acceptedAnswer';
                }
            }
        }
    }

    return array_values(array_unique($issues));
}
