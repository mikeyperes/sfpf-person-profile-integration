<?php

declare( strict_types=1 );

namespace sfpf_person_website;

/**
 * FAQ set persistence action.
 *
 * Callback names remain in the legacy namespace for template and hook compatibility.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Save FAQ Sets
 */
function ajax_save_faq_sets() {
    $faq_sets_json = stripslashes($_POST["faq_sets"] ?? "[]");
    $faq_sets = json_decode($faq_sets_json, true);

    if (!is_array($faq_sets)) {
        $faq_sets = [];
    }

    $sanitized_sets = (new \Hexa\PluginCore\FaqSets\FaqSetManager())->sanitizeSets($faq_sets);

    update_option("sfpf_faq_sets", $sanitized_sets);

    $inject_schema = !empty($_POST["inject_schema"]);
    update_option("sfpf_inject_faq_schema", $inject_schema);

    $primary_faq = sanitize_key($_POST["primary_faq_set"] ?? "");
    update_option("sfpf_primary_faq_set", $primary_faq);

    write_log("FAQ sets saved through Hexa Core FaqSets: " . count($sanitized_sets) . " sets");

    wp_send_json_success(["count" => count($sanitized_sets)]);
}
