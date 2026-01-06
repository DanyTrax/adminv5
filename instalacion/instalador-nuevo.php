<?php
// Verificación de autenticación
session_start();

if (!isset($_SESSION['instalacion_logueado']) || $_SESSION['instalacion_logueado'] !== true) {
    header('Location: index.php');
    exit;
}

// Verificar tiempo de sesión
if (!isset($_SESSION['instalacion_tiempo']) || (time() - $_SESSION['instalacion_tiempo']) > 3600) {
    session_destroy();
    header('Location: index.php');
    exit;
}

// Actualizar tiempo de actividad
$_SESSION['instalacion_tiempo'] = time();

// Botón de logout
echo '
<style>
.logout-btn {
    position: fixed; top: 20px; right: 20px; background: #dc3545; color: white;
    padding: 8px 15px; border: none; border-radius: 5px; text-decoration: none;
    font-size: 12px; z-index: 1000; cursor: pointer;
}
.logout-btn:hover { background: #c82333; color: white; }
</style>
<a href="logout.php" class="logout-btn" onclick="return confirm(\'¿Cerrar sesión?\')">
    🔓 Cerrar Sesión
</a>';

// ===================================================================
// INSTALADOR SIMPLIFICADO PARA SUCURSALES - VERSIÓN 2.0
// Enfoque: Configuración básica + Primer usuario + Conexión central
// ===================================================================

$INSTALADOR_VERSION = "2.0";
$FECHA_INSTALACION = date('Y-m-d H:i:s');

// Procesar formulario
$mensaje = '';
$tipo_mensaje = '';
$paso_actual = $_GET['paso'] ?? 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    if (isset($_POST['paso']) && $_POST['paso'] == '1') {
        // PASO 1: Configuración de la sucursal
        $datos_sucursal = [
            'codigo_sucursal' => $_POST['codigo_sucursal'] ?? '',
            'nombre' => $_POST['nombre'] ?? '',
            'direccion' => $_POST['direccion'] ?? '',
            'telefono' => $_POST['telefono'] ?? '',
            'email' => $_POST['email'] ?? '',
            'url_base' => $_POST['url_base'] ?? '',
            'url_api' => $_POST['url_api'] ?? '',
            'importar_medios_pago' => isset($_POST['importar_medios_pago']) ? 1 : 0,
            'sucursales_seleccionadas' => $_POST['sucursales_seleccionadas'] ?? '[]'
        ];
        
        // Validar datos básicos
        if (empty($datos_sucursal['codigo_sucursal']) || empty($datos_sucursal['nombre'])) {
            $mensaje = 'El código de sucursal y el nombre son obligatorios.';
            $tipo_mensaje = 'error';
        } else {
            $_SESSION['datos_sucursal'] = $datos_sucursal;
            $paso_actual = 2;
        }
    }
    
    if (isset($_POST['paso']) && $_POST['paso'] == '2') {
        // PASO 2: Configuración de base de datos
        $datos_bd = [
            'host' => $_POST['host_bd'] ?? 'localhost',
            'puerto' => $_POST['puerto_bd'] ?? '3306',
            'usuario' => $_POST['usuario_bd'] ?? '',
            'password' => $_POST['password_bd'] ?? '',
            'nombre_bd' => $_POST['nombre_bd'] ?? ''
        ];
        
        // Validar datos de BD
        if (empty($datos_bd['usuario']) || empty($datos_bd['nombre_bd'])) {
            $mensaje = 'El usuario y nombre de la base de datos son obligatorios.';
            $tipo_mensaje = 'error';
        } else {
            // Probar conexión
            try {
                $dsn = "mysql:host={$datos_bd['host']};port={$datos_bd['puerto']};charset=utf8";
                $pdo = new PDO($dsn, $datos_bd['usuario'], $datos_bd['password']);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                // Verificar si la BD existe
                $stmt = $pdo->prepare("SHOW DATABASES LIKE ?");
                $stmt->execute([$datos_bd['nombre_bd']]);
                $bd_existe = $stmt->fetch();
                
                if (!$bd_existe) {
                    // Crear la base de datos
                    $pdo->exec("CREATE DATABASE `{$datos_bd['nombre_bd']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
                    $mensaje = "Base de datos '{$datos_bd['nombre_bd']}' creada exitosamente.";
                    $tipo_mensaje = 'success';
                } else {
                    $mensaje = "Base de datos '{$datos_bd['nombre_bd']}' ya existe.";
                    $tipo_mensaje = 'info';
                }
                
                $_SESSION['datos_bd'] = $datos_bd;
                $paso_actual = 3;
                
            } catch (Exception $e) {
                $mensaje = 'Error conectando a la base de datos: ' . $e->getMessage();
                $tipo_mensaje = 'error';
            }
        }
    }
    
    if (isset($_POST['paso']) && $_POST['paso'] == '3') {
        // PASO 3: Crear primer usuario administrador
        $datos_usuario = [
            'nombre' => $_POST['nombre_usuario'] ?? '',
            'usuario' => $_POST['usuario_login'] ?? '',
            'password' => $_POST['password_usuario'] ?? '',
            'perfil' => 'Administrador'
        ];
        
        // Validar datos de usuario
        if (empty($datos_usuario['nombre']) || empty($datos_usuario['usuario']) || empty($datos_usuario['password'])) {
            $mensaje = 'Todos los campos del usuario son obligatorios.';
            $tipo_mensaje = 'error';
        } else {
            $_SESSION['datos_usuario'] = $datos_usuario;
            $paso_actual = 4;
        }
    }
    
    if (isset($_POST['paso']) && $_POST['paso'] == '4') {
        // PASO 4: Configuración del sistema central
        $datos_central = [
            'url_central' => $_POST['url_central'] ?? '',
            'api_key' => $_POST['api_key'] ?? ''
        ];
        
        // Validar datos centrales
        if (empty($datos_central['url_central'])) {
            $mensaje = 'La URL del sistema central es obligatoria.';
            $tipo_mensaje = 'error';
        } else {
            $_SESSION['datos_central'] = $datos_central;
            $paso_actual = 5;
        }
    }
    
    if (isset($_POST['paso']) && $_POST['paso'] == '5') {
        // PASO 5: Ejecutar instalación
        try {
            $resultado = ejecutarInstalacionCompleta();
            if ($resultado['success']) {
                $mensaje = $resultado['message'];
                $tipo_mensaje = 'success';
                $paso_actual = 6; // Mostrar resumen final
            } else {
                $mensaje = $resultado['error'];
                $tipo_mensaje = 'error';
            }
        } catch (Exception $e) {
            $mensaje = 'Error durante la instalación: ' . $e->getMessage();
            $tipo_mensaje = 'error';
        }
    }
}

// Función para ejecutar la instalación completa
function ejecutarInstalacionCompleta() {
    try {
        $datos_sucursal = $_SESSION['datos_sucursal'];
        $datos_bd = $_SESSION['datos_bd'];
        $datos_usuario = $_SESSION['datos_usuario'];
        $datos_central = $_SESSION['datos_central'];
        
        // 1. Crear archivo config.php
        $config_content = generarConfigPHP($datos_bd, $datos_sucursal, $datos_central);
        if (!file_put_contents('../config.php', $config_content)) {
            throw new Exception('No se pudo crear el archivo config.php');
        }
        
        // 2. Actualizar modelos/conexion.php
        $conexion_content = generarConexionPHP($datos_bd);
        if (!file_put_contents('../modelos/conexion.php', $conexion_content)) {
            throw new Exception('No se pudo actualizar el archivo modelos/conexion.php');
        }
        
        // 3. Crear tablas de la base de datos
        $pdo = conectarBD($datos_bd);
        crearTablasBD($pdo);
        
        // 4. Insertar datos iniciales
        insertarDatosIniciales($pdo, $datos_sucursal, $datos_usuario, $datos_bd);
        
        // 4.1. Importar medios de pago si está marcada la opción
        if ($datos_sucursal['importar_medios_pago']) {
            $sucursales_seleccionadas = json_decode($datos_sucursal['sucursales_seleccionadas'], true);
            $datos_central = $_SESSION['datos_central'] ?? [];
            importarMediosPagoDeSucursalesSeleccionadas($pdo, $datos_central, $sucursales_seleccionadas);
        }
        
        // 5. Registrar sucursal en el sistema central
        registrarSucursalEnCentral($datos_sucursal, $datos_central);
        
        // 6. Aplicar cambios SQL necesarios en la base de datos central
        aplicarCambiosSQLCentral($datos_central);
        
        return [
            'success' => true,
            'message' => 'Instalación completada exitosamente. La sucursal está lista para usar.'
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

// Función para generar config.php
function generarConfigPHP($datos_bd, $datos_sucursal, $datos_central) {
    return "<?php
// Configuración de la sucursal
define('DB_HOST', '{$datos_bd['host']}');
define('DB_PORT', '{$datos_bd['puerto']}');
define('DB_USER', '{$datos_bd['usuario']}');
define('DB_PASS', '{$datos_bd['password']}');
define('DB_NAME', '{$datos_bd['nombre_bd']}');

// Configuración de la sucursal
define('CODIGO_SUCURSAL', '{$datos_sucursal['codigo_sucursal']}');
define('NOMBRE_SUCURSAL', '{$datos_sucursal['nombre']}');
define('URL_BASE', '{$datos_sucursal['url_base']}');
define('URL_API', '{$datos_sucursal['url_api']}');

// Configuración del sistema central
define('URL_CENTRAL', '{$datos_central['url_central']}');
define('API_KEY_CENTRAL', '{$datos_central['api_key']}');

// Configuración general
define('RUTA_PROYECTO', '/');
define('RUTA_SERVIDOR', '{$datos_sucursal['url_base']}');
?>";
}

// Función para generar modelos/conexion.php
function generarConexionPHP($datos_bd) {
    return "<?php

class Conexion{

	static public function conectar(){

		\$link = new PDO(\"mysql:host={$datos_bd['host']};port={$datos_bd['puerto']};dbname={$datos_bd['nombre_bd']}\",
			            \"{$datos_bd['usuario']}\",
			            \"{$datos_bd['password']}\");

		\$link->exec(\"set names utf8\");

		return \$link;

	}

}";
}

// Función para conectar a la BD
function conectarBD($datos_bd) {
    $dsn = "mysql:host={$datos_bd['host']};port={$datos_bd['puerto']};dbname={$datos_bd['nombre_bd']};charset=utf8";
    $pdo = new PDO($dsn, $datos_bd['usuario'], $datos_bd['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $pdo;
}

// Función para crear todas las tablas del sistema con estructura exacta
function crearTablasBD($pdo) {
    // Estructura completa basada en la BD local real
    $sql_tablas = [
        // 1. Tabla abonos_historial
        "CREATE TABLE IF NOT EXISTS abonos_historial (
            id INT(11) NOT NULL AUTO_INCREMENT,
            id_venta INT(11) NOT NULL,
            codigo_venta INT(11) NOT NULL,
            monto_abono DECIMAL(10,2) NOT NULL,
            fecha_abono DATETIME NOT NULL,
            id_vendedor_abono INT(11) NOT NULL,
            nombre_vendedor_abono VARCHAR(255) NOT NULL,
            medio_pago VARCHAR(50) DEFAULT NULL,
            observaciones TEXT DEFAULT NULL,
            fecha_registro TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_id_venta (id_venta),
            KEY idx_codigo_venta (codigo_venta),
            KEY idx_fecha_abono (fecha_abono)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci",
        
        // 2. Tabla categorias
        "CREATE TABLE IF NOT EXISTS categorias (
            id INT(11) NOT NULL AUTO_INCREMENT,
            categoria TEXT NOT NULL,
            fecha TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci",
        
        // 3. Tabla clientes
        "CREATE TABLE IF NOT EXISTS clientes (
            id INT(11) NOT NULL AUTO_INCREMENT,
            nombre TEXT NOT NULL,
            documento INT(11) NOT NULL,
            email TEXT NOT NULL,
            telefono TEXT NOT NULL,
            direccion TEXT NOT NULL,
            fecha_nacimiento DATE NOT NULL,
            compras INT(11) NOT NULL,
            ultima_compra DATETIME NOT NULL,
            fecha TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci",
        
        // 4. Tabla contabilidad
        "CREATE TABLE IF NOT EXISTS contabilidad (
            id INT(11) NOT NULL AUTO_INCREMENT,
            id_vendedor INT(11) NOT NULL,
            fecha DATETIME NOT NULL,
            detalle TEXT NOT NULL,
            valor VARCHAR(100) NOT NULL,
            medio_pago VARCHAR(50) NOT NULL,
            forma_pago VARCHAR(50) DEFAULT NULL,
            factura VARCHAR(20) DEFAULT NULL,
            tipo VARCHAR(50) NOT NULL,
            PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci",
        
        // 5. Tabla cotizaciones
        "CREATE TABLE IF NOT EXISTS cotizaciones (
            id INT(11) NOT NULL AUTO_INCREMENT,
            codigo INT(11) NOT NULL,
            id_cliente INT(11) NOT NULL,
            id_vendedor INT(11) NOT NULL,
            productos TEXT NOT NULL,
            impuesto FLOAT NOT NULL,
            descuento INT(11) NOT NULL DEFAULT 0,
            neto FLOAT NOT NULL,
            total FLOAT NOT NULL,
            detalle TEXT NOT NULL,
            metodo_pago TEXT NOT NULL,
            fecha_venta DATETIME NOT NULL,
            id_vend_abono INT(11) NOT NULL,
            abono FLOAT NOT NULL,
            fecha_abono DATETIME NOT NULL,
            pago TEXT NOT NULL,
            Ult_abono FLOAT NOT NULL,
            medio_pago VARCHAR(50) DEFAULT NULL,
            images TEXT DEFAULT NULL,
            PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci",
        
        // 6. Tabla medios_pago
        "CREATE TABLE IF NOT EXISTS medios_pago (
            id INT(11) NOT NULL AUTO_INCREMENT,
            nombre VARCHAR(100) NOT NULL,
            PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci",
        
        // 7. Tabla productos
        "CREATE TABLE IF NOT EXISTS productos (
            id INT(11) NOT NULL AUTO_INCREMENT,
            id_categoria INT(11) NOT NULL,
            parent_id INT(11) DEFAULT NULL,
            codigo TEXT NOT NULL,
            codigo_maestro VARCHAR(50) DEFAULT NULL,
            descripcion TEXT NOT NULL,
            imagen TEXT NOT NULL,
            stock INT(11) NOT NULL,
            precio_venta FLOAT NOT NULL,
            ventas INT(11) NOT NULL,
            fecha TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            es_divisible TINYINT(1) DEFAULT 0,
            nombre_mitad VARCHAR(255) DEFAULT NULL,
            precio_mitad DECIMAL(10,2) DEFAULT NULL,
            nombre_tercio VARCHAR(255) DEFAULT NULL,
            precio_tercio DECIMAL(10,2) DEFAULT NULL,
            nombre_cuarto VARCHAR(255) DEFAULT NULL,
            precio_cuarto DECIMAL(10,2) DEFAULT NULL,
            PRIMARY KEY (id),
            KEY idx_codigo_maestro (codigo_maestro)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci",
        
        // 8. Tabla sincronizacion_maestro
        "CREATE TABLE IF NOT EXISTS sincronizacion_maestro (
            id INT(11) NOT NULL AUTO_INCREMENT,
            codigo_maestro VARCHAR(50) NOT NULL,
            id_producto_local INT(11) NOT NULL,
            ultima_sincronizacion TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY unique_sync (codigo_maestro, id_producto_local),
            KEY idx_codigo_maestro (codigo_maestro),
            KEY id_producto_local (id_producto_local),
            CONSTRAINT sincronizacion_maestro_ibfk_1 FOREIGN KEY (id_producto_local) REFERENCES productos (id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci",
        
        // 9. Tabla sucursal_local
        "CREATE TABLE IF NOT EXISTS sucursal_local (
            id INT(11) NOT NULL AUTO_INCREMENT,
            codigo_sucursal VARCHAR(20) NOT NULL,
            nombre VARCHAR(255) NOT NULL,
            direccion TEXT DEFAULT NULL,
            telefono VARCHAR(50) DEFAULT NULL,
            email VARCHAR(255) DEFAULT NULL,
            usuario_bd VARCHAR(50) DEFAULT NULL,
            password_bd VARCHAR(255) DEFAULT NULL,
            nombre_bd VARCHAR(100) DEFAULT NULL,
            host_bd VARCHAR(255) DEFAULT NULL,
            puerto_bd INT(11) DEFAULT 3306,
            url_base VARCHAR(255) NOT NULL,
            url_api VARCHAR(255) NOT NULL,
            url_central VARCHAR(255) DEFAULT NULL,
            importar_medios_pago TINYINT(1) DEFAULT 0,
            es_principal TINYINT(1) DEFAULT 0,
            activo TINYINT(1) DEFAULT 1,
            registrada_en_central TINYINT(1) DEFAULT 0,
            fecha_registro TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            fecha_actualizacion TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci",
        
        // 10. Tabla usuarios
        "CREATE TABLE IF NOT EXISTS usuarios (
            id INT(11) NOT NULL AUTO_INCREMENT,
            nombre TEXT NOT NULL,
            usuario TEXT NOT NULL,
            password TEXT NOT NULL,
            perfil TEXT NOT NULL,
            foto TEXT NOT NULL,
            estado INT(11) NOT NULL,
            ultimo_login DATETIME NOT NULL,
            fecha TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            empresa TEXT NOT NULL,
            telefono TEXT DEFAULT NULL,
            direccion TEXT DEFAULT NULL,
            PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci",
        
        // 11. Tabla venta_productos
        "CREATE TABLE IF NOT EXISTS venta_productos (
            id INT(11) NOT NULL AUTO_INCREMENT,
            id_venta INT(11) NOT NULL,
            descripcion VARCHAR(255) NOT NULL,
            cantidad INT(11) NOT NULL,
            total DECIMAL(10,2) NOT NULL,
            PRIMARY KEY (id),
            KEY id_venta (id_venta)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci",
        
        // 12. Tabla ventas
        "CREATE TABLE IF NOT EXISTS ventas (
            id INT(11) NOT NULL AUTO_INCREMENT,
            codigo INT(11) NOT NULL,
            id_cliente INT(11) NOT NULL,
            id_vendedor INT(11) NOT NULL,
            productos TEXT NOT NULL,
            impuesto FLOAT NOT NULL,
            descuento INT(11) NOT NULL DEFAULT 0,
            neto FLOAT NOT NULL,
            total FLOAT NOT NULL,
            detalle TEXT NOT NULL,
            metodo_pago TEXT NOT NULL,
            fecha_venta DATETIME NOT NULL,
            id_vend_abono INT(11) NOT NULL,
            abono FLOAT NOT NULL,
            fecha_abono DATETIME NOT NULL,
            pago TEXT NOT NULL,
            Ult_abono FLOAT NOT NULL,
            medio_pago VARCHAR(50) DEFAULT NULL,
            PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci",
        
        // 13. Tabla salidas_inventario
        "CREATE TABLE IF NOT EXISTS salidas_inventario (
            id INT(11) NOT NULL AUTO_INCREMENT,
            id_producto INT(11) NOT NULL,
            id_usuario INT(11) NOT NULL,
            cantidad DECIMAL(10,2) NOT NULL,
            descripcion TEXT,
            numero_remision VARCHAR(255),
            fecha_salida DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            FOREIGN KEY (id_producto) REFERENCES productos(id) ON DELETE CASCADE ON UPDATE CASCADE,
            FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE ON UPDATE CASCADE,
            KEY idx_fecha_salida (fecha_salida),
            KEY idx_id_producto (id_producto),
            KEY idx_id_usuario (id_usuario)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci"
    ];
    
    foreach ($sql_tablas as $sql) {
        $pdo->exec($sql);
    }
}

// Función para insertar datos iniciales
function insertarDatosIniciales($pdo, $datos_sucursal, $datos_usuario, $datos_bd) {
    // Insertar configuración de la sucursal con datos de BD
    $stmt = $pdo->prepare("
        INSERT INTO sucursal_local (
            codigo_sucursal, nombre, direccion, telefono, email, 
            usuario_bd, password_bd, nombre_bd, host_bd, puerto_bd,
            url_base, url_api, url_central, importar_medios_pago, es_principal, activo, registrada_en_central
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, 1, 0)
    ");
    $stmt->execute([
        $datos_sucursal['codigo_sucursal'],
        $datos_sucursal['nombre'],
        $datos_sucursal['direccion'],
        $datos_sucursal['telefono'],
        $datos_sucursal['email'],
        $datos_bd['usuario'], // usuario_bd
        $datos_bd['password'], // password_bd
        $datos_bd['nombre_bd'], // nombre_bd
        $datos_bd['host'], // host_bd
        $datos_bd['puerto'], // puerto_bd
        $datos_sucursal['url_base'],
        $datos_sucursal['url_api'],
        $datos_sucursal['url_central'],
        $datos_sucursal['importar_medios_pago']
    ]);
    
    // Insertar primer usuario administrador con estructura exacta
    $password_encriptado = crypt($datos_usuario['password'], '$2a$07$asxx54ahjppf45sd87a5a4dDDGsystemdev$');
    $stmt = $pdo->prepare("
        INSERT INTO usuarios (
            nombre, usuario, password, perfil, foto, estado, 
            ultimo_login, empresa, telefono, direccion
        ) VALUES (?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?)
    ");
    $stmt->execute([
        $datos_usuario['nombre'],
        $datos_usuario['usuario'],
        $password_encriptado,
        $datos_usuario['perfil'],
        'vistas/img/usuarios/default/anonymous.png', // foto por defecto
        1, // estado activo
        $datos_sucursal['nombre'], // empresa = nombre de la sucursal
        '', // telefono vacío
        ''  // direccion vacía
    ]);
    
    // Las categorías y medios de pago se importarán desde las sucursales seleccionadas
    // No se crean datos genéricos para evitar duplicados y datos innecesarios
    
    // Verificar que el usuario se creó correctamente
    $stmt = $pdo->prepare("SELECT id, usuario, password FROM usuarios WHERE usuario = ?");
    $stmt->execute([$datos_usuario['usuario']]);
    $usuario_creado = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$usuario_creado) {
        throw new Exception('Error: No se pudo crear el usuario administrador');
    }
    
}

// Función para detectar URL actual
function detectarUrlActual() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    
    // Obtener solo la URL base sin rutas adicionales
    $base_url = $protocol . '://' . $host;
    
    // Limpiar la URL para que termine con /
    if (substr($base_url, -1) !== '/') {
        $base_url .= '/';
    }
    
    return $base_url;
}

// Función para sugerir URL de API
function sugerirUrlApi($url_base) {
    // Remover la última barra si existe
    $url_limpia = rtrim($url_base, '/');
    return $url_limpia . '/api-transferencias/';
}

// Función para registrar sucursal en el sistema central
function registrarSucursalEnCentral($datos_sucursal, $datos_central) {
    // Esta función se implementaría para registrar la sucursal en el sistema central
    // Por ahora solo retornamos true
    return true;
}

// Función para aplicar cambios SQL necesarios en la base de datos central
function aplicarCambiosSQLCentral($datos_central) {
    try {
        // Verificar si hay configuración del central
        if (empty($datos_central['url_central'])) {
            error_log("URL central no configurada, saltando cambios SQL del central");
            return true; // No es crítico, solo un log
        }
        
        // Conectar a la base de datos central
        // Intentar diferentes rutas posibles para conexion-central.php
        $rutas_posibles = [
            __DIR__ . '/../api-transferencias/conexion-central.php',
            __DIR__ . '/../../api-transferencias/conexion-central.php',
            dirname(__DIR__) . '/api-transferencias/conexion-central.php'
        ];
        
        $conexion_central_cargado = false;
        foreach ($rutas_posibles as $ruta) {
            if (file_exists($ruta)) {
                require_once $ruta;
                $conexion_central_cargado = true;
                break;
            }
        }
        
        if (!$conexion_central_cargado) {
            error_log("⚠️  No se pudo cargar conexion-central.php, saltando cambios SQL del central");
            return true; // No es crítico
        }
        
        $conexion = ConexionCentral::conectar();
        $conexion->beginTransaction();
        
        // ============================================
        // 1. TABLA personalizacion_colores
        // ============================================
        
        // 1.1. Verificar si la tabla existe
        $stmt = $conexion->query("SHOW TABLES LIKE 'personalizacion_colores'");
        if ($stmt->rowCount() > 0) {
            // 1.2. Agregar campo id_sucursal si no existe
            $stmt = $conexion->query("SHOW COLUMNS FROM personalizacion_colores LIKE 'id_sucursal'");
            if ($stmt->rowCount() == 0) {
                $conexion->exec("
                    ALTER TABLE personalizacion_colores 
                    ADD COLUMN id_sucursal INT(11) NULL DEFAULT NULL AFTER activo,
                    ADD INDEX idx_id_sucursal (id_sucursal)
                ");
                error_log("✅ Campo 'id_sucursal' agregado a personalizacion_colores");
            }
            
            // 1.3. Agregar campo nombre_sucursal si no existe
            $stmt = $conexion->query("SHOW COLUMNS FROM personalizacion_colores LIKE 'nombre_sucursal'");
            if ($stmt->rowCount() == 0) {
                $conexion->exec("
                    ALTER TABLE personalizacion_colores 
                    ADD COLUMN nombre_sucursal VARCHAR(255) NULL AFTER id_sucursal
                ");
                error_log("✅ Campo 'nombre_sucursal' agregado a personalizacion_colores");
            }
        }
        
        // ============================================
        // 2. TABLA personalizacion_cotizaciones
        // ============================================
        
        // 2.1. Crear tabla si no existe
        $stmt = $conexion->query("SHOW TABLES LIKE 'personalizacion_cotizaciones'");
        if ($stmt->rowCount() == 0) {
            $conexion->exec("
                CREATE TABLE personalizacion_cotizaciones (
                    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    id_sucursal INT(11) NULL,
                    nombre_sucursal VARCHAR(255) NULL,
                    
                    -- Header
                    header_logo VARCHAR(255) NULL,
                    header_nombre_empresa VARCHAR(255) NULL,
                    header_nit VARCHAR(255) NULL,
                    header_regimen VARCHAR(255) NULL,
                    header_servicios TEXT NULL,
                    header_color_fondo VARCHAR(7) NULL,
                    header_color_texto VARCHAR(7) NULL,
                    
                    -- Footer
                    footer_direccion VARCHAR(255) NULL,
                    footer_telefono VARCHAR(255) NULL,
                    footer_movil VARCHAR(255) NULL,
                    footer_correo VARCHAR(255) NULL,
                    footer_color_fondo VARCHAR(7) NULL,
                    footer_color_texto VARCHAR(7) NULL,
                    
                    activo TINYINT(1) DEFAULT 1,
                    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
                    fecha_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    usuario_creador INT(11) NULL,
                    
                    INDEX idx_sucursal (id_sucursal),
                    INDEX idx_activo (activo)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            error_log("✅ Tabla 'personalizacion_cotizaciones' creada");
            
            // Crear configuración global por defecto
            $stmt = $conexion->prepare("
                INSERT INTO personalizacion_cotizaciones (
                    id_sucursal, nombre_sucursal,
                    header_logo, header_nombre_empresa, header_nit, header_regimen, header_servicios,
                    header_color_fondo, header_color_texto,
                    footer_direccion, footer_telefono, footer_movil, footer_correo,
                    footer_color_fondo, footer_color_texto,
                    activo, usuario_creador
                ) VALUES (
                    NULL, 'Global',
                    'vistas/img/cotizacion/Infinito1.png', 'ACPLASTICOS', 'NIT: 901.718.358-2', 
                    'IVA E ICA RÉGIMEN COMÚN', 'AVISOS\nLETRAS EN 3D\nTOMA UNO\nTRABAJOS ESPECIALES',
                    '#873173', '#FFFFFF',
                    'Carrera 27 # 10-65 Local 116', 'Tel: 601 569 9557', 'Móvil: 322 744 5631', 
                    'Correo: ventas1@acplasticos.com',
                    '#873173', '#FFFFFF',
                    1, 1
                )
            ");
            $stmt->execute();
            error_log("✅ Configuración global por defecto creada en personalizacion_cotizaciones");
        }
        
        // 2.2. Agregar campos de logo y texto si no existen
        $camposLogo = [
            'logo_width' => "INT(3) NULL DEFAULT 80",
            'logo_align_vertical' => "VARCHAR(20) NULL DEFAULT 'center'",
            'logo_align_horizontal' => "VARCHAR(20) NULL DEFAULT 'center'",
            'header_font_size' => "INT(3) NULL DEFAULT 14",
            'body_font_size' => "INT(3) NULL DEFAULT 13",
            'footer_font_size' => "INT(3) NULL DEFAULT 16"
        ];
        
        foreach ($camposLogo as $nombreCampo => $definicion) {
            $stmt = $conexion->prepare("SHOW COLUMNS FROM personalizacion_cotizaciones LIKE ?");
            $stmt->execute([$nombreCampo]);
            if ($stmt->rowCount() == 0) {
                // Determinar después de qué campo agregarlo
                $despuesDe = '';
                switch ($nombreCampo) {
                    case 'logo_width':
                        $despuesDe = 'AFTER header_logo';
                        break;
                    case 'logo_align_vertical':
                        $despuesDe = 'AFTER logo_width';
                        break;
                    case 'logo_align_horizontal':
                        $despuesDe = 'AFTER logo_align_vertical';
                        break;
                    case 'header_font_size':
                        $despuesDe = 'AFTER header_color_texto';
                        break;
                    case 'body_font_size':
                        $despuesDe = 'AFTER header_font_size';
                        break;
                    case 'footer_font_size':
                        $despuesDe = 'AFTER footer_color_texto';
                        break;
                }
                
                $conexion->exec("ALTER TABLE personalizacion_cotizaciones ADD COLUMN $nombreCampo $definicion $despuesDe");
                error_log("✅ Campo '$nombreCampo' agregado a personalizacion_cotizaciones");
            }
        }
        
        $conexion->commit();
        error_log("✅ Cambios SQL del central aplicados exitosamente");
        return true;
        
    } catch (PDOException $e) {
        if (isset($conexion) && $conexion->inTransaction()) {
            $conexion->rollBack();
        }
        error_log("❌ Error al aplicar cambios SQL del central: " . $e->getMessage());
        // No lanzar excepción para no bloquear la instalación
        return false;
    } catch (Exception $e) {
        if (isset($conexion) && $conexion->inTransaction()) {
            $conexion->rollBack();
        }
        error_log("❌ Error al aplicar cambios SQL del central: " . $e->getMessage());
        // No lanzar excepción para no bloquear la instalación
        return false;
    }
}

// Función para importar medios de pago de sucursales seleccionadas
function importarMediosPagoDeSucursalesSeleccionadas($pdo, $datos_central, $sucursales_seleccionadas) {
    try {
        if (empty($sucursales_seleccionadas)) {
            error_log("No hay sucursales seleccionadas para importar medios de pago");
            return false;
        }
        
        // Obtener datos de las sucursales seleccionadas desde el central
        $url_central = $datos_central['url_central'] ?? '';
        if (empty($url_central)) {
            error_log("URL central no configurada para importar medios de pago");
            return false;
        }
        
        // Construir URL de la API central
        $url_api_central = rtrim($url_central, '/') . '/api-transferencias/';
        
        // Obtener datos completos de las sucursales seleccionadas
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url_api_central . 'obtener-sucursales-activas.php');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code !== 200 || !$response) {
            error_log("Error al obtener datos de sucursales del central: HTTP $http_code");
            return false;
        }
        
        $sucursales_data = json_decode($response, true);
        
        if (!$sucursales_data || !isset($sucursales_data['success']) || !$sucursales_data['success']) {
            error_log("Respuesta inválida del central para sucursales");
            return false;
        }
        
        // Filtrar solo las sucursales seleccionadas
        $sucursales_activas = [];
        foreach ($sucursales_data['sucursales'] as $sucursal) {
            foreach ($sucursales_seleccionadas as $seleccionada) {
                if ($sucursal['id'] == $seleccionada['id']) {
                    $sucursales_activas[] = $sucursal;
                    break;
                }
            }
        }
        
        if (empty($sucursales_activas)) {
            error_log("No se encontraron datos de las sucursales seleccionadas");
            return false;
        }
        
        $medios_importados = 0;
        $sucursales_procesadas = 0;
        
        // Procesar cada sucursal seleccionada
        foreach ($sucursales_activas as $sucursal) {
            try {
                // Construir URL de la API de la sucursal usando url_api del central
                $url_api_sucursal = rtrim($sucursal['url_api'], '/') . '/';
                
                error_log("Intentando conectar a sucursal: {$sucursal['nombre']} - URL: $url_api_sucursal");
                
                // Obtener medios de pago de esta sucursal específica
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url_api_sucursal . 'obtener-medios-pago-activos.php');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 15);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                
                $response = curl_exec($ch);
                $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $error = curl_error($ch);
                curl_close($ch);
                
                if ($error) {
                    error_log("Error cURL al conectar con {$sucursal['nombre']}: $error");
                    continue;
                }
                
                if ($http_code !== 200 || !$response) {
                    error_log("Error al obtener medios de pago de {$sucursal['nombre']}: HTTP $http_code");
                    continue;
                }
                
                $medios_pago_data = json_decode($response, true);
                
                if (!$medios_pago_data || !isset($medios_pago_data['success']) || !$medios_pago_data['success']) {
                    error_log("Respuesta inválida de {$sucursal['nombre']} para medios de pago: " . json_encode($medios_pago_data));
                    continue;
                }
                
                error_log("Medios de pago encontrados en {$sucursal['nombre']}: " . count($medios_pago_data['medios_pago']));
                
                // Insertar cada medio de pago en la sucursal local
                foreach ($medios_pago_data['medios_pago'] as $medio) {
                    try {
                        // Verificar si ya existe
                        $stmt_check = $pdo->prepare("
                            SELECT id FROM medios_pago 
                            WHERE nombre = ?
                        ");
                        $stmt_check->execute([$medio['nombre']]);
                        
                        if ($stmt_check->fetch()) {
                            error_log("Medio de pago '{$medio['nombre']}' ya existe, omitiendo");
                            continue;
                        }
                        
                        $stmt = $pdo->prepare("
                            INSERT INTO medios_pago (
                                nombre
                            ) VALUES (?)
                        ");
                        
                        $stmt->execute([
                            $medio['nombre']
                        ]);
                        
                        $medios_importados++;
                        error_log("Medio de pago importado de {$sucursal['nombre']}: " . $medio['nombre']);
                        
                    } catch (PDOException $e) {
                        error_log("Error al insertar medio de pago '{$medio['nombre']}' de {$sucursal['nombre']}: " . $e->getMessage());
                        continue;
                    }
                }
                
                $sucursales_procesadas++;
                
            } catch (Exception $e) {
                error_log("Error al procesar sucursal {$sucursal['nombre']}: " . $e->getMessage());
                continue;
            }
        }
        
        error_log("Importación completada: $medios_importados medios de pago importados de $sucursales_procesadas sucursales");
        return true;
        
    } catch (Exception $e) {
        error_log("Error en importarMediosPagoDeSucursalesActivas: " . $e->getMessage());
        return false;
    }
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalador de Sucursal - Versión 2.0</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .installer-container { max-width: 800px; margin: 50px auto; }
        .step-card { background: white; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .step-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 15px 15px 0 0; }
        .step-content { padding: 30px; }
        .step-indicator { display: flex; justify-content: center; margin-bottom: 30px; }
        .step-dot { width: 40px; height: 40px; border-radius: 50%; background: #e9ecef; color: #6c757d; display: flex; align-items: center; justify-content: center; margin: 0 10px; font-weight: bold; }
        .step-dot.active { background: #667eea; color: white; }
        .step-dot.completed { background: #28a745; color: white; }
        .btn-install { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; padding: 12px 30px; border-radius: 25px; color: white; font-weight: bold; }
        .btn-install:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.2); }
        .form-control:focus { border-color: #667eea; box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25); }
        .alert { border-radius: 10px; }
    </style>
</head>
<body>
    <div class="installer-container">
        <div class="step-card">
            <div class="step-header text-center">
                <h2><i class="fas fa-building"></i> Instalador de Sucursal v2.0</h2>
                <p class="mb-0">Configuración simplificada para nuevas sucursales</p>
            </div>
            
            <div class="step-content">
                <!-- Indicador de pasos -->
                <div class="step-indicator">
                    <div class="step-dot <?= $paso_actual >= 1 ? ($paso_actual == 1 ? 'active' : 'completed') : '' ?>">1</div>
                    <div class="step-dot <?= $paso_actual >= 2 ? ($paso_actual == 2 ? 'active' : 'completed') : '' ?>">2</div>
                    <div class="step-dot <?= $paso_actual >= 3 ? ($paso_actual == 3 ? 'active' : 'completed') : '' ?>">3</div>
                    <div class="step-dot <?= $paso_actual >= 4 ? ($paso_actual == 4 ? 'active' : 'completed') : '' ?>">4</div>
                    <div class="step-dot <?= $paso_actual >= 5 ? ($paso_actual == 5 ? 'active' : 'completed') : '' ?>">5</div>
                </div>
                
                <?php if ($mensaje): ?>
                    <div class="alert alert-<?= $tipo_mensaje == 'error' ? 'danger' : ($tipo_mensaje == 'success' ? 'success' : 'info') ?>">
                        <?= htmlspecialchars($mensaje) ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($paso_actual == 1): ?>
                    <!-- PASO 1: Configuración de la sucursal -->
                    <h4><i class="fas fa-building"></i> Configuración de la Sucursal</h4>
                    <p class="text-muted">Ingrese los datos básicos de la sucursal.</p>
                    
                    <form method="POST">
                        <input type="hidden" name="paso" value="1">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Código de Sucursal *</label>
                                    <input type="text" class="form-control" name="codigo_sucursal" required 
                                           placeholder="Ej: SUC001" value="<?= $_SESSION['datos_sucursal']['codigo_sucursal'] ?? '' ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Nombre de la Sucursal *</label>
                                    <input type="text" class="form-control" name="nombre" required 
                                           placeholder="Ej: Sucursal Principal" value="<?= $_SESSION['datos_sucursal']['nombre'] ?? '' ?>">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Dirección</label>
                            <input type="text" class="form-control" name="direccion" 
                                   placeholder="Calle 123 #45-67" value="<?= $_SESSION['datos_sucursal']['direccion'] ?? '' ?>">
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Teléfono</label>
                                    <input type="text" class="form-control" name="telefono" 
                                           placeholder="(601) 123-4567" value="<?= $_SESSION['datos_sucursal']['telefono'] ?? '' ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" name="email" 
                                           placeholder="sucursal@empresa.com" value="<?= $_SESSION['datos_sucursal']['email'] ?? '' ?>">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">URL Base</label>
                                    <input type="url" class="form-control" name="url_base" 
                                           placeholder="https://sucursal.empresa.com/" 
                                           value="<?= $_SESSION['datos_sucursal']['url_base'] ?? detectarUrlActual() ?>">
                                    <div class="form-text">Se detectó automáticamente la URL actual</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">URL API</label>
                                    <input type="url" class="form-control" name="url_api" 
                                           placeholder="https://sucursal.empresa.com/api-transferencias/" 
                                           value="<?= $_SESSION['datos_sucursal']['url_api'] ?? sugerirUrlApi(detectarUrlActual()) ?>">
                                    <div class="form-text">Sugerida automáticamente</div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Opción para importar medios de pago -->
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="importar_medios_pago" id="importar_medios_pago" 
                                       value="1" <?= isset($_SESSION['datos_sucursal']['importar_medios_pago']) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="importar_medios_pago">
                                    <i class="fas fa-credit-card text-primary"></i>
                                    <strong>Importar medios de pago de sucursales activas</strong>
                                </label>
                                <div class="form-text">Seleccione de qué sucursales activas importar los medios de pago</div>
                            </div>
                            
                            <!-- Botón para seleccionar sucursales -->
                            <div id="seleccion-sucursales" style="display: none; margin-top: 15px;">
                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="mostrarModalSucursales()">
                                    <i class="fas fa-list"></i> Seleccionar Sucursales
                                </button>
                                <button type="button" class="btn btn-outline-info btn-sm ms-2" onclick="mostrarVistaPrevia()">
                                    <i class="fas fa-eye"></i> Ver Medios de Pago
                                </button>
                                <div id="sucursales-seleccionadas" class="mt-2"></div>
                                <input type="hidden" name="sucursales_seleccionadas" id="sucursales_seleccionadas" value="">
                            </div>
                        </div>
                        
                        <div class="text-end">
                            <button type="submit" class="btn btn-install">
                                <i class="fas fa-arrow-right"></i> Siguiente
                            </button>
                        </div>
                    </form>
                    
                <?php elseif ($paso_actual == 2): ?>
                    <!-- PASO 2: Configuración de base de datos -->
                    <h4><i class="fas fa-database"></i> Configuración de Base de Datos</h4>
                    <p class="text-muted">Configure la conexión a la base de datos MySQL.</p>
                    
                    <form method="POST">
                        <input type="hidden" name="paso" value="2">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Host de la Base de Datos</label>
                                    <input type="text" class="form-control" name="host_bd" 
                                           value="<?= $_SESSION['datos_bd']['host'] ?? 'localhost' ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Puerto</label>
                                    <input type="number" class="form-control" name="puerto_bd" 
                                           value="<?= $_SESSION['datos_bd']['puerto'] ?? '3306' ?>" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Usuario de la Base de Datos *</label>
                                    <input type="text" class="form-control" name="usuario_bd" 
                                           value="<?= $_SESSION['datos_bd']['usuario'] ?? '' ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Contraseña</label>
                                    <input type="password" class="form-control" name="password_bd" 
                                           value="<?= $_SESSION['datos_bd']['password'] ?? '' ?>">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nombre de la Base de Datos *</label>
                            <input type="text" class="form-control" name="nombre_bd" 
                                   value="<?= $_SESSION['datos_bd']['nombre_bd'] ?? '' ?>" required>
                            <div class="form-text">Se creará automáticamente si no existe.</div>
                        </div>
                        <div class="d-flex justify-content-between">
                            <a href="?paso=1" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left"></i> Anterior
                            </a>
                            <button type="submit" class="btn btn-install">
                                <i class="fas fa-arrow-right"></i> Siguiente
                            </button>
                        </div>
                    </form>
                    
                <?php elseif ($paso_actual == 3): ?>
                    <!-- PASO 3: Crear primer usuario -->
                    <h4><i class="fas fa-user-shield"></i> Primer Usuario Administrador</h4>
                    <p class="text-muted">Cree el primer usuario administrador para esta sucursal.</p>
                    
                    <form method="POST">
                        <input type="hidden" name="paso" value="3">
                        <div class="mb-3">
                            <label class="form-label">Nombre Completo *</label>
                            <input type="text" class="form-control" name="nombre_usuario" 
                                   value="<?= $_SESSION['datos_usuario']['nombre'] ?? '' ?>" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Usuario de Login *</label>
                                    <input type="text" class="form-control" name="usuario_login" 
                                           value="<?= $_SESSION['datos_usuario']['usuario'] ?? '' ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Contraseña *</label>
                                    <input type="password" class="form-control" name="password_usuario" 
                                           value="<?= $_SESSION['datos_usuario']['password'] ?? '' ?>" required>
                                </div>
                            </div>
                        </div>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Nota:</strong> Este usuario tendrá permisos de administrador y podrá gestionar la sucursal.
                            Los demás usuarios se sincronizarán desde el sistema central.
                        </div>
                        <div class="d-flex justify-content-between">
                            <a href="?paso=2" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left"></i> Anterior
                            </a>
                            <button type="submit" class="btn btn-install">
                                <i class="fas fa-arrow-right"></i> Siguiente
                            </button>
                        </div>
                    </form>
                    
                <?php elseif ($paso_actual == 4): ?>
                    <!-- PASO 4: Configuración del sistema central -->
                    <h4><i class="fas fa-network-wired"></i> Conexión con Sistema Central</h4>
                    <p class="text-muted">Configure la conexión con el sistema central para sincronización.</p>
                    
                    <form method="POST">
                        <input type="hidden" name="paso" value="4">
                        <div class="mb-3">
                            <label class="form-label">URL del Sistema Central *</label>
                            <input type="url" class="form-control" name="url_central" 
                                   value="<?= $_SESSION['datos_central']['url_central'] ?? detectarUrlActual() ?>" required
                                   placeholder="https://central.empresa.com/">
                            <div class="form-text">Se detectó automáticamente la URL actual</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">API Key (Opcional)</label>
                            <input type="text" class="form-control" name="api_key" 
                                   value="<?= $_SESSION['datos_central']['api_key'] ?? '' ?>"
                                   placeholder="Clave de API para autenticación">
                        </div>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Importante:</strong> Asegúrese de que el sistema central esté configurado y accesible.
                            La sincronización de usuarios y clientes se realizará automáticamente.
                        </div>
                        <div class="d-flex justify-content-between">
                            <a href="?paso=3" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left"></i> Anterior
                            </a>
                            <button type="submit" class="btn btn-install">
                                <i class="fas fa-arrow-right"></i> Siguiente
                            </button>
                        </div>
                    </form>
                    
                <?php elseif ($paso_actual == 5): ?>
                    <!-- PASO 5: Ejecutar instalación -->
                    <h4><i class="fas fa-cogs"></i> Ejecutar Instalación</h4>
                    <p class="text-muted">Revise la configuración y ejecute la instalación.</p>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <h6><i class="fas fa-building"></i> Datos de la Sucursal</h6>
                            <ul class="list-unstyled">
                                <li><strong>Código:</strong> <?= $_SESSION['datos_sucursal']['codigo_sucursal'] ?></li>
                                <li><strong>Nombre:</strong> <?= $_SESSION['datos_sucursal']['nombre'] ?></li>
                                <li><strong>Email:</strong> <?= $_SESSION['datos_sucursal']['email'] ?></li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6><i class="fas fa-database"></i> Base de Datos</h6>
                            <ul class="list-unstyled">
                                <li><strong>Host:</strong> <?= $_SESSION['datos_bd']['host'] ?></li>
                                <li><strong>BD:</strong> <?= $_SESSION['datos_bd']['nombre_bd'] ?></li>
                                <li><strong>Usuario:</strong> <?= $_SESSION['datos_bd']['usuario'] ?></li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <h6><i class="fas fa-user"></i> Usuario Administrador</h6>
                            <ul class="list-unstyled">
                                <li><strong>Nombre:</strong> <?= $_SESSION['datos_usuario']['nombre'] ?></li>
                                <li><strong>Usuario:</strong> <?= $_SESSION['datos_usuario']['usuario'] ?></li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6><i class="fas fa-network-wired"></i> Sistema Central</h6>
                            <ul class="list-unstyled">
                                <li><strong>URL:</strong> <?= $_SESSION['datos_central']['url_central'] ?></li>
                            </ul>
                        </div>
                    </div>
                    
                    <form method="POST">
                        <input type="hidden" name="paso" value="5">
                        <div class="d-flex justify-content-between">
                            <a href="?paso=4" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left"></i> Anterior
                            </a>
                            <button type="submit" class="btn btn-install">
                                <i class="fas fa-play"></i> Ejecutar Instalación
                            </button>
                        </div>
                    </form>
                    
                <?php elseif ($paso_actual == 6): ?>
                    <!-- PASO 6: Instalación completada -->
                    <div class="text-center">
                        <div class="mb-4">
                            <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                        </div>
                        <h4 class="text-success">¡Instalación Completada!</h4>
                        <p class="text-muted">La sucursal ha sido configurada exitosamente.</p>
                        
                        <div class="alert alert-success">
                            <h6><i class="fas fa-info-circle"></i> Próximos pasos:</h6>
                            <ul class="list-unstyled mb-0">
                                <li>• Acceda al sistema con el usuario administrador creado</li>
                                <li>• Configure la conexión con el sistema central</li>
                                <li>• Sincronice usuarios y clientes desde la gestión central</li>
                                <li>• Configure los productos y catálogo</li>
                            </ul>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                            <a href="../index.php" class="btn btn-install">
                                <i class="fas fa-sign-in-alt"></i> Acceder al Sistema
                            </a>
                            <a href="logout.php" class="btn btn-outline-secondary">
                                <i class="fas fa-sign-out-alt"></i> Cerrar Instalador
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal para seleccionar sucursales -->
    <div class="modal fade" id="modalSucursales" tabindex="-1" aria-labelledby="modalSucursalesLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalSucursalesLabel">
                        <i class="fas fa-building text-primary"></i>
                        Seleccionar Sucursales para Importar Medios de Pago
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="cargando-sucursales" class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                        <p class="mt-2">Cargando sucursales activas...</p>
                    </div>
                    
                    <div id="lista-sucursales" style="display: none;">
                        <div class="mb-3">
                            <button type="button" class="btn btn-outline-success btn-sm" onclick="seleccionarTodas()">
                                <i class="fas fa-check-square"></i> Seleccionar Todas
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="deseleccionarTodas()">
                                <i class="fas fa-square"></i> Deseleccionar Todas
                            </button>
                        </div>
                        
                        <div id="sucursales-container">
                            <!-- Las sucursales se cargarán aquí dinámicamente -->
                        </div>
                        
                        <!-- Sección para mostrar medios de pago de sucursales seleccionadas -->
                        <div id="medios-pago-seleccionados" style="display: none; margin-top: 20px;">
                            <hr>
                            <h6><i class="fas fa-credit-card text-info"></i> Medios de Pago de Sucursales Seleccionadas:</h6>
                            <div id="lista-medios-pago">
                                <!-- Los medios de pago se mostrarán aquí -->
                            </div>
                        </div>
                    </div>
                    
                    <div id="error-sucursales" style="display: none;" class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Error:</strong> No se pudieron cargar las sucursales activas.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="confirmarSeleccion()">
                        <i class="fas fa-check"></i> Confirmar Selección
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Variables globales
        let sucursalesDisponibles = [];
        let sucursalesSeleccionadas = [];

        // Mostrar/ocultar sección de selección según checkbox
        document.getElementById('importar_medios_pago').addEventListener('change', function() {
            const seleccionDiv = document.getElementById('seleccion-sucursales');
            if (this.checked) {
                seleccionDiv.style.display = 'block';
            } else {
                seleccionDiv.style.display = 'none';
                sucursalesSeleccionadas = [];
                actualizarSucursalesSeleccionadas();
            }
        });

        // Mostrar modal de sucursales
        function mostrarModalSucursales() {
            const modal = new bootstrap.Modal(document.getElementById('modalSucursales'));
            modal.show();
            cargarSucursalesActivas();
        }

        // Cargar sucursales activas desde el central
        function cargarSucursalesActivas() {
            // Usar la URL central del paso 4 (se configurará después)
            const urlCentral = '<?= $_SESSION["datos_central"]["url_central"] ?? detectarUrlActual() ?>';
            if (!urlCentral) {
                mostrarError('URL Central no configurada. Complete el paso 4 primero.');
                return;
            }

            const urlApi = urlCentral.replace(/\/$/, '') + '/api-transferencias/obtener-sucursales-activas.php';
            
            fetch(urlApi)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        sucursalesDisponibles = data.sucursales;
                        mostrarSucursales();
                    } else {
                        mostrarError(data.message || 'Error al cargar sucursales');
                    }
                })
                .catch(error => {
                    mostrarError('Error de conexión: ' + error.message);
                });
        }

        // Mostrar sucursales en la modal
        function mostrarSucursales() {
            const container = document.getElementById('sucursales-container');
            container.innerHTML = '';

            sucursalesDisponibles.forEach(sucursal => {
                const div = document.createElement('div');
                div.className = 'form-check mb-2';
                div.innerHTML = `
                    <input class="form-check-input" type="checkbox" 
                           id="sucursal_${sucursal.id}" 
                           value="${sucursal.id}"
                           onchange="toggleSucursal(${sucursal.id}, '${sucursal.nombre}')">
                    <label class="form-check-label" for="sucursal_${sucursal.id}">
                        <strong>${sucursal.nombre}</strong> (${sucursal.codigo_sucursal})
                        <br><small class="text-muted">${sucursal.direccion || 'Sin dirección'}</small>
                    </label>
                `;
                container.appendChild(div);
            });

            document.getElementById('cargando-sucursales').style.display = 'none';
            document.getElementById('lista-sucursales').style.display = 'block';
        }

        // Toggle sucursal seleccionada
        function toggleSucursal(id, nombre) {
            const index = sucursalesSeleccionadas.findIndex(s => s.id === id);
            if (index > -1) {
                sucursalesSeleccionadas.splice(index, 1);
            } else {
                sucursalesSeleccionadas.push({id: id, nombre: nombre});
            }
            
            // Mostrar medios de pago de sucursales seleccionadas
            mostrarMediosPagoSeleccionados();
        }

        // Seleccionar todas las sucursales
        function seleccionarTodas() {
            sucursalesSeleccionadas = [...sucursalesDisponibles];
            document.querySelectorAll('#sucursales-container input[type="checkbox"]').forEach(cb => {
                cb.checked = true;
            });
            mostrarMediosPagoSeleccionados();
        }

        // Deseleccionar todas las sucursales
        function deseleccionarTodas() {
            sucursalesSeleccionadas = [];
            document.querySelectorAll('#sucursales-container input[type="checkbox"]').forEach(cb => {
                cb.checked = false;
            });
            mostrarMediosPagoSeleccionados();
        }

        // Confirmar selección
        function confirmarSeleccion() {
            actualizarSucursalesSeleccionadas();
            bootstrap.Modal.getInstance(document.getElementById('modalSucursales')).hide();
        }

        // Actualizar display de sucursales seleccionadas
        function actualizarSucursalesSeleccionadas() {
            const container = document.getElementById('sucursales-seleccionadas');
            const hiddenInput = document.getElementById('sucursales_seleccionadas');
            
            if (sucursalesSeleccionadas.length === 0) {
                container.innerHTML = '<small class="text-muted">No hay sucursales seleccionadas</small>';
                hiddenInput.value = '';
            } else {
                const badges = sucursalesSeleccionadas.map(s => 
                    `<span class="badge bg-primary me-1">${s.nombre}</span>`
                ).join('');
                container.innerHTML = `<div class="mt-1">${badges}</div>`;
                hiddenInput.value = JSON.stringify(sucursalesSeleccionadas);
            }
        }

        // Mostrar error
        function mostrarError(mensaje) {
            document.getElementById('cargando-sucursales').style.display = 'none';
            document.getElementById('lista-sucursales').style.display = 'none';
            document.getElementById('error-sucursales').style.display = 'block';
            document.getElementById('error-sucursales').innerHTML = 
                `<i class="fas fa-exclamation-triangle"></i><strong>Error:</strong> ${mensaje}`;
        }

        // Mostrar vista previa de medios de pago
        function mostrarVistaPrevia() {
            // Abrir vista previa en nueva ventana
            const urlVistaPrevia = '../vista-previa-medios-pago.php';
            window.open(urlVistaPrevia, '_blank', 'width=1200,height=800,scrollbars=yes,resizable=yes');
        }

        // Mostrar medios de pago de sucursales seleccionadas
        function mostrarMediosPagoSeleccionados() {
            const container = document.getElementById('medios-pago-seleccionados');
            const listaMedios = document.getElementById('lista-medios-pago');
            
            if (sucursalesSeleccionadas.length === 0) {
                container.style.display = 'none';
                return;
            }
            
            container.style.display = 'block';
            listaMedios.innerHTML = '<div class="text-center"><div class="spinner-border spinner-border-sm" role="status"></div> Cargando medios de pago...</div>';
            
            // Obtener medios de pago de cada sucursal seleccionada
            Promise.all(sucursalesSeleccionadas.map(sucursal => obtenerMediosPagoSucursal(sucursal)))
                .then(resultados => {
                    mostrarListaMediosPago(resultados);
                })
                .catch(error => {
                    listaMedios.innerHTML = '<div class="alert alert-warning"><i class="fas fa-exclamation-triangle"></i> Error al cargar medios de pago: ' + error.message + '</div>';
                });
        }

        // Obtener medios de pago de una sucursal específica
        function obtenerMediosPagoSucursal(sucursal) {
            return new Promise((resolve, reject) => {
                // Buscar la sucursal en sucursalesDisponibles para obtener sus datos
                const sucursalData = sucursalesDisponibles.find(s => s.id == sucursal.id);
                if (!sucursalData) {
                    reject(new Error('Datos de sucursal no encontrados'));
                    return;
                }
                
                // Construir URL de la API de la sucursal
                const urlApi = sucursalData.url_api + 'obtener-medios-pago-activos.php';
                
                fetch(urlApi)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            resolve({
                                sucursal: sucursal,
                                medios_pago: data.medios_pago || []
                            });
                        } else {
                            reject(new Error(data.message || 'Error al obtener medios de pago'));
                        }
                    })
                    .catch(error => {
                        reject(error);
                    });
            });
        }

        // Mostrar lista de medios de pago
        function mostrarListaMediosPago(resultados) {
            const listaMedios = document.getElementById('lista-medios-pago');
            
            if (resultados.length === 0) {
                listaMedios.innerHTML = '<div class="alert alert-info"><i class="fas fa-info-circle"></i> No hay sucursales seleccionadas</div>';
                return;
            }
            
            let html = '';
            let todosMediosPago = [];
            
            resultados.forEach(resultado => {
                if (resultado.medios_pago.length > 0) {
                    html += `<div class="card mb-2">
                        <div class="card-header py-2">
                            <h6 class="mb-0"><i class="fas fa-building text-primary"></i> ${resultado.sucursal.nombre}</h6>
                        </div>
                        <div class="card-body py-2">
                            <div class="row">`;
                    
                    resultado.medios_pago.forEach(medio => {
                        html += `<div class="col-md-6 mb-1">
                            <span class="badge bg-success me-1">${medio.nombre}</span>
                        </div>`;
                        todosMediosPago.push(medio.nombre);
                    });
                    
                    html += `</div>
                        </div>
                    </div>`;
                } else {
                    html += `<div class="alert alert-warning py-2">
                        <i class="fas fa-exclamation-triangle"></i> ${resultado.sucursal.nombre}: No tiene medios de pago
                    </div>`;
                }
            });
            
            // Mostrar resumen
            const mediosUnicos = [...new Set(todosMediosPago)];
            html += `<div class="alert alert-info mt-3">
                <h6><i class="fas fa-list"></i> Resumen de Importación:</h6>
                <p class="mb-1"><strong>Total medios de pago únicos:</strong> ${mediosUnicos.length}</p>
                <p class="mb-0"><strong>Medios de pago:</strong> ${mediosUnicos.join(', ')}</p>
            </div>`;
            
            listaMedios.innerHTML = html;
        }
    </script>
</body>
</html>
