"""Maska korony: różnica (zdjęcie z koroną − baza), wygładzona, największe skupisko + wypełnienie.
crown_mask(base, korona) -> float mask 0..1 (rozmyta krawędź)."""
import numpy as np
from PIL import Image, ImageFilter
from scipy import ndimage

def crown_mask(base, kor, thr=55):
    a = np.asarray(base.convert('RGB')).astype(float); b = np.asarray(kor.convert('RGB')).astype(float)
    d = np.abs(a - b).sum(2)
    d = ndimage.gaussian_filter(d, 2.0)
    m = d > thr
    m = ndimage.binary_opening(m, iterations=2)
    lab, n = ndimage.label(m)
    if n == 0:
        return np.zeros(d.shape)
    sizes = ndimage.sum(m, lab, range(1, n + 1))
    keep = np.zeros_like(m)
    big = sizes.max()
    for i, s in enumerate(sizes):
        if s >= big * 0.15:
            keep |= lab == i + 1
    keep = ndimage.binary_closing(keep, iterations=6)
    keep = ndimage.binary_fill_holes(keep)
    keep = ndimage.binary_dilation(keep, iterations=5)
    f = Image.fromarray((keep * 255).astype('uint8')).filter(ImageFilter.GaussianBlur(4))
    return np.asarray(f).astype(float) / 255

def apply(var, kor, mask):
    v = np.asarray(var.convert('RGB')).astype(float); k = np.asarray(kor.convert('RGB')).astype(float)
    m = mask[..., None]
    return Image.fromarray(np.clip(v * (1 - m) + k * m, 0, 255).astype('uint8'))
