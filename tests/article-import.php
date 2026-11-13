<?php
/** Pasted article links: found in HTML, plain lists and bare domains, cleaned and deduplicated. */

declare( strict_types=1 );

require __DIR__ . '/bootstrap.php';

use SFPF\PersonProfile\Admin\Profile\ArticleImport;

$import = new ArticleImport();
$html   = '<p><a href="https://www.forbes.com/a">A</a> and <a href="#">skip</a> <a href="mailto:x@y.z">mail</a></p> also https://techcrunch.com/b, https://forbes.com/a/';
sfpf_assert( [ 'https://forbes.com/a', 'https://techcrunch.com/b' ] === $import->extract( $html ), 'Links come from href values and bare text, without anchors, mail links or repeats.' );
sfpf_assert( [ 'https://example.com/story' ] === $import->extract( "example.com/story\nnot-a-domain" ), 'Bare domains get https://.' );
sfpf_assert( [] === $import->extract( 'nothing here' ), 'Text without links gives none.' );

echo "PASS: article links are found and cleaned.\n";
