<?php
namespace sfpf_person_website;

/**
 * Schema Builder: the person site's data mapped onto HexaWP Core's schema
 * library (SchemaNodes, SchemaSources, SchemaIds). Nothing is stored; the
 * nodes are built when a page is viewed and printed in Core's shared block.
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
        '@id'              => \Hexa\PluginCore\SchemaTools\SchemaIds::person(),
        'name'             => $founder['display_name'],
        'url'              => $site_url . '/',
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
        foreach (sfpf_normalize_gallery_images(sfpf_get_person_gallery($uid), 'full') as $image) {
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
    if (\Hexa\PluginCore\Fields\Field::available()) {
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
        $p['identifier'] = \Hexa\PluginCore\SchemaTools\SchemaNodes::property_value('googleKgMID', $kgid);
    }

    return \Hexa\PluginCore\SchemaTools\SchemaNodes::person($p);
}


// ═══════════════════════════════════════════════════════════════════════════
//  HOMEPAGE / BIOGRAPHY  (Person ± ProfilePage)
// ═══════════════════════════════════════════════════════════════════════════

/**
 * The person site's homepage or biography page nodes: the website, the
 * page, and the Person, built with HexaWP Core's schema library.
 *
 * @param string   $schema_type person | profile_page_only | profile_page
 * @param int|null $page_id     The page the ProfilePage describes
 * @return list<array<string,mixed>>
 */
function build_profile_page_nodes($schema_type, $page_id = null) {
    $person = build_person_schema();
    if (empty($person) || 'none' === $schema_type) return [];

    $person_id = \Hexa\PluginCore\SchemaTools\SchemaIds::person();
    $website   = \Hexa\PluginCore\SchemaTools\SchemaSources::website($person_id);
    if ('person' === $schema_type) {
        return [$website, $person];
    }

    $page_id  = $page_id ? (int) $page_id : (int) get_option('page_on_front');
    $page     = $page_id ? get_post($page_id) : null;
    $page_url = $page instanceof \WP_Post ? (string) get_permalink($page) : \Hexa\PluginCore\SchemaTools\SchemaIds::home();
    $person_image = is_array($person['image'] ?? null) && isset($person['image'][0]) ? $person['image'][0] : ($person['image'] ?? []);
    $image = \Hexa\PluginCore\SchemaTools\SchemaNodes::image($person_image, \Hexa\PluginCore\SchemaTools\SchemaIds::primary_image($page_url));

    $properties = [
        'primaryImageOfPage' => $image ? \Hexa\PluginCore\SchemaTools\SchemaNodes::ref((string) $image['@id']) : null,
        'about'              => \Hexa\PluginCore\SchemaTools\SchemaNodes::ref($person_id),
        'mainEntity'         => 'profile_page' === $schema_type ? \Hexa\PluginCore\SchemaTools\SchemaNodes::ref($person_id) : null,
    ];
    if ($page instanceof \WP_Post) {
        $node = \Hexa\PluginCore\SchemaTools\SchemaSources::post_page($page, 'ProfilePage', $properties + ['dateCreated' => get_post_time('c', true, $page)]);
    } else {
        $node = \Hexa\PluginCore\SchemaTools\SchemaNodes::web_page($properties + [
            '@id'         => \Hexa\PluginCore\SchemaTools\SchemaIds::webpage($page_url),
            'url'         => $page_url,
            'name'        => get_bloginfo('name'),
            'description' => get_bloginfo('description'),
            'isPartOf'    => \Hexa\PluginCore\SchemaTools\SchemaNodes::ref(\Hexa\PluginCore\SchemaTools\SchemaIds::website()),
            'inLanguage'  => \Hexa\PluginCore\SchemaTools\SchemaSources::language(),
        ], 'ProfilePage');
    }

    return array_values(array_filter([$website, $node, $person, $image]));
}

/**
 * The same nodes as a JSON document, for dashboard previews.
 *
 * @return string|null
 */
function build_homepage_schema_for_injection($schema_type, $page_id = null) {
    $nodes = build_profile_page_nodes($schema_type, $page_id);
    return $nodes ? schema_json(['@context' => 'https://schema.org', '@graph' => $nodes]) : null;
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
    $permalink = get_permalink($post_id);
    $title     = get_the_title($post_id);
    $founder   = get_founder_full_info();

    $s = [
        '@id'      => \Hexa\PluginCore\SchemaTools\SchemaIds::for_url((string) $permalink, 'book'),
        'name'     => sanitize_text_field($title),
    ];

    // URL — prefer Google Books if present, else permalink
    $gbu = _sf('google_books_url', $post_id);
    $s['url'] = \Hexa\PluginCore\DataNormalization\ValueNormalizer::url($gbu) ?: $permalink;

    // Author — full Person ref
    if ($founder) {
        $s['author'] = \Hexa\PluginCore\SchemaTools\SchemaNodes::ref(\Hexa\PluginCore\SchemaTools\SchemaIds::person());
    }

    // Book imagery is owned by the native WordPress featured image.
    $s['image'] = \Hexa\PluginCore\SchemaTools\SchemaSources::post_image((int) $post_id);
    $s['mainEntityOfPage'] = \Hexa\PluginCore\SchemaTools\SchemaNodes::ref(\Hexa\PluginCore\SchemaTools\SchemaIds::webpage((string) $permalink));

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
    if ($fmt) $s['bookFormat'] = sanitize_text_field($fmt);

    // Language
    $lang = _sf('in_language', $post_id);
    $s['inLanguage'] = $lang ? sanitize_text_field($lang) : 'en';

    // Genre
    $genre = _sf('genre', $post_id);
    if ($genre) $s['genre'] = sanitize_text_field($genre);

    // Knowledge Graph ID
    $kgid = _sf('knowledge_graph_id', $post_id);
    if ($kgid) {
        $s['identifier'] = \Hexa\PluginCore\SchemaTools\SchemaNodes::property_value('googleKgMID', $kgid);
    }

    return \Hexa\PluginCore\SchemaTools\SchemaNodes::book($s);
}

/**
 * A book page's nodes: the website, the book's page, the book and its author.
 *
 * @return list<array<string,mixed>>
 */
function build_book_nodes($post_id) {
    $post = get_post($post_id);
    $book = $post instanceof \WP_Post ? build_book_schema($post_id) : [];
    if (!$book) return [];

    $person = build_person_schema();
    $page = \Hexa\PluginCore\SchemaTools\SchemaSources::post_page($post, 'ItemPage', [
        'mainEntity' => \Hexa\PluginCore\SchemaTools\SchemaNodes::ref((string) $book['@id']),
        'about'      => \Hexa\PluginCore\SchemaTools\SchemaNodes::ref((string) $book['@id']),
    ]);

    return array_values(array_filter([
        \Hexa\PluginCore\SchemaTools\SchemaSources::website($person ? \Hexa\PluginCore\SchemaTools\SchemaIds::person() : ''),
        $page,
        $book,
        $person,
    ]));
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
