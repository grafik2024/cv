#!/usr/bin/env python3
"""Product group "zdrowie-ptaki-inne": preparaty wspomagające zdrowie (23 pages) + preparaty dla ptaków ozdobnych (10 pages).
Every text field is copied verbatim from the product page sections in content/data/products.base.json (extracted from
eurowet.pl); WooCommerce id/price/stock come from audit/extracted/products/*.md. Families group pages of the same
product (capacities / pack sizes for the same target animals). Output: content/data/products/zdrowie-ptaki-inne.json
"""
import glob
import json
import os
import re

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
H = "/produkty/preparaty-wspomagajace-zdrowie/"
B = "/produkty/preparaty-dla-ptakow-ozdobnych/"

# slug: (name, line, product_type, [(page_path, capacity, woo_id)], species, body_areas)
FAMILIES = {
    "amylan-ad": ("Amylan-Ad", None, "karma uzupełniająca", [(H + "amylan-30szt/", "30 tab.", 5168)], ["pies", "kot"], ["układ pokarmowy"]),
    "diarsed": ("Diarsed", None, "karma uzupełniająca", [(H + "diarsed-30szt/", "30 tab.", 16597)], ["pies", "kot"], ["układ pokarmowy"]),
    "hepachol": ("Hepachol", None, "karma uzupełniająca", [(H + "hepachol-30ml/", "30 ml", 7853), (H + "hepachol-125ml/", "125 ml", 5229)], ["pies", "kot", "gołębie"], ["układ pokarmowy", "wątroba"]),
    "hepachol-zwierzeta-gospodarskie": ("Hepachol dla zwierząt gospodarskich", None, "karma uzupełniająca", [(H + "hepachol-1l/", "1 l", 5230)], ["zwierzęta gospodarskie"], ["układ pokarmowy", "wątroba"]),
    "karbowital": ("Karbowital", None, "karma uzupełniająca", [(H + "karbowital-30-tab/", "30 tab.", 10956), (H + "karbowital-125ml/", "125 ml", 5231)], ["pies", "kot", "gołębie"], ["układ pokarmowy"]),
    "karbowital-zwierzeta-gospodarskie": ("Karbowital dla zwierząt gospodarskich", None, "karma uzupełniająca", [(H + "karbowital-1l/", "1 l", 5232)], ["zwierzęta gospodarskie"], ["układ pokarmowy"]),
    "lespewet": ("Lespewet", None, "karma uzupełniająca", [(H + "lespewet-125ml/", "125 ml", 5233)], ["pies", "kot"], ["układ moczowy"]),
    "lespewet-pies": ("Lespewet dla psów", None, "karma uzupełniająca", [(H + "lespewet-30szt/", "30 tab.", 5234)], ["pies"], ["układ moczowy"]),
    "lespewet-kot": ("Lespewet dla kotów", None, "karma uzupełniająca", [(H + "lespewet-dla-kotow-60-tab-5-blistrow/", "60 tab.", 16712), (H + "lespewet-50szt/", "50 tab.", None)], ["kot"], ["układ moczowy"]),
    "metadiar": ("Metadiar", None, "karma uzupełniająca", [(H + "metadiar-50g/", "50 g", 21148), (H + "metadiar-60g/", "60 g", None)], ["pies", "kot"], ["układ pokarmowy"]),
    "probiotyk-kot": ("Probiotyk dla kotów", None, "karma uzupełniająca", [(H + "probiotyk-15-saszetek-kot/", "15 saszetek", 8810)], ["kot"], ["układ pokarmowy"]),
    "probiotyk-pies": ("Probiotyk dla psów", None, "karma uzupełniająca", [(H + "probiotyk-15-saszetek-pies/", "15 saszetek", 8807)], ["pies"], ["układ pokarmowy"]),
    "probiotyk": ("Probiotyk", None, "karma uzupełniająca", [(H + "probiotyk-15-saszetek/", "15 saszetek", None)], ["pies", "kot"], ["układ pokarmowy"]),
    "stresnal": ("Stresnal", None, "karma uzupełniająca", [(H + "stresnal-30ml/", "30 ml", 7813), (H + "stresnal-125ml/", "125 ml", 5239)], ["pies", "kot"], ["stres/zachowanie"]),
    "uromil": ("Uromil", None, "karma uzupełniająca", [(H + "uromil-30szt/", "30 tab.", 5241)], ["pies", "kot"], ["rozród/ciąża"]),
    "witamina-b12": ("Witamina B12", None, "karma uzupełniająca", [(H + "witamina-b12/", "50 tab.", 7925)], ["pies", "kot"], ["układ pokarmowy", "wątroba"]),
    "zn-cynk": ("Zn-Cynk", None, "karma uzupełniająca", [(H + "zn-cynk-60szt/", "60 tab.", 8530)], ["pies", "kot"], ["skóra", "sierść"]),
    "zn-a-b6": ("Zn-A-B6", None, "karma uzupełniająca", [(H + "zn-a-b6-60szt/", "60 tab.", None)], ["pies", "kot"], ["skóra", "sierść"]),
    "florabactil": ("Florabactil", None, "mieszanka paszowa uzupełniająca", [(B + "florabactil-30g/", "30 g", None)], ["ptaki ozdobne"], ["układ pokarmowy"]),
    "floracal": ("Floracal", None, "mieszanka paszowa uzupełniająca", [(B + "floracal-15ml/", "15 ml", None)], ["ptaki ozdobne"], ["ogólna kondycja/witalność"]),
    "floracholine": ("Floracholine", None, "mieszanka paszowa uzupełniająca", [(B + "floracholine-60ml/", "60 ml", None)], ["ptaki ozdobne"], ["wątroba"]),
    "floraferol": ("Floraferol", None, "mieszanka paszowa uzupełniająca", [(B + "floraferol-15ml/", "15 ml", None)], ["ptaki ozdobne"], ["rozród/ciąża"]),
    "floramucil": ("Floramucil", None, "mieszanka paszowa uzupełniająca", [(B + "floramucil-15ml/", "15 ml", None)], ["ptaki ozdobne"], ["inne"]),
    "floramue": ("Floramue", None, "mieszanka paszowa uzupełniająca", [(B + "floramue-15ml/", "15 ml", None)], ["ptaki ozdobne"], ["inne"]),
    "florarome": ("Florarome", None, "mieszanka paszowa uzupełniająca", [(B + "florarome-15ml/", "15 ml", None)], ["ptaki ozdobne"], ["układ pokarmowy"]),
    "florarutil": ("Florarutil", None, "mieszanka paszowa uzupełniająca", [(B + "florarutil-10g/", "10 g", None)], ["ptaki ozdobne"], ["inne"]),
    "floratonyl": ("Floratonyl", None, "mieszanka paszowa uzupełniająca", [(B + "floratonyl-15ml/", "15 ml", None)], ["ptaki ozdobne"], ["ogólna kondycja/witalność", "odporność"]),
    "floratransit": ("Floratransit", None, "mieszanka paszowa uzupełniająca", [(B + "floratransit-10g/", "10 g", None)], ["ptaki ozdobne"], ["układ pokarmowy"]),
}
CATEGORY = {H: "preparaty-wspomagajace-zdrowie", B: "preparaty-dla-ptakow-ozdobnych"}


def strip_md(s):
    return re.sub(r"\s+", " ", (s or "").replace("**", "")).strip() or None


base = json.load(open(os.path.join(ROOT, "content/data/products.base.json"), encoding="utf-8"))
items = base["products"] if isinstance(base["products"], list) else list(base["products"].values())
PAGES = {p["page_path"]: p for p in items}
WOO = {}
for f in glob.glob(os.path.join(ROOT, "audit/extracted/products/*.md")):
    t = open(f, encoding="utf-8").read()
    m = re.match(r"```json\n(.*?)\n```", t, re.S)
    j = json.loads(m.group(1))
    WOO[j["id"]] = j

errors, fams = [], []
seen_pages = set()
for slug, (name, line, ptype, variants, species, areas) in FAMILIES.items():
    first = PAGES.get(variants[0][0])
    if not first:
        errors.append(f"{slug}: page {variants[0][0]} missing")
        continue
    s = first["sections"]
    cat = CATEGORY[H if variants[0][0].startswith(H) else B]
    subtitle = first.get("subtitle")
    if subtitle and re.match(r"^\d", subtitle):  # parser picked the capacity as subtitle (bird pages)
        subtitle = None
    fam = {
        "family_slug": slug, "name": name, "line": line, "product_type": ptype, "category_slugs": [cat],
        "subtitle_verbatim": subtitle, "badges_verbatim": [b for b in first.get("badges") or [] if b and b.strip()],
        "species": species, "species_evidence": strip_md(s.get("intended_for")), "body_areas": areas,
        "needs_evidence": [], "key_ingredients": [],
        "properties_verbatim": strip_md(s.get("properties")), "usage_verbatim": strip_md(s.get("usage")),
        "indications_verbatim": strip_md(s.get("indications")), "intended_for_verbatim": strip_md(s.get("intended_for")),
        "precautions_verbatim": None, "composition_verbatim": strip_md(s.get("composition")),
        "notes_verbatim": strip_md(s.get("packaging")), "variants": [], "related_on_site": [], "documents": [],
        "data_issues": [], "vet_context_verbatim": None,
    }
    if "konsultację z lekarzem weterynarii" in (fam["usage_verbatim"] or ""):
        fam["vet_context_verbatim"] = "Przed użyciem zaleca się konsultację z lekarzem weterynarii."
    for path, cap, wid in variants:
        p = PAGES.get(path)
        if not p:
            errors.append(f"{slug}: page {path} missing")
            continue
        seen_pages.add(path)
        v = {"title": p["title"].strip(), "capacity": cap, "page_path": path, "woo_id": wid, "woo_path": None,
             "price_pln": None, "regular_price_pln": None, "in_stock": None,
             "packshot_url": (p.get("images") or [None])[0], "en_page_path": None}
        if wid:
            j = WOO.get(wid)
            if not j:
                errors.append(f"{slug}: woo {wid} missing")
            else:
                pr = j["woo"]["prices"]
                v.update(woo_path=j["path"], price_pln=int(pr["price"]) / 100, regular_price_pln=int(pr["regular_price"]) / 100,
                         in_stock=j["woo"].get("is_in_stock"))
        if p["sections"] != s and strip_md(p["sections"].get("properties")) != fam["properties_verbatim"]:
            fam["data_issues"].append(f"Variant page {path} text differs from {variants[0][0]}; family text taken from the first page.")
        fam["variants"].append(v)
        for rp in p.get("related_paths") or []:
            for other, spec in FAMILIES.items():
                if other != slug and any(rp == vv[0] for vv in spec[3]) and other not in fam["related_on_site"]:
                    fam["related_on_site"].append(other)
    if not fam["properties_verbatim"]:
        errors.append(f"{slug}: no properties text")
    fams.append(fam)

# Known data issues found while building (for the audit report).
for f in fams:
    if f["family_slug"] == "zn-cynk":
        f["data_issues"].append("WooCommerce product 8530 is assigned to dermokosmetyki-weterynaryjne; its page is under preparaty-wspomagajace-zdrowie (category to correct). Shop button on the page points to non-existent /produkt/zn-cynk_60tab/.")
    if f["family_slug"] in ("lespewet-kot",):
        f["data_issues"].append("Two pack sizes for cats (50 tab. page without shop, 60 tab. in shop) — confirm whether 50 tab. is still sold.")
    if f["family_slug"] == "floratonyl":
        f["data_issues"].append("Packshot file name is a generic template name (produkty_2000px_…png) — confirm the image shows Floratonyl.")
    if f["family_slug"] == "floramue":
        f["data_issues"].append("Badge on the page reads 'PRIÓRA' (typo for 'PIÓRA').")

group_pages = {pp for pp, x in PAGES.items() if x.get("category_slug") in ("preparaty-wspomagajace-zdrowie", "preparaty-dla-ptakow-ozdobnych")}
for missing in sorted(group_pages - seen_pages):
    errors.append(f"page not assigned to a family: {missing}")
out = {"group": "zdrowie-ptaki-inne", "families": fams, "unmatched_woo": []}
json.dump(out, open(os.path.join(ROOT, "content/data/products/zdrowie-ptaki-inne.json"), "w", encoding="utf-8"), ensure_ascii=False, indent=1)
print("families", len(fams), "variants", sum(len(f["variants"]) for f in fams), "woo", sum(1 for f in fams for v in f["variants"] if v["woo_id"]))
print("\n".join(errors) if errors else "NO ERRORS")
