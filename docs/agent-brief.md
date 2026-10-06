# Eurowet 2026 — brief for every contributor (human or agent)

Project: complete new version of https://eurowet.pl (Eurowet — Polish producer and distributor of veterinary
dermocosmetics, complementary feeds and care/hygiene products for dogs, cats, small mammals, birds, livestock).
Stack (already running on production, confirmed from /wp-json): WordPress + Elementor Pro + WooCommerce +
Polylang (PL default, EN under /en/) + Yoast SEO + Wordfence + LiteSpeed Cache + Jetpack.
The new build = theme `eurowet-2026` (child of Hello Elementor) + plugin `eurowet-core`, deployed onto the
existing install (keeps orders, customers, product IDs, SEO history). Target languages: PL, EN, FR, UA (hreflang uk).

## Repository layout
- `audit/raw/` public REST dumps of eurowet.pl (posts, pages, products, media) + raw HTML in `audit/raw/html/`
- `audit/extracted/{pages,posts,products}/*.md` one clean Markdown file per item, JSON front-matter with id/path/yoast
- `audit/extracted/index.json` index of all 917 items
- `content/data/products.base.json` verbatim product sections parsed from the 174 /produkty/ pages + Woo mapping
- `audit/reports/` audit reports (Markdown)
- `content/` all content to be imported (data JSON, articles, translations)
- `wordpress/wp-content/{themes,plugins}` code
- Local WordPress for QA: /opt/eurowet-wp served at http://localhost:8080 (wp-cli: `wp --allow-root --path=/opt/eurowet-wp`)

## Non-negotiable content rules
1. Never invent facts: product properties, claims, indications, dosages, ingredients, certificates, studies,
   customer numbers, partners, people. Product facts come ONLY from eurowet.pl text (quote verbatim, cite the page path).
2. Separate care (pielęgnacja) from diagnosis/treatment. Where a topic may involve disease, add the red-flag line:
   "Jeśli objawy są nasilone, utrzymują się lub stan zwierzęcia budzi niepokój, skonsultuj się z lekarzem weterynarii."
3. No placeholders (lorem ipsum, example product, test@test.pl, dummy prices).
4. Problem → product links only when the product's own text supports that use.
5. Persons: do not invent authors or vets. Use "Zespół Eurowet" / "Redakcja Eurowet" for editorial byline.
6. Keep the human reader first; no keyword stuffing, no near-duplicate pages per synonym.
