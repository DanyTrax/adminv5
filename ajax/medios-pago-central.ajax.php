<?php
/*=============================================
AJAX MEDIOS DE PAGO CENTRAL
=============================================*/

session_start();

// Verificar sesión
if (!isset($_SESSION["perfil"])) {
    echo json_encode(['success' => false, 'error' => 'Usuario no autenticado']);
    exit;
}

// Verificar permisos
if (!in_array($_SESSION["perfil"], ["Administrador", "Especial"])) {
    echo json_encode(['success' => false, 'error' => 'Sin permisos para acceder a este módulo']);
    exit;
}

// Incluir controlador
require_once __DIR__ . "/../controladores/medios-pago-central.controlador.php";

// Verificar que se especificó una acción
if(isset($_POST["accion"])) {
    try {
        switch($_POST["accion"]) {
            case "obtener_medios_pago_central":
                $medios = ControladorMediosPagoCentral::ctrObtenerMediosPagoCentral();
                echo json_encode($medios);
                break;
                
            case "obtener_sucursales_estado":
                $sucursales = ControladorMediosPagoCentral::ctrObtenerSucursalesEstado();
                echo json_encode($sucursales);
                break;
                
            case "obtener_estado_medios_sucursal":
                $sucursalId = $_POST["sucursal_id"];
                $medios = ControladorMediosPagoCentral::ctrObtenerEstadoMediosSucursal($sucursalId);
                echo json_encode($medios);
                break;
                
            case "obtener_sucursales_destino_activar":
                $sucursales = ControladorMediosPagoCentral::ctrObtenerSucursalesDestinoActivar();
                echo json_encode($sucursales);
                break;
                
            case "obtener_sucursales_destino_desactivar":
                $sucursales = ControladorMediosPagoCentral::ctrObtenerSucursalesDestinoDesactivar();
                echo json_encode($sucursales);
                break;
                
            case "obtener_estado_completo":
                $estado = ControladorMediosPagoCentral::ctrObtenerEstadoCompleto();
                echo json_encode($estado);
                break;
                
            case "obtener_estado_medio":
                $medioId = $_POST["medio_id"];
                $estado = ControladorMediosPagoCentral::ctrObtenerEstadoMedio($medioId);
                echo json_encode($estado);
                break;
                
            case "crear_medio_pago":
                $datos = [
                    'codigo' => $_POST["codigo"],
                    'nombre' => $_POST["nombre"],
                    'descripcion' => $_POST["descripcion"],
                    'tipo' => $_POST["tipo"]
                ];
                $resultado = ControladorMediosPagoCentral::ctrCrearMedioPago($datos);
                echo json_encode($resultado);
                break;
                
            case "activar_medios_sucursales":
                $mediosPago = $_POST["medios_pago"];
                $sucursales = $_POST["sucursales"];
                $resultado = ControladorMediosPagoCentral::ctrActivarMediosSucursales($mediosPago, $sucursales);
                echo json_encode($resultado);
                break;
                
            case "desactivar_medios_sucursales":
                $mediosPago = $_POST["medios_pago"];
                $sucursales = $_POST["sucursales"];
                $resultado = ControladorMediosPagoCentral::ctrDesactivarMediosSucursales($mediosPago, $sucursales);
                echo json_encode($resultado);
                break;
                
            case "toggle_estado_medio_sucursal":
                $medioId = $_POST["medio_id"];
                $sucursalId = $_POST["sucursal_id"];
                $nuevoEstado = $_POST["nuevo_estado"];
                $resultado = ControladorMediosPagoCentral::ctrToggleEstadoMedioSucursal($medioId, $sucursalId, $nuevoEstado);
                echo json_encode($resultado);
                break;
                
            case "eliminar_medio_pago":
                $id = $_POST["id"];
                $resultado = ControladorMediosPagoCentral::ctrEliminarMedioPago($id);
                echo json_encode($resultado);
                break;
                
            default:
                echo json_encode(['success' => false, 'error' => 'Acción no reconocida']);
                break;
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => 'Error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'No se especificó acción']);
}
?>
