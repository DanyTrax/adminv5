<?php
/**
 * Script de prueba para verificar la importación de medios de pago
 * desde sucursales seleccionadas del central
 */

require_once 'config.php';

try {
    // Conectar a la base de datos local
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    
    echo "<h2>🔍 Verificación de Importación de Medios de Pago</h2>\n";
    
    // Simular datos de sucursal (como los que vendrían del instalador)
    $datos_sucursal = [
        'codigo_sucursal' => 'PRUEBA',
        'nombre' => 'Sucursal Prueba',
        'url_central' => 'https://pruebas2.acplasticos.com/'
    ];
    
    // Simular sucursales seleccionadas (como las que vendrían del modal)
    $sucursales_seleccionadas = [
        ['id' => 1, 'nombre' => 'Sucursal Principal'],
        ['id' => 2, 'nombre' => 'Sucursal Norte']
    ];
    
    echo "<h3>📋 Datos de Prueba:</h3>\n";
    echo "<p><strong>URL Central:</strong> {$datos_sucursal['url_central']}</p>\n";
    echo "<p><strong>Sucursales Seleccionadas:</strong> " . count($sucursales_seleccionadas) . "</p>\n";
    
    foreach ($sucursales_seleccionadas as $sucursal) {
        echo "<p>- {$sucursal['nombre']} (ID: {$sucursal['id']})</p>\n";
    }
    
    echo "<h3>🌐 Probando Conexión al Central:</h3>\n";
    
    // Probar conexión al central
    $url_api_central = rtrim($datos_sucursal['url_central'], '/') . '/api-transferencias/obtener-sucursales-activas.php';
    echo "<p><strong>URL API Central:</strong> $url_api_central</p>\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url_api_central);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        echo "<p style='color: red;'>❌ Error cURL: $error</p>\n";
    } elseif ($http_code !== 200) {
        echo "<p style='color: red;'>❌ HTTP Error: $http_code</p>\n";
    } else {
        $data = json_decode($response, true);
        if ($data && isset($data['success']) && $data['success']) {
            echo "<p style='color: green;'>✅ Conexión exitosa - {$data['total']} sucursales disponibles</p>\n";
            
            // Mostrar sucursales disponibles
            echo "<h4>Sucursales Disponibles:</h4>\n";
            echo "<ul>\n";
            foreach ($data['sucursales'] as $sucursal) {
                echo "<li><strong>{$sucursal['nombre']}</strong> ({$sucursal['codigo_sucursal']}) - {$sucursal['url_api']}</li>\n";
            }
            echo "</ul>\n";
            
            // Filtrar sucursales seleccionadas
            $sucursales_activas = [];
            foreach ($data['sucursales'] as $sucursal) {
                foreach ($sucursales_seleccionadas as $seleccionada) {
                    if ($sucursal['id'] == $seleccionada['id']) {
                        $sucursales_activas[] = $sucursal;
                        break;
                    }
                }
            }
            
            echo "<h4>Sucursales Seleccionadas para Importar:</h4>\n";
            if (empty($sucursales_activas)) {
                echo "<p style='color: orange;'>⚠️ No se encontraron datos de las sucursales seleccionadas</p>\n";
            } else {
                echo "<ul>\n";
                foreach ($sucursales_activas as $sucursal) {
                    echo "<li><strong>{$sucursal['nombre']}</strong> - {$sucursal['url_api']}</li>\n";
                }
                echo "</ul>\n";
            }
            
        } else {
            echo "<p style='color: orange;'>⚠️ Respuesta inválida del central</p>\n";
        }
    }
    
    echo "<h3>💳 Verificación de Medios de Pago Actuales:</h3>\n";
    
    // Verificar si existe la tabla medios_pago
    try {
        $stmt = $pdo->query("SHOW TABLES LIKE 'medios_pago'");
        $tabla_existe = $stmt->fetch();
        
        if ($tabla_existe) {
            echo "<p style='color: green;'>✅ Tabla 'medios_pago' existe</p>\n";
            
            // Obtener medios de pago actuales
            $stmt = $pdo->prepare("
                SELECT 
                    id,
                    nombre,
                    descripcion,
                    activo,
                    fecha_creacion
                FROM medios_pago
                ORDER BY nombre ASC
            ");
            
            $stmt->execute();
            $medios_actuales = $stmt->fetchAll();
            
            echo "<p><strong>Total de medios de pago actuales:</strong> " . count($medios_actuales) . "</p>\n";
            
            if (!empty($medios_actuales)) {
                echo "<table border='1' style='border-collapse: collapse; width: 100%;'>\n";
                echo "<tr style='background: #f0f0f0;'>\n";
                echo "<th>ID</th><th>Nombre</th><th>Descripción</th><th>Estado</th><th>Fecha Creación</th>\n";
                echo "</tr>\n";
                
                foreach ($medios_actuales as $medio) {
                    echo "<tr>\n";
                    echo "<td>{$medio['id']}</td>\n";
                    echo "<td>{$medio['nombre']}</td>\n";
                    echo "<td>{$medio['descripcion']}</td>\n";
                    echo "<td>" . ($medio['activo'] ? '✅ Activo' : '❌ Inactivo') . "</td>\n";
                    echo "<td>{$medio['fecha_creacion']}</td>\n";
                    echo "</tr>\n";
                }
                
                echo "</table>\n";
            } else {
                echo "<p style='color: orange;'>⚠️ No hay medios de pago configurados</p>\n";
            }
            
        } else {
            echo "<p style='color: red;'>❌ Tabla 'medios_pago' no existe</p>\n";
        }
        
    } catch (PDOException $e) {
        echo "<p style='color: red;'>❌ Error al verificar tabla medios_pago: " . $e->getMessage() . "</p>\n";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Error de base de datos: " . $e->getMessage() . "</p>\n";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error general: " . $e->getMessage() . "</p>\n";
}
?>
