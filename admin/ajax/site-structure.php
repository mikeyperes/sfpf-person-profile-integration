<?php

declare( strict_types=1 );

namespace sfpf_person_website;

/**
 * Shared Core site-structure AJAX registration.
 *
 * Callback names remain in the legacy namespace for template and hook compatibility.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register reusable critical page and navigation menu AJAX actions through Hexa core.
 */
function register_site_structure_ajax() {
    if (!class_exists('\\Hexa\\PluginCore\\SiteStructure\\SiteStructureAjaxController')) {
        return;
    }

    (new \Hexa\PluginCore\SiteStructure\SiteStructureAjaxController(sfpf_site_structure_manager(), [
        'capability' => 'manage_options',
        'nonce_action' => 'sfpf_ajax',
        'nonce_field' => 'nonce',
        'logger' => static function ($error) {
            if ($error instanceof \Throwable) {
                write_log('Site structure AJAX error: ' . $error->getMessage());
            }
        },
        'actions' => [
            'assign_page' => 'sfpf_assign_page',
            'create_page' => 'sfpf_create_page',
            'delete_page' => 'sfpf_delete_page',
            'create_navigation_menu' => 'sfpf_create_navigation_menu',
            'delete_navigation_menu' => 'sfpf_delete_navigation_menu',
            'attach_page_to_menu_item' => 'sfpf_attach_page_to_menu_item',
            'attach_menu_structure' => 'sfpf_attach_menu_structure',
        ],
    ]))->register();
}
register_site_structure_ajax();
