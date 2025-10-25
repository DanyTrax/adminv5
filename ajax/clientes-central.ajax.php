<?php

// Limpiar cualquier salida previa si existe buffer
if (ob_get_level()) {
    ob_clean();
}

session_start();

require_once __DIR__ . "/../controladores/clientes-central.controlador.php";

// Código para producción: Solo Administradores
if (!isset($_SESSION["perfil"]) || $_SESSION["perfil"] != "Administrador") {
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
            $resultado = ControladorClientesCentral::ctrImportarClientesDesdeSucursales();
            echo json_encode($resultado);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => 'Error: ' . $e->getMessage()
            ]);
        }
        break;
    
    case "importar_clientes_desde_sucursales":
        try {
            $resultado = ControladorClientesCentral::ctrImportarClientesDesdeSucursales();
            echo json_encode($resultado);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => 'Error: ' . $e->getMessage()
            ]);
        }
        break;
    
    case "obtenerSucursalesBidireccional":
        try {
            $sucursales = ControladorClientesCentral::ctrObtenerSucursalesBidireccional();
            
            if ($sucursales !== false) {
                echo json_encode([
                    'success' => true,
                    'sucursales' => $sucursales
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'error' => 'Error obteniendo sucursales'
                ]);
            }
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => 'Error: ' . $e->getMessage()
            ]);
        }
        break;
    
    case "obtenerSucursalesDestino":
        try {
            $sucursales = ControladorClientesCentral::ctrObtenerSucursalesDestino();
            
            if ($sucursales !== false) {
                echo json_encode([
                    'success' => true,
                    'sucursales' => $sucursales
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'error' => 'Error obteniendo sucursales'
                ]);
            }
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => 'Error: ' . $e->getMessage()
            ]);
        }
        break;
    
    case "obtenerSucursalesParaBorrar":
        try {
            $sucursales = ControladorClientesCentral::ctrObtenerSucursalesParaBorrar();
            
            if ($sucursales !== false) {
                echo json_encode([
                    'success' => true,
                    'sucursales' => $sucursales
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'error' => 'Error obteniendo sucursales'
                ]);
            }
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => 'Error: ' . $e->getMessage()
            ]);
        }
        break;
    
    case "guardarSincronizacionBidireccional":
        try {
            $sucursales = json_decode($_POST["sucursales"], true);
            $resultado = ControladorClientesCentral::ctrGuardarSincronizacionBidireccional($sucursales);
            
            if ($resultado) {
                echo json_encode([
                    'success' => true,
                    'mensaje' => 'Sincronización bidireccional configurada correctamente'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'mensaje' => 'Error configurando sincronización bidireccional'
                ]);
            }
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'mensaje' => 'Error: ' . $e->getMessage()
            ]);
        }
        break;
    
    case "copiarClientesASucursal":
        try {
            $sucursalId = $_POST["sucursalId"];
            $resultado = ControladorClientesCentral::ctrCopiarClientesASucursal($sucursalId);
            
            if ($resultado) {
                echo json_encode([
                    'success' => true,
                    'mensaje' => 'Clientes copiados correctamente'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'mensaje' => 'Error copiando clientes'
                ]);
            }
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'mensaje' => 'Error: ' . $e->getMessage()
            ]);
        }
        break;
    
    case "borrarClientes":
        try {
            $origen = $_POST["origen"];
            $sucursalId = $_POST["sucursalId"] ?? null;
            $resultado = ControladorClientesCentral::ctrBorrarClientes($origen, $sucursalId);
            
            if ($resultado) {
                echo json_encode([
                    'success' => true,
                    'mensaje' => 'Clientes eliminados correctamente'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'mensaje' => 'Error eliminando clientes'
                ]);
            }
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'mensaje' => 'Error: ' . $e->getMessage()
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
