<?php

declare( strict_types=1 );

namespace sfpf_person_website;

/**
 * ACF user-profile field recovery, normalization, and duplicate-group guards.
 *
 * Callback names remain in the legacy namespace for template and hook compatibility.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Runtime ACF field filter: enrich Education History with LinkedIn/Crunchbase links
 */
add_filter('acf/prepare_field', function($field) {
    if (!$field || !is_array($field)) return $field;

    // Enrich Education History with LinkedIn/Crunchbase links
    if (isset($field['key']) && $field['key'] === 'field_sfpf_education_repeater') {
        $screen = function_exists('get_current_screen') ? get_current_screen() : null;
        if ($screen && ($screen->id === 'profile' || $screen->id === 'user-edit')) {
            $user_id = defined('IS_PROFILE_PAGE') && IS_PROFILE_PAGE ? get_current_user_id() : (isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0);
            if ($user_id) {
                $urls = get_field('urls', 'user_' . $user_id);
                $links = [];
                if (!empty($urls['linkedin'])) {
                    $links[] = '<a href="' . esc_url($urls['linkedin']) . '" target="_blank" style="color:#0a66c2;">LinkedIn ↗</a>';
                }
                if (!empty($urls['crunchbase'])) {
                    $links[] = '<a href="' . esc_url($urls['crunchbase']) . '" target="_blank" style="color:#0288d1;">Crunchbase ↗</a>';
                }
                if (!empty($links)) {
                    $field['instructions'] .= '<br><span style="color:#6b7280;">Profile: ' . implode(' &nbsp;|&nbsp; ', $links) . '</span>';
                }
            }
        }
    }

    return $field;
});

// ═══════════════════════════════════════════════════════════════════════════
// ACF REPEATER HYDRATION FIX (profile.php / user-edit.php)
//
// On user profile screens ACF's repeater load pipeline sometimes returns
// arrays with the correct row count but empty subfield values ("shell rows").
// When the form renders blank and gets submitted, ACF overwrites real data
// with empty strings.
//
// Fix has three layers:
//   1. acf/load_value   – rebuild from usermeta if value has no actual data
//   2. acf/prepare_field – render-stage injection (ACF may skip load_value)
//   3. acf/update_value  – save guard to prevent blank overwrites
//
// CRITICAL: ACF mutates $field['name'] during prepare_field to
// "acf[field_key]", so we NEVER use $field['name'] for meta lookups.
// Instead we use a hardcoded key→meta_name mapping.
// ═══════════════════════════════════════════════════════════════════════════

/**
 * Detect WordPress user profile screens via $pagenow.
 * get_current_screen() is unreliable in ACF hook contexts (too early/undefined).
 */
function sfpf_is_user_profile_screen() {
    if (!is_admin()) return false;
    $pagenow = $GLOBALS['pagenow'] ?? '';
    if (in_array($pagenow, ['profile.php', 'user-edit.php'], true)) return true;
    if (defined('IS_PROFILE_PAGE') && IS_PROFILE_PAGE) return true;
    $a = current_action();
    if (in_array($a, ['show_user_profile', 'edit_user_profile', 'personal_options_update', 'edit_user_profile_update'], true)) return true;
    return false;
}

/**
 * Get the user ID being edited on profile/user-edit screens.
 */
function sfpf_get_profile_target_user_id() {
    if (($GLOBALS['pagenow'] ?? '') === 'profile.php') {
        return (int) get_current_user_id();
    }
    if (isset($_GET['user_id'])) {
        return (int) $_GET['user_id'];
    }
    return 0;
}

/**
 * Map ACF field keys to their real usermeta base names.
 *
 * ACF mutates $field['name'] during prepare_field (e.g. "acf[field_sfpf_education_repeater]")
 * so we can NEVER trust it for building meta keys like "education_0_college".
 * This mapping is the single source of truth.
 */
function sfpf_repeater_meta_name($field) {
    static $map = [
        'field_sfpf_education_repeater'  => 'education',
        'field_sfpf_professions_repeater' => 'professions',
        'field_sfpf_alternate_names'     => 'alternate_names',
        'field_sfpf_nationality'         => 'nationality',
        'field_sfpf_knows_language'      => 'knows_language',
        'field_sfpf_awards'              => 'awards',
        'field_sfpf_articles'            => 'articles',
        'field_sfpf_additional_urls'     => 'additional_urls',
    ];
    $key = $field['key'] ?? '';
    return $map[$key] ?? '';
}

/**
 * Check whether a repeater value array has any actual subfield data.
 *
 * ACF can return an array with N rows but all subfields empty ("shell rows").
 * This returns true only if at least one row has one non-empty subfield.
 */
function sfpf_repeater_value_has_data($value, $field) {
    if (!is_array($value) || empty($value)) return false;
    $sub_fields = $field['sub_fields'] ?? [];
    if (empty($sub_fields)) return false;

    foreach ($value as $row) {
        if (!is_array($row)) continue;
        foreach ($sub_fields as $sf) {
            $v = $row[$sf['key']] ?? ($row[$sf['name']] ?? '');
            if ($v !== '' && $v !== null) return true;
        }
    }
    return false;
}

/**
 * Rebuild a repeater's value from usermeta.
 *
 * Reads the row count from get_user_meta($uid, $meta_name, true),
 * then reads each subfield: {meta_name}_{i}_{subfield_name}.
 */
function sfpf_rebuild_repeater_from_usermeta($user_id, $meta_name, $field) {
    $count = (int) get_user_meta($user_id, $meta_name, true);
    if ($count <= 0) return [];

    $sub_fields = $field['sub_fields'] ?? [];
    if (empty($sub_fields)) return [];

    $rows = [];
    for ($i = 0; $i < $count; $i++) {
        $row = [];
        foreach ($sub_fields as $sf) {
            $mk = "{$meta_name}_{$i}_{$sf['name']}";
            $row[$sf['key']] = get_user_meta($user_id, $mk, true);
        }
        $rows[] = $row;
    }
    return $rows;
}

/**
 * Normalize ACF's post_id to a numeric user ID.
 * ACF passes "user_3" or "3" depending on context.
 */
function sfpf_normalize_user_id($post_id) {
    if (is_string($post_id) && strpos($post_id, 'user_') === 0) {
        return (int) substr($post_id, 5);
    }
    if (is_numeric($post_id)) {
        return (int) $post_id;
    }
    return 0;
}

/**
 * acf/load_value handler for all repeaters.
 *
 * If ACF returns shell rows (correct count but empty subfields),
 * rebuild from usermeta. Uses key→meta_name mapping, not $field['name'].
 */
function sfpf_fix_repeater_load_value($value, $post_id, $field) {
    if (!sfpf_is_user_profile_screen()) return $value;

    $user_id = sfpf_normalize_user_id($post_id);
    if ($user_id <= 0) return $value;

    $meta_name = sfpf_repeater_meta_name($field);
    if (empty($meta_name)) return $value;

    // Only keep existing value if it has ACTUAL data (not shell rows)
    if (sfpf_repeater_value_has_data($value, $field)) {
        return $value;
    }

    return sfpf_rebuild_repeater_from_usermeta($user_id, $meta_name, $field);
}

/**
 * acf/update_value save guard for all repeaters.
 *
 * If a subfield arrives empty but DB has a value, keep the DB value.
 * Trims fully empty rows to prevent row count inflation.
 */
function sfpf_repeater_save_guard($value, $post_id, $field) {
    if (!sfpf_is_user_profile_screen()) return $value;

    $user_id = sfpf_normalize_user_id($post_id);
    if ($user_id <= 0) return $value;
    if (!is_array($value)) return $value;

    $sub_fields = $field['sub_fields'] ?? [];
    if (empty($sub_fields)) return $value;

    $meta_name = sfpf_repeater_meta_name($field);
    if (empty($meta_name)) return $value;

    $merged = [];
    foreach ($value as $i => $row) {
        if (!is_array($row)) $row = [];

        foreach ($sub_fields as $sf) {
            $incoming = $row[$sf['key']] ?? '';
            if ($incoming === '' || $incoming === null) {
                $existing = get_user_meta($user_id, "{$meta_name}_{$i}_{$sf['name']}", true);
                if ($existing !== '' && $existing !== null) {
                    $row[$sf['key']] = $existing;
                }
            }
        }

        // Only keep rows with at least one non-empty value
        $all_empty = true;
        foreach ($sub_fields as $sf) {
            if (($row[$sf['key']] ?? '') !== '') { $all_empty = false; break; }
        }
        if (!$all_empty) {
            $merged[] = $row;
        }
    }

    return $merged;
}

// Register all hooks for each repeater
$sfpf_repeater_keys = [
    'field_sfpf_education_repeater',
    'field_sfpf_professions_repeater',
    'field_sfpf_alternate_names',
    'field_sfpf_nationality',
    'field_sfpf_knows_language',
    'field_sfpf_awards',
    'field_sfpf_articles',
    'field_sfpf_additional_urls',
];

foreach ($sfpf_repeater_keys as $rk) {
    // Layer 1: Rebuild on load if shell rows
    add_filter("acf/load_value/key={$rk}", __NAMESPACE__ . '\\sfpf_fix_repeater_load_value', 20, 3);

    // Layer 2: Render-stage injection (ACF may skip load_value on profile.php)
    add_filter("acf/prepare_field/key={$rk}", function($field) {
        if (!$field || !is_array($field)) return $field;
        if (!sfpf_is_user_profile_screen()) return $field;

        $user_id = sfpf_get_profile_target_user_id();
        if ($user_id <= 0) return $field;

        $meta_name = sfpf_repeater_meta_name($field);
        if (empty($meta_name)) return $field;

        // Only inject if current value has no actual data
        if (!sfpf_repeater_value_has_data($field['value'] ?? null, $field)) {
            $rebuilt = sfpf_rebuild_repeater_from_usermeta($user_id, $meta_name, $field);
            if (!empty($rebuilt)) {
                $field['value'] = $rebuilt;
            }
        }

        return $field;
    }, 50);

    // Layer 3: Save guard
    add_filter("acf/update_value/key={$rk}", __NAMESPACE__ . '\\sfpf_repeater_save_guard', 1, 3);
}

/**
 * Block duplicate ACF field groups from loading.
 * Prevents DB-stored copies from overriding code-registered groups.
 */
add_filter('acf/load_field_groups', function($field_groups) {
    if (!is_array($field_groups)) return $field_groups;

    $blocked_prefixes = ['group_hws_', 'group_sfpf_'];

    return array_filter($field_groups, function($group) use ($blocked_prefixes) {
        if (!isset($group['key'])) return true;
        $is_db = isset($group['ID']) && $group['ID'] > 0;
        if (!$is_db) return true;
        foreach ($blocked_prefixes as $prefix) {
            if (strpos($group['key'], $prefix) === 0) return false;
        }
        return true;
    });
});
