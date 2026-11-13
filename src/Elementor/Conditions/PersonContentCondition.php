<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Elementor\Conditions;

use SFPF\PersonProfile\ContentTypes\Book\BookContentType;
use SFPF\PersonProfile\ContentTypes\Organization\OrganizationContentType;
use SFPF\PersonProfile\Person\PersonResolver;
use SFPF\PersonProfile\Shortcodes\Sections\SectionRegistry;

defined( 'ABSPATH' ) || exit;

/**
 * Elementor Pro display condition "SFPF Person content": shows an element
 * only when a person section has content (or only when it is empty), so an
 * empty heading or container never shows. Loaded only with Elementor Pro.
 */
final class PersonContentCondition extends \ElementorPro\Modules\DisplayConditions\Conditions\Base\Condition_Base {
    /** Older source names saved in existing pages => section ids. */
    private const ALIASES = [ 'organizations_founded' => 'organizations' ];

    public function get_name() {
        return 'sfpf_content_available';
    }

    public function get_label() {
        return 'SFPF Person content';
    }

    public function get_group() {
        return 'sfpf';
    }

    public function check( $args ): bool {
        $source     = sanitize_key( (string) ( $args['source'] ?? '' ) );
        $has        = self::has_content( self::ALIASES[ $source ] ?? $source );
        $comparator = sanitize_key( (string) ( $args['comparator'] ?? 'is_not_empty' ) );
        return 'is_empty' === $comparator ? ! $has : $has;
    }

    public function get_options() {
        $sources = [];
        foreach ( SectionRegistry::all() as $section ) {
            $sources[ $section->id() ] = $section->label();
        }
        $sources['books'] = 'Published books';
        $this->add_control( 'source', [ 'type' => \Elementor\Controls_Manager::SELECT, 'label' => 'Content', 'options' => $sources, 'default' => 'education' ] );
        $this->add_control( 'comparator', [ 'type' => \Elementor\Controls_Manager::SELECT, 'label' => 'Status', 'options' => [ 'is_not_empty' => 'Has content', 'is_empty' => 'Is empty' ], 'default' => 'is_not_empty' ] );
    }

    public static function has_content( string $source ): bool {
        if ( 'books' === $source ) {
            return self::published( BookContentType::POST_TYPE );
        }
        if ( 'organizations' === $source && null === PersonResolver::profile() ) {
            return self::published( OrganizationContentType::POST_TYPE );
        }
        $section = SectionRegistry::get( $source );
        $profile = PersonResolver::profile();
        return null !== $section && null !== $profile && $section->has_content( $profile );
    }

    private static function published( string $post_type ): bool {
        return post_type_exists( $post_type ) && [] !== get_posts( [ 'post_type' => $post_type, 'post_status' => 'publish', 'posts_per_page' => 1, 'fields' => 'ids', 'no_found_rows' => true ] );
    }
}
