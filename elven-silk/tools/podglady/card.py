"""Zamazanie wygenerowanego przez AI nadruku na białej karteczce: wnętrze wielokąta karteczki
wypełniam płaszczyzną dopasowaną do jasnych pikseli karteczki (światło i cień zostają),
z lekkim ziarnem, a potem drukuję prawdziwe logo."""
import numpy as np
from PIL import Image, ImageDraw, ImageFilter

def blank_card(im, poly, shrink=5):
    im = im.convert('RGB'); W = im.width; k = W / 1024
    a = np.asarray(im).astype(float)
    P = [(x * k, y * k) for x, y in poly]
    cx = sum(p[0] for p in P) / len(P); cy = sum(p[1] for p in P) / len(P)
    Ps = [(cx + (x - cx) * (1 - shrink * k / 60), cy + (y - cy) * (1 - shrink * k / 60)) for x, y in P]
    m = Image.new('L', im.size, 0); ImageDraw.Draw(m).polygon(Ps, fill=255)
    mk = np.asarray(m) > 0
    lum = a.mean(2)
    sel = mk & (lum > np.percentile(lum[mk], 55))
    ys, xs = np.nonzero(sel)
    A = np.stack([xs, ys, np.ones_like(xs)], 1).astype(float)
    coef = [np.linalg.lstsq(A, a[ys, xs, c], rcond=None)[0] for c in range(3)]
    yy, xx = np.mgrid[0:a.shape[0], 0:a.shape[1]]
    plane = np.stack([coef[c][0] * xx + coef[c][1] * yy + coef[c][2] for c in range(3)], 2)
    plane += np.random.default_rng(3).normal(0, 1.6, plane.shape)
    soft = np.asarray(m.filter(ImageFilter.GaussianBlur(1.2 * k))).astype(float)[..., None] / 255
    out = a * (1 - soft) + plane * soft
    return Image.fromarray(np.clip(out, 0, 255).astype('uint8'))
