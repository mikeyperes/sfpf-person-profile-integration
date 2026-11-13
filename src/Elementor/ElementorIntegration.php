<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Elementor;

use Hexa\PluginCore\CoreContracts\ModuleInterface;
use SFPF\PersonProfile\Elementor\Conditions\PersonContentCondition;
use SFPF\PersonProfile\Elementor\Tags\PersonImageTag;
use SFPF\PersonProfile\Elementor\Tags\PersonTextTag;
use SFPF\PersonProfile\Elementor\Tags\PersonUrlTag;
use SFPF\PersonProfile\Settings\Settings;

defined( 'ABSPATH' ) || exit;

/**
 * Elementor building blocks for the person's pages:
 * - dynamic tags (SFPF Person group): any person value as text, a link or
 *   the portrait, chosen from a list, so no shortcode is needed;
 * - display conditions (Elementor Pro): show an element only when a person
 *   section, the books or the organizations have content (on by default);
 * - Social Icons widgets drop icons whose link is empty (on by default).
 */
final class ElementorIntegration implements ModuleInterface {
    public const TAG_GROUP = 'sfpf-person';

    public function register(): void {
        add_action( 'elementor/dynamic_tags/register', [ $this, 'register_tags' ] );
        if ( Settings::flag( Settings::ELEMENTOR_CONDITIONS, true ) ) {
            add_action( 'elementor/display_conditions/register_groups', [ $this, 'register_condition_group' ] );
            add_action( 'elementor/display_conditions/register', [ $this, 'register_conditions' ] );
        }
        if ( Settings::flag( Settings::HIDE_EMPTY_SOCIAL_ICONS, true ) ) {
            add_filter( 'elementor/widget/render_content', [ SocialIconsCleanup::class, 'filter' ], 20, 2 );
        }
    }

    public function register_tags( mixed $manager ): void {
        if ( ! is_object( $manager ) || ! class_exists( '\Elementor\Core\DynamicTags\Tag' ) ) {
            return;
        }
        $manager->register_group( self::TAG_GROUP, [ 'title' => 'SFPF Person' ] );
        $manager->register( new PersonTextTag() );
        $manager->register( new PersonUrlTag() );
        $manager->register( new PersonImageTag() );
    }

    public function register_condition_group( mixed $manager ): void {
        if ( is_object( $manager ) && method_exists( $manager, 'add_group' ) ) {
            $manager->add_group( 'sfpf', [ 'label' => 'SFPF Person' ] );
        }
    }

    public function register_conditions( mixed $manager ): void {
        if ( ! is_object( $manager ) || ! method_exists( $manager, 'register_condition_instance' )
            || ! class_exists( '\ElementorPro\Modules\DisplayConditions\Conditions\Base\Condition_Base' )
            || ! class_exists( '\ElementorPro\Core\Isolation\Wordpress_Adapter' ) ) {
            return;
        }
        $manager->register_condition_instance( new PersonContentCondition( [ new \ElementorPro\Core\Isolation\Wordpress_Adapter() ] ) );
    }
}
