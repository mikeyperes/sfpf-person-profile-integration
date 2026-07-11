<?php

declare( strict_types=1 );

namespace sfpf_person_website;

defined( 'ABSPATH' ) || exit;

require_once SFPF_PLUGIN_DIR . 'src/Admin/Ajax/ModuleLoader.php';
\SFPF\PersonProfile\Admin\Ajax\ModuleLoader::load();
