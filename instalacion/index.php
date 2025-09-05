<?php
// Activar todos los errores para debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config-instalacion.php';

// Variables de debug
$debug_info = [];
$debug_info['metodo'] = $_SERVER['REQUEST_METHOD'];
$debug_info['post_data'] = $_POST;
$debug_info['session_status'] = session_status();
$debug_info['session_data'] = $_SESSION ?? [];

// Procesar login si se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $debug_info['procesando_post'] = true;
    
    if (isset($_POST['password_instalacion'])) {
        $password_ingresada = $_POST['password_instalacion'];
        $debug_info['password_recibida'] = '***' . substr($password_ingresada, -3);
        $debug_info['password_esperada'] = '***' . substr(INSTALACION_PASSWORD, -3);
        $debug_info['passwords_coinciden'] = ($password_ingresada === INSTALACION_PASSWORD);
        
        $resultado = autenticarUsuario($password_ingresada);
        $debug_info['resultado_auth'] = $resultado;
        
        if ($resultado['success']) {
            $debug_info['redirigiendo'] = true;
            logInstalacion("Login exitoso al sistema de instalación - IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'Unknown'));
            
            // Forzar headers antes de cualquier output
            if (!headers_sent()) {
                header('Location: instalador.php');
                exit;
            } else {
                $debug_info['headers_sent'] = true;
                echo '<script>window.location.href="instalador.php";</script>';
                echo '<meta http-equiv="refresh" content="0;url=instalador.php">';
                exit;
            }
        } else {
            logInstalacion("Intento de login fallido: " . $resultado['message'] . " - IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'Unknown'));
            $error_login = $resultado;
            $debug_info['error_login'] = $error_login;
        }
    } else {
        $debug_info['no_password_field'] = true;
        $error_login = ['success' => false, 'message' => 'No se recibió el campo de contraseña'];
    }
} else {
    $debug_info['no_post'] = true;
}

// Si ya está autenticado, redirigir
if (verificarAutenticacion()) {
    $debug_info['ya_autenticado'] = true;
    if (!headers_sent()) {
        header('Location: instalador.php');
        exit;
    } else {
        echo '<script>window.location.href="instalador.php";</script>';
        exit;
    }
}

// Mostrar debug si hay errores
$mostrar_debug = isset($error_login) || isset($_GET['debug']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🔐 Acceso al Sistema de Instalación - AdminV5</title>
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
            margin: 0; padding: 0; min-height: 100vh; 
            display: flex; align-items: center; justify-content: center;
        }
        .login-container { 
            background: white; border-radius: 15px; box-shadow: 0 15px 35px rgba(0,0,0,0.1); 
            padding: 40px; max-width: 450px; width: 90%; text-align: center; margin: 20px;
        }
        .logo { font-size: 48px; margin-bottom: 20px; }
        h1 { color: #333; margin-bottom: 10px; font-size: 24px; }
        .subtitle { color: #666; margin-bottom: 30px; font-size: 14px; }
        .form-group { margin-bottom: 20px; text-align: left; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; color: #333; }
        .form-group input { 
            width: 100%; padding: 12px; border: 2px solid #ddd; border-radius: 8px; 
            font-size: 16px; transition: border-color 0.3s; box-sizing: border-box;
        }
        .form-group input:focus { border-color: #667eea; outline: none; }
        .btn-login { 
            background: #667eea; color: white; padding: 15px 30px; border: none; 
            border-radius: 8px; cursor: pointer; font-size: 16px; font-weight: bold; 
            width: 100%; transition: background-color 0.3s; box-sizing: border-box;
        }
        .btn-login:hover { background: #5a67d8; }
        .btn-login:disabled { background: #ccc; cursor: not-allowed; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border: 1px solid #f5c6cb; border-radius: 8px; margin: 20px 0; text-align: left; }
        .warning { background: #fff3cd; color: #856404; padding: 15px; border: 1px solid #ffeaa7; border-radius: 8px; margin: 20px 0; }
        .success { background: #d4edda; color: #155724; padding: 15px; border: 1px solid #c3e6cb; border-radius: 8px; margin: 20px 0; }
        .debug-info { background: #e8f4f8; padding: 20px; border-radius: 8px; margin-top: 20px; font-size: 12px; color: #0c5460; text-align: left; }
        .debug-info h4 { margin-top: 0; color: #0c5460; }
        .debug-info pre { background: #f8f9fa; padding: 10px; border-radius: 4px; overflow-x: auto; }
        .security-info { background: #e8f4f8; padding: 20px; border-radius: 8px; margin-top: 20px; font-size: 12px; color: #0c5460; }
        .security-info h4 { margin-top: 0; color: #0c5460; }
        .footer { margin-top: 30px; font-size: 12px; color: #666; }
        .test-buttons { margin: 10px 0; }
        .test-buttons button { padding: 5px 10px; margin: 2px; font-size: 11px; }
    </style>
</head>
<body>

<div class="login-container">
    <div class="logo">🔐</div>
    <h1>Sistema de Instalación</h1>
    <p class="subtitle">AdminV5 - Configuración Segura de Sucursales</p>
    
    <?php if (isset($_GET['logout']) && $_GET['logout'] === 'success'): ?>
        <div class="success">
            <strong>✅ Sesión cerrada correctamente</strong><br>
            Puedes iniciar sesión nuevamente si es necesario.
        </div>
    <?php endif; ?>
    
    <?php if (isset($error_login)): ?>
        <?php if (isset($error_login['bloqueado'])): ?>
            <div class="error">
                <strong>🚫 Acceso Bloqueado</strong><br>
                <?php echo htmlspecialchars($error_login['message']); ?>
            </div>
        <?php else: ?>
            <div class="error">
                <strong>❌ <?php echo htmlspecialchars($error_login['message']); ?></strong>
                <?php if (isset($error_login['intentos_restantes']) && $error_login['intentos_restantes'] > 0): ?>
                    <br><small>Intentos restantes: <?php echo $error_login['intentos_restantes']; ?></small>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
    
    <form method="POST" id="loginForm" action="<?php echo $_SERVER['PHP_SELF']; ?>">
        <div class="form-group">
            <label for="password_instalacion">Contraseña de Instalación:</label>
            <input type="password" id="password_instalacion" name="password_instalacion" 
                   required placeholder="Ingrese la contraseña maestra"
                   <?php echo (isset($error_login['bloqueado'])) ? 'disabled' : ''; ?>
                   value="">
        </div>
        
        <button type="submit" class="btn-login" 
                <?php echo (isset($error_login['bloqueado'])) ? 'disabled' : ''; ?>>
            🚀 Acceder al Instalador
        </button>
        
        <!-- Botones de prueba -->
        <div class="test-buttons">
            <button type="button" onclick="autoComplete()">🔑 Auto-completar</button>
            <button type="button" onclick="toggleDebug()">🐛 Ver Debug</button>
            <button type="button" onclick="testConfig()">⚙️ Test Config</button>
        </div>
    </form>
    
    <!-- Información de debug -->
    <?php if ($mostrar_debug): ?>
        <div class="debug-info" id="debugInfo">
            <h4>🐛 Información de Debug</h4>
            <pre><?php echo json_encode($debug_info, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE); ?></pre>
            
            <h5>📋 Verificación de Configuración:</h5>
            <ul>
                <li><strong>Archivo config existe:</strong> <?php echo file_exists('config-instalacion.php') ? '✅ SÍ' : '❌ NO'; ?></li>
                <li><strong>Constante PASSWORD definida:</strong> <?php echo defined('INSTALACION_PASSWORD') ? '✅ SÍ' : '❌ NO'; ?></li>
                <li><strong>Función autenticarUsuario existe:</strong> <?php echo function_exists('autenticarUsuario') ? '✅ SÍ' : '❌ NO'; ?></li>
                <li><strong>Sesiones habilitadas:</strong> <?php echo (session_status() === PHP_SESSION_ACTIVE) ? '✅ SÍ' : '❌ NO'; ?></li>
            </ul>
            
            <?php if (defined('INSTALACION_PASSWORD')): ?>
            <p><strong>Contraseña configurada:</strong> ***<?php echo substr(INSTALACION_PASSWORD, -4); ?></p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <div class="security-info">
        <h4>🛡️ Información de Seguridad</h4>
        <ul style="text-align: left; margin: 10px 0;">
            <li><strong>Sesión:</strong> <?php echo defined('SESION_DURACION') ? SESION_DURACION : '60'; ?> minutos</li>
            <li><strong>Intentos máximos:</strong> <?php echo defined('MAX_INTENTOS_LOGIN') ? MAX_INTENTOS_LOGIN : '3'; ?></li>
            <li><strong>Bloqueo temporal:</strong> 5 minutos</li>
            <li><strong>Logs:</strong> Todos los accesos son registrados</li>
        </ul>
        <p><strong>⚠️ Solo personal autorizado puede acceder a este sistema.</strong></p>
    </div>
    
    <div class="footer">
        <p><strong>danytrax/adminv5</strong> | Sistema de Gestión Empresarial</p>
        <p>IP: <?php echo $_SERVER['REMOTE_ADDR'] ?? 'Unknown'; ?> | 
           Fecha: <?php echo date('Y-m-d H:i:s'); ?></p>
        <p><a href="?debug=1" style="color: #666; font-size: 10px;">Ver información de debug</a></p>
    </div>
</div>

<script>
// Auto-focus en el campo de contraseña
document.getElementById('password_instalacion').focus();

// Función para auto-completar (solo para debug)
function autoComplete() {
    document.getElementById('password_instalacion').value = 'InstalarAdmin2024!';
}

// Toggle debug info
function toggleDebug() {
    const debugInfo = document.getElementById('debugInfo');
    if (debugInfo) {
        debugInfo.style.display = debugInfo.style.display === 'none' ? 'block' : 'none';
    } else {
        window.location.href = '?debug=1';
    }
}

// Test configuración
function testConfig() {
    fetch('index.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'password_instalacion=TEST_CONFIG'
    })
    .then(response => response.text())
    .then(data => {
        console.log('Respuesta del servidor:', data);
        alert('Ver consola para la respuesta del servidor');
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error de conexión: ' + error.message);
    });
}

// Manejo del formulario
document.getElementById('loginForm').addEventListener('submit', function(e) {
    const password = document.getElementById('password_instalacion').value;
    
    if (password.length < 8) {
        e.preventDefault();
        alert('La contraseña debe tener al menos 8 caracteres');
        return false;
    }
    
    console.log('Enviando formulario con contraseña de longitud:', password.length);
    
    // Mostrar indicador de carga
    const submitBtn = this.querySelector('.btn-login');
    submitBtn.textContent = '⏳ Procesando...';
    submitBtn.disabled = true;
    
    // Habilitar de nuevo después de un timeout en caso de error
    setTimeout(() => {
        submitBtn.textContent = '🚀 Acceder al Instalador';
        submitBtn.disabled = false;
    }, 5000);
});

// Debug de eventos
console.log('Login page loaded');
console.log('POST data available:', <?php echo json_encode($debug_info); ?>);
</script>

</body>
</html>