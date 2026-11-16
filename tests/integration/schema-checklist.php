<?php
/**
 * Read-only WordPress integration check. Run from the site root with:
 * wp eval-file wp-content/plugins/sfpf-person-profile-integration/tests/integration/schema-checklist.php
 * No records, settings, content types or schema modes are saved.
 */

use Hexa\PluginCore\SchemaTools\SchemaContext;
use Hexa\PluginCore\SchemaTools\SchemaGraph;
use Hexa\PluginCore\SchemaTools\SchemaPage;
use Hexa\PluginCore\SchemaTools\SchemaStructures;
use SFPF\PersonProfile\Schema\PersonSchema;
use SFPF\PersonProfile\Schema\SchemaCatalog;

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
    throw new RuntimeException( 'Run this integration check through wp eval-file.' );
}

$assert = static function ( bool $condition, string $message ): void {
    if ( ! $condition ) {
        WP_CLI::error( $message );
    }
};
$verify = static function ( array $document, string $label ) use ( $assert ): void {
    $assert( ! empty( $document['@graph'] ), $label . ': no graph.' );
    $assert( [] === SchemaGraph::validation_issues( $document ), $label . ': invalid URL values.' );
    $ids = [];
    foreach ( $document['@graph'] as $node ) {
        $assert( ! empty( $node['@type'] ), $label . ': untyped top-level node.' );
        if ( isset( $node['@id'] ) ) {
            $assert( ! isset( $ids[ $node['@id'] ] ), $label . ': duplicate node identifier.' );
            $ids[ $node['@id'] ] = true;
        }
    }
    $encoded = wp_json_encode( $document );
    $assert( is_string( $encoded ) && is_array( json_decode( $encoded, true ) ), $label . ': JSON encoding failed.' );
};

$structures = SchemaCatalog::structures();
$samples = 0;
foreach ( $structures as $structure ) {
    $definition = $structure->definition();
    $id = $definition['id'];
    $preview = SchemaStructures::preview( SchemaCatalog::OWNER, $id );
    if ( $preview['ok'] ) {
        $verify( $preview['document'], $id );
        ++$samples;
    }
    if ( isset( $definition['modes'] ) && is_callable( $definition['sample'] ?? null ) ) {
        $context = $definition['sample']();
        if ( $context instanceof SchemaContext ) {
            foreach ( array_keys( $definition['modes'] ) as $mode ) {
                $verify( SchemaPage::document_for( $structure->build( $context, $mode ) ), $id . '/' . $mode );
            }
        }
    }
}

// Clone an existing record only in memory. Filters supply disposable field values.
$posts = get_posts( [ 'post_type' => 'page', 'post_status' => 'publish', 'posts_per_page' => 1 ] );
$assert( [] !== $posts, 'A published page is needed for the in-memory context.' );
$fixture_id = (int) $posts[0]->ID;
$fields = [
    'quote'                           => 'A disposable schema test quotation.',
    'assigned_name'                   => 'Fixture Speaker',
    'sfpf_press_release_source_name'   => 'Fixture Distributor',
    'sfpf_press_release_original_url'  => 'https://example.org/release/',
    'sfpf_press_release_date'          => '2026-01-01',
    'podcast_name'                     => 'Fixture Series',
    'host_name'                        => 'Fixture Host',
    'start_date'                       => '2026-01-01',
    'end_date'                         => '2026-01-02',
    'venue'                            => 'Fixture Gallery',
    'city'                             => 'Fixture City',
];
$metadata = static function ( mixed $value, int $id, string $key ) use ( $fixture_id, &$fields ): mixed {
    return $id === $fixture_id && array_key_exists( $key, $fields ) ? [ $fields[ $key ] ] : $value;
};
$acf = static function ( mixed $value, mixed $id, array $field ) use ( $fixture_id, &$fields ): mixed {
    return (int) $id === $fixture_id && isset( $fields[ $field['name'] ] ) ? $fields[ $field['name'] ] : $value;
};
add_filter( 'get_post_metadata', $metadata, 10, 3 );
add_filter( 'acf/pre_load_value', $acf, 10, 3 );
$expected = [
    'award'                => 'ItemPage',
    'exhibition'           => 'ExhibitionEvent',
    'quote'                => 'Quotation',
    'press-release'        => 'NewsArticle',
    'interview'            => 'CreativeWork',
    'contributing-profile' => 'WebPage',
];
$fixtures = 0;
try {
    foreach ( $structures as $structure ) {
        $id = $structure->definition()['id'];
        if ( ! isset( $expected[ $id ] ) ) {
            continue;
        }
        $post = clone $posts[0];
        $post->post_type = $id;
        $post->post_title = 'Disposable schema fixture';
        $post->post_content = '';
        $post->post_excerpt = '';
        $context = new SchemaContext( SchemaContext::SINGULAR, $post, 'https://example.org/schema-fixture/' );
        $document = SchemaPage::document_for( $structure->build( $context, 'on' ) );
        $verify( $document, $id . '/fixture' );
        $assert( in_array( $expected[ $id ], SchemaGraph::types( $document ), true ), $id . ': missing entity type.' );
        if ( 'press-release' === $id ) {
            $nodes = array_column( $document['@graph'], null, '@type' );
            $assert( ! isset( $nodes['NewsArticle']['author'] ), 'A distributor must not be asserted as the author.' );
            $assert( 'Fixture Distributor' === ( $nodes['NewsArticle']['citation'] ?? '' ), 'The original source credit must remain.' );
            $assert( ( $nodes['WebPage']['mainEntity']['@id'] ?? '' ) === $nodes['NewsArticle']['@id'], 'The page must identify its NewsArticle.' );
        }
        ++$fixtures;
    }
} finally {
    remove_filter( 'get_post_metadata', $metadata, 10 );
    remove_filter( 'acf/pre_load_value', $acf, 10 );
}
$assert( count( $expected ) === $fixtures, 'Not every fixture structure was checked.' );
foreach ( (array) ( PersonSchema::node()['alumniOf'] ?? [] ) as $school ) {
    $assert( 'EducationalOrganization' === $school['@type'], 'An institution type must not be guessed.' );
}
WP_CLI::success( count( $structures ) . ' structures reviewed; ' . $samples . ' current-data samples, ' . $fixtures . ' in-memory fixture builders, declared modes and source relationships passed.' );
