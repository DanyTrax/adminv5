<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🔍 DEBUG - NOTIFICACIONES SOLICITUDES</h2>";
echo "<style>body{font-family:Arial;} .success{color:green;} .error{color:red;} .info{color:blue;}</style>";

try {
    
    echo "<h3>1. Verificar Sesión</h3>";
    if(isset($_SESSION['perfil'])) {
        echo "<p class='success'>✅ Sesión activa - Perfil: " . $_SESSION['perfil'] . "</p>";
    } else {
        echo "<p class='error'>❌ Sin sesión activa</p>";
        exit;
    }
    
    echo "<h3>2. Verificar Archivos</h3>";
    
    $archivos = [
        "api-transferencias/conexion-central.php",
        "controladores/solicitudes-stock.controlador.php", 
        "modelos/solicitudes-stock.modelo.php"
    ];
    
    foreach($archivos as $archivo) {
        if(file_exists($archivo)) {
            echo "<p class='success'>✅ $archivo existe</p>";
        } else {
            echo "<p class='error'>❌ $archivo NO existe</p>";
        }
    }
    
    echo "<h3>3. Probar Conexión Central</h3>";
    require_once "api-transferencias/conexion-central.php";
    
    $conexion = ConexionCentral::conectar();
    if($conexion) {
        echo "<p class='success'>✅ Conexión central OK</p>";
        
        // Verificar tabla solicitudes_stock
        $stmt = $conexion->prepare("SHOW TABLES LIKE 'solicitudes_stock'");
        $stmt->execute();
        if($stmt->fetch()) {
            echo "<p class='success'>✅ Tabla solicitudes_stock existe</p>";
            
            // Contar solicitudes pendientes
            $stmt = $conexion->prepare("SELECT COUNT(*) as total FROM solicitudes_stock WHERE estado = 'pendiente'");
            $stmt->execute();
            $resultado = $stmt->fetch();
            echo "<p class='info'>📊 Solicitudes pendientes: " . $resultado['total'] . "</p>";
            
        } else {
            echo "<p class='error'>❌ Tabla solicitudes_stock NO existe</p>";
        }
        
    } else {
        echo "<p class='error'>❌ Error conexión central</p>";
    }
    
    echo "<h3>4. Probar Controlador</h3>";
    require_once "controladores/solicitudes-stock.controlador.php";
    
    if(method_exists('ControladorSolicitudesStock', 'ctrContarSolicitudesPendientes')) {
        echo "<p class='success'>✅ Método ctrContarSolicitudesPendientes existe</p>";
        
        $contador = ControladorSolicitudesStock::ctrContarSolicitudesPendientes();
        echo "<p class='info'>📊 Contador desde controlador: $contador</p>";
        
    } else {
        echo "<p class='error'>❌ Método ctrContarSolicitudesPendientes NO existe</p>";
    }
    
    if(method_exists('ControladorSolicitudesStock', 'ctrObtenerSolicitudesPendientes')) {
        echo "<p class='success'>✅ Método ctrObtenerSolicitudesPendientes existe</p>";
        
        $solicitudes = ControladorSolicitudesStock::ctrObtenerSolicitudesPendientes(3);
        echo "<p class='info'>📊 Solicitudes obtenidas: " . count($solicitudes) . "</p>";
        
        if(count($solicitudes) > 0) {
            echo "<pre>" . print_r($solicitudes, true) . "</pre>";
        }
        
    } else {
        echo "<p class='error'>❌ Método ctrObtenerSolicitudesPendientes NO existe</p>";
    }
    
    echo "<h3>5. Simular Respuesta JSON</h3>";
    
    $respuesta = [
        "success" => true,
        "data" => [
            "contador" => $contador ?? 0,
            "solicitudes" => $solicitudes ?? [],
            "perfil" => $_SESSION["perfil"]
        ]
    ];
    
    echo "<p class='success'>JSON que debería devolver:</p>";
    echo "<pre style='background:#f0f0f0; padding:10px; border-radius:5px;'>";
    echo json_encode($respuesta, JSON_PRETTY_PRINT);
    echo "</pre>";
    
} catch(Exception $e) {
    echo "<p class='error'>❌ ERROR: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<hr><p><a href='ajax/notificaciones-solicitudes.ajax.php' target='_blank'>🔗 Ver respuesta del AJAX actual</a></p>";
?>