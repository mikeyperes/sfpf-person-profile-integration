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
use SFPF\PersonProfile\Admin\Actions\PagesActions;
use SFPF\PersonProfile\Admin\Actions\SchemaActions;
use SFPF\PersonProfile\Admin\Tabs\BreadcrumbsTab;
use SFPF\PersonProfile\Admin\Tabs\ContentTypesTab;
use SFPF\PersonProfile\Admin\Tabs\DebugTab;
use SFPF\PersonProfile\Admin\Tabs\FaqTab;
use SFPF\PersonProfile\Admin\Tabs\PagesTab;
use SFPF\PersonProfile\Admin\Tabs\PersonTab;
use SFPF\PersonProfile\Admin\Tabs\SchemaTab;
use SFPF\PersonProfile\Admin\Tabs\SettingsTab;
use SFPF\PersonProfile\Admin\Tabs\ShortcodesTab;
use SFPF\PersonProfile\Admin\Tabs\SitemapsTab;
use SFPF\PersonProfile\Admin\Tabs\StatusTab;
use SFPF\PersonProfile\Admin\Tabs\Tab;
use SFPF\PersonProfile\Admin\Tabs\TemplatesTab;
use SFPF\PersonProfile\Admin\Tabs\UpdateCenterTab;
use SFPF\PersonProfile\PluginRuntime\PluginMetadata;

defined( 'ABSPATH' ) || exit;

/**
 * The plugin's one settings page (Settings, SFPF Person Profile): grouped
 * tabs in a sidebar through Hexa WP Core's tab shell, each loaded when opened.
 * Other plugins and Core add tabs through the sfpf_dashboard_tabs and
 * sfpf_dashboard_render_tab filters.
 */
final class Dashboard implements ModuleInterface {
    public const TAB_ACTION = 'sfpf_load_dashboard_tab';
    public const TAB_NONCE  = 'sfpf_dashboard_tabs';
    public const TABS_FILTER   = 'sfpf_dashboard_tabs';
    public const RENDER_FILTER = 'sfpf_dashboard_render_tab';

    private const GROUPS = [ 'Overview', 'Person', 'Schema', 'Content', 'Site', 'System' ];

    /** @var array<string,Tab>|null */
    private static ?array $tabs = null;

    public function register(): void {
        add_action( 'admin_menu', [ $this, 'menu' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue' ] );
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

    public function enqueue( string $hook ): void {
        if ( 'settings_page_' . PluginMetadata::ADMIN_PAGE_SLUG !== $hook ) {
            return;
        }
        wp_enqueue_style( 'sfpf-dashboard', PluginMetadata::url( 'assets/admin/dashboard.css' ), [], PluginMetadata::VERSION );
        wp_enqueue_script( 'sfpf-dashboard', PluginMetadata::url( 'assets/admin/dashboard.js' ), [], PluginMetadata::VERSION, true );
        wp_localize_script(
            'sfpf-dashboard',
            'sfpfAdmin',
            [
                'ajaxUrl' => admin_url( 'admin-ajax.php' ),
                'nonce'   => wp_create_nonce( AdminUi::NONCE ),
                'actions' => [ 'scan' => SchemaActions::SCAN, 'createProfession' => PagesActions::CREATE_PROFESSION, 'deleteProfession' => PagesActions::DELETE_PROFESSION ],
            ]
        );
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
                'groups'              => $this->groups( $tabs ),
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

    public function render_tab( string $tab ): void {
        if ( apply_filters( self::RENDER_FILTER, false, $tab ) ) {
            return;
        }
        $definition = self::tabs()[ $tab ] ?? null;
        if ( null === $definition ) {
            echo '<div class="notice notice-warning"><p>This section is not available.</p></div>';
            return;
        }
        $definition->render();
    }

    /** @return array<string,Tab> */
    public static function tabs(): array {
        if ( null === self::$tabs ) {
            self::$tabs = [];
            foreach ( [
                new StatusTab(),
                new PersonTab(),
                new ShortcodesTab(),
                new SchemaTab(),
                new ContentTypesTab(),
                new ContentTypesTab( 'awards', 'Awards', [ 'award' ], [], 'Awards the person received. Turn the content type on here; each award gets a page at /award/name/, the list is at /awards/, and every award is added to the person\'s schema.' ),
                new ContentTypesTab( 'exhibitions', 'Exhibitions', [ 'exhibition' ], [], 'Exhibitions, shows and fairs that showed the person\'s work. Each gets a page at /exhibition/name/ with ExhibitionEvent schema, and the list is at /exhibitions/.' ),
                new PagesTab(),
                new TemplatesTab(),
                new FaqTab(),
                new BreadcrumbsTab(),
                new SettingsTab(),
                new SitemapsTab(),
                new UpdateCenterTab(),
                new DebugTab(),
            ] as $tab ) {
                self::$tabs[ $tab->id() ] = $tab;
            }
        }
        return self::$tabs;
    }

    /** @return array<string,array{label:string}> Every tab, Core's included, by id. */
    private function tab_labels(): array {
        $tabs = [];
        foreach ( self::tabs() as $id => $tab ) {
            $tabs[ $id ] = [ 'label' => $tab->label() ];
        }
        $filtered = apply_filters( self::TABS_FILTER, $tabs );
        return is_array( $filtered ) ? $filtered : $tabs;
    }

    /**
     * @param array<string,mixed> $tabs
     * @return list<array{label:string,tabs:list<string>}>
     */
    private function groups( array $tabs ): array {
        $groups = array_fill_keys( self::GROUPS, [] );
        foreach ( array_keys( $tabs ) as $id ) {
            $group = isset( self::tabs()[ $id ] ) ? self::tabs()[ $id ]->group() : 'System';
            $groups[ $group ][] = (string) $id;
        }
        $out = [];
        foreach ( $groups as $label => $ids ) {
            if ( [] !== $ids ) {
                $out[] = [ 'label' => $label, 'tabs' => $ids ];
            }
        }
        return $out;
    }
}
