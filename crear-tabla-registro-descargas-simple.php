<?php
/*=============================================
CREAR TABLA DE REGISTRO DE DESCARGAS SIMPLE
=============================================*/

require_once "modelos/conexion.php";

try {
    // Conectar a la base de datos
    $conexion = Conexion::conectar();
    
    echo "🔧 Creando Tabla de Registro de Descargas Simple\n";
    echo "✅ Conexión a la base de datos establecida\n";
    
    // Verificar si la tabla ya existe
    $stmt = $conexion->prepare("SHOW TABLES LIKE 'registro_descargas_stock_transito'");
    $stmt->execute();
    $tablaExiste = $stmt->fetch();
    
    if ($tablaExiste) {
        echo "⚠️ La tabla 'registro_descargas_stock_transito' ya existe.\n";
        echo "¿Desea recrearla? (ADVERTENCIA: Esto eliminará todos los datos existentes)\n";
        echo "Para recrear, ejecute: DROP TABLE registro_descargas_stock_transito;\n";
    } else {
        // Leer y ejecutar el SQL
        $sql = file_get_contents("crear-tabla-registro-descargas-simple.sql");
        
        if ($sql === false) {
            throw new Exception("No se pudo leer el archivo SQL");
        }
        
        $conexion->exec($sql);
        
        echo "✅ Tabla 'registro_descargas_stock_transito' creada exitosamente\n";
        echo "🎉 ¡Sistema de Registro de Descargas Listo!\n\n";
        
        echo "📋 Campos de la Tabla:\n";
        echo "- Producto: Código, descripción, cantidad descargada\n";
        echo "- Usuario: ID y nombre del usuario que descargó\n";
        echo "- Sucursal: ID y nombre de la sucursal destino\n";
        echo "- Transportador: ID y nombre del transportador\n";
        echo "- Despacho: Número de despacho origen\n";
        echo "- Tiempo: Fecha y hora de descarga\n";
        echo "- Detalles: Observaciones, IP, User Agent\n\n";
        
        echo "🔍 Índices Creados:\n";
        echo "- Código de producto\n";
        echo "- Usuario de descarga\n";
        echo "- Sucursal destino\n";
        echo "- Fecha de descarga\n";
        echo "- Número de despacho\n\n";
        
        echo "Próximos pasos:\n";
        echo "1. Crear el modelo y controlador\n";
        echo "2. Crear la interfaz de usuario\n";
        echo "3. Integrar el hook de registro\n";
        echo "4. Agregar al menú principal\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
