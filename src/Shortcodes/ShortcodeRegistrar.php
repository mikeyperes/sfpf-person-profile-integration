<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Shortcodes;

use Hexa\PluginCore\ShortcodeRegistry\ShortcodeDefinition;
use Hexa\PluginCore\ShortcodeRegistry\ShortcodeRegistry;

defined( 'ABSPATH' ) || exit;

final class ShortcodeRegistrar {
    private static bool $registered = false;
    private static ?ShortcodeRegistry $catalog = null;

    public static function register(): void {
        if ( self::$registered ) {
            return;
        }

        add_action( 'init', [ self::class, 'register_shortcodes' ], 100 );
        self::$registered = true;
    }

    public static function register_shortcodes(): void {
        $callbacks = [
            'sfpf_loop'          => 'sfpf_person_website\\sfpf_loop_shortcode',
            'founder'            => 'sfpf_person_website\\founder_shortcode',
            'book'               => 'sfpf_person_website\\book_shortcode',
            'sfpf_faq'           => 'sfpf_person_website\\sfpf_faq_shortcode',
            'sfpf_faq_schema'    => 'sfpf_person_website\\sfpf_faq_schema_shortcode',
            'sfpf_person_faq'    => 'sfpf_person_website\\sfpf_person_faq_shortcode',
            'sfpf_elementor_faq' => 'sfpf_person_website\\sfpf_elementor_faq_shortcode',
        ];

        foreach ( $callbacks as $tag => $callback ) {
            if ( is_callable( $callback ) ) {
                add_shortcode( $tag, $callback );
            }
        }
    }

    public static function catalog(): ShortcodeRegistry {
        if ( self::$catalog instanceof ShortcodeRegistry ) {
            return self::$catalog;
        }

        $definitions = [
            [ 'founder', 'Founder field or section', '[founder id="{input}"]', 'Renders canonical Person profile data.', 'name' ],
            [ 'book', 'Book field', '[book field="{input}"]', 'Renders a field from the primary or selected Book.', 'name' ],
            [ 'sfpf-loop', 'Bounded content loop', '[sfpf_loop cpt="{input}"]', 'Renders a bounded Elementor-aware content loop.', 'book' ],
            [ 'person-faq', 'Person FAQ', '[sfpf_person_faq]', 'Renders the canonical Person FAQ set.', '' ],
            [ 'faq-set', 'FAQ set', '[sfpf_faq set="{input}"]', 'Renders one configured FAQ set.', 'default' ],
            [ 'faq-schema', 'FAQ schema', '[sfpf_faq_schema set="{input}"]', 'Renders one FAQPage schema document.', 'default' ],
        ];

        self::$catalog = new ShortcodeRegistry();
        foreach ( $definitions as [ $id, $label, $template, $description, $default ] ) {
            self::$catalog->add(
                new ShortcodeDefinition( $id, $label, $template, $description, 'shortcode', $default )
            );
        }

        return self::$catalog;
    }
}
