<?php

declare( strict_types=1 );

namespace Hexa\PluginCore\EntitySources {
    final class CanonicalEntityResolver {
        public static ?array $entity = null;
        public static function resolve(): ?array { return self::$entity; }
    }
}

namespace {
    define( 'ABSPATH', __DIR__ . '/' );
    final class WP_User { public function __construct( public int $ID ) {} }
    function get_userdata( int $id ): WP_User|false { return $id > 0 ? new WP_User( $id ) : false; }
    function get_field( string $name, string $context ): mixed {
        if ( 'option' !== $context ) return null;
        if ( 'founder' === $name ) return [ 'founder_user' => 9 ];
        if ( 'website' === $name ) return [ 'company' => 10 ];
        return null;
    }

    require __DIR__ . '/load-core-data-normalization.php';
    require dirname( __DIR__ ) . '/includes/helper-functions.php';

    use Hexa\PluginCore\EntitySources\CanonicalEntityResolver;
    use function sfpf_person_website\get_company_user_id;
    use function sfpf_person_website\get_founder_user_id;

    CanonicalEntityResolver::$entity = [ 'kind' => 'user', 'id' => 7, 'entity_type' => 'person' ];
    if ( 7 !== get_founder_user_id() ) { fwrite( STDERR, "Canonical Person user was not preferred.\n" ); exit( 1 ); }
    CanonicalEntityResolver::$entity = [ 'kind' => 'post', 'id' => 22, 'entity_type' => 'person', 'attached_user_id' => 8 ];
    if ( 8 !== get_founder_user_id() ) { fwrite( STDERR, "Bound profile author was not resolved.\n" ); exit( 1 ); }
    CanonicalEntityResolver::$entity = [ 'kind' => 'user', 'id' => 10, 'entity_type' => 'organization' ];
    if ( 10 !== get_company_user_id() ) { fwrite( STDERR, "Canonical Organization user was not resolved.\n" ); exit( 1 ); }
    CanonicalEntityResolver::$entity = null;
    if ( 9 !== get_founder_user_id() ) { fwrite( STDERR, "Legacy founder fallback was not preserved.\n" ); exit( 1 ); }
    echo "PASS: SFPF consumes canonical HWS users and post-bound authors with legacy fallback.\n";
}
