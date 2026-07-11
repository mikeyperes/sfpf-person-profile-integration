<?php

namespace sfpf_person_website;

use SFPF\PersonProfile\Admin\Dashboard;

defined( 'ABSPATH' ) || exit;

require_once SFPF_PLUGIN_DIR . 'src/Admin/Dashboard.php';

Dashboard::register();

/**
 * Legacy callback retained for integrations that invoke the old function.
 */
function render_dashboard(): void {
    Dashboard::render();
}
