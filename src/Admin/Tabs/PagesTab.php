<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Admin\Tabs;

use Hexa\PluginCore\SiteStructure\SiteStructureRenderer;
use SFPF\PersonProfile\Admin\Actions\PagesActions;
use SFPF\PersonProfile\Admin\AdminUi;
use SFPF\PersonProfile\Pages\SiteStructure;
use SFPF\PersonProfile\Person\PersonResolver;
use SFPF\PersonProfile\Settings\Settings;

defined( 'ABSPATH' ) || exit;

/**
 * The person pages and menus (Hexa WP Core's page manager: link or create
 * each page, edit and apply its starting content, build the menus), and a
 * page for each profession.
 */
final class PagesTab implements Tab {
    public function id(): string {
        return 'pages';
    }

    public function label(): string {
        return 'Pages & Menus';
    }

    public function group(): string {
        return 'Site';
    }

    public function render(): void {
        echo ( new SiteStructureRenderer( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Core renderer escapes.
            SiteStructure::manager(),
            [
                'instance_id'             => 'sfpf-site-structure',
                'nonce'                   => wp_create_nonce( PagesActions::NONCE ),
                'enable_templates'        => true,
                'enable_template_editors' => true,
                'show_page_details'       => true,
                'actions'                 => PagesActions::site_structure_actions(),
                'apply_template_action'   => PagesActions::site_structure_actions()['apply_template'],
                'labels'                  => [
                    'pages_title'       => 'Person Pages',
                    'pages_heading'     => 'The person website\'s pages',
                    'pages_description' => 'Link each page to an existing WordPress page or create it. Every page starts from shortcodes that always show the person\'s current data; edit a page\'s starting content and apply it here.',
                    'menus_title'       => 'Menus',
                    'menus_heading'     => 'Header, footer and sub-footer menus',
                    'menus_description' => 'Create the menus and add the linked pages to them in one go.',
                ],
            ]
        ) )->render();
        $this->professions();
    }

    private function professions(): void {
        $profile = PersonResolver::profile();
        if ( null === $profile ) {
            return;
        }
        $parent = Settings::assigned_page( 'professions' );
        $rows   = '';
        foreach ( $profile->professions() as $row ) {
            if ( '' === $row['name'] ) {
                continue;
            }
            $page    = $row['page_id'] > 0 ? '<a href="' . esc_url( (string) get_permalink( $row['page_id'] ) ) . '" target="_blank" rel="noopener">' . esc_html( (string) get_the_title( $row['page_id'] ) ) . '</a>' : '<span class="sfpf-empty">No page yet</span>';
            $action  = $row['page_id'] > 0
                ? '<button type="button" class="button" data-sfpf-profession="delete" data-index="' . (int) $row['index'] . '" data-page="' . (int) $row['page_id'] . '">Move page to trash</button>'
                : ( $parent > 0 ? '<button type="button" class="button button-primary" data-sfpf-profession="create" data-index="' . (int) $row['index'] . '">Create page</button>' : '<span class="sfpf-empty">Link the Professions page first</span>' );
            $rows .= '<tr><td><strong>' . esc_html( $row['name'] ) . '</strong></td><td>' . $page . '</td><td>' . $action . ' <span class="sfpf-form-status" role="status" aria-live="polite"></span></td></tr>';
        }
        $body = '' === $rows
            ? '<p>No professions on ' . esc_html( $profile->name() ) . '\'s profile yet. ' . AdminUi::link_button( $profile->edit_url(), 'Add them' ) . '</p>'
            : '<table class="widefat striped"><thead><tr><th>Profession</th><th>Page</th><th></th></tr></thead><tbody>' . $rows . '</tbody></table>';
        echo AdminUi::card( 'Profession pages', $body, 'A page per profession, under the Professions page. The profession on the profile links to it, and the schema names it as the occupation\'s page.' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }
}
