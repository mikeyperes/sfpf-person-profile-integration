<?php

declare( strict_types=1 );

$canonical_root = dirname( __DIR__, 2 ) . '/hexa-wordpress-plugin-core/src/DataNormalization';
$bundled_root = dirname( __DIR__ ) . '/lib/hexa-wordpress-plugin-core/src/DataNormalization';
$data_normalization_root = is_file( $canonical_root . '/ValueNormalizer.php' ) ? $canonical_root : $bundled_root;

foreach ( [ 'ValueNormalizer', 'FieldReader', 'MediaNormalizer' ] as $class_file ) {
    $class_name = 'Hexa\\PluginCore\\DataNormalization\\' . $class_file;
    if ( ! class_exists( $class_name, false ) ) {
        require_once $data_normalization_root . '/' . $class_file . '.php';
    }
}

if ( ! function_exists( 'trailingslashit' ) ) {
    function trailingslashit( string $value ): string { return rtrim( $value, '/\\' ) . '/'; }
}
if ( ! function_exists( 'untrailingslashit' ) ) {
    function untrailingslashit( string $value ): string { return rtrim( $value, '/\\' ); }
}
if ( ! function_exists( 'home_url' ) ) {
    function home_url( string $path = '' ): string {
        $site = function_exists( 'get_site_url' ) ? rtrim( (string) get_site_url(), '/' ) : 'https://example.test';
        return $site . '/' . ltrim( $path, '/' );
    }
}

$schema_tools_root = dirname( $data_normalization_root ) . '/SchemaTools';
foreach ( [ 'SchemaGraph', 'SchemaIds', 'SchemaNodes', 'SchemaSources' ] as $class_file ) {
    if ( ! class_exists( 'Hexa\\PluginCore\\SchemaTools\\' . $class_file, false ) ) {
        require_once $schema_tools_root . '/' . $class_file . '.php';
    }
}

require_once dirname( __DIR__ ) . '/lib/hexa-wordpress-plugin-core/tests/support/fields.php';
