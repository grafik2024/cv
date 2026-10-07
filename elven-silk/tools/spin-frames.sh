#!/usr/bin/env bash
# Tnie wideo, w którym kamera okrąża bukiet, na klatki obrotu 360° dla konfiguratora.
#
#   tools/spin-frames.sh orbita.mp4 <kolor> <oprawa> [klatek=24] [bok=760]
#   np. tools/spin-frames.sh granat-czarna.mp4 granat org-czarna
#   z koroną: tools/spin-frames.sh granat-korona.mp4 granat org-czarna-korona
#
# Wynik: assets/cfg/spin-<kolor>-<oprawa>-00.webp … -23.webp (kwadrat, środek kadru).
# Klatki biorę co duration/N, od 0 do N-1 — ostatnia klatka filmu to zwykle ta sama
# pozycja co pierwsza, więc jej nie powtarzam. Potrzebny ffmpeg z libwebp.
set -euo pipefail
SRC=${1:?plik wideo}; COLOR=${2:?id koloru z COLORS}; SLEEVE=${3:?id oprawy z SLEEVE}
N=${4:-24}; SIZE=${5:-760}
OUT="$(cd "$(dirname "$0")/../assets/cfg" && pwd)"
DUR=$(ffprobe -v error -show_entries format=duration -of csv=p=0 "$SRC")
for i in $(seq 0 $((N - 1))); do
  T=$(awk -v d="$DUR" -v i="$i" -v n="$N" 'BEGIN{printf "%.3f", d*i/n}')
  ffmpeg -v error -y -ss "$T" -i "$SRC" -frames:v 1 \
    -vf "crop='min(iw,ih)':'min(iw,ih)',scale=${SIZE}:${SIZE}:flags=lanczos" \
    -c:v libwebp -quality 72 "$OUT/spin-$COLOR-$SLEEVE-$(printf %02d "$i").webp"
done
echo "Gotowe: $N klatek w $OUT/spin-$COLOR-$SLEEVE-*.webp"
echo "Teraz dopisz  \"$COLOR-$SLEEVE\": {}  do SPIN_SETS w page.html i uruchom node build.js"
