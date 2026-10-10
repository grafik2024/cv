#!/usr/bin/env python3
"""Compact, human/agent-readable digest of every product family (verbatim fields only) for content work:
content/data/catalog-digest.md. Evidence for need→product relations must still be copied from these verbatim texts."""
import glob, json, os

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
out = ["# Katalog Eurowet — skrót rodzin produktów (teksty dosłowne z eurowet.pl)", "",
       "Pola *_verbatim są dosłownymi cytatami z kart produktów. Dowody (evidence) w plikach potrzeb muszą być ich dokładnymi fragmentami.", ""]
n = 0
for f in sorted(glob.glob(os.path.join(ROOT, "content/data/products/*.json"))):
    data = json.load(open(f, encoding="utf-8"))
    out.append(f"## Grupa: {data.get('group', os.path.basename(f))}")
    for fam in data["families"]:
        n += 1
        caps = ", ".join(f"{v.get('capacity')}{' (sklep)' if v.get('woo_id') else ' (katalog)'}" for v in fam["variants"])
        out.append(f"### {fam['name']} — `{fam['family_slug']}`")
        out.append(f"- kategoria: {', '.join(fam.get('category_slugs') or [])}; linia: {fam.get('line') or '—'}; typ: {fam.get('product_type') or '—'}")
        out.append(f"- gatunki: {', '.join(fam.get('species') or [])}; obszary: {', '.join(fam.get('body_areas') or [])}; warianty: {caps}")
        out.append(f"- strona: {fam['variants'][0].get('page_path')}")
        for k in ("subtitle_verbatim", "badges_verbatim", "intended_for_verbatim", "indications_verbatim", "properties_verbatim", "usage_verbatim", "precautions_verbatim", "vet_context_verbatim", "notes_verbatim"):
            v = fam.get(k)
            if not v:
                continue
            if isinstance(v, list):
                v = " | ".join(v)
            v = " ".join(str(v).split())
            if k in ("usage_verbatim", "notes_verbatim") and len(v) > 500:
                v = v[:500] + " […]"
            out.append(f"- {k}: {v}")
        ings = [i.get("name_pl") for i in fam.get("key_ingredients") or [] if isinstance(i, dict) and i.get("name_pl")]
        if ings:
            out.append(f"- kluczowe składniki: {', '.join(ings)}")
        ev = fam.get("needs_evidence") or []
        if ev:
            out.append("- potwierdzone zastosowania (need → cytat):")
            for e in ev:
                out.append(f"  - {e.get('need')} → „{e.get('evidence_verbatim')}” ({e.get('source_field')})")
        if fam.get("data_issues"):
            out.append(f"- uwagi audytu: {' / '.join(fam['data_issues'])[:400]}")
        out.append("")
open(os.path.join(ROOT, "content/data/catalog-digest.md"), "w", encoding="utf-8").write("\n".join(out))
print("families", n, "bytes", len("\n".join(out).encode()))
