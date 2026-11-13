<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Admin\Tabs;

use Hexa\PluginCore\ShortcodeRegistry\ShortcodeCatalogRenderer;
use SFPF\PersonProfile\Person\PersonResolver;
use SFPF\PersonProfile\Shortcodes\ShortcodeCatalog;

defined( 'ABSPATH' ) || exit;

/** Every shortcode with its attributes, variations and older forms. */
final class ShortcodesTab implements Tab {
    public function id(): string {
        return 'shortcodes';
    }

    public function label(): string {
        return 'Shortcodes';
    }

    public function group(): string {
        return 'Person';
    }

    public function render(): void {
        echo ( new ShortcodeCatalogRenderer() )->render_page( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Core renderer escapes.
            [
                'title'   => 'Shortcodes',
                'intro'   => 'Every SFPF shortcode shares the same attributes: format, link, target, pretty, separator, index, key, fallback, class, style="none" and heading.',
                'catalog' => ShortcodeCatalog::catalog(),
                'context' => [ 'user_id' => PersonResolver::user_id() ],
                'layers'  => [ 'Person values' => 'author', 'Sections' => 'post' ],
            ]
        );
    }
}
