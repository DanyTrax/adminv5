<?php
// Vista para usuarios - Todos los productos disponibles para descarga
$filtroTransportador = $_GET["transportador"] ?? null;
$transportadores = ControladorStockTransito::ctrMostrarStockDisponibleUsuarios($filtroTransportador);
$listaTransportadores = ControladorStockTransito::ctrObtenerTransportadores();

$totalProductos = 0;
$totalTransportadores = count($transportadores);
foreach($transportadores as $productos) {
    $totalProductos += count($productos);
}
?>

<div class="content-wrapper">
    <section class="content-header">
        <h1>
            📦 Productos Disponibles para Descarga
            <small>Stock en tránsito organizado por transportador</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li class="active">Stock en Tránsito</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <!-- Filtros -->
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-filter"></i> Filtros de Búsqueda
                        </h3>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Filtrar por Transportador:</label>
                                    <select class="form-control" id="filtroTransportador" onchange="filtrarPorTransportador()">
                                        <option value="">Todos los Transportadores</option>
                                        <?php foreach($listaTransportadores as $transportador): ?>
                                            <option value="<?php echo $transportador['transportador_id']; ?>" 
                                                    <?php echo ($filtroTransportador == $transportador['transportador_id']) ? 'selected' : ''; ?>>
                                                <?php echo $transportador['nombre_transportador']; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Buscar Producto:</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="buscarProducto" placeholder="Código o descripción...">
                                        <span class="input-group-btn">
                                            <button class="btn btn-default" type="button" onclick="limpiarBusqueda()">
                                                <i class="fa fa-times"></i>
                                            </button>
                                        </span>
                                    </div>
                                    <small class="text-muted">
                                        <i class="fa fa-info-circle"></i> Búsqueda en tiempo real
                                    </small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>&nbsp;</label><br>
                                    <button class="btn btn-default" onclick="limpiarFiltros()">
                                        <i class="fa fa-refresh"></i> Limpiar Filtros
                                    </button>
                                    <span id="resultadosInfo" class="label label-info" style="margin-left: 10px; display: none;">
                                        <i class="fa fa-info-circle"></i> <span id="resultadosTexto">0 productos</span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Resumen -->
                <div class="row">
                    <div class="col-md-3">
                        <div class="info-box bg-blue">
                            <span class="info-box-icon"><i class="fa fa-truck"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Transportadores</span>
                                <span class="info-box-number"><?php echo $totalTransportadores; ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box bg-green">
                            <span class="info-box-icon"><i class="fa fa-box"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Productos Disponibles</span>
                                <span class="info-box-number"><?php echo $totalProductos; ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box bg-yellow">
                            <span class="info-box-icon"><i class="fa fa-user"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Usuario Actual</span>
                                <span class="info-box-number"><?php echo $_SESSION["nombre"]; ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box bg-red">
                            <span class="info-box-icon"><i class="fa fa-clock-o"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Última Actualización</span>
                                <span class="info-box-number"><?php echo date('H:i'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if(empty($transportadores)): ?>
                    <!-- Sin productos -->
                    <div class="box box-warning">
                        <div class="box-body text-center">
                            <i class="fa fa-info-circle fa-3x text-muted"></i>
                            <h3 class="text-muted">No hay productos disponibles</h3>
                            <p class="text-muted">
                                <?php if($filtroTransportador): ?>
                                    El transportador seleccionado no tiene productos disponibles.
                                <?php else: ?>
                                    No hay productos en tránsito disponibles para descarga.
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Contenedor para resultados AJAX -->
                    <div id="contenedorProductos">
               <!-- Lista de Transportadores -->
               <?php foreach($transportadores as $transportadorId => $productos): ?>
                   <?php 
                   $primerProducto = reset($productos); // Obtener el primer producto del array
                   $totalCantidad = array_sum(array_column($productos, 'cantidad_total'));
                   $totalProductos = count($productos);
                   ?>
                        <div class="box box-info transportador-section">
                            <div class="box-header with-border">
                                <div class="row">
                                    <div class="col-md-8">
                                        <h3 class="box-title">
                                            <i class="fa fa-truck"></i> 
                                            <strong><?php echo $primerProducto['nombre_transportador']; ?></strong>
                                        </h3>
                                        <p class="text-muted" style="margin: 5px 0 0 0;">
                                            <i class="fa fa-map-marker"></i> Transportador responsable
                                        </p>
                                    </div>
                                    <div class="col-md-4 text-right">
                                        <div class="info-box-content" style="display: inline-block; text-align: right;">
                                            <span class="info-box-text">Productos</span>
                                            <span class="info-box-number" style="font-size: 24px; color: #17a2b8;"><?php echo $totalProductos; ?></span>
                                        </div>
                                        <div class="info-box-content" style="display: inline-block; text-align: right; margin-left: 20px;">
                                            <span class="info-box-text">Unidades</span>
                                            <span class="info-box-number" style="font-size: 24px; color: #28a745;"><?php echo $totalCantidad; ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="box-body" style="padding: 0;">
                                <!-- Tabla de Productos -->
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover productos-table">
                                        <thead style="background-color: #f8f9fa;">
                                            <tr>
                                                <th style="width: 100px;">
                                                    <i class="fa fa-barcode"></i> Código
                                                </th>
                                                <th>
                                                    <i class="fa fa-cube"></i> Descripción del Producto
                                                </th>
                                                <th style="width: 120px;">
                                                    <i class="fa fa-shipping-fast"></i> Despacho
                                                </th>
                                                <th style="width: 120px;">
                                                    <i class="fa fa-map-marker"></i> Origen
                                                </th>
                                                <th style="width: 100px; text-align: center;">
                                                    <i class="fa fa-cubes"></i> Cantidad
                                                </th>
                                                <th style="width: 100px; text-align: center;">
                                                    <i class="fa fa-flag"></i> Estado
                                                </th>
                                                <th style="width: 120px; text-align: center;">
                                                    <i class="fa fa-cogs"></i> Acciones
                                                </th>
                                            </tr>
                                        </thead>
                                   <tbody>
                                       <?php foreach($productos as $codigoProducto => $producto): ?>
                                           <tr class="producto-row" 
                                               data-codigo="<?php echo strtolower($producto['codigo_producto']); ?>" 
                                               data-descripcion="<?php echo strtolower($producto['descripcion_producto']); ?>">
                                               <td>
                                                   <strong class="text-primary" style="font-size: 16px;">
                                                       <?php echo $producto['codigo_producto']; ?>
                                                   </strong>
                                               </td>
                                               <td>
                                                   <div style="max-width: 300px; line-height: 1.3;">
                                                       <strong style="word-wrap: break-word; white-space: normal;"><?php echo $producto['descripcion_producto']; ?></strong>
                                                   </div>
                                               </td>
                                               <td>
                                                   <span class="label label-info" style="font-size: 12px;">
                                                       <i class="fa fa-list"></i> <?php echo count($producto['detalles']); ?> despachos
                                                   </span>
                                               </td>
                                               <td>
                                                   <span class="text-muted">
                                                       <i class="fa fa-building"></i> 
                                                       Múltiples sucursales
                                                   </span>
                                               </td>
                                               <td style="text-align: center;">
                                                   <span class="badge bg-blue" style="font-size: 16px; padding: 8px 12px;">
                                                       <?php echo $producto['cantidad_total']; ?>
                                                   </span>
                                               </td>
                                               <td style="text-align: center;">
                                                   <span class="label label-success" style="font-size: 12px;">
                                                       <i class="fa fa-check"></i> Disponible
                                                   </span>
                                               </td>
                                               <td style="text-align: center; vertical-align: middle;">
                                                   <div class="btn-group-vertical" style="width: 100%;">
                                                       <button class="btn btn-info btn-sm btnVerDetalle" 
                                                               data-codigo="<?php echo $producto['codigo_producto']; ?>"
                                                               data-descripcion="<?php echo $producto['descripcion_producto']; ?>"
                                                               data-detalles='<?php echo json_encode($producto['detalles']); ?>'
                                                               data-cronologia='<?php echo json_encode($producto['cronologia_completa']); ?>'
                                                               data-cantidad-total="<?php echo $producto['cantidad_total']; ?>"
                                                               data-transportador="<?php echo $producto['nombre_transportador']; ?>"
                                                               style="width: 100%; margin-bottom: 5px; font-size: 12px; padding: 8px 12px;">
                                                           <i class="fa fa-info-circle"></i> Ver Detalle
                                                       </button>
                                                       <button class="btn btn-success btn-sm btnDescargaDirecta" 
                                                               data-codigo="<?php echo $producto['codigo_producto']; ?>"
                                                               data-descripcion="<?php echo $producto['descripcion_producto']; ?>"
                                                               data-cantidad="<?php echo $producto['cantidad_total']; ?>"
                                                               data-transportador="<?php echo $producto['nombre_transportador']; ?>"
                                                               data-detalles='<?php echo json_encode($producto['detalles']); ?>'
                                                               style="width: 100%; font-size: 12px; padding: 8px 12px;">
                                                           <i class="fa fa-download"></i> Descargar
                                                       </button>
                                                   </div>
                                               </td>
                                           </tr>
                                       <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
</div>

<!-- Modal de Detalle del Producto -->
<div class="modal fade" id="modalDetalleProducto" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">
                    <i class="fa fa-info-circle text-info"></i> Detalle del Producto
                </h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="alert alert-info">
                            <h4 id="detalleCodigoProducto">-</h4>
                            <p id="detalleDescripcionProducto">-</p>
                            <p><strong>Transportador:</strong> <span id="detalleTransportador">-</span></p>
                            <p><strong>Cantidad Total:</strong> <span class="badge bg-blue" id="detalleCantidadTotal">-</span></p>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-12">
                        <h5><i class="fa fa-list"></i> Detalle por Despacho (Orden LIFO)</h5>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th><i class="fa fa-sort-numeric-desc"></i> Orden</th>
                                        <th><i class="fa fa-shipping-fast"></i> Despacho</th>
                                        <th><i class="fa fa-building"></i> Sucursal</th>
                                        <th><i class="fa fa-cubes"></i> Cantidad</th>
                                        <th><i class="fa fa-clock-o"></i> Fecha Carga</th>
                                    </tr>
                                </thead>
                                <tbody id="detalleTablaDespachos">
                                    <!-- Se llena dinámicamente -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-12">
                        <h5><i class="fa fa-history"></i> Cronología Completa de Cargas</h5>
                        <div class="timeline" id="detalleCronologia">
                            <!-- Se llena dinámicamente -->
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    <i class="fa fa-times"></i> Cerrar
                </button>
                <button type="button" class="btn btn-success" id="btnDescargarDesdeDetalle">
                    <i class="fa fa-download"></i> Descargar Producto
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Descarga -->
<div class="modal fade" id="modalDescargaDirecta" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">
                    <i class="fa fa-download"></i> Descargar Producto
                </h4>
            </div>
            <form id="formDescargaDirecta">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <h4>Información del Producto</h4>
                            <div class="well">
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong>Código:</strong> <span id="descargaCodigo"></span><br>
                                        <strong>Descripción:</strong> <span id="descargaDescripcion"></span>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Transportador:</strong> <span id="descargaTransportador"></span><br>
                                        <strong>Origen:</strong> <span id="descargaOrigen"></span><br>
                                        <strong>Despacho:</strong> <span id="descargaDespacho"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Cantidad disponible:</label>
                                <input type="number" id="descargaCantidadDisponible" class="form-control" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Cantidad a descargar: *</label>
                                <input type="number" 
                                       name="cantidadDescargar" 
                                       id="cantidadDescargar"
                                       class="form-control" 
                                       min="1" 
                                       required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Observaciones:</label>
                                <textarea name="observacionesDescarga" 
                                          id="observacionesDescarga" 
                                          class="form-control" 
                                          rows="3" 
                                          placeholder="Observaciones sobre la descarga..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">
                        <i class="fa fa-times"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fa fa-download"></i> Descargar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.transportador-section {
    margin-bottom: 30px;
    border-left: 4px solid #17a2b8;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.transportador-section .box-header {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-bottom: 2px solid #17a2b8;
    padding: 20px;
}

.transportador-section .box-title {
    font-size: 20px;
    color: #495057;
    margin: 0;
}

.productos-table {
    margin: 0;
    font-size: 14px;
}

.productos-table thead th {
    background-color: #f8f9fa !important;
    border-bottom: 2px solid #dee2e6;
    font-weight: bold;
    color: #495057;
    padding: 15px 10px;
    vertical-align: middle;
}

.productos-table tbody tr {
    transition: background-color 0.2s;
}

.productos-table tbody tr:hover {
    background-color: #f8f9fa;
}

.productos-table tbody td {
    padding: 15px 10px;
    vertical-align: middle;
    border-bottom: 1px solid #f1f3f4;
}

.producto-row:hover {
    background-color: #f8f9fa !important;
}

.info-box {
    margin-bottom: 0;
}

.well {
    background-color: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 4px;
    padding: 15px;
}

/* Mejorar espaciado y organización */
.transportador-section + .transportador-section {
    margin-top: 20px;
}

/* Estilos para badges y labels */
.badge {
    font-size: 12px;
    padding: 6px 10px;
}

.label {
    font-size: 11px;
    padding: 4px 8px;
}

/* Botones de acción */
.btn-sm {
    padding: 6px 12px;
    font-size: 12px;
}

       /* Mejorar espaciado de descripción */
       .productos-table tbody td:nth-child(2) {
           vertical-align: top;
           padding-top: 12px;
           padding-bottom: 12px;
       }
       
       .productos-table tbody td:nth-child(2) div {
           line-height: 1.4;
           word-spacing: 0.1em;
       }
       
       /* Mejorar botones de acción */
       .btn-group-vertical .btn {
           border-radius: 4px;
           transition: all 0.3s ease;
           box-shadow: 0 2px 4px rgba(0,0,0,0.1);
       }
       
       .btn-group-vertical .btn:hover {
           transform: translateY(-1px);
           box-shadow: 0 4px 8px rgba(0,0,0,0.15);
       }
       
       .btn-group-vertical .btn-info {
           background: linear-gradient(135deg, #5bc0de 0%, #46b8da 100%);
           border-color: #46b8da;
       }
       
       .btn-group-vertical .btn-success {
           background: linear-gradient(135deg, #5cb85c 0%, #449d44 100%);
           border-color: #449d44;
       }
       
       .btn-group-vertical .btn-info:hover {
           background: linear-gradient(135deg, #46b8da 0%, #31b0d5 100%);
       }
       
       .btn-group-vertical .btn-success:hover {
           background: linear-gradient(135deg, #449d44 0%, #398439 100%);
       }
       
       /* Responsive */
       @media (max-width: 768px) {
           .productos-table {
               font-size: 12px;
           }
           
           .productos-table thead th,
           .productos-table tbody td {
               padding: 10px 5px;
           }
           
           .transportador-section .box-header .row {
               text-align: center;
           }
           
           .transportador-section .box-header .col-md-4 {
               margin-top: 10px;
           }
           
           .btn-group-vertical .btn {
               font-size: 11px;
               padding: 6px 8px;
           }
       }
</style>

<script>
// Variable global para almacenar el ID del stock seleccionado
var stockSeleccionado = null;
var timeoutBusqueda = null;

// Función para filtrar por transportador
function filtrarPorTransportador() {
    var transportadorId = document.getElementById('filtroTransportador').value;
    var terminoBusqueda = document.getElementById('buscarProducto').value;
    
    // Realizar búsqueda AJAX
    buscarProductos(terminoBusqueda, transportadorId);
}

// Función para limpiar búsqueda
function limpiarBusqueda() {
    document.getElementById('buscarProducto').value = '';
    var transportadorId = document.getElementById('filtroTransportador').value;
    buscarProductos('', transportadorId);
}

// Función para limpiar filtros
function limpiarFiltros() {
    document.getElementById('filtroTransportador').value = '';
    document.getElementById('buscarProducto').value = '';
    buscarProductos('', '');
}

// Función principal de búsqueda AJAX
function buscarProductos(termino, transportadorId) {
    // Mostrar indicador de carga
    mostrarCargando();
    
    // Realizar petición AJAX
    $.ajax({
        url: "ajax/buscar-stock-transito.ajax.php",
        method: "POST",
        data: {
            buscarProductos: true,
            terminoBusqueda: termino,
            filtroTransportador: transportadorId
        },
        success: function(respuesta) {
            try {
                var datos = JSON.parse(respuesta);
                
                if(datos.success) {
                    // Actualizar contenedor de productos
                    $("#contenedorProductos").html(datos.html);
                    
                    // Actualizar información de resultados
                    actualizarInfoResultados(datos.totalProductos, datos.totalTransportadores);
                    
                    // Reconfigurar eventos de descarga
                    configurarEventosDescarga();
                } else {
                    mostrarError("Error en la búsqueda: " + datos.error);
                }
            } catch(e) {
                mostrarError("Error procesando respuesta del servidor");
            }
        },
        error: function() {
            mostrarError("Error de conexión con el servidor");
        }
    });
}

// Función para mostrar indicador de carga
function mostrarCargando() {
    $("#contenedorProductos").html(`
        <div class="box box-info">
            <div class="box-body text-center">
                <i class="fa fa-spinner fa-spin fa-2x text-info"></i>
                <h4 class="text-info">Buscando productos...</h4>
            </div>
        </div>
    `);
}

// Función para mostrar error
function mostrarError(mensaje) {
    $("#contenedorProductos").html(`
        <div class="box box-danger">
            <div class="box-body text-center">
                <i class="fa fa-exclamation-triangle fa-2x text-danger"></i>
                <h4 class="text-danger">Error</h4>
                <p>${mensaje}</p>
            </div>
        </div>
    `);
}

// Función para actualizar información de resultados
function actualizarInfoResultados(totalProductos, totalTransportadores) {
    if(totalProductos > 0) {
        $("#resultadosTexto").text(`${totalProductos} productos en ${totalTransportadores} transportador${totalTransportadores > 1 ? 'es' : ''}`);
        $("#resultadosInfo").show();
    } else {
        $("#resultadosInfo").hide();
    }
}

// Función para configurar eventos de descarga
function configurarEventosDescarga() {
    // Los eventos ya están configurados con $(document).on()
    // Esta función se puede usar para reconfigurar si es necesario
}

// Event listener para búsqueda en tiempo real
$(document).ready(function() {
    // Búsqueda en tiempo real con debounce
    $("#buscarProducto").on("input", function() {
        var termino = $(this).val();
        var transportadorId = $("#filtroTransportador").val();
        
        // Limpiar timeout anterior
        if(timeoutBusqueda) {
            clearTimeout(timeoutBusqueda);
        }
        
        // Establecer nuevo timeout (500ms de delay)
        timeoutBusqueda = setTimeout(function() {
            buscarProductos(termino, transportadorId);
        }, 500);
    });
    
    // Event listener para cambio de transportador
    $("#filtroTransportador").on("change", function() {
        var transportadorId = $(this).val();
        var termino = $("#buscarProducto").val();
        buscarProductos(termino, transportadorId);
    });
});

       // Event listener para botón de detalle
       $(document).on("click", ".btnVerDetalle", function(e) {
           e.preventDefault();
           
           var codigo = $(this).data("codigo");
           var descripcion = $(this).data("descripcion");
           var detalles = $(this).data("detalles");
           var cronologia = $(this).data("cronologia");
           var cantidadTotal = $(this).data("cantidad-total");
           var transportador = $(this).data("transportador");
           
           console.log("🔍 DEBUG: Mostrando detalle del producto:", {
               codigo, descripcion, cantidadTotal, transportador, detalles, cronologia
           });
           
           // Llenar información básica
           $("#detalleCodigoProducto").text(codigo);
           $("#detalleDescripcionProducto").text(descripcion);
           $("#detalleTransportador").text(transportador);
           $("#detalleCantidadTotal").text(cantidadTotal);
           
           // Llenar tabla de despachos
           var tablaHtml = "";
           detalles.forEach(function(detalle, index) {
               tablaHtml += `
                   <tr>
                       <td><span class="badge bg-blue">${detalle.orden_carga || (index + 1)}</span></td>
                       <td><span class="label label-default">${detalle.numero_despacho}</span></td>
                       <td><i class="fa fa-building"></i> ${detalle.sucursal_origen}</td>
                       <td><span class="badge bg-green">${detalle.cantidad}</span></td>
                       <td>${new Date(detalle.fecha_carga).toLocaleString()}</td>
                   </tr>
               `;
           });
           $("#detalleTablaDespachos").html(tablaHtml);
           
           // Llenar cronología
           var cronologiaHtml = "";
           cronologia.forEach(function(entrada, index) {
               cronologiaHtml += `
                   <div class="timeline-item">
                       <div class="timeline-marker bg-blue"></div>
                       <div class="timeline-content">
                           <h6 class="timeline-title">Carga #${entrada.orden_carga || (index + 1)}</h6>
                           <p><strong>Despacho:</strong> ${entrada.despacho}</p>
                           <p><strong>Sucursal:</strong> ${entrada.sucursal_origen}</p>
                           <p><strong>Cantidad:</strong> ${entrada.cantidad_agregada || entrada.total_cantidad}</p>
                           <p><strong>Fecha:</strong> ${new Date(entrada.fecha).toLocaleString()}</p>
                       </div>
                   </div>
               `;
           });
           $("#detalleCronologia").html(cronologiaHtml);
           
           // Mostrar modal
           $("#modalDetalleProducto").modal("show");
       });
       
       // Event listener para botón de descarga desde detalle
       $(document).on("click", "#btnDescargarDesdeDetalle", function(e) {
           e.preventDefault();
           $("#modalDetalleProducto").modal("hide");
           
           // Buscar el botón de descarga correspondiente y hacer clic
           var codigo = $("#detalleCodigoProducto").text();
           $(".btnDescargaDirecta[data-codigo='" + codigo + "']").click();
       });
       
       // Event listener para botones de descarga
       $(document).on("click", ".btnDescargaDirecta", function(e) {
           e.preventDefault();
           
           var codigo = $(this).data('codigo');
           var descripcion = $(this).data('descripcion');
           var cantidad = $(this).data('cantidad');
           var transportador = $(this).data('transportador');
           var detalles = $(this).data('detalles');
           
           console.log("🔍 DEBUG: Iniciando descarga consolidada:", {
               codigo, descripcion, cantidad, transportador, detalles
           });
           
           // Guardar código globalmente
           stockSeleccionado = codigo;
           
           // Llenar modal
           $("#descargaCodigo").text(codigo);
           $("#descargaDescripcion").text(descripcion);
           $("#descargaTransportador").text(transportador);
           $("#descargaOrigen").text("Múltiples sucursales");
           $("#descargaDespacho").text(detalles ? detalles.length + " despachos" : "N/A");
           $("#descargaCantidadDisponible").val(cantidad);
           
           // Configurar máximo en el input
           $("#cantidadDescargar").attr("max", cantidad);
           $("#cantidadDescargar").val("");
           $("#observacionesDescarga").val("");
           
           // Mostrar modal
           $("#modalDescargaDirecta").modal("show");
       });

// Event listener para el formulario de descarga
$(document).on("submit", "#formDescargaDirecta", function(e) {
    e.preventDefault();
    
    var cantidadDescargar = $("#cantidadDescargar").val();
    var observaciones = $("#observacionesDescarga").val();
    
    if(!cantidadDescargar || cantidadDescargar <= 0) {
        alert("Debe ingresar una cantidad válida");
        return;
    }
    
    if(!stockSeleccionado) {
        alert("Error: No se ha seleccionado un producto");
        return;
    }
    
    // Enviar datos por AJAX
    console.log("🔍 DEBUG: Datos a enviar:", {
        stockSeleccionado: stockSeleccionado,
        cantidadDescargar: cantidadDescargar,
        observaciones: observaciones
    });
    
    var datos = new FormData();
    datos.append("descargarStockDirecto", true);
    datos.append("codigoProducto", stockSeleccionado);
    datos.append("cantidadDescargar", cantidadDescargar);
    datos.append("observaciones", observaciones);
    
    console.log("🔍 DEBUG: FormData creado, enviando AJAX...");
    
    $.ajax({
        url: "ajax/stock-transito.ajax.php",
        method: "POST",
        data: datos,
        cache: false,
        contentType: false,
        processData: false,
        success: function(respuesta) {
            var respuestaJSON = JSON.parse(respuesta);
            
            if(respuestaJSON.success) {
                swal({
                    type: "success",
                    title: "Descarga Exitosa",
                    text: respuestaJSON.message,
                    showConfirmButton: true,
                    confirmButtonText: "Cerrar"
                }).then(function() {
                    // Recargar la página para actualizar los datos
                    location.reload();
                });
            } else {
                swal({
                    type: "error",
                    title: "Error en la Descarga",
                    text: respuestaJSON.error,
                    showConfirmButton: true,
                    confirmButtonText: "Cerrar"
                });
            }
        },
        error: function() {
            swal({
                type: "error",
                title: "Error de Conexión",
                text: "No se pudo procesar la descarga",
                showConfirmButton: true,
                confirmButtonText: "Cerrar"
            });
        }
    });
});

// Incluir script limpio
</script>
<script src="vistas/js/stock-transito-clean.js"></script>
