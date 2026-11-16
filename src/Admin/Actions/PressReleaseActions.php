<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Admin\Actions;

use Hexa\PluginCore\CoreContracts\ModuleInterface;
use Hexa\PluginCore\PressReleaseProfiles\PressReleaseProfilesClient;
use Hexa\PluginCore\WpAdminAjax\AjaxActionRegistry;
use Hexa\PluginCore\WpAdminAjax\AjaxFailure;
use Hexa\PluginCore\WpAdminAjax\AjaxRequest;
use SFPF\PersonProfile\Admin\AdminUi;
use SFPF\PersonProfile\Person\PersonResolver;
use SFPF\PersonProfile\PluginRuntime\PluginMetadata;
use SFPF\PersonProfile\PressReleases\PressReleaseSettings;
use SFPF\PersonProfile\Settings\Settings;
use SFPF\PersonProfile\Support\ActivityLog;

defined( 'ABSPATH' ) || exit;

/** Protected connection checks and settings for the website's current person. */
final class PressReleaseActions implements ModuleInterface {
    public const SAVE  = 'sfpf_save_press_releases';
    public const CHECK = 'sfpf_check_press_releases';

    public function register(): void {
        AjaxActionRegistry::create( [ 'capability' => PluginMetadata::ADMIN_CAPABILITY, 'nonce_action' => AdminUi::NONCE ] )->register(
            [ self::SAVE => [ $this, 'save' ], self::CHECK => [ $this, 'check' ] ]
        );
    }

    /** @return array<string,mixed> */
    public function check( AjaxRequest $request ): array {
        $this->require_person( $request );
        $client = new PressReleaseProfilesClient();
        $origin = $this->origin( $request );
        $mode   = $request->key( 'mode', '', 'post' );
        if ( 'profiles' === $mode ) {
            $page = max( 1, $request->int( 'profile_page', 1, 'post' ) );
            $data = $this->data( $client->search_people( $origin, $request->text( 'profile_name', '', 'post' ), $page ) );
            $profiles = (array) ( $data['items'] ?? [] );
            return [
                'profiles' => $profiles,
                'page' => (int) ( $data['page'] ?? $page ),
                'total_pages' => (int) ( $data['total_pages'] ?? 0 ),
                'message' => [] === $profiles ? 'No matching published person profiles were found.' : count( $profiles ) . ' profile(s) on this page. Choose yours, then check the connection.',
            ];
        }
        if ( 'author' === $mode ) {
            $author = $this->data( $client->find_author( $origin, $request->text( 'author_url', '', 'post' ) ) );
            $releases = $this->data( $client->author_releases( $origin, (int) $author['id'] ) );
            return [ 'message' => $this->connection_message( $author, $releases ), 'profile' => $author ];
        }
        if ( 'about' === $mode ) {
            $person = $this->data( $client->person( $origin, $request->int( 'profile_id', 0, 'post' ) ) );
            $releases = $this->data( $client->releases( $origin, (int) $person['id'] ) );
            return [ 'message' => $this->connection_message( $person, $releases ), 'profile' => $person ];
        }
        throw AjaxFailure::bad_request( 'Choose an author or Verified Profile connection to check.' );
    }

    /** @return array{message:string} */
    public function save( AjaxRequest $request ): array {
        $person_id = $this->require_person( $request );
        $origin = $this->origin( $request );
        $by = $request->bool( 'by', false, 'post' );
        $about = $request->bool( 'about', false, 'post' );
        $author_url = esc_url_raw( $request->text( 'author_url', '', 'post' ), [ 'https' ] );
        $profile_id = $request->int( 'profile_id', 0, 'post' );
        $saved = PressReleaseSettings::current();
        $author = [];
        $profile = [];
        $client = new PressReleaseProfilesClient();
        if ( $by ) {
            $author = $this->data( $client->find_author( $origin, $author_url ) );
            $this->data( $client->author_releases( $origin, (int) $author['id'] ) );
        }
        if ( $about ) {
            $profile = $this->data( $client->person( $origin, $profile_id ) );
            $this->data( $client->releases( $origin, (int) $profile['id'] ) );
        }
        $same_author = $origin === ( $saved['origin'] ?? '' ) && $author_url === ( $saved['author_url'] ?? '' );
        $same_profile = $origin === ( $saved['origin'] ?? '' ) && $profile_id === (int) ( $saved['profile_id'] ?? 0 );
        $author = $this->resolve_connection( $author, $saved, 'author', $same_author );
        $profile = $this->resolve_connection( $profile, $saved, 'profile', $same_profile );
        update_option( Settings::PRESS_RELEASES, [
            'person_id' => $person_id,
            'origin' => $origin,
            'by' => $by,
            'about' => $about,
            'author_id' => (int) ( $author['id'] ?? 0 ),
            'author_url' => (string) ( $author['url'] ?? $author_url ),
            'author_name' => (string) ( $author['name'] ?? '' ),
            'profile_id' => $profile_id,
            'profile_name' => (string) ( $profile['name'] ?? $request->text( 'profile_label', '', 'post' ) ),
            'profile_url' => (string) ( $profile['url'] ?? '' ),
        ], false );
        ActivityLog::add( 'Press release connections saved.' );
        return [
            'message' => 'Saved. ' . PressReleaseSettings::MODES['by'] . ': ' . ( $by ? 'on' : 'off' )
                . '. ' . PressReleaseSettings::MODES['about'] . ': ' . ( $about ? 'on' : 'off' ) . '.',
        ];
    }

    /**
     * @param array<string,mixed> $fresh
     * @param array<string,mixed> $saved
     * @return array<string,mixed>
     */
    private function resolve_connection( array $fresh, array $saved, string $prefix, bool $unchanged ): array {
        if ( [] !== $fresh ) {
            return $fresh;
        }
        if ( ! $unchanged ) {
            return [];
        }
        return [
            'id'   => (int) ( $saved[ $prefix . '_id' ] ?? 0 ),
            'name' => (string) ( $saved[ $prefix . '_name' ] ?? '' ),
            'url'  => (string) ( $saved[ $prefix . '_url' ] ?? '' ),
        ];
    }

    private function require_person( AjaxRequest $request ): int {
        $person = PersonResolver::profile();
        if ( null === $person || $person->user_id() !== $request->int( 'person_id', 0, 'post' ) ) {
            throw AjaxFailure::bad_request( 'The website person changed. Reload this section before continuing.' );
        }
        return $person->user_id();
    }

    private function origin( AjaxRequest $request ): string {
        $url = esc_url_raw( $request->text( 'origin', '', 'post' ), [ 'https' ] );
        $parts = wp_parse_url( $url );
        if ( ! is_array( $parts ) || 'https' !== ( $parts['scheme'] ?? '' ) || empty( $parts['host'] )
            || isset( $parts['user'] ) || isset( $parts['pass'] ) || isset( $parts['query'] ) || isset( $parts['fragment'] )
            || ! in_array( $parts['path'] ?? '', [ '', '/' ], true ) ) {
            throw AjaxFailure::bad_request( 'Enter an HTTPS publication origin without a path, query or login.' );
        }
        return untrailingslashit( $url );
    }

    /** @param array<string,mixed> $result @return array<string,mixed> */
    private function data( array $result ): array {
        if ( empty( $result['success'] ) || ! is_array( $result['data'] ?? null ) ) {
            throw AjaxFailure::bad_request( (string) ( $result['message'] ?? 'The publication returned an invalid response.' ), (string) ( $result['code'] ?? 'press_connection_failed' ) );
        }
        return $result['data'];
    }

    /** @param array<string,mixed> $profile @param array<string,mixed> $releases */
    private function connection_message( array $profile, array $releases ): string {
        return 'Connected to ' . (string) $profile['name'] . ': ' . (string) $profile['url'] . '. Published releases: ' . (int) ( $releases['total'] ?? 0 ) . '.';
    }
}
