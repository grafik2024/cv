#!/usr/bin/env python3
"""301 map OLD → NEW (content/redirects.csv: old_path,new_path,status,note) for `wp eurowet import redirects`.

Sources (all deterministic, nothing guessed):
  * WooCommerce product URLs /produkt/{slug}/ (and the old shop-button slugs on product pages) → new product URL
    /produkty/{kategoria}/{slug}/ (= old content page path whenever the category is unchanged);
  * old content product pages whose category changed or that were duplicates/orphans → their product;
  * WooCommerce category archives /kategoria-produktu/… → /produkty/{kategoria}/;
  * legacy guides and merged pages/posts → /porady/{slug}/ (from content/source/guides/*.md legacy_path / merged_from);
  * structural pages (/pielegnacja-2/, /sklep/, /pomoc-…/, …) → their new section.
Every OLD path is checked against the audit inventory (audit/extracted/index.json) and reported if unknown.
"""
import csv
import glob
import json
import os
import re
import sys

import yaml

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
sys.path.insert(0, ROOT)
CAT_MAP = {"pielegnacja-oczu-i-uszu": "produkty-do-pielegnacji-oczu-i-uszu", "kolekcja-kolor-pielegnacja": "kolekcja-kolorpielegnacja"}

rows: list[tuple[str, str, str, str]] = []
seen: dict[str, str] = {}
problems: list[str] = []
ambiguous: set[str] = set()


def add(old: str, new: str, note: str, status: str = "301") -> None:
    old = "/" + old.strip("/") + "/" if old.strip("/") else "/"
    if new and not new.startswith("http"):
        new = "/" + new.strip("/") + "/" if new.strip("/") else "/"
    if old == new:
        return
    if old in seen:
        if seen[old] != new:
            if "shop-button" in note:  # the same broken shop link was used on two different product pages: ambiguous, drop it
                rows[:] = [r for r in rows if r[0] != old]
                ambiguous.add(old)
            else:
                problems.append(f"conflict for {old}: {seen[old]} vs {new} ({note})")
        return
    if old in ambiguous:
        return
    seen[old] = new
    rows.append((old, new, status, note))


def path_of(url: str) -> str:
    return re.sub(r"^https?://[^/]+", "", url or "").split("?")[0]


# ---- products
families = []
for f in sorted(glob.glob(os.path.join(ROOT, "content/data/products/*.json"))):
    if f.endswith("catalog-summary.json"):
        continue
    families.extend(json.load(open(f, encoding="utf-8"))["families"])

page_to_new: dict[str, str] = {}
for fam in families:
    for v in fam["variants"]:
        page = v.get("page_path") or ""
        slug = page.rstrip("/").split("/")[-1] if page else (v.get("woo_path") or "").rstrip("/").split("/")[-1]
        cat = (fam.get("category_slugs") or [""])[0]
        cat = CAT_MAP.get(cat, cat)
        new = f"/produkty/{cat}/{slug}/"
        if page:
            page_to_new[page] = new
            add(page, new, f"product page category changed ({fam['family_slug']})")
        if v.get("woo_path"):
            add(v["woo_path"], new, f"WooCommerce product URL ({fam['family_slug']})")

base = json.load(open(os.path.join(ROOT, "content/data/products.base.json"), encoding="utf-8"))
items = base["products"] if isinstance(base["products"], list) else list(base["products"].values())
for p in items:
    new = page_to_new.get(p["page_path"])
    shop = path_of(p.get("shop_url") or "")
    if new and shop.startswith("/produkt/"):
        add(shop, new, "old shop-button slug on the product page")
    if not new:
        # orphan / duplicate product pages: map to the family variant with the same trailing slug or same title
        tail = p["page_path"].rstrip("/").split("/")[-1]
        cand = [n for pg, n in page_to_new.items() if pg.rstrip("/").split("/")[-1] in (tail, re.sub(r"-\d+$", "", tail)) or pg.rstrip("/").endswith("kolorpielegnacja-" + tail)]
        if len(cand) == 1:
            add(p["page_path"], cand[0], "duplicate/orphan product page")
        else:
            problems.append(f"unmapped product page {p['page_path']} candidates={cand}")

# ---- WooCommerce category archives
for c in json.load(open(os.path.join(ROOT, "audit/raw/product_cat.json"), encoding="utf-8")):
    old = path_of(c.get("link", ""))
    slug = CAT_MAP.get(c["slug"], c["slug"])
    if c["slug"] == "sklep":
        add(old, "/produkty/", "WooCommerce shop root category")
    elif slug == "akcesoria-dla-zwierzat":
        add(old, "/produkty/", "accessories category (products shown in their content categories)")
    else:
        add(old, f"/produkty/{slug}/", "WooCommerce category archive")

# ---- structural pages
for old, new, note in (
    ("/sklep/", "/produkty/", "shop page"),
    ("/pielegnacja-2/", "/porady/", "old guides index"),
    ("/pomoc-znajdz-rozwiazanie-dla-swojego-pupila/", "/potrzeby/", "old help page → Product Finder"),
    ("/produkty-higieniczne/", "/produkty/higiena/", "old hygiene products page"),
    ("/pobierz/katalog/", "/pobierz/", "catalogues now on the downloads page"),
    ("/ulotki/", "/pobierz/", "leaflets now on the downloads page"),
):
    add(old, new, note)

# ---- guides (legacy_path, merged_from) and merged posts
for f in sorted(glob.glob(os.path.join(ROOT, "content/source/guides/*.md"))):
    if re.search(r"\.(en|fr|ua)\.md$", f):
        continue
    raw = open(f, encoding="utf-8").read()
    m = re.match(r"^---\n(.*?)\n---", raw, re.S)
    fm = yaml.safe_load(m.group(1)) if m else {}
    slug = fm.get("slug") or os.path.basename(f)[:-3]
    for old in [fm.get("legacy_path")] + list(fm.get("merged_from") or []):
        if old:
            add(old, f"/porady/{slug}/", f"guide {slug} ({fm.get('decision', '')})")
for extra in glob.glob(os.path.join(ROOT, "content/source/redirects-extra.csv")):
    for r in csv.DictReader(open(extra, encoding="utf-8")):
        add(r["old_path"], r["new_path"], r.get("note", "manual"), r.get("status", "301"))

# ---- verify OLD paths exist in the audit inventory (or are Woo URLs / category archives)
index = json.load(open(os.path.join(ROOT, "audit/extracted/index.json"), encoding="utf-8"))
known = {path_of(i.get("path") or i.get("url") or "") for i in (index if isinstance(index, list) else index.get("items", []))}
for old, new, st, note in rows:
    if old not in known and not old.startswith("/kategoria-produktu/") and "shop-button" not in note and old not in ("/sklep/", "/ulotki/"):
        problems.append(f"OLD path not in audit inventory: {old} ({note})")

rows.sort()
with open(os.path.join(ROOT, "content/redirects.csv"), "w", encoding="utf-8", newline="") as fh:
    w = csv.writer(fh)
    w.writerow(["old_path", "new_path", "status", "note"])
    w.writerows(rows)
print(f"{len(rows)} redirects written to content/redirects.csv")
for p in problems:
    print("CHECK:", p)
