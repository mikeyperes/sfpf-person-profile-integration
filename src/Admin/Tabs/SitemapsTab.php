<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Admin\Tabs;

use Hexa\PluginCore\Sitemaps\SitemapsPanel;
use SFPF\PersonProfile\Sitemaps\SitemapTypes;

defined( 'ABSPATH' ) || exit;

/** The sitemaps of the plugin's content types, with Rank Math's state for each. */
final class SitemapsTab implements Tab {
    public function id(): string {
        return 'sitemaps';
    }

    public function label(): string {
        return 'Sitemaps';
    }

    public function group(): string {
        return 'Site';
    }

    public function render(): void {
        SitemapsPanel::render( SitemapTypes::SLUG );
    }
}
