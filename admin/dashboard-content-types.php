<?php

declare( strict_types=1 );

namespace sfpf_person_website;

use Hexa\PluginCore\ContentTypes\ContentTypeRenderer;
use Hexa\PluginCore\FieldStructures\AcfFieldGroupRenderer;
use SFPF\PersonProfile\ContentTypes\PersonContentTypes;

defined( 'ABSPATH' ) || exit;

echo ( new ContentTypeRenderer() )->render(
    PersonContentTypes::content_types(),
    [
        'title' => 'Person Website Custom Post Types',
        'description' => 'SFPF owns Book, Press Release, Interview, and Contributing Profile content. Post-type keys stay fixed while public slugs, labels, and related ACF structures remain independently configurable.',
        'persist_prefix' => 'sfpf-content-types',
    ]
);

echo ( new AcfFieldGroupRenderer() )->render(
    PersonContentTypes::acf_groups(),
    [
        'title' => 'Person Website ACF Structures',
        'description' => 'Optional user-profile and front-page field structures. Each structure uses the same Core registration and AJAX state contract.',
        'persist_prefix' => 'sfpf-acf-structures',
    ]
);
