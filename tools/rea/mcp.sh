#!/usr/bin/env bash
# Starts the REA MCP server over stdio (registered in .mcp.json).
# Provider paths come from /opt/rea-tools/env.sh (written by tools/rea/install.sh); any provider
# whose path is missing on this host is unset so REA reports it as "not configured" instead of broken.
# Nothing may be printed to stdout before exec: it is the MCP transport.
set -uo pipefail

REA_VERSION=6.2.0
ENV_FILE=/opt/rea-tools/env.sh
[ -f "$ENV_FILE" ] && source "$ENV_FILE"

for var in GHIDRA_INSTALL_DIR JAVA_HOME REA_JADX_MCP_JAR REA_PWNTOOLS_PYTHON REA_UNBLOB_COMMAND \
           REA_MITMDUMP_COMMAND REA_WAKARU_COMMAND REA_ILSPY_CMD_PATH REA_BROWSER_EXECUTABLE; do
  [ -n "${!var:-}" ] && [ ! -e "${!var}" ] && unset "$var"
done
[ -n "${GHIDRA_INSTALL_DIR:-}" ] && [ ! -f "$GHIDRA_INSTALL_DIR/Ghidra/application.properties" ] && unset GHIDRA_INSTALL_DIR

if [ "$(rea --version 2>/dev/null)" = "$REA_VERSION" ]; then
  exec rea mcp
fi
exec npx -y "rea-agents@${REA_VERSION}" mcp
