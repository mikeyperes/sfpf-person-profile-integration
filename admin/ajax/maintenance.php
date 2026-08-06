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
    delete_option('sfpf_activity_log');
    write_log("Activity log cleared");

    wp_send_json_success();
}
