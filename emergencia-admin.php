<?php
/**
 * SCRIPT DE EMERGENCIA - CONTRASEÑA ADMIN
 * Detecta automáticamente la configuración de BD
 */

echo "=== SCRIPT DE EMERGENCIA - CONTRASEÑA ADMIN ===\n";
echo "Fecha: " . date('Y-m-d H:i:s') . "\n\n";

// Intentar cargar configuración automáticamente
$configFiles = [
    'config.php',
    'modelos/conexion.php',
    'configuracion.php'
];

$configLoaded = false;
foreach ($configFiles as $file) {
    if (file_exists($file)) {
        echo "Cargando configuración desde: $file\n";
        try {
            require_once $file;
            $configLoaded = true;
            break;
        } catch (Exception $e) {
            echo "Error cargando $file: " . $e->getMessage() . "\n";
        }
    }
}

if (!$configLoaded) {
    echo "❌ No se pudo cargar configuración automáticamente\n";
    echo "Usando configuración manual...\n";
    
    // Configuración manual - AJUSTAR SEGÚN TU SERVIDOR
    $host = "localhost";
    $dbname = "epicosie_pruebas";
    $username = "epicosie_pruebas";
    $password = "tu_password_aqui"; // CAMBIAR POR LA CONTRASEÑA REAL
    
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo "✅ Conexión manual exitosa\n";
    } catch (Exception $e) {
        echo "❌ Error de conexión manual: " . $e->getMessage() . "\n";
        echo "\nPor favor, edita este script y ajusta la configuración de BD\n";
        exit;
    }
} else {
    echo "✅ Configuración cargada exitosamente\n";
    
    // Intentar usar la conexión existente
    try {
        if (class_exists('Conexion')) {
            $pdo = Conexion::conectar();
            echo "✅ Usando conexión existente\n";
        } else {
            throw new Exception("Clase Conexion no encontrada");
        }
    } catch (Exception $e) {
        echo "Error usando conexión existente: " . $e->getMessage() . "\n";
        echo "Intentando conexión directa...\n";
        
        // Configuración de emergencia
        $host = "localhost";
        $dbname = "epicosie_pruebas";
        $username = "epicosie_pruebas";
        $password = "tu_password_aqui"; // CAMBIAR POR LA CONTRASEÑA REAL
        
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
}

try {
    echo "\n=== ESTABLECIENDO CONTRASEÑA ===\n";
    
    // Verificar si existe admin
    $stmt = $pdo->prepare("SELECT id, usuario, nombre FROM usuarios WHERE usuario = 'admin'");
    $stmt->execute();
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($admin) {
        echo "✅ Usuario admin encontrado (ID: {$admin['id']}, Nombre: {$admin['nombre']})\n";
    } else {
        echo "⚠️  Usuario admin no encontrado, creando...\n";
        
        // Crear usuario admin
        $stmt = $pdo->prepare("
            INSERT INTO usuarios (nombre, usuario, password, perfil, foto, empresa, telefono, direccion, estado, fecha) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, NOW())
        ");
        
        $nombre = "Administrador";
        $usuario = "admin";
        $perfil = "Administrador";
        $foto = "vistas/img/usuarios/default/anonymous.png";
        $empresa = "Central";
        $telefono = "";
        $direccion = "";
        
        $stmt->execute([$nombre, $usuario, "", $perfil, $foto, $empresa, $telefono, $direccion]);
        echo "✅ Usuario admin creado\n";
    }
    
    // Establecer contraseña
    $nuevaPassword = "admin123";
    $passwordEncriptada = crypt($nuevaPassword, '$2a$07$asxx54ahjppf45sd87a5a4dDDGsystemdev$');
    
    echo "Contraseña: $nuevaPassword\n";
    echo "Encriptada: $passwordEncriptada\n";
    
    // Actualizar contraseña
    $stmt = $pdo->prepare("UPDATE usuarios SET password = ? WHERE usuario = 'admin'");
    $resultado = $stmt->execute([$passwordEncriptada]);
    
    if ($resultado) {
        echo "✅ Contraseña actualizada exitosamente\n";
        
        // Verificar
        $stmt = $pdo->prepare("SELECT usuario, password FROM usuarios WHERE usuario = 'admin'");
        $stmt->execute();
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($usuario && $usuario['password'] === $passwordEncriptada) {
            echo "✅ Verificación exitosa\n";
            echo "\n🎉 LISTO PARA USAR:\n";
            echo "Usuario: admin\n";
            echo "Contraseña: admin123\n";
            echo "URL: https://pruebas.acrilicosinfinito.com/\n";
            echo "\n⚠️  IMPORTANTE: Eliminar este archivo después de usar por seguridad\n";
        } else {
            echo "❌ Error en verificación\n";
        }
    } else {
        echo "❌ Error actualizando contraseña\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
?>
