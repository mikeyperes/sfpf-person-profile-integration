<?php

declare( strict_types=1 );

namespace sfpf_person_website;

/**
 * Plugin lifecycle, CPT loading, ACF registration, and activation hooks.
 *
 * Callback names remain in the legacy namespace for template and hook compatibility.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Preserve saved settings while Organization ownership moves from HWS to SFPF.
 */
function migrate_shared_content_type_ownership(): void {
    $migration_version = '3.1.0';

    if ( $migration_version === get_option( 'sfpf_shared_content_type_ownership_migration', '' ) ) {
        return;
    }

    // Testimonial remains HWS-owned.
    if ( get_option( 'sfpf_enable_testimonial_cpt', false ) ) {
        update_option( 'enable_cpt_testimonial', 1, false );
    }

    $sfpf_settings = get_option( 'sfpf_content_type_settings', [] );
    $sfpf_settings = is_array( $sfpf_settings ) ? $sfpf_settings : [];

    if ( ! isset( $sfpf_settings['organization'] ) || ! is_array( $sfpf_settings['organization'] ) ) {
        $hws_settings = get_option( 'hws_content_type_settings', [] );
        $hws_settings = is_array( $hws_settings ) ? $hws_settings : [];
        $organization = is_array( $hws_settings['organization'] ?? null ) ? $hws_settings['organization'] : [];

        $standalone_acf = get_option( 'sfpf_acf_structure_settings', [] );
        $standalone_acf = is_array( $standalone_acf ) ? $standalone_acf : [];
        $legacy_acf = get_option( 'sfpf_enable_organization_acf', null );
        if ( null === $legacy_acf ) {
            $legacy_acf = get_option( 'smp_enable_acf_organization', false );
        }
        $acf_enabled = array_key_exists( 'legacy-organization-profile', $standalone_acf )
            ? (bool) $standalone_acf['legacy-organization-profile']
            : (bool) $legacy_acf;

        $sfpf_settings['organization'] = [
            'enabled' => array_key_exists( 'enabled', $organization )
                ? ( ! empty( $organization['enabled'] ) ? 1 : 0 )
                : ( get_option( 'smp_enable_cpt_organization', get_option( 'sfpf_enable_organization_cpt', false ) ) ? 1 : 0 ),
            'singular' => sanitize_text_field( (string) ( $organization['singular'] ?? 'Organization' ) ) ?: 'Organization',
            'plural' => sanitize_text_field( (string) ( $organization['plural'] ?? 'Organizations' ) ) ?: 'Organizations',
            'rewrite_slug' => sanitize_title( (string) ( $organization['rewrite_slug'] ?? 'organization' ) ) ?: 'organization',
            'field_groups' => [ 'organization-details' => $acf_enabled ? 1 : 0 ],
        ];

        update_option( 'sfpf_content_type_settings', $sfpf_settings, false );
    }

    update_option( 'sfpf_shared_content_type_ownership_migration', $migration_version, false );
}
add_action( 'plugins_loaded', __NAMESPACE__ . '\\migrate_shared_content_type_ownership', 5 );

// ============================================================================
// MAIN INIT - Hook to init priority 5
// ============================================================================
function init_plugin() {
    // Load schema files
    $schema_files = ['schema-templates.php', 'schema-builder.php', 'schema-manager.php', 'schema-injector.php'];
    foreach ($schema_files as $file) {
        $path = SFPF_PLUGIN_DIR . 'schema/' . $file;
        if (file_exists($path)) require_once $path;
    }

    if (function_exists(__NAMESPACE__ . '\\enable_schema_on_save')) {
        enable_schema_on_save();
    }

    $doing_ajax = wp_doing_ajax();
    $ajax_action = $doing_ajax && isset($_REQUEST['action']) && is_scalar($_REQUEST['action'])
        ? sanitize_key(wp_unslash((string) $_REQUEST['action']))
        : '';

    // Register the dashboard only where its menu or lazy-tab endpoint is needed.
    if (is_admin() && (!$doing_ajax || 'sfpf_load_dashboard_tab' === $ajax_action)) {
        require_once SFPF_PLUGIN_DIR . 'admin/settings-dashboard.php';
    }

    // Legacy handlers are selected by action so unrelated AJAX requests stay lean.
    if ($doing_ajax) {
        require_once SFPF_PLUGIN_DIR . 'admin/ajax-handlers.php';
    }
}
add_action('init', __NAMESPACE__ . '\\init_plugin', 5);

/**
 * Plugin activation
 */
function activate_plugin() {
    // Set default options — add_option only writes if key doesn't exist yet
    add_option('sfpf_homepage_schema_type', 'person');
    add_option('sfpf_biography_schema_type', 'profile_page_only');
    add_option('sfpf_rankmath_disable_biography', false);
    add_option(SFPF_HIDE_EMPTY_ELEMENTOR_SOCIAL_ICONS_OPTION, 1);
    add_option(SFPF_ELEMENTOR_DYNAMIC_VISIBILITY_OPTION, 1);

    // Migration: fix sites that had the old 'none' default from previous activation bug
    $current_hp = get_option('sfpf_homepage_schema_type');
    if ($current_hp === 'none') {
        // Only auto-fix if the user hasn't explicitly saved a schema type yet
        // (check if the option was set by old activation code vs. user choice)
        $hp_was_explicitly_saved = get_option('sfpf_homepage_schema_explicitly_saved', false);
        if (!$hp_was_explicitly_saved) {
            update_option('sfpf_homepage_schema_type', 'person');
        }
    }

    // Flush rewrite rules
    flush_rewrite_rules();

    // Log activation
    if (function_exists(__NAMESPACE__ . '\\write_log')) {
        write_log('Plugin activated');
    }
}
register_activation_hook(SFPF_PLUGIN_FILE, __NAMESPACE__ . '\\activate_plugin');

/**
 * Plugin deactivation
 */
function deactivate_plugin() {
    // Flush rewrite rules
    flush_rewrite_rules();

    // Log deactivation
    if (function_exists(__NAMESPACE__ . '\\write_log')) {
        write_log('Plugin deactivated');
    }
}
register_deactivation_hook(SFPF_PLUGIN_FILE, __NAMESPACE__ . '\\deactivate_plugin');
