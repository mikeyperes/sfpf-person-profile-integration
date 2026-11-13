<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Pages;

defined( 'ABSPATH' ) || exit;

/**
 * The starting content of each person page, built from shortcodes so the page
 * always shows the person's current data. A site can save its own version of
 * a template in the Templates tab.
 */
final class PageTemplates {
    /** @return array<string,array{title:string,content:string}> */
    public static function defaults(): array {
        return [
            'biography'             => [ 'title' => 'Biography', 'content' => "[person_section id=\"biography\"]\n\n[person_section id=\"professions\" heading=\"Professions\"]\n\n[person_section id=\"education\" heading=\"Education\"]\n\n[person_section id=\"organizations\" heading=\"Organizations Founded\"]\n\n[person_section id=\"socials\" heading=\"Connect\"]" ],
            'education'             => [ 'title' => 'Education', 'content' => '[person_section id="education"]' ],
            'location_born'         => [ 'title' => 'Location Born', 'content' => '[person_section id="birthplace"]' ],
            'organizations_founded' => [ 'title' => 'Organizations Founded', 'content' => '[person_section id="organizations"]' ],
            'alternate_names'       => [ 'title' => 'Alternate Names', 'content' => "<p>[person field=\"name\"] is also known as [person field=\"alternate_names\"].</p>" ],
            'professions'           => [ 'title' => 'Professions', 'content' => "[person field=\"biography_short\"]\n\n[person_section id=\"professions\"]" ],
            'recent_articles'       => [ 'title' => 'Recent Articles', 'content' => '[person_section id="articles"]' ],
            'additional_urls'       => [ 'title' => 'Additional URLs', 'content' => '[person_section id="additional_urls"]' ],
            'gallery'               => [ 'title' => 'Gallery', 'content' => '[person_section id="gallery"]' ],
            'connect'               => [ 'title' => 'Connect', 'content' => "<p>Get in touch with [person field=\"name\"].</p>\n\n[person_section id=\"contact\"]" ],
            'faqs'                  => [ 'title' => 'FAQs', 'content' => '[person_section id="faq"]' ],
            'media'                 => [ 'title' => 'Media', 'content' => "[person_section id=\"articles\" heading=\"Recent Articles\" limit=\"6\"]\n\n[person_loop type=\"interview\" heading=\"Interviews\"]" ],
            'interviews'            => [ 'title' => 'Interviews', 'content' => '[person_loop type="interview"]' ],
            'podcasts'              => [ 'title' => 'Podcasts', 'content' => '' ],
        ];
    }

    /** @return array<string,string> Page key => default content, for Core's page manager. */
    public static function contents(): array {
        return array_map( static fn( array $template ): string => $template['content'], self::defaults() );
    }

    public static function default_template( string $key ): string {
        return self::defaults()[ $key ]['content'] ?? '';
    }
}
