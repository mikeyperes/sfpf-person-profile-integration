# Changelog

## 4.0.2

- Organization pages get schema: an ItemPage about the Organization (name, legal name, website, summary, logo, founding date, headquarters, address, contact, brands, awards, profiles and Knowledge Graph ID), with the person as founder, and a CollectionPage on the organizations list.
- Older shortcodes look as they did before 4.0: article links keep their ↗ arrow, article cards keep their source, education reads "school - degree", compact organizations read "name — place", and `[founder action="display_bio_full"]` leaves out the social links block.
- `[faq_set]` without a slug shows the primary FAQ set again.
- The title value no longer comes wrapped in a paragraph, so it fits in headings and Elementor's text tags.
- `[person_loop]` prints its fallback when the content type is off.
- Article import keeps addresses pasted without https:// when the same paste also has full links, and ignores the visible text of links.
- Set Up Pages finds an existing articles page at /recent-press/, /press/ or /articles/, and a contact page at /contact/.

## 4.0.1

- Removes a PHP error log that was committed by mistake and keeps error logs out of the repository.

## 4.0.0

- Rebuilt on the modern structure: every feature is a Hexa WP Core module in its own namespace under `src/` (PluginRuntime, Person, Fields, ContentTypes/<Type>, Schema, Shortcodes, Pages, Elementor, Frontend, Admin, Migrations, Diagnostics, Support). The pre-4.0 `includes/`, `admin/`, `snippets/` and `schema/` folders are gone; every stored field key and option name is unchanged.
- Schema ships working: each page type is a structure in Hexa WP Core 3.61.0's `SchemaStructures` registry, on by default, with modes, a Schema tab with live previews and a live-page scan, and `wp hexa schema`. New: ProfilePage with the person as main entity on the homepage by default, schema on every other page (about the person, with breadcrumbs), FAQPage in the shared block instead of inline scripts, Award, Exhibition, Quote, Press Release, Interview and Contributing Profile schema, author ProfilePages, and a fuller Person (Country nationality, credentials, occupations with their pages, award records, original-size photos, Wikimedia images, subjectOf articles, contributing profiles in sameAs).
- Shortcodes restructured: `[person field]` for every value and `[person_section id]` for every block, with one shared attribute set (format, separator, link, target, pretty, index, key, fallback, heading, class, style="none"); `[person_loop]` and `[faq_set]`; `[book]` and `[organization]` share the same output rules. Older shortcodes keep working.
- Elementor dynamic tags for person values, links and photos; display conditions cover every section.
- Sections print their styles once, only on pages that show them, and use the page's colors and fonts.
- Admin rebuilt on Core's tab shell: Status (readiness and Knowledge Panel checks), Person Values, Shortcodes, Schema, Content Types, Awards, Exhibitions, Pages & Menus, Loop Templates, FAQ Sets, Breadcrumbs, Settings, Sitemaps, Update Center, Debug.
- The person's field group is on from the start; content types bring their fields with them.
- Article import fetches titles safely (no disabled certificate checks, no requests to private addresses) and is limited to profile editors.


## 3.4.0

- Sitemaps tab and plugin-owned sitemap declarations (Hexa WP Core 3.51.0).

## 3.3.4

- Consolidates the person page catalog, conditional registered content layouts and current bundled Core guide.

## 3.3.0

- The homepage, biography page and books are built with Hexa WP Core 3.22.0's shared schema library and printed in Core's one schema block per page. Books are on an ItemPage with the Book as its main entity.
- Schema is no longer saved into posts; dashboard previews and the read-only Schema Markup fields show the live schema.
- Removed SFPF's own Rank Math schema switches; Rank Math's schema is one HWS Base Tools setting. Removed the unused schema templates.

## 3.2.1

- Registers the Book quote migration hook with Core's load-safe `hexa_fields_on()`: 3.2.0 called `Hooks::on()` while the plugin loaded, before Core's classes were available, which stopped the site with a fatal error.
- Synchronizes the bundled Hexa WordPress Plugin Core to 3.4.11.

## 3.2.0

- Removes the ACF Pro requirement: every field structure, field read/write, row loop and ACF hook runs on Hexa WP Core 3.4.7 `Fields` (ACF when active, native otherwise). Hooks registered while the plugin loads use Core's load-safe `hexa_fields_on()`.
- Removes the ACF Pro recommendation notice, plugin-check entry and structure dependency labels; the overview reports the active field mode.
- Synchronizes the bundled Hexa WordPress Plugin Core to 3.4.7; tests use Core's shared Fields test support.

## 3.1.5

- Makes `[founder id="gallery"]` render the canonical HWS user-profile Photos field, retaining the former SFPF Gallery field only as a non-destructive fallback.
- Uses the same canonical HWS gallery for Person schema images and profile diagnostics.

## 3.1.4

- Adds a canonical `quotes` repeater with quote text, source URL, and attribution/tagline fields to Book.
- Migrates Book and legacy `hbs-book` quote values from known repeater and singular fields without deleting source metadata.

## 3.1.3

- Adds an optional Quote custom post type with an independently enabled Quote Fields ACF group.
- Registers clean quote, attribution, source URL, logo gallery, and publication metadata fields; logos support sanitized SVG and common raster MIME types.
- Surfaces the Quote CPT and field-group controls through the generic Hexa WordPress Plugin Core settings UI.

## 3.1.2

- Synchronizes the bundled Hexa WordPress Plugin Core to 3.0.5.
- Shows structured ACF field label, name, and type with a collapsed JSON breakdown while keeping legacy text-only inventories compatible.

## 3.1.1

- Synchronizes the bundled Hexa WordPress Plugin Core to 3.0.4.
- CPT and ACF titles now appear plainly in their card headers with functional enable switches.
- Each CPT's ACF cards render outside and immediately below that CPT accordion.

## 3.1.0

- Makes SFPF the canonical registrar for the Organization CPT and its existing ACF field group, with a non-destructive migration from HWS settings.
- Registers `[organization]` after legacy snippets, supports canonical `field=` and legacy `id=` field syntax, and normalizes cropped-logo and featured-image URLs.

## 3.0.3

- Synchronized the bundled Hexa WordPress Plugin Core to 3.0.3.
- Every CPT now renders as a collapsed parent card, with its CPT configuration followed by a separate collapsed child section for each attached ACF field group.
- The hierarchy is supplied by the generic Core renderer so every host plugin using `ContentTypeRenderer` inherits it without plugin-specific UI code.

## 3.0.2

- Synchronized the bundled Hexa WordPress Plugin Core to 3.0.2 with the redesigned single-column content-type settings UI.
- Keeps the SFPF plugin and Core minimum version metadata aligned at 3.0.2.

## 3.0.1

- Synchronized the bundled Hexa WordPress Plugin Core to 3.0.1, including the standalone schema relationship fix.

## 3.0.0

- Coordinated major release for the consolidated person-profile, schema, query, lifecycle, and admin infrastructure, synchronized with Hexa WordPress Plugin Core 3.0.0.

## 2.0.10

- Gives Press Release fields their own SFPF group and field keys so SMP Article Fields remain independently registered.

## 2.0.9

- Adds Core-managed Press Release, Interview, and Contributing Profile CPT controls.
- Adds an independent ACF structure toggle to each new CPT while preserving established field keys.
- Keeps all new structures disabled by default so sites opt in explicitly.

## 2.0.8

- Bounds `[sfpf_loop]` defaults and explicit row grids with filterable positive limits and a hard maximum.
- Resolves founder Organization output and Person `worksFor` schema from canonical `founder_users` or legacy `founder` relationships first.
- Pages Organization IDs in counted, capped batches and preserves pre-relationship sites through a signaled, filterable inventory fallback.

## 2.0.7

- Resolves organization shortcodes from the current Organization CPT before using the configured primary organization.
- Adds a reusable, responsive `[organization action="display_profile"]` detail renderer.
- Rejects stale primary IDs from other post types and keeps featured media out of the Organization `logo` schema property.

## 2.0.6

- Keeps Wikidata URLs in Person schema `sameAs` while excluding them from public Additional URLs and author-profile output.
- Adds a plain dynamic Google Knowledge Panel URL shortcode for native Elementor link controls.

## 2.0.5

- Stacks every Education History input on its own row in the user-profile editor.
- Adds shared Wikimedia Commons photo URLs for Person and Organization entities, with shortcode and Person-schema image support.

## 2.0.4

- Adds a Person-level Additional URLs ACF repeater matching Recent Articles with title, source, and URL columns.
- Adds titled, cards, sources, and compact display formats plus raw, JSON, count, author-archive, managed-page, and Elementor availability support.
- Maps valid Additional URLs into the Person schema `sameAs` collection with deduplication.

## 2.0.3

- Keeps SFPF authoritative for the <code>[founder]</code> shortcode when HWS Base Tools also supplies its generic fallback.

## 2.0.2

- Adds native Elementor content-availability conditions for founder articles and person-scoped FAQs.
- Prevents blank Education repeater rows from emitting empty frontend markup.

## 2.0.1

- Defers author archive rendering to Elementor Pro when Theme Builder has a matching archive template.
- Preserves the built-in SFPF author profile as a fallback when Elementor has no matching archive.

## 2.0.0

- Established the stable major baseline for Person fields, schema, FAQs, Book structures, shortcodes, and presentation helpers.
- Updated shared entity, CPT, ACF, FAQ, schema, updater, rendering, and admin UI infrastructure to Hexa WP Core 1.0.0.
- Preserved existing user fields, schema identities, Book records, and optional HWS canonical-person fallback behavior.

## 1.8.3

- Consumed HWS canonical Person entities and post-bound WordPress authors while preserving legacy fallback behavior.
- Registered Book CPT/ACF structures through Hexa WP Core.
- Routed FAQ rendering and schema document output through the shared Core implementation.
- Corrected Organization and Testimonial ownership documentation.
- Updated the bundled Hexa WordPress Plugin Core to 0.19.78.

## Support

Report issues at <https://github.com/mikeyperes/sfpf-person-profile-integration/issues>.

## License

GPL-2.0-or-later.
