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
                                                   <div style="line-height: 1.3;">
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
                                                   <button class="btn btn-info btn-xs btnVerDetalleStockTransito" 
                                                           data-codigo="<?php echo $producto['codigo_producto']; ?>"
                                                           data-descripcion="<?php echo $producto['descripcion_producto']; ?>"
                                                           data-detalles='<?php echo json_encode($producto['detalles']); ?>'
                                                           data-cronologia='<?php echo json_encode($producto['cronologia_completa']); ?>'
                                                           data-cantidad-total="<?php echo $producto['cantidad_total']; ?>"
                                                           data-transportador="<?php echo $producto['nombre_transportador']; ?>"
                                                           title="Ver Detalle">
                                                       <i class="fa fa-info-circle"></i>
                                                   </button>
                                                   <button class="btn btn-success btn-xs btnDescargaDirecta" 
                                                           data-codigo="<?php echo $producto['codigo_producto']; ?>"
                                                           data-descripcion="<?php echo $producto['descripcion_producto']; ?>"
                                                           data-cantidad="<?php echo $producto['cantidad_total']; ?>"
                                                           data-transportador="<?php echo $producto['nombre_transportador']; ?>"
                                                           data-detalles='<?php echo json_encode($producto['detalles']); ?>'
                                                           title="Descargar">
                                                       <i class="fa fa-download"></i>
                                                   </button>
                                                   <?php if($_SESSION["perfil"] == "Administrador"): ?>
                                                   <button class="btn btn-danger btn-xs btnEliminarStock" 
                                                           data-codigo="<?php echo $producto['codigo_producto']; ?>"
                                                           data-descripcion="<?php echo $producto['descripcion_producto']; ?>"
                                                           data-cantidad="<?php echo $producto['cantidad_total']; ?>"
                                                           data-transportador="<?php echo $producto['nombre_transportador']; ?>"
                                                           data-detalles='<?php echo json_encode($producto['detalles']); ?>'
                                                           title="Eliminar">
                                                       <i class="fa fa-trash"></i>
                                                   </button>
                                                   <?php endif; ?>
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

<!-- MODAL ELIMINAR STOCK EN TRÁNSITO -->
<div class="modal fade" id="modalEliminarStock" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">
                    <i class="fa fa-trash"></i> Eliminar Stock en Tránsito
                </h4>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <i class="fa fa-warning"></i>
                    <strong>¡Atención!</strong> Esta acción eliminará permanentemente el stock en tránsito del producto seleccionado.
                </div>
                
                <div class="row">
                    <div class="col-md-12">
                        <h4>Información del Producto:</h4>
                        <table class="table table-bordered">
                            <tr>
                                <td><strong>Código:</strong></td>
                                <td id="eliminarCodigo">-</td>
                            </tr>
                            <tr>
                                <td><strong>Descripción:</strong></td>
                                <td id="eliminarDescripcion">-</td>
                            </tr>
                            <tr>
                                <td><strong>Transportador:</strong></td>
                                <td id="eliminarTransportador">-</td>
                            </tr>
                            <tr>
                                <td><strong>Cantidad Total:</strong></td>
                                <td id="eliminarCantidad">-</td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="motivoEliminacion">Motivo de Eliminación:</label>
                            <textarea class="form-control" id="motivoEliminacion" rows="3" 
                                      placeholder="Escriba el motivo de la eliminación..." required></textarea>
                        </div>
                    </div>
                </div>
                
                <input type="hidden" id="eliminarCodigoProducto">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default pull-left" data-dismiss="modal">
                    <i class="fa fa-times"></i> Cancelar
                </button>
                <button type="button" class="btn btn-danger" id="btnConfirmarEliminarStock">
                    <i class="fa fa-trash"></i> Eliminar Stock
                </button>
            </div>
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
       
         /* Botones de acción - Estilo como despachos */
         .btn-xs {
             margin-right: 2px;
         }
         
         .btn-xs:last-child {
             margin-right: 0;
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
           
             .btn-xs {
                 font-size: 10px;
                 padding: 3px 6px;
             }
       }
</style>

<script>
// Variable global para almacenar el stock seleccionado
var stockSeleccionado = null;
var timeoutBusqueda;

// Incluir script unificado
</script>
<script src="vistas/js/stock-transito-unificado.js"></script>

