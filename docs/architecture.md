# Architecture

`initialization.php` (the main file, named so because sites and Code's audit tool activate the plugin by that path) registers the autoloader for `src/`, registers the bundled Hexa WP Core and boots `PluginRuntime\Bootstrap` on `plugins_loaded`. The bootstrap builds one `PluginContext`, one `CoreBootstrap` and adds every feature as a module whose `register()` only adds hooks.

## Namespaces (`SFPF\PersonProfile\…`)

| Namespace | Owns |
| --- | --- |
| `PluginRuntime` | Identity (`PluginMetadata`), autoloader, bootstrap, activation, updater configs |
| `Settings` | Every option name, default and reader |
| `Person` | Who the person is (`PersonResolver`), their normalized profile (`PersonProfile`), field names, typed values (`FieldValue`, `PersonFieldValues`), links and Knowledge Graph IDs, organizations founded, query limits |
| `Fields` | The person's field group, profile-screen repeater protection, duplicate field-group guard, Knowledge Graph ID clean-up, education hints |
| `ContentTypes\<Type>` | One folder per content type: `<Type>ContentType` (registration), `<Type>Fields` (fields), and a `<Type>Record` reader where shortcodes need one. `ContentTypeCatalog` lists them |
| `Schema` | The Person node, page nodes, shortcode detection, the structure catalog and `Structures\*` (one class per structure) |
| `Shortcodes` | Every shortcode, shared attributes and output, `Sections\*` (one class per section) and the older tags |
| `Pages` | The page catalog, starting content, Core's page manager and profession pages |
| `Faq` | FAQ sets |
| `Elementor` | Dynamic tags, display condition, social icon cleanup |
| `Frontend` | Author profile pages, breadcrumb visibility |
| `Admin` | Settings page, `Tabs\*`, `Actions\*` (AJAX), `Checks\*`, `Profile\*` (profile screen) |
| `Migrations` | One class per one-time upgrade and their runner |
| `Diagnostics` | Profile report and its debug page |
| `Sitemaps`, `Support` | Sitemap declarations; activity log and plugin requirements |

## Shared Core

Generic behavior lives in Hexa WP Core (`lib/hexa-wordpress-plugin-core`, never edited here): schema builders and the `SchemaStructures` registry, content types and field groups, the page manager, admin tabs and UI, AJAX guards, shortcode catalogs, FAQ sets, schema scanning, sitemaps, activity logs and updaters. The plugin adds only person-website definitions.

## Stored data

Field keys and names (`tests/fixtures/stored-field-keys.json`) and option names never change; upgrades in `Migrations` move older data without deleting it.

## Query bounds

Public queries are bounded: organizations founded load in batches of 20 up to 100 (`sfpf_founder_organizations_default_limit`, `sfpf_founder_organizations_hard_max`), lists up to 12 by default and 48 at most (`sfpf_loop_default_limit`, `sfpf_loop_hard_max`). `sfpf_founder_organizations_allow_inventory_fallback` turns off the fallback to every published organization when no founder relation exists.
