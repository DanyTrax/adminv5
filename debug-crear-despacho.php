<?php
session_start();

echo "<h2>🔍 Debug Crear Despacho</h2>";
echo "<p><strong>Usuario actual:</strong> " . ($_SESSION['nombre'] ?? 'No definido') . "</p>";
echo "<p><strong>Perfil:</strong> " . ($_SESSION['perfil'] ?? 'No definido') . "</p>";

// 1. Verificar POST data
if ($_POST) {
    echo "<h3>📥 Datos POST recibidos:</h3>";
    echo "<pre style='background: #f0f0f0; padding: 10px; border-radius: 5px;'>";
    print_r($_POST);
    echo "</pre>";
    
    // Intentar procesar como lo haría el archivo real
    if(isset($_POST["crearDespacho"])){
        echo "<h3>🚛 Procesando creación de despacho...</h3>";
        
        require_once "controladores/despachos.controlador.php";
        
        // Preparar datos para el controlador
        $datosDespacho = array(
            "id_solicitud_origen" => $_POST["idSolicitudOrigen"] ?? null,
            "nombre_sucursal_origen" => $_SESSION["sucursal"] ?? "Sucursal Local",
            "id_usuario_creador" => $_SESSION["id"],
            "nombre_usuario_creador" => $_SESSION["nombre"],
            "productos_despacho" => $_POST["productosDespacho"],
            "total_productos" => intval($_POST["totalProductos"]),
            "total_cantidad" => intval($_POST["totalCantidad"]),
            "detalle_adicional" => $_POST["detalleAdicional"] ?? ""
        );
        
        echo "<h4>📦 Datos preparados para controlador:</h4>";
        echo "<pre style='background: #e8f5e8; padding: 10px; border-radius: 5px;'>";
        print_r($datosDespacho);
        echo "</pre>";
        
        try {
            $resultado = ControladorDespachos::ctrCrearDespacho($datosDespacho);
            
            echo "<h4>📋 Resultado del controlador:</h4>";
            echo "<div style='background: " . ($resultado == "ok" ? "#d4edda" : "#f8d7da") . "; color: " . ($resultado == "ok" ? "#155724" : "#721c24") . "; padding: 10px; border-radius: 5px;'>";
            echo "<strong>" . ($resultado == "ok" ? "✅ ÉXITO" : "❌ ERROR") . ":</strong> " . $resultado;
            echo "</div>";
            
            if($resultado == "ok") {
                echo "<p style='color: green; font-weight: bold;'>🎉 Despacho creado exitosamente</p>";
            }
            
        } catch(Exception $e) {
            echo "<div style='background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px;'>";
            echo "<strong>❌ EXCEPCIÓN:</strong> " . $e->getMessage();
            echo "</div>";
        }
    }
} else {
    echo "<p>⚠️ No hay datos POST. Este archivo debe recibir datos del formulario de crear despacho.</p>";
}

// 2. Verificar archivos necesarios
echo "<h3>📁 Verificando archivos necesarios:</h3>";
$archivos = [
    "controladores/despachos.controlador.php",
    "modelos/despachos.modelo.php",
    "vistas/modulos/crear-despacho.php",
    "vistas/js/crear-despacho.js"
];

foreach($archivos as $archivo) {
    $existe = file_exists($archivo);
    echo "<p>" . ($existe ? "✅" : "❌") . " {$archivo}</p>";
}

// 3. Verificar función del controlador
echo "<h3>🔧 Verificando función del controlador:</h3>";
try {
    require_once "controladores/despachos.controlador.php";
    
    if (class_exists('ControladorDespachos')) {
        echo "<p>✅ Clase ControladorDespachos existe</p>";
        
        if (method_exists('ControladorDespachos', 'ctrCrearDespacho')) {
            echo "<p>✅ Método ctrCrearDespacho existe</p>";
        } else {
            echo "<p>❌ Método ctrCrearDespacho NO existe</p>";
        }
    } else {
        echo "<p>❌ Clase ControladorDespachos NO existe</p>";
    }
} catch(Exception $e) {
    echo "<p>❌ Error cargando controlador: " . $e->getMessage() . "</p>";
}

// 4. Formulario de prueba simple
echo "<hr>";
echo "<h3>🧪 Formulario de prueba:</h3>";
?>

<form method="POST" action="" style="background: #f8f9fa; padding: 20px; border-radius: 5px;">
    <input type="hidden" name="crearDespacho" value="1">
    
    <div style="margin-bottom: 10px;">
        <label><strong>Productos Despacho (JSON):</strong></label><br>
        <textarea name="productosDespacho" style="width: 100%; height: 100px;" placeholder='[{"codigo":"001","descripcion":"Producto Test","cantidad":5,"precio":1000}]'></textarea>
    </div>
    
    <div style="margin-bottom: 10px;">
        <label><strong>Total Productos:</strong></label>
        <input type="number" name="totalProductos" value="1" style="width: 100px;">
    </div>
    
    <div style="margin-bottom: 10px;">
        <label><strong>Total Cantidad:</strong></label>
        <input type="number" name="totalCantidad" value="5" style="width: 100px;">
    </div>
    
    <div style="margin-bottom: 10px;">
        <label><strong>Detalle Adicional:</strong></label><br>
        <textarea name="detalleAdicional" style="width: 100%; height: 60px;" placeholder="Observaciones..."></textarea>
    </div>
    
    <input type="hidden" name="idSolicitudOrigen" value="">
    
    <button type="submit" style="background: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">
        🧪 Probar Crear Despacho
    </button>
</form>

<?php
echo "<hr>";
echo "<h3>📋 Instrucciones:</h3>";
echo "<ol>";
echo "<li>Accede a este archivo desde el navegador</li>";
echo "<li>Llena el formulario de prueba con datos válidos</li>";
echo "<li>Haz clic en 'Probar Crear Despacho'</li>";
echo "<li>Revisa los resultados para identificar el problema</li>";
echo "</ol>";

// 5. Verificar error log
echo "<h3>📜 Últimos errores del log:</h3>";
$errorLog = "error_log";
if (file_exists($errorLog)) {
    $lines = file($errorLog);
    $recentLines = array_slice($lines, -10); // Últimas 10 líneas
    
    echo "<pre style='background: #fff3cd; padding: 10px; border-radius: 5px; max-height: 200px; overflow-y: auto;'>";
    foreach($recentLines as $line) {
        echo htmlspecialchars($line);
    }
    echo "</pre>";
} else {
    echo "<p>⚠️ No se encontró archivo error_log</p>";
}
?>