<?php
namespace sfpf_person_website;

/**
 * Elementor social icon compatibility fixes.
 *
 * @package sfpf_person_website
 * @since 1.6.12
 */

defined('ABSPATH') || exit;

const SFPF_HIDE_EMPTY_ELEMENTOR_SOCIAL_ICONS_OPTION = 'sfpf_hide_empty_elementor_social_icons';

/**
 * Determine whether empty Elementor social icons should be removed server-side.
 *
 * @return bool
 */
function should_hide_empty_elementor_social_icons() {
    $value = get_option(SFPF_HIDE_EMPTY_ELEMENTOR_SOCIAL_ICONS_OPTION, '1');
    return !in_array(strtolower((string) $value), ['0', 'false', 'off', 'no'], true);
}

/**
 * Remove invalid social icon items from Elementor's rendered Social Icons widget.
 *
 * @param string $content Rendered widget HTML.
 * @param mixed  $widget Elementor widget instance.
 * @return string
 */
function filter_empty_elementor_social_icons($content, $widget = null) {
    if (!should_hide_empty_elementor_social_icons() || trim((string) $content) === '') {
        return $content;
    }

    $widget_name = is_object($widget) && method_exists($widget, 'get_name') ? (string) $widget->get_name() : '';
    if ($widget_name !== 'social-icons' && strpos((string) $content, 'elementor-social-icon') === false) {
        return $content;
    }

    if (!class_exists('\DOMDocument') || !class_exists('\DOMXPath')) {
        return $content;
    }

    $previous_errors = libxml_use_internal_errors(true);
    $dom = new \DOMDocument('1.0', 'UTF-8');
    $loaded = $dom->loadHTML(
        '<?xml encoding="UTF-8"><div id="sfpf-social-icon-filter-root">' . $content . '</div>',
        LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
    );
    libxml_clear_errors();
    libxml_use_internal_errors($previous_errors);

    if (!$loaded) {
        return $content;
    }

    $xpath = new \DOMXPath($dom);
    $root = $xpath->query('//*[@id="sfpf-social-icon-filter-root"]')->item(0);
    if (!$root instanceof \DOMNode) {
        return $content;
    }

    $anchors = $xpath->query('.//a[contains(concat(" ", normalize-space(@class), " "), " elementor-social-icon ")]', $root);
    if (!$anchors || $anchors->length === 0) {
        return $content;
    }

    $nodes_to_remove = [];
    foreach ($anchors as $anchor) {
        if (!$anchor instanceof \DOMElement || !is_empty_elementor_social_icon_anchor($anchor)) {
            continue;
        }

        $nodes_to_remove[] = find_elementor_social_icon_removal_node($anchor, $root);
    }

    foreach ($nodes_to_remove as $node) {
        if ($node instanceof \DOMNode && $node->parentNode) {
            $node->parentNode->removeChild($node);
        }
    }

    $remaining_anchors = $xpath->query('.//a[contains(concat(" ", normalize-space(@class), " "), " elementor-social-icon ")]', $root);
    if (!$remaining_anchors || $remaining_anchors->length === 0) {
        return '';
    }

    return get_dom_node_inner_html($dom, $root);
}
add_filter('elementor/widget/render_content', __NAMESPACE__ . '\\filter_empty_elementor_social_icons', 20, 2);

/**
 * Check whether an Elementor social icon anchor has a usable destination.
 *
 * @param \DOMElement $anchor Anchor element.
 * @return bool
 */
function is_empty_elementor_social_icon_anchor(\DOMElement $anchor) {
    if (!$anchor->hasAttribute('href')) {
        return true;
    }

    $href = html_entity_decode(trim((string) $anchor->getAttribute('href')), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    if ($href === '' || $href === '#') {
        return true;
    }

    $decoded_href = rawurldecode($href);
    if ((strpos($decoded_href, '[') !== false && strpos($decoded_href, ']') !== false) || strpos($decoded_href, '{{') !== false) {
        return true;
    }

    if (preg_match('/^mailto:\s*(?:\?.*)?$/i', $href)) {
        return true;
    }

    if (preg_match('/^tel:\s*$/i', $href)) {
        return true;
    }

    if (stripos($href, 'tel:') === 0) {
        $number = preg_replace('/[^0-9+]/', '', substr($href, 4));
        if ($number === '' || $number === '+') {
            return true;
        }
    }

    return false;
}

/**
 * Find the Elementor grid item wrapper that should be removed for an invalid icon.
 *
 * @param \DOMElement $anchor Anchor element.
 * @param \DOMNode    $root Root wrapper node.
 * @return \DOMNode
 */
function find_elementor_social_icon_removal_node(\DOMElement $anchor, \DOMNode $root) {
    $node = $anchor;
    while ($node instanceof \DOMNode && $node !== $root) {
        if ($node instanceof \DOMElement && has_dom_class($node, 'elementor-grid-item')) {
            return $node;
        }
        $node = $node->parentNode;
    }

    return $anchor;
}

/**
 * Check whether a DOM element has a class.
 *
 * @param \DOMElement $element Element.
 * @param string      $class Class name.
 * @return bool
 */
function has_dom_class(\DOMElement $element, $class) {
    $classes = preg_split('/\s+/', trim((string) $element->getAttribute('class')));
    return in_array($class, $classes ?: [], true);
}

/**
 * Get a DOM node's inner HTML.
 *
 * @param \DOMDocument $dom DOM document.
 * @param \DOMNode     $node Node.
 * @return string
 */
function get_dom_node_inner_html(\DOMDocument $dom, \DOMNode $node) {
    $html = '';
    foreach ($node->childNodes as $child) {
        $html .= $dom->saveHTML($child);
    }

    return $html;
}
