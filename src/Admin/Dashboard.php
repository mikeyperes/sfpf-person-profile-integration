<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Admin;

use Hexa\PluginCore\WpAdminAjax\AjaxActionRegistry;
use Hexa\PluginCore\WpAdminAjax\AjaxFailure;
use Hexa\PluginCore\WpAdminAjax\AjaxRequest;
use Hexa\PluginCore\WpAdminTabs\HostTabsRenderer;

defined( 'ABSPATH' ) || exit;

final class Dashboard {
    private const PAGE_SLUG = 'sfpf-person-profile';
    private const AJAX_ACTION = 'sfpf_load_dashboard_tab';
    private const NONCE_ACTION = 'sfpf_dashboard_tabs';

    private const SECTIONS = [
        'overview' => [
            'label' => 'Overview',
            'icon'  => 'dashicons-dashboard',
        ],
        'profile' => [
            'label' => 'Profile',
            'icon'  => 'dashicons-admin-users',
        ],
        'site' => [
            'label' => 'Site',
            'icon'  => 'dashicons-admin-site-alt3',
        ],
        'integrations' => [
            'label' => 'Integrations',
            'icon'  => 'dashicons-admin-plugins',
        ],
        'system' => [
            'label' => 'System',
            'icon'  => 'dashicons-admin-tools',
        ],
    ];

    private const TABS = [
        'overview' => [
            'overview' => [ 'label' => 'Status' ],
        ],
        'profile' => [
            'settings'   => [ 'label' => 'Settings' ],
            'shortcodes' => [ 'label' => 'Shortcodes' ],
            'schema'     => [ 'label' => 'Schema' ],
        ],
        'site' => [
            'pages'     => [ 'label' => 'Pages & Menus' ],
            'templates' => [ 'label' => 'Templates' ],
            'faq'       => [ 'label' => 'FAQ Structures' ],
        ],
        'integrations' => [
            'snippets' => [ 'label' => 'Snippets' ],
        ],
        'system' => [
            'debug' => [ 'label' => 'Debug' ],
        ],
    ];

    private static bool $registered = false;

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

        wp_enqueue_style(
            'sfpf-person-profile-dashboard',
            SFPF_PLUGIN_URL . 'assets/admin/dashboard.css',
            [],
            SFPF_PLUGIN_VERSION
        );
    }

    public static function render(): void {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'You do not have permission to view this page.', 'sfpf-person-profile-integration' ) );
        }

        $section = self::currentSection();
        $tab     = self::currentTab( $section );
        $tabs    = self::tabsForSection( $section );
        ?>
        <div class="wrap sfpf-dashboard sfpf-dashboard-shell">
            <header class="sfpf-dashboard-header">
                <div>
                    <h1>SFPF Person Profile</h1>
                    <span class="sfpf-dashboard-version">v<?php echo esc_html( SFPF_PLUGIN_VERSION ); ?></span>
                </div>
            </header>

            <nav class="sfpf-primary-nav" aria-label="SFPF areas">
                <?php foreach ( self::SECTIONS as $sectionId => $definition ) :
                    $firstTab = self::firstTab( $sectionId );
                    $url       = self::dashboardUrl( $sectionId, $firstTab );
                    $active    = $sectionId === $section;
                    ?>
                    <a class="sfpf-primary-tab<?php echo $active ? ' is-active' : ''; ?>"
                       href="<?php echo esc_url( $url ); ?>"
                       aria-current="<?php echo $active ? 'page' : 'false'; ?>">
                        <span class="dashicons <?php echo esc_attr( $definition['icon'] ); ?>" aria-hidden="true"></span>
                        <span><?php echo esc_html( $definition['label'] ); ?></span>
                    </a>
                <?php endforeach; ?>
            </nav>

            <main class="sfpf-dashboard-workspace">
                <div class="sfpf-section-heading">
                    <h2><?php echo esc_html( self::SECTIONS[ $section ]['label'] ); ?></h2>
                </div>
                <?php
                ( new HostTabsRenderer() )->render(
                    [
                        'tabs'            => $tabs,
                        'active'          => $tab,
                        'page_url'        => self::sectionUrl( $section ),
                        'ajax_action'     => self::AJAX_ACTION,
                        'nonce'           => wp_create_nonce( self::NONCE_ACTION ),
                        'nonce_field'     => 'nonce',
                        'root_id'         => 'sfpf-secondary-tabs',
                        'panel_id'        => 'sfpf-dashboard-panel',
                        'label'           => self::SECTIONS[ $section ]['label'] . ' views',
                        'render_callback' => [ self::class, 'renderPanel' ],
                    ]
                );
                ?>
            </main>
            <?php self::renderLegacyHashRedirect(); ?>
        </div>
        <?php
    }

    public static function loadTab( AjaxRequest $request ): array {
        $tab     = $request->key( 'tab', '', 'post' );
        $section = self::sectionForTab( $tab );

        if ( null === $section ) {
            throw AjaxFailure::not_found( 'Unknown dashboard tab.', 'unknown_tab' );
        }

        $tabs = self::tabsForSection( $section );

        ob_start();
        self::renderPanel( $tab );
        $html = (string) ob_get_clean();

        return [
            'html'    => $html,
            'tab'     => $tab,
            'section' => $section,
            'label'   => self::tabLabel( $tabs[ $tab ] ?? $tab ),
        ];
    }

    public static function renderPanel( string $tab ): void {
        if ( apply_filters( 'sfpf_dashboard_render_tab', false, $tab ) ) {
            return;
        }

        $section = self::sectionForTab( $tab );
        if ( null === $section ) {
            echo '<div class="notice notice-warning"><p>Dashboard panel not found.</p></div>';
            return;
        }

        $file = SFPF_PLUGIN_DIR . 'admin/dashboard-' . $tab . '.php';
        if ( ! is_readable( $file ) ) {
            echo '<div class="notice notice-warning"><p>Dashboard panel is unavailable.</p></div>';
            return;
        }

        include $file;
    }

    private static function currentSection(): string {
        $requested = isset( $_GET['section'] ) ? sanitize_key( wp_unslash( $_GET['section'] ) ) : '';
        if ( isset( self::SECTIONS[ $requested ] ) ) {
            return $requested;
        }

        $tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : '';
        return self::sectionForTab( $tab ) ?? 'overview';
    }

    private static function currentTab( string $section ): string {
        $tabs      = self::tabsForSection( $section );
        $requested = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : '';

        return isset( $tabs[ $requested ] ) ? $requested : self::firstTab( $section );
    }

    private static function tabsForSection( string $section ): array {
        $tabs = self::TABS[ $section ] ?? [];

        if ( 'system' === $section ) {
            $tabs = apply_filters( 'sfpf_dashboard_tabs', $tabs );
        }

        return is_array( $tabs ) ? $tabs : [];
    }

    private static function firstTab( string $section ): string {
        $tabs = self::tabsForSection( $section );
        $keys = array_keys( $tabs );

        return isset( $keys[0] ) ? (string) $keys[0] : 'overview';
    }

    private static function sectionForTab( string $tab ): ?string {
        if ( '' === $tab ) {
            return null;
        }

        foreach ( array_keys( self::SECTIONS ) as $section ) {
            if ( isset( self::tabsForSection( $section )[ $tab ] ) ) {
                return $section;
            }
        }

        return null;
    }

    private static function tabLabel( mixed $tab ): string {
        if ( is_array( $tab ) && isset( $tab['label'] ) ) {
            return (string) $tab['label'];
        }

        return (string) $tab;
    }

    private static function sectionUrl( string $section ): string {
        return add_query_arg(
            [
                'page'    => self::PAGE_SLUG,
                'section' => $section,
            ],
            admin_url( 'options-general.php' )
        );
    }

    private static function dashboardUrl( string $section, string $tab ): string {
        return add_query_arg( 'tab', $tab, self::sectionUrl( $section ) );
    }

    private static function renderLegacyHashRedirect(): void {
        $urls = [];
        foreach ( array_keys( self::SECTIONS ) as $section ) {
            foreach ( array_keys( self::tabsForSection( $section ) ) as $tab ) {
                $urls[ $tab ] = self::dashboardUrl( $section, $tab );
            }
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
}
