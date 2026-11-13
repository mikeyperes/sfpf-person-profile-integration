<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Shortcodes\Sections;

use SFPF\PersonProfile\Faq\FaqSets;
use SFPF\PersonProfile\Person\PersonProfile;

defined( 'ABSPATH' ) || exit;

/**
 * The person's questions and answers. Their FAQ schema is added to the page's
 * schema block automatically; nothing is printed inside the page body.
 */
final class FaqSection implements Section {
    public function id(): string {
        return 'faq';
    }

    public function label(): string {
        return 'FAQ';
    }

    public function layouts(): array {
        return [ 'accordion' => 'Questions that open to show the answer', 'list' => 'Every answer shown' ];
    }

    public function attributes(): array {
        return [ 'first_open' => 'false' ];
    }

    public function has_content( PersonProfile $profile ): bool {
        return [] !== FaqSets::person_items();
    }

    public function render( PersonProfile $profile, array $atts ): string {
        return self::items( FaqSets::person_items(), (string) ( $atts['layout'] ?? 'accordion' ), in_array( strtolower( (string) ( $atts['first_open'] ?? '' ) ), [ '1', 'true', 'yes' ], true ) );
    }

    /**
     * FAQ items as an accordion or a list; also used for FAQ sets.
     *
     * @param list<array{question:string,answer:string}> $items
     */
    public static function items( array $items, string $layout, bool $first_open = false ): string {
        $manager = FaqSets::manager();
        $html    = '<div class="sfpf-faq__items sfpf-faq__items--' . esc_attr( 'list' === $layout ? 'list' : 'accordion' ) . '">';
        foreach ( $items as $index => $item ) {
            if ( 'list' === $layout ) {
                $html .= '<div class="sfpf-faq__item"><h3 class="sfpf-faq__question">' . esc_html( $item['question'] ) . '</h3><div class="sfpf-faq__answer">' . $manager->answerHtml( $item['answer'] ) . '</div></div>';
                continue;
            }
            $html .= '<details class="sfpf-faq__item"' . ( 0 === $index && $first_open ? ' open' : '' ) . '><summary class="sfpf-faq__question">' . esc_html( $item['question'] ) . '</summary><div class="sfpf-faq__answer">' . $manager->answerHtml( $item['answer'] ) . '</div></details>';
        }
        return $html . '</div>';
    }
}
