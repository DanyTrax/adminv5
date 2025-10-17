<?php
session_start();

echo "<h2>🔧 DEBUG - SISTEMA CENTRALIZADO</h2>";

// 1. Verificar BD Local (SOLO CONSULTA)
echo "<h3>1. BD Local - Solo Consulta</h3>";
try {
    require_once "modelos/conexion.php";
    $conn = Conexion::conectar();
    echo "✅ Conexión local exitosa<br>";
    
    // Solo consultar productos (sin column estado)
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM productos WHERE stock > 0");
    $stmt->execute();
    $result = $stmt->fetch();
    echo "📦 Productos con stock: " . $result['total'] . "<br>";
    
    // Mostrar productos de ejemplo
    $stmt = $conn->prepare("SELECT codigo, descripcion, stock FROM productos WHERE stock > 0 ORDER BY descripcion LIMIT 5");
    $stmt->execute();
    $productos = $stmt->fetchAll();
    
    echo "<strong>Productos disponibles:</strong><br>";
    foreach($productos as $p) {
        echo "- {$p['codigo']}: {$p['descripcion']} (Stock: {$p['stock']})<br>";
    }
    
} catch(Exception $e) {
    echo "❌ Error BD local: " . $e->getMessage() . "<br>";
}

echo "<hr>";

// 2. Verificar BD Central
echo "<h3>2. BD Central - Tablas Nuevas</h3>";
try {
    require_once "api-transferencias/conexion-central.php";
    $conn = ConexionCentral::conectar();
    echo "✅ Conexión central exitosa<br>";
    
    // Verificar tablas nuevas
    $tablasNuevas = ['despachos', 'stock_transito', 'solicitudes_descarga', 'historico_transito'];
    
    foreach($tablasNuevas as $tabla) {
        $stmt = $conn->prepare("SHOW TABLES LIKE '$tabla'");
        $stmt->execute();
        $existe = $stmt->rowCount() > 0;
        echo ($existe ? "✅" : "❌") . " Tabla '$tabla' " . ($existe ? "existe" : "falta") . "<br>";
        
        if($existe) {
            $stmt = $conn->prepare("SELECT COUNT(*) as total FROM $tabla");
            $stmt->execute();
            $count = $stmt->fetch();
            echo "&nbsp;&nbsp;&nbsp;📊 Registros: " . $count['total'] . "<br>";
        }
    }
    
    // Verificar columna codigo_solicitud
    echo "<br><strong>Verificar solicitudes_stock:</strong><br>";
    $stmt = $conn->prepare("DESCRIBE solicitudes_stock");
    $stmt->execute();
    $columnas = $stmt->fetchAll();
    
    $tieneCodigoSolicitud = false;
    foreach($columnas as $col) {
        if($col['Field'] == 'codigo_solicitud') {
            $tieneCodigoSolicitud = true;
            break;
        }
    }
    
    echo ($tieneCodigoSolicitud ? "✅" : "❌") . " Columna 'codigo_solicitud' " . ($tieneCodigoSolicitud ? "existe" : "falta") . "<br>";
    
    if($tieneCodigoSolicitud) {
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM solicitudes_stock WHERE codigo_solicitud IS NOT NULL");
        $stmt->execute();
        $result = $stmt->fetch();
        echo "&nbsp;&nbsp;&nbsp;📋 Solicitudes con código: " . $result['total'] . "<br>";
    }
    
} catch(Exception $e) {
    echo "❌ Error BD central: " . $e->getMessage() . "<br>";
}

echo "<hr>";

// 3. Test de consultas mixtas
echo "<h3>3. Test Consultas Mixtas</h3>";

echo "<strong>📦 Test inventario (BD Local):</strong><br>";
try {
    require_once "modelos/conexion.php";
    $stmt = Conexion::conectar()->prepare("SELECT codigo, descripcion, stock FROM productos WHERE stock > 0 LIMIT 3");
    $stmt->execute();
    $productos = $stmt->fetchAll();
    echo "✅ Consulta exitosa - " . count($productos) . " productos<br>";
} catch(Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

echo "<br><strong>📋 Test solicitudes (BD Central):</strong><br>";
if($tieneCodigoSolicitud ?? false) {
    try {
        require_once "api-transferencias/conexion-central.php";
        $stmt = ConexionCentral::conectar()->prepare("SELECT codigo_solicitud, nombre_usuario_solicitante FROM solicitudes_stock WHERE codigo_solicitud IS NOT NULL LIMIT 3");
        $stmt->execute();
        $solicitudes = $stmt->fetchAll();
        echo "✅ Consulta exitosa - " . count($solicitudes) . " solicitudes<br>";
    } catch(Exception $e) {
        echo "❌ Error: " . $e->getMessage() . "<br>";
    }
} else {
    echo "⏸️ Esperando creación de columna codigo_solicitud<br>";
}

echo "<hr>";
echo "<h3>🎯 ESTADO ACTUAL:</h3>";
echo "<p><strong>✅ BD Local:</strong> Sin modificaciones, solo consultas<br>";
echo "<strong>🔄 BD Central:</strong> Necesita ejecutar scripts SQL<br>";
echo "<strong>📋 Siguiente:</strong> Ejecutar scripts y refrescar esta página</p>";

?>