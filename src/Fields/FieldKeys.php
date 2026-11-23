<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Fields;

use SFPF\PersonProfile\Settings\Settings;

defined( 'ABSPATH' ) || exit;

/** Maps SFPF's previous field and group identifiers to their canonical identifiers. */
final class FieldKeys {
    /** @var array<string,string> */
    public const RENAMED = [
        'field_64b623fbd4aa4' => 'field_sfpf_interview_press',
        'field_64bb58d291399' => 'field_sfpf_contributing_profile_url',
        'field_64bb8d69325e7' => 'field_sfpf_interview_additional_links',
        'field_64bb8d7a325e8' => 'field_sfpf_interview_primary_url',
        'field_64bc48f36f3a1' => 'field_sfpf_interview_podcast_name',
        'field_64bc93db4f0f8' => 'field_sfpf_contributing_profile_secondary_logo',
        'field_64bc93ee4f0f9' => 'field_sfpf_contributing_profile_featured_item',
        'field_64bc94084f0fa' => 'field_sfpf_contributing_profile_primary_featured_item',
        'field_64bf870639653' => 'field_sfpf_interview_guest_name',
        'field_64bf870e39654' => 'field_sfpf_interview_host_name',
        'field_sfpf_additional_name' => 'field_sfpf_person_additional_name',
        'field_sfpf_additional_url_source' => 'field_sfpf_person_additional_url_source',
        'field_sfpf_additional_url_title' => 'field_sfpf_person_additional_url_title',
        'field_sfpf_additional_url_url' => 'field_sfpf_person_additional_url_url',
        'field_sfpf_additional_urls' => 'field_sfpf_person_additional_urls',
        'field_sfpf_alt_name_value' => 'field_sfpf_person_alt_name_value',
        'field_sfpf_alternate_names' => 'field_sfpf_person_alternate_names',
        'field_sfpf_article_source' => 'field_sfpf_person_article_source',
        'field_sfpf_article_title' => 'field_sfpf_person_article_title',
        'field_sfpf_article_url' => 'field_sfpf_person_article_url',
        'field_sfpf_articles' => 'field_sfpf_person_articles',
        'field_sfpf_articles_import' => 'field_sfpf_person_articles_import',
        'field_sfpf_award_value' => 'field_sfpf_person_award_value',
        'field_sfpf_awards' => 'field_sfpf_person_awards',
        'field_sfpf_biography' => 'field_sfpf_person_biography',
        'field_sfpf_biography_short' => 'field_sfpf_person_biography_short',
        'field_sfpf_birth_date' => 'field_sfpf_person_birth_date',
        'field_sfpf_edu_college' => 'field_sfpf_person_edu_college',
        'field_sfpf_edu_designation' => 'field_sfpf_person_edu_designation',
        'field_sfpf_edu_major' => 'field_sfpf_person_edu_major',
        'field_sfpf_edu_wiki_url' => 'field_sfpf_person_edu_wiki_url',
        'field_sfpf_edu_year' => 'field_sfpf_person_edu_year',
        'field_sfpf_education_repeater' => 'field_sfpf_person_education_repeater',
        'field_sfpf_entity_type' => 'field_sfpf_person_entity_type',
        'field_sfpf_faq' => 'field_sfpf_person_faq',
        'field_sfpf_faq_answer' => 'field_sfpf_person_faq_answer',
        'field_sfpf_faq_question' => 'field_sfpf_person_faq_question',
        'field_sfpf_gallery' => 'field_sfpf_person_gallery',
        'field_sfpf_gender' => 'field_sfpf_person_gender',
        'field_sfpf_headquarters_group' => 'field_sfpf_person_headquarters_group',
        'field_sfpf_honorific_prefix' => 'field_sfpf_person_honorific_prefix',
        'field_sfpf_honorific_suffix' => 'field_sfpf_person_honorific_suffix',
        'field_sfpf_hq_location' => 'field_sfpf_person_hq_location',
        'field_sfpf_hq_wiki' => 'field_sfpf_person_hq_wiki',
        'field_sfpf_inception_date' => 'field_sfpf_person_inception_date',
        'field_sfpf_knowledge_graph_id' => 'field_sfpf_person_knowledge_graph_id',
        'field_sfpf_knowledge_graph_images' => 'field_sfpf_person_knowledge_graph_images',
        'field_sfpf_knows_language' => 'field_sfpf_person_knows_language',
        'field_sfpf_knows_language_value' => 'field_sfpf_person_knows_language_value',
        'field_sfpf_location_born' => 'field_sfpf_person_location_born',
        'field_sfpf_location_born_location' => 'field_sfpf_person_location_born_location',
        'field_sfpf_location_born_wikipedia' => 'field_sfpf_person_location_born_wikipedia',
        'field_sfpf_mission_statement' => 'field_sfpf_person_mission_statement',
        'field_sfpf_nationality' => 'field_sfpf_person_nationality',
        'field_sfpf_nationality_value' => 'field_sfpf_person_nationality_value',
        'field_sfpf_org_accordion_end' => 'field_sfpf_organization_accordion_end',
        'field_sfpf_org_addr_country' => 'field_sfpf_organization_addr_country',
        'field_sfpf_org_addr_locality' => 'field_sfpf_organization_addr_locality',
        'field_sfpf_org_addr_postal' => 'field_sfpf_organization_addr_postal',
        'field_sfpf_org_addr_region' => 'field_sfpf_organization_addr_region',
        'field_sfpf_org_addr_street' => 'field_sfpf_organization_addr_street',
        'field_sfpf_org_address' => 'field_sfpf_organization_address',
        'field_sfpf_org_alternate_names' => 'field_sfpf_organization_alternate_names',
        'field_sfpf_org_awards' => 'field_sfpf_organization_awards',
        'field_sfpf_org_brands' => 'field_sfpf_organization_brands',
        'field_sfpf_org_company_info' => 'field_sfpf_organization_company_info',
        'field_sfpf_org_contact_point' => 'field_sfpf_organization_contact_point',
        'field_sfpf_org_cp_email' => 'field_sfpf_organization_cp_email',
        'field_sfpf_org_cp_hours' => 'field_sfpf_organization_cp_hours',
        'field_sfpf_org_cp_product' => 'field_sfpf_organization_cp_product',
        'field_sfpf_org_cp_telephone' => 'field_sfpf_organization_cp_telephone',
        'field_sfpf_org_cp_type' => 'field_sfpf_organization_cp_type',
        'field_sfpf_org_cp_url' => 'field_sfpf_organization_cp_url',
        'field_sfpf_org_email' => 'field_sfpf_organization_email',
        'field_sfpf_org_founding_date' => 'field_sfpf_organization_founding_date',
        'field_sfpf_org_gallery' => 'field_sfpf_organization_gallery',
        'field_sfpf_org_header_address' => 'field_sfpf_organization_header_address',
        'field_sfpf_org_header_basic' => 'field_sfpf_organization_header_basic',
        'field_sfpf_org_header_contact' => 'field_sfpf_organization_header_contact',
        'field_sfpf_org_header_details' => 'field_sfpf_organization_header_details',
        'field_sfpf_org_header_founding' => 'field_sfpf_organization_header_founding',
        'field_sfpf_org_header_media' => 'field_sfpf_organization_header_media',
        'field_sfpf_org_header_schema' => 'field_sfpf_organization_header_schema',
        'field_sfpf_org_header_urls' => 'field_sfpf_organization_header_urls',
        'field_sfpf_org_headquarters' => 'field_sfpf_organization_headquarters',
        'field_sfpf_org_hq_location' => 'field_sfpf_organization_hq_location',
        'field_sfpf_org_hq_wikipedia' => 'field_sfpf_organization_hq_wikipedia',
        'field_sfpf_org_image_cropped' => 'field_sfpf_organization_image_cropped',
        'field_sfpf_org_knowledge_graph_id' => 'field_sfpf_organization_knowledge_graph_id',
        'field_sfpf_org_legal_name' => 'field_sfpf_organization_legal_name',
        'field_sfpf_org_mission_statement' => 'field_sfpf_organization_mission_statement',
        'field_sfpf_org_naics_code' => 'field_sfpf_organization_naics_code',
        'field_sfpf_org_number_of_employees' => 'field_sfpf_organization_number_of_employees',
        'field_sfpf_org_sameas_urls' => 'field_sfpf_organization_sameas_urls',
        'field_sfpf_org_schema' => 'field_sfpf_organization_schema',
        'field_sfpf_org_secondary_location' => 'field_sfpf_organization_secondary_location',
        'field_sfpf_org_seeks' => 'field_sfpf_organization_seeks',
        'field_sfpf_org_short_summary' => 'field_sfpf_organization_short_summary',
        'field_sfpf_org_social_urls' => 'field_sfpf_organization_social_urls',
        'field_sfpf_org_subtitle' => 'field_sfpf_organization_subtitle',
        'field_sfpf_org_telephone' => 'field_sfpf_organization_telephone',
        'field_sfpf_org_url' => 'field_sfpf_organization_url',
        'field_sfpf_org_url_crunchbase' => 'field_sfpf_organization_url_crunchbase',
        'field_sfpf_org_url_facebook' => 'field_sfpf_organization_url_facebook',
        'field_sfpf_org_url_github' => 'field_sfpf_organization_url_github',
        'field_sfpf_org_url_instagram' => 'field_sfpf_organization_url_instagram',
        'field_sfpf_org_url_linkedin' => 'field_sfpf_organization_url_linkedin',
        'field_sfpf_org_url_tiktok' => 'field_sfpf_organization_url_tiktok',
        'field_sfpf_org_url_wikipedia' => 'field_sfpf_organization_url_wikipedia',
        'field_sfpf_org_url_x' => 'field_sfpf_organization_url_x',
        'field_sfpf_org_url_youtube' => 'field_sfpf_organization_url_youtube',
        'field_sfpf_profession_name' => 'field_sfpf_person_profession_name',
        'field_sfpf_profession_page' => 'field_sfpf_person_profession_page',
        'field_sfpf_profession_summary' => 'field_sfpf_person_profession_summary',
        'field_sfpf_professions_repeater' => 'field_sfpf_person_professions_repeater',
        'field_sfpf_quote' => 'field_sfpf_quote_text',
        'field_sfpf_sameas' => 'field_sfpf_person_sameas',
        'field_sfpf_telephone' => 'field_sfpf_person_telephone',
        'field_sfpf_wikimedia_commons_url' => 'field_sfpf_person_wikimedia_commons_url',
        'field_sfpf_wikimedia_commons_urls' => 'field_sfpf_person_wikimedia_commons_urls',
        'group_64b623fb36208' => 'group_sfpf_interview',
        'group_64bb58d10c008' => 'group_sfpf_contributing_profile',
        'group_sfpf_user_schema_structures' => 'group_sfpf_person',
    ];

    /** Whether this site's stored references use the canonical identifiers. */
    public static function migrated(): bool {
        return '1' === (string) get_option( Settings::FIELD_KEYS_MIGRATED, '0' );
    }

    /** Returns the identifier that matches this site's stored references. */
    public static function active( string $canonical ): string {
        if ( self::migrated() ) {
            return $canonical;
        }

        return self::legacy_keys()[ $canonical ] ?? $canonical;
    }

    /** Keeps released code compatible with the previous data until the migration succeeds. */
    public static function active_group( array $canonical ): array {
        return self::migrated() ? $canonical : self::replace( $canonical, self::legacy_keys() );
    }

    /** Translates any form opened before migration, keeping its exact posted values. */
    public static function normalize_submission(): void {
        if ( self::migrated() && isset( $_POST['acf'] ) && is_array( $_POST['acf'] ) ) {
            // WordPress and Core verify permissions and nonces before saving this form.
            $_POST['acf'] = self::replace_submission_keys( $_POST['acf'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
        }
    }

    /** @return array<string,string> */
    private static function legacy_keys(): array {
        static $legacy;
        return $legacy ??= array_flip( self::RENAMED );
    }

    /** Rewrites only exact key references in definitions, including conditional fields. */
    private static function replace( array $values, array $keys ): array {
        foreach ( $values as $index => $value ) {
            $values[ $index ] = is_array( $value )
                ? self::replace( $value, $keys )
                : ( is_string( $value ) ? $keys[ $value ] ?? $value : $value );
        }
        return $values;
    }

    /** Rewrites field selectors in a submission without touching any scalar value. */
    private static function replace_submission_keys( array $submitted ): array {
        foreach ( $submitted as $key => $value ) {
            $canonical = self::RENAMED[ $key ] ?? $key;
            if ( $canonical !== $key ) {
                unset( $submitted[ $key ] );
                if ( array_key_exists( $canonical, $submitted ) ) {
                    continue;
                }
            }
            $submitted[ $canonical ] = is_array( $value ) ? self::replace_submission_keys( $value ) : $value;
        }
        return $submitted;
    }
}
