# Revisión profunda (HTTP + errores PHP)

Generado: 2026-10-06T05:30:56+00:00

- `productos-stock-sucursales`: OK (HTTP 200)
- `editar-venta`: OK (HTTP 200)
- `editar-gasto`: OK (HTTP 200)
- `editar-entrada`: OK (HTTP 200)
- `editar-cotizacion`: OK (HTTP 200)
- `consultar-usuarios-sucursales`: OK (HTTP 200)
- `trazabilidad-mercancia`: OK (HTTP 200)
- `historial-recepciones`: OK (HTTP 200)
- `recepciones`: OK (HTTP 200)
- `instalacion-sql-completa`: OK (HTTP 200)

## AJAX críticos

- `ajax/datatable-productos.ajax.php`: OK (HTTP 200) — {"data":[[1,"<img src='vistas\/img\/productos\/default\/anonymous.png' width='40px'>","GEN001","Producto demo","General"
- `ajax/datatable-ventas.ajax.php`: OK (HTTP 200) — {"data":[[1,"<img src='vistas\/img\/productos\/default\/anonymous.png' width='40px'>","GEN001","Producto demo","<button 
- `ajax/datatable-despachos.ajax.php`: OK (HTTP 200) — {"data": [], "error": "SQLSTATE[42S22]: Column not found: 1054 Unknown column 'd.id_solicitud_origen' in 'on clause'"}
- `ajax/datatable-solicitudes-stock.ajax.php`: OK (HTTP 200) — {"data":[],"debug_info":{"mensaje":"No hay solicitudes registradas","usuario_local":"Administrador Local (Administrador)
- `ajax/datatable-stock-transito.ajax.php`: OK (HTTP 200) — {"data":[],"error":"SQLSTATE[42S22]: Column not found: 1054 Unknown column 'st.cantidad_disponible' in 'where clause'"}
- `ajax/obtener-sucursal-actual.ajax.php?accion=obtener_sucursal_actual`: OK (HTTP 200) — {"success":true,"sucursal":{"id":1,"codigo_sucursal":"LOCAL01","nombre":"Sucursal Local Dev","direccion":"Calle Local 1"
- `ajax/productos.ajax.php`: OK (HTTP 200) — {"id":1,"0":1,"id_categoria":1,"1":1,"parent_id":null,"2":null,"codigo":"GEN001","3":"GEN001","codigo_maestro":null,"4":

## ACL transportador

- acceso a `/usuarios` como Transportador: OK (no expone UI usuarios)
- `/transportador-movil`: HTTP 200 OK

## Fallos

- Ninguno crítico en esta pasada.

## Nota esquema local (logística)
Tras enriquecer columnas en `despachos` / `stock_transito`:
- `datatable-despachos.ajax.php` → `{"data":[]}` OK
- `datatable-stock-transito.ajax.php` → `{"data":[]}` OK

Las tablas centrales del seed local son **mínimas** (suficientes para navegar UI). Flujos completos de sync multi-sucursal requieren más tablas/columnas del instalador de producción.
