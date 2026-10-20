# Behance – gotowy projekt (PL)

Kolejność: na Behance dodajesz moduły po kolei – obraz, tekst, obraz…
`[OBRAZ xx]` = plik `pl/xx_….png` z tego folderu. Okładka projektu: `pl/00_okladka.png`.

---

## Tytuł projektu

Yo! Joe! 3 – 12 światów i gra online w co-opie

## Opis projektu (krótkie pole pod tytułem)

Fanowska edycja platformówki Yo! Joe! z Amigi (Scipio, 1993) rozbudowana do 12 światów, z nowymi mapami i zakończeniem. Do emulatora WinUAE dopisaliśmy tryb gry online: dwóch graczy na dwóch komputerach gra razem, jakby siedzieli przy jednej Amidze.

## Tagi

Amiga, retro gaming, game mod, level design, pixel art, emulation, WinUAE, netplay, multiplayer, C++

## Narzędzia (pole „Tools Used”)

WinUAE, C++, Visual Studio, GitHub Actions, Python, SDL3, Wine, Claude Code

---

## Moduły projektu

[OBRAZ 01] `01_tytul.png`

**Dwa mody w jednym projekcie**

Yo! Joe! to platformówka dla dwóch graczy: Joe i Nat przechodzą kolejne światy razem, każdy swoim joystickiem. Wzięliśmy oryginał na warsztat w dwóch krokach. Najpierw rozbudowaliśmy samą grę do 12 światów. Potem przerobiliśmy emulator WinUAE tak, żeby w co-opa dało się grać przez internet.

[OBRAZ 02] `02_edycje.png`

**Od oryginału do 12 światów**

Mod powstawał w czterech edycjach, każda na dwóch dyskietkach (A i B):

1. Klasyczne światy – 6 światów przebudowanych na oryginalnej grafice.
2. Nowa grafika – 6 światów z nową grafiką, w tym metro.
3. Dwanaście światów – najpierw 6 klasycznych, potem 6 z nową grafiką i zakończenie.
4. Dwanaście światów v6 – światy 7–12 dostały całkiem nowe mapy.

[OBRAZ 03] `03_mapy_7_12.png`

**Nowe mapy: światy 7–12**

Zamek na Bagnach, Kanion Grobowców, Osada w Koronach Drzew, Klasztor w Górach, Metro i Port Nocą. Mapy z wersji 6 to budynki z salami różnej wielkości, skrytki, pułapki, schody i zjeżdżalnie, zbudowane z oryginalnych kafli gry. Dzięki temu nowe poziomy wyglądają jak część oryginału.

[OBRAZ 04] `04_swiaty_w_grze.png`

**Światy w grze**

Kadry z przejścia całej gry przez internet – początek kolejnych światów, Joe na jednym komputerze, Nat na drugim. Od pierwszego poziomu, przez nowe światy i metro, po ostatni świat przed zakończeniem.

[OBRAZ 05] `05_klimat_oryginalu.png`

**Jak w grze z epoki**

Zostawiliśmy wszystko, co budowało klimat: trainer TRSI (nieskończone życia, energia, broń), oryginalny ekran tytułowy, opcje z przełącznikiem ONE/TWO PLAYER, tabelę rekordów z graffiti na wagonie metra i grę na jednej stacji dysków z wymianą dyskietek. Całość sprawdziliśmy w emulatorze: wszystkie światy, zakończenie i nową grę.

[OBRAZ 06] `06_jak_dziala.png`

**Problem: co-op tylko przy jednej Amidze**

Yo! Joe! najlepiej smakuje we dwóch, ale tylko przy jednym komputerze. WinUAE nie oferuje gry sieciowej w co-opie, więc ją dopisaliśmy. To tzw. lockstep:

- Obie Amigi startują z identycznego stanu: ta sama konfiguracja, ten sam Kickstart, te same dyskietki (host sam wysyła je graczowi 2) i wspólne ziarno losowości.
- Przez sieć idzie tylko sterowanie: joystick, mysz i klawiatura. Ruch z klatki N obie Amigi wykonują w klatce N + opóźnienie, dokładnie w tym samym miejscu emulacji.
- Opóźnienie dobiera się samo z pingu: w sieci lokalnej 1 klatka (20 ms), przy pingu 90 ms 3 klatki.
- Co sekundę obie strony porównują sumę kontrolną pamięci i procesora. Rozjazd jest wykrywany od razu.

[OBRAZ 07] `07_okno_netplay.png`

**Wygodne dla gracza**

- Nowa strona „Netplay” w ustawieniach WinUAE: Host albo Join, adres, hasło, opóźnienie.
- UPnP: host sam otwiera port w routerze i pokazuje adres, który podaje koledze.
- Joystick gracza 2 sam trafia do portu 1 Amigi – każdy ustawia sterowanie jak do gry solo.
- Reset i tryb turbo działają u obu w tej samej klatce. Pauza u jednego zatrzymuje drugiego.
- Esc przerywa czekanie na partnera, twardy reset łączy ponownie.
- Dla Yo! Joe! gotowe pliki startowe HOST i DOŁĄCZ.

[OBRAZ 08] `08_dyskietki.png`

**Zmiana dyskietki w trakcie gry**

Gra na jednej stacji prosi o drugi dysk. Host wkłada go jak zwykle, a netplay robi resztę: wysyła opis obrazu, gracz 2 bierze dysk z pamięci podręcznej albo pobiera go w tle (16 KB na klatkę, gra toczy się dalej), po czym obie Amigi zmieniają dyskietkę w tej samej klatce.

[OBRAZ 09] `09_debugowanie.png`

**Polowanie na rozjazd**

Obie Amigi muszą liczyć dokładnie to samo, co do bitu. Najciekawszy błąd: po ponownym połączeniu gra rozjeżdżała się od razu. Śledziliśmy go krok po kroku:

1. Klatka 0 – reset procesora zostawia stare wartości rejestrów, więc zerujemy je na starcie sesji.
2. Klatka 150 – mechanika stacji dysków (położenie głowicy, silnik) zostaje taka, jak była, więc stacja wraca do stanu „jak po włączeniu”.
3. Klatka 219 – ślad wszystkich instrukcji obu Amig pokazał jedną różnicę: pod adresem $FA3458 system wczytuje licznik 11 zamiast 6. To pamięć, którą emulator dokłada dla systemu AROS, a reset jej nie czyści. Czyścimy ją i wliczamy do sumy kontrolnej.

Po poprawkach sesja po resecie jest identyczna co do klatki z sesją od włączenia. Po drodze wyszły też dwa inne rozjazdy: obraz dyskietki w wyłączonej stacji oraz różnice między wersją na Windows i na Linuksa. Teraz łączą się tylko identyczne wersje.

[OBRAZ 10] `10_testy.png`

**Testy**

Grą sterowały skrypty, a obie Amigi zapisywały sumę kontrolną stanu w każdej klatce:

- Cała gra od trainera przez wszystkie światy do zakończenia, rekordy wpisane z dwóch klawiatur (JOE i NAT), nowa gra – 64 002 klatki identyczne, 27/27 zrzutów ekranu identycznych.
- Gra na jednej stacji z wymianą dyskietek – 20 002 klatki identyczne, na Linuksie i na Windowsie.
- Symulowany internet: 40 ms + wahania opóźnienia – automatyczne opóźnienie 3 klatki, gra płynna.
- Do tego ponowne połączenie po resecie, router UPnP, złe hasło, inna konfiguracja i zerwane połączenie.

[OBRAZ 11] `11_final.png`

**Finał**

Zakończenie, tabela rekordów i nowa gra – przez internet, z dwóch klawiatur. Projekt fanowski i niekomercyjny.

Oryginalna gra: Yo! Joe! – Scipio, 1993 (Play Byte, Hudson Soft). Trainer: TRSI. Emulator: WinUAE (Toni Wilen i współtwórcy), z wbudowanym ROM-em AROS.
