<?php
/*=============================================
VERIFICAR CONEXIÓN CENTRAL EN TODAS LAS SUCURSALES
=============================================*/

echo "🔍 VERIFICANDO CONEXIÓN CENTRAL EN SUCURSALES\n";
echo "===============================================\n\n";

// Incluir conexión central
require_once "api-transferencias/conexion-central.php";

try {
    $conexionCentral = ConexionCentral::conectar();
    
    // Obtener todas las sucursales activas
    $stmt = $conexionCentral->prepare("
        SELECT id, nombre, host_bd, usuario_bd, password_bd, nombre_bd, puerto_bd 
        FROM sucursales 
        WHERE activo = 1
    ");
    $stmt->execute();
    $sucursales = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "📊 Sucursales encontradas: " . count($sucursales) . "\n\n";
    
    foreach($sucursales as $sucursal) {
        echo "🏢 Sucursal: {$sucursal['nombre']} (ID: {$sucursal['id']})\n";
        echo "   Host: {$sucursal['host_bd']}\n";
        echo "   BD: {$sucursal['nombre_bd']}\n";
        
        try {
            // Intentar conectar a la sucursal
            $dsn = "mysql:host={$sucursal['host_bd']};dbname={$sucursal['nombre_bd']};charset=utf8mb4";
            $conexionLocal = new PDO($dsn, $sucursal['usuario_bd'], $sucursal['password_bd']);
            $conexionLocal->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            echo "   ✅ Conexión exitosa\n";
            
            // Verificar si existe el archivo conexion-central.php
            $rutaArchivo = "/home/epicosie/{$sucursal['nombre_bd']}.acrilicosinfinito.com/api-transferencias/conexion-central.php";
            
            if (file_exists($rutaArchivo)) {
                echo "   ✅ Archivo conexion-central.php existe\n";
                
                // Verificar contenido del archivo
                $contenido = file_get_contents($rutaArchivo);
                if (strpos($contenido, 'ConexionCentral') !== false) {
                    echo "   ✅ Archivo conexion-central.php válido\n";
                } else {
                    echo "   ⚠️  Archivo conexion-central.php corrupto\n";
                }
            } else {
                echo "   ❌ Archivo conexion-central.php NO existe\n";
                echo "   🔧 Creando archivo...\n";
                
                // Crear directorio si no existe
                $directorio = dirname($rutaArchivo);
                if (!is_dir($directorio)) {
                    mkdir($directorio, 0755, true);
                    echo "   📁 Directorio creado: $directorio\n";
                }
                
                // Crear archivo conexion-central.php
                $contenidoArchivo = '<?php
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
                
                if (file_put_contents($rutaArchivo, $contenidoArchivo)) {
                    echo "   ✅ Archivo conexion-central.php creado\n";
                } else {
                    echo "   ❌ Error creando archivo conexion-central.php\n";
                }
            }
            
        } catch (Exception $e) {
            echo "   ❌ Error de conexión: " . $e->getMessage() . "\n";
        }
        
        echo "\n";
    }
    
    echo "✅ Verificación completada\n";
    
} catch (Exception $e) {
    echo "❌ Error general: " . $e->getMessage() . "\n";
}

?>
