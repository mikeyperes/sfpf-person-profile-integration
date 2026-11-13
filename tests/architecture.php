<?php
/**
 * The code's shape: thin main file, one class per file in the folder its
 * namespace names, no code from before 4.0, matching versions, every
 * registered module a Core module, and Core bundled whole.
 */

declare( strict_types=1 );

require __DIR__ . '/bootstrap.php';

$root = dirname( __DIR__ );

foreach ( [ 'includes', 'admin', 'snippets', 'schema' ] as $old ) {
    sfpf_assert( ! is_dir( $root . '/' . $old ), "The pre-4.0 folder {$old}/ is gone." );
}

$main = (string) file_get_contents( $root . '/initialization.php' );
sfpf_assert( substr_count( $main, "\n" ) < 45, 'The main file stays a thin loader.' );
preg_match( '/^\s*\*\s*Version:\s*(\S+)/m', $main, $header );
sfpf_assert( \SFPF\PersonProfile\PluginRuntime\PluginMetadata::VERSION === ( $header[1] ?? '' ), 'The header version matches PluginMetadata::VERSION.' );

$files = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $root . '/src', FilesystemIterator::SKIP_DOTS ) );
$count = 0;
foreach ( $files as $file ) {
    if ( 'php' !== $file->getExtension() ) {
        continue;
    }
    $count++;
    $code     = (string) file_get_contents( $file->getPathname() );
    $relative = substr( $file->getPathname(), strlen( $root . '/src/' ) );
    $expected = 'SFPF\\PersonProfile\\' . str_replace( '/', '\\', dirname( $relative ) );
    $expected = rtrim( str_replace( '\\.', '', $expected ), '\\' );
    sfpf_assert( 1 === preg_match( '/^namespace ' . preg_quote( $expected, '/' ) . ';$/m', $code ), "{$relative} declares namespace {$expected}." );
    sfpf_assert( 1 === preg_match_all( '/^(?:final |abstract )?(?:class|interface|trait) ' . preg_quote( basename( $relative, '.php' ), '/' ) . '\b/m', $code ), "{$relative} holds exactly its own class." );
    sfpf_assert( ! str_contains( $code, 'sfpf_person_website' ), "{$relative} uses no pre-4.0 namespace." );
    sfpf_assert( 0 === preg_match( "/(image|thumbnail|avatar|gallery|size)[^\\n]*'full'/i", $code ), "{$relative} asks for no full-size image." );
}
sfpf_assert( $count > 100, 'Every source file was checked.' );

preg_match_all( '/add_module\(\s*new\s+([A-Za-z\\\\]+)\(/', (string) file_get_contents( $root . '/src/PluginRuntime/Bootstrap.php' ), $modules );
foreach ( $modules[1] as $module ) {
    $uses = [];
    preg_match_all( '/^use ([^;]+);$/m', (string) file_get_contents( $root . '/src/PluginRuntime/Bootstrap.php' ), $uses );
    $class = '';
    foreach ( $uses[1] as $use ) {
        if ( str_ends_with( $use, '\\' . $module ) ) {
            $class = $use;
        }
    }
    sfpf_assert( '' !== $class && is_subclass_of( $class, \Hexa\PluginCore\CoreContracts\ModuleInterface::class ), "{$module} is a Core module." );
}

$core = $root . '/lib/hexa-wordpress-plugin-core';
sfpf_assert( is_file( $core . '/VERSION' ) && is_file( $core . '/bootstrap.php' ) && ! is_dir( $core . '/.git' ), 'Hexa WP Core is bundled whole, without its own Git folder.' );
sfpf_assert( class_exists( \Hexa\PluginCore\SchemaTools\SchemaStructures::class ), 'The bundled Core has the schema structure registry.' );

echo "PASS: {$count} source files are namespaced, one class each, with no pre-4.0 code.\n";
