<?php
namespace sfpf_person_website;

/**
 * Schema Builder — single source of truth for all schema generation.
 * Each build_*_schema() returns a PHP array. Callers encode to JSON.
 *
 * @package sfpf_person_website
 * @since 1.6.2
 */

defined('ABSPATH') || exit;

// ─── Helpers ────────────────────────────────────────────────────────────────

/** Safe ACF getter with fallback */
function _sf($field, $post_id, $default = '') {
    return \Hexa\PluginCore\DataNormalization\FieldReader::acf_value($field, $post_id, $default, true);
}

/** Collect valid URLs from mixed args, deduplicated */
function _collect_urls(...$sources) {
    return \Hexa\PluginCore\DataNormalization\ValueNormalizer::url_values($sources);
}

/** Parse textarea of URLs (one per line) */
function _parse_url_textarea($text) {
    return \Hexa\PluginCore\DataNormalization\ValueNormalizer::url_values($text, true, false);
}

/** Pull one subfield value from each row of a repeater */
function _repeater_values($rows, $subfield = 'name') {
    return \Hexa\PluginCore\DataNormalization\ValueNormalizer::row_values($rows, $subfield);
}

/** Return string if 1 item, array if 2+, null if 0 */
function _single_or_array($arr) {
    return \Hexa\PluginCore\DataNormalization\ValueNormalizer::single_or_array(is_array($arr) ? $arr : []);
}

/** Encode schema through the shared Hexa WP Core renderer. */
function schema_json($schema) {
    if (!is_array($schema) || empty($schema) || !class_exists('\\Hexa\\PluginCore\\SchemaTools\\SchemaDocumentRenderer')) {
        return '';
    }
    return (new \Hexa\PluginCore\SchemaTools\SchemaDocumentRenderer())->json($schema);
}


// ═══════════════════════════════════════════════════════════════════════════
//  PERSON
// ═══════════════════════════════════════════════════════════════════════════

/**
 * Build comprehensive Person schema from founder user-profile ACF fields.
 *
 * @return array  Person schema node (no @context).
 */
function build_person_schema() {
    $founder = get_founder_full_info();
    if (!$founder) return [];

    $site_url = rtrim(get_site_url(), '/');
    $uid      = $founder['id'];
    $uk       = 'user_' . $uid;

    $p = [
        '@type'            => 'Person',
        '@id'              => $site_url . '/#person',
        'name'             => $founder['display_name'],
        'url'              => $site_url . '/',
        'mainEntityOfPage' => $site_url,
    ];

    // ── identity ──
    if (!empty($founder['first_name']))  $p['givenName']  = $founder['first_name'];
    if (!empty($founder['last_name']))   $p['familyName'] = $founder['last_name'];

    $an = _sf('additional_name', $uk);
    if ($an) $p['additionalName'] = sanitize_text_field($an);

    $alt = _repeater_values(_sf('alternate_names', $uk, []), 'name');
    $alt = _single_or_array(array_map('sanitize_text_field', $alt));
    if ($alt) $p['alternateName'] = $alt;

    // ── professional ──
    $tf = _sf('title', $uk);
    $jt = !empty($tf) ? wp_strip_all_tags($tf) : ($founder['job_title'] ?? '');
    if ($jt) $p['jobTitle'] = sanitize_text_field($jt);

    if (!empty($founder['email'])) $p['email'] = $founder['email'];

    $tel = _sf('telephone', $uk);
    if ($tel) $p['telephone'] = sanitize_text_field($tel);

    $hp = _sf('honorific_prefix', $uk);
    if ($hp) $p['honorificPrefix'] = sanitize_text_field($hp);

    $hs = _sf('honorific_suffix', $uk);
    if ($hs) $p['honorificSuffix'] = sanitize_text_field($hs);

    $g = _sf('gender', $uk);
    if ($g) $p['gender'] = sanitize_text_field($g);

    // ── biography / description ──
    $bio = _sf('biography', $uk);
    if (empty($bio)) $bio = $founder['biography'] ?? '';
    if ($bio) $p['description'] = wp_strip_all_tags(mb_substr($bio, 0, 500));

    // ── birth ──
    $bd = _sf('birth_date', $uk);
    if ($bd) $p['birthDate'] = sanitize_text_field($bd);

    $lb = _sf('location_born', $uk, []);
    if (!empty($lb['location'])) {
        $bp = ['@type' => 'Place', 'name' => sanitize_text_field($lb['location'])];
        $birth_place_url = \Hexa\PluginCore\DataNormalization\ValueNormalizer::url($lb['wikipedia_url'] ?? '');
        if ($birth_place_url) $bp['sameAs'] = $birth_place_url;
        $p['birthPlace'] = $bp;
    }

    // ── nationality (repeater) ──
    $nat = _sf('nationality', $uk, []);
    $nats = [];
    if (is_array($nat))        $nats = _repeater_values($nat, 'value');
    elseif (is_string($nat) && $nat) $nats = array_filter(array_map('trim', explode(',', $nat)));
    $nout = _single_or_array(array_map('sanitize_text_field', $nats));
    if ($nout) $p['nationality'] = $nout;

    // ── languages (repeater) ──
    $langs = _repeater_values(_sf('knows_language', $uk, []), 'value');
    $lo = _single_or_array(array_map('sanitize_text_field', $langs));
    if ($lo) $p['knowsLanguage'] = $lo;

    // ── awards (repeater) ──
    $aw = _repeater_values(_sf('awards', $uk, []), 'value');
    $ao = _single_or_array(array_map('sanitize_text_field', $aw));
    if ($ao) $p['award'] = $ao;

    // ── images: public gallery + KG gallery + avatar, deduplicated ──
    $img_urls = [];
    if (function_exists(__NAMESPACE__ . '\\sfpf_normalize_gallery_images')) {
        foreach (sfpf_normalize_gallery_images(_sf('gallery', $uk, []), 'full') as $image) {
            if (!empty($image['url'])) $img_urls[] = $image['url'];
        }
    }
    $kg = _sf('knowledge_graph_images', $uk, []);
    $img_urls = array_merge($img_urls, _collect_urls(_repeater_values($kg, 'url')));
    $img_urls = array_merge(
        $img_urls,
        _collect_urls(_repeater_values(_sf('wikimedia_commons_urls', $uk, []), 'url'))
    );
    $avatar = $founder['avatar_url'] ?? '';
    if (empty($avatar)) $avatar = get_avatar_url($uid, ['size' => 400]);
    $img_urls = array_merge($img_urls, _collect_urls($avatar));
    $img_urls = array_values(array_unique($img_urls));
    if (!empty($img_urls)) {
        $p['image'] = count($img_urls) === 1 ? $img_urls[0] : $img_urls;
    }

    // ── education / alumniOf + hasCredential ──
    $edu = _sf('education', $uk, []);
    if (is_array($edu) && !empty($edu)) {
        $alumni = [];
        $creds  = [];
        foreach ($edu as $e) {
            if (empty($e['college'])) continue;
            $entry = ['@type' => 'CollegeOrUniversity', 'name' => sanitize_text_field($e['college'])];
            $education_url = \Hexa\PluginCore\DataNormalization\ValueNormalizer::url($e['wiki_url'] ?? '');
            if ($education_url) $entry['sameAs'] = $education_url;
            $alumni[] = $entry;

            if (!empty($e['designation']) || !empty($e['major'])) {
                $c = ['@type' => 'EducationalOccupationalCredential'];
                $parts = [];
                if (!empty($e['designation'])) $parts[] = sanitize_text_field($e['designation']);
                if (!empty($e['major']))       $parts[] = sanitize_text_field($e['major']);
                $c['name'] = implode(' in ', $parts);
                $c['recognizedBy'] = ['@type' => 'CollegeOrUniversity', 'name' => sanitize_text_field($e['college'])];
                if (!empty($e['year'])) $c['dateCreated'] = sanitize_text_field($e['year']);
                $creds[] = $c;
            }
        }
        if ($alumni) $p['alumniOf']       = $alumni;
        if ($creds)  $p['hasCredential']  = $creds;
    }

    // ── professions / hasOccupation ──
    $profs = _sf('professions', $uk, []);
    if (is_array($profs) && !empty($profs)) {
        $occs = [];
        foreach ($profs as $pr) {
            if (empty($pr['name'])) continue;
            $o = ['@type' => 'Occupation', 'name' => sanitize_text_field($pr['name'])];
            if (!empty($pr['summary'])) $o['description'] = sanitize_text_field(wp_strip_all_tags($pr['summary']));
            $occs[] = $o;
        }
        if ($occs) $p['hasOccupation'] = $occs;
    }

    // ── worksFor (explicit founder-to-Organization relationships) ──
    $orgs = sfpf_founder_organization_ids((int) $uid);
    if ($orgs) {
        $wf = [];
        foreach ($orgs as $org_id) {
            $e = ['@type' => 'Organization', 'name' => sanitize_text_field(get_the_title($org_id))];
            $ou = \Hexa\PluginCore\DataNormalization\FieldReader::acf_value('url', $org_id);
            $organization_url = \Hexa\PluginCore\DataNormalization\ValueNormalizer::url($ou);
            if ($organization_url) $e['url'] = $organization_url;
            $wf[] = $e;
        }
        $p['worksFor'] = $wf;
    }

    // ── sameAs ──
    $sa = _collect_urls(
        $founder['urls'] ?? [],
        _parse_url_textarea(_sf('sameas', $uk)),
        sfpf_collect_wikidata_urls(_sf('urls_wikidata', $uk))
    );
    // Social media from website options
    if (function_exists('get_field')) {
        $wo = \Hexa\PluginCore\DataNormalization\FieldReader::acf_value('website', 'option', []);
        $sm = is_array($wo) ? ($wo['social_media'] ?? []) : [];
        $sa = array_merge($sa, _collect_urls($sm));
    }
    // Article and additional profile URLs
    foreach (['articles', 'additional_urls'] as $link_field) {
        $links = _sf($link_field, $uk, []);
        $sa = array_merge($sa, _collect_urls(_repeater_values($links, 'url')));
    }
    $sa = array_values(array_unique($sa));
    if ($sa) $p['sameAs'] = $sa;

    // ── Knowledge Graph ID ──
    $kgid = _sf('knowledge_graph_id', $uk);
    if ($kgid) {
        $p['identifier'] = [
            '@type'      => 'PropertyValue',
            'propertyID' => 'googleKgMID',
            'value'      => sanitize_text_field($kgid),
        ];
    }

    return $p;
}


// ═══════════════════════════════════════════════════════════════════════════
//  HOMEPAGE / BIOGRAPHY  (Person ± ProfilePage)
// ═══════════════════════════════════════════════════════════════════════════

/**
 * Build homepage / biography schema for injection AND dashboard preview.
 *
 * @param string $schema_type  person | profile_page_only | profile_page
 * @param int|null $page_id Specific page ID to model the ProfilePage node on
 * @return string|null  JSON string
 */
function build_homepage_schema_for_injection($schema_type, $page_id = null) {
    $person = build_person_schema();
    if (empty($person)) return null;

    $site_url   = rtrim(get_site_url(), '/');
    $site_name  = get_bloginfo('name');
    $site_desc  = get_bloginfo('description');
    $page_id    = $page_id ? (int) $page_id : (int) get_option('page_on_front');
    $page_url   = $page_id ? get_permalink($page_id) : home_url('/');
    $page_url   = $page_url ? $page_url : $site_url . '/';
    $page_name  = $site_name;
    $page_desc  = $site_desc;

    if ($page_id) {
        $stored_title = get_the_title($page_id);
        $stored_excerpt = wp_strip_all_tags((string) get_post_field('post_excerpt', $page_id));

        if (!empty($stored_title)) {
            $page_name = $stored_title;
        }

        if (!empty($stored_excerpt)) {
            $page_desc = $stored_excerpt;
        }
    }

    $schema = ['@context' => 'https://schema.org', '@graph' => []];

    // ProfilePage node
    $pp = [
        '@type'      => 'ProfilePage',
        '@id'        => $page_url . '#profilepage',
        'url'        => $page_url,
        'name'       => $page_name,
        'inLanguage' => 'en-US',
        'isPartOf'   => [
            '@type' => 'WebSite',
            '@id'   => $site_url . '/#website',
            'url'   => $site_url . '/',
            'name'  => $site_name,
        ],
    ];
    if ($page_desc) $pp['description'] = $page_desc;

    if ($page_id) {
        $dm = get_post_modified_time('c', true, $page_id);
        $dc = get_post_time('c', true, $page_id);
        if ($dm) $pp['dateModified'] = $dm;
        if ($dc) $pp['dateCreated']  = $dc;
    }

    // Primary image from person
    $pi = isset($person['image'])
        ? (is_array($person['image']) ? ($person['image'][0] ?? '') : $person['image'])
        : '';
    if ($pi) {
        $pp['primaryImageOfPage'] = [
            '@type' => 'ImageObject', '@id' => $page_url . '#primaryimage',
            'url' => $pi, 'contentUrl' => $pi,
        ];
    }

    switch ($schema_type) {
        case 'person':
            $schema['@graph'][] = $person;
            break;
        case 'profile_page_only':
            $pp['about'] = ['@id' => $site_url . '/#person'];
            $schema['@graph'][] = $pp;
            // Include full Person so @id resolves
            $schema['@graph'][] = $person;
            break;
        case 'profile_page':
        default:
            $pp['mainEntity'] = ['@id' => $site_url . '/#person'];
            $pp['about']      = ['@id' => $site_url . '/#person'];
            $schema['@graph'][] = $pp;
            $schema['@graph'][] = $person;
            break;
    }

    return schema_json($schema);
}

// Backward-compat wrappers
function build_person_schema_from_settings($include_context = false) {
    $p = build_person_schema();
    if ($include_context) $p = array_merge(['@context' => 'https://schema.org'], $p);
    return $p;
}
function build_homepage_schema($post_id, $schema_type = 'profile_page') {
    $j = build_homepage_schema_for_injection($schema_type, $post_id);
    return $j ? json_decode($j, true) : [];
}


// ═══════════════════════════════════════════════════════════════════════════
//  BOOK
// ═══════════════════════════════════════════════════════════════════════════

/**
 * Build comprehensive Book schema.
 *
 * @param int $post_id  Book CPT post ID
 * @return array  Schema array (with @context)
 */
function build_book_schema($post_id) {
    $site_url  = rtrim(get_site_url(), '/');
    $permalink = get_permalink($post_id);
    $title     = get_the_title($post_id);
    $slug      = get_post_field('post_name', $post_id);
    $founder   = get_founder_full_info();

    $s = [
        '@context' => 'https://schema.org',
        '@type'    => 'Book',
        '@id'      => $site_url . '#book-' . $slug,
        'name'     => sanitize_text_field($title),
    ];

    // URL — prefer Google Books if present, else permalink
    $gbu = _sf('google_books_url', $post_id);
    $s['url'] = \Hexa\PluginCore\DataNormalization\ValueNormalizer::url($gbu) ?: $permalink;

    // Author — full Person ref
    if ($founder) {
        $s['author'] = [
            '@type' => 'Person',
            '@id'   => $site_url . '/#person',
            'name'  => $founder['display_name'],
            'url'   => $site_url,
        ];
    }

    // Book imagery is owned by the native WordPress featured image.
    if ($th = get_the_post_thumbnail_url($post_id, 'full')) {
        $image_url = \Hexa\PluginCore\DataNormalization\ValueNormalizer::url($th);
        if ($image_url) $s['image'] = ['@type' => 'ImageObject', 'url' => $image_url];
    }

    $s['mainEntityOfPage'] = $permalink;

    // sameAs — all book URLs + permalink
    $sa = _collect_urls(
        $permalink,
        _sf('amazon_url', $post_id),
        _sf('audible_url', $post_id),
        _sf('google_books_url', $post_id),
        _sf('goodreads_url', $post_id),
        _sf('knowledge_graph_url', $post_id),
        _parse_url_textarea(_sf('sameas_urls', $post_id))
    );
    if ($sa) $s['sameAs'] = $sa;

    // Subtitle
    $sub = _sf('subtitle', $post_id);
    if ($sub) $s['alternativeHeadline'] = sanitize_text_field($sub);

    // Alternate names (repeater)
    $altr = _sf('alternate_names', $post_id, []);
    if (is_array($altr) && !empty($altr)) {
        $names = _repeater_values($altr, 'name');
        $out = _single_or_array(array_map('sanitize_text_field', $names));
        if ($out) $s['alternateName'] = $out;
    }

    // Description
    $desc = _sf('description', $post_id);
    if ($desc) $s['description'] = wp_strip_all_tags($desc);

    // Publisher
    $pub = _sf('publishing_company', $post_id);
    if ($pub) $s['publisher'] = ['@type' => 'Organization', 'name' => sanitize_text_field(wp_strip_all_tags($pub))];

    // ISBN
    $isbn = _sf('isbn', $post_id);
    if ($isbn) $s['isbn'] = sanitize_text_field($isbn);

    // Number of pages
    $np = _sf('number_of_pages', $post_id);
    if ($np) $s['numberOfPages'] = (int)$np;

    // Date published
    $dp = _sf('date_published', $post_id);
    if ($dp) $s['datePublished'] = sanitize_text_field($dp);

    // Book edition
    $ed = _sf('book_edition', $post_id);
    if ($ed) $s['bookEdition'] = sanitize_text_field($ed);

    // Book format
    $fmt = _sf('book_format', $post_id);
    if ($fmt) $s['bookFormat'] = 'https://schema.org/' . sanitize_text_field($fmt);

    // Language
    $lang = _sf('in_language', $post_id);
    $s['inLanguage'] = $lang ? sanitize_text_field($lang) : 'en';

    // Genre
    $genre = _sf('genre', $post_id);
    if ($genre) $s['genre'] = sanitize_text_field($genre);

    // Knowledge Graph ID
    $kgid = _sf('knowledge_graph_id', $post_id);
    if ($kgid) {
        $s['identifier'] = [
            '@type'      => 'PropertyValue',
            'propertyID' => 'googleKgMID',
            'value'      => sanitize_text_field($kgid),
        ];
    }

    return $s;
}


// ═══════════════════════════════════════════════════════════════════════════
//  ORGANIZATION
// ═══════════════════════════════════════════════════════════════════════════

/**
 * Delegate the historical SFPF Organization callback to its canonical owner.
 *
 * @param int $post_id  Organization CPT post ID
 * @return array  Schema array (with @context)
 */
function build_organization_schema($post_id) {
    return class_exists( '\\SMC\\OrganizationProfile\\Schema\\OrganizationSchema' )
        ? \SMC\OrganizationProfile\Schema\OrganizationSchema::build( (int) $post_id, true )
        : [];
}


// ═══════════════════════════════════════════════════════════════════════════
//  SAVE HOOKS
// ═══════════════════════════════════════════════════════════════════════════

function enable_schema_on_save() {
    add_action('save_post', __NAMESPACE__ . '\\handle_schema_on_save', 20, 2);
}

function handle_schema_on_save($post_id, $post) {
    if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) return;
    $ok = ['book', 'page'];
    if (!in_array($post->post_type, $ok, true)) return;
    if ($post->post_type === 'page' && !is_schema_managed_page_id($post_id)) return;
    generate_and_save_schema($post_id);
}
