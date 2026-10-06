#!/usr/bin/env python3
"""Convert raw REST dumps into per-item clean Markdown files for the audit.
Usage: python3 tools/extract_content.py audit/raw audit/extracted"""
import json, os, re, sys, html
from html.parser import HTMLParser

RAW, OUT = sys.argv[1], sys.argv[2]

class MD(HTMLParser):
    """Very small HTML->Markdown-ish converter that keeps headings, lists, links, images, tables."""
    BLOCK = {"p","div","section","br","li","tr","h1","h2","h3","h4","h5","h6","ul","ol","table","figure","figcaption","blockquote"}
    def __init__(self):
        super().__init__(convert_charrefs=True)
        self.out, self.links, self.imgs, self.skip = [], [], [], 0
        self.href = None
    def handle_starttag(self, tag, attrs):
        a = dict(attrs)
        if tag in ("script","style","noscript","svg"): self.skip += 1; return
        if self.skip: return
        if tag in ("h1","h2","h3","h4","h5","h6"): self.out.append("\n\n" + "#"*int(tag[1]) + " ")
        elif tag == "li": self.out.append("\n- ")
        elif tag == "br": self.out.append("\n")
        elif tag in ("td","th"): self.out.append(" | ")
        elif tag in self.BLOCK: self.out.append("\n\n")
        if tag == "a" and a.get("href"):
            self.href = a["href"]; self.links.append(a["href"])
        if tag == "img":
            src = a.get("data-src") or a.get("src") or ""
            if src and not src.startswith("data:"):
                self.imgs.append((src, a.get("alt","")))
                self.out.append(f" ![{a.get('alt','')}]({src}) ")
        if tag in ("strong","b"): self.out.append("**")
    def handle_endtag(self, tag):
        if tag in ("script","style","noscript","svg"): self.skip = max(0, self.skip-1); return
        if self.skip: return
        if tag in ("strong","b"): self.out.append("**")
        if tag == "a" and self.href:
            self.out.append(f" <{self.href}>"); self.href = None
        if tag in self.BLOCK: self.out.append("\n")
    def handle_data(self, data):
        if self.skip: return
        self.out.append(re.sub(r"[ \t\r\n]+", " ", data))
    def text(self):
        t = "".join(self.out)
        t = re.sub(r"\*\*\s*\*\*", "", t)
        t = re.sub(r"[ \t]+\n", "\n", t)
        t = re.sub(r"\n{3,}", "\n\n", t)
        return t.strip()

def conv(h):
    p = MD(); p.feed(h or ""); return p.text(), p.links, p.imgs

def path_of(link): return link.replace("https://eurowet.pl", "") or "/"

def slugify(path):
    s = path.strip("/").replace("/", "__") or "home"
    return s[:180]

def yoast(item):
    y = item.get("yoast_head_json") or {}
    return {k: y.get(k) for k in ("title","description","canonical","robots","og_title","og_description","og_image") if y.get(k)}

def write(kind, item, extra=None):
    os.makedirs(os.path.join(OUT, kind), exist_ok=True)
    body, links, imgs = conv(item.get("content",{}).get("rendered",""))
    title = html.unescape(re.sub(r"<[^>]+>","", item.get("title",{}).get("rendered","")))
    path = path_of(item["link"])
    meta = {"id": item["id"], "type": kind, "title": title, "path": path, "date": item.get("date"),
            "modified": item.get("modified"), "parent": item.get("parent"), "status": item.get("status"),
            "featured_media": item.get("featured_media"), "yoast": yoast(item), "word_count": len(body.split())}
    if extra: meta.update(extra)
    internal = sorted({path_of(l) for l in links if "eurowet.pl" in l or l.startswith("/")})
    external = sorted({l for l in links if l.startswith("http") and "eurowet.pl" not in l})
    with open(os.path.join(OUT, kind, slugify(path) + ".md"), "w", encoding="utf-8") as f:
        f.write("```json\n" + json.dumps(meta, ensure_ascii=False, indent=1) + "\n```\n\n")
        f.write(f"# {title}\n\n{body}\n\n")
        f.write("## [audit] internal links\n" + "\n".join(internal) + "\n\n")
        f.write("## [audit] external links\n" + "\n".join(external) + "\n\n")
        f.write("## [audit] images\n" + "\n".join(f"{s} | alt={a}" for s,a in dict(imgs).items()) + "\n")
    return meta

index = []
cats = {c["id"]: c["slug"] for c in json.load(open(os.path.join(RAW,"categories.json")))}
tags = {t["id"]: t["name"] for t in json.load(open(os.path.join(RAW,"tags.json")))}
for p in json.load(open(os.path.join(RAW,"posts.json"))):
    index.append(write("posts", p, {"categories": [cats.get(c) for c in p["categories"]], "tags": [tags.get(t) for t in p["tags"]]}))
for p in json.load(open(os.path.join(RAW,"pages.json"))):
    index.append(write("pages", p))
pcat = {c["id"]: c["slug"] for c in json.load(open(os.path.join(RAW,"product_cat.json")))}
store = {s["id"]: s for s in json.load(open(os.path.join(RAW,"store_products.json")))}
for p in json.load(open(os.path.join(RAW,"wp_products.json"))):
    s = store.get(p["id"], {})
    extra = {"woo": {k: s.get(k) for k in ("sku","prices","short_description","categories","attributes","variations","type","is_in_stock","stock_availability","images","permalink","add_to_cart","has_options","low_stock_remaining","is_purchasable") if k in s},
             "product_cat": [pcat.get(c) for c in p.get("product_cat", [])]}
    if s.get("description"):
        p = dict(p); p["content"] = {"rendered": s["description"]}
    index.append(write("products", p, extra))
json.dump(index, open(os.path.join(OUT, "index.json"), "w", encoding="utf-8"), ensure_ascii=False, indent=1)
print(len(index), "items")
