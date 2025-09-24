<?php
session_start();
require_once "config.php";
require_once "modelos/conexion.php";
require_once "api-transferencias/conexion-central.php";
require_once "controladores/solicitudes-stock.controlador.php";
require_once "modelos/solicitudes-stock.modelo.php";

// ✅ ACTIVAR MOSTRAR TODOS LOS ERRORES
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🔍 DEBUG COMPLETO - CREAR SOLICITUD</h2>";
echo "<style>body{font-family:Arial;} .success{color:green;} .error{color:red;} .info{color:blue;}</style>";

try {

    // ✅ 1. VERIFICAR SESIÓN
    echo "<h3>1. Verificar Sesión</h3>";
    if(isset($_SESSION["id"]) && isset($_SESSION["nombre"])) {
        echo "<p class='success'>✅ Sesión activa - Usuario ID: {$_SESSION['id']} - Nombre: {$_SESSION['nombre']}</p>";
    } else {
        echo "<p class='error'>❌ No hay sesión activa</p>";
        exit;
    }

    // ✅ 2. OBTENER DATOS DE SUCURSAL
    echo "<h3>2. Obtener Datos de Sucursal</h3>";
    $stmt = Conexion::conectar()->prepare("SELECT codigo_sucursal, nombre FROM sucursal_local LIMIT 1");
    $stmt->execute();
    $sucursal = $stmt->fetch();
    
    if($sucursal) {
        echo "<p class='success'>✅ Datos de sucursal obtenidos:</p>";
        echo "<ul>";
        echo "<li><strong>Código:</strong> {$sucursal['codigo_sucursal']}</li>";
        echo "<li><strong>Nombre:</strong> {$sucursal['nombre']}</li>";
        echo "</ul>";
    } else {
        echo "<p class='error'>❌ No se encontraron datos de sucursal</p>";
        exit;
    }

    // ✅ 3. VERIFICAR CONEXIÓN CENTRAL
    echo "<h3>3. Verificar Conexión Central</h3>";
    $conexionCentral = ConexionCentral::conectar();
    if($conexionCentral) {
        echo "<p class='success'>✅ Conexión central establecida</p>";
    } else {
        echo "<p class='error'>❌ No se pudo conectar a la base central</p>";
        exit;
    }

    // ✅ 4. VERIFICAR TABLA EN CENTRAL
    echo "<h3>4. Verificar Tabla en Base Central</h3>";
    $stmt = $conexionCentral->prepare("SHOW TABLES LIKE 'solicitudes_stock'");
    $stmt->execute();
    if($stmt->fetch()) {
        echo "<p class='success'>✅ Tabla solicitudes_stock existe en base central</p>";
        
        // Contar registros actuales
        $stmt = $conexionCentral->prepare("SELECT COUNT(*) as total FROM solicitudes_stock");
        $stmt->execute();
        $total = $stmt->fetch();
        echo "<p class='info'>📊 Total de solicitudes actuales: {$total['total']}</p>";
        
    } else {
        echo "<p class='error'>❌ Tabla solicitudes_stock NO existe en base central</p>";
        exit;
    }

    // ✅ 5. GENERAR NÚMERO DE SOLICITUD
    echo "<h3>5. Generar Número de Solicitud</h3>";
    $numeroSolicitud = ModeloSolicitudesStock::mdlGenerarNumeroSolicitud("solicitudes_stock");
    
    if($numeroSolicitud) {
        echo "<p class='success'>✅ Número generado: <strong>{$numeroSolicitud}</strong></p>";
    } else {
        echo "<p class='error'>❌ Error generando número de solicitud</p>";
        exit;
    }

    // ✅ 6. PREPARAR DATOS DE PRUEBA
    echo "<h3>6. Preparar Datos de Prueba</h3>";
    $productosTest = [
        [
            "id" => 1,
            "codigo" => "TEST001",
            "descripcion" => "Producto de prueba - " . date('Y-m-d H:i:s'),
            "cantidad" => 3,
            "observacion" => "Prueba desde debug completo"
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
        "detalle_adicional" => "Solicitud de prueba desde debug - " . date('Y-m-d H:i:s'),
        "total_productos" => count($productosTest),
        "total_cantidad" => array_sum(array_column($productosTest, 'cantidad'))
    );

    echo "<p class='success'>✅ Datos preparados correctamente</p>";
    echo "<details><summary>👁️ Ver datos preparados</summary>";
    echo "<pre style='background:#f5f5f5; padding:10px; border-radius:5px;'>" . json_encode($datos, JSON_PRETTY_PRINT) . "</pre>";
    echo "</details>";

    // ✅ 7. EJECUTAR SQL MANUALMENTE PARA VERIFICAR
    echo "<h3>7. Intentar INSERT Manual (Paso a Paso)</h3>";
    
    $sql = "INSERT INTO solicitudes_stock(
        numero_solicitud, 
        codigo_sucursal_solicitante, 
        nombre_sucursal_solicitante, 
        usuario_solicitante, 
        nombre_usuario_solicitante, 
        productos_solicitados, 
        tipo_solicitud, 
        codigo_remision, 
        nombre_cliente_remision, 
        detalle_adicional, 
        total_productos, 
        total_cantidad
    ) VALUES (
        :numero_solicitud, 
        :codigo_sucursal_solicitante, 
        :nombre_sucursal_solicitante, 
        :usuario_solicitante, 
        :nombre_usuario_solicitante, 
        :productos_solicitados, 
        :tipo_solicitud, 
        :codigo_remision, 
        :nombre_cliente_remision, 
        :detalle_adicional, 
        :total_productos, 
        :total_cantidad
    )";
    
    echo "<details><summary>👁️ Ver SQL que se ejecutará</summary>";
    echo "<pre style='background:#f0f8ff; padding:10px; border-radius:5px;'>{$sql}</pre>";
    echo "</details>";

    $stmt = $conexionCentral->prepare($sql);
    
    // Bind parameters
    $stmt->bindParam(":numero_solicitud", $datos["numero_solicitud"], PDO::PARAM_STR);
    $stmt->bindParam(":codigo_sucursal_solicitante", $datos["codigo_sucursal_solicitante"], PDO::PARAM_STR);
    $stmt->bindParam(":nombre_sucursal_solicitante", $datos["nombre_sucursal_solicitante"], PDO::PARAM_STR);
    $stmt->bindParam(":usuario_solicitante", $datos["usuario_solicitante"], PDO::PARAM_INT);
    $stmt->bindParam(":nombre_usuario_solicitante", $datos["nombre_usuario_solicitante"], PDO::PARAM_STR);
    $stmt->bindParam(":productos_solicitados", $datos["productos_solicitados"], PDO::PARAM_STR);
    $stmt->bindParam(":tipo_solicitud", $datos["tipo_solicitud"], PDO::PARAM_STR);
    
    $codigo_remision = $datos["codigo_remision"];
    $nombre_cliente_remision = $datos["nombre_cliente_remision"];
    $detalle_adicional = $datos["detalle_adicional"];
    
    $stmt->bindParam(":codigo_remision", $codigo_remision, PDO::PARAM_STR);
    $stmt->bindParam(":nombre_cliente_remision", $nombre_cliente_remision, PDO::PARAM_STR);
    $stmt->bindParam(":detalle_adicional", $detalle_adicional, PDO::PARAM_STR);
    $stmt->bindParam(":total_productos", $datos["total_productos"], PDO::PARAM_INT);
    $stmt->bindParam(":total_cantidad", $datos["total_cantidad"], PDO::PARAM_INT);

    echo "<p class='info'>🔄 Ejecutando INSERT...</p>";
    
    $resultado = $stmt->execute();
    
    if($resultado) {
        $insertId = $conexionCentral->lastInsertId();
        echo "<p class='success'>✅ <strong>INSERT EXITOSO</strong> - ID asignado: {$insertId}</p>";
        
        // ✅ 8. VERIFICAR QUE SE GUARDÓ
        echo "<h3>8. Verificar Registro Guardado</h3>";
        $stmt = $conexionCentral->prepare("SELECT * FROM solicitudes_stock WHERE id = :id");
        $stmt->bindParam(":id", $insertId);
        $stmt->execute();
        $solicitudGuardada = $stmt->fetch();
        
        if($solicitudGuardada) {
            echo "<p class='success'>✅ <strong>CONFIRMADO</strong> - Registro encontrado:</p>";
            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
            echo "<tr style='background: #f0f8ff;'>";
            foreach($solicitudGuardada as $campo => $valor) {
                if(!is_numeric($campo)) {
                    echo "<th style='padding: 8px; border: 1px solid #ddd;'>{$campo}</th>";
                }
            }
            echo "</tr>";
            echo "<tr>";
            foreach($solicitudGuardada as $campo => $valor) {
                if(!is_numeric($campo)) {
                    $valorMostrar = strlen($valor) > 50 ? substr($valor, 0, 50) . '...' : $valor;
                    echo "<td style='padding: 8px; border: 1px solid #ddd;'>{$valorMostrar}</td>";
                }
            }
            echo "</tr>";
            echo "</table>";
            
            // ✅ ELIMINAR REGISTRO DE PRUEBA
            echo "<p class='info'>🗑️ Eliminando registro de prueba...</p>";
            $stmtDelete = $conexionCentral->prepare("DELETE FROM solicitudes_stock WHERE id = :id");
            $stmtDelete->bindParam(":id", $insertId);
            $stmtDelete->execute();
            echo "<p class='success'>✅ Registro de prueba eliminado</p>";
            
        } else {
            echo "<p class='error'>❌ ERROR: Registro NO encontrado después del INSERT</p>";
        }
        
    } else {
        echo "<p class='error'>❌ <strong>ERROR EN INSERT</strong></p>";
        $errorInfo = $stmt->errorInfo();
        echo "<pre style='background:#ffe4e1; padding:10px; border-radius:5px;'>";
        echo "SQLSTATE: " . $errorInfo[0] . "\n";
        echo "Error Code: " . $errorInfo[1] . "\n";
        echo "Error Message: " . $errorInfo[2] . "\n";
        echo "</pre>";
    }

    // ✅ 9. USAR EL MODELO ORIGINAL
    echo "<h3>9. Probar con Modelo Original</h3>";
    echo "<p class='info'>🔄 Ejecutando ModeloSolicitudesStock::mdlCrearSolicitud()...</p>";
    
    $respuestaModelo = ModeloSolicitudesStock::mdlCrearSolicitud("solicitudes_stock", $datos);
    echo "<p><strong>Respuesta del modelo:</strong> <code>{$respuestaModelo}</code></p>";
    
    if($respuestaModelo == "ok") {
        echo "<p class='success'>✅ <strong>MODELO FUNCIONANDO CORRECTAMENTE</strong></p>";
    } else {
        echo "<p class='error'>❌ <strong>PROBLEMA EN EL MODELO:</strong> {$respuestaModelo}</p>";
    }

} catch(Exception $e) {
    echo "<p class='error'>❌ <strong>EXCEPCIÓN:</strong> " . $e->getMessage() . "</p>";
    echo "<pre style='background:#ffe4e1; padding:10px;'>" . $e->getTraceAsString() . "</pre>";
}

echo "<hr>";
echo "<h3>🎯 CONCLUSIONES</h3>";
echo "<ul>";
echo "<li>Si el <strong>INSERT manual</strong> funciona ✅ pero el <strong>modelo</strong> falla ❌, hay un problema en el código del modelo</li>";
echo "<li>Si ambos fallan ❌, hay un problema de permisos o estructura de tabla</li>";
echo "<li>Si ambos funcionan ✅, el problema está en el controlador o en el envío desde JavaScript</li>";
echo "</ul>";

echo "<p><a href='crear-solicitud-stock' style='background: #007cba; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;'>← Volver a crear solicitud</a></p>";
?>