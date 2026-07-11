# SFPF Plugin Architecture

## Ownership Boundaries

- initialization.php owns only plugin metadata, constants, Core registration, shared helper loading, and runtime module startup.
- src contains namespaced host adapters and deterministic module loaders.
- includes/runtime contains lifecycle, ACF-profile, diagnostics, SEO, and profile-admin compatibility callbacks.
- includes/shortcodes contains shortcode domains and their rendering helpers.
- includes/frontend contains frontend route renderers that are not shortcode handlers.
- admin/ajax-handlers.php is a compatibility entry point only.
- admin/ajax separates settings, schema, site structure, templates, Elementor, professions, diagnostics, and article actions.
- lib/hexa-wordpress-plugin-core is a vendored copy of canonical Core and is never the source-of-truth editing location.

## Compatibility Policy

Public shortcodes, action names, filters, and procedural callback names remain in sfpf_person_website until a versioned deprecation path exists. New orchestration code uses the SFPF\PersonProfile namespace.

## Shared Core Policy

Generic admin tabs, updater controls, AJAX request guards, site-structure services, and reusable UI primitives belong in Hexa WordPress Plugin Core. SFPF modules own only person-profile behavior and host-specific callback mapping.

## Regression Limits

The standalone suite enforces thin bootstrap files, bounded ownership modules, synchronized versions, no arbitrary PHP evaluation, no unauthenticated SFPF AJAX actions, gallery schema mapping, and server-side social-icon filtering.
