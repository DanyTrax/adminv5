<?php
/**
 * TEST DE TODAS LAS CREDENCIALES POSIBLES
 * Encuentra la combinación correcta que coincida con phpMyAdmin
 */

echo "<h2>🔍 Test de Todas las Credenciales Posibles</h2>";

// Lista de todas las combinaciones posibles basadas en lo que hemos visto
$configuraciones = [
    // Configuraciones originales
    [
        'nombre' => 'epicosie_central + epicosie_central (original)',
        'host' => 'localhost',
        'dbname' => 'epicosie_central',
        'user' => 'epicosie_central',
        'pass' => '=Nf?M#6A\'QU&.6c'
    ],
    [
        'nombre' => 'epicosie_pruebas + epicosie_ricaurte (local)',
        'host' => 'localhost',
        'dbname' => 'epicosie_pruebas',
        'user' => 'epicosie_ricaurte',
        'pass' => 'm5Wwg)~M{i~*kFr{'
    ],
    // Posibles variaciones
    [
        'nombre' => 'epicosie_central + epicosie_ricaurte',
        'host' => 'localhost',
        'dbname' => 'epicosie_central',
        'user' => 'epicosie_ricaurte',
        'pass' => 'm5Wwg)~M{i~*kFr{'
    ],
    [
        'nombre' => 'epicosie_pruebas + epicosie_central',
        'host' => 'localhost',
        'dbname' => 'epicosie_pruebas',
        'user' => 'epicosie_central',
        'pass' => '=Nf?M#6A\'QU&.6c'
    ],
    // Posibles nombres de base de datos diferentes
    [
        'nombre' => 'epicosie_central + epicosie_central (sin guión)',
        'host' => 'localhost',
        'dbname' => 'epicosiecentral',
        'user' => 'epicosie_central',
        'pass' => '=Nf?M#6A\'QU&.6c'
    ],
    [
        'nombre' => 'epicosie_central + epicosie_central (con prefijo)',
        'host' => 'localhost',
        'dbname' => 'epicosie_epicosie_central',
        'user' => 'epicosie_central',
        'pass' => '=Nf?M#6A\'QU&.6c'
    ],
    // Posibles usuarios diferentes
    [
        'nombre' => 'epicosie_central + epicosie (usuario corto)',
        'host' => 'localhost',
        'dbname' => 'epicosie_central',
        'user' => 'epicosie',
        'pass' => '=Nf?M#6A\'QU&.6c'
    ],
    [
        'nombre' => 'epicosie_central + epicosie (usuario corto + pass local)',
        'host' => 'localhost',
        'dbname' => 'epicosie_central',
        'user' => 'epicosie',
        'pass' => 'm5Wwg)~M{i~*kFr{'
    ]
];

function probarConexionCompleta($config) {
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
        
        // Verificar tablas específicas
        $tablasImportantes = ['despachos', 'stock_transito', 'solicitudes_stock', 'catalogo_maestro'];
        $tablasEncontradas = [];
        
        foreach ($tablasImportantes as $tabla) {
            if (in_array($tabla, $tablas)) {
                $tablasEncontradas[] = $tabla;
                
                // Contar registros
                $stmt = $pdo->query("SELECT COUNT(*) as total FROM `$tabla`");
                $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
                echo "<p>✅ <strong>$tabla</strong>: $total registros</p>";
            } else {
                echo "<p>❌ <strong>$tabla</strong>: NO encontrada</p>";
            }
        }
        
        // Si encontramos las tablas importantes, esta es probablemente la configuración correcta
        if (count($tablasEncontradas) >= 3) {
            echo "<p style='color: green; font-weight: bold;'>🎯 ¡CONFIGURACIÓN CORRECTA ENCONTRADA!</p>";
            echo "<p>Esta configuración tiene las tablas necesarias para el sistema.</p>";
            return $config;
        }
        
        return null;
        
    } catch (Exception $e) {
        echo "<p>❌ Error de conexión: " . $e->getMessage() . "</p>";
        return null;
    }
}

echo "<hr>";

$configuracionCorrecta = null;
foreach ($configuraciones as $config) {
    $resultado = probarConexionCompleta($config);
    if ($resultado) {
        $configuracionCorrecta = $resultado;
        break; // Encontramos la correcta, no necesitamos probar más
    }
    echo "<hr>";
}

if ($configuracionCorrecta) {
    echo "<h2>🎯 CONFIGURACIÓN CORRECTA ENCONTRADA</h2>";
    echo "<p>La configuración correcta es:</p>";
    echo "<ul>";
    echo "<li><strong>Host:</strong> " . $configuracionCorrecta['host'] . "</li>";
    echo "<li><strong>Base de datos:</strong> " . $configuracionCorrecta['dbname'] . "</li>";
    echo "<li><strong>Usuario:</strong> " . $configuracionCorrecta['user'] . "</li>";
    echo "<li><strong>Contraseña:</strong> " . $configuracionCorrecta['pass'] . "</li>";
    echo "</ul>";
    echo "<p>Ahora podemos actualizar el archivo conexion-central.php con estos datos.</p>";
} else {
    echo "<h2>❌ No se encontró la configuración correcta</h2>";
    echo "<p>Es posible que necesitemos verificar las credenciales en cPanel.</p>";
}

echo "<hr>";
echo "<p><strong>Fecha:</strong> " . date('Y-m-d H:i:s') . "</p>";
?>
