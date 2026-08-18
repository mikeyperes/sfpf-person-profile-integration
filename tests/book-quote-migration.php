<?php

declare( strict_types=1 );

define( 'ABSPATH', dirname( __DIR__ ) . '/' );

$options = [];
$post_meta = [
    21 => [
        'quotes'                       => 1,
        'quotes_0_quote'               => 'Existing Book quote.',
        'quotes_0_url'                 => 'https://example.com/existing',
        'quotes_0_tagline'             => 'Existing attribution',
        'book_quotes'                  => 1,
        'book_quotes_0_quote'          => 'Legacy repeater quote.',
        'book_quotes_0_source_url'     => 'https://example.com/legacy',
        'book_quotes_0_assigned_name'  => 'Legacy author',
        'quote'                        => 'Legacy single Book quote.',
    ],
    22 => [
        'notable_quotes'               => 1,
        'notable_quotes_0_quote'       => 'Legacy HBS Book quote.',
        'notable_quotes_0_url'         => 'https://example.com/hbs',
        'notable_quotes_0_tagline'     => 'HBS attribution',
    ],
];
$update_calls = 0;
$failures = [];

function add_action( string $hook, callable|string $callback, int $priority = 10 ): void {}

function get_option( string $key, mixed $default = false ): mixed {
    global $options;
    return $options[ $key ] ?? $default;
}

function update_option( string $key, mixed $value, bool $autoload = true ): bool {
    global $options;
    $options[ $key ] = $value;
    return true;
}

function get_post_stati(): array {
    return [ 'publish' => 'publish' ];
}

function get_posts( array $args ): array {
    return 1 === (int) ( $args['paged'] ?? 1 ) ? [ 21, 22 ] : [];
}

function get_post_meta( int $post_id, string $key, bool $single = false ): mixed {
    global $post_meta;
    return $post_meta[ $post_id ][ $key ] ?? '';
}

function update_field( string $field_key, mixed $value, int $post_id ): bool {
    global $post_meta, $update_calls;
    ++$update_calls;
    $post_meta[ $post_id ]['quotes'] = count( $value );
    foreach ( $value as $index => $row ) {
        $post_meta[ $post_id ][ 'quotes_' . $index . '_quote' ] = $row['field_sfpf_book_quote_text'] ?? '';
        $post_meta[ $post_id ][ 'quotes_' . $index . '_url' ] = $row['field_sfpf_book_quote_url'] ?? '';
        $post_meta[ $post_id ][ 'quotes_' . $index . '_tagline' ] = $row['field_sfpf_book_quote_tagline'] ?? '';
    }
    return true;
}

require_once dirname( __DIR__ ) . '/src/Migrations/BookQuoteRepeaterMigration.php';

use SFPF\PersonProfile\Migrations\BookQuoteRepeaterMigration;

$report = BookQuoteRepeaterMigration::run();

$checks = [
    [ 2 === $report['posts_scanned'], 'migration scans Book and legacy HBS Book records' ],
    [ 2 === $report['posts_changed'], 'both records with legacy Book quotes are migrated' ],
    [ 3 === $report['legacy_rows'] && 3 === $report['rows_written'], 'every legacy Book quote row is written once' ],
    [ 3 === $post_meta[21]['quotes'], 'existing canonical Book rows are retained while legacy rows are appended' ],
    [ 'Legacy repeater quote.' === $post_meta[21]['quotes_1_quote'], 'legacy Book repeater text is preserved' ],
    [ 'https://example.com/legacy' === $post_meta[21]['quotes_1_url'], 'legacy Book source URL is normalized into url' ],
    [ 'Legacy author' === $post_meta[21]['quotes_1_tagline'], 'legacy assigned name is normalized into tagline' ],
    [ 'Legacy single Book quote.' === $post_meta[21]['quotes_2_quote'], 'legacy single Book quote fields are preserved' ],
    [ 1 === $post_meta[22]['quotes'] && 'Legacy HBS Book quote.' === $post_meta[22]['quotes_0_quote'], 'legacy HBS Book quotes are included' ],
    [ 1 === $post_meta[21]['book_quotes'] && 1 === $post_meta[22]['notable_quotes'], 'legacy metadata remains available for rollback' ],
    [ [] === $report['errors'], 'Book quote migration verifies without errors' ],
];

foreach ( $checks as [ $passed, $message ] ) {
    if ( $passed ) {
        continue;
    }
    $failures[] = $message;
}

$second_report = BookQuoteRepeaterMigration::run();
if ( 2 !== $update_calls || $second_report !== $report ) {
    $failures[] = 'Book quote migration must be idempotent after its verified report is stored';
}

if ( $failures ) {
    foreach ( $failures as $failure ) {
        fwrite( STDERR, 'FAIL: ' . $failure . PHP_EOL );
    }
    exit( 1 );
}

echo 'PASS: Book quote migration merges canonical and legacy rows without deleting source data.' . PHP_EOL;
