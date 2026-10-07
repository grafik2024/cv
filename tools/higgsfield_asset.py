#!/usr/bin/env python3
"""Download a Higgsfield generation, keep the original in a scratch dir, write web-optimised
derivatives (AVIF + WebP + JPEG, 1600 and 800 px wide) to content/assets/higgsfield/ and append the
asset to content/assets/higgsfield/manifest.json (prompt, model, job id, alt text, usage, AI disclosure).
Usage: higgsfield_asset.py <slug> <url> <job_id> <model> <alt_pl> <usage> <prompt> [original_dir]"""
import json, os, subprocess, sys, urllib.request
from PIL import Image

slug, url, job, model, alt, usage, prompt = sys.argv[1:8]
orig_dir = sys.argv[8] if len(sys.argv) > 8 else "/tmp/eurowet-hf-originals"
repo = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
out_dir = os.path.join(repo, "content", "assets", "higgsfield")
os.makedirs(orig_dir, exist_ok=True); os.makedirs(out_dir, exist_ok=True)
orig = os.path.join(orig_dir, f"{slug}.png")
if not os.path.exists(orig):
    urllib.request.urlretrieve(url, orig)
im = Image.open(orig).convert("RGB")
files = {}
for w in (1600, 800):
    h = round(im.height * w / im.width)
    r = im.resize((w, h), Image.LANCZOS)
    base = os.path.join(out_dir, f"{slug}-{w}")
    r.save(base + ".jpg", "JPEG", quality=80, optimize=True, progressive=True)
    r.save(base + ".webp", "WEBP", quality=78, method=6)
    try:
        subprocess.run(["avifenc", "-q", "55", "-s", "6", base + ".jpg", base + ".avif"], check=True, capture_output=True)
    except Exception as e:  # avifenc missing — AVIF optional, WebP/JPEG still served
        print("avif skipped:", e)
    files[str(w)] = {ext: os.path.basename(base + "." + ext) for ext in ("avif", "webp", "jpg") if os.path.exists(base + "." + ext)}
    files[str(w)]["height"] = h
man_path = os.path.join(out_dir, "manifest.json")
man = json.load(open(man_path)) if os.path.exists(man_path) else []
man = [m for m in man if m["slug"] != slug]
man.append({"slug": slug, "job_id": job, "model": model, "source_url": url, "prompt": prompt, "alt_pl": alt,
            "usage": usage, "ai_generated": True, "disclosure_pl": "Ilustracja wygenerowana z pomocą AI (Higgsfield).",
            "width": im.width, "height": im.height, "files": files})
json.dump(sorted(man, key=lambda m: m["slug"]), open(man_path, "w"), ensure_ascii=False, indent=1)
print(slug, {k: v for k, v in files.items()})
