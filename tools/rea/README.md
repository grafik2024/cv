# REA — Reverse Engineer Anything

Serwer MCP + CLI ([`rea-agents`](https://www.npmjs.com/package/rea-agents), repo `morluto/rea`) do
inżynierii wstecznej binariów, aplikacji JS/Electron, APK, .NET, firmware'u i stron WWW.

## Co jest w repozytorium

| Plik | Rola |
|---|---|
| `.mcp.json` | rejestruje serwer `rea` dla Claude Code (startuje przez `tools/rea/mcp.sh`) |
| `.claude/settings.json` | włącza serwer `rea` z `.mcp.json` bez pytania o zgodę |
| `.claude/skills/reverse-engineer-anything/` | skill REA (z pakietu `rea-agents@6.2.0`) |
| `tools/rea/mcp.sh` | uruchamia `rea mcp` z providerami z `/opt/rea-tools/env.sh` (tylko te, które istnieją) |
| `tools/rea/install.sh` | instaluje REA i wszystkich providerów w `/opt` (idempotentny) |

Sam `.mcp.json` wystarcza do analiz, które nie potrzebują silników zewnętrznych (statyczna analiza
JS/Electron/ASAR, bundle stron WWW, inwentarz archiwów). Pozostałe wymagają `install.sh`.

## Providerzy instalowani przez `install.sh`

| Provider | Wersja | Do czego |
|---|---|---|
| Ghidra (build ze źródeł) | 12.1.4 | dekompilacja natywnych binariów (ELF/PE/Mach-O) |
| jadx-headless-mcp (build ze źródeł) | 0.7.1 | klasy i metody APK |
| ilspycmd + .NET SDK 8 | 9.1 | zestawy .NET (PE/CLI) |
| unblob + ekstraktory (7z, sasquatch, ubireader, jefferson…) | 26.6.4 | firmware |
| pwntools | 4.15.0 | układ ELF, symbole, core dumpy |
| mitmproxy (`mitmdump`) | 12.2.3 | przechwycone sesje sieciowe |
| wakaru | 1.13.0 | deobfuskacja JS |
| Chromium (z obrazu) | — | scenariusze przeglądarkowe |

Pominięte: Hopper i IDA (komercyjne), pwndbg (wymaga GDB zbudowanego z Pythonem 3.13),
rozszerzenie Ghidry SymbolicSummaryZ3 (Z3 jest dostępne tylko jako GitHub release).

## Ograniczenia sieci w chmurze Claude Code

Proxy blokuje pobieranie release'ów z github.com, a `repo.maven.apache.org` odpowiada 429.
Dlatego `install.sh` buduje Ghidrę i jadx-headless-mcp z tagów git, a Gradle korzysta z mirrora
Maven Central od Google. Zbudowany JAR jadx ma ten sam sha256 co oficjalny release (`6e5eacf5…`).

## Użycie w nowej sesji

Kontener chmurowy jest tymczasowy. Żeby providerzy byli gotowi od startu sesji, dodaj do
**Setup script** środowiska (menu środowiska → Edit):

```bash
bash tools/rea/install.sh            # pełna instalacja, ~25 min na 4 rdzeniach (głównie build Ghidry)
bash tools/rea/install.sh --no-ghidra  # bez Ghidry, kilka minut
```

Sprawdzenie: `source /opt/rea-tools/env.sh && rea doctor`.
