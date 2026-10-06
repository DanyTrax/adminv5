# Promoción a producción — por capas

**No desplegar un megadiff.** Validar cada capa en prueba con [CHECKLIST-PRUEBA.md](CHECKLIST-PRUEBA.md).

## Paquetes sugeridos

### Paquete A (bajo riesgo) — ya en esta copia de trabajo
- plantilla HTML/ACL/`apiUrl`/`salir`/medios-pago-central menú
- `display_errors` off
- limpieza `*.phpold` / `phpand` / usuarios-central-old
- `registro-descargas-simple` restaurado

### Paquete B (seguridad)
- `config.database.php` (+ example) — **crear en cada servidor; no subir passwords al git**
- conexion local/central sin credenciales en código
- `GIT_PULL_TOKEN` fuerte, solo POST
- sin `eval()`, sin `password_bd` en JSON
- guards `AjaxAuth` en AJAX críticos

**Antes de B en prod:** rotar passwords de BD tras desplegar el nuevo mecanismo.

### Paquete C (funcional/deuda)
- AJAX historial recepciones / sucursales stubs
- SQL prepared contabilidad/cotizaciones
- JS condicional por ruta en plantilla

## Procedimiento por paquete

1. Backup código + BD producción
2. Desplegar solo archivos del paquete
3. En prod: crear `config.database.php` desde example con credenciales actuales
4. Smoke test 7 puntos del checklist
5. Si falla → rollback del paquete

## Evidencia local

- Grafo: `graphify-out/graph.json`, `GRAPH_REPORT.md`, `graph.html`
- Inventario: `graphify-out/INVENTARIO-P0-P3.md`
