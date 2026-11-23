<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Shortcodes;

use Hexa\PluginCore\CoreContracts\ModuleInterface;

defined( 'ABSPATH' ) || exit;

/** Registers one SFPF shortcode through the plugin's Core bootstrap. */
final class ShortcodeModule implements ModuleInterface {
    /** Holds the shortcode's existing WordPress tag and callback. */
    public function __construct( private readonly string $tag, private readonly array $handler ) {}

    /** Adds the shortcode hook at the existing late priority. */
    public function register(): void {
        add_action( 'init', [ $this, 'add_shortcode' ], 100 );
    }

    /** Replaces any earlier registration of the same shortcode. */
    public function add_shortcode(): void {
        remove_shortcode( $this->tag );
        add_shortcode( $this->tag, $this->handler );
    }
}
