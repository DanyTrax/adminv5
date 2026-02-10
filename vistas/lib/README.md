# Librerías locales (antes CDN)

Estos archivos sustituyen las cargas desde CDN en `plantilla.php` para evitar errores CORS en consola.

- **toastr.min.css / toastr.min.js** – notificaciones toast  
- **moment-timezone-with-data.min.js** – fechas y zonas horarias (usa moment de bower)  
- **xlsx.full.min.js** – exportar Excel  

Si algo deja de funcionar (toasts, fechas, exportar a Excel), en `vistas/plantilla.php` se pueden volver a poner las URLs de CDN:

- Toastr: `https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css` y `.min.js`
- Moment-timezone: `https://cdnjs.cloudflare.com/ajax/libs/moment-timezone/0.5.43/moment-timezone-with-data.min.js`
- XLSX: `https://unpkg.com/xlsx@0.18.5/dist/xlsx.full.min.js`
