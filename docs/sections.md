# Sections: HTML and styling

Every `[person_section]` prints one wrapper, an optional heading and its body:

```html
<div class="sfpf-section sfpf-education sfpf-education--list" data-sfpf-section="education">
  <h2 class="sfpf-section__heading">Education</h2>
  …body…
</div>
```

`class="…"` adds classes to the wrapper; `style="none"` adds `sfpf-section--unstyled` and loads no CSS. The stylesheet (`assets/frontend/person-sections.css`) sets layout and spacing only; text color, fonts and link colors come from the page. Override these variables on `.sfpf-section` or any parent:

| Variable | Default | Changes |
| --- | --- | --- |
| `--sfpf-gap` | `16px` | Space between items |
| `--sfpf-radius` | `8px` | Card and photo corners |
| `--sfpf-border` | `rgba(0,0,0,.12)` | Card and row borders |
| `--sfpf-muted` | `rgba(0,0,0,.6)` | Secondary text (sources, years, facts) |
| `--sfpf-surface` | `rgba(0,0,0,.03)` | Photo backgrounds |
| `--sfpf-gallery-columns` | `3` (or `columns`) | Gallery columns |
| `--sfpf-loop-columns` | `3` (or `columns`) | `[person_loop]` columns |

Older class names (`founder-education`, `education-item`, `founder-articles`, `org-card`, `bio-section`…) stay on the same elements, so existing custom CSS keeps working.

## education

```html
<ul class="sfpf-education__list founder-education">
  <li class="sfpf-education__item education-item">
    <div class="sfpf-education__school college"><a href="https://en.wikipedia.org/wiki/…">Purdue University</a></div>
    <div class="sfpf-education__degree degree"><span>B.A.</span> in <span>Philosophy</span></div>
    <div class="sfpf-education__year year">2003-2007</div>
  </li>
</ul>
```

`layout="inline"` prints each school on one line as `<span>`s: school - degree in field, year.

## professions

```html
<ul class="sfpf-professions__list founder-professions">
  <li class="sfpf-professions__item profession-item">
    <div class="sfpf-professions__name name">Co-Founder</div>
    <div class="sfpf-professions__summary summary"><p>…</p></div>
    <a class="sfpf-professions__link page-link" href="…">View details</a>
  </li>
</ul>
```

`page="content"` also prints the profession page's content in `.sfpf-professions__page`; `page="none"` drops the link; `link_text` changes its text; `layout="names"` prints names only.

## articles and additional_urls

```html
<ul class="sfpf-links__list sfpf-links--titled founder-articles">
  <li class="sfpf-links__item">
    <a class="sfpf-links__link article-link" href="…" target="_blank" rel="noopener"><span class="sfpf-links__arrow article-arrow" aria-hidden="true">↗</span><span class="sfpf-links__title">Title</span></a>
    <span class="sfpf-links__source article-source-tag">forbes.com</span>
  </li>
</ul>
```

`layout="cards"` moves the source inside the link, adds `.sfpf-links__url` under the title and borders each item (no arrow); `compact` puts the source on the right of one line; `sources` groups items under `.sfpf-links__group-name`. `limit` shows the first n.

## gallery

```html
<div class="sfpf-gallery__grid sfpf-founder-gallery" style="--sfpf-gallery-columns:3">
  <figure class="sfpf-gallery__item"><a href="…"><img src="…" alt="…" loading="lazy"></a><figcaption>…</figcaption></figure>
</div>
```

Two columns under 900px, one under 560px. `source="knowledge_graph"` shows the Knowledge Panel photos.

## faq

```html
<div class="sfpf-faq__items sfpf-faq__items--accordion">
  <details class="sfpf-faq__item"><summary class="sfpf-faq__question">Question</summary><div class="sfpf-faq__answer">…</div></details>
</div>
```

`layout="list"` uses `<div class="sfpf-faq__item">` with an `<h3 class="sfpf-faq__question">`. The FAQ schema goes to the page's schema block, never into the body.

## socials and contact

```html
<ul class="sfpf-socials__list social-list">
  <li class="sfpf-socials__item social-item linkedin"><a class="sfpf-socials__link social-link" href="…" rel="noopener me">LinkedIn</a></li>
</ul>
<dl class="sfpf-contact__list"><div class="sfpf-contact__row"><dt>Email</dt><dd><a href="mailto:…">…</a></dd></div></dl>
```

## organizations

```html
<ul class="sfpf-organizations__list">
  <li class="sfpf-organizations__item org-card">
    <img class="sfpf-organizations__logo" src="…" alt="…">
    <div class="sfpf-organizations__body">
      <a class="sfpf-organizations__name" href="…">Name</a>
      <span class="sfpf-organizations__facts">2016 · Atlanta, Georgia</span>
      <div class="sfpf-organizations__summary">…</div>
      <a class="sfpf-organizations__website" href="…">example.com</a>
    </div>
  </li>
</ul>
```

`layout="compact"` prints one line per organization, name — facts, with `.sfpf-organizations__separator` between them and no summary or website.

## birthplace, nationality, knowledge_panel, biography

`.sfpf-birthplace__label` + `.sfpf-birthplace__value`; `.sfpf-nationality__value`; `a.sfpf-knowledge-panel__link`; `.sfpf-biography__text`.

## profile

One `.sfpf-profile__part.sfpf-profile__part--<part>` per part with an `.sfpf-profile__heading` and `.sfpf-profile__body`.
