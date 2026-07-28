<?php
namespace sfpf_person_website;

/**
 * SFPF Elementor Pro display conditions.
 *
 * @package sfpf_person_website
 * @since 1.6.13
 */

defined('ABSPATH') || exit;

const SFPF_ELEMENTOR_DYNAMIC_VISIBILITY_OPTION = 'sfpf_elementor_dynamic_visibility';

/**
 * Determine whether SFPF Elementor display conditions are enabled.
 *
 * @return bool
 */
function should_enable_elementor_dynamic_visibility() {
    $value = get_option(SFPF_ELEMENTOR_DYNAMIC_VISIBILITY_OPTION, '1');
    return !in_array(strtolower((string) $value), ['0', 'false', 'off', 'no'], true);
}

/**
 * Register the SFPF display-condition group with Elementor Pro.
 *
 * @param mixed $manager Elementor Pro conditions manager.
 * @return void
 */
function register_sfpf_elementor_display_condition_group($manager) {
    if (!should_enable_elementor_dynamic_visibility() || !is_object($manager) || !method_exists($manager, 'add_group')) {
        return;
    }

    $manager->add_group('sfpf', [
        'label' => esc_html__('SFPF', 'sfpf-person-profile-integration'),
    ]);
}
add_action('elementor/display_conditions/register_groups', __NAMESPACE__ . '\\register_sfpf_elementor_display_condition_group');

/**
 * Register the SFPF content availability condition.
 *
 * The class is declared only after Elementor Pro's base condition is loaded.
 *
 * @param mixed $manager Elementor Pro conditions manager.
 * @return void
 */
function register_sfpf_elementor_display_conditions($manager) {
    if (!should_enable_elementor_dynamic_visibility() || !is_object($manager) || !method_exists($manager, 'register_condition_instance')) {
        return;
    }

    if (!class_exists('\\ElementorPro\\Modules\\DisplayConditions\\Conditions\\Base\\Condition_Base') || !class_exists('\\ElementorPro\\Core\\Isolation\\Wordpress_Adapter')) {
        return;
    }

    if (!class_exists(__NAMESPACE__ . '\\SFPF_Elementor_Content_Condition', false)) {
        class SFPF_Elementor_Content_Condition extends \ElementorPro\Modules\DisplayConditions\Conditions\Base\Condition_Base {
            public function get_name() {
                return 'sfpf_content_available';
            }

            public function get_label() {
                return esc_html__('SFPF Content', 'sfpf-person-profile-integration');
            }

            public function get_group() {
                return 'sfpf';
            }

            public function check($args): bool {
                $source = isset($args['source']) ? sanitize_key((string) $args['source']) : '';
                $comparator = isset($args['comparator']) ? sanitize_key((string) $args['comparator']) : 'is_not_empty';
                $has_content = sfpf_elementor_dynamic_source_has_content($source);

                return $comparator === 'is_empty' ? !$has_content : $has_content;
            }

            public function get_options() {
                $this->add_control(
                    'source',
                    [
                        'type' => \Elementor\Controls_Manager::SELECT,
                        'label' => esc_html__('Content source', 'sfpf-person-profile-integration'),
                        'options' => [
                            'education' => esc_html__('Founder education', 'sfpf-person-profile-integration'),
                            'articles' => esc_html__('Founder articles', 'sfpf-person-profile-integration'),
                            'additional_urls' => esc_html__('Founder additional URLs', 'sfpf-person-profile-integration'),
                            'faq' => esc_html__('Founder FAQs', 'sfpf-person-profile-integration'),
                            'organizations_founded' => esc_html__('Published organizations', 'sfpf-person-profile-integration'),
                            'books' => esc_html__('Published books', 'sfpf-person-profile-integration'),
                        ],
                        'default' => 'education',
                    ]
                );

                $this->add_control(
                    'comparator',
                    [
                        'type' => \Elementor\Controls_Manager::SELECT,
                        'label' => esc_html__('Status', 'sfpf-person-profile-integration'),
                        'options' => [
                            'is_not_empty' => esc_html__('Is not empty', 'sfpf-person-profile-integration'),
                            'is_empty' => esc_html__('Is empty', 'sfpf-person-profile-integration'),
                        ],
                        'default' => 'is_not_empty',
                    ]
                );
            }
        }
    }

    $manager->register_condition_instance(new SFPF_Elementor_Content_Condition([
        new \ElementorPro\Core\Isolation\Wordpress_Adapter(),
    ]));
}
add_action('elementor/display_conditions/register', __NAMESPACE__ . '\\register_sfpf_elementor_display_conditions');

/**
 * Determine whether an SFPF source has public content.
 *
 * @param string $source Source key.
 * @return bool
 */
function sfpf_elementor_dynamic_source_has_content($source) {
    switch (sanitize_key((string) $source)) {
        case 'education':
            return sfpf_founder_has_public_education();
        case 'articles':
            return sfpf_founder_has_public_articles();
        case 'additional_urls':
            return sfpf_founder_has_public_additional_urls();
        case 'faq':
            return sfpf_founder_has_public_faq();
        case 'organizations_founded':
            return sfpf_has_published_posts('organization');
        case 'books':
            return sfpf_has_published_posts('book');
        default:
            return false;
    }
}

/**
 * Check if the selected founder user has at least one usable education row.
 *
 * @return bool
 */
function sfpf_founder_has_public_education() {
    $user_id = function_exists(__NAMESPACE__ . '\\get_founder_user_id') ? get_founder_user_id() : 0;
    if (!$user_id) {
        return false;
    }

    $education = function_exists('get_field') ? get_field('education', 'user_' . $user_id) : [];
    if (empty($education)) {
        $education = get_user_meta($user_id, 'education', true);
    }

    return sfpf_repeater_has_public_row($education, ['college', 'designation', 'major', 'year', 'wiki_url']);
}

/**
 * Check if the selected founder user has at least one usable article URL.
 *
 * @return bool
 */
function sfpf_founder_has_public_articles() {
    return sfpf_founder_has_public_link_repeater('articles');
}

/**
 * Check if the selected founder user has at least one usable additional URL.
 *
 * @return bool
 */
function sfpf_founder_has_public_additional_urls() {
    return sfpf_founder_has_public_link_repeater('additional_urls');
}

/**
 * Check a supported founder link repeater for a public URL.
 *
 * @param string $field_name ACF field name.
 * @return bool
 */
function sfpf_founder_has_public_link_repeater($field_name) {
    $user_id = function_exists(__NAMESPACE__ . '\\get_founder_user_id') ? get_founder_user_id() : 0;
    if (!$user_id) {
        return false;
    }

    $field_name = sanitize_key((string) $field_name);
    if (!in_array($field_name, ['articles', 'additional_urls'], true)) {
        return false;
    }

    $links = function_exists('get_field') ? get_field($field_name, 'user_' . $user_id) : [];
    if (empty($links)) {
        $links = get_user_meta($user_id, $field_name, true);
    }

    if (is_string($links)) {
        foreach (preg_split('/\\R/', $links) ?: [] as $url) {
            if (filter_var(trim($url), FILTER_VALIDATE_URL)) {
                return true;
            }
        }

        return false;
    }

    if (!is_array($links)) {
        return false;
    }

    foreach ($links as $link) {
        if (is_array($link) && filter_var(trim((string) ($link['url'] ?? '')), FILTER_VALIDATE_URL)) {
            return true;
        }
    }

    return false;
}

/**
 * Check if the selected founder user has at least one complete person FAQ.
 *
 * @return bool
 */
function sfpf_founder_has_public_faq() {
    $user_id = function_exists(__NAMESPACE__ . '\\get_founder_user_id') ? get_founder_user_id() : 0;
    if (!$user_id) {
        return false;
    }

    if (function_exists(__NAMESPACE__ . '\\sfpf_faq_source_resolver')) {
        return [] !== sfpf_faq_source_resolver()->acf('user_' . $user_id, 'faq');
    }

    $faqs = function_exists('get_field') ? get_field('faq', 'user_' . $user_id) : [];
    if (empty($faqs)) {
        $faqs = get_user_meta($user_id, 'faq', true);
    }

    if (!is_array($faqs)) {
        return false;
    }

    foreach ($faqs as $faq) {
        if (!is_array($faq)) {
            continue;
        }

        if (trim((string) ($faq['question'] ?? '')) !== '' && trim((string) ($faq['answer'] ?? '')) !== '') {
            return true;
        }
    }

    return false;
}

/**
 * Check if a repeater-like value contains at least one public row.
 *
 * @param mixed $rows Repeater data.
 * @param array $keys Row keys to inspect.
 * @return bool
 */
function sfpf_repeater_has_public_row($rows, array $keys) {
    if (!is_array($rows)) {
        return is_string($rows) && trim($rows) !== '' && trim($rows) !== '0';
    }

    foreach ($rows as $row) {
        if (!is_array($row)) {
            if (is_scalar($row) && trim((string) $row) !== '') {
                return true;
            }
            continue;
        }

        foreach ($keys as $key) {
            if (isset($row[$key]) && is_scalar($row[$key]) && trim((string) $row[$key]) !== '') {
                return true;
            }
        }
    }

    return false;
}

/**
 * Check whether a post type has at least one published post.
 *
 * @param string $post_type Post type.
 * @return bool
 */
function sfpf_has_published_posts($post_type) {
    $query = new \WP_Query([
        'post_type' => sanitize_key((string) $post_type),
        'post_status' => 'publish',
        'posts_per_page' => 1,
        'fields' => 'ids',
        'no_found_rows' => true,
    ]);

    return $query->have_posts();
}
