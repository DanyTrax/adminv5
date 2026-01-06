<?php
/*=============================================
AGREGAR CAMPO nombre_sucursal A TABLA personalizacion_colores
=============================================*/

require_once __DIR__ . "/api-transferencias/conexion-central.php";

try {
    $conexion = ConexionCentral::conectar();
    
    echo "🔍 Verificando si el campo 'nombre_sucursal' existe...\n";
    
    // Verificar si la columna ya existe
    $stmt = $conexion->query("SHOW COLUMNS FROM personalizacion_colores LIKE 'nombre_sucursal'");
    $existe = $stmt->rowCount() > 0;
    
    if ($existe) {
        echo "✅ El campo 'nombre_sucursal' ya existe en la tabla personalizacion_colores.\n";
    } else {
        echo "📝 Agregando el campo 'nombre_sucursal' a la tabla personalizacion_colores...\n";
        
        // Agregar la columna nombre_sucursal después de id_sucursal
        $sql = "ALTER TABLE personalizacion_colores 
                ADD COLUMN nombre_sucursal VARCHAR(255) NULL AFTER id_sucursal";
        
        $conexion->exec($sql);
        
        echo "✅ Campo 'nombre_sucursal' agregado exitosamente.\n";
        
        // Actualizar registros existentes con el nombre de la sucursal
        echo "🔄 Actualizando registros existentes con nombres de sucursales...\n";
        
        $stmt = $conexion->query("SELECT id, id_sucursal FROM personalizacion_colores WHERE id_sucursal IS NOT NULL");
        $configuraciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($configuraciones as $config) {
            $stmt = $conexion->prepare("SELECT nombre FROM sucursales WHERE id = ?");
            $stmt->execute([$config['id_sucursal']]);
            $sucursal = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($sucursal) {
                $updateStmt = $conexion->prepare("UPDATE personalizacion_colores SET nombre_sucursal = ? WHERE id = ?");
                $updateStmt->execute([$sucursal['nombre'], $config['id']]);
            }
        }
        
        echo "✅ Registros actualizados exitosamente.\n";
    }
    
    echo "\n✅ Proceso completado.\n";
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>

