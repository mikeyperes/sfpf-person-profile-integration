<?php

declare( strict_types=1 );

use SFPF\PersonProfile\ContentTypes\Organization\OrganizationContentType;

define( 'ABSPATH', dirname( __DIR__ ) . '/' );
require dirname( __DIR__ ) . '/src/ContentTypes/Organization/OrganizationContentType.php';

$legacy = OrganizationContentType::definition();
eval( 'namespace HWS\BaseTools\ContentTypes\Organization; final class OrganizationContentType { public const SETTINGS_OPTION = "hws_fixture"; }' );
$integration = OrganizationContentType::definition();
if ( 'owned' !== $legacy['registration_mode'] || 'external' !== $integration['registration_mode'] ) {
    throw new RuntimeException( 'Organization registration did not honor the Base Tools provider.' );
}
if ( $legacy['field_groups'] !== $integration['field_groups'] || $legacy['post_type'] !== $integration['post_type'] ) {
    throw new RuntimeException( 'The handoff changed Organization fields or compatibility arguments.' );
}
echo "PASS: legacy fallback and Base Tools field integration.\n";
