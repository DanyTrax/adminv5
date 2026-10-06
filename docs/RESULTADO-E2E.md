# Resultado E2E AdminV5

Fecha: 2026-10-06T05:35:38+00:00
Base: `http://127.0.0.1:8080`

**24 / 24 OK**

| Prueba | Estado | Detalle |
|--------|--------|---------|
| Servidor responde | OK | HTTP 200 |
| Login admin | OK | sesión |
| Página inicio | OK | HTTP 200 |
| Crear categoría | OK | id=6 |
| Crear producto | OK | id=5 stock=50 |
| Crear cliente | OK | id=2 |
| Crear medio de pago | OK | id=6 |
| Crear venta | OK | venta id=4 codigo=20004 |
| Stock descontado en venta | OK | antes=50 despues=48 |
| Asiento contable de venta | OK | registros=4 |
| Páginas módulo sin Fatal | OK | 23 OK |
| AJAX DataTables | OK | todos OK |
| Módulo sucursales | OK | sucursales_central=1 |
| Página crear cotización | OK | HTTP 200 |
| ACL vendedor ve ventas | OK | ventas |
| ACL vendedor no administra usuarios | OK | bloqueado/redirigido |
| AJAX sucursal sin password_bd | OK | {"success":true,"sucursal":{"id":1,"codigo_sucursal":"LOCAL01","nombre":"Sucursal Local Dev","direccion":"Calle Local 1" |
| Crear gasto | OK | id=7 |
| Crear entrada | OK | id=8 |
| Crear salida inventario | OK | id=2 |
| Stock tras salida | OK | antes=48 despues=45 |
| Listado ventas carga | OK | HTTP 200 |
| Módulo usuarios admin | OK | HTTP 200 |
| Crear cotización | OK | id=2 total=25000 |
