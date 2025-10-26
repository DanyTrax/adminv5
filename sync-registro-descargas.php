<?php
/*=============================================
SYNC REGISTRO DESCARGAS - APLICAR EN TODAS LAS SUCURSALES
=============================================*/

echo "🔄 SINCRONIZANDO REGISTRO DE DESCARGAS\n";
echo "=====================================\n\n";

// 1. Crear directorio api-transferencias si no existe
$directorio = __DIR__ . "/api-transferencias";
if (!is_dir($directorio)) {
    echo "📁 Creando directorio: $directorio\n";
    mkdir($directorio, 0755, true);
} else {
    echo "✅ Directorio existe: $directorio\n";
}

// 2. Crear archivo conexion-central.php
$archivoConexion = $directorio . "/conexion-central.php";
$contenidoConexion = '<?php
/*=============================================
CONEXIÓN A BASE DE DATOS CENTRAL
=============================================*/

class ConexionCentral {

    static public function conectar() {
        
        $link = new PDO("mysql:host=localhost;dbname=epicosie_central",
                        "epicosie_ricaurte", 
                        "m5Wwg)~M{i~*kFr{");
        
        $link->exec("set names utf8");
        
        return $link;
        
    }
    
}
?>';

if (file_put_contents($archivoConexion, $contenidoConexion)) {
    echo "✅ Archivo conexion-central.php creado\n";
} else {
    echo "❌ Error creando conexion-central.php\n";
}

// 3. Verificar que los archivos necesarios existen
$archivosNecesarios = [
    "ajax/registro-descargas-simple.ajax.php",
    "controladores/registro-descargas-simple.controlador.php", 
    "modelos/registro-descargas-simple.modelo.php"
];

echo "\n📋 Verificando archivos necesarios:\n";
foreach($archivosNecesarios as $archivo) {
    $rutaCompleta = __DIR__ . "/" . $archivo;
    if (file_exists($rutaCompleta)) {
        echo "✅ $archivo\n";
    } else {
        echo "❌ $archivo - FALTANTE\n";
    }
}

// 4. Probar conexión central
echo "\n🔍 Probando conexión central:\n";
try {
    require_once $archivoConexion;
    $conexion = ConexionCentral::conectar();
    echo "✅ Conexión central exitosa\n";
    
    // Probar consulta
    $stmt = $conexion->prepare("SELECT COUNT(*) as total FROM registro_descargas_stock_transito");
    $stmt->execute();
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "✅ Tabla registro_descargas_stock_transito: " . $resultado['total'] . " registros\n";
    
} catch (Exception $e) {
    echo "❌ Error de conexión central: " . $e->getMessage() . "\n";
}

// 5. Probar registro de descarga
echo "\n🧪 Probando registro de descarga:\n";
try {
    require_once "controladores/registro-descargas-simple.controlador.php";
    
    // Simular datos POST
    $_POST["accion"] = "registrar_descarga";
    $_POST["codigo_producto"] = "TEST001";
    $_POST["cantidad_descargada"] = 1;
    $_POST["descripcion_producto"] = "Producto de prueba";
    $_POST["usuario_id"] = "999";
    $_POST["usuario_nombre"] = "Usuario Prueba";
    $_POST["sucursal_id"] = "999";
    $_POST["sucursal_nombre"] = "Sucursal Prueba";
    
    $controlador = new ControladorRegistroDescargasSimple();
    $resultado = $controlador->ctrRegistrarDescarga();
    
    if ($resultado["success"]) {
        echo "✅ Registro de descarga funcionando\n";
    } else {
        echo "❌ Error en registro: " . $resultado["error"] . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error probando registro: " . $e->getMessage() . "\n";
}

echo "\n🎯 SINCRONIZACIÓN COMPLETADA\n";
echo "============================\n";
echo "Si todo está ✅, el registro de descargas debería funcionar\n";
echo "Si hay ❌, revisa los errores mostrados arriba\n";

?>
