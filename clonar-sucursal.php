<?php

/**
 * SCRIPT DE CLONACIÓN DE SUCURSAL
 * 
 * Este script permite clonar una sucursal existente con toda su estructura LOCAL:
 * - Base de datos LOCAL de la nueva sucursal
 * - Estructura de tablas idéntica a la sucursal origen
 * - Datos de ejemplo (categorías, usuarios, productos)
 * - Registro en la BD CENTRAL (compartida por todas las sucursales)
 * 
 * IMPORTANTE: Todas las sucursales comparten la misma BD CENTRAL
 * Solo se clona la BD LOCAL de cada sucursal
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "api-transferencias/conexion-central.php";
require_once "modelos/conexion.php";

class ClonadorSucursal {
    
    private $sucursalOrigen;
    private $sucursalDestino;
    private $conexionCentral;
    private $conexionLocal;
    
    public function __construct($sucursalOrigenId, $datosNuevaSucursal) {
        $this->sucursalDestino = $datosNuevaSucursal;
        
        // Validar conexiones
        $this->conexionCentral = ConexionCentral::conectar();
        if(!$this->conexionCentral) {
            throw new Exception("No se pudo conectar a la base de datos central");
        }
        
        $this->conexionLocal = Conexion::conectar();
        if(!$this->conexionLocal) {
            throw new Exception("No se pudo conectar a la base de datos local");
        }
        
        $this->sucursalOrigen = $this->obtenerSucursalOrigen($sucursalOrigenId);
    }
    
    /**
     * Obtener datos de la sucursal origen
     */
    private function obtenerSucursalOrigen($id) {
        // Si no se especifica ID o es 0, obtener la primera sucursal disponible
        if($id <= 0) {
            $stmt = $this->conexionCentral->prepare("
                SELECT * FROM sucursales WHERE activo = 1 ORDER BY id ASC LIMIT 1
            ");
            $stmt->execute();
        } else {
            $stmt = $this->conexionCentral->prepare("
                SELECT * FROM sucursales WHERE id = :id
            ");
            $stmt->bindParam(":id", $id);
            $stmt->execute();
        }
        
        $sucursal = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if(!$sucursal) {
            throw new Exception("No se encontró sucursal origen. Verifica que existan sucursales en la BD Central.");
        }
        
        return $sucursal;
    }
    
    /**
     * Crear nueva sucursal en BD Central
     */
    public function crearSucursalEnCentral() {
        try {
            $stmt = $this->conexionCentral->prepare("
                INSERT INTO sucursales (
                    codigo_sucursal, nombre, direccion, telefono, email,
                    url_base, url_api, es_principal, activo,
                    usuario_bd, password_bd, nombre_bd, host_bd, puerto_bd
                ) VALUES (
                    :codigo, :nombre, :direccion, :telefono, :email,
                    :url_base, :url_api, :es_principal, :activo,
                    :usuario_bd, :password_bd, :nombre_bd, :host_bd, :puerto_bd
                )
            ");
            
            $stmt->execute([
                ':codigo' => $this->sucursalDestino['codigo_sucursal'],
                ':nombre' => $this->sucursalDestino['nombre'],
                ':direccion' => $this->sucursalDestino['direccion'],
                ':telefono' => $this->sucursalDestino['telefono'],
                ':email' => $this->sucursalDestino['email'],
                ':url_base' => $this->sucursalDestino['url_base'],
                ':url_api' => $this->sucursalDestino['url_api'],
                ':es_principal' => 0,
                ':activo' => 1,
                ':usuario_bd' => $this->sucursalDestino['usuario_bd'],
                ':password_bd' => $this->sucursalDestino['password_bd'],
                ':nombre_bd' => $this->sucursalDestino['nombre_bd'],
                ':host_bd' => $this->sucursalDestino['host_bd'],
                ':puerto_bd' => $this->sucursalDestino['puerto_bd']
            ]);
            
            return $this->conexionCentral->lastInsertId();
            
        } catch(Exception $e) {
            throw new Exception("Error creando sucursal en BD Central: " . $e->getMessage());
        }
    }
    
    /**
     * Crear base de datos de la nueva sucursal
     */
    public function crearBaseDatosSucursal() {
        try {
            // Conectar al servidor MySQL (sin especificar base de datos)
            $dsn = "mysql:host={$this->sucursalDestino['host_bd']};port={$this->sucursalDestino['puerto_bd']}";
            $pdo = new PDO($dsn, $this->sucursalDestino['usuario_bd'], $this->sucursalDestino['password_bd']);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Crear la base de datos
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$this->sucursalDestino['nombre_bd']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            
            return true;
            
        } catch(Exception $e) {
            throw new Exception("Error creando base de datos: " . $e->getMessage());
        }
    }
    
    /**
     * Clonar estructura de tablas desde sucursal origen
     */
    public function clonarEstructuraTablas() {
        try {
            // Conectar a la nueva base de datos
            $dsn = "mysql:host={$this->sucursalDestino['host_bd']};port={$this->sucursalDestino['puerto_bd']};dbname={$this->sucursalDestino['nombre_bd']}";
            $pdo = new PDO($dsn, $this->sucursalDestino['usuario_bd'], $this->sucursalDestino['password_bd']);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Obtener estructura de tablas de la sucursal origen
            $tablas = $this->obtenerEstructuraTablas();
            
            foreach($tablas as $tabla) {
                $this->crearTabla($pdo, $tabla);
            }
            
            return true;
            
        } catch(Exception $e) {
            throw new Exception("Error clonando estructura de tablas: " . $e->getMessage());
        }
    }
    
    /**
     * Obtener estructura de todas las tablas de la sucursal origen
     */
    private function obtenerEstructuraTablas() {
        $tablas = [
            'categorias', 'clientes', 'contabilidad', 'cotizaciones', 
            'medios_pago', 'productos', 'sincronizacion_maestro', 
            'sucursal_local', 'usuarios', 'venta_productos', 'ventas'
        ];
        
        $estructuras = [];
        
        foreach($tablas as $tabla) {
            $stmt = $this->conexionLocal->prepare("SHOW CREATE TABLE `$tabla`");
            $stmt->execute();
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            $estructuras[$tabla] = $resultado['Create Table'];
        }
        
        return $estructuras;
    }
    
    /**
     * Crear tabla en la nueva base de datos
     */
    private function crearTabla($pdo, $createTableSQL) {
        $pdo->exec($createTableSQL);
    }
    
    /**
     * Clonar datos de ejemplo de la sucursal origen
     */
    public function clonarDatosEjemplo() {
        try {
            // Conectar a la nueva base de datos
            $dsn = "mysql:host={$this->sucursalDestino['host_bd']};port={$this->sucursalDestino['puerto_bd']};dbname={$this->sucursalDestino['nombre_bd']}";
            $pdo = new PDO($dsn, $this->sucursalDestino['usuario_bd'], $this->sucursalDestino['password_bd']);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Clonar datos de tablas específicas
            $this->clonarDatosTabla('categorias', $pdo);
            $this->clonarDatosTabla('medios_pago', $pdo);
            $this->clonarDatosTabla('usuarios', $pdo);
            $this->clonarDatosTabla('productos', $pdo);
            
            // Crear registro de sucursal local
            $this->crearRegistroSucursalLocal($pdo);
            
            return true;
            
        } catch(Exception $e) {
            throw new Exception("Error clonando datos de ejemplo: " . $e->getMessage());
        }
    }
    
    /**
     * Clonar datos de una tabla específica
     */
    private function clonarDatosTabla($tabla, $pdoDestino) {
        // Obtener datos de la tabla origen
        $stmt = $this->conexionLocal->prepare("SELECT * FROM `$tabla`");
        $stmt->execute();
        $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if(empty($datos)) return;
        
        // Obtener columnas de la tabla
        $stmt = $this->conexionLocal->prepare("DESCRIBE `$tabla`");
        $stmt->execute();
        $columnas = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        // Preparar INSERT
        $columnasStr = implode('`, `', $columnas);
        $placeholders = ':' . implode(', :', $columnas);
        
        $insertSQL = "INSERT INTO `$tabla` (`$columnasStr`) VALUES ($placeholders)";
        $stmt = $pdoDestino->prepare($insertSQL);
        
        // Insertar cada fila
        foreach($datos as $fila) {
            $stmt->execute($fila);
        }
    }
    
    /**
     * Crear registro de sucursal local
     */
    private function crearRegistroSucursalLocal($pdo) {
        $stmt = $pdo->prepare("
            INSERT INTO sucursal_local (
                codigo_sucursal, nombre, direccion, telefono, email,
                url_base, url_api, es_principal, activo, registrada_en_central
            ) VALUES (
                :codigo, :nombre, :direccion, :telefono, :email,
                :url_base, :url_api, :es_principal, :activo, :registrada_en_central
            )
        ");
        
        $stmt->execute([
            ':codigo' => $this->sucursalDestino['codigo_sucursal'],
            ':nombre' => $this->sucursalDestino['nombre'],
            ':direccion' => $this->sucursalDestino['direccion'],
            ':telefono' => $this->sucursalDestino['telefono'],
            ':email' => $this->sucursalDestino['email'],
            ':url_base' => $this->sucursalDestino['url_base'],
            ':url_api' => $this->sucursalDestino['url_api'],
            ':es_principal' => 0,
            ':activo' => 1,
            ':registrada_en_central' => 1
        ]);
    }
    
    /**
     * Ejecutar proceso completo de clonación
     */
    public function ejecutarClonacion() {
        $resultado = [
            'success' => true,
            'mensajes' => [],
            'errores' => []
        ];
        
        try {
            // 1. Crear sucursal en BD Central
            $resultado['mensajes'][] = "✅ Creando sucursal en BD Central...";
            $nuevaSucursalId = $this->crearSucursalEnCentral();
            $resultado['mensajes'][] = "✅ Sucursal creada con ID: $nuevaSucursalId";
            
            // 2. Crear base de datos
            $resultado['mensajes'][] = "✅ Creando base de datos...";
            $this->crearBaseDatosSucursal();
            $resultado['mensajes'][] = "✅ Base de datos creada: {$this->sucursalDestino['nombre_bd']}";
            
            // 3. Clonar estructura de tablas
            $resultado['mensajes'][] = "✅ Clonando estructura de tablas...";
            $this->clonarEstructuraTablas();
            $resultado['mensajes'][] = "✅ Estructura de tablas clonada";
            
            // 4. Clonar datos de ejemplo
            $resultado['mensajes'][] = "✅ Clonando datos de ejemplo...";
            $this->clonarDatosEjemplo();
            $resultado['mensajes'][] = "✅ Datos de ejemplo clonados";
            
            $resultado['mensajes'][] = "🎉 ¡Clonación completada exitosamente!";
            $resultado['nueva_sucursal_id'] = $nuevaSucursalId;
            
        } catch(Exception $e) {
            $resultado['success'] = false;
            $resultado['errores'][] = $e->getMessage();
        }
        
        return $resultado;
    }
}

// Si se ejecuta directamente
if(isset($_GET['ejecutar']) && $_GET['ejecutar'] == '1') {
    
    // Obtener datos del formulario o usar valores por defecto
    $datosNuevaSucursal = [
        'codigo_sucursal' => $_GET['codigo'] ?? 'SUC004',
        'nombre' => $_GET['nombre'] ?? 'Sucursal Prueba 2',
        'direccion' => $_GET['direccion'] ?? 'Calle 123 #45-67',
        'telefono' => $_GET['telefono'] ?? '(555) 123-4567',
        'email' => $_GET['email'] ?? 'prueba2@empresa.com',
        'url_base' => $_GET['url_base'] ?? 'https://prueba2.empresa.com',
        'url_api' => $_GET['url_api'] ?? 'https://prueba2.empresa.com/api-transferencias/',
        'usuario_bd' => $_GET['usuario_bd'] ?? 'usuario_prueba2',
        'password_bd' => $_GET['password_bd'] ?? 'password123',
        'nombre_bd' => $_GET['nombre_bd'] ?? 'bd_prueba2',
        'host_bd' => $_GET['host_bd'] ?? 'localhost',
        'puerto_bd' => intval($_GET['puerto_bd'] ?? 3306)
    ];
    
    echo "<h2>Resultado de la Clonación</h2>";
    
    try {
        // Verificar conexiones antes de crear el clonador
        echo "<p>🔍 Verificando conexiones...</p>";
        
        $conexionCentral = ConexionCentral::conectar();
        if(!$conexionCentral) {
            throw new Exception("❌ No se pudo conectar a la base de datos central. Verifica la configuración en api-transferencias/conexion-central.php");
        }
        echo "<p>✅ Conexión a BD Central: OK</p>";
        
        $conexionLocal = Conexion::conectar();
        if(!$conexionLocal) {
            throw new Exception("❌ No se pudo conectar a la base de datos local. Verifica la configuración en modelos/conexion.php");
        }
        echo "<p>✅ Conexión a BD Local: OK</p>";
        
        // Mostrar información de la sucursal origen
        $stmt = $conexionCentral->prepare("SELECT * FROM sucursales WHERE activo = 1 ORDER BY id ASC LIMIT 1");
        $stmt->execute();
        $sucursalOrigen = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($sucursalOrigen) {
            echo "<p>📋 Sucursal origen: <strong>{$sucursalOrigen['nombre']}</strong> (ID: {$sucursalOrigen['id']})</p>";
        }
        
        // Crear clonador (0 = usar primera sucursal disponible)
        $clonador = new ClonadorSucursal(0, $datosNuevaSucursal);
        $resultado = $clonador->ejecutarClonacion();
        
        if($resultado['success']) {
            echo "<div style='color: green; background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
            foreach($resultado['mensajes'] as $mensaje) {
                echo "<p>$mensaje</p>";
            }
            echo "</div>";
        } else {
            echo "<div style='color: red; background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
            foreach($resultado['errores'] as $error) {
                echo "<p>❌ $error</p>";
            }
            echo "</div>";
        }
        
    } catch(Exception $e) {
        echo "<div style='color: red; background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<p><strong>❌ Error:</strong> " . $e->getMessage() . "</p>";
        echo "<p><strong>💡 Soluciones:</strong></p>";
        echo "<ul>";
        echo "<li>Verifica que los archivos de conexión existan y estén configurados correctamente</li>";
        echo "<li>Revisa las credenciales de base de datos</li>";
        echo "<li>Asegúrate de que las bases de datos estén accesibles</li>";
        echo "</ul>";
        echo "</div>";
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Clonador de Sucursales</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, textarea { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        button { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #0056b3; }
        .warning { background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 4px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <h1>🔧 Clonador de Sucursales</h1>
    
    <div class="warning">
        <strong>⚠️ Información Importante:</strong> 
        <ul style="margin: 10px 0; padding-left: 20px;">
            <li><strong>BD Central:</strong> Todas las sucursales comparten la misma base de datos central</li>
            <li><strong>BD Local:</strong> Solo se crea la base de datos LOCAL de la nueva sucursal</li>
            <li><strong>Estructura:</strong> Se clona la estructura de tablas de la sucursal origen</li>
            <li><strong>Datos:</strong> Se copian datos de ejemplo (categorías, usuarios, productos)</li>
        </ul>
        Asegúrate de tener los permisos necesarios y de que la base de datos LOCAL de destino no exista.
    </div>
    
    <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 20px; border-left: 4px solid #007bff;">
        <h4 style="margin: 0 0 10px 0; color: #007bff;">🏗️ Arquitectura del Sistema</h4>
        <div style="font-family: monospace; font-size: 12px; line-height: 1.4;">
            <div style="background: #e3f2fd; padding: 8px; margin: 5px 0; border-radius: 3px;">
                <strong>BD CENTRAL (Compartida)</strong><br>
                ├── sucursales (registro de todas las sucursales)<br>
                ├── usuarios_central (usuarios centrales)<br>
                ├── catalogo_maestro (productos maestros)<br>
                ├── despachos (despachos entre sucursales)<br>
                └── stock_transito (stock en tránsito)
            </div>
            <div style="background: #f3e5f5; padding: 8px; margin: 5px 0; border-radius: 3px;">
                <strong>BD LOCAL Sucursal A</strong><br>
                ├── usuarios (usuarios locales)<br>
                ├── productos (inventario local)<br>
                ├── ventas (ventas locales)<br>
                └── sucursal_local (configuración local)
            </div>
            <div style="background: #f3e5f5; padding: 8px; margin: 5px 0; border-radius: 3px;">
                <strong>BD LOCAL Sucursal B (Nueva)</strong><br>
                ├── usuarios (usuarios locales)<br>
                ├── productos (inventario local)<br>
                ├── ventas (ventas locales)<br>
                └── sucursal_local (configuración local)
            </div>
        </div>
    </div>
    
    <form method="GET">
        <h3>Configuración de la Nueva Sucursal</h3>
        
        <div class="form-group">
            <label>Código de Sucursal:</label>
            <input type="text" name="codigo" value="SUC004" required>
        </div>
        
        <div class="form-group">
            <label>Nombre:</label>
            <input type="text" name="nombre" value="Sucursal Prueba 2" required>
        </div>
        
        <div class="form-group">
            <label>Dirección:</label>
            <textarea name="direccion">Calle 123 #45-67</textarea>
        </div>
        
        <div class="form-group">
            <label>Teléfono:</label>
            <input type="text" name="telefono" value="(555) 123-4567">
        </div>
        
        <div class="form-group">
            <label>Email:</label>
            <input type="email" name="email" value="prueba2@empresa.com">
        </div>
        
        <div class="form-group">
            <label>URL Base:</label>
            <input type="url" name="url_base" value="https://prueba2.empresa.com" required>
        </div>
        
        <div class="form-group">
            <label>URL API:</label>
            <input type="url" name="url_api" value="https://prueba2.empresa.com/api-transferencias/" required>
        </div>
        
        <h3>Configuración de Base de Datos LOCAL</h3>
        <div style="background: #e7f3ff; padding: 10px; border-radius: 4px; margin-bottom: 15px;">
            <strong>📌 Nota:</strong> Esta es la configuración de la base de datos LOCAL de la nueva sucursal. 
            La BD Central se mantiene igual para todas las sucursales.
        </div>
        
        <div class="form-group">
            <label>Usuario BD Local:</label>
            <input type="text" name="usuario_bd" value="usuario_prueba2" required>
        </div>
        
        <div class="form-group">
            <label>Contraseña BD Local:</label>
            <input type="password" name="password_bd" value="password123" required>
        </div>
        
        <div class="form-group">
            <label>Nombre BD Local:</label>
            <input type="text" name="nombre_bd" value="bd_prueba2" required>
        </div>
        
        <div class="form-group">
            <label>Host BD Local:</label>
            <input type="text" name="host_bd" value="localhost" required>
        </div>
        
        <div class="form-group">
            <label>Puerto BD Local:</label>
            <input type="number" name="puerto_bd" value="3306" required>
        </div>
        
        <button type="submit" name="ejecutar" value="1">🚀 Crear Sucursal Completa</button>
    </form>
</body>
</html>
