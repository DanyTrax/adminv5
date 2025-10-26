<?php
/*=============================================
CREAR CONEXIÓN CENTRAL EN SUCURSAL ACTUAL
=============================================*/

echo "🔧 CREANDO CONEXIÓN CENTRAL EN SUCURSAL ACTUAL\n";
echo "===============================================\n\n";

// Verificar si el directorio existe
$directorio = __DIR__ . "/api-transferencias";
if (!is_dir($directorio)) {
    echo "📁 Creando directorio: $directorio\n";
    mkdir($directorio, 0755, true);
} else {
    echo "✅ Directorio existe: $directorio\n";
}

// Ruta del archivo
$archivo = $directorio . "/conexion-central.php";

// Contenido del archivo
$contenido = '<?php
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

// Crear el archivo
if (file_put_contents($archivo, $contenido)) {
    echo "✅ Archivo creado: $archivo\n";
    
    // Verificar que se puede usar
    try {
        require_once $archivo;
        $conexion = ConexionCentral::conectar();
        echo "✅ Conexión central funcionando\n";
        
        // Probar una consulta simple
        $stmt = $conexion->prepare("SELECT COUNT(*) as total FROM sucursales");
        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "✅ Consulta exitosa. Sucursales en central: " . $resultado['total'] . "\n";
        
    } catch (Exception $e) {
        echo "❌ Error probando conexión: " . $e->getMessage() . "\n";
    }
    
} else {
    echo "❌ Error creando archivo: $archivo\n";
}

echo "\n🎯 SCRIPT COMPLETADO\n";

?>
