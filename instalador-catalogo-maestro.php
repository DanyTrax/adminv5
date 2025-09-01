<?php
/**
 * INSTALADOR AUTOMÁTICO DEL CATÁLOGO MAESTRO
 * ===========================================
 * 
 * Este script creará automáticamente:
 * 1. Tablas necesarias en base de datos central
 * 2. Migración de categorías y productos existentes
 * 3. Configuración inicial del sistema
 * 
 * INSTRUCCIONES:
 * - Ejecutar UNA SOLA VEZ desde el navegador: tudominio.com/instalador-catalogo-maestro.php
 * - Eliminar el archivo después de la instalación por seguridad
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(300); // 5 minutos máximo

require_once "modelos/conexion.php";

class InstaladorCatalogoMaestro {
    
    private static $dbLocal = null;
    private static $dbCentral = null;
    
    /*=============================================
    CONEXIÓN A BASE DE DATOS CENTRAL
    =============================================*/
    private static function conectarCentral() {
        if (self::$dbCentral === null) {
            try {
                $servidor = "localhost";
                $nombreBD = "epicosie_central";
                $usuario = "epicosie_central"; 
                $password = "=Nf?M#6A'QU&.6c";
                
                self::$dbCentral = new PDO(
                    "mysql:host=$servidor;dbname=$nombreBD;charset=utf8mb4",
                    $usuario,
                    $password
                );
                self::$dbCentral->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
            } catch (PDOException $e) {
                throw new Exception("Error conectando a base central: " . $e->getMessage());
            }
        }
        return self::$dbCentral;
    }
    
    /*=============================================
    CONEXIÓN A BASE DE DATOS LOCAL
    =============================================*/
    private static function conectarLocal() {
        if (self::$dbLocal === null) {
            try {
                self::$dbLocal = Conexion::conectar();
            } catch (Exception $e) {
                throw new Exception("Error conectando a base local: " . $e->getMessage());
            }
        }
        return self::$dbLocal;
    }
    
    /*=============================================
    VERIFICAR SI YA ESTÁ INSTALADO
    =============================================*/
    public static function verificarInstalacion() {
        try {
            $dbCentral = self::conectarCentral();
            $stmt = $dbCentral->prepare("SHOW TABLES LIKE 'catalogo_maestro'");
            $stmt->execute();
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /*=============================================
    PASO 1: CREAR TABLA CATEGORÍAS EN CENTRAL
    =============================================*/
    private static function crearTablaCategoriasCentral() {
        
        echo "<li>🔄 Creando tabla categorías en base central...</li>";
        
        $dbCentral = self::conectarCentral();
        
        $sqlCategorias = "
        CREATE TABLE IF NOT EXISTS categorias (
            id INT(11) NOT NULL AUTO_INCREMENT,
            categoria VARCHAR(50) NOT NULL,
            fecha TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY categoria (categoria)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci
        ";
        
        $dbCentral->exec($sqlCategorias);
        echo "<li>✅ Tabla categorías creada en base central</li>";
    }
    
    /*=============================================
    PASO 2: MIGRAR CATEGORÍAS DE LOCAL A CENTRAL
    =============================================*/
    private static function migrarCategorias() {
        
        echo "<li>🔄 Migrando categorías de local a central...</li>";
        
        $dbLocal = self::conectarLocal();
        $dbCentral = self::conectarCentral();
        
        // Obtener categorías locales
        $stmtLocal = $dbLocal->prepare("SELECT * FROM categorias ORDER BY id ASC");
        $stmtLocal->execute();
        $categoriasLocales = $stmtLocal->fetchAll(PDO::FETCH_ASSOC);
        
        $categoriasInsertadas = 0;
        
        foreach ($categoriasLocales as $categoria) {
            try {
                $stmtCentral = $dbCentral->prepare("
                    INSERT INTO categorias (id, categoria, fecha) 
                    VALUES (:id, :categoria, :fecha)
                    ON DUPLICATE KEY UPDATE 
                    categoria = VALUES(categoria), 
                    fecha = VALUES(fecha)
                ");
                
                $stmtCentral->bindParam(":id", $categoria['id'], PDO::PARAM_INT);
                $stmtCentral->bindParam(":categoria", $categoria['categoria'], PDO::PARAM_STR);
                $stmtCentral->bindParam(":fecha", $categoria['fecha'], PDO::PARAM_STR);
                
                $stmtCentral->execute();
                $categoriasInsertadas++;
                
            } catch (PDOException $e) {
                echo "<li>⚠️ Error migrando categoría {$categoria['categoria']}: " . $e->getMessage() . "</li>";
            }
        }
        
        echo "<li>✅ {$categoriasInsertadas} categorías migradas a base central</li>";
    }
    
    /*=============================================
    PASO 3: CREAR TABLA CATÁLOGO MAESTRO
    =============================================*/
    private static function crearTablaCatalogoMaestro() {
        
        echo "<li>🔄 Creando tabla catálogo maestro...</li>";
        
        $dbCentral = self::conectarCentral();
        
        $sqlCatalogoMaestro = "
        CREATE TABLE IF NOT EXISTS catalogo_maestro (
            id INT(11) NOT NULL AUTO_INCREMENT,
            codigo VARCHAR(50) NOT NULL UNIQUE,
            descripcion VARCHAR(255) NOT NULL,
            id_categoria INT(11) NOT NULL,
            precio_venta DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            imagen TEXT,
            es_divisible TINYINT(1) NOT NULL DEFAULT 0,
            codigo_hijo_mitad VARCHAR(50) NULL,
            codigo_hijo_tercio VARCHAR(50) NULL,
            codigo_hijo_cuarto VARCHAR(50) NULL,
            es_hijo TINYINT(1) NOT NULL DEFAULT 0,
            codigo_padre VARCHAR(50) NULL,
            tipo_division ENUM('mitad','tercio','cuarto') NULL,
            activo TINYINT(1) NOT NULL DEFAULT 1,
            fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_codigo (codigo),
            KEY idx_categoria (id_categoria),
            KEY idx_padre (codigo_padre),
            KEY idx_activo (activo),
            FOREIGN KEY (id_categoria) REFERENCES categorias(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci
        ";
        
        $dbCentral->exec($sqlCatalogoMaestro);
        echo "<li>✅ Tabla catálogo maestro creada</li>";
    }
    
    /*=============================================
    PASO 4: MIGRAR PRODUCTOS AL CATÁLOGO MAESTRO
    =============================================*/
    private static function migrarProductosACatalogoMaestro() {
        
        echo "<li>🔄 Migrando productos existentes al catálogo maestro...</li>";
        
        $dbLocal = self::conectarLocal();
        $dbCentral = self::conectarCentral();
        
        // Obtener productos únicos (sin duplicar por código)
        $stmtLocal = $dbLocal->prepare("
            SELECT 
                id_categoria, 
                codigo, 
                descripcion, 
                imagen, 
                precio_venta, 
                es_divisible,
                nombre_mitad,
                precio_mitad,
                nombre_tercio,
                precio_tercio,
                nombre_cuarto,
                precio_cuarto
            FROM productos 
            WHERE codigo IS NOT NULL AND codigo != '' 
            GROUP BY codigo 
            ORDER BY codigo ASC
        ");
        
        $stmtLocal->execute();
        $productosLocales = $stmtLocal->fetchAll(PDO::FETCH_ASSOC);
        
        $productosInsertados = 0;
        
        foreach ($productosLocales as $producto) {
            try {
                
                // Verificar que la categoría existe en central
                $stmtCatExiste = $dbCentral->prepare("SELECT id FROM categorias WHERE id = :id_categoria");
                $stmtCatExiste->bindParam(":id_categoria", $producto['id_categoria'], PDO::PARAM_INT);
                $stmtCatExiste->execute();
                
                if ($stmtCatExiste->rowCount() == 0) {
                    echo "<li>⚠️ Categoría {$producto['id_categoria']} no existe para producto {$producto['codigo']}</li>";
                    continue;
                }
                
                $stmtCentral = $dbCentral->prepare("
                    INSERT INTO catalogo_maestro 
                    (codigo, descripcion, id_categoria, precio_venta, imagen, es_divisible, codigo_hijo_mitad, codigo_hijo_tercio, codigo_hijo_cuarto) 
                    VALUES 
                    (:codigo, :descripcion, :id_categoria, :precio_venta, :imagen, :es_divisible, :codigo_hijo_mitad, :codigo_hijo_tercio, :codigo_hijo_cuarto)
                    ON DUPLICATE KEY UPDATE
                    descripcion = VALUES(descripcion),
                    precio_venta = VALUES(precio_venta),
                    imagen = VALUES(imagen)
                ");
                
                // Determinar códigos de hijos basándose en los nombres
                $codigoHijoMitad = !empty($producto['nombre_mitad']) ? $producto['codigo'] . 'M' : '';
                $codigoHijoTercio = !empty($producto['nombre_tercio']) ? $producto['codigo'] . 'T' : '';
                $codigoHijoCuarto = !empty($producto['nombre_cuarto']) ? $producto['codigo'] . 'C' : '';
                
                $stmtCentral->bindParam(":codigo", $producto['codigo'], PDO::PARAM_STR);
                $stmtCentral->bindParam(":descripcion", $producto['descripcion'], PDO::PARAM_STR);
                $stmtCentral->bindParam(":id_categoria", $producto['id_categoria'], PDO::PARAM_INT);
                $stmtCentral->bindParam(":precio_venta", $producto['precio_venta'], PDO::PARAM_STR);
                $stmtCentral->bindParam(":imagen", $producto['imagen'], PDO::PARAM_STR);
                $stmtCentral->bindParam(":es_divisible", $producto['es_divisible'], PDO::PARAM_INT);
                $stmtCentral->bindParam(":codigo_hijo_mitad", $codigoHijoMitad, PDO::PARAM_STR);
                $stmtCentral->bindParam(":codigo_hijo_tercio", $codigoHijoTercio, PDO::PARAM_STR);
                $stmtCentral->bindParam(":codigo_hijo_cuarto", $codigoHijoCuarto, PDO::PARAM_STR);
                
                $stmtCentral->execute();
                $productosInsertados++;
                
            } catch (PDOException $e) {
                echo "<li>⚠️ Error migrando producto {$producto['codigo']}: " . $e->getMessage() . "</li>";
            }
        }
        
        echo "<li>✅ {$productosInsertados} productos migrados al catálogo maestro</li>";
    }
    
    /*=============================================
    PASO 5: CREAR TABLA DE SINCRONIZACIÓN LOCAL
    =============================================*/
    private static function crearTablaSincronizacionLocal() {
        
        echo "<li>🔄 Creando tabla de sincronización en base local...</li>";
        
        $dbLocal = self::conectarLocal();
        
        // Agregar columna codigo_maestro si no existe
        try {
            $dbLocal->exec("ALTER TABLE productos ADD COLUMN codigo_maestro VARCHAR(50) NULL AFTER codigo");
            echo "<li>✅ Columna codigo_maestro agregada a tabla productos</li>";
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
                echo "<li>✅ Columna codigo_maestro ya existe</li>";
            } else {
                echo "<li>⚠️ Error agregando columna: " . $e->getMessage() . "</li>";
            }
        }
        
        // Crear índice si no existe
        try {
            $dbLocal->exec("ALTER TABLE productos ADD KEY idx_codigo_maestro (codigo_maestro)");
            echo "<li>✅ Índice codigo_maestro creado</li>";
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate key name') !== false) {
                echo "<li>✅ Índice codigo_maestro ya existe</li>";
            }
        }
        
        // Crear tabla de sincronización
        $sqlSincronizacion = "
        CREATE TABLE IF NOT EXISTS sincronizacion_maestro (
            id INT(11) NOT NULL AUTO_INCREMENT,
            codigo_maestro VARCHAR(50) NOT NULL,
            id_producto_local INT(11) NOT NULL,
            ultima_sincronizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY unique_sync (codigo_maestro, id_producto_local),
            KEY idx_codigo_maestro (codigo_maestro),
            FOREIGN KEY (id_producto_local) REFERENCES productos(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci
        ";
        
        $dbLocal->exec($sqlSincronizacion);
        echo "<li>✅ Tabla sincronización_maestro creada</li>";
    }
    
    /*=============================================
    PASO 6: SINCRONIZACIÓN INICIAL
    =============================================*/
    private static function sincronizacionInicial() {
        
        echo "<li>🔄 Realizando sincronización inicial...</li>";
        
        $dbLocal = self::conectarLocal();
        
        // Actualizar campo codigo_maestro en productos existentes
        $sqlUpdate = "
        UPDATE productos 
        SET codigo_maestro = codigo 
        WHERE codigo_maestro IS NULL AND codigo IS NOT NULL AND codigo != ''
        ";
        
        $resultadoUpdate = $dbLocal->exec($sqlUpdate);
        echo "<li>✅ {$resultadoUpdate} productos locales vinculados al catálogo maestro</li>";
        
        // Crear registros de sincronización inicial
        $sqlSincronizacion = "
        INSERT IGNORE INTO sincronizacion_maestro (codigo_maestro, id_producto_local)
        SELECT codigo_maestro, id 
        FROM productos 
        WHERE codigo_maestro IS NOT NULL
        ";
        
        $resultadoSync = $dbLocal->exec($sqlSincronizacion);
        echo "<li>✅ {$resultadoSync} registros de sincronización creados</li>";
    }
    
    /*=============================================
    EJECUTAR INSTALACIÓN COMPLETA
    =============================================*/
    public static function ejecutarInstalacion() {
        
        echo "<div style='font-family: Arial, sans-serif; max-width: 800px; margin: 20px auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px;'>";
        echo "<h2 style='color: #2c3e50; text-align: center;'>🚀 Instalador del Catálogo Maestro v1.0</h2>";
        echo "<hr>";
        
        try {
            
            echo "<h3>📋 Iniciando proceso de instalación...</h3>";
            echo "<ul style='line-height: 1.8;'>";
            
            // Paso 1: Crear categorías en central
            self::crearTablaCategoriasCentral();
            
            // Paso 2: Migrar categorías
            self::migrarCategorias();
            
            // Paso 3: Crear tabla catálogo maestro
            self::crearTablaCatalogoMaestro();
            
            // Paso 4: Migrar productos
            self::migrarProductosACatalogoMaestro();
            
            // Paso 5: Configurar sincronización local
            self::crearTablaSincronizacionLocal();
            
            // Paso 6: Sincronización inicial
            self::sincronizacionInicial();
            
            echo "</ul>";
            echo "<hr>";
            echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; color: #155724;'>";
            echo "<h3>🎉 ¡INSTALACIÓN COMPLETADA EXITOSAMENTE!</h3>";
            echo "<p><strong>El Catálogo Maestro ha sido instalado correctamente.</strong></p>";
            echo "<p>✅ Todas las tablas fueron creadas</p>";
            echo "<p>✅ Los datos existentes fueron migrados</p>";
            echo "<p>✅ La sincronización está configurada</p>";
            echo "</div>";
            
            echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; color: #856404; margin-top: 15px;'>";
            echo "<h4>📋 Próximos pasos:</h4>";
            echo "<ol>";
            echo "<li>Acceder al sistema: <strong><a href='index.php?ruta=catalogo-maestro' target='_blank'>Catálogo Maestro</a></strong></li>";
            echo "<li>Verificar que todos los productos aparezcan correctamente</li>";
            echo "<li>Realizar una sincronización de prueba</li>";
            echo "<li><strong>IMPORTANTE:</strong> Eliminar este archivo (instalador-catalogo-maestro.php) por seguridad</li>";
            echo "</ol>";
            echo "</div>";
            
        } catch (Exception $e) {
            echo "</ul>";
            echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; color: #721c24;'>";
            echo "<h3>❌ ERROR EN LA INSTALACIÓN</h3>";
            echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>";
            echo "<p>Por favor revise la configuración de la base de datos y vuelva a intentar.</p>";
            echo "</div>";
        }
        
        echo "<div style='text-align: center; margin-top: 30px;'>";
        echo "<a href='index.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🏠 Ir al Sistema</a>";
        echo "</div>";
        
        echo "</div>";
    }
    
    /*=============================================
    VERIFICAR REQUISITOS DEL SISTEMA
    =============================================*/
    public static function verificarRequisitos() {
        
        $errores = [];
        $advertencias = [];
        
        // Verificar versión PHP
        if (version_compare(PHP_VERSION, '7.4', '<')) {
            $errores[] = "PHP 7.4 o superior es requerido. Versión actual: " . PHP_VERSION;
        }
        
        // Verificar extensiones PHP necesarias
        $extensionesRequeridas = ['pdo', 'pdo_mysql', 'mysqli'];
        foreach ($extensionesRequeridas as $extension) {
            if (!extension_loaded($extension)) {
                $errores[] = "Extensión PHP requerida: $extension";
            }
        }
        
        // Verificar permisos de escritura
        $directoriosEscritura = [
            'vistas/img/productos',
            'pdf',
            'xml'
        ];
        
        foreach ($directoriosEscritura as $directorio) {
            if (!is_writable($directorio)) {
                $advertencias[] = "Directorio '$directorio' no tiene permisos de escritura";
            }
        }
        
        // Verificar conexión a base de datos local
        try {
            $dbLocal = self::conectarLocal();
            $stmt = $dbLocal->prepare("SELECT 1");
            $stmt->execute();
        } catch (Exception $e) {
            $errores[] = "No se puede conectar a la base de datos local: " . $e->getMessage();
        }
        
        // Verificar conexión a base de datos central
        try {
            $dbCentral = self::conectarCentral();
            $stmt = $dbCentral->prepare("SELECT 1");
            $stmt->execute();
        } catch (Exception $e) {
            $errores[] = "No se puede conectar a la base de datos central: " . $e->getMessage();
        }
        
        return [
            'errores' => $errores,
            'advertencias' => $advertencias,
            'puede_instalar' => empty($errores)
        ];
    }
    
    /*=============================================
    GENERAR REPORTE DE INSTALACIÓN
    =============================================*/
    public static function generarReporte() {
        
        try {
            $dbCentral = self::conectarCentral();
            $dbLocal = self::conectarLocal();
            
            // Contar registros en catálogo maestro
            $stmtCatalogo = $dbCentral->prepare("SELECT COUNT(*) as total FROM catalogo_maestro WHERE activo = 1");
            $stmtCatalogo->execute();
            $totalCatalogo = $stmtCatalogo->fetch()['total'];
            
            // Contar categorías centrales
            $stmtCategorias = $dbCentral->prepare("SELECT COUNT(*) as total FROM categorias");
            $stmtCategorias->execute();
            $totalCategorias = $stmtCategorias->fetch()['total'];
            
            // Contar productos locales vinculados
            $stmtLocales = $dbLocal->prepare("SELECT COUNT(*) as total FROM productos WHERE codigo_maestro IS NOT NULL");
            $stmtLocales->execute();
            $totalLocales = $stmtLocales->fetch()['total'];
            
            // Contar registros de sincronización
            $stmtSync = $dbLocal->prepare("SELECT COUNT(*) as total FROM sincronizacion_maestro");
            $stmtSync->execute();
            $totalSync = $stmtSync->fetch()['total'];
            
            echo "<div style='background: #e7f3ff; padding: 15px; border-radius: 5px; margin-top: 15px;'>";
            echo "<h4>📊 Reporte de Instalación</h4>";
            echo "<table style='width: 100%; border-collapse: collapse;'>";
            echo "<tr><td style='padding: 5px; border-bottom: 1px solid #ddd;'><strong>Productos en Catálogo Maestro:</strong></td><td style='padding: 5px; border-bottom: 1px solid #ddd;'>{$totalCatalogo}</td></tr>";
            echo "<tr><td style='padding: 5px; border-bottom: 1px solid #ddd;'><strong>Categorías Centrales:</strong></td><td style='padding: 5px; border-bottom: 1px solid #ddd;'>{$totalCategorias}</td></tr>";
            echo "<tr><td style='padding: 5px; border-bottom: 1px solid #ddd;'><strong>Productos Locales Vinculados:</strong></td><td style='padding: 5px; border-bottom: 1px solid #ddd;'>{$totalLocales}</td></tr>";
            echo "<tr><td style='padding: 5px; border-bottom: 1px solid #ddd;'><strong>Registros de Sincronización:</strong></td><td style='padding: 5px; border-bottom: 1px solid #ddd;'>{$totalSync}</td></tr>";
            echo "</table>";
            echo "</div>";
            
        } catch (Exception $e) {
            echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; color: #856404; margin-top: 15px;'>";
            echo "<p>⚠️ No se pudo generar el reporte completo: " . $e->getMessage() . "</p>";
            echo "</div>";
        }
    }
    
    /*=============================================
    DESINSTALAR CATÁLOGO MAESTRO (OPCIONAL)
    =============================================*/
    public static function desinstalar() {
        
        if (!isset($_GET['desinstalar']) || $_GET['desinstalar'] !== 'confirmar') {
            return;
        }
        
        echo "<div style='font-family: Arial, sans-serif; max-width: 800px; margin: 20px auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px;'>";
        echo "<h2 style='color: #dc3545; text-align: center;'>🗑️ Desinstalación del Catálogo Maestro</h2>";
        echo "<hr>";
        
        try {
            $dbCentral = self::conectarCentral();
            $dbLocal = self::conectarLocal();
            
            echo "<ul style='line-height: 1.8;'>";
            
            // Eliminar tabla de sincronización local
            $dbLocal->exec("DROP TABLE IF EXISTS sincronizacion_maestro");
            echo "<li>✅ Tabla sincronizacion_maestro eliminada</li>";
            
            // Eliminar columna codigo_maestro
            $dbLocal->exec("ALTER TABLE productos DROP COLUMN codigo_maestro");
            echo "<li>✅ Columna codigo_maestro eliminada de productos</li>";
            
            // Eliminar catálogo maestro (CUIDADO: esto elimina todos los datos)
            $dbCentral->exec("DROP TABLE IF EXISTS catalogo_maestro");
            echo "<li>✅ Tabla catalogo_maestro eliminada</li>";
            
            echo "</ul>";
            echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; color: #155724;'>";
            echo "<h3>✅ Desinstalación completada</h3>";
            echo "<p>El Catálogo Maestro ha sido desinstalado completamente del sistema.</p>";
            echo "</div>";
            
        } catch (Exception $e) {
            echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; color: #721c24;'>";
            echo "<h3>❌ Error en la desinstalación</h3>";
            echo "<p>" . $e->getMessage() . "</p>";
            echo "</div>";
        }
        
        echo "</div>";
    }
}

/*=============================================
EJECUCIÓN PRINCIPAL DEL INSTALADOR
=============================================*/

// Verificar si se solicita desinstalación
if (isset($_GET['accion']) && $_GET['accion'] === 'desinstalar') {
    InstaladorCatalogoMaestro::desinstalar();
    exit;
}

// Página principal del instalador
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalador Catálogo Maestro - danytrax/adminv5</title>
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background: #f8f9fa; 
            margin: 0; 
            padding: 20px;
        }
        .container { 
            max-width: 900px; 
            margin: 0 auto; 
            background: white; 
            border-radius: 10px; 
            box-shadow: 0 0 20px rgba(0,0,0,0.1); 
            overflow: hidden;
        }
        .header { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
            color: white; 
            padding: 30px; 
            text-align: center;
        }
        .content { 
            padding: 30px; 
        }
        .btn { 
            display: inline-block; 
            padding: 12px 30px; 
            margin: 10px; 
            text-decoration: none; 
            border-radius: 5px; 
            font-weight: bold; 
            transition: all 0.3s;
        }
        .btn-primary { 
            background: #007bff; 
            color: white; 
        }
        .btn-success { 
            background: #28a745; 
            color: white; 
        }
        .btn-danger { 
            background: #dc3545; 
            color: white; 
        }
        .btn:hover { 
            transform: translateY(-2px); 
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .alert { 
            padding: 15px; 
            margin: 15px 0; 
            border-radius: 5px;
        }
        .alert-danger { 
            background: #f8d7da; 
            color: #721c24; 
            border: 1px solid #f5c6cb;
        }
        .alert-warning { 
            background: #fff3cd; 
            color: #856404; 
            border: 1px solid #ffeaa7;
        }
        .alert-info { 
            background: #d1ecf1; 
            color: #0c5460; 
            border: 1px solid #bee5eb;
        }
        .feature-list { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); 
            gap: 20px; 
            margin: 20px 0;
        }
        .feature { 
            padding: 20px; 
            background: #f8f9fa; 
            border-radius: 8px; 
            border-left: 4px solid #007bff;
        }
        .feature h4 { 
            margin: 0 0 10px 0; 
            color: #333;
        }
        .feature p { 
            margin: 0; 
            color: #666; 
            font-size: 14px;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1>🚀 Instalador del Catálogo Maestro</h1>
        <p>Sistema de Gestión Centralizada de Productos para danytrax/adminv5</p>
    </div>
    
    <div class="content">
        <?php
        
        // Verificar si ya está instalado
        if (InstaladorCatalogoMaestro::verificarInstalacion()) {
            echo '<div class="alert alert-warning">';
            echo '<h4>⚠️ El Catálogo Maestro ya está instalado</h4>';
            echo '<p>El sistema detectó que el Catálogo Maestro ya está instalado en este servidor.</p>';
            echo '<p><a href="index.php?ruta=catalogo-maestro" class="btn btn-primary">🏠 Ir al Catálogo Maestro</a></p>';
            echo '<p><a href="?accion=desinstalar&desinstalar=confirmar" class="btn btn-danger" onclick="return confirm(\'¿Está seguro? Esto eliminará todos los datos del catálogo maestro.\')">🗑️ Desinstalar</a></p>';
            echo '</div>';
            
            // Generar reporte del estado actual
            InstaladorCatalogoMaestro::generarReporte();
            
        } else {
            
            // Verificar requisitos del sistema
            $requisitos = InstaladorCatalogoMaestro::verificarRequisitos();
            
            if (!empty($requisitos['errores'])) {
                echo '<div class="alert alert-danger">';
                echo '<h4>❌ Errores que impiden la instalación:</h4>';
                echo '<ul>';
                foreach ($requisitos['errores'] as $error) {
                    echo '<li>' . htmlspecialchars($error) . '</li>';
                }
                echo '</ul>';
                echo '<p><strong>Por favor corrija estos errores antes de continuar.</strong></p>';
                echo '</div>';
            }
            
            if (!empty($requisitos['advertencias'])) {
                echo '<div class="alert alert-warning">';
                echo '<h4>⚠️ Advertencias:</h4>';
                echo '<ul>';
                foreach ($requisitos['advertencias'] as $advertencia) {
                    echo '<li>' . htmlspecialchars($advertencia) . '</li>';
                }
                echo '</ul>';
                echo '</div>';
            }
            
            if ($requisitos['puede_instalar']) {
                echo '<div class="alert alert-info">';
                echo '<h4>📋 ¿Qué es el Catálogo Maestro?</h4>';
                echo '<p>El <strong>Catálogo Maestro</strong> es un sistema centralizado de gestión de productos que permite:</p>';
                echo '</div>';
                
                echo '<div class="feature-list">';
                echo '<div class="feature">';
                echo '<h4>🏢 Gestión Centralizada</h4>';
                echo '<p>Administrar productos desde una ubicación central para todas las sucursales</p>';
                echo '</div>';
                
                echo '<div class="feature">';
                echo '<h4>🔄 Sincronización Automática</h4>';
                echo '<p>Sincronizar precios y datos de productos automáticamente</p>';
                echo '</div>';
                
                echo '<div class="feature">';
                echo '<h4>✂️ Sistema de División</h4>';
                echo '<p>Configurar productos padre-hijo para divisiones (1/2, 1/3, 1/4)</p>';
                echo '</div>';
                
                echo '<div class="feature">';
                echo '<h4>📊 Importación Excel</h4>';
                echo '<p>Importar productos masivamente desde archivos Excel</p>';
                echo '</div>';
                echo '</div>';
                
                if (isset($_GET['instalar']) && $_GET['instalar'] === 'ejecutar') {
                    // Ejecutar instalación
                    InstaladorCatalogoMaestro::ejecutarInstalacion();
                    InstaladorCatalogoMaestro::generarReporte();
                } else {
                    // Mostrar botón de instalación
                    echo '<div style="text-align: center; margin: 30px 0;">';
                    echo '<a href="?instalar=ejecutar" class="btn btn-success" onclick="return confirm(\'¿Está seguro de instalar el Catálogo Maestro? Esta acción creará nuevas tablas y migrará los datos existentes.\')">🚀 Instalar Catálogo Maestro</a>';
                    echo '</div>';
                }
            }
        }
        ?>
        
        <hr>
        <div style="text-align: center; color: #666; font-size: 12px;">
            <p><strong>danytrax/adminv5</strong> - Instalador del Catálogo Maestro v1.0</p>
            <p>⚠️ <strong>Importante:</strong> Elimine este archivo después de la instalación por seguridad</p>
        </div>
    </div>
</div>

</body>
</html>