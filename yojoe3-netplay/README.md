# Yo! Joe! 3 – gra online w co-opie (WinUAE z netplay)

WinUAE zmodyfikowany tak, żeby dwóch graczy na dwóch komputerach mogło grać
razem przez internet w trybie dwóch graczy – jak przy jednej Amidze z dwoma
joystickami. Zmiana jest w samym emulatorze (nowy moduł `netplay.cpp` + kilka
haków), więc działa z każdą grą dla 2 graczy na A500, a nie tylko z Yo! Joe!.

| | Host (gracz 1) | Dołącza (gracz 2) |
|---|---|---|
| Postać w Yo! Joe! | Joe | Nat |
| Port Amigi | joystick w porcie 2 | joystick w porcie 1 (mysz) |
| Dyski gry | ma pliki `*_A.adf`, `*_B.adf` | nie potrzebuje – dostaje od hosta |
| Uruchamia | `HOST_gra_online.bat` | `DOLACZ_do_gry.bat` |

Krótka instrukcja dla graczy: [`gra/JAK_GRAC_ONLINE.txt`](gra/JAK_GRAC_ONLINE.txt).

## Jak to działa

Netplay działa w trybie *lockstep*, tak jak nagrywanie i odtwarzanie wejścia
w WinUAE (input recorder), tylko na żywo między dwoma komputerami:

1. **Start z tego samego stanu.** Przed włączeniem Amigi host i klient łączą
   się przez TCP. Host sprawdza, czy obaj mają tę samą konfigurację emulatora
   i ten sam Kickstart (suma CRC), przesyła obrazy dyskietek (klient trzyma je
   w katalogu SaveImages jako `netplay_<crc>_<nazwa>.adf` i przy następnej
   sesji nie pobiera ich ponownie) oraz wspólne ziarno liczb losowych.
   Potem obaj robią twardy reset – stan obu Amig jest identyczny co do bitu.
2. **Przez sieć idzie tylko wejście.** Każde zdarzenie z joysticka, myszy
   i klawiatury przechwycone w klatce *N* jest wysyłane do partnera i
   stosowane u obu w klatce *N + opóźnienie*, w tym samym miejscu emulacji
   (początek klatki, vpos 0), w tej samej kolejności (najpierw host, potem
   klient). Emulacja jest deterministyczna, więc obie Amigi liczą dokładnie
   to samo.
3. **Kontrola zgodności.** Co sekundę obie strony wymieniają sumę kontrolną
   pamięci Chip/Slow i rejestrów CPU. Rozjazd jest od razu zgłaszany
   („Netplay: DESYNC”).

Opóźnienie wejścia (`netplay_delay`) jest domyślnie dobierane automatycznie
z pingu zmierzonego przy łączeniu: `ceil((ping/2 + 15 ms) / 20 ms)` klatek,
czyli w LAN 1 klatka (20 ms), przy pingu ~90 ms 3 klatki (60 ms).

Joystick klienta jest przenoszony z portu 2 do portu 1 Amigi (opcja
`netplay_swap_ports`), więc obaj gracze ustawiają sterowanie jak w grze dla
jednego gracza. Na czas sesji oba porty są emulowane jako zwykłe joysticki
(niezależnie od tego, co kto ma podłączone), żeby obie Amigi miały ten sam
sprzęt. Lewy przycisk myszy hosta nadal działa jako fire w porcie 1.

## Szybki start (Windows)

1. Pobierz paczkę `YoJoe3-netplay-WinUAE-x64` (artefakt workflow
   [winuae-netplay.yml](../.github/workflows/winuae-netplay.yml) w zakładce
   *Actions* repozytorium) i rozpakuj ją u obu graczy. Obaj muszą mieć ten
   sam `winuae64.exe` i ten sam `YoJoe3_netplay.uae`. To WinUAE 6.1.0 beta
   (gałąź główna WinUAE); dołączony `winuae.ini` wyłącza okienko
   „Public Beta Disclaimer” (oficjalny przełącznik `Beta_Just_Shut_Up`).
2. Host kopiuje do tego folderu oba dyski gry (`..._A.adf` i `..._B.adf`)
   i uruchamia `HOST_gra_online.bat`.
3. Gracz 2 uruchamia `DOLACZ_do_gry.bat` i wpisuje adres hosta.
4. W grze: trainer startuje fire gracza 2 (albo kliknięcie lewym przyciskiem
   myszy u hosta), F1–F5 działają u obu. Na ekranie tytułowym: OPTIONS →
   fire na „ONE PLAYER” zmienia na „TWO PLAYER” → OK → START.

Stan połączenia widać na pasku tytułu okna WinUAE, np.
`[Netplay host, delay 3, 84 ms] WinUAE`.

### Połączenie przez internet

* **VPN (najprościej):** obaj instalują Radmin VPN, ZeroTier albo Tailscale,
  dołączają do wspólnej sieci, a gracz 2 wpisuje adres hosta z tej sieci.
  Nie trzeba nic ustawiać w routerze.
* **Przekierowanie portu:** host przekierowuje w routerze port **TCP 25200**
  na swój komputer i pozwala WinUAE na połączenia w zaporze Windows. Gracz 2
  wpisuje publiczny adres IP hosta (np. z https://ifconfig.me). Inny port:
  podaj go przy starcie hosta, a gracz 2 wpisuje `adres:port`.
* IPv6 też działa (`[2001:db8::1]:25200`).

### Uruchamianie ręczne

```
winuae64.exe -portable -f YoJoe3_netplay.uae -s floppy0=Gra_A.adf -s floppy1=Gra_B.adf -s netplay=host
winuae64.exe -portable -f YoJoe3_netplay.uae -s netplay=client -s netplay_address=83.12.34.56
```

Opcje można też wpisać do pliku `.uae`:

| Opcja | Znaczenie | Domyślnie |
|---|---|---|
| `netplay` | `host`, `client` (albo `join`), `off` | `off` |
| `netplay_address` | adres hosta dla klienta: `ip`, `nazwa`, `ip:port`, `[ipv6]:port` | – |
| `netplay_port` | port TCP | `25200` |
| `netplay_delay` | opóźnienie wejścia w klatkach, `0` = automatycznie z pingu | `0` |
| `netplay_password` | hasło; klient z innym hasłem jest odrzucany | brak |
| `netplay_swap_ports` | joystick klienta z portu 2 do portu 1 | `true` |
| `netplay_mouse` | port 1 emulowany jako mysz (gry na mysz), sterowanie myszą hosta | `false` |
| `rndseed` | stałe ziarno losowe – sesje są powtarzalne (testy) | losowe |
| `netplay_debug_input`, `netplay_debug_log` | skrypt wejścia i log sum kontrolnych co klatkę (testy, patrz `dev/`) | – |

## Co jest pilnowane w trakcie sesji

* **Reset** (Ctrl+Amiga+Amiga, skróty, przycisk Reset w GUI i na pasku stanu)
  jest wysyłany do partnera i wykonywany u obu w tej samej klatce.
* **Zmiana dyskietek** (GUI, pasek stanu, skróty), **wczytywanie stanów**,
  przewijanie, Action Replay/freeze, wklejanie schowka – zablokowane,
  bo zmieniłyby tylko jedną Amigę.
* **Zmiana ustawień emulatora w GUI (F12)** u jednego gracza – WinUAE co 5 s
  porównuje konfigurację z tą z początku sesji i ostrzega na ekranie.
* **Pauza / GUI / przeciągnięcie okna** u jednego gracza – drugi czeka
  („waiting for partner…”) i gra rusza dalej po wznowieniu.
* **Zerwanie połączenia** – sesja się kończy, każdy gra dalej sam.
* Ustawienia, które psują determinizm, są odrzucane przy starcie: CPU
  „Fastest possible”, JIT, wątek CPU, wczytanie stanu przy starcie. Zegar RTC
  jest wyłączany (czytałby czas komputera).

Komunikaty przy łączeniu wskazują przyczynę, np.:

```
Player 2 cannot join:
Different emulator configuration (use the same .uae file):
chipmem_size: host=1 client=2
cpu_cycle_exact: host=true client=false
```

## Testy

Testowane na porcie Linux tego samego WinUAE (`CMakeLists.txt` w źródłach
WinUAE, SDL3, wirtualny ekran i dźwięk) z dyskami Yo! Joe! 3 (12 światów v6),
dwie instancje na jednym komputerze, sterowanie skryptami
(`netplay_debug_input`) i suma kontrolna stanu co klatkę (`netplay_debug_log`):

| Test | Wynik |
|---|---|
| Trainer (fire klienta → port 1), menu, OPTIONS → TWO PLAYER, START, 2 graczy biega i strzela, 12 002 klatki (4 min) | sumy kontrolne identyczne w każdej klatce, 9/9 zrzutów ekranu identycznych bit w bit |
| To samo przez proxy dodające 40 ms (+0–10 ms jitter) w każdą stronę | ping ~90 ms, automatyczne opóźnienie 3 klatki, identyczne; czekanie na partnera średnio 3,5 ms w 6–10% klatek |
| Dwie osobne sesje z tym samym `rndseed`, różniące się tylko ruchem klienta w klatce 6600 | identyczne do klatki 6601, różne od 6602 – wejście klienta steruje Nat dokładnie w zaplanowanej klatce ([zrzut](zrzuty/klient_steruje_nat.png)) |
| Klawiatura: klient F1+F2, host F3 w trainerze | u obu „Yes” przy F1, F2, F3 |
| Reset zażądany przez klienta w klatce 6000 | obie Amigi zresetowane w tej samej klatce, sumy identyczne |
| Dźwięk `interrupts` kontra `exact` (osobne sesje, ten sam seed) | identyczna emulacja przez 8001 klatek – tryb wyjścia dźwięku może się różnić |
| Zabicie klienta w trakcie gry | host od razu kończy sesję i gra dalej (50 kl./s) |
| Inna konfiguracja (`chipmem_size`, cycle-exact) / złe hasło | odrzucone z listą różnic / „Wrong netplay password” |
| **Windows ↔ Windows**: `winuae64.exe` zbudowany przez GitHub Actions, obie kopie uruchomione pod Wine 9 (Xvfb, bez karty dźwiękowej), menu → TWO PLAYER → gra | 8 002 klatki, sumy identyczne w każdej klatce |
| Windows (Wine) ↔ Linux | połączenie, przesłanie dysków i lockstep działają, ale buildy Windows i Unix emulują inaczej od klatki 96 – rozjazd został wykryty u obu; dlatego teraz handshake od razu odrzuca graczy z inną platformą |

Szczegóły i skrypty: [`dev/TESTY.md`](dev/TESTY.md).

## Dla programistów

* [`winuae-netplay.patch`](winuae-netplay.patch) – łatka na WinUAE
  `tonioni/WinUAE@ae186a9c6c5a4a91f370a2927937c0bbcd9f6a2f`
  (`git apply winuae-netplay.patch`). Zawiera:
  * `netplay.cpp`, `include/netplay.h` – protokół, handshake, transfer dysków,
    synchronizacja klatek, sumy kontrolne;
  * `inputdevice.cpp` – przechwytywanie lokalnego wejścia, wstrzykiwanie na
    początku klatki, jednolite typy portów, blokada akcji AKS, reset przez sieć;
  * `newcpu.cpp` – połączenie przed pierwszym włączeniem Amigi;
  * `cfgfile.cpp`, `include/options.h` – opcje `netplay_*`;
  * `disk.cpp`, `od-win32/win32.cpp`, `od-win32/win32gui.cpp` – blokada zmiany
    dyskietek, reset przez sieć, status na pasku tytułu;
  * `od-win32/winuae_msvc15/*.vcxproj*`, `CMakeLists.txt` – nowy plik w projektach;
  * `od-unix/graphics.cpp` – status w tytule okna i poprawka kompilacji portu Unix
    (`set_custom_limits` po zmianie sygnatury w upstream).
* Windows: workflow [`../.github/workflows/winuae-netplay.yml`](../.github/workflows/winuae-netplay.yml)
  (kroki jak w oficjalnym workflow WinUAE: Visual Studio, NASM,
  `winuaeinclibs.zip`, `msbuild ... FullRelease x64`).
* Linux: [`dev/build-linux.sh`](dev/build-linux.sh) buduje SDL3 i WinUAE z łatką,
  [`dev/nptest.sh`](dev/nptest.sh) uruchamia sesję host + klient,
  [`dev/latency_proxy.py`](dev/latency_proxy.py) symuluje opóźnienie internetu.

### Protokół

TCP, wiadomości `[u32 długość][u8 typ][dane]`, liczby big-endian:
`HELLO` (magic `WUNP`, wersja, hasło) → `PING/PONG` (pomiar RTT) → `SETUP`
(ziarno, opóźnienie, flagi portów, CRC ROM, lista dyskietek z CRC, porównywana
konfiguracja) → `SETUPREPLY` (zgoda albo lista różnic, które dyski wysłać) →
`DISKDATA` (po 32 KB) → `START` → `READY` → w każdej klatce `FRAME` (numer
klatki + zdarzenia) i co 50 klatek `HASH`; `BYE`/`REJECT` z powodem.
Zdarzenia od partnera są sprawdzane (tylko klawiatura, joysticki i mysz,
nigdy akcje emulatora), rozmiary dysków i wiadomości są ograniczone, nazwy
plików od hosta są oczyszczane.

### Ograniczenia

* Dwóch graczy. Obaj muszą mieć tę samą kompilację WinUAE na tej samej
  platformie (Windows z Windows); inna wersja lub platforma jest odrzucana
  przy łączeniu.
* Sesja zaczyna się od włączenia Amigi; nie da się dołączyć do trwającej gry,
  a po rozjechaniu się („DESYNC”) trzeba połączyć się od nowa.
* Gry z dysku twardego/CD nie są przesyłane (tylko dyskietki).
