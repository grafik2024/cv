# Podglądy konfiguratora — jak powstały i jak dorobić kolejne

130 plików `assets/cfg/preview-{kolor}-{oprawa}[-korona]-900.webp`:
13 kolorów × 5 opraw (`brak`, `org-biala`, `org-czarna`, `org-rozowa`, `org-blekit`) × bez/z koroną.

**Każdy kolor ma własną scenę i okazję w roku** (Walentynki nocą, jabłoń w maju, Bałtyk
w sierpniu, liście w październiku, jarmark w śniegu…). Lista: `SCENES.md`, a opis okazji
pokazuje się nad nazwą bukietu w podglądzie (pole `occ` w `COLORS`, `page.html`).
Zmieniasz scenę koloru — zmień też `occ`.

W obrębie jednej sceny wszystkie warianty mają **ten sam kadr co do piksela** (sprawdzane
korelacją fazową), więc przełączanie organzy i korony nie przesuwa bukietu.

## Kolejność kroków

1. **Scena bazowa** (biała organza) — GPT Image 2.5, `quality: high`, 1k, 1:1.
   Referencje: wzorzec produktu `1ca58baa-…` (zatwierdzona czerwień w złotej godzinie,
   zrobiona z prawdziwego zdjęcia) + prawdziwe zdjęcie właścicielki jako wzór „stylu
   telefonu” (`ebfa30af-…` w dzień, `2aa1b46a-…` wieczorem). Szablon promptu niżej.
2. **Warianty oprawy** — Nano Banana 2 (1k) edytuje bazę: czarna / jasnoróżowa /
   jasnoniebieska organza, wersja bez organzy (łodygi owinięte wstążką + **pusta**
   zawieszka), korona (druga referencja: zdjęcie tiary `a403c809-…`). Przy czarnej organzie
   dopisz, że róże **nie są** przykryte folią — inaczej model je przyciemnia.
3. **Składanie** — `PODGLADY_SRC=<katalog z PNG> python3 zloz.py ../../assets/cfg [kolor …]`:
   - koronę z jednego zdjęcia (biała organza + korona) przenosi maską na pozostałe organzy
     (`crown.py`), wersja „bez organzy + korona” to osobna generacja;
   - **drukuje prawdziwe logo** z `assets/logo-lockup.png` (`lp.py`) na co drugim arkuszu,
     wzdłuż arkusza, nigdy w poprzek krawędzi: ciemna farba na jasnych arkuszach, jasna
     na czarnej organzie; to, co leży na arkuszu (wstążka, palec), zasłania nadruk;
   - w wersji bez organzy logo idzie na zawieszkę (`tag.py` sam ją znajduje, ręczne
     poprawki w `TAG_FIX` w `zloz.py`); logo, które AI narysowało na czerwonej karteczce,
     jest zamazywane (`card.py`) i zastępowane prawdziwym.

Współrzędne nadruku (`spots.json`, `tags.json`, `size-spots.json`) są w skali 1024 px:
`[środek x, środek y, obrót w stopniach przeciwnie do wskazówek, szerokość, (skrót pionowy),
(pochylenie)]`. Nowe miejsca dobieraj na siatce: `python3 grid.py plik.png siatka.jpg`,
a potem obejrzyj każde logo w powiększeniu — logo przecinające krawędź arkusza odrzuć.

Job ID wszystkich użytych generacji: `jobs.tsv` (można ich użyć jako referencji
w Higgsfield bez ponownego wgrywania).

## Szablon promptu sceny (GPT Image 2.5)

> Image 1 shows the product: a handmade bouquet of about 25 satin-ribbon roses in a dome,
> wrapped in square sheets of translucent matte white organza film with the corners pointing
> outward, and thin satin ribbons tied in small bows on the sheet edges. Image 2 is only
> a reference for the photo style of the shop owner's own phone pictures — copy its casual,
> imperfect, real look, not its objects.
> Recolor the roses and the ribbons to **{kolor z hex}**. The wrapping sheets stay white and
> plain: no logo, no print, no text, no tag.
> New photo. SCENE: **{okazja, miejsce, światło}**. ANGLE: **{skąd ręka, pod jakim kątem}**.
> Make it a quick, casual snapshot the shop owner took with her phone, not a professional or
> AI-perfect image: the bouquet is a little off-center and one sheet corner is cut off by the
> frame edge; the camera is slightly tilted; the organza sheets are softly creased and
> wrinkled, unevenly layered, not symmetrical; the ribbons are a bit twisted and of uneven
> length; the handmade roses vary slightly in size and openness; ordinary smartphone
> processing; no glow, no HDR, no perfect symmetry, no cinematic bokeh. No text, no watermark.

Logo **nie** jest generowane przez AI — modele rysują je krzywo. Zawsze nadruk z pliku.

## Koszt (7 października 2026)

GPT Image 2.5 high 1k: 1,5 kredytu. Nano Banana 2 1k: 1,5 kredytu. Jedna scena z kompletem
wariantów: 1 baza + 4 oprawy + korona + „bez organzy + korona” ≈ 10,5 kredytu.
GPT ma limit równoległych zapytań — wysyłaj po 3–4 naraz.
