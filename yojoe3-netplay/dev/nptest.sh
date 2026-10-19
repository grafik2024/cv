#!/bin/bash
# Runs a netplay session: host + client on this machine, headless.
#
# usage: nptest.sh WINUAE ADF_DIR TESTDIR PORT [extra -s options for both...]
#   WINUAE   path to the winuae binary (build-linux.sh)
#   ADF_DIR  folder with the game disks *_A.adf and *_B.adf (host only)
#   TESTDIR  folder with game.uae, host.script, client.script
#
# Environment: HOST_ARGS / CLIENT_ARGS add options to one side only,
# e.g. CLIENT_ARGS="-s netplay_address=127.0.0.1:25400" to go through
# latency_proxy.py. NPTEST_TIMEOUT limits the run (seconds).
#
# Scripts (netplay_debug_input), one step per line, frame = netplay frame:
#   <frame> event <INPUTEVENT name> <state>   local input, e.g. JOY2_FIRE_BUTTON 1
#   <frame> shot <file.png>                   screenshot
#   <frame> reset soft|hard                   synchronized reset
#   <frame> quit
# Each side writes "<frame> <state checksum>" for every frame to
# host.hash / client.hash, the files must be identical.
set -u
W=$(realpath "$1")
ADF=$(realpath "$2")
DIR=$3
PORT=$4
shift 4
TIMEOUT=${NPTEST_TIMEOUT:-400}
export SDL_AUDIODRIVER=${SDL_AUDIODRIVER:-dummy} SDL_VIDEODRIVER=${SDL_VIDEODRIVER:-dummy}
cd "$DIR"
DISKA=$(ls "$ADF"/*_A.adf | head -1)
DISKB=${DISKA%_A.adf}_B.adf
mkdir -p hhome chome
rm -f status.txt host.hash client.hash
(HOME=$PWD/hhome timeout $TIMEOUT "$W" -f game.uae \
	-s floppy0="$DISKA" -s floppy1="$DISKB" \
	-s netplay=host -s netplay_port=$PORT \
	-s netplay_debug_input=$PWD/host.script -s netplay_debug_log=$PWD/host.hash \
	${HOST_ARGS:-} "$@" > host.log 2>&1; echo "host exit $?" >> status.txt) &
sleep 1
(HOME=$PWD/chome timeout $TIMEOUT "$W" -f game.uae \
	-s netplay=client -s netplay_address=127.0.0.1:$PORT \
	-s netplay_debug_input=$PWD/client.script -s netplay_debug_log=$PWD/client.hash \
	${CLIENT_ARGS:-} "$@" > client.log 2>&1; echo "client exit $?" >> status.txt) &
wait
cat status.txt
echo "--- host"
grep -a "NETPLAY" host.log | head -20
echo "--- client"
grep -a "NETPLAY" client.log | head -20
if [ -f host.hash ] && [ -f client.hash ]; then
	echo "--- hashes: host $(wc -l < host.hash) lines, client $(wc -l < client.hash) lines"
	if cmp -s host.hash client.hash; then
		echo "HASHES IDENTICAL"
	else
		echo "HASHES DIFFER, first difference:"
		diff host.hash client.hash | head -5
	fi
fi
