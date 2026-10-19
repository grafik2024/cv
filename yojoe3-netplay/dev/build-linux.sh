#!/bin/bash
# Builds the Unix port of WinUAE with the netplay patch (used for the tests).
# usage: build-linux.sh WORKDIR
# Result: WORKDIR/build/winuae
set -eu
HERE=$(cd "$(dirname "$0")" && pwd)
WORK=${1:?usage: build-linux.sh WORKDIR}
WINUAE_COMMIT=ae186a9c6c5a4a91f370a2927937c0bbcd9f6a2f
SDL_TAG=release-3.4.18
JOBS=$(nproc)

mkdir -p "$WORK"
cd "$WORK"

if [ ! -d WinUAE ]; then
	git init -q WinUAE
	git -C WinUAE remote add origin https://github.com/tonioni/WinUAE.git
	git -C WinUAE fetch -q --depth 1 origin "$WINUAE_COMMIT"
	git -C WinUAE checkout -q FETCH_HEAD
	git -C WinUAE apply "$HERE/../winuae-netplay.patch"
fi

# SDL 3.4 (the Unix port needs it, Ubuntu 24.04 has no libsdl3-dev)
if [ ! -d sdl3prefix ]; then
	git clone -q --depth 1 --branch "$SDL_TAG" https://github.com/libsdl-org/SDL.git SDL3
	cmake -S SDL3 -B sdl3build -DCMAKE_BUILD_TYPE=Release -DCMAKE_INSTALL_PREFIX="$WORK/sdl3prefix" \
		-DSDL_TESTS=OFF -DSDL_EXAMPLES=OFF -DSDL_UNIX_CONSOLE_BUILD=ON -DSDL_X11=OFF -DSDL_WAYLAND=OFF
	cmake --build sdl3build -j"$JOBS"
	cmake --install sdl3build
fi

# minimal build: no Qt GUI, nothing fetched from the network
cmake -S WinUAE -B build -DCMAKE_BUILD_TYPE=RelWithDebInfo -DCMAKE_PREFIX_PATH="$WORK/sdl3prefix" \
	-DWINUAE_UNIX_WITH_QT_UI=OFF -DWINUAE_UNIX_WITH_INTEGRATED_QT_UI=OFF \
	-DWINUAE_UNIX_WITH_PPC_QEMU=OFF -DWINUAE_UNIX_BUILD_QEMU_UAE_PLUGIN=OFF -DWINUAE_QEMU_UAE_BUILDER_FETCH=OFF \
	-DWINUAE_UNIX_BUNDLE_DRIVE_SOUNDS=OFF -DWINUAE_DRIVE_SOUNDS_FETCH=OFF \
	-DWINUAE_LZMA_SDK_FETCH=OFF -DWINUAE_UNIX_WITH_ARCHIVES=OFF \
	-DWINUAE_UNIX_WITH_LUA=OFF -DWINUAE_LUA_FETCH=OFF -DWINUAE_UNIX_WITH_CAPS=OFF \
	-DWINUAE_UNIX_WITH_FLOPPYBRIDGE=OFF -DWINUAE_UNIX_BUILD_FLOPPYBRIDGE_PLUGIN=OFF -DWINUAE_FLOPPYBRIDGE_FETCH=OFF \
	-DWINUAE_UNIX_WITH_CHD=OFF -DWINUAE_UNIX_WITH_CHD_FLAC=OFF -DWINUAE_UNIX_WITH_LIBMPEG2=OFF \
	-DWINUAE_UNIX_WITH_MIDI=OFF -DWINUAE_UNIX_WITH_MIDIEMU=OFF -DWINUAE_UNIX_WITH_FFMPEG=OFF \
	-DWINUAE_UNIX_WITH_OPENGL_SHADER_PIPELINE=OFF -DWINUAE_UNIX_WITH_UAENET_PCAP=OFF -DWINUAE_UNIX_WITH_SANA2=OFF \
	-DWINUAE_UNIX_WITH_SAMPLER=OFF -DWINUAE_UNIX_WITH_VIDEOGRAB=OFF -DWINUAE_UNIX_WITH_JIT=OFF
cmake --build build --target winuae_unix -j"$JOBS"
echo "built: $WORK/build/winuae"
