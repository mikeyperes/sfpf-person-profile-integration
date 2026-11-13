# SFPF Person Profile Integration

> Feature base for HWS Skills. Read before building on this plugin; use or
> extend these features instead of rebuilding them.

**Purpose:** person profile websites for SEO for Public Figures: the person's fields, schema for search engines and the Google Knowledge Panel, shortcodes and Elementor tags for every value and section, the person pages and menus, and the person's content types. **Admin:** WP Admin → Settings → SFPF Person Profile (`options-general.php?page=sfpf-person-profile`). **Depends on:** HWS Base Tools (chooses the person, owns the social links on the profile, controls Rank Math's schema); Elementor and Elementor Pro optional; Rank Math recommended.

## Design examples and styling

- Every section's HTML, classes and CSS variables: [docs/sections.md](docs/sections.md)
- The schema each page carries: [docs/schema.md](docs/schema.md)
- Where the plugin's CSS and JS load: [docs/asset-loading.md](docs/asset-loading.md)
- How the code is organized: [docs/architecture.md](docs/architecture.md)
- Live check on any site: `/sfpf-profile-debug/` (administrators) shows every value, section and the schema of the key pages.

## Features

### The website's person
- **Does:** every value comes from one WordPress user: the Person chosen as the site's primary entity in HWS Base Tools (a user, or the user bound to a person post). Older sites that set a founder user keep working.
- **Switch:** HWS Base Tools → Website Profile.
- **Use:** automatic. `\SFPF\PersonProfile\Person\PersonResolver::profile()` returns a `PersonProfile` with every normalized value (names, title, biographies, contact, birth, nationality, languages, awards, education, professions, organizations, links, photos, Knowledge Graph ID, FAQ).
- **Extend:** `sfpf_person_user_id` (int) changes who the person is.
- **Code:** `src/Person/PersonResolver.php`, `src/Person/PersonProfile.php`.

### Person fields
- **Does:** the person's identity, title, biographies, mission, professions (each with an optional page), education, birth details, nationality, languages, awards, profile links, articles, additional pages, Knowledge Graph ID and photos, Wikimedia Commons photos and FAQ, on their WordPress user profile. Pasting an article list imports titles and sources; a pasted Google link saves just the Knowledge Graph ID.
- **Switch:** on from the start; Content Types → Person Fields can turn the group off (`sfpf_acf_structure_settings`).
- **Use:** WP Admin → Users → the person. Field names are stored data and never change (`src/Person/PersonFields.php`).
- **Code:** `src/Fields/PersonProfileFields.php`, `src/Admin/Profile/`, `src/Fields/RepeaterHydration.php`.

### Schema (search engines and the Knowledge Panel)
- **Does:** every page prints one schema block, with the person as one Person node referenced everywhere: the front page is a ProfilePage whose main entity is the person; the Biography page a ProfilePage (or AboutPage); every other page part of the person's website and about the person, with its breadcrumb (Connect is a ContactPage, Gallery an ImageGallery, list pages CollectionPages); FAQPage on the FAQs page and wherever an FAQ shows; Book, Organization (founder: the person), ExhibitionEvent, Quotation, NewsArticle (press releases), CreativeWork (interviews) and ItemPage on their pages, CollectionPage with ItemList on their lists; ProfilePage on author pages. The Person carries names, title, description, contact, birth, nationality (Country), languages, education (alumniOf, hasCredential), professions (hasOccupation), awards (from the profile and Award records), photos, organizations founded (worksFor), profile links (sameAs, with Wikidata and contributing profiles), articles about them (subjectOf) and the Knowledge Graph ID (identifier).
- **Switch:** on from the start. Schema tab: each structure's mode or Off (`sfpf_homepage_schema_type`, `sfpf_biography_schema_type`, `hexa_plugin_core_schema_structure_modes`). Rank Math's own schema: HWS Base Tools.
- **Use:** automatic. Preview any structure in the Schema tab or with `wp hexa schema preview --owner=sfpf-person --id=homepage`; `wp hexa schema list --owner=sfpf-person`; the Schema tab also scans the live key pages.
- **Extend:** `sfpf_person_schema` (array Person node, PersonProfile); `hexa_plugin_core_schema_graph` (the merged page graph). Structures are declared to Hexa WP Core's `SchemaStructures`.
- **Code:** `src/Schema/` (`SchemaCatalog.php`, `PersonSchema.php`, `PageSchema.php`, `Structures/`).

### [person] values
- **Does:** prints one value of the person.
- **Switch:** always on.
- **Use:** `[person field="title"]`. Fields: name, first_name, last_name, display_name, additional_name, alternate_names, honorific_prefix, honorific_suffix, title, biography, biography_short, mission_statement, professions, education, awards, languages, birth_date, birthplace, birthplace_url, nationality, gender, email, phone, telephone, website, social (with network="linkedin"), same_as, articles, additional_urls, knowledge_graph_id, knowledge_panel_url, avatar (size="512"), gallery, knowledge_graph_images, wikimedia_photos, faq, or any stored field name. Shared attributes: format (auto, text, html, url, link, list, json, count), separator (", "), link ("false"), target, pretty ("false"), index, key, fallback.
- **Code:** `src/Shortcodes/PersonShortcode.php`, `src/Person/PersonFieldValues.php`, `src/Shortcodes/Output.php`.

### [person_section] blocks
- **Does:** a ready-made block of the person's page. A section without content prints nothing, heading included.
- **Switch:** always on.
- **Use:** `[person_section id="education" layout="list" heading="Education"]`. Sections and layouts: biography (part="full|short"), education (list, inline), professions (list, names; page="link|content|none"), articles and additional_urls (titled, cards, sources, compact; limit), gallery (grid; columns, size, limit, source="photos|knowledge_graph"), faq (accordion, list; first_open), socials (list, inline; source="person|site", networks="linkedin,x"), contact (list; socials), birthplace (link, text, inline; label), organizations (cards, list, compact; limit), knowledge_panel (text), nationality, profile (parts="biography,education,…"). Shared: heading, heading_tag, class, style="none", fallback.
- **Style:** `.sfpf-section`, `.sfpf-<section>`, `.sfpf-<section>--<layout>` and BEM children; variables `--sfpf-gap`, `--sfpf-radius`, `--sfpf-border`, `--sfpf-muted`, `--sfpf-surface`, `--sfpf-gallery-columns`. Colors and fonts come from the page.
- **Example:** [docs/sections.md](docs/sections.md).
- **Extend:** `sfpf_person_sections` (list of `Section` objects) adds or replaces sections.
- **Code:** `src/Shortcodes/PersonSectionShortcode.php`, `src/Shortcodes/Sections/`, `assets/frontend/person-sections.css`.

### [book] and [organization]
- **Does:** a book's or an organization's value, or an organization's profile or gallery block: the one whose page is open, the one with id="…", else the primary one.
- **Switch:** the Book and Organization content types (Content Types tab); primary book in Settings (`sfpf_primary_book`); primary organization in HWS Base Tools.
- **Use:** `[book field="amazon_url" link="true" target="_blank"]` (title, cover, permalink or any book field); `[organization field="url"]` (name, url, logo, headquarters_location, founding_date or any field); `[organization section="profile"]`, `[organization section="gallery" columns="3"]`. Older `[organization id="url"]` and `action="display_profile"` work.
- **Code:** `src/Shortcodes/BookShortcode.php`, `src/Shortcodes/OrganizationShortcode.php`, `src/ContentTypes/Book/BookRecord.php`, `src/ContentTypes/Organization/OrganizationRecord.php`.

### [person_loop] lists
- **Does:** the newest posts of a content type in a responsive grid, each drawn with its Elementor loop item template, or title and summary.
- **Switch:** Loop Templates tab (`sfpf_elementor_loop_assignments`).
- **Use:** `[person_loop type="book" columns="3" rows="2"]`; older `[sfpf_loop cpt="book"]`.
- **Style:** `.sfpf-loop`, `.sfpf-loop__item`, `--sfpf-loop-columns`.
- **Extend:** `sfpf_loop_default_limit`, `sfpf_loop_hard_max`.
- **Code:** `src/Shortcodes/ContentLoopShortcode.php`.

### FAQ sets
- **Does:** site-wide question lists, shown as an accordion or list; their FAQ schema joins the page's schema block.
- **Switch:** FAQ Sets tab (`sfpf_faq_sets`, `sfpf_primary_faq_set`).
- **Use:** `[faq_set slug="main" layout="accordion"]`; one question: `index="0"`. Older `[sfpf_faq set="…"]`, `[sfpf_faq_schema]` (now prints nothing) and `[sfpf_elementor_faq]` work.
- **Code:** `src/Faq/FaqSets.php`, `src/Shortcodes/FaqSetShortcode.php`.

### Older shortcodes
- **Does:** `[founder id="…"]` and `[founder action="display_…"]`, `[sfpf_person_faq]`, `[person_faq]` keep showing the same content through today's shortcodes; `[founder id="name"]` keeps its first/last name spans.
- **Code:** `src/Shortcodes/LegacyShortcodes.php`.

### Elementor dynamic tags and display conditions
- **Does:** SFPF Person group of dynamic tags: Person value (text), Person link (social profiles, website, email, phone, Knowledge Panel, birthplace page) and Person photo (portrait or a gallery photo). Elementor Pro display condition "SFPF Person content" shows an element only when a section (or the books) has content. Empty icons are removed from Social Icons widgets.
- **Switch:** tags always on; conditions and icon cleanup on by default in Settings (`sfpf_elementor_dynamic_visibility`, `sfpf_hide_empty_elementor_social_icons`).
- **Code:** `src/Elementor/`.

### Person pages and menus
- **Does:** Biography (with Education, Location Born, Organizations Founded, Alternate Names), Professions, Recent Articles, Additional URLs, Gallery, Connect, FAQs, Media, Interviews and Podcasts: link or create each, edit and apply its starting content (built from shortcodes), build the header, footer and sub-footer menus; a page per profession under Professions.
- **Switch:** Pages & Menus tab (`sfpf_page_<key>`, `sfpf_template_<key>`).
- **Use:** the catalog `\SFPF\PersonProfile\Pages\PageTypes::definitions()` (pages, content-type pages and lists) for setup and redesign tools.
- **Code:** `src/Pages/`.

### Content types
- **Does:** Organization, Book, Press Release, Interview, Contributing Profile, Quote, Award and Exhibition, each off until turned on, with its fields, schema and sitemap.
- **Switch:** Content Types, Awards and Exhibitions tabs (`sfpf_content_type_settings`).
- **Code:** `src/ContentTypes/<Type>/` (`<Type>ContentType.php`, `<Type>Fields.php`), `src/ContentTypes/ContentTypeCatalog.php`.

### Author pages
- **Does:** /author/name/ shows the author's profile (portrait, title, bio, social profiles, facts, biography, education, links) when no Elementor archive template covers it, with ProfilePage schema and without Rank Math's copy.
- **Switch:** Settings → Author profile pages (`sfpf_author_archive_profile`), on.
- **Style:** `.sfpf-author-profile` and its children (`assets/frontend/author-profile.css`).
- **Code:** `src/Frontend/AuthorArchive.php`.

### Breadcrumb visibility
- **Does:** hides Rank Math's visible breadcrumb trail on the front page, chosen pages and single pages of chosen content types.
- **Switch:** Breadcrumbs tab (`sfpf_breadcrumb_hide_frontpage`, `sfpf_breadcrumb_excluded_pages`, `sfpf_breadcrumb_excluded_cpts`).
- **Code:** `src/Frontend/BreadcrumbVisibility.php`.

### Sitemaps
- **Does:** declares the content types' sitemaps and the person-website authors rule; the Sitemaps tab shows Rank Math's state and turns each on or off.
- **Code:** `src/Sitemaps/SitemapTypes.php`.

### Profile report
- **Does:** `/sfpf-profile-debug/` (administrators; `?format=json`) lists every value, section, linked page and the schema of the key pages.
- **Code:** `src/Diagnostics/`.

## Internals

- Bootstrap: `initialization.php` loads `src/PluginRuntime/Bootstrap.php`, which adds every feature as a Hexa WP Core module.
- Upgrades run once each on admin, cron and command-line requests (`src/Migrations/`, `sfpf_migrations`).
- Admin: one page with grouped tabs (`src/Admin/`), Core's update center and Hexa Core tab.
- Activity log: `sfpf_activity_log` through Core's activity log.

## Requirements

WordPress 6.0, PHP 8.1, Hexa WP Core 3.61.0 (bundled at `lib/hexa-wordpress-plugin-core`). ACF Pro is not required.

## Development

`php tests/run.php` runs the standalone suite (architecture, stored field keys, links, output formats, article import, upgrades, query limits).

## Changelog

See [CHANGELOG.md](CHANGELOG.md).
