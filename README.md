# SFPF Person Profile Integration

A WordPress plugin for managing structured data (Schema.org), profile pages, FAQ structures, and Elementor integrations for public figure websites.

## Requirements

- WordPress 5.8+
- PHP 8.0+
- Advanced Custom Fields (ACF) Pro
- HWS Base Tools Plugin (for website settings)
- Optional: Elementor (for loop templates and FAQ integration)

## Features

### 📊 Schema.org Structured Data
- **Homepage Schema**: ProfilePage, Person, or combined schema injection
- **User Schema Fields**: Entity type, education history, additional names, alternate names, sameAs URLs
- **CPT Schema**: Book, Organization, Testimonial structured data
- **RankMath Control**: Disable RankMath schema on specific post types
- **Schema Detection Tool**: Analyze existing schema on your pages

### 📄 Page Management
- Pre-configured pages: Biography, Education, Organizations Founded, Professions
- Page templates with WYSIWYG editors
- One-click template application
- Automatic page creation with proper hierarchy

### ❓ FAQ System
- **FAQ Sets**: Create multiple named FAQ collections
- **Rich Text Answers**: Full WYSIWYG editor for FAQ answers
- **Auto Schema**: Automatic FAQPage schema injection
- **Accordion Display**: `[sfpf_faq set="slug" style="accordion"]`
- **Elementor Integration**: `[sfpf_elementor_faq set="slug" target=".selector"]`

### 🔧 Snippets System
- Toggle-based feature activation
- Book CPT & ACF fields
- Organization CPT & ACF fields
- Testimonial CPT & ACF fields
- User Schema fields

### 🎨 Elementor Loop Templates
- Assign Elementor Loop Items to custom post types
- Responsive grid shortcode: `[sfpf_loop cpt="book" columns="3"]`
- Support for Books, Organizations, Testimonials

### 🔄 GitHub Integration
- Core-backed update checks with capability and nonce enforcement
- Normalized plugin updates and version ZIP downloads
- Update progress and rollback-oriented version history

## Installation

1. Download the latest release ZIP
2. Upload via WordPress Admin → Plugins → Add New → Upload
3. Activate the plugin
4. Enable required snippets in SFPF → Snippets tab
5. Configure schema settings in SFPF → Schema tab

## Shortcodes

### Founder/Person Shortcodes
```
[founder id="name"]              - Display founder name
[founder id="biography"]         - Display biography
[founder id="entity_type"]       - Entity type (person/organization)
[founder id="additional_name"]   - Middle name/nickname
[founder id="alternate_names"]   - List of alternate names
[founder id="education"]         - Education history (HTML list)
[founder id="education" format="json"]  - Education as JSON
[founder id="sameas"]            - Social URLs
```

### Company/Organization Shortcodes
```
[company id="name"]              - Organization name
[company id="entity_type"]       - Entity type
[company id="alternate_names"]   - Alternate names
[company id="sameas"]            - Social URLs
```

### FAQ Shortcodes
```
[founder action="display_faq" style="accordion"] - Recommended person-profile FAQ accordion from ACF user FAQ rows
[sfpf_person_faq]                              - Alias for the person-profile FAQ accordion
[sfpf_faq set="slug"]                          - All FAQs from a set
[sfpf_faq set="slug" index="0"]                - Single FAQ item
[sfpf_faq set="slug" style="accordion"]        - Collapsible accordion
[sfpf_faq_schema set="slug"]                   - Schema only (hidden)
[sfpf_elementor_faq set="slug" target=".sel"]  - Inject into Elementor
```

### Loop Shortcodes
```
[sfpf_loop cpt="book"]                              - Book grid
[sfpf_loop cpt="organization" columns="2"]          - 2-column grid
[sfpf_loop cpt="testimonial" columns="3" rows="2"]  - 3x2 grid (6 items)
[sfpf_loop cpt="book" responsive="true"]            - Mobile-responsive
```

### Website Content Shortcodes
```
[website_content field="biography_short"]
[website_content field="email"]
[website_url social="linkedin"]
[website_url social="twitter"]
```

## Schema Types Supported

- **Person**: Full Schema.org Person markup
- **ProfilePage**: Schema.org ProfilePage wrapper
- **FAQPage**: Automatic FAQ structured data
- **Book**: Book schema for publications
- **Organization**: Organization schema for businesses
- **LocalBusiness**: For testimonials with business context

## Configuration

### Homepage Schema
1. Go to SFPF → Schema
2. Choose schema type: None, ProfilePage Only, Person Only, or ProfilePage + Person
3. Save settings

### RankMath Integration
- Disable RankMath schema on specific post types
- Prevents duplicate schema conflicts

### User Schema Fields
Edit any user profile to add:
- Entity Type (Person/Organization/None)
- Additional Name (for Person)
- Alternate Names (for both)
- Education History (for Person)
- SameAs URLs (social profiles)

## Hooks & Filters

### Filter: Modify schema output
```php
add_filter('sfpf_person_schema', function($schema, $user_id) {
    $schema['additionalProperty'] = ['custom' => 'value'];
    return $schema;
}, 10, 2);
```

### Action: After page creation
```php
add_action('sfpf_page_created', function($page_id, $page_key) {
    // Custom logic after page creation
}, 10, 2);
```

## Changelog

### v1.7.1
- Reduced the plugin bootstrap from 2,554 lines to a versioned loader under 100 lines.
- Split frontend, shortcode, ACF, lifecycle, and admin-profile callbacks into focused compatibility modules.
- Reduced the legacy AJAX entry point from 2,523 lines to a thin loader and separated thirteen action domains.
- Added namespaced runtime and AJAX module loaders while preserving existing WordPress callback names.
- Added architecture regression limits so bootstrap files and ownership modules cannot silently flatten again.

### v1.7.0
- Updated the vendored Hexa WordPress Plugin Core to 0.19.40 and passed its complete test suite.
- Added an isolated host adapter that creates PluginContext and boots updater and Core tab modules through CoreBootstrap.
- Replaced the legacy updater implementation with the shared Core updater renderer and guarded controllers.
- Reorganized the dashboard into Overview, Profile, Site, Integrations, and System areas.
- Added server-routed primary areas and nonce-protected lazy secondary panels instead of eagerly rendering every tab.
- Added a responsive dashboard stylesheet that removes the mobile overflow found during the audit.

### v1.6.27
- Removed the arbitrary PHP debug runner and its AJAX endpoint.
- Stopped automatic deletion and global hiding of cross-plugin ACF fields.
- Restricted profile diagnostics to authenticated administrators and fixed debug URL warnings.
- Added CSRF protection to plugin-info AJAX actions and disabled unsafe direct filesystem replacement.
- Activated schema regeneration on managed content saves and fixed the SameAs ACF wrapper definition.

### v1.6.18
- Integrated Hexa WordPress Plugin Core 0.12.0.
- Rebuilt the Pages & Menus tab with core SiteStructure tools for critical pages, navigation menus, and page-to-menu-item attachment.
- Moved SFPF page/menu AJAX actions to the core AJAX controller while keeping the existing action names.

### v1.6.12
- Added a default-enabled setting that removes empty Elementor Social Icons widget links server-side before the page renders.

### v1.6.11
- Verified public Gallery ACF images are included in Person schema `image` output ahead of avatar fallback.

### v1.6.10
- Gallery Notion mapping label corrected to capital Gallery for Person and Company debug output.
- Profile debug output includes dynamic URLs, gallery shortcode output, and schema snapshots for authenticated administrators only.

### v1.6.3
- Dashboard: Reordered schema previews — Book and Organization now appear right after Biography (before RankMath control)
- Dashboard: All 4 schema preview cards now have Schema.org Validator + Google Rich Results Test buttons with dynamic URLs
- Dashboard: Progress bar now green at 100% complete, yellow at 70%+, red below 70% (was always red)
- Schema Detection: Shows expectations section based on configured schema types and RankMath status
- Schema Detection: Properly identifies SFPF Plugin blocks (via HTML comment markers) vs RankMath blocks (via content heuristics)
- Schema Detection: Each RankMath block now has direct "Edit in RankMath →" link
- ACF: Added `knowledge_graph_id` field to Book and Organization CPTs (with auto-extract from URL, shortcode support)
- Schema: Book and Organization schemas now include `identifier` with `googleKgMID` when Knowledge Graph ID is set

### v1.6.2
- Unified schema builder: all 4 schema types (Person, ProfilePage, Book, Organization) now share a single codebase in `schema-builder.php`
- Person schema: KG gallery images now included with avatar dedup, education adds `hasCredential` with degree/major, professions add `hasOccupation`
- ProfilePage schema: now always includes full Person data in `@graph` (not just `@id` reference)
- Book schema: added ISBN, numberOfPages, datePublished, bookEdition, bookFormat, inLanguage, genre fields
- Organization schema: added legal_name, email, telephone, naics_code, number_of_employees, structured address group, contact_point group, awards, brands, seeks fields
- Dashboard: Book and Organization now have live schema previews with Schema.org Validator and Google Rich Results Test buttons
- Book/Org injection now generates live from unified builder instead of reading stale stored meta
- Removed duplicate inline schema builders from register-acf-book.php and register-acf-organization.php

### v1.6.1
- Fixed critical bug: ACF `conditional_logic` on repeater fields caused data to be wiped when entity type was switched or saved. Replaced all `conditional_logic` with CSS/JS visibility system — fields are always in the DOM, never removed
- Fixed: ACF repeater hydration on user profile pages — ACF returns "shell rows" (correct count but empty subfields) which caused blank renders and data loss on save. Added three-layer fix: load_value rebuild, prepare_field injection, and save guard
- Fixed: ACF mutates `$field['name']` during prepare_field rendering, breaking meta key lookups. All meta access now uses hardcoded key→name mapping
- Fixed: Bulk Import Articles and Articles repeater now hidden when entity type is "none"
- Fixed: DB-stored ACF field groups with `group_sfpf_*` prefix now blocked from overriding code-registered groups

### v1.6.0
- Added nationality field as repeater (supports multiple nationalities)
- Added gender, telephone, honorific prefix/suffix fields
- Added languages (knows_language) and awards repeaters

### 1.3.3
- Fixed: Plugin Dependencies section simplified (removed duplicate HWS Snippets checks)
- Fixed: Version and Author now display one per row in dependencies
- Fixed: Edit Profile and View Profile buttons open in new tab
- Fixed: All Edit/View page buttons now open in new tab
- Fixed: Removed "(recommended)" text from ProfilePage + Person option
- Fixed: Critical Pages and Pages Overview now use same codebase (render_page_actions)
- Fixed: Git version dropdown now fetches actual plugin version from each commit
- Added: Knowledge Graph Images gallery field for Person/Organization schema
- Added: Shortcode examples for organization and testimonial in Loop Templates

### 1.3.2
- Fixed: Save buttons now use consistent toast notifications (no page reloads)
- Fixed: FAQ WYSIWYG editor initialization for dynamically added items
- Fixed: Apply Template button duplicate popup and cancel behavior
- Fixed: HWS snippet status detection
- Added: additionalName field for Person schema
- Added: alternateNames repeater for Person/Organization
- Added: Elementor Loop Templates section with shortcode
- Added: Loop shortcode with columns, rows, responsive parameters
- Improved: Plugin dependencies display (one per row)

### 1.3.1
- Fixed: User schema admin styling (white backgrounds)
- Fixed: Git version history loading from GitHub commits
- Added: FAQ Sets structure with named collections
- Added: WYSIWYG editor for FAQ answers
- Added: Elementor FAQ integration shortcode
- Changed: Default schema type to "None"

### 1.3.0
- Added: RankMath schema control
- Added: Schema detection tool
- Added: FAQ structures with auto-schema
- Added: Schema templates 2-per-row layout
- Fixed: Various UI/UX improvements

### 1.2.0
- Fixed: Fatal error with ACF timing
- Added: User Schema ACF snippet

### 1.1.0
- Added: Testimonial CPT
- Fixed: Social URLs display
- Fixed: ACF registration timing

### 1.0.0
- Initial release

## Support

For issues and feature requests, please use the [GitHub Issues](https://github.com/mikeyperes/sfpf-person-profile-integration/issues) page.

## License

GPL v2 or later

## Author

[SEO For Public Figures](https://seoforpublicfigures.com)
