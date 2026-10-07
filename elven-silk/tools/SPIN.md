# Obrót 360° — jak dorobić klatki dla zestawienia

Strona jest gotowa na obrót dla **każdego zestawienia kolor × oprawa, z koroną i bez**
(13 × 5 × 2 = 130). Klatek na razie nie generowano. Zanim ruszysz, właścicielka zatwierdza
statyczne podglądy `preview-*-900.webp`, bo to one są klatką startową.

## Nazwy plików

```
assets/cfg/spin-{klucz}-00.webp … spin-{klucz}-23.webp
klucz = {kolor}-{oprawa}  albo  {kolor}-{oprawa}-korona
```

- `{kolor}` — `id` z `COLORS` (`czerwony`, `roz`, `pudrowy`, `morelowy`, `zolty`, `kremowy`,
  `bialy`, `zielony`, `blekit`, `granat`, `jasfiolet`, `fiolet`, `czarny`)
- `{oprawa}` — `id` z `SLEEVE` (`brak`, `org-biala`, `org-czarna`, `org-rozowa`, `org-blekit`)
- 24 klatki co 15°, kwadrat 760 px, ten sam kadr i światło co `preview-{klucz}-900.webp`

Po wrzuceniu plików dopisz jedną linijkę do `SPIN_SETS` w `page.html`:

```js
var SPIN_SETS = {
  "granat-org-czarna": {},            // ← nowy obrót bez korony
  "granat-org-czarna-korona": {}      // ← i z koroną (osobne klatki)
};
```

Potem `node build.js`. Nic więcej — kanwa przejmuje kadr dopiero, gdy wczyta się komplet
24 klatek; przy braku choćby jednej zostaje zwykłe zdjęcie. `prefix` we wpisie pozwala
użyć innej nazwy plików niż `spin-{klucz}`.

## Generowanie (Higgsfield)

1. Klatka startowa: `assets/cfg/preview-{klucz}-900.webp` — **z wydrukowanym logo**.
   Wgraj ją do Higgsfield (nie używaj surowego job_id z `tools/podglady/jobs.tsv`, bo tam
   logo jeszcze nie ma).
2. Model **Seedance 2.5**, `mode: omni_reference`, 10 s, 1:1, **1080p**, bez dźwięku.
3. Prompt — opisuje **nieruchomy** bukiet i **wyłącznie** ruch kamery:

   > The bouquet from the reference image is held completely still in the same place, with
   > the same background and the same light (it is a casual phone video). The camera makes
   > one smooth, full 360-degree orbit around the bouquet at constant speed and height,
   > always centred on it, ending exactly where it started. No zoom, no cuts; the printed
   > logos on the wrapping sheets stay exactly as in the reference; nothing in the scene
   > moves except the camera.

   Ręka trzymająca bukiet utrudnia pełny obrót — jeśli model się gubi, poproś o obrót
   o ±40° (klatki 00–23 pokryją wtedy łuk zamiast koła, kod tego nie sprawdza).
4. Pobierz wideo i potnij: `tools/spin-frames.sh wideo.mp4 granat org-czarna`
   (systemowy ffmpeg z libwebp; ffmpeg z Playwrighta tego nie umie).
5. Obejrzyj logo w kilku klatkach — model wideo potrafi je rozmyć. Klatki z krzywym logo
   odrzuć i wygeneruj ujęcie jeszcze raz.
6. Dopisz klucz do `SPIN_SETS`, zbuduj, sprawdź w przeglądarce: przeciągnięcie myszą
   i strzałki ←/→ na podglądzie.

Kluczowe jest **jedno** ujęcie orbitujące zamiast 24 osobnych generacji — niezależne
generacje dają 24 różne bukiety.

## Koszt

Wycena Higgsfield z 7 października 2026: Seedance 2.5, 10 s — **70 kredytów przy 720p**,
ok. **90 przy 1080p**. Cięcie klatek jest bezpłatne.

| Zakres | Ujęć | Kredyty (1080p) |
|---|---|---|
| jedno zestawienie na próbę | 1 | ~90 |
| 13 kolorów z białą organzą | 13 | ~1 170 |
| 13 kolorów z białą organzą, z koroną i bez | 26 | ~2 340 |
| pełna macierz 13 × 5 × 2 | 130 | ~11 700 |

Rozsądny start: jedno zestawienie na próbę, ocena jakości, potem kolory z jedną oprawą.

## Dodatki w trybie obrotu

Korona jest na samych zdjęciach (`…-korona`), więc obrót z koroną to osobny komplet klatek.
Pozostałe dodatki widać jako miniatury obok podglądu — w trybie obrotu też zostają.

## Stary obrót czerwieni

`spin-czerwony-00..23.webp` powstał ze studyjnego renderu z wtopioną koroną. Nie pasuje do
zdjęć „z telefonu”, więc jest wyrejestrowany (`SPIN_SETS` jest puste). Pliki zostały w repo.

## Skąd są zdjęcia bazowe

`tools/podglady/README.md` — sceny, prompty, nadruk logo, job ID generacji.
