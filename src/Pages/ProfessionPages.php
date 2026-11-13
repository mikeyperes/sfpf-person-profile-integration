<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Pages;

use Hexa\PluginCore\Fields\Field;
use SFPF\PersonProfile\Person\PersonFields;
use SFPF\PersonProfile\Person\PersonResolver;
use SFPF\PersonProfile\Settings\Settings;
use SFPF\PersonProfile\Support\ActivityLog;

defined( 'ABSPATH' ) || exit;

/**
 * A page for each of the person's professions, under the Professions page,
 * linked from the profession on the person's profile.
 */
final class ProfessionPages {
    /** @return array{page_id:int,title:string,permalink:string,edit_url:string}|\WP_Error */
    public static function create( int $index ): array|\WP_Error {
        $profession = self::profession( $index );
        if ( is_wp_error( $profession ) ) {
            return $profession;
        }
        $parent = Settings::assigned_page( 'professions' );
        if ( $parent <= 0 ) {
            return new \WP_Error( 'no_parent', 'Create or link the Professions page first.' );
        }
        $slug     = sanitize_title( $profession['name'] );
        $existing = get_posts( [ 'name' => $slug, 'post_type' => 'page', 'post_status' => 'publish', 'post_parent' => $parent, 'posts_per_page' => 1, 'fields' => 'ids', 'no_found_rows' => true ] );
        $page_id  = [] !== $existing ? (int) $existing[0] : wp_insert_post( [ 'post_title' => $profession['name'], 'post_name' => $slug, 'post_type' => 'page', 'post_status' => 'publish', 'post_parent' => $parent, 'post_content' => '' ], true );
        if ( is_wp_error( $page_id ) ) {
            return $page_id;
        }
        self::link( $index, (int) $page_id );
        ActivityLog::add( 'Profession page linked: ' . $profession['name'] . ' (page ' . (int) $page_id . ')', 'success' );
        return [ 'page_id' => (int) $page_id, 'title' => $profession['name'], 'permalink' => (string) get_permalink( $page_id ), 'edit_url' => (string) get_edit_post_link( $page_id, 'raw' ) ];
    }

    /** Unlinks the profession and moves its page to the trash. */
    public static function delete( int $index, int $page_id ): true|\WP_Error {
        $profession = self::profession( $index );
        if ( is_wp_error( $profession ) ) {
            return $profession;
        }
        if ( $page_id <= 0 || (int) $profession['page_id'] !== $page_id || ! PageTypes::is_profession_page( $page_id ) ) {
            return new \WP_Error( 'not_linked', 'That page is not this profession\'s page.' );
        }
        self::link( $index, 0 );
        if ( ! wp_trash_post( $page_id ) ) {
            return new \WP_Error( 'trash_failed', 'The page could not be moved to the trash.' );
        }
        ActivityLog::add( 'Profession page moved to the trash (page ' . $page_id . ')', 'warning' );
        return true;
    }

    /** @return array{name:string,page_id:int}|\WP_Error */
    private static function profession( int $index ): array|\WP_Error {
        $profile = PersonResolver::profile();
        foreach ( null !== $profile ? $profile->professions() : [] as $row ) {
            if ( $row['index'] === $index && '' !== $row['name'] ) {
                return [ 'name' => $row['name'], 'page_id' => $row['page_id'] ];
            }
        }
        return new \WP_Error( 'unknown_profession', 'That profession is not on the person\'s profile.' );
    }

    private static function link( int $index, int $page_id ): void {
        $context = 'user_' . PersonResolver::user_id();
        // Unformatted rows, so the summaries are saved back exactly as stored.
        $rows = array_values( (array) Field::get( PersonFields::PROFESSIONS, $context, false ) );
        if ( isset( $rows[ $index ] ) && is_array( $rows[ $index ] ) ) {
            $key = array_key_exists( 'field_sfpf_profession_page', $rows[ $index ] ) ? 'field_sfpf_profession_page' : 'page';
            $rows[ $index ][ $key ] = $page_id > 0 ? $page_id : '';
            Field::update( PersonFields::PROFESSIONS, $rows, $context );
        }
    }
}
