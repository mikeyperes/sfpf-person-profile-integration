<?php
/** Wikimedia Commons file pages become image addresses for the Person's images. */

declare( strict_types=1 );

require __DIR__ . '/bootstrap.php';

use SFPF\PersonProfile\Schema\PersonSchema;

sfpf_assert( 'https://commons.wikimedia.org/wiki/Special:FilePath/Ada%20Lovelace.jpg' === PersonSchema::wikimedia_file_url( 'https://commons.wikimedia.org/wiki/File:Ada%20Lovelace.jpg' ), 'A file page becomes its image address.' );
sfpf_assert( 'https://upload.wikimedia.org/a.jpg' === PersonSchema::wikimedia_file_url( 'https://upload.wikimedia.org/a.jpg' ), 'An image address stays.' );

echo "PASS: Wikimedia photos.\n";
