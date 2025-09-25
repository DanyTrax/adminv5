<?php

session_start();

require_once "../controladores/productos.controlador.php";
require_once "../modelos/productos.modelo.php";

class AjaxProductosDespacho {

/*=============================================
OBTENER INVENTARIO LOCAL PARA DESPACHO - CORREGIDO
=============================================*/
public function ajaxObtenerInventarioLocal() {
    
    try {
        require_once "../modelos/conexion.php";
        
        $stmt = Conexion::conectar()->prepare("
            SELECT 
                codigo,
                descripcion,
                stock,
                precio_venta,
                imagen
            FROM productos 
            WHERE stock > 0
            ORDER BY descripcion ASC
        ");
        
        $stmt->execute();
        $productos = $stmt->fetchAll();
        
        echo json_encode([
            "success" => true,
            "productos" => $productos,
            "total" => count($productos)
        ]);
        
    } catch(Exception $e) {
        echo json_encode([
            "success" => false,
            "error" => "Error cargando inventario: " . $e->getMessage()
        ]);
    }
}

/*=============================================
BUSCAR SOLICITUDES DE STOCK - CORREGIDO
=============================================*/
public function ajaxBuscarSolicitudes() {
    
    if(isset($_POST["termino"])) {
        
        $termino = $_POST["termino"];
        
        try {
            require_once "../api-transferencias/conexion-central.php";
            
            $stmt = ConexionCentral::conectar()->prepare("
                SELECT 
                    id,
                    codigo_solicitud,
                    nombre_usuario_solicitante,
                    nombre_sucursal_solicitante,
                    total_productos,
                    total_cantidad,
                    estado,
                    fecha_solicitud,
                    productos_solicitados
                FROM solicitudes_stock 
                WHERE estado IN ('aprobado', 'pendiente')
                AND (codigo_solicitud LIKE :termino 
                     OR nombre_usuario_solicitante LIKE :termino)
                ORDER BY fecha_solicitud DESC
                LIMIT 20
            ");
            
            $terminoBusqueda = "%" . $termino . "%";
            $stmt->bindParam(":termino", $terminoBusqueda, PDO::PARAM_STR);
            $stmt->execute();
            
            $solicitudes = $stmt->fetchAll();
            
            echo json_encode([
                "success" => true,
                "solicitudes" => $solicitudes,
                "total" => count($solicitudes)
            ]);
            
        } catch(Exception $e) {
            echo json_encode([
                "success" => false,
                "error" => "Error buscando solicitudes: " . $e->getMessage()
            ]);
        }
    }
}

    /*=============================================
    OBTENER DETALLE DE SOLICITUD
    =============================================*/
    public function ajaxObtenerDetalleSolicitud() {
        
        if(isset($_POST["idSolicitud"])) {
            
            $idSolicitud = intval($_POST["idSolicitud"]);
            
            try {
                require_once "../api-transferencias/conexion-central.php";
                
                $stmt = ConexionCentral::conectar()->prepare("
                    SELECT * FROM solicitudes_stock 
                    WHERE id = :id
                ");
                
                $stmt->bindParam(":id", $idSolicitud, PDO::PARAM_INT);
                $stmt->execute();
                
                $solicitud = $stmt->fetch();
                
                if($solicitud) {
                    echo json_encode([
                        "success" => true,
                        "solicitud" => $solicitud
                    ]);
                } else {
                    echo json_encode([
                        "success" => false,
                        "error" => "Solicitud no encontrada"
                    ]);
                }
                
            } catch(Exception $e) {
                echo json_encode([
                    "success" => false,
                    "error" => "Error obteniendo solicitud: " . $e->getMessage()
                ]);
            }
        }
    }

    /*=============================================
    VALIDAR STOCK DE PRODUCTO - CORREGIDO
    =============================================*/
    public function ajaxValidarStock() {
        
        if(isset($_POST["codigoProducto"]) && isset($_POST["cantidad"])) {
            
            $codigoProducto = $_POST["codigoProducto"];
            $cantidad = intval($_POST["cantidad"]);
            
            try {
                require_once "../modelos/conexion.php";
                
                $stmt = Conexion::conectar()->prepare("
                    SELECT stock FROM productos 
                    WHERE codigo = :codigo
                ");
                
                $stmt->bindParam(":codigo", $codigoProducto, PDO::PARAM_STR);
                $stmt->execute();
                
                $producto = $stmt->fetch();
                
                if($producto) {
                    $stockDisponible = intval($producto["stock"]);
                    
                    echo json_encode([
                        "success" => true,
                        "stock_disponible" => $stockDisponible,
                        "cantidad_solicitada" => $cantidad,
                        "valido" => $cantidad <= $stockDisponible && $cantidad > 0,
                        "mensaje" => $cantidad > $stockDisponible ? 
                                "Stock insuficiente. Disponible: " . $stockDisponible : 
                                "Stock suficiente"
                    ]);
                } else {
                    echo json_encode([
                        "success" => false,
                        "error" => "Producto no encontrado"
                    ]);
                }
                
            } catch(Exception $e) {
                echo json_encode([
                    "success" => false,
                    "error" => "Error validando stock: " . $e->getMessage()
                ]);
            }
        }
    }
}

/*=============================================
PROCESAR PETICIONES
=============================================*/
if(isset($_POST["obtenerInventarioLocal"])) {
    $inventario = new AjaxProductosDespacho();
    $inventario->ajaxObtenerInventarioLocal();
}

if(isset($_POST["buscarSolicitudes"])) {
    $buscar = new AjaxProductosDespacho();
    $buscar->ajaxBuscarSolicitudes();
}

if(isset($_POST["obtenerDetalleSolicitud"])) {
    $detalle = new AjaxProductosDespacho();
    $detalle->ajaxObtenerDetalleSolicitud();
}

if(isset($_POST["validarStock"])) {
    $validar = new AjaxProductosDespacho();
    $validar->ajaxValidarStock();
}

?>