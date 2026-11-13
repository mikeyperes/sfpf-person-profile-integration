<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Schema;

use Hexa\PluginCore\DataNormalization\FieldReader;
use Hexa\PluginCore\DataNormalization\ValueNormalizer;
use Hexa\PluginCore\Fields\Field;
use Hexa\PluginCore\SchemaTools\SchemaIds;
use Hexa\PluginCore\SchemaTools\SchemaNodes;
use Hexa\PluginCore\SchemaTools\SchemaSources;
use SFPF\PersonProfile\ContentTypes\Award\AwardContentType;
use SFPF\PersonProfile\ContentTypes\ContributingProfile\ContributingProfileContentType;
use SFPF\PersonProfile\Person\PersonProfile;
use SFPF\PersonProfile\Person\PersonResolver;
use SFPF\PersonProfile\Settings\Settings;

defined( 'ABSPATH' ) || exit;

/**
 * The website's person as one schema.org Person, built from their profile
 * with Hexa WP Core's person_profile() builder. Every structure that mentions
 * the person refers to this node by its @id, so the whole site describes one
 * person.
 */
final class PersonSchema {
    private const RELATED_LIMIT = 50;

    /** @var array<string,mixed>|null */
    private static ?array $node = null;

    public static function id(): string {
        return SchemaIds::person();
    }

    public static function ref(): array {
        return SchemaNodes::ref( self::id() );
    }

    public static function exists(): bool {
        return [] !== self::node();
    }

    /** @return array<string,mixed> The Person, or [] when no person is set up. */
    public static function node(): array {
        if ( null !== self::$node ) {
            return self::$node;
        }
        $profile = PersonResolver::profile();
        self::$node = null === $profile ? [] : SchemaNodes::person_profile( self::record( $profile ) );
        self::$node = (array) apply_filters( 'sfpf_person_schema', self::$node, $profile );
        return self::$node;
    }

    public static function forget(): void {
        self::$node = null;
    }

    /** The site's WebSite node, published by the person. */
    public static function website(): array {
        return SchemaSources::website( self::exists() ? self::id() : '' );
    }

    /**
     * The person's profile as the plain record Core's builder takes.
     *
     * @return array<string,mixed>
     */
    public static function record( PersonProfile $profile ): array {
        $front_page_id = 'page' === get_option( 'show_on_front' ) ? (int) get_option( 'page_on_front' ) : 0;
        $professions_page = Settings::assigned_page( 'professions' );
        $occupations = [];
        foreach ( $profile->professions() as $row ) {
            $occupations[] = [
                'name'        => $row['name'],
                'description' => ValueNormalizer::text( $row['summary_html'] ),
                'url'         => $row['page_id'] > 0 ? (string) get_permalink( $row['page_id'] ) : ( $professions_page > 0 ? (string) get_permalink( $professions_page ) : '' ),
            ];
        }
        $education = [];
        foreach ( $profile->education() as $row ) {
            $education[] = [ 'school' => $row['school'], 'same_as' => $row['wikipedia_url'], 'degree' => $row['degree'], 'field' => $row['field'], 'year' => $row['year'] ];
        }
        return [
            '@id'                => self::id(),
            'url'                => SchemaIds::home(),
            'name'               => $profile->name(),
            'given_name'         => $profile->first_name(),
            'family_name'        => $profile->last_name(),
            'additional_name'    => $profile->additional_name(),
            'alternate_names'    => $profile->alternate_names(),
            'honorific_prefix'   => $profile->honorific_prefix(),
            'honorific_suffix'   => $profile->honorific_suffix(),
            'job_title'          => $profile->title(),
            'description'        => $profile->summary(),
            'email'              => $profile->public_email(),
            'telephone'          => $profile->telephone(),
            'gender'             => $profile->gender(),
            'birth_date'         => $profile->birth_date(),
            'birth_place'        => [ 'name' => $profile->birth_place()['location'], 'same_as' => $profile->birth_place()['wikipedia_url'] ],
            'nationality'        => $profile->nationality(),
            'languages'          => $profile->languages(),
            'awards'             => array_merge( $profile->awards(), self::award_records() ),
            'images'             => self::images( $profile ),
            'education'          => $education,
            'occupations'        => $occupations,
            'works_for'          => self::organizations( $profile ),
            'same_as'            => self::same_as( $profile ),
            'knowledge_graph_id' => $profile->knowledge_graph_id(),
            'main_entity_of_page' => $front_page_id > 0 ? SchemaIds::webpage( SchemaIds::home() ) : '',
            'properties'         => [ 'subjectOf' => self::subject_of( $profile ) ],
        ];
    }

    /**
     * Where the person's profiles live: their social links, profile links,
     * additional pages, Wikidata, the site's own social links and the
     * publications they write for.
     *
     * @return list<string>
     */
    public static function same_as( PersonProfile $profile ): array {
        $site_social = FieldReader::acf_value( 'website', 'option', [] );
        $site_social = is_array( $site_social ) ? (array) ( $site_social['social_media'] ?? [] ) : [];
        $own_site    = untrailingslashit( strtolower( SchemaIds::home() ) );
        $urls        = SchemaNodes::same_as(
            array_values( $profile->social_urls() ),
            $profile->same_as(),
            array_column( $profile->additional_urls(), 'url' ),
            $profile->wikidata_urls(),
            array_values( $site_social ),
            self::contributing_profile_urls()
        );
        return array_values( array_filter( $urls, static fn( string $url ): bool => ! str_starts_with( untrailingslashit( strtolower( $url ) ), $own_site ) ) );
    }

    /**
     * The person's photos, largest copies first: the public photos, the
     * Knowledge Panel photos, Wikimedia Commons files and the portrait.
     *
     * @return list<array<string,mixed>|string>
     */
    public static function images( PersonProfile $profile ): array {
        $images = [];
        $ids    = array_merge( $profile->gallery_ids(), array_map( static fn( array $image ): int => (int) ( $image['id'] ?? 0 ), $profile->knowledge_graph_images() ), [ $profile->avatar_attachment_id() ] );
        foreach ( array_unique( array_filter( $ids ) ) as $id ) {
            $image = SchemaSources::attachment_image( (int) $id );
            if ( [] !== $image ) {
                $images[] = $image;
            }
        }
        foreach ( $profile->wikimedia_photo_urls() as $url ) {
            $images[] = self::wikimedia_file_url( $url );
        }
        if ( [] === $images ) {
            $images[] = $profile->avatar_url( 512 );
        }
        return $images;
    }

    /**
     * A Wikimedia Commons file page (…/wiki/File:Name.jpg) as the address of
     * the image itself, which is what an image property needs.
     */
    public static function wikimedia_file_url( string $url ): string {
        if ( preg_match( '~^https?://commons\.(?:m\.)?wikimedia\.org/wiki/(File:[^?#]+)~i', $url, $match ) ) {
            return 'https://commons.wikimedia.org/wiki/Special:FilePath/' . rawurlencode( rawurldecode( substr( $match[1], 5 ) ) );
        }
        return $url;
    }

    /**
     * The Organizations the person founded, as references to the nodes SMC
     * Organization Profile prints on their pages.
     *
     * @return list<array<string,mixed>>
     */
    private static function organizations( PersonProfile $profile ): array {
        $nodes = [];
        foreach ( $profile->organizations() as $id ) {
            $permalink = (string) get_permalink( $id );
            $site_url  = Field::available() ? Field::get( 'url', $id ) : get_post_meta( $id, 'url', true );
            $nodes[]   = SchemaNodes::organization(
                [
                    '@id'  => SchemaIds::for_url( $permalink, 'organization' ),
                    'name' => (string) get_the_title( $id ),
                    'url'  => ValueNormalizer::url( is_string( $site_url ) && '' !== $site_url ? $site_url : $permalink ),
                ]
            );
        }
        return array_values( array_filter( $nodes ) );
    }

    /**
     * Articles, interviews and press releases about the person: what
     * schema.org calls the person being the subject of a work.
     *
     * @return list<array<string,mixed>>
     */
    private static function subject_of( PersonProfile $profile ): array {
        $works = [];
        foreach ( array_slice( $profile->articles(), 0, self::RELATED_LIMIT ) as $article ) {
            $works[] = SchemaNodes::creative_work(
                [
                    'name'      => '' !== $article['title'] ? $article['title'] : $article['source'],
                    'url'       => $article['url'],
                    'publisher' => '' !== $article['source'] ? SchemaNodes::thing( $article['source'], '', 'Organization' ) : null,
                ],
                'Article'
            );
        }
        return array_values( array_filter( $works ) );
    }

    /** @return list<array<string,string>> Award records as award rows. */
    private static function award_records(): array {
        if ( ! post_type_exists( AwardContentType::POST_TYPE ) ) {
            return [];
        }
        $rows = [];
        foreach ( self::published( AwardContentType::POST_TYPE ) as $post_id ) {
            $given_by = (string) ( Field::available() ? Field::get( 'given_by', $post_id ) : get_post_meta( $post_id, 'given_by', true ) );
            $org_id   = absint( Field::available() ? Field::get( 'given_by_organization', $post_id ) : 0 );
            $rows[]   = [
                'name'     => (string) get_the_title( $post_id ),
                'given_by' => '' !== $given_by ? $given_by : ( $org_id > 0 ? (string) get_the_title( $org_id ) : '' ),
                'year'     => (string) ( Field::available() ? Field::get( 'year', $post_id ) : get_post_meta( $post_id, 'year', true ) ),
            ];
        }
        return $rows;
    }

    /** @return list<string> */
    private static function contributing_profile_urls(): array {
        if ( ! post_type_exists( ContributingProfileContentType::POST_TYPE ) ) {
            return [];
        }
        $urls = [];
        foreach ( self::published( ContributingProfileContentType::POST_TYPE ) as $post_id ) {
            $urls[] = (string) ( Field::available() ? Field::get( 'url', $post_id ) : get_post_meta( $post_id, 'url', true ) );
        }
        return $urls;
    }

    /** @return list<int> */
    private static function published( string $post_type ): array {
        return array_map(
            'intval',
            get_posts(
                [
                    'post_type'      => $post_type,
                    'post_status'    => 'publish',
                    'posts_per_page' => self::RELATED_LIMIT,
                    'fields'         => 'ids',
                    'orderby'        => 'date',
                    'order'          => 'DESC',
                    'no_found_rows'  => true,
                ]
            )
        );
    }
}
