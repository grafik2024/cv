# Obrót 360° — jak dorobić klatki dla kolejnego zestawienia

Strona jest gotowa na obrót dla **każdego zestawienia kolor róż × oprawa** (13 × 5 = 65).
Klatek na razie nie generowano — poza starym obrotem czerwieni (patrz niżej). Zanim ruszysz,
właścicielka zatwierdza statyczne podglądy `preview-*-*-900.webp`, bo to one są klatką startową.

## Nazwy plików

```
assets/cfg/spin-{kolor}-{oprawa}-00.webp … spin-{kolor}-{oprawa}-23.webp
```

- `{kolor}` — `id` z `COLORS` (`czerwony`, `roz`, `pudrowy`, `morelowy`, `zolty`, `kremowy`,
  `bialy`, `zielony`, `blekit`, `granat`, `jasfiolet`, `fiolet`, `czarny`)
- `{oprawa}` — `id` z `SLEEVE` (`brak`, `org-biala`, `org-czarna`, `org-rozowa`, `org-blekit`)
- 24 klatki co 15°, kwadrat 760 px, ten sam kadr i światło co `preview-{kolor}-{oprawa}-900.webp`

Po wrzuceniu plików dopisz jedną linijkę do `SPIN_SETS` w `page.html`:

```js
var SPIN_SETS = {
  "czerwony-org-biala": {prefix:"spin-czerwony", needs:"korona"},   // stary obrót
  "granat-org-czarna": {}                                            // ← nowy
};
```

Potem `node build.js`. Nic więcej — kanwa przejmuje kadr dopiero, gdy wczyta się komplet
24 klatek; przy braku choćby jednej zostaje zwykłe zdjęcie.

## Generowanie (Higgsfield)

1. Klatka startowa: `assets/cfg/preview-{kolor}-{oprawa}-900.webp` (bez korony).
   Wgraj ją do Higgsfield albo użyj `job_id` generacji z tabeli niżej.
2. Model **Seedance 2.5**, `mode: omni_reference`, 10 s, 1:1, **1080p**, bez dźwięku.
3. Prompt — opisuje **nieruchomy** bukiet i **wyłącznie** ruch kamery:

   > The bouquet from the reference image stands completely still on the same warm beige-grey
   > studio background. The camera makes one smooth, full 360-degree orbit around it at
   > constant speed and constant height, always centred on the bouquet, ending exactly where
   > it started. Same lighting, same framing, no zoom, no cuts, no hands, no other objects,
   > nothing in the scene moves except the camera.

4. Pobierz wideo i potnij: `tools/spin-frames.sh wideo.mp4 granat org-czarna`
   (systemowy ffmpeg z libwebp; ffmpeg z Playwrighta tego nie umie).
5. Dopisz klucz do `SPIN_SETS`, zbuduj, sprawdź w przeglądarce: przeciągnięcie myszą
   i strzałki ←/→ na podglądzie.

Kluczowe jest **jedno** ujęcie orbitujące zamiast 24 osobnych generacji — niezależne
generacje dają 24 różne bukiety.

## Koszt

Wycena Higgsfield z 7 października 2026: Seedance 2.5, 10 s — **70 kredytów przy 720p**,
ok. **90 przy 1080p** (tyle kosztował obrót czerwieni). Cięcie klatek jest bezpłatne.

| Zakres | Ujęć | Kredyty (1080p) |
|---|---|---|
| jedno zestawienie na próbę | 1 | ~90 |
| 13 kolorów z białą organzą | 13 | ~1 170 |
| 13 kolorów × 2 najpopularniejsze oprawy | 26 | ~2 340 |
| pełna macierz 13 × 5 | 65 | ~5 850 |

Rozsądny start: jedno zestawienie na próbę, ocena jakości, potem kolory z jedną oprawą.

## Dodatki w trybie obrotu

Zdjęcia bazowe są **bez korony** — korona, światełka LED i brokat to osobne warstwy
nakładane na zdjęcie. Warstwy się nie obracają, więc w trybie obrotu są schowane
(klasa `.is-spin` na `.preview`); miniatury dodatków po prawej zostają widoczne.

Jeśli kiedyś obrót ma pokazywać koronę, wygeneruj osobny komplet z koroną w kadrze
i opisz go tak jak stary obrót: `{prefix:"spin-…-korona", needs:"korona"}` — wtedy włącza się
tylko, gdy klientka wybrała koronę.

## Stary obrót czerwieni

`spin-czerwony-00..23.webp` powstał z poprzedniego zdjęcia czerwieni, które miało koronę
i białą organzę „wtopione" w kadr. Dlatego jest zarejestrowany jako
`"czerwony-org-biala": {prefix:"spin-czerwony", needs:"korona"}` i włącza się wyłącznie
przy tym zestawieniu z zaznaczoną koroną. Korona w obrocie ma inny wzór niż nakładka na
zdjęciu — przy generowaniu nowego kompletu dla czerwieni warto go podmienić.

## Skąd są zdjęcia bazowe

Wszystkie 65 podglądów powstało w Higgsfield (Nano Banana Pro, 1k) z jednego wzorca:
`preview-czerwony-900.webp` → usunięta korona → 4 warianty oprawy → przekolorowanie na
12 kolorów. Dzięki temu kadr jest ten sam we wszystkich plikach — przełączanie koloru
i oprawy nie przesuwa bukietu, a warstwa korony pasuje do każdego.

Identyfikatory generacji czerwieni (do użycia jako klatka startowa bez ponownego wgrywania):

| Plik | job_id |
|---|---|
| preview-czerwony-org-biala | `180729cc-b6f8-44a1-85f2-b6d4830c74c3` |
| preview-czerwony-brak | `60a3d96c-7ed5-4813-97de-bdef3901cb4b` |
| preview-czerwony-org-czarna | `56c7a082-1188-414b-801f-f0eab69dd793` |
| preview-czerwony-org-rozowa | `ca28f36b-27ce-485a-b8b8-913b57f72e6b` |
| preview-czerwony-org-blekit | `1f68a438-45d5-445c-80b1-c717dec3a6cf` |
