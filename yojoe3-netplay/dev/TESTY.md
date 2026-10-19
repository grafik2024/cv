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
| 14 | **Windows ↔ Windows**: `winuae64.exe` z GitHub Actions, host i klient pod Wine 9.0 + Xvfb (bez karty dźwiękowej, `winuae.ini` z paczki), trainer, menu → TWO PLAYER → START, obaj gracze biegają i strzelają | 8 002 | sumy identyczne w każdej klatce (WinUAE pod Wine robi ~42 kl./s, więc partner częściej czeka – gra i tak idzie dalej) |
| 15 | Windows (Wine) host ↔ Linux klient | 8 001 | protokół, transfer dysków i lockstep działają, ale emulacja buildu Windows i Unix różni się od klatki 96 (wykryte „DESYNC” u obu). Mapy pamięci i porównywana konfiguracja są identyczne, więc różnica jest w kodzie platform. Wniosek: wersja w handshake zawiera teraz platformę (`WinUAE 6.1.0 Windows x64`) i sesja mieszana jest odrzucana przed startem |
| 16 | Host bez działającego audio (`SDL_AUDIODRIVER` z błędną nazwą) ↔ klient z audio, Linux | 8 002 | identyczne – brak karty dźwiękowej nie psuje synchronizacji |

Czas od uruchomienia do startu sesji w LAN: ~1,5 s (łącznie z przesłaniem
2 × 880 KB dysków).

## Narzędzia

* `nptest.sh` – sesja host + klient, porównanie sum.
* `latency_proxy.py PORT_NASŁUCH PORT_HOSTA OPÓŹNIENIE_MS [JITTER_MS]` – proxy TCP
  dodające opóźnienie w obie strony (klient łączy się przez nie:
  `CLIENT_ARGS="-s netplay_address=127.0.0.1:PORT_NASŁUCH"`).
* `gdbctl.py` – mini-klient serwera GDB WinUAE (`debugging_features=gdbserver`)
  do ręcznych zrzutów ekranu i podglądu licznika klatek.
