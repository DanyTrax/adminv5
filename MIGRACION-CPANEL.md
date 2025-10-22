# 🔧 MIGRACIÓN DE BASE DE DATOS - CPANEL

## 📋 INSTRUCCIONES PARA APLICAR CAMBIOS EN CPANEL

### ⚠️ IMPORTANTE
Ejecutar estos scripts **SOLO UNA VEZ** en cPanel para aplicar los cambios necesarios.

---

## 🚀 PASOS A SEGUIR

### 1. **Subir archivos al servidor**
- Subir `migracion-cpanel.php` a la raíz del proyecto en cPanel
- Subir `actualizar-passwords-locales.php` a la raíz del proyecto en cPanel

### 2. **Ejecutar migración de BD Central**
```bash
# Acceder a: https://tu-dominio.com/migracion-cpanel.php
```
**Este script:**
- ✅ Corrige el campo `sucursal_id` para permitir NULL
- ✅ Actualiza contraseñas de usuarios centrales con encriptado correcto
- ✅ Verifica que todos los cambios se aplicaron correctamente

### 3. **Ejecutar migración de BD Local**
```bash
# Acceder a: https://tu-dominio.com/actualizar-passwords-locales.php
```
**Este script:**
- ✅ Actualiza contraseñas de usuarios locales con encriptado correcto
- ✅ Asegura consistencia entre usuarios centrales y locales

### 4. **Verificar funcionamiento**
- ✅ Probar login con `admin` / `admin123`
- ✅ Probar crear usuario central
- ✅ Probar editar usuario central
- ✅ Probar eliminar usuario central
- ✅ Probar sincronización

### 5. **Limpiar archivos temporales**
```bash
# Eliminar los archivos de migración después de ejecutarlos
rm migracion-cpanel.php
rm actualizar-passwords-locales.php
```

---

## 🔍 VERIFICACIÓN

### ✅ Cambios aplicados correctamente si:
- Login de admin funciona con `admin` / `admin123`
- Se pueden crear usuarios centrales sin errores
- Se pueden editar usuarios centrales sin errores
- Se pueden eliminar usuarios centrales sin errores
- La sincronización funciona correctamente

### ❌ Si hay problemas:
- Verificar logs de error en cPanel
- Verificar que las conexiones a BD funcionen
- Contactar al administrador del sistema

---

## 📝 NOTAS TÉCNICAS

### Cambios aplicados:
1. **Campo sucursal_id:** Modificado para permitir NULL con DEFAULT NULL
2. **Encriptado de contraseñas:** Unificado con salt `$2a$07$asxx54ahjppf45sd87a5a4dDDGsystemdev$`
3. **Consistencia:** Mismo método de encriptado en usuarios centrales y locales

### Archivos modificados:
- `modelos/usuarios-central.modelo.php`
- `controladores/usuarios-central.controlador.php`
- `ajax/usuarios-central.ajax.php`
- `vistas/js/usuarios-central.js`
- `vistas/modulos/usuarios-central.php`

---

**✅ Una vez ejecutados estos scripts, el sistema estará completamente funcional en cPanel.**
