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
