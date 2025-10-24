<?php
/*=============================================
PROBAR REGISTRO DE DESCARGA
=============================================*/

// Incluir archivos necesarios
require_once "modelos/conexion.php";
require_once "modelos/registro-descargas-simple.modelo.php";
require_once "controladores/registro-descargas-simple.controlador.php";

echo "<h2>🧪 Probar Registro de Descarga</h2>";

try {
    // 1. Verificar conexión
    echo "<h3>1. Verificar Conexión</h3>";
    $conexion = Conexion::conectar();
    echo "✅ Conexión exitosa<br>";
    
    // 2. Simular datos de descarga
    echo "<h3>2. Simular Datos de Descarga</h3>";
    $datos = array(
        "codigo_producto" => "TEST001",
        "descripcion_producto" => "Producto de prueba",
        "cantidad_descargada" => 5,
        "usuario_id" => 1,
        "usuario_nombre" => "Usuario Prueba",
        "sucursal_id" => 1,
        "sucursal_nombre" => "Local Pruebas",
        "transportador_id" => 1,
        "transportador_nombre" => "Transportador Prueba",
        "numero_despacho" => "DESP001",
        "observaciones" => "Prueba de registro"
    );
    
    echo "📋 Datos a insertar:<br>";
    echo "<pre>";
    print_r($datos);
    echo "</pre>";
    
    // 3. Probar inserción directa
    echo "<h3>3. Probar Inserción Directa</h3>";
    $stmt = $conexion->prepare("INSERT INTO registro_descargas_stock_transito (
        codigo_producto, 
        descripcion_producto, 
        cantidad_descargada, 
        usuario_id, 
        usuario_nombre, 
        sucursal_id, 
        sucursal_nombre, 
        transportador_id, 
        transportador_nombre, 
        numero_despacho, 
        observaciones, 
        fecha_descarga, 
        ip_usuario, 
        user_agent, 
        created_at
    ) VALUES (
        :codigo_producto, 
        :descripcion_producto, 
        :cantidad_descargada, 
        :usuario_id, 
        :usuario_nombre, 
        :sucursal_id, 
        :sucursal_nombre, 
        :transportador_id, 
        :transportador_nombre, 
        :numero_despacho, 
        :observaciones, 
        :fecha_descarga, 
        :ip_usuario, 
        :user_agent, 
        :created_at
    )");
    
    $stmt->bindParam(":codigo_producto", $datos["codigo_producto"], PDO::PARAM_STR);
    $stmt->bindParam(":descripcion_producto", $datos["descripcion_producto"], PDO::PARAM_STR);
    $stmt->bindParam(":cantidad_descargada", $datos["cantidad_descargada"], PDO::PARAM_INT);
    $stmt->bindParam(":usuario_id", $datos["usuario_id"], PDO::PARAM_INT);
    $stmt->bindParam(":usuario_nombre", $datos["usuario_nombre"], PDO::PARAM_STR);
    $stmt->bindParam(":sucursal_id", $datos["sucursal_id"], PDO::PARAM_INT);
    $stmt->bindParam(":sucursal_nombre", $datos["sucursal_nombre"], PDO::PARAM_STR);
    $stmt->bindParam(":transportador_id", $datos["transportador_id"], PDO::PARAM_INT);
    $stmt->bindParam(":transportador_nombre", $datos["transportador_nombre"], PDO::PARAM_STR);
    $stmt->bindParam(":numero_despacho", $datos["numero_despacho"], PDO::PARAM_STR);
    $stmt->bindParam(":observaciones", $datos["observaciones"], PDO::PARAM_STR);
    $stmt->bindParam(":fecha_descarga", date('Y-m-d H:i:s'), PDO::PARAM_STR);
    $stmt->bindParam(":ip_usuario", $_SERVER['REMOTE_ADDR'], PDO::PARAM_STR);
    $stmt->bindParam(":user_agent", $_SERVER['HTTP_USER_AGENT'], PDO::PARAM_STR);
    $stmt->bindParam(":created_at", date('Y-m-d H:i:s'), PDO::PARAM_STR);
    
    if($stmt->execute()) {
        echo "✅ Inserción directa exitosa<br>";
        $ultimoId = $conexion->lastInsertId();
        echo "🆔 Último ID insertado: $ultimoId<br>";
    } else {
        echo "❌ Error en inserción directa<br>";
        $errorInfo = $stmt->errorInfo();
        echo "Error: " . $errorInfo[2] . "<br>";
    }
    
    // 4. Verificar registro insertado
    echo "<h3>4. Verificar Registro Insertado</h3>";
    $stmt = $conexion->prepare("SELECT COUNT(*) as total FROM registro_descargas_stock_transito");
    $stmt->execute();
    $total = $stmt->fetch()['total'];
    echo "📊 Total de registros: $total<br>";
    
    if($total > 0) {
        $stmt = $conexion->prepare("SELECT * FROM registro_descargas_stock_transito ORDER BY id DESC LIMIT 1");
        $stmt->execute();
        $ultimoRegistro = $stmt->fetch();
        
        echo "<h4>Último registro:</h4>";
        echo "<pre>";
        print_r($ultimoRegistro);
        echo "</pre>";
    }
    
    // 5. Probar modelo
    echo "<h3>5. Probar Modelo</h3>";
    $datos2 = array(
        "codigo_producto" => "TEST002",
        "descripcion_producto" => "Producto de prueba modelo",
        "cantidad_descargada" => 3,
        "usuario_id" => 2,
        "usuario_nombre" => "Usuario Modelo",
        "sucursal_id" => 1,
        "sucursal_nombre" => "Local Pruebas",
        "transportador_id" => 2,
        "transportador_nombre" => "Transportador Modelo",
        "numero_despacho" => "DESP002",
        "observaciones" => "Prueba de modelo"
    );
    
    $resultado = ModeloRegistroDescargasSimple::mdlRegistrarDescarga($datos2);
    echo "📋 Resultado del modelo: <strong>$resultado</strong><br>";
    
    // 6. Verificar total final
    echo "<h3>6. Verificar Total Final</h3>";
    $stmt = $conexion->prepare("SELECT COUNT(*) as total FROM registro_descargas_stock_transito");
    $stmt->execute();
    $totalFinal = $stmt->fetch()['total'];
    echo "📊 Total final de registros: <strong>$totalFinal</strong><br>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    echo "📍 Archivo: " . $e->getFile() . "<br>";
    echo "📍 Línea: " . $e->getLine() . "<br>";
    echo "📍 Stack trace:<br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>
