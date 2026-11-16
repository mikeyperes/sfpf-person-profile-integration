<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Shortcodes\Sections;

use SFPF\PersonProfile\Person\PersonProfile;
use SFPF\PersonProfile\PressReleases\PressReleaseSettings;
use SFPF\PersonProfile\PressReleases\PressReleaseSource;

defined( 'ABSPATH' ) || exit;

/** A gated release list with the canonical author or person profile link. */
final class PressReleaseSection implements Section {
    private readonly LinkListSection $links;

    public function __construct( private readonly string $mode ) {
        $this->links = new LinkListSection(
            'press_releases_' . $mode,
            'Press releases ' . strtolower( PressReleaseSettings::MODES[ $mode ] ),
            fn( PersonProfile $person ): array => PressReleaseSource::rows( $person, $this->mode )
        );
    }

    public function id(): string {
        return $this->links->id();
    }

    public function label(): string {
        return $this->links->label();
    }

    public function layouts(): array {
        return $this->links->layouts();
    }

    public function attributes(): array {
        return $this->links->attributes();
    }

    public function has_content( PersonProfile $profile ): bool {
        return $this->links->has_content( $profile );
    }

    public function render( PersonProfile $profile, array $atts ): string {
        if ( ! $this->has_content( $profile ) ) {
            return '';
        }
        $settings = PressReleaseSettings::current();
        $prefix = 'by' === $this->mode ? 'author' : 'profile';
        $url = (string) ( $settings[ $prefix . '_url' ] ?? '' );
        $name = (string) ( $settings[ $prefix . '_name' ] ?? '' );
        $link = '' !== $url ? '<p class="sfpf-press-releases__profile"><a href="' . esc_url( $url ) . '" target="_blank" rel="noopener">' . esc_html( $name ) . '</a></p>' : '';
        return $link . $this->links->render( $profile, $atts );
    }
}
