# Asset loading

| Asset | Where it loads |
| --- | --- |
| `assets/frontend/person-sections.css` | Once, with the first styled section, content loop, FAQ set or organization output. `style="none"` leaves it out. |
| `assets/frontend/author-profile.css` | Only when the author archive uses the plugin's fallback layout. |
| Legacy Elementor FAQ script | With a rendered `[sfpf_elementor_faq]` that has FAQ items. |
| `assets/admin/dashboard.css` | The SFPF settings screen. |
| `assets/admin/dashboard.js` | A tab with SFPF settings controls or a copyable shortcode, or a tab that needs a feature script. |
| `assets/admin/press-releases.js` | The Press Releases tab when its connection controls are present. |
| `assets/admin/schema.js` | The Schema tab when the schema scan control is present. |
| `assets/admin/profession-pages.js` | The Pages & Menus tab when a profession page action is available. |
| `assets/admin/faq.js` and `assets/admin/faq.css` | The FAQ Sets tab. |
| `assets/admin/profile-screen.css` and `assets/admin/profile-screen.js` | WordPress user profile screens with the SFPF field group enabled. |

Core loads each dashboard tab on demand. The Update Center uses Core's updater scripts and the shared SFPF dashboard stylesheet.

For a release, compare the plugin's style and script list before and after on the home page, an article, a page that renders a styled SFPF section, and the settings tabs. The home page and article should only contain SFPF assets when they render a feature that uses them.
