<?php
session_start();
require_once "config.php";
require_once "modelos/conexion.php";
require_once "api-transferencias/conexion-central.php";
require_once "controladores/solicitudes-stock.controlador.php";
require_once "modelos/solicitudes-stock.modelo.php";

// ✅ SIMULAR UNA SOLICITUD COMPLETA
echo "<h2>🔍 DEBUG COMPLETO - CREAR SOLICITUD</h2>";

// ✅ 1. VERIFICAR SESIÓN
echo "<h3>1. Verificar Sesión</h3>";
if(isset($_SESSION["id"]) && isset($_SESSION["nombre"])) {
    echo "<p>✅ Sesión activa - Usuario ID: {$_SESSION['id']} - Nombre: {$_SESSION['nombre']}</p>";
} else {
    echo "<p>❌ No hay sesión activa</p>";
    exit;
}

// ✅ 2. OBTENER DATOS DE SUCURSAL
echo "<h3>2. Obtener Datos de Sucursal</h3>";
try {
    $stmt = Conexion::conectar()->prepare("SELECT codigo_sucursal, nombre FROM sucursal_local LIMIT 1");
    $stmt->execute();
    $sucursal = $stmt->fetch();
    
    if($sucursal) {
        echo "<p>✅ Datos de sucursal obtenidos:</p>";
        echo "<ul>";
        echo "<li>Código: {$sucursal['codigo_sucursal']}</li>";
        echo "<li>Nombre: {$sucursal['nombre']}</li>";
        echo "</ul>";
    } else {
        echo "<p>❌ No se encontraron datos de sucursal</p>";
        exit;
    }
} catch(Exception $e) {
    echo "<p>❌ Error obteniendo datos de sucursal: " . $e->getMessage() . "</p>";
    exit;
}

// ✅ 3. GENERAR NÚMERO DE SOLICITUD
echo "<h3>3. Generar Número de Solicitud</h3>";
try {
    $numeroSolicitud = ModeloSolicitudesStock::mdlGenerarNumeroSolicitud("solicitudes_stock");
    echo "<p>✅ Número generado: {$numeroSolicitud}</p>";
} catch(Exception $e) {
    echo "<p>❌ Error generando número: " . $e->getMessage() . "</p>";
    exit;
}

// ✅ 4. PREPARAR DATOS DE PRUEBA
echo "<h3>4. Preparar Datos de Prueba</h3>";
$productosTest = [
    [
        "id" => 1,
        "codigo" => "TEST001",
        "descripcion" => "Producto de prueba",
        "cantidad" => 5,
        "observacion" => "Prueba desde debug"
    ]
];

$datos = array(
    "numero_solicitud" => $numeroSolicitud,
    "codigo_sucursal_solicitante" => $sucursal["codigo_sucursal"],
    "nombre_sucursal_solicitante" => $sucursal["nombre"],
    "usuario_solicitante" => $_SESSION["id"],
    "nombre_usuario_solicitante" => $_SESSION["nombre"],
    "productos_solicitados" => json_encode($productosTest),
    "tipo_solicitud" => "stock",
    "codigo_remision" => null,
    "nombre_cliente_remision" => null,
    "detalle_adicional" => "Solicitud de prueba desde debug",
    "total_productos" => count($productosTest),
    "total_cantidad" => array_sum(array_column($productosTest, 'cantidad'))
);

echo "<p>✅ Datos preparados:</p>";
echo "<pre>" . json_encode($datos, JSON_PRETTY_PRINT) . "</pre>";

// ✅ 5. INTENTAR CREAR SOLICITUD
echo "<h3>5. Crear Solicitud en Base Central</h3>";
try {
    $respuesta = ModeloSolicitudesStock::mdlCrearSolicitud("solicitudes_stock", $datos);
    echo "<p>📝 Respuesta del modelo: <strong>{$respuesta}</strong></p>";
    
    if($respuesta == "ok") {
        echo "<p>✅ <strong>ÉXITO</strong> - Solicitud creada correctamente</p>";
        
        // ✅ 6. VERIFICAR QUE SE GUARDÓ
        echo "<h3>6. Verificar que se guardó en la base</h3>";
        $stmt = ConexionCentral::conectar()->prepare("SELECT * FROM solicitudes_stock WHERE numero_solicitud = :numero ORDER BY id DESC LIMIT 1");
        $stmt->bindParam(":numero", $numeroSolicitud);
        $stmt->execute();
        $solicitudGuardada = $stmt->fetch();
        
        if($solicitudGuardada) {
            echo "<p>✅ <strong>CONFIRMADO</strong> - Solicitud encontrada en base de datos:</p>";
            echo "<ul>";
            echo "<li>ID: {$solicitudGuardada['id']}</li>";
            echo "<li>Número: {$solicitudGuardada['numero_solicitud']}</li>";
            echo "<li>Sucursal: {$solicitudGuardada['nombre_sucursal_solicitante']}</li>";
            echo "<li>Estado: {$solicitudGuardada['estado']}</li>";
            echo "<li>Fecha: {$solicitudGuardada['fecha_solicitud']}</li>";
            echo "</ul>";
            
            // ✅ ELIMINAR SOLICITUD DE PRUEBA
            $stmtDelete = ConexionCentral::conectar()->prepare("DELETE FROM solicitudes_stock WHERE numero_solicitud = :numero");
            $stmtDelete->bindParam(":numero", $numeroSolicitud);
            $stmtDelete->execute();
            echo "<p>🗑️ Solicitud de prueba eliminada</p>";
            
        } else {
            echo "<p>❌ <strong>ERROR</strong> - Solicitud NO encontrada en base de datos</p>";
        }
        
    } else {
        echo "<p>❌ <strong>ERROR</strong> - Fallo al crear solicitud: {$respuesta}</p>";
    }
    
} catch(Exception $e) {
    echo "<p>❌ <strong>EXCEPCIÓN</strong> - Error: " . $e->getMessage() . "</p>";
    echo "<p>📝 Stack trace:</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<hr>";
echo "<p><a href='crear-solicitud-stock'>← Volver a crear solicitud</a></p>";
echo "<p><strong>Instrucciones:</strong></p>";
echo "<ol>";
echo "<li>Si este test funciona ✅, el problema está en el JavaScript o en el envío del formulario</li>";
echo "<li>Si este test falla ❌, el problema está en el modelo o conexión central</li>";
echo "<li>Compara los datos de este test con los logs del formulario real</li>";
echo "</ol>";
?>