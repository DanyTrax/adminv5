# Inventario P0–P3 — AdminV5 (enlazado al grafo)

Fuente: `graphify-out/graph.json` (1483 nodos, 2153 edges) + auditoría estática.
God nodes: `ConexionCentral` (192), `Conexion` (158), `ModeloCatalogoMaestro`, `ModeloSucursales`, `ModeloContabilidad`.

## P0 — Seguridad (no dejar en prod)

| ID | Hallazgo | Nodos / archivos | Callers / impacto |
|----|----------|------------------|-------------------|
| P0-1 | Credenciales BD local en claro | `conexion_conexion` → [modelos/conexion.php](../modelos/conexion.php) | Casi todo el ERP local |
| P0-2 | Credenciales BD central en claro | `conexion_central_conexioncentral` → [api-transferencias/conexion-central.php](../api-transferencias/conexion-central.php) | Comunidad Despachos/Stock/Solicitudes |
| P0-3 | Token git pull débil + GET | [config.php](../config.php), [ajax/git-pull-remoto.ajax.php](../ajax/git-pull-remoto.ajax.php) | Herramientas admin sync |
| P0-4 | Exposición `password_bd` en JSON | [ajax/obtener-sucursal-actual.ajax.php](../ajax/obtener-sucursal-actual.ajax.php), sin-sesión | JS stock-transito |
| P0-5 | `eval(respuesta)` XSS | productos.php, categorias.php, registro-descargas-funcional.php | Botones borrar/sync admin |
| P0-6 | AJAX instalador sin auth + creds | [ajax/ajax-instalador-datos.php](../ajax/ajax-instalador-datos.php) | Instalación |

## P1 — Bugs que rompen acciones

| ID | Hallazgo | Callers |
|----|----------|---------|
| P1-1 | Falta `datatable-historial-recepciones.ajax.php` | historial-recepciones.php |
| P1-2 | Falta `obtener-detalle-recepcion.ajax.php` | historial-recepciones / recepciones |
| P1-3 | Faltan AJAX usuarios-sucursales (`datatable-usuarios-sucursales`, `estadisticas-usuarios-sucursales`, `obtener-sucursales`) | usuarios-sucursales |
| P1-4 | `registro-descargas-simple` (restaurado) | stock-transito-unificado.js |
| P1-5 | Rutas ACL / perfil Control (corregido Contador) | plantilla.php, menu.php |
| P1-6 | apiUrl pruebas vs pruebas2 (unificado API_URL) | plantilla.php |

## P2 — Deuda / basura

| ID | Hallazgo |
|----|----------|
| P2-1 | `*.phpold`, `ventas.controlador.phpand` (0 callers en grafo) |
| P2-2 | Vistas personalizacion-colores duplicadas |
| P2-3 | JS globales en todas las páginas (plantilla.php) |
| P2-4 | usuarios-central-old.php |

## P3 — Hardening

| ID | Hallazgo |
|----|----------|
| P3-1 | Sesión/perfil en todos los AJAX críticos (ventas, despachos, stock, productos, clientes) |
| P3-2 | SQL concatenado contabilidad/cotizaciones → prepared |
| P3-3 | `crypt` salt fijo → migrar a password_hash (fase posterior) |
| P3-4 | display_errors off (hecho en index.php) |

## Checklist regresión (por capa)

1. Login / logout / perfiles
2. Productos listar/editar
3. Crear venta + stock
4. Solicitud → despacho → tránsito → descarga
5. Gestión central
6. Reportes + medios de pago
7. Herramientas admin (solo admin)
