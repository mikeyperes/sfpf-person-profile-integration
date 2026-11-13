<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Shortcodes;

use SFPF\PersonProfile\Faq\FaqSets;

defined( 'ABSPATH' ) || exit;

/**
 * [sfpf_elementor_faq set="main" target=".elementor-accordion"] (older)
 *
 * Writes an FAQ set's questions and answers into an Elementor Accordion
 * widget already on the page, in its order. Kept for pages built with it;
 * new pages use [faq_set] or [person_section id="faq"].
 */
final class ElementorFaqShortcode {
    public const TAG = 'sfpf_elementor_faq';

    /** @param array<string,string>|string $atts */
    public static function render( mixed $atts ): string {
        $atts  = Attributes::parse( $atts, [ 'set' => '', 'target' => '.elementor-accordion' ], self::TAG );
        $items = FaqSets::set_items( $atts['set'] );
        if ( [] === $items ) {
            return '';
        }
        $answers = array_map( static fn( array $item ): array => [ 'question' => $item['question'], 'answer' => FaqSets::manager()->answerHtml( $item['answer'] ) ], $items );
        return '<script>(function(){var faq=' . wp_json_encode( $answers, JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE ) . ',selector=' . wp_json_encode( $atts['target'], JSON_HEX_TAG ) . ';'
            . 'function fill(){var el=document.querySelector(selector);if(!el)return;var acc=el.classList.contains("elementor-accordion")?el:(el.querySelector(".elementor-accordion")||el);'
            . 'var items=acc.querySelectorAll(".elementor-accordion-item");faq.forEach(function(f,i){if(!items[i])return;var t=items[i].querySelector(".elementor-accordion-title"),c=items[i].querySelector(".elementor-tab-content");if(t)t.textContent=f.question;if(c)c.innerHTML=f.answer;});}'
            . 'if(document.readyState==="loading"){document.addEventListener("DOMContentLoaded",fill);}else{fill();}'
            . 'if(window.jQuery){window.jQuery(window).on("elementor/frontend/init",function(){setTimeout(fill,300);});}})();</script>';
    }
}
