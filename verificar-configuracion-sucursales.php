<?php
/**
 * SCRIPT PARA VERIFICAR CONFIGURACIÓN DE SUCURSALES
 * Verifica que las sucursales tengan los datos de conexión correctos
 */

echo "=== VERIFICAR CONFIGURACIÓN DE SUCURSALES ===\n";
echo "Fecha: " . date('Y-m-d H:i:s') . "\n\n";

// Configuración BD Central
$hostCentral = "localhost";
$dbnameCentral = "epicosie_central";
$usernameCentral = "epicosie_central";
$passwordCentral = "=Nf?M#6A'QU&.6c";

try {
    echo "Conectando a BD Central...\n";
    $pdoCentral = new PDO("mysql:host=$hostCentral;dbname=$dbnameCentral;charset=utf8mb4", $usernameCentral, $passwordCentral);
    $pdoCentral->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdoCentral->exec("set names utf8");
    echo "✅ Conexión BD Central exitosa\n\n";
    
    // Verificar estructura de tabla sucursales
    echo "=== ESTRUCTURA DE TABLA SUCURSALES ===\n";
    $stmt = $pdoCentral->prepare("DESCRIBE sucursales");
    $stmt->execute();
    $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Columnas en tabla sucursales:\n";
    foreach ($columnas as $columna) {
        echo "   - {$columna['Field']}: {$columna['Type']} " . 
             ($columna['Null'] === 'YES' ? "NULL" : "NOT NULL") . 
             ($columna['Key'] ? " ({$columna['Key']})" : "") . "\n";
    }
    echo "\n";
    
    // Verificar sucursales existentes
    echo "=== SUCURSALES EXISTENTES ===\n";
    $stmt = $pdoCentral->prepare("SELECT * FROM sucursales ORDER BY id");
    $stmt->execute();
    $sucursales = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Sucursales encontradas: " . count($sucursales) . "\n\n";
    
    foreach ($sucursales as $sucursal) {
        echo "Sucursal: {$sucursal['nombre']} (ID: {$sucursal['id']})\n";
        echo "   - Activa: " . ($sucursal['activo'] ? "✅ Sí" : "❌ No") . "\n";
        echo "   - Host BD: " . ($sucursal['host_bd'] ?? "❌ NULL") . "\n";
        echo "   - Puerto BD: " . ($sucursal['puerto_bd'] ?? "❌ NULL") . "\n";
        echo "   - Nombre BD: " . ($sucursal['nombre_bd'] ?? "❌ NULL") . "\n";
        echo "   - Usuario BD: " . ($sucursal['usuario_bd'] ?? "❌ NULL") . "\n";
        echo "   - Password BD: " . (empty($sucursal['password_bd']) ? "❌ Vacía" : "✅ Configurada") . "\n";
        echo "\n";
    }
    
    // Verificar si hay sucursales sin datos de conexión
    echo "=== SUCURSALES SIN DATOS DE CONEXIÓN ===\n";
    $stmt = $pdoCentral->prepare("
        SELECT id, nombre 
        FROM sucursales 
        WHERE activo = 1 
        AND (host_bd IS NULL OR host_bd = '' OR 
             puerto_bd IS NULL OR puerto_bd = '' OR 
             nombre_bd IS NULL OR nombre_bd = '' OR 
             usuario_bd IS NULL OR usuario_bd = '' OR 
             password_bd IS NULL OR password_bd = '')
    ");
    $stmt->execute();
    $sucursalesIncompletas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($sucursalesIncompletas)) {
        echo "✅ Todas las sucursales activas tienen datos de conexión completos\n";
    } else {
        echo "❌ Sucursales con datos de conexión incompletos:\n";
        foreach ($sucursalesIncompletas as $sucursal) {
            echo "   - {$sucursal['nombre']} (ID: {$sucursal['id']})\n";
        }
        echo "\n⚠️  Estas sucursales no se pueden sincronizar hasta completar los datos\n";
    }
    
    echo "\n=== INSTRUCCIONES ===\n";
    echo "1. Si hay sucursales sin datos de conexión:\n";
    echo "   - Ir a 'Sucursales' en el sistema\n";
    echo "   - Editar cada sucursal y completar los datos de BD\n";
    echo "   - Guardar los cambios\n";
    echo "\n2. Datos necesarios para cada sucursal:\n";
    echo "   - Host BD: localhost (o IP del servidor)\n";
    echo "   - Puerto BD: 3306 (puerto por defecto de MySQL)\n";
    echo "   - Nombre BD: nombre de la base de datos\n";
    echo "   - Usuario BD: usuario con permisos\n";
    echo "   - Password BD: contraseña del usuario\n";
    
    echo "\n✅ VERIFICACIÓN COMPLETADA\n";
    
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
}
?>
