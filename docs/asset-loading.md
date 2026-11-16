# Asset loading

| Asset | Loads on | Check |
| --- | --- | --- |
| `assets/frontend/person-sections.css` | Front-end pages that show a styled section, `[person_loop]`, `[faq_set]` or an organization block; printed once through Core's `InlineStyles` | The page's shortcodes render; nothing on pages without them |
| `assets/frontend/author-profile.css` | Author pages drawn by the plugin (no Elementor archive template) | `is_author()` and the fallback renders |
| `assets/admin/dashboard.css`, `assets/admin/dashboard.js` | Settings → SFPF Person Profile only | Hook suffix `settings_page_sfpf-person-profile` |
| `assets/admin/profile-screen.css`, `assets/admin/profile-screen.js` | The user profile screens only | Hook suffix `profile.php` or `user-edit.php` |
| `[sfpf_elementor_faq]` inline script | Pages using that older shortcode | Printed with the shortcode |

Nothing loads in `wp_head` on every page; the schema block is data, printed by Core.

To check: compare the home page, a person page with sections, a book page and the settings page with the browser's network panel; the sections stylesheet appears only as the inline style `sfpf-person-sections-inline-css` on pages with sections.

## Press Releases

The Press Releases tab reuses `assets/admin/dashboard.js`, already limited to
the SFPF settings page. Its delegated handlers support tabs loaded by AJAX.
There is no new admin asset. The home page and an article load no additional
assets. A page explicitly using an enabled release section uses the existing
section stylesheet on first nonempty output, or no stylesheet with
`style="none"`. Disabled and empty release sections load no assets.
