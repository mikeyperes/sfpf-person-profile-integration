<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Admin;

use Hexa\PluginCore\WpAdminComponents\CoreUi;

defined( 'ABSPATH' ) || exit;

/**
 * Small admin building blocks the tabs share, on top of Hexa WP Core's UI:
 * a card, a form that saves through admin-ajax without reloading, and a
 * copyable shortcode.
 */
final class AdminUi {
    public const NONCE = 'sfpf_admin';

    public static function card( string $title, string $body, string $intro = '' ): string {
        return CoreUi::card( [ 'title' => $title, 'body_html' => ( '' !== $intro ? '<p>' . esc_html( $intro ) . '</p>' : '' ) . $body ] );
    }

    /**
     * A form that posts its fields to an admin-ajax action and shows the
     * result next to its button (assets/admin/dashboard.js).
     */
    public static function form( string $action, string $fields_html, string $button = 'Save' ): string {
        return '<form class="sfpf-ajax-form" data-sfpf-action="' . esc_attr( $action ) . '">' . $fields_html
            . '<p class="sfpf-form-actions"><button type="submit" class="button button-primary">' . esc_html( $button ) . '</button> <span class="sfpf-form-status" role="status" aria-live="polite"></span></p></form>';
    }

    public static function shortcode( string $code ): string {
        return '<code class="sfpf-shortcode" title="Click to copy" tabindex="0">' . esc_html( $code ) . '</code>';
    }

    public static function toggle( string $name, bool $checked, string $label, string $description = '' ): string {
        return '<p class="sfpf-setting"><label><input type="checkbox" name="' . esc_attr( $name ) . '" value="1"' . checked( $checked, true, false ) . '> <strong>' . esc_html( $label ) . '</strong></label>'
            . ( '' !== $description ? '<br><span class="description">' . esc_html( $description ) . '</span>' : '' ) . '</p>';
    }

    public static function link_button( string $url, string $label, bool $new_tab = true ): string {
        return '<a class="button" href="' . esc_url( $url ) . '"' . ( $new_tab ? ' target="_blank" rel="noopener"' : '' ) . '>' . esc_html( $label ) . '</a>';
    }
}
