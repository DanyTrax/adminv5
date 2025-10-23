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
            'url_api' => $_POST['url_api'] ?? ''
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
        
        // 2. Crear tablas de la base de datos
        $pdo = conectarBD($datos_bd);
        crearTablasBD($pdo);
        
        // 3. Insertar datos iniciales
        insertarDatosIniciales($pdo, $datos_sucursal, $datos_usuario);
        
        // 4. Registrar sucursal en el sistema central
        registrarSucursalEnCentral($datos_sucursal, $datos_central);
        
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

// Función para conectar a la BD
function conectarBD($datos_bd) {
    $dsn = "mysql:host={$datos_bd['host']};port={$datos_bd['puerto']};dbname={$datos_bd['nombre_bd']};charset=utf8";
    $pdo = new PDO($dsn, $datos_bd['usuario'], $datos_bd['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $pdo;
}

// Función para crear tablas básicas
function crearTablasBD($pdo) {
    // Solo crear las tablas esenciales
    $sql_tablas = [
        // Tabla de usuarios
        "CREATE TABLE IF NOT EXISTS usuarios (
            id INT(11) NOT NULL AUTO_INCREMENT,
            nombre VARCHAR(255) NOT NULL,
            usuario VARCHAR(255) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            perfil ENUM('Administrador', 'Especial', 'Vendedor', 'Contador', 'Transportador') NOT NULL,
            foto VARCHAR(255) DEFAULT 'vistas/img/usuarios/default/anonymous.png',
            estado TINYINT(1) NOT NULL DEFAULT 1,
            ultimo_login DATETIME NULL,
            fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",
        
        // Tabla de configuración local
        "CREATE TABLE IF NOT EXISTS sucursal_local (
            id INT(11) NOT NULL AUTO_INCREMENT,
            codigo_sucursal VARCHAR(50) NOT NULL UNIQUE,
            nombre VARCHAR(255) NOT NULL,
            direccion TEXT,
            telefono VARCHAR(50),
            email VARCHAR(255),
            url_base VARCHAR(500),
            url_api VARCHAR(500),
            usuario_bd VARCHAR(255),
            password_bd VARCHAR(255),
            nombre_bd VARCHAR(255),
            host_bd VARCHAR(255),
            puerto_bd INT(11) DEFAULT 3306,
            es_principal TINYINT(1) DEFAULT 0,
            activo TINYINT(1) DEFAULT 1,
            fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
            fecha_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"
    ];
    
    foreach ($sql_tablas as $sql) {
        $pdo->exec($sql);
    }
}

// Función para insertar datos iniciales
function insertarDatosIniciales($pdo, $datos_sucursal, $datos_usuario) {
    // Insertar configuración de la sucursal
    $stmt = $pdo->prepare("
        INSERT INTO sucursal_local (
            codigo_sucursal, nombre, direccion, telefono, email, 
            url_base, url_api, es_principal, activo
        ) VALUES (?, ?, ?, ?, ?, ?, ?, 0, 1)
    ");
    $stmt->execute([
        $datos_sucursal['codigo_sucursal'],
        $datos_sucursal['nombre'],
        $datos_sucursal['direccion'],
        $datos_sucursal['telefono'],
        $datos_sucursal['email'],
        $datos_sucursal['url_base'],
        $datos_sucursal['url_api']
    ]);
    
    // Insertar primer usuario administrador
    $password_encriptado = crypt($datos_usuario['password'], '$2a$07$asxx54ahjppf45sd87a5a4dDDGsystemdev$');
    $stmt = $pdo->prepare("
        INSERT INTO usuarios (nombre, usuario, password, perfil, estado, ultimo_login) 
        VALUES (?, ?, ?, ?, 1, NOW())
    ");
    $stmt->execute([
        $datos_usuario['nombre'],
        $datos_usuario['usuario'],
        $password_encriptado,
        $datos_usuario['perfil']
    ]);
}

// Función para registrar sucursal en el sistema central
function registrarSucursalEnCentral($datos_sucursal, $datos_central) {
    // Esta función se implementaría para registrar la sucursal en el sistema central
    // Por ahora solo retornamos true
    return true;
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
                                           placeholder="https://sucursal.empresa.com/" value="<?= $_SESSION['datos_sucursal']['url_base'] ?? '' ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">URL API</label>
                                    <input type="url" class="form-control" name="url_api" 
                                           placeholder="https://sucursal.empresa.com/api/" value="<?= $_SESSION['datos_sucursal']['url_api'] ?? '' ?>">
                                </div>
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
                                   value="<?= $_SESSION['datos_central']['url_central'] ?? '' ?>" required
                                   placeholder="https://central.empresa.com/">
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
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
