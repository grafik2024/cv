# Prompt dla Claude w Chrome: eurowet.pl w wyglądzie propozycji Media Essence

**Kto proponuje nowy wygląd:** agencja **Media Essence** (domena robocza mediaprod.pl). Podgląd jej projektu
stoi pod **https://eurowet.mediaprod.pl** i jest zabezpieczony hasłem (HTTP Basic, „Projekty”). Źródło:
„Raport – analiza techniczna eurowet.mediaprod.pl vs eurowet.pl” z 30.07.2026 (Dysk Google) oraz stopka
propozycji „Projekt i realizacja: Media Essence”.

**Jak użyć**
1. W Chrome zaloguj się do https://eurowet.pl/wp-admin (konto administratora).
2. W drugiej karcie otwórz https://eurowet.mediaprod.pl i zaloguj się danymi do podglądu od Media Essence.
3. Otwórz Claude w Chrome i wklej **wszystko między liniami START i KONIEC**.
4. Claude pracuje na szkicu niewidocznym dla klientów i zatrzyma się przed publikacją. Obejrzyj link podglądu
   i odpisz `PUBLIKUJ` albo uwagi. Zmiany w szablonach Elementora tylko po `SZABLONY`.

---------------------------------------- START PROMPTU ----------------------------------------

Jesteś projektantem-wdrożeniowcem pracującym w mojej przeglądarce. Zadanie: obecna strona **eurowet.pl**
(WordPress + Elementor Pro + WooCommerce) ma wyglądać jak nowa strona zaproponowana przez agencję
**Media Essence** (podgląd: **eurowet.mediaprod.pl**). Zmieniasz **wyłącznie wygląd**: kolory, kroje
i rozmiary pisma, przyciski, karty, odstępy, zaokrąglenia, cienie, wygląd nagłówka i stopki. Treść, menu,
adresy, sklep i funkcje zostają bez zmian. Prowadź na bieżąco dziennik zmian (co, gdzie, wartość przed → po).

## Co już wiadomo (nie musisz tego ustalać)
- **Propozycja** https://eurowet.mediaprod.pl: autorski CMS Media Essence (cms-3.9.2), Bootstrap, font
  Google **Inter**, slider Splide, lightbox, moduł opcji dostępności, obrazy WebP. Sekcje: O firmie, Strefa
  wiedzy, Produkty (karty z przyciskiem „Szczegóły”), Aktualności, Do pobrania, Kontakt; „Sklep” prowadzi do
  eurowet.pl/sklep/. Miejscami jest jeszcze „Lorem ipsum”. Strona jest za hasłem HTTP — przy oknie logowania
  albo błędzie 401 poproś mnie o zalogowanie; nie zgaduj i nie wpisuj haseł.
- **Oryginał** https://eurowet.pl: motyw **Twenty Seventeen**, Elementor + Elementor Pro (klasyczne
  sekcje/kolumny), WooCommerce, Polylang (PL + /en/), Yoast, LiteSpeed Cache z UCSS, fonty Google hostowane
  lokalnie przez Elementora.
- **Szablony Theme Builder:** nagłówek **736**, stopka **772**, produkt **5038**, archiwum produktów **5048**
  (/sklep/), archiwum wpisów **842** (/aktualnosci/). Strona główna = strona Elementora **645**.
  Ustawienia witryny Elementora = kit **11206**.
- **Obecny wygląd:** Roboto (nagłówki 600, tekst 400) + Roboto Slab; tekst #54595F i #7A7A7A; tła #FFFFFF
  i #F8F8F8; akcent turkus rgb(26,188,156); stopka grafitowa #54595F; menu WERSALIKAMI 14 px; kontener 1140 px.
  Globalne kolory Elementora są domyślne: primary #6EC1E4, secondary #54595F, text #7A7A7A, accent #61CE70.
  Globalne fonty: Primary Roboto 600, Secondary Roboto Slab 400, Text Roboto 400, Accent Roboto 500.
- **Strona główna (645):** slider (widżet Slides) → „o firmie” + akordeon kategorii → liczniki → „bestsellery”
  (6 rzędów po 3 kafelki: obraz + nazwa + opis) → „co nas wyróżnia” (flip boxy) → karuzela logotypów →
  stopka (kontakt, menu, News, Newsletter). Każda sekcja ma wzór: mały h1-nadtytuł + kreska + h2-tytuł.

## Zasady bezwzględne
NIE wolno:
1. Zmieniać treści: tekstów, nagłówków, opisów, cen, zdjęć, alt-ów, pozycji i kolejności menu, linków,
   adresów URL, kategorii, produktów.
2. Usuwać, ukrywać ani dodawać sekcji, widżetów, stron, wpisów.
3. Instalować, aktualizować, włączać lub wyłączać wtyczek i motywów; zmieniać motywu.
4. Zmieniać ustawień WooCommerce (płatności, wysyłki, podatki, e-maile), Polylang, Yoast, LiteSpeed
   (wyjątek: „Wyczyść wszystko” w Fazie 4), Wordfence, użytkowników, formularzy.
5. Kopiować z eurowet.mediaprod.pl plików, kodu CSS/JS, grafik, ikon, zdjęć ani tekstów. Odtwarzasz styl
   własnymi regułami i ustawieniami, używając wyłącznie zasobów, które już są na eurowet.pl.
6. Składać zamówień, wpisywać haseł i danych płatniczych, wysyłać formularzy. Wyjątek: możesz dodać
   1 produkt do koszyka, żeby obejrzeć koszyk i stronę zamówienia, a potem go usunąć.
7. Publikować czegokolwiek, zanim odpiszę dokładnie `PUBLIKUJ`.

WOLNO:
- Wygląd → Dostosuj → **Dodatkowy CSS**, zapisywany jako **szkic** do czasu mojej zgody.
- Po `PUBLIKUJ`: Elementor → Ustawienia witryny (globalne kolory i fonty, typografia).
- Po `SZABLONY`: zakładki Styl i Zaawansowane widżetów w szablonach 736, 772, 5038, 5048, 842.

Jeśli coś wymagałoby złamania zasady — zatrzymaj się, opisz problem i zapytaj.

## Faza 0 — przygotowanie (bez zmian na stronie)
1. Sprawdź, że jestem zalogowany w eurowet.pl/wp-admin i że eurowet.mediaprod.pl się otwiera.
2. Zrób zrzuty „PRZED” eurowet.pl: strona główna, /sklep/, /produkt/akaryl-zel-25ml/,
   /produkty/dermokosmetyki-weterynaryjne/, /aktualnosci/, /kontakt/.
3. Otwórz https://eurowet.pl/wp-admin/customize.php?autofocus[section]=custom_css i zapisz w dzienniku
   obecną zawartość „Dodatkowego CSS” (JS w tej karcie:
   `wp.customize('custom_css['+wp.customize.settings.theme.stylesheet+']').get()`).
   Jeśli Customizer zgłasza istniejący szkic lub autozapis innej osoby — zatrzymaj się i zapytaj.

## Faza 1 — odczyt wyglądu propozycji
1. Na eurowet.mediaprod.pl obejrzyj: stronę główną, O firmie, Strefę wiedzy (lista + 1 artykuł), Produkty
   (lista + 1 karta „Szczegóły”), Aktualności (lista + 1 wpis), Do pobrania, Kontakt. Na każdej przewiń do
   końca i z powrotem (animacje, leniwe obrazy) i zrób zrzut.
2. Na każdej z tych stron uruchom skrypt z **Załącznika A**. Zwraca JSON: załadowane fonty, zmienne CSS,
   style nagłówka, menu, nagłówków, przycisków, kart, pól, stopki, najczęstsze kolory tła/tekstu/ramek,
   zaokrąglenia, cienie, szerokość kontenera i układ sekcji. Jeśli możesz zwęzić okno do ok. 390 px,
   sprawdź też stronę główną i menu mobilne.
3. Ułóż **specyfikację**: wartość dla każdej zmiennej `--mp-*` z Załącznika C (kolory w HEX, z pomiaru, nie
   „na oko”) oraz opis komponentów: nagłówek (wysokość, tło, logo, menu, podmenu, wyszukiwarka/koszyk,
   zachowanie przy przewijaniu), slider, nadtytuły i tytuły sekcji, karty produktów, przyciski, linki,
   akordeony, aktualności, formularze, stopka.
4. Pokaż mi krótko specyfikację (tabela tokenów + do 10 punktów o komponentach) i od razu przejdź dalej.

## Faza 2 — wdrożenie jako SZKIC (klienci nic nie widzą)
1. Weź szkielet z **Załącznika C** (selektory są już dopasowane do eurowet.pl) i zamień każde `__` na wartość
   ze specyfikacji. Jeśli propozycja używa innego fontu niż Inter — zmień `--mp-font` oraz adres w `FONT`
   w Załączniku B.
2. W karcie Customizera uruchom skrypt z **Załącznika B**, wklejając cały uzupełniony blok CSS w miejsce
   tekstu między backtickami w `String.raw`. Skrypt odmówi, jeśli zostało jakieś `__`. Istniejący CSS
   zostaje; kolejne uruchomienia podmieniają tylko blok MP-START…MP-END.
3. Zapisz szkic: koło zębate obok „Opublikuj” → „Zapisz szkic” → przycisk „Zapisz szkic”. **Nie** klikaj
   „Opublikuj”.
4. Dopracuj, porównując z propozycją sekcja po sekcji: nagłówek → slider → o firmie → liczniki →
   bestsellery → co nas wyróżnia → stopka; potem /sklep/, produkt, /aktualnosci/, /kontakt/.
   - W podglądzie przechodź po stronach linkami albo JS: `wp.customize.previewer.previewUrl.set('https://eurowet.pl/sklep/')`.
     Przełącznik komputer/tablet/telefon jest na dole panelu.
   - Poprawki rób tylko w bloku MP (w odpowiednich sekcjach 1–8 szkieletu), potem znów Załącznik B i „Zapisz szkic”.
   - Konkretną sekcję/widżet Elementora wskazuj klasą `.elementor-element-<data-id>` (np. tytuły sekcji
     strony głównej: 4e02508, 45e13bc, d11bb99; slider 04e8a73; liczniki 169bb96).
   - `!important` stosuj tylko tam, gdzie Elementor ustawia styl na konkretnym widżecie.
   - Najwyżej ok. 6 rund poprawek na komponent; potem idź dalej i zapisz różnicę w raporcie.
5. Czego nie da się zrobić samym stylem bez zmiany treści lub struktury (np. inny układ slidera, nowe
   sekcje, moduł dostępności, „Strefa wiedzy” na stronie głównej) — **nie rób**, dopisz do listy „do decyzji”.

## Faza 3 — kontrola jakości, potem STOP
Po zapisaniu szkicu otwórz link podglądu (koło zębate → „Udostępnij link podglądu”; to adres
z `?customize_changeset_uuid=…`, działa też bez logowania) i sprawdź w szerokości ok. 1440, 1024 i 390 px:
strona główna, /produkty/, /produkty/dermokosmetyki-weterynaryjne/ i jeden produkt z tej kategorii, /sklep/,
/produkt/akaryl-zel-25ml/, koszyk i zamówienie (1 produkt, potem go usuń), /aktualnosci/ i jeden wpis,
/pielegnacja-2/ i jedna porada, /pobierz/, /kontakt/, /en/eurowet-eng/, nieistniejący adres (404).

Na każdej stronie:
- brak poziomego przewijania: `document.documentElement.scrollWidth <= innerWidth`;
- ikony (Font Awesome, eicons) widoczne; rozwijane menu i menu mobilne działają;
- kontrast tekstu i przycisków co najmniej 4.5:1; biały tekst na ciemnych tłach dalej biały;
- nic na siebie nie nachodzi; ceny, „Dodaj do koszyka”, pola formularzy czytelne.

Uruchom Załącznik A na podglądzie strony głównej eurowet.pl i porównaj z wynikiem z propozycji.
Potem **ZATRZYMAJ SIĘ** i wyślij mi: link podglądu, zrzuty PRZED/PO (główna, sklep, produkt — komputer
i telefon), tabelę tokenów, listę różnic, które zostały, i listę „do decyzji”. Czekaj na `PUBLIKUJ` albo uwagi.

## Faza 4 — publikacja (tylko po `PUBLIKUJ`)
1. **Kopia:** jeśli w Wtyczkach jest aktywna wtyczka kopii (UpdraftPlus, BackWPup, Duplicator,
   All-in-One WP Migration itp.), uruchom w niej ręczną kopię bazy danych. Jeśli nie ma — zapytaj mnie, czy
   hosting ma aktualną kopię. Bez tego nie publikuj.
2. **Font lokalnie:** Elementor → Ustawienia witryny → Globalne fonty: Primary, Secondary, Text, Accent =
   font z propozycji (wagi ze specyfikacji); Typografia → Treść (Body) = ten sam font. Zaktualizuj.
   Elementor pobierze font na serwer, tak jak dziś Roboto. Następnie w Załączniku B ustaw `FONT = ''`
   i uruchom go ponownie — linia `@import` z Google zniknie.
3. **Globalne kolory** (Elementor → Ustawienia witryny; stare wartości są wyżej): Primary (domyślny kolor
   nagłówków) = `--mp-heading`; Secondary = `--mp-accent`; Text = `--mp-text`; Accent (domyślny kolor
   przycisków) = `--mp-primary`. Zaktualizuj.
4. Customizer → **Opublikuj**.
5. Elementor → Narzędzia → „Regeneruj pliki i dane” (Regenerate CSS & Data). LiteSpeed Cache → Przybornik →
   Wyczyść → „Wyczyść wszystko” (Purge All).
6. Sprawdź jako gość (okno incognito): strona główna, /sklep/, produkt, /kontakt/, /en/eurowet-eng/.
   UCSS może się generować kilka minut. Jeśli po ok. 10 minutach części stylów brakuje tylko gościom —
   **nie** zmieniaj ustawień LiteSpeed; zgłoś mi, których selektorów brakuje.
7. Wyślij raport końcowy.

## Faza 5 — opcjonalnie, tylko po `SZABLONY`
Dla rzeczy z listy „do decyzji”, które da się zrobić samym stylem w szablonach (np. wysokość i układ paska
nagłówka 736, kolumny stopki 772, układ karty produktu 5038):
1. Szablony → Theme Builder → najpierw „Eksportuj szablon” (plik JSON) dla każdego edytowanego szablonu.
2. Zmieniaj tylko zakładki Styl i Zaawansowane (odstępy, wyrównanie, tła, typografia). Nie zmieniaj Treści,
   nie usuwaj widżetów.
3. Zaktualizuj, wykonaj krok 5 z Fazy 4, sprawdź komputer i telefon.

## Raport końcowy
- Co zmieniono: blok CSS, Ustawienia witryny (przed → po), edytowane szablony.
- Tabela tokenów i porównanie z propozycją: co jest 1:1, co przybliżone.
- Lista „do decyzji” (wymaga zmiany treści, struktury lub funkcji).
- Problemy z kontroli jakości i jak je rozwiązano.
- Instrukcja cofnięcia z rzeczywistymi starymi wartościami.

## Jak cofnąć
- CSS: Wygląd → Dostosuj → Dodatkowy CSS → usuń blok od `/* === MP-START` do `=== MP-END === */` oraz linię
  z `MP-FONT` (jeśli jest) → Opublikuj. Albo przywróć treść zapisaną w Fazie 0.
- Elementor → Ustawienia witryny: kolory primary #6EC1E4, secondary #54595F, text #7A7A7A, accent #61CE70;
  fonty Primary Roboto 600, Secondary Roboto Slab 400, Text Roboto 400, Accent Roboto 500; Typografia → Treść
  jak przed zmianą (z dziennika).
- Szablony: Historia wersji w edytorze Elementora albo import JSON z Fazy 5.
- Na koniec: Elementor → „Regeneruj pliki i dane”; LiteSpeed → „Wyczyść wszystko”.

## Załącznik A — odczyt wyglądu strony (uruchom w karcie strony)

```js
(() => {
  const vis = (el) => { const r = el.getBoundingClientRect(); const s = getComputedStyle(el); return r.width > 0 && r.height > 0 && s.display !== 'none'; };
  const first = (sels) => { for (const s of sels) { const el = [...document.querySelectorAll(s)].find(vis); if (el) return el; } return null; };
  const P = ['font-family','font-size','font-weight','line-height','letter-spacing','text-transform','color','background-color','background-image','border','border-radius','box-shadow','padding','height','max-width','position','top'];
  const pick = (el) => { if (!el) return null; const s = getComputedStyle(el); const o = { _tag: el.tagName.toLowerCase(), _class: (el.className && el.className.baseVal === undefined ? String(el.className) : '').slice(0, 80) }; P.forEach(p => { const v = s.getPropertyValue(p); if (v && v !== 'none' && v !== 'normal' && v !== '0px' && v !== 'static' && v !== 'auto') o[p] = v; }); return o; };
  const T = {
    body: ['body'], header: ['header', '[class*="header"]'], headerLink: ['header nav a', 'header a', 'nav a'],
    logo: ['header img', '[class*="logo"] img', '[class*="logo"]'], topbar: ['[class*="topbar"]', '[class*="top-bar"]'],
    h1: ['main h1', 'h1'], h2: ['main h2', 'h2'], h3: ['main h3', 'h3'], h4: ['h4'], lead: ['[class*="lead"]', 'main p'], p: ['main p', 'p'],
    link: ['main p a', 'main a', 'a'], btnPrimary: ['.btn-primary', 'a.btn', '.btn', 'button[type=submit]', '[class*="button"]', 'button'],
    btnSecondary: ['.btn-outline-primary', '.btn-secondary', '[class*="btn-outline"]', '.btn-light'],
    card: ['.card', '[class*="card"]', '[class*="product-item"]', '[class*="product"]', '[class*="tile"]'],
    cardTitle: ['.card-title', '[class*="card"] h3', '[class*="card"] h2', '[class*="product"] h3'],
    badge: ['.badge', '[class*="badge"]', '[class*="label"]', '[class*="tag"]'],
    input: ['input[type=text]', 'input[type=email]', 'input[type=search]', 'textarea', 'select'],
    hero: ['.splide', '[class*="slider"]', '[class*="hero"]', '[class*="banner"]'],
    section: ['main section', 'section'], sectionAlt: ['section[class*="bg"]', '[class*="bg-light"]', '[class*="gray"]', '[class*="grey"]'],
    container: ['.container', '[class*="container"]'], breadcrumb: ['.breadcrumb', '[class*="breadcrumb"]'],
    footer: ['footer', '[class*="footer"]'], footerHeading: ['footer h2', 'footer h3', 'footer h4', 'footer h5', 'footer strong'], footerLink: ['footer a'],
    a11yWidget: ['[class*="accessib"]', '[class*="a11y"]'], cookieBar: ['[class*="cookie"]']
  };
  const elements = {}; for (const k in T) elements[k] = pick(first(T[k]));
  const rootVars = {};
  for (const sh of document.styleSheets) { let rules; try { rules = sh.cssRules; } catch (e) { continue; }
    for (const r of rules || []) { if (r.selectorText && /^(:root|html|body)$/.test(r.selectorText.trim())) { for (const n of r.style) if (n.startsWith('--') && !n.startsWith('--wp--') && Object.keys(rootVars).length < 120) rootVars[n] = r.style.getPropertyValue(n).trim(); } } }
  const bump = (m, k, w) => { if (!k) return; m[k] = (m[k] || 0) + w; };
  const bg = {}, txt = {}, brd = {}, fam = {}, rad = {}, shd = {};
  const all = [...document.querySelectorAll('body *')].filter(vis).slice(0, 6000);
  for (const el of all) { const s = getComputedStyle(el); const r = el.getBoundingClientRect();
    if (s.backgroundColor !== 'rgba(0, 0, 0, 0)') bump(bg, s.backgroundColor, Math.round(Math.min(r.width * r.height, 3e6) / 1000));
    if ([...el.childNodes].some(n => n.nodeType === 3 && n.textContent.trim())) { bump(txt, s.color, 1); bump(fam, s.fontFamily, 1); }
    if (s.borderTopStyle !== 'none' && parseFloat(s.borderTopWidth) > 0) bump(brd, s.borderTopColor, 1);
    if (parseFloat(s.borderTopLeftRadius) > 0) bump(rad, s.borderRadius, 1);
    if (s.boxShadow && s.boxShadow !== 'none') bump(shd, s.boxShadow, 1); }
  const top = (m, n) => Object.entries(m).sort((a, b) => b[1] - a[1]).slice(0, n);
  const outline = [...document.querySelectorAll('body > *, main > *, body > div > *')].filter(el => vis(el) && el.getBoundingClientRect().height > 60).slice(0, 40).map(el => {
    const s = getComputedStyle(el); const h = el.querySelector('h1,h2,h3');
    return { tag: el.tagName.toLowerCase(), cls: String(el.className || '').slice(0, 60), heading: h ? h.textContent.trim().slice(0, 60) : '', height: Math.round(el.getBoundingClientRect().height), bg: s.backgroundColor, bgImg: s.backgroundImage !== 'none', cols: s.display.includes('grid') ? s.gridTemplateColumns : '' };
  });
  const wide = [...document.querySelectorAll('div,section,main')].filter(vis).map(el => getComputedStyle(el).maxWidth).filter(v => v.endsWith('px'));
  return JSON.stringify({
    url: location.href, title: document.title, viewport: innerWidth + 'x' + innerHeight,
    fontsLoaded: [...new Set([...document.fonts].filter(f => f.status === 'loaded').map(f => f.family + ' ' + f.weight))],
    rootVars, elements,
    topBackgrounds: top(bg, 12), topTextColors: top(txt, 10), topBorderColors: top(brd, 6), topFontFamilies: top(fam, 5), topRadii: top(rad, 6), topShadows: top(shd, 5),
    containerMaxWidths: top(wide.reduce((m, v) => (m[v] = (m[v] || 0) + 1, m), {}), 4), outline
  }, null, 1);
})()
```

## Załącznik B — wstawienie bloku CSS do Customizera (uruchom w karcie wp-admin/customize.php)

```js
// Uruchamiać w karcie Wygląd → Dostosuj (wp-admin/customize.php) na eurowet.pl.
// Wstawia albo podmienia blok MP-START…MP-END w "Dodatkowy CSS". Zmiana jest tylko w podglądzie,
// dopóki nie klikniesz "Zapisz szkic" (szkic) lub "Opublikuj" (na żywo).
(() => {
  const CSS = String.raw`/* === MP-START ... (tu cały blok z mediaprod-look.css, z uzupełnionymi tokenami) ... === MP-END === */`;
  // Font do podglądu. Przed publikacją usuń tę linię (FONT = '') i ustaw font w Elementor → Ustawienia witryny (Faza 4).
  const FONT = '@import url("https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap"); /* MP-FONT */';

  if (!window.wp || !wp.customize || !wp.customize.settings || !wp.customize.settings.theme) return 'To nie jest karta Customizera (wp-admin/customize.php).';
  const id = 'custom_css[' + wp.customize.settings.theme.stylesheet + ']';
  const setting = wp.customize(id);
  if (!setting) return 'Brak ustawienia ' + id + '. Otwórz Wygląd → Dostosuj → Dodatkowy CSS.';
  if (/:\s*__\s*;/.test(CSS)) return 'STOP: w bloku zostały nieuzupełnione tokeny "__".';
  const block = /\/\* === MP-START[\s\S]*?=== MP-END === \*\//;
  const fontLine = /^@import[^\n]*\/\* MP-FONT \*\/\n?/m;
  const before = setting.get() || '';
  let next = before.replace(fontLine, '');
  next = block.test(next) ? next.replace(block, CSS.trim()) : (next.trim() ? next.trim() + '\n\n' : '') + CSS.trim() + '\n';
  if (FONT) next = FONT + '\n' + next; // @import musi być pierwszą regułą arkusza
  setting.set(next);
  return 'OK — blok MP ' + (block.test(before) ? 'podmieniony' : 'dodany') + '; poprzednio ' + before.length + ' znaków, teraz ' + next.length + '. Teraz: Zapisz szkic.';
})()
```

## Załącznik C — szkielet CSS dopasowany do eurowet.pl (uzupełnij `__`)

```css
/* === MP-START: wygląd eurowet.pl wg propozycji Media Essence (eurowet.mediaprod.pl) v1 ===
   Cały blok między MP-START i MP-END można usunąć, aby wrócić do poprzedniego wyglądu.
   Zmienia wyłącznie wygląd (kolory, typografia, odstępy, zaokrąglenia, cienie). Bez treści, linków i funkcji.
   Każdą wartość "__" zastąp wartością odczytaną z propozycji (Faza 1). Nie publikuj, dopóki zostało choć jedno "__". */

:root {
  /* Typografia */
  --mp-font: "Inter", system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif; /* rodzina z propozycji */
  --mp-body-size: __;        /* np. 16px */
  --mp-line: __;             /* np. 1.6 */
  --mp-h1: __;               /* rozmiar h1 na desktopie */
  --mp-h2: __;
  --mp-h3: __;
  --mp-heading-weight: __;   /* np. 700 */
  --mp-heading-line: __;     /* np. 1.2 */
  --mp-eyebrow-size: __;     /* mały nadtytuł nad sekcją, np. 13px */
  --mp-eyebrow-spacing: __;  /* np. .08em */
  --mp-nav-size: __;         /* rozmiar linków menu */
  --mp-nav-weight: __;
  --mp-nav-transform: __;    /* none | uppercase */

  /* Kolory */
  --mp-primary: __;          /* kolor główny marki (przyciski, akcenty, linki) */
  --mp-primary-hover: __;
  --mp-on-primary: __;       /* tekst na kolorze głównym */
  --mp-accent: __;           /* drugi akcent, jeśli występuje (inaczej = primary) */
  --mp-heading: __;          /* kolor nagłówków */
  --mp-text: __;             /* kolor tekstu */
  --mp-muted: __;            /* tekst drugorzędny */
  --mp-bg: __;               /* tło strony */
  --mp-bg-alt: __;           /* tło sekcji wyróżnionych / naprzemiennych */
  --mp-surface: __;          /* tło kart */
  --mp-border: __;
  --mp-header-bg: __;
  --mp-header-link: __;
  --mp-header-link-hover: __;
  --mp-footer-bg: __;
  --mp-footer-text: __;
  --mp-footer-heading: __;
  --mp-footer-link: __;
  --mp-footer-link-hover: __;

  /* Kształt i rytm */
  --mp-radius: __;           /* przyciski, pola */
  --mp-radius-card: __;      /* karty, zdjęcia */
  --mp-shadow: __;           /* cień kart */
  --mp-shadow-hover: __;
  --mp-btn-pad: __;          /* np. 14px 28px */
  --mp-btn-weight: __;
  --mp-btn-transform: __;    /* none | uppercase */
  --mp-container: __;        /* szerokość treści, np. 1320px */
  --mp-header-h: __;         /* wysokość paska nagłówka */
}

/* ---------- 1. Typografia bazowa (bez ikon: eicons / Font Awesome zostają) ---------- */
body,
body :is(p, li, td, th, label, input, select, textarea, button, blockquote, figcaption),
body :is(h1, h2, h3, h4, h5, h6, .elementor-heading-title),
body :is(.elementor-button, .elementor-nav-menu a, .elementor-item, .elementor-sub-item, .elementor-counter-title,
  .elementor-counter-number-wrapper, .elementor-flip-box__layer__title, .elementor-flip-box__layer__description,
  .elementor-tab-title, .elementor-tab-content, .elementor-slide-heading, .elementor-slide-description,
  .elementor-icon-list-text, .elementor-post__title, .elementor-post__excerpt, .elementor-post__meta-data, .elementor-post__read-more, .elementor-field,
  .woocommerce-loop-product__title, .price, .product_title, .woocommerce-breadcrumb) {
  font-family: var(--mp-font) !important;
}
body { background-color: var(--mp-bg); color: var(--mp-text); font-size: var(--mp-body-size); line-height: var(--mp-line); }
/* Kolor i rozmiar tekstu ustawiane na widżecie, nie na <p>: sekcje z jawnie ustawionym kolorem (np. biały tekst na ciemnym tle) zachowują go. */
body .elementor-widget-text-editor { color: var(--mp-text); font-size: var(--mp-body-size); line-height: var(--mp-line); }
/* Nagłówki: krój i grubość wymuszone; kolor bez !important, więc kolory ustawione w sekcjach zostają. */
body .elementor-heading-title { line-height: var(--mp-heading-line); letter-spacing: normal; font-weight: var(--mp-heading-weight) !important; }
body .elementor-widget-heading .elementor-heading-title { color: var(--mp-heading); }
/* Tytuły sekcji na stronie głównej: "Producent preparatów…", "Najczęściej kupowane produkty", "dlaczego produkty eurowet?" */
body .elementor-645 :is(.elementor-element-4e02508, .elementor-element-45e13bc, .elementor-element-d11bb99) .elementor-heading-title {
  font-size: var(--mp-h2) !important; color: var(--mp-heading) !important; text-transform: none !important;
}
/* Nazwy produktów w kafelkach bestsellerów (część to h2, część h3 — wyrównane do jednego rozmiaru) */
body .elementor-645 :is(.elementor-element-ab95347, .elementor-element-22c3eaa, .elementor-element-41f7cbf,
  .elementor-element-4c3abbb, .elementor-element-0f6cc1b, .elementor-element-0e43b9a) .elementor-heading-title {
  font-size: var(--mp-h3) !important; color: var(--mp-heading) !important; text-transform: none !important;
}
body a { color: var(--mp-primary); }
body a:hover, body a:focus { color: var(--mp-primary-hover); }
body .elementor-widget-text-editor a { text-decoration: underline; text-underline-offset: .15em; }

/* Strona główna (645): wzór "mały h1 (nadtytuł) + kreska + h2" jako nadtytuł w kolorze marki */
body .elementor-645 h1.elementor-heading-title.elementor-size-default {
  font-size: var(--mp-eyebrow-size) !important; font-weight: 600 !important; letter-spacing: var(--mp-eyebrow-spacing) !important;
  text-transform: uppercase !important; color: var(--mp-primary) !important;
}
body .elementor-645 .elementor-widget-divider .elementor-divider-separator { border-color: var(--mp-primary) !important; }

/* ---------- 2. Nagłówek (szablon Theme Builder 736) ---------- */
body .elementor-location-header { background-color: var(--mp-header-bg); box-shadow: 0 1px 0 var(--mp-border); position: relative; z-index: 50; }
body .elementor-location-header > .elementor-section,
body .elementor-location-header > .e-con { background-color: var(--mp-header-bg) !important; }
body .elementor-location-header .elementor-widget-theme-site-logo img { max-height: calc(var(--mp-header-h) - 32px); width: auto; }
body .elementor-location-header .elementor-nav-menu--main .elementor-item {
  color: var(--mp-header-link) !important; font-size: var(--mp-nav-size) !important; font-weight: var(--mp-nav-weight) !important;
  text-transform: var(--mp-nav-transform) !important; letter-spacing: normal !important;
}
body .elementor-location-header .elementor-nav-menu--main .elementor-item:is(:hover, :focus, .elementor-item-active, .highlighted) { color: var(--mp-header-link-hover) !important; }
body .elementor-location-header .elementor-nav-menu--main .elementor-item::before,
body .elementor-location-header .elementor-nav-menu--main .elementor-item::after { background-color: var(--mp-primary) !important; }
body .elementor-location-header .elementor-nav-menu--dropdown {
  background-color: var(--mp-surface) !important; border-radius: var(--mp-radius-card); box-shadow: var(--mp-shadow); border: 1px solid var(--mp-border);
}
body .elementor-location-header .elementor-nav-menu--dropdown a { color: var(--mp-text) !important; background-color: transparent !important; }
body .elementor-location-header .elementor-nav-menu--dropdown a:is(:hover, :focus, .elementor-item-active) { color: var(--mp-primary) !important; background-color: var(--mp-bg-alt) !important; }
body .elementor-location-header .elementor-menu-toggle { color: var(--mp-header-link) !important; background-color: transparent !important; }
body .elementor-location-header .elementor-menu-cart__toggle .elementor-button {
  background-color: transparent !important; color: var(--mp-header-link) !important; border: 1px solid var(--mp-border) !important; border-radius: var(--mp-radius) !important;
}
body .elementor-location-header .elementor-menu-cart__toggle .elementor-button-icon[data-counter]::before { background-color: var(--mp-primary) !important; color: var(--mp-on-primary) !important; }
body .elementor-location-header .search-form .search-field { border: 1px solid var(--mp-border) !important; border-radius: var(--mp-radius) !important; background: var(--mp-bg) !important; color: var(--mp-text) !important; }
body .elementor-location-header .search-form .search-submit { border-radius: var(--mp-radius) !important; }

/* ---------- 3. Przyciski (Elementor + WooCommerce + wyszukiwarka) ---------- */
body :is(.elementor-button:not(.elementor-menu-cart__toggle .elementor-button), .elementor-slide-button,
  .woocommerce a.button, .woocommerce button.button, .woocommerce input.button, .woocommerce #respond input#submit,
  .woocommerce .single_add_to_cart_button, .woocommerce .checkout-button, .search-submit, .elementor-field-type-submit button) {
  background-color: var(--mp-primary) !important; color: var(--mp-on-primary) !important; border: 0 !important;
  border-radius: var(--mp-radius) !important; padding: var(--mp-btn-pad) !important; font-weight: var(--mp-btn-weight) !important;
  text-transform: var(--mp-btn-transform) !important; letter-spacing: normal !important; box-shadow: none !important;
  transition: background-color .2s ease, transform .2s ease;
}
body :is(.elementor-button:not(.elementor-menu-cart__toggle .elementor-button), .elementor-slide-button,
  .woocommerce a.button, .woocommerce button.button, .woocommerce input.button, .woocommerce .single_add_to_cart_button,
  .woocommerce .checkout-button, .search-submit, .elementor-field-type-submit button):is(:hover, :focus-visible) {
  background-color: var(--mp-primary-hover) !important; color: var(--mp-on-primary) !important;
}

/* ---------- 4. Sekcje i karty (treść stron Elementora) ---------- */
body .elementor-section.elementor-section-boxed > .elementor-container { max-width: var(--mp-container); }
/* Kafelki produktów na stronie głównej (rzędy bestsellerów: obraz + nagłówek + tekst w kolumnie) */
body .elementor-645 :is(.elementor-element-ab95347, .elementor-element-22c3eaa, .elementor-element-41f7cbf,
  .elementor-element-4c3abbb, .elementor-element-0f6cc1b, .elementor-element-0e43b9a) .elementor-column > .elementor-widget-wrap {
  background-color: var(--mp-surface); border: 1px solid var(--mp-border); border-radius: var(--mp-radius-card);
  box-shadow: var(--mp-shadow); margin: 12px; padding: 24px; transition: box-shadow .2s ease, transform .2s ease;
}
body .elementor-645 :is(.elementor-element-ab95347, .elementor-element-22c3eaa, .elementor-element-41f7cbf,
  .elementor-element-4c3abbb, .elementor-element-0f6cc1b, .elementor-element-0e43b9a) .elementor-column > .elementor-widget-wrap:hover {
  box-shadow: var(--mp-shadow-hover); transform: translateY(-2px);
}
body .elementor-widget-image img { border-radius: var(--mp-radius-card); }
body .elementor-widget-flip-box :is(.elementor-flip-box__front, .elementor-flip-box__back) { border-radius: var(--mp-radius-card); overflow: hidden; }
body .elementor-widget-accordion .elementor-accordion-item { border-color: var(--mp-border) !important; }
body .elementor-widget-accordion .elementor-tab-title { color: var(--mp-heading) !important; }
body .elementor-widget-accordion .elementor-tab-title.elementor-active { color: var(--mp-primary) !important; }
body .elementor-widget-counter .elementor-counter-number-wrapper { font-weight: var(--mp-heading-weight) !important; }
/* Slider główny (widget Slides) — tylko typografia i przycisk; zdjęcia i teksty bez zmian */
body .elementor-widget-slides .elementor-slide-heading { font-weight: var(--mp-heading-weight) !important; letter-spacing: normal !important; }
body .elementor-widget-slides .swiper-pagination-bullet-active { background-color: var(--mp-primary) !important; }
/* Wpisy (Aktualności, archiwum 842, widget Posts) */
body .elementor-posts .elementor-post__card { border-radius: var(--mp-radius-card) !important; box-shadow: var(--mp-shadow) !important; border: 1px solid var(--mp-border) !important; }
body .elementor-post__title a { color: var(--mp-heading) !important; }
body .elementor-post__read-more { color: var(--mp-primary) !important; }

/* ---------- 5. WooCommerce (archiwum 5048, produkt 5038, koszyk, zamówienie) ---------- */
/* Karty produktów; kafelki kategorii (li.product-category, 11 kolumn w /sklep/) pomijamy, bo dodatkowy padding rozpycha siatkę */
body.woocommerce ul.products li.product:not(.product-category), body .woocommerce ul.products li.product:not(.product-category) {
  background-color: var(--mp-surface); border: 1px solid var(--mp-border); border-radius: var(--mp-radius-card);
  box-shadow: var(--mp-shadow); padding: 16px !important; transition: box-shadow .2s ease, transform .2s ease;
}
body .woocommerce ul.products li.product:not(.product-category):hover { box-shadow: var(--mp-shadow-hover); transform: translateY(-2px); }
body .woocommerce ul.products li.product img { border-radius: calc(var(--mp-radius-card) - 4px); }
body .woocommerce ul.products li.product :is(.woocommerce-loop-product__title, .woocommerce-loop-category__title) { color: var(--mp-heading) !important; font-weight: 600 !important; }
body .woocommerce :is(.price, .amount), body .elementor-widget-woocommerce-product-price .price { color: var(--mp-heading) !important; font-weight: 700; }
body .woocommerce span.onsale { background-color: var(--mp-accent) !important; color: var(--mp-on-primary) !important; border-radius: var(--mp-radius) !important; }
body .woocommerce-breadcrumb, body .woocommerce-breadcrumb a { color: var(--mp-muted) !important; }
body .woocommerce div.product .woocommerce-tabs ul.tabs li { border-radius: var(--mp-radius) var(--mp-radius) 0 0 !important; border-color: var(--mp-border) !important; background: var(--mp-bg-alt) !important; }
body .woocommerce div.product .woocommerce-tabs ul.tabs li.active { background: var(--mp-surface) !important; }
body .woocommerce div.product .woocommerce-tabs ul.tabs li a { color: var(--mp-heading) !important; }
body .woocommerce :is(input.input-text, select, textarea, .quantity .qty),
body .elementor-field-group :is(.elementor-field, input, select, textarea) {
  border: 1px solid var(--mp-border) !important; border-radius: var(--mp-radius) !important; background-color: var(--mp-bg) !important; color: var(--mp-text) !important;
}
body .woocommerce-message, body .woocommerce-info { border-top-color: var(--mp-primary) !important; }
body .woocommerce-message::before, body .woocommerce-info::before { color: var(--mp-primary) !important; }

/* ---------- 6. Stopka (szablon Theme Builder 772) ---------- */
body .elementor-location-footer > :is(.elementor-section, .e-con) { background-color: var(--mp-footer-bg) !important; }
body .elementor-location-footer { color: var(--mp-footer-text); }
body .elementor-location-footer .elementor-heading-title { color: var(--mp-footer-heading) !important; text-transform: none !important; font-weight: 600 !important; }
body .elementor-location-footer :is(p, .elementor-icon-list-text, .elementor-post__excerpt) { color: var(--mp-footer-text) !important; }
body .elementor-location-footer a, body .elementor-location-footer .elementor-nav-menu a { color: var(--mp-footer-link) !important; }
body .elementor-location-footer a:is(:hover, :focus) { color: var(--mp-footer-link-hover) !important; }
body .elementor-location-footer .elementor-icon-list-icon :is(i, svg) { color: var(--mp-footer-link-hover) !important; fill: var(--mp-footer-link-hover) !important; }
body .elementor-location-footer .elementor-social-icon { border-radius: 50% !important; }
/* Karty wpisów w stopce (widget Posts "News") mają jasne tło — tytuł i metadane w kolorach treści, nie stopki */
body .elementor-location-footer .elementor-post__card .elementor-post__title a { color: var(--mp-heading) !important; }
body .elementor-location-footer .elementor-post__card :is(.elementor-post__meta-data, .elementor-post__excerpt, .elementor-post__excerpt p) { color: var(--mp-muted) !important; }
body .elementor-location-footer .elementor-field { background-color: rgba(255, 255, 255, .08) !important; color: var(--mp-footer-text) !important; border: 1px solid rgba(255, 255, 255, .2) !important; }

/* ---------- 7. Dostępność: widoczny fokus ---------- */
body :is(a, button, input, select, textarea, .elementor-button):focus-visible { outline: 2px solid var(--mp-primary) !important; outline-offset: 2px; }

/* ---------- 8. Tablet i telefon ---------- */
@media (max-width: 1024px) {
  body .elementor-location-header .elementor-nav-menu--dropdown { border-radius: 0; box-shadow: var(--mp-shadow); }
}
@media (max-width: 767px) {
  body .elementor-645 :is(.elementor-element-4e02508, .elementor-element-45e13bc, .elementor-element-d11bb99) .elementor-heading-title { font-size: calc(var(--mp-h2) * .78) !important; }
  body .elementor-645 :is(.elementor-element-ab95347, .elementor-element-22c3eaa, .elementor-element-41f7cbf,
    .elementor-element-4c3abbb, .elementor-element-0f6cc1b, .elementor-element-0e43b9a) .elementor-column > .elementor-widget-wrap { margin: 8px 0; padding: 16px; }
}
/* === MP-END === */
```

---------------------------------------- KONIEC PROMPTU ----------------------------------------
