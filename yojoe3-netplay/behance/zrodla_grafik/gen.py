#!/usr/bin/env python3
"""Behance slides for the Yo! Joe! 3 project: writes slides/<lang>/<name>.html."""
import os, sys, json

B = os.path.dirname(os.path.abspath(__file__))
A = 'file://' + B + '/assets/'

CSS = """
:root{--bg:#0c0d1b;--panel:#14162b;--panel2:#1b1e3a;--line:#2b2f57;--ink:#f0f1ff;--muted:#a3a8d4;
--pink:#ff3d7f;--yellow:#ffd84a;--cyan:#41d9ff;--green:#7cff6b;--violet:#8f6bff;}
*{box-sizing:border-box}
html,body{margin:0;background:var(--bg);color:var(--ink);font-family:'Inter',sans-serif;-webkit-font-smoothing:antialiased}
.slide{width:1400px;padding:76px 80px 84px;position:relative;overflow:hidden}
.stripe{position:absolute;left:0;right:0;top:0;height:8px;background:linear-gradient(90deg,#ff1d3d,#ff2ad4 20%,#7a3cff 40%,#22d3ff 60%,#3dff7a 80%,#f7ff3c)}
.px{font-family:'Unifont',monospace;font-size:24px;line-height:1;letter-spacing:.06em;text-transform:uppercase}
.kicker{color:var(--yellow)}
.num{color:var(--pink)}
h1{font-family:'Inter Display','Inter',sans-serif;font-weight:800;font-size:64px;line-height:1.02;letter-spacing:-.02em;margin:14px 0 0}
h2{font-family:'Inter Display','Inter',sans-serif;font-weight:700;font-size:30px;line-height:1.15;margin:0}
.lead{font-size:24px;line-height:1.5;color:var(--muted);margin:22px 0 0;max-width:980px}
.lead b{color:var(--ink);font-weight:600}
.shot{display:block;width:100%;height:auto;image-rendering:pixelated;border-radius:6px;
box-shadow:0 0 0 1px var(--line),0 20px 44px rgba(0,0,0,.5)}
.smooth{image-rendering:auto}
.cap{font-family:'Unifont',monospace;font-size:16px;line-height:1.25;color:var(--muted);margin-top:12px;text-transform:uppercase;letter-spacing:.05em}
.cap b{color:var(--yellow);font-weight:400}
.grid{display:grid;gap:28px}
.card{background:var(--panel);border:1px solid var(--line);border-radius:14px;padding:28px}
.muted{color:var(--muted)}
.tag{display:inline-block;font-family:'Unifont',monospace;font-size:16px;text-transform:uppercase;letter-spacing:.06em;
padding:6px 10px;border-radius:6px;background:var(--panel2);border:1px solid var(--line);color:var(--ink)}
ul.clean{list-style:none;padding:0;margin:0}
ul.clean li{position:relative;padding-left:30px;font-size:21px;line-height:1.45;margin:0 0 14px;color:var(--ink)}
ul.clean li:before{content:"";position:absolute;left:4px;top:11px;width:10px;height:10px;background:var(--cyan);image-rendering:pixelated}
ul.clean li span{color:var(--muted)}
.footer{margin-top:48px;display:flex;justify-content:space-between;align-items:center;color:var(--muted)}
"""

def page(body, w=1400, extra=''):
    return f"""<!doctype html><html><head><meta charset="utf-8"><style>{CSS}{extra}</style></head>
<body><div class="slide" style="width:{w}px"><div class="stripe"></div>{body}</div></body></html>"""

def img(name, cls='shot', style=''):
    return f'<img class="{cls}" src="{A}{name}.png" style="{style}">'

def shot(name, cap, cls='shot'):
    return f'<figure style="margin:0">{img(name, cls)}<figcaption class="cap">{cap}</figcaption></figure>'

T = json.load(open(os.path.join(B, 'texts.json'), encoding='utf-8'))


def floppy(letter, color):
    return f'''<svg width="54" height="58" viewBox="0 0 27 29" shape-rendering="crispEdges">
<rect x="0" y="0" width="27" height="29" fill="{color}"/><rect x="6" y="0" width="14" height="9" fill="#d7dbef"/>
<rect x="15" y="1" width="3" height="7" fill="#20233f"/><rect x="4" y="14" width="19" height="15" fill="#f4f5ff"/>
<text x="13.5" y="26" font-family="Unifont" font-size="12" text-anchor="middle" fill="#20233f">{letter}</text></svg>'''


def s_cover(t):
    body = f"""
<div style="position:absolute;left:0;top:8px;width:1616px;height:960px;overflow:hidden">
  <img src="{A}hero.png" style="width:1616px;height:auto;image-rendering:pixelated;display:block">
  <div style="position:absolute;inset:0;background:linear-gradient(180deg,rgba(12,13,27,0) 45%,rgba(12,13,27,.85) 80%,#0c0d1b 100%)"></div>
</div>
<div style="position:absolute;left:96px;right:96px;bottom:92px">
  <div class="px kicker" style="font-size:32px">{t['cover_kicker']}</div>
  <div style="font-family:'Inter Display';font-weight:900;font-size:176px;line-height:.9;letter-spacing:-.03em;margin-top:18px;
     text-shadow:6px 6px 0 #ff2a6d, 12px 12px 0 rgba(0,0,0,.35)">YO! JOE! 3</div>
  <div style="font-family:'Inter Display';font-weight:700;font-size:56px;line-height:1.1;margin-top:34px">{t['cover_sub']}</div>
  <div style="display:flex;gap:14px;margin-top:30px">{''.join(f'<span class="tag" style="font-size:22px;padding:9px 14px">{x}</span>' for x in t['cover_tags'])}</div>
</div>"""
    extra = ".slide{height:1264px;padding:0}"
    return page(body, 1616, extra)


def s_title(t):
    body = f"""
<div style="display:grid;grid-template-columns:1fr 600px;gap:56px;align-items:center">
 <div>
  <div class="px kicker">{t['title_kicker']}</div>
  <h1>{t['title_h']}</h1>
  <p class="lead">{t['title_lead']}</p>
  <div class="grid" style="grid-template-columns:1fr 1fr;margin-top:40px;gap:20px">
   <div class="card"><div class="px num">01</div><h2 style="margin-top:12px">{t['title_c1h']}</h2><p class="muted" style="font-size:19px;line-height:1.45;margin:10px 0 0">{t['title_c1']}</p></div>
   <div class="card"><div class="px num">02</div><h2 style="margin-top:12px">{t['title_c2h']}</h2><p class="muted" style="font-size:19px;line-height:1.45;margin:10px 0 0">{t['title_c2']}</p></div>
  </div>
 </div>
 <div>{shot('loading', t['title_cap'])}</div>
</div>"""
    return page(body)


def s_editions(t):
    colors = ['#ff3d7f', '#41d9ff', '#ffd84a', '#7cff6b']
    cards = ''
    for i, (h, d) in enumerate(t['ed_items']):
        cards += f"""<div class="card" style="border-top:4px solid {colors[i]}">
<div style="display:flex;gap:10px">{floppy('A', colors[i])}{floppy('B', colors[i])}</div>
<div class="px" style="color:{colors[i]};margin-top:22px">{t['ed_label']} {i+1}</div>
<h2 style="margin-top:10px">{h}</h2><p class="muted" style="font-size:19px;line-height:1.45;margin:10px 0 0">{d}</p></div>"""
    worlds = ''
    for w in range(1, 13):
        c = '#2c3a6e' if w <= 6 else '#ff3d7f'
        worlds += f'<div style="background:{c};height:64px;border-radius:6px;display:flex;align-items:center;justify-content:center;font-family:Unifont;font-size:32px">{w}</div>'
    body = f"""
<div class="px kicker">{t['ed_kicker']}</div>
<h1>{t['ed_h']}</h1>
<p class="lead">{t['ed_lead']}</p>
<div class="grid" style="grid-template-columns:repeat(4,1fr);margin-top:44px;gap:20px">{cards}</div>
<div style="margin-top:44px">
 <div class="grid" style="grid-template-columns:repeat(12,1fr);gap:8px">{worlds}</div>
 <div style="display:grid;grid-template-columns:1fr 1fr;margin-top:12px" class="cap">
  <div>{t['ed_w16']}</div><div style="color:var(--pink)">{t['ed_w712']}</div></div>
</div>"""
    return page(body)


def s_maps(t):
    cells = ''
    for i, n in enumerate(t['maps_names']):
        cells += f'<figure style="margin:0">{img("map%d" % (i + 7), "shot smooth")}<figcaption class="cap"><b>{t["maps_world"]} {i+7}</b> &nbsp;{n}</figcaption></figure>'
    body = f"""
<div style="display:flex;justify-content:space-between;align-items:flex-end;gap:40px">
 <div><div class="px kicker">{t['maps_kicker']}</div><h1>{t['maps_h']}</h1></div>
 <div style="display:flex;gap:10px;flex-wrap:wrap;justify-content:flex-end;max-width:560px">{''.join(f'<span class="tag">{x}</span>' for x in t['maps_feats'])}</div>
</div>
<p class="lead">{t['maps_lead']}</p>
<div class="grid" style="grid-template-columns:repeat(3,1fr);margin-top:40px;gap:28px 24px">{cells}</div>"""
    return page(body)


def s_ingame(t):
    names = ['trainer', 'title', 'options', 'coop_both', 'hs_joe', 'bonus']
    cells = ''.join(shot(n, c) for n, c in zip(names, t['ig_caps']))
    body = f"""
<div class="px kicker">{t['ig_kicker']}</div>
<h1>{t['ig_h']}</h1>
<p class="lead">{t['ig_lead']}</p>
<div class="grid" style="grid-template-columns:repeat(3,1fr);margin-top:40px;gap:30px 24px">{cells}</div>"""
    return page(body)


def s_worlds(t):
    frames = [6550, 9950, 17100, 23150, 27900, 34900, 38050, 40950]
    cells = ''
    for f, c in zip(frames, t['wo_caps']):
        cells += shot('w%d' % f, f'<b>{t["wo_frame"]} {f:,}</b>'.replace(',', '&#8239;') + f' &nbsp;{c}')
    body = f"""
<div class="px kicker">{t['wo_kicker']}</div>
<h1>{t['wo_h']}</h1>
<p class="lead">{t['wo_lead']}</p>
<div class="grid" style="grid-template-columns:repeat(4,1fr);margin-top:40px;gap:28px 20px">{cells}</div>"""
    return page(body)


def screen_svg(x, y, label, sub, color, imgname):
    return f'''<g transform="translate({x},{y})">
<rect x="0" y="0" width="360" height="300" rx="16" fill="#14162b" stroke="#2b2f57" stroke-width="2"/>
<image href="{A}{imgname}.png" x="20" y="20" width="320" height="225" style="image-rendering:pixelated"/>
<text x="20" y="276" font-family="Unifont" font-size="24" fill="{color}">{label}</text>
<text x="340" y="276" font-family="Inter" font-size="17" fill="#a3a8d4" text-anchor="end">{sub}</text></g>'''


def s_how(t):
    frames = ''
    for i in range(8):
        x = 120 + i * 140
        hl = i == 0 or i == 3
        frames += f'<rect x="{x}" y="0" width="124" height="54" rx="8" fill="{"#ff3d7f" if i==3 else ("#41d9ff" if i==0 else "#1b1e3a")}" stroke="#2b2f57"/>'
        frames += f'<text x="{x+62}" y="35" font-family="Unifont" font-size="24" text-anchor="middle" fill="{"#0c0d1b" if hl else "#a3a8d4"}">{"N" if i==0 else "N+"+str(i)}</text>'
    svg = f'''<svg width="1240" height="560" viewBox="0 0 1240 560">
<defs><marker id="ar" viewBox="0 0 10 10" refX="9" refY="5" markerWidth="8" markerHeight="8" orient="auto"><path d="M0,0 L10,5 L0,10 z" fill="#ffd84a"/></marker></defs>
{screen_svg(0, 0, t['how_host'], t['how_host_sub'], '#41d9ff', 'how_h')}
{screen_svg(880, 0, t['how_p2'], t['how_p2_sub'], '#ff3d7f', 'how_c')}
<line x1="380" y1="110" x2="860" y2="110" stroke="#ffd84a" stroke-width="4" marker-end="url(#ar)"/>
<line x1="860" y1="170" x2="380" y2="170" stroke="#ffd84a" stroke-width="4" marker-end="url(#ar)"/>
<text x="620" y="92" font-family="Unifont" font-size="24" text-anchor="middle" fill="#ffd84a">{t['how_input']}</text>
<text x="620" y="214" font-family="Inter" font-size="19" text-anchor="middle" fill="#a3a8d4">{t['how_input_sub']}</text>
<text x="620" y="258" font-family="Inter" font-size="19" text-anchor="middle" fill="#7cff6b">{t['how_hash']}</text>
<g transform="translate(0,360)">
<text x="0" y="35" font-family="Unifont" font-size="24" fill="#a3a8d4">{t['how_frames']}</text>
{frames}
<path d="M182,64 C182,130 602,130 602,64" fill="none" stroke="#ffd84a" stroke-width="3" stroke-dasharray="8 6" marker-end="url(#ar)"/>
<text x="392" y="150" font-family="Inter" font-size="19" text-anchor="middle" fill="#f0f1ff">{t['how_arc']}</text>
</g></svg>'''
    pts = ''.join(f'<div class="card"><div class="px num">0{i+1}</div><p style="font-size:20px;line-height:1.45;margin:12px 0 0">{p}</p></div>' for i, p in enumerate(t['how_points']))
    body = f"""
<div class="px kicker">{t['how_kicker']}</div>
<h1>{t['how_h']}</h1>
<p class="lead">{t['how_lead']}</p>
<div style="margin-top:44px">{svg}</div>
<div class="grid" style="grid-template-columns:repeat(4,1fr);gap:20px;margin-top:8px">{pts}</div>"""
    return page(body)


def s_gui(t):
    items = ''.join(f'<li>{a} <span>{b}</span></li>' for a, b in t['gui_items'])
    body = f"""
<div class="px kicker">{t['gui_kicker']}</div>
<h1>{t['gui_h']}</h1>
<div style="display:grid;grid-template-columns:792px 1fr;gap:52px;margin-top:44px;align-items:start">
 <figure style="margin:0">
  <div style="border-radius:10px;overflow:hidden;box-shadow:0 0 0 1px #2b2f57,0 24px 60px rgba(0,0,0,.55)">
   <div style="background:#1b1e3a;height:34px;display:flex;align-items:center;gap:8px;padding:0 14px">
    <i style="width:12px;height:12px;border-radius:50%;background:#ff3d7f;display:block"></i><i style="width:12px;height:12px;border-radius:50%;background:#ffd84a;display:block"></i><i style="width:12px;height:12px;border-radius:50%;background:#7cff6b;display:block"></i>
    <span style="font-size:14px;color:#a3a8d4;margin-left:10px">WinUAE Properties</span></div>
   <img src="{A}gui.png" style="display:block;width:792px">
  </div>
  <figcaption class="cap">{t['gui_cap']}</figcaption>
 </figure>
 <div><ul class="clean">{items}</ul>
  <div class="card" style="margin-top:22px;padding:22px"><div class="px kicker" style="font-size:16px">{t['gui_title_k']}</div>
  <div style="font-family:Unifont;font-size:16px;line-height:1.5;margin-top:10px;color:#f0f1ff">{t['gui_title1']}</div>
  <div style="font-family:Unifont;font-size:16px;line-height:1.5;margin-top:6px;color:#7cff6b">{t['gui_title2']}</div></div>
 </div>
</div>"""
    return page(body)


def s_disk(t):
    seq = ['disk_pre', 'disk_insert', 'disk_after']
    figs = ''
    for i, (n, c) in enumerate(zip(seq, t['disk_caps'])):
        figs += shot(n, c)
        if i < 2:
            figs += '<div style="display:flex;align-items:center;justify-content:center;font-family:Unifont;font-size:48px;color:#ffd84a;padding-bottom:30px">&rarr;</div>'
    steps = ''
    cols = ['#41d9ff', '#a3a8d4', '#ffd84a', '#7cff6b', '#ff3d7f']
    for i, (h, d) in enumerate(t['disk_steps']):
        steps += f'<div class="card" style="padding:22px;border-top:4px solid {cols[i]}"><div class="px" style="color:{cols[i]};font-size:16px">{i+1}</div><div style="font-weight:600;font-size:19px;margin-top:10px">{h}</div><div class="muted" style="font-size:16px;line-height:1.4;margin-top:6px">{d}</div></div>'
    body = f"""
<div class="px kicker">{t['disk_kicker']}</div>
<h1>{t['disk_h']}</h1>
<p class="lead">{t['disk_lead']}</p>
<div style="display:grid;grid-template-columns:1fr 70px 1fr 70px 1fr;margin-top:40px">{figs}</div>
<div class="grid" style="grid-template-columns:repeat(5,1fr);gap:14px;margin-top:40px">{steps}</div>"""
    return page(body)


def s_debug(t):
    steps = ''
    for i, (fr, h, d) in enumerate(t['dbg_steps']):
        c = '#7cff6b' if i == 3 else '#ff3d7f'
        steps += f"""<div style="display:grid;grid-template-columns:190px 1fr;gap:20px;align-items:start;margin-bottom:22px">
<div class="px" style="color:{c};font-size:24px;padding-top:6px">{fr}</div>
<div class="card" style="padding:20px 24px;border-left:4px solid {c}"><div style="font-weight:600;font-size:21px">{h}</div><div class="muted" style="font-size:18px;line-height:1.45;margin-top:6px">{d}</div></div></div>"""
    trace = """<span style="color:#a3a8d4">           PC       OP   D0       D1       D2</span>
<span style="color:#a3a8d4">           00fa3458 202a 00a8a138 00000010 00a8811e</span>  <span style="color:#ffd84a">MOVE.L ($10,A2),D0</span>
<span style="color:#41d9ff">HOST   </span>    00fa345c 5380 <span style="background:#ff3d7f;color:#0c0d1b">0000000b</span> 00000010 00a8811e
<span style="color:#ff3d7f">GRACZ 2</span>    00fa345c 5380 <span style="background:#7cff6b;color:#0c0d1b">00000006</span> 00000010 00a8811e"""
    trace = trace.replace('GRACZ 2', t['dbg_p2'])
    body = f"""
<div class="px kicker">{t['dbg_kicker']}</div>
<h1>{t['dbg_h']}</h1>
<p class="lead">{t['dbg_lead']}</p>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:48px;margin-top:44px;align-items:start">
 <div>{steps}</div>
 <div>
  <div style="background:#07080f;border:1px solid #2b2f57;border-radius:12px;padding:22px 24px;box-shadow:0 20px 44px rgba(0,0,0,.5)">
   <div class="px" style="color:#a3a8d4;font-size:16px;margin-bottom:14px">{t['dbg_trace_t']}</div>
   <pre style="margin:0;font-family:Unifont;font-size:16px;line-height:1.6;color:#f0f1ff;white-space:pre">{trace}</pre>
  </div>
  <p class="muted" style="font-size:19px;line-height:1.5;margin-top:22px">{t['dbg_trace_d']}</p>
  <div class="card" style="margin-top:22px"><div class="px kicker" style="font-size:16px">{t['dbg_more_t']}</div>
   <ul class="clean" style="margin-top:14px">{''.join(f'<li style="font-size:18px">{x}</li>' for x in t['dbg_more'])}</ul></div>
 </div>
</div>"""
    return page(body)


def s_tests(t):
    tiles = ''
    cols = ['#7cff6b', '#41d9ff', '#ffd84a', '#ff3d7f']
    for i, (big, h, d) in enumerate(t['test_tiles']):
        tiles += f'<div class="card" style="padding:26px"><div style="font-family:Inter Display;font-weight:800;font-size:54px;line-height:1;white-space:nowrap;color:{cols[i]}">{big}</div><div style="font-weight:600;font-size:20px;margin-top:14px">{h}</div><div class="muted" style="font-size:17px;line-height:1.45;margin-top:6px">{d}</div></div>'
    body = f"""
<div class="px kicker">{t['test_kicker']}</div>
<h1>{t['test_h']}</h1>
<p class="lead">{t['test_lead']}</p>
<div class="grid" style="grid-template-columns:repeat(4,1fr);gap:20px;margin-top:44px">{tiles}</div>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-top:44px">
 {shot('test_h', t['test_cap_h'])}{shot('test_c', t['test_cap_c'])}
</div>
<div class="card" style="margin-top:28px;display:flex;align-items:center;gap:22px;padding:22px 28px">
 <div style="font-family:Unifont;font-size:32px;color:#7cff6b">=</div>
 <div style="font-size:20px;line-height:1.45">{t['test_same']}</div></div>"""
    return page(body)


def s_finale(t):
    names = ['ending', 'hs_joe', 'hs_nat', 'newgame2']
    cells = ''.join(shot(n, c) for n, c in zip(names, t['fin_caps']))
    body = f"""
<div class="px kicker">{t['fin_kicker']}</div>
<h1>{t['fin_h']}</h1>
<p class="lead">{t['fin_lead']}</p>
<div class="grid" style="grid-template-columns:1fr 1fr;gap:30px 24px;margin-top:40px">{cells}</div>
<div class="footer" style="border-top:1px solid #2b2f57;padding-top:28px;font-size:17px;line-height:1.5">
 <div style="max-width:900px">{t['fin_credits']}</div>
 <div class="px" style="color:#ff3d7f">YO! JOE! 3</div></div>"""
    return page(body)


SLIDES = [('00', s_cover), ('01', s_title), ('02', s_editions), ('03', s_maps), ('04', s_worlds), ('05', s_ingame),
          ('06', s_how), ('07', s_gui), ('08', s_disk), ('09', s_debug), ('10', s_tests), ('11', s_finale)]

for lang in T:
    t = T[lang]
    d = os.path.join(B, 'slides', lang)
    os.makedirs(d, exist_ok=True)
    for (num, fn), name in zip(SLIDES, t['files']):
        with open(os.path.join(d, f'{num}_{name}.html'), 'w', encoding='utf-8') as f:
            f.write(fn(t))
print('ok')
