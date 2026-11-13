<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Shortcodes\Sections;

defined( 'ABSPATH' ) || exit;

/** A grid of images, each opening on its own, used by every gallery. */
final class GalleryGrid {
    /** @param list<array<string,mixed>> $images Image records (url, full_url, alt, title, caption). */
    public static function render( array $images, int $columns, string $fallback_alt ): string {
        $columns = max( 1, min( 6, $columns ) );
        $html    = '<div class="sfpf-gallery__grid sfpf-founder-gallery" style="--sfpf-gallery-columns:' . $columns . '" data-sfpf-gallery-count="' . count( $images ) . '">';
        foreach ( $images as $image ) {
            $alt     = (string) ( ( $image['alt'] ?? '' ) ?: ( $image['title'] ?? '' ) ?: $fallback_alt );
            $caption = trim( (string) ( $image['caption'] ?? '' ) );
            $html   .= '<figure class="sfpf-gallery__item sfpf-gallery-item"><a href="' . esc_url( (string) ( ( $image['full_url'] ?? '' ) ?: $image['url'] ) ) . '" target="_blank" rel="noopener">';
            $html   .= '<img src="' . esc_url( (string) $image['url'] ) . '" alt="' . esc_attr( $alt ) . '" loading="lazy" decoding="async"></a>';
            $html   .= '' !== $caption ? '<figcaption>' . esc_html( $caption ) . '</figcaption>' : '';
            $html   .= '</figure>';
        }
        return $html . '</div>';
    }
}
