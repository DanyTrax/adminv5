<?php
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../error_log');
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Log inicial para confirmar que la página se carga
error_log("🔍 DEBUG crear-despacho.php iniciado - " . date('Y-m-d H:i:s'));
error_log("👤 Usuario: " . ($_SESSION['nombre'] ?? 'No session'));
error_log("🔗 URL: " . $_SERVER['REQUEST_URI']);

// Verificar si es POST
if ($_POST) {
    error_log("📥 POST recibido en crear-despacho: " . print_r($_POST, true));

if($_SESSION["perfil"] == "Limitado" || $_SESSION["perfil"] == "Transportador"){
    echo '<script>
        window.location = "inicio";
    </script>';
    return;
}}

// LÓGICA PARA CARGAR DESDE SOLICITUD
$cargarDesdeSolicitud = false;
$solicitudOrigen = null;
$productosDesdeSolicitud = [];

if(isset($_GET["desde_solicitud"]) && $_GET["desde_solicitud"] == "1" && isset($_GET["id_solicitud"])) {
    
    $cargarDesdeSolicitud = true;
    $idSolicitud = $_GET["id_solicitud"];
    $numeroSolicitud = $_GET["numero_solicitud"] ?? '';
    
    error_log("🚛 Cargando despacho desde solicitud ID: " . $idSolicitud);
    
    try {
        require_once "api-transferencias/conexion-central.php";
        
        $stmt = ConexionCentral::conectar()->prepare("
            SELECT * FROM solicitudes_stock 
            WHERE id = :id AND estado = 'aprobado'
        ");
        $stmt->bindParam(":id", $idSolicitud);
        $stmt->execute();
        $solicitudOrigen = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($solicitudOrigen) {
            $productosDesdeSolicitud = json_decode($solicitudOrigen['productos_solicitados'] ?? '[]', true);
            error_log("✅ Solicitud encontrada: " . $solicitudOrigen['numero_solicitud'] . " con " . count($productosDesdeSolicitud) . " productos");
        } else {
            error_log("❌ Solicitud no encontrada o no está aprobada");
        }
        
    } catch(Exception $e) {
        error_log("❌ Error cargando solicitud: " . $e->getMessage());
    }
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
                                        <button type="button" class="btn btn-info" onclick="buscarSolicitudesStock($('#numeroSolicitudBuscar').val())">
                                            <i class="fa fa-search"></i>
                                        </button>
                                    </span>
                                </div>
                                
                                <!-- INFO SOLICITUD ENCONTRADA CON BOTÓN CERRAR Y AGREGAR -->
                                <div id="infoSolicitudEncontrada" class="alert alert-info" style="display: none;">
                                    <div class="row">
                                        <div class="col-xs-12">
                                            <div class="row">
                                                <div class="col-xs-12">
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
                                            </div>
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
    
    // Cambiar action del form - VERSIÓN MEJORADA
    $("#formCrearDespacho").append('<input type="hidden" name="editarDespacho" value="1" id="campoEditarDespacho">');
    $("#formCrearDespacho").append('<input type="hidden" name="idDespachoEditar" value="<?php echo $idDespacho; ?>" id="campoIdDespachoEditar">');
    $("#formCrearDespacho input[name='crearDespacho']").remove();

    // CONFIGURAR VARIABLES GLOBALES PARA DETECCIÓN
    window.modoEdicionActivo = true;
    window.idDespachoEditando = <?php echo $idDespacho; ?>;
    
    // Cargar productos después de que se cargue el inventario
    setTimeout(function() {
        cargarProductosDespachoEdicion();
    }, 2000);
    
    function cargarProductosDespachoEdicion() {
        console.log("📋 Cargando productos del despacho...");
        
        var productosGuardados = <?php echo json_encode($productosParaEditar); ?>;
        
        if(productosGuardados && productosGuardados.length > 0) {
            
            // Limpiar productos actuales
            productosDespacho = [];
            $("#productosDespachoSeleccionados").empty();
            $("#sinProductosDespacho").remove();
            
            // Obtener stock actual de cada producto de la BD
            obtenerStockActualProductos(productosGuardados);
        }
    }
    
    // FUNCIÓN: Obtener stock actual de la BD
    function obtenerStockActualProductos(productosGuardados) {
        
        console.log("🔄 Obteniendo stock actual de la BD...");
        
        var datos = new FormData();
        datos.append("obtenerStockActual", true);
        
        // Convertir códigos a array
        var codigos = productosGuardados.map(p => p.codigo);
        console.log("🔍 Códigos a buscar:", codigos);
        
        // Agregar cada código como elemento del array
        codigos.forEach(function(codigo, index) {
            datos.append("codigos[" + index + "]", codigo);
        });
        
        $.ajax({
            url: "ajax/productos-despacho.ajax.php",
            method: "POST",
            data: datos,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function(respuesta) {
                
                console.log("✅ Respuesta stock actual:", respuesta);
                
                if(respuesta.success) {
                    
                    // Combinar productos guardados con stock actual
                    productosGuardados.forEach(function(productoGuardado, index) {
                        
                        // Buscar el stock actual en la respuesta
                        var stockActual = 0;
                        if(respuesta.productos && respuesta.productos.length > 0) {
                            var productoActual = respuesta.productos.find(p => p.codigo === productoGuardado.codigo);
                            if(productoActual) {
                                stockActual = parseInt(productoActual.stock);
                                console.log("📊 Stock encontrado para " + productoGuardado.codigo + ": " + stockActual);
                            } else {
                                console.warn("⚠️ No se encontró stock para código: " + productoGuardado.codigo);
                            }
                        }
                        
                        // Crear objeto del producto con stock actual
                        var productoObj = {
                            codigo: productoGuardado.codigo,
                            descripcion: productoGuardado.descripcion,
                            cantidad: parseInt(productoGuardado.cantidad),
                            stock_disponible: stockActual, // STOCK ACTUAL DE LA BD
                            observacion: productoGuardado.observacion || '',
                            precio_venta: 0 // Valor por defecto
                        };
                        
                        console.log("➕ Agregando producto con stock actualizado:", productoObj);
                        
                        // Agregar al array global
                        productosDespacho.push(productoObj);
                        
                        // Crear fila en la tabla
                        agregarFilaProductoDespacho(productoObj, index);
                    });
                    
                    // Actualizar contadores - USAR FUNCIÓN CORRECTA
                    actualizarContadoresEdicion();
                    
                    // Habilitar botón guardar
                    $("#btnCrearDespacho").prop("disabled", false);
                    
                    console.log("✅ Productos cargados en modo edición:", productosDespacho.length);
                    
                } else {
                    console.error("Error obteniendo stock actual:", respuesta.error);
                    cargarProductosSinStockActual(productosGuardados);
                }
            },
            error: function(xhr, status, error) {
                console.error("Error AJAX obteniendo stock:", error);
                console.log("Respuesta del servidor:", xhr.responseText);
                cargarProductosSinStockActual(productosGuardados);
            }
        });
    }
    
    // FUNCIÓN FALLBACK: Si no se puede obtener stock actual
    function cargarProductosSinStockActual(productosGuardados) {
        console.log("⚠️ Cargando productos sin stock actualizado");
        
        productosGuardados.forEach(function(productoGuardado, index) {
            
            var productoObj = {
                codigo: productoGuardado.codigo,
                descripcion: productoGuardado.descripcion,
                cantidad: parseInt(productoGuardado.cantidad),
                stock_disponible: productoGuardado.stock_disponible || 0,
                observacion: productoGuardado.observacion || '',
                precio_venta: 0
            };
            
            productosDespacho.push(productoObj);
            agregarFilaProductoDespacho(productoObj, index);
        });
        
        actualizarContadoresEdicion();
        $("#btnCrearDespacho").prop("disabled", false);
    }
    
    // FUNCIÓN: Agregar fila a la tabla
    function agregarFilaProductoDespacho(producto, indice) {
        
        var stockClass = producto.stock_disponible >= producto.cantidad ? 'stock-disponible' : 'stock-bajo';
        if(producto.stock_disponible == 0) {
            stockClass = 'stock-agotado';
        }
        
        var fila = `
            <tr class="producto-agregado" data-codigo="${producto.codigo}" data-indice="${indice}">
                <td>
                    <div>
                        <strong>${producto.codigo}</strong>
                        <br>
                        <small class="text-muted">${producto.descripcion}</small>
                        ${producto.observacion ? '<br><em class="text-info">' + producto.observacion + '</em>' : ''}
                    </div>
                </td>
                <td class="text-center">
                    <input type="number" 
                           class="form-control input-sm text-center cantidad-producto" 
                           value="${producto.cantidad}" 
                           min="1" 
                           data-indice="${indice}"
                           onchange="actualizarCantidadProductoDespacho(this, ${indice})"
                           style="width: 70px;">
                </td>
                <td class="text-center">
                    <span class="${stockClass}" id="stock_${indice}" title="Stock actual en la base de datos">${producto.stock_disponible}</span>
                </td>
                <td class="text-center">
                    <button type="button" 
                            class="btn btn-warning btn-xs" 
                            onclick="editarProductoDespacho(${indice})"
                            data-toggle="tooltip" 
                            title="Editar producto">
                        <i class="fa fa-edit"></i>
                    </button>
                    <button type="button" 
                            class="btn btn-danger btn-xs" 
                            onclick="eliminarProductoDespacho(${indice})"
                            data-toggle="tooltip" 
                            title="Eliminar producto">
                        <i class="fa fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
        
        $("#productosDespachoSeleccionados").append(fila);
    }
    
    // FUNCIÓN: Actualizar contadores en modo edición
    function actualizarContadoresEdicion() {
        
        // Mostrar resumen si hay productos
        if(productosDespacho.length > 0) {
            $("#resumenDespacho").show();
            $("#sinProductosDespacho").remove();
        }
        
        // Calcular totales
        var totalProductos = productosDespacho.length;
        var totalCantidad = productosDespacho.reduce(function(sum, producto) {
            return sum + parseInt(producto.cantidad);
        }, 0);
        
        // Actualizar elementos en la página
        $("#contadorProductosDespacho").text(totalProductos);
        $("#totalProductosResumen").text(totalProductos);
        $("#totalUnidadesResumen").text(totalCantidad);
        
        // Actualizar campos ocultos
        $("#totalProductosHidden").val(totalProductos);
        $("#totalCantidadHidden").val(totalCantidad);
        $("#productosDespachoHidden").val(JSON.stringify(productosDespacho));
        
        console.log("📊 Contadores actualizados - Productos:", totalProductos, "Cantidad:", totalCantidad);
    }
    
    <?php endif; ?>
    
});

// FUNCIÓN GLOBAL: Actualizar cantidad de producto en edición
function actualizarCantidadProductoDespacho(input, indice) {
    
    var nuevaCantidad = parseInt($(input).val());
    var stockDisponible = productosDespacho[indice].stock_disponible;
    
    if(nuevaCantidad > stockDisponible) {
        swal({
            title: "Stock insuficiente",
            text: `Solo hay ${stockDisponible} unidades disponibles actualmente`,
            type: "warning",
            confirmButtonText: "Entendido"
        });
        $(input).val(productosDespacho[indice].cantidad);
        return;
    }
    
    if(nuevaCantidad < 1) {
        $(input).val(1);
        nuevaCantidad = 1;
    }
    
    // Actualizar en el array
    productosDespacho[indice].cantidad = nuevaCantidad;
    
    // Actualizar resumen usando la función correcta
    actualizarContadoresEdicion();
    
    console.log("✅ Cantidad actualizada:", productosDespacho[indice].codigo, "Nueva cantidad:", nuevaCantidad);
}
/*=============================================
FUNCIÓN: ENVIAR FORMULARIO DE DESPACHO
=============================================*/
function enviarFormularioDespacho() {
    
    console.log("🚀 Enviando formulario de despacho...");
    
    // Verificar si hay productos
    if(productosDespacho.length === 0) {
        swal({
            title: "Sin productos",
            text: "Debe agregar al menos un producto al despacho",
            type: "warning",
            confirmButtonText: "Entendido"
        });
        return false;
    }
    
    // Actualizar campos ocultos antes de enviar
    $("#productosDespachoHidden").val(JSON.stringify(productosDespacho));
    $("#totalProductosHidden").val(productosDespacho.length);
    
    var totalCantidad = productosDespacho.reduce(function(sum, producto) {
        return sum + parseInt(producto.cantidad);
    }, 0);
    $("#totalCantidadHidden").val(totalCantidad);
    
    // Verificar si es modo edición
    var esEdicion = $("#formCrearDespacho input[name='editarDespacho']").length > 0;
    
    if(esEdicion) {
        console.log("📝 Modo EDICIÓN - Datos a enviar:");
        console.log("- idDespachoEditar:", $("input[name='idDespachoEditar']").val());
        console.log("- editarDespacho:", $("input[name='editarDespacho']").val());
        console.log("- productosDespacho:", $("#productosDespachoHidden").val());
        console.log("- totalProductos:", $("#totalProductosHidden").val());
        console.log("- totalCantidad:", $("#totalCantidadHidden").val());
        console.log("- detalleAdicional:", $("#detalleAdicional").val());
        
        // Confirmar edición
        swal({
            title: "¿Guardar cambios?",
            text: "Se actualizarán " + productosDespacho.length + " productos en el despacho",
            type: "question",
            showCancelButton: true,
            confirmButtonColor: "#3c8dbc",
            cancelButtonColor: "#d33",
            confirmButtonText: "Sí, guardar cambios",
            cancelButtonText: "Cancelar"
        }).then(function(result) {
            if(result.value) {
                // Enviar formulario
                $("#formCrearDespacho")[0].submit();
            }
        });
        
    } else {
        console.log("📝 Modo CREACIÓN - Datos a enviar:");
        console.log("- productosDespacho:", $("#productosDespachoHidden").val());
        console.log("- totalProductos:", $("#totalProductosHidden").val());
        console.log("- totalCantidad:", $("#totalCantidadHidden").val());
        
        // Confirmar creación
        swal({
            title: "¿Crear despacho?",
            text: "Se creará un nuevo despacho con " + productosDespacho.length + " productos",
            type: "question",
            showCancelButton: true,
            confirmButtonColor: "#3c8dbc",
            cancelButtonColor: "#d33",
            confirmButtonText: "Sí, crear despacho",
            cancelButtonText: "Cancelar"
        }).then(function(result) {
            if(result.value) {
                // Enviar formulario
                $("#formCrearDespacho")[0].submit();
            }
        });
    }
}
// DEBUG: Interceptar envío del formulario
$("#formCrearDespacho").on("submit", function(e) {
    console.log("📨 FORMULARIO ENVIÁNDOSE...");
    console.log("🔍 Datos del formulario:");
    
    // Obtener todos los datos del formulario
    var formData = new FormData(this);
    
    for (var pair of formData.entries()) {
        console.log("- " + pair[0] + ": " + pair[1]);
    }
    
    // Permitir envío normal
    return true;
});
</script>
<?php
// PROCESAMIENTO DEL FORMULARIO
if(isset($_POST["crearDespacho"])){
    error_log("✅ Entrando al procesamiento del formulario crearDespacho");
    
    try {
        error_log("🔄 Cargando controlador de despachos...");
        require_once "controladores/despachos.controlador.php";
        error_log("✅ Controlador cargado exitosamente");
        
        // Preparar datos para el controlador
        $datosDespacho = array(
            "id_solicitud_origen" => $_POST["idSolicitudOrigen"] ?? null,
            "id_usuario_creador" => isset($_SESSION["id"]) ? $_SESSION["id"] : 0,
            "nombre_usuario_creador" => isset($_SESSION["nombre"]) ? $_SESSION["nombre"] : 'Usuario',
            "productos_despacho" => $_POST["productosDespacho"],
            "total_productos" => intval($_POST["totalProductos"]),
            "total_cantidad" => intval($_POST["totalCantidad"]),
            "detalle_adicional" => $_POST["detalleAdicional"] ?? ""
        );
        
        error_log("📦 Datos preparados para controlador: " . print_r($datosDespacho, true));
        
        error_log("🚀 Llamando al controlador...");
        $resultado = ControladorDespachos::ctrCrearDespacho($datosDespacho);
        error_log("📋 Resultado del controlador: " . $resultado);
        
        if($resultado == "ok") {
            error_log("✅ Despacho creado exitosamente");
            echo '<script>
                swal({
                    title: "¡Despacho creado!",
                    text: "El despacho ha sido creado exitosamente",
                    type: "success",
                    confirmButtonText: "Ver despachos"
                }).then(function() {
                    window.location = "despachos";
                });
            </script>';
        } else {
            error_log("❌ Error creando despacho: " . $resultado);
            echo '<script>
                swal({
                    title: "Error",
                    text: "Error al crear el despacho: ' . htmlspecialchars($resultado) . '",
                    type: "error",
                    confirmButtonText: "Cerrar"
                });
            </script>';
        }
        
    } catch(Exception $e) {
        error_log("❌ Excepción procesando despacho: " . $e->getMessage());
        error_log("📍 Línea del error: " . $e->getLine());
        error_log("📄 Archivo del error: " . $e->getFile());
        echo '<script>
            swal({
                title: "Error del sistema",
                text: "Error interno: ' . htmlspecialchars($e->getMessage()) . '",
                type: "error",
                confirmButtonText: "Cerrar"
            });
        </script>';
    }
}

// PROCESAMIENTO PARA EDICIÓN
if($modoEdicion && isset($_POST["editarDespacho"])) {
    error_log("📝 Procesando edición de despacho");
    try {
        require_once "controladores/despachos.controlador.php";
        $crearDespacho = new ControladorDespachos();
        $crearDespacho->ctrEditarDespacho($_POST);
    } catch(Exception $e) {
        error_log("❌ Error en edición: " . $e->getMessage());
    }
}
?>

<!-- Variables JavaScript para cargar desde solicitud -->
<script>
    // Variables para cargar desde solicitud
    window.cargarDesdeSolicitud = <?php echo $cargarDesdeSolicitud ? 'true' : 'false'; ?>;
    window.solicitudOrigen = <?php echo json_encode($solicitudOrigen); ?>;
    window.productosDesdeSolicitud = <?php echo json_encode($productosDesdeSolicitud); ?>;
    
    console.log("🚛 Variables de solicitud cargadas:");
    console.log("- Cargar desde solicitud:", window.cargarDesdeSolicitud);
    console.log("- Solicitud origen:", window.solicitudOrigen);
    console.log("- Productos desde solicitud:", window.productosDesdeSolicitud);
</script>

<!-- Incluir JavaScript específico para crear despacho -->
<script src="vistas/js/crear-despacho.js"></script>