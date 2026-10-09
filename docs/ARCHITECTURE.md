# Eurowet 2026 — architektura techniczna (kontrakt implementacyjny)

Ten dokument jest **kontraktem** między modułami. Kod, który go łamie, jest błędem.
Język kodu: angielski (identyfikatory, komentarze). Język interfejsu: polski (domyślny) + tłumaczenia.

## 1. Model wdrożenia

Produkcja (potwierdzone w `/wp-json`): WordPress + Elementor Pro + WooCommerce + Polylang + Yoast SEO +
Wordfence + LiteSpeed Cache + Jetpack + Duplicate Post. Nowa wersja **nie jest nową instalacją** —
to motyw `eurowet-2026` (child theme Hello Elementor) i wtyczka `eurowet-core` wdrażane na **kopię
stagingową istniejącej bazy**, a po akceptacji na produkcję. Dzięki temu zostają: zamówienia, klienci,
ID produktów, ceny, VAT, stany, historia SEO, media library, konta.

Treści i relacje (potrzeby, porady, składniki, PH, przekierowania, tłumaczenia, rozszerzone pola produktów)
są w repozytorium jako dane (`content/`) i trafiają do WordPressa przez idempotentne komendy WP-CLI
(`wp eurowet import ...`). Ten sam importer buduje lokalne środowisko QA.

Wymagania: PHP ≥ 8.1, WP ≥ 6.6, WooCommerce ≥ 9, Polylang ≥ 3.6 (produkcja: Polylang Pro +
Polylang for WooCommerce — tłumaczone bazy URL i produkty), Yoast SEO ≥ 23, Elementor ≥ 3.25 (Pro opcjonalnie).
Wtyczka i motyw MUSZĄ działać (bez fatal error, z degradacją funkcji) gdy WooCommerce/Polylang/Yoast/Elementor są wyłączone.

## 2. Struktura repozytorium

```
wordpress/wp-content/plugins/eurowet-core/
  eurowet-core.php            bootstrap, stałe EW_CORE_VERSION, EW_CORE_DIR, EW_CORE_URL, autoloader
  src/                        PSR-4: namespace Eurowet\Core\  (src/Foo/Bar.php => Eurowet\Core\Foo\Bar)
    Plugin.php                rejestruje moduły (każdy moduł: klasa z metodą register(): void)
    Data/Registry.php         CPT, taksonomie, register_post_meta, register_term_meta (JEDYNE źródło nazw)
    Data/Meta.php             helpery get/set dla meta (typowane), sanitizery
    Admin/...                 menu, meta boxy, ustawienia, listy, analityka, audyt treści
    Graph/...                 relacje, related products, porady do produktu, next best article, huby
    Finder/...                silnik NL (czysty PHP, bez WP), REST, log, interpreter LLM
    Seo/...                   schema JSON-LD, llms.txt, przekierowania 301, robots, hreflang helpers
    Reps/...  Leads/...  Materials/...  Consent/...  I18n/...  Elementor/...  Cli/...  Security/...
  templates/components/*.php  szablony komponentów renderowanych przez ew_render()
  assets/css/*.css  assets/js/*.js (vanilla ES modules, bez bundlera)
  data/lexicon-{pl,en,fr,uk}.json   leksykon wyszukiwarki (gatunki, obszary, stopwords, red flags)
  languages/*.po|*.mo         text domain: eurowet-core
  tests/                      PHPUnit (silnik Finder, normalizacja, scoring) — vendor przez composer (dev)
wordpress/wp-content/themes/eurowet-2026/
  style.css functions.php theme.json
  inc/*.php                   setup, assets, template tags, Woo overrides, a11y, dark mode, performance
  template-parts/**           header, footer, sekcje strony głównej, karty
  woocommerce/**              nadpisania szablonów Woo
  *.php                       front-page, single-ew_need, single-ew_guide, archive-ew_guide, taxonomy-ew_hub,
                              single-ew_ingredient, archive-ew_ingredient, archive-ew_need, search, 404, page templates
  assets/css/ assets/js/ assets/fonts/ assets/img/ assets/vendor/three/
  languages/                  text domain: eurowet-2026
content/                      dane do importu (JSON/Markdown) — patrz §9
tools/                        skrypty audytu, importu, buildu, lokalnego WP
tests/                        Playwright (E2E, a11y axe, wizualne), Lighthouse
docs/                         dokumentacja, runbook wdrożenia, raport końcowy
```

## 3. Encje, typy treści i taksonomie (Data/Registry.php)

| Encja | Implementacja | Publiczny URL (PL) | Uwagi |
|---|---|---|---|
| PRODUKT | `product` (WooCommerce) | `/produkty/{product_cat}/{slug}/` | jedno źródło prawdy (zastępuje strony Elementor `/produkty/...` i `/produkt/...`) |
| KATEGORIA | `product_cat` | `/produkty/{slug}/` | sklep = strona `/produkty/` |
| LINIA PRODUKTOWA | tax `ew_line` (na `product`) | `/linia/{slug}/` | np. Excellence, Kolor & Pielęgnacja, Wita-Vet |
| RODZINA (pojemności) | tax `ew_family` (na `product`, niepubliczna) | — | przełącznik pojemności; produkty zostają `simple` (ID/URL/zamówienia bez zmian) |
| GATUNEK | tax `ew_species` (product, ew_need, ew_guide, ew_ingredient) | niepubliczna archiwa | terminy: pies, kot, male-ssaki, fretka, ptaki-ozdobne, golebie, kon, zwierzeta-gospodarskie |
| OBSZAR CIAŁA | tax `ew_area` (product, ew_need, ew_guide) | niepubliczna | skora, siersc, uszy, oczy, jama-ustna-i-zeby, lapy-i-pazury, stawy, uklad-pokarmowy, watroba, uklad-moczowy, serce, odpornosc, kondycja, stres-i-zachowanie, rozrod, inne |
| POTRZEBA / PROBLEM | CPT `ew_need` | `/potrzeby/{slug}/`, archiwum `/potrzeby/` = Product Finder | rekord edytowalny bez programisty |
| PORADA (artykuł) | CPT `ew_guide` | `/porady/{slug}/`, archiwum `/porady/` = Centrum wiedzy | aktualności zostają jako `post` |
| HUB WIEDZY | tax `ew_hub` (na `ew_guide`) | `/porady/{slug}/` | term meta `hub_type` = topic\|species; `species_slug` dla hubów gatunkowych |
| SKŁADNIK | CPT `ew_ingredient` | `/skladniki/{slug}/`, archiwum `/skladniki/` | tylko składniki występujące w produktach |
| PRZEDSTAWICIEL | CPT `ew_rep` (niepubliczny) | — (strona `/znajdz-przedstawiciela/` + REST) | |
| WOJEWÓDZTWO | stała lista 16 slugów w `Reps\Voivodeships` | — | slug ASCII: dolnoslaskie, kujawsko-pomorskie, lubelskie, lubuskie, lodzkie, malopolskie, mazowieckie, opolskie, podkarpackie, podlaskie, pomorskie, slaskie, swietokrzyskie, warminsko-mazurskie, wielkopolskie, zachodniopomorskie |
| MATERIAŁ | CPT `ew_material` (niepubliczny, pliki) + tax `ew_material_type` | pliki PDF; listy na `/pobierz/`, `/pobierz/katalog/`, `/ulotki/` | |
| LEAD B2B | CPT `ew_lead` (prywatny) | — | formularze B2B / marka własna / kontakt PH |
| ARTYKUŁ (news) | `post` | `/{slug}/` | menu admina „Aktualności” |

Rozwiązywanie konfliktu `/porady/{slug}/`: najpierw `ew_guide` o tym slugu, potem term `ew_hub`.
Rozwiązywanie `/produkty/{a}/`: term `product_cat`; `/produkty/{a}/{b}/`: produkt `b` (gdy brak — 404).
Implementacja: filtr `request` w `Graph\Routing` (testy w Playwright).

### 3.1 Meta (klucze — wszystkie z prefiksem `_ew_`, rejestrowane `register_post_meta`, `show_in_rest` gdzie potrzebne)

Typy: `str` (sanitize_text_field), `html` (wp_kses_post), `int`, `bool` ('1'/''), `date` (Y-m-d), `ids` (array<int>), `json` (tablica struktur, zapis jako tablica PHP).

**product**: `_ew_subtitle` str · `_ew_badges` json<string[]> · `_ew_properties` html (Właściwości) · `_ew_usage` html (Sposób stosowania) ·
`_ew_indications` html (Zastosowanie) · `_ew_intended_for` html (Przeznaczenie) · `_ew_precautions` html · `_ew_composition` html (Skład / INCI) ·
`_ew_analytical` html (Składniki analityczne/dodatki) · `_ew_notes` html · `_ew_capacity` str · `_ew_product_type` str ·
`_ew_catalog_only` bool (brak sprzedaży online → CTA „Zapytaj o dostępność”) · `_ew_where_to_buy` json<{label,url}[]> ·
`_ew_documents` ids (załączniki PDF) · `_ew_video` int (załącznik) · `_ew_video_transcript` html · `_ew_spin360` ids · `_ew_model3d` int (GLB) ·
`_ew_rel_similar` ids · `_ew_rel_complementary` ids (ręczne — PIERWSZEŃSTWO nad algorytmem) · `_ew_key_ingredients` ids (ew_ingredient) ·
`_ew_evidence` json<{need,quote,field}[]> (cytaty uzasadniające zastosowania — tylko z tekstu Eurowet) ·
`_ew_need_ids` ids (indeks odwrotny, przeliczany przy zapisie potrzeby) · `_ew_source_path` str · `_ew_source_key` str (klucz idempotencji importu).

**ew_need**: `_ew_short_answer` html (W skrócie, 2–4 zdania) · `_ew_synonyms` json<string[]> · `_ew_questions` json<string[]> ·
`_ew_products` json<{product_id:int, role:'primary'|'similar'|'complementary', reason:str, evidence:str}[]> ·
`_ew_guides` ids · `_ew_red_flags` json<string[]> · `_ew_red_flag_level` str: none|caution|urgent · `_ew_priority` int (0–100) ·
`_ew_active` bool · `_ew_faq` json<{q,a}[]> · `_ew_care_steps` html · `_ew_avoid` html · `_ew_source_key` str.

**ew_guide**: `_ew_tldr` html (W skrócie) · `_ew_stage` str: informational|practical|product · `_ew_published_label` (nie używać — data publikacji = post_date) ·
`_ew_reviewed` date (ostatnia weryfikacja) · `_ew_needs_review` bool · `_ew_review_note` str · `_ew_author_label` str (domyślnie „Zespół Eurowet”) ·
`_ew_reviewer` str (TYLKO realna osoba zatwierdzona przez firmę; domyślnie pusty) · `_ew_sources` json<{title,url,publisher,year}[]> ·
`_ew_faq` json<{q,a}[]> · `_ew_needs` ids · `_ew_products` ids · `_ew_related` ids (ew_guide) · `_ew_next` int (ew_guide, ręczny „następny”) ·
`_ew_red_flag` bool (pokaż ramkę „kiedy do weterynarza”) · `_ew_red_flags` json<string[]> (punkty ramki) · `_ew_legacy_path` str · `_ew_video` int · `_ew_source_key` str.

**ew_ingredient**: `_ew_inci` str · `_ew_aliases` json<string[]> · `_ew_summary` html · `_ew_function_quotes` json<{product_id,quote}[]> ·
`_ew_sources` json · `_ew_guides` ids · `_ew_source_key` str. (Lista produktów = produkty z `_ew_key_ingredients` zawierającym ID.)

**ew_rep**: `_ew_first_name` · `_ew_last_name` · `_ew_position` · `_ew_phone` · `_ew_email` · `_ew_voivodeships` json<slug[]> ·
`_ew_segments` json<string[]> · `_ew_active` bool · `_ew_order` int · zdjęcie = obrazek wyróżniający.

**ew_material**: `_ew_file` int · `_ew_products` ids · `_ew_lang` str · `_ew_source_url` str.

**ew_lead**: `_ew_form` (b2b|private_label|rep_contact|contact) · `_ew_company` · `_ew_name` · `_ew_email` · `_ew_phone` · `_ew_voivodeship` ·
`_ew_segment` · `_ew_message` · `_ew_consent` json<{text,version,at}> · `_ew_status` (new|in_progress|closed) · `_ew_source_url` · `_ew_lang`.
Leady nie są publiczne, nie trafiają do REST publicznie, retencja konfigurowalna (domyślnie 24 mies.), cron usuwa starsze.

**Term meta**: `ew_hub`: `hub_type`, `species_slug`, `intro` (html), `icon` (str), `order` (int). `ew_area`/`ew_species`: `icon`, `order`, `label_plural`.

Polylang: wszystkie CPT i taksonomie `ew_*` są tłumaczalne (rejestracja przez filtr `pll_get_post_types`/`pll_get_taxonomies`),
z wyjątkiem `ew_rep`, `ew_lead`, `ew_family`. Meta z ID (`ids`, `_ew_products`, `_ew_next`) są tłumaczone przy kopiowaniu
(filtr `pll_translate_post_meta`) a przy renderze mapowane funkcją `ew_tr_id( $id )` (pll_get_post → fallback na oryginał).

## 4. Publiczne API PHP (funkcje globalne w `src/functions.php`, prefiks `ew_`)

Wszystkie zwracają dane w bieżącym języku (Polylang) i nigdy nie rzucają wyjątków do szablonu.

```php
ew_tr_id( int $post_id, ?string $lang = null ): int
ew_meta( int $post_id, string $key, mixed $default = null ): mixed          // typowany odczyt wg Registry
ew_need_products( int $need_id, ?string $role = null ): array             // [{product: WC_Product, role, reason, evidence}]
ew_primary_product( int $need_id ): ?array
ew_needs_for_product( int $product_id ): array                             // WP_Post[] ew_need (aktywne)
ew_related_products( int $product_id ): array                              // ['complementary'=>WC_Product[], 'similar'=>[], 'same_need'=>[], 'same_line'=>[], 'same_category'=>[]] (każda grupa max 4, bez duplikatów między grupami, ręczne pierwsze)
ew_family_variants( int $product_id ): array                               // [{product_id, capacity, url, price_html, in_stock, current:bool}]
ew_guides_for_product( int $product_id, int $limit = 3 ): array           // WP_Post[] — ranking, nie losowo
ew_guides_for_need( int $need_id, int $limit = 6 ): array
ew_related_guides( int $guide_id, int $limit = 3 ): array
ew_next_guide( int $guide_id ): ?array                                     // ['post'=>WP_Post|null,'product_cta'=>?array,'reason'=>string]
ew_product_ingredients( int $product_id ): array                           // WP_Post[] ew_ingredient
ew_ingredient_products( int $ingredient_id ): array                        // WC_Product[]
ew_hub_query_args( WP_Term $hub, array $extra = [] ): array                // WP_Query args dla huba (topic lub species)
ew_reps_for_voivodeship( string $slug ): array                             // [{name, position, phone, email, photo, voivodeships}]
ew_voivodeships(): array                                                    // slug => nazwa (pl)
ew_render( string $component, array $args = [] ): string                   // komponent → HTML (escapowany)
ew_get_option( string $key, mixed $default = null ): mixed                 // ustawienia wtyczki
```

### 4.1 Komponenty `ew_render()` (plugin: `templates/components/{name}.php`, CSS: `assets/css/components/{name}.css` ładowany warunkowo)

`product-card` (args: product, context) · `product-grid` (products, heading, id) · `guide-card` (post) · `guide-grid` ·
`need-tile` (need) · `need-tiles` (needs|area|species, heading) · `finder` (variant: hero|page|compact, preset species/area) ·
`finder-results` (server-side render dla `/potrzeby/?q=` i no-JS) · `related-products` (product_id) · `guides-for-product` (product_id) ·
`next-guide` (guide_id) · `vet-notice` (level: caution|urgent, context) · `tldr` (text) · `faq` (items, heading) ·
`sources` (items) · `article-meta` (post — autor/redakcja, data publikacji, aktualizacji, weryfikacji) · `breadcrumbs` (Yoast → fallback własny) ·
`rep-finder` (map + select) · `lead-form` (form: b2b|private_label|rep_contact) · `material-list` (type) · `ingredient-list` (product_id|all) ·
`family-switcher` (product_id) · `language-picker` · `hero-3d` (product_id) · `consent-banner`.

Kontrakt HTML: semantyczny, klasy BEM z prefiksem `ew-` (np. `ew-card`, `ew-card__title`), bez stylów inline poza custom properties,
dostępny (role/aria, focus visible, etykiety formularzy, aria-live dla wyników), działa bez JS (progressive enhancement:
finder bez JS = formularz GET do `/potrzeby/?q=` renderowany serwerowo).

## 5. Design tokens (motyw — `assets/css/tokens.css`) — komponenty wtyczki używają WYŁĄCZNIE tych zmiennych (z fallbackiem)

```
--ew-color-brand: #0074A8   (niebieski z logo)      --ew-color-accent: #38B448 (zielony z logo)
--ew-color-bg, --ew-color-surface, --ew-color-surface-2, --ew-color-text, --ew-color-text-muted, --ew-color-border,
--ew-color-link, --ew-color-focus, --ew-color-success, --ew-color-warning, --ew-color-danger, --ew-color-on-brand
--ew-font-sans (Manrope Variable, latin+latin-ext+cyrillic, self-hosted), --ew-font-display (to samo, ciężar 700–800)
--ew-step--1 … --ew-step-5 (fluid clamp), --ew-space-1 … --ew-space-9, --ew-radius-s/m/l/pill, --ew-shadow-1/2/3,
--ew-container: 1280px, --ew-gutter, --ew-duration-fast/base/slow, --ew-ease
```
Motyw: `:root` = jasny; `:root[data-theme="dark"]` = ciemny; `@media (prefers-color-scheme: dark) { :root:not([data-theme="light"]) {...} }`.
Wybór zapisany w `localStorage['ew-theme']` (`light|dark|system`), inline script w `<head>` ustawia atrybut przed pierwszym renderem.
Kontrasty: tekst ≥ 4.5:1, duży tekst/UI ≥ 3:1 w obu trybach (sprawdzane axe + skrypt tokenów).
Panel dostępności ustawia na `<html>`: `data-a11y-text="1|2|3"`, `data-a11y-contrast="high"`, `data-a11y-links="underline"`,
`data-a11y-spacing="wide"`, `data-a11y-motion="reduce"` (zapis `localStorage['ew-a11y']`). `prefers-reduced-motion` respektowane zawsze.

## 6. REST API — namespace `eurowet/v1`

| Metoda | Ścieżka | Opis |
|---|---|---|
| GET | `/finder?q=&species=&area=&need=&lang=` | wynik NL/facet (poniżej) |
| GET | `/finder/facets?lang=` | gatunki, obszary, potrzeby (wizard) |
| POST | `/finder/event` | `{log_id, type: product_click|buy_click|guide_click|need_click, target_id}` — anonimowo |
| GET | `/reps?voivodeship=` | PH dla województwa (tylko aktywni, dane publikowane) |
| POST | `/lead` | formularz (nonce `wp_rest`, honeypot `ew_hp`, czas wypełnienia ≥ 3 s, rate limit 5/h na hash IP, zgoda RODO wymagana) |
| GET | `/translate/status?post=&lang=` | rozszerzone języki (opcjonalnie) |

Odpowiedź `/finder`:
```json
{ "query": "pies cały czas się drapie", "normalized": "pies caly czas sie drapie", "corrected": null,
  "intent": { "need_id": 12, "need_slug": "swiad-i-drapanie", "confidence": 0.82, "species": "pies", "area": "skora",
              "matched": ["drapie→drapanie", "pies"], "interpreter": "local|llm" },
  "match": true,
  "red_flag": { "level": "caution|urgent|none", "message": "…", "terms": ["krew"] },
  "need": { "id": 12, "title": "…", "url": "…", "short_answer": "…" },
  "primary": { "id": 101, "name": "…", "url": "…", "image": "…", "capacity": "200 ml", "intended_for": "…",
               "reason": "…", "evidence": "…", "buy_url": "…|null", "catalog_only": false },
  "complementary": [ /* 0–4, ten sam kształt */ ],
  "guides": [ { "id": 5, "title": "…", "url": "…", "tldr": "…" } ],
  "alternatives": [ { "need_id": 3, "title": "…", "url": "…" } ],
  "log_id": 991 }
```
Gdy brak dopasowania (`match:false`): `primary:null`, `complementary:[]`, `alternatives` = 3 najbliższe potrzeby (lub huby),
komunikat „nie znaleźliśmy pewnego dopasowania” + link do kontaktu. **Nigdy losowy produkt.** Próg pewności konfigurowalny (domyślnie 0.45).
`red_flag.level == urgent` → brak rekomendacji produktu, komunikat o pilnym kontakcie z lekarzem weterynarii.

## 7. Product Finder — silnik (src/Finder, czysty PHP, testowalny bez WP)

Proces: ZAPYTANIE → normalizacja (lowercase, NFKC, usunięcie diakrytyków do indeksu, interpunkcji) → tokenizacja →
stopwords (per język) → lekki stemming (PL: obcinanie końcówek fleksyjnych z listy; EN/FR: proste reguły; UK: końcówki) →
korekta literówek (Damerau-Levenshtein ≤1 dla tokenów 4–7 znaków, ≤2 dla ≥8, wobec słownika zbudowanego z synonimów/pytań/nazw potrzeb/leksykonu) →
detekcja gatunku i obszaru (leksykon) → scoring potrzeb (dopasowanie fraz n-gram synonimów > tokenów, waga pytań, zgodność gatunku/obszaru, priorytet) →
INTENCJA (potrzeba + pewność) → produkty WYŁĄCZNIE z relacji `_ew_products` tej potrzeby (zweryfikowane) → filtr gatunku (produkt musi mieć gatunek zgodny) →
porady z `_ew_guides`. Opcjonalny `LlmInterpreter` (Claude API, klucz w ustawieniach) wywoływany tylko gdy lokalna pewność < progu;
dostaje zamkniętą listę slugów potrzeb i może zwrócić tylko jeden z nich albo `none` (walidacja); wynik cache'owany (transient 30 dni, klucz = hash znormalizowanego zapytania + język).
Log (tabela `{prefix}ew_finder_log`): `id, created_at (DATETIME, zaokrąglone do godziny), lang, query (max 200 znaków, e-maile/telefony/cyfry ≥ 6 zamaskowane), need_id, confidence, matched (bool), primary_product_id, red_flag_level, source (finder|search|hub), interpreter`.
Tabela `{prefix}ew_finder_event`: `id, log_id, created_at, type, target_id`. Bez IP, bez cookies, bez user ID. Retencja 13 mies. (cron).

## 8. SEO / GEO

- Yoast: tytuły/opisy (szablony per typ), breadcrumbs (renderowane w szablonach), sitemap (CPT `ew_need`, `ew_guide`, `ew_ingredient` włączone; `ew_rep`, `ew_material`, `ew_lead`, `ew_family` wyłączone).
- Schema przez graf Yoast (`wpseo_schema_graph_pieces`, `wpseo_schema_*`): Organization (logo, sameAs, contactPoint), WebSite+SearchAction (→ `/potrzeby/?q=`),
  Article dla `ew_guide` (author = Organization „Zespół Eurowet” jeśli brak realnej osoby, datePublished, dateModified, `lastReviewed` na WebPage, about = potrzeby, mentions = produkty/składniki),
  FAQPage tylko gdy blok FAQ jest widoczny, BreadcrumbList (Yoast), VideoObject dla filmów z transkrypcją.
  Product: WooCommerce generuje własny JSON-LD — moduł `Seo\ProductSchema` uzupełnia go (brand Eurowet, image, description = Właściwości, sku jeśli niepusty, offers tylko gdy cena i sprzedaż online; dla `_ew_catalog_only` bez offers) i nie dubluje Product w grafie Yoast.
- `/llms.txt` (route rewrite, cache 12 h, per język sekcje): opis firmy, huby, potrzeby, porady, składniki, kategorie produktów — linki do kanonicznych URL.
- robots.txt: filtr `robots_txt` — Sitemap Yoast, brak blokad wyszukiwarek i botów wyszukiwania AI (OAI-SearchBot, ChatGPT-User, PerplexityBot, Claude-SearchBot, Bingbot, Googlebot); polityka dla botów treningowych konfigurowalna (domyślnie: allow).
- Przekierowania 301: `Seo\Redirects` (tabela `{prefix}ew_redirects`: source_path, target, code, hits, last_hit) — obsługa w `template_redirect` priorytet 1 (przed 404), import CSV `content/redirects.csv`, admin lista + licznik.
- hreflang + x-default: Polylang (wymagane ustawienie „ukryj kod języka domyślnego”, x-default = PL). Weryfikacja w QA.
- Treść kluczowa zawsze jako HTML (nie w canvas/obrazie/JS). Finder ma server-side render.

## 9. Dane w repozytorium (`content/`) — format importu

```
content/taxonomy/{species,areas,hubs,lines,material-types}.json   [{slug, name:{pl,en,fr,uk}, description:{...}, meta:{...}}]
content/data/products/*.json                                       fakty produktów (audyt — dosłowne cytaty)
content/products/enrichment.json                                   [{family_slug|woo_id, species[], areas[], line, key_ingredients[], rel_similar[], rel_complementary[], catalog_only, where_to_buy[]}]
content/needs/{slug}.json                                          {slug, title:{pl,en,fr,uk}, short_answer:{..}, synonyms:{pl:[],en:[],fr:[],uk:[]}, questions:{..}, species[], areas[], products:[{family_slug|product_key, role, reason:{..}, evidence}], guides:[slug], red_flags:{..}, red_flag_level, priority, faq:{pl:[{q,a}],...}, care_steps:{..}, avoid:{..}}
content/guides/{pl,en,fr,uk}/{slug}.md                             front matter YAML: title, slug, translation_of, hub[], species[], areas[], needs[], products[], related[], next, stage, tldr, faq[], sources[], published, updated, reviewed, author_label, red_flag, legacy_path, image, image_alt
content/ingredients/{pl,en,fr,uk}/{slug}.md                        front matter: title, slug, inci, aliases[], products[], guides[], sources[]
content/reps.json · content/company.json · content/materials.json · content/redirects.csv (old_path,new_path,status,reason) · content/i18n/*.po
```
Klucz produktu w danych: `family_slug` (wszystkie warianty) albo `woo:{id}` albo `page:{old_path}`.

## 10. Bezpieczeństwo i prywatność

Każdy endpoint: `permission_callback` jawny; zapis tylko z nonce + capability; `$wpdb->prepare`; escapowanie na wyjściu;
brak danych osobowych w logach; nagłówki: X-Content-Type-Options, Referrer-Policy strict-origin-when-cross-origin,
Permissions-Policy (camera=(), microphone=(), geolocation=()), X-Frame-Options SAMEORIGIN (HSTS i CSP — konfiguracja serwera/LiteSpeed, opis w runbooku).
Consent: kategorie necessary/analytics/marketing; Google Consent Mode v2 (default denied); skrypty analityczne/marketingowe
wstrzykiwane dopiero po zgodzie; przyciski Akceptuj / Odrzuć / Ustawienia o równej wadze; link „Ustawienia cookies” w stopce.

## 11. Wydajność

Budżety (mobile, 4G): LCP < 2.5 s, CLS < 0.1, INP < 200 ms. CSS krytyczny motywu < 40 KB, JS na stronie treści < 50 KB (bez Woo checkout).
Obraz LCP: `<img fetchpriority="high">` + preload, AVIF/WebP przez `<picture>` dla zasobów motywu; media library: WebP/AVIF przez LiteSpeed Image Optimization.
Warunkowe ładowanie: finder.js tylko gdy komponent na stronie; rep-map.js tylko na stronie PH; three.js tylko desktop ≥ 1024 px,
bez `prefers-reduced-motion`, bez `Save-Data`, po `requestIdleCallback` i po zdarzeniu LCP; poza ekranem — pauza renderu.
Usunięcie zbędnych assetów (wc-cart-fragments poza sklepem, block-library gdy nie używane, emoji, embeds).

## 12. Testy

`tests/` Playwright: viewporty 1920, 1440, 1366, 1024 (tablet poziomo), 768 (tablet), 414 (duży telefon), 360 (mały telefon);
Chromium + Firefox + WebKit (jeśli dostępne); light/dark; klawiatura; axe (WCAG 2.2 AA tags); reduced motion; zoom 200%;
PL/EN/FR/UA; Finder (frazy, potoczny język, literówki, synonimy, brak wyniku, red flags); mapa PH; formularze; koszyk/checkout; 404; SEO (title, canonical, hreflang, JSON-LD parse).
PHPUnit: silnik Finder.
