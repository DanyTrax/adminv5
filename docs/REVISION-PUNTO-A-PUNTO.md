# Revisión punto a punto — AdminV5

Generado: 2026-10-06 05:31:25
HTTP base: `http://127.0.0.1:8080`

## Resumen navegación

| Opción | En whitelist | Vista PHP | JS ruta | HTTP | Nota |
|--------|--------------|-----------|---------|------|------|
| inicio | Sí (Administrador, Especial, Vendedor, Contador, Transportador) | Sí | (común/inline) | OK (200) | OK estructura |
| usuarios | Sí (Administrador) | Sí | usuarios.js | OK (200) | OK estructura |
| categorias | Sí (Administrador, Especial, Vendedor) | Sí | categorias.js | OK (200) | OK estructura |
| productos | Sí (Administrador, Especial, Vendedor) | Sí | productos.js | OK (200) | OK estructura |
| solicitudes-stock | Sí (Administrador, Especial, Vendedor, Contador, Transportador) | Sí | solicitudes-stock.js | OK (200) | OK estructura |
| crear-solicitud-stock | Sí (Administrador, Especial, Vendedor, Contador) | Sí | solicitudes-stock.js, crear-solicitud-stock.js | OK (200) | OK estructura |
| despachos | Sí (Administrador, Especial, Vendedor, Contador, Transportador) | Sí | despachos.js | OK (200) | OK estructura |
| crear-despacho | Sí (Administrador, Especial, Vendedor) | Sí | crear-despacho.js | OK (200) | OK estructura |
| stock-transito | Sí (Administrador, Especial, Vendedor, Contador, Transportador) | Sí | (común/inline) | OK (200) | OK estructura |
| registro-descargas-funcional | Sí (Administrador, Especial, Vendedor, Contador, Transportador) | Sí | (común/inline) | OK (200) | OK estructura |
| transportador-movil | Sí (Transportador) | Sí | transportador-movil.js | OK (200) | OK estructura |
| clientes | Sí (Administrador, Vendedor, Contador) | Sí | clientes.js | OK (200) | OK estructura |
| ventas | Sí (Administrador, Vendedor, Contador) | Sí | ventas.js | OK (200) | OK estructura |
| crear-venta | Sí (Administrador, Vendedor, Contador) | Sí | ventas.js | OK (200) | OK estructura |
| reportes | Sí (Administrador, Especial, Contador) | Sí | reportes.js, contabilidad.js | OK (200) | OK estructura |
| reporte-detallado | Sí (Administrador, Especial, Contador) | Sí | reportes.js | OK (200) | OK estructura |
| contabilidad | Sí (Administrador, Especial) | Sí | contabilidad.js | OK (200) | OK estructura |
| gastos | Sí (Administrador, Contador, Vendedor) | Sí | contabilidad.js | OK (200) | OK estructura |
| crear-gastos | Sí (Administrador, Contador, Vendedor) | Sí | contabilidad.js | OK (200) | OK estructura |
| entradas | Sí (Administrador, Contador) | Sí | contabilidad.js | OK (200) | OK estructura |
| crear-entradas | Sí (Administrador, Contador) | Sí | (común/inline) | OK (200) | OK estructura |
| medios-pago | Sí (Administrador, Especial) | Sí | medios-pago.js | OK (200) | OK estructura |
| salidas-inventario | Sí (Administrador, Especial, Vendedor) | Sí | salidas-inventario.js | OK (200) | OK estructura |
| cotizacion | Sí (Administrador, Vendedor, Contador) | Sí | (común/inline) | OK (200) | OK estructura |
| crear-cotizacion | Sí (Administrador, Vendedor, Contador) | Sí | (común/inline) | OK (200) | OK estructura |
| herramientas-admin-sync | Sí (Administrador) | Sí | (común/inline) | OK (200) | OK estructura |
| usuarios-central | Sí (Administrador) | Sí | usuarios-central.js | OK (200) | OK estructura |
| clientes-central | Sí (Administrador) | Sí | clientes-central.js | OK (200) | OK estructura |
| categorias-central | Sí (Administrador) | Sí | categorias-central.js | OK (200) | OK estructura |
| personalizacion-colores-simplificado | Sí (Administrador) | Sí | (común/inline) | OK (200) | OK estructura |
| personalizacion-cotizaciones | Sí (Administrador) | Sí | (común/inline) | OK (200) | OK estructura |
| sucursales | Sí (Administrador) | Sí | sucursales.js | OK (200) | OK estructura |
| catalogo-maestro | Sí (Administrador, Especial) | Sí | catalogo-maestro.js | OK (200) | OK estructura |
| medios-pago-central | Sí (Administrador) | Sí | medios-pago-central.js | OK (200) | OK estructura |
| salir | Sí (Administrador, Especial, Vendedor, Contador, Transportador) | Sí | (común/inline) | OK (200) | OK estructura |

## Rutas en ACL sin enlace de menú (acceso directo)

- `productos-stock-sucursales` → Administrador, Especial, Vendedor (vista OK)
- `editar-venta` → Administrador, Vendedor, Contador (vista OK)
- `editar-gasto` → Administrador, Contador (vista OK)
- `editar-entrada` → Administrador (vista OK)
- `editar-cotizacion` → Administrador, Vendedor, Contador (vista OK)
- `consultar-usuarios-sucursales` → Administrador (vista OK)
- `trazabilidad-mercancia` → Administrador, Especial, Vendedor, Contador, Transportador (vista OK)
- `historial-recepciones` → Administrador, Especial, Vendedor, Contador (vista OK)
- `recepciones` → Administrador, Especial, Vendedor, Contador (vista OK)
- `crear-tabla-personalizacion-cotizaciones` → Administrador (vista OK)
- `agregar-campos-logo-texto-cotizaciones` → Administrador (vista OK)
- `agregar-campo-nombre-sucursal-personalizacion` → Administrador (vista OK)
- `instalacion-sql-completa` → Administrador (vista OK)

## Hallazgos

- Sin hallazgos estructurales en navegación.

## Login HTTP
- OK — sesión OK
