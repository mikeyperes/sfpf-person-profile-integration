<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Person;

defined( 'ABSPATH' ) || exit;

/**
 * The names of the person's fields on the WordPress user. These are stored
 * data: they never change, because live sites, Notion imports and Code's
 * audit tool read and write them by these names.
 */
final class PersonFields {
    // SFPF person fields.
    public const ENTITY_TYPE       = 'entity_type';
    public const TITLE             = 'title';
    public const BIOGRAPHY         = 'biography';
    public const BIOGRAPHY_SHORT   = 'biography_short';
    public const MISSION           = 'mission_statement';
    public const ADDITIONAL_NAME   = 'additional_name';
    public const ALTERNATE_NAMES   = 'alternate_names';
    public const LOCATION_BORN     = 'location_born';
    public const BIRTH_DATE        = 'birth_date';
    public const NATIONALITY       = 'nationality';
    public const GENDER            = 'gender';
    public const TELEPHONE         = 'telephone';
    public const HONORIFIC_PREFIX  = 'honorific_prefix';
    public const HONORIFIC_SUFFIX  = 'honorific_suffix';
    public const LANGUAGES         = 'knows_language';
    public const AWARDS            = 'awards';
    public const PROFESSIONS       = 'professions';
    public const EDUCATION         = 'education';
    public const SAME_AS           = 'sameas';
    public const ARTICLES          = 'articles';
    public const ADDITIONAL_URLS   = 'additional_urls';
    public const FAQ               = 'faq';
    public const KNOWLEDGE_GRAPH_ID = 'knowledge_graph_id';
    public const KNOWLEDGE_IMAGES  = 'knowledge_graph_images';
    public const WIKIMEDIA_PHOTOS  = 'wikimedia_commons_urls';
    public const LEGACY_GALLERY    = 'gallery';
    public const INCEPTION_DATE    = 'inception_date';
    public const HEADQUARTERS      = 'headquarters';

    // Fields HWS Base Tools owns on every user profile.
    public const SOCIAL_URLS       = 'urls';
    public const CONTACT           = 'additional';
    public const PHOTOS            = 'field_hws_user_profile_2025_photos';

    /** Repeaters by field key, with the meta name their rows are stored under. */
    public const REPEATERS = [
        'field_sfpf_education_repeater'     => self::EDUCATION,
        'field_sfpf_professions_repeater'   => self::PROFESSIONS,
        'field_sfpf_alternate_names'        => self::ALTERNATE_NAMES,
        'field_sfpf_nationality'            => self::NATIONALITY,
        'field_sfpf_knows_language'         => self::LANGUAGES,
        'field_sfpf_awards'                 => self::AWARDS,
        'field_sfpf_articles'               => self::ARTICLES,
        'field_sfpf_additional_urls'        => self::ADDITIONAL_URLS,
        'field_sfpf_wikimedia_commons_urls' => self::WIKIMEDIA_PHOTOS,
        'field_sfpf_faq'                    => self::FAQ,
    ];

    /** Social networks HWS Base Tools stores in the user's URLs group, with their names. */
    public const SOCIAL_NETWORKS = [
        'website'    => 'Website',
        'linkedin'   => 'LinkedIn',
        'x'          => 'X',
        'twitter'    => 'X',
        'facebook'   => 'Facebook',
        'instagram'  => 'Instagram',
        'youtube'    => 'YouTube',
        'tiktok'     => 'TikTok',
        'threads'    => 'Threads',
        'github'     => 'GitHub',
        'crunchbase' => 'Crunchbase',
        'muckrack'   => 'Muck Rack',
        'wikipedia'  => 'Wikipedia',
        'wikidata'   => 'Wikidata',
        'imdb'       => 'IMDb',
        'f6s'        => 'F6S',
        'the_org'    => 'The Org',
        'wellfound'  => 'Wellfound',
        'soundcloud' => 'SoundCloud',
        'amazon'     => 'Amazon',
        'audible'    => 'Audible',
        'calendly'   => 'Calendly',
        'whatsapp'   => 'WhatsApp',
        'telegram'   => 'Telegram',
        'signal'     => 'Signal',
    ];
}
