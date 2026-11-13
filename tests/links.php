<?php
/** Link lists, Wikidata handling, display addresses and Knowledge Graph IDs. */

declare( strict_types=1 );

require __DIR__ . '/bootstrap.php';

use SFPF\PersonProfile\Person\Links;

$rows = Links::rows( [ [ 'title' => 'One', 'url' => 'https://www.example.com/a' ], [ 'url' => 'not a url' ], [ 'title' => 'Wiki', 'url' => 'https://www.wikidata.org/wiki/Q1' ] ] );
sfpf_assert( 2 === count( $rows ) && 'example.com' === $rows[0]['source'], 'Rows keep valid links and take their source from the address.' );
sfpf_assert( 1 === count( Links::public_rows( $rows ) ), 'Wikidata rows are never shown.' );
sfpf_assert( [ 'https://a.test/x' ] === array_column( Links::rows( "https://a.test/x\nnope" ), 'url' ), 'One link per line still reads as rows.' );
sfpf_assert( [ 'https://www.wikidata.org/wiki/Q1' ] === Links::wikidata_urls( "https://x.test\nhttps://www.wikidata.org/wiki/Q1" ), 'Wikidata links are found for schema.' );
sfpf_assert( 'example.com/a' === Links::pretty( 'https://example.com/a/' ), 'A display address drops the scheme and trailing slash.' );
sfpf_assert( '/g/11abc_9' === Links::knowledge_graph_id( 'https://www.google.com/search?kgmid=%2Fg%2F11abc_9&hl=en' ), 'The ID is taken from a pasted Google link.' );
sfpf_assert( '/m/0abc' === Links::knowledge_graph_id( 'm/0abc' ), 'A missing leading slash is added.' );
sfpf_assert( '' === Links::knowledge_graph_id( 'hello' ), 'Text that is not an ID gives nothing.' );
sfpf_assert( str_contains( Links::knowledge_panel_url( '/g/11abc' ), 'kgmid=%2Fg%2F11abc' ), 'The Knowledge Panel link carries the encoded ID.' );

echo "PASS: links, Wikidata and Knowledge Graph IDs.\n";
