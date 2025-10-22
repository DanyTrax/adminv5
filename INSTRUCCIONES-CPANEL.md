# 🚀 INSTRUCCIONES PARA ACTUALIZAR CPANEL

## 📋 Archivos que necesitan ser actualizados

Los siguientes archivos han sido modificados y necesitan ser subidos al servidor de cPanel:

### Archivos principales:
1. **`vistas/modulos/sucursales.php`**
   - ✅ Agregada sección "Configuración de Base de Datos" al formulario local
   - ✅ Campos: usuario_bd, password_bd, nombre_bd, host_bd, puerto_bd

2. **`controladores/sucursales.controlador.php`**
   - ✅ Actualizado para manejar campos de BD en configuración local
   - ✅ Valores por defecto: localhost, 3306
   - ✅ Validación corregida para respuesta del modelo

3. **`vistas/js/sucursales.js`**
   - ✅ Carga de campos de BD al editar configuración local
   - ✅ Valores por defecto establecidos correctamente

4. **`ajax/sucursales.ajax.php`**
   - ✅ Sincronización bidireccional mejorada
   - ✅ Endpoint para actualizar estado de sucursales
   - ✅ Sincronización automática Central → Local

5. **`modelos/sucursales.modelo.php`**
   - ✅ Manejo de campos faltantes con valores por defecto
   - ✅ Función de eliminación mejorada sin errores FK
   - ✅ Configuración local con campos de BD

## 🔧 Pasos para actualizar en cPanel

### Opción 1: Subir archivos individuales
1. **Acceder a cPanel**
   - Iniciar sesión en cPanel
   - Ir a "Administrador de archivos"

2. **Navegar al directorio del proyecto**
   - Buscar la carpeta donde está instalado el sistema
   - Normalmente en `public_html/` o subcarpeta

3. **Subir archivos actualizados**
   - Descargar archivos del repositorio GitHub
   - Subir cada archivo a su ubicación correspondiente
   - Verificar permisos (644 para archivos, 755 para carpetas)

### Opción 2: Usar Git en cPanel (si está disponible)
```bash
# En el terminal de cPanel
cd /path/to/project
git pull origin main
```

### Opción 3: Usar FileZilla o similar
1. **Conectar al servidor via FTP/SFTP**
2. **Navegar al directorio del proyecto**
3. **Subir archivos modificados**
4. **Verificar que se hayan subido correctamente**

## ✅ Verificación post-actualización

### 1. Verificar archivos subidos
- Comprobar que todos los archivos estén en su ubicación
- Verificar permisos de archivos
- Confirmar que no hay errores de sintaxis

### 2. Probar funcionalidades
- **Configuración de sucursal local:**
  - Hacer clic en "Editar Configuración"
  - Verificar que aparezcan los campos de "Configuración de Base de Datos"
  - Probar guardar configuración

- **Sincronización bidireccional:**
  - Verificar que los estados se sincronicen automáticamente
  - Probar desactivar/activar sucursales
  - Confirmar que la configuración local se actualice

- **Eliminación de sucursales:**
  - Probar eliminar sucursales sin errores de FK
  - Verificar que se desactiven usuarios asociados

### 3. Verificar logs de errores
- Revisar `error_log` del servidor
- Verificar que no haya errores PHP
- Confirmar que las conexiones a BD funcionen

## 🐛 Solución de problemas comunes

### Error: "Archivo no encontrado"
- Verificar ruta del archivo
- Comprobar permisos de lectura
- Confirmar que el archivo se subió correctamente

### Error: "Permisos denegados"
- Cambiar permisos a 644 para archivos
- Cambiar permisos a 755 para carpetas
- Verificar propietario del archivo

### Error: "Sintaxis PHP"
- Verificar que el archivo se subió completo
- Comprobar codificación (UTF-8)
- Revisar caracteres especiales

## 📞 Soporte

Si encuentras problemas durante la actualización:

1. **Verificar logs de error** del servidor
2. **Comprobar permisos** de archivos y carpetas
3. **Confirmar que todos los archivos** se subieron correctamente
4. **Probar funcionalidades** una por una

## 🎯 Cambios implementados

### ✅ Nuevas funcionalidades:
- **Campos de BD en formulario local**: Completamente funcionales
- **Sincronización bidireccional**: Automática y robusta
- **Eliminación de sucursales**: Sin errores de FK
- **Estados sincronizados**: Local ↔ Central automáticamente

### ✅ Mejoras de UX:
- **Formularios consistentes**: Local y central con mismos campos
- **Validaciones robustas**: Manejo de errores mejorado
- **Sincronización transparente**: Automática y sin intervención del usuario

---

**Fecha de actualización:** $(date)  
**Versión:** 1.0  
**Estado:** Listo para producción ✅
