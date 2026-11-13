<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Admin\Actions;

use Hexa\PluginCore\CoreContracts\ModuleInterface;
use Hexa\PluginCore\WpAdminAjax\AjaxActionRegistry;
use Hexa\PluginCore\WpAdminAjax\AjaxRequest;
use SFPF\PersonProfile\Admin\AdminUi;
use SFPF\PersonProfile\Faq\FaqSets;
use SFPF\PersonProfile\PluginRuntime\PluginMetadata;
use SFPF\PersonProfile\Settings\Settings;
use SFPF\PersonProfile\Support\ActivityLog;

defined( 'ABSPATH' ) || exit;

/** Saving the settings forms: general settings, breadcrumbs, loop templates, FAQ sets, and clearing the log. */
final class SettingsActions implements ModuleInterface {
    public const SETTINGS    = 'sfpf_save_settings';
    public const BREADCRUMBS = 'sfpf_save_breadcrumb_settings';
    public const LOOPS       = 'sfpf_save_elementor_loops';
    public const FAQ_SETS    = 'sfpf_save_faq_sets';
    public const CLEAR_LOG   = 'sfpf_clear_log';

    public function register(): void {
        AjaxActionRegistry::create( [ 'capability' => PluginMetadata::ADMIN_CAPABILITY, 'nonce_action' => AdminUi::NONCE ] )->register(
            [
                self::SETTINGS    => [ $this, 'settings' ],
                self::BREADCRUMBS => [ $this, 'breadcrumbs' ],
                self::LOOPS       => [ $this, 'loops' ],
                self::FAQ_SETS    => [ $this, 'faq_sets' ],
                self::CLEAR_LOG   => [ $this, 'clear_log' ],
            ]
        );
    }

    /** @return array{message:string} */
    public function settings( AjaxRequest $request ): array {
        if ( $request->has( 'primary_book', 'post' ) ) {
            update_option( Settings::PRIMARY_BOOK, $request->int( 'primary_book', 0, 'post' ), false );
        }
        foreach ( [ 'elementor_conditions' => Settings::ELEMENTOR_CONDITIONS, 'hide_empty_social_icons' => Settings::HIDE_EMPTY_SOCIAL_ICONS, 'author_archive_profile' => Settings::AUTHOR_ARCHIVE_PROFILE ] as $field => $option ) {
            update_option( $option, $request->bool( $field, false, 'post' ) ? '1' : '0', false );
        }
        ActivityLog::add( 'Settings saved.' );
        return [ 'message' => 'Saved.' ];
    }

    /** @return array{message:string} */
    public function breadcrumbs( AjaxRequest $request ): array {
        update_option( Settings::BREADCRUMB_HIDE_FRONT_PAGE, $request->bool( 'front_page', false, 'post' ) ? '1' : '0', false );
        update_option( Settings::BREADCRUMB_EXCLUDED_PAGES, array_values( array_filter( array_map( 'absint', (array) $request->raw( 'pages', [], 'post' ) ) ) ), false );
        update_option( Settings::BREADCRUMB_EXCLUDED_TYPES, array_values( array_filter( $request->key_array( 'types', 'post' ), 'post_type_exists' ) ), false );
        ActivityLog::add( 'Breadcrumb settings saved.' );
        return [ 'message' => 'Saved.' ];
    }

    /** @return array{message:string} */
    public function loops( AjaxRequest $request ): array {
        $loops = [];
        foreach ( (array) $request->raw( 'loop', [], 'post' ) as $type => $template ) {
            $type     = sanitize_key( (string) $type );
            $template = absint( $template );
            if ( post_type_exists( $type ) && ( 0 === $template || 'elementor_library' === get_post_type( $template ) ) ) {
                $loops[ $type ] = $template;
            }
        }
        update_option( Settings::LOOP_TEMPLATES, $loops + Settings::map( Settings::LOOP_TEMPLATES ), false );
        ActivityLog::add( 'Loop templates saved.' );
        return [ 'message' => 'Saved.' ];
    }

    /** @return array{message:string} */
    public function faq_sets( AjaxRequest $request ): array {
        $sets = [];
        foreach ( (array) $request->raw( 'sets', [], 'post' ) as $set ) {
            if ( ! is_array( $set ) || '' === trim( (string) ( $set['name'] ?? '' ) ) ) {
                continue;
            }
            $items = array_values( array_filter( (array) ( $set['items'] ?? [] ), static fn( mixed $item ): bool => is_array( $item ) && '' !== trim( (string) ( $item['question'] ?? '' ) ) && '' !== trim( (string) ( $item['answer'] ?? '' ) ) ) );
            $sets[] = [ 'name' => (string) $set['name'], 'slug' => sanitize_title( '' !== trim( (string) ( $set['slug'] ?? '' ) ) ? (string) $set['slug'] : (string) $set['name'] ), 'items' => $items ];
        }
        FaqSets::save( $sets, $request->text( 'primary', '', 'post' ) );
        ActivityLog::add( 'FAQ sets saved (' . count( $sets ) . ').' );
        return [ 'message' => 'Saved ' . count( $sets ) . ' FAQ set(s). Reload to see their shortcodes.' ];
    }

    /** @return array{message:string} */
    public function clear_log(): array {
        ActivityLog::clear();
        return [ 'message' => 'Cleared.' ];
    }
}
