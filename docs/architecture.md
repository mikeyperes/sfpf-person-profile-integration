# SFPF Plugin Architecture

## Ownership Boundaries

- `initialization.php` owns only plugin metadata, constants, Core package registration, PSR-4 registration, and composition-root startup.
- `src/Plugin.php` is the namespaced composition root. `src` also contains Core adapters, registries, and deterministic module loaders.
- includes/runtime contains lifecycle, ACF-profile, diagnostics, SEO, and profile-admin compatibility callbacks.
- `includes/shortcodes` contains legacy procedural callbacks for Person, Book, loop, and FAQ behavior. Registration is centralized in `Shortcodes/ShortcodeRegistrar`.
- includes/frontend contains frontend route renderers that are not shortcode handlers.
- admin/ajax-handlers.php is a compatibility entry point that loads only the module mapped to the current AJAX action.
- admin/ajax separates settings, schema, site structure, templates, Elementor, professions, diagnostics, and article actions; ordinary admin, dashboard-tab, and unrelated AJAX requests do not parse these modules.
- `lib/hexa-wordpress-plugin-core` is a byte-for-byte vendored copy of canonical Core and is never the source-of-truth editing location.

## Domain Ownership

SFPF owns Person profile behavior, the Book content type, Person/Book field structures, FAQ sets, bounded Person-to-Organization relationship views, and its page/template orchestration.

SFPF is the canonical owner of Organization CPT registration, the `group_sfpf_organization` ACF structure, primary Organization resolution, and the `[organization]` shortcode. Existing HWS content-type settings and legacy SFPF field toggles are migrated into SFPF's Core-backed content-type store without deleting their source options. Organization schema may still delegate to a dedicated integration when one is installed.

## Compatibility Policy

Public Person/Organization/Book/FAQ shortcode tags, action names, filters, and procedural callback names remain in `sfpf_person_website` until a versioned deprecation path exists. New orchestration code uses the `SFPF\PersonProfile` namespace.

## Shared Core Policy

Generic grouped admin tabs, tab/shortcode catalogs, updater controls, AJAX registration and request guards, activity-log storage, plugin checks, site-structure services, value/row/URL/media normalization, and reusable UI primitives belong in Hexa WordPress Plugin Core. SFPF owns the Person/Book/FAQ definitions, tab labels and panels, dependency policy, domain-specific Wikidata filtering, schema construction, and host-specific callback mapping.

The procedural AJAX, logging, ACF getter, URL collection, and gallery functions are compatibility seams, not alternate implementations. AJAX modules are selected by the current action and registered through Core with one capability/nonce policy. Existing log rows are migrated in place to Core's permanent activity-log format while legacy callers continue receiving their historical array shape. Normalization callbacks delegate to Core while retaining their historical empty-value, ordering, deduplication, and fixed gallery-record contracts.

## Regression Limits

The standalone suite enforces thin bootstrap files, bounded ownership modules, synchronized Core vendors, no arbitrary PHP evaluation, no unauthenticated SFPF AJAX actions, centralized Core-backed registries and logging, Organization ownership, gallery schema mapping, and server-side social-icon filtering.
