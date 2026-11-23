<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Admin;

use Hexa\PluginCore\CoreContracts\ModuleInterface;
use Hexa\PluginCore\CoreRuntime\CoreVersion;
use Hexa\PluginCore\WpAdminAjax\AjaxActionRegistry;
use Hexa\PluginCore\WpAdminAjax\AjaxFailure;
use Hexa\PluginCore\WpAdminAjax\AjaxRequest;
use Hexa\PluginCore\WpAdminComponents\CoreUi;
use Hexa\PluginCore\WpAdminTabs\HostTabsRenderer;
use Hexa\PluginCore\WpAdminTabs\TabRegistry;
use SFPF\PersonProfile\Admin\Tabs\DashboardTabs;
use SFPF\PersonProfile\Admin\Tabs\Tab;
use SFPF\PersonProfile\PluginRuntime\PluginMetadata;

defined( 'ABSPATH' ) || exit;

/**
 * The plugin's one settings page (Settings, SFPF Person Profile): grouped
 * tabs in a sidebar through Hexa WP Core's tab shell, each loaded when opened.
 * Other plugins and Core can add tabs through the dashboard filters.
 */
final class Dashboard implements ModuleInterface {
    public const TAB_ACTION = 'sfpf_load_dashboard_tab';
    public const TAB_NONCE  = 'sfpf_dashboard_tabs';
    public const TABS_FILTER   = 'sfpf_dashboard_tabs';
    public const RENDER_FILTER = 'sfpf_dashboard_render_tab';

    public const STANDARD_TABS_FILTER   = PluginMetadata::HOOK_PREFIX . '_dashboard_tabs';
    public const STANDARD_RENDER_FILTER = PluginMetadata::HOOK_PREFIX . '_render_dashboard_tab';

    private ?TabRegistry $registry = null;

    public function register(): void {
        add_action( 'admin_menu', [ $this, 'menu' ] );
        add_filter( 'plugin_action_links_' . PluginMetadata::basename(), [ $this, 'action_links' ] );
        AjaxActionRegistry::create( [ 'capability' => PluginMetadata::ADMIN_CAPABILITY, 'nonce_action' => self::TAB_NONCE ] )->register(
            [ self::TAB_ACTION => [ $this, 'load_tab' ] ]
        );
    }

    public function menu(): void {
        add_options_page( 'SFPF Person Profile', 'SFPF Person Profile', PluginMetadata::ADMIN_CAPABILITY, PluginMetadata::ADMIN_PAGE_SLUG, [ $this, 'render' ] );
    }

    /** @param array<int|string,string> $links */
    public function action_links( array $links ): array {
        array_unshift( $links, '<a href="' . esc_url( PluginMetadata::admin_url() ) . '">Settings</a>' );
        return $links;
    }

    /** Enqueues the shared dashboard stylesheet for callers of this entry point. */
    public function enqueue( string $hook ): void {
        ( new DashboardAssets() )->enqueue( $hook );
    }

    public function render(): void {
        if ( ! current_user_can( PluginMetadata::ADMIN_CAPABILITY ) ) {
            wp_die( esc_html__( 'You do not have permission to view this page.', 'sfpf-person-profile-integration' ) );
        }
        CoreUi::render_assets();
        $tabs   = $this->tab_labels();
        $active = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'overview'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only tab choice.
        echo '<div class="wrap sfpf-dashboard hpc-ui"><h1>SFPF Person Profile</h1>';
        ( new HostTabsRenderer() )->render(
            [
                'tabs'                => $tabs,
                'active'              => isset( $tabs[ $active ] ) ? $active : 'overview',
                'page_url'            => PluginMetadata::admin_url(),
                'ajax_action'         => self::TAB_ACTION,
                'nonce'               => wp_create_nonce( self::TAB_NONCE ),
                'nonce_field'         => 'nonce',
                'root_id'             => 'sfpf-dashboard-tabs',
                'panel_id'            => 'sfpf-dashboard-panel',
                'label'               => 'SFPF Person Profile sections',
                'layout'              => 'sidebar',
                'groups'              => DashboardTabs::groups( $tabs ),
                'sidebar_identity'    => [
                    'plugin_name'     => 'SFPF Person Profile',
                    'current_version' => PluginMetadata::VERSION,
                    'github_url'      => 'https://github.com/' . PluginMetadata::GITHUB_REPOSITORY,
                    'core_name'       => CoreVersion::PACKAGE_NAME,
                    'core_version'    => CoreVersion::current(),
                    'core_github_url' => 'https://github.com/mikeyperes/hexa-wordpress-plugin-core',
                ],
                'sidebar_collapsible' => true,
                'sidebar_persist'     => true,
                'render_callback'     => [ $this, 'render_tab' ],
            ]
        );
        echo '</div>';
    }

    /** @return array<string,mixed> */
    public function load_tab( AjaxRequest $request ): array {
        $tab = $request->key( 'tab', '', 'post' );
        if ( ! isset( $this->tab_labels()[ $tab ] ) ) {
            throw AjaxFailure::not_found( 'Unknown tab.', 'unknown_tab' );
        }
        ob_start();
        $this->render_tab( $tab );
        return [ 'html' => (string) ob_get_clean(), 'tab' => $tab ];
    }

    /** Renders one tab and attaches only the assets its controls require. */
    public function render_tab( string $tab ): void {
        ob_start();
        try {
            $rendered = (bool) apply_filters( self::RENDER_FILTER, false, $tab );
            if ( ! $rendered ) {
                $rendered = (bool) apply_filters( self::STANDARD_RENDER_FILTER, false, $tab );
            }
            if ( ! $rendered ) {
                $definition = $this->registry()->get( $tab );
                if ( null !== $definition ) {
                    ( $definition->renderer )();
                } else {
                    echo '<div class="notice notice-warning"><p>This section is not available.</p></div>';
                }
            }
            $html = (string) ob_get_contents();
        } finally {
            ob_end_clean();
        }

        echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by each tab renderer.
        DashboardAssets::render( $html );
    }

    /** @return array<string,Tab> The built-in tab objects for existing callers. */
    public static function tabs(): array {
        return DashboardTabs::tab_objects();
    }

    /** @return array<string,array{label:string}> Every tab, Core's included, by id. */
    private function tab_labels(): array {
        $tabs = [];
        foreach ( $this->registry()->all() as $id => $definition ) {
            $tabs[ $id ] = [ 'label' => $definition->label ];
        }
        $filtered = apply_filters( self::STANDARD_TABS_FILTER, $tabs );
        $filtered = apply_filters( self::TABS_FILTER, is_array( $filtered ) ? $filtered : $tabs );
        return is_array( $filtered ) ? $filtered : $tabs;
    }

    /** Keeps the Core tab registry scoped to this dashboard instance. */
    private function registry(): TabRegistry {
        return $this->registry ??= DashboardTabs::registry();
    }
}
