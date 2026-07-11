<?php

namespace sfpf_person_website;

use Hexa\PluginCore\PluginUpdates\UpdaterPanelRenderer;
use SFPF\PersonProfile\Core\CoreIntegration;

defined( 'ABSPATH' ) || exit;

/**
 * Render the shared Core updater inside the legacy overview call site.
 */
function sfpf_display_plugin_info(): void {
    if ( ! class_exists( UpdaterPanelRenderer::class ) ) {
        echo '<div class="notice notice-error"><p>The Hexa Core updater is unavailable.</p></div>';
        return;
    }

    ( new UpdaterPanelRenderer( CoreIntegration::updater_config() ) )->render();
}
