#!/usr/bin/env bash
# Installs REA (Reverse Engineer Anything, npm `rea-agents`) and its analysis providers on a
# Linux x64 Claude Code cloud container. Idempotent: finished steps are skipped on re-run.
#
# Egress constraints this script works around (see tools/rea/README.md):
#   - github.com release downloads are blocked, so Ghidra and jadx-headless-mcp are built from
#     their git tags (git clone is allowed) instead of downloading release assets;
#   - repo.maven.apache.org answers 429 to this shared IP, so Gradle uses Google's Maven Central mirror;
#   - Z3 is only published as GitHub release assets, so Ghidra's optional SymbolicSummaryZ3
#     extension is left out of the build.
#
# Usage: tools/rea/install.sh [--no-ghidra]   (Ghidra build: ~20-40 min on 4 cores)
set -euo pipefail

REA_VERSION=6.2.0
GHIDRA_VERSION=12.1.4
JADX_MCP_TAG=v0.7.1
TOOLS=/opt/rea-tools
SRC=/opt/src
MIRROR=https://maven-central.storage-download.googleapis.com/maven2/
BUILD_GHIDRA=1
[ "${1:-}" = "--no-ghidra" ] && BUILD_GHIDRA=0
export DEBIAN_FRONTEND=noninteractive DOTNET_CLI_TELEMETRY_OPTOUT=1 DOTNET_NOLOGO=1 GIT_LFS_SKIP_SMUDGE=1

mkdir -p "$TOOLS" "$SRC"
log() { printf '\n==> %s\n' "$*"; }

log "System packages (JDKs, .NET 8, firmware extractors, sasquatch build deps)"
apt-get update -qq
apt-get install -y -qq openjdk-21-jdk-headless openjdk-17-jdk-headless dotnet-sdk-8.0 \
  p7zip-full erofs-utils lz4 lziprecover lzop partclone android-sdk-libsparse-utils unar zstd \
  zlib1g-dev liblzma-dev liblzo2-dev liblz4-dev libzstd-dev >/dev/null

log "npm: rea-agents@${REA_VERSION}, @wakaru/cli@1.13.0"
[ "$(rea --version 2>/dev/null || true)" = "$REA_VERSION" ] || npm i -g "rea-agents@${REA_VERSION}" >/dev/null
[ "$(wakaru --version 2>/dev/null || true)" = "wakaru 1.13.0" ] || npm i -g @wakaru/cli@1.13.0 >/dev/null

log "Python providers (separate venvs: pwntools / unblob / mitmproxy)"
venv() { # name, pip packages...
  local name=$1; shift
  [ -x "$TOOLS/$name/bin/python" ] || python3 -m venv "$TOOLS/$name"
  "$TOOLS/$name/bin/pip" install -q "$@"
}
venv pwn pwntools==4.15.0
venv unblob unblob==26.6.4 jefferson ubi_reader
venv mitm mitmproxy==12.2.3
for b in jefferson ubireader_extract_files ubireader_extract_images; do
  ln -sf "$TOOLS/unblob/bin/$b" "/usr/local/bin/$b"
done

log "sasquatch (SquashFS extractor for unblob)"
if [ ! -x /usr/local/bin/sasquatch-v4be ]; then
  [ -d "$SRC/sasquatch" ] || git clone -q --depth 1 https://github.com/onekey-sec/sasquatch "$SRC/sasquatch"
  make -s -C "$SRC/sasquatch/squashfs-tools" -j"$(nproc)" >/dev/null
  cp "$SRC/sasquatch/squashfs-tools/sasquatch" /usr/local/bin/sasquatch
  make -s -C "$SRC/sasquatch/squashfs-tools" clean >/dev/null
  CFLAGS=-DFIX_BE make -s -C "$SRC/sasquatch/squashfs-tools" -j"$(nproc)" >/dev/null
  cp "$SRC/sasquatch/squashfs-tools/sasquatch" /usr/local/bin/sasquatch-v4be
fi

log "ilspycmd 9.1 (.NET 8 compatible)"
[ -x "$TOOLS/ilspy/ilspycmd" ] || dotnet tool install ilspycmd --version 9.1.0.7988 --tool-path "$TOOLS/ilspy" >/dev/null

mkdir -p "$SRC/gradle-init"
cat > "$SRC/gradle-init/mirror.gradle" <<EOF
// Prefer Google's Maven Central mirror (repo.maven.apache.org rate-limits this shared egress IP with 429)
allprojects {
	buildscript { repositories { maven { url = uri("${MIRROR}") } } }
	repositories { maven { url = uri("${MIRROR}") } }
}
EOF

log "jadx-headless-mcp ${JADX_MCP_TAG} (Android provider, built from the audited tag)"
JADX_JAR="$TOOLS/jadx/jadx-headless-mcp-${JADX_MCP_TAG#v}-all.jar"
if [ ! -f "$JADX_JAR" ]; then
  [ -d "$SRC/jadx-headless-mcp" ] || git clone -q --depth 1 --branch "$JADX_MCP_TAG" \
    https://github.com/1013503897/jadx-headless-mcp "$SRC/jadx-headless-mcp"
  (cd "$SRC/jadx-headless-mcp" && ./gradlew -q -I "$SRC/gradle-init/mirror.gradle" shadowJar --max-workers=2 \
    -Dorg.gradle.java.installations.paths=/usr/lib/jvm/java-17-openjdk-amd64)
  mkdir -p "$TOOLS/jadx"
  cp "$SRC/jadx-headless-mcp/build/libs/$(basename "$JADX_JAR")" "$JADX_JAR"
fi

if [ "$BUILD_GHIDRA" = 1 ] && [ ! -f /opt/ghidra/Ghidra/application.properties ]; then
  log "Ghidra ${GHIDRA_VERSION} from source"
  G="$SRC/ghidra"
  [ -d "$G" ] || git clone -q --depth 1 --branch "Ghidra_${GHIDRA_VERSION}_build" \
    https://github.com/NationalSecurityAgency/ghidra "$G"
  [ -d "$SRC/ghidra-data" ] || git clone -q --depth 1 --branch "Ghidra_${GHIDRA_VERSION}" \
    https://github.com/NationalSecurityAgency/ghidra-data "$SRC/ghidra-data"
  # Files fetchDependencies would pull from github.com/.../ghidra-data/raw; matching sha256 skips the download.
  mkdir -p "$G/dependencies/downloads"
  cp "$SRC/ghidra-data/lib/java-sarif-2.1-modified.jar" "$SRC/ghidra-data/Debugger/dbgmodel.tlb" \
     "$SRC"/ghidra-data/FunctionID/*.fidb "$G/dependencies/downloads/"
  FETCH="$G/gradle/support/fetchDependencies.gradle"
  grep -q 'startsWith("z3-")' "$FETCH" || sed -i '/^\/\/ Download dependencies (if necessary)/i\
// LOCAL PATCH: Z3 is only published as GitHub release assets (blocked); SymbolicSummaryZ3 is excluded\
ext.deps = deps.findAll { !it.name.startsWith("z3-") }\
' "$FETCH"
  Z3_BUILD="$G/Ghidra/Extensions/SymbolicSummaryZ3/build.gradle"
  [ ! -f "$Z3_BUILD" ] || mv "$Z3_BUILD" "$Z3_BUILD.disabled"
  (cd "$G" \
    && gradle -q -I "$SRC/gradle-init/mirror.gradle" -I gradle/support/fetchDependencies.gradle \
         -DhideDownloadProgress --max-workers=1 \
    && gradle -q -I "$SRC/gradle-init/mirror.gradle" buildGhidra --max-workers="$(nproc)")
  rm -rf /opt/ghidra
  DIST_ZIP=$(ls -t "$G"/build/dist/ghidra_*_linux_x86_64.zip | head -1)
  unzip -q "$DIST_ZIP" -d "$SRC/ghidra-dist"
  ln -sfn "$(ls -d "$SRC"/ghidra-dist/ghidra_* | head -1)" /opt/ghidra
fi

log "Provider environment ($TOOLS/env.sh)"
cat > "$TOOLS/env.sh" <<'EOF'
# REA analysis providers; sourced by tools/rea/mcp.sh before starting the MCP server
export GHIDRA_INSTALL_DIR=/opt/ghidra
export JAVA_HOME=/usr/lib/jvm/java-21-openjdk-amd64
export REA_JADX_MCP_JAR=/opt/rea-tools/jadx/jadx-headless-mcp-0.7.1-all.jar
export REA_PWNTOOLS_PYTHON=/opt/rea-tools/pwn/bin/python
export REA_UNBLOB_COMMAND=/opt/rea-tools/unblob/bin/unblob
export REA_MITMDUMP_COMMAND=/opt/rea-tools/mitm/bin/mitmdump
export REA_WAKARU_COMMAND=/opt/node22/bin/wakaru
export REA_ILSPY_CMD_PATH=/opt/rea-tools/ilspy/ilspycmd
export REA_BROWSER_EXECUTABLE=/opt/pw-browsers/chromium
export REA_BROWSER_NO_SANDBOX=true  # container runs as root; Chromium refuses to sandbox as root
EOF

log "Done. Check with: source $TOOLS/env.sh && rea doctor"
