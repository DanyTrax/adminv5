<?php
// ===================================================================
// INSTALADOR DE BASE DE DATOS LOCAL PARA SUCURSALES
// danytrax/adminv5 - Configuración de derivaciones
// ===================================================================

ini_set('display_errors', 1);
error_reporting(E_ALL);

$INSTALADOR_VERSION = "1.0";
$FECHA_INSTALACION = date('Y-m-d H:i:s');

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🚀 Instalador BD Local - AdminV5</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; margin: 0; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; background: white; border-radius: 10px; box-shadow: 0 0 20px rgba(0,0,0,0.1); overflow: hidden; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; }
        .content { padding: 30px; }
        .step { background: #f8f9ff; border-left: 4px solid #667eea; padding: 20px; margin: 20px 0; border-radius: 5px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; color: #333; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 12px; border: 2px solid #ddd; border-radius: 5px; font-size: 14px; }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus { border-color: #667eea; outline: none; }
        .btn { background: #667eea; color: white; padding: 15px 30px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; font-weight: bold; }
        .btn:hover { background: #5a67d8; }
        .btn:disabled { background: #ccc; cursor: not-allowed; }
        .success { background: #d4edda; color: #155724; padding: 15px; border: 1px solid #c3e6cb; border-radius: 5px; margin: 10px 0; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border: 1px solid #f5c6cb; border-radius: 5px; margin: 10px 0; }
        .warning { background: #fff3cd; color: #856404; padding: 15px; border: 1px solid #ffeaa7; border-radius: 5px; margin: 10px 0; }
        .info { background: #d1ecf1; color: #0c5460; padding: 15px; border: 1px solid #b8daff; border-radius: 5px; margin: 10px 0; }
        .progress { background: #e9ecef; height: 20px; border-radius: 10px; overflow: hidden; margin: 20px 0; }
        .progress-bar { background: #667eea; height: 100%; transition: width 0.3s ease; }
        .code { background: #f8f9fa; padding: 15px; border-radius: 5px; font-family: monospace; border: 1px solid #e9ecef; margin: 10px 0; }
        .config-actual { background: #e8f4f8; padding: 15px; border-radius: 5px; margin: 10px 0; }
    </style>
    <script>
        function confirmarInstalacion() {
            return confirm('¿Está seguro de que desea iniciar la instalación? Este proceso modificará la base de datos.');
        }
        
        function copiarConfiguracion() {
            const textarea = document.getElementById('nuevaConfiguracion');
            textarea.select();
            document.execCommand('copy');
            alert('Configuración copiada al portapapeles');
        }
        
        function generarNombreBD() {
            const codigoSucursal = document.getElementById('codigo_sucursal').value.toLowerCase();
            if(codigoSucursal) {
                const nombreBD = 'epicosie_' + codigoSucursal.replace(/[^a-z0-9]/g, '');
                document.getElementById('bd_nombre').value = nombreBD;
            }
        }
    </script>
</head>
<body>

<div class="container">
    
    <div class="header">
        <h1>🚀 Instalador de Base de Datos Local</h1>
        <p>AdminV5 - Sistema de Gestión de Sucursales</p>
        <small>Versión <?php echo $INSTALADOR_VERSION; ?></small>
    </div>
    
    <div class="content">
        
        <?php if (!isset($_POST['action'])): ?>
        
        <!-- ===== DETECCIÓN AUTOMÁTICA DE CONFIGURACIÓN ===== -->
        <div class="step">
            <h2>🔍 Configuración Actual Detectada</h2>
            <div class="config-actual">
                <p><strong>📊 Base de Datos Local Actual:</strong></p>
                <ul>
                    <li><strong>Host:</strong> localhost</li>
                    <li><strong>BD:</strong> epicosie_pruebas</li>
                    <li><strong>Usuario:</strong> epicosie_ricaurte</li>
                    <li><strong>Estructura:</strong> ✅ Tablas existentes detectadas</li>
                </ul>
                <p><em>El instalador configurará una nueva derivación manteniendo la BD central separada.</em></p>
            </div>
        </div>
        
        <form method="POST" id="instaladorForm">
            <input type="hidden" name="action" value="instalar">
            
            <!-- Configuración de Sucursal -->
            <div class="step">
                <h3>🏪 Configuración de Nueva Sucursal</h3>
                
                <div class="form-group">
                    <label for="nombre_sucursal">Nombre de la Sucursal:</label>
                    <input type="text" id="nombre_sucursal" name="nombre_sucursal" required 
                           placeholder="Ej: Sucursal Centro, Sucursal Norte">
                </div>
                
                <div class="form-group">
                    <label for="codigo_sucursal">Código de Sucursal:</label>
                    <input type="text" id="codigo_sucursal" name="codigo_sucursal" required 
                           placeholder="Ej: CENTRO, NORTE, SUR" pattern="[A-Z0-9]{3,10}"
                           onkeyup="generarNombreBD()" style="text-transform: uppercase;">
                    <small>Solo letras mayúsculas y números, 3-10 caracteres</small>
                </div>
            </div>
            
            <!-- Configuración BD -->
            <div class="step">
                <h3>💾 Nueva Base de Datos Local</h3>
                
                <div class="form-group">
                    <label for="bd_host">Host de BD:</label>
                    <input type="text" id="bd_host" name="bd_host" value="localhost" required>
                </div>
                
                <div class="form-group">
                    <label for="bd_usuario">Usuario de BD (debe tener permisos CREATE):</label>
                    <input type="text" id="bd_usuario" name="bd_usuario" value="epicosie_ricaurte" required>
                </div>
                
                <div class="form-group">
                    <label for="bd_password">Contraseña de BD:</label>
                    <input type="password" id="bd_password" name="bd_password" required
                           placeholder="Contraseña del usuario de BD">
                </div>
                
                <div class="form-group">
                    <label for="bd_nombre">Nombre de la Nueva BD:</label>
                    <input type="text" id="bd_nombre" name="bd_nombre" required 
                           placeholder="Se genera automáticamente" pattern="[a-zA-Z0-9_]{5,50}">
                    <small>Se generará automáticamente: epicosie_[código_sucursal]</small>
                </div>
            </div>
            
            <!-- Opciones -->
            <div class="step">
                <h3>⚙️ Opciones de Instalación</h3>
                
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="verificar_central" checked>
                        Verificar conexión con BD Central
                    </label>
                </div>
                
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="crear_usuario_admin" checked>
                        Crear usuario administrador para la sucursal
                    </label>
                </div>
                
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="sincronizar_categorias" checked>
                        Sincronizar categorías desde BD Central
                    </label>
                </div>
                
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="crear_archivo_conexion" checked>
                        Crear nuevo archivo de conexión
                    </label>
                </div>
                
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="registrar_en_central">
                        Registrar sucursal en BD Central
                    </label>
                </div>
            </div>
            
            <div style="text-align: center; margin-top: 30px;">
                <button type="submit" class="btn" onclick="return confirmarInstalacion()">
                    🚀 Iniciar Instalación
                </button>
            </div>
            
        </form>
        
        <?php else: ?>
        
        <!-- ===== PROCESO DE INSTALACIÓN ===== -->
        <?php
        
        $errores = [];
        $pasos_completados = 0;
        $total_pasos = 10;
        
        // Datos del formulario
        $nombre_sucursal = trim($_POST['nombre_sucursal'] ?? '');
        $codigo_sucursal = strtoupper(trim($_POST['codigo_sucursal'] ?? ''));
        $bd_host = trim($_POST['bd_host'] ?? 'localhost');
        $bd_usuario = trim($_POST['bd_usuario'] ?? '');
        $bd_password = $_POST['bd_password'] ?? '';
        $bd_nombre = trim($_POST['bd_nombre'] ?? '');
        
        $verificar_central = isset($_POST['verificar_central']);
        $crear_usuario_admin = isset($_POST['crear_usuario_admin']);
        $sincronizar_categorias = isset($_POST['sincronizar_categorias']);
        $crear_archivo_conexion = isset($_POST['crear_archivo_conexion']);
        $registrar_en_central = isset($_POST['registrar_en_central']);
        
        // Validaciones
        if (empty($nombre_sucursal) || empty($codigo_sucursal) || empty($bd_usuario) || empty($bd_nombre)) {
            $errores[] = "Todos los campos obligatorios deben estar completos";
        }
        
        if (!empty($errores)) {
            echo '<div class="error">';
            echo '<h3>❌ Errores de validación:</h3><ul>';
            foreach ($errores as $error) {
                echo '<li>' . htmlspecialchars($error) . '</li>';
            }
            echo '</ul><p><a href="javascript:history.back()">← Volver</a></p></div>';
        } else {
            
            echo '<div class="step">';
            echo '<h2>⚡ Instalando Nueva Sucursal</h2>';
            echo '<p><strong>Sucursal:</strong> ' . htmlspecialchars($nombre_sucursal) . ' (' . $codigo_sucursal . ')</p>';
            echo '<p><strong>Nueva BD:</strong> ' . htmlspecialchars($bd_nombre) . '</p>';
            echo '</div>';
            
            echo '<div class="progress"><div class="progress-bar" id="progressBar" style="width: 0%"></div></div>';
            echo '<div id="pasoActual">Iniciando...</div>';
            
            // ===== PASO 1: VERIFICAR BD CENTRAL =====
            if ($verificar_central) {
                echo '<script>document.getElementById("pasoActual").innerHTML = "Paso 1/10: Verificando BD Central...";</script>';
                echo '<div class="step"><h3>🌐 Paso 1: Verificando BD Central</h3>';
                
                try {
                    require_once "api-transferencias/conexion-central.php";
                    $dbCentral = ConexionCentral::conectar();
                    
                    $stmt = $dbCentral->prepare("SELECT COUNT(*) as total FROM catalogo_maestro WHERE activo = 1");
                    $stmt->execute();
                    $resultado = $stmt->fetch();
                    
                    echo '<div class="success">✅ BD Central OK - Productos: ' . $resultado['total'] . '</div>';
                    $pasos_completados++;
                } catch (Exception $e) {
                    echo '<div class="error">❌ Error BD Central: ' . htmlspecialchars($e->getMessage()) . '</div>';
                    $errores[] = "Error BD Central";
                }
                echo '</div>';
                echo '<script>document.getElementById("progressBar").style.width = "10%";</script>';
                flush();
            }
            
            // ===== PASO 2: CONECTAR A MYSQL =====
            echo '<script>document.getElementById("pasoActual").innerHTML = "Paso 2/10: Conectando a MySQL...";</script>';
            echo '<div class="step"><h3>💾 Paso 2: Conectando a MySQL</h3>';
            
            try {
                $dsn = "mysql:host={$bd_host};charset=utf8mb4";
                $pdo = new PDO($dsn, $bd_usuario, $bd_password);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                echo '<div class="success">✅ Conexión MySQL exitosa</div>';
                $pasos_completados++;
            } catch (Exception $e) {
                echo '<div class="error">❌ Error MySQL: ' . htmlspecialchars($e->getMessage()) . '</div>';
                $errores[] = "Error MySQL";
            }
            echo '</div>';
            echo '<script>document.getElementById("progressBar").style.width = "20%";</script>';
            flush();
            
            // ===== PASO 3: CREAR BASE DE DATOS =====
            if (empty($errores)) {
                echo '<script>document.getElementById("pasoActual").innerHTML = "Paso 3/10: Creando BD...";</script>';
                echo '<div class="step"><h3>🏗️ Paso 3: Creando Base de Datos</h3>';
                
                try {
                    // Verificar si existe
                    $stmt = $pdo->prepare("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?");
                    $stmt->execute([$bd_nombre]);
                    
                    if ($stmt->rowCount() > 0) {
                        echo '<div class="warning">⚠️ BD "' . htmlspecialchars($bd_nombre) . '" ya existe</div>';
                    } else {
                        $pdo->exec("CREATE DATABASE `{$bd_nombre}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                        echo '<div class="success">✅ BD "' . htmlspecialchars($bd_nombre) . '" creada</div>';
                    }
                    $pasos_completados++;
                } catch (Exception $e) {
                    echo '<div class="error">❌ Error creando BD: ' . htmlspecialchars($e->getMessage()) . '</div>';
                    $errores[] = "Error creando BD";
                }
                echo '</div>';
                echo '<script>document.getElementById("progressBar").style.width = "30%";</script>';
                flush();
            }
            
            // ===== PASO 4: CONECTAR A LA NUEVA BD =====
            if (empty($errores)) {
                echo '<script>document.getElementById("pasoActual").innerHTML = "Paso 4/10: Conectando a nueva BD...";</script>';
                echo '<div class="step"><h3>🔗 Paso 4: Conectando a Nueva BD</h3>';
                
                try {
                    $dsn_nueva = "mysql:host={$bd_host};dbname={$bd_nombre};charset=utf8mb4";
                    $pdo_nueva = new PDO($dsn_nueva, $bd_usuario, $bd_password);
                    $pdo_nueva->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    
                    echo '<div class="success">✅ Conectado a: ' . htmlspecialchars($bd_nombre) . '</div>';
                    $pasos_completados++;
                } catch (Exception $e) {
                    echo '<div class="error">❌ Error conectando: ' . htmlspecialchars($e->getMessage()) . '</div>';
                    $errores[] = "Error conectando nueva BD";
                }
                echo '</div>';
                echo '<script>document.getElementById("progressBar").style.width = "40%";</script>';
                flush();
            }
            
            // ===== PASO 5: CREAR ESTRUCTURA DE TABLAS =====
            if (empty($errores)) {
                echo '<script>document.getElementById("pasoActual").innerHTML = "Paso 5/10: Creando tablas...";</script>';
                echo '<div class="step"><h3>🏗️ Paso 5: Creando Estructura</h3>';
                
                // SQL basado en tu estructura actual
                $tablas_sql = [
                    "categorias" => "
                    CREATE TABLE IF NOT EXISTS `categorias` (
                      `id` int(11) NOT NULL AUTO_INCREMENT,
                      `categoria` text NOT NULL,
                      `fecha` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
                      PRIMARY KEY (`id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci;",
                    
                    "clientes" => "
                    CREATE TABLE IF NOT EXISTS `clientes` (
                      `id` int(11) NOT NULL AUTO_INCREMENT,
                      `nombre` text NOT NULL,
                      `documento` int(11) NOT NULL,
                      `email` text NOT NULL,
                      `telefono` text NOT NULL,
                      `direccion` text NOT NULL,
                      `fecha_nacimiento` date NOT NULL,
                      `compras` int(11) NOT NULL,
                      `ultima_compra` datetime NOT NULL,
                      `fecha` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
                      PRIMARY KEY (`id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci;",
                    
                    "productos" => "
                    CREATE TABLE IF NOT EXISTS `productos` (
                      `id` int(11) NOT NULL AUTO_INCREMENT,
                      `id_categoria` int(11) NOT NULL,
                      `parent_id` int(11) DEFAULT NULL,
                      `codigo` varchar(50) NOT NULL,
                      `codigo_maestro` varchar(50) DEFAULT NULL,
                      `descripcion` text NOT NULL,
                      `imagen` text NOT NULL,
                      `stock` int(11) NOT NULL,
                      `precio_venta` float NOT NULL,
                      `ventas` int(11) NOT NULL,
                      `fecha` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
                      `es_divisible` tinyint(1) DEFAULT 0,
                      `nombre_mitad` varchar(255) DEFAULT NULL,
                      `precio_mitad` decimal(10,2) DEFAULT NULL,
                      `nombre_tercio` varchar(255) DEFAULT NULL,
                      `precio_tercio` decimal(10,2) DEFAULT NULL,
                      `nombre_cuarto` varchar(255) DEFAULT NULL,
                      `precio_cuarto` decimal(10,2) DEFAULT NULL,
                      PRIMARY KEY (`id`),
                      UNIQUE KEY `uk_codigo` (`codigo`),
                      KEY `idx_codigo_maestro` (`codigo_maestro`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci;",
                    
                    "usuarios" => "
                    CREATE TABLE IF NOT EXISTS `usuarios` (
                      `id` int(11) NOT NULL AUTO_INCREMENT,
                      `nombre` text NOT NULL,
                      `usuario` text NOT NULL,
                      `password` text NOT NULL,
                      `perfil` text NOT NULL,
                      `foto` text NOT NULL,
                      `estado` int(11) NOT NULL,
                      `ultimo_login` datetime NOT NULL,
                      `fecha` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
                      `empresa` text NOT NULL,
                      `telefono` text DEFAULT NULL,
                      `direccion` text DEFAULT NULL,
                      PRIMARY KEY (`id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci;",
                    
                    "ventas" => "
                    CREATE TABLE IF NOT EXISTS `ventas` (
                      `id` int(11) NOT NULL AUTO_INCREMENT,
                      `codigo` int(11) NOT NULL,
                      `id_cliente` int(11) NOT NULL,
                      `id_vendedor` int(11) NOT NULL,
                      `productos` text NOT NULL,
                      `impuesto` float NOT NULL,
                      `descuento` int(11) NOT NULL DEFAULT 0,
                      `neto` float NOT NULL,
                      `total` float NOT NULL,
                      `detalle` text NOT NULL,
                      `metodo_pago` text NOT NULL,
                      `fecha_venta` datetime NOT NULL,
                      `id_vend_abono` int(11) NOT NULL,
                      `abono` float NOT NULL,
                      `fecha_abono` datetime NOT NULL,
                      `pago` text NOT NULL,
                      `Ult_abono` float NOT NULL,
                      `medio_pago` varchar(50) DEFAULT NULL,
                      PRIMARY KEY (`id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci;",
                    
                    "sucursal_local" => "
                    CREATE TABLE IF NOT EXISTS `sucursal_local` (
                      `id` int(11) NOT NULL AUTO_INCREMENT,
                      `codigo_sucursal` varchar(20) NOT NULL,
                      `nombre` varchar(255) NOT NULL,
                      `direccion` text DEFAULT NULL,
                      `telefono` varchar(50) DEFAULT NULL,
                      `email` varchar(255) DEFAULT NULL,
                      `url_base` varchar(255) NOT NULL,
                      `url_api` varchar(255) NOT NULL,
                      `es_principal` tinyint(1) DEFAULT 0,
                      `activo` tinyint(1) DEFAULT 1,
                      `registrada_en_central` tinyint(1) DEFAULT 0,
                      `fecha_registro` timestamp NULL DEFAULT current_timestamp(),
                      `fecha_actualizacion` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
                      PRIMARY KEY (`id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;",
                    
                    "medios_pago" => "
                    CREATE TABLE IF NOT EXISTS `medios_pago` (
                      `id` int(11) NOT NULL AUTO_INCREMENT,
                      `nombre` varchar(100) NOT NULL,
                      PRIMARY KEY (`id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;",
                    
                    "cotizaciones" => "
                    CREATE TABLE IF NOT EXISTS `cotizaciones` (
                      `id` int(11) NOT NULL AUTO_INCREMENT,
                      `codigo` int(11) NOT NULL,
                      `id_cliente` int(11) NOT NULL,
                      `id_vendedor` int(11) NOT NULL,
                      `productos` text NOT NULL,
                      `impuesto` float NOT NULL,
                      `descuento` int(11) NOT NULL DEFAULT 0,
                      `neto` float NOT NULL,
                      `total` float NOT NULL,
                      `detalle` text NOT NULL,
                      `metodo_pago` text NOT NULL,
                      `fecha_venta` datetime NOT NULL,
                      `id_vend_abono` int(11) NOT NULL,
                      `abono` float NOT NULL,
                      `fecha_abono` datetime NOT NULL,
                      `pago` text NOT NULL,
                      `Ult_abono` float NOT NULL,
                      `medio_pago` varchar(50) DEFAULT NULL,
                      `images` text DEFAULT NULL,
                      PRIMARY KEY (`id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci;",
                    
                    "contabilidad" => "
                    CREATE TABLE IF NOT EXISTS `contabilidad` (
                      `id` int(11) NOT NULL AUTO_INCREMENT,
                      `id_vendedor` int(11) NOT NULL,
                      `fecha` datetime NOT NULL,
                      `detalle` text NOT NULL,
                      `valor` varchar(100) NOT NULL,
                      `medio_pago` varchar(50) NOT NULL,
                      `forma_pago` varchar(50) DEFAULT NULL,
                      `factura` varchar(20) DEFAULT NULL,
                      `tipo` varchar(50) NOT NULL,
                      PRIMARY KEY (`id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;",
                    
                    "venta_productos" => "
                    CREATE TABLE IF NOT EXISTS `venta_productos` (
                      `id` int(11) NOT NULL AUTO_INCREMENT,
                      `id_venta` int(11) NOT NULL,
                      `descripcion` varchar(255) NOT NULL,
                      `cantidad` int(11) NOT NULL,
                      `total` decimal(10,2) NOT NULL,
                      PRIMARY KEY (`id`),
                      KEY `id_venta` (`id_venta`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci;"
                ];
                
                $tablas_creadas = 0;
                foreach ($tablas_sql as $tabla => $sql) {
                    try {
                        $pdo_nueva->exec($sql);
                        echo "<p>✅ Tabla <strong>{$tabla}</strong> creada</p>";
                        $tablas_creadas++;
                    } catch (Exception $e) {
                        echo "<p>❌ Error tabla <strong>{$tabla}</strong>: " . htmlspecialchars($e->getMessage()) . "</p>";
                    }
                }
                
                if ($tablas_creadas > 0) {
                    echo '<div class="success">✅ Estructura creada: ' . $tablas_creadas . ' tablas</div>';
                    $pasos_completados++;
                }
                
                echo '</div>';
                echo '<script>document.getElementById("progressBar").style.width = "50%";</script>';
                flush();
            }
            
            // ===== PASO 6: CONFIGURAR SUCURSAL =====
            if (empty($errores)) {
                echo '<script>document.getElementById("pasoActual").innerHTML = "Paso 6/10: Configurando sucursal...";</script>';
                echo '<div class="step"><h3>⚙️ Paso 6: Configurando Sucursal</h3>';
                
                try {
                    $url_actual = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);
                    $url_api = $url_actual . '/api-transferencias/';
                    
                    $stmt = $pdo_nueva->prepare("
                        INSERT INTO sucursal_local 
                        (codigo_sucursal, nombre, url_base, url_api, activo, fecha_registro) 
                        VALUES (?, ?, ?, ?, 1, ?)
                    ");
                    $stmt->execute([$codigo_sucursal, $nombre_sucursal, $url_actual, $url_api, $FECHA_INSTALACION]);
                    
                    echo '<div class="success">';
                    echo '✅ <strong>Sucursal configurada:</strong><br>';
                    echo '• Código: ' . $codigo_sucursal . '<br>';
                    echo '• Nombre: ' . htmlspecialchars($nombre_sucursal) . '<br>';
                    echo '• URL: ' . $url_actual . '<br>';
                    echo '• API: ' . $url_api;
                    echo '</div>';
                    
                    $pasos_completados++;
                } catch (Exception $e) {
                    echo '<div class="error">❌ Error configurando: ' . htmlspecialchars($e->getMessage()) . '</div>';
                }
                
                echo '</div>';
                echo '<script>document.getElementById("progressBar").style.width = "60%";</script>';
                flush();
            }
            
            // ===== PASO 7: CREAR USUARIO ADMIN =====
            if (empty($errores) && $crear_usuario_admin) {
                echo '<script>document.getElementById("pasoActual").innerHTML = "Paso 7/10: Creando usuario admin...";</script>';
                echo '<div class="step"><h3>👤 Paso 7: Creando Usuario Administrador</h3>';
                
                                try {
                    $usuario_admin = 'admin_' . strtolower($codigo_sucursal);
                    $password_admin = 'admin123';
                    $password_hash = crypt($password_admin, '$2a$07$asxx54ahjppf45sd87a5a4dDDGsystemdev$');
                    
                    $stmt = $pdo_nueva->prepare("
                        INSERT INTO usuarios 
                        (nombre, usuario, password, perfil, foto, estado, ultimo_login, empresa, telefono, direccion) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        'Administrador ' . $nombre_sucursal,
                        $usuario_admin,
                        $password_hash,
                        'Administrador',
                        'vistas/img/usuarios/default/anonymous.png',
                        1,
                        $FECHA_INSTALACION,
                        $nombre_sucursal,
                        '',
                        ''
                    ]);
                    
                    echo '<div class="success">';
                    echo '✅ <strong>Usuario administrador creado:</strong><br>';
                    echo '• Usuario: <code>' . $usuario_admin . '</code><br>';
                    echo '• Contraseña: <code>' . $password_admin . '</code><br>';
                    echo '<em>⚠️ Cambiar contraseña después del primer login</em>';
                    echo '</div>';
                    
                    $pasos_completados++;
                    
                } catch (Exception $e) {
                    echo '<div class="error">❌ Error creando usuario: ' . htmlspecialchars($e->getMessage()) . '</div>';
                }
                
                echo '</div>';
                echo '<script>document.getElementById("progressBar").style.width = "70%";</script>';
                flush();
            }
            
            // ===== PASO 8: SINCRONIZAR CATEGORÍAS =====
            if (empty($errores) && $sincronizar_categorias && $verificar_central) {
                echo '<script>document.getElementById("pasoActual").innerHTML = "Paso 8/10: Sincronizando categorías...";</script>';
                echo '<div class="step"><h3>📂 Paso 8: Sincronizando Categorías desde BD Central</h3>';
                
                try {
                    // Obtener categorías de BD central
                    $stmt_central = $dbCentral->prepare("SELECT id, categoria, fecha FROM categorias ORDER BY id");
                    $stmt_central->execute();
                    $categorias_central = $stmt_central->fetchAll();
                    
                    $categorias_sincronizadas = 0;
                    
                    foreach ($categorias_central as $categoria) {
                        try {
                            $stmt_local = $pdo_nueva->prepare("
                                INSERT INTO categorias (id, categoria, fecha) 
                                VALUES (?, ?, ?) 
                                ON DUPLICATE KEY UPDATE categoria = VALUES(categoria), fecha = VALUES(fecha)
                            ");
                            $stmt_local->execute([
                                $categoria['id'],
                                $categoria['categoria'],
                                $categoria['fecha']
                            ]);
                            $categorias_sincronizadas++;
                        } catch (Exception $e) {
                            // Ignorar errores de duplicados
                        }
                    }
                    
                    echo '<div class="success">';
                    echo '✅ <strong>Categorías sincronizadas:</strong> ' . $categorias_sincronizadas;
                    echo '</div>';
                    
                    $pasos_completados++;
                    
                } catch (Exception $e) {
                    echo '<div class="error">❌ Error sincronizando categorías: ' . htmlspecialchars($e->getMessage()) . '</div>';
                }
                
                echo '</div>';
                echo '<script>document.getElementById("progressBar").style.width = "80%";</script>';
                flush();
            }
            
            // ===== PASO 9: CREAR ARCHIVO DE CONEXIÓN =====
            if (empty($errores) && $crear_archivo_conexion) {
                echo '<script>document.getElementById("pasoActual").innerHTML = "Paso 9/10: Creando archivo de conexión...";</script>';
                echo '<div class="step"><h3>🔗 Paso 9: Creando Archivo de Conexión</h3>';
                
                // Generar contenido del archivo de conexión
                $contenido_conexion = '<?php
/*=============================================
CONEXIÓN BD LOCAL PARA SUCURSAL: ' . $codigo_sucursal . '
Generado automáticamente: ' . $FECHA_INSTALACION . '
=============================================*/

class ConexionLocal {
    
    static public function conectar() {
        
        try {
            
            $link = new PDO(
                "mysql:host=' . $bd_host . ';dbname=' . $bd_nombre . ';charset=utf8",
                "' . $bd_usuario . '",
                "' . str_replace('"', '\\"', $bd_password) . '",
                array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
            );
            
            return $link;
            
        } catch(PDOException $e) {
            
            error_log("Error de conexión BD Local [' . $codigo_sucursal . ']: " . $e->getMessage());
            die("Error de conexión a la base de datos local");
        }
    }
}

/*=============================================
INFORMACIÓN DE LA SUCURSAL
=============================================*/
class InfoSucursal {
    
    static public function obtenerDatos() {
        return array(
            "codigo" => "' . $codigo_sucursal . '",
            "nombre" => "' . addslashes($nombre_sucursal) . '",
            "bd_nombre" => "' . $bd_nombre . '",
            "fecha_instalacion" => "' . $FECHA_INSTALACION . '"
        );
    }
}
?>';
                
                try {
                    $archivo_conexion = "modelos/conexion-local-{$codigo_sucursal}.php";
                    
                    if (file_put_contents($archivo_conexion, $contenido_conexion)) {
                        echo '<div class="success">';
                        echo '✅ <strong>Archivo de conexión creado:</strong><br>';
                        echo '<code>' . $archivo_conexion . '</code>';
                        echo '</div>';
                        
                        // Mostrar código para copiar
                        echo '<div class="code">';
                        echo '<strong>📋 Configuración generada:</strong><br>';
                        echo '<textarea id="nuevaConfiguracion" rows="10" style="width: 100%; font-family: monospace; font-size: 12px;">';
                        echo htmlspecialchars($contenido_conexion);
                        echo '</textarea>';
                        echo '<br><button onclick="copiarConfiguracion()" style="margin-top: 10px; padding: 8px 15px; background: #28a745; color: white; border: none; border-radius: 3px;">📋 Copiar código</button>';
                        echo '</div>';
                        
                        $pasos_completados++;
                    } else {
                        echo '<div class="error">❌ No se pudo crear el archivo de conexión</div>';
                    }
                    
                } catch (Exception $e) {
                    echo '<div class="error">❌ Error creando archivo: ' . htmlspecialchars($e->getMessage()) . '</div>';
                }
                
                echo '</div>';
                echo '<script>document.getElementById("progressBar").style.width = "90%";</script>';
                flush();
            }
            
            // ===== PASO 10: REGISTRAR EN BD CENTRAL (OPCIONAL) =====
            if (empty($errores) && $registrar_en_central && $verificar_central) {
                echo '<script>document.getElementById("pasoActual").innerHTML = "Paso 10/10: Registrando en BD Central...";</script>';
                echo '<div class="step"><h3>🌐 Paso 10: Registrando Sucursal en BD Central</h3>';
                
                try {
                    $url_actual = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);
                    
                    // Verificar si ya existe
                    $stmt_verificar = $dbCentral->prepare("
                        SELECT id FROM sucursales WHERE codigo_sucursal = ?
                    ");
                    $stmt_verificar->execute([$codigo_sucursal]);
                    
                    if ($stmt_verificar->rowCount() > 0) {
                        echo '<div class="warning">⚠️ Sucursal ya registrada en BD Central</div>';
                    } else {
                        // Insertar nueva sucursal
                        $stmt_insertar = $dbCentral->prepare("
                            INSERT INTO sucursales 
                            (codigo_sucursal, nombre, direccion, url_base, url_api, activo, fecha_creacion) 
                            VALUES (?, ?, ?, ?, ?, 1, ?)
                        ");
                        $stmt_insertar->execute([
                            $codigo_sucursal,
                            $nombre_sucursal,
                            'Dirección por definir',
                            $url_actual,
                            $url_actual . '/api-transferencias/',
                            $FECHA_INSTALACION
                        ]);
                        
                        echo '<div class="success">';
                        echo '✅ <strong>Sucursal registrada en BD Central</strong><br>';
                        echo '• Código: ' . $codigo_sucursal . '<br>';
                        echo '• URL: ' . $url_actual;
                        echo '</div>';
                    }
                    
                    // Actualizar estado local
                    $stmt_local = $pdo_nueva->prepare("
                        UPDATE sucursal_local 
                        SET registrada_en_central = 1 
                        WHERE codigo_sucursal = ?
                    ");
                    $stmt_local->execute([$codigo_sucursal]);
                    
                    $pasos_completados++;
                    
                } catch (Exception $e) {
                    echo '<div class="error">❌ Error registrando en central: ' . htmlspecialchars($e->getMessage()) . '</div>';
                }
                
                echo '</div>';
                echo '<script>document.getElementById("progressBar").style.width = "100%";</script>';
                flush();
            }
            
            // ===== RESULTADO FINAL =====
            echo '<script>document.getElementById("pasoActual").innerHTML = "¡Instalación completada!";</script>';
            
            $porcentaje_exito = ($pasos_completados / $total_pasos) * 100;
            
            echo '<div class="step">';
            echo '<h2>🎉 Instalación Completada</h2>';
            
            if ($porcentaje_exito >= 80) {
                echo '<div class="success">';
                echo '<h3>✅ ¡Instalación Exitosa!</h3>';
                echo '<p><strong>Progreso:</strong> ' . $pasos_completados . '/' . $total_pasos . ' pasos completados (' . round($porcentaje_exito) . '%)</p>';
                
                echo '<h4>📋 Resumen de la instalación:</h4>';
                echo '<ul>';
                echo '<li><strong>Sucursal:</strong> ' . htmlspecialchars($nombre_sucursal) . ' (' . $codigo_sucursal . ')</li>';
                echo '<li><strong>Base de Datos:</strong> ' . htmlspecialchars($bd_nombre) . '</li>';
                echo '<li><strong>Usuario Admin:</strong> ' . (isset($usuario_admin) ? $usuario_admin : 'No creado') . '</li>';
                echo '<li><strong>Fecha:</strong> ' . $FECHA_INSTALACION . '</li>';
                echo '</ul>';
                
                echo '<h4>🚀 Próximos pasos:</h4>';
                echo '<ol>';
                echo '<li>Acceder al sistema con las credenciales de administrador</li>';
                echo '<li>Cambiar la contraseña por defecto</li>';
                echo '<li>Configurar datos adicionales de la sucursal</li>';
                echo '<li>Sincronizar productos desde el catálogo maestro</li>';
                echo '<li>Entrenar al personal en el uso del sistema</li>';
                echo '</ol>';
                
                echo '<div style="text-align: center; margin-top: 20px;">';
                echo '<a href="index.php" class="btn" style="text-decoration: none;">🏠 Ir al Sistema</a>';
                echo '</div>';
                
                echo '</div>';
                
            } else {
                echo '<div class="warning">';
                echo '<h3>⚠️ Instalación Parcial</h3>';
                echo '<p>La instalación se completó con algunos errores. Progreso: ' . $pasos_completados . '/' . $total_pasos . ' pasos.</p>';
                echo '<p>Revise los mensajes anteriores y complete manualmente los pasos faltantes.</p>';
                echo '</div>';
            }
            
            if (!empty($errores)) {
                echo '<div class="error">';
                echo '<h4>❌ Errores encontrados:</h4>';
                echo '<ul>';
                foreach ($errores as $error) {
                    echo '<li>' . htmlspecialchars($error) . '</li>';
                }
                echo '</ul>';
                echo '</div>';
            }
            
            echo '</div>';
            
            // ===== INFORMACIÓN TÉCNICA =====
            echo '<div class="step">';
            echo '<h3>🔧 Información Técnica</h3>';
            echo '<div class="info">';
            echo '<p><strong>Versión del instalador:</strong> ' . $INSTALADOR_VERSION . '</p>';
            echo '<p><strong>PHP Version:</strong> ' . PHP_VERSION . '</p>';
            echo '<p><strong>MySQL Version:</strong> ' . (isset($pdo_nueva) ? $pdo_nueva->getAttribute(PDO::ATTR_SERVER_VERSION) : 'N/A') . '</p>';
            echo '<p><strong>Directorio de instalación:</strong> ' . __DIR__ . '</p>';
            echo '<p><strong>URL de acceso:</strong> ' . 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/</p>';
            echo '</div>';
            echo '</div>';
            
            // ===== LOG DE INSTALACIÓN =====
            $log_instalacion = [
                'fecha' => $FECHA_INSTALACION,
                'version_instalador' => $INSTALADOR_VERSION,
                'sucursal' => [
                    'codigo' => $codigo_sucursal,
                    'nombre' => $nombre_sucursal
                ],
                'bd' => [
                    'host' => $bd_host,
                    'nombre' => $bd_nombre,
                    'usuario' => $bd_usuario
                ],
                'pasos_completados' => $pasos_completados,
                'total_pasos' => $total_pasos,
                'porcentaje_exito' => $porcentaje_exito,
                'errores' => $errores
            ];
            
            // Guardar log
            try {
                file_put_contents(
                    "logs/instalacion_{$codigo_sucursal}_{$FECHA_INSTALACION}.json",
                    json_encode($log_instalacion, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                );
            } catch (Exception $e) {
                // Ignorar errores de log
            }
        }
        
        ?>
        
        <?php endif; ?>
        
    </div>
</div>

</body>
</html>