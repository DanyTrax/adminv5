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
    
    // ✅ PASO 3: Probar conexión central
    echo "<h2>3. 🌐 Verificando Conexión Central</h2>";
    if (method_exists('ModeloCatalogoMaestro', 'conectarCentral')) {
        try {
            $dbCentral = ModeloCatalogoMaestro::conectarCentral();
            echo "✅ <strong>Conexión central exitosa</strong><br>";
            
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
                $stmt = $dbCentral->prepare("SELECT codigo, descripcion, precio_venta FROM catalogo_maestro WHERE activo = 1 ORDER BY codigo LIMIT 5");
                $stmt->execute();
                $productos = $stmt->fetchAll();
                
                if (!empty($productos)) {
                    echo "<br>📋 <strong>Primeros 5 productos:</strong><br>";
                    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
                    echo "<tr style='background: #f0f0f0;'><th>Código</th><th>Descripción</th><th>Precio</th></tr>";
                    foreach ($productos as $prod) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($prod['codigo']) . "</td>";
                        echo "<td>" . htmlspecialchars($prod['descripcion']) . "</td>";
                        echo "<td>$" . number_format($prod['precio_venta'], 2) . "</td>";
                        echo "</tr>";
                    }
                    echo "</table><br>";
                } else {
                    echo "⚠️ <strong>No hay productos en catálogo maestro</strong><br><br>";
                }
                
            } else {
                echo "❌ <strong>ERROR:</strong> La tabla 'catalogo_maestro' NO EXISTE en BD central<br><br>";
            }
            
        } catch (Exception $e) {
            echo "❌ <strong>ERROR conexión central:</strong> " . $e->getMessage() . "<br><br>";
        }
    } else {
        echo "❌ <strong>ERROR:</strong> Método conectarCentral() no existe<br><br>";
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
                    echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ccc;'>";
                    print_r($datos[0]);
                    echo "</pre>";
                }
                
                // Guardar datos en archivo temporal
                file_put_contents('debug_datos_sincronizacion.json', json_encode($datos, JSON_PRETTY_PRINT));
                echo "💾 <strong>Datos guardados en:</strong> debug_datos_sincronizacion.json<br><br>";
                
            } else {
                echo "❌ <strong>ERROR:</strong> El método retornó datos vacíos o nulos<br>";
                echo "Valor retornado: " . var_export($datos, true) . "<br><br>";
            }
            
        } catch (Exception $e) {
            echo "❌ <strong>ERROR ejecutando método:</strong> " . $e->getMessage() . "<br><br>";
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
            
            $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM productos");
            $stmt->execute();
            $count = $stmt->fetch();
            echo "📊 <strong>Total productos locales:</strong> " . $count['total'] . "<br>";
            
            // Mostrar estructura de tabla
            $stmt = $pdo->prepare("DESCRIBE productos");
            $stmt->execute();
            $campos = $stmt->fetchAll();
            
            echo "<br>📋 <strong>Estructura tabla productos:</strong><br>";
            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
            echo "<tr style='background: #f0f0f0;'><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Clave</th></tr>";
            foreach ($campos as $campo) {
                echo "<tr>";
                echo "<td>" . $campo['Field'] . "</td>";
                echo "<td>" . $campo['Type'] . "</td>";
                echo "<td>" . $campo['Null'] . "</td>";
                echo "<td>" . $campo['Key'] . "</td>";
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
            
            echo "<br>📋 <strong>Lista de sucursales:</strong><br>";
            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
            echo "<tr style='background: #f0f0f0;'><th>Código</th><th>Nombre</th><th>Estado</th><th>URL API</th></tr>";
            foreach ($respuestaSucursales['data'] as $sucursal) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($sucursal['codigo_sucursal']) . "</td>";
                echo "<td>" . htmlspecialchars($sucursal['nombre']) . "</td>";
                echo "<td>" . ($sucursal['activo'] ? '✅ Activa' : '❌ Inactiva') . "</td>";
                echo "<td style='font-size: 11px;'>" . htmlspecialchars($sucursal['url_api']) . "</td>";
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
    
    echo "<hr>";
    echo "<h2>✅ DIAGNÓSTICO COMPLETADO</h2>";
    echo "<p><strong>Archivo creado:</strong> " . date('Y-m-d H:i:s') . "</p>";
    echo "<p><strong>Para eliminar este archivo:</strong> Borra debug-sincronizacion.php del servidor</p>";
    
} catch (Exception $e) {
    echo "<h2>❌ ERROR CRÍTICO</h2>";
    echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Archivo:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Línea:</strong> " . $e->getLine() . "</p>";
}
?>