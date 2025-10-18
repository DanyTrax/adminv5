<?php
/**
 * TEST DE MÚLTIPLES CONEXIONES A BASE DE DATOS
 * Prueba diferentes combinaciones de credenciales
 */

echo "<h2>🔍 Test de Múltiples Conexiones a Base de Datos</h2>";

// Diferentes combinaciones de credenciales a probar
$configuraciones = [
    [
        'nombre' => 'epicosie_central (original)',
        'host' => 'localhost',
        'dbname' => 'epicosie_central',
        'user' => 'epicosie_central',
        'pass' => '=Nf?M#6A\'QU&.6c'
    ],
    [
        'nombre' => 'epicosie_pruebas (local)',
        'host' => 'localhost',
        'dbname' => 'epicosie_pruebas',
        'user' => 'epicosie_ricaurte',
        'pass' => 'm5Wwg)~M{i~*kFr{'
    ],
    [
        'nombre' => 'epicosie_central con usuario local',
        'host' => 'localhost',
        'dbname' => 'epicosie_central',
        'user' => 'epicosie_ricaurte',
        'pass' => 'm5Wwg)~M{i~*kFr{'
    ],
    [
        'nombre' => 'epicosie_pruebas con usuario central',
        'host' => 'localhost',
        'dbname' => 'epicosie_pruebas',
        'user' => 'epicosie_central',
        'pass' => '=Nf?M#6A\'QU&.6c'
    ]
];

function probarConexion($config) {
    echo "<h3>🔌 Probando: {$config['nombre']}</h3>";
    
    try {
        $pdo = new PDO(
            "mysql:host={$config['host']};dbname={$config['dbname']};charset=utf8mb4",
            $config['user'],
            $config['pass'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        
        echo "<p>✅ Conexión exitosa</p>";
        
        // Listar todas las tablas
        $stmt = $pdo->query("SHOW TABLES");
        $tablas = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo "<p>📋 Tablas encontradas (" . count($tablas) . "):</p>";
        echo "<ul>";
        foreach ($tablas as $tabla) {
            echo "<li>$tabla</li>";
        }
        echo "</ul>";
        
        // Verificar si existe la tabla despachos
        if (in_array('despachos', $tablas)) {
            echo "<p>✅ <strong>Tabla 'despachos' ENCONTRADA</strong></p>";
            
            // Contar registros
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM despachos");
            $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            echo "<p>📊 Registros en despachos: $total</p>";
            
            // Mostrar estructura
            $stmt = $pdo->query("DESCRIBE despachos");
            $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo "<p>📝 Columnas en despachos:</p>";
            echo "<ul>";
            foreach ($columnas as $columna) {
                echo "<li>{$columna['Field']} ({$columna['Type']})</li>";
            }
            echo "</ul>";
            
            return true; // Encontramos la tabla despachos
        } else {
            echo "<p>❌ Tabla 'despachos' NO encontrada</p>";
            return false;
        }
        
    } catch (Exception $e) {
        echo "<p>❌ Error de conexión: " . $e->getMessage() . "</p>";
        return false;
    }
}

echo "<hr>";

$despachosEncontrada = false;
foreach ($configuraciones as $config) {
    if (probarConexion($config)) {
        $despachosEncontrada = true;
    }
    echo "<hr>";
}

if ($despachosEncontrada) {
    echo "<h2>✅ ¡Tabla 'despachos' encontrada!</h2>";
    echo "<p>Ahora sabemos qué credenciales usar para la conexión central.</p>";
} else {
    echo "<h2>❌ Tabla 'despachos' no encontrada en ninguna configuración</h2>";
    echo "<p>Es posible que la tabla no exista o tenga un nombre diferente.</p>";
}

echo "<hr>";
echo "<h3>🔍 Información adicional</h3>";
echo "<p><strong>Servidor:</strong> " . $_SERVER['SERVER_NAME'] . "</p>";
echo "<p><strong>Directorio:</strong> " . __DIR__ . "</p>";
echo "<p><strong>Fecha:</strong> " . date('Y-m-d H:i:s') . "</p>";
?>
