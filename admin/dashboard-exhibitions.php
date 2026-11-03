<?php

declare( strict_types=1 );

namespace sfpf_person_website;

use Hexa\PluginCore\ContentTypes\ContentTypeRenderer;
use SFPF\PersonProfile\ContentTypes\PersonContentTypes;

defined( 'ABSPATH' ) || exit;

echo ( new ContentTypeRenderer() )->render(
    PersonContentTypes::view( [ 'exhibition' ] ),
    [
        'title' => 'Exhibitions',
        'description' => 'Exhibitions, shows and fairs where the person\'s work was displayed. Turn the content type on, then turn on its fields. Each exhibition has its own page at /exhibition/<name>/ and all exhibitions list at /exhibitions/.',
        'persist_prefix' => 'sfpf-content-types-exhibition',
    ]
);
