<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\PluginRuntime;

use Hexa\PluginCore\CoreContracts\ModuleInterface;

defined( 'ABSPATH' ) || exit;

/** Registers SFPF activation and deactivation through the Core bootstrap. */
final class LifecycleModule implements ModuleInterface {
    /** Adds the lifecycle hooks for the existing plugin file. */
    public function register(): void {
        Lifecycle::register( PluginMetadata::plugin_file() );
    }
}
