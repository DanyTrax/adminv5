# 📁 Scripts SQL del Sistema

## 📋 **DESCRIPCIÓN**

Esta carpeta contiene los scripts SQL necesarios para el funcionamiento del sistema multi-sucursal. Cada script tiene un propósito específico y debe ejecutarse en el contexto correcto.

---

## 📂 **ARCHIVOS INCLUIDOS**

### **1. `crear-abonos-historial.sql`**
- **Propósito:** Crear la tabla `abonos_historial` para el sistema de abonos
- **Ubicación:** Base de datos **LOCAL** de cada sucursal
- **Cuándo usar:** Durante la instalación de nuevas sucursales
- **Tabla creada:** `abonos_historial`
- **Campos principales:** id, id_venta, codigo_venta, monto_abono, fecha_abono, etc.

### **2. `agregar-columnas-bd.sql`**
- **Propósito:** Agregar columnas de conexión BD a la tabla `sucursal_local`
- **Ubicación:** Base de datos **LOCAL** de cada sucursal
- **Cuándo usar:** Para migrar sucursales existentes
- **Columnas agregadas:** usuario_bd, password_bd, nombre_bd, host_bd, puerto_bd
- **Nota:** Solo necesario para sucursales que no tienen estas columnas

### **3. `crear-tabla-registro-descargas-simple.sql`**
- **Propósito:** Crear la tabla `registro_descargas_stock_transito` para el sistema de stock
- **Ubicación:** Base de datos **LOCAL** de cada sucursal
- **Cuándo usar:** Durante la instalación de nuevas sucursales
- **Tabla creada:** `registro_descargas_stock_transito`
- **Funcionalidad:** Registro de descargas de stock en tránsito

### **4. `crear-tablas-trazabilidad.sql`**
- **Propósito:** Crear el sistema completo de trazabilidad para despachos
- **Ubicación:** Base de datos **LOCAL** de cada sucursal
- **Cuándo usar:** Durante la instalación de nuevas sucursales
- **Tablas creadas:** 
  - `trazabilidad_movimientos` (tabla principal)
  - `trazabilidad_descargas` (descargas individuales)
  - `trazabilidad_relaciones` (relaciones entre despachos)
- **Funcionalidad:** Sistema completo de trazabilidad de despachos

---

## 🚀 **INSTRUCCIONES DE USO**

### **Para Nuevas Instalaciones**
Los scripts se ejecutan automáticamente durante el proceso de instalación a través del instalador web.

### **Para Migraciones Manuales**
1. **Conectar a la base de datos local** de la sucursal
2. **Ejecutar los scripts necesarios** según el caso:
   - `crear-abonos-historial.sql` - Siempre necesario
   - `crear-tabla-registro-descargas-simple.sql` - Siempre necesario
   - `crear-tablas-trazabilidad.sql` - Siempre necesario
   - `agregar-columnas-bd.sql` - Solo si faltan las columnas

### **Verificación Post-Ejecución**
Después de ejecutar los scripts, verificar que las tablas se crearon correctamente:
```sql
SHOW TABLES LIKE '%abonos%';
SHOW TABLES LIKE '%registro%';
SHOW TABLES LIKE '%trazabilidad%';
DESCRIBE sucursal_local;
```

---

## ⚠️ **NOTAS IMPORTANTES**

### **Orden de Ejecución**
1. **Primero:** `crear-abonos-historial.sql`
2. **Segundo:** `crear-tabla-registro-descargas-simple.sql`
3. **Tercero:** `crear-tablas-trazabilidad.sql`
4. **Último:** `agregar-columnas-bd.sql` (solo si es necesario)

### **Compatibilidad**
- **MySQL:** 5.7 o superior
- **MariaDB:** 10.3 o superior
- **Charset:** utf8mb3/utf8mb4
- **Engine:** InnoDB

### **Backup Recomendado**
Siempre crear un backup de la base de datos antes de ejecutar estos scripts en producción.

---

## 🔧 **INTEGRACIÓN CON EL INSTALADOR**

Estos scripts están integrados en el instalador automático (`instalacion/instalador-nuevo.php`) y se ejecutan durante el proceso de instalación de nuevas sucursales.

### **Ubicación en el Instalador**
Los scripts se encuentran en la función `crearTablasBD()` del instalador y se ejecutan automáticamente.

---

## 📞 **SOPORTE**

Si encuentras problemas con la ejecución de estos scripts:
1. Verificar la conectividad a la base de datos
2. Verificar los permisos del usuario de BD
3. Revisar los logs de error del sistema
4. Contactar al administrador del sistema

---

**Última actualización:** Diciembre 2025  
**Versión:** 5.0  
**Estado:** Producción**
