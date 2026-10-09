#!/usr/bin/env python3
"""Build favicons / app icons for the eurowet-2026 theme from the Eurowet sign.

Source: wordpress/wp-content/themes/eurowet-2026/assets/brand/eurowet-sign.png (512x512, upscaled bitmap
with jagged edges). The sign is pure geometry (a regular pointy-top hexagon ring opened on the right + a
plus sign), so it is redrawn here as vector polygons fitted to that bitmap:
  binary-mask IoU vs. the original: cross 0.998, hexagon ring 0.951 (the residual is only the staircase
  aliasing of the original's diagonal edges). The full logo (eurowet-logo.png) shows a symmetric cross
  (arms 73 / 72 px from the vertical bar), so the "full mark" variant restores the right arm that the
  512 sign crops at its canvas edge.

Outputs (assets/icons/):
  favicon.svg            sign composition, vector; blue lifted to #5CB8EC under prefers-color-scheme: dark
  favicon.ico            16 / 32 / 48 px (sign composition, transparent)
  favicon-32.png         32 px
  icon-192.png           192 px, transparent, purpose "any"
  icon-512.png           512 px, transparent, purpose "any"
  apple-touch-icon.png   180 px, full mark on white (iOS does not support transparency)
  icon-maskable-512.png  full mark on white inside the 80 % safe zone, purpose "maskable"
  site.webmanifest

Usage: python3 tools/build_app_icons.py   (Pillow required; no other dependencies)
"""
from __future__ import annotations

import json
import math
from pathlib import Path

from PIL import Image, ImageDraw

ROOT = Path(__file__).resolve().parents[1]
OUT = ROOT / 'wordpress/wp-content/themes/eurowet-2026/assets/icons'

BLUE = '#0074A8'        # ARCHITECTURE §5 --ew-color-brand
BLUE_DARK = '#5CB8EC'   # brand blue lifted for dark UI (favicon.svg only)
GREEN = '#38B448'       # ARCHITECTURE §5 --ew-color-accent
LIGHT_BG = '#FFFFFF'
DARK_BG = '#0B1622'

# Geometry fitted to eurowet-sign.png, in its 512x512 coordinate space.
HEX = dict(cx=218.5, cy=260.25, ro=245.75, ri=180.5, sx_o=0.99, sx_i=1.002, cut=372.25)
VBAR = (395.75, 100.75, 457.0, 418.5)              # x0, y0, x1, y1
HBAR_Y = (233.0, 286.75)
HBAR_X0 = 264.5
HBAR_X1_SIGN = 512.0                                # cropped by the sign's canvas
VBAR_CX = (VBAR[0] + VBAR[2]) / 2
HBAR_X1_FULL = 2 * VBAR_CX - HBAR_X0                # symmetric arm, as in the full logo


def _hex(r: float, sx: float) -> list[tuple[float, float]]:
    c = HEX
    return [(c['cx'] + sx * r * math.cos(math.radians(-90 + 60 * k)),
             c['cy'] + r * math.sin(math.radians(-90 + 60 * k))) for k in range(6)]


def _x_cut(p: tuple[float, float], q: tuple[float, float], x: float) -> tuple[float, float]:
    t = (x - p[0]) / (q[0] - p[0])
    return (x, p[1] + t * (q[1] - p[1]))


def ring_polygon() -> list[tuple[float, float]]:
    """C-shaped ring: outer hexagon minus inner hexagon, cut open at x = HEX['cut']."""
    o = _hex(HEX['ro'], HEX['sx_o'])   # 0 top, 1 upper-right, 2 lower-right, 3 bottom, 4 lower-left, 5 upper-left
    i = _hex(HEX['ri'], HEX['sx_i'])
    x = HEX['cut']
    assert o[0][0] < x < o[1][0] and i[0][0] < x < i[1][0], 'cut must cross the slanted right edges'
    return [_x_cut(o[0], o[1], x), o[0], o[5], o[4], o[3], _x_cut(o[3], o[2], x),
            _x_cut(i[3], i[2], x), i[3], i[4], i[5], i[0], _x_cut(i[0], i[1], x)]


def cross_rects(full: bool) -> list[tuple[float, float, float, float]]:
    x1 = HBAR_X1_FULL if full else HBAR_X1_SIGN
    return [VBAR, (HBAR_X0, HBAR_Y[0], x1, HBAR_Y[1])]


def mark_bbox(full: bool) -> tuple[float, float, float, float]:
    pts = ring_polygon()
    xs = [p[0] for p in pts] + [r[2] for r in cross_rects(full)]
    ys = [p[1] for p in pts]
    return min(xs), min(ys), max(xs), max(ys)


def render(size: int, *, full: bool = False, bg: str | None = None, content: float = 1.0) -> Image.Image:
    """Rasterise at 8x and downsample. `content` = fraction of the canvas the mark's bbox may occupy
    (1.0 = the sign's own 512 framing; <1 = full mark centred with padding)."""
    ss = 8
    w = size * ss
    img = Image.new('RGBA', (w, w), bg or (0, 0, 0, 0))
    d = ImageDraw.Draw(img)
    if content >= 1.0 and not full:
        scale, ox, oy = w / 512.0, 0.0, 0.0
    else:
        x0, y0, x1, y1 = mark_bbox(full)
        scale = content * w / max(x1 - x0, y1 - y0)
        ox = (w - (x1 - x0) * scale) / 2 - x0 * scale
        oy = (w - (y1 - y0) * scale) / 2 - y0 * scale
    tr = lambda p: (ox + p[0] * scale, oy + p[1] * scale)  # noqa: E731
    d.polygon([tr(p) for p in ring_polygon()], fill=BLUE)
    for x0, y0, x1, y1 in cross_rects(full):
        a, b = tr((x0, y0)), tr((x1, y1))
        d.rectangle([a[0], a[1], b[0] - 1, b[1] - 1], fill=GREEN)
    return img.resize((size, size), Image.LANCZOS)


def svg() -> str:
    f = lambda v: f'{v:.2f}'.rstrip('0').rstrip('.')  # noqa: E731
    ring = ring_polygon()
    ring_d = 'M' + ' '.join(f'{f(x)} {f(y)}' for x, y in ring) + 'Z'
    cross_d = ''.join(f'M{f(x0)} {f(y0)}H{f(x1)}V{f(y1)}H{f(x0)}Z' for x0, y0, x1, y1 in cross_rects(False))
    return ('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">'
            f'<style>.b{{fill:{BLUE}}}.g{{fill:{GREEN}}}@media (prefers-color-scheme:dark){{.b{{fill:{BLUE_DARK}}}}}</style>'
            f'<path class="b" d="{ring_d}"/><path class="g" d="{cross_d}"/></svg>\n')


def main() -> None:
    OUT.mkdir(parents=True, exist_ok=True)
    (OUT / 'favicon.svg').write_text(svg(), encoding='utf-8')
    render(32).save(OUT / 'favicon-32.png', optimize=True)
    ico = [render(s) for s in (16, 32, 48)]
    ico[2].save(OUT / 'favicon.ico', sizes=[(16, 16), (32, 32), (48, 48)], append_images=ico[:2])
    render(192).save(OUT / 'icon-192.png', optimize=True)
    render(512).save(OUT / 'icon-512.png', optimize=True)
    # iOS: opaque tile, ~12 % padding around the full mark.
    render(180, full=True, bg=LIGHT_BG, content=0.76).convert('RGB').save(OUT / 'apple-touch-icon.png', optimize=True)
    # Maskable: the mark's bbox diagonal must stay inside the 80 % safe circle.
    x0, y0, x1, y1 = mark_bbox(True)
    diag_ratio = math.hypot(x1 - x0, y1 - y0) / max(x1 - x0, y1 - y0)
    render(512, full=True, bg=LIGHT_BG, content=0.78 / diag_ratio).convert('RGB').save(OUT / 'icon-maskable-512.png', optimize=True)
    manifest = {
        'name': 'EUROWET',
        'short_name': 'EUROWET',
        'id': '/',
        'start_url': '/',
        'scope': '/',
        'display': 'browser',
        'lang': 'pl',
        'dir': 'ltr',
        'background_color': LIGHT_BG,
        'theme_color': LIGHT_BG,
        'icons': [
            {'src': 'icon-192.png', 'sizes': '192x192', 'type': 'image/png', 'purpose': 'any'},
            {'src': 'icon-512.png', 'sizes': '512x512', 'type': 'image/png', 'purpose': 'any'},
            {'src': 'icon-maskable-512.png', 'sizes': '512x512', 'type': 'image/png', 'purpose': 'maskable'},
            {'src': 'favicon.svg', 'sizes': 'any', 'type': 'image/svg+xml', 'purpose': 'any'},
        ],
    }
    (OUT / 'site.webmanifest').write_text(json.dumps(manifest, indent=2, ensure_ascii=False) + '\n', encoding='utf-8')
    for p in sorted(OUT.glob('*')):
        if p.suffix in ('.png', '.ico', '.svg', '.webmanifest') and p.name != 'sprite.svg':
            print(f'{p.name:24s} {p.stat().st_size:7d} B')


if __name__ == '__main__':
    main()
