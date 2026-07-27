<?php
namespace sfpf_person_website;

/**
 * Schema Injector
 * 
 * Injects schema markup into the page head via wp_head hook.
 * 
 * @package sfpf_person_website
 * @since 1.0.0
 */

defined('ABSPATH') || exit;

/**
 * Enable schema injection into page head
 * 
 * Called when the snippet is activated.
 */
function enable_schema_injection() {
    add_action('wp_head', __NAMESPACE__ . '\\inject_schema_markup', 1);
}

/**
 * Inject schema markup into page head
 * 
 * Checks the current page type and injects appropriate schema.
 */
function inject_schema_markup() {
    if (class_exists('\\SFPF\\PersonProfile\\Schema\\SchemaProvider')) {
        output_schema_script(\SFPF\PersonProfile\Schema\SchemaProvider::current());
    }
}
// build_homepage_schema_for_injection() moved to schema-builder.php

/**
 * Output schema as JSON-LD script tag
 * 
 * @param string $schema JSON schema string
 */
function output_schema_script($schema) {
    if (is_string($schema)) {
        $schema = json_decode($schema, true);
    }
    if (!is_array($schema) || empty($schema) || !class_exists('\\Hexa\\PluginCore\\SchemaTools\\SchemaDocumentRenderer')) {
        return;
    }
    echo (new \Hexa\PluginCore\SchemaTools\SchemaDocumentRenderer())->script($schema, 'sfpf-person-website-schema');
}

/**
 * Get schema for display in admin
 * 
 * @param int $post_id Post ID
 * @return array Schema info
 */
function get_schema_for_display($post_id) {
    $schema = function_exists(__NAMESPACE__ . '\\get_post_schema')
        ? get_post_schema($post_id)
        : get_post_meta($post_id, 'schema_markup', true);
    
    $decoded = is_array($schema) ? $schema : json_decode((string) $schema, true);
    $normalized = is_array($decoded) && class_exists('\\Hexa\\PluginCore\\SchemaTools\\SchemaDocumentRenderer')
        ? (new \Hexa\PluginCore\SchemaTools\SchemaDocumentRenderer())->json($decoded)
        : '';
    return [
        'raw' => $normalized,
        'formatted' => $normalized ? format_json_display($normalized) : '<em>No schema generated</em>',
        'valid' => '' !== $normalized,
        'validator_url' => get_schema_validator_url(get_permalink($post_id)),
        'google_url' => get_google_rich_results_url(get_permalink($post_id)),
    ];
}
