<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\ContentTypes;

use Hexa\PluginCore\ContentTypes\ContentTypeRegistry;
use Hexa\PluginCore\FieldStructures\AcfFieldGroupRegistry;

defined( 'ABSPATH' ) || exit;

final class PersonContentTypes {
    private static ?ContentTypeRegistry $content_types = null;
    private static ?AcfFieldGroupRegistry $acf_groups = null;

    public static function content_types(): ContentTypeRegistry {
        self::load_definitions();
        if ( self::$content_types instanceof ContentTypeRegistry ) {
            return self::$content_types;
        }

        self::$content_types = new ContentTypeRegistry(
            [
                'option_name' => 'sfpf_content_type_settings', 'capability' => 'manage_options',
                'ajax_action' => 'sfpf_save_content_type', 'nonce_action' => 'sfpf_content_types',
                'nonce_field' => 'nonce', 'hook_priority' => 4,
            ]
        );
        self::$content_types->add(
            [
                'id' => 'book', 'owner' => 'SFPF Person Profile Integration',
                'description' => 'Books published by or associated with the primary Person profile.',
                'enabled_default' => false, 'legacy_enabled_option' => 'sfpf_enable_book_cpt',
                'post_type' => [
                    'key' => 'book', 'singular' => 'Book', 'plural' => 'Books', 'rewrite_slug' => 'book',
                    'args' => [
                        'public' => true, 'publicly_queryable' => true, 'show_ui' => true,
                        'show_in_menu' => true, 'show_in_nav_menus' => true, 'show_in_admin_bar' => true,
                        'show_in_rest' => true, 'menu_position' => 20, 'menu_icon' => 'dashicons-book-alt',
                        'capability_type' => 'post', 'hierarchical' => false,
                        'supports' => [ 'title', 'author', 'editor', 'thumbnail', 'custom-fields', 'excerpt' ],
                        'has_archive' => 'books', 'rewrite' => [ 'with_front' => false ],
                        'query_var' => true, 'delete_with_user' => false,
                    ],
                ],
                'field_groups' => [
                    [
                        'id' => 'book-details', 'label' => 'Book Details',
                        'description' => 'Schema controls, publishing metadata, purchase URLs, media, and social references.',
                        'group_key' => 'group_sfpf_book', 'enabled_default' => false,
                        'legacy_option' => 'sfpf_enable_book_acf',
                        'definition' => 'sfpf_person_website\\book_acf_field_group',
                        'fields' => [ 'Schema Markup', 'Featured', 'Subtitle', 'Description', 'Author Bio', 'Alternate Names', 'Featured Content', 'Retail URLs', 'Knowledge Graph ID', 'SameAs URLs', 'ISBN', 'Page Count', 'Publication Date', 'Edition', 'Format', 'Language', 'Genre' ],
                        'dependencies' => [ 'Advanced Custom Fields Pro' ],
                    ],
                ],
            ]
        );
        return self::$content_types;
    }

    public static function acf_groups(): AcfFieldGroupRegistry {
        self::load_definitions();
        if ( self::$acf_groups instanceof AcfFieldGroupRegistry ) {
            return self::$acf_groups;
        }

        self::$acf_groups = new AcfFieldGroupRegistry(
            [
                'option_name' => 'sfpf_acf_structure_settings', 'capability' => 'manage_options',
                'ajax_action' => 'sfpf_save_acf_structure', 'nonce_action' => 'sfpf_acf_structures',
                'nonce_field' => 'nonce', 'hook_priority' => 4,
            ]
        );
        self::$acf_groups
            ->add(
                [
                    'id' => 'person-user-profile', 'label' => 'Person User Profile Fields',
                    'description' => 'Canonical Person identity, biography, professions, education, media, FAQ, and schema fields attached to WordPress users.',
                    'group_key' => 'group_sfpf_user_schema_structures', 'legacy_option' => 'sfpf_enable_user_schema_acf',
                    'enabled_default' => false, 'definition' => 'sfpf_person_website\\user_schema_acf_field_group',
                    'location' => 'All WordPress user profile forms',
                    'fields' => [ 'Entity Type', 'Biography', 'Short Biography', 'Mission Statement', 'Professions', 'Education', 'Awards', 'Languages', 'Birth Details', 'Contact Details', 'SameAs URLs', 'Additional URLs', 'Knowledge Graph ID', 'Gallery', 'FAQ', 'Articles' ],
                    'dependencies' => [ 'Advanced Custom Fields Pro', 'HWS Base Tools primary entity (optional)' ],
                ]
            )
            ->add(
                [
                    'id' => 'homepage-schema', 'label' => 'Homepage Schema Fields',
                    'description' => 'Schema controls and generated schema preview attached to the configured WordPress front page.',
                    'group_key' => 'group_sfpf_homepage', 'legacy_option' => 'sfpf_enable_homepage_acf',
                    'enabled_default' => false, 'definition' => 'sfpf_person_website\\homepage_acf_field_group',
                    'location' => 'WordPress front page', 'fields' => [ 'Schema Type', 'Generated Schema', 'Schema Status' ],
                    'dependencies' => [ 'Advanced Custom Fields Pro' ],
                ]
            )
            ->add(
                [
                    'id' => 'legacy-organization-profile', 'label' => 'Legacy Organization Compatibility Fields',
                    'description' => 'Compatibility only for sites that enabled the former SFPF Organization fields before SMC became the canonical owner.',
                    'group_key' => 'group_sfpf_organization', 'legacy_option' => 'sfpf_enable_organization_acf',
                    'enabled_default' => false, 'definition' => 'sfpf_person_website\\organization_acf_field_group',
                    'available_when' => static fn(): bool => ! class_exists( '\\SMC\\OrganizationProfile\\Acf\\OrganizationFields' ),
                    'location' => 'Organization posts when SMC is unavailable',
                    'fields' => [ 'Organization identity, media, contact, people, and schema compatibility fields' ],
                    'dependencies' => [ 'HWS Base Tools Organization CPT', 'Advanced Custom Fields Pro' ],
                ]
            );
        return self::$acf_groups;
    }

    private static function load_definitions(): void {
        foreach ( [ 'register-acf-book.php', 'register-acf-user-schema.php', 'register-acf-homepage.php', 'register-acf-organization.php' ] as $file ) {
            require_once SFPF_PLUGIN_DIR . 'snippets/' . $file;
        }
    }
}
