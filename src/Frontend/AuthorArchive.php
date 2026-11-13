<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Frontend;

use Hexa\PluginCore\CoreContracts\ModuleInterface;
use Hexa\PluginCore\SchemaTools\SchemaStructures;
use SFPF\PersonProfile\Person\PersonProfile;
use SFPF\PersonProfile\PluginRuntime\PluginMetadata;
use SFPF\PersonProfile\Schema\SchemaCatalog;
use SFPF\PersonProfile\Settings\Settings;
use SFPF\PersonProfile\Shortcodes\Sections\SectionRegistry;
use SFPF\PersonProfile\Shortcodes\SectionStyles;

defined( 'ABSPATH' ) || exit;

/**
 * Author pages (/author/name/) show the author's profile when no Elementor
 * archive template covers them: portrait, name, title, short biography,
 * social profiles, facts, then the biography, education and links. With an
 * Elementor template, Elementor draws the page as designed.
 */
final class AuthorArchive implements ModuleInterface {
    public function register(): void {
        add_filter( 'rank_math/json_ld', [ $this, 'leave_schema_to_sfpf' ], 99 );
        if ( Settings::flag( Settings::AUTHOR_ARCHIVE_PROFILE, true ) ) {
            add_action( 'template_redirect', [ $this, 'render' ], 0 );
        }
    }

    /** Author pages carry SFPF's ProfilePage, so Rank Math's second copy is left off. */
    public function leave_schema_to_sfpf( mixed $data ): mixed {
        return is_author() && SchemaStructures::enabled( SchemaCatalog::OWNER, 'author-profile' ) ? [] : $data;
    }

    public function render(): void {
        if ( is_admin() || wp_doing_ajax() || is_feed() || ! is_author() || self::elementor_template_covers() ) {
            return;
        }
        $author  = get_queried_object();
        $profile = $author instanceof \WP_User ? PersonProfile::for_user( (int) $author->ID ) : null;
        if ( null === $profile ) {
            return;
        }
        SectionStyles::enqueue();
        wp_enqueue_style( 'sfpf-author-profile', PluginMetadata::url( 'assets/frontend/author-profile.css' ), [], PluginMetadata::VERSION );
        status_header( 200 );
        get_header();
        echo self::html( $profile ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Built from escaped parts.
        get_footer();
        exit;
    }

    public static function html( PersonProfile $profile ): string {
        $facts = array_filter(
            [
                'Email'       => '' !== $profile->public_email() ? '<a href="' . esc_url( 'mailto:' . $profile->public_email() ) . '">' . esc_html( $profile->public_email() ) . '</a>' : '',
                'Born'        => esc_html( implode( ', ', array_filter( [ $profile->birth_date(), $profile->birth_place()['location'] ] ) ) ),
                'Nationality' => esc_html( implode( ', ', $profile->nationality() ) ),
                'Languages'   => esc_html( implode( ', ', $profile->languages() ) ),
            ]
        );
        $html  = '<main id="content" class="site-main sfpf-author-profile">';
        $html .= '<section class="sfpf-author-profile__hero"><img class="sfpf-author-profile__portrait" src="' . esc_url( $profile->avatar_url( 300 ) ) . '" alt="' . esc_attr( $profile->name() ) . '">';
        $html .= '<div><h1 class="sfpf-author-profile__name">' . esc_html( $profile->name() ) . '</h1>';
        $html .= '' !== $profile->title() ? '<p class="sfpf-author-profile__title">' . esc_html( $profile->title() ) . '</p>' : '';
        $html .= '' !== trim( wp_strip_all_tags( $profile->biography_short_html() ) ) ? '<div class="sfpf-author-profile__intro">' . wpautop( $profile->biography_short_html() ) . '</div>' : '';
        $html .= self::section( $profile, 'socials', '', [ 'layout' => 'inline' ] ) . '</div></section>';
        if ( [] !== $facts ) {
            $html .= '<dl class="sfpf-author-profile__facts">';
            foreach ( $facts as $label => $value ) {
                $html .= '<div><dt>' . esc_html( $label ) . '</dt><dd>' . $value . '</dd></div>';
            }
            $html .= '</dl>';
        }
        foreach ( [ 'biography' => 'Biography', 'education' => 'Education', 'articles' => 'Articles and Press', 'additional_urls' => 'More About ' . $profile->name() ] as $id => $heading ) {
            $html .= self::section( $profile, $id, $heading );
        }
        return $html . '</main>';
    }

    /** @param array<string,string> $atts */
    private static function section( PersonProfile $profile, string $id, string $heading, array $atts = [] ): string {
        $section = SectionRegistry::get( $id );
        if ( null === $section || ! $section->has_content( $profile ) ) {
            return '';
        }
        $layout = $atts['layout'] ?? (string) array_key_first( $section->layouts() );
        $body   = $section->render( $profile, $atts + [ 'layout' => $layout ] + $section->attributes() );
        $slug   = str_replace( '_', '-', $id );
        return '' === $body ? '' : '<section class="sfpf-section sfpf-' . esc_attr( $slug ) . ' sfpf-' . esc_attr( $slug ) . '--' . esc_attr( $layout ) . '">' . ( '' !== $heading ? '<h2 class="sfpf-section__heading">' . esc_html( $heading ) . '</h2>' : '' ) . $body . '</section>';
    }

    /** Whether an Elementor Pro archive template covers this request. */
    private static function elementor_template_covers(): bool {
        $module = '\ElementorPro\Modules\ThemeBuilder\Module';
        if ( ! class_exists( $module ) ) {
            return false;
        }
        try {
            return ! empty( $module::instance()->get_conditions_manager()->get_documents_for_location( 'archive' ) );
        } catch ( \Throwable $error ) {
            return false;
        }
    }
}
