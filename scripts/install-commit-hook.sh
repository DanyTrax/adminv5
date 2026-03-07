#!/bin/sh
# Instala el hook que elimina "Made-with: Cursor" y "Co-authored-by" de los commits
HOOK=".git/hooks/prepare-commit-msg"
SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
REPO_ROOT="$(cd "$SCRIPT_DIR/.." && pwd)"
cd "$REPO_ROOT" || exit 1

cat > "$HOOK" << 'HOOK_EOF'
#!/bin/sh
# Eliminar trailers que Cursor agrega automáticamente
COMMIT_MSG_FILE=$1
if [ -f "$COMMIT_MSG_FILE" ]; then
  grep -v -E "Co-authored-by:|Made-with: Cursor" "$COMMIT_MSG_FILE" > "${COMMIT_MSG_FILE}.tmp"
  if [ -s "${COMMIT_MSG_FILE}.tmp" ]; then
    mv "${COMMIT_MSG_FILE}.tmp" "$COMMIT_MSG_FILE"
  else
    rm -f "${COMMIT_MSG_FILE}.tmp"
  fi
fi
HOOK_EOF
chmod +x "$HOOK"
echo "Hook instalado en $HOOK"
