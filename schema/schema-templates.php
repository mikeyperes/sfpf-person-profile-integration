<?php
namespace sfpf_person_website;

/**
 * Schema Templates
 * 
 * Provides raw schema skeleton templates without dynamic content.
 * These are the base structures that get populated by the schema builder.
 * 
 * @package sfpf_person_website
 * @since 1.0.0
 */

defined('ABSPATH') || exit;

/**
 * Get Person schema template (skeleton)
 * 
 * @return array Schema template
 */
function get_person_schema_template() {
    return [
        '@context' => 'https://schema.org',
        '@type' => 'Person',
        '@id' => '{site_url}/#person',
        'name' => '{name}',
        'givenName' => '{given_name}',
        'familyName' => '{family_name}',
        'alternateName' => '{alternate_names}',
        'jobTitle' => '{job_title}',
        'url' => '{url}',
        'email' => '{email}',
        'telephone' => '{telephone}',
        'gender' => '{gender}',
        'birthDate' => '{birth_date}',
        'birthPlace' => [
            '@type' => 'Place',
            'name' => '{birth_place}',
        ],
        'nationality' => '{nationality}',
        'image' => '{image}',
        'description' => '{description}',
        'alumniOf' => '{alumni_of}',
        'worksFor' => '{works_for}',
        'sameAs' => '{same_as}',
    ];
}

/**
 * Get ProfilePage schema template (skeleton)
 * 
 * @return array Schema template
 */
function get_profile_page_schema_template() {
    return [
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'ProfilePage',
                '@id' => '{site_url}/#profilepage',
                'url' => '{url}',
                'name' => '{name}',
                'description' => '{description}',
                'inLanguage' => '{language}',
                'isPartOf' => [
                    '@type' => 'WebSite',
                    '@id' => '{site_url}/#website',
                    'url' => '{site_url}',
                    'name' => '{site_name}',
                ],
                'primaryImageOfPage' => [
                    '@type' => 'ImageObject',
                    '@id' => '{site_url}/#headshot',
                    'url' => '{headshot_url}',
                    'contentUrl' => '{headshot_url}',
                    'width' => '{headshot_width}',
                    'height' => '{headshot_height}',
                ],
                'mainEntity' => [
                    '@id' => '{site_url}/#person',
                ],
            ],
            '{person_object}',  // Will be replaced with full Person object
        ],
    ];
}

/**
 * Get Book schema template (skeleton)
 * 
 * @return array Schema template
 */
function get_book_schema_template() {
    return [
        '@context' => 'https://schema.org',
        '@type' => 'Book',
        '@id' => '{permalink}/#book',
        'name' => '{title}',
        'url' => '{permalink}',
        'description' => '{description}',
        'image' => [
            '@type' => 'ImageObject',
            'url' => '{cover_url}',
        ],
        'author' => [
            '@id' => '{author_id}',
        ],
        'publisher' => [
            '@type' => 'Organization',
            'name' => '{publisher_name}',
        ],
        'inLanguage' => '{language}',
        'sameAs' => '{same_as}',
    ];
}

/**
 * Get Organization schema template (skeleton)
 * 
 * @return array Schema template
 */
function get_organization_schema_template() {
    return [
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        '@id' => '{permalink}/#organization',
        'name' => '{name}',
        'legalName' => '{legal_name}',
        'url' => '{url}',
        'description' => '{description}',
        'email' => '{email}',
        'logo' => [
            '@type' => 'ImageObject',
            'url' => '{logo_url}',
        ],
        'founder' => [
            '@id' => '{founder_id}',
        ],
        'foundingDate' => '{founding_date}',
        'numberOfEmployees' => '{num_employees}',
        'address' => [
            '@type' => 'PostalAddress',
            'streetAddress' => '{street_address}',
            'addressLocality' => '{locality}',
            'addressRegion' => '{region}',
            'postalCode' => '{postal_code}',
            'addressCountry' => '{country}',
        ],
        'contactPoint' => [
            '@type' => 'ContactPoint',
            'contactType' => '{contact_type}',
            'email' => '{contact_email}',
            'telephone' => '{contact_telephone}',
            'url' => '{contact_url}',
        ],
        'sameAs' => '{same_as}',
    ];
}

/**
 * Get all available schema templates
 * 
 * @return array Array of template info
 */
function get_all_schema_templates() {
    return [
        'person' => [
            'name' => 'Person',
            'description' => 'Schema for an individual person (e.g., the website owner)',
            'template' => get_person_schema_template(),
        ],
        'profile_page' => [
            'name' => 'ProfilePage',
            'description' => 'Schema for a profile page containing a Person entity',
            'template' => get_profile_page_schema_template(),
        ],
        'book' => [
            'name' => 'Book',
            'description' => 'Schema for a book entry',
            'template' => get_book_schema_template(),
        ],
        'organization' => [
            'name' => 'Organization',
            'description' => 'Schema for a company or organization',
            'template' => get_organization_schema_template(),
        ],
    ];
}

/**
 * Render schema template as formatted JSON for display
 * 
 * @param string $template_key Template key
 * @return string Formatted JSON
 */
function render_schema_template($template_key) {
    $templates = get_all_schema_templates();
    
    if (!isset($templates[$template_key])) {
        return '{}';
    }
    
    return json_encode(
        $templates[$template_key]['template'],
        JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
    );
}
