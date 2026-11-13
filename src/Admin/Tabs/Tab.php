<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Admin\Tabs;

defined( 'ABSPATH' ) || exit;

/** One section of the plugin's settings page. */
interface Tab {
    public function id(): string;

    public function label(): string;

    /** The sidebar group the tab sits in. */
    public function group(): string;

    public function render(): void;
}
