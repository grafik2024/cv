#!/usr/bin/env python3
"""Build the voivodeship map SVG paths used by the rep-finder component.
Source: Natural Earth 1:10m admin-1 states/provinces (public domain), filtered to iso_a2 == PL.
Usage: build_poland_map.py <pl.geojson> <out.json>
Output JSON: {"viewBox": "...", "regions": {slug: {"name": ..., "d": ..., "cx": .., "cy": ..}}}"""
import json, math, sys, unicodedata

SLUG = {
    "dolnośląskie": "dolnoslaskie", "kujawsko-pomorskie": "kujawsko-pomorskie", "lubelskie": "lubelskie", "lubuskie": "lubuskie",
    "łódzkie": "lodzkie", "małopolskie": "malopolskie", "mazowieckie": "mazowieckie", "opolskie": "opolskie", "podkarpackie": "podkarpackie",
    "podlaskie": "podlaskie", "pomorskie": "pomorskie", "śląskie": "slaskie", "świętokrzyskie": "swietokrzyskie",
    "warmińsko-mazurskie": "warminsko-mazurskie", "wielkopolskie": "wielkopolskie", "zachodniopomorskie": "zachodniopomorskie",
}

def dp(points, eps):
    """Douglas–Peucker simplification."""
    if len(points) < 3:
        return points
    (x1, y1), (x2, y2) = points[0], points[-1]
    dmax, idx = 0.0, 0
    for i in range(1, len(points) - 1):
        x0, y0 = points[i]
        num = abs((y2 - y1) * x0 - (x2 - x1) * y0 + x2 * y1 - y2 * x1)
        den = math.hypot(y2 - y1, x2 - x1) or 1e-12
        d = num / den
        if d > dmax:
            dmax, idx = d, i
    if dmax > eps:
        return dp(points[: idx + 1], eps)[:-1] + dp(points[idx:], eps)
    return [points[0], points[-1]]

src, out = sys.argv[1], sys.argv[2]
features = json.load(open(src))["features"]
lat0 = 52.0
k = math.cos(math.radians(lat0))
def proj(lon, lat):
    return (lon * k, -lat)
rings = {}
for f in features:
    name = f["properties"]["name_pl"].replace("województwo ", "").strip()
    slug = SLUG[name]
    geom = f["geometry"]
    polys = geom["coordinates"] if geom["type"] == "MultiPolygon" else [geom["coordinates"]]
    rings[slug] = (name, [[proj(*pt) for pt in poly[0]] for poly in polys])
xs = [p[0] for _, rs in rings.values() for r in rs for p in r]
ys = [p[1] for _, rs in rings.values() for r in rs for p in r]
minx, maxx, miny, maxy = min(xs), max(xs), min(ys), max(ys)
W = 1000.0
scale = W / (maxx - minx)
H = (maxy - miny) * scale
regions = {}
for slug, (name, rs) in rings.items():
    parts, cx, cy, n = [], 0.0, 0.0, 0
    for r in rs:
        pts = [((x - minx) * scale, (y - miny) * scale) for x, y in r]
        mid = len(pts) // 2  # closed ring: simplify two open halves (DP is undefined on a closed path)
        pts = dp(pts[: mid + 1], 0.9)[:-1] + dp(pts[mid:], 0.9)
        if len(pts) < 4:
            continue
        parts.append("M" + "L".join(f"{x:.1f},{y:.1f}" for x, y in pts) + "Z")
        for x, y in pts:
            cx += x; cy += y; n += 1
    regions[slug] = {"name": name, "d": "".join(parts), "cx": round(cx / n, 1), "cy": round(cy / n, 1)}
json.dump({"viewBox": f"0 0 {W:.0f} {H:.0f}", "source": "Natural Earth 1:10m admin-1 (public domain)", "regions": regions}, open(out, "w"), ensure_ascii=False)
print(out, "viewBox", f"0 0 {W:.0f} {H:.0f}", "bytes", sum(len(r['d']) for r in regions.values()))
