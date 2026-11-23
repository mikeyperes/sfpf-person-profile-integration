<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Admin;

use Hexa\PluginCore\CoreContracts\ModuleInterface;
use SFPF\PersonProfile\Admin\Actions\PagesActions;
use SFPF\PersonProfile\Admin\Actions\PressReleaseActions;
use SFPF\PersonProfile\Admin\Actions\SchemaActions;
use SFPF\PersonProfile\PluginRuntime\PluginMetadata;

defined( 'ABSPATH' ) || exit;

/** Loads the shell stylesheet and only the assets used by a rendered tab. */
final class DashboardAssets implements ModuleInterface {
    /** Registers the settings screen's stylesheet hook. */
    public function register(): void {
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue' ] );
    }

    /** Enqueues the shared styles for this plugin's settings page. */
    public function enqueue( string $hook ): void {
        if ( 'settings_page_' . PluginMetadata::ADMIN_PAGE_SLUG === $hook ) {
            wp_enqueue_style( 'sfpf-dashboard', self::url( 'dashboard.css' ), [], null );
        }
    }

    /** Identifies optional assets by the controls present in the active tab. */
    public static function for_tab( string $html ): array {
        $files = [];
        if ( str_contains( $html, 'sfpf-ajax-form' ) || str_contains( $html, 'class="sfpf-shortcode"' ) ) {
            $files[] = 'dashboard.js';
        }
        foreach ( [
            'data-sfpf-press-check' => 'press-releases.js',
            'data-sfpf-scan>' => 'schema.js',
            'data-sfpf-profession=' => 'profession-pages.js',
            'data-sfpf-add-set' => 'faq.js',
        ] as $control => $file ) {
            if ( str_contains( $html, $control ) ) {
                $files[] = $file;
            }
        }
        return $files;
    }

    /** Attaches assets to a tab on initial page render or Core's AJAX tab response. */
    public static function render( string $html ): void {
        $files = self::for_tab( $html );
        if ( [] === $files ) {
            return;
        }
        if ( in_array( 'faq.js', $files, true ) ) {
            printf( '<link rel="stylesheet" id="sfpf-faq-css" href="%s">', esc_url( self::url( 'faq.css' ) ) );
        }

        $config = [
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( AdminUi::NONCE ),
            'actions' => [
                'scan' => SchemaActions::SCAN,
                'createProfession' => PagesActions::CREATE_PROFESSION,
                'deleteProfession' => PagesActions::DELETE_PROFESSION,
                'pressCheck' => PressReleaseActions::CHECK,
            ],
        ];
        $scripts = array_values( array_diff( $files, [ 'dashboard.js' ] ) );
        $urls = array_map( [ self::class, 'url' ], [ 'dashboard.js', ...$scripts ] );
        $options = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;
        wp_print_inline_script_tag(
            'window.sfpfAdmin = window.sfpfAdmin || ' . wp_json_encode( $config, $options ) . ';'
            . '(function(urls){'
            . 'const pending = window.sfpfAdmin.scripts ||= {};'
            . 'const load = (url) => pending[url] ||= new Promise((resolve, reject) => {'
            . 'const script = document.createElement("script");'
            . 'script.src = url; script.onload = resolve;'
            . 'script.onerror = () => { delete pending[url]; script.remove(); reject(); };'
            . 'document.head.append(script);'
            . '});'
            . 'load(urls[0]).then(() => Promise.all(urls.slice(1).map(load))).catch(() => {});'
            . '})(' . wp_json_encode( $urls, $options ) . ');',
            [ 'id' => 'sfpf-dashboard-tab-assets' ]
        );
    }

    /** Returns the release-versioned URL of an admin asset. */
    private static function url( string $file ): string {
        return add_query_arg( 'ver', PluginMetadata::VERSION, PluginMetadata::url( 'assets/admin/' . $file ) );
    }
}
