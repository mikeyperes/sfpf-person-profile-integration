<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Admin\Tabs;

use Hexa\PluginCore\CorePackageUpdates\CorePackagePanelRenderer;
use Hexa\PluginCore\PluginUpdates\UpdaterPanelRenderer;
use SFPF\PersonProfile\PluginRuntime\Updates;

defined( 'ABSPATH' ) || exit;

/** The plugin's version against GitHub, and its bundled Hexa WP Core against Core's. */
final class UpdateCenterTab implements Tab {
    public function id(): string {
        return 'update-center';
    }

    public function label(): string {
        return 'Update Center';
    }

    public function group(): string {
        return 'System';
    }

    public function render(): void {
        ( new UpdaterPanelRenderer( Updates::plugin() ) )->render();
        ( new CorePackagePanelRenderer( Updates::core() ) )->render();
    }
}
