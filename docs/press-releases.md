# Press Releases

Settings → SFPF Person Profile → Press Releases connects the website's selected
person to a publication. The publication origin defaults to
`https://hexaprwire.com` and is editable.

Both **By you** and **About you** ship off. Updating the plugin creates no
page, enables no content type and changes no visitor output. A connection
check never saves settings or enables output. Settings belong to the selected
person's user ID; choosing another website person makes both sections off
until that person's connections are saved.

## By you

Paste the person's canonical author profile URL, then choose **Check author
connection**. Hexa WP Core resolves the exact author and reads their published
release list. The result identifies the author, their profile URL and the
number of published releases. Saving this switch on repeats those checks and
stores the confirmed author ID and canonical profile link.

## About you

Search the person's name with **Find Verified Profiles**. The publication
returns published person profiles only, with previous/next controls when
there are multiple pages. Select the correct person, then choose **Check
profile connection**. Releases come from that verified profile's explicit
release relationships, rather than a text search of release titles.
An empty search is reported as no matching published profiles. It does not
create a profile or choose a different person automatically.

## Visitor output

Place either shortcode explicitly on the page where the list belongs:

- `[person_section id="press_releases_by" heading="Press releases by me"]`
- `[person_section id="press_releases_about" heading="Press releases about me"]`

Each shortcode prints nothing when its switch is off, the person is different,
the connection fails or there are no releases. The canonical connected profile
is linked above the release list. These lists show the first remote result
page; `limit` can shorten that list. Layouts are `titled`, `cards`, `sources`
and `compact`, with the existing section attributes, including `style="none"`.
No remote request runs for a disabled section. Successful release lists are
cached for five minutes. Failed calls cache an empty list for one minute,
so an unavailable publication is not contacted on every page view.

The wrapper classes are `.sfpf-press-releases-by` and
`.sfpf-press-releases-about`. The profile link uses
`.sfpf-press-releases__profile`. List HTML, CSS variables and selector examples
are the existing [link-list structures](sections.md). No new visitor asset is
loaded.

## Ownership

Hexa WP Core's `PressReleaseProfilesClient` owns remote requests, response
validation and errors. SFPF owns its settings, current-person binding, protected
AJAX actions and section composition. AJAX uses Core's administrator capability
and nonce checks. The existing dashboard script handles checks only on the
SFPF settings page. The serving publication owns author and Verified Profile
data and the published release queries.
