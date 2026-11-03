<?php

declare( strict_types=1 );

namespace sfpf_person_website;

use Hexa\PluginCore\ContentTypes\ContentTypeRenderer;
use SFPF\PersonProfile\ContentTypes\PersonContentTypes;

defined( 'ABSPATH' ) || exit;

echo ( new ContentTypeRenderer() )->render(
    PersonContentTypes::view( [ 'award' ] ),
    [
        'title' => 'Awards',
        'description' => 'Awards and recognitions the person received. Turn the content type on, then turn on its fields. Each award has its own page at /award/<name>/ and all awards list at /awards/.',
        'persist_prefix' => 'sfpf-content-types-award',
    ]
);
