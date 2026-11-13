<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Admin\Checks;

use Hexa\PluginCore\SchemaTools\SchemaStructures;
use SFPF\PersonProfile\Pages\PageTypes;
use SFPF\PersonProfile\Person\Links;
use SFPF\PersonProfile\Person\PersonProfile;
use SFPF\PersonProfile\Person\PersonResolver;
use SFPF\PersonProfile\PluginRuntime\PluginMetadata;
use SFPF\PersonProfile\Schema\PersonSchema;
use SFPF\PersonProfile\Schema\SchemaCatalog;
use SFPF\PersonProfile\Settings\Settings;

defined( 'ABSPATH' ) || exit;

/**
 * Whether the person website is ready: the plugins it needs, WordPress
 * basics, the person's profile, what a Knowledge Panel needs, the schema and
 * the key pages. Each check says what is wrong and links to where to fix it.
 */
final class ReadinessChecks {
    /** @return list<array<string,string>> Items for Hexa WP Core's system checks. */
    public static function items(): array {
        $items = [];
        $add   = static function ( string $category, string $label, string $status, string $detail, string $url = '', string $action = '' ) use ( &$items ): void {
            $items[] = [ 'category' => $category, 'label' => $label, 'status' => $status, 'detail' => $detail, 'action_url' => $url, 'action_label' => $action ];
        };
        $hws = defined( 'HWS_BASE_TOOLS_BOOTSTRAPPED' ) || class_exists( '\HWS\BaseTools\PluginRuntime\PluginMetadata' );
        $add( 'Plugins', 'HWS Base Tools', $hws ? 'pass' : 'fail', $hws ? 'Active' : 'Needed: it chooses the person the website is about.', admin_url( 'plugins.php' ), 'Plugins' );
        $add( 'Plugins', 'Elementor', defined( 'ELEMENTOR_VERSION' ) ? 'pass' : 'warn', defined( 'ELEMENTOR_VERSION' ) ? 'Version ' . ELEMENTOR_VERSION : 'Not active: the page designs and dynamic tags need it.', admin_url( 'plugins.php' ), 'Plugins' );
        $rank_math = defined( 'RANK_MATH_VERSION' );
        $add( 'Plugins', 'Rank Math SEO', $rank_math ? 'pass' : 'warn', $rank_math ? 'Active' : 'Recommended for titles, descriptions, sitemaps and breadcrumbs.', admin_url( 'plugins.php' ), 'Plugins' );

        $site_type = (string) get_option( 'hws_site_type', '' );
        $add( 'WordPress', 'Website type', 'personal_website' === $site_type ? 'pass' : 'warn', '' !== $site_type ? 'Set to ' . $site_type : 'Not set: choose Personal Website in HWS Base Tools.', admin_url( 'options-general.php?page=hws-core-tools&tab=website-types' ), 'Website Profile' );
        $add( 'WordPress', 'Visible to search engines', '1' === (string) get_option( 'blog_public', '1' ) ? 'pass' : 'fail', '1' === (string) get_option( 'blog_public', '1' ) ? 'Search engines can index the site.' : 'The site asks search engines not to index it.', admin_url( 'options-reading.php' ), 'Reading Settings' );
        $add( 'WordPress', 'Readable links', '' !== (string) get_option( 'permalink_structure' ) ? 'pass' : 'fail', '' !== (string) get_option( 'permalink_structure' ) ? (string) get_option( 'permalink_structure' ) : 'Plain ?p= links: choose Post name.', admin_url( 'options-permalink.php' ), 'Permalinks' );
        $add( 'WordPress', 'Site icon', '' !== get_site_icon_url() ? 'pass' : 'warn', '' !== get_site_icon_url() ? 'Set' : 'No icon for browser tabs and search results.', admin_url( 'customize.php?autofocus[section]=title_tagline' ), 'Site Identity' );

        $profile = PersonResolver::profile();
        $add( 'Person', 'Person chosen', null !== $profile ? 'pass' : 'fail', null !== $profile ? $profile->name() : 'No person yet: choose the person in HWS Base Tools.', admin_url( 'options-general.php?page=hws-core-tools&tab=website-types' ), 'Website Profile' );
        if ( null !== $profile ) {
            foreach ( self::profile_checks( $profile ) as [ $category, $label, $ok, $detail, $warn_only ] ) {
                $add( $category, $label, $ok ? 'pass' : ( $warn_only ? 'warn' : 'fail' ), $detail, $profile->edit_url(), 'Edit profile' );
            }
        }

        foreach ( [ 'homepage' => 'Homepage schema', 'biography' => 'Biography schema', 'person-pages' => 'Page schema', 'faq' => 'FAQ schema' ] as $id => $label ) {
            $on = SchemaStructures::enabled( SchemaCatalog::OWNER, $id );
            $add( 'Schema', $label, $on ? 'pass' : 'warn', $on ? 'On (' . SchemaStructures::mode( SchemaCatalog::OWNER, $id ) . ')' : 'Off', PluginMetadata::admin_url( 'schema' ), 'Schema' );
        }
        $add( 'Schema', 'Person schema', PersonSchema::exists() ? 'pass' : 'fail', PersonSchema::exists() ? 'Built from the profile on every page.' : 'Nothing to build until a person is chosen.', PluginMetadata::admin_url( 'schema' ), 'Schema' );

        foreach ( [ 'biography' => 'Biography page', 'connect' => 'Connect page', 'faqs' => 'FAQs page' ] as $key => $label ) {
            $id = Settings::assigned_page( $key ) ?: ( 'faqs' === $key ? PageTypes::base_tools_page( 'faqs' ) : 0 );
            $add( 'Pages', $label, $id > 0 && 'publish' === get_post_status( $id ) ? 'pass' : 'warn', $id > 0 ? (string) get_the_title( $id ) : 'Not linked', PluginMetadata::admin_url( 'pages' ), 'Pages & Menus' );
        }
        return $items;
    }

    /**
     * The person's checks: [category, label, passes, detail, only a warning].
     *
     * @return list<array{0:string,1:string,2:bool,3:string,4:bool}>
     */
    private static function profile_checks( PersonProfile $profile ): array {
        $photos   = count( $profile->gallery() ) + count( $profile->knowledge_graph_images() ) + ( $profile->avatar_attachment_id() > 0 ? 1 : 0 );
        $same_as  = PersonSchema::same_as( $profile );
        $hosts    = array_map( static fn( string $url ): string => Links::host( $url ), $same_as );
        $has_host = static fn( string $needle ): bool => [] !== array_filter( $hosts, static fn( string $host ): bool => str_contains( $host, $needle ) );
        $text     = static fn( string $value, string $empty ): string => '' !== $value ? wp_trim_words( $value, 10 ) : $empty;
        return [
            [ 'Person', 'Title or role', '' !== $profile->title(), $text( $profile->title(), 'Not set' ), false ],
            [ 'Person', 'Biography', '' !== trim( wp_strip_all_tags( $profile->biography_html() ) ), $text( wp_strip_all_tags( $profile->biography_html() ), 'Not set' ), false ],
            [ 'Person', 'Short biography', '' !== trim( wp_strip_all_tags( $profile->biography_short_html() ) ), $text( wp_strip_all_tags( $profile->biography_short_html() ), 'Not set: search engines use it as the person\'s description' ), false ],
            [ 'Person', 'Professions', [] !== $profile->professions(), count( $profile->professions() ) . ' listed', false ],
            [ 'Person', 'Education', [] !== $profile->education(), count( $profile->education() ) . ' listed', true ],
            [ 'Person', 'Public email', '' !== $profile->public_email(), $text( $profile->public_email(), 'Not set' ), true ],
            [ 'Person', 'Languages', [] !== $profile->languages(), [] !== $profile->languages() ? implode( ', ', $profile->languages() ) : 'None listed', true ],
            [ 'Knowledge Panel', 'Knowledge Graph ID', '' !== $profile->knowledge_graph_id(), $text( $profile->knowledge_graph_id(), 'Not set: add it once Google shows a Knowledge Panel' ), true ],
            [ 'Knowledge Panel', 'Photos', $photos > 0, $photos . ' photo(s) in schema', false ],
            [ 'Knowledge Panel', 'Profile links', count( $same_as ) >= 3, count( $same_as ) . ' profile links (aim for 3 or more)', false ],
            [ 'Knowledge Panel', 'Wikipedia or Wikidata', $has_host( 'wikipedia.org' ) || $has_host( 'wikidata.org' ), $has_host( 'wikidata.org' ) ? 'Wikidata linked' : ( $has_host( 'wikipedia.org' ) ? 'Wikipedia linked' : 'Neither linked' ), true ],
            [ 'Knowledge Panel', 'LinkedIn', $has_host( 'linkedin.com' ), $has_host( 'linkedin.com' ) ? 'Linked' : 'Not linked', true ],
            [ 'Knowledge Panel', 'Birth date and place', '' !== $profile->birth_date() && '' !== $profile->birth_place()['location'], trim( $profile->birth_date() . ' ' . $profile->birth_place()['location'] ) ?: 'Not set', true ],
            [ 'Knowledge Panel', 'Articles about the person', [] !== $profile->articles(), count( $profile->articles() ) . ' listed', true ],
        ];
    }
}
