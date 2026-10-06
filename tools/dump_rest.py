#!/usr/bin/env python3
"""Dump public WordPress/WooCommerce REST data from eurowet.pl for the audit.
Usage: python3 tools/dump_rest.py <out_dir>
Only public, unauthenticated endpoints are used."""
import json, sys, time, urllib.request, urllib.parse, os

BASE = "https://eurowet.pl/wp-json"
OUT = sys.argv[1] if len(sys.argv) > 1 else "audit/raw"
os.makedirs(OUT, exist_ok=True)

def get(url, tries=4):
    for i in range(tries):
        try:
            req = urllib.request.Request(url, headers={"User-Agent": "EurowetAudit/1.0 (+site migration audit)"})
            with urllib.request.urlopen(req, timeout=60) as r:
                return json.loads(r.read().decode("utf-8")), dict(r.headers)
        except Exception as e:
            if i == tries - 1:
                raise
            time.sleep(2 ** (i + 1))

def dump(name, path, params=None, per_page=100):
    items, page = [], 1
    while True:
        p = dict(params or {})
        p.update({"per_page": per_page, "page": page})
        url = f"{BASE}{path}?{urllib.parse.urlencode(p)}"
        try:
            data, headers = get(url)
        except Exception as e:
            print(f"{name}: stop at page {page}: {e}")
            break
        if not isinstance(data, list) or not data:
            break
        items.extend(data)
        total_pages = int(headers.get("X-WP-TotalPages", headers.get("x-wp-totalpages", "1")))
        if page >= total_pages:
            break
        page += 1
    with open(os.path.join(OUT, f"{name}.json"), "w", encoding="utf-8") as f:
        json.dump(items, f, ensure_ascii=False, indent=1)
    print(f"{name}: {len(items)}")
    return items

dump("posts", "/wp/v2/posts", {"lang": ""})
dump("pages", "/wp/v2/pages", {"lang": ""})
dump("wp_products", "/wp/v2/product", {"lang": ""})
dump("store_products", "/wc/store/v1/products", {"lang": ""})
dump("product_cat", "/wp/v2/product_cat", {"lang": ""})
dump("store_categories", "/wc/store/v1/products/categories", {})
dump("categories", "/wp/v2/categories", {"lang": ""})
dump("tags", "/wp/v2/tags", {"lang": ""})
dump("product_tag", "/wp/v2/product_tag", {"lang": ""})
dump("media", "/wp/v2/media", {"lang": ""})
dump("users", "/wp/v2/users", {})
try:
    langs, _ = get(f"{BASE}/pll/v1/languages")
    json.dump(langs, open(os.path.join(OUT, "pll_languages.json"), "w"), ensure_ascii=False, indent=1)
    print("pll languages:", len(langs))
except Exception as e:
    print("pll languages not public:", e)
