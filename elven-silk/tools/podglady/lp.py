"""Nadruk prawdziwego logo jako funkcja (ta sama logika co logoprint.py)."""
import os
import numpy as np
from PIL import Image, ImageFilter

_LOGO = None
def _ink():
    global _LOGO
    if _LOGO is None:
        la = np.asarray(Image.open(os.path.join(os.path.dirname(os.path.abspath(__file__)), '../../assets/logo-lockup.png')).convert('RGBA')).astype(float)
        ink = (la[..., 3] / 255) * (1 - la[..., :3].mean(axis=2) / 255)
        _LOGO = Image.fromarray((np.clip(ink * 1.15, 0, 1) * 255).astype('uint8'))
    return _LOGO

def erase_regions(a, erase, k, seed=11):
    rng = np.random.default_rng(seed)
    for x0, y0, x1, y1 in erase:
        x0, y0, x1, y1 = [int(round(v * k)) for v in (x0, y0, x1, y1)]
        reg = a[y0:y1, x0:x1].copy()
        border = np.concatenate([reg[0], reg[-1], reg[:, 0], reg[:, -1]])
        fill = reg.copy(); fill[1:-1, 1:-1] = border.mean(axis=0)
        for _ in range(600):
            fill[1:-1, 1:-1] = 0.25 * (fill[:-2, 1:-1] + fill[2:, 1:-1] + fill[1:-1, :-2] + fill[1:-1, 2:])
        ring = np.concatenate([a[y0-6:y0, x0:x1].reshape(-1, 3), a[y1:y1+6, x0:x1].reshape(-1, 3)])
        sd = float(np.clip(ring.std(axis=0).mean(), 1.0, 5.0))
        fill[1:-1, 1:-1] += rng.normal(0, sd * 0.6, fill[1:-1, 1:-1].shape)
        a[y0:y1, x0:x1] = fill

def print_logos(im, spots, erase=(), ink_mode='dark'):
    im = im.convert('RGB'); W, H = im.size; k = W / 1024.0
    a = np.asarray(im).astype(float)
    if erase:
        erase_regions(a, erase, k)
    ink = _ink()
    for sp in spots:
        cx, cy, rot, wpx = sp[:4]
        sy = sp[4] if len(sp) > 4 else 1.0
        sh = sp[5] if len(sp) > 5 else 0.0
        cx, cy = cx * k, cy * k
        w4 = int(wpx * k * 4); h4 = max(4, int(w4 * ink.height / ink.width * sy))
        m = ink.resize((w4, h4), Image.LANCZOS)
        if sh:
            pad = int(abs(sh) * h4) + 2
            m = m.transform((w4 + 2 * pad, h4), Image.AFFINE, (1, sh, -pad - sh * h4 / 2, 0, 1, 0), resample=Image.BICUBIC)
        m = m.rotate(rot, resample=Image.BICUBIC, expand=True)
        tw, th = max(1, round(m.width / 4)), max(1, round(m.height / 4))
        m = m.resize((tw, th), Image.LANCZOS).filter(ImageFilter.GaussianBlur(0.4 * k))
        x0, y0 = int(cx - tw / 2), int(cy - th / 2)
        if x0 < 0 or y0 < 0 or x0 + tw > W or y0 + th > H:
            continue
        mm = np.asarray(m).astype(float)[..., None] / 255
        reg = a[y0:y0+th, x0:x0+tw]
        if ink_mode == 'light':
            col = np.clip(reg.mean(axis=(0, 1)) * 0.25 + np.array([205, 200, 192]), 0, 245)
            alpha = mm * 0.78
        else:
            col = reg * 0.16
            lum = reg.mean(axis=2, keepdims=True); med = np.median(lum)
            cover = np.clip((lum - 0.55 * med) / (0.2 * med + 1e-6), 0, 1)
            alpha = mm * 0.9 * cover
        a[y0:y0+th, x0:x0+tw] = reg * (1 - alpha) + col * alpha
    return Image.fromarray(np.clip(a, 0, 255).astype('uint8'))
