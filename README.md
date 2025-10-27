# 🚀 Sistema de Gestión Multi-Sucursal - AdminV5

## 📋 **INFORMACIÓN GENERAL**

**Versión:** 5.0  
**Última actualización:** Diciembre 2025  
**Arquitectura:** Multi-sucursal con sistema central  
**Tecnologías:** PHP, MySQL, JavaScript, Bootstrap, jQuery  

---

## 🎯 **DESCRIPCIÓN DEL SISTEMA**

Sistema completo de gestión empresarial multi-sucursal que permite:
- **Gestión centralizada** de usuarios, clientes y productos
- **Sincronización automática** entre sucursales
- **Control de stock** en tránsito entre sucursales
- **Sistema de despachos** y solicitudes de stock
- **Reportes centralizados** y por sucursal
- **Gestión de medios de pago** centralizada
- **Salidas de inventario** con trazabilidad

---

## 🏗️ **ARQUITECTURA DEL SISTEMA**

```
┌──────────────────────┐    ┌──────────────────────┐    ┌──────────────────────┐
│    SUCURSAL A        │    │    SUCURSAL B        │    │    SUCURSAL C        │
│                      │    │                      │    │                      │
│ • Base de datos local│    │ • Base de datos local│    │ • Base de datos local│
│ • Usuarios locales   │    │ • Usuarios locales   │    │ • Usuarios locales   │
│ • Productos locales  │    │ • Productos locales  │    │ • Productos locales  │
│ • Ventas locales     │    │ • Ventas locales     │    │ • Ventas locales     │
└──────────┬───────────┘    └──────────┬───────────┘    └──────────┬───────────┘
           │                           │                           │
           └───────────────────────────┼───────────────────────────┘
                                       │
                           ┌───────────▼───────────┐
                           │   SISTEMA CENTRAL     │
                           │                       │
                           │ • usuarios_central    │
                           │ • clientes_central    │
                           │ • sucursales          │
                           │ • medios_pago_central │
                           │ • stock_transito      │
                           │ • registro_descargas  │
                           └───────────────────────┘
```

---

## 📦 **MÓDULOS PRINCIPALES**

### **🔐 Gestión de Usuarios**
- **Usuarios Locales:** Gestión por sucursal
- **Usuarios Centrales:** Gestión centralizada
- **Perfiles:** Administrador, Especial, Vendedor, Contador, Transportador, Limitado
- **Sincronización:** Automática entre central y sucursales

### **👥 Gestión de Clientes**
- **Clientes Locales:** Gestión tradicional por sucursal
- **Clientes Centrales:** Gestión centralizada con sincronización
- **Detección de duplicados:** Por documento y email
- **Sincronización bidireccional:** Central ↔ Sucursales

### **📦 Gestión de Productos**
- **Productos Locales:** Gestión por sucursal
- **Categorías Centrales:** Sincronización automática
- **Stock en Tránsito:** Control entre sucursales
- **Solicitudes de Stock:** Sistema de solicitudes entre sucursales

### **🚚 Sistema de Despachos**
- **Creación de Despachos:** Entre sucursales
- **Control de Transporte:** Asignación de transportadores
- **Estados:** En espera, En tránsito, Entregado, Cancelado
- **Trazabilidad:** Seguimiento completo del proceso

### **💰 Gestión de Ventas**
- **Ventas Locales:** Por sucursal
- **Reportes Centralizados:** Consolidados
- **Medios de Pago:** Gestión centralizada
- **Facturación:** PDF automático

### **📊 Reportes y Contabilidad**
- **Reportes por Sucursal:** Individuales
- **Reportes Centralizados:** Consolidados
- **Contabilidad:** Por sucursal y central
- **Estadísticas:** En tiempo real

### **🏪 Gestión de Sucursales**
- **Configuración:** Datos básicos y BD
- **Conexión Central:** Configuración automática
- **Sincronización:** Estado y configuración
- **Monitoreo:** Estado de conexión

### **💳 Medios de Pago Centrales**
- **Gestión Centralizada:** Creación y edición
- **Asignación a Sucursales:** Activación por sucursal
- **Sincronización:** Automática a sucursales locales
- **Estados:** Activo/Inactivo por sucursal

### **📤 Salidas de Inventario**
- **Registro de Salidas:** Con descripción y remisión
- **Control de Cantidades:** Máximo 10 unidades
- **Búsqueda AJAX:** Productos y remisiones
- **Actualización de Stock:** Automática
- **Permisos:** Solo Administrador y Especial

---

## 🛠️ **INSTALACIÓN**

### **Requisitos del Sistema**
- **PHP:** 7.4 o superior
- **MySQL:** 5.7 o superior / MariaDB 10.3+
- **Servidor Web:** Apache/Nginx
- **Extensiones PHP:** PDO, cURL, GD, mbstring

### **Instalación de Nueva Sucursal**

1. **Acceder al Instalador:**
   ```
   https://tu-dominio.com/instalacion/
   ```

2. **Paso 1: Configuración de Sucursal**
   - Código de sucursal (único)
   - Nombre, dirección, teléfono, email
   - URL base y API

3. **Paso 2: Configuración de Base de Datos**
   - Host, puerto, nombre de BD
   - Usuario y contraseña
   - Creación automática de BD

4. **Paso 3: Usuario Administrador**
   - Datos del primer administrador
   - Credenciales de acceso

5. **Paso 4: Conexión Central**
   - URL del sistema central
   - Configuración de sincronización

6. **Paso 5: Instalación**
   - Creación automática de tablas
   - Importación de datos iniciales
   - Configuración final

### **Importación de Medios de Pago**
- **Opción disponible** en el instalador
- **Selección de sucursales** para importar
- **Importación automática** desde sucursales activas

---

## 🔧 **CONFIGURACIÓN**

### **Archivos de Configuración**
- **`config.php`:** Configuración principal
- **`modelos/conexion.php`:** Conexión a BD local
- **`api-transferencias/conexion-central.php`:** Conexión a BD central

### **Configuración de Sucursal**
- **Datos básicos:** Nombre, dirección, contacto
- **Base de datos:** Credenciales locales
- **Conexión central:** URL y configuración
- **Sincronización:** Automática

### **Configuración Central**
- **Base de datos central:** Configuración principal
- **Sucursales:** Registro y configuración
- **Usuarios centrales:** Gestión centralizada
- **Medios de pago:** Gestión centralizada

---

## 📊 **ESTRUCTURA DE BASE DE DATOS**

### **Tablas Locales (por sucursal)**
- **`usuarios`:** Usuarios de la sucursal
- **`clientes`:** Clientes de la sucursal
- **`productos`:** Productos de la sucursal
- **`categorias`:** Categorías locales
- **`ventas`:** Ventas de la sucursal
- **`venta_productos`:** Detalle de ventas
- **`contabilidad`:** Contabilidad local
- **`abonos_historial`:** Historial de abonos
- **`medios_pago`:** Medios de pago locales
- **`salidas_inventario`:** Salidas de inventario
- **`sucursal_local`:** Configuración local

### **Tablas Centrales**
- **`usuarios_central`:** Usuarios centrales
- **`clientes_central`:** Clientes centrales
- **`sucursales`:** Configuración de sucursales
- **`categorias_central`:** Categorías centrales
- **`medios_pago_central`:** Medios de pago centrales
- **`medios_pago_sucursal`:** Asignación a sucursales
- **`stock_transito`:** Stock en tránsito
- **`registro_descargas_stock_transito`:** Registro de descargas
- **`sincronizacion_maestro`:** Sincronización de productos

---

## 🔄 **SINCRONIZACIÓN**

### **Tipos de Sincronización**
- **Usuarios:** Central → Sucursales
- **Clientes:** Bidireccional (Central ↔ Sucursales)
- **Categorías:** Central → Sucursales
- **Medios de Pago:** Central → Sucursales
- **Stock:** Entre sucursales
- **Despachos:** Central ↔ Sucursales

### **Frecuencia de Sincronización**
- **Automática:** En tiempo real para operaciones críticas
- **Manual:** Para operaciones masivas
- **Programada:** Para mantenimiento y limpieza

---

## 🔐 **PERMISOS Y ROLES**

### **Perfiles de Usuario**
- **Administrador:** Acceso completo a todo el sistema
- **Especial:** Acceso amplio con algunas restricciones
- **Vendedor:** Gestión de ventas y clientes
- **Contador:** Reportes y contabilidad
- **Transportador:** Solo despachos y entregas
- **Limitado:** Acceso restringido

### **Permisos por Módulo**
- **Ver:** Visualización de datos
- **Crear:** Creación de nuevos registros
- **Editar:** Modificación de registros existentes
- **Eliminar:** Eliminación de registros
- **Exportar:** Exportación de datos
- **Imprimir:** Generación de reportes

---

## 📱 **INTERFAZ DE USUARIO**

### **Características**
- **Responsive Design:** Adaptable a dispositivos móviles
- **Bootstrap 5:** Framework CSS moderno
- **DataTables:** Tablas interactivas
- **SweetAlert:** Alertas y confirmaciones
- **AJAX:** Operaciones asíncronas
- **Modales:** Formularios y confirmaciones

### **Navegación**
- **Menú Principal:** Navegación por módulos
- **Breadcrumbs:** Navegación contextual
- **Búsqueda Global:** Búsqueda en tiempo real
- **Filtros Avanzados:** Filtrado por múltiples criterios

---

## 🚀 **API Y SERVICIOS**

### **Endpoints Principales**
- **`/api-transferencias/`:** API de transferencias
- **`/ajax/`:** Endpoints AJAX
- **Autenticación:** Sistema de sesiones
- **CORS:** Configurado para comunicación entre dominios

### **Servicios Externos**
- **PDF Generation:** TCPDF para facturas
- **Email:** PHPMailer para notificaciones
- **Logs:** Sistema de logging integrado

---

## 🔧 **MANTENIMIENTO**

### **Logs del Sistema**
- **Error Logs:** Errores de aplicación
- **Access Logs:** Acceso y operaciones
- **Sync Logs:** Sincronización entre sistemas
- **Debug Logs:** Información de depuración

### **Backup y Restauración**
- **Backup Automático:** Base de datos y archivos
- **Restauración:** Procedimientos documentados
- **Versionado:** Control de versiones con Git

### **Monitoreo**
- **Estado de Sucursales:** Monitoreo de conexión
- **Sincronización:** Estado de sincronización
- **Rendimiento:** Métricas de rendimiento
- **Alertas:** Notificaciones automáticas

---

## 📚 **DOCUMENTACIÓN TÉCNICA**

### **Archivos de Documentación**
- **`README.md`:** Este archivo (documentación principal)
- **`INSTALADOR-NUEVO.md`:** Documentación del instalador
- **`INSTRUCCIONES-CPANEL.md`:** Instrucciones para cPanel
- **`MIGRACION-CPANEL.md`:** Migración de base de datos

### **Scripts de Utilidad**
- **`crear-abonos-historial.sql`:** Creación de tabla de abonos
- **`agregar-columnas-bd.sql`:** Agregar columnas a sucursal_local
- **`crear-tabla-registro-descargas-simple.sql`:** Tabla de registro
- **`crear-tablas-trazabilidad.sql`:** Sistema de trazabilidad

---

## 🐛 **RESOLUCIÓN DE PROBLEMAS**

### **Problemas Comunes**
- **Error de conexión:** Verificar configuración de BD
- **Sincronización fallida:** Verificar conectividad
- **Permisos:** Verificar configuración de archivos
- **Performance:** Optimizar consultas y índices

### **Logs de Error**
- **Ubicación:** `/error_log`
- **Formato:** Timestamp, nivel, mensaje
- **Rotación:** Automática por tamaño

---

## 🔄 **ACTUALIZACIONES**

### **Control de Versiones**
- **Git:** Repositorio principal
- **Commits:** Documentados y versionados
- **Branches:** Desarrollo y producción
- **Tags:** Versiones estables

### **Proceso de Actualización**
1. **Backup:** Crear respaldo completo
2. **Descarga:** Obtener nueva versión
3. **Instalación:** Aplicar cambios
4. **Verificación:** Probar funcionalidades
5. **Rollback:** Procedimiento de reversión

---

## 📞 **SOPORTE**

### **Información de Contacto**
- **Desarrollador:** DanyTrax
- **Repositorio:** GitHub
- **Documentación:** Este README
- **Issues:** Sistema de tickets

### **Recursos Adicionales**
- **Wiki:** Documentación extendida
- **FAQ:** Preguntas frecuentes
- **Tutoriales:** Guías paso a paso
- **Ejemplos:** Casos de uso

---

## 📄 **LICENCIA**

Este proyecto es propiedad privada. Todos los derechos reservados.

---

## 🎉 **AGRADECIMIENTOS**

Gracias a todos los contribuidores y usuarios que han ayudado a mejorar este sistema.

---

**Última actualización:** Diciembre 2025  
**Versión:** 5.0  
**Estado:** Producción**
