# Elven Silk — brief przekazania projektu

Dokument dla **nowej rozmowy z Claude**. Wklej treść sekcji „Prompt startowy" na dole,
a asystent od razu będzie wiedział, gdzie jest projekt i czego nie ruszać.

**Zasada numer jeden: wszystkie grafiki są już wygenerowane i leżą w `assets/`.
Nie generuj ich ponownie — to jedyna rzecz w tym projekcie, która kosztuje kredyty.**

> **Wyjątki (za zgodą właściciela):** ujęcie obrotowe 360° dla czerwieni
> (`assets/cfg/spin-czerwony-00..23.webp`) oraz sesja z 7 października 2026, w której
> na wyraźne polecenie powstał komplet podglądów (130 plików: każdy kolor we własnej scenie,
> 5 opraw, z koroną i bez) i brakujące kafelki — szczegóły w „Sesja z 7 października —
> grafiki z Higgsfield” i w `tools/podglady/README.md`. **Obrotów 360°
> dla kolejnych zestawień nie generuj bez osobnej zgody** — procedura i koszt w `tools/SPIN.md`.
>
> **Lista rzeczy do zrobienia po stronie właścicielki i przegląd prawny: `DO-ZROBIENIA.md`.**

---

## Czym jest ten projekt

Jednostronicowa strona sprzedażowa atelier **Elven Silk** (Kalisz) — ręcznie zwijane bukiety
z satynowych róż. Bez frameworków, bez build-stepu w produkcji, bez backendu.

| Element | Stan |
|---|---|
| Landing z konfiguratorem bukietu | gotowe |
| Wersje językowe PL / EN / UA | gotowe, komplet kluczy |
| Dokumenty prawne (6 sztuk × 3 języki) | gotowe, do uzupełnienia danych |
| Wysyłka zamówień i zgłoszeń B2B na maila | gotowe, wymaga aktywacji FormSubmit |
| Grafiki konfiguratora | gotowe: 130 podglądów „jak z telefonu” — każdy kolor w innej scenie i okazji w roku, 5 opraw, z koroną i bez, nadruk prawdziwego logo; kafelki zestawów, kolorów, organzy i dodatków |
| Obrót 360° bukietu | kod gotowy na każde zestawienie kolor × oprawa (± korona); stary obrót czerwieni wyłączony, nowe klatki czekają na zgodę |
| Poradnik + pliki dla wyszukiwarek AI | gotowe, 4 artykuły × 3 języki |
| Film w tle nagłówka + plakat | gotowe, wyczyszczony z cudzego logo |
| Zdjęcie pudełka w kontakcie | gotowe, opisane jako wizualizacja |
| Prawdziwe zdjęcia z Instagrama | gotowe, 18 kadrów |
| Ceny bukietów | **przykładowe, do podmiany** |
| Dane rejestrowe firmy | **do uzupełnienia** |

---

## Mapa plików

```
elven-silk/
├── page.html          ŹRÓDŁO — cała strona: CSS + HTML + JS w jednym pliku. Tu edytujesz.
├── index.html         WYNIK `node build.js`. Nie edytuj ręcznie — nadpisze się.
├── build.js           Owija page.html w dokument + meta OG + JSON-LD. Czyta z page.html
│                     tablice FAQ i SIZES, więc dane strukturalne i ceny nie mogą
│                     rozjechać się z treścią. Generuje też trzy pliki niżej.
├── robots.txt         WYNIK `node build.js`. Nie edytuj ręcznie.
├── llms.txt           WYNIK `node build.js`. Nie edytuj ręcznie.
├── sitemap.xml        WYNIK `node build.js`. Nie edytuj ręcznie.
├── README.md          Instrukcja wdrożeniowa dla właścicielki.
├── HANDOFF.md         Ten plik.
├── assets/            18 zdjęć z Instagrama (400/640/1000 px) + logo jako maski PNG
│                     + hero-atelier.{webm,mp4}, hero-poster-1280.webp, pudelko-premium-*.webp.
│   └── cfg/           41 grafik konfiguratora + 11 próbek koloru
│                     + spin-czerwony-00..23.webp (obrót 360°, tylko czerwień).
└── tools/             Skrypty generujące assety. Uruchamiaj TYLKO gdy trzeba coś dorobić.
```

Po każdej zmianie w `page.html` **lub `build.js`**: `node build.js`.
Podgląd lokalny: `python3 -m http.server 8731` (maski CSS logo nie działają z `file://`).

---

## Gdzie co siedzi w `page.html`

Plik jest długi, ale ma sztywną kolejność. Szukaj po tych kotwicach:

| Szukaj | Co znajdziesz |
|---|---|
| `--- TOKENY ---` / `:root{` | Paleta, typografia, skala odstępów |
| `POINTER GLOW` | Poświata za kursorem (gradient na `body::before`) |
| `HERO` | Pełnoekranowy nagłówek z filmem, własne tokeny na ciemne tło |
| `2027 —` | Warstwa wyciszająca: rytm, linie zamiast cieni, ziarno, menu na hero |
| `KONTAKT + MAPA` | Dwie kolumny + mapa na całą szerokość kontenera |
| `function heroFilm` | Start, pauza i warunki pobrania filmu w tle |
| `KONFIGURATOR — KAFELKI` | Style kafelków, podsumowania, B2B, mapy, przycisku na górę |
| `var DRAFT_MODE` | Przełącznik trybu roboczego |
| `var ORDER_ENDPOINT` | Adres, na który lecą zamówienia |
| `function pvScale` / `bouquetWidth` | Skala podglądu i szerokość bukietu wg liczby róż |
| `function previewSrc` | Plik podglądu: `preview-{kolor}-{oprawa}[-korona]-900.webp` |
| `function renderAddonChips` | Miniatury zaznaczonych dodatków obok podglądu (korona jest na samym zdjęciu) |
| `function paintPreviewCaption` | Podpis podglądu: okazja koloru (`occ`), nazwa, liczba róż, oprawa, dodatki |
| `var SPIN_SETS` | Zestawienia z obrotem 360° (klucz `kolor-oprawa[-korona]`), na razie puste |
| `var BIZ` | Dane sprzedawcy (działalność nierejestrowana — bez NIP i REGON) |
| `var ROSE_TIERS` | **Jedyne źródło ceny róży.** Czyta je też `build.js` |
| `var SIZES` | Cztery gotowe zestawy: liczba róż + szerokość. Bez kwot — te liczy `rosesPrice()` |
| `var COLORS` / `SLEEVE` / `WRAPS` / `ADDONS` | Opcje konfiguratora |
| `function domeHTML` / `satinHTML` / `organzaHTML` | Rysunki zastępcze tam, gdzie nie ma zdjęcia |
| `var SHIPPING` | Stawki dostawy |
| `var COLLECTION` / `GALLERY` / `FAQ` | Treści sekcji |
| `var T = {` | Tłumaczenia EN i UA |
| `var PL_JS` | Polskie teksty generowane skryptem |
| `var DOCS` | Sześć dokumentów prawnych w trzech językach |
| `function renderForm` | Budowanie kafelków |
| `function renderSummary` | Panel podsumowania |
| `function postForm` | Wysyłka formularzy |

### Jak działa wielojęzyczność

Polski siedzi **w HTML-u** (dobre dla SEO, działa bez JS) i jest zbierany do słownika przez
`harvestPL()` przy starcie. EN i UA są w obiekcie `T`. Teksty generowane skryptem mają klucze
`js.*` i polskie wersje w `PL_JS`.

Dodajesz napis? Nadaj elementowi `data-t="klucz"` (albo `data-tl` dla `aria-label`/`alt`,
`data-tp` dla `placeholder`) i dopisz klucz do `T.en` **oraz** `T.uk`. Dane produktowe mają
pola `pl` / `en` / `uk` przy każdym wpisie.

Sprawdzenie kompletności kluczy:
```bash
python3 - <<'PY'
import re
s=open('page.html',encoding='utf-8').read()
a=s.index('en:{'); b=s.index(',\nuk:{')
k=lambda t: set(re.findall(r'(?:^|[,{]\s*)"([A-Za-z][\w.]*)":', t, re.M))
en,uk = k(s[a:b]), k(s[b:s.index('\n};',b)])
used = set(re.findall(r'data-t(?:l|p)?="([^"]+)"', s))
print('brak w UA:', sorted(en-uk) or 'ok')
print('brak w EN:', sorted(used-en) or 'ok')
PY
```

---

## Decyzje, których nie cofaj bez powodu

**Podgląd w konfiguratorze to „Wizualizacja", nie „zdjęcie produktu."**
Grafiki powstały modelem Google Nano Banana na bazie dwóch prawdziwych zdjęć jako referencji
stylu. Strona mówi o tym wprost, a lede odsyła do galerii z prawdziwymi fotografiami.
Podmiana tego opisu na „zdjęcie produktu" to nieuczciwa praktyka rynkowa — nie rób tego.

**Brak prawa odstąpienia dla bukietów z konfiguratora.**
Art. 38 ust. 1 pkt 3 ustawy o prawach konsumenta — towar nieprefabrykowany na indywidualne
zamówienie. Klientka musi to potwierdzić checkboxem przed wysłaniem. Nie usuwaj tego pola.

**Zero odesłań do platformy ODR.** Wyłączona 20 lipca 2025 r., linkowanie do niej wprowadza
konsumenta w błąd.

**Liczba realizacji: 32.** Tyle postów ma profil (sprawdzone scraperem). Nie wpisuj „100+".

**Film w nagłówku został wyczyszczony z cudzych znaków towarowych.** Atelier przysłało dwa
klipy i **oba** miały na wstążce napis „Dior" w każdej klatce; drugi dodatkowo kartkę
z kodem QR i złotą tabliczkę w tle. Cudzy zarejestrowany znak na stronie sprzedającej
bukiety sugeruje powiązanie z obcą marką i naraża atelier na roszczenia. Procedura była
za każdym razem ta sama: klatka z klipu → model usuwa napisy i rekwizyty → z czystej
klatki powstaje nowy film. Obecny materiał pochodzi z drugiego klipu (tiara, liliowy
motyl, satynowa róża). **Nie podmieniaj go na wersję z widocznym logo.**

Jeśli właścicielka przyniesie kolejny materiał — obejrzyj go klatka po klatce, zanim
cokolwiek z nim zrobisz. Szybki podgląd:
`ffmpeg -i klip.mp4 -vf "select='not(mod(n,48))',scale=520:-1,tile=5x1" -frames:v 1 strip.png`

**Zwolnione tempo robi interpolacja klatek, nie samo rozciągnięcie czasu.** Zwykłe
`setpts` przy 24 kl./s daje skokowy ruch. W `README.md` jest gotowy łańcuch komend:
`minterpolate` dorabia klatki pośrednie, potem dopiero spowolnienie, na końcu przenikanie
zamykające pętlę.

**Nagłówek i pudełko w kontakcie to wizualizacje, nie fotografie produktu.** Oba mają to
napisane — plakietka w rogu nagłówka, podpis pod zdjęciem pudełka i zdanie w stopce.
Stopka nie mówi już „wszystkie zdjęcia przedstawiają realne zamówienia", bo po dodaniu
tych dwóch rzeczy przestało to być prawdą.

**Przycisk pauzy filmu zostaje.** WCAG 2.2.2: treść, która sama się animuje dłużej niż
5 sekund, musi dać się zatrzymać. To jedyne zabezpieczenie, jakie zostało — tym bardziej
go nie ruszaj.

**Film startuje sam i przewijanie jest płynne niezależnie od systemowego „ogranicz
animacje".** Właścicielka zgłosiła, że film nie rusza i linki nie przewijają się płynnie.
Przyczyna była jedna: jej system ma włączone ograniczanie animacji, a kod to respektował
(`prefers-reduced-motion`). Na jej wyraźne życzenie oba zachowania są teraz bezwarunkowe.
Pobranie filmu wstrzymuje już tylko cudzy transfer — tryb oszczędzania danych i łącze 2G.
Animacje wejścia sekcji (GSAP) **nadal** respektują to ustawienie; jeśli ktoś poprosi
o zmianę także tam, to osobna decyzja.

**Logo siedzi w `page.html` jako `data:` URI, nie jako osobny plik.** Maska CSS wczytywana
z pliku obok jest blokowana przy otwarciu strony z dysku (`file://`) i logo wtedy znika —
właśnie tak to wyglądało u właścicielki. Data URI to ten sam dokument, więc działa wszędzie.
Kosztuje ok. 33 kB w `index.html`. `assets/logo-*.png` zostają: pierwszy jest favikoną,
oba są źródłem, gdyby trzeba było przeliczyć maski od nowa (patrz `README.md`).

**Mapa Google ładuje się dopiero po kliknięciu.** Baner cookie i polityka cookies mówią,
że poza plikami niezbędnymi nic nie startuje bez zgody. Osadzona ramka Google łączy się
z Google już przy wejściu na stronę — zostawienie jej z `src` czyniło z tej obietnicy pustą
deklarację. Adres czeka teraz w `data-src`, przycisk „Pokaż mapę" go wstawia, a obok stoi
zdanie o tym, co się wtedy stanie. Po wczytaniu warstwa zastępcza dostaje `hidden`, bo leży
pod ramką i jej przycisk dałoby się złapać tabulatorem mimo że jest niewidoczny.
**Nie wracaj do `src` w znaczniku.**

**Ruch przy przewijaniu liczy natywna oś czasu CSS, GSAP jest tylko zapasem.**
Odsłanianie sekcji, pasek postępu w nawigacji, odjazd nagłówka i oś czasu w „Jak to powstaje"
chodzą na `animation-timeline` — w kompozytorze, bez pracy w JS-ie. Flaga `SCROLL_NATIVE`
sprawdza obsługę i **wyłącza** wtedy odpowiedniki w GSAP; gdzie osi nie ma (dziś Safari
i Firefox), GSAP wchodzi na jej miejsce. Nie włączaj obu naraz — policzyłyby to samo.
Reguły ukrywające elementy siedzą wewnątrz `@supports (animation-timeline: view())`,
żeby bez tej obsługi nic nie zostało niewidoczne. Sprawdzone na trzech ścieżkach:
z osią, bez osi z GSAP-em, i bez obu (wtedy `.rv{opacity:1}` z bazy trzyma treść widoczną).

**Nagłówki sekcji dzielone są na słowa dopiero PO `applyLang()`.** `harvestPL()` zbiera
polskie teksty z `innerHTML`, więc podział wykonany wcześniej wsadziłby do słownika markup
zamiast tekstu. Dzielę po zwykłych spacjach — twarda spacja zostaje w środku słowa.

**Treści dla wyszukiwarek AI leżą w HTML-u, nie w JS-ie.** Sekcja „Poradnik" (`#poradnik`)
to cztery artykuły w `<details>` — zamknięte, ale **z treścią w DOM**, więc crawler czyta je
bez klikania. Polski jest w HTML-u, EN i UA w `T`, tak jak reszta strony. To celowe:
FAQ i dokumenty prawne są generowane JS-em i dla części robotów praktycznie nie istnieją.
Jeśli dopisujesz treść, którą ma cytować AI — pisz ją w HTML-u, nie w `DOCS`.

**`robots.txt`, `llms.txt` i `sitemap.xml` generuje `build.js`,** żeby nie rozjechały się
z treścią strony. Nie edytuj ich ręcznie. `robots.txt` wpuszcza roboty AI **jawnie, każdego
w osobnej sekcji** — samo `User-agent: *` nie wystarcza, bo część z nich czyta wyłącznie
własną sekcję. Gdyby właścicielka zmieniła zdanie co do trenowania modeli na jej treściach,
wystarczy zamienić `Allow` na `Disallow` w tej jednej sekcji.

**Dane strukturalne w `build.js` czytają dane ze strony, nie z przepisanej kopii.**
`build.js` wyciąga z `page.html` tablice `FAQ` i `SIZES` (funkcja `extractArray`, skan
z liczeniem nawiasów, pomija nawiasy w cudzysłowach) i buduje z nich `FAQPage` oraz
wszystkie ceny: `lowPrice`, `highPrice`, `offerCount`, `priceRange` i `minPrice`.
**Przy zmianie cen w `SIZES` nie ma już nic do poprawienia w `build.js`.**
Reszta grafu: `LocalBusiness`/`Store`, `WebSite`, `HowTo` (pięć kroków produkcji),
`Article` (poradnik), `Product` z `AggregateOffer` i `MadeToOrder`.

**Nie dodawaj `aggregateRating` ani `review` do `Product`.** Strona sama pisze, że cytaty
w sekcji opinii **nie są zweryfikowanymi opiniami** w rozumieniu art. 4 ust. 2 ustawy
o przeciwdziałaniu nieuczciwym praktykom rynkowym. Wystawienie na ich podstawie gwiazdek
w wynikach wyszukiwania byłoby wprowadzaniem konsumenta w błąd — i to takim, które
Google też traktuje jako naruszenie wytycznych. Gwiazdki wolno dodać dopiero, gdy
właścicielka zacznie zbierać opinie w sposób umożliwiający weryfikację zakupu.

**Znane ograniczenie: EN i UA nie są indeksowalne.** Trzy wersje językowe siedzą pod
jednym adresem i przełącza je JavaScript, więc wyszukiwarki widzą wyłącznie polską.
Naprawienie tego wymaga osobnych adresów (`/en/`, `/uk/`) i `hreflang` — to przebudowa
architektury, nie poprawka. Do rozważenia, jeśli ruch z zagranicy zacznie mieć znaczenie.

**Sekcja „Jak to powstaje" ma przyklejony nagłówek i pionową oś czasu.** Cztery kroki
w rzędzie ściskały każdy opis do wąskiej szpalty; w pionie mają pełną szerokość, a numer
siedzi na osi zamiast wisieć nad ramką. Nagłówek przykleja się dopiero od 901 px i nigdy
przy systemowym „ogranicz animacje" — na telefonie zjadałby ekran.

**Obrót 360° — kod gotowy, klatek na razie brak.** Stary obrót czerwieni
(`spin-czerwony-00..23.webp`, render studyjny) jest wyrejestrowany, bo nie pasuje do zdjęć
„z telefonu”; pliki zostały. Kanwa `#pvSpin` przejmuje kadr dopiero, gdy **komplet** klatek
zestawienia się wczyta — do tego czasu i przy zestawieniach bez klatek widać zwykłe zdjęcie,
więc nie ma stanu pustego. Dodanie obrotu: 24 pliki `spin-{kolor}-{oprawa}[-korona]-00..23.webp`
i jeden wpis w `SPIN_SETS`. Procedura, prompt i koszt (ok. 90 kredytów za zestawienie):
`tools/SPIN.md`. Plakietka „Wizualizacja AI” zostaje także w trybie obrotu.

*Pułapki środowiska:* ffmpeg z Playwrighta obsługuje **tylko WebM/VP8** — nie otworzy
MP4 i nie zapisze WebP. Chromium z Playwrighta nie ma H.264 ani HEVC, więc nie odtworzy
takiego wideo. Klatki trzeba więc wyciąć po stronie usługi, a do WebP konwertować
przez `canvas.toDataURL('image/webp')` w przeglądarce.

**Trzy kadry z Instagrama zostały świadomie pominięte:** jeden z widoczną tablicą
rejestracyjną, dwa z dominującymi wstążkami z logo Diora.

---

## Do zrobienia po stronie właścicielki

> Aktualna, pełniejsza lista (z numerami linii i przeglądem prawnym z 7 października):
> **`DO-ZROBIENIA.md`**. Poniżej wersja z poprzedniej sesji.

1. `BIZ.legal` — **imię i nazwisko sprzedawcy**. To jedyne pole, które zostało puste:
   przy działalności nierejestrowanej nie ma nazwy firmy, a ustawa o prawach konsumenta
   i tak wymaga podania, kto sprzedaje. Żółte `[imię i nazwisko]` jest w stopce, w GPSR
   i w czterech dokumentach × trzy języki — wszystkie znajdziesz, szukając `class="todo"`.
   Reszta danych jest uzupełniona: 62-800, tel. 781 893 346, brak NIP i REGON.
2. `ROSE_TIERS` i `SIZES` — ceny wpisane 7 października 2026 wg poprawek z PDF-ów.
   Zmiana jednej kwoty w `ROSE_TIERS` przelicza kafelki, podsumowanie, poradnik,
   FAQ, `llms.txt` i dane strukturalne. `COLORS`, `WRAPS`, `ADDONS` — dopłaty.
3. `SHIPPING` — stawki są prawdziwe (cennik Furgonetka/InPost dla nadawcy indywidualnego,
   wrzesień 2026). Przy umowie biznesowej będą niższe.
4. `DRAFT_MODE = false` — dopiero po punktach 1–3.
5. `DOCS` — uzupełnić dane w regulaminie, polityce prywatności, informacji GPSR
   i deklaracji dostępności, we **wszystkich trzech językach**.
6. FormSubmit — po pierwszym wysłanym formularzu przyjdzie na `elvensilk@wp.pl` mail
   z linkiem aktywacyjnym. Bez kliknięcia zamówienia nie dochodzą.
7. Zweryfikować progi rabatowe w sekcji B2B — to na razie propozycja.

## Ryzyka zgłoszone wcześniej, nadal otwarte

- **„Biała organza" jest teraz w dwóch krokach naraz** — w nowym kroku 3 „Oprawa bukietu"
  i w kroku 4 „Opakowanie". Poprawka z PDF-u mówiła „**dodaj** kolejny wariant główny
  nr 3", więc dodałem krok, niczego nie usuwając: usunięcie organzy z „Opakowania"
  wywaliłoby też cztery opcje, o których nikt nie pisał, i rozsypało kartę „Nokturn"
  (ma folię holo na zdjęciu i w opisie). Domyślna oprawa to „bez organzy", więc przy
  pierwszym wejściu nic się nie dubluje. Jeśli to miała być podmiana, a nie dodanie —
  wystarczy usunąć wpis `organza` z `WRAPS` i zmienić `DEFAULT.wrap`.
- **„Wysyłka na cały świat"** w nagłówku to deklaracja z poprawki. Tabela dostaw ma
  wyłącznie stawki krajowe (InPost/Furgonetka). Albo dopisać cennik zagraniczny, albo
  zdanie w FAQ, że wysyłkę poza Polskę wycenia się indywidualnie.
- ~~„1–∞ róż w bukiecie"~~ — zamienione 7 października na **„1–101”** (największy gotowy
  zestaw; liczba liczy się z `SIZES`). Pole własnej liczby nadal przyjmuje 1–999.
- Na części zdjęć z galerii widać **wstążki z logo Diora** — na stronie firmowej to ryzyko
  znaku towarowego. Docelowo dorobić zdjęcia z własnymi wstążkami Elven Silk.
- Maskotki na kilku zdjęciach to **postacie Disneya**. W konfiguratorze jest neutralna
  „pluszowa maskotka", ale zdjęcia zostały — decyzja właścicielki.

---

## Naprawione błędy — nie cofaj tych zmian

| Było | Jest |
|---|---|
| Reguła poświaty nadpisywała `position:sticky` menu — nagłówek **nigdy się nie przyklejał** | `.nav` wyjęte z tej listy selektorów |
| Logo w stopce miało rozmiar 0×0 — `<span>` liniowy ignoruje `width`/`height` | `.logo-lockup{display:block}` |
| `build.js` zostawiał `<title>` i linki do fontów w `<body>`, dokument miał **dwa `<title>`** | Tagi przenoszone do `<head>` |
| Styl pól celował tylko w `input[type="text"]` — e-mail, telefon i data świeciły na biało w ciemnym motywie | Selektor obejmuje wszystkie pola tekstowe |
| `img` bez `height:auto` — atrybut `height` z HTML-a rozciągał obrazki przy płynnej szerokości | `img{height:auto}` w bazie |
| `--ink-3` i `--gold` miały kontrast 3,2:1 zamiast wymaganych 4,5:1 | Ciemniejsze warianty, sprawdzone liczbowo |
| Brak favikony | `logo-mark.png` jako `icon` i `apple-touch-icon` |
| Blok liczb nad kolekcją zgubił `display:grid` — liczba i podpis sklejały się w jedną linię bez odstępu | `display:grid` z powrotem na `.stat` |
| Logo znikało przy otwarciu strony z dysku — maska CSS z osobnego pliku jest blokowana przy `file://` | Logo wklejone jako `data:` URI |
| Systemowe „ogranicz animacje" wyłączało **naraz** autostart filmu i płynne przewijanie | Oba bezwarunkowe; pauza filmu zostaje jako wyjście awaryjne |
| Dane strukturalne `FAQPage` rozjechały się z widocznym FAQ: osiem pytań w schema, dziewięć na stronie, inne brzmienia, a trzy pozycje pochodziły z sekcji „Poradnik". Do tego w odpowiedzi o cenie stał goły myślnik zamiast kwoty, bo `<b data-price>` wypełnia dopiero JS | `FAQPage` budowany z tablicy `FAQ` w `page.html`, ceny podstawiane z `SIZES` — rozjazd przestał być możliwy |
| `--ok` dawał 3,72:1 na plakietce „bezpłatnie" (zielony tekst na zielonkawym tle) przy wymaganych 4,5:1 | Ciemniejsza zieleń `#1B6A4E`, sprawdzona na wszystkich czterech tłach w obu motywach |
| **Ta sama przyczyna, druga ofiara:** „ogranicz animacje" dawało `display:none` warstwie poświaty i blokowało `initGlow()` — gradient za kursorem **nigdy** nie działał na maszynie właścicielki | Poświata bezwarunkowa, gaśnie już tylko przy `hover:none` (brak kursora). Wzmocniona z ok. 4,7% do 11,4% krycia, bo przy poprzedniej wartości była na granicy dostrzegalności |
| `Object.assign({}, DEFAULT)` kopiowało tablicę `addons` przez referencję — zaznaczenie dodatku dopisywało go do samego `DEFAULT`, więc **„Zacznij od nowa" nie czyściło konfiguratora** | `freshState()` daje każdemu stanowi własną kopię tablicy |
| Czytnik ekranu ogłaszał podgląd jako **„Photo of a bouquet" / „Фото букета"** — wersja PL mówiła „Wizualizacja", EN i UA zostały z czasów prawdziwych zdjęć | `js.pvAlt` we wszystkich trzech językach mówi o wizualizacji |
| Mapa Google startowała sama, mimo że baner cookie obiecuje coś innego | Ramka czeka na kliknięcie (patrz decyzja wyżej) |
| Błąd walidacji zamówienia **nadpisywał na stałe** informację prawną pod przyciskiem i nie był ogłaszany przez czytnik ekranu | Osobne pole `#orderAlert` z `role="alert"`; informacja prawna zostaje na miejscu |
| Nagłówki skakały z `h2` na `h4` (sekcja B2B i stopka) przy deklarowanym WCAG 2.1 AA | `h3` w obu miejscach, selektory CSS zaktualizowane |
| ~2 kB martwego CSS-u (`.swatches`, `.sw*`, `.addon*`) — zostało po przejściu na kafelki | Usunięte; `.opt*` zostaje, bo obsługuje wybór dostawy |

## Czego NIE robić w nowej sesji

- Nie generuj obrazów (Magnific / Nano Banana / Higgsfield) — komplet jest w `assets/`.
  **Wyjątek 1:** jeśli właścicielka przyniesie nowy materiał, sprawdź go najpierw pod kątem
  cudzych znaków towarowych i dopiero wtedy decyduj.
  **Wyjątek 2:** obroty 360° — ok. 90 kredytów za zestawienie, tylko na wyraźne życzenie
  właścicielki (`tools/SPIN.md`). Nowe podglądy rób wg `tools/podglady/README.md`.
- Nie scrapuj Instagrama ponownie (Apify) — dane są w tym dokumencie i w `GALLERY`.
- Nie edytuj `index.html` — to plik generowany.
- Nie instaluj `sharp` ani `playwright`, jeśli nie robisz nowych assetów albo zrzutów ekranu.

Chcesz podmienić grafiki na prawdziwe zdjęcia? Zachowaj nazwy plików —
`preview-{kolor}-{oprawa}[-korona]-900.webp`, `size-{liczba}.webp`, `sleeve-{id}.webp`,
`wrap-{id}.webp`, `addon-{id}.webp`, `color-{kolor}.webp` — wtedy kod nie wymaga żadnej zmiany
(poza polem `occ` koloru, jeśli zmienia się scena).

---

## Dane firmy zebrane z profilu

- **Elven Silk**, ul. Częstochowska 140, 62-800 Kalisz, tel. 781 893 346 — adres i telefon
  to **dane punktu Furgonetka**, nie adres prywatny. Strona mówi o tym wprost w stopce,
  w regulaminie i w `llms.txt`; nie usuwaj tego dopisku, bo bez niego klient dzwoni
  pod numer, który nie jest numerem atelier.
- Sprzedaż w ramach **działalności nierejestrowanej** (art. 5 ust. 1 Prawa przedsiębiorców):
  brak wpisu w CEIDG, a więc brak NIP i REGON. Prawa konsumenta obowiązują bez zmian
  i dokumenty prawne to wyjaśniają.
- e-mail `elvensilk@wp.pl`, Instagram `@elvensilkofficial`, Vinted `2wearr`
- Asortyment: bukiety z satynowych róż (dowolna liczba, zestawy 7/19/37/101), boxy komunijne, kartki okolicznościowe,
  bukiety ze zdrapek, dawniej gumki scrunchie
- Dodatki spotykane w realizacjach: korony i tiary, pluszaki, podświetlenie LED, brokat,
  motylki, pralinki, balony foliowe, suszki i pampasy
- Kolory w konfiguratorze (13): czerwony, róż, pudrowy róż, morelowy, żółty, kremowy, biały,
  zielony, błękit, granat, jasny fiolet, fiolet, czarny. **Wszystkie mają już grafiki**
  (od 7 października): próbkę `color-<id>.webp` i dziesięć podglądów
  `preview-<id>-<oprawa>[-korona]-900.webp` we własnej scenie koloru.
- Bordo i szampański **usunięte na życzenie właścicielki** (poprawki z 7 października).
  Ich pliki zostały w `assets/cfg` — gdyby miały wrócić, nie trzeba ich generować od nowa.

---

## Prompt startowy do nowej rozmowy

> Pracuję nad stroną Elven Silk. Repo: `grafik2024/cv`, gałąź
> `claude/brave-heisenberg-1tdgug`, katalog `elven-silk/` (wcześniej `grafik2024/2026`,
> gałąź `claude/confident-euler-7c207e`).
>
> Zacznij od przeczytania `elven-silk/HANDOFF.md` — jest tam mapa plików, zasady projektu
> i lista rzeczy, których nie wolno ruszać. Najważniejsze: **wszystkie grafiki są już
> wygenerowane, nie generuj żadnych nowych obrazów i nie scrapuj Instagrama.**
>
> Edytuj `elven-silk/page.html` (treść, style, skrypt strony) albo `elven-silk/build.js`
> (dane strukturalne i pliki dla wyszukiwarek). Po każdej zmianie uruchom `node build.js`
> w katalogu `elven-silk/` — generuje `index.html`, `robots.txt`, `llms.txt` i `sitemap.xml`.
> **Nie edytuj tych czterech plików ręcznie, nadpiszą się.**
>
> Podgląd lokalny: `python3 -m http.server 8731` w katalogu `elven-silk/`.
> Nie otwieraj `index.html` podwójnym kliknięciem — mapa Google i fonty potrzebują serwera.
>
> Do zrobienia: [tu wpisz swoje poprawki]

**Sprawdzanie zmian bez instalowania niczego.** Chromium z Playwrighta stoi
w `/opt/pw-browsers/chromium-1194/chrome-linux/chrome` i da się nim sterować przez CDP
z wbudowanego `WebSocket` Node'a — tak weryfikowałem wszystkie poprawki z tabeli niżej.
Pułapki tego środowiska: ten Chromium **nie ma H.264 ani HEVC**, a ffmpeg z Playwrighta
obsługuje **tylko WebM/VP8** (nie otworzy MP4, nie zapisze WebP). Do WebP konwertuj przez
`canvas.toDataURL('image/webp')` w przeglądarce. `matchMedia('(hover: hover)')` jest tam
domyślnie `false` — bez flagi `--blink-settings=primaryHoverType=2,availableHoverTypes=2`
poświata za kursorem i pochylenia w ogóle się nie uruchomią i wyjdzie fałszywy alarm.

---

## Sesja z 7 października — grafiki z Higgsfield, podgląd z organzą, przegląd prawny

**Grafiki — wersja ostateczna (runda 2, ok. 160 kredytów).** Pierwsza wersja (studyjne
rendery + nakładki CSS: korona, LED, brokat, barwienie zestawów) została odrzucona — „wygląda
jak nokturn”. Właścicielka chciała zdjęć, które wyglądają jak jej własne z telefonu,
z widocznym logo, i różnych scen zamiast jednego kadru z podmienianym kolorem.

- **Każdy kolor = własna scena i okazja w roku** (`tools/podglady/SCENES.md`): czerwień —
  złota godzina (zatwierdzony wzorzec), róż — Walentynki nocą, pudrowy — jabłoń w maju,
  żółty — Dzień Kobiet, kremowy — ślub, jasny fiolet — lawenda, błękit — Bałtyk, granat —
  most o niebieskiej godzinie, morelowy — liście z góry, czarny — Halloween, fiolet —
  listopadowa aleja, biały — jarmark w śniegu, zielony — zimowe osiedle. Ręka i kąt
  ujęcia są za każdym razem inne. Okazja pokazuje się nad nazwą bukietu („Pomysł na:
  Walentynki · luty”) — pole `occ` w `COLORS`, klucz `pv.occ` w trzech językach.
- **„Nie tak AI-idealnie”**: prompt każe zrobić szybkie zdjęcie telefonem — kadr lekko
  krzywy i przycięty, pogniecione arkusze, nierówne wstążki, zwykła obróbka telefonu.
  Jako wzór stylu poszło prawdziwe zdjęcie właścicielki.
- **Logo to nadruk prawdziwego pliku**, nie rysunek AI: `assets/logo-lockup.png` na co
  drugim arkuszu (3–4 na zdjęcie), wzdłuż arkusza, nigdy w poprzek krawędzi; na czarnej
  organzie jasną farbą; w wersji bez organzy na zawieszce. Każde logo obejrzane w powiększeniu.
- **Korona to osobne zdjęcia** (`…-korona-900.webp`), nie nakładka. W obrębie sceny kadr jest
  identyczny, więc koronę z jednego zdjęcia przenosi maska na pozostałe organzy.
- **Zestawy 7/19/37/101** — cztery zdjęcia czerwieni w scenie wzorca, z logo. Maski
  `size-*-tint.webp` i barwienie kanwą usunięte; kafelek pokazuje zdjęcie, a dla koloru
  bez zdjęcia nadal rysuje się schemat z CSS.
- Podgląd dostał rozmyte tło z tego samego zdjęcia (`#pvBlur`) zamiast beżowego gradientu
  — przy małych bukietach brzeg pomniejszonego zdjęcia nie odcina się od tła.

Procedura, prompty, skrypty składania, współrzędne logo i job ID: `tools/podglady/`.

**Obrót 360°** uogólniony na zestawienia (`SPIN_SETS`, klucz `kolor-oprawa[-korona]`).
Stary obrót czerwieni (render studyjny) jest wyrejestrowany — pliki zostały. Procedura
i koszt: `tools/SPIN.md`, `tools/spin-frames.sh`.

**Przegląd prawny** — szczegóły i lista dla właścicielki w `DO-ZROBIENIA.md`. Najważniejsze
zmiany: akapit o wizualizacjach poglądowych w regulaminie §2 (PL/EN/UA), oznaczenie
„Wizualizacja AI” (art. 50 AI Act), przycisk „Wyślij zapytanie” zgodny z regulaminem §4,
FormSubmit / wp.pl / Google w polityce prywatności, §10 uzupełniony o wymogi ustawy
o świadczeniu usług drogą elektroniczną, **fonty i GSAP z własnego serwera** (wcześniej
Google Fonts i cdnjs łączyły się przed zgodą — nie wracaj do CDN-ów). Usunięty żółty
tekst „DO ZROBIENIA” w sekcji opinii.

Sprawdzone w Chromium (Playwright): 130 podglądów i wszystkie kafelki istnieją, podgląd
zmienia się z kolorem, oprawą i koroną, podpis okazji zmienia się z kolorem i językiem,
miniatury reagują na dodatki, konsola czysta, żadnego 404, komplet kluczy PL/EN/UA.

## Próbowane i odrzucone

**Bukiet jako model 3D (Three.js).** Zbudowany i obejrzany: płatki jako wyginana siatka,
róże w okółkach, kopuła rozłożona spiralą Fibonacciego, satyna na `MeshPhysicalMaterial`
z `sheen`, wszystkie płatki w jednym `InstancedMesh` (170 tys. trójkątów, 9 wywołań
rysowania). Po trzech podejściach model nadal czyta się jak czerwone wiatraczki na lejku
i **przegrywa ze zdjęciami z `assets/cfg/`** — a strona sprzedaje rękodzieło, więc gorsza
wizualizacja produktu to strata, nie zysk. Do tego 687 kB biblioteki i konieczny zapas
na wypadek braku WebGL. Nie wracaj do tego bez modelu zrobionego przez grafika 3D.

**Przechylanie płaskiego podglądu w CSS 3D.** Ze zdjęcia robi się skośny prostokąt
z odklejoną plakietką — wygląda jak błąd, nie jak prezentacja produktu. Odrzucone przez
właściciela. Prawdziwy obrót 360° wymaga 24–32 klatek dookoła obiektu; w `assets/cfg/`
jest **jeden kadr na kolor**.

## Stan na koniec ostatniej sesji

### Sesja z 7 października — poprawki z czterech PDF-ów

Wprowadzone w całości, w PL, EN i UA:

- **Dane sprzedawcy.** Kod pocztowy 62-800, telefon 781 893 346, **usunięte NIP i REGON**
  (działalność nierejestrowana — właścicielka to doprecyzowała w trakcie sesji, pierwotna
  prośba mówiła o NIP-ie Furgonetki). Wszędzie, gdzie pojawia się adres albo telefon,
  jest dopisek, że to dane **punktu Furgonetka**. Regulamin dostał akapit wyjaśniający,
  że brak NIP-u nie zmienia praw konsumenta.
- **Nagłówek.** „Bukiet, który zostaje **z tobą** na lata" (było: „z nią" — zdanie pod
  spodem też przeszło na drugą osobę, inaczej traciło sens), „Ręcznie tworzone wieczne
  róże z satyny", trzy fakty przepisane.
- **„szyję/zwijam" → „tworzę"** — konsekwentnie w całym serwisie, bo ta sama podmiana
  wracała w czterech osobnych PDF-ach. Wyjątek: **opis samej techniki w poradniku
  i w `HowTo` zostaje**, bo tam „zwijam płatki wokół łodygi" to dosłownie to, co się dzieje.
- **Pasek i liczby.** „2–5 dni na wytworzenie", „Ręcznie tworzone w Kaliszu", usunięty
  wpis „Punkt Furgonetka na miejscu", licznik „1–∞ róż w bukiecie".
- **Konfigurator przebudowany:**
  - Krok 1 „Ilość róż" — cztery zestawy (7 / 19 / 37 / 101) **plus pole z plusem
    i minusem** na dowolną liczbę od 1 do 999.
  - Cena liczona z `ROSE_TIERS`: 1–3 → 15 zł/szt., 4–7 → 14, 8–11 → 13, 12–15 → 12,
    od 16 → 11. Zestawy nie mają własnych kwot — wychodzą z tej samej tabeli,
    więc nie mają jak się rozjechać. `build.js` czyta ją tak samo.
  - Krok 2 „Kolor róż" — bez bordo i szampańskiego, z morelowym, jasnym fioletem,
    zielonym i żółtym.
  - **Nowy krok 3 „Oprawa bukietu"** („otulina wokół kwiatów"): biała, czarna, różowa
    i jasnoniebieska organza albo bez organzy. Reszta kroków przesunięta o jeden.
- **Kafelki bez zdjęć.** Nowe wielkości, kolory i oprawy nie mają fotografii, a grafik
  nie generowałem. Zamiast pustych kadrów rysuję z CSS: kopułę z kropek (zestawy
  i podgląd), próbkę satyny (kolory) i siatkę organzy (oprawy). Od razu widać, że to
  schemat, nie zdjęcie produktu — i nic nie trzeba dogrywać, żeby strona działała.
- **Treści zależne od cennika** przepisane: poradnik „Ile róż wybrać", FAQ o cenie,
  sekcja kontaktowa, `llms.txt`, opis produktu w danych strukturalnych.

Sprawdzone w przeglądarce: pełna regresja z poprzedniej sesji (8 punktów) przechodzi,
cennik zweryfikowany na dwunastu wartościach przez DOM, 25 losowań bez pustego pola
i bez brakujących plików, stary stan w `localStorage` (bordo + rozmiar 33 + folia holo)
degraduje się bezpiecznie, obrót 360° dla czerwieni nadal działa, konsola czysta,
komplet kluczy PL/EN/UA się zgadza.

**Dwie rzeczy do decyzji właścicielki** — opisane niżej w „Ryzyka".

---

### Wcześniej: siedem commitów na `claude/confident-euler-7c207e`, w kolejności:

1. **Siedem poprawek z przeglądu** — zepsuty przycisk „Zacznij od nowa" (tablica `addons`
   kopiowana przez referencję psuła `DEFAULT`), opis podglądu mówiący w EN i UA „Photo"
   zamiast „Wizualizacja", mapa Google ładowana bez zgody mimo obietnicy w banerze cookie,
   błąd walidacji kasujący na stałe informację prawną, kolejność nagłówków, martwy CSS.
2. **Warstwa ruchu na natywnej osi czasu CSS** — odsłanianie sekcji, pasek postępu
   w nawigacji, odjazd nagłówka, nagłówki słowo po słowie, View Transitions przy zmianie
   motywu i języka. GSAP zszedł do roli zapasu.
3. **Obrót 360° bukietu** dla czerwieni — 24 klatki z jednego ujęcia orbitującego.
4. **Obrót uogólniony na dowolny kolor** + sekcja „Jak to powstaje" przebudowana na
   przyklejony nagłówek i pionową oś czasu.
5. **Gradient za kursorem** odblokowany przy „ogranicz animacje" i wzmocniony.
6. **Poradnik o produkcji** (4 artykuły × PL/EN/UA) + `robots.txt`, `llms.txt`,
   `sitemap.xml`, dane strukturalne `HowTo`/`Article`/`Product`.
7. **Dane strukturalne czytane ze strony** zamiast przepisywane — `FAQPage` i wszystkie
   ceny wyciągane z `page.html`, kontrast `--ok` podniesiony do 4,5:1.

Wszystko sprawdzone w przeglądarce. Komplet kluczy PL/EN/UA się zgadza, konsola czysta,
konfigurator, galeria, FAQ i sześć dokumentów prawnych działają bez zmian.

Nie zaczęte i warte rozważenia:

- **Zdjęcia w galerii z wstążkami Diora i maskotkami Disneya.** To ten sam problem, przez
  który dwa razy czyściłem film. Zostały, bo to prawdziwe realizacje i decyzja należy do
  właścicielki — ale ryzyko jest realne i nie zniknęło.
- **Animacje wejścia sekcji nadal respektują „ogranicz animacje"** — i tylko one.
  Film, płynne przewijanie i poświata za kursorem działają u właścicielki bezwarunkowo
  (trzy osobne decyzje, opisane wyżej). Jeśli zgłosi, że „sekcje nie pojawiają się przy
  przewijaniu" — to jest przyczyna i to ta sama rodzina błędu co dwa poprzednie razy.
  Odblokowanie ich to czwarta taka decyzja, nie oczywistość: tam chodzi o ruch treści,
  a nie o tło, więc dla kogoś z prawdziwą nadwrażliwością na ruch ma to znaczenie.
- **`DRAFT_MODE` wciąż na `true`** i żółty pasek u góry jest widoczny. Zejdzie dopiero po
  uzupełnieniu danych firmy i prawdziwych cen (lista wyżej).
