<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\ContentTypes\Organization;

use HWS\BaseTools\ContentTypes\Organization\OrganizationContentType as BaseToolsOrganization;

defined( 'ABSPATH' ) || exit;

/**
 * The person profile’s Organization fields and legacy registration fallback. SMC Organization Profile builds Organization schema.
 * Off until a site turns it on; its fields are on with it.
 */
final class OrganizationContentType {
    public const ID        = 'organization';
    public const POST_TYPE = 'organization';

    /** @return array<string,mixed> */
    public static function definition(): array {
        // The new Base Tools owns Organization even when its local switch is off.
        // Retain the previous registrar on sites that have not received that provider.
        $base_tools_owns_type = defined( BaseToolsOrganization::class . '::SETTINGS_OPTION' );
        return [
            'id'              => self::ID,
            'owner'           => $base_tools_owns_type ? 'HWS Base Tools (post type) / SFPF (fields)' : 'SFPF Person Profile Integration',
            'registration_mode' => $base_tools_owns_type ? 'external' : 'owned',
            'description'     => 'Organizations the person founded or leads. SMC Organization Profile builds their schema.',
            'enabled_default' => false,
            'legacy_enabled_option' => 'smp_enable_cpt_organization',
            'post_type'       => [
                'key'          => self::POST_TYPE,
                'singular'     => 'Organization',
                'plural'       => 'Organizations',
                'rewrite_slug' => 'organization',
                'args'         => [
                    'public' => true, 'publicly_queryable' => true, 'show_ui' => true,
                    'show_in_menu' => true, 'show_in_nav_menus' => true, 'show_in_admin_bar' => true,
                    'show_in_rest' => true, 'menu_position' => 21, 'menu_icon' => 'dashicons-building',
                    'capability_type' => 'post', 'hierarchical' => false,
                    'supports' => [ 'title', 'author', 'editor', 'excerpt', 'thumbnail', 'revisions', 'page-attributes', 'custom-fields' ],
                    'has_archive' => 'organizations', 'rewrite' => [ 'with_front' => false ],
                    'query_var' => true, 'taxonomies' => [ 'category' ], 'delete_with_user' => false,
                ],
            ],
            'field_groups'    => [
                [
                    'id'              => 'organization-details',
                    'label'           => 'Organization Details',
                    'description'     => 'Organization identity, media, contact, people, and schema fields.',
                    'group_key'       => 'group_sfpf_organization',
                    'enabled_default' => true,
                    'legacy_option'   => 'sfpf_enable_organization_acf',
                    'definition'      => [ OrganizationFields::class, 'group' ],
                    'fields'          => [ 'Organization identity', 'Logo and gallery', 'Contact details', 'Founders and leadership', 'Schema data' ],
                    'dependencies'    => [],
                ],
            ],
        ];
    }
}
