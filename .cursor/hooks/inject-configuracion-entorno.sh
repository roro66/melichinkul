#!/usr/bin/env bash
# Inyecta docs/CONFIGURACION_ENTORNO.md al inicio de cada sesión de agente.
set -euo pipefail

cat >/dev/null # consumir stdin (payload sessionStart)

DOC="docs/CONFIGURACION_ENTORNO.md"

if [[ ! -f "$DOC" ]]; then
  printf '%s\n' '{"additional_context":"[Melichinkul] No se encontró docs/CONFIGURACION_ENTORNO.md. Leer la regla .cursor/rules/configuracion-entorno-melichinkul.mdc y pedir al usuario el doc si hace falta."}'
  exit 0
fi

python3 - "$DOC" <<'PY'
import json
import pathlib
import sys

path = pathlib.Path(sys.argv[1])
content = path.read_text(encoding="utf-8")
context = (
    "# Contexto de entorno Melichinkul (sessionStart)\n\n"
    "Aplicar durante toda la sesión. Origen: docs/CONFIGURACION_ENTORNO.md\n\n"
    "---\n\n"
    + content
)
print(json.dumps({"additional_context": context}, ensure_ascii=False))
PY
