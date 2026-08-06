<?php

declare( strict_types=1 );

namespace sfpf_person_website;

use SFPF\PersonProfile\Dependencies\PluginRequirements;

/**
 * Plugin action links, requirements, and one-time profile migrations.
 *
 * Callback names remain in the legacy namespace for template and hook compatibility.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Add settings link on plugins page
 */
function add_settings_link($links) {
    $settings_link = '<a href="' . admin_url('options-general.php?page=sfpf-person-profile') . '">Settings</a>';
    array_unshift($links, $settings_link);
    return $links;
}
add_filter('plugin_action_links_' . SFPF_PLUGIN_BASENAME, __NAMESPACE__ . '\\add_settings_link');

/**
 * Check for required plugins
 */
function check_requirements() {
    PluginRequirements::register();
}
add_action('admin_init', __NAMESPACE__ . '\\check_requirements');

/**
 * Migrate articles field from textarea (string) to repeater (array)
 * Runs once per user if old format detected
 */
function migrate_articles_textarea_to_repeater() {
    if (get_option('sfpf_articles_migration_done', false)) {
        return;
    }

    $founder_id = get_founder_user_id();
    if (!$founder_id) {
        return;
    }

    $articles = get_field('articles', 'user_' . $founder_id);

    // Only migrate if it's a string (old textarea format)
    if (!is_string($articles) || empty(trim($articles))) {
        update_option('sfpf_articles_migration_done', true);
        return;
    }

    $urls = array_filter(array_map('trim', explode("\n", $articles)));
    if (empty($urls)) {
        update_option('sfpf_articles_migration_done', true);
        return;
    }

    $repeater = [];
    foreach ($urls as $url) {
        if (!filter_var($url, FILTER_VALIDATE_URL)) continue;
        $parsed = wp_parse_url($url);
        $source = preg_replace('/^www\./', '', $parsed['host'] ?? '');
        $repeater[] = [
            'title' => '',
            'source' => $source,
            'url' => $url,
        ];
    }

    if (!empty($repeater)) {
        update_field('articles', $repeater, 'user_' . $founder_id);
        write_log("Migrated " . count($repeater) . " articles from textarea to repeater for user {$founder_id}");
    }

    update_option('sfpf_articles_migration_done', true);
}
add_action('admin_init', __NAMESPACE__ . '\\migrate_articles_textarea_to_repeater', 20);
