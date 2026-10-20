# Źródła grafik Behance

Plansze są stronami HTML renderowanymi przez Chromium (Playwright).

```
python3 gen.py                       # texts.json -> slides/pl, slides/en (HTML)
NODE_PATH=$(npm root -g) node render.js   # slides -> out/pl, out/en (PNG)
```

* `texts.json` – wszystkie napisy na planszach (PL i EN).
* `assets/` – zrzuty z sesji netplay (kadr Amigi 640×450), mapy światów 7–12
  wycięte z `podglad_swiaty_7_12.png`, okno ustawień WinUAE.
* Czcionki systemowe: Inter, Inter Display, Unifont.
