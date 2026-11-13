<?php
/** One value printed every way a shortcode asks: text, lists, links, counts, JSON, one item and a fallback. */

declare( strict_types=1 );

require __DIR__ . '/bootstrap.php';

use SFPF\PersonProfile\Person\FieldValue;
use SFPF\PersonProfile\Shortcodes\Attributes;
use SFPF\PersonProfile\Shortcodes\Output;

$atts = static fn( array $given = [] ): array => Attributes::parse( $given, [], 'test' );
$list = FieldValue::items( [ 'English', 'French' ] );
sfpf_assert( 'English, French' === Output::value( $list, $atts() ), 'Lists print comma-separated by default.' );
sfpf_assert( 'English · French' === Output::value( $list, $atts( [ 'separator' => ' · ' ] ) ), 'A separator keeps its spaces.' );
sfpf_assert( '<ul class="sfpf-field-list"><li>English</li><li>French</li></ul>' === Output::value( $list, $atts( [ 'format' => 'list' ] ) ), 'format="list" prints a list.' );
sfpf_assert( '2' === Output::value( $list, $atts( [ 'format' => 'count' ] ) ), 'format="count" counts.' );
sfpf_assert( 'French' === Output::value( $list, $atts( [ 'index' => '1' ] ) ), 'index picks one item.' );
$rows = FieldValue::items( [ 'Harvard' ], [ [ 'school' => 'Harvard', 'degree' => 'B.A.' ] ] );
sfpf_assert( 'B.A.' === Output::value( $rows, $atts( [ 'index' => '0', 'key' => 'degree' ] ) ), 'key picks one value of an item.' );
$url = FieldValue::url( 'https://example.com/a?b=1&c=2', 'Example' );
sfpf_assert( 'https://example.com/a?b=1&#038;c=2' === Output::value( $url, $atts() ), 'A link prints as an address for link fields.' );
sfpf_assert( str_contains( Output::value( $url, $atts( [ 'link' => 'true', 'target' => '_blank' ] ) ), 'rel="noopener">Example</a>' ), 'link="true" prints an anchor with its label.' );
sfpf_assert( 'example.com/a?b=1&amp;c=2' === Output::value( FieldValue::url( 'https://example.com/a?b=1&c=2' ), $atts( [ 'pretty' => 'true' ] ) ), 'pretty drops the scheme.' );
sfpf_assert( 'None yet' === Output::value( FieldValue::none(), $atts( [ 'fallback' => 'None yet' ] ) ), 'An empty value prints its fallback.' );
sfpf_assert( '[&quot;English&quot;,&quot;French&quot;]' === Output::value( $list, $atts( [ 'format' => 'json' ] ) ), 'format="json" prints escaped JSON.' );
sfpf_assert( 'Bold' === Output::value( FieldValue::html( '<b>Bold</b>' ), $atts( [ 'format' => 'text' ] ) ), 'format="text" strips formatting.' );
sfpf_assert( "https://a.test\nhttps://b.test" === Output::value( FieldValue::urls( [ 'https://a.test', 'https://b.test' ] ), $atts() ), 'Link lists print one address per line.' );

echo "PASS: every output format.\n";
