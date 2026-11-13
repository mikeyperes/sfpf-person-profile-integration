<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Admin\Actions;

use Hexa\PluginCore\CoreContracts\ModuleInterface;
use Hexa\PluginCore\SiteStructure\SiteStructureAjaxController;
use Hexa\PluginCore\WpAdminAjax\AjaxActionRegistry;
use Hexa\PluginCore\WpAdminAjax\AjaxRequest;
use SFPF\PersonProfile\Admin\AdminUi;
use SFPF\PersonProfile\Pages\ProfessionPages;
use SFPF\PersonProfile\Pages\SiteStructure;
use SFPF\PersonProfile\PluginRuntime\PluginMetadata;
use SFPF\PersonProfile\Support\ActivityLog;

defined( 'ABSPATH' ) || exit;

/** The Pages & Menus actions: Core's page manager actions and the profession pages. */
final class PagesActions implements ModuleInterface {
    public const NONCE              = AdminUi::NONCE;
    public const CREATE_PROFESSION  = 'sfpf_create_profession_page';
    public const DELETE_PROFESSION  = 'sfpf_delete_profession_page';

    /** @return array<string,string> Core page manager action => this plugin's AJAX action name. */
    public static function site_structure_actions(): array {
        $actions = [];
        foreach ( [ 'assign_page', 'create_page', 'delete_page', 'create_navigation_menu', 'delete_navigation_menu', 'create_menu_item', 'attach_page_to_menu_item', 'attach_menu_structure', 'menu_inventory', 'save_template', 'apply_template', 'page_details', 'page_workspace', 'update_page_slug', 'toggle_page' ] as $action ) {
            $actions[ $action ] = 'sfpf_' . $action;
        }
        return $actions;
    }

    public function register(): void {
        ( new SiteStructureAjaxController(
            SiteStructure::manager(),
            [
                'capability'   => PluginMetadata::ADMIN_CAPABILITY,
                'nonce_action' => self::NONCE,
                'nonce_field'  => 'nonce',
                'logger'       => static fn( \Throwable $error ) => ActivityLog::add( 'Pages: ' . $error->getMessage(), 'error' ),
                'actions'      => self::site_structure_actions(),
            ]
        ) )->register();
        AjaxActionRegistry::create( [ 'capability' => PluginMetadata::ADMIN_CAPABILITY, 'nonce_action' => self::NONCE ] )->register(
            [
                self::CREATE_PROFESSION => static fn( AjaxRequest $request ): array|\WP_Error => ProfessionPages::create( $request->int( 'index', -1, 'post' ) ),
                self::DELETE_PROFESSION => static function ( AjaxRequest $request ): array|\WP_Error {
                    $result = ProfessionPages::delete( $request->int( 'index', -1, 'post' ), $request->int( 'page', 0, 'post' ) );
                    return is_wp_error( $result ) ? $result : [ 'message' => 'Moved to the trash.' ];
                },
            ]
        );
    }
}
