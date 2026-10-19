# Testy netplay

Wszystkie testy: port Linux WinUAE (`build-linux.sh`, SDL3 z wirtualnym
ekranem i dźwiękiem `SDL_VIDEODRIVER=dummy`, `SDL_AUDIODRIVER=dummy`), dyski
`YoJoe3_12swiatow_v6_A.adf` / `_B.adf`, ROM AROS wbudowany w WinUAE, host
i klient na jednym komputerze (127.0.0.1). Każda strona ma osobny katalog
domowy, więc klient naprawdę pobiera dyski od hosta.

Wejście graczy podają skrypty `netplay_debug_input` (te same zdarzenia, które
dałby joystick/klawiatura – przechodzą przez przechwytywanie netplay, więc
u klienta działa też przenoszenie portu 2 → 1). `netplay_debug_log` zapisuje
sumę kontrolną stanu (pamięć Chip + Slow, rejestry D0–A7, PC, licznik linii)
w **każdej** klatce; plik hosta i klienta muszą być identyczne.

```
dev/build-linux.sh /tmp/np                       # WinUAE z łatką -> /tmp/np/build/winuae
cp -r dev/testy/coop /tmp/coop
dev/nptest.sh /tmp/np/build/winuae KATALOG_Z_ADF /tmp/coop 25200
# cała gra (~20 min) i zmiana dyskietek; rndseed = ta sama losowość co w wynikach
dev/nptest.sh /tmp/np/build/winuae KATALOG_Z_ADF /tmp/cala_gra 25201 -s rndseed=4242
dev/nptest.sh /tmp/np/build/winuae KATALOG_Z_ADF /tmp/dyskietka 25202 -s rndseed=4242
```

## Wyniki

| # | Test | Klatki | Wynik |
|---|---|---|---|
| 1 | Trainer startowany fire klienta (JOY2 klienta → port 1 Amigi), ładowanie, ekran tytułowy | 6 001 | sumy identyczne w każdej klatce |
| 2 | `testy/coop`: OPTIONS → TWO PLAYER → OK → START, Joe i Nat biegają, skaczą, strzelają | 12 002 | sumy identyczne, 9/9 zrzutów ekranu host = klient bit w bit |
| 3 | Jak 2, przez `latency_proxy.py` 40 ms + 0–10 ms jitter w każdą stronę | 12 002 | RTT 89 ms → opóźnienie 3 klatki (auto), identyczne; host czekał łącznie 2,4 s w 699 klatkach, klient 4,2 s w 1146 klatkach (średnio ~3,5 ms – mieści się w zapasie klatki 20 ms) |
| 4 | Jak 2, proxy 25 ms + 0–5 ms, po wszystkich poprawkach | 12 002 | identyczne |
| 5 | Jak 2, bez logu debug (normalny tryb: suma co 50 klatek) | 12 002 | brak „DESYNC”, zrzuty identyczne |
| 6 | Dwie osobne sesje, `rndseed=12345`, jedyna różnica: klient trzyma „w prawo” od klatki 6600 | 6 801 | sumy obu sesji identyczne do klatki 6601, różne od 6602 (6600 + opóźnienie 1 + 1) – wejście klienta działa dokładnie w zaplanowanej klatce i steruje Nat (`zrzuty/klient_steruje_nat.png`) |
| 7 | Trainer: klient F1 i F2, host F3; klient zleca `reset soft` w klatce 6000 | 7 002 | trainer u obu „Yes/Yes/Yes”, reset w tej samej klatce u obu („Reset at 0000A694”), sumy identyczne |
| 8 | `sound_output=exact` kontra `interrupts`, ten sam seed, te same skrypty | 8 001 | identyczna emulacja → porównywane jest tylko „dźwięk emulowany / none” |
| 9 | `gra/YoJoe3_netplay.uae` (plik z paczki dla graczy), menu → 2 graczy | 6 701 | identyczne, Joe i Nat w grze (`zrzuty/host_i_klient_ta_sama_klatka.png`) |
| 10 | Klient zabity (`kill -9`) w trakcie | – | host: „Connection lost”, gra dalej 50 kl./s (licznik z monitora GDB) |
| 11 | Klient z `chipmem_size=2`, bez cycle-exact | – | obie strony: lista różnic (`chipmem_size: host=1 client=2`, …), gra bez netplay |
| 12 | Złe hasło | – | klient: „Wrong netplay password”, host czeka na kolejnego gracza |
| 13 | Druga sesja z tym samym katalogiem klienta | – | „DF0 found in cache” – dyski nie są przesyłane ponownie |
| 14 | **Windows ↔ Windows**: `winuae64.exe` z GitHub Actions, host i klient pod Wine 9.0 + Xvfb (bez karty dźwiękowej, `winuae.ini` z paczki), trainer, menu → TWO PLAYER → START, obaj gracze biegają i strzelają. Powtórzone na finalnym artefakcie (run #3) z przesłaniem dysków od nowa | 8 002 | sumy identyczne w każdej klatce, 3/3 zrzuty identyczne (WinUAE pod Wine robi ~42 kl./s, więc partner częściej czeka – gra i tak idzie dalej) |
| 15 | Windows (Wine) host ↔ Linux klient | 8 001 | protokół, transfer dysków i lockstep działają, ale emulacja buildu Windows i Unix różni się od klatki 96 (wykryte „DESYNC” u obu). Mapy pamięci i porównywana konfiguracja są identyczne, więc różnica jest w kodzie platform. Wniosek: wersja w handshake zawiera teraz platformę (`WinUAE 6.1.0 Windows x64`) i sesja mieszana jest odrzucana przed startem – sprawdzone na finalnym artefakcie: „Connection refused: Different emulator version: host WinUAE 6.1.0 Windows x64, player 2 WinUAE 6.1.0 Unix x64” |
| 16 | Host bez działającego audio (`SDL_AUDIODRIVER` z błędną nazwą) ↔ klient z audio, Linux | 8 002 | identyczne – brak karty dźwiękowej nie psuje synchronizacji |
| 17 | `testy/cala_gra`: cała gra od trainera do zakończenia (HELP co 300 klatek), tabela rekordów – Joe wpisuje „JOE” z klawiatury hosta, Nat „NAT” z klawiatury klienta – potem START i nowa gra dwóch graczy | 64 001 | sumy identyczne w każdej klatce, 27/27 zrzutów identycznych (`zrzuty/cala_gra_koniec_rekordy_nowa_gra.png`). Wcześniej znaleziony błąd: gra z jedną wyłączoną stacją i obrazem wpisanym w DF1 u hosta rozjeżdżała się w klatce 241 (obraz w wyłączonej stacji zmienia emulację) – host teraz czyści takie sloty |
| 18 | `testy/dyskietka`: `nr_floppies=1`; po 1. poziomie gra prosi „Insert Disk 2”, host wkłada B (`disk @DISKB@ 0`) – klient nie ma B, 901 120 B przesłane w trakcie gry; potem wysunięcie, A, B (z pamięci podręcznej klienta); klient w klatkach 9000/9100 próbuje zmienić dysk | 20 002 | sumy identyczne, 16/16 zrzutów; gra ładuje kolejne poziomy z nowo włożonego dysku u obu; log klienta: „floppy 1 received”, „found in cache” ×2, „DF0 change refused” ×2 |
| 19 | Ponowne połączenie (3 procesy): host + klient 1 grają 6 900 klatek (trainer, menu, gra 2 graczy), host kończy sesję i robi twardy reset (`leave`), łączy się klient 2 (świeży proces, pobiera dyski) | 6 900 + 6 900 | druga sesja identyczna co do klatki z pierwszą. Wcześniej rozjazd: konfiguracja „rtc” zmieniona przez sesję (odrzucenie klienta), rejestry CPU, mechanika stacji i RAM AROS pod `$A80000` przetrwały reset – teraz zerowane na starcie sesji, a RAM `$A80000` jest w sumie kontrolnej |
| 20 | UPnP z atrapą routera (`fake_igd.py`: SSDP multicast, opis urządzenia w kodowaniu chunked, SOAP) | – | `AddPortMapping` (dzierżawa 7200 s) + `GetExternalIPAddress` → pasek tytułu „203.0.113.7 port …”, `DeletePortMapping` po sesji; router „tylko stałe dzierżawy” (725) → ponowienie z 0; port przekierowany na inny komputer (718) → komunikat, bez usuwania cudzego wpisu; brak routera → komunikat po 2,5 s, sesja działa |

Czas od uruchomienia do startu sesji w LAN: ~1,5 s (łącznie z przesłaniem
2 × 880 KB dysków).

## Narzędzia

* `nptest.sh` – sesja host + klient, porównanie sum. W skryptach
  `@DISKA@`/`@DISKB@` to ścieżki dysków gry; `game.uae` z `nr_floppies=1`
  dostaje tylko dysk A.
* `fake_igd.py PORT_HTTP [--only-permanent] [--conflict IP]` – atrapa routera
  UPnP do testów otwierania portu.
* `latency_proxy.py PORT_NASŁUCH PORT_HOSTA OPÓŹNIENIE_MS [JITTER_MS]` – proxy TCP
  dodające opóźnienie w obie strony (klient łączy się przez nie:
  `CLIENT_ARGS="-s netplay_address=127.0.0.1:PORT_NASŁUCH"`).
* `gdbctl.py` – mini-klient serwera GDB WinUAE (`debugging_features=gdbserver`)
  do ręcznych zrzutów ekranu i podglądu licznika klatek.
