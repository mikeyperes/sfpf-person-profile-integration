<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Admin\Profile;

use Hexa\PluginCore\CoreContracts\ModuleInterface;
use Hexa\PluginCore\WpAdminAjax\AjaxActionRegistry;
use Hexa\PluginCore\WpAdminAjax\AjaxFailure;
use Hexa\PluginCore\WpAdminAjax\AjaxRequest;
use SFPF\PersonProfile\Person\PersonProfile;
use SFPF\PersonProfile\PluginRuntime\PluginMetadata;

defined( 'ABSPATH' ) || exit;

/**
 * The person fields on the WordPress profile screens: their styles, the
 * entity-type field switch, the live Knowledge Panel link and the article
 * import. Its assets load on profile.php and user-edit.php only.
 */
final class ProfileScreen implements ModuleInterface {
    public const IMPORT_ACTION = 'sfpf_process_articles';
    public const NONCE         = 'sfpf_profile_screen';

    public function register(): void {
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue' ] );
        AjaxActionRegistry::create( [ 'capability' => 'read', 'nonce_action' => self::NONCE ] )->register(
            [ self::IMPORT_ACTION => [ $this, 'import_articles' ] ]
        );
    }

    public function enqueue( string $hook ): void {
        if ( ! in_array( $hook, [ 'profile.php', 'user-edit.php' ], true ) ) {
            return;
        }
        $version = PluginMetadata::VERSION;
        wp_enqueue_style( 'sfpf-profile-screen', PluginMetadata::url( 'assets/admin/profile-screen.css' ), [], $version );
        wp_enqueue_script( 'sfpf-profile-screen', PluginMetadata::url( 'assets/admin/profile-screen.js' ), [ 'jquery' ], $version, true );
        wp_localize_script(
            'sfpf-profile-screen',
            'sfpfProfileScreen',
            [
                'ajaxUrl'      => admin_url( 'admin-ajax.php' ),
                'nonce'        => wp_create_nonce( self::NONCE ),
                'importAction' => self::IMPORT_ACTION,
                'i18n'         => [
                    'invalidKgid'   => 'This is not a Knowledge Graph ID yet. It looks like /g/11abc123.',
                    'noInput'       => 'Nothing to import',
                    'pasteFirst'    => 'Paste links or HTML above first.',
                    'working'       => 'Importing…',
                    'workingBody'   => 'Cleaning the links, skipping repeats and fetching each title. This can take a moment.',
                    'failed'        => 'The import failed',
                    'requestFailed' => 'The request did not reach the site. Check the connection and try again.',
                    'saveToKeep'    => 'Save the profile to keep these changes.',
                    'nothingNew'    => 'Every link was already listed.',
                    'confirmRemove' => 'Remove all %d articles? Nothing is deleted until the profile is saved.',
                    'removed'       => 'Removed %d articles',
                ],
            ]
        );
    }

    /** @return array<string,mixed> */
    public function import_articles( AjaxRequest $request ): array {
        $user_id = $request->int( 'user_id', 0, 'post' );
        if ( $user_id <= 0 || ! current_user_can( 'edit_user', $user_id ) ) {
            throw new AjaxFailure( 'You cannot edit this profile.', 403, 'forbidden' );
        }
        $input = (string) wp_unslash( $_POST['urls'] ?? '' ); // phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Nonce checked by the registry; links are parsed and validated one by one.
        if ( '' === trim( $input ) ) {
            throw new AjaxFailure( 'Paste links or HTML first.', 400, 'empty' );
        }
        $profile  = PersonProfile::for_user( $user_id );
        $existing = null !== $profile ? array_column( $profile->articles(), 'url' ) : [];
        $result   = ( new ArticleImport() )->process( $input, $existing );
        $count    = count( $result['articles'] );
        return [
            'articles' => $result['articles'],
            'imported' => $count,
            'summary'  => sprintf( '%d new article%s added, %d already listed', $count, 1 === $count ? '' : 's', $result['duplicates'] ),
            'report'   => implode( "\n", $result['report'] ),
        ];
    }
}
