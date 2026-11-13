# Schema

Each page carries one JSON-LD block (`<script id="hexa-schema">`) that every Hexa plugin adds to; this plugin's source is `sfpf-person`. The person is one node, `https://site/#person`, referenced from every page.

| Structure (Schema tab) | Pages | Types | Modes |
| --- | --- | --- | --- |
| Homepage | Front page | WebSite, ProfilePage (mainEntity: the person), Person | profile_page (default), person, off |
| Biography page | The Biography page | ProfilePage or AboutPage, Person, BreadcrumbList | profile_page (default), about_page, person, off |
| Other pages | Every other page (or only the person pages) | WebPage / ContactPage / ImageGallery / CollectionPage (about: the person), Person, BreadcrumbList | all (default), assigned, off |
| FAQs | FAQs page and pages showing an FAQ | FAQPage | on, off |
| Books | Each book, the books list | ItemPage + Book (author: the person); CollectionPage + ItemList | on, off |
| Awards | Each award, the list | ItemPage; CollectionPage + ItemList; each award also on the Person | on, off |
| Exhibitions | Each exhibition, the list | ItemPage + ExhibitionEvent (contributor: the person) | on, off |
| Quotes | Each quote | ItemPage + Quotation | on, off |
| Press releases | Each press release | WebPage + NewsArticle (about and published by the person) | on, off |
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
| alumniOf, hasCredential | Education: schools and degrees |
| hasOccupation | Professions, each with its page |
| award | Awards on the profile and every published Award record |
| image | Photos, Knowledge Panel photos and the portrait at original size; Wikimedia Commons files |
| worksFor | Organizations founded (linked to their own Organization nodes) |
| sameAs | Social links, profile links, additional pages, Wikidata, the site's social links, contributing profiles |
| subjectOf | Articles about the person |
| identifier | The Knowledge Graph ID (googleKgMID) with its Google link |

Rank Math's own schema is controlled in HWS Base Tools; with its default it is left off pages this plugin covers.
