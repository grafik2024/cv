#!/usr/bin/env python3
"""Build content/build/*.json for `wp eurowet import` from editable sources in content/source/.

Sources
  content/source/taxonomy.yaml   species, areas, hubs, lines, material_types, product_cats
  content/source/company.yaml    company contact data (from the current eurowet.pl contact page)
  content/source/reps.yaml       sales representatives (from the current contact page)
  content/source/materials.yaml  catalogues / leaflets (from the current download pages)
  content/source/{needs,guides,ingredients,pages}/{slug}.md        Polish master (YAML front matter + Markdown)
  content/source/{needs,guides,ingredients,pages}/{slug}.{lang}.md translations (en, fr, ua)

Guards (the build FAILS instead of publishing unverified content)
  * every need→product "evidence" and ingredient "quote" must be a verbatim substring of the product's own text
    (content/data/products/*.json, *_verbatim fields) — products are never matched by invention;
  * every referenced product family, guide, need, hub, species, area must exist;
  * no placeholders (lorem ipsum, example product, coming soon, test@test, dummy);
  * no invented authors: guides may only use author "Zespół Eurowet" unless listed in content/source/people.yaml
    (people approved by the company; empty by default).

Usage: python3 tools/build_content.py [--check]   (--check validates without writing)
"""
import glob
import json
import os
import re
import sys
import unicodedata

import markdown
import yaml

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
SRC = os.path.join(ROOT, "content", "source")
OUT = os.path.join(ROOT, "content", "build")
LANGS = ("en", "fr", "ua")
VERBATIM_FIELDS = ("subtitle_verbatim", "badges_verbatim", "properties_verbatim", "usage_verbatim", "indications_verbatim",
                   "intended_for_verbatim", "precautions_verbatim", "composition_verbatim", "notes_verbatim",
                   "vet_context_verbatim", "analytical_verbatim")
PLACEHOLDERS = re.compile(r"lorem ipsum|example product|coming soon|test@test|dummy|\bTODO\b|\bTBD\b|xxx", re.I)
VET_SENTENCE = "Jeśli objawy są nasilone, utrzymują się lub stan zwierzęcia budzi niepokój, skonsultuj się z lekarzem weterynarii."

errors: list[str] = []
warnings: list[str] = []


def err(msg: str) -> None:
    errors.append(msg)


def norm(s: str) -> str:
    """Whitespace/quote/dash-insensitive form for verbatim checks (case-insensitive)."""
    s = unicodedata.normalize("NFC", str(s))
    s = s.replace(" ", " ").replace("–", "-").replace("—", "-").replace("„", '"').replace("”", '"').replace("“", '"')
    s = re.sub(r"\s+", " ", s)
    return s.strip().lower()


def md(text: str) -> str:
    text = (text or "").strip()
    if not text:
        return ""
    return markdown.markdown(text, extensions=["extra", "sane_lists"], output_format="html")


def read_md(path: str) -> tuple[dict, str]:
    raw = open(path, encoding="utf-8").read()
    m = re.match(r"^---\n(.*?)\n---\n?(.*)$", raw, re.S)
    if not m:
        err(f"{rel(path)}: missing YAML front matter")
        return {}, raw
    try:
        fm = yaml.safe_load(m.group(1)) or {}
    except yaml.YAMLError as e:
        err(f"{rel(path)}: YAML error {e}")
        fm = {}
    return fm, m.group(2)


def rel(p: str) -> str:
    return os.path.relpath(p, ROOT)


def load_yaml(name: str, default):
    p = os.path.join(SRC, name)
    if not os.path.exists(p):
        return default
    return yaml.safe_load(open(p, encoding="utf-8")) or default


# ------------------------------------------------------------------ product data (verbatim corpus)
FAMILIES: dict[str, dict] = {}
for f in sorted(glob.glob(os.path.join(ROOT, "content", "data", "products", "*.json"))):
    if f.endswith("catalog-summary.json"):
        continue
    for fam in json.load(open(f, encoding="utf-8")).get("families", []):
        slug = fam.get("family_slug")
        if slug:
            FAMILIES[slug] = fam


def family_text(slug: str) -> str:
    fam = FAMILIES.get(slug, {})
    parts = []
    for k in VERBATIM_FIELDS:
        v = fam.get(k)
        if isinstance(v, list):
            parts.extend(str(x) for x in v)
        elif v:
            parts.append(str(v))
    for e in fam.get("needs_evidence") or []:
        parts.append(str(e.get("evidence_verbatim", "")))
    return norm(" \n ".join(parts))


def check_family(slug: str, where: str) -> bool:
    if slug not in FAMILIES:
        err(f"{where}: unknown product family '{slug}'")
        return False
    return True


def check_verbatim(family: str, quote: str, where: str) -> None:
    if not quote:
        return
    q = norm(quote).strip(' ."')
    if q and q not in family_text(family):
        err(f"{where}: quote is not verbatim in product '{family}': \"{quote[:90]}\"")


def check_text(obj, where: str) -> None:
    s = json.dumps(obj, ensure_ascii=False)
    m = PLACEHOLDERS.search(s)
    if m:
        err(f"{where}: placeholder text '{m.group(0)}'")


# ------------------------------------------------------------------ taxonomy / company / reps / materials
taxonomy = load_yaml("taxonomy.yaml", {})
company = load_yaml("company.yaml", {})
reps = load_yaml("reps.yaml", [])
materials = load_yaml("materials.yaml", [])
people = load_yaml("people.yaml", [])  # approved real people (reviewers/authors); empty unless the company provides

species_slugs = {t["slug"] for t in taxonomy.get("species", [])}
area_slugs = {t["slug"] for t in taxonomy.get("areas", [])}
hub_slugs = {t["slug"] for t in taxonomy.get("hubs", [])}
approved_people = {p["name"] for p in people if isinstance(p, dict) and p.get("name")}


def load_collection(kind: str) -> dict[str, dict]:
    items: dict[str, dict] = {}
    files = sorted(glob.glob(os.path.join(SRC, kind, "*.md")))
    for path in files:
        base = os.path.basename(path)[:-3]
        lang = "pl"
        if "." in base:
            base, lang = base.rsplit(".", 1)
        if lang != "pl" and lang not in LANGS:
            err(f"{rel(path)}: unknown language suffix '{lang}'")
            continue
        fm, body = read_md(path)
        fm.setdefault("slug", base)
        fm["_body"] = body
        fm["_path"] = rel(path)
        items.setdefault(base, {})[lang] = fm
    for slug, langs in items.items():
        if "pl" not in langs:
            err(f"{kind}/{slug}: translation without Polish master")
    return {s: l for s, l in items.items() if "pl" in l}


NEEDS = load_collection("needs")
GUIDES = load_collection("guides")
INGREDIENTS = load_collection("ingredients")
PAGES = load_collection("pages")


def terms_of(fm: dict, where: str) -> dict:
    t = {}
    sp = [s for s in fm.get("species", []) or []]
    for s in sp:
        if s not in species_slugs:
            err(f"{where}: unknown species '{s}'")
    ar = [a for a in fm.get("areas", []) or []]
    for a in ar:
        if a not in area_slugs:
            err(f"{where}: unknown area '{a}'")
    if sp:
        t["ew_species"] = sp
    if ar:
        t["ew_area"] = ar
    hubs = fm.get("hubs") or ([fm["hub"]] if fm.get("hub") else [])
    for h in hubs:
        if h not in hub_slugs:
            err(f"{where}: unknown hub '{h}'")
    if hubs:
        t["ew_hub"] = hubs
    return t


def faq_items(fm: dict) -> list:
    out = []
    for i in fm.get("faq") or []:
        if i.get("q") and i.get("a"):
            out.append({"q": str(i["q"]).strip(), "a": md(str(i["a"]))})
    return out


# ------------------------------------------------------------------ needs
def build_needs() -> list:
    out = []
    for slug, langs in NEEDS.items():
        fm = langs["pl"]
        where = fm["_path"]
        rows = []
        roles = [p.get("role") for p in fm.get("products") or []]
        if fm.get("products") and roles.count("primary") != 1:
            err(f"{where}: exactly one product with role 'primary' is required when products are listed")
        for p in fm.get("products") or []:
            fam = p.get("family", "")
            if not check_family(fam, where):
                continue
            if p.get("role") not in ("primary", "similar", "complementary"):
                err(f"{where}: bad role for {fam}")
            if p.get("role") in ("primary", "similar") and not p.get("evidence"):
                err(f"{where}: product '{fam}' ({p.get('role')}) needs a verbatim 'evidence' quote")
            check_verbatim(fam, p.get("evidence", ""), where)
            reason = {"pl": str(p.get("reason", "")).strip()}
            for lang in LANGS:
                tr = langs.get(lang, {})
                r = (tr.get("product_reasons") or {}).get(fam)
                if r:
                    reason[lang] = str(r).strip()
            rows.append({"family_slug": fam, "role": p.get("role"), "reason": reason, "evidence": str(p.get("evidence", "")).strip()})
        for g in fm.get("guides") or []:
            if g not in GUIDES:
                err(f"{where}: unknown guide '{g}'")

        def meta_for(d: dict) -> dict:
            return {
                "_ew_short_answer": md(d.get("short_answer", "")),
                "_ew_synonyms": [str(x) for x in d.get("synonyms") or []],
                "_ew_questions": [str(x) for x in d.get("questions") or []],
                "_ew_red_flags": [str(x) for x in d.get("red_flags") or []],
                "_ew_faq": faq_items(d),
                "_ew_care_steps": md(d.get("care", "")),
                "_ew_avoid": md(d.get("avoid", "")),
            }

        item = {
            "slug": slug,
            "title": fm["title"],
            "content": md(fm["_body"]),
            "excerpt": str(fm.get("excerpt", "")).strip(),
            "menu_order": int(fm.get("order", 0)),
            "meta": {**meta_for(fm), "_ew_red_flag_level": fm.get("red_flag_level", "caution"),
                     "_ew_priority": int(fm.get("priority", 50)), "_ew_active": bool(fm.get("active", True))},
            "meta_shared": {"_ew_red_flag_level": fm.get("red_flag_level", "caution"), "_ew_priority": int(fm.get("priority", 50)),
                            "_ew_active": bool(fm.get("active", True))},
            "terms": terms_of(fm, where),
            "relations": {"need_products": rows, "guides": fm.get("guides") or []},
            "translations": {},
        }
        if not item["meta"]["_ew_short_answer"]:
            err(f"{where}: short_answer is required (answer-first)")
        for lang in LANGS:
            tr = langs.get(lang)
            if tr:
                item["translations"][lang] = {"slug": tr.get("slug", slug), "title": tr["title"], "content": md(tr["_body"]),
                                              "excerpt": str(tr.get("excerpt", "")).strip(), "meta": meta_for(tr)}
        check_text(item, where)
        out.append(item)
    return out


# ------------------------------------------------------------------ guides
def build_guides() -> list:
    out = []
    for slug, langs in GUIDES.items():
        fm = langs["pl"]
        where = fm["_path"]
        author = fm.get("author", "Zespół Eurowet")
        if author != "Zespół Eurowet" and author not in approved_people:
            err(f"{where}: author '{author}' is not approved (content/source/people.yaml)")
        reviewer = fm.get("reviewer", "")
        if reviewer and reviewer not in approved_people:
            err(f"{where}: reviewer '{reviewer}' is not approved (content/source/people.yaml)")
        for f in fm.get("products") or []:
            check_family(f, where)
        for n in fm.get("needs") or []:
            if n not in NEEDS:
                err(f"{where}: unknown need '{n}'")
        for g in (fm.get("related") or []) + ([fm["next"]] if fm.get("next") else []):
            if g not in GUIDES:
                err(f"{where}: unknown guide '{g}'")
        if fm.get("stage") not in ("informational", "practical", "product"):
            err(f"{where}: stage must be informational|practical|product")
        if not str(fm.get("tldr", "")).strip():
            err(f"{where}: tldr (W skrócie) is required")
        body = fm["_body"]
        if VET_SENTENCE in body:
            warnings.append(f"{where}: the standard vet sentence is added by the template; remove it from the body")

        def meta_for(d: dict) -> dict:
            return {
                "_ew_tldr": md(d.get("tldr", "")),
                "_ew_faq": faq_items(d),
                "_ew_red_flags": [str(x) for x in d.get("red_flags") or []],
                "_ew_sources": [s for s in (d.get("sources") or fm.get("sources") or []) if isinstance(s, dict) and s.get("title")],
            }

        shared = {
            "_ew_stage": fm.get("stage"),
            "_ew_reviewed": str(fm.get("reviewed", "")),
            "_ew_needs_review": bool(fm.get("needs_review", False)),
            "_ew_review_note": str(fm.get("review_note", "")),
            "_ew_author_label": author,
            "_ew_reviewer": reviewer,
            "_ew_red_flag": bool(fm.get("red_flag", True)),
            "_ew_legacy_path": str(fm.get("legacy_path", "")),
        }
        item = {
            "slug": slug,
            "title": fm["title"],
            "content": md(body),
            "excerpt": str(fm.get("excerpt", "")).strip(),
            "date": str(fm.get("date", "")) or None,
            "meta": {**meta_for(fm), **shared},
            "meta_shared": shared,
            "terms": terms_of(fm, where),
            "relations": {"products": fm.get("products") or [], "needs": fm.get("needs") or [], "related": fm.get("related") or [],
                          "next": fm.get("next", "")},
            "image": fm.get("image", ""),
            "image_alt": fm.get("image_alt", ""),
            "translations": {},
            "_decision": fm.get("decision", "new"),
            "_merged_from": fm.get("merged_from") or [],
        }
        if item["date"] is None:
            del item["date"]
        if fm.get("image") and not fm.get("image_alt"):
            err(f"{where}: image_alt is required with image")
        for lang in LANGS:
            tr = langs.get(lang)
            if tr:
                item["translations"][lang] = {"slug": tr.get("slug", slug), "title": tr["title"], "content": md(tr["_body"]),
                                              "excerpt": str(tr.get("excerpt", "")).strip(), "meta": meta_for(tr),
                                              "image_alt": tr.get("image_alt", "")}
        check_text(item, where)
        out.append(item)
    return out


# ------------------------------------------------------------------ ingredients
def build_ingredients() -> list:
    out = []
    for slug, langs in INGREDIENTS.items():
        fm = langs["pl"]
        where = fm["_path"]
        fams = [f for f in fm.get("families") or [] if check_family(f, where)]
        quotes = []
        for q in fm.get("quotes") or []:
            if check_family(q.get("family", ""), where):
                check_verbatim(q["family"], q.get("quote", ""), where)
                quotes.append({"family_slug": q["family"], "quote": str(q.get("quote", "")).strip()})
        for g in fm.get("guides") or []:
            if g not in GUIDES:
                err(f"{where}: unknown guide '{g}'")

        def meta_for(d: dict) -> dict:
            return {"_ew_summary": md(d.get("summary", "")), "_ew_aliases": [str(x) for x in d.get("aliases") or []]}

        shared = {"_ew_inci": str(fm.get("inci", "")), "_ew_sources": [s for s in fm.get("sources") or [] if isinstance(s, dict) and s.get("title")]}
        item = {
            "slug": slug, "title": fm["title"], "content": md(fm["_body"]), "excerpt": str(fm.get("excerpt", "")).strip(),
            "meta": {**meta_for(fm), **shared}, "meta_shared": shared, "terms": terms_of(fm, where),
            "relations": {"ingredient_families": fams, "function_quotes": quotes, "guides": fm.get("guides") or []},
            "translations": {},
        }
        for lang in LANGS:
            tr = langs.get(lang)
            if tr:
                item["translations"][lang] = {"slug": tr.get("slug", slug), "title": tr["title"], "content": md(tr["_body"]),
                                              "excerpt": str(tr.get("excerpt", "")).strip(), "meta": meta_for(tr)}
        check_text(item, where)
        out.append(item)
    return out


# ------------------------------------------------------------------ pages
def build_pages() -> list:
    out = []
    for slug, langs in PAGES.items():
        fm = langs["pl"]
        item = {"slug": slug, "title": fm["title"], "content": md(fm["_body"]), "excerpt": str(fm.get("excerpt", "")).strip(),
                "template": fm.get("template", ""), "protect": bool(fm.get("protect", False)), "translations": {}}
        for lang in LANGS:
            tr = langs.get(lang)
            if tr:
                item["translations"][lang] = {"slug": tr.get("slug", slug), "title": tr["title"], "content": md(tr["_body"]),
                                              "excerpt": str(tr.get("excerpt", "")).strip(), "template": fm.get("template", "")}
        check_text(item, fm["_path"])
        out.append(item)
    return out


def build_reps() -> list:
    from_vv = set(json.load(open(os.path.join(ROOT, "wordpress", "wp-content", "plugins", "eurowet-core", "assets", "img", "poland-voivodeships.json"), encoding="utf-8")).get("regions", {}).keys()) \
        if os.path.exists(os.path.join(ROOT, "wordpress", "wp-content", "plugins", "eurowet-core", "assets", "img", "poland-voivodeships.json")) else set()
    out = []
    for r in reps:
        for v in r.get("voivodeships") or []:
            if from_vv and v not in from_vv:
                err(f"reps.yaml: unknown voivodeship '{v}' for {r.get('name')}")
        out.append(r)
    check_text(out, "reps.yaml")
    return out


def main() -> int:
    check_only = "--check" in sys.argv or any(a.startswith("--only") for a in sys.argv)
    # --only=<kind>/<slug>: validate one source file (parallel writers); errors from other files are ignored, and
    # unknown cross-references to items not written yet are reported as warnings.
    only = next((a.split("=", 1)[1] for a in sys.argv if a.startswith("--only=")), "")
    built = {
        "taxonomy": taxonomy,
        "company": company,
        "reps": build_reps(),
        "materials": materials,
        "needs": build_needs(),
        "guides": build_guides(),
        "ingredients": build_ingredients(),
        "pages": build_pages(),
    }
    if only:
        mine = [e for e in errors if f"source/{only}." in e or f"{only}:" in e or e.startswith(only)]
        soft = [e for e in mine if re.search(r"unknown (guide|need) '", e)]
        hard = [e for e in mine if e not in soft]
        for e in soft:
            print("warning (cross-reference not built yet):", e)
        for e in hard:
            print("ERROR:", e)
        print(f"{only}: {len(hard)} error(s)")
        return 1 if hard else 0
    for w in warnings:
        print("warning:", w)
    if errors:
        for e in errors:
            print("ERROR:", e)
        print(f"{len(errors)} error(s) — nothing written.")
        return 1
    if not check_only:
        os.makedirs(OUT, exist_ok=True)
        for name, data in built.items():
            json.dump(data, open(os.path.join(OUT, name + ".json"), "w", encoding="utf-8"), ensure_ascii=False, indent=1)
    print("OK:", ", ".join(f"{k}={len(v) if isinstance(v, (list, dict)) else 1}" for k, v in built.items()), "(checked only)" if check_only else "")
    return 0


if __name__ == "__main__":
    sys.exit(main())
