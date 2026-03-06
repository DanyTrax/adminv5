<?php

session_start();

require_once "../modelos/conexion.php";
require_once "../api-transferencias/conexion-central.php";
require_once "../controladores/solicitudes-stock.controlador.php";
require_once "../modelos/solicitudes-stock.modelo.php";

class AjaxSolicitudesStock {

    /*=============================================
    MOSTRAR SOLICITUD INDIVIDUAL
    =============================================*/
    public $idSolicitud;

    public function ajaxMostrarSolicitud(){
        
        $item = "id";
        $valor = $this->idSolicitud;
        
        $respuesta = ControladorSolicitudesStock::ctrMostrarSolicitudes($item, $valor);
        
        echo json_encode($respuesta);
    }

    /*=============================================
    VER DETALLE COMPLETO DE SOLICITUD - BASE CENTRAL
    =============================================*/
    public function ajaxVerDetalleSolicitud(){
        
        try {
            $stmt = ConexionCentral::conectar()->prepare("
                SELECT * FROM solicitudes_stock 
                WHERE id = :id
            ");
            $stmt->bindParam(":id", $this->idSolicitud, PDO::PARAM_INT);
            $stmt->execute();
            
            $solicitud = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if($solicitud) {
                echo json_encode([
                    'success' => true,
                    'data' => $solicitud,
                    'message' => 'Detalles obtenidos correctamente'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Solicitud no encontrada'
                ]);
            }
            
        } catch(Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al obtener detalles: ' . $e->getMessage()
            ]);
        }
    }

    /*=============================================
    APROBAR SOLICITUD - BASE CENTRAL (Solo Transportador y Administrador)
    =============================================*/
    public function ajaxAprobarSolicitud(){
        
        if($_SESSION['perfil'] != 'Transportador' && $_SESSION['perfil'] != 'Administrador') {
            echo json_encode(['success' => false, 'message' => 'No tiene permisos para aprobar solicitudes']);
            return;
        }
        
        try {
            $stmt = ConexionCentral::conectar()->prepare("
                UPDATE solicitudes_stock 
                SET estado = 'aprobado',
                    usuario_aprobacion = :usuario_id,
                    nombre_usuario_aprobacion = :usuario_nombre,
                    fecha_aprobacion = NOW()
                WHERE id = :id AND estado = 'pendiente'
            ");
            
            $stmt->bindParam(":id", $this->idSolicitud, PDO::PARAM_INT);
            $stmt->bindParam(":usuario_id", $_SESSION['id'], PDO::PARAM_INT);
            $stmt->bindParam(":usuario_nombre", $_SESSION['nombre'], PDO::PARAM_STR);
            
            if($stmt->execute() && $stmt->rowCount() > 0) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Solicitud aprobada correctamente'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'No se pudo aprobar la solicitud (puede que ya esté procesada)'
                ]);
            }
            
        } catch(Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al aprobar solicitud: ' . $e->getMessage()
            ]);
        }
    }

    /*=============================================
    CANCELAR SOLICITUD - BASE CENTRAL
    =============================================*/
    public $motivoCancelacion;

    public function ajaxCancelarSolicitud(){
        
        if($_SESSION['perfil'] != 'Transportador' && $_SESSION['perfil'] != 'Administrador') {
            echo json_encode(['success' => false, 'message' => 'No tiene permisos para cancelar solicitudes']);
            return;
        }
        
        try {
            $stmt = ConexionCentral::conectar()->prepare("
                UPDATE solicitudes_stock 
                SET estado = 'cancelado',
                    motivo_cancelacion = :motivo,
                    usuario_aprobacion = :usuario_id,
                    nombre_usuario_aprobacion = :usuario_nombre,
                    fecha_aprobacion = NOW()
                WHERE id = :id AND estado = 'pendiente'
            ");
            
            $stmt->bindParam(":id", $this->idSolicitud, PDO::PARAM_INT);
            $stmt->bindParam(":motivo", $this->motivoCancelacion, PDO::PARAM_STR);
            $stmt->bindParam(":usuario_id", $_SESSION['id'], PDO::PARAM_INT);
            $stmt->bindParam(":usuario_nombre", $_SESSION['nombre'], PDO::PARAM_STR);
            
            if($stmt->execute() && $stmt->rowCount() > 0) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Solicitud cancelada correctamente'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'No se pudo cancelar la solicitud (puede que ya esté procesada)'
                ]);
            }
            
        } catch(Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al cancelar solicitud: ' . $e->getMessage()
            ]);
        }
    }

    /*=============================================
    ELIMINAR SOLICITUD - BASE CENTRAL
    =============================================*/
    public function ajaxEliminarSolicitud(){
        
        // Verificar permisos
        if($_SESSION['perfil'] !== 'Administrador') {
            echo json_encode([
                'success' => false,
                'message' => 'No tiene permisos para eliminar solicitudes'
            ]);
            return;
        }
        
        try {
            $stmt = ConexionCentral::conectar()->prepare("
                DELETE FROM solicitudes_stock 
                WHERE id = :id
            ");
            
            $stmt->bindParam(":id", $this->idSolicitud, PDO::PARAM_INT);
            
            if($stmt->execute() && $stmt->rowCount() > 0) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Solicitud eliminada correctamente'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'No se pudo eliminar la solicitud'
                ]);
            }
            
        } catch(Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al eliminar solicitud: ' . $e->getMessage()
            ]);
        }
    }

    /*=============================================
    BUSCAR VENTAS PARA REMISIÓN - EN BASE LOCAL
    =============================================*/
    public $busquedaVenta;

    public function ajaxBuscarVentas(){
        
        try {
            $stmt = Conexion::conectar()->prepare("SELECT 
                v.id,
                v.codigo,
                v.fecha_venta as fecha,
                v.total,
                c.nombre as nombre_cliente,
                c.documento as documento_cliente
                FROM ventas v 
                LEFT JOIN clientes c ON v.id_cliente = c.id 
                WHERE v.codigo LIKE :busqueda 
                OR c.nombre LIKE :busqueda 
                OR c.documento LIKE :busqueda
                ORDER BY v.fecha_venta DESC 
                LIMIT 10");
            
            $busqueda = "%" . $this->busquedaVenta . "%";
            $stmt->bindParam(":busqueda", $busqueda, PDO::PARAM_STR);
            $stmt->execute();
            
            $ventas = $stmt->fetchAll();
            
            echo json_encode([
                "success" => true,
                "data" => $ventas
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                "success" => false,
                "message" => "Error buscando remisiones: " . $e->getMessage()
            ]);
        }
    }

    /*=============================================
    OBTENER PRODUCTOS DE UNA VENTA - EN BASE LOCAL
    =============================================*/
    public $codigoVenta;

    public function ajaxObtenerProductosVenta(){
        
        try {
            $stmt = Conexion::conectar()->prepare("SELECT 
                productos
                FROM ventas 
                WHERE codigo = :codigo");
            
            $stmt->bindParam(":codigo", $this->codigoVenta, PDO::PARAM_STR);
            $stmt->execute();
            
            $venta = $stmt->fetch();
            
            if($venta) {
                $productos = json_decode($venta["productos"], true);
                
                echo json_encode([
                    "success" => true,
                    "productos" => $productos
                ]);
            } else {
                echo json_encode([
                    "success" => false,
                    "message" => "Venta no encontrada"
                ]);
            }
            
        } catch (Exception $e) {
            echo json_encode([
                "success" => false,
                "message" => "Error obteniendo productos de venta: " . $e->getMessage()
            ]);
        }
    }

    /*=============================================
    OBTENER PRODUCTO INDIVIDUAL - BASE LOCAL
    =============================================*/
    public $idProducto;

    public function ajaxObtenerProducto(){
        
        try {
            $stmt = Conexion::conectar()->prepare("SELECT 
                id, codigo, descripcion, stock
                FROM productos 
                WHERE id = :id");
            
            $stmt->bindParam(":id", $this->idProducto, PDO::PARAM_INT);
            $stmt->execute();
            
            $producto = $stmt->fetch();
            
            if($producto) {
                echo json_encode([
                    "success" => true,
                    "data" => $producto
                ]);
            } else {
                echo json_encode([
                    "success" => false,
                    "message" => "Producto no encontrado"
                ]);
            }
            
        } catch (Exception $e) {
            echo json_encode([
                "success" => false,
                "message" => "Error obteniendo producto: " . $e->getMessage()
            ]);
        }
    }
}

/*=============================================
MOSTRAR SOLICITUD
=============================================*/
if(isset($_POST["idSolicitud"])){
    
    $solicitud = new AjaxSolicitudesStock();
    $solicitud->idSolicitud = $_POST["idSolicitud"];
    $solicitud->ajaxMostrarSolicitud();
}

/*=============================================
VER DETALLE DE SOLICITUD
=============================================*/
if(isset($_POST["accion"]) && $_POST["accion"] == "ver_detalle"){
    
    $detalleSolicitud = new AjaxSolicitudesStock();
    $detalleSolicitud->idSolicitud = $_POST["id_solicitud"];
    $detalleSolicitud->ajaxVerDetalleSolicitud();
}

/*=============================================
APROBAR SOLICITUD
=============================================*/
if(isset($_POST["accion"]) && $_POST["accion"] == "aprobar"){
    
    $aprobarSolicitud = new AjaxSolicitudesStock();
    $aprobarSolicitud->idSolicitud = $_POST["id_solicitud"];
    $aprobarSolicitud->ajaxAprobarSolicitud();
}

/*=============================================
CANCELAR SOLICITUD
=============================================*/
if(isset($_POST["accion"]) && $_POST["accion"] == "cancelar"){
    
    $cancelarSolicitud = new AjaxSolicitudesStock();
    $cancelarSolicitud->idSolicitud = $_POST["id_solicitud"];
    $cancelarSolicitud->motivoCancelacion = $_POST["motivo"];
    $cancelarSolicitud->ajaxCancelarSolicitud();
}

/*=============================================
ELIMINAR SOLICITUD
=============================================*/
if(isset($_POST["accion"]) && $_POST["accion"] == "eliminar"){
    
    $eliminarSolicitud = new AjaxSolicitudesStock();
    $eliminarSolicitud->idSolicitud = $_POST["id_solicitud"];
    $eliminarSolicitud->ajaxEliminarSolicitud();
}

/*=============================================
BUSCAR VENTAS (REMISIONES)
=============================================*/
if(isset($_POST["accion"]) && $_POST["accion"] == "buscar_ventas"){
    
    $buscarVentas = new AjaxSolicitudesStock();
    $buscarVentas->busquedaVenta = $_POST["busqueda"];
    $buscarVentas->ajaxBuscarVentas();
}

/*=============================================
OBTENER PRODUCTOS DE VENTA
=============================================*/
if(isset($_POST["accion"]) && $_POST["accion"] == "productos_venta"){
    
    $productosVenta = new AjaxSolicitudesStock();
    $productosVenta->codigoVenta = $_POST["codigo_venta"];
    $productosVenta->ajaxObtenerProductosVenta();
}

/*=============================================
OBTENER PRODUCTO
=============================================*/
if(isset($_POST["idProducto"])){
    
    $producto = new AjaxSolicitudesStock();
    $producto->idProducto = $_POST["idProducto"];
    $producto->ajaxObtenerProducto();
}

?>