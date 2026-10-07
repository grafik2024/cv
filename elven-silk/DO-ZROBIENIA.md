# Elven Silk — co zostało do zrobienia (stan na 7 października 2026)

Lista dla właścicielki. Każdy punkt ma miejsce w kodzie: plik i numer linii w `page.html`
(numery przesuną się przy edycji — wtedy szukaj po kotwicy w nawiasie). Wszystkie
brakujące dane w treści są oznaczone żółtym tłem (`class="todo"`) — jest ich 30,
szukaj `class="todo"`. **Po każdej zmianie w `page.html`: `node build.js`.**

To nie jest porada prawna. Przegląd obejmuje przepisy konsumenckie, RODO, AI Act
i e-commerce; przy punktach oznaczonych ⚖️ warto potwierdzić u prawnika
albo w Rzeczniku Konsumentów / urzędzie skarbowym.

---

## A. Musisz uzupełnić, zanim strona pójdzie na produkcję

1. **Imię i nazwisko sprzedawcy** — `BIZ.legal`, linia ~1689 (`var BIZ = {`) oraz
   żółte `[imię i nazwisko]` w stopce (~1604), regulaminie (§1, ~2784), polityce
   prywatności (administrator, ~2960), formularzu odstąpienia (~3197) i GPSR (~3315) —
   **w trzech językach** (PL / EN / UA). Bez tego strona nie spełnia art. 12 ustawy
   o prawach konsumenta ani art. 19 GPSR: klient musi wiedzieć, kto sprzedaje.
2. **Prawdziwe ceny** — `ROSE_TIERS` (~1700) to jedyne źródło ceny róży; dopłaty
   w `COLORS` (~1723), `SLEEVE`, `WRAPS` (~1757), `ADDONS` (~1770). Ceny dostawy
   `SHIPPING` (~1786) są prawdziwe dla nadawcy indywidualnego; przy umowie z Furgonetką
   podmień. Próg darmowej dostawy `FREE_FROM` (~1808).
3. **`DRAFT_MODE = false`** (~1668) — dopiero po punktach 1–2. Wyłącza żółty pasek
   „Tryb roboczy”.
4. **FormSubmit — aktywacja.** Po pierwszym wysłanym formularzu przyjdzie na
   `elvensilk@wp.pl` mail z linkiem. Bez kliknięcia zapytania nie dochodzą
   (`ORDER_ENDPOINT`, ~1674).
5. **Polityka prywatności — odbiorcy danych** (`prywatnosc`, ~2954, sekcja „Komu
   przekazuję dane”): biuro rachunkowe (jeśli jest), dostawca hostingu (po wyborze
   hostingu) — w trzech językach.
6. **⚖️ FormSubmit a RODO.** Formularz zamówienia przechodzi przez zewnętrzną usługę
   FormSubmit. Jest już wpisana do polityki prywatności, ale z żółtą notatką: trzeba ustalić,
   gdzie przetwarza dane i czy da się z nią zawrzeć umowę powierzenia (art. 28 RODO).
   Jeśli nie — zmień `ORDER_ENDPOINT` na dostawcę z umową powierzenia albo na własny
   skrypt na hostingu (formularz wysyła zwykły JSON POST-em, zmiana to jedna linijka).
7. **Tabela cookies** (`cookies`, ~3098) — wpisane są GA4 i Meta Pixel, których strona
   **dziś nie uruchamia**. Usuń wiersze narzędzi, których nie używasz, albo podepnij je
   w `applyConsent()` (instrukcja w `README.md`, sekcja „Zgody na cookies”).
8. **Deklaracja dostępności** (`dostepnosc`, ~3386) — żółte zdanie o mikroprzedsiębiorcy:
   potwierdź, że to Ciebie dotyczy (wtedy Europejski Akt o Dostępności Cię nie obejmuje).

## B. Decyzje i ryzyka, które zostają po Twojej stronie

9. **⚖️ Limit działalności nierejestrowanej.** Od 2026 r. limit liczony jest kwartalnie:
   **10 813,50 zł przychodu na kwartał** (225% minimalnego wynagrodzenia 4806 zł).
   Jeden bukiet ze 101 róż to 1111 zł — dziesięć takich w kwartale przekracza limit.
   Po przekroczeniu działalność staje się firmą z mocy prawa i masz **7 dni** na wpis do
   CEIDG; wtedy trzeba też zmienić teksty o „działalności nierejestrowanej” (regulamin §1,
   polityka prywatności, GPSR, stopka, `llms.txt` w `build.js`). Sekcja B2B
   (`#wspolpraca`, ~1439) obiecuje hurt — duże zamówienia przyspieszają przekroczenie.
   Prowadź uproszczoną ewidencję sprzedaży.
10. **⚖️ Adres = punkt Furgonetka.** Ustawa o prawach konsumenta i GPSR wymagają adresu,
    pod którym da się Ciebie realnie zastać / doręczyć korespondencję i reklamacje.
    Upewnij się, że punkt Furgonetka przyjmuje dla Ciebie listy i paczki zwrotne na
    piśmie — inaczej podaj własny adres korespondencyjny (może być wirtualne biuro).
11. **⚖️ Wyłączenie prawa odstąpienia** (art. 38 ust. 1 pkt 3, checkbox `sum.ack1`, ~1273,
    dokument `zwroty`, ~3175). Bukiet robiony po zamówieniu według wyborów klientki
    zwykle mieści się w tym wyjątku, ale UOKiK czyta go wąsko: sam wybór z gotowej listy
    wariantów bywa uznawany za niewystarczający. Najmocniejsza pozycja: imię na wstążce,
    indywidualny bilecik, nietypowa liczba róż. Ten zapis został bez zmian.
12. **⚖️ Potwierdzenie zamówienia z obowiązkiem zapłaty.** Formularz na stronie jest teraz
    konsekwentnie „zapytaniem” (przycisk „Wyślij zapytanie do atelier”, regulamin §4).
    Umowa zawiera się dopiero, gdy klientka potwierdzi Twoją wycenę. W mailu z wyceną
    poproś o odpowiedź w rodzaju: „Potwierdzam zamówienie z obowiązkiem zapłaty”
    (art. 17 ustawy o prawach konsumenta) — i dopiero po niej wyślij potwierdzenie
    z pouczeniem o odstąpieniu.
13. **Znaki towarowe na zdjęciach galerii** (`GALLERY`, ~1868) — na części zdjęć widać
    wstążki z logo Diora i maskotki Disneya. To realne ryzyko przy stronie sprzedażowej.
    Docelowo: zdjęcia z własnymi wstążkami Elven Silk.
14. **⚖️ LED i baterie, opakowania — BDO.** Bukiet z modułem LED na baterie guzikowe to
    sprzęt elektryczny z bateriami; wysyłka w kartonach to produkty w opakowaniach.
    Sprawdź, czy ciążą na Tobie obowiązki rejestracji w BDO (sprzęt, baterie, opakowania)
    — przy działalności nierejestrowanej sprawa nie jest oczywista. Zachowaj dokumenty
    (deklarację CE) od dostawcy modułów LED.
15. **GPSR — dokumentacja wewnętrzna.** Strona ma komplet ostrzeżeń (`gpsr`, ~3309).
    Rozporządzenie wymaga też, żebyś miała u siebie krótką analizę ryzyka i dokumentację
    techniczną (z czego robisz, jakie elementy drobne, skąd baterie). Wystarczy prosty
    dokument — nie publikuje się go.
16. **Sekcja B2B** (`#wspolpraca`, ~1439, punkty `b2b.1h`…) — progi rabatowe i warunki to
    projekt. Zweryfikuj przed publikacją.
17. **„Biała organza” w dwóch krokach** — w kroku 3 „Oprawa” i w kroku 4 „Opakowanie”.
    Podgląd pokazuje teraz organzę z kroku 3. Jeśli krok 4 nie powinien jej mieć, usuń wpis
    `organza` z `WRAPS` (~1757) i zmień `DEFAULT.wrap`.

## C. Zatwierdź grafiki

18. **Nowe podglądy** — 130 plików `assets/cfg/preview-{kolor}-{oprawa}[-korona]-900.webp`.
    Każdy kolor ma własną scenę i okazję w roku (lista: `tools/podglady/SCENES.md`), a na
    stronie nad nazwą bukietu stoi „Pomysł na: …”. Sprawdź:
    - czy okazje pasują do Twojej oferty (np. Halloween dla czarnych, Dzień Chłopaka dla
      granatu) — opis zmienia się w polu `occ` koloru w `COLORS` (`page.html`), bez grafik;
    - czy na czarnej organzie nadruk ma być jasny (tak jest teraz) — jeśli Twoja czarna
      organza ma czarny druk albo nie ma go wcale, daj znać, zmiana to jedno polecenie;
    - logo na zdjęciach to Twój plik `logo-lockup.png` nadrukowany na arkusze, nie rysunek AI.
    Do tego 4 zdjęcia zestawów (7/19/37/101) w scenie wzorca. Jeśli coś się nie podoba —
    wskaż plik; poprawka dotyczy tylko tej sceny, reszta zostaje.
19. **Obrót 360°** — strona jest gotowa na obrót dla każdego zestawienia (także z koroną),
    klatek jeszcze nie wygenerowano; stary obrót czerwieni jest wyłączony, bo był studyjny.
    Procedura i koszty: `tools/SPIN.md` (ok. 90 kredytów za zestawienie, 13 kolorów z białą
    organzą ~1170, pełna macierz z koroną ~11 700). Zdecyduj, od których zestawień zacząć.

---

## Co zostało zmienione w ramach przeglądu prawnego (już zrobione)

- **Regulamin §2** — akapit „Wizualizacje”: podgląd (także obrót 360°), film w nagłówku
  i zdjęcie pudełka to wizualizacje poglądowe wygenerowane z pomocą AI; gotowy bukiet
  może się różnić; o tym, co dostajesz, decyduje potwierdzona specyfikacja. PL/EN/UA.
- **Oznaczenie AI** — plakietki „Wizualizacja AI” na podglądzie i filmie, podpis pudełka,
  stopka i opis konfiguratora. Art. 50 ust. 4 AI Act (obowiązuje od 2 sierpnia 2026)
  wymaga ujawnienia, że realistyczny obraz został wygenerowany przez AI.
- **Regulamin §4** — formularz na stronie to zapytanie, nie zamówienie, i nie zobowiązuje
  do zapłaty; umowa zawiera się po potwierdzeniu z obowiązkiem zapłaty. Przycisk i komunikat
  po wysłaniu mówią teraz „zapytanie” (wcześniej „zamówienie” kłóciło się z regulaminem).
- **Specyfikacja zamówienia w mailu** dostała zdanie, że podgląd jest poglądowy, a wiąże
  specyfikacja.
- **Regulamin §9** — poprawione zdanie o wyłączonej platformie ODR.
- **Regulamin §10** — dopisane formularze, informacja o zagrożeniach w sieci i tryb
  zgłaszania problemów z działaniem strony (wymogi ustawy o świadczeniu usług drogą
  elektroniczną).
- **Polityka prywatności** — dopisani odbiorcy: FormSubmit, Wirtualna Polska (skrzynka
  wp.pl), Google (mapa — tylko po kliknięciu); transfer poza EOG uwzględnia FormSubmit.
- **Fonty i GSAP z własnego serwera** — wcześniej strona przy samym wejściu łączyła się
  z Google Fonts i cdnjs (Cloudflare), czyli przekazywała adres IP przed jakąkolwiek zgodą,
  wbrew obietnicy z polityki cookies. Teraz `assets/fonts/` i `assets/js/`.
- **Usunięty tekst „DO ZROBIENIA”** w sekcji opinii (PL/EN/UA). Sama informacja, że cytaty
  nie są zweryfikowanymi opiniami, zostaje — tego wymaga ustawa o przeciwdziałaniu
  nieuczciwym praktykom rynkowym.

## Co sprawdzono i jest w porządku

- Ceny brutto, koszt dostawy podany przed wysłaniem, łączna kwota w podsumowaniu.
- Informacja o najniższej cenie z 30 dni przy promocjach (Omnibus) — w regulaminie §3.
- Prawo odstąpienia, wzór formularza, zwrot kosztu najtańszej dostawy, rękojmia 2 lata.
- Brak odesłań do wyłączonej platformy ODR.
- Baner cookies: równorzędne „Odrzuć” i „Akceptuj”, nic poza niezbędnymi przed zgodą,
  mapa Google dopiero po kliknięciu.
- Opinie oznaczone jako niezweryfikowane, bez gwiazdek w danych strukturalnych.
- Ostrzeżenia GPSR: nie-zabawka, drobne elementy, łatwopalność, baterie guzikowe.
- Zdjęcia realizacji opisane jako prawdziwe, wizualizacje jako wizualizacje.

Źródła do limitu i AI Act:
[money.pl — limit 2026](https://direct.money.pl/artykuly/porady/dzialalnosc-nierejestrowana-2026-limit-10-813,50-zl,-po-przekroczeniu-7-dni-na-ceidg),
[art. 50 AI Act od 2 sierpnia 2026](https://ai.netzstrategen.com/en/insights/ai-content-disclosure-rules/).
