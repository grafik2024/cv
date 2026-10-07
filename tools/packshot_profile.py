#!/usr/bin/env python3
"""Derive a lathe (rotation) profile from the alpha silhouette of a REAL cylindrical packshot.
The 3D hero builds a LatheGeometry from this profile and projects the original packshot onto the front
hemisphere, so the label shown in 3D is the untouched original image (no AI re-texturing).
Usage: packshot_profile.py <packshot.png> <out.json> [rows=160]"""
import json, sys
from PIL import Image

src, out = sys.argv[1], sys.argv[2]
rows = int(sys.argv[3]) if len(sys.argv) > 3 else 160
im = Image.open(src).convert("RGBA")
a = im.getchannel("A")
x0, y0, x1, y1 = a.getbbox()
w, h = x1 - x0, y1 - y0
px = a.load()
cx = (x0 + x1) / 2
profile = []
for i in range(rows + 1):
    y = y0 + min(h - 1, round(i * (h - 1) / rows))
    xs = [x for x in range(x0, x1) if px[x, y] > 128]
    half = ((max(xs) - min(xs) + 1) / 2) if xs else 0
    # y from bottom (0) to top (1), radius normalised by bbox height
    profile.append([round(1 - (y - y0) / h, 5), round(half / h, 5)])
profile.reverse()
json.dump({"source": src.split("/")[-1], "image_size": im.size, "bbox": [x0, y0, x1, y1],
           "aspect": round(w / h, 5), "center_x": cx, "profile": profile}, open(out, "w"), indent=0)
print(out, "rows", len(profile), "aspect", round(w / h, 4))
