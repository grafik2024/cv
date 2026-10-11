# Audit of eurowet.pl (state on 2026-10-06/07)

Source data: public REST API dumps and HTML captured from https://eurowet.pl (`audit/raw/`), one Markdown file per
item (`audit/extracted/`), product sections parsed from every product page (`content/data/products.base.json`).
Nothing here required back-office access; items that need it are marked **(to confirm with Eurowet)**.

## 1. Inventory

| Item | Count | Notes |
|---|---|---|
| Pages (incl. product content pages) | 428 | 174 PL product pages under `/produkty/…`, ~190 EN pages under `/en/…` |
| Posts (Aktualności) | 356 | 324 PL (`aktualnosci`), 32 EN (`bez-kategorii-en`); 186 posts have < 50 words |
| WooCommerce products | 133 | `/produkt/{slug}/`, separate from the content pages |
| Media | 2 597 | 1 263 PNG, 1 246 JPEG, 30 WebP, 35 PDF, 11 MP4, 6 HEIC; 967 MB total |
| Languages | PL, EN | Polylang; no FR/UA yet |
| Product families (after grouping capacities) | 121 | 173 product pages/variants (incl. 8 Amiwet pages and 33 catalogue-only health/bird products) |

## 2. Architecture and duplicates
- **Two URLs per product.** Every shop product exists twice: an Elementor content page `/produkty/{kategoria}/{slug}/`
  (rich text, indexed) and a WooCommerce product `/produkt/{slug}/` (indexed, own canonical, often different text).
  Both are in the Yoast sitemaps (`sitemap-page.xml`, `sitemap-product.xml`) → duplicate content and split signals.
  **New site:** one product = one WooCommerce product at the old content-page URL; `/produkt/…` → 301 (`content/redirects.csv`).
- **Orphan product pages.** 8 Amiwet pages sit directly under `/produkty/{slug}/`, not linked from any category page;
  a duplicate `peroxyvet-20ml-2` page exists. Mapped (Amiwet → Kolekcja Kolor&Pielęgnacja, duplicate → 301).
- **WooCommerce categories** live under `/kategoria-produktu/sklep/{kategoria}/` with a "sklep" parent and two slugs
  that differ from the content pages (`pielegnacja-oczu-i-uszu`, `kolekcja-kolor-pielegnacja`). Zn-Cynk is filed under
  dermokosmetyki. **New site:** flat categories at `/produkty/{kategoria}/`, parent removed (`wp eurowet setup`), 301s.
- **Shop buttons**: 41 links from product pages to the shop were checked — 13 rely on redirects, several point to slugs
  that no longer exist (e.g. `/produkt/zn-cynk_60tab/`), two different products share the same button slug.
- **Guides** ("Pielęgnacja", `/pielegnacja-2/…`): 26 articles + `/jak-czyscic-uszy/`, no hub structure, no dates shown,
  no link to products or between articles. All are migrated with an explicit keep/update/merge/301 decision.

## 3. Language handling (SEO risk)
- Polylang **browser-language detection is on**: a visitor whose browser prefers English requesting `/` is served the EN
  home — Googlebot (no Accept-Language) and users can see different content on the same URL. **New site:** detection off,
  PL without prefix, EN/FR/UA under `/en/ /fr/ /ua/` with hreflang + x-default.
- EN pages: many have **Polish Yoast descriptions**, titles with "ENG" suffixes, EN product texts that are older
  translations and sometimes differ from the PL page (e.g. Alervet indications). **New site:** translations are made from the
  Polish master texts; old EN product URLs keep working through localized bases (`/en/products/{category}/{product}/`).

## 4. On-page SEO
- 62 Yoast titles are used more than once (128 items, mostly seasonal greetings and events).
- 277 items have no meta description; 92 descriptions are duplicated across 373 items (59× "Aktualności").
- 266 items have no og:description; 133 products share the generic "Od 1992 producent i dystrybutor…".
- **Meta descriptions contradict product pages** (e.g. Triaderm page uses the Hexoderm text, Ektovet uses Hexoderm-K, Woo
  uses Alervet) and several descriptions make **stronger claims than the page** ("ogranicza skutki świądu" vs page
  "wspomaga zmniejszanie objawów świądu"). **New site:** descriptions generated from the product's own short text.
- 4 posts have emoji in the slug (canonical differs from the encoded path).
- No structured data beyond Yoast defaults; no Product/Offer from WooCommerce on the content pages that rank.
- Yoast **attachment sitemaps** are on (3 sitemaps, ~8 300 media URLs) → thousands of thin attachment pages.

## 5. Technical
- Server: Apache + LiteSpeed Cache (`x-litespeed-tag`), HTML cached `max-age=7200`.
- **No security headers** on pages: no HSTS, X-Content-Type-Options, Referrer-Policy, Permissions-Policy, frame options.
  **New site:** eurowet-core sends X-Content-Type-Options, Referrer-Policy, Permissions-Policy, X-Frame-Options; HSTS to be
  enabled at the server after HTTPS check (runbook).
- 404s return a proper 404 status.
- Front end: Elementor + WooCommerce assets on every page; no `fetchpriority` on the hero image; images served as large
  PNG/JPEG (521 files > 500 KB, largest 36.7 MB); 6 HEIC files cannot be displayed by browsers.

## 6. Media
- 2 519 of 2 550 images have **no alt text** (99 %).
- Packshots: several og:images and WooCommerce images are older packshots than the ones on the product pages.
- **New site:** imported packshots get alt text = product name + capacity; theme serves responsive `srcset`,
  WebP/AVIF for bundled imagery, explicit dimensions, lazy loading below the fold.

## 7. Content facts and claims
- 513 product data issues in 99 of 121 families (typos in INCI, capacity labels, stronger claims in badges/meta than in
  the page text, species conflicts, Polish text on EN pages, wrong category): `audit/reports/product-data-issues.md`.
- Company texts include claims that cannot be verified from public sources (e.g. "Wszystkie preparaty oparte są na naturalnych
  składnikach" — contradicted by products containing chlorhexidine/ketoconazole; "receptury opracowane we Francji…",
  "skuteczność potwierdzają lekarze weterynarii"). They are **not** used on the new site until confirmed
  (`docs/client-confirmations.md`).
- No online shop terms page (regulamin sklepu) was found among public pages **(to confirm with Eurowet — required for B2C sales)**.

## 8. Contact and sales network
- Contact page lists the head office (Kalisz, ul. Wrocławska 53-57 since 2024-05-01), sales and office phones, NIP/BDO and
  7 regional contacts covering all 16 voivodeships; imported as structured reps (`content/source/reps.yaml`).
- Catalogues: 5 PDF on eurowet.pl + 2 Google Drive; 34 leaflets on Google Drive (`content/source/materials.yaml`).
