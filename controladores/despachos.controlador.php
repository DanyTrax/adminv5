<?php

class ControladorDespachos {

/*=============================================
CREAR DESPACHO - VERSIÓN CORREGIDA
=============================================*/
static public function ctrCrearDespacho($datos = null) {

    // Si se llama desde AJAX, usar los datos enviados
    if($datos) {
        
        try {
            // Generar número de despacho único
            $numeroDespacho = ModeloDespachos::mdlGenerarNumeroDespacho();

            // Preparar datos para el modelo (con nombres correctos)
            $datosModelo = array(
                "numero_despacho" => $numeroDespacho,
                "id_solicitud_origen" => $datos["id_solicitud_origen"],
                "nombre_sucursal_origen" => $datos["nombre_sucursal_origen"],
                "id_usuario_creador" => $datos["id_usuario_creador"],
                "nombre_usuario_creador" => $datos["nombre_usuario_creador"],
                "productos_despacho" => $datos["productos_despacho"],
                "total_productos" => $datos["total_productos"],
                "total_cantidad" => $datos["total_cantidad"],
                "detalle_adicional" => $datos["detalle_adicional"]
            );

            $tabla = "despachos";
            $respuesta = ModeloDespachos::mdlCrearDespacho($tabla, $datosModelo);

            if($respuesta && $respuesta != "error") {
                return "ok";
            } else {
                return "error: " . $respuesta;
            }

        } catch(Exception $e) {
            return "error: " . $e->getMessage();
        }
    }

    // Si se llama desde POST (formulario tradicional)
    if(isset($_POST["crearDespacho"])) {

        // Validar campos requeridos (nombres corregidos)
        if(empty($_POST["productosDespacho"]) || 
           empty($_POST["totalProductos"]) || 
           empty($_POST["totalCantidad"])) {
            
            echo '<script>
                swal({
                    type: "error",
                    title: "Campos incompletos",
                    text: "Debe agregar al menos un producto al despacho",
                    showConfirmButton: true,
                    confirmButtonText: "Cerrar"
                });
            </script>';
            return;
        }

        // Generar número de despacho único
        $numeroDespacho = ModeloDespachos::mdlGenerarNumeroDespacho();

        try {
            
            $tabla = "despachos";
            $datos = array(
                "numero_despacho" => $numeroDespacho,
                "id_solicitud_origen" => $_POST["idSolicitudOrigen"] ?? null,
                "nombre_sucursal_origen" => self::obtenerSucursalLocal(),
                "id_usuario_creador" => $_SESSION["id"],
                "nombre_usuario_creador" => $_SESSION["nombre"],
                "productos_despacho" => $_POST["productosDespacho"],
                "total_productos" => $_POST["totalProductos"],
                "total_cantidad" => $_POST["totalCantidad"],
                "detalle_adicional" => $_POST["detalleAdicional"] ?? null
            );

            $respuesta = ModeloDespachos::mdlCrearDespacho($tabla, $datos);

            if($respuesta && $respuesta != "error") {
                
                // Si viene de una solicitud de stock, actualizar el estado
                if(!empty($_POST["idSolicitudOrigen"])) {
                    self::actualizarEstadoSolicitudStock($_POST["idSolicitudOrigen"], "despachado");
                }

                echo '<script>
                    swal({
                        type: "success",
                        title: "¡Despacho creado!",
                        text: "El despacho ' . $numeroDespacho . ' se ha creado correctamente",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    }).then(function(result) {
                        if (result.value) {
                            window.location = "despachos";
                        }
                    });
                </script>';

            } else {
                throw new Exception("Error al crear el despacho: " . $respuesta);
            }

        } catch(Exception $e) {
            echo '<script>
                swal({
                    type: "error",
                    title: "Error",
                    text: "Error al crear el despacho: ' . $e->getMessage() . '",
                    showConfirmButton: true,
                    confirmButtonText: "Cerrar"
                });
            </script>';
        }
    }
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
    EDITAR DESPACHO
    =============================================*/
    static public function ctrEditarDespacho() {

        if(isset($_POST["editarDespacho"])) {

            // Solo se pueden editar despachos pendientes
            $despachoActual = self::ctrMostrarDespachos("id", $_POST["idDespacho"]);
            
            if($despachoActual["estado"] != "pendiente") {
                echo '<script>
                    swal({
                        type: "error",
                        title: "No se puede editar",
                        text: "Solo se pueden editar despachos en estado pendiente",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    });
                </script>';
                return;
            }

            try {
                
                $tabla = "despachos";
                $datos = array(
                    "id" => $_POST["idDespacho"],
                    "productos_despacho" => $_POST["productosDespacho"],
                    "total_productos" => $_POST["totalProductos"],
                    "total_cantidad" => $_POST["totalCantidad"],
                    "detalle_adicional" => $_POST["detalleAdicional"] ?? null
                );

                $respuesta = ModeloDespachos::mdlEditarDespacho($tabla, $datos);

                if($respuesta == "ok") {

                    echo '<script>
                        swal({
                            type: "success",
                            title: "¡Despacho actualizado!",
                            text: "El despacho se ha actualizado correctamente",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        }).then(function(result) {
                            if (result.value) {
                                window.location = "despachos";
                            }
                        });
                    </script>';

                } else {
                    throw new Exception("Error al actualizar el despacho");
                }

            } catch(Exception $e) {
                echo '<script>
                    swal({
                        type: "error",
                        title: "Error",
                        text: "Error al actualizar el despacho: ' . $e->getMessage() . '",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    });
                </script>';
            }
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
    PROCESAR DESPACHO EN TRÁNSITO
    =============================================*/
    static private function procesarDespachoEnTransito($idDespacho) {
        
        try {
            require_once "modelos/conexion.php";
            require_once "api-transferencias/conexion-central.php";
            
            // Obtener despacho
            $despacho = self::ctrMostrarDespachos("id", $idDespacho);
            
            if(!$despacho) {
                return ["exito" => false, "mensaje" => "Despacho no encontrado"];
            }
            
            // Decodificar productos
            $productos = json_decode($despacho["productos_despacho"], true);
            
            if(!$productos) {
                return ["exito" => false, "mensaje" => "Error al procesar productos del despacho"];
            }
            
            // Iniciar transacciones
            $conexionLocal = Conexion::conectar();
            $conexionCentral = ConexionCentral::conectar();
            
            $conexionLocal->beginTransaction();
            $conexionCentral->beginTransaction();
            
            // 1. Descontar del stock local
            foreach($productos as $producto) {
                $stmt = $conexionLocal->prepare("
                    UPDATE productos 
                    SET stock = stock - :cantidad 
                    WHERE codigo = :codigo 
                    AND stock >= :cantidad
                ");
                
                $stmt->bindParam(":cantidad", $producto["cantidad"], PDO::PARAM_INT);
                $stmt->bindParam(":codigo", $producto["codigo"], PDO::PARAM_STR);
                
                if(!$stmt->execute() || $stmt->rowCount() === 0) {
                    $conexionLocal->rollBack();
                    $conexionCentral->rollBack();
                    return ["exito" => false, "mensaje" => "Stock insuficiente para el producto: " . $producto["codigo"]];
                }
            }
            
            // 2. Agregar a stock en tránsito
            require_once "modelos/stock-transito.modelo.php";
            $sessionTransportador = [
                "id" => $_SESSION["id"],
                "nombre" => $_SESSION["nombre"]
            ];
            
            $resultadoStockTransito = ModeloStockTransito::mdlAgregarStockTransito(
                $productos, 
                $despacho, 
                $sessionTransportador
            );
            
            if(!$resultadoStockTransito) {
                $conexionLocal->rollBack();
                $conexionCentral->rollBack();
                return ["exito" => false, "mensaje" => "Error al agregar productos a stock en tránsito"];
            }
            
            // 3. Registrar en histórico
            foreach($productos as $producto) {
                require_once "controladores/stock-transito.controlador.php";
                ControladorStockTransito::registrarHistoricoTransito(
                    $producto["codigo"],
                    $producto["descripcion"],
                    $producto["cantidad"],
                    'cargue',
                    $_SESSION["id"],
                    $_SESSION["nombre"],
                    $despacho["sucursal_origen"],
                    null, // No hay destino específico aún
                    $_SESSION["id"],
                    $_SESSION["nombre"],
                    null,
                    null,
                    $despacho["id"],
                    $despacho["numero_despacho"],
                    null,
                    "Productos cargados desde despacho: " . $despacho["numero_despacho"]
                );
            }
            
            // Confirmar transacciones
            $conexionLocal->commit();
            $conexionCentral->commit();
            
            return ["exito" => true, "mensaje" => "Productos movidos a stock en tránsito correctamente"];
            
        } catch(Exception $e) {
            if(isset($conexionLocal)) $conexionLocal->rollBack();
            if(isset($conexionCentral)) $conexionCentral->rollBack();
            
            return ["exito" => false, "mensaje" => "Error en la transacción: " . $e->getMessage()];
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
}