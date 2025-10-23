<?php
/**
 * SCRIPT PARA DIAGNOSTICAR IMPORTAR USUARIOS
 */

echo "<h2>🔍 DIAGNOSTICAR IMPORTAR USUARIOS</h2>";

try {
    require_once "config.php";
    require_once "modelos/usuarios-central.modelo.php";
    
    // 1. Verificar conexión central
    echo "<h3>📋 1. Verificando conexión central:</h3>";
    $conexionCentral = ConexionCentral::conectar();
    if ($conexionCentral) {
        echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px;'>";
        echo "<strong>✅ Conexión central exitosa</strong>";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px;'>";
        echo "<strong>❌ Error en conexión central</strong>";
        echo "</div>";
        exit;
    }
    
    // 2. Verificar sucursales en central
    echo "<h3>📋 2. Verificando sucursales en central:</h3>";
    $stmt = $conexionCentral->prepare("SELECT id, nombre, codigo_sucursal, activo FROM sucursales WHERE activo = 1");
    $stmt->execute();
    $sucursales = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($sucursales) {
        echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px;'>";
        echo "<strong>✅ Sucursales encontradas: " . count($sucursales) . "</strong><br>";
        foreach ($sucursales as $sucursal) {
            echo "• ID: {$sucursal['id']} - {$sucursal['nombre']} ({$sucursal['codigo_sucursal']})<br>";
        }
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px;'>";
        echo "<strong>❌ No hay sucursales activas en central</strong>";
        echo "</div>";
        exit;
    }
    
    // 3. Verificar usuarios en central
    echo "<h3>📋 3. Verificando usuarios en central:</h3>";
    $stmt = $conexionCentral->prepare("SELECT COUNT(*) as total FROM usuarios_central WHERE activo = 1");
    $stmt->execute();
    $totalUsuarios = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    echo "<div style='background: #e7f3ff; padding: 10px; border-radius: 5px;'>";
    echo "<strong>Usuarios centrales actuales: {$totalUsuarios}</strong>";
    echo "</div>";
    
    // 4. Probar mdlConsultarUsuariosSucursales
    echo "<h3>📋 4. Probando mdlConsultarUsuariosSucursales:</h3>";
    $sucursalesConUsuarios = ModeloUsuariosCentral::mdlConsultarUsuariosSucursales();
    
    if ($sucursalesConUsuarios) {
        echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px;'>";
        echo "<strong>✅ Sucursales con usuarios encontradas: " . count($sucursalesConUsuarios) . "</strong><br>";
        
        $totalUsuariosEncontrados = 0;
        foreach ($sucursalesConUsuarios as $sucursal) {
            $estado = $sucursal['estado_conexion'];
            $total = $sucursal['total_usuarios'];
            $usuarios = $sucursal['usuarios'];
            $nombre = $sucursal['sucursal']['nombre'];
            
            echo "<br><strong>Sucursal: {$nombre}</strong><br>";
            echo "Estado: {$estado}<br>";
            echo "Total usuarios: {$total}<br>";
            
            if ($estado === 'conectado' && !empty($usuarios)) {
                echo "Usuarios encontrados:<br>";
                foreach ($usuarios as $usuario) {
                    echo "• {$usuario['nombre']} ({$usuario['usuario']}) - {$usuario['perfil']}<br>";
                    $totalUsuariosEncontrados++;
                }
            } else {
                echo "❌ No se pudieron obtener usuarios<br>";
                if (isset($sucursal['error'])) {
                    echo "Error: " . $sucursal['error'] . "<br>";
                }
            }
        }
        
        echo "<br><strong>Total usuarios encontrados en todas las sucursales: {$totalUsuariosEncontrados}</strong>";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px;'>";
        echo "<strong>❌ No se pudieron obtener sucursales con usuarios</strong>";
        echo "</div>";
    }
    
    // 5. Probar importación
    echo "<h3>📋 5. Probando importación de usuarios:</h3>";
    
    if ($totalUsuariosEncontrados > 0) {
        $resultadoImportacion = ModeloUsuariosCentral::mdlImportarUsuariosSucursales();
        
        if ($resultadoImportacion && $resultadoImportacion['success']) {
            echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px;'>";
            echo "<strong>✅ Importación exitosa</strong><br>";
            echo "Mensaje: " . $resultadoImportacion['message'] . "<br>";
            echo "Usuarios importados: " . $resultadoImportacion['usuarios_importados'] . "<br>";
            
            if (!empty($resultadoImportacion['errores'])) {
                echo "Errores: " . implode(', ', $resultadoImportacion['errores']) . "<br>";
            }
            echo "</div>";
        } else {
            echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px;'>";
            echo "<strong>❌ Error en importación</strong><br>";
            echo "Error: " . ($resultadoImportacion['error'] ?? 'Error desconocido') . "<br>";
            echo "</div>";
        }
    } else {
        echo "<div style='background: #fff3cd; padding: 10px; border-radius: 5px;'>";
        echo "<strong>⚠️ No hay usuarios para importar</strong><br>";
        echo "No se encontraron usuarios en las sucursales activas.";
        echo "</div>";
    }
    
    // 6. Verificar usuarios después de importación
    echo "<h3>📋 6. Verificando usuarios después de importación:</h3>";
    $stmt = $conexionCentral->prepare("SELECT COUNT(*) as total FROM usuarios_central WHERE activo = 1");
    $stmt->execute();
    $totalUsuariosDespues = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    echo "<div style='background: #e7f3ff; padding: 10px; border-radius: 5px;'>";
    echo "<strong>Usuarios centrales después: {$totalUsuariosDespues}</strong><br>";
    echo "Diferencia: " . ($totalUsuariosDespues - $totalUsuarios) . " usuarios nuevos";
    echo "</div>";
    
    // 7. Mostrar algunos usuarios centrales
    echo "<h3>📋 7. Mostrando algunos usuarios centrales:</h3>";
    $stmt = $conexionCentral->prepare("SELECT id, nombre, usuario, perfil, activo FROM usuarios_central WHERE activo = 1 ORDER BY id DESC LIMIT 10");
    $stmt->execute();
    $usuariosCentrales = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($usuariosCentrales) {
        echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px;'>";
        echo "<strong>✅ Usuarios centrales (últimos 10):</strong><br>";
        foreach ($usuariosCentrales as $usuario) {
            echo "• ID: {$usuario['id']} - {$usuario['nombre']} ({$usuario['usuario']}) - {$usuario['perfil']}<br>";
        }
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px;'>";
        echo "<strong>❌ No hay usuarios centrales</strong>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px;'>";
    echo "<strong>💥 ERROR:</strong> " . $e->getMessage();
    echo "</div>";
}

echo "<p><em>Diagnóstico completado - " . date('Y-m-d H:i:s') . "</em></p>";
?>
