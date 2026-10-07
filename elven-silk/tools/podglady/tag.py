"""Wykrywanie białej zawieszki (karteczki) w wersji „bez organzy": jasny, mało nasycony,
zwarty obszar w dolnej połowie kadru. Zwraca środek, kąt i wymiary w skali 1024."""
import numpy as np
from PIL import Image
from scipy import ndimage

def find_tag(path, box=None):
    im = Image.open(path).convert('RGB').resize((1024, 1024))
    a = np.asarray(im).astype(float)
    mx, mn = a.max(2), a.min(2)
    sat = (mx - mn) / (mx + 1e-6)
    m = (mx > 175) & (sat < 0.16)
    if box:
        x0, y0, x1, y1 = box; z = np.zeros_like(m); z[y0:y1, x0:x1] = True; m &= z
    else:
        m[:420] = False
    m = ndimage.binary_opening(m, iterations=2)
    lab, n = ndimage.label(m)
    best = None
    for i in range(1, n + 1):
        ys, xs = np.nonzero(lab == i)
        area = len(xs)
        if area < 1200 or area > 30000:
            continue
        pts = np.stack([xs, ys], 1).astype(float); c = pts.mean(0)
        cov = np.cov((pts - c).T); ev, evec = np.linalg.eigh(cov)
        L = np.sqrt(ev) * 3.46          # boki prostokąta o tej samej wariancji
        fill = area / (L[0] * L[1] + 1e-6)
        if fill < 0.75 or L[0] / L[1] < 0.45:
            continue
        score = area * fill
        if best is None or score > best[0]:
            ang = np.degrees(np.arctan2(evec[1, 1], evec[0, 1]))   # oś dłuższa
            best = (score, c, L, ang, area, fill)
    return best

def tag_spot(path, box=None, fill_ratio=0.5):
    """Miejsce na logo na zawieszce: [cx, cy, rot, width] w skali 1024 albo None."""
    im = Image.open(path).convert('RGB').resize((1024, 1024))
    a = np.asarray(im).astype(float)
    mx, mn = a.max(2), a.min(2); sat = (mx - mn) / (mx + 1e-6)
    m = (mx > 175) & (sat < 0.16)
    z = np.zeros_like(m)
    if box: x0, y0, x1, y1 = box; z[y0:y1, x0:x1] = True
    else: z[420:] = True
    m &= z
    m = ndimage.binary_opening(m, iterations=2)
    r = find_tag(path, box)
    if r is None: return None
    _, c, L, ang, area, fill = r
    lab, n = ndimage.label(m)
    comp = lab == lab[int(round(c[1])), int(round(c[0]))]
    if not comp.any(): return None
    comp = ndimage.binary_fill_holes(comp)
    g = ndimage.gaussian_filter(comp.astype(float), 1.5)
    gy, gx = np.gradient(g); w = np.hypot(gx, gy)
    th = np.arctan2(-gy, gx)
    z4 = (w * np.exp(4j * th)).sum()
    phi = np.degrees(np.angle(z4) / 4)                  # obrót krawędzi karteczki, |phi| <= 45
    ys, xs = np.nonzero(comp); pts = np.stack([xs - c[0], -(ys - c[1])], 1)
    t = np.radians(phi); R = np.array([[np.cos(t), np.sin(t)], [-np.sin(t), np.cos(t)]])
    q = pts @ R.T
    wext = np.percentile(q[:, 0], 97) - np.percentile(q[:, 0], 3)
    hext = np.percentile(q[:, 1], 97) - np.percentile(q[:, 1], 3)
    width = min(fill_ratio * wext, fill_ratio * hext * 640 / 759)
    return [round(float(c[0]), 1), round(float(c[1]), 1), round(float(phi), 1), round(float(width), 1)]
