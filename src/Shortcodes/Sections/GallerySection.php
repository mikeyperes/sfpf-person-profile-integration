<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Shortcodes\Sections;

use SFPF\PersonProfile\Person\PersonProfile;

defined( 'ABSPATH' ) || exit;

/**
 * The person's public photos in a grid; each photo opens on its own.
 * source="knowledge_graph" shows the Knowledge Panel photos instead.
 */
final class GallerySection implements Section {
    public function id(): string {
        return 'gallery';
    }

    public function label(): string {
        return 'Photo gallery';
    }

    public function layouts(): array {
        return [ 'grid' => 'A grid of photos with captions' ];
    }

    public function attributes(): array {
        return [ 'columns' => '3', 'size' => 'large', 'limit' => '0', 'source' => 'photos' ];
    }

    public function has_content( PersonProfile $profile ): bool {
        return [] !== $profile->gallery() || [] !== $profile->knowledge_graph_images();
    }

    public function render( PersonProfile $profile, array $atts ): string {
        $size    = in_array( $atts['size'] ?? '', get_intermediate_image_sizes(), true ) ? (string) $atts['size'] : 'large';
        $images  = 'knowledge_graph' === ( $atts['source'] ?? '' ) ? $profile->knowledge_graph_images( $size ) : $profile->gallery( $size );
        $limit   = absint( $atts['limit'] ?? 0 );
        $images  = $limit > 0 ? array_slice( $images, 0, $limit ) : $images;
        return [] === $images ? '' : GalleryGrid::render( $images, absint( $atts['columns'] ?? 3 ), $profile->name() );
    }
}
