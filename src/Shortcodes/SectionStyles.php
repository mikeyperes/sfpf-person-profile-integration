<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Shortcodes;

use Hexa\PluginCore\PublicComponents\InlineStyles;
use SFPF\PersonProfile\PluginRuntime\PluginMetadata;

defined( 'ABSPATH' ) || exit;

/**
 * The sections' stylesheet, printed once on a page that shows a styled
 * section, never on pages without one. Colors and fonts come from the page;
 * the styles only set layout and spacing, overridable with CSS variables.
 */
final class SectionStyles {
    public const HANDLE = 'sfpf-person-sections';

    public static function enqueue(): void {
        static $css = null;
        if ( null === $css ) {
            $path = PluginMetadata::root_path() . '/assets/frontend/person-sections.css';
            $css  = is_readable( $path ) ? (string) file_get_contents( $path ) : ''; // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Local plugin asset.
        }
        InlineStyles::enqueue( self::HANDLE, $css );
    }
}
