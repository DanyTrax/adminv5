# Checklist de regresión — instancia de prueba

Ejecutar tras cada capa. Marcar OK/FAIL.

## Pre-requisitos

- [ ] Copiado `config.database.php.example` → `config.database.php` con credenciales de **prueba**
- [ ] `GIT_PULL_TOKEN` fuerte en `config.php` (o env) y el mismo en panel sync
- [ ] Backup BD prueba antes de Capa B/C

## Flujos

1. [ ] Login / logout / perfiles (Admin, Vendedor, Transportador, Contador)
2. [ ] Productos: listar, editar, sync (admin)
3. [ ] Crear venta + descuento de stock
4. [ ] Solicitud stock → crear despacho → stock tránsito → descarga
5. [ ] Gestión central: usuarios / clientes / categorías / sucursales / medios pago
6. [ ] Reportes + medios de pago locales
7. [ ] Herramientas admin (solo user admin): SQL / git pull POST

## Seguridad rápida

- [ ] `ajax/obtener-sucursal-actual.ajax.php` no devuelve `password_bd`
- [ ] Sin sesión → AJAX productos/ventas/clientes responden 401 JSON
- [ ] Botones borrar/sync productos/categorías muestran swal sin `eval`
