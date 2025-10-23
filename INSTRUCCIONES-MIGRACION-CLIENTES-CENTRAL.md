# 📋 INSTRUCCIONES PARA MIGRACIÓN DE CLIENTES CENTRALES EN CPANEL

## 🎯 **OBJETIVO**

Implementar el sistema de gestión central de clientes en cPanel sin modificar la estructura existente de la tabla `clientes` local.

---

## ✅ **PASOS PARA MIGRACIÓN**

### **PASO 1: Descargar Archivos Actualizados**

1. Descargar archivos desde el repositorio:
   - `crear-tabla-clientes-central.php`
   - `modelos/clientes-central.modelo.php`
   - `controladores/clientes-central.controlador.php`
   - `vistas/modulos/clientes-central.php`
   - `vistas/js/clientes-central.js`
   - `ajax/clientes-central.ajax.php`

### **PASO 2: Subir Archivos a cPanel**

1. Subir los archivos a las carpetas correspondientes:
   - `crear-tabla-clientes-central.php` → raíz del proyecto
   - `modelos/clientes-central.modelo.php` → `modelos/`
   - `controladores/clientes-central.controlador.php` → `controladores/`
   - `vistas/modulos/clientes-central.php` → `vistas/modulos/`
   - `vistas/js/clientes-central.js` → `vistas/js/`
   - `ajax/clientes-central.ajax.php` → `ajax/`

### **PASO 3: Crear Tabla clientes_central**

1. Acceder a cPanel
2. Ir a File Manager
3. Navegar a la raíz del proyecto
4. Ejecutar `crear-tabla-clientes-central.php` en el navegador:
   ```
   https://tu-dominio.com/crear-tabla-clientes-central.php
   ```
5. Verificar que la tabla se creó correctamente

### **PASO 4: Verificar Conexión**

1. Verificar que `api-transferencias/conexion-central.php` existe
2. Verificar credenciales de conexión a BD central
3. Probar conexión desde el script

### **PASO 5: Agregar Menú**

1. Editar `vistas/plantilla.php`
2. Agregar entrada de menú para "Clientes Centrales":
   ```php
   <li class="treeview">
       <a href="clientes-central">
           <i class="fa fa-users"></i>
           <span>Clientes Centrales</span>
       </a>
   </li>
   ```

### **PASO 6: Probar Funcionalidad**

1. Acceder a "Clientes Centrales" desde el menú
2. Crear un cliente de prueba
3. Verificar que se detecta duplicados
4. Editar cliente y verificar sincronización
5. Eliminar cliente de prueba

---

## 🔧 **VERIFICACIONES IMPORTANTES**

### **Verificar Tabla Local `clientes` NO SE MODIFICA**

La estructura de la tabla `clientes` local debe permanecer intacta:
- ✅ Sin cambios en estructura
- ✅ Sin cambios en campos
- ✅ Sin cambios en índices
- ✅ Sin cambios en tipos de datos

### **Verificar Conexión a Sucursales**

La sincronización requiere:
- ✅ Datos de conexión almacenados en tabla `sucursales`
- ✅ Campos: `host_bd`, `usuario_bd`, `password_bug`, `nombre_bd`, `puerto_bd`
- ✅ Acceso remoto habilitado en MySQL

### **Verificar Permisos**

El sistema requiere:
- ✅ Permisos de Administrador para acceder
- ✅ Permisos de lectura/escritura en BD central
- ✅ Permisos de lectura/escritura en BD locales

---

## 📊 **ESTRUCTURA DE TABLA clientes_central**

```sql
CREATE TABLE clientes_central (
    id_central INT PRIMARY KEY AUTO_INCREMENT,
    documento VARCHAR(20) NOT NULL UNIQUE,
    email VARCHAR(100) NULL,
    nombre VARCHAR(100) NOT NULL,
    telefono VARCHAR(20) NULL,
    direccion TEXT NULL,
    fecha_nacimiento DATE NULL,
    sucursales_asignadas TEXT NULL,
    id_local_principal INT NULL,
    sucursal_origen VARCHAR(50) NULL,
    activo TINYINT(1) DEFAULT 1,
    sincronizado TINYINT(1) DEFAULT 0,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_sucursal_origen (sucursal_origen)
);
```

---

## 🚨 **PROBLEMAS COMUNES Y SOLUCIONES**

### **Error: "Este script debe ejecutarse desde el sistema CENTRAL"**

**Solución:** Asegúrate de ejecutar el script desde el sistema central, no desde una sucursal local.

### **Error: "Error conectando a base de datos central"**

**Solución:** Verifica las credenciales en `api-transferencias/conexion-central.php`.

### **Error: "Error sincronizando cliente a sucursal"**

**Solución:** Verifica que los datos de conexión a las sucursales estén correctos en la tabla `sucursales`.

### **Error: "Column 'documento' cannot be null"**

**Solución:** Asegúrate de que el campo `documento` siempre tenga un valor al crear/editar clientes.

---

## ✅ **VERIFICACIÓN FINAL**

Después de la migración, verifica:

1. ✅ Tabla `clientes_central` creada correctamente
2. ✅ Puedes acceder a "Clientes Centrales" desde el menú
3. ✅ Puedes crear clientes centrales
4. ✅ La detección de duplicados funciona
5. ✅ La sincronización automática funciona al editar
6. ✅ Los clientes se sincronizan a las sucursales asignadas
7. ✅ La tabla `clientes` local NO se modificó

---

## 📝 **NOTAS IMPORTANTES**

- ⚠️ **NO modificar** la tabla `clientes` local existente
- ⚠️ **NO afectar** la funcionalidad existente del sistema
- ✅ **SÍ detectar** duplicados por documento o email
- ✅ **SÍ sincronizar** automáticamente a todas las sucursales
- ✅ **SÍ mantener** consistencia de datos entre sucursales

---

## 🎉 **¡LISTO!**

Una vez completados todos los pasos, el sistema de gestión central de clientes estará operativo y listo para usar.

**Fecha de migración:** ____________

**Realizado por:** ____________

**Verificado por:** ____________
