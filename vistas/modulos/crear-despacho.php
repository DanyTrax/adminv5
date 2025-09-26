<?php

if($_SESSION["perfil"] == "Limitado" || $_SESSION["perfil"] == "Transportador"){
    echo '<script>
        window.location = "inicio";
    </script>';
    return;
}

// LÓGICA DE EDICIÓN
$modoEdicion = false;
$despachoEditar = null;
$productosParaEditar = [];

if(isset($_GET["editar"]) && is_numeric($_GET["editar"])) {
    
    $modoEdicion = true;
    $idDespacho = $_GET["editar"];
    
    // Obtener datos del despacho desde BD central
    try {
        require_once "api-transferencias/conexion-central.php";
        
        $stmt = ConexionCentral::conectar()->prepare("SELECT * FROM despachos WHERE id = :id");
        $stmt->bindParam(":id", $idDespacho);
        $stmt->execute();
        $despachoEditar = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if(!$despachoEditar) {
            echo '<script>
                swal({
                    title: "Error",
                    text: "Despacho no encontrado",
                    type: "error",
                    confirmButtonText: "Cerrar"
                }).then(function() {
                    window.location = "despachos";
                });
            </script>';
            exit;
        }
        
        if($despachoEditar["estado"] != "pendiente") {
            echo '<script>
                swal({
                    title: "No editable",
                    text: "Solo se pueden editar despachos pendientes. Estado actual: ' . $despachoEditar["estado"] . '",
                    type: "warning",
                    confirmButtonText: "Cerrar"
                }).then(function() {
                    window.location = "despachos";
                });
            </script>';
            exit;
        }
        
        // Parsear productos
        $productosParaEditar = json_decode($despachoEditar["productos_despacho"], true);
        if(!$productosParaEditar) {
            $productosParaEditar = [];
        }
        
    } catch(Exception $e) {
        echo '<script>
            swal({
                title: "Error de conexión",
                text: "No se pudo cargar el despacho: ' . $e->getMessage() . '",
                type: "error",
                confirmButtonText: "Cerrar"
            }).then(function() {
                window.location = "despachos";
            });
        </script>';
        exit;
    }
}
?>

<div class="content-wrapper">

    <section class="content-header">
        <h1>
            Crear Despacho
            <small>Generar nuevo despacho de mercancía</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="despachos"><i class="fa fa-truck"></i> Despachos</a></li>
            <li class="active">Crear Despacho</li>
        </ol>
    </section>

    <section class="content">

        <div class="row">

            <!-- COLUMNA IZQUIERDA: INFORMACIÓN + PRODUCTOS SELECCIONADOS -->
            <div class="col-md-6">

                <!-- FORMULARIO DE DESPACHO -->
                <div class="box box-primary">
                    
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-truck"></i> Información del Despacho
                        </h3>
                    </div>

                    <form role="form" method="post" id="formCrearDespacho" novalidate>

                        <div class="box-body">

                            <!-- NÚMERO DE DESPACHO (AUTO-GENERADO) -->
                            <div class="form-group">
                                <label>
                                    <i class="fa fa-barcode"></i> Número de Despacho:
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="numeroDespacho" 
                                       value="<?php echo 'DESP-' . date('Ymd') . '-' . str_pad(rand(1,999), 3, '0', STR_PAD_LEFT); ?>" 
                                       readonly
                                       style="background-color: #f4f4f4;">
                                <small class="help-block">
                                    <i class="fa fa-info-circle"></i> Se genera automáticamente
                                </small>
                            </div>

                            <!-- ORIGEN DE LA SOLICITUD (OPCIONAL) -->
                            <div class="form-group">
                                <label>
                                    <i class="fa fa-search"></i> Basado en Solicitud de Stock:
                                </label>
                                <div class="input-group">
                                    <input type="text" 
                                           class="form-control" 
                                           id="numeroSolicitudBuscar"
                                           placeholder="Número de solicitud (opcional)">
                                    <span class="input-group-btn">
                                        <button type="button" class="btn btn-info" onclick="buscarSolicitud()">
                                            <i class="fa fa-search"></i>
                                        </button>
                                    </span>
                                </div>
                                
                                <!-- INFO SOLICITUD ENCONTRADA CON BOTÓN CERRAR Y AGREGAR -->
                                <div id="infoSolicitudEncontrada" class="alert alert-info" style="display: none;">
                                    <div class="row">
                                        <div class="col-xs-8">
                                            <strong><i class="fa fa-check-circle"></i> Solicitud Encontrada:</strong>
                                            <div id="datosSolicitudEncontrada" class="mt-2"></div>
                                        </div>
                                        <div class="col-xs-4 text-right">
                                            <button type="button" 
                                                    class="btn btn-success btn-xs mr-2" 
                                                    onclick="cargarProductosDeSolicitud()"
                                                    data-toggle="tooltip" 
                                                    title="Agregar productos de esta solicitud al despacho">
                                                <i class="fa fa-plus"></i> Agregar productos
                                            </button>
                                            <button type="button" 
                                                    class="btn btn-danger btn-xs" 
                                                    onclick="limpiarSolicitudSeleccionada()"
                                                    data-toggle="tooltip" 
                                                    title="Limpiar solicitud seleccionada">
                                                <i class="fa fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                                <small class="help-block">
                                    <i class="fa fa-lightbulb-o"></i> Opcional: Puede cargar productos desde una solicitud existente
                                </small>
                            </div>

                            <!-- DETALLE ADICIONAL -->
                            <div class="form-group">
                                <label>
                                    <i class="fa fa-comment"></i> Detalle Adicional:
                                </label>
                                <textarea class="form-control" 
                                          name="detalleAdicional" 
                                          id="detalleAdicional" 
                                          rows="3" 
                                          maxlength="500"
                                          placeholder="Describa detalles del despacho, destino, instrucciones especiales, etc."></textarea>
                                <small class="help-block">
                                    <i class="fa fa-info-circle"></i> Máximo 500 caracteres
                                </small>
                            </div>

                        </div>

                        <!-- CAMPOS OCULTOS PARA ENVÍO -->
                        <input type="hidden" name="productosDespacho" id="productosDespachoHidden">
                        <input type="hidden" name="totalProductos" id="totalProductosHidden">
                        <input type="hidden" name="totalCantidad" id="totalCantidadHidden">
                        <input type="hidden" name="idSolicitudOrigen" id="idSolicitudOrigenHidden">
                        <input type="hidden" name="crearDespacho" value="1">

                    </form>

                </div>

                <!-- PRODUCTOS SELECCIONADOS (DEBAJO DEL FORMULARIO) -->
                <div class="box box-success">
                    
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-list"></i> Productos para Despachar
                            <span class="badge bg-green" id="contadorProductosDespacho">0</span>
                        </h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" onclick="validarStockProductos()">
                                <i class="fa fa-check-circle" data-toggle="tooltip" title="Validar Stock"></i>
                            </button>
                        </div>
                    </div>

                    <div class="box-body">

                        <!-- ALERTA DE VALIDACIÓN DE STOCK -->
                        <div id="alertaValidacionStock" class="alert alert-warning" style="display: none;">
                            <h5><i class="fa fa-exclamation-triangle"></i> Problemas de Stock Detectados:</h5>
                            <ul id="listaProblemasStock"></ul>
                            <button type="button" class="btn btn-xs btn-info" onclick="validarStockProductos()">
                                <i class="fa fa-refresh"></i> Revalidar Stock
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-condensed" id="tablaProductosDespacho">
                                <thead>
                                    <tr class="bg-light">
                                        <th>Producto</th>
                                        <th class="text-center" width="80px">Cantidad</th>
                                        <th class="text-center" width="80px">Stock</th>
                                        <th class="text-center" width="80px">Acción</th>
                                    </tr>
                                </thead>
                                <tbody id="productosDespachoSeleccionados">
                                    <tr id="sinProductosDespacho">
                                        <td colspan="4" class="text-center text-muted">
                                            <i class="fa fa-info-circle"></i> No hay productos agregados al despacho
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- RESUMEN -->
                        <div id="resumenDespacho" style="display: none;">
                            <hr>
                            <div class="row">
                                <div class="col-xs-6">
                                    <strong>Total Productos:</strong> <span id="totalProductosResumen">0</span>
                                </div>
                                <div class="col-xs-6">
                                    <strong>Total Unidades:</strong> <span id="totalUnidadesResumen">0</span>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>

                <!-- BOTONES DE ACCIÓN -->
                <div class="box box-default">
                    <div class="box-body">
                        
                        <div class="row">
                            <div class="col-xs-6">
                                <a href="despachos" class="btn btn-default btn-block">
                                    <i class="fa fa-arrow-left"></i> Cancelar
                                </a>
                            </div>
                            <div class="col-xs-6">
                                <button type="button" 
                                        class="btn btn-primary btn-block" 
                                        id="btnCrearDespacho"
                                        onclick="enviarFormularioDespacho()"
                                        disabled>
                                    <i class="fa fa-truck"></i> Crear Despacho
                                </button>
                            </div>
                        </div>

                    </div>
                </div>

            </div>

            <!-- COLUMNA DERECHA: CATÁLOGO DE PRODUCTOS LOCALES -->
            <div class="col-md-6">

                <div class="box box-info">
                    
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-cubes"></i> Inventario Local
                        </h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" onclick="actualizarInventarioLocal()">
                                <i class="fa fa-refresh" data-toggle="tooltip" title="Actualizar inventario"></i>
                            </button>
                        </div>
                    </div>

                    <div class="box-body" style="padding: 0;">
                        
                        <!-- FILTRO DE BÚSQUEDA -->
                        <div style="padding: 15px; border-bottom: 1px solid #f4f4f4;">
                            <div class="input-group">
                                <input type="text" 
                                       class="form-control" 
                                       id="filtroProductosLocal"
                                       placeholder="Buscar por código o descripción...">
                                <span class="input-group-btn">
                                    <button type="button" class="btn btn-default" onclick="limpiarFiltroLocal()">
                                        <i class="fa fa-times"></i>
                                    </button>
                                </span>
                            </div>
                        </div>
                        
                        <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                            <table class="table table-bordered table-condensed" id="tablaInventarioLocal">
                                <thead style="position: sticky; top: 0; background: white; z-index: 1;">
                                    <tr class="bg-info text-white">
                                        <th width="60px">Imagen</th>
                                        <th>Código</th>
                                        <th>Descripción</th>
                                        <th width="80px">Stock</th>
                                        <th width="80px">Acción</th>
                                    </tr>
                                </thead>
                                <tbody id="listaProductosLocal">
                                    <tr>
                                        <td colspan="5" class="text-center">
                                            <i class="fa fa-spinner fa-spin"></i> Cargando inventario local...
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                    </div>

                </div>

            </div>

        </div>

    </section>

</div>

<!-- MODAL PARA CANTIDAD DE PRODUCTO -->
<div class="modal fade" id="modalCantidadProductoDespacho" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">
                    <i class="fa fa-plus-circle text-primary"></i> Agregar al Despacho
                </h4>
            </div>
            <div class="modal-body">
                
                <div class="form-group">
                    <label><strong>Producto:</strong></label>
                    <p id="nombreProductoDespachoModal" class="text-primary"></p>
                    <p><strong>Stock disponible:</strong> <span id="stockProductoDespachoModal" class="text-success"></span> unidades</p>
                </div>
                
                <div class="form-group">
                    <label for="cantidadProductoDespachoModal">
                        <i class="fa fa-calculator"></i> Cantidad a despachar: <span class="text-danger">*</span>
                    </label>
                    <input type="number" 
                           class="form-control text-center" 
                           id="cantidadProductoDespachoModal" 
                           min="1" 
                           value="1" 
                           placeholder="Ingrese cantidad">
                    <small class="help-block" id="ayudaCantidadDespacho">
                        <i class="fa fa-info-circle"></i> Máximo: <span id="maximoCantidadDespacho">0</span> unidades
                    </small>
                </div>
                
                <div class="form-group">
                    <label for="observacionProductoDespachoModal">
                        <i class="fa fa-comment"></i> Observación (opcional):
                    </label>
                    <textarea class="form-control" 
                              id="observacionProductoDespachoModal" 
                              rows="2" 
                              maxlength="200"
                              placeholder="Detalles especiales, instrucciones, etc."></textarea>
                    <small class="help-block text-muted">
                        <i class="fa fa-info-circle"></i> Máximo 200 caracteres
                    </small>
                </div>

                <!-- CAMPOS OCULTOS DEL PRODUCTO -->
                <input type="hidden" id="codigoProductoDespachoModal">
                <input type="hidden" id="descripcionProductoDespachoModal">
                <input type="hidden" id="stockActualProductoDespachoModal">

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    <i class="fa fa-times"></i> Cancelar
                </button>
                <button type="button" class="btn btn-primary" id="confirmarAgregarProductoDespacho">
                    <i class="fa fa-check"></i> Agregar al Despacho
                </button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL PARA EDITAR CANTIDAD -->
<div class="modal fade" id="modalEditarCantidadDespacho" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">
                    <i class="fa fa-edit text-warning"></i> Editar Cantidad
                </h4>
            </div>
            <div class="modal-body">
                
                <div class="form-group">
                    <label><strong>Producto:</strong></label>
                    <p id="nombreProductoEditarModal" class="text-primary"></p>
                    <p><strong>Stock disponible:</strong> <span id="stockDisponibleEditar" class="text-success"></span> unidades</p>
                </div>
                
                <div class="form-group">
                    <label for="nuevaCantidadEditar">
                        <i class="fa fa-calculator"></i> Nueva cantidad:
                    </label>
                    <input type="number" 
                           class="form-control text-center" 
                           id="nuevaCantidadEditar" 
                           min="1">
                    <small class="help-block" id="ayudaEditarCantidad">
                        <i class="fa fa-info-circle"></i> Stock disponible más cantidad actual
                    </small>
                </div>

                <!-- CAMPOS OCULTOS -->
                <input type="hidden" id="indiceProductoEditar">

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    <i class="fa fa-times"></i> Cancelar
                </button>
                <button type="button" class="btn btn-warning" onclick="confirmarEditarCantidad()">
                    <i class="fa fa-check"></i> Actualizar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ESTILOS CSS -->
<style>
/* Productos en la tabla */
.producto-agregado {
    background-color: #d4edda !important;
    animation: fadeIn 0.5s;
}

.producto-problema-stock {
    background-color: #f8d7da !important;
}

.producto-sin-stock {
    background-color: #fff3cd !important;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

/* Validación de cantidades */
.cantidad-editando {
    border-color: #ffc107 !important;
    box-shadow: 0 0 0 0.2rem rgba(255, 193, 7, 0.25) !important;
}

.cantidad-valida {
    border-color: #28a745 !important;
    box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25) !important;
}

.cantidad-invalida {
    border-color: #dc3545 !important;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
}

/* Tabla de inventario */
#tablaInventarioLocal tbody tr:hover {
    background-color: #f5f5f5;
}

.stock-disponible {
    color: #28a745;
    font-weight: bold;
}

.stock-bajo {
    color: #ffc107;
    font-weight: bold;
}

.stock-agotado {
    color: #dc3545;
    font-weight: bold;
}

/* Búsqueda */
.producto-encontrado {
    background-color: #fff3cd;
    animation: highlight 2s;
}

@keyframes highlight {
    from { background-color: #fff3cd; }
    to { background-color: transparent; }
}

/* Responsive */
@media (max-width: 768px) {
    .col-md-6 {
        margin-bottom: 20px;
    }
    
    #tablaInventarioLocal {
        font-size: 12px;
    }
    
    .modal-sm {
        width: 95%;
    }
}
</style>
<script>
$(document).ready(function() {
    
    <?php if($modoEdicion && $despachoEditar): ?>
    
    console.log("🔄 MODO EDICIÓN ACTIVADO");
    console.log("📦 Despacho a editar:", <?php echo json_encode($despachoEditar); ?>);
    
    // Cambiar título y textos
    $("h1").html('<i class="fa fa-edit"></i> Editar Despacho <small>Modificar despacho <?php echo $despachoEditar["numero_despacho"]; ?></small>');
    $("#btnCrearDespacho").html('<i class="fa fa-save"></i> Guardar Cambios');
    
    // Cargar número de despacho
    $("#numeroDespacho").val("<?php echo $despachoEditar["numero_despacho"]; ?>");
    
    // Cargar detalle adicional
    <?php if($despachoEditar["detalle_adicional"]): ?>
    $("#detalleAdicional").val("<?php echo htmlspecialchars($despachoEditar["detalle_adicional"]); ?>");
    <?php endif; ?>
    
    // Cargar ID de solicitud origen si existe
    <?php if($despachoEditar["id_solicitud_origen"]): ?>
    $("#idSolicitudOrigenHidden").val("<?php echo $despachoEditar["id_solicitud_origen"]; ?>");
    <?php endif; ?>
    
    // Agregar campo oculto para ID del despacho
    $("#formCrearDespacho").append('<input type="hidden" name="idDespachoEditar" value="<?php echo $idDespacho; ?>">');
    
    // Cambiar action del form
    $("#formCrearDespacho").append('<input type="hidden" name="editarDespacho" value="1">');
    $("#formCrearDespacho input[name='crearDespacho']").remove();
    
    // Cargar productos después de que se cargue el inventario
    setTimeout(function() {
        cargarProductosDespachoEdicion();
    }, 2000);
    
    function cargarProductosDespachoEdicion() {
        console.log("📋 Cargando productos del despacho...");
        
        var productos = <?php echo json_encode($productosParaEditar); ?>;
        
        if(productos && productos.length > 0) {
            
            // Limpiar productos actuales
            productosDespachoArray = [];
            $("#productosDespachoSeleccionados").empty();
            $("#sinProductosDespacho").remove();
            
            // Agregar cada producto
            productos.forEach(function(producto, index) {
                
                console.log("➕ Agregando producto:", producto);
                
                // Crear objeto del producto
                var productoObj = {
                    codigo: producto.codigo,
                    descripcion: producto.descripcion,
                    cantidad: parseInt(producto.cantidad),
                    stock_disponible: producto.stock_disponible || producto.cantidad, // Fallback
                    observacion: producto.observacion || ''
                };
                
                // Agregar al array
                productosDespachoArray.push(productoObj);
                
                // Crear fila en la tabla
                var stockClass = productoObj.stock_disponible >= productoObj.cantidad ? 'stock-disponible' : 'stock-bajo';
                
                var fila = `
                    <tr class="producto-agregado" data-codigo="${productoObj.codigo}">
                        <td>
                            <div>
                                <strong>${productoObj.codigo}</strong>
                                <br>
                                <small class="text-muted">${productoObj.descripcion}</small>
                                ${productoObj.observacion ? '<br><em class="text-info">' + productoObj.observacion + '</em>' : ''}
                            </div>
                        </td>
                        <td class="text-center">
                            <input type="number" 
                                   class="form-control input-sm text-center cantidad-producto" 
                                   value="${productoObj.cantidad}" 
                                   min="1" 
                                   max="${productoObj.stock_disponible}"
                                   data-indice="${index}"
                                   onchange="actualizarCantidadProducto(this, ${index})"
                                   style="width: 60px;">
                        </td>
                        <td class="text-center">
                            <span class="${stockClass}">${productoObj.stock_disponible}</span>
                        </td>
                        <td class="text-center">
                            <button type="button" 
                                    class="btn btn-danger btn-xs" 
                                    onclick="eliminarProductoDespacho(${index})"
                                    data-toggle="tooltip" 
                                    title="Eliminar producto">
                                <i class="fa fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
                
                $("#productosDespachoSeleccionados").append(fila);
            });
            
            // Actualizar contadores y habilitar botón
            actualizarResumenDespacho();
            $("#btnCrearDespacho").prop("disabled", false);
            
            console.log("✅ Productos cargados correctamente:", productosDespachoArray.length);
        }
    }
    
    <?php endif; ?>
    
});
</script>
<?php
// EJECUTAR CONTROLADOR
$crearDespacho = new ControladorDespachos();
$crearDespacho->ctrCrearDespacho();
?>