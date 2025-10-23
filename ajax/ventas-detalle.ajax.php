<?php

session_start();

require_once __DIR__ . "/../controladores/ventas.controlador.php";
require_once __DIR__ . "/../controladores/clientes.controlador.php";
require_once __DIR__ . "/../controladores/usuarios.controlador.php";

// Limpiar cualquier salida previa si existe buffer
if (ob_get_level()) {
    ob_clean();
}

// Verificar sesión
if (!isset($_SESSION["perfil"])) {
    echo json_encode([
        'success' => false,
        'error' => 'Sesión no válida'
    ]);
    exit;
}

$accion = $_POST["accion"] ?? "";

switch ($accion) {
    
    case "obtener_detalle_venta":
        try {
            $idVenta = $_POST["idVenta"] ?? null;
            
            if (!$idVenta) {
                echo json_encode([
                    'success' => false,
                    'error' => 'ID de venta no proporcionado'
                ]);
                exit;
            }
            
            // Obtener datos de la venta
            $venta = ControladorVentas::ctrMostrarVentas("id", $idVenta);
            
            if (!$venta) {
                echo json_encode([
                    'success' => false,
                    'error' => 'Venta no encontrada'
                ]);
                exit;
            }
            
            // Obtener datos del cliente
            $cliente = ControladorClientes::ctrMostrarClientes("id", $venta["id_cliente"]);
            
            // Obtener datos del vendedor
            $vendedor = ControladorUsuarios::ctrMostrarUsuarios("id", $venta["id_vendedor"]);
            
            // Obtener productos de la venta
            $productos = ControladorVentas::ctrObtenerProductosVenta($idVenta);
            
            // Obtener historial de abonos
            $historialAbonos = ControladorVentas::ctrObtenerHistorialAbonos($idVenta);
            
            // Preparar respuesta
            $respuesta = [
                'success' => true,
                'venta' => $venta,
                'cliente' => $cliente,
                'vendedor' => $vendedor,
                'productos' => $productos,
                'historial_abonos' => $historialAbonos
            ];
            
            echo json_encode($respuesta);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => 'Error al obtener detalle de venta: ' . $e->getMessage()
            ]);
        }
        break;
        
    default:
        echo json_encode([
            'success' => false,
            'error' => 'Acción no válida'
        ]);
        break;
}

?>
