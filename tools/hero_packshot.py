#!/usr/bin/env python3
"""Hero packshot derivatives from an ORIGINAL transparent packshot: crop to the alpha bbox (+ margin) and
uniform resize only — pixels are not altered. Outputs PNG (alpha), WebP (alpha) and AVIF (alpha) at two heights,
plus a 1024px square-ish texture used by the 3D lathe (same pixels).
Usage: hero_packshot.py <packshot.png> <out_dir> <slug>"""
import json, os, subprocess, sys
from PIL import Image

src, out_dir, slug = sys.argv[1], sys.argv[2], sys.argv[3]
os.makedirs(out_dir, exist_ok=True)
im = Image.open(src).convert("RGBA")
x0, y0, x1, y1 = im.getchannel("A").getbbox()
m = round((y1 - y0) * 0.02)
crop = im.crop((max(0, x0 - m), max(0, y0 - m), min(im.width, x1 + m), min(im.height, y1 + m)))
meta = {"source": os.path.basename(src), "crop": [x0 - m, y0 - m, x1 + m, y1 + m], "files": {}}
for h in (1000, 560):
    w = round(crop.width * h / crop.height)
    r = crop.resize((w, h), Image.LANCZOS)
    base = os.path.join(out_dir, f"{slug}-{h}")
    r.save(base + ".png", optimize=True)
    r.save(base + ".webp", quality=86, method=6)
    subprocess.run(["avifenc", "-q", "70", "-s", "6", base + ".png", base + ".avif"], check=False, capture_output=True)
    meta["files"][str(h)] = {"w": w, "h": h}
# Texture for the 3D lathe: the bbox crop WITHOUT margin (profile is measured on the bbox), power-of-two height.
tex = im.crop((x0, y0, x1, y1))
th = 1024
tex.resize((round(tex.width * th / tex.height), th), Image.LANCZOS).save(os.path.join(out_dir, f"{slug}-texture.webp"), quality=90, method=6)
json.dump(meta, open(os.path.join(out_dir, f"{slug}.json"), "w"), indent=1)
print(json.dumps(meta))
