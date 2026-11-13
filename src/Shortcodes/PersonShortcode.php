<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Shortcodes;

use SFPF\PersonProfile\Person\PersonFieldValues;
use SFPF\PersonProfile\Person\PersonResolver;

defined( 'ABSPATH' ) || exit;

/**
 * [person field="title"]
 *
 * One value of the person: a name, their title, a link, a list. Links print
 * as plain addresses so Elementor link fields can use them; link="true" makes
 * an anchor. Lists print comma-separated, or format="list", "json", "count".
 */
final class PersonShortcode {
    public const TAG = 'person';

    /** @param array<string,string>|string $atts */
    public static function render( mixed $atts ): string {
        $atts    = Attributes::parse( $atts, [ 'field' => '', 'network' => '', 'size' => '' ], self::TAG );
        $profile = PersonResolver::profile();
        if ( null === $profile || '' === $atts['field'] ) {
            return esc_html( $atts['fallback'] );
        }
        return Output::value( PersonFieldValues::get( $profile, sanitize_key( $atts['field'] ), $atts ), $atts );
    }
}
