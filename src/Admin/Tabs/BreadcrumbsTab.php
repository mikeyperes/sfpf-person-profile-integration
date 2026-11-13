<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Admin\Tabs;

use SFPF\PersonProfile\Admin\Actions\SettingsActions;
use SFPF\PersonProfile\Admin\AdminUi;
use SFPF\PersonProfile\Settings\Settings;

defined( 'ABSPATH' ) || exit;

/** Where Rank Math's visible breadcrumb trail is hidden. */
final class BreadcrumbsTab implements Tab {
    public function id(): string {
        return 'breadcrumbs';
    }

    public function label(): string {
        return 'Breadcrumbs';
    }

    public function group(): string {
        return 'Site';
    }

    public function render(): void {
        $pages = '';
        foreach ( get_pages( [ 'sort_column' => 'post_title' ] ) as $page ) {
            $pages .= '<option value="' . (int) $page->ID . '"' . selected( in_array( (int) $page->ID, Settings::ids( Settings::BREADCRUMB_EXCLUDED_PAGES ), true ), true, false ) . '>' . esc_html( $page->post_title . ' (/' . $page->post_name . '/)' ) . '</option>';
        }
        $types = '';
        foreach ( get_post_types( [ 'public' => true ], 'objects' ) as $type ) {
            $types .= '<label class="sfpf-inline-check"><input type="checkbox" name="types[]" value="' . esc_attr( $type->name ) . '"' . checked( in_array( $type->name, Settings::keys( Settings::BREADCRUMB_EXCLUDED_TYPES ), true ), true, false ) . '> ' . esc_html( $type->labels->singular_name ) . '</label> ';
        }
        $fields = AdminUi::toggle( 'front_page', Settings::flag( Settings::BREADCRUMB_HIDE_FRONT_PAGE, false ), 'Hide on the front page' )
            . '<p class="sfpf-setting"><label><strong>Hide on these pages</strong><br><select name="pages[]" multiple size="8" class="sfpf-wide">' . $pages . '</select></label><br><span class="description">Hold Ctrl or Cmd to choose several.</span></p>'
            . '<p class="sfpf-setting"><strong>Hide on single pages of these content types</strong><br>' . $types . '</p>';
        $rank_math = defined( 'RANK_MATH_VERSION' ) ? '' : '<p class="sfpf-empty">Rank Math is not active, so there is no breadcrumb trail to hide.</p>';
        echo AdminUi::card( 'Breadcrumbs', $rank_math . AdminUi::form( SettingsActions::BREADCRUMBS, $fields ), 'Rank Math prints the breadcrumb trail with [rank_math_breadcrumb]. Hide it where it does not belong; the breadcrumb schema stays.' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }
}
