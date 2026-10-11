# Deployment runbook — eurowet.pl 2026

The new site is the theme `eurowet-2026` (child of Hello Elementor) + plugin `eurowet-core`, deployed onto a **copy of the
production database** so orders, customers, WooCommerce product IDs, SEO history and media stay intact. All content is
imported idempotently from this repository with WP-CLI. Nothing below can be executed without hosting/admin access to
eurowet.pl — this document is the exact procedure for whoever has it.

## 0. Requirements
- PHP ≥ 8.1 (8.3 tested), WordPress ≥ 6.5 (script modules), WooCommerce ≥ 9.2 (GTIN field), Polylang (free is enough for
  catalogue translations; *Polylang for WooCommerce* only if translated products must be purchasable), Yoast SEO,
  Elementor (+ Pro, already licensed), Wordfence, LiteSpeed Cache. WP-CLI on the server (or SSH + `wp`).
- Parent theme **Hello Elementor** installed.
- Outbound HTTPS from the server for the first import (packshots and PDFs are downloaded from eurowet.pl's own media).

## 1. Backup and staging
1. Full backup: database dump + `wp-content/uploads` + current theme/plugins (`wp db export`, archive uploads). Keep it.
2. Create staging (`staging.eurowet.pl`): restore the copy, `wp search-replace https://eurowet.pl https://staging.eurowet.pl --all-tables`.
3. Protect staging: HTTP basic auth at the web server **and** `wp option update blog_public 0`. Disable outgoing e-mail on
   staging (e.g. WP Mail SMTP "disable all emails") so customers receive nothing. Pause payment gateways' live mode.

## 2. Install code
```bash
# from a checkout of this repository on the server
rsync -a wordpress/wp-content/themes/eurowet-2026/ $WP/wp-content/themes/eurowet-2026/
rsync -a wordpress/wp-content/plugins/eurowet-core/ $WP/wp-content/plugins/eurowet-core/
wp plugin activate eurowet-core
wp theme activate eurowet-2026
wp language core install pl_PL && wp language plugin install --all pl_PL && wp language theme install hello-elementor pl_PL
wp eurowet status            # modules, tables, versions
```

## 3. Configure and import (staging first)
```bash
REPO=/path/to/this/repo
wp eurowet setup --production --root=$REPO --dry-run    # read the plan
wp eurowet setup --production --root=$REPO              # permalinks, Woo bases, flat categories, Polylang, Yoast, menus, Elementor kit
python3 $REPO/tools/build_content.py                     # validates and writes content/build/*.json (refuses unverified evidence)
python3 $REPO/tools/build_redirects.py                   # content/redirects.csv
wp eurowet import all --production --root=$REPO          # taxonomy, company, products (+translations), content, reps, materials, redirects
wp eurowet redirects chains && wp eurowet redirects test --base=https://staging.eurowet.pl
wp eurowet audit                                         # content audit (needs without products, guides without links, …)
```
`--production` never overwrites prices, stock, SKUs, existing descriptions edited by Eurowet or pages marked `protect`.
Re-running any import is safe (idempotency key `_ew_source_key`).

Pages that exist on production as Elementor layouts (`marka-wlasna`, `kontakt`) are **not** overwritten. To switch them to the
new templates: Edit page → remove Elementor content ("Back to WordPress editor") → Page attributes → template, then paste the
text from `content/source/pages/*.md` (or run `wp eurowet import pages --root=$REPO` without `--production` for those pages).

## 4. Settings checklist (staging)
- **Settings → Reading:** front page = "Strona główna", posts page = "Aktualności". Search engines: visible only on production.
- **Polylang:** default PL without prefix, *Detect browser language* **off**, URL modifications: directory. Languages EN, FR, UA (locale uk).
  Translate the menus for EN/FR/UA (Appearance → Menus → language) — PL menus are assigned by setup.
- **Yoast:** Search appearance → Media → *Redirect attachment URLs to the attachment itself* **on**; disable attachment sitemaps;
  Organization logo = `assets/brand/eurowet-logo.png`; breadcrumbs on (setup does it). Check that product/guide/need sitemaps exist.
- **WooCommerce:** Settings → Site visibility = Live (production); permalinks show `/produkty/%product_cat%/`; check tax and
  shipping unchanged; **shop terms (regulamin)** page assigned (see client confirmations).
- **eurowet-core → Ustawienia:** company data (imported), lead recipients per form, retention (default 24 months), Finder
  threshold 0.45, optional AI interpreter key (Anthropic) — the Finder works without it; consent: GA4 / GTM / Meta Pixel IDs.
- **LiteSpeed Cache:** keep page cache; exclude `/koszyk/`, `/zamowienie/`, `/moje-konto/` (WooCommerce does by default);
  do **not** combine/defer JS modules of the theme (they are already deferred ES modules) — exclude
  `themes/eurowet-2026/assets/js/` and `plugins/eurowet-core/assets/js/` from JS combine; enable WebP replacement for uploads;
  purge all after deploy. REST: `/wp-json/eurowet/v1/finder` and `/lead` send `no-store` (must not be cached); `/finder/facets` and `/reps` allow a short public cache.
- **Wordfence:** firewall *Extended protection* (after a learning period on staging), brute-force protection (lock out after
  5 failures / 20 min), block admin username login, **2FA mandatory for administrators and shop managers** (Login Security),
  reCAPTCHA v3 on login optional, e-mail alerts only for critical events, scheduled scan daily.
- **Server:** HTTPS redirect, then **HSTS** `Strict-Transport-Security: max-age=31536000; includeSubDomains` (after verifying
  every subdomain has HTTPS). Security headers (nosniff, referrer policy, permissions policy, frame options) are sent by eurowet-core.

## 5. QA on staging (sign-off)
Run `tests/` (Playwright) against staging: `BASE_URL=https://staging.eurowet.pl npm --prefix tests test` — viewports
1920/1440/1366/1024/768/414/360, light/dark, keyboard, axe (WCAG 2.2 AA), reduced motion, 200 % zoom, PL/EN/FR/UA, Finder
test set (`php wp-content/plugins/eurowet-core/tests/finder-eval.php $REPO`), all 301s (`wp eurowet redirects test`).
Manual: place a real order with a test payment method, check e-mails, B2B/contact forms land in *B2B Leads*, consent banner
(Reject → no GA/Meta requests in DevTools), Lighthouse mobile on home/product/guide (LCP < 2.5 s, CLS < 0.1, INP < 200 ms).

## 6. Go-live
1. Freeze content edits on production (short window). Export new orders since the staging copy if the window is long.
2. Either swap staging → production (preferred: same DB + new code) or repeat steps 2–4 on production with `--production`.
3. `wp option update blog_public 1`, purge LiteSpeed, `wp rewrite flush`.
4. **Google Search Console:** submit `https://eurowet.pl/sitemap_index.xml`; inspect home, 3 products, 3 guides; monitor
   *Pages → Not indexed* and *Redirects* daily for 2 weeks; keep the 301 table at least 12 months.
5. Bing Webmaster Tools: submit sitemap. Check `https://eurowet.pl/llms.txt` and `robots.txt`.

## 7. Rollback
Restore the backup from step 1 (DB + uploads + old theme/plugins). The new plugin only adds its own tables
(`{prefix}ew_*`) and post types; deactivating it leaves WooCommerce data intact.

## 8. After launch
- Review the Finder analytics (Eurowet → Analityka wyszukiwarki): queries without a match → new needs/guides.
- Guides carry a review date; eurowet-core flags guides older than 12 months for re-review.
- Extended languages (DE, CS, …): machine translation only from the admin, `noindex` until a reviewer approves.
