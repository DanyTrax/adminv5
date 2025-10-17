<?php
/**
 * GENERADOR DE INFORMES DE BASE DE DATOS
 * Muestra la estructura completa de las bases de datos local y central
 */

// Configuración de bases de datos
$config_local = [
    'host' => 'localhost',
    'dbname' => 'epicosie_pruebas',
    'user' => 'epicosie_ricaurte',
    'pass' => 'm5Wwg)~M{i~*kFr{'
];

$config_central = [
    'host' => 'localhost',
    'dbname' => 'epicosie_central',
    'user' => 'epicosie_central',
    'pass' => '=Nf?M#6A\'QU&.6c'
];

function conectarBD($config) {
    try {
        $pdo = new PDO(
            "mysql:host={$config['host']};dbname={$config['dbname']};charset=utf8mb4",
            $config['user'],
            $config['pass'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        return $pdo;
    } catch (PDOException $e) {
        return null;
    }
}

function obtenerInformeBD($pdo, $nombreBD) {
    if (!$pdo) {
        return "❌ ERROR: No se pudo conectar a la base de datos '$nombreBD'\n";
    }
    
    $informe = "📊 INFORMACIÓN DE BASE DE DATOS: $nombreBD\n";
    $informe .= str_repeat("=", 60) . "\n\n";
    
    try {
        // Obtener lista de tablas
        $stmt = $pdo->query("SHOW TABLES");
        $tablas = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (empty($tablas)) {
            $informe .= "⚠️  No hay tablas en esta base de datos\n\n";
            return $informe;
        }
        
        $informe .= "📋 TABLAS ENCONTRADAS (" . count($tablas) . "):\n";
        foreach ($tablas as $tabla) {
            $informe .= "   • $tabla\n";
        }
        $informe .= "\n";
        
        // Información detallada de cada tabla
        foreach ($tablas as $tabla) {
            $informe .= "🔍 TABLA: $tabla\n";
            $informe .= str_repeat("-", 40) . "\n";
            
            // Estructura de la tabla
            $stmt = $pdo->query("DESCRIBE `$tabla`");
            $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $informe .= "📝 COLUMNAS:\n";
            foreach ($columnas as $columna) {
                $informe .= sprintf(
                    "   %-20s %-15s %-5s %-5s %-10s %s\n",
                    $columna['Field'],
                    $columna['Type'],
                    $columna['Null'] === 'YES' ? 'NULL' : 'NOT NULL',
                    $columna['Key'],
                    $columna['Default'] ?? 'NULL',
                    $columna['Extra']
                );
            }
            
            // Contar registros
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM `$tabla`");
            $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            $informe .= "\n📊 REGISTROS: $total\n";
            
            // Índices
            $stmt = $pdo->query("SHOW INDEX FROM `$tabla`");
            $indices = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (!empty($indices)) {
                $informe .= "🔑 ÍNDICES:\n";
                $indices_unicos = [];
                foreach ($indices as $indice) {
                    $nombre_indice = $indice['Key_name'];
                    if (!isset($indices_unicos[$nombre_indice])) {
                        $indices_unicos[$nombre_indice] = [
                            'columnas' => [],
                            'tipo' => $indice['Non_unique'] ? 'INDEX' : 'UNIQUE'
                        ];
                    }
                    $indices_unicos[$nombre_indice]['columnas'][] = $indice['Column_name'];
                }
                
                foreach ($indices_unicos as $nombre => $info) {
                    $columnas_str = implode(', ', $info['columnas']);
                    $informe .= "   • $nombre ($info[tipo]): $columnas_str\n";
                }
            }
            
            $informe .= "\n" . str_repeat("=", 60) . "\n\n";
        }
        
    } catch (Exception $e) {
        $informe .= "❌ ERROR al obtener información: " . $e->getMessage() . "\n\n";
    }
    
    return $informe;
}

// Generar informe
echo "<pre>";
echo "🚀 GENERADOR DE INFORMES DE BASE DE DATOS\n";
echo "Fecha: " . date('Y-m-d H:i:s') . "\n";
echo str_repeat("=", 80) . "\n\n";

// Conectar a base de datos local
echo "🔌 Conectando a base de datos LOCAL...\n";
$pdo_local = conectarBD($config_local);
echo obtenerInformeBD($pdo_local, 'epicosie_pruebas');

echo "\n" . str_repeat("=", 80) . "\n\n";

// Conectar a base de datos central
echo "🔌 Conectando a base de datos CENTRAL...\n";
$pdo_central = conectarBD($config_central);
echo obtenerInformeBD($pdo_central, 'epicosie_central');

echo "\n✅ Informe generado exitosamente\n";
echo "</pre>";
?>
