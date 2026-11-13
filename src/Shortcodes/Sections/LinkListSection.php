<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Shortcodes\Sections;

use SFPF\PersonProfile\Person\Links;
use SFPF\PersonProfile\Person\PersonProfile;

defined( 'ABSPATH' ) || exit;

/**
 * A list of links with their titles and sources: the person's articles, or
 * the additional pages about them. Every link opens in a new tab.
 */
final class LinkListSection implements Section {
    /** @param \Closure(PersonProfile):list<array{title:string,source:string,url:string}> $rows */
    public function __construct( private readonly string $id, private readonly string $label, private readonly \Closure $rows ) {
    }

    public function id(): string {
        return $this->id;
    }

    public function label(): string {
        return $this->label;
    }

    public function layouts(): array {
        return [
            'titled'  => 'Titles with their source',
            'cards'   => 'A card per link with its address',
            'sources' => 'Grouped by source',
            'compact' => 'One line per link, source on the right',
        ];
    }

    public function attributes(): array {
        return [ 'limit' => '0' ];
    }

    public function has_content( PersonProfile $profile ): bool {
        return [] !== ( $this->rows )( $profile );
    }

    public function render( PersonProfile $profile, array $atts ): string {
        $rows  = ( $this->rows )( $profile );
        $limit = absint( $atts['limit'] ?? 0 );
        $rows  = $limit > 0 ? array_slice( $rows, 0, $limit ) : $rows;
        $layout = (string) ( $atts['layout'] ?? 'titled' );
        if ( 'sources' === $layout ) {
            $groups = [];
            foreach ( $rows as $row ) {
                $groups[ '' !== $row['source'] ? $row['source'] : 'Other' ][] = $row;
            }
            ksort( $groups );
            $html = '';
            foreach ( $groups as $source => $items ) {
                $html .= '<div class="sfpf-links__group"><div class="sfpf-links__group-name">' . esc_html( $source ) . ' <span>(' . count( $items ) . ')</span></div>' . self::list( $items, 'titled', false ) . '</div>';
            }
            return $html;
        }
        return self::list( $rows, $layout );
    }

    /**
     * @param list<array{title:string,source:string,url:string}> $rows
     * @param bool $sources False under a source's own heading, where the name would repeat.
     */
    private static function list( array $rows, string $layout, bool $sources = true ): string {
        $html = '<ul class="sfpf-links__list sfpf-links--' . esc_attr( $layout ) . ' founder-articles format-' . esc_attr( $layout ) . '">';
        foreach ( $rows as $row ) {
            $title = '' !== $row['title'] ? $row['title'] : Links::pretty( $row['url'] );
            $html .= '<li class="sfpf-links__item"><a class="sfpf-links__link article-link" href="' . esc_url( $row['url'] ) . '" target="_blank" rel="noopener">';
            $html .= 'titled' === $layout ? '<span class="sfpf-links__arrow article-arrow" aria-hidden="true">↗</span>' : '';
            $html .= '<span class="sfpf-links__title">' . esc_html( $title ) . '</span>';
            $source = $sources && '' !== $row['source'] ? '<span class="sfpf-links__source article-source-tag">' . esc_html( $row['source'] ) . '</span>' : '';
            if ( 'cards' === $layout ) {
                // A card keeps its source inside the link, above the address.
                $html .= $source . '<span class="sfpf-links__url">' . esc_html( Links::pretty( $row['url'] ) ) . '</span>';
                $source = '';
            }
            $html .= '</a>' . $source . '</li>';
        }
        return $html . '</ul>';
    }
}
