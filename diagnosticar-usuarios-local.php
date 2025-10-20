<?php
/**
 * Script para diagnosticar usuarios locales y corregir campo empresa
 */

echo "<h2>🔍 Diagnóstico de Usuarios Locales</h2>";

try {
    require_once "modelos/conexion.php";
    $conexion = Conexion::conectar();
    
    // 1. Verificar usuarios actuales
    echo "<h3>📊 Usuarios en BD Local:</h3>";
    $stmt = $conexion->query("SELECT id, nombre, usuario, perfil, empresa, estado FROM usuarios ORDER BY id");
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p><strong>Total usuarios:</strong> " . count($usuarios) . "</p>";
    
    if(!empty($usuarios)) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Usuario</th><th>Nombre</th><th>Perfil</th><th>Empresa</th><th>Estado</th></tr>";
        foreach($usuarios as $usuario) {
            echo "<tr>";
            echo "<td>" . $usuario['id'] . "</td>";
            echo "<td>" . htmlspecialchars($usuario['usuario']) . "</td>";
            echo "<td>" . htmlspecialchars($usuario['nombre']) . "</td>";
            echo "<td>" . htmlspecialchars($usuario['perfil']) . "</td>";
            echo "<td style='background: " . (empty($usuario['empresa']) ? '#ffebee' : '#e8f5e8') . ";'>" . htmlspecialchars($usuario['empresa'] ?: 'VACÍO') . "</td>";
            echo "<td>" . ($usuario['estado'] ? 'Activo' : 'Inactivo') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // 2. Verificar sucursal local
    echo "<h3>🏢 Información de Sucursal Local:</h3>";
    $stmt = $conexion->query("SELECT * FROM sucursal_local LIMIT 1");
    $sucursalLocal = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if($sucursalLocal) {
        echo "<p><strong>Nombre:</strong> " . htmlspecialchars($sucursalLocal['nombre']) . "</p>";
        echo "<p><strong>Código:</strong> " . htmlspecialchars($sucursalLocal['codigo_sucursal']) . "</p>";
        echo "<p><strong>Activa:</strong> " . ($sucursalLocal['activo'] ? 'Sí' : 'No') . "</p>";
    } else {
        echo "<p style='color: red;'>❌ No hay información de sucursal local</p>";
    }
    
    // 3. Verificar BD Central
    echo "<h3>🌐 Información de BD Central:</h3>";
    try {
        require_once "api-transferencias/conexion-central.php";
        $conexionCentral = ConexionCentral::conectar();
        
        $stmt = $conexionCentral->query("SELECT id, nombre, codigo_sucursal FROM sucursales WHERE activo = 1");
        $sucursalesCentral = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<p><strong>Sucursales activas en Central:</strong></p>";
        echo "<ul>";
        foreach($sucursalesCentral as $sucursal) {
            echo "<li>" . htmlspecialchars($sucursal['nombre']) . " (" . htmlspecialchars($sucursal['codigo_sucursal']) . ")</li>";
        }
        echo "</ul>";
        
    } catch(Exception $e) {
        echo "<p style='color: red;'>❌ Error conectando a BD Central: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    
    // 4. Proponer corrección
    echo "<h3>🔧 Corrección Propuesta:</h3>";
    
    if($sucursalLocal && !empty($usuarios)) {
        $nombreSucursal = $sucursalLocal['nombre'];
        $usuariosSinEmpresa = array_filter($usuarios, function($u) { return empty($u['empresa']); });
        
        if(!empty($usuariosSinEmpresa)) {
            echo "<p style='color: orange;'>⚠️ Hay " . count($usuariosSinEmpresa) . " usuarios sin empresa asignada</p>";
            echo "<p><strong>Acción sugerida:</strong> Actualizar campo 'empresa' con el nombre de la sucursal: <strong>" . htmlspecialchars($nombreSucursal) . "</strong></p>";
            
            echo "<form method='post' style='margin: 20px 0;'>";
            echo "<input type='hidden' name='accion' value='corregir_empresa'>";
            echo "<input type='hidden' name='nombre_sucursal' value='" . htmlspecialchars($nombreSucursal) . "'>";
            echo "<button type='submit' style='background: #4CAF50; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;'>";
            echo "🔧 Corregir Campo Empresa";
            echo "</button>";
            echo "</form>";
        } else {
            echo "<p style='color: green;'>✅ Todos los usuarios tienen empresa asignada</p>";
        }
    }
    
    // 5. Procesar corrección si se envió
    if(isset($_POST['accion']) && $_POST['accion'] === 'corregir_empresa') {
        $nombreSucursal = $_POST['nombre_sucursal'];
        
        try {
            $stmt = $conexion->prepare("UPDATE usuarios SET empresa = ? WHERE empresa = '' OR empresa IS NULL");
            $resultado = $stmt->execute([$nombreSucursal]);
            
            if($resultado) {
                echo "<div style='background: #e8f5e8; padding: 10px; border-radius: 4px; margin: 10px 0;'>";
                echo "<p style='color: green;'>✅ Campo 'empresa' actualizado correctamente</p>";
                echo "<p>Se asignó la empresa: <strong>" . htmlspecialchars($nombreSucursal) . "</strong></p>";
                echo "</div>";
                
                // Recargar página para mostrar cambios
                echo "<script>setTimeout(function(){ window.location.reload(); }, 2000);</script>";
            } else {
                echo "<p style='color: red;'>❌ Error al actualizar campo empresa</p>";
            }
        } catch(Exception $e) {
            echo "<p style='color: red;'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }
    
    // 6. Probar consulta de sincronización
    echo "<h3>🧪 Prueba de Consulta de Sincronización:</h3>";
    
    if($sucursalLocal) {
        $nombreSucursal = $sucursalLocal['nombre'];
        
        $stmt = $conexion->prepare("SELECT COUNT(*) as total FROM usuarios WHERE empresa = ?");
        $stmt->execute([$nombreSucursal]);
        $totalConEmpresa = $stmt->fetch()['total'];
        
        echo "<p><strong>Usuarios con empresa = '" . htmlspecialchars($nombreSucursal) . "':</strong> " . $totalConEmpresa . "</p>";
        
        if($totalConEmpresa > 0) {
            echo "<p style='color: green;'>✅ La consulta de sincronización debería funcionar correctamente</p>";
        } else {
            echo "<p style='color: red;'>❌ La consulta de sincronización no encontrará usuarios</p>";
        }
    }
    
} catch(Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<h3>📋 Próximos pasos:</h3>";
echo "<p>1. Si hay usuarios sin empresa, usa el botón de corrección</p>";
echo "<p>2. Verifica que la consulta de sincronización funcione</p>";
echo "<p>3. Prueba el sistema bidireccional nuevamente</p>";
?>
