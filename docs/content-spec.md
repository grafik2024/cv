# Content source format (content/source) — for writers and agents

Build: `python3 tools/build_content.py` → `content/build/*.json` → `wp eurowet import <type>`.
Validate one file while writing: `python3 tools/build_content.py --only=needs/<slug>` (also `guides/…`, `ingredients/…`, `pages/…`).
The build refuses: non-verbatim evidence/quotes, unknown product families / guides / needs / hubs / species / areas,
placeholders, unapproved authors. Read `docs/agent-brief.md` (non-negotiable rules) first.

Reference data: `content/data/catalog-digest.md` (every product family, verbatim texts, slug in backticks),
`content/source/taxonomy.yaml` (species, areas, hubs), `audit/extracted/pages/*.md` (current site pages).

## Language and tone (PL master)
- Polish for pet owners: concrete, calm, second person ("Twój pies"), short paragraphs, no marketing superlatives.
- Answer first: the reader must get the core answer in the first 2–4 sentences (`short_answer` / `tldr`).
- Care, not diagnosis: describe hygiene and care steps; never diagnose, never give drug names, doses, or treatment plans.
- Never invent: product properties, claims, indications, dosages, studies, statistics, certificates, partners, people.
- General animal-care facts must be uncontroversial and widely accepted (e.g. "use lukewarm water", "dry the ear
  canal entrance gently", "don't use cotton buds deep in the ear canal"). When unsure, leave it out.
- No numbers you cannot source (frequencies, percentages, ages) unless they are quoted from an Eurowet product text
  (e.g. "Częstotliwość stosowania 2–3 razy w tygodniu" for that product only).
- The standard vet sentence is rendered by the templates — do not paste it into bodies.
- No keyword stuffing, no near-duplicate pages per synonym; one page per real problem.

## needs/<slug>.md — a "Potrzeba / problem" (controlled taxonomy for the Product Finder)
```yaml
---
title: Świąd i drapanie            # what owners call the problem (H1)
priority: 90                       # 0–100, ordering of tiles and tie-breaks
species: [pies, kot]               # slugs from taxonomy.yaml
areas: [skora]                     # slugs from taxonomy.yaml
red_flag_level: caution            # none | caution | urgent (urgent = see a vet first, product hidden behind the warning)
red_flags:                         # concrete signals that need a vet (no diagnoses): "rany, strupy lub krwawienie", "apatia lub brak apetytu"…
  - …
synonyms: [swędzi, drapie się, …]  # colloquial words/phrases incl. typos-free variants, diminutives, both species
questions: ["dlaczego pies ciągle się drapie", …]  # natural questions people type
short_answer: |                    # 2–4 sentences, answer-first, care-oriented
  …
care: |                            # Markdown list of practical care steps (no drugs, no doses)
  - …
avoid: |                           # Markdown list of what to avoid
  - …
faq:                               # 2–4 real questions; answers short, no invented facts
  - q: …
    a: …
products:                          # verified relations ONLY (from catalog-digest)
  - family: triaderm-excellence    # family slug
    role: primary                  # exactly one primary; others: similar | complementary
    reason: "Szampon pielęgnacyjny dla psów i kotów; producent wskazuje łagodzenie świądu i podrażnień."  # owner-facing, factual
    evidence: "Łagodzi świąd i podrażnienia."   # EXACT substring of a *_verbatim field of that family
guides: []                         # filled later by the guides stage
---
Optional extra body (Markdown). Usually empty.
```
Roles: `primary` = the single best-fitting product for the need as stated by its own text; `similar` = alternative with
the same purpose (evidence required); `complementary` = used alongside (e.g. ear cleaner + conditioner), evidence
recommended. Prefer products sold in the shop when two fit equally; never pick by price. 2–6 products total.

## guides/<slug>.md — "Porady i wiedza"
```yaml
---
title: Jak czyścić uszy psa i kota
hub: uszy                          # one topic hub (taxonomy.yaml hubs) — species hubs come from `species`
species: [pies, kot]
areas: [uszy]
stage: practical                   # informational | practical | product
decision: B                        # A keep | B update | C merge | new  (legacy pages only A/B/C)
legacy_path: /pielegnacja-2/jak-czyscic-ucho/   # old URL (for the 301 map); omit for new guides
merged_from: [/jak-czyscic-uszy/]  # other old URLs folded into this guide (decision C)
date: 2019-05-14                   # original publication date for legacy guides (from the page JSON); omit for new
reviewed: 2026-10-10               # date of this editorial review
author: Zespół Eurowet
excerpt: One sentence for cards and meta.
tldr: |                            # "W skrócie": 2–4 sentences answering the title question
  …
red_flag: true
red_flags: [ … ]                   # items for the "Kiedy do lekarza weterynarii" box
products: [otolan, …]              # product families relevant to the guide (their own text must support it)
needs: [higiena-uszu]              # need slugs
related: [jak-czyscic-oczy-psa-i-kota]  # 2–3 guide slugs
next: …                            # optional manual "next best article"
faq: [ {q: …, a: …} ]              # only questions answered in the article; FAQPage schema is emitted only if visible
image: assets/higgsfield/hub-uszy-1600.jpg   # optional; repository image (must exist) + image_alt
image_alt: …
sources: []                        # only real, checked sources {title, url, publisher, year}; empty is fine
---
## Dlaczego … (causes / background — general, non-diagnostic)
## Jak … krok po kroku (care)
## Czego unikać
## Kiedy do lekarza weterynarii   (one short paragraph; the box with red_flags + standard sentence is added by the template)
## Produkty Eurowet, które mogą pomóc  (only verbatim-supported uses; link `/produkty/...` page paths)
(FAQ, related guides and the next article are rendered by the template from the front matter.)
```
Legacy guides: keep the useful substance of the original article, correct errors, remove claims not supported by
product texts, restructure answer-first. Preserve the original publication date.

## ingredients/<slug>.md
```yaml
---
title: Fitosfingozyna
inci: Phytosphingosine
aliases: [Phytosphingosine HCl]
summary: |            # 1–3 sentences, ONLY what Eurowet product texts say about this ingredient's role
  …
families: [triaderm-excellence, alervet-excellence]   # families whose composition/key ingredients contain it
quotes:               # verbatim sentences from those families' texts about the ingredient's role
  - family: triaderm-excellence
    quote: "…"
guides: []
sources: []
---
```

## pages/<slug>.md — static pages (o-firmie, wspolpraca-b2b, marka-wlasna, kontakt, pobierz, …)
Front matter `title`, `template`, `excerpt`, `protect: true` (production keeps an edited page). Body = the client's
own text from the current site, edited for clarity only (no new capabilities, numbers or certificates).
