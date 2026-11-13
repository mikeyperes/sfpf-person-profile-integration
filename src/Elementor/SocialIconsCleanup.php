<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Elementor;

defined( 'ABSPATH' ) || exit;

/**
 * Removes icons with no real link from Elementor Social Icons widgets before
 * the page is sent: an empty link, "#", a shortcode or dynamic tag that
 * printed nothing, an empty mailto: or tel:. A widget left with no icons
 * prints nothing.
 */
final class SocialIconsCleanup {
    public static function filter( mixed $content, mixed $widget = null ): mixed {
        if ( ! is_string( $content ) || '' === trim( $content ) || ! class_exists( '\DOMDocument' ) ) {
            return $content;
        }
        $name = is_object( $widget ) && method_exists( $widget, 'get_name' ) ? (string) $widget->get_name() : '';
        if ( 'social-icons' !== $name && ! str_contains( $content, 'elementor-social-icon' ) ) {
            return $content;
        }
        $errors = libxml_use_internal_errors( true );
        $dom    = new \DOMDocument( '1.0', 'UTF-8' );
        $loaded = $dom->loadHTML( '<?xml encoding="UTF-8"><div id="sfpf-social-root">' . $content . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
        libxml_clear_errors();
        libxml_use_internal_errors( $errors );
        if ( ! $loaded ) {
            return $content;
        }
        $xpath   = new \DOMXPath( $dom );
        $root    = $xpath->query( '//*[@id="sfpf-social-root"]' )->item( 0 );
        $anchors = $root ? $xpath->query( './/a[contains(concat(" ", normalize-space(@class), " "), " elementor-social-icon ")]', $root ) : false;
        if ( ! $root || ! $anchors || 0 === $anchors->length ) {
            return $content;
        }
        $removed = 0;
        foreach ( iterator_to_array( $anchors ) as $anchor ) {
            if ( $anchor instanceof \DOMElement && self::empty_link( $anchor ) ) {
                $node = self::grid_item( $anchor, $root );
                $node->parentNode?->removeChild( $node );
                $removed++;
            }
        }
        if ( 0 === $removed ) {
            return $content;
        }
        if ( $removed === $anchors->length ) {
            return '';
        }
        $html = '';
        foreach ( $root->childNodes as $child ) {
            $html .= $dom->saveHTML( $child );
        }
        return $html;
    }

    private static function empty_link( \DOMElement $anchor ): bool {
        $href = html_entity_decode( trim( $anchor->getAttribute( 'href' ) ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
        if ( '' === $href || '#' === $href ) {
            return true;
        }
        $decoded = rawurldecode( $href );
        if ( ( str_contains( $decoded, '[' ) && str_contains( $decoded, ']' ) ) || str_contains( $decoded, '{{' ) ) {
            return true;
        }
        if ( preg_match( '/^mailto:\s*(\?.*)?$/i', $href ) ) {
            return true;
        }
        return 0 === stripos( $href, 'tel:' ) && in_array( preg_replace( '/[^0-9+]/', '', substr( $href, 4 ) ), [ '', '+' ], true );
    }

    /** The grid item wrapping an icon, so the whole slot is removed. */
    private static function grid_item( \DOMElement $anchor, \DOMNode $root ): \DOMNode {
        for ( $node = $anchor; $node instanceof \DOMNode && $node !== $root; $node = $node->parentNode ) {
            if ( $node instanceof \DOMElement && in_array( 'elementor-grid-item', preg_split( '/\s+/', $node->getAttribute( 'class' ) ) ?: [], true ) ) {
                return $node;
            }
        }
        return $anchor;
    }
}
