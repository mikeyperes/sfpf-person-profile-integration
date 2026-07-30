<?php

declare( strict_types=1 );

namespace sfpf_person_website;

/**
 * Founder birthplace, organizations, and biography section rendering.
 *
 * Callback names remain in the legacy namespace for template and hook compatibility.
 */

defined( 'ABSPATH' ) || exit;

// =============================================================================

/**
 * Display location born
 * format: link (default), text, inline
 */
function founder_display_location_born($user_id, $format = 'link') {
    $lb = get_field('location_born', 'user_' . $user_id);
    if (empty($lb) || empty($lb['location'])) {
        return '';
    }

    $location = esc_html($lb['location']);
    $wiki_url = $lb['wikipedia_url'] ?? '';

    switch ($format) {
        case 'text':
            return '<div class="founder-location-born"><span class="location-born-label">Location Born:</span> <span class="location-born-value">' . $location . '</span></div>';

        case 'inline':
            if ($wiki_url) {
                return '<span class="founder-location-born-inline"><a href="' . esc_url($wiki_url) . '" target="_blank" rel="noopener">' . $location . '</a></span>';
            }
            return '<span class="founder-location-born-inline">' . $location . '</span>';

        case 'link':
        default:
            $location_html = $location;
            if ($wiki_url) {
                $location_html = '<a href="' . esc_url($wiki_url) . '" target="_blank" rel="noopener">' . $location . '</a>';
            }
            return '<div class="founder-location-born"><span class="location-born-label">Birthplace:</span> <span class="location-born-value">' . $location_html . '</span></div>';
    }
}

// =============================================================================
// ORGANIZATIONS FOUNDED DISPLAY
// =============================================================================

/**
 * Display organizations founded
 * format: cards (default), list, compact
 */
function founder_display_organizations_founded($format = 'cards', $user_id = 0) {
    $orgs = sfpf_founder_organization_ids((int) $user_id);

    if (empty($orgs)) {
        return '';
    }

    $output = '<div class="founder-organizations-founded format-' . esc_attr($format) . '">';

    foreach ($orgs as $org_id) {
        $name      = esc_html(get_the_title($org_id));
        $url       = get_field('url', $org_id);
        $summary   = get_field('short_summary', $org_id);
        $founding   = get_field('founding_date', $org_id);
        $hq         = get_field('headquarters', $org_id);
        $hq_loc     = $hq['location'] ?? '';
        $logo_field = get_field('image_cropped', $org_id);
        $logo_url   = $logo_field['url'] ?? '';
        $permalink  = get_permalink($org_id);

        switch ($format) {
            case 'compact':
                $output .= '<div class="org-item org-compact">';
                $name_html = $permalink ? '<a href="' . esc_url($permalink) . '">' . $name . '</a>' : $name;
                $output .= '<span class="org-name">' . $name_html . '</span>';
                if ($founding) {
                    $output .= ' <span class="org-date">(' . esc_html($founding) . ')</span>';
                }
                if ($hq_loc) {
                    $output .= ' <span class="org-hq">— ' . esc_html($hq_loc) . '</span>';
                }
                $output .= '</div>';
                break;

            case 'list':
                $output .= '<div class="org-item org-list-item" style="margin-bottom:15px;padding-bottom:15px;border-bottom:1px solid #e5e7eb;">';
                $name_html = $permalink ? '<a href="' . esc_url($permalink) . '">' . $name . '</a>' : $name;
                $output .= '<div class="org-name" style="font-weight:600;font-size:16px;">' . $name_html . '</div>';
                $meta_parts = [];
                if ($founding) $meta_parts[] = esc_html($founding);
                if ($hq_loc) $meta_parts[] = esc_html($hq_loc);
                if ($url) $meta_parts[] = '<a href="' . esc_url($url) . '" target="_blank" rel="noopener">' . esc_html(preg_replace('#^https?://#', '', rtrim($url, '/'))) . '</a>';
                if (!empty($meta_parts)) {
                    $output .= '<div class="org-meta" style="font-size:13px;color:#6b7280;margin-top:4px;">' . implode(' · ', $meta_parts) . '</div>';
                }
                if ($summary) {
                    $output .= '<div class="org-summary" style="margin-top:8px;color:#374151;">' . wp_kses_post($summary) . '</div>';
                }
                $output .= '</div>';
                break;

            case 'cards':
            default:
                $output .= '<div class="org-item org-card" style="display:flex;gap:16px;margin-bottom:20px;padding:20px;background:#f9fafb;border-radius:8px;border:1px solid #e5e7eb;">';

                if ($logo_url) {
                    $output .= '<div class="org-logo" style="flex-shrink:0;width:80px;height:80px;">';
                    $output .= '<img src="' . esc_url($logo_url) . '" alt="' . $name . '" style="width:80px;height:80px;object-fit:contain;border-radius:8px;">';
                    $output .= '</div>';
                }

                $output .= '<div class="org-details" style="flex:1;min-width:0;">';
                $name_html = $permalink ? '<a href="' . esc_url($permalink) . '" style="text-decoration:none;color:inherit;">' . $name . '</a>' : $name;
                $output .= '<div class="org-name" style="font-weight:700;font-size:18px;margin-bottom:4px;">' . $name_html . '</div>';

                $meta_parts = [];
                if ($founding) $meta_parts[] = '<span class="org-date">' . esc_html($founding) . '</span>';
                if ($hq_loc) $meta_parts[] = '<span class="org-hq">' . esc_html($hq_loc) . '</span>';
                if (!empty($meta_parts)) {
                    $output .= '<div class="org-meta" style="font-size:13px;color:#6b7280;margin-bottom:8px;">' . implode(' · ', $meta_parts) . '</div>';
                }

                if ($summary) {
                    $output .= '<div class="org-summary" style="color:#374151;line-height:1.6;">' . wp_kses_post($summary) . '</div>';
                }

                if ($url) {
                    $output .= '<div class="org-url" style="margin-top:8px;"><a href="' . esc_url($url) . '" target="_blank" rel="noopener" style="font-size:13px;color:#2563eb;">' . esc_html(preg_replace('#^https?://#', '', rtrim($url, '/'))) . '</a></div>';
                }

                $output .= '</div>';
                $output .= '</div>';
                break;
        }
    }

    $output .= '</div>';
    return $output;
}

// =============================================================================
// BIO FULL DISPLAY (composite shortcode)
// =============================================================================

/**
 * Display full biography structure with all sections
 * Conditionally shows each section only if data exists
 */
function founder_display_bio_full($user_id) {
    $user_key = 'user_' . $user_id;
    $output = '<div class="founder-bio-full">';

    // Biography
    $bio = get_field('biography', $user_key);
    if (!empty($bio)) {
        $output .= '<div class="bio-section bio-section-biography">';
        $output .= '<h3>Biography</h3>';
        $output .= '<div class="bio-content">' . wp_kses_post($bio) . '</div>';
        $output .= '</div>';
    }

    // Alternate Names
    $alt_names = get_field('alternate_names', $user_key);
    if (!empty($alt_names) && is_array($alt_names)) {
        $names = array_filter(array_map(function($n) { return $n['name'] ?? ''; }, $alt_names));
        if (!empty($names)) {
            $output .= '<div class="bio-section bio-section-alternate-names">';
            $output .= '<h3>Also Known As</h3>';
            $output .= '<div class="bio-content">' . esc_html(implode(', ', $names)) . '</div>';
            $output .= '</div>';
        }
    }

    // Education
    $education_html = founder_display_education($user_id);
    if (!empty($education_html)) {
        $output .= '<div class="bio-section bio-section-education">';
        $output .= '<h3>Education</h3>';
        $output .= '<div class="bio-content">' . $education_html . '</div>';
        $output .= '</div>';
    }

    // Location Born
    $location_html = founder_display_location_born($user_id, 'link');
    if (!empty($location_html)) {
        $output .= '<div class="bio-section bio-section-location-born">';
        $output .= '<h3>Birthplace</h3>';
        $output .= '<div class="bio-content">' . $location_html . '</div>';
        $output .= '</div>';
    }

    // Organizations Founded
    $orgs_html = founder_display_organizations_founded('cards', $user_id);
    if (!empty($orgs_html)) {
        $output .= '<div class="bio-section bio-section-organizations">';
        $output .= '<h3>Organizations Founded</h3>';
        $output .= '<div class="bio-content">' . $orgs_html . '</div>';
        $output .= '</div>';
    }

    // Professions
    $professions_html = founder_display_professions($user_id);
    if (!empty($professions_html)) {
        $output .= '<div class="bio-section bio-section-professions">';
        $output .= '<h3>Professions</h3>';
        $output .= '<div class="bio-content">' . $professions_html . '</div>';
        $output .= '</div>';
    }

    // Social Links
    $socials_html = founder_display_socials($user_id);
    if (!empty($socials_html)) {
        $output .= '<div class="bio-section bio-section-socials">';
        $output .= '<h3>Connect</h3>';
        $output .= '<div class="bio-content">' . $socials_html . '</div>';
        $output .= '</div>';
    }

    $output .= '</div>';

    // Only return if we have at least one section
    if (strpos($output, 'bio-section') === false) {
        return '';
    }

    return $output;
}

// =============================================================================
// SANITIZE URLs BUTTON - PROFILE PAGE SCRIPT
