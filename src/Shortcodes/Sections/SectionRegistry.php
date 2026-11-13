<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Shortcodes\Sections;

use SFPF\PersonProfile\Person\PersonProfile;

defined( 'ABSPATH' ) || exit;

/** Every person section by its id. */
final class SectionRegistry {
    /** @var array<string,Section>|null */
    private static ?array $sections = null;

    /** @return array<string,Section> */
    public static function all(): array {
        if ( null === self::$sections ) {
            self::$sections = [];
            $sections = [
                new BiographySection(),
                new EducationSection(),
                new ProfessionsSection(),
                new LinkListSection( 'articles', 'Articles', static fn( PersonProfile $profile ): array => $profile->articles() ),
                new LinkListSection( 'additional_urls', 'Additional links', static fn( PersonProfile $profile ): array => $profile->additional_urls() ),
                new GallerySection(),
                new FaqSection(),
                new SocialsSection(),
                new ContactSection(),
                new BirthplaceSection(),
                new OrganizationsSection(),
                new KnowledgePanelSection(),
                new NationalitySection(),
                new ProfileSection(),
            ];
            foreach ( (array) apply_filters( 'sfpf_person_sections', $sections ) as $section ) {
                if ( $section instanceof Section ) {
                    self::$sections[ $section->id() ] = $section;
                }
            }
        }
        return self::$sections;
    }

    public static function get( string $id ): ?Section {
        return self::all()[ sanitize_key( $id ) ] ?? null;
    }
}
