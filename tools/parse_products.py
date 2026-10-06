#!/usr/bin/env python3
"""Build the verified product fact base from the extracted Elementor product pages.
All text fields are copied verbatim from eurowet.pl (no rewriting) so they can be cited as company-approved claims.
Usage: python3 tools/parse_products.py audit/extracted content/data/products.base.json"""
import json, os, re, sys, glob, html

EXT, OUT = sys.argv[1], sys.argv[2]
SECTION_ALIASES = {
    "wlasciwosci": "properties", "właściwości": "properties",
    "sposób stosowania": "usage", "stosowanie": "usage", "instrukcja prawidłowego stosowania": "usage", "dawkowanie": "usage",
    "zastosowanie": "indications", "wskazania": "indications",
    "przeznaczenie": "intended_for",
    "środki ostrożności": "precautions", "przeciwwskazania": "contraindications", "uwagi": "notes", "informacja": "notes",
    "opakowanie": "packaging",
    "skład": "composition", "skład analityczny": "analytical_composition", "dodatki": "additives",
}
STOP = {"powiązane produkty", "kup najtaniej", "skontaktuj się z nami", "[audit] internal links", "porównaj ceny"}

def norm(s): return re.sub(r"\s+", " ", s.replace(" ", " ")).strip()

def parse(path):
    raw = open(path, encoding="utf-8").read()
    meta = json.loads(raw.split("```json\n",1)[1].split("\n```",1)[0])
    body = raw.split("\n```\n",1)[1]
    links = re.findall(r"^(/[^\s]*)$", body.split("## [audit] internal links",1)[1].split("## [audit] external links")[0], re.M)
    lines = body.split("\n")
    rec = {"page_id": meta["id"], "page_path": meta["path"], "title": meta["title"], "modified": meta["modified"],
           "yoast": meta.get("yoast", {}), "badges": [], "sections": {}, "images": [], "shop_url": None, "related": [], "pdfs": []}
    cur, buf, h1_seen, subtitle = None, [], 0, None
    def flush():
        nonlocal buf
        if cur and buf:
            txt = norm(" ".join(buf))
            txt = re.sub(r"!\[[^\]]*\]\([^)]*\)", "", txt)
            txt = re.sub(r"<https?://[^>]+>", "", txt).strip()
            txt = re.sub(r"\s*Porównaj ceny\s*$", "", txt).strip()
            if txt:
                prev = rec["sections"].get(cur)
                rec["sections"][cur] = (prev + "\n" + txt) if prev else txt
        buf = []
    in_related = False
    for ln in lines:
        m = re.match(r"^(#{1,6})\s*(.*)$", ln)
        if m:
            level, head = len(m.group(1)), norm(m.group(2))
            key = head.lower().strip(" :")
            if key in STOP or key.startswith("[audit]"):
                flush(); cur = None
                in_related = key == "powiązane produkty"
                if key.startswith("[audit]"): break
                continue
            if key in SECTION_ALIASES:
                flush(); cur = SECTION_ALIASES[key]; continue
            if in_related:
                rec["related"].append(head); continue
            if level == 1:
                h1_seen += 1; rec.setdefault("display_name", head) if h1_seen == 2 else None; continue
            if level == 2 and h1_seen >= 1 and subtitle is None and cur is None:
                subtitle = head; rec["subtitle"] = head; continue
            if level == 3 and cur is None and not rec["sections"]:
                rec["badges"].append(head); continue
            flush(); cur = None
            continue
        if cur: buf.append(ln)
    flush()
    for src in re.findall(r"!\[[^\]]*\]\((https://eurowet\.pl/wp-content/uploads/[^)]+)\)", body):
        if "logotyp" in src.lower() or "logo" in src.lower(): continue
        if src not in rec["images"]: rec["images"].append(src)
    m = re.search(r"<(https://eurowet\.pl/produkt/[^>]+)>", body)
    if m: rec["shop_url"] = m.group(1)
    rec["pdfs"] = sorted(set(re.findall(r"https://eurowet\.pl/wp-content/uploads/[^\s>)]+\.pdf", body)))
    rec["related_paths"] = [l for l in links if l.startswith("/produkty/") and l != meta["path"]]
    rec["capacity_hint"] = re.findall(r"(\d+(?:[.,]\d+)?\s?(?:ml|l|L|g|kg|tab\.?|szt\.?|kaps\.?|x\s?\d+\s?ml))", meta["title"])
    return rec

files = sorted(glob.glob(os.path.join(EXT, "pages", "produkty__*__*.md")))
products = []
for f in files:
    r = parse(f)
    parts = r["page_path"].strip("/").split("/")
    r["category_slug"] = parts[1] if len(parts) >= 3 else None
    products.append(r)
# top-level product pages (shampoos directly under /produkty/<slug>/) — category pages are excluded when they list many products
for f in sorted(glob.glob(os.path.join(EXT, "pages", "produkty__*.md"))):
    if f.count("__") != 1: continue
    r = parse(f)
    if r["sections"].get("composition") or r["sections"].get("intended_for"):
        r["category_slug"] = None
        products.append(r)
# Woo mapping
woo = {}
for f in glob.glob(os.path.join(EXT, "products", "*.md")):
    raw = open(f, encoding="utf-8").read()
    meta = json.loads(raw.split("```json\n",1)[1].split("\n```",1)[0])
    w = meta.get("woo", {})
    woo[meta["path"]] = {"woo_id": meta["id"], "name": html.unescape(meta["title"]), "path": meta["path"],
        "price_minor": (w.get("prices") or {}).get("price"), "regular_minor": (w.get("prices") or {}).get("regular_price"),
        "sale_minor": (w.get("prices") or {}).get("sale_price"), "currency": (w.get("prices") or {}).get("currency_code"),
        "in_stock": w.get("is_in_stock"), "purchasable": w.get("is_purchasable"),
        "categories": [c.get("slug") for c in (w.get("categories") or [])], "images": [i.get("src") for i in (w.get("images") or [])],
        "short_description": w.get("short_description")}
for r in products:
    if r["shop_url"]:
        r["woo"] = woo.get(r["shop_url"].replace("https://eurowet.pl", ""))
r_paths = {r["shop_url"].replace("https://eurowet.pl","") for r in products if r.get("shop_url")}
orphans = [w for p, w in woo.items() if p not in r_paths]
json.dump({"products": products, "woo_without_product_page": orphans}, open(OUT, "w", encoding="utf-8"), ensure_ascii=False, indent=1)
print(len(products), "product pages;", sum(1 for r in products if r.get("woo")), "mapped to Woo;", len(orphans), "Woo products without page")
missing = [r["page_path"] for r in products if not r["sections"].get("composition")]
print("without composition:", len(missing), missing[:10])
