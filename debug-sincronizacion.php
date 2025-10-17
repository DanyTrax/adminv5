<?php
// Archivo temporal para debug de sincronización
session_start();

// Configuración para mostrar errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>🔍 DEBUG: Sincronización de Catálogo</h1>";
echo "<hr>";

try {
    
    // ✅ PASO 1: Verificar conexión local
    echo "<h2>1. 📋 Verificando Conexión Local</h2>";
    require_once "modelos/conexion.php";
    $pdo = Conexion::conectar();
    echo "✅ <strong>Conexión local exitosa</strong><br>";
    echo "Base de datos local conectada correctamente<br><br>";
    
    // ✅ PASO 2: Verificar modelo de catálogo maestro
    echo "<h2>2. 📦 Verificando Modelo de Catálogo Maestro</h2>";
    if (file_exists("modelos/catalogo-maestro.modelo.php")) {
        echo "✅ <strong>Archivo modelo existe:</strong> modelos/catalogo-maestro.modelo.php<br>";
        require_once "modelos/catalogo-maestro.modelo.php";
        echo "✅ <strong>Modelo cargado correctamente</strong><br>";
        
        // Verificar si existe el método
        if (method_exists('ModeloCatalogoMaestro', 'mdlObtenerDatosParaSincronizacion')) {
            echo "✅ <strong>Método existe:</strong> mdlObtenerDatosParaSincronizacion()<br><br>";
        } else {
            echo "❌ <strong>ERROR:</strong> El método mdlObtenerDatosParaSincronizacion() NO EXISTE<br>";
            echo "📝 <strong>Métodos disponibles en la clase:</strong><br>";
            $metodos = get_class_methods('ModeloCatalogoMaestro');
            foreach ($metodos as $metodo) {
                echo "   - " . $metodo . "<br>";
            }
            echo "<br>";
        }
    } else {
        echo "❌ <strong>ERROR:</strong> No se encuentra el archivo modelo<br><br>";
    }
    
    // ✅ PASO 3: Probar conexión central usando API
    echo "<h2>3. 🌐 Verificando Conexión Central (API)</h2>";
    try {
        require_once "api-transferencias/conexion-central.php";
        $dbCentral = ConexionCentral::conectar();
        echo "✅ <strong>Conexión central exitosa (usando API)</strong><br>";
        echo "✅ <strong>Base de datos:</strong> epicosie_central<br>";
        
        // Verificar tabla catalogo_maestro
        $stmt = $dbCentral->prepare("SHOW TABLES LIKE 'catalogo_maestro'");
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            echo "✅ <strong>Tabla 'catalogo_maestro' existe</strong><br>";
            
            // Contar productos
            $stmt = $dbCentral->prepare("SELECT COUNT(*) as total FROM catalogo_maestro WHERE activo = 1");
            $stmt->execute();
            $count = $stmt->fetch();
            echo "📊 <strong>Total productos activos:</strong> " . $count['total'] . "<br>";
            
            // Mostrar primeros 5 productos
            $stmt = $dbCentral->prepare("SELECT codigo, descripcion, precio_venta, es_divisible FROM catalogo_maestro WHERE activo = 1 ORDER BY codigo LIMIT 5");
            $stmt->execute();
            $productos = $stmt->fetchAll();
            
            if (!empty($productos)) {
                echo "<br>📋 <strong>Primeros 5 productos:</strong><br>";
                echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
                echo "<tr style='background: #f0f0f0;'><th>Código</th><th>Descripción</th><th>Precio</th><th>Divisible</th></tr>";
                foreach ($productos as $prod) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($prod['codigo']) . "</td>";
                    echo "<td>" . htmlspecialchars($prod['descripcion']) . "</td>";
                    echo "<td>$" . number_format($prod['precio_venta'], 2) . "</td>";
                    echo "<td>" . ($prod['es_divisible'] ? '✅ Sí' : '❌ No') . "</td>";
                    echo "</tr>";
                }
                echo "</table><br>";
                
                // Verificar productos divisibles
                $stmt = $dbCentral->prepare("SELECT COUNT(*) as total FROM catalogo_maestro WHERE activo = 1 AND es_divisible = 1");
                $stmt->execute();
                $divisibles = $stmt->fetch();
                echo "📊 <strong>Productos divisibles:</strong> " . $divisibles['total'] . "<br>";
                
                // Verificar tabla categorías
                $stmt = $dbCentral->prepare("SHOW TABLES LIKE 'categorias'");
                $stmt->execute();
                if ($stmt->rowCount() > 0) {
                    echo "✅ <strong>Tabla 'categorias' existe en BD central</strong><br>";
                    $stmt = $dbCentral->prepare("SELECT COUNT(*) as total FROM categorias");
                    $stmt->execute();
                    $catCount = $stmt->fetch();
                    echo "📊 <strong>Total categorías:</strong> " . $catCount['total'] . "<br><br>";
                } else {
                    echo "⚠️ <strong>ADVERTENCIA:</strong> Tabla 'categorias' no existe en BD central<br><br>";
                }
                
            } else {
                echo "⚠️ <strong>No hay productos en catálogo maestro</strong><br><br>";
            }
            
        } else {
            echo "❌ <strong>ERROR:</strong> La tabla 'catalogo_maestro' NO EXISTE en BD central<br><br>";
        }
        
    } catch (Exception $e) {
        echo "❌ <strong>ERROR conexión central:</strong> " . $e->getMessage() . "<br><br>";
    }
    
    // ✅ PASO 4: Probar el método de sincronización (si existe)
    echo "<h2>4. 🔄 Probando Método de Sincronización</h2>";
    if (method_exists('ModeloCatalogoMaestro', 'mdlObtenerDatosParaSincronizacion')) {
        try {
            echo "🚀 <strong>Ejecutando mdlObtenerDatosParaSincronizacion()...</strong><br>";
            $datos = ModeloCatalogoMaestro::mdlObtenerDatosParaSincronizacion();
            
            if ($datos && is_array($datos)) {
                echo "✅ <strong>Datos obtenidos exitosamente</strong><br>";
                echo "📊 <strong>Total productos procesados:</strong> " . count($datos) . "<br>";
                
                if (count($datos) > 0) {
                    echo "<br>📋 <strong>Primer producto procesado:</strong><br>";
                    echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ccc; font-size: 12px; overflow: auto;'>";
                    print_r($datos[0]);
                    echo "</pre>";
                    
                    // Verificar productos divisibles procesados
                    $divisiblesEnDatos = array_filter($datos, function($p) { return $p['es_divisible'] == 1; });
                    echo "📊 <strong>Productos divisibles en datos:</strong> " . count($divisiblesEnDatos) . "<br>";
                    
                    if (count($divisiblesEnDatos) > 0) {
                        echo "<br>📋 <strong>Primer producto divisible procesado:</strong><br>";
                        $primerDivisible = array_values($divisiblesEnDatos)[0];
                        echo "<div style='background: #f0f8ff; padding: 10px; border: 1px solid #ddd; font-size: 12px;'>";
                        echo "<strong>Código:</strong> " . $primerDivisible['codigo'] . "<br>";
                        echo "<strong>Descripción:</strong> " . $primerDivisible['descripcion'] . "<br>";
                        echo "<strong>Es divisible:</strong> " . ($primerDivisible['es_divisible'] ? 'SÍ' : 'NO') . "<br>";
                        echo "<strong>Hijo mitad:</strong> " . ($primerDivisible['codigo_hijo_mitad'] ?: 'N/A') . "<br>";
                        echo "<strong>Nombre mitad:</strong> " . ($primerDivisible['nombre_mitad'] ?: 'N/A') . "<br>";
                        echo "<strong>Precio mitad:</strong> $" . number_format($primerDivisible['precio_mitad'], 2) . "<br>";
                        echo "</div><br>";
                    }
                }
                
                // Guardar datos en archivo temporal
                $archivoJson = 'debug_datos_sincronizacion.json';
                file_put_contents($archivoJson, json_encode($datos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                echo "💾 <strong>Datos guardados en:</strong> <a href='{$archivoJson}' target='_blank'>{$archivoJson}</a><br>";
                echo "📁 <strong>Tamaño archivo:</strong> " . round(filesize($archivoJson) / 1024, 2) . " KB<br><br>";
                
            } else {
                echo "❌ <strong>ERROR:</strong> El método retornó datos vacíos o nulos<br>";
                echo "Valor retornado: " . var_export($datos, true) . "<br><br>";
            }
            
        } catch (Exception $e) {
            echo "❌ <strong>ERROR ejecutando método:</strong> " . $e->getMessage() . "<br>";
            echo "📍 <strong>Archivo:</strong> " . $e->getFile() . "<br>";
            echo "📍 <strong>Línea:</strong> " . $e->getLine() . "<br><br>";
        }
    } else {
        echo "⚠️ <strong>SALTADO:</strong> Método mdlObtenerDatosParaSincronizacion() no existe<br><br>";
    }
    
    // ✅ PASO 5: Verificar tabla productos local
    echo "<h2>5. 🏪 Verificando Tabla Productos Local</h2>";
    try {
        $stmt = $pdo->prepare("SHOW TABLES LIKE 'productos'");
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            echo "✅ <strong>Tabla 'productos' existe localmente</strong><br>";
            
            $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM productos WHERE activo = 1");
            $stmt->execute();
            $count = $stmt->fetch();
            echo "📊 <strong>Total productos locales activos:</strong> " . $count['total'] . "<br>";
            
            $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM productos");
            $stmt->execute();
            $countTotal = $stmt->fetch();
            echo "📊 <strong>Total productos locales (todos):</strong> " . $countTotal['total'] . "<br>";
            
            // Verificar productos divisibles locales
            $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM productos WHERE es_divisible = 1");
            $stmt->execute();
            $divisiblesLocal = $stmt->fetch();
            echo "📊 <strong>Productos divisibles locales:</strong> " . $divisiblesLocal['total'] . "<br>";
            
            // Mostrar estructura de tabla
            $stmt = $pdo->prepare("DESCRIBE productos");
            $stmt->execute();
            $campos = $stmt->fetchAll();
            
            echo "<br>📋 <strong>Estructura tabla productos:</strong><br>";
            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
            echo "<tr style='background: #f0f0f0;'><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Clave</th><th>Default</th></tr>";
            foreach ($campos as $campo) {
                echo "<tr>";
                echo "<td><strong>" . $campo['Field'] . "</strong></td>";
                echo "<td>" . $campo['Type'] . "</td>";
                echo "<td>" . $campo['Null'] . "</td>";
                echo "<td>" . $campo['Key'] . "</td>";
                echo "<td>" . ($campo['Default'] ?: 'NULL') . "</td>";
                echo "</tr>";
            }
            echo "</table><br>";
            
        } else {
            echo "❌ <strong>ERROR:</strong> La tabla 'productos' NO EXISTE localmente<br><br>";
        }
        
    } catch (Exception $e) {
        echo "❌ <strong>ERROR verificando tabla productos:</strong> " . $e->getMessage() . "<br><br>";
    }
    
    // ✅ PASO 6: Verificar sucursales
    echo "<h2>6. 🏢 Verificando Sucursales</h2>";
    try {
        require_once "modelos/sucursales.modelo.php";
        $respuestaSucursales = ModeloSucursales::mdlObtenerSucursales();
        
        if ($respuestaSucursales && $respuestaSucursales['success']) {
            echo "✅ <strong>Sucursales obtenidas exitosamente</strong><br>";
            echo "📊 <strong>Total sucursales:</strong> " . count($respuestaSucursales['data']) . "<br>";
            
            $sucursalesActivas = array_filter($respuestaSucursales['data'], function($s) { return $s['activo']; });
            echo "📊 <strong>Sucursales activas:</strong> " . count($sucursalesActivas) . "<br>";
            
            echo "<br>📋 <strong>Lista de sucursales:</strong><br>";
            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
            echo "<tr style='background: #f0f0f0;'><th>Código</th><th>Nombre</th><th>Estado</th><th>URL API</th><th>Registrada</th></tr>";
            foreach ($respuestaSucursales['data'] as $sucursal) {
                echo "<tr>";
                echo "<td><strong>" . htmlspecialchars($sucursal['codigo_sucursal']) . "</strong></td>";
                echo "<td>" . htmlspecialchars($sucursal['nombre']) . "</td>";
                echo "<td>" . ($sucursal['activo'] ? '✅ Activa' : '❌ Inactiva') . "</td>";
                echo "<td style='font-size: 10px; max-width: 200px; word-wrap: break-word;'>" . htmlspecialchars($sucursal['url_api']) . "</td>";
                echo "<td>" . (isset($sucursal['registrada_en_central']) && $sucursal['registrada_en_central'] ? '✅ Sí' : '❌ No') . "</td>";
                echo "</tr>";
            }
            echo "</table><br>";
            
        } else {
            echo "❌ <strong>ERROR:</strong> No se pudieron obtener sucursales<br>";
            if (isset($respuestaSucursales['message'])) {
                echo "Mensaje: " . $respuestaSucursales['message'] . "<br>";
            }
            echo "<br>";
        }
        
    } catch (Exception $e) {
        echo "❌ <strong>ERROR verificando sucursales:</strong> " . $e->getMessage() . "<br><br>";
    }
    
    // ✅ PASO 7: Verificar archivo de sincronización receptor
    echo "<h2>7. 🎯 Verificando API de Sincronización</h2>";
    $archivoAPI = "api-transferencias/sincronizar_catalogo.php";
    if (file_exists($archivoAPI)) {
        echo "✅ <strong>Archivo API existe:</strong> {$archivoAPI}<br>";
        echo "📁 <strong>Tamaño:</strong> " . round(filesize($archivoAPI) / 1024, 2) . " KB<br>";
        echo "📅 <strong>Última modificación:</strong> " . date('Y-m-d H:i:s', filemtime($archivoAPI)) . "<br>";
        
        // Verificar permisos
        if (is_readable($archivoAPI)) {
            echo "✅ <strong>Archivo legible</strong><br>";
        } else {
            echo "❌ <strong>ERROR:</strong> Archivo no legible<br>";
        }
        echo "<br>";
    } else {
        echo "❌ <strong>ERROR:</strong> Archivo {$archivoAPI} NO EXISTE<br><br>";
    }
    
    // ✅ PASO 8: Test de conectividad a sucursales
    echo "<h2>8. 🌐 Test de Conectividad a Sucursales</h2>";
    if (isset($sucursalesActivas) && count($sucursalesActivas) > 0) {
        echo "🚀 <strong>Probando conexión a las primeras 2 sucursales activas...</strong><br><br>";
        
        $testCount = 0;
        foreach ($sucursalesActivas as $sucursal) {
            if ($testCount >= 2) break;
            $testCount++;
            
            $apiUrl = rtrim($sucursal['url_api'], '/') . '/test_conexion.php';
            echo "<strong>🏢 " . $sucursal['nombre'] . " (" . $sucursal['codigo_sucursal'] . ")</strong><br>";
            echo "🌐 URL: " . $apiUrl . "<br>";
            
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $apiUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_CONNECTTIMEOUT => 5,
                CURLOPT_SSL_VERIFYPEER => false
            ]);
            
            $inicio = microtime(true);
            $respuesta = curl_exec($ch);
            $tiempo = round((microtime(true) - $inicio) * 1000);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            if (curl_errno($ch)) {
                echo "❌ <strong>Error:</strong> " . curl_error($ch) . "<br>";
            } else {
                echo "📡 <strong>HTTP Code:</strong> {$httpCode}<br>";
                echo "⏱️ <strong>Tiempo:</strong> {$tiempo}ms<br>";
                
                if ($httpCode === 200 && $respuesta) {
                    $json = json_decode($respuesta, true);
                    if ($json && $json['success']) {
                        echo "✅ <strong>Conexión exitosa</strong><br>";
                        if (isset($json['version'])) {
                            echo "🔧 <strong>Versión API:</strong> " . $json['version'] . "<br>";
                        }
                    } else {
                        echo "⚠️ <strong>Respuesta no válida:</strong> " . substr($respuesta, 0, 100) . "...<br>";
                    }
                } else {
                    echo "❌ <strong>Conexión fallida</strong><br>";
                }
            }
            
            curl_close($ch);
            echo "<br>";
        }
    } else {
        echo "⚠️ <strong>No hay sucursales activas para probar</strong><br><br>";
    }
    
    echo "<hr>";
    echo "<h2>✅ DIAGNÓSTICO COMPLETADO</h2>";
    echo "<div style='background: #f0f8ff; padding: 15px; border: 2px solid #4CAF50; border-radius: 5px;'>";
    echo "<p><strong>📅 Fecha:</strong> " . date('Y-m-d H:i:s') . "</p>";
    echo "<p><strong>🌐 Servidor:</strong> " . ($_SERVER['SERVER_NAME'] ?? 'localhost') . "</p>";
    echo "<p><strong>📂 Directorio:</strong> " . __DIR__ . "</p>";
    echo "<p><strong>🚀 Para eliminar este archivo:</strong> Borra <code>debug-sincronizacion.php</code> del servidor</p>";
    echo "<p><strong>🔍 Archivos generados:</strong></p>";
    echo "<ul>";
    if (file_exists('debug_datos_sincronizacion.json')) {
        echo "<li>📁 <a href='debug_datos_sincronizacion.json' target='_blank'>debug_datos_sincronizacion.json</a></li>";
    }
    echo "</ul>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<h2>❌ ERROR CRÍTICO</h2>";
    echo "<div style='background: #ffe6e6; padding: 15px; border: 2px solid #f44336; border-radius: 5px;'>";
    echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Archivo:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Línea:</strong> " . $e->getLine() . "</p>";
    echo "<p><strong>Stack Trace:</strong></p>";
    echo "<pre style='background: #f5f5f5; padding: 10px; font-size: 11px;'>" . $e->getTraceAsString() . "</pre>";
    echo "</div>";
}
?>