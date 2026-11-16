# Schema

Each page carries one JSON-LD block (`<script id="hexa-schema">`) that every Hexa plugin adds to; this plugin's source is `sfpf-person`. The person is one node, `https://site/#person`, referenced from every page.

| Structure (Schema tab) | Pages | Types | Modes |
| --- | --- | --- | --- |
| Homepage | Front page | WebSite, ProfilePage (mainEntity: the person), Person | profile_page (default), person, off |
| Biography page | The Biography page | ProfilePage or AboutPage, Person, BreadcrumbList | profile_page (default), about_page, person, off |
| Other pages | Every other page (or only the person pages) | WebPage / ContactPage / ImageGallery / CollectionPage (about: the person), Person, BreadcrumbList | all (default), assigned, off |
| FAQs | FAQs page and pages showing an FAQ | FAQPage | on, off |
| Books | Each book, the books list | ItemPage + Book (author: the person); CollectionPage + ItemList | on, off |
| Organizations | Each organization, the organizations list | ItemPage + Organization (founder: the person when they founded it; the @id the person's worksFor points to); CollectionPage + ItemList | on, off |
| Awards | Each award, the list | ItemPage; CollectionPage + ItemList; each award also on the Person | on, off |
| Exhibitions | Each exhibition, the list | ItemPage + ExhibitionEvent (contributor: the person) | on, off |
| Quotes | Each quote | ItemPage + Quotation | on, off |
| Press releases | Each press release | WebPage (mainEntity: NewsArticle) + NewsArticle (about and published by the person); original source credited as citation, without inferring authorship | on, off |
| Interviews | Each interview | ItemPage + CreativeWork (about the person) | on, off |
| Contributing profiles | Each profile | WebPage; the profile links join the Person's sameAs | on, off |
| Author pages | /author/name/ | ProfilePage, Person, BreadcrumbList | on, off |

## The Person

| Property | From |
| --- | --- |
| name, givenName, familyName, additionalName, alternateName, honorificPrefix/Suffix | The user's names and identity fields |
| jobTitle, description | Title; short biography (else the start of the biography) |
| email, telephone, gender, birthDate, birthPlace | Public email and phone; birth fields (birthplace as a Place linked to Wikipedia) |
| nationality, knowsLanguage | Nationality as Country nodes; languages |
| alumniOf, hasCredential | Education: schools and degrees, with EducationalOrganization when no institution type is stored |
| hasOccupation | Professions, each with its page |
| award | Awards on the profile and every published Award record |
| image | Photos, Knowledge Panel photos and the portrait at original size; Wikimedia Commons files |
| worksFor | Organizations founded (linked to their own Organization nodes) |
| sameAs | Social links, profile links, additional pages, Wikidata, the site's social links, contributing profiles |
| subjectOf | Articles about the person |
| identifier | The Knowledge Graph ID (googleKgMID) with its Google link |

Rank Math's own schema is controlled in HWS Base Tools; with its default it is left off pages this plugin covers.

## Schema checklist

Review every catalog structure, including types without published records:

- Homepage: WebSite, Person and the selected ProfilePage mode share stable identifiers; the profile page points to the Person.
- Biography: the selected ProfilePage, AboutPage or Person mode uses the same Person and a breadcrumb where applicable.
- Other pages: assigned contact, gallery and list pages select ContactPage, ImageGallery and CollectionPage; ordinary pages use WebPage.
- FAQs: Question and Answer values match the questions shown on the page and join the shared graph.
- Books: ItemPage points to Book; collections contain ItemList; authorship points to the profile Person.
- Organizations: ItemPage points to Organization; collections contain ItemList; founder relationships follow the stored person relationship.
- Awards: ItemPage and collections describe award records; awards remain text on Person because schema.org has no Award type.
- Exhibitions: ItemPage points to ExhibitionEvent with stored dates, location and organizer; collections contain ItemList.
- Quotes: ItemPage points to Quotation with its stored text and attribution; an empty quote produces no Quotation.
- Press releases: WebPage points to NewsArticle; publication dates, original URL and source credit come from stored fields. A publisher or distributor name does not establish authorship.
- Interviews: ItemPage points to CreativeWork, with the stored host, series and source links.
- Contributing profiles: WebPage points to the external profile; its address joins Person.sameAs.
- Author pages: ProfilePage points to the correct author Person, with the website and breadcrumb.

For each graph, check JSON encoding, unique top-level identifiers, valid URL values, entity relationships, every declared mode, and one shared output block without duplicate Rank Math schema. Confirm the served pages after installation.

`tests/integration/schema-checklist.php` is a read-only WordPress check, run with `wp eval-file` from the site root. It previews all structures and available modes, exercises six content builders with disposable in-memory records and metadata filters, and checks education typing and press-release attribution. It saves no records or options. Public HTTP output and external validator results are separate evidence.
