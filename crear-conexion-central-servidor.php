<?php
/*=============================================
CREAR CONEXIÓN CENTRAL EN EL SERVIDOR
=============================================*/

echo "🚀 Creando conexión central en el servidor\n";
echo "📋 Objetivo: Crear api-transferencias/conexion-central.php en el servidor\n\n";

// Contenido del archivo de conexión central
$contenidoConexion = '<?php

class ConexionCentral {
    static public function conectar(){

        // Base de datos CENTRAL para despachos y transferencias
        $servidor = "localhost";
        $nombreBD = "epicosie_central";
        $usuario = "epicosie_central";
        $password = "=Nf?M#6A\'QU&.6c";

        try {
            // CORRECCIÓN: Añadimos charset=utf8mb4 directamente a la línea de conexión.
            $link = new PDO(
                "mysql:host=$servidor;dbname=$nombreBD;charset=utf8mb4",
                $usuario,
                $password
            );

            // Habilitamos los errores de PDO para ver problemas
            $link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            return $link;

        } catch (PDOException $e) {
            // Manejar el error de conexión
            die("Error de conexión: " . $e->getMessage());
        }
    }
}';

// Crear directorio si no existe
$directorio = "api-transferencias";
if (!is_dir($directorio)) {
    if (mkdir($directorio, 0755, true)) {
        echo "✅ Directorio creado: $directorio\n";
    } else {
        echo "❌ ERROR: No se pudo crear el directorio $directorio\n";
        exit(1);
    }
} else {
    echo "✅ Directorio existe: $directorio\n";
}

// Crear archivo de conexión central
$archivo = "$directorio/conexion-central.php";
if (file_put_contents($archivo, $contenidoConexion)) {
    echo "✅ Archivo creado: $archivo\n";
} else {
    echo "❌ ERROR: No se pudo crear el archivo $archivo\n";
    exit(1);
}

// Verificar que el archivo se creó correctamente
if (file_exists($archivo)) {
    echo "✅ Archivo verificado: $archivo\n";
    
    // Verificar que la clase se puede cargar
    try {
        require_once $archivo;
        if (class_exists('ConexionCentral')) {
            echo "✅ Clase ConexionCentral cargada correctamente\n";
            
            // Probar conexión
            $conexion = ConexionCentral::conectar();
            if ($conexion) {
                echo "✅ Conexión a BD central establecida\n";
                
                // Verificar que la tabla existe
                $sql = "SHOW TABLES LIKE 'registro_descargas_stock_transito'";
                $stmt = $conexion->prepare($sql);
                $stmt->execute();
                $tabla = $stmt->fetch();
                
                if ($tabla) {
                    echo "✅ Tabla registro_descargas_stock_transito encontrada\n";
                    
                    // Contar registros
                    $sql = "SELECT COUNT(*) as total FROM registro_descargas_stock_transito";
                    $stmt = $conexion->prepare($sql);
                    $stmt->execute();
                    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    echo "📊 Total de registros: " . $resultado['total'] . "\n";
                } else {
                    echo "⚠️ Tabla registro_descargas_stock_transito no encontrada\n";
                }
            } else {
                echo "❌ ERROR: No se pudo establecer conexión a BD central\n";
            }
        } else {
            echo "❌ ERROR: Clase ConexionCentral no encontrada\n";
        }
    } catch (Exception $e) {
        echo "❌ ERROR: " . $e->getMessage() . "\n";
    }
} else {
    echo "❌ ERROR: El archivo no se creó correctamente\n";
    exit(1);
}

echo "\n🎉 ¡CONEXIÓN CENTRAL CREADA EXITOSAMENTE!\n";
echo "==========================================\n";
echo "✅ Archivo api-transferencias/conexion-central.php creado\n";
echo "✅ Conexión a BD central verificada\n";
echo "✅ Sistema listo para usar BD central\n\n";

echo "🧪 PRÓXIMOS PASOS:\n";
echo "1. Probar funcionalidad de registro de descargas\n";
echo "2. Verificar que el historial se carga correctamente\n";
echo "3. Confirmar que las descargas se registran en BD central\n\n";

echo "✅ Conexión central creada\n";
?>
