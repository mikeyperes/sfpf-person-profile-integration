# SFPF Person Profile Integration

Person-profile fields, schema, FAQs, page structures, and presentation helpers for WordPress sites managed by SEO for Public Figures.

## Ownership

SFPF owns:

- Person fields stored on WordPress users.
- Person, ProfilePage, and person-related FAQ schema construction.
- The optional `book`, `press-release`, `interview`, and `contributing-profile` custom post types and their ACF structures.
- Person/profile shortcodes, page structures, and presentation templates.

SFPF does not own Organization or Testimonial registration:

- HWS Base Tools owns the `organization` and `testimonial` post types.
- SMC Organization Profile Integration owns canonical Organization fields and Organization schema.
- SFPF consumes those external records only where person-profile output needs them.

## Canonical Entity

HWS Base Tools is the source of truth for website type and the optional primary entity. SFPF consumes a canonical Person entity from HWS when configured and falls back to its legacy founder/user settings only for migration compatibility.

A canonical source may be a WordPress user or a post with a bound WordPress author. SFPF reads the attached user through Hexa WP Core and keeps the existing user-field contract intact. SFPF does not require HWS to have a primary entity configured.

## Architecture

`initialization.php` is the WordPress plugin entry. Domain code is isolated under `src/`, `schema/`, `includes/`, `admin/`, and focused `snippets/` adapters.

Reusable behavior is delegated to Hexa WordPress Plugin Core:

- Dashboard tabs and UI components.
- Custom post type and ACF structure registration/settings.
- Canonical entity and attached-user resolution.
- FAQ normalization/rendering.
- Schema document encoding and injection.
- Plugin and Core update panels.

The plugin bundles Core 1.0.0 and keeps [HEXA_PLUGIN_CORE_LIBRARY.md](HEXA_PLUGIN_CORE_LIBRARY.md) synchronized with the canonical package.

## Features

- Person and ProfilePage schema graphs with stable IDs and Rank Math coexistence.
- Person FAQ sets and reusable FAQ output.
- Book CPT, fields, schema, and shortcodes.
- Optional Press Release, Interview, and Contributing Profile CPTs with independently controlled ACF groups.
- Biography, education, profession, organization-founded, and related page structures.
- Person/profile galleries, social identities, additional URLs, awards, credentials, press, and Knowledge Graph identifiers.
- Shortcode catalog and Elementor-compatible output helpers.
- Admin previews, diagnostics, and GitHub/Core update reporting.

## Common Shortcodes

| Shortcode | Purpose |
| --- | --- |
| `[founder id="..."]` | Render a canonical person field. |
| `[website_content id="..."]` | Render an assigned website/profile value. |
| `[faq slug="primary"]` | Render a configured FAQ set. |
| `[book id="..."]` | Render a Book field or display value. |

The dashboard Shortcodes tab is the canonical source for the complete current list and parameter documentation.

## Requirements

| Requirement | Minimum |
| --- | --- |
| WordPress | 5.8 |
| PHP | 8.0 |
| Hexa WP Core bundle | 1.0.0 |

HWS Base Tools is recommended for canonical website/entity settings. ACF Pro is required for ACF-backed profile and Book field structures.

## Installation

Install the repository as `wp-content/plugins/sfpf-person-profile-integration`, activate it, and open its settings page. Existing legacy person settings are read as migration fallbacks and are not destructively rewritten.

## Development

Run the standalone architecture and regression suite with:

```bash
php tests/run.php
```

Run `tests/wordpress-integration.php` inside a bootstrapped WordPress environment to verify the selected Core package and live hooks.

Public loop and founder-Organization query limits, filters, relationship
semantics, and truncation signaling are documented in
[Frontend Query Bounds](docs/frontend-query-bounds.md).

## Changelog

### 2.0.9

- Adds Core-managed Press Release, Interview, and Contributing Profile CPT controls.
- Adds an independent ACF structure toggle to each new CPT while preserving established field keys.
- Keeps all new structures disabled by default so sites opt in explicitly.

### 2.0.8

- Bounds `[sfpf_loop]` defaults and explicit row grids with filterable positive limits and a hard maximum.
- Resolves founder Organization output and Person `worksFor` schema from canonical `founder_users` or legacy `founder` relationships first.
- Pages Organization IDs in counted, capped batches and preserves pre-relationship sites through a signaled, filterable inventory fallback.

### 2.0.7

- Resolves organization shortcodes from the current Organization CPT before using the configured primary organization.
- Adds a reusable, responsive `[organization action="display_profile"]` detail renderer.
- Rejects stale primary IDs from other post types and keeps featured media out of the Organization `logo` schema property.

### 2.0.6

- Keeps Wikidata URLs in Person schema `sameAs` while excluding them from public Additional URLs and author-profile output.
- Adds a plain dynamic Google Knowledge Panel URL shortcode for native Elementor link controls.

### 2.0.5

- Stacks every Education History input on its own row in the user-profile editor.
- Adds shared Wikimedia Commons photo URLs for Person and Organization entities, with shortcode and Person-schema image support.

### 2.0.4

- Adds a Person-level Additional URLs ACF repeater matching Recent Articles with title, source, and URL columns.
- Adds titled, cards, sources, and compact display formats plus raw, JSON, count, author-archive, managed-page, and Elementor availability support.
- Maps valid Additional URLs into the Person schema `sameAs` collection with deduplication.

### 2.0.3

- Keeps SFPF authoritative for the <code>[founder]</code> shortcode when HWS Base Tools also supplies its generic fallback.

### 2.0.2

- Adds native Elementor content-availability conditions for founder articles and person-scoped FAQs.
- Prevents blank Education repeater rows from emitting empty frontend markup.

### 2.0.1

- Defers author archive rendering to Elementor Pro when Theme Builder has a matching archive template.
- Preserves the built-in SFPF author profile as a fallback when Elementor has no matching archive.

### 2.0.0

- Established the stable major baseline for Person fields, schema, FAQs, Book structures, shortcodes, and presentation helpers.
- Updated shared entity, CPT, ACF, FAQ, schema, updater, rendering, and admin UI infrastructure to Hexa WP Core 1.0.0.
- Preserved existing user fields, schema identities, Book records, and optional HWS canonical-person fallback behavior.

### 1.8.3

- Consumed HWS canonical Person entities and post-bound WordPress authors while preserving legacy fallback behavior.
- Registered Book CPT/ACF structures through Hexa WP Core.
- Routed FAQ rendering and schema document output through the shared Core implementation.
- Corrected Organization and Testimonial ownership documentation.
- Updated the bundled Hexa WordPress Plugin Core to 0.19.78.

## Support

Report issues at <https://github.com/mikeyperes/sfpf-person-profile-integration/issues>.

## License

GPL-2.0-or-later.
