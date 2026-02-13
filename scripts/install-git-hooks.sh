#!/bin/bash
# Instalar hook para quitar Co-authored-by de los commits
HOOK_SRC="prepare-commit-msg"
HOOK_DEST=".git/hooks/prepare-commit-msg"
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(cd "$SCRIPT_DIR/.." && pwd)"
cd "$REPO_ROOT"

cat > "$HOOK_DEST" << 'HOOK'
#!/bin/sh
# Eliminar Co-authored-by que Cursor agrega automáticamente
COMMIT_MSG_FILE=$1
if [ -f "$COMMIT_MSG_FILE" ]; then
  grep -v "Co-authored-by:" "$COMMIT_MSG_FILE" > "${COMMIT_MSG_FILE}.tmp"
  mv "${COMMIT_MSG_FILE}.tmp" "$COMMIT_MSG_FILE"
fi
HOOK
chmod +x "$HOOK_DEST"
echo "Hook instalado: $HOOK_DEST"
