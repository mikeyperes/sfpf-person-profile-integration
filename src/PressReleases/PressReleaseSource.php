<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\PressReleases;

use Hexa\PluginCore\PressReleaseProfiles\PressReleaseProfilesClient;
use SFPF\PersonProfile\Person\PersonProfile;

defined( 'ABSPATH' ) || exit;

/** Published remote releases, read only after this person's output is enabled. */
final class PressReleaseSource {
    /** @var array<string,array> */
    private static array $rows = [];

    /** @return list<array{title:string,source:string,url:string}> */
    public static function rows( PersonProfile $person, string $mode ): array {
        $settings = PressReleaseSettings::current();
        if ( ! PressReleaseSettings::enabled( $mode ) || $person->user_id() !== (int) ( $settings['person_id'] ?? 0 ) ) {
            return [];
        }
        $id = (int) ( $settings[ 'by' === $mode ? 'author_id' : 'profile_id' ] ?? 0 );
        $origin = (string) ( $settings['origin'] ?? '' );
        if ( $id < 1 || '' === $origin ) {
            return [];
        }
        $key = 'sfpf_press_' . md5( $person->user_id() . ':' . $mode . ':' . $origin . ':' . $id );
        if ( array_key_exists( $key, self::$rows ) ) {
            return self::$rows[ $key ];
        }
        $cached = get_transient( $key );
        if ( is_array( $cached ) ) {
            return self::$rows[ $key ] = $cached;
        }
        $client = new PressReleaseProfilesClient();
        $result = 'by' === $mode ? $client->author_releases( $origin, $id ) : $client->releases( $origin, $id );
        if ( empty( $result['success'] ) ) {
            set_transient( $key, [], MINUTE_IN_SECONDS );
            return self::$rows[ $key ] = [];
        }
        $rows = [];
        foreach ( (array) ( $result['data']['items'] ?? [] ) as $release ) {
            $url = esc_url_raw( (string) ( $release['url'] ?? '' ), [ 'https' ] );
            if ( '' !== $url ) {
                $rows[] = [
                    'title' => wp_strip_all_tags( (string) ( $release['title'] ?? '' ) ),
                    'source' => (string) wp_parse_url( $origin, PHP_URL_HOST ),
                    'url' => $url,
                ];
            }
        }
        set_transient( $key, $rows, 5 * MINUTE_IN_SECONDS );
        return self::$rows[ $key ] = $rows;
    }
}
