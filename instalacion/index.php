<?php
// Configuración simple de errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Iniciar sesión
session_start();

// Configuración de instalación
define('INSTALACION_PASSWORD', 'InstalarAdmin2024!');
define('MAX_INTENTOS', 3);
define('TIEMPO_BLOQUEO', 300); // 5 minutos

// Procesar login
$error = '';
$debug_info = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password_instalacion'])) {
    
    $password_ingresada = $_POST['password_instalacion'];
    $debug_info['password_length'] = strlen($password_ingresada);
    $debug_info['expected_password'] = 'InstalarAdmin2024!';
    $debug_info['matches'] = ($password_ingresada === INSTALACION_PASSWORD);
    
    // Verificar intentos
    $intentos = $_SESSION['intentos'] ?? 0;
    $ultimo_intento = $_SESSION['ultimo_intento'] ?? 0;
    
    if ($intentos >= MAX_INTENTOS && (time() - $ultimo_intento) < TIEMPO_BLOQUEO) {
        $tiempo_restante = TIEMPO_BLOQUEO - (time() - $ultimo_intento);
        $error = "Demasiados intentos. Espere " . ceil($tiempo_restante / 60) . " minutos.";
    } elseif ($password_ingresada === INSTALACION_PASSWORD) {
        // Login exitoso
        $_SESSION['instalacion_logueado'] = true;
        $_SESSION['instalacion_tiempo'] = time();
        $_SESSION['intentos'] = 0;
        
        header('Location: instalador.php');
        exit;
    } else {
        // Password incorrecto
        $_SESSION['intentos'] = $intentos + 1;
        $_SESSION['ultimo_intento'] = time();
        $error = "Contraseña incorrecta. Intento " . $_SESSION['intentos'] . " de " . MAX_INTENTOS;
    }
}

// Verificar si ya está logueado
if (isset($_SESSION['instalacion_logueado']) && $_SESSION['instalacion_logueado'] === true) {
    // Verificar tiempo de sesión (60 minutos)
    if (isset($_SESSION['instalacion_tiempo']) && (time() - $_SESSION['instalacion_tiempo']) < 3600) {
        header('Location: instalador.php');
        exit;
    } else {
        // Sesión expirada
        session_destroy();
        session_start();
        $error = "Sesión expirada. Inicie sesión nuevamente.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🔐 Instalación AdminV5</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
            margin: 0; padding: 20px; min-height: 100vh; 
            display: flex; align-items: center; justify-content: center;
        }
        .container { 
            background: white; border-radius: 10px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); 
            padding: 30px; max-width: 400px; width: 100%; text-align: center;
        }
        .logo { font-size: 64px; margin-bottom: 20px; }
        h1 { color: #333; margin-bottom: 30px; }
        .form-group { margin-bottom: 20px; text-align: left; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="password"] { 
            width: 100%; padding: 12px; border: 2px solid #ddd; border-radius: 5px; 
            font-size: 16px; box-sizing: border-box;
        }
        input[type="password"]:focus { border-color: #667eea; outline: none; }
        .btn { 
            background: #667eea; color: white; padding: 12px 24px; border: none; 
            border-radius: 5px; cursor: pointer; font-size: 16px; width: 100%;
        }
        .btn:hover { background: #5a67d8; }
        .error { 
            background: #f8d7da; color: #721c24; padding: 15px; 
            border: 1px solid #f5c6cb; border-radius: 5px; margin: 15px 0; 
        }
        .debug { 
            background: #e8f4f8; color: #0c5460; padding: 15px; 
            border: 1px solid #bee5eb; border-radius: 5px; margin: 15px 0; 
            text-align: left; font-size: 12px;
        }
        .info { 
            background: #d4edda; color: #155724; padding: 15px; 
            border: 1px solid #c3e6cb; border-radius: 5px; margin: 15px 0; 
            font-size: 12px;
        }
        .footer { 
            margin-top: 30px; font-size: 12px; color: #666; 
            border-top: 1px solid #eee; padding-top: 15px;
        }
        .test-btn { 
            background: #28a745; padding: 8px 12px; margin: 5px; 
            font-size: 12px; border-radius: 3px; border: none; color: white; cursor: pointer;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="logo">🔐</div>
    <h1>Sistema de Instalación</h1>
    <p>AdminV5 - Configuración de Sucursales</p>
    
    <?php if (!empty($error)): ?>
        <div class="error">
            <strong>❌ <?php echo htmlspecialchars($error); ?></strong>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_GET['logout'])): ?>
        <div class="info">
            <strong>✅ Sesión cerrada correctamente</strong>
        </div>
    <?php endif; ?>
    
    <form method="POST" action="">
        <div class="form-group">
            <label for="password_instalacion">Contraseña de Instalación:</label>
            <input type="password" 
                   id="password_instalacion" 
                   name="password_instalacion" 
                   placeholder="Ingrese la contraseña maestra"
                   required 
                   autocomplete="off">
        </div>
        
        <button type="submit" class="btn">
            🚀 Acceder al Instalador
        </button>
    </form>
    
    <!-- Botones de prueba -->
    <div style="margin-top: 20px;">
        <button type="button" class="test-btn" onclick="autoFill()">
            🔑 Auto-completar
        </button>
        <button type="button" class="test-btn" onclick="toggleDebug()">
            🐛 Debug
        </button>
    </div>
    
    <!-- Debug info (oculto por defecto) -->
    <?php if (isset($_GET['debug']) && !empty($debug_info)): ?>
        <div class="debug">
            <strong>🐛 Información de Debug:</strong><br>
            <pre><?php echo json_encode($debug_info, JSON_PRETTY_PRINT); ?></pre>
        </div>
    <?php endif; ?>
    
    <div class="info">
        <strong>🛡️ Configuración de Seguridad:</strong><br>
        • Sesión: 60 minutos<br>
        • Intentos máximos: <?php echo MAX_INTENTOS; ?><br>
        • Bloqueo: 5 minutos<br>
        • Logs: Registrados automáticamente
    </div>
    
    <div class="footer">
        <strong>danytrax/adminv5</strong> | Sistema de Gestión<br>
        IP: <?php echo $_SERVER['REMOTE_ADDR'] ?? 'Unknown'; ?><br>
        <?php echo date('d/m/Y H:i:s'); ?>
    </div>
</div>

<script>
// Auto-focus
document.getElementById('password_instalacion').focus();

// Auto-completar para pruebas
function autoFill() {
    document.getElementById('password_instalacion').value = 'InstalarAdmin2024!';
}

// Toggle debug
function toggleDebug() {
    const currentUrl = window.location.href.split('?')[0];
    const hasDebug = window.location.search.includes('debug');
    
    if (hasDebug) {
        window.location.href = currentUrl;
    } else {
        window.location.href = currentUrl + '?debug=1';
    }
}

// Envío del formulario
document.querySelector('form').addEventListener('submit', function() {
    const btn = document.querySelector('.btn');
    btn.textContent = '⏳ Procesando...';
    btn.disabled = true;
});
</script>

</body>
</html>