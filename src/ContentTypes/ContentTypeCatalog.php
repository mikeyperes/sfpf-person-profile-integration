<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\ContentTypes;

use Hexa\PluginCore\ContentTypes\ContentTypeRegistry;
use Hexa\PluginCore\FieldStructures\AcfFieldGroupRegistry;
use SFPF\PersonProfile\ContentTypes\Award\AwardContentType;
use SFPF\PersonProfile\ContentTypes\Book\BookContentType;
use SFPF\PersonProfile\ContentTypes\ContributingProfile\ContributingProfileContentType;
use SFPF\PersonProfile\ContentTypes\Exhibition\ExhibitionContentType;
use SFPF\PersonProfile\ContentTypes\Interview\InterviewContentType;
use SFPF\PersonProfile\ContentTypes\Organization\OrganizationContentType;
use SFPF\PersonProfile\ContentTypes\PressRelease\PressReleaseContentType;
use SFPF\PersonProfile\ContentTypes\Quote\QuoteContentType;
use SFPF\PersonProfile\Fields\PersonProfileFields;
use SFPF\PersonProfile\PluginRuntime\PluginMetadata;
use SFPF\PersonProfile\Settings\Settings;

defined( 'ABSPATH' ) || exit;

/**
 * Every content type the person website can use, and the person's own field
 * group, registered through Hexa WP Core. Each content type lives in its own
 * folder; this class only lists them.
 */
final class ContentTypeCatalog {
    /** @var list<class-string> */
    public const TYPES = [
        OrganizationContentType::class,
        BookContentType::class,
        PressReleaseContentType::class,
        InterviewContentType::class,
        ContributingProfileContentType::class,
        QuoteContentType::class,
        AwardContentType::class,
        ExhibitionContentType::class,
    ];

    private static ?ContentTypeRegistry $content_types = null;
    private static ?AcfFieldGroupRegistry $field_groups = null;

    public static function content_types(): ContentTypeRegistry {
        if ( null === self::$content_types ) {
            self::$content_types = new ContentTypeRegistry( self::registry_config( Settings::CONTENT_TYPES, 'sfpf_save_content_type', 'sfpf_content_types' ) );
            foreach ( self::TYPES as $type ) {
                self::$content_types->add( $type::definition() );
            }
        }
        return self::$content_types;
    }

    /**
     * A display-only registry with some of the content types, for a tab that
     * shows one of them. Saving still goes through the main registry.
     *
     * @param list<string> $only   IDs to keep (empty keeps all).
     * @param list<string> $except IDs to leave out.
     */
    public static function view( array $only = [], array $except = [] ): ContentTypeRegistry {
        $view = new ContentTypeRegistry( self::registry_config( Settings::CONTENT_TYPES, 'sfpf_save_content_type', 'sfpf_content_types' ) );
        foreach ( self::TYPES as $type ) {
            $id = $type::ID;
            if ( ( [] !== $only && ! in_array( $id, $only, true ) ) || in_array( $id, $except, true ) ) {
                continue;
            }
            $view->add( $type::definition() );
        }
        return $view;
    }

    /** The person's own fields on their WordPress user, on from the start. */
    public static function field_groups(): AcfFieldGroupRegistry {
        if ( null === self::$field_groups ) {
            self::$field_groups = new AcfFieldGroupRegistry( self::registry_config( Settings::FIELD_GROUPS, 'sfpf_save_acf_structure', 'sfpf_acf_structures' ) );
            self::$field_groups->add(
                [
                    'id'              => 'person-user-profile',
                    'label'           => 'Person Profile Fields',
                    'description'     => 'The person\'s identity, biography, professions, education, profile links, photos, Knowledge Graph ID and FAQ, on their WordPress user.',
                    'group_key'       => PersonProfileFields::GROUP_KEY,
                    'legacy_option'   => 'sfpf_enable_user_schema_acf',
                    'enabled_default' => true,
                    'definition'      => [ PersonProfileFields::class, 'group' ],
                    'location'        => 'Every WordPress user profile',
                    'fields'          => [ 'Entity Type', 'Biography', 'Short Biography', 'Mission Statement', 'Professions', 'Education', 'Awards', 'Languages', 'Birth Details', 'Contact Details', 'Profile Links', 'Articles', 'Additional URLs', 'Knowledge Graph ID', 'Knowledge Graph Images', 'Wikimedia Photos', 'FAQ' ],
                    'dependencies'    => [ 'HWS Base Tools primary entity' ],
                ]
            );
        }
        return self::$field_groups;
    }

    public static function enabled( string $id ): bool {
        return post_type_exists( $id );
    }

    /**
     * Post type arguments shared by the profile content types that have no
     * list page of their own.
     *
     * @return array<string,mixed>
     */
    public static function profile_args( string $icon, int $position ): array {
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

    /** @return array<string,mixed> */
    private static function registry_config( string $option, string $ajax_action, string $nonce_action ): array {
        return [
            'option_name'   => $option,
            'capability'    => PluginMetadata::ADMIN_CAPABILITY,
            'ajax_action'   => $ajax_action,
            'nonce_action'  => $nonce_action,
            'nonce_field'   => 'nonce',
            'hook_priority' => 4,
        ];
    }
}
