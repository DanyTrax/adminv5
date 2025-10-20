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
    
    // 4. Análisis de usuarios (SIN MODIFICAR ESTRUCTURA LOCAL)
    echo "<h3>📊 Análisis de Usuarios Locales:</h3>";
    
    if($sucursalLocal && !empty($usuarios)) {
        $nombreSucursal = $sucursalLocal['nombre'];
        $usuariosSinEmpresa = array_filter($usuarios, function($u) { return empty($u['empresa']); });
        $usuariosConEmpresaDiferente = array_filter($usuarios, function($u) use ($nombreSucursal) { 
            return !empty($u['empresa']) && $u['empresa'] !== $nombreSucursal; 
        });
        
        echo "<p><strong>Distribución de usuarios por empresa:</strong></p>";
        echo "<ul>";
        echo "<li>Usuarios sin empresa: " . count($usuariosSinEmpresa) . "</li>";
        echo "<li>Usuarios con empresa diferente: " . count($usuariosConEmpresaDiferente) . "</li>";
        echo "<li>Usuarios con empresa correcta: " . (count($usuarios) - count($usuariosSinEmpresa) - count($usuariosConEmpresaDiferente)) . "</li>";
        echo "</ul>";
        
        echo "<div style='background: #e8f4fd; padding: 15px; border-radius: 4px; margin: 15px 0;'>";
        echo "<h4>💡 Nueva Estrategia de Sincronización:</h4>";
        echo "<p><strong>✅ NO se modificará la estructura local</strong></p>";
        echo "<p>• El sistema importará <strong>TODOS</strong> los usuarios locales sin restricción de empresa</p>";
        echo "<p>• Al sincronizar desde central → local, se asignará automáticamente la empresa correcta</p>";
        echo "<p>• Al importar desde local → central, se puede asignar la empresa deseada</p>";
        echo "</div>";
        
        if(!empty($usuariosSinEmpresa) || !empty($usuariosConEmpresaDiferente)) {
            echo "<div style='background: #fff3cd; padding: 15px; border-radius: 4px; margin: 15px 0;'>";
            echo "<h4>📋 Usuarios que se pueden importar:</h4>";
            echo "<ul>";
            foreach($usuarios as $usuario) {
                if(empty($usuario['empresa']) || $usuario['empresa'] !== $nombreSucursal) {
                    $tipo = empty($usuario['empresa']) ? 'sin empresa' : 'empresa: ' . $usuario['empresa'];
                    echo "<li><strong>" . htmlspecialchars($usuario['usuario']) . "</strong> (" . htmlspecialchars($usuario['nombre']) . ") - " . $tipo . "</li>";
                }
            }
            echo "</ul>";
            echo "<p><em>Estos usuarios se pueden importar al sistema central y asignar la empresa correcta.</em></p>";
            echo "</div>";
        } else {
            echo "<p style='color: green;'>✅ Todos los usuarios tienen empresa asignada correctamente</p>";
        }
    }
    
    // 5. Información adicional
    echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 4px; margin: 15px 0;'>";
    echo "<h4>🔧 Cómo usar el sistema bidireccional:</h4>";
    echo "<ol>";
    echo "<li><strong>Importar usuarios locales:</strong> Ve a 'Usuarios Centrales Bidireccional' y usa el botón 'Importar' en cada usuario</li>";
    echo "<li><strong>Asignar empresa:</strong> Al importar, se puede asignar la empresa correcta para la sucursal</li>";
    echo "<li><strong>Sincronizar desde central:</strong> Los usuarios centrales se sincronizan automáticamente con la empresa correcta</li>";
    echo "<li><strong>Mantener estructura local:</strong> No se modifica la tabla local, solo se asigna empresa al sincronizar</li>";
    echo "</ol>";
    echo "</div>";
    
    // 6. Prueba de consulta de sincronización (NUEVA ESTRATEGIA)
    echo "<h3>🧪 Prueba de Consulta de Sincronización (Nueva Estrategia):</h3>";
    
    if($sucursalLocal) {
        $nombreSucursal = $sucursalLocal['nombre'];
        
        try {
            // Consulta anterior (con restricción de empresa)
            $stmt = $conexion->prepare("SELECT COUNT(*) as total FROM usuarios WHERE empresa = ?");
            $stmt->execute([$nombreSucursal]);
            $resultadoAnterior = $stmt->fetch();
            
            // Nueva consulta (SIN restricción de empresa)
            $stmt = $conexion->prepare("SELECT COUNT(*) as total FROM usuarios");
            $stmt->execute();
            $resultadoNuevo = $stmt->fetch();
            
            echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 4px; margin: 10px 0;'>";
            echo "<h4>📊 Comparación de estrategias:</h4>";
            echo "<ul>";
            echo "<li><strong>Estrategia anterior:</strong> Usuarios con empresa = '" . htmlspecialchars($nombreSucursal) . "': <strong>" . $resultadoAnterior['total'] . "</strong></li>";
            echo "<li><strong>Nueva estrategia:</strong> TODOS los usuarios locales: <strong>" . $resultadoNuevo['total'] . "</strong></li>";
            echo "</ul>";
            echo "</div>";
            
            if($resultadoNuevo['total'] > 0) {
                echo "<p style='color: green;'>✅ La nueva consulta encontrará " . $resultadoNuevo['total'] . " usuarios para importar</p>";
                echo "<p><em>Estos usuarios se pueden importar al sistema central y asignar la empresa correcta.</em></p>";
            } else {
                echo "<p style='color: red;'>❌ No hay usuarios en la base de datos local</p>";
            }
            
        } catch(Exception $e) {
            echo "<p style='color: red;'>❌ Error en consulta: " . htmlspecialchars($e->getMessage()) . "</p>";
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
