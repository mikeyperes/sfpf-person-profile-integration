<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Admin\Actions;

use Hexa\PluginCore\CoreContracts\ModuleInterface;
use Hexa\PluginCore\SchemaDetection\SchemaPageScanner;
use Hexa\PluginCore\SchemaDetection\SchemaScanRenderer;
use Hexa\PluginCore\WpAdminAjax\AjaxActionRegistry;
use SFPF\PersonProfile\Admin\AdminUi;
use SFPF\PersonProfile\Diagnostics\ProfileReport;
use SFPF\PersonProfile\PluginRuntime\PluginMetadata;

defined( 'ABSPATH' ) || exit;

/** Scans the key pages as visitors load them, with Hexa WP Core's schema scanner. */
final class SchemaActions implements ModuleInterface {
    public const SCAN = 'sfpf_detect_schema';

    /** The schema each key page should carry. */
    private const EXPECTED = [ 'Homepage' => [ 'ProfilePage', 'Person', 'WebSite' ], 'Biography' => [ 'ProfilePage', 'Person' ], 'FAQs' => [ 'FAQPage', 'Person' ], 'Connect' => [ 'ContactPage', 'Person' ] ];

    public function register(): void {
        AjaxActionRegistry::create( [ 'capability' => PluginMetadata::ADMIN_CAPABILITY, 'nonce_action' => AdminUi::NONCE ] )->register(
            [ self::SCAN => [ $this, 'scan' ] ]
        );
    }

    /** @return array{html:string} */
    public function scan(): array {
        $scanner = new SchemaPageScanner();
        $scans   = [];
        foreach ( ProfileReport::schema_pages() as $label => $context ) {
            $scans[] = $scanner->scanUrl( $context->url, [ 'title' => $label, 'sslverify' => true ] );
        }
        $expected = [];
        foreach ( self::EXPECTED as $label => $types ) {
            $expected[] = $label . ': ' . implode( ', ', $types );
        }
        return [ 'html' => ( new SchemaScanRenderer() )->renderReport( $scans, [ 'title' => 'Schema on the live pages', 'expected' => $expected ] ) ];
    }
}
