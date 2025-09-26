<?php

session_start();

// Verificar permisos
if($_SESSION["perfil"] != "Administrador" && $_SESSION["perfil"] != "Transportador") {
    echo json_encode(['success' => false, 'message' => 'Sin permisos']);
    exit;
}

class AjaxDespachos {
    
    /*=============================================
    CARGAR PRODUCTOS DEL INVENTARIO LOCAL
    =============================================*/
    public function ajaxCargarInventario() {
        
        if(isset($_POST["cargarInventario"])) {
            
            try {
                require_once "../modelos/conexion.php";
                
                $stmt = Conexion::conectar()->prepare("
                    SELECT codigo, descripcion, stock, precio_venta 
                    FROM productos 
                    WHERE estado = 1 
                    ORDER BY descripcion ASC
                ");
                
                $stmt->execute();
                $productos = $stmt->fetchAll();
                
                echo json_encode($productos);
                
            } catch(Exception $e) {
                echo json_encode([]);
            }
        }
    }

    /*=============================================
    OBTENER DESPACHO PARA EDITAR
    =============================================*/
    public function ajaxObtenerDespachoEditar() {
        
        if(isset($_POST["idDespachoEditar"])) {
            
            $item = "id";
            $valor = $_POST["idDespachoEditar"];
            
            $respuesta = ControladorDespachos::ctrMostrarDespachos($item, $valor);
            
            // Solo permitir edición de despachos pendientes
            if($respuesta && $respuesta["estado"] == "pendiente") {
                echo json_encode($respuesta);
            } else {
                echo json_encode(["error" => "No se puede editar este despacho"]);
            }
        }
    }
    
    /*=============================================
    CREAR NUEVO DESPACHO
    =============================================*/
    public function ajaxCrearDespacho() {
        
        if(isset($_POST["crearDespacho"])) {
            
            try {
                require_once "../controladores/despachos.controlador.php";
                require_once "../modelos/despachos.modelo.php";
                
                // Validar datos requeridos
                $productosJson = $_POST["productos_despacho"] ?? '';
                $totalProductos = $_POST["total_productos"] ?? 0;
                $totalCantidad = $_POST["total_cantidad"] ?? 0;
                $tipoDespacho = $_POST["tipo_despacho"] ?? 'libre';
                $observaciones = $_POST["observaciones"] ?? '';
                $idSolicitudOrigen = $_POST["id_solicitud_origen"] ?? null;
                
                if(empty($productosJson)) {
                    throw new Exception("No se enviaron productos para el despacho");
                }
                
                // Validar JSON de productos
                $productos = json_decode($productosJson, true);
                if(!$productos || !is_array($productos)) {
                    throw new Exception("Error en formato de productos");
                }
                
                // Preparar datos para el controlador
                $datos = array(
                    "numero_despacho" => null, // Se genera automáticamente
                    "id_sucursal_origen" => $_SESSION["id_sucursal"],
                    "nombre_sucursal_origen" => $_SESSION["nombre_sucursal"],
                    "id_usuario_creador" => $_SESSION["id"],
                    "nombre_usuario_creador" => $_SESSION["nombre"],
                    "tipo_despacho" => $tipoDespacho,
                    "productos_despacho" => $productosJson,
                    "total_productos" => $totalProductos,
                    "total_cantidad" => $totalCantidad,
                    "estado" => "pendiente",
                    "detalle_adicional" => $observaciones,
                    "id_solicitud_origen" => $idSolicitudOrigen,
                    "fecha_creacion" => date("Y-m-d H:i:s"),
                    "fecha_aceptacion" => null,
                    "id_transportador_asignado" => null,
                    "nombre_transportador_asignado" => null
                );
                
                // Crear el despacho usando el controlador
                $respuesta = ControladorDespachos::ctrCrearDespacho($datos);
                
                if($respuesta == "ok") {
                    
                    // Obtener el número del despacho recién creado
                    $ultimoDespacho = ControladorDespachos::ctrObtenerUltimoDespacho();
                    $numeroDespacho = $ultimoDespacho ? $ultimoDespacho["numero_despacho"] : "DEP" . date("YmdHis");
                    
                    echo json_encode([
                        "success" => true,
                        "message" => "Despacho creado exitosamente",
                        "numero_despacho" => $numeroDespacho,
                        "id_despacho" => $ultimoDespacho ? $ultimoDespacho["id"] : null
                    ]);
                    
                } else {
                    throw new Exception("Error al crear el despacho: " . $respuesta);
                }
                
            } catch(Exception $e) {
                echo json_encode([
                    "success" => false,
                    "error" => $e->getMessage()
                ]);
            }
        }
    }
}

// MANEJO DE PETICIONES POST
if(isset($_POST["cargarInventario"])) {
    $cargarInventario = new AjaxDespachos();
    $cargarInventario->ajaxCargarInventario();
}

if(isset($_POST["idDespachoEditar"])) {
    $obtenerEditar = new AjaxDespachos();
    $obtenerEditar->ajaxObtenerDespachoEditar();
}

if(isset($_POST["crearDespacho"])) {
    $crearDespacho = new AjaxDespachos();
    $crearDespacho->ajaxCrearDespacho();
}

// RESTO DEL CÓDIGO ORIGINAL PARA REPORTES...
if(!class_exists('AjaxDespachos')) {
    
try {
    require_once "../api-transferencias/conexion-central.php";
    
    // Obtener filtros
    $fechaDesde = $_POST['fechaDesde'] ?? date('Y-m-d', strtotime('-30 days'));
    $fechaHasta = $_POST['fechaHasta'] ?? date('Y-m-d');
    $estado = $_POST['estado'] ?? '';
    $transportador = $_POST['transportador'] ?? '';
    
    // ... resto del código de reportes
    
} catch(Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al generar reporte: ' . $e->getMessage()
    ]);
}
}
?>