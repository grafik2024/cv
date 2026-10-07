"""python3 zloz.py ../../assets/cfg [kolor ...]
Składa 130 podglądów: 13 scen (kolor = okazja) × 5 opraw × bez/z koroną.
Korona na arkuszach organzy jest przenoszona maską z jednego zdjęcia z koroną (ten sam kadr),
logo drukowane z assets/logo-lockup.png w stałych miejscach danej sceny."""
import sys, json, os
sys.path.insert(0, os.path.dirname(__file__))
from PIL import Image
from crown import crown_mask, apply
from lp import print_logos
from card import blank_card

# Katalog ze źródłowymi PNG (1024 px) pobranymi z Higgsfield po job_id z jobs.tsv:
#   $PODGLADY_SRC/gen3/{kolor}.png, gen3/v/{kolor}-{wariant}.png, gen2/ (czerwień z rundy 1).
# Źródeł nie trzymam w repo (≈200 MB); współrzędne logo i zawieszek są tutaj obok.
HERE = os.path.dirname(os.path.abspath(__file__))
S = os.environ.get('PODGLADY_SRC', HERE)
G3 = S + '/gen3'; G2 = S + '/gen2'
OUT = sys.argv[1]
only = sys.argv[2:] or None
spots = json.load(open(HERE + '/spots.json'))
tags = json.load(open(HERE + '/tags.json'))
TAG_FIX = {
    'bialy-brak': [642, 818, 25, 44], 'bialy-brak-korona': [644, 834, 28, 44],
    'roz-brak-korona': [580, 850, 20, 40],
    'kremowy-brak': [566, 815, 4, 52], 'kremowy-brak-korona': [567, 818, 4, 52],
}
tags.update({k: v for k, v in TAG_FIX.items() if v})
COLORS = ['czerwony', 'roz', 'pudrowy', 'morelowy', 'zolty', 'kremowy', 'bialy', 'zielony',
          'blekit', 'granat', 'jasfiolet', 'fiolet', 'czarny']
ORG = ['org-biala', 'org-czarna', 'org-rozowa', 'org-blekit']

def src(c, s):
    if c == 'czerwony':
        return {'org-biala': G2 + '/master.png', 'org-czarna': G2 + '/t-czarna.png',
                'org-rozowa': G2 + '/s-rozowa.png', 'org-blekit': G2 + '/s-blekit.png',
                'brak': G2 + '/s-brak.png', 'korona': G2 + '/k-org-biala.png',
                'brak-korona': G2 + '/k-brak.png'}[s]
    if s == 'org-biala': return G3 + '/%s.png' % c
    if s == 'korona': return G3 + '/v/%s-org-biala-korona.png' % c
    return G3 + '/v/%s-%s.png' % (c, s)

def save(im, name):
    im.resize((900, 900), Image.LANCZOS).save(OUT + '/preview-%s-900.webp' % name, quality=74, method=6)

for c in COLORS:
    if only and c not in only: continue
    sp = spots[c]['spots']; er = spots[c].get('erase', [])
    base = Image.open(src(c, 'org-biala')).convert('RGB')
    kor = Image.open(src(c, 'korona')).convert('RGB')
    mask = crown_mask(base, kor)
    for s in ORG:
        im = base if s == 'org-biala' else Image.open(src(c, s)).convert('RGB')
        ink = 'light' if s == 'org-czarna' else 'dark'
        save(print_logos(im, sp, er, ink), '%s-%s' % (c, s))
        save(print_logos(apply(im, kor, mask), sp, er, ink), '%s-%s-korona' % (c, s))
    for suf in ['brak', 'brak-korona']:
        im = Image.open(src(c, suf)).convert('RGB')
        if c == 'czerwony':
            im = blank_card(im, [(535, 627), (632, 687), (557, 807), (450, 735)])
            t = [543.5, 716, -32, 54]
        else:
            t = tags.get('%s-%s' % (c, suf))
        save(print_logos(im, [t] if t else []), '%s-%s' % (c, suf.replace('brak-korona', 'brak-korona')))
        if not t: print('BRAK LOGO NA ZAWIESZCE', c, suf)
    print('ok', c, int((mask > .5).sum()))
