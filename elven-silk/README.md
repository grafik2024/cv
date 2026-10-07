# Elven Silk — landing page

Strona sprzedażowa atelier bukietów z satynowych róż (Kalisz). Bez frameworków i bez build-stepu
w produkcji — wystarczy wrzucić `index.html` + `assets/` na hosting.

## Pliki

| Plik | Do czego |
|---|---|
| `page.html` | **Źródło.** Cała strona: CSS, HTML i JS w jednym pliku. Tu wprowadzasz zmiany. |
| `index.html` | Wynik `node build.js` — samodzielny dokument z `<head>`, meta OG i danymi strukturalnymi JSON-LD. To publikujesz. |
| `build.js` | Owija `page.html` w pełny dokument i dokleja SEO. Uruchom po każdej zmianie: `node build.js`. |
| `assets/` | Zdjęcia realizacji w WebP (400/640/1000 px), film tła hero, logo jako maski PNG z kanałem alfa. |
| `assets/cfg/` | Grafiki konfiguratora: 11 podglądów bukietu, 5 miniatur rozmiaru, 5 próbek opakowań, 10 miniatur dodatków, 11 próbek koloru satyny. |
| `tools/` | Skrypty generujące assety (wymagają `npm i sharp`). |

## Zanim opublikujesz — lista do uzupełnienia

> Pełna, aktualna lista z numerami linii i przeglądem prawnym: **`DO-ZROBIENIA.md`**.

Miejsca do wypełnienia są w `page.html` w obiekcie `BIZ` i w cennikach, a w treści oznaczone
żółtym tłem (klasa `.todo`). Kolejno:

1. **`BIZ`** — pełna nazwa firmy, kod pocztowy, NIP, REGON, telefon.
2. **`SIZES`, `COLORS`, `WRAPS`, `ADDONS`** — realne ceny. Wartości w repo są **przykładowe**.
3. **`SHIPPING`** — ceny są prawdziwe (cennik Furgonetka/InPost dla nadawcy indywidualnego,
   wrzesień 2026). Przy umowie biznesowej stawki są niższe — podmień.
4. **`FREE_FROM`** — próg darmowej dostawy (domyślnie 300 zł).
5. **`DRAFT_MODE = false`** — dopiero po punktach 1–4. Wyłącza żółty pasek „Tryb roboczy”.
6. **Dokumenty prawne** (obiekt `DOCS`) — uzupełnij dane w regulaminie, polityce prywatności,
   informacji GPSR i deklaracji dostępności, we **wszystkich trzech językach**. Tabelę cookies
   dostosuj do narzędzi, które faktycznie uruchomisz.

## Wersje językowe

Polski, angielski i ukraiński. Polskie teksty są **w HTML-u** (dobre dla SEO i działają bez JS),
a przy starcie zbiera je `harvestPL()` do `T.pl`. Tłumaczenia EN i UA siedzą w obiekcie `T`,
teksty generowane przez skrypt — w `PL_JS` i w kluczach `js.*`.

Żeby dodać lub zmienić napis: nadaj elementowi `data-t="klucz"` (albo `data-tl` dla `aria-label`
i `alt`, `data-tp` dla `placeholder`) i dopisz ten klucz do `T.en` oraz `T.uk`. Dane produktowe
(rozmiary, kolory, dodatki, dostawa, FAQ, galeria) mają pola `pl` / `en` / `uk` przy każdym wpisie.

Domyślnym językiem jest polski. Automatycznie przełączam tylko osoby z ukraińsko- lub
rosyjskojęzyczną przeglądarką; zapisany wybór (`es_lang`) zawsze wygrywa.

## Grafiki konfiguratora

Kafelki i podgląd to **wizualizacje AI** (GPT Image 2.5 i Google Nano Banana 2 w Higgsfield)
zrobione tak, żeby wyglądały jak zdjęcia właścicielki z telefonu: wzorcem produktu jest
prawdziwe zdjęcie bukietu, wzorem stylu — jej zdjęcia z profilu. Sposób zwijania róż,
wachlarz organzy, kokardki i korona odpowiadają realnym bukietom. **Logo nie jest
rysowane przez AI** — to nadruk prawdziwego pliku `assets/logo-lockup.png` na co drugim
arkuszu (albo na zawieszce w wersji bez organzy).

**To ważne prawnie i etycznie:** podgląd jest opisany na stronie jako „Wizualizacja”, a lede
sekcji mówi wprost, że gotowy bukiet szyty jest ręcznie i może się nieznacznie różnić.
Nie podmieniaj tego opisu na „zdjęcie produktu” — wprowadzanie w błąd co do wyglądu towaru to
nieuczciwa praktyka rynkowa. Prawdziwe fotografie są w galerii realizacji i tak są podpisane.

Rozmiar w podglądzie oddaje skala zdjęcia (`pvScale()` w `page.html`) plus pasek z rzeczywistą
liczbą róż — nie ma osobnego zdjęcia dla każdej kombinacji rozmiaru i koloru.

Podgląd zależy od **koloru róż, oprawy (organzy) i korony** — 130 plików
`preview-{kolor}-{oprawa}[-korona]-900.webp`. **Każdy kolor ma własną scenę i okazję w roku**
(np. róż — Walentynki nocą, biały — jarmark w śniegu), opisaną nad nazwą bukietu (`occ`
w `COLORS`). W obrębie koloru kadr jest ten sam, więc zmiana oprawy i korony nie przesuwa
bukietu. Pozostałe dodatki widać jako miniatury obok zdjęcia. Plakietka mówi
„Wizualizacja AI” — to wymóg art. 50 AI Act, nie usuwaj go.

Jak powstały i jak dorobić kolejne: `tools/podglady/README.md`. Chcesz podmienić grafiki na
własne zdjęcia? Zachowaj nazwy plików: `preview-{kolor}-{oprawa}[-korona]-900.webp`,
`size-{liczba}.webp`, `sleeve-{id}.webp`, `wrap-{id}.webp`, `addon-{id}.webp`,
`color-{kolor}.webp` — kod nie wymaga wtedy żadnej zmiany. Obrót 360°: `tools/SPIN.md`.

`tools/recolor-preview.js` został z poprzedniej wersji: przelicza odcień satyny na prawdziwym
zdjęciu, gdyby wolisz tę drogę zamiast generowania.

Zdjęcie w sekcji kontaktu (`assets/pudelko-premium-*.webp`) też jest wizualizacją — pokazuje
pudełko premium, które jest dodatkiem w konfiguratorze. Pod zdjęciem stoi podpis mówiący to
wprost. Jeśli zrobisz zdjęcie prawdziwego pudełka, podmień plik i skasuj słowo „Wizualizacja”
z podpisu (klucz `ct.figcap` w trzech językach).

## Film w tle nagłówka

Nagłówek jest na pełną szerokość, ciemny, z zapętlonym filmem w tle. Menu leży na nim
i dopiero po przewinięciu poza hero robi się nieprzezroczyste.

| Plik | Co to |
|---|---|
| `assets/hero-atelier.webm` | VP9, ~535 kB — wersja podstawowa dla nowych przeglądarek. |
| `assets/hero-atelier.mp4` | H.264, ~840 kB — zapas dla Safari i starszych. |
| `assets/hero-poster-1280.webp` | Plakat, ~51 kB. Widać go zanim film ruszy i zamiast filmu tam, gdzie film się nie ładuje. |

**Film rusza sam** — również na komputerach z włączonym systemowym „ogranicz animacje".
To świadoma decyzja właścicielki: domyślnie taki system wyłącza autoodtwarzanie, a strona
ma go mimo to odtwarzać. Wyjściem awaryjnym jest **przycisk pauzy** w prawym dolnym rogu
nagłówka — wymaga go WCAG 2.2.2, bo pętla trwa dłużej niż 5 sekund. Nie usuwaj go.

Jedyne, co wstrzymuje pobranie filmu, to cudzy transfer: tryb oszczędzania danych
i łącze 2G. Wtedy zostaje sam plakat, a film odpala się z przycisku.

Gdyby przeglądarka mimo wszystko zablokowała start, skrypt próbuje jeszcze raz przy
pierwszym kliknięciu lub naciśnięciu klawisza — po geście użytkownika polityka
autoodtwarzania już nie obowiązuje.

### Skąd ten film i czego nie cofać

Materiałem wyjściowym był klip dostarczony przez atelier. **Na wstążce w całym klipie widniał
napis „Dior”** — cudzy zarejestrowany znak towarowy. Na stronie firmowej sprzedającej bukiety
to realne ryzyko: sugeruje powiązanie z obcą marką i naraża atelier na roszczenia. Dlatego
z klipu została wyjęta klatka, wyczyszczona z napisów, i na jej podstawie powstał nowy film —
ta sama kompozycja, świece, gwiazdy i tiara, ale wstążka jest gładka i pusta.

**Nie podmieniaj tego pliku z powrotem na wersję z widocznym logo Diora.** Ta sama uwaga
dotyczy zdjęć w galerii — patrz „Uwagi do zdjęć” na końcu.

Film jest **wizualizacją**, nie nagraniem prawdziwego bukietu, i tak jest opisany plakietką
w rogu nagłówka oraz w stopce. Nie kasuj tych opisów.

### Podmiana filmu

Wrzuć swój materiał jako `nowy.mp4` i przelicz go tak (potrzebny `ffmpeg`):

```bash
# 1. pętla bez szwu: ostatnia sekunda przenika w pierwszą
DUR=$(ffprobe -v error -show_entries format=duration -of csv=p=0 nowy.mp4)
OFF=$(python3 -c "print($DUR-2)")
ffmpeg -i nowy.mp4 -filter_complex \
  "[0]split[body][pre];[pre]trim=duration=1,format=yuva420p,fade=d=1:alpha=1,setpts=PTS-STARTPTS+($OFF/TB)[jt];\
   [body]trim=start=1,setpts=PTS-STARTPTS[main];[main][jt]overlay=eof_action=pass,format=yuv420p" \
  -an loop.mp4

# 2. dwa formaty + plakat
ffmpeg -i loop.mp4 -c:v libx264 -crf 28 -preset slow -pix_fmt yuv420p -movflags +faststart -an \
  assets/hero-atelier.mp4
ffmpeg -i loop.mp4 -c:v libvpx-vp9 -crf 36 -b:v 0 -row-mt 1 -pix_fmt yuv420p -an \
  assets/hero-atelier.webm
ffmpeg -i loop.mp4 -frames:v 1 -vf "scale=1280:-2:flags=lanczos" -c:v libwebp -quality 62 \
  assets/hero-poster-1280.webp
```

Nazwy plików zostaw bez zmian — kod ich szuka po nazwie.

## Logo

Logo firmy (monogram ES) jest wyciągnięte ze zdjęcia profilowego, wyprogowane i zapisane
z kanałem alfa. W CSS działa jako `mask-image` na tle `currentColor`, więc samo dopasowuje
się do motywu jasnego i ciemnego.

**Logo siedzi w samym pliku, jako `data:` URI** — nie jako osobny obrazek. Powód: maska CSS
wczytywana z osobnego pliku jest blokowana przy otwarciu strony bezpośrednio z dysku
(protokół `file://`) i logo wtedy po prostu znika. Data URI to ten sam dokument, więc działa
i z dysku, i z serwera. Kosztuje to ok. 33 kB w `index.html` — świadoma zamiana wagi na to,
żeby logo było widać zawsze.

`assets/logo-mark.png` i `assets/logo-lockup.png` zostają w repo: pierwszy służy za favikonę,
oba są źródłem, gdyby trzeba było przeliczyć maski od nowa (`ffmpeg -i logo-mark.png
-c:v libwebp -lossless 1 mark.webp`, potem base64 do CSS).

Skrypt: `tools/extract-logo.js` (potrzebuje `logo-src.jpg` w katalogu `tools/`).

## Zgody na cookies

Baner zapisuje wybór w `localStorage` pod kluczem `es_consent` razem ze znacznikiem czasu
(dowód zgody) i wersją `CONSENT_VERSION`. Podbicie wersji wymusza ponowne pytanie.

Google Consent Mode v2 startuje z wszystkimi sygnałami `denied`. Gdy podepniesz GA4 lub
Meta Pixel:

- przenieś blok `gtag("consent","default",…)` do osobnego `<script>` na samą górę `<head>`
  (musi wykonać się przed tagiem), a w `build.js` dopisz go do szablonu;
- skrypty ładuj w funkcji `applyConsent()` — jest tam przygotowane miejsce z komentarzem.

## Zamówienia i formularz B2B

Oba formularze wysyłają dane na skrzynkę atelier przez **FormSubmit** — adres endpointu siedzi
w `ORDER_ENDPOINT` na początku skryptu.

> **Jednorazowa aktywacja.** Po pierwszym wysłanym formularzu FormSubmit przyśle na
> `elvensilk@wp.pl` maila z linkiem potwierdzającym. Trzeba w niego kliknąć — dopóki tego nie
> zrobisz, wiadomości nie będą dochodzić.

Jeśli żądanie sieciowe nie przejdzie (np. w podglądzie Artifacta, gdzie sandbox blokuje ruch na
zewnątrz), skrypt automatycznie otwiera klienta poczty z gotową treścią. Zielony komunikat
o wysłaniu pokazuje się **wyłącznie** po faktycznie udanym żądaniu.

Chcesz inny kanał? Podmień `ORDER_ENDPOINT` na własny endpoint przyjmujący JSON-a POST-em
(Formspree, Web3Forms, własny skrypt) albo wpisz `""`, żeby zostało samo `mailto:`.

Stan konfiguratora żyje w przeglądarce klientki (`localStorage`, klucz `es_cfg`). Podpięcie
płatności (Przelewy24, Stripe) sprowadza się do podmiany funkcji `postForm()`.

## Sekcja B2B

`#wspolpraca` — oferta dla kwiaciarni, butików i sklepów z prezentami plus formularz zgłoszenia
(firma, NIP, osoba kontaktowa, kontakt, rodzaj działalności, wolumen, wiadomość). Treść czterech
punktów oferty **jest projektem** — zweryfikuj progi rabatowe i warunki, zanim opublikujesz.

## Mapa dojazdu

W sekcji kontaktu jest osadzona mapa Google. Pod nią leży zaprojektowana karta zastępcza
z adresem i przyciskiem „Wyznacz trasę” — pokazuje się, gdy iframe nie może się załadować
(tak dzieje się w podglądzie Artifacta). Na Twojej domenie zobaczysz normalną mapę.

## Uwagi do zdjęć

Zdjęcia pochodzą z profilu [@elvensilkofficial](https://www.instagram.com/elvensilkofficial/),
zostały przycięte, wyrównane kolorystycznie i przekonwertowane do WebP. Trzy kadry pominięto
świadomie: jeden z widoczną tablicą rejestracyjną i dwa, na których dominowały wstążki z cudzym
logo. Przed kampanią reklamową warto dorobić serię zdjęć na jednolitym tle i z własnymi
wstążkami Elven Silk — poprawi to też jakość przeliczania kolorów w konfiguratorze.
