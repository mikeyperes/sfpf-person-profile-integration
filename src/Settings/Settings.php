<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Settings;

defined( 'ABSPATH' ) || exit;

/**
 * Every SFPF option in one place: its name, default and reader. Option names
 * are the historical ones, because live sites and Code's audit tool read them.
 */
final class Settings {
    public const HOMEPAGE_SCHEMA_MODE       = 'sfpf_homepage_schema_type';
    public const HOMEPAGE_SCHEMA_CHOSEN     = 'sfpf_homepage_schema_explicitly_saved';
    public const BIOGRAPHY_SCHEMA_MODE      = 'sfpf_biography_schema_type';
    public const PRIMARY_BOOK               = 'sfpf_primary_book';
    public const PRIMARY_ORGANIZATION       = 'sfpf_primary_organization';
    public const FAQ_SETS                   = 'sfpf_faq_sets';
    public const PRIMARY_FAQ_SET            = 'sfpf_primary_faq_set';
    public const LOOP_TEMPLATES             = 'sfpf_elementor_loop_assignments';
    public const HIDE_EMPTY_SOCIAL_ICONS    = 'sfpf_hide_empty_elementor_social_icons';
    public const ELEMENTOR_CONDITIONS       = 'sfpf_elementor_dynamic_visibility';
    public const BREADCRUMB_HIDE_FRONT_PAGE = 'sfpf_breadcrumb_hide_frontpage';
    public const BREADCRUMB_EXCLUDED_PAGES  = 'sfpf_breadcrumb_excluded_pages';
    public const BREADCRUMB_EXCLUDED_TYPES  = 'sfpf_breadcrumb_excluded_cpts';
    public const AUTHOR_ARCHIVE_PROFILE     = 'sfpf_author_archive_profile';
    public const PAGE_TEMPLATE_PREFIX       = 'sfpf_template_';
    public const PAGE_ASSIGNMENT_PREFIX     = 'sfpf_page_';
    public const CONTENT_TYPES              = 'sfpf_content_type_settings';
    public const FIELD_GROUPS               = 'sfpf_acf_structure_settings';
    public const MIGRATIONS                 = 'sfpf_migrations';

    public static function flag( string $option, bool $default ): bool {
        $value = get_option( $option, $default ? '1' : '0' );
        return ! in_array( strtolower( (string) $value ), [ '0', 'false', 'off', 'no', '' ], true );
    }

    public static function int( string $option, int $default = 0 ): int {
        return absint( get_option( $option, $default ) );
    }

    /** @return list<int> */
    public static function ids( string $option ): array {
        return array_values( array_filter( array_map( 'absint', (array) get_option( $option, [] ) ) ) );
    }

    /** @return list<string> */
    public static function keys( string $option ): array {
        return array_values( array_filter( array_map( 'sanitize_key', (array) get_option( $option, [] ) ) ) );
    }

    /** @return array<string,mixed> */
    public static function map( string $option ): array {
        $value = get_option( $option, [] );
        return is_array( $value ) ? $value : [];
    }

    /** The WordPress page assigned to a person page key, when it still exists. */
    public static function assigned_page( string $key ): int {
        $id = self::int( self::PAGE_ASSIGNMENT_PREFIX . sanitize_key( $key ) );
        return $id > 0 && 'page' === get_post_type( $id ) && 'trash' !== get_post_status( $id ) ? $id : 0;
    }
}
