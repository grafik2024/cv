"""grid.py src out [x0 y0 x1 y1] — kadr z siatką co 50 px (współrzędne 1024) do wyboru miejsc na logo."""
import sys
from PIL import Image, ImageDraw
im = Image.open(sys.argv[1]).convert('RGB')
k = im.width / 1024
box = [int(v) for v in sys.argv[3:7]] if len(sys.argv) > 6 else [0, 0, 1024, 1024]
d = ImageDraw.Draw(im)
for v in range(0, 1025, 50):
    c = (255, 0, 0) if v % 100 == 0 else (0, 160, 255)
    d.line([(v * k, 0), (v * k, im.height)], fill=c, width=1)
    d.line([(0, v * k), (im.width, v * k)], fill=c, width=1)
    if v % 100 == 0:
        for u in range(0, 1025, 100):
            d.text((v * k + 2, u * k + 2), f"{v},{u}", fill=(255, 255, 0))
cr = im.crop(tuple(int(b * k) for b in box))
s = 1000 / max(cr.size)
cr.resize((int(cr.width * s), int(cr.height * s))).save(sys.argv[2], quality=88)
