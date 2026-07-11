<?php

declare( strict_types=1 );

namespace sfpf_person_website;

/**
 * Activity-log maintenance action.
 *
 * Callback names remain in the legacy namespace for template and hook compatibility.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Clear log
 */
function ajax_clear_log() {
    verify_ajax_nonce();
    
    delete_option('sfpf_activity_log');
    write_log("Activity log cleared");
    
    wp_send_json_success();
}
add_action('wp_ajax_sfpf_clear_log', __NAMESPACE__ . '\\ajax_clear_log');
