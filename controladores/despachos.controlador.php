<?php

class ControladorDespachos {

/*=============================================
CREAR DESPACHO - VERSIÓN CORREGIDA
=============================================*/
static public function ctrCrearDespacho($datos = null) {

    if($datos) {
        
        try {
            // Generar número de despacho único
            $numeroDespacho = ModeloDespachos::mdlGenerarNumeroDespacho();

            // Preparar datos para el modelo
            $datosModelo = array(
                "numero_despacho" => $numeroDespacho,
                "id_solicitud_origen" => $datos["id_solicitud_origen"],
                "nombre_sucursal_origen" => self::obtenerSucursalConDebug(),
                "id_usuario_creador" => $datos["id_usuario_creador"],
                "nombre_usuario_creador" => $datos["nombre_usuario_creador"],
                "productos_despacho" => $datos["productos_despacho"],
                "total_productos" => $datos["total_productos"],
                "total_cantidad" => $datos["total_cantidad"],
                "detalle_adicional" => $datos["detalle_adicional"]
            );

            // Debug: Log datos preparados
            error_log("🔍 Controlador - datos para modelo: " . print_r($datosModelo, true));

            $tabla = "despachos";
            $respuesta = ModeloDespachos::mdlCrearDespacho($tabla, $datosModelo);

            // Debug: Log respuesta del modelo
            error_log("🔍 Controlador - respuesta del modelo: " . print_r($respuesta, true));

            if($respuesta && $respuesta != "error" && is_numeric($respuesta)) {
                error_log("✅ Controlador - Despacho creado con ID: " . $respuesta);
                return "ok";
            } else {
                error_log("❌ Controlador - Error del modelo: " . $respuesta);
                return "error: " . $respuesta;
            }

        } catch(Exception $e) {
            error_log("❌ Controlador - Excepción: " . $e->getMessage());
            return "error: " . $e->getMessage();
        }
    }
    
    // ... resto del método para POST
}

/*=============================================
OBTENER ÚLTIMO DESPACHO CREADO
=============================================*/
static public function ctrObtenerUltimoDespacho() {
    
    $tabla = "despachos";
    $despachos = ModeloDespachos::mdlMostrarDespachos($tabla, null, null);
    
    if($despachos && count($despachos) > 0) {
        return $despachos[0]; // El primero es el más reciente
    }
    
    return null;
}

    /*=============================================
    MOSTRAR DESPACHOS
    =============================================*/
    static public function ctrMostrarDespachos($item, $valor) {
        
        $tabla = "despachos";
        $respuesta = ModeloDespachos::mdlMostrarDespachos($tabla, $item, $valor);
        return $respuesta;
    }

/*=============================================
EDITAR DESPACHO - VERSIÓN CORREGIDA
=============================================*/
public function ctrEditarDespacho($datos = null) {
    
    // Verificar si llegaron datos de edición
    if($datos && (isset($datos["editarDespacho"]) || isset($datos["idDespachoEditar"]))) {
        
        try {
            require_once "modelos/despachos.modelo.php";
            require_once "api-transferencias/conexion-central.php";
            
            $idDespacho = $datos["idDespachoEditar"];
            
            error_log("🔄 EDITANDO DESPACHO ID: " . $idDespacho);
            error_log("📦 Datos recibidos: " . print_r($datos, true));
            
            // Verificar que el despacho existe y está pendiente
            $stmt = ConexionCentral::conectar()->prepare("SELECT * FROM despachos WHERE id = :id");
            $stmt->bindParam(":id", $idDespacho);
            $stmt->execute();
            $despachoActual = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if(!$despachoActual) {
                throw new Exception("Despacho no encontrado");
            }
            
            if($despachoActual["estado"] != "pendiente") {
                throw new Exception("Solo se pueden editar despachos pendientes. Estado actual: " . $despachoActual["estado"]);
            }
            
            // Preparar datos actualizados
            $productosDespacho = $datos["productosDespacho"];
            $totalProductos = intval($datos["totalProductos"]);
            $totalCantidad = intval($datos["totalCantidad"]);
            $detalleAdicional = $datos["detalleAdicional"] ?? '';
            
            error_log("📊 Productos: " . $totalProductos . " - Cantidad total: " . $totalCantidad);
            
            // Actualizar directamente en la base de datos
            $stmtUpdate = ConexionCentral::conectar()->prepare("
                UPDATE despachos SET 
                    productos_despacho = :productos_despacho,
                    total_productos = :total_productos,
                    total_cantidad = :total_cantidad,
                    detalle_adicional = :detalle_adicional,
                    fecha_actualizacion = NOW()
                WHERE id = :id
            ");
            
            $stmtUpdate->bindParam(":productos_despacho", $productosDespacho);
            $stmtUpdate->bindParam(":total_productos", $totalProductos);
            $stmtUpdate->bindParam(":total_cantidad", $totalCantidad);
            $stmtUpdate->bindParam(":detalle_adicional", $detalleAdicional);
            $stmtUpdate->bindParam(":id", $idDespacho);
            
            if($stmtUpdate->execute()) {
                
                $filasAfectadas = $stmtUpdate->rowCount();
                error_log("✅ Despacho actualizado exitosamente. Filas afectadas: " . $filasAfectadas);
                
                echo '<script>
                    swal({
                        title: "¡Despacho actualizado!",
                        text: "Los cambios han sido guardados correctamente",
                        type: "success",
                        confirmButtonText: "Ver despachos"
                    }).then(function() {
                        window.location = "despachos";
                    });
                </script>';
                
            } else {
                $errorInfo = $stmtUpdate->errorInfo();
                throw new Exception("Error en la actualización: " . print_r($errorInfo, true));
            }
            
        } catch(Exception $e) {
            error_log("❌ Error editando despacho: " . $e->getMessage());
            
            echo '<script>
                swal({
                    title: "Error",
                    text: "No se pudieron guardar los cambios: ' . htmlspecialchars($e->getMessage()) . '",
                    type: "error",
                    confirmButtonText: "Cerrar"
                });
            </script>';
        }
    } else {
        error_log("⚠️ No se recibieron datos de edición válidos");
        error_log("📦 POST data: " . print_r($_POST, true));
    }
}

    /*=============================================
    ELIMINAR DESPACHO
    =============================================*/
    static public function ctrEliminarDespacho() {

        if(isset($_GET["idDespacho"])) {

            // Verificar permisos (solo administradores pueden eliminar)
            if($_SESSION["perfil"] != "Administrador") {
                echo '<script>
                    swal({
                        type: "error",
                        title: "Sin permisos",
                        text: "Solo los administradores pueden eliminar despachos",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    }).then(function() {
                        window.location = "despachos";
                    });
                </script>';
                return;
            }

            // Solo se pueden eliminar despachos pendientes
            $despacho = self::ctrMostrarDespachos("id", $_GET["idDespacho"]);
            
            if($despacho["estado"] != "pendiente") {
                echo '<script>
                    swal({
                        type: "error",
                        title: "No se puede eliminar",
                        text: "Solo se pueden eliminar despachos en estado pendiente",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    }).then(function() {
                        window.location = "despachos";
                    });
                </script>';
                return;
            }

            $tabla = "despachos";
            $datos = $_GET["idDespacho"];

            $respuesta = ModeloDespachos::mdlEliminarDespacho($tabla, $datos);

            if($respuesta == "ok") {

                // Si tenía una solicitud origen, revertir su estado
                if(!empty($despacho["id_solicitud_origen"])) {
                    self::actualizarEstadoSolicitudStock($despacho["id_solicitud_origen"], "pendiente");
                }

                echo '<script>
                    swal({
                        type: "success",
                        title: "¡Despacho eliminado!",
                        text: "El despacho se ha eliminado correctamente",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    }).then(function(result) {
                        if (result.value) {
                            window.location = "despachos";
                        }
                    });
                </script>';

            } else {
                echo '<script>
                    swal({
                        type: "error",
                        title: "Error",
                        text: "Error al eliminar el despacho",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    });
                </script>';
            }
        }
    }

    /*=============================================
    CAMBIAR ESTADO DE DESPACHO
    =============================================*/
    static public function ctrCambiarEstadoDespacho() {

        if(isset($_POST["cambiarEstado"])) {

            $idDespacho = $_POST["idDespacho"];
            $nuevoEstado = $_POST["nuevoEstado"];
            $observaciones = $_POST["observaciones"] ?? null;

            // Validar estados permitidos
            $estadosPermitidos = ["pendiente", "en_transito", "entregado", "cancelado"];
            
            if(!in_array($nuevoEstado, $estadosPermitidos)) {
                echo '<script>
                    swal({
                        type: "error",
                        title: "Estado inválido",
                        text: "El estado especificado no es válido",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    });
                </script>';
                return;
            }

            // Validar permisos según el estado
            if($nuevoEstado == "en_transito" && $_SESSION["perfil"] != "Transportador") {
                echo '<script>
                    swal({
                        type: "error",
                        title: "Sin permisos",
                        text: "Solo los transportadores pueden poner despachos en tránsito",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    });
                </script>';
                return;
            }

            try {
                
                // Si el estado cambia a "en_transito", mover productos a stock en tránsito
                if($nuevoEstado == "en_transito") {
                    $resultado = self::procesarDespachoEnTransito($idDespacho);
                    
                    if(!$resultado["exito"]) {
                        throw new Exception($resultado["mensaje"]);
                    }
                }

                $tabla = "despachos";
                $datos = array(
                    "id" => $idDespacho,
                    "estado" => $nuevoEstado,
                    "observaciones" => $observaciones,
                    "transportador_id" => $_SESSION["perfil"] == "Transportador" ? $_SESSION["id"] : null,
                    "nombre_transportador" => $_SESSION["perfil"] == "Transportador" ? $_SESSION["nombre"] : null
                );

                $respuesta = ModeloDespachos::mdlCambiarEstadoDespacho($tabla, $datos);

                if($respuesta == "ok") {

                    $mensajeEstado = self::obtenerMensajeEstado($nuevoEstado);

                    echo '<script>
                        swal({
                            type: "success",
                            title: "¡Estado actualizado!",
                            text: "' . $mensajeEstado . '",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        }).then(function(result) {
                            if (result.value) {
                                window.location = "despachos";
                            }
                        });
                    </script>';

                } else {
                    throw new Exception("Error al cambiar el estado del despacho");
                }

            } catch(Exception $e) {
                echo '<script>
                    swal({
                        type: "error",
                        title: "Error",
                        text: "' . $e->getMessage() . '",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    });
                </script>';
            }
        }
    }

    /*=============================================
    GENERAR NÚMERO DE DESPACHO ÚNICO
    =============================================*/
    static private function generarNumeroDespacho() {
        
        $prefijo = "DESP-";
        $fecha = date("Ymd");
        $contador = 1;
        
        do {
            $numero = $prefijo . $fecha . "-" . str_pad($contador, 3, '0', STR_PAD_LEFT);
            
            // Verificar si existe
            $existente = self::ctrMostrarDespachos("numero_despacho", $numero);
            
            if(!$existente) {
                return $numero;
            }
            
            $contador++;
            
        } while($contador <= 999); // Límite de seguridad
        
        // Si llegamos aquí, usar timestamp
        return $prefijo . $fecha . "-" . time();
    }

    /*=============================================
    OBTENER SUCURSAL LOCAL
    =============================================*/
    static private function obtenerSucursalLocal() {
        
        try {
            require_once "controladores/sucursales.controlador.php";
            $sucursalLocal = ControladorSucursales::ctrObtenerConfiguracionLocal();
            
            return $sucursalLocal ? $sucursalLocal['nombre'] : 'Sucursal Local';
            
        } catch(Exception $e) {
            return 'Sucursal Local';
        }
    }

    /*=============================================
    ACTUALIZAR ESTADO DE SOLICITUD DE STOCK
    =============================================*/
    static private function actualizarEstadoSolicitudStock($idSolicitud, $nuevoEstado) {
        
        try {
            require_once "api-transferencias/conexion-central.php";
            
            $stmt = ConexionCentral::conectar()->prepare("
                UPDATE solicitudes_stock 
                SET estado = :estado,
                    fecha_actualizacion = CURRENT_TIMESTAMP 
                WHERE id = :id
            ");
            
            $stmt->bindParam(":estado", $nuevoEstado, PDO::PARAM_STR);
            $stmt->bindParam(":id", $idSolicitud, PDO::PARAM_INT);
            
            return $stmt->execute();
            
        } catch(Exception $e) {
            return false;
        }
    }

    /*=============================================
    OBTENER MENSAJE SEGÚN ESTADO
    =============================================*/
    static private function obtenerMensajeEstado($estado) {
        
        $mensajes = [
            "pendiente" => "El despacho ha vuelto a estado pendiente",
            "en_transito" => "El despacho está ahora en tránsito y los productos han sido cargados",
            "entregado" => "El despacho ha sido marcado como entregado",
            "cancelado" => "El despacho ha sido cancelado"
        ];
        
        return $mensajes[$estado] ?? "Estado del despacho actualizado";
    }

    /*=============================================
    OBTENER TRANSPORTADORES ACTIVOS
    =============================================*/
    static public function ctrObtenerTransportadores() {
        
        try {
            require_once "modelos/conexion.php";
            
            $stmt = Conexion::conectar()->prepare("
                SELECT id, nombre, usuario 
                FROM usuarios 
                WHERE perfil = 'Transportador' 
                AND estado = 1 
                ORDER BY nombre ASC
            ");
            
            $stmt->execute();
            return $stmt->fetchAll();
            
        } catch(Exception $e) {
            return [];
        }
    }

    /*=============================================
    OBTENER DESPACHOS POR ESTADO
    =============================================*/
    static public function ctrObtenerDespachosPorEstado($estado) {
        
        $tabla = "despachos";
        return ModeloDespachos::mdlObtenerDespachosPorEstado($tabla, $estado);
    }

    /*=============================================
    OBTENER ESTADÍSTICAS DE DESPACHOS
    =============================================*/
    static public function ctrObtenerEstadisticasDespachos() {
        
        try {
            require_once "modelos/conexion.php";
            
            $stmt = Conexion::conectar()->prepare("
                SELECT 
                    COUNT(*) as total_despachos,
                    COUNT(CASE WHEN estado = 'pendiente' THEN 1 END) as pendientes,
                    COUNT(CASE WHEN estado = 'en_transito' THEN 1 END) as en_transito,
                    COUNT(CASE WHEN estado = 'entregado' THEN 1 END) as entregados,
                    COUNT(CASE WHEN estado = 'cancelado' THEN 1 END) as cancelados,
                    SUM(total_productos) as productos_totales,
                    SUM(total_cantidad) as cantidad_total
                FROM despachos 
                WHERE DATE(fecha_creacion) >= DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY)
            ");
            
            $stmt->execute();
            $estadisticas = $stmt->fetch();
            
            return $estadisticas;
            
        } catch(Exception $e) {
            return [
                "total_despachos" => 0,
                "pendientes" => 0,
                "en_transito" => 0,
                "entregados" => 0,
                "cancelados" => 0,
                "productos_totales" => 0,
                "cantidad_total" => 0
            ];
        }
    }

    /*=============================================
    BUSCAR PRODUCTOS PARA DESPACHO
    =============================================*/
    static public function ctrBuscarProductosDespacho($termino) {
        
        try {
            require_once "modelos/conexion.php";
            
            $stmt = Conexion::conectar()->prepare("
                SELECT codigo, descripcion, stock, precio_venta
                FROM productos 
                WHERE estado = 1 
                AND (codigo LIKE :termino OR descripcion LIKE :termino)
                ORDER BY descripcion ASC
                LIMIT 20
            ");
            
            $terminoBusqueda = "%" . $termino . "%";
            $stmt->bindParam(":termino", $terminoBusqueda, PDO::PARAM_STR);
            $stmt->execute();
            
            return $stmt->fetchAll();
            
        } catch(Exception $e) {
            return [];
        }
    }

    /*=============================================
    VALIDAR STOCK PARA DESPACHO
    =============================================*/
    static public function ctrValidarStockDespacho($codigoProducto, $cantidadSolicitada) {
        
        try {
            require_once "modelos/conexion.php";
            
            $stmt = Conexion::conectar()->prepare("
                SELECT stock FROM productos 
                WHERE codigo = :codigo AND estado = 1
            ");
            
            $stmt->bindParam(":codigo", $codigoProducto, PDO::PARAM_STR);
            $stmt->execute();
            
            $producto = $stmt->fetch();
            
            if($producto) {
                return [
                    "disponible" => $producto["stock"] >= $cantidadSolicitada,
                    "stock_actual" => intval($producto["stock"]),
                    "cantidad_solicitada" => intval($cantidadSolicitada),
                    "diferencia" => intval($producto["stock"]) - intval($cantidadSolicitada)
                ];
            } else {
                return [
                    "disponible" => false,
                    "stock_actual" => 0,
                    "cantidad_solicitada" => intval($cantidadSolicitada),
                    "diferencia" => -intval($cantidadSolicitada)
                ];
            }
            
        } catch(Exception $e) {
            return [
                "disponible" => false,
                "stock_actual" => 0,
                "cantidad_solicitada" => intval($cantidadSolicitada),
                "diferencia" => -intval($cantidadSolicitada),
                "error" => $e->getMessage()
            ];
        }
    }
    /*=============================================
    ACEPTAR DESPACHO
    =============================================*/
    static public function ctrAceptarDespacho() {
        
        if(isset($_POST["aceptarDespacho"])) {
            
            $tabla = "despachos";
            $item1 = "id";
            $valor1 = $_POST["idDespacho"];
            
            $datos = array(
                "estado" => "aceptado",
                "fecha_aceptacion" => date("Y-m-d H:i:s"),
                "id_transportador_asignado" => $_SESSION["id"],
                "nombre_transportador_asignado" => $_SESSION["nombre"]
            );
            
            $respuesta = ModeloDespachos::mdlActualizarDespacho($tabla, $datos, $item1, $valor1);
            
            if($respuesta == "ok") {
                
                echo '<script>
                    swal({
                        type: "success",
                        title: "¡Despacho aceptado!",
                        text: "El despacho ha sido aceptado correctamente",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    }).then(function(result) {
                        if (result.value) {
                            window.location = "despachos";
                        }
                    });
                </script>';
                
            } else {
                
                echo '<script>
                    swal({
                        type: "error",
                        title: "Error",
                        text: "Error al aceptar el despacho",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    });
                </script>';
            }
        }
    }
    /*=============================================
    CANCELAR DESPACHO
    =============================================*/
    static public function ctrCancelarDespacho() {
        
        if(isset($_POST["cancelarDespacho"])) {
            
            $tabla = "despachos";
            $item1 = "id";
            $valor1 = $_POST["idDespacho"];
            
            $datos = array(
                "estado" => "cancelado",
                "fecha_cancelacion" => date("Y-m-d H:i:s"),
                "motivo_cancelacion" => $_POST["motivoCancelacion"] ?? "Sin motivo especificado"
            );
            
            $respuesta = ModeloDespachos::mdlActualizarDespacho($tabla, $datos, $item1, $valor1);
            
            if($respuesta == "ok") {
                
                echo '<script>
                    swal({
                        type: "success",
                        title: "¡Despacho cancelado!",
                        text: "El despacho ha sido cancelado correctamente",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    }).then(function(result) {
                        if (result.value) {
                            window.location = "despachos";
                        }
                    });
                </script>';
                
            } else {
                
                echo '<script>
                    swal({
                        type: "error",
                        title: "Error",
                        text: "Error al cancelar el despacho",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    });
                </script>';
            }
        }
    }
    /*=============================================
    BORRAR DESPACHO
    =============================================*/
    static public function ctrBorrarDespacho() {
        
        if(isset($_POST["borrarDespacho"])) {
            
            $tabla = "despachos";
            $item = "id";
            $valor = $_POST["idDespacho"];
            
            $respuesta = ModeloDespachos::mdlBorrarDespacho($tabla, $item, $valor);
            
            if($respuesta == "ok") {
                
                echo '<script>
                    swal({
                        type: "success",
                        title: "¡Despacho eliminado!",
                        text: "El despacho ha sido eliminado correctamente",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    }).then(function(result) {
                        if (result.value) {
                            window.location = "despachos";
                        }
                    });
                </script>';
                
            } else {
                
                echo '<script>
                    swal({
                        type: "error",
                        title: "Error",
                        text: "Error al eliminar el despacho",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    });
                </script>';
            }
        }
    }

    /*=============================================
    FUNCIÓN PARA LIMPIAR STOCK EN TRÁNSITO CUANDO CANTIDAD = 0
    =============================================*/
    static public function limpiarStockTransitoCero() {
        
        try {
            require_once "api-transferencias/conexion-central.php";
            
            $conexion = ConexionCentral::conectar();
            
            // Eliminar registros con cantidad 0 o menor
            $stmt = $conexion->prepare("DELETE FROM stock_transito WHERE cantidad_disponible <= 0");
            $stmt->execute();
            
            $registrosEliminados = $stmt->rowCount();
            
            if ($registrosEliminados > 0) {
                error_log("🧹 Limpieza automática: {$registrosEliminados} registros eliminados del stock en tránsito");
            }
            
            return $registrosEliminados;
            
        } catch (Exception $e) {
            error_log("❌ Error limpiando stock en tránsito: " . $e->getMessage());
            return 0;
        }
    }

    /*=============================================
    ACTUALIZAR STOCK EN TRÁNSITO AL APROBAR DESPACHO
    =============================================*/
    static public function actualizarStockTransitoDespacho($numeroDespacho, $productos) {
        
        try {
            require_once "api-transferencias/conexion-central.php";
            
            $conexion = ConexionCentral::conectar();
            $conexion->beginTransaction();
            
            foreach ($productos as $producto) {
                
                $codigoProducto = $producto['codigo'];
                $cantidadDespacho = intval($producto['cantidad']);
                $descripcionProducto = $producto['descripcion'];
                
                // Buscar si ya existe el producto en stock_transito
                $stmt = $conexion->prepare("
                    SELECT id, cantidad_disponible 
                    FROM stock_transito 
                    WHERE codigo_producto = ? 
                    AND numero_despacho_origen = ?
                ");
                $stmt->execute([$codigoProducto, $numeroDespacho]);
                $stockExistente = $stmt->fetch();
                
                if ($stockExistente) {
                    // Sumar a la cantidad existente
                    $nuevaCantidad = $stockExistente['cantidad_disponible'] + $cantidadDespacho;
                    $stmt = $conexion->prepare("
                        UPDATE stock_transito 
                        SET cantidad_disponible = ?,
                            fecha_actualizacion = NOW()
                        WHERE id = ?
                    ");
                    $stmt->execute([$nuevaCantidad, $stockExistente['id']]);
                    
                } else {
                    // Crear nuevo registro
                    $stmt = $conexion->prepare("
                        INSERT INTO stock_transito (
                            codigo_producto, descripcion_producto, cantidad_disponible,
                            transportador_id, nombre_transportador, sucursal_origen,
                            numero_despacho_origen, fecha_carga
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
                    ");
                    
                    $stmt->execute([
                        $codigoProducto,
                        $descripcionProducto,
                        $cantidadDespacho,
                        $_SESSION['id'],
                        $_SESSION['nombre'] ?? 'Administrador',
                        $_SESSION['sucursal'] ?? 'Sucursal Principal',
                        $numeroDespacho
                    ]);
                }
            }
            
            $conexion->commit();
            
            // Limpiar registros con cantidad 0
            self::limpiarStockTransitoCero();
            
            return true;
            
        } catch (Exception $e) {
            if (isset($conexion)) {
                $conexion->rollBack();
            }
            error_log("❌ Error actualizando stock en tránsito: " . $e->getMessage());
            return false;
        }
    }
/*=============================================
PROCESAR DESPACHO EN TRÁNSITO - AGREGAR A STOCK TRANSITO
=============================================*/
static public function procesarDespachoEnTransito($idDespacho) {
    
    try {
        require_once "api-transferencias/conexion-central.php";
        
        $conexion = ConexionCentral::conectar();
        $conexion->beginTransaction();
        
        // 1. Obtener datos del despacho
        $stmt = $conexion->prepare("SELECT * FROM despachos WHERE id = ?");
        $stmt->execute([$idDespacho]);
        $despacho = $stmt->fetch();
        
        if (!$despacho) {
            throw new Exception("Despacho no encontrado");
        }
        
        // 2. Decodificar productos del despacho
        $productosDespacho = json_decode($despacho['productos_despacho'], true);
        
        if (!$productosDespacho || !is_array($productosDespacho)) {
            throw new Exception("No se pudieron decodificar los productos del despacho");
        }
        
        error_log("🚛 Procesando despacho #{$despacho['numero_despacho']} con " . count($productosDespacho) . " productos");
        
        // 3. Procesar cada producto y agregarlo al stock en tránsito
        foreach ($productosDespacho as $producto) {
            
            $codigoProducto = $producto['codigo'];
            $cantidadDespacho = intval($producto['cantidad']);
            $descripcionProducto = $producto['descripcion'] ?? $producto['nombre'] ?? 'Producto sin descripción';
            
            // Verificar si ya existe en stock_transito del mismo despacho
            $stmt = $conexion->prepare("
                SELECT id, cantidad_disponible 
                FROM stock_transito 
                WHERE codigo_producto = ? 
                AND numero_despacho_origen = ?
                AND transportador_id = ?
            ");
            $stmt->execute([
                $codigoProducto, 
                $despacho['numero_despacho'],
                $_SESSION['id']
            ]);
            $stockExistente = $stmt->fetch();
            
            if ($stockExistente) {
                // Si ya existe, sumar a la cantidad existente
                $nuevaCantidad = $stockExistente['cantidad_disponible'] + $cantidadDespacho;
                $stmt = $conexion->prepare("
                    UPDATE stock_transito 
                    SET cantidad_disponible = ?
                    WHERE id = ?
                ");
                $stmt->execute([$nuevaCantidad, $stockExistente['id']]);
                
                error_log("✅ Stock actualizado - Código: {$codigoProducto}, Nueva cantidad: {$nuevaCantidad}");
                
            } else {
                // Crear nuevo registro en stock_transito
                $stmt = $conexion->prepare("
                    INSERT INTO stock_transito (
                        codigo_producto, descripcion_producto, cantidad_disponible,
                        transportador_id, nombre_transportador, sucursal_origen,
                        numero_despacho_origen, fecha_carga, id_despacho_origen
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), ?)
                ");
                
                $nombreTransportador = $_SESSION['nombre'] ?? 'Transportador';
                $sucursalOrigen = $_SESSION['sucursal'] ?? $despacho['nombre_sucursal_origen'] ?? 'Sucursal Principal';
                
                $stmt->execute([
                    $codigoProducto,
                    $descripcionProducto,
                    $cantidadDespacho,
                    $_SESSION['id'],
                    $nombreTransportador,
                    $sucursalOrigen,
                    $despacho['numero_despacho'],
                    $idDespacho
                ]);
                
                error_log("✅ Nuevo stock creado - Código: {$codigoProducto}, Cantidad: {$cantidadDespacho}");
            }
        }
        
        $conexion->commit();
        
        error_log("🎉 Despacho #{$despacho['numero_despacho']} procesado exitosamente en stock tránsito");
        
        return [
            "exito" => true, 
            "mensaje" => "Despacho procesado y productos agregados al stock en tránsito exitosamente"
        ];
        
    } catch (Exception $e) {
        if (isset($conexion)) {
            $conexion->rollBack();
        }
        error_log("❌ Error procesando despacho en tránsito: " . $e->getMessage());
        return [
            "exito" => false, 
            "mensaje" => "Error: " . $e->getMessage()
        ];
    }
}
/*=============================================
FUNCIÓN DEBUG TEMPORAL PARA SUCURSAL
=============================================*/
static public function obtenerSucursalConDebug(){
    
    error_log("🔍 DEBUG: Iniciando obtención de sucursal local...");
    
    try {
        // Verificar si la función existe en el modelo
        if(!method_exists('ModeloDespachos', 'mdlObtenerSucursalLocal')) {
            error_log("❌ La función mdlObtenerSucursalLocal NO existe en ModeloDespachos");
            return "Sucursal Local";
        }
        
        error_log("✅ La función mdlObtenerSucursalLocal SÍ existe");
        
        // Intentar llamar la función
        $resultado = ModeloDespachos::mdlObtenerSucursalLocal();
        
        error_log("✅ Función ejecutada, resultado: " . $resultado);
        
        return $resultado;
        
    } catch(Exception $e) {
        error_log("❌ Error en obtenerSucursalConDebug: " . $e->getMessage());
        error_log("📍 Línea: " . $e->getLine());
        error_log("📄 Archivo: " . $e->getFile());
        return "Sucursal Local";
    }
}
}