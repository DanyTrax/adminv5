<?php
require_once 'config-instalacion.php';

// Procesar login si se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password_instalacion'])) {
    $resultado = autenticarUsuario($_POST['password_instalacion']);
    
    if ($resultado['success']) {
        logInstalacion("Login exitoso al sistema de instalación");
        header('Location: instalador.php');
        exit;
    } else {
        logInstalacion("Intento de login fallido: " . $resultado['message']);
        $error_login = $resultado;
    }
}

// Si ya está autenticado, redirigir
if (verificarAutenticacion()) {
    header('Location: instalador.php');
    exit;
}
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
            margin: 0; padding: 0; height: 100vh; 
            display: flex; align-items: center; justify-content: center;
        }
        .login-container { 
            background: white; border-radius: 15px; box-shadow: 0 15px 35px rgba(0,0,0,0.1); 
            padding: 40px; max-width: 400px; width: 90%; text-align: center;
        }
        .logo { font-size: 48px; margin-bottom: 20px; }
        h1 { color: #333; margin-bottom: 10px; font-size: 24px; }
        .subtitle { color: #666; margin-bottom: 30px; font-size: 14px; }
        .form-group { margin-bottom: 20px; text-align: left; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; color: #333; }
        .form-group input { 
            width: 100%; padding: 12px; border: 2px solid #ddd; border-radius: 8px; 
            font-size: 16px; transition: border-color 0.3s;
        }
        .form-group input:focus { border-color: #667eea; outline: none; }
        .btn-login { 
            background: #667eea; color: white; padding: 15px 30px; border: none; 
            border-radius: 8px; cursor: pointer; font-size: 16px; font-weight: bold; 
            width: 100%; transition: background-color 0.3s;
        }
        .btn-login:hover { background: #5a67d8; }
        .btn-login:disabled { background: #ccc; cursor: not-allowed; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border: 1px solid #f5c6cb; border-radius: 8px; margin: 20px 0; }
        .warning { background: #fff3cd; color: #856404; padding: 15px; border: 1px solid #ffeaa7; border-radius: 8px; margin: 20px 0; }
        .security-info { background: #e8f4f8; padding: 20px; border-radius: 8px; margin-top: 20px; font-size: 12px; color: #0c5460; }
        .security-info h4 { margin-top: 0; color: #0c5460; }
        .footer { margin-top: 30px; font-size: 12px; color: #666; }
    </style>
</head>
<body>

<div class="login-container">
    <div class="logo">🔐</div>
    <h1>Sistema de Instalación</h1>
    <p class="subtitle">AdminV5 - Configuración Segura de Sucursales</p>
    
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
    
    <form method="POST" id="loginForm">
        <div class="form-group">
            <label for="password_instalacion">Contraseña de Instalación:</label>
            <input type="password" id="password_instalacion" name="password_instalacion" 
                   required placeholder="Ingrese la contraseña maestra"
                   <?php echo (isset($error_login['bloqueado'])) ? 'disabled' : ''; ?>>
        </div>
        
        <button type="submit" class="btn-login" 
                <?php echo (isset($error_login['bloqueado'])) ? 'disabled' : ''; ?>>
            🚀 Acceder al Instalador
        </button>
    </form>
    
    <div class="security-info">
        <h4>🛡️ Información de Seguridad</h4>
        <ul style="text-align: left; margin: 10px 0;">
            <li><strong>Sesión:</strong> <?php echo SESION_DURACION; ?> minutos</li>
            <li><strong>Intentos máximos:</strong> <?php echo MAX_INTENTOS_LOGIN; ?></li>
            <li><strong>Bloqueo temporal:</strong> 5 minutos</li>
            <li><strong>Logs:</strong> Todos los accesos son registrados</li>
        </ul>
        <p><strong>⚠️ Solo personal autorizado puede acceder a este sistema.</strong></p>
    </div>
    
    <div class="footer">
        <p><strong>danytrax/adminv5</strong> | Sistema de Gestión Empresarial</p>
        <p>IP: <?php echo $_SERVER['REMOTE_ADDR'] ?? 'Unknown'; ?> | 
           Fecha: <?php echo date('Y-m-d H:i:s'); ?></p>
    </div>
</div>

<script>
// Auto-focus en el campo de contraseña
document.getElementById('password_instalacion').focus();

// Envío del formulario
document.getElementById('loginForm').addEventListener('submit', function(e) {
    const password = document.getElementById('password_instalacion').value;
    if (password.length < 8) {
        e.preventDefault();
        alert('La contraseña debe tener al menos 8 caracteres');
        return false;
    }
});
</script>

</body>
</html>