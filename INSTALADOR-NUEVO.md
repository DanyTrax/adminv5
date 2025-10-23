# Instalador de Sucursal - Versión 2.0

## 🎯 Objetivo

Replantear el instalador de sucursal para enfocarse en la configuración esencial, ya que los usuarios y clientes ahora se sincronizan desde la gestión central.

## 🔄 Cambios Principales

### ❌ Eliminado (Ya no necesario)
- **Importación masiva de usuarios**: Los usuarios se sincronizan desde el sistema central
- **Importación masiva de clientes**: Los clientes se sincronizan desde el sistema central
- **Configuración compleja de permisos**: Se maneja desde el sistema central
- **Múltiples pasos de configuración**: Simplificado a 5 pasos esenciales

### ✅ Nuevo Enfoque
- **Configuración básica de sucursal**: Datos esenciales de la sucursal
- **Configuración de base de datos**: Conexión y creación de BD
- **Primer usuario administrador**: Solo un usuario inicial
- **Conexión con sistema central**: Para sincronización automática
- **Instalación automática**: Proceso simplificado

## 📋 Pasos del Nuevo Instalador

### Paso 1: Configuración de la Sucursal
- Código de sucursal (único)
- Nombre de la sucursal
- Dirección, teléfono, email
- URL base y API

### Paso 2: Configuración de Base de Datos
- Host y puerto de MySQL
- Usuario y contraseña
- Nombre de la base de datos (se crea automáticamente)

### Paso 3: Primer Usuario Administrador
- Nombre completo
- Usuario de login
- Contraseña
- Perfil: Administrador

### Paso 4: Conexión con Sistema Central
- URL del sistema central
- API Key (opcional)
- Configuración de sincronización

### Paso 5: Ejecutar Instalación
- Revisar configuración
- Ejecutar instalación automática
- Crear archivos de configuración
- Crear tablas básicas
- Insertar datos iniciales

## 🗂️ Archivos Creados

### `instalacion/instalador-nuevo.php`
- Instalador principal simplificado
- Interfaz moderna con Bootstrap 5
- Proceso paso a paso
- Validaciones integradas

### `instalacion/config-nuevo.php`
- Configuración del instalador
- Constantes y mensajes
- Funciones de validación
- Configuración por defecto

## 🎨 Características de la Interfaz

- **Diseño moderno**: Bootstrap 5 + Font Awesome
- **Indicador de pasos**: Visual progress indicator
- **Responsive**: Adaptable a diferentes pantallas
- **Validaciones**: En tiempo real
- **Mensajes claros**: Feedback inmediato

## 🔧 Funcionalidades Técnicas

### Creación Automática de Base de Datos
```php
// Crear BD si no existe
$pdo->exec("CREATE DATABASE `{$nombre_bd}` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
```

### Tablas Esenciales Creadas
- `usuarios`: Solo para el primer administrador
- `sucursal_local`: Configuración de la sucursal

### Archivo config.php Generado
```php
// Configuración automática
define('DB_HOST', 'localhost');
define('DB_NAME', 'sucursal_bd');
define('CODIGO_SUCURSAL', 'SUC001');
define('URL_CENTRAL', 'https://central.empresa.com/');
```

## 🚀 Ventajas del Nuevo Instalador

1. **Más rápido**: 5 pasos vs 15+ pasos anteriores
2. **Más simple**: Enfoque en lo esencial
3. **Más moderno**: Interfaz actualizada
4. **Más seguro**: Validaciones mejoradas
5. **Más escalable**: Preparado para múltiples sucursales

## 📝 Próximos Pasos

1. **Probar el nuevo instalador** en un entorno de desarrollo
2. **Documentar el proceso** para los administradores
3. **Crear guía de migración** del instalador anterior
4. **Implementar en producción** cuando esté listo

## 🔄 Migración del Instalador Anterior

Para migrar del instalador anterior:

1. **Backup** de la configuración actual
2. **Reemplazar** archivos del instalador
3. **Probar** en entorno de desarrollo
4. **Implementar** en producción
5. **Documentar** cambios para usuarios

## 📞 Soporte

Para dudas sobre el nuevo instalador:
- Revisar este documento
- Consultar logs de instalación
- Contactar al equipo de desarrollo
