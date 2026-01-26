# SFPF Person Profile Integration

A WordPress plugin for personal website schema management, page structures, and content templates. Integrates with HWS Base Tools for website settings.

## Features

- **Schema Management**: Generate and manage JSON-LD schema for Person, ProfilePage, Book, and Organization types
- **Page Hierarchy**: Create and manage critical pages (Biography, Education, Professions, etc.)
- **Content Templates**: WYSIWYG editors for page templates with shortcode support
- **Snippet System**: Enable/disable CPT and ACF field groups as needed
- **Dark Theme Schema Viewer**: Beautiful syntax-highlighted JSON display
- **JavaScript Tab Navigation**: Smooth tab switching without page reloads

## Requirements

- WordPress 5.8+
- PHP 7.4+
- Advanced Custom Fields (ACF) Pro (recommended)
- HWS Base Tools plugin (for website settings)

## Installation

1. Download the plugin zip file
2. Go to Plugins → Add New → Upload Plugin
3. Upload the zip file and click Install Now
4. Activate the plugin
5. Go to Settings → HWS Person Profile to configure

## Configuration

### 1. Enable Required Snippets

Navigate to the **Snippets** tab and enable:
- Book Custom Post Type (if needed)
- Organization Custom Post Type (if needed)
- ACF field groups as needed

### 2. Configure Pages

Go to the **Pages** tab to:
- Assign existing pages to the personal website structure
- Create new pages with proper hierarchy

### 3. Set Up Templates

Use the **Templates** tab to:
- Edit default content templates
- Insert shortcodes for dynamic content
- Apply templates to assigned pages

### 4. Configure Schema

In the **Schema** tab:
- Choose homepage schema type (ProfilePage + Person or Person only)
- Preview generated schema
- Reprocess schema for all content types

## Shortcodes

### Website Content
- `[website_content field="biography"]` - Full biography
- `[website_content field="email"]` - Contact email
- `[website_content field="mission_statement"]` - Mission statement

### Founder
- `[founder id="name"]` - Founder name
- `[founder id="biography"]` - Founder biography
- `[founder id="avatar"]` - Avatar URL
- `[founder id="website"]` - Website URL
- `[founder id="url_facebook"]` - Facebook URL

### Company
- `[company id="name"]` - Company name
- `[company id="email"]` - Company email
- `[company id="website"]` - Company website

### Social URLs
- `[website_url social="facebook"]`
- `[website_url social="linkedin"]`
- `[website_url social="twitter"]`
- `[website_url social="instagram"]`

## Page Structure

```
Biography (biography)
├── Education (education)
├── Location Born (location-born)
├── Organizations Founded (organizations-founded)
├── Alternate Names (alternate-names)
└── Professions (professions)
```

## Schema Types

### Person Schema
Basic person information including name, job title, email, and social links.

### ProfilePage Schema
Wraps Person schema with a ProfilePage type for the homepage.

### Book Schema
Full book information with author reference, cover image, and marketplace links.

### Organization Schema
Company/organization information with founder reference and social links.

## Hooks & Filters

### Actions
- `sfpf_schema_generated` - Fired after schema is generated
- `sfpf_page_created` - Fired after a page is created

### Filters
- `sfpf_person_schema` - Modify Person schema before output
- `sfpf_book_schema` - Modify Book schema before output
- `sfpf_organization_schema` - Modify Organization schema before output

## File Structure

```
sfpf-person-profile-integration/
├── initialization.php           # Main plugin file
├── README.md                    # This file
├── includes/
│   ├── helper-functions.php     # Utility functions
│   ├── logging.php              # Activity logging
│   └── snippets-loader.php      # Snippet management
├── admin/
│   ├── settings-dashboard.php   # Main dashboard (JS tabs)
│   ├── dashboard-overview.php   # Overview with checks
│   ├── dashboard-schema.php     # Schema management
│   ├── dashboard-pages.php      # Page hierarchy
│   ├── dashboard-templates.php  # WYSIWYG templates
│   ├── dashboard-snippets.php   # Snippet toggles
│   └── ajax-handlers.php        # AJAX operations
├── schema/
│   ├── schema-templates.php     # Skeleton templates
│   ├── schema-builder.php       # Build with content
│   ├── schema-manager.php       # Schema operations
│   └── schema-injector.php      # JSON-LD injection
├── snippets/
│   ├── register-cpt-book.php
│   ├── register-cpt-organization.php
│   ├── register-acf-book.php
│   ├── register-acf-organization.php
│   └── register-acf-homepage.php
└── assets/
    ├── css/
    └── js/
```

## Changelog

### 1.0.0
- Initial release
- JavaScript tab navigation (no page reload)
- Proper founder/company user detection matching HWS Base Tools
- Dark theme schema viewer
- WYSIWYG template editors
- Merged checks into Overview tab
- Added Professions page to biography structure
- Profile cards with photos in Overview
- Clickable schema validator links

## Support

For support, please contact [SEO For Public Figures](https://seoforpublicfigures.com).

## License

GPL v2 or later
