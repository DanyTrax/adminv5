<?php

class ControladorStockTransito {

    /*=============================================
    MOSTRAR STOCK EN TRÁNSITO
    =============================================*/
    static public function ctrMostrarStockTransito($item, $valor, $transportador = null) {
        
        $tabla = "stock_transito";
        $respuesta = ModeloStockTransito::mdlMostrarStockTransito($tabla, $item, $valor, $transportador);
        return $respuesta;
    }

    /*=============================================
    SOLICITAR DESCARGA DE PRODUCTO
    =============================================*/
    static public function ctrSolicitarDescarga() {

        if(isset($_POST["solicitarDescarga"])) {

            // Validar campos requeridos
            if(empty($_POST["cantidadDescargar"]) || 
               empty($_POST["idStockTransito"]) || 
               empty($_POST["codigoProducto"])) {
                
                echo '<script>
                    swal({
                        type: "error",
                        title: "Campos incompletos",
                        text: "Todos los campos marcados con * son obligatorios",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    });
                </script>';
                return;
            }

            $cantidadSolicitada = intval($_POST["cantidadDescargar"]);
            $idStockTransito = intval($_POST["idStockTransito"]);
            $codigoProducto = $_POST["codigoProducto"];
            $transportadorId = intval($_POST["transportadorId"]);

            // ✅ VALIDACIONES CRÍTICAS
            
            // 1. Cantidad debe ser mayor a 0
            if($cantidadSolicitada <= 0) {
                echo '<script>
                    swal({
                        type: "error",
                        title: "Cantidad inválida",
                        text: "La cantidad debe ser mayor a 0",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    });
                </script>';
                return;
            }

            // 2. Obtener stock actual del producto específico
            $stockActual = self::ctrObtenerStockDisponible($idStockTransito);
            
            if(!$stockActual) {
                echo '<script>
                    swal({
                        type: "error",
                        title: "Producto no encontrado",
                        text: "El producto no existe en stock en tránsito",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    });
                </script>';
                return;
            }

            // 3. ✅ VALIDACIÓN PRINCIPAL: No superar stock disponible
            if($cantidadSolicitada > $stockActual['cantidad_disponible']) {
                echo '<script>
                    swal({
                        type: "error",
                        title: "Stock insuficiente",
                        text: "Solo hay ' . $stockActual['cantidad_disponible'] . ' unidades disponibles. No puedes solicitar ' . $cantidadSolicitada . ' unidades",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    });
                </script>';
                return;
            }

            // 4. Verificar si ya hay solicitudes pendientes para este producto del mismo transportador
            $solicitudesPendientes = self::ctrVerificarSolicitudesPendientes($codigoProducto, $transportadorId);
            $cantidadYaSolicitada = 0;
            
            if($solicitudesPendientes > 0) {
                $cantidadYaSolicitada = self::ctrObtenerCantidadSolicitadaPendiente($codigoProducto, $transportadorId);
            }

            // 5. ✅ VALIDAR QUE LA SUMA NO SUPERE EL STOCK DISPONIBLE
            $totalSolicitado = $cantidadSolicitada + $cantidadYaSolicitada;
            
            if($totalSolicitado > $stockActual['cantidad_disponible']) {
                echo '<script>
                    swal({
                        type: "error",
                        title: "Límite excedido",
                        text: "Ya hay ' . $cantidadYaSolicitada . ' unidades solicitadas pendientes. Solo puedes solicitar ' . ($stockActual['cantidad_disponible'] - $cantidadYaSolicitada) . ' unidades más",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    });
                </script>';
                return;
            }

            // Obtener información de sucursal local
            require_once "controladores/sucursales.controlador.php";
            $sucursalLocal = ControladorSucursales::ctrObtenerConfiguracionLocal();
            
            try {
                
                $tabla = "solicitudes_descarga";
                $datos = array(
                    "id_stock_transito" => $idStockTransito,
                    "codigo_producto" => $codigoProducto,
                    "cantidad_solicitada" => $cantidadSolicitada,
                    "sucursal_destino" => $sucursalLocal ? $sucursalLocal['nombre'] : 'Sucursal Local',
                    "id_usuario_solicitante" => $_SESSION["id"],
                    "nombre_usuario_solicitante" => $_SESSION["nombre"],
                    "transportador_id" => $transportadorId,
                    "nombre_transportador" => $_POST["nombreTransportador"] ?? '',
                    "observaciones" => $_POST["observacionesDescarga"] ?? null
                );

                $respuesta = ModeloStockTransito::mdlCrearSolicitudDescarga($tabla, $datos);

                if($respuesta == "ok") {
                    
                    // Registrar en histórico
                    self::registrarHistoricoTransito(
                        $codigoProducto,
                        $stockActual['descripcion_producto'],
                        $cantidadSolicitada,
                        'solicitud_descarga',
                        $transportadorId,
                        $datos["nombre_transportador"],
                        $stockActual['sucursal_origen'],
                        $datos["sucursal_destino"],
                        $_SESSION["id"],
                        $_SESSION["nombre"],
                        null,
                        null,
                        null,
                        null,
                        null,
                        "Solicitud de descarga creada: " . $cantidadSolicitada . " unidades"
                    );

                    echo '<script>
                        swal({
                            type: "success",
                            title: "¡Solicitud enviada!",
                            text: "La solicitud de descarga ha sido enviada al transportador",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        }).then(function(result) {
                            if (result.value) {
                                window.location = "stock-transito";
                            }
                        });
                    </script>';

                } else {
                    throw new Exception("Error al crear la solicitud");
                }

            } catch(Exception $e) {
                echo '<script>
                    swal({
                        type: "error",
                        title: "Error",
                        text: "Error al procesar la solicitud: ' . $e->getMessage() . '",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    });
                </script>';
            }
        }
    }

    /*=============================================
    CONFIRMAR DESCARGA (SOLO TRANSPORTADORES)
    =============================================*/
    static public function ctrConfirmarDescarga() {

        if(isset($_POST["confirmarDescarga"]) && $_SESSION["perfil"] == "Transportador") {

            $idSolicitud = intval($_POST["idSolicitudDescarga"]);
            $observacionesConfirmacion = $_POST["observacionesConfirmacion"] ?? null;

            try {
                require_once __DIR__ . "/../api-transferencias/conexion-central.php";
                require_once "modelos/conexion.php";

                // Obtener detalles de la solicitud
                $solicitud = ModeloStockTransito::mdlObtenerSolicitudDescarga($idSolicitud);
                
                if(!$solicitud) {
                    throw new Exception("Solicitud no encontrada");
                }

                // Verificar que el transportador sea el dueño del stock
                if($solicitud["transportador_id"] != $_SESSION["id"]) {
                    throw new Exception("No tiene permisos para confirmar esta solicitud");
                }

                // Verificar estado pendiente
                if($solicitud["estado"] != "pendiente") {
                    throw new Exception("La solicitud ya fue procesada");
                }

                // Obtener stock actual
                $stockActual = self::ctrObtenerStockDisponible($solicitud["id_stock_transito"]);
                
                if(!$stockActual) {
                    throw new Exception("Stock no encontrado");
                }

                // ✅ VALIDACIÓN FINAL: Verificar stock suficiente
                if($solicitud["cantidad_solicitada"] > $stockActual["cantidad_disponible"]) {
                    throw new Exception("Stock insuficiente. Disponible: " . $stockActual["cantidad_disponible"] . ", Solicitado: " . $solicitud["cantidad_solicitada"]);
                }

                // ✅ INICIAR TRANSACCIÓN PARA OPERACIONES CRÍTICAS
                $conexionCentral = ConexionCentral::conectar();
                $conexionLocal = Conexion::conectar();
                
                $conexionCentral->beginTransaction();
                $conexionLocal->beginTransaction();

                // 1. ACTUALIZAR ESTADO DE SOLICITUD
                $respuestaConfirmacion = ModeloStockTransito::mdlActualizarEstadoSolicitud(
                    $idSolicitud, 
                    "confirmado", 
                    $observacionesConfirmacion
                );

                if(!$respuestaConfirmacion) {
                    throw new Exception("Error actualizando solicitud");
                }

                // 2. ✅ DESCONTAR DEL STOCK EN TRÁNSITO
                $respuestaDescuento = ModeloStockTransito::mdlDescontarStockTransito(
                    $solicitud["id_stock_transito"], 
                    $solicitud["cantidad_solicitada"]
                );

                if(!$respuestaDescuento) {
                    throw new Exception("Error descontando stock en tránsito");
                }

                // 3. ✅ AGREGAR AL STOCK LOCAL DE LA SUCURSAL DESTINO
                $respuestaAgregar = ModeloStockTransito::mdlAgregarStockLocal(
                    $solicitud["codigo_producto"], 
                    $solicitud["cantidad_solicitada"]
                );

                if(!$respuestaAgregar) {
                    throw new Exception("Error agregando stock local");
                }

                // 4. REGISTRAR EN HISTÓRICO
                self::registrarHistoricoTransito(
                    $solicitud["codigo_producto"],
                    $stockActual["descripcion_producto"],
                    $solicitud["cantidad_solicitada"],
                    'descarga',
                    $_SESSION["id"],
                    $_SESSION["nombre"],
                    $stockActual["sucursal_origen"],
                    $solicitud["sucursal_destino"],
                    $_SESSION["id"],
                    $_SESSION["nombre"],
                    $solicitud["id_usuario_solicitante"],
                    $solicitud["nombre_usuario_solicitante"],
                    null,
                    null,
                    $idSolicitud,
                    "Descarga confirmada: " . $solicitud["cantidad_solicitada"] . " unidades"
                );

                // ✅ CONFIRMAR TRANSACCIONES
                $conexionCentral->commit();
                $conexionLocal->commit();

                echo '<script>
                    swal({
                        type: "success",
                        title: "¡Descarga confirmada!",
                        text: "Los productos han sido descargados y agregados al inventario local",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    }).then(function(result) {
                        if (result.value) {
                            window.location = "stock-transito";
                        }
                    });
                </script>';

            } catch(Exception $e) {
                
                // ✅ REVERTIR TRANSACCIONES EN CASO DE ERROR
                if(isset($conexionCentral)) {
                    $conexionCentral->rollback();
                }
                if(isset($conexionLocal)) {
                    $conexionLocal->rollback();
                }

                echo '<script>
                    swal({
                        type: "error",
                        title: "Error",
                        text: "Error al confirmar descarga: ' . $e->getMessage() . '",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    });
                </script>';
            }
        }
    }

    /*=============================================
    RECHAZAR DESCARGA (SOLO TRANSPORTADORES)
    =============================================*/
    static public function ctrRechazarDescarga() {

        if(isset($_POST["rechazarDescarga"]) && $_SESSION["perfil"] == "Transportador") {

            $idSolicitud = intval($_POST["idSolicitudRechazar"]);
            $motivoRechazo = $_POST["motivoRechazo"];

            // Validar motivo
            if(empty($motivoRechazo)) {
                echo '<script>
                    swal({
                        type: "error",
                        title: "Campo requerido",
                        text: "Debe especificar el motivo del rechazo",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    });
                </script>';
                return;
            }

            try {
                
                // Obtener detalles de la solicitud
                $solicitud = ModeloStockTransito::mdlObtenerSolicitudDescarga($idSolicitud);
                
                if(!$solicitud) {
                    throw new Exception("Solicitud no encontrada");
                }

                // Verificar permisos
                if($solicitud["transportador_id"] != $_SESSION["id"]) {
                    throw new Exception("No tiene permisos para rechazar esta solicitud");
                }

                // Actualizar estado de solicitud
                $respuesta = ModeloStockTransito::mdlActualizarEstadoSolicitud(
                    $idSolicitud, 
                    "rechazado", 
                    null,
                    $motivoRechazo
                );

                if($respuesta == "ok") {
                    
                    // Registrar en histórico
                    $stockActual = self::ctrObtenerStockDisponible($solicitud["id_stock_transito"]);
                    
                    self::registrarHistoricoTransito(
                        $solicitud["codigo_producto"],
                        $stockActual ? $stockActual["descripcion_producto"] : "Producto",
                        $solicitud["cantidad_solicitada"],
                        'rechazo_descarga',
                        $_SESSION["id"],
                        $_SESSION["nombre"],
                        $stockActual ? $stockActual["sucursal_origen"] : null,
                        $solicitud["sucursal_destino"],
                        $_SESSION["id"],
                        $_SESSION["nombre"],
                        $solicitud["id_usuario_solicitante"],
                        $solicitud["nombre_usuario_solicitante"],
                        null,
                        null,
                        $idSolicitud,
                        "Solicitud rechazada. Motivo: " . $motivoRechazo
                    );

                    echo '<script>
                        swal({
                            type: "success",
                            title: "Solicitud rechazada",
                            text: "La solicitud ha sido rechazada correctamente",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        }).then(function(result) {
                            if (result.value) {
                                window.location = "stock-transito";
                            }
                        });
                    </script>';

                } else {
                    throw new Exception("Error al rechazar la solicitud");
                }

            } catch(Exception $e) {
                echo '<script>
                    swal({
                        type: "error",
                        title: "Error",
                        text: "Error al rechazar solicitud: ' . $e->getMessage() . '",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    });
                </script>';
            }
        }
    }

    /*=============================================
    OBTENER STOCK DISPONIBLE DE UN PRODUCTO ESPECÍFICO
    =============================================*/
    static public function ctrObtenerStockDisponible($idStockTransito) {
        
        $tabla = "stock_transito";
        $respuesta = ModeloStockTransito::mdlMostrarStockTransito($tabla, "id", $idStockTransito);
        return $respuesta;
    }

    /*=============================================
    VERIFICAR SOLICITUDES PENDIENTES
    =============================================*/
    static public function ctrVerificarSolicitudesPendientes($codigoProducto, $transportadorId) {
        
        return ModeloStockTransito::mdlContarSolicitudesPendientes($codigoProducto, $transportadorId);
    }

    /*=============================================
    OBTENER CANTIDAD YA SOLICITADA PENDIENTE
    =============================================*/
    static public function ctrObtenerCantidadSolicitadaPendiente($codigoProducto, $transportadorId) {
        
        return ModeloStockTransito::mdlObtenerCantidadSolicitadaPendiente($codigoProducto, $transportadorId);
    }

    /*=============================================
    OBTENER SOLICITUDES PENDIENTES PARA TRANSPORTADOR
    =============================================*/
    static public function ctrObtenerSolicitudesPendientes($transportadorId) {
        
        return ModeloStockTransito::mdlObtenerSolicitudesPendientesTransportador($transportadorId);
    }

    /*=============================================
    OBTENER RESUMEN DE STOCK EN TRÁNSITO
    =============================================*/
    static public function ctrObtenerResumenStockTransito() {
        
        try {
            require_once __DIR__ . "/../api-transferencias/conexion-central.php";
            
            $stmt = ConexionCentral::conectar()->prepare("
                SELECT 
                    COUNT(DISTINCT codigo_producto) as total_productos,
                    SUM(cantidad_disponible) as total_unidades,
                    COUNT(DISTINCT transportador_id) as total_transportadores
                FROM stock_transito 
                WHERE cantidad_disponible > 0
            ");
            
            $stmt->execute();
            $resumen = $stmt->fetch();
            
            // Obtener solicitudes pendientes
            $stmt = ConexionCentral::conectar()->prepare("
                SELECT COUNT(*) as solicitudes_pendientes
                FROM solicitudes_descarga 
                WHERE estado = 'pendiente'
            ");
            
            $stmt->execute();
            $solicitudes = $stmt->fetch();
            
            return array(
                "total_productos" => $resumen["total_productos"] ?? 0,
                "total_unidades" => $resumen["total_unidades"] ?? 0,
                "total_transportadores" => $resumen["total_transportadores"] ?? 0,
                "solicitudes_pendientes" => $solicitudes["solicitudes_pendientes"] ?? 0
            );
            
        } catch(Exception $e) {
            return array(
                "total_productos" => 0,
                "total_unidades" => 0,
                "total_transportadores" => 0,
                "solicitudes_pendientes" => 0
            );
        }
    }

    /*=============================================
    REGISTRAR EN HISTÓRICO DE TRÁNSITO
    =============================================*/
    static public function registrarHistoricoTransito($codigo, $descripcion, $cantidad, $tipoMovimiento, $transportadorId, $nombreTransportador, $sucursalOrigen, $sucursalDestino, $usuarioOrigen, $nombreUsuarioOrigen, $usuarioDestino, $nombreUsuarioDestino, $idDespacho, $numeroDespacho, $idSolicitudDescarga, $observaciones) {
        
        try {
            require_once __DIR__ . "/../api-transferencias/conexion-central.php";
            
            $stmt = ConexionCentral::conectar()->prepare("
                INSERT INTO historico_transito 
                (codigo_producto, descripcion_producto, cantidad, tipo_movimiento, 
                transportador_id, nombre_transportador, sucursal_origen, sucursal_destino,
                usuario_origen, nombre_usuario_origen, usuario_destino, nombre_usuario_destino,
                id_despacho, numero_despacho, id_solicitud_descarga, observaciones) 
                VALUES 
                (:codigo, :descripcion, :cantidad, :tipo_movimiento,
                :transportador_id, :nombre_transportador, :sucursal_origen, :sucursal_destino,
                :usuario_origen, :nombre_usuario_origen, :usuario_destino, :nombre_usuario_destino,
                :id_despacho, :numero_despacho, :id_solicitud_descarga, :observaciones)
            ");
            
            $stmt->bindParam(":codigo", $codigo, PDO::PARAM_STR);
            $stmt->bindParam(":descripcion", $descripcion, PDO::PARAM_STR);
            $stmt->bindParam(":cantidad", $cantidad, PDO::PARAM_INT);
            $stmt->bindParam(":tipo_movimiento", $tipoMovimiento, PDO::PARAM_STR);
            $stmt->bindParam(":transportador_id", $transportadorId, PDO::PARAM_INT);
            $stmt->bindParam(":nombre_transportador", $nombreTransportador, PDO::PARAM_STR);
            $stmt->bindParam(":sucursal_origen", $sucursalOrigen, PDO::PARAM_STR);
            $stmt->bindParam(":sucursal_destino", $sucursalDestino, PDO::PARAM_STR);
            $stmt->bindParam(":usuario_origen", $usuarioOrigen, PDO::PARAM_INT);
            $stmt->bindParam(":nombre_usuario_origen", $nombreUsuarioOrigen, PDO::PARAM_STR);
            $stmt->bindParam(":usuario_destino", $usuarioDestino, PDO::PARAM_INT);
            $stmt->bindParam(":nombre_usuario_destino", $nombreUsuarioDestino, PDO::PARAM_STR);
            $stmt->bindParam(":id_despacho", $idDespacho, PDO::PARAM_INT);
            $stmt->bindParam(":numero_despacho", $numeroDespacho, PDO::PARAM_STR);
            $stmt->bindParam(":id_solicitud_descarga", $idSolicitudDescarga, PDO::PARAM_INT);
            $stmt->bindParam(":observaciones", $observaciones, PDO::PARAM_STR);
            
            return $stmt->execute();
            
        } catch(Exception $e) {
            return false;
        }
    }

    /*=============================================
    FORZAR DESCARGA (SOLO ADMINISTRADOR)
    =============================================*/
    static public function ctrForzarDescarga() {

        if(isset($_POST["forzarDescarga"]) && $_SESSION["perfil"] == "Administrador") {

            $idStockTransito = intval($_POST["idStockTransitoForzar"]);
            $cantidadForzar = intval($_POST["cantidadForzar"]);
            $motivoForzado = $_POST["motivoForzado"];

            try {
                require_once __DIR__ . "/../api-transferencias/conexion-central.php";
                require_once "modelos/conexion.php";

                // Obtener stock actual
                $stockActual = self::ctrObtenerStockDisponible($idStockTransito);
                
                if(!$stockActual) {
                    throw new Exception("Stock no encontrado");
                }

                // ✅ VALIDAR CANTIDAD
                if($cantidadForzar <= 0 || $cantidadForzar > $stockActual["cantidad_disponible"]) {
                    throw new Exception("Cantidad inválida. Disponible: " . $stockActual["cantidad_disponible"]);
                }

                // ✅ INICIAR TRANSACCIONES
                $conexionCentral = ConexionCentral::conectar();
                $conexionLocal = Conexion::conectar();
                
                $conexionCentral->beginTransaction();
                $conexionLocal->beginTransaction();

                // 1. Descontar del stock en tránsito
                $respuestaDescuento = ModeloStockTransito::mdlDescontarStockTransito($idStockTransito, $cantidadForzar);

                if(!$respuestaDescuento) {
                    throw new Exception("Error descontando stock en tránsito");
                }

                // 2. Agregar al stock local
                $respuestaAgregar = ModeloStockTransito::mdlAgregarStockLocal(
                    $stockActual["codigo_producto"], 
                    $cantidadForzar
                );

                if(!$respuestaAgregar) {
                    throw new Exception("Error agregando stock local");
                }

                // 3. Registrar en histórico
                self::registrarHistoricoTransito(
                    $stockActual["codigo_producto"],
                    $stockActual["descripcion_producto"],
                    $cantidadForzar,
                    'descarga_forzada',
                    $stockActual["transportador_id"],
                    $stockActual["nombre_transportador"],
                    $stockActual["sucursal_origen"],
                    "Forzado por Administrador",
                    $_SESSION["id"],
                    $_SESSION["nombre"],
                    $_SESSION["id"],
                    $_SESSION["nombre"],
                    null,
                    null,
                    null,
                    "Descarga forzada por administrador. Motivo: " . $motivoForzado
                );

                // ✅ CONFIRMAR TRANSACCIONES
                $conexionCentral->commit();
                $conexionLocal->commit();

                echo '<script>
                    swal({
                        type: "success",
                        title: "¡Descarga forzada!",
                        text: "Los productos han sido forzadamente descargados al inventario local",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    }).then(function(result) {
                        if (result.value) {
                            window.location = "stock-transito";
                        }
                    });
                </script>';

            } catch(Exception $e) {
                
                // ✅ REVERTIR TRANSACCIONES
                if(isset($conexionCentral)) {
                    $conexionCentral->rollback();
                }
                if(isset($conexionLocal)) {
                    $conexionLocal->rollback();
                }

                echo '<script>
                    swal({
                        type: "error",
                        title: "Error",
                        text: "Error al forzar descarga: ' . $e->getMessage() . '",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    });
                </script>';
            }
        }
    }

    /*=============================================
    EXPORTAR STOCK TRÁNSITO A PDF
    =============================================*/
    static public function ctrExportarStockTransitoPDF($filtros = []) {
        
        if($_SESSION["perfil"] == "Administrador" || $_SESSION["perfil"] == "Transportador") {
            
            require_once "extensiones/tcpdf/tcpdf_include.php";
            
            // Obtener datos según filtros
            $stockTransito = self::obtenerStockTransitoParaExportar($filtros);
            
            // Crear PDF
            $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
            
            // Configuración del PDF
            $pdf->SetCreator('Sistema de Stock en Tránsito');
            $pdf->SetAuthor($_SESSION["nombre"]);
            $pdf->SetTitle('Reporte de Stock en Tránsito');
            $pdf->SetSubject('Stock de Productos en Movimiento');
            
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
            $pdf->SetMargins(15, 15, 15);
            $pdf->SetAutoPageBreak(TRUE, 15);
            
            $pdf->AddPage();
            
            // TÍTULO
            $pdf->SetFont('helvetica', 'B', 16);
            $pdf->SetTextColor(60, 141, 188);
            $pdf->Cell(0, 15, 'REPORTE DE STOCK EN TRÁNSITO', 0, 1, 'C');
            $pdf->Ln(5);
            
            // INFORMACIÓN DEL REPORTE
            $pdf->SetFont('helvetica', '', 10);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->Cell(60, 8, 'Generado por: ' . $_SESSION["nombre"], 0, 0, 'L');
            $pdf->Cell(60, 8, 'Fecha: ' . date('d/m/Y H:i:s'), 0, 1, 'L');
            $pdf->Ln(5);
            
            // TABLA DE STOCK
            $html = '
            <table cellpadding="4" cellspacing="0" border="1" style="border-collapse: collapse;">
                <thead>
                    <tr style="background-color: #3c8dbc; color: white; font-weight: bold;">
                        <th width="80px">Código</th>
                        <th width="120px">Descripción</th>
                        <th width="40px">Cant.</th>
                        <th width="80px">Transportador</th>
                        <th width="80px">Origen</th>
                        <th width="60px">Fecha</th>
                    </tr>
                </thead>
                <tbody>';
            
            $totalUnidades = 0;
            foreach($stockTransito as $stock) {
                $totalUnidades += $stock["cantidad_disponible"];
                $fechaCargue = date('d/m/Y', strtotime($stock["fecha_carga"]));
                
                $html .= '
                    <tr>
                        <td style="font-size: 9px;">' . $stock["codigo_producto"] . '</td>
                        <td style="font-size: 9px;">' . $stock["descripcion_producto"] . '</td>
                        <td style="text-align: center; font-size: 9px;">' . $stock["cantidad_disponible"] . '</td>
                        <td style="font-size: 9px;">' . $stock["nombre_transportador"] . '</td>
                        <td style="font-size: 9px;">' . $stock["sucursal_origen"] . '</td>
                        <td style="font-size: 9px;">' . $fechaCargue . '</td>
                    </tr>';
            }
            
            $html .= '
                </tbody>
                <tfoot>
                    <tr style="background-color: #f0f0f0; font-weight: bold;">
                        <td colspan="2" style="text-align: center;">TOTAL:</td>
                        <td style="text-align: center;">' . $totalUnidades . '</td>
                        <td colspan="3" style="text-align: center;">' . count($stockTransito) . ' productos</td>
                    </tr>
                </tfoot>
            </table>';
            
            $pdf->writeHTML($html, true, false, true, false, '');
            
            // PIE DE PÁGINA
            $pdf->Ln(10);
            $pdf->SetFont('helvetica', '', 8);
            $pdf->SetTextColor(128, 128, 128);
            $pdf->Cell(0, 8, 'Total productos en tránsito: ' . count($stockTransito) . ' | Total unidades: ' . $totalUnidades, 0, 1, 'C');
            
            // Salida del PDF
            ob_end_clean();
            $nombreArchivo = 'Stock_Transito_' . date('Y-m-d_H-i-s') . '.pdf';
            $pdf->Output($nombreArchivo, 'I');
        }
    }

    /*=============================================
    OBTENER STOCK TRÁNSITO PARA EXPORTAR
    =============================================*/
    static private function obtenerStockTransitoParaExportar($filtros) {
        
        try {
            require_once __DIR__ . "/../api-transferencias/conexion-central.php";
            
            $sql = "SELECT * FROM stock_transito WHERE cantidad_disponible > 0";
            $parametros = [];
            
            // Aplicar filtros
            if(!empty($filtros['transportador'])) {
                $sql .= " AND transportador_id = :transportador";
                $parametros[':transportador'] = $filtros['transportador'];
            }
            
            if(!empty($filtros['fecha_desde'])) {
                $sql .= " AND DATE(fecha_carga) >= :fecha_desde";
                $parametros[':fecha_desde'] = $filtros['fecha_desde'];
            }
            
            if(!empty($filtros['fecha_hasta'])) {
                $sql .= " AND DATE(fecha_carga) <= :fecha_hasta";
                $parametros[':fecha_hasta'] = $filtros['fecha_hasta'];
            }
            
            $sql .= " ORDER BY nombre_transportador ASC, codigo_producto ASC";
            
            $stmt = ConexionCentral::conectar()->prepare($sql);
            
            foreach($parametros as $key => $valor) {
                $stmt->bindValue($key, $valor);
            }
            
            $stmt->execute();
            return $stmt->fetchAll();
            
        } catch(Exception $e) {
            return [];
        }
    }

    /*=============================================
    DESCARGAR STOCK DIRECTO
    =============================================*/
    static public function ctrDescargarStockDirecto($idStockTransito, $cantidadDescargar, $usuarioId, $nombreUsuario, $sucursalDestino, $observaciones) {
        
        try {
            require_once __DIR__ . "/../api-transferencias/conexion-central.php";
            require_once __DIR__ . "/../modelos/conexion.php";
            
            $conexionCentral = ConexionCentral::conectar();
            $conexionLocal = Conexion::conectar();
            
            // Iniciar transacciones
            $conexionCentral->beginTransaction();
            $conexionLocal->beginTransaction();
            
            // 1. Obtener información del stock en tránsito
            $stmt = $conexionCentral->prepare("SELECT * FROM stock_transito WHERE id = ?");
            $stmt->execute([$idStockTransito]);
            $stock = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if(!$stock) {
                throw new Exception("Producto no encontrado en stock en tránsito");
            }
            
            // 2. Verificar cantidad disponible
            if($cantidadDescargar > $stock["cantidad_disponible"]) {
                throw new Exception("Cantidad a descargar excede la disponible");
            }
            
            // 3. Actualizar stock en tránsito (descontar cantidad)
            $nuevaCantidad = $stock["cantidad_disponible"] - $cantidadDescargar;
            $stmt = $conexionCentral->prepare("UPDATE stock_transito SET cantidad_disponible = ? WHERE id = ?");
            $stmt->execute([$nuevaCantidad, $idStockTransito]);
            
            // 4. Incrementar stock local del producto existente
            $stmt = $conexionLocal->prepare("
                UPDATE productos 
                SET stock = stock + ? 
                WHERE codigo = ?
            ");
            $stmt->execute([
                $cantidadDescargar,
                $stock["codigo_producto"]
            ]);
            
            // Verificar que el producto existe
            if($stmt->rowCount() == 0) {
                throw new Exception("Producto con código " . $stock["codigo_producto"] . " no existe en la base local");
            }
            
            // 5. Registrar en historial de tránsito
            self::registrarHistoricoTransito(
                $stock["codigo_producto"],
                $stock["descripcion_producto"],
                $cantidadDescargar,
                "descarga_directa",
                $stock["transportador_id"],
                $stock["nombre_transportador"],
                $stock["sucursal_origen"],
                $sucursalDestino,
                $stock["usuario_origen"] ?? null,
                $stock["nombre_usuario_origen"] ?? null,
                $usuarioId,
                $nombreUsuario,
                $stock["id_despacho_origen"],
                $stock["numero_despacho_origen"],
                null, // No hay solicitud de descarga
                $observaciones
            );
            
            // 6. Verificar si el despacho se completó
            $stmt = $conexionCentral->prepare("
                SELECT SUM(cantidad_disponible) as total_pendiente 
                FROM stock_transito 
                WHERE id_despacho_origen = ?
            ");
            $stmt->execute([$stock["id_despacho_origen"]]);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $totalPendiente = $resultado["total_pendiente"] ?? 0;
            
            if($totalPendiente == 0) {
                // 7. Marcar despacho como entregado
                $stmt = $conexionCentral->prepare("UPDATE despachos SET estado = 'entregado' WHERE id = ?");
                $stmt->execute([$stock["id_despacho_origen"]]);
                
                error_log("✅ DESPACHO COMPLETADO - ID: " . $stock["id_despacho_origen"] . " - Estado: entregado");
            }
            
            // Confirmar transacciones
            $conexionCentral->commit();
            $conexionLocal->commit();
            
            $mensaje = "Se descargaron $cantidadDescargar unidades de " . $stock["descripcion_producto"];
            if($totalPendiente == 0) {
                $mensaje .= ". Despacho completado y marcado como entregado.";
            }
            
            return [
                "success" => true,
                "message" => $mensaje,
                "despacho_completado" => $totalPendiente == 0
            ];
            
        } catch(Exception $e) {
            // Rollback en caso de error
            if(isset($conexionCentral)) $conexionCentral->rollBack();
            if(isset($conexionLocal)) $conexionLocal->rollBack();
            
            error_log("❌ Error en descarga directa: " . $e->getMessage());
            return [
                "success" => false,
                "error" => $e->getMessage()
            ];
        }
    }
}