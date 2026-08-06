<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Admin;

use Hexa\PluginCore\CoreRuntime\CoreVersion;
use Hexa\PluginCore\WpAdminAjax\AjaxActionRegistry;
use Hexa\PluginCore\WpAdminAjax\AjaxFailure;
use Hexa\PluginCore\WpAdminAjax\AjaxRequest;
use Hexa\PluginCore\WpAdminTabs\HostTabsRenderer;
use Hexa\PluginCore\WpAdminTabs\TabDefinition;
use Hexa\PluginCore\WpAdminTabs\TabRegistry;

defined( 'ABSPATH' ) || exit;

final class Dashboard {
    private const PAGE_SLUG = 'sfpf-person-profile';
    private const AJAX_ACTION = 'sfpf_load_dashboard_tab';
    private const NONCE_ACTION = 'sfpf_dashboard_tabs';

    private const GROUPS = [
        [ 'label' => 'Overview', 'tabs' => [ 'overview' ] ],
        [ 'label' => 'Profile', 'tabs' => [ 'settings', 'content-types', 'shortcodes', 'schema' ] ],
        [ 'label' => 'Site', 'tabs' => [ 'pages', 'templates', 'faq' ] ],
        [ 'label' => 'System', 'tabs' => [ 'debug', 'hexa-core' ] ],
    ];

    private const LEGACY_SECTION_DEFAULTS = [
        'overview'     => 'overview',
        'profile'      => 'settings',
        'site'         => 'pages',
        'integrations' => 'content-types',
        'system'       => 'debug',
    ];

    private const TAB_SECTIONS = [
        'overview'   => 'overview',
        'settings'   => 'profile',
        'content-types' => 'profile',
        'shortcodes' => 'profile',
        'schema'     => 'profile',
        'pages'      => 'site',
        'templates'  => 'site',
        'faq'        => 'site',
        'debug'      => 'system',
        'hexa-core'  => 'system',
    ];

    private static bool $registered = false;
    private static ?TabRegistry $tabRegistry = null;

    public static function register(): void {
        if ( self::$registered ) {
            return;
        }

        add_action( 'admin_menu', [ self::class, 'registerMenu' ] );
        add_action( 'admin_enqueue_scripts', [ self::class, 'enqueueAssets' ] );

        if ( class_exists( AjaxActionRegistry::class ) ) {
            ( new AjaxActionRegistry(
                [
                    'capability'   => 'manage_options',
                    'nonce_action' => self::NONCE_ACTION,
                    'nonce_field'  => 'nonce',
                ]
            ) )->register(
                [
                    self::AJAX_ACTION => [
                        'callback' => [ self::class, 'loadTab' ],
                    ],
                ]
            );
        }

        self::$registered = true;
    }

    public static function registerMenu(): void {
        add_options_page(
            'HWS Person Profile Setup',
            'HWS Person Profile',
            'manage_options',
            self::PAGE_SLUG,
            [ self::class, 'render' ]
        );
    }

    public static function enqueueAssets( string $hookSuffix ): void {
        if ( 'settings_page_' . self::PAGE_SLUG !== $hookSuffix ) {
            return;
        }

        $css = self::dashboardCss();
        if ( '' !== $css ) {
            wp_add_inline_style( 'common', $css );
        }
    }

    public static function render(): void {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'You do not have permission to view this page.', 'sfpf-person-profile-integration' ) );
        }

        $tabs = self::tabs();
        $tab  = self::currentTab( $tabs );
        ?>
        <div class="wrap sfpf-dashboard sfpf-dashboard-shell">
            <header class="sfpf-dashboard-header">
                <div>
                    <h1>SFPF Person Profile</h1>
                </div>
            </header>

            <?php
            ( new HostTabsRenderer() )->render(
                [
                    'tabs'                => $tabs,
                    'active'              => $tab,
                    'page_url'            => self::pageUrl(),
                    'ajax_action'         => self::AJAX_ACTION,
                    'nonce'               => wp_create_nonce( self::NONCE_ACTION ),
                    'nonce_field'         => 'nonce',
                    'root_id'             => 'sfpf-person-profile-tabs',
                    'panel_id'            => 'sfpf-dashboard-panel',
                    'label'               => 'SFPF Person Profile sections',
                    'layout'              => 'sidebar',
                    'groups'              => self::GROUPS,
                    'sidebar_identity'    => [
                        'plugin_name'     => 'SFPF Person Profile',
                        'current_version' => SFPF_PLUGIN_VERSION,
                        'github_url'      => 'https://github.com/mikeyperes/sfpf-person-profile-integration',
                        'core_name'       => CoreVersion::PACKAGE_NAME,
                        'core_version'    => CoreVersion::current(),
                        'core_github_url' => 'https://github.com/mikeyperes/hexa-wordpress-plugin-core',
                    ],
                    'sidebar_collapsible' => true,
                    'sidebar_collapsed'   => false,
                    'sidebar_persist'     => true,
                    'render_callback'     => [ self::class, 'renderPanel' ],
                ]
            );
            ?>
            <?php self::renderLegacyHashRedirect(); ?>
        </div>
        <?php
    }

    public static function loadTab( AjaxRequest $request ): array {
        $tabs = self::tabs();
        $tab  = $request->key( 'tab', '', 'post' );

        if ( ! isset( $tabs[ $tab ] ) ) {
            throw AjaxFailure::not_found( 'Unknown dashboard tab.', 'unknown_tab' );
        }

        ob_start();
        self::renderPanel( $tab );
        $html = (string) ob_get_clean();

        return [
            'html'    => $html,
            'tab'     => $tab,
            'section' => self::legacySectionForTab( $tab ),
            'label'   => self::tabLabel( $tabs[ $tab ] ),
        ];
    }

    public static function renderPanel( string $tab ): void {
        if ( apply_filters( 'sfpf_dashboard_render_tab', false, $tab ) ) {
            return;
        }

        $definition = self::tabRegistry()->get( $tab );
        if ( ! $definition instanceof TabDefinition || ! is_callable( $definition->renderer ) ) {
            echo '<div class="notice notice-warning"><p>Dashboard panel not found.</p></div>';
            return;
        }

        call_user_func( $definition->renderer );
    }

    private static function renderLegacyPanel( string $tab ): void {
        if ( 'overview' === $tab ) {
            require_once SFPF_PLUGIN_DIR . 'admin/dashboard-plugin-info.php';
        }

        $file = SFPF_PLUGIN_DIR . 'admin/dashboard-' . $tab . '.php';
        if ( ! is_readable( $file ) ) {
            echo '<div class="notice notice-warning"><p>Dashboard panel is unavailable.</p></div>';
            return;
        }

        include $file;
    }

    /** @param array<string,mixed> $tabs */
    private static function currentTab( array $tabs ): string {
        $requested = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : '';
        if ( isset( $tabs[ $requested ] ) ) {
            return $requested;
        }

        $section = isset( $_GET['section'] ) ? sanitize_key( wp_unslash( $_GET['section'] ) ) : '';
        $legacy  = self::LEGACY_SECTION_DEFAULTS[ $section ] ?? 'overview';
        if ( isset( $tabs[ $legacy ] ) ) {
            return $legacy;
        }

        $keys = array_keys( $tabs );
        return isset( $keys[0] ) ? (string) $keys[0] : 'overview';
    }

    /** @return array<string,mixed> */
    private static function tabs(): array {
        $tabs = [];
        foreach ( self::tabRegistry()->all() as $tab ) {
            $tabs[ $tab->id ] = [ 'label' => $tab->label ];
        }

        $filtered = apply_filters( 'sfpf_dashboard_tabs', $tabs );
        return is_array( $filtered ) ? $filtered : $tabs;
    }

    private static function tabRegistry(): TabRegistry {
        if ( self::$tabRegistry instanceof TabRegistry ) {
            return self::$tabRegistry;
        }

        self::$tabRegistry = new TabRegistry();
        foreach (
            [
                'overview'      => 'Status',
                'settings'      => 'Settings',
                'content-types' => 'Custom Post Types',
                'shortcodes'    => 'Shortcodes',
                'schema'        => 'Schema',
                'pages'         => 'Pages & Menus',
                'templates'     => 'Templates',
                'faq'           => 'FAQ Structures',
                'debug'         => 'Debug',
            ] as $id => $label
        ) {
            self::$tabRegistry->add(
                new TabDefinition(
                    $id,
                    $label,
                    static fn(): mixed => self::renderLegacyPanel( $id )
                )
            );
        }

        return self::$tabRegistry;
    }

    private static function legacySectionForTab( string $tab ): string {
        return self::TAB_SECTIONS[ $tab ] ?? 'system';
    }

    private static function tabLabel( mixed $tab ): string {
        if ( is_array( $tab ) && isset( $tab['label'] ) ) {
            return (string) $tab['label'];
        }

        return (string) $tab;
    }

    private static function pageUrl(): string {
        return add_query_arg( 'page', self::PAGE_SLUG, admin_url( 'options-general.php' ) );
    }

    private static function renderLegacyHashRedirect(): void {
        $urls = [];
        foreach ( array_keys( self::tabs() ) as $tab ) {
            $urls[ $tab ] = add_query_arg( 'tab', $tab, self::pageUrl() );
        }
        ?>
        <script>
        (function(){
            var tab = String(window.location.hash || "").replace(/^#/, "");
            var urls = <?php echo wp_json_encode( $urls ); ?>;
            if (tab && urls[tab]) window.location.replace(urls[tab]);
        })();
        </script>
        <?php
    }

    private static function dashboardCss(): string {
        static $css = null;

        if ( is_string( $css ) ) {
            return $css;
        }

        $path = SFPF_PLUGIN_DIR . 'assets/admin/dashboard.css';
        $css  = is_readable( $path ) ? (string) file_get_contents( $path ) : '';

        return $css;
    }
}
