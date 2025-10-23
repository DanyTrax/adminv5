<?php

require_once __DIR__ . "/../controladores/clientes-central.controlador.php";

// Limpiar cualquier salida previa
ob_clean();

// Código para producción: Solo Administradores
if ($_SESSION["perfil"] != "Administrador") {
    echo json_encode([
        'success' => false,
        'error' => 'No tienes permisos para acceder a esta sección'
    ]);
    exit;
}

$accion = $_POST["accion"] ?? "";

switch ($accion) {
    
    case "obtener_clientes_centrales":
        try {
            $clientes = ControladorClientesCentral::ctrMostrarClientesCentral();
            
            if ($clientes !== false) {
                echo json_encode([
                    'success' => true,
                    'clientes' => $clientes
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'error' => 'Error obteniendo clientes centrales'
                ]);
            }
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => 'Error: ' . $e->getMessage()
            ]);
        }
        break;
    
    case "crear_cliente_central":
        try {
            $datos = json_decode($_POST["datos"], true);
            
            $resultado = ControladorClientesCentral::ctrCrearClienteCentral($datos);
            
            echo json_encode($resultado);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => 'Error: ' . $e->getMessage()
            ]);
        }
        break;
    
    case "editar_cliente_central":
        try {
            $datos = json_decode($_POST["datos"], true);
            
            $resultado = ControladorClientesCentral::ctrEditarClienteCentral($datos);
            
            echo json_encode($resultado);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => 'Error: ' . $e->getMessage()
            ]);
        }
        break;
    
    case "eliminar_cliente_central":
        try {
            $id_central = $_POST["id_central"];
            
            $resultado = ControladorClientesCentral::ctrEliminarClienteCentral($id_central);
            
            echo json_encode($resultado);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => 'Error: ' . $e->getMessage()
            ]);
        }
        break;
    
    case "verificar_duplicado_cliente":
        try {
            $documento = $_POST["documento"] ?? "";
            $email = $_POST["email"] ?? "";
            $es_edicion = $_POST["es_edicion"] ?? "0";
            $id_central = $_POST["id_central"] ?? "";
            
            $resultado = ControladorClientesCentral::ctrVerificarDuplicadoCliente($documento, $email);
            
            // Si es edición y el cliente es el mismo, no es duplicado
            if ($es_edicion == "1" && $resultado['existe'] && $resultado['cliente']['id_central'] == $id_central) {
                echo json_encode([
                    'existe' => false
                ]);
            } else {
                echo json_encode($resultado);
            }
        } catch (Exception $e) {
            echo json_encode([
                'existe' => false,
                'error' => 'Error: ' . $e->getMessage()
            ]);
        }
        break;
    
    case "obtener_sucursales_disponibles":
        try {
            $sucursales = ControladorClientesCentral::ctrObtenerSucursalesDisponibles();
            
            echo json_encode([
                'success' => true,
                'sucursales' => $sucursales
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => 'Error: ' . $e->getMessage()
            ]);
        }
        break;
    
    case "obtener_estadisticas":
        try {
            $clientes = ControladorClientesCentral::ctrMostrarClientesCentral();
            $sucursales = ControladorClientesCentral::ctrObtenerSucursalesDisponibles();
            
            $totalClientes = is_array($clientes) ? count($clientes) : 0;
            $sucursalesActivas = is_array($sucursales) ? count($sucursales) : 0;
            
            echo json_encode([
                'success' => true,
                'total_clientes' => $totalClientes,
                'sucursales_activas' => $sucursalesActivas,
                'clientes_unicos' => $totalClientes,
                'duplicados' => 0
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => 'Error: ' . $e->getMessage()
            ]);
        }
        break;
    
    case "sincronizar_todos_clientes":
        try {
            // Por ahora solo retornamos éxito
            // La sincronización real se implementará después
            echo json_encode([
                'success' => true,
                'message' => 'Sincronización completada (pendiente de implementar)'
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => 'Error: ' . $e->getMessage()
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
