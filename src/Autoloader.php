<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile;

defined( 'ABSPATH' ) || exit;

final class Autoloader {
    private const PREFIX = 'SFPF\\PersonProfile\\';

    private static string $source_root = '';

    public static function register( string $source_root ): void {
        if ( '' !== self::$source_root ) {
            return;
        }

        self::$source_root = rtrim( $source_root, '/\\' );
        spl_autoload_register( [ self::class, 'autoload' ], true, true );
    }

    public static function autoload( string $class_name ): void {
        if ( 0 !== strncmp( $class_name, self::PREFIX, strlen( self::PREFIX ) ) ) {
            return;
        }

        $relative = substr( $class_name, strlen( self::PREFIX ) );
        $file     = self::$source_root . DIRECTORY_SEPARATOR
            . str_replace( '\\', DIRECTORY_SEPARATOR, $relative ) . '.php';

        if ( is_readable( $file ) ) {
            require_once $file;
        }
    }
}
