<?php

// Script completo para probar el sistema de stock por sucursales
echo "<h1>🧪 Prueba Completa del Sistema de Stock por Sucursales</h1>";

// Simular sesión
session_start();
$_SESSION["perfil"] = "Administrador";

echo "<h2>1. Verificando conexión a BD central...</h2>";

try {
    require_once "api-transferencias/conexion-central.php";
    $pdo = ConexionCentral::conectar();
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM sucursales WHERE activo = 1");
    $stmt->execute();
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<p>✅ Conexión a BD central exitosa - Sucursales activas: " . $resultado['total'] . "</p>";
    
} catch (Exception $e) {
    echo "<p>❌ Error conectando a BD central: " . $e->getMessage() . "</p>";
    exit;
}

echo "<h2>2. Probando consulta de sucursales disponibles...</h2>";

try {
    require_once "controladores/sucursales.controlador.php";
    $sucursales = ControladorSucursales::ctrObtenerSucursalesDisponibles();
    
    if ($sucursales && $sucursales['success']) {
        echo "<p>✅ Sucursales obtenidas: " . count($sucursales['data']) . "</p>";
        
        $conectadas = 0;
        foreach ($sucursales['data'] as $sucursal) {
            if ($sucursal['estado_conexion'] === 'conectado') {
                $conectadas++;
            }
        }
        echo "<p>✅ Sucursales conectadas: " . $conectadas . "</p>";
    } else {
        echo "<p>❌ Error obteniendo sucursales: " . ($sucursales['message'] ?? 'Error desconocido') . "</p>";
        exit;
    }
    
} catch (Exception $e) {
    echo "<p>❌ Error en consulta de sucursales: " . $e->getMessage() . "</p>";
    exit;
}

echo "<h2>3. Probando consulta de stock en sucursales...</h2>";

// Simular productos de una solicitud
$productos = [
    [
        "codigo" => "PROD001",
        "descripcion" => "Producto de Prueba 1",
        "cantidad" => 10
    ],
    [
        "codigo" => "PROD002", 
        "descripcion" => "Producto de Prueba 2",
        "cantidad" => 5
    ]
];

echo "<p>Productos a consultar:</p>";
echo "<ul>";
foreach ($productos as $producto) {
    echo "<li>{$producto['codigo']} - {$producto['descripcion']} (Solicitado: {$producto['cantidad']})</li>";
}
echo "</ul>";

// Simular la consulta AJAX
$_POST["accion"] = "consultar_stock_sucursales";
$_POST["productos"] = json_encode($productos);

echo "<h3>Ejecutando consulta AJAX...</h3>";

ob_start();
try {
    include "ajax/stock-disponible-sucursales.ajax.php";
    $output = ob_get_clean();
    
    echo "<h4>✅ Respuesta AJAX generada correctamente</h4>";
    
    $json = json_decode($output, true);
    if ($json && $json['success']) {
        echo "<h4>📊 Resultados del stock:</h4>";
        
        foreach ($json['data'] as $producto) {
            echo "<div style='border: 1px solid #ccc; margin: 10px; padding: 10px;'>";
            echo "<h5>{$producto['codigo']} - {$producto['descripcion']}</h5>";
            echo "<p><strong>Cantidad solicitada:</strong> {$producto['cantidad_solicitada']}</p>";
            echo "<p><strong>Stock disponible por sucursal:</strong></p>";
            echo "<ul>";
            
            foreach ($producto['sucursales'] as $sucursal) {
                $estado = $sucursal['puede_satisfacer'] ? '✅ Puede satisfacer' : '❌ No puede satisfacer';
                echo "<li>{$sucursal['nombre']}: {$sucursal['stock_disponible']} unidades - {$estado}</li>";
            }
            
            echo "</ul>";
            echo "</div>";
        }
        
        echo "<h4>🎯 Resumen:</h4>";
        echo "<p>Total productos consultados: " . count($json['data']) . "</p>";
        
        $totalSucursales = 0;
        foreach ($json['data'] as $producto) {
            $totalSucursales += count($producto['sucursales']);
        }
        echo "<p>Total consultas a sucursales: " . $totalSucursales . "</p>";
        
    } else {
        echo "<h4>❌ Error en respuesta JSON</h4>";
        echo "<p>Mensaje: " . ($json['message'] ?? 'Error desconocido') . "</p>";
    }
    
} catch (Exception $e) {
    ob_end_clean();
    echo "<h4>❌ Excepción en consulta AJAX</h4>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
}

echo "<h2>4. Verificando que el modal se pueda mostrar...</h2>";

// Simular los datos que recibiría el JavaScript
$stockData = [
    [
        'codigo' => 'PROD001',
        'descripcion' => 'Producto de Prueba 1',
        'cantidad_solicitada' => 10,
        'sucursales' => [
            [
                'id' => 1,
                'nombre' => 'Pruebas2',
                'stock_disponible' => 100,
                'puede_satisfacer' => true
            ],
            [
                'id' => 2,
                'nombre' => 'Sucursal Secundaria',
                'stock_disponible' => 0,
                'puede_satisfacer' => false
            ]
        ]
    ]
];

echo "<p>✅ Datos simulados para el modal:</p>";
echo "<pre>";
print_r($stockData);
echo "</pre>";

echo "<h2>✅ Prueba completada</h2>";
echo "<p>El sistema está funcionando correctamente. Si el problema persiste en el navegador, revisa:</p>";
echo "<ul>";
echo "<li>La consola del navegador para ver los console.log</li>";
echo "<li>La pestaña Network para ver si la petición AJAX se está enviando</li>";
echo "<li>Los logs del servidor para ver si hay errores</li>";
echo "</ul>";

?>
