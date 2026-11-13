<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Admin\Tabs;

use Hexa\PluginCore\SchemaTools\SchemaStructuresPanel;
use SFPF\PersonProfile\Admin\AdminUi;
use SFPF\PersonProfile\Schema\SchemaCatalog;

defined( 'ABSPATH' ) || exit;

/**
 * The schema the plugin prints, structure by structure, with live previews,
 * and a scan of the live key pages for every schema block they carry.
 */
final class SchemaTab implements Tab {
    public function id(): string {
        return 'schema';
    }

    public function label(): string {
        return 'Schema';
    }

    public function group(): string {
        return 'Schema';
    }

    public function render(): void {
        SchemaStructuresPanel::render( SchemaCatalog::OWNER );
        $scan = '<p><button type="button" class="button button-primary" data-sfpf-scan>Scan the key pages</button> <span class="sfpf-form-status" role="status" aria-live="polite"></span></p><div data-sfpf-scan-output></div>';
        echo AdminUi::card( 'Live pages', $scan, 'Loads the homepage, biography, FAQs and connect pages as a visitor sees them and lists every schema block on each, with its source, types and any conflict. Rank Math\'s own schema is controlled in HWS Base Tools.' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }
}
