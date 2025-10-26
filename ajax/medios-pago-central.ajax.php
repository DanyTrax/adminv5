<?php
require_once "../controladores/medios-pago-central.controlador.php";
require_once "../modelos/medios-pago-central.modelo.php";

if (isset($_POST["accion"])) {
    switch ($_POST["accion"]) {
        case "obtener_medios_pago_central":
            $medios = ControladorMediosPagoCentral::ctrObtenerMediosPagoCentral();
            echo json_encode($medios);
            break;
        case "obtener_medio_pago_central":
            $id = $_POST["id"];
            $medio = ControladorMediosPagoCentral::ctrObtenerMedioPagoCentral($id);
            echo json_encode($medio);
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
        case "obtener_sucursales_destino_asignar":
            $sucursales = ControladorMediosPagoCentral::ctrObtenerSucursalesDestinoAsignar();
            echo json_encode($sucursales);
            break;
        case "obtener_estado_completo":
            $estadoCompleto = ControladorMediosPagoCentral::ctrObtenerEstadoCompleto();
            echo json_encode($estadoCompleto);
            break;
        case "obtener_estado_medio_especifico":
            $medioId = $_POST["medio_id"];
            $estado = ControladorMediosPagoCentral::ctrObtenerEstadoMedioEspecifico($medioId);
            echo json_encode($estado);
            break;
        case "crear_medio_pago":
            $datos = [
                "codigo" => $_POST["codigo"],
                "nombre" => $_POST["nombre"],
                "descripcion" => $_POST["descripcion"],
                "tipo" => $_POST["tipo"]
            ];
            $respuesta = ControladorMediosPagoCentral::ctrCrearMedioPago($datos);
            echo json_encode($respuesta);
            break;
        case "editar_medio_pago":
            $datos = [
                "id" => $_POST["id"],
                "codigo" => $_POST["codigo"],
                "nombre" => $_POST["nombre"],
                "descripcion" => $_POST["descripcion"],
                "tipo" => $_POST["tipo"],
                "activo" => $_POST["activo"]
            ];
            $respuesta = ControladorMediosPagoCentral::ctrEditarMedioPago($datos);
            echo json_encode($respuesta);
            break;
        case "eliminar_medio_pago":
            $id = $_POST["id"];
            $respuesta = ControladorMediosPagoCentral::ctrEliminarMedioPago($id);
            echo json_encode($respuesta);
            break;
        case "asignar_medios_sucursales":
            $mediosPago = $_POST["medios_pago"];
            $sucursales = $_POST["sucursales"];
            $resultado = ControladorMediosPagoCentral::ctrAsignarMediosSucursales($mediosPago, $sucursales);
            echo json_encode($resultado);
            break;
        case "sincronizar_sucursales_activas":
            $resultado = ControladorMediosPagoCentral::ctrSincronizarSucursalesActivas();
            echo json_encode($resultado);
            break;
        case "desactivar_todas_sucursales":
            $mediosPago = $_POST["medios_pago"];
            $resultado = ControladorMediosPagoCentral::ctrDesactivarTodasSucursales($mediosPago);
            echo json_encode($resultado);
            break;
        case "eliminar_todas_asignaciones":
            $mediosPago = $_POST["medios_pago"];
            $resultado = ControladorMediosPagoCentral::ctrEliminarTodasAsignaciones($mediosPago);
            echo json_encode($resultado);
            break;
        case "toggle_estado_medio_sucursal":
            $medioId = $_POST["medio_id"];
            $sucursalId = $_POST["sucursal_id"];
            $nuevoEstado = $_POST["nuevo_estado"];
            $resultado = ControladorMediosPagoCentral::ctrToggleEstadoMedioSucursal($medioId, $sucursalId, $nuevoEstado);
            echo json_encode($resultado);
            break;
        default:
            echo json_encode(['success' => false, 'error' => 'Acción no reconocida']);
            break;
    }
} else {
    echo json_encode(['success' => false, 'error' => 'No se especificó ninguna acción']);
}
?>