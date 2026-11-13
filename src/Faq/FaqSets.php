<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Faq;

use Hexa\PluginCore\FaqSets\FaqSetManager;
use SFPF\PersonProfile\Person\PersonResolver;
use SFPF\PersonProfile\Settings\Settings;

defined( 'ABSPATH' ) || exit;

/**
 * The website's FAQs: the person's own questions (on their profile) and the
 * site-wide FAQ sets kept in the plugin's FAQ tab, through Hexa WP Core's FAQ
 * manager.
 */
final class FaqSets {
    private static ?FaqSetManager $manager = null;

    public static function manager(): FaqSetManager {
        return self::$manager ??= new FaqSetManager();
    }

    /** @return list<array{question:string,answer:string}> The person's questions with answers. */
    public static function person_items(): array {
        $profile = PersonResolver::profile();
        return null === $profile ? [] : self::manager()->normalizeItems( $profile->faq() );
    }

    /** @return list<array<string,mixed>> */
    public static function sets(): array {
        return array_values( array_filter( Settings::map( Settings::FAQ_SETS ), 'is_array' ) );
    }

    /** One FAQ set by its slug; an empty slug gives the primary set. */
    public static function set( string $slug ): ?array {
        return self::manager()->resolveSet( self::sets(), sanitize_title( $slug ), (string) get_option( Settings::PRIMARY_FAQ_SET, '' ) );
    }

    /**
     * The items of a set, or one of them.
     *
     * @return list<array{question:string,answer:string}>
     */
    public static function set_items( string $slug, ?int $index = null ): array {
        $set   = self::set( $slug );
        $items = null === $set ? [] : self::manager()->normalizeItems( $set['items'] ?? [] );
        if ( null === $index ) {
            return $items;
        }
        return isset( $items[ $index ] ) ? [ $items[ $index ] ] : [];
    }

    /** @param array<int,array<string,mixed>> $sets */
    public static function save( array $sets, string $primary ): void {
        update_option( Settings::FAQ_SETS, self::manager()->sanitizeSets( $sets ), false );
        update_option( Settings::PRIMARY_FAQ_SET, sanitize_title( $primary ), false );
    }
}
