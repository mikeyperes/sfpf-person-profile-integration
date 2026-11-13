<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Frontend;

use Hexa\PluginCore\CoreContracts\ModuleInterface;
use SFPF\PersonProfile\Settings\Settings;

defined( 'ABSPATH' ) || exit;

/**
 * Hides Rank Math's visible breadcrumb trail on the pages chosen in the
 * Breadcrumbs tab: the front page, chosen pages, and single pages of chosen
 * content types. Breadcrumb schema is not affected.
 */
final class BreadcrumbVisibility implements ModuleInterface {
    public function register(): void {
        add_filter( 'rank_math/frontend/breadcrumb/html', [ $this, 'filter' ] );
    }

    public function filter( mixed $html ): mixed {
        if ( is_front_page() && Settings::flag( Settings::BREADCRUMB_HIDE_FRONT_PAGE, false ) ) {
            return '';
        }
        if ( is_page() && in_array( (int) get_queried_object_id(), Settings::ids( Settings::BREADCRUMB_EXCLUDED_PAGES ), true ) ) {
            return '';
        }
        if ( is_singular() && in_array( (string) get_post_type(), Settings::keys( Settings::BREADCRUMB_EXCLUDED_TYPES ), true ) ) {
            return '';
        }
        return $html;
    }
}
