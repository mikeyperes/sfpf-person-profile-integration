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
        self::$content_types
            ->add(
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
            )
            ->add(
                [
                    'id' => 'press-release', 'owner' => 'SFPF Person Profile Integration',
                    'description' => 'Press releases published by or about the primary Person profile.',
                    'enabled_default' => false, 'legacy_enabled_option' => 'sfpf_enable_press_release_cpt',
                    'post_type' => [
                        'key' => 'press-release', 'singular' => 'Press Release', 'plural' => 'Press Releases', 'rewrite_slug' => 'press-release',
                        'args' => self::profile_content_type_args( 'dashicons-media-document', 21 ),
                    ],
                    'field_groups' => [
                        [
                            'id' => 'press-release-fields', 'label' => 'Press Release Fields',
                            'description' => 'Source, original URL, release date, and featured controls for press releases.',
                            'group_key' => 'group_sfpf_press_release', 'enabled_default' => false,
                            'legacy_option' => 'sfpf_enable_press_release_acf',
                            'definition' => 'sfpf_person_website\\press_release_acf_field_group',
                            'fields' => [ 'Source Name', 'Original URL', 'Release Date', 'Featured' ],
                            'dependencies' => [ 'Advanced Custom Fields Pro' ],
                        ],
                    ],
                ]
            )
            ->add(
                [
                    'id' => 'interview', 'owner' => 'SFPF Person Profile Integration',
                    'description' => 'Interviews featuring or hosted by the primary Person profile.',
                    'enabled_default' => false, 'legacy_enabled_option' => 'sfpf_enable_interview_cpt',
                    'post_type' => [
                        'key' => 'interview', 'singular' => 'Interview', 'plural' => 'Interviews', 'rewrite_slug' => 'interview',
                        'args' => self::profile_content_type_args( 'dashicons-microphone', 22 ),
                    ],
                    'field_groups' => [
                        [
                            'id' => 'interview-fields', 'label' => 'Interview Fields',
                            'description' => 'Podcast, guest, host, source-link, and press details for interviews.',
                            'group_key' => 'group_64b623fb36208', 'enabled_default' => false,
                            'legacy_option' => 'sfpf_enable_interview_acf',
                            'definition' => 'sfpf_person_website\\interview_acf_field_group',
                            'fields' => [ 'Podcast Name', 'Guest Name', 'Host Name', 'Primary URL', 'Additional Links', 'Press' ],
                            'dependencies' => [ 'Advanced Custom Fields Pro' ],
                        ],
                    ],
                ]
            )
            ->add(
                [
                    'id' => 'contributing-profile', 'owner' => 'SFPF Person Profile Integration',
                    'description' => 'External publications and platforms where the primary Person profile contributes.',
                    'enabled_default' => false, 'legacy_enabled_option' => 'sfpf_enable_contributing_profile_cpt',
                    'post_type' => [
                        'key' => 'contributing-profile', 'singular' => 'Contributing Profile', 'plural' => 'Contributing Profiles', 'rewrite_slug' => 'contributing-profile',
                        'args' => self::profile_content_type_args( 'dashicons-id-alt', 23 ),
                    ],
                    'field_groups' => [
                        [
                            'id' => 'contributing-profile-fields', 'label' => 'Contributing Profile Fields',
                            'description' => 'Destination URL, alternate logo, and featured-profile controls.',
                            'group_key' => 'group_64bb58d10c008', 'enabled_default' => false,
                            'legacy_option' => 'sfpf_enable_contributing_profile_acf',
                            'definition' => 'sfpf_person_website\\contributing_profile_acf_field_group',
                            'fields' => [ 'URL', 'Secondary Logo', 'Featured Item', 'Primary Featured Item' ],
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
                    'fields' => [ 'Entity Type', 'Biography', 'Short Biography', 'Mission Statement', 'Professions', 'Education', 'Awards', 'Languages', 'Birth Details', 'Contact Details', 'SameAs URLs', 'Additional URLs', 'Knowledge Graph ID', 'Wikimedia Commons URLs (Photos)', 'Gallery', 'FAQ', 'Articles' ],
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
        foreach ( [ 'register-acf-book.php', 'register-acf-profile-content-types.php', 'register-acf-user-schema.php', 'register-acf-homepage.php', 'register-acf-organization.php' ] as $file ) {
            require_once SFPF_PLUGIN_DIR . 'snippets/' . $file;
        }
    }

    /** @return array<string,mixed> */
    private static function profile_content_type_args( string $icon, int $position ): array {
        return [
            'public' => true, 'publicly_queryable' => true, 'show_ui' => true,
            'show_in_menu' => true, 'show_in_nav_menus' => true, 'show_in_admin_bar' => true,
            'show_in_rest' => true, 'menu_position' => $position, 'menu_icon' => $icon,
            'capability_type' => 'post', 'hierarchical' => false,
            'supports' => [ 'title', 'editor', 'thumbnail', 'custom-fields' ],
            'has_archive' => false, 'rewrite' => [ 'with_front' => true, 'feeds' => false ],
            'query_var' => true, 'can_export' => true, 'delete_with_user' => false,
        ];
    }
}
