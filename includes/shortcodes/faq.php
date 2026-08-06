<?php

declare( strict_types=1 );

namespace sfpf_person_website;

/**
 * FAQ set, person FAQ, schema, and Elementor FAQ shortcodes.
 *
 * Callback names remain in the legacy namespace for template and hook compatibility.
 */

defined( 'ABSPATH' ) || exit;

// ============================================================================
// FAQ SHORTCODES (Sets-based structure)
// ============================================================================

/**
 * Get FAQ set by slug
 */
function sfpf_faq_manager() {
    static $manager = null;

    if (null === $manager) {
        $manager = new \Hexa\PluginCore\FaqSets\FaqSetManager();
    }

    return $manager;
}

function sfpf_faq_source_resolver() {
    static $resolver = null;
    if (null === $resolver) {
        $resolver = new \Hexa\PluginCore\FaqSets\FaqSourceResolver(sfpf_faq_manager());
    }
    return $resolver;
}

function get_faq_set_by_slug($slug) {
    $faq_sets = get_option("sfpf_faq_sets", []);

    return sfpf_faq_manager()->resolveSet(
        is_array($faq_sets) ? $faq_sets : [],
        (string) $slug,
        (string) get_option("sfpf_primary_faq_set", "")
    );
}

function sfpf_faq_answer_html($answer) {
    return sfpf_faq_manager()->answerHtml((string) $answer);
}

function sfpf_normalize_faq_items($items) {
    return sfpf_faq_manager()->normalizeItems($items);
}

/**
 * FAQ shortcode
 * [sfpf_faq set="slug"] - All FAQs from a set
 * [sfpf_faq set="slug" index="0"] - Single FAQ from a set
 * [sfpf_faq set="slug" style="accordion"] - Accordion style
 */
function sfpf_faq_shortcode($atts) {
    $atts = shortcode_atts([
        'set' => '',
        'index' => null,
        'style' => 'list', // list, accordion
    ], $atts);

    if (empty($atts['set'])) {
        return '<!-- SFPF FAQ: No set specified -->';
    }

    $set = get_faq_set_by_slug($atts['set']);
    if (!$set || empty($set['items'])) {
        return '<!-- SFPF FAQ: Set not found or empty -->';
    }

    $items = $set['items'];

    // Single item
    if ($atts['index'] !== null) {
        $index = intval($atts['index']);
        if (!isset($items[$index])) {
            return '';
        }
        $faq = $items[$index];
        return '<div class="sfpf-faq-single" data-set="' . esc_attr($atts['set']) . '" data-index="' . $index . '">
            <div class="sfpf-faq-question" style="font-weight:600;font-size:16px;margin-bottom:8px;">' . esc_html($faq['question']) . '</div>
            <div class="sfpf-faq-answer">' . sfpf_faq_answer_html($faq['answer']) . '</div>
        </div>';
    }

    // Multiple items
    if ($atts['style'] === 'accordion') {
        return render_faq_accordion($set, $items);
    }

    // Default list style - collapsible, all closed on load
    $html = '<div class="sfpf-faq-list" data-set="' . esc_attr($atts['set']) . '">';
    foreach ($items as $i => $faq) {
        if (!empty($faq['question'])) {
            $html .= '<div class="sfpf-faq-item" style="margin-bottom:12px;background:#f9fafb;border-radius:8px;border:1px solid #e5e7eb;overflow:hidden;">';
            $html .= '<div role="button" tabindex="0" class="sfpf-faq-toggle" style="width:100%;padding:16px 20px;background:transparent;border:none;text-align:left;cursor:pointer;display:flex !important;justify-content:space-between;align-items:center;box-sizing:border-box;-webkit-appearance:none;appearance:none;font-family:inherit;line-height:1.4;" onclick="var c=this.nextElementSibling;var icon=this.querySelector(\'.sfpf-toggle-icon\');if(c.style.display===\'none\'||c.style.display===\'\'){c.style.display=\'block\';icon.textContent=\'−\';this.parentElement.classList.add(\'open\');}else{c.style.display=\'none\';icon.textContent=\'+\';this.parentElement.classList.remove(\'open\');}">';
            $html .= '<span style="font-weight:600;font-size:16px;color:#1e1e1e !important;display:block;">' . esc_html($faq['question']) . '</span>';
            $html .= '<span class="sfpf-toggle-icon" style="font-size:20px;color:#6b7280;flex-shrink:0;margin-left:12px;">+</span>';
            $html .= '</div>';
            $html .= '<div class="sfpf-faq-answer" style="display:none;padding:0 20px 16px;color:#4b5563;line-height:1.6;">' . sfpf_faq_answer_html($faq['answer']) . '</div>';
            $html .= '</div>';
        }
    }
    $html .= '</div>';

    // Inject schema if enabled
    if (get_option('sfpf_inject_faq_schema', true)) {
        $html .= render_faq_schema($items);
    }

    return $html;
}

/**
 * Render FAQ as accordion
 */
function render_faq_accordion($set, $items) {
    $set = is_array($set) ? $set : [];
    $set["items"] = $items;

    return sfpf_faq_manager()->renderFaqs($set, [
        "style" => "accordion",
        "inject_schema" => (bool) get_option("sfpf_inject_faq_schema", true),
    ]);
}


/**
 * Render FAQ schema
 */
function render_faq_schema($items) {
    return sfpf_faq_manager()->renderSchemaScript($items);
}


/**
 * FAQ Schema only shortcode
 * [sfpf_faq_schema set="slug"]
 */
function sfpf_faq_schema_shortcode($atts) {
    $atts = shortcode_atts(['set' => ''], $atts);

    if (empty($atts['set'])) {
        return '';
    }

    $set = get_faq_set_by_slug($atts['set']);
    if (!$set || empty($set['items'])) {
        return '';
    }

    return render_faq_schema($set['items']);
}

function founder_display_faq($user_id, $atts = []) {
    $items = sfpf_faq_source_resolver()->acf("user_" . (int) $user_id, "faq");
    $format = strtolower((string) ($atts["format"] ?? "accordion"));
    $style = strtolower((string) ($atts["style"] ?? $format));

    if ($format === "json") {
        return wp_json_encode($items, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    if ($format === "count") {
        return (string) count($items);
    }

    if ($items === []) {
        return "";
    }

    if (!in_array($style, ["accordion", "list"], true)) {
        $style = "accordion";
    }

    return sfpf_faq_manager()->renderFaqs(
        [
            "slug" => "person-faq-" . (int) $user_id,
            "name" => "Person FAQ",
            "items" => $items,
        ],
        [
            "style" => $style,
            "inject_schema" => (bool) get_option("sfpf_inject_faq_schema", true),
        ]
    );
}


function sfpf_person_faq_shortcode($atts) {
    $atts = shortcode_atts([
        'format' => 'accordion',
        'style' => 'accordion',
    ], $atts);

    $user_id = get_founder_user_id();
    if (!$user_id) {
        return '';
    }

    return founder_display_faq($user_id, $atts);
}

/**
 * Elementor FAQ integration shortcode
 * [sfpf_elementor_faq set="slug" target=".elementor-accordion"]
 *
 * Injects JavaScript that populates Elementor accordion widgets with FAQ content
 */
function sfpf_elementor_faq_shortcode($atts) {
    $atts = shortcode_atts([
        'set' => '',
        'target' => '.elementor-accordion',
    ], $atts);

    if (empty($atts['set'])) {
        return '<!-- SFPF Elementor FAQ: No set specified -->';
    }

    $set = get_faq_set_by_slug($atts['set']);
    if (!$set || empty($set['items'])) {
        return '<!-- SFPF Elementor FAQ: Set not found or empty -->';
    }

    $items = $set['items'];
    $target = esc_js($atts['target']);

    // Prepare FAQ data for JavaScript
    $faq_data = [];
    foreach ($items as $faq) {
        if (!empty($faq['question'])) {
            $faq_data[] = [
                'question' => $faq['question'],
                'answer' => $faq['answer'],
            ];
        }
    }

    $json_data = wp_json_encode($faq_data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    $html = '<script>
(function() {
    var faqData = ' . $json_data . ';
    var targetSelector = "' . $target . '";

    function populateElementorAccordion() {
        var accordion = null;
        var el = document.querySelector(targetSelector);

        if (!el) {
            console.log("SFPF FAQ: Element not found with selector:", targetSelector);
            return;
        }

        // Check if this element IS the accordion
        if (el.classList.contains("elementor-accordion")) {
            accordion = el;
        }
        // Check if the accordion is inside this element (user added class to widget wrapper)
        if (!accordion) {
            accordion = el.querySelector(".elementor-accordion");
        }
        // Check if this element has accordion items directly
        if (!accordion && el.querySelector(".elementor-accordion-item")) {
            accordion = el;
        }

        if (!accordion) {
            console.log("SFPF FAQ: No .elementor-accordion found in or at:", targetSelector);
            return;
        }

        var items = accordion.querySelectorAll(".elementor-accordion-item");

        faqData.forEach(function(faq, index) {
            if (items[index]) {
                var title = items[index].querySelector(".elementor-accordion-title");
                if (title) {
                    title.textContent = faq.question;
                }
                var content = items[index].querySelector(".elementor-tab-content");
                if (content) {
                    content.innerHTML = faq.answer;
                }
            }
        });

        console.log("SFPF FAQ: Populated " + Math.min(faqData.length, items.length) + " accordion items");
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", populateElementorAccordion);
    } else {
        setTimeout(populateElementorAccordion, 100);
    }

    if (typeof jQuery !== "undefined") {
        jQuery(window).on("elementor/frontend/init", function() {
            setTimeout(populateElementorAccordion, 500);
        });
    }
})();
</script>';

    // Inject schema
    if (get_option('sfpf_inject_faq_schema', true)) {
        $html .= render_faq_schema($items);
    }

    return $html;
}
