#!/usr/bin/env python3
"""Composite REAL Eurowet packshots (transparent PNG, pixels untouched apart from uniform scaling)
onto an AI-generated empty background plate. This guarantees the label, logo, colours and proportions
of the product stay identical to the original packshot (brief §42).
Usage: composite_packshot.py <background.png> <out_slug> <surface_y_ratio> <height_ratio> <packshot1.png> [packshot2.png ...]
  surface_y_ratio — vertical position (0..1) of the surface where products stand
  height_ratio    — product height as a fraction of the background height"""
import json, os, subprocess, sys
from PIL import Image, ImageFilter, ImageDraw

bg_path, slug, surface, hratio = sys.argv[1], sys.argv[2], float(sys.argv[3]), float(sys.argv[4])
packs = sys.argv[5:]
repo = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
out_dir = os.path.join(repo, "content", "assets", "composites")
os.makedirs(out_dir, exist_ok=True)
bg = Image.open(bg_path).convert("RGBA")
W, H = bg.size
items = []
for p in packs:
    im = Image.open(p).convert("RGBA")
    im = im.crop(im.getchannel("A").getbbox())          # trim transparent margin only
    th = int(H * hratio)
    tw = int(im.width * th / im.height)
    items.append(im.resize((tw, th), Image.LANCZOS))     # uniform scale — aspect ratio preserved
gap = int(W * 0.03)
total = sum(i.width for i in items) + gap * (len(items) - 1)
x = (W - total) // 2
base_y = int(H * surface)
canvas = bg.copy()
for im in items:
    # soft contact shadow under the product (drawn on the background, never on the product)
    sh = Image.new("L", (W, H), 0)
    d = ImageDraw.Draw(sh)
    d.ellipse([x - im.width * 0.05, base_y - im.width * 0.06, x + im.width * 1.05, base_y + im.width * 0.08], fill=120)
    sh = sh.filter(ImageFilter.GaussianBlur(im.width * 0.08))
    shadow = Image.new("RGBA", (W, H), (20, 40, 60, 0)); shadow.putalpha(sh)
    canvas = Image.alpha_composite(canvas, shadow)
    canvas.alpha_composite(im, (x, base_y - im.height))
    x += im.width + gap
rgb = canvas.convert("RGB")
files = {}
for w in (1600, 800):
    h = round(H * w / W)
    r = rgb.resize((w, h), Image.LANCZOS)
    base = os.path.join(out_dir, f"{slug}-{w}")
    r.save(base + ".jpg", "JPEG", quality=82, optimize=True, progressive=True)
    r.save(base + ".webp", "WEBP", quality=80, method=6)
    subprocess.run(["avifenc", "-q", "58", "-s", "6", base + ".jpg", base + ".avif"], check=False, capture_output=True)
    files[str(w)] = {"height": h}
man_path = os.path.join(out_dir, "manifest.json")
man = json.load(open(man_path)) if os.path.exists(man_path) else []
man = [m for m in man if m["slug"] != slug]
man.append({"slug": slug, "background": os.path.basename(bg_path), "packshots": [os.path.basename(p) for p in packs],
            "method": "real packshot pixels, uniform scaling only, composited on AI-generated empty background",
            "disclosure_pl": "Zdjęcie produktu oryginalne; tło wygenerowane z pomocą AI (Higgsfield).", "files": files})
json.dump(sorted(man, key=lambda m: m["slug"]), open(man_path, "w"), ensure_ascii=False, indent=1)
print(slug, W, H, files)
