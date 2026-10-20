# Behance – ready project (EN)

Order: add the modules on Behance one after another – image, text, image…
`[IMAGE xx]` = file `en/xx_….png` from this folder. Project cover: `en/00_cover.png`.

---

## Project title

Yo! Joe! 3 – 12 worlds and online co-op

## Project description (short field under the title)

A fan edition of the Amiga platformer Yo! Joe! (Scipio, 1993) expanded to 12 worlds, with new maps and an ending. We also added an online mode to the WinUAE emulator: two players on two computers play together as if they were sitting at one Amiga.

## Tags

Amiga, retro gaming, game mod, level design, pixel art, emulation, WinUAE, netplay, multiplayer, C++

## Tools Used

WinUAE, C++, Visual Studio, GitHub Actions, Python, SDL3, Wine, Claude Code

---

## Project modules

[IMAGE 01] `01_title.png`

**Two mods in one project**

Yo! Joe! is a two-player platformer: Joe and Nat fight through the worlds together, each with their own joystick. We took the original apart in two steps. First we expanded the game itself to 12 worlds. Then we modified the WinUAE emulator so the co-op can be played over the internet.

[IMAGE 02] `02_editions.png`

**From the original to 12 worlds**

The mod was built in four editions, each on two floppy disks (A and B):

1. Classic worlds – 6 worlds rebuilt with the original graphics.
2. New graphics – 6 worlds with new graphics, including the subway.
3. Twelve worlds – the 6 classic worlds, then the 6 new-graphics worlds and the ending.
4. Twelve worlds v6 – worlds 7–12 got completely new maps.

[IMAGE 03] `03_maps_7_12.png`

**New maps: worlds 7–12**

Swamp Castle, Tomb Canyon, Treetop Village, Mountain Monastery, Subway and Night Harbour. The version 6 maps are buildings with rooms of different sizes, hiding places, traps, stairs and slides, all built from the game's original tiles, so the new levels look like part of the original.

[IMAGE 04] `04_worlds_in_game.png`

**Worlds in the game**

Frames from a full online playthrough – the start of successive worlds, Joe on one computer, Nat on the other. From the first level, through the new worlds and the subway, to the last world before the ending.

[IMAGE 05] `05_original_feel.png`

**Like a game from the era**

We kept everything that made the atmosphere: the TRSI trainer (infinite lives, energy, weapons), the original title screen, the options with the ONE/TWO PLAYER switch, the high score table sprayed on a subway car, and single-drive play with disk swapping. We checked the whole thing in the emulator: all worlds, the ending and a new game.

[IMAGE 06] `06_how_it_works.png`

**The problem: co-op only at one Amiga**

Yo! Joe! is best with two players, but only at one computer. WinUAE has no online co-op mode, so we wrote one. It works in lockstep:

- Both Amigas start from an identical state: same configuration, same Kickstart, same floppy disks (the host sends them to player 2 automatically) and a shared random seed.
- Only the controls travel over the network: joystick, mouse and keyboard. An input from frame N is applied by both Amigas in frame N + delay, at exactly the same point of the emulation.
- The delay is chosen automatically from the ping: 1 frame (20 ms) on a LAN, 3 frames at 90 ms ping.
- Every second both sides compare a checksum of memory and CPU, so any desync is caught immediately.

[IMAGE 07] `07_netplay_window.png`

**Made for players**

- A new "Netplay" page in WinUAE settings: Host or Join, address, password, delay.
- UPnP: the host opens the port in the router by itself and shows the address to give to the friend.
- Player 2's joystick moves to Amiga port 1 automatically, so everyone sets up controls as for single player.
- Reset and warp mode happen on both machines in the same frame. Pausing on one side makes the other wait.
- Esc cancels waiting for the partner, a hard reset reconnects.
- Ready-made HOST and JOIN launchers for Yo! Joe!.

[IMAGE 08] `08_floppies.png`

**Changing floppies mid-game**

On a single drive the game asks for the second disk. The host inserts it as usual and netplay does the rest: it offers the image, player 2 takes it from the cache or downloads it in the background (16 KB per frame, the game keeps running), and then both Amigas swap the disk in the same frame.

[IMAGE 09] `09_debugging.png`

**Hunting a desync**

Both Amigas must compute exactly the same thing, bit for bit. The most interesting bug: after reconnecting, the game desynced immediately. We tracked it down step by step:

1. Frame 0 – a CPU reset keeps the old register values, so we clear them when the session starts.
2. Frame 150 – the floppy drive mechanics (head position, motor) stay as they were, so the drive goes back to its power-on state.
3. Frame 219 – a trace of every instruction on both Amigas showed a single difference: at $FA3458 the system loads a counter of 11 instead of 6. It lives in memory the emulator adds for the AROS system, and a reset does not clear it. Now we clear it and include it in the checksum.

After the fixes a session started by a reset is identical, frame by frame, to one started at power-on. Two more desyncs turned up along the way: a disk image left in a disabled drive, and differences between the Windows and Linux builds. Now only identical builds can connect.

[IMAGE 10] `10_tests.png`

**Testing**

Scripts played the game while both Amigas logged a state checksum every frame:

- The whole game from the trainer through all worlds to the ending, high scores typed on two keyboards (JOE and NAT), a new game – 64,002 identical frames, 27/27 identical screenshots.
- Single-drive play with disk swaps – 20,002 identical frames, on Linux and on Windows.
- Simulated internet: 40 ms plus jitter – automatic delay of 3 frames, smooth play.
- Plus reconnecting after a reset, a UPnP router, a wrong password, a different configuration and a dropped connection.

[IMAGE 11] `11_finale.png`

**Finale**

The ending, the high score table and a new game – over the internet, from two keyboards. A non-commercial fan project.

Original game: Yo! Joe! – Scipio, 1993 (Play Byte, Hudson Soft). Trainer: TRSI. Emulator: WinUAE (Toni Wilen and contributors), with the built-in AROS ROM.
