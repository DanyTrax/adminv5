# 📋 PROPUESTA: GESTIÓN CENTRAL DE CLIENTES

## 🎯 **OBJETIVO**

Implementar un sistema de gestión centralizada de clientes que:
- ✅ **NO modifica** la tabla `clientes` local
- ✅ **NO afecta** la funcionalidad existente
- ✅ **Detecta duplicados** por documento o email
- ✅ **Sincroniza automáticamente** a todas las sucursales

---

## 🏗️ **ARQUITECTURA**

```
┌──────────────────────┐    ┌──────────────────────┐    ┌──────────────────────┐
│    SUCURSAL A        │    │    SUCURSAL B        │    │    SUCURSAL C        │
│                      │    │                      │    │                      │
│ Tabla: clientes      │    │ Tabla: clientes      │    │ Tabla: clientes      │
│ - Sin cambios        │    │ - Sin cambios        │    │ - Sin cambios        │
│ - Funcionalidad      │    │ - Funcionalidad      │    │ - Funcionalidad      │
│   intacta           │    │   intacta           │    │   intacta           │
└──────────┬───────────┘    └──────────┬───────────┘    └──────────┬───────────┘
           │                           │                           │
           └───────────────────────────┼───────────────────────────┘
                                       │
                           ┌───────────▼───────────┐
                           │   SISTEMA CENTRAL     │
                           │                       │
                           │ Tabla: clientes_central│
                           │                       │
                           │ - id_central          │
                           │ - documento (ÚNICO)    │
                           │ - email (ÚNICO)       │
                           │ - nombre              │
                           │ - telefono            │
                           │ - direccion           │
                           │ - sucursales_asignadas│
                           │ - sucursal_origen     │
                           └───────────────────────┘
```

---

## 🔧 **COMPONENTES**

### **1. Tabla Central (NUEVA)**

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

### **2. Archivos a Crear**

| Archivo | Descripción |
|---------|-------------|
| `modelos/clientes-central.modelo.php` | Operaciones BD central |
| `controladores/clientes-central.controlador.php` | Lógica de negocio |
| `vistas/modulos/clientes-central.php` | Interfaz de usuario |
| `vistas/js/clientes-central.js` | JavaScript y AJAX |
| `ajax/clientes-central.ajax.php` | Endpoints AJAX |

---

## 📊 **FLUJO DE TRABAJO**

### **CASO 1: Crear Cliente en Sucursal**

```
┌─────────────────────────────────────────┐
│ Usuario crea cliente en sucursal       │
│ documento: 123456789                    │
│ email: cliente@email.com                │
└────────────┬────────────────────────────┘
             │
             ▼
┌─────────────────────────────────────────┐
│ Verificar en central                   │
│ ¿Existe por documento O email?         │
└────────────┬────────────────────────────┘
             │
     ┌───────┴───────┐
     │               │
    SÍ              NO
     │               │
     ▼               ▼
┌──────────┐  ┌──────────────┐
│ ADVERTIR │  │ CREAR EN     │
│ Usuario  │  │ Central +    │
│ existe   │  │ Local        │
└──────────┘  └──────────────┘
```

### **CASO 2: Editar Cliente en Sucursal**

```
┌─────────────────────────────────────────┐
│ Usuario edita cliente en sucursal     │
└────────────┬────────────────────────────┘
             │
             ▼
┌─────────────────────────────────────────┐
│ Actualizar en central                   │
└────────────┬────────────────────────────┘
             │
             ▼
┌─────────────────────────────────────────┐
│ Sincronizar a todas las sucursales     │
│ activas asignadas                       │
└─────────────────────────────────────────┘
```

---

## ✅ **FUNCIONALIDADES**

### **1. Detección de Duplicados**
- ✅ **Por documento:** Campo único en central
- ✅ **Por email:** Campo único si no está vacío
- ✅ **Mensaje claro:** "Este cliente ya existe en otra sucursal"

### **2. Sincronización Automática**
- ✅ **Al crear:** Crea en central y local
- ✅ **Al editar:** Actualiza en central y todas las sucursales
- ✅ **Al eliminar:** Elimina de central y todas las sucursales

### **3. Gestión Central**
- ✅ **Vista unificada:** Todos los clientes de todas las sucursales
- ✅ **Filtros:** Por sucursal, por estado, por búsqueda
- ✅ **Asignación:** Seleccionar sucursales para cliente

---

## 🚀 **VENTAJAS**

### **Para el Negocio**
- ✅ Vista unificada de todos los clientes
- ✅ Evita duplicados y confusión
- ✅ Mejor servicio al cliente
- ✅ Datos consistentes entre sucursales

### **Para los Usuarios**
- ✅ Interfaz familiar (similar a usuarios centrales)
- ✅ Gestión centralizada pero flexible
- ✅ Sincronización automática transparente
- ✅ Prevención de errores de duplicación

### **Para el Sistema**
- ✅ **NO modifica** estructura existente
- ✅ **NO afecta** funcionalidad actual
- ✅ Escalable para nuevas sucursales
- ✅ Mantenible y robusto

---

## 📈 **COSTOS DE IMPLEMENTACIÓN**

| Concepto | Estimación |
|----------|------------|
| ⏱️ Tiempo | 4-6 horas |
| 💻 Líneas de código | ~1,500 líneas |
| 📁 Archivos nuevos | 5 archivos |
| 🔧 Modificaciones | 2 archivos existentes |

---

## 🔍 **DETALLES TÉCNICOS**

### **Estructura de Tabla clientes (Actual)**

```sql
- id
- nombre
- documento
- email
- telefono
- direccion
- fecha_nacimiento
- compras
- ultima_compra
- fecha
```

### **Campos Clave para Detección**

1. **documento:** Único por cliente (campo principal)
2. **email:** Único si no está vacío (campo secundario)

### **Sincronización**

- **Crear:** Inserta en central y local
- **Editar:** Actualiza en central y todas las sucursales asignadas
- **Eliminar:** Elimina de central y todas las sucursales asignadas

---

## 🎯 **PREGUNTAS PARA REFINAR**

1. ¿Hay algún campo específico que NO se debe sincronizar?
2. ¿Prefieres sincronización automática o manual?
3. ¿Hay algún campo de `clientes` que no vimos?
4. ¿Cómo manejar conflictos si dos sucursales editan el mismo cliente?

---

## ✅ **RESUMEN**

Esta propuesta permite:
- ✅ Gestionar clientes de forma centralizada
- ✅ Evitar duplicados automáticamente
- ✅ Sincronizar cambios a todas las sucursales
- ✅ **SIN modificar** estructura existente
- ✅ **SIN afectar** funcionalidad actual

**¿Te parece bien esta propuesta? ¿Quieres que la implemente?** 🚀