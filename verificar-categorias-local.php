<?php
// Script para verificar las categorías en la BD local después de la sincronización
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔍 Verificar Categorías en BD Local</h1>";

try {
    // Conexión a la BD local
    require_once "config.php";
    require_once "modelos/conexion.php";
    $pdo = Conexion::conectar();
    
    echo "<p>✅ Conexión exitosa a la BD local</p>";
    
    // Obtener todas las categorías de la BD local
    $stmt = $pdo->prepare("SELECT * FROM categorias ORDER BY categoria");
    $stmt->execute();
    $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($categorias) {
        echo "<h2>📋 Categorías en BD Local (" . count($categorias) . " encontradas):</h2>";
        echo "<table border='1' cellpadding='8' cellspacing='0' style='border-collapse: collapse;'>";
        echo "<tr style='background: #f0f0f0;'><th>ID</th><th>Categoría</th><th>Fecha</th></tr>";
        
        foreach ($categorias as $cat) {
            echo "<tr>";
            echo "<td>" . $cat['id'] . "</td>";
            echo "<td><strong>" . htmlspecialchars($cat['categoria']) . "</strong></td>";
            echo "<td>" . $cat['fecha'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Verificar si las categorías activas de la central están presentes
        echo "<h2>🔍 Verificación de Sincronización:</h2>";
        
        // Categorías que deberían estar (las activas de la central)
        $categorias_esperadas = [
            'ACCESORIOS',
            'BISAGRAS EN ACRILICO', 
            'ILUMINACION LED',
            'INSUMOS',
            'LAMINAS DE ACRILICO',
            'LAMINAS DE POLIESTIRENO',
            'NEONFLEX',
            'TUBOS EN ACRILICO',
            'VARILLAS EN ACRILICO'
        ];
        
        $categorias_encontradas = [];
        foreach ($categorias as $cat) {
            $categorias_encontradas[] = $cat['categoria'];
        }
        
        echo "<h3>✅ Categorías sincronizadas correctamente:</h3>";
        $sincronizadas = 0;
        foreach ($categorias_esperadas as $esperada) {
            if (in_array($esperada, $categorias_encontradas)) {
                echo "<p style='color: green;'>✅ $esperada</p>";
                $sincronizadas++;
            } else {
                echo "<p style='color: red;'>❌ $esperada (FALTA)</p>";
            }
        }
        
        echo "<h3>📊 Resumen:</h3>";
        echo "<p><strong>Categorías esperadas:</strong> " . count($categorias_esperadas) . "</p>";
        echo "<p><strong>Categorías encontradas:</strong> $sincronizadas</p>";
        echo "<p><strong>Total en BD local:</strong> " . count($categorias) . "</p>";
        
        if ($sincronizadas == count($categorias_esperadas)) {
            echo "<p style='color: green; font-weight: bold;'>🎉 ¡SINCRONIZACIÓN COMPLETA!</p>";
        } else {
            echo "<p style='color: orange; font-weight: bold;'>⚠️ Sincronización parcial o incompleta</p>";
        }
        
    } else {
        echo "<p style='color: red;'>❌ No se encontraron categorías en la BD local</p>";
    }
    
} catch (Exception $e) {
    echo "<h2>❌ Error:</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
}

echo "<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h1, h2, h3 { color: #333; }
table { margin: 10px 0; }
th { background: #007bff; color: white; }
</style>";
?>
