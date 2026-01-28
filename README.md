# SFPF Person Profile Integration

A WordPress plugin for managing structured data (Schema.org), profile pages, FAQ structures, and Elementor integrations for public figure websites.

## Requirements

- WordPress 5.8+
- PHP 7.4+
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
- Direct update from GitHub
- Version history with rollback support
- Download specific commits as ZIP
- No folder name suffix issues

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
