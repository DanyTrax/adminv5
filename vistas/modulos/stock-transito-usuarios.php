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
                                    <input type="text" class="form-control" id="buscarProducto" placeholder="Código o descripción...">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>&nbsp;</label><br>
                                    <button class="btn btn-primary" onclick="aplicarFiltros()">
                                        <i class="fa fa-search"></i> Buscar
                                    </button>
                                    <button class="btn btn-default" onclick="limpiarFiltros()">
                                        <i class="fa fa-refresh"></i> Limpiar
                                    </button>
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
                    <!-- Lista de Transportadores -->
                    <?php foreach($transportadores as $transportadorId => $productos): ?>
                        <?php 
                        $primerProducto = $productos[0];
                        $totalCantidad = array_sum(array_column($productos, 'cantidad_disponible'));
                        ?>
                        <div class="box box-info transportador-section">
                            <div class="box-header with-border">
                                <h3 class="box-title">
                                    <i class="fa fa-truck"></i> 
                                    <?php echo $primerProducto['nombre_transportador']; ?>
                                </h3>
                                <div class="box-tools pull-right">
                                    <span class="label label-info"><?php echo count($productos); ?> productos</span>
                                    <span class="label label-success"><?php echo $totalCantidad; ?> unidades</span>
                                </div>
                            </div>
                            <div class="box-body">
                                <!-- Grid de Productos -->
                                <div class="row productos-grid">
                                    <?php foreach($productos as $producto): ?>
                                        <div class="col-md-6 col-lg-4 producto-card" data-codigo="<?php echo strtolower($producto['codigo_producto']); ?>" data-descripcion="<?php echo strtolower($producto['descripcion_producto']); ?>">
                                            <div class="box box-solid box-primary">
                                                <div class="box-header with-border">
                                                    <h3 class="box-title">
                                                        <i class="fa fa-cube"></i> 
                                                        <?php echo $producto['codigo_producto']; ?>
                                                    </h3>
                                                </div>
                                                <div class="box-body">
                                                    <p><strong>Descripción:</strong><br>
                                                    <?php echo $producto['descripcion_producto']; ?></p>
                                                    
                                                    <p><strong>Despacho:</strong> <?php echo $producto['numero_despacho']; ?></p>
                                                    <p><strong>Origen:</strong> <?php echo $producto['sucursal_origen']; ?></p>
                                                    
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <strong>Cantidad:</strong><br>
                                                            <span class="badge bg-blue" style="font-size: 16px;">
                                                                <?php echo $producto['cantidad_disponible']; ?>
                                                            </span>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <strong>Estado:</strong><br>
                                                            <span class="label label-success">Disponible</span>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="text-center" style="margin-top: 15px;">
                                                        <button class="btn btn-success btn-sm btnDescargaDirecta" 
                                                                data-id="<?php echo $producto['id']; ?>"
                                                                data-codigo="<?php echo $producto['codigo_producto']; ?>"
                                                                data-descripcion="<?php echo $producto['descripcion_producto']; ?>"
                                                                data-cantidad="<?php echo $producto['cantidad_disponible']; ?>"
                                                                data-despacho="<?php echo $producto['numero_despacho']; ?>"
                                                                data-origen="<?php echo $producto['sucursal_origen']; ?>"
                                                                data-transportador="<?php echo $producto['nombre_transportador']; ?>">
                                                            <i class="fa fa-download"></i> Descargar
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>
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
    margin-bottom: 25px;
    border-left: 4px solid #17a2b8;
}

.transportador-section .box-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

.productos-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 15px;
}

.producto-card {
    margin-bottom: 15px;
}

.producto-card .box {
    height: 100%;
    transition: transform 0.2s;
}

.producto-card .box:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
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
</style>

<script>
// Variable global para almacenar el ID del stock seleccionado
var stockSeleccionado = null;

// Función para filtrar por transportador
function filtrarPorTransportador() {
    var transportadorId = document.getElementById('filtroTransportador').value;
    var url = new URL(window.location);
    
    if(transportadorId) {
        url.searchParams.set('transportador', transportadorId);
    } else {
        url.searchParams.delete('transportador');
    }
    
    window.location.href = url.toString();
}

// Función para aplicar filtros
function aplicarFiltros() {
    var transportadorId = document.getElementById('filtroTransportador').value;
    var buscarProducto = document.getElementById('buscarProducto').value.toLowerCase();
    
    // Filtrar por transportador
    if(transportadorId) {
        filtrarPorTransportador();
        return;
    }
    
    // Filtrar por búsqueda de producto
    if(buscarProducto) {
        var productos = document.querySelectorAll('.producto-card');
        productos.forEach(function(producto) {
            var codigo = producto.getAttribute('data-codigo');
            var descripcion = producto.getAttribute('data-descripcion');
            
            if(codigo.includes(buscarProducto) || descripcion.includes(buscarProducto)) {
                producto.style.display = 'block';
            } else {
                producto.style.display = 'none';
            }
        });
    }
}

// Función para limpiar filtros
function limpiarFiltros() {
    document.getElementById('filtroTransportador').value = '';
    document.getElementById('buscarProducto').value = '';
    
    var productos = document.querySelectorAll('.producto-card');
    productos.forEach(function(producto) {
        producto.style.display = 'block';
    });
    
    // Recargar página sin filtros
    var url = new URL(window.location);
    url.searchParams.delete('transportador');
    window.location.href = url.toString();
}

// Event listener para botones de descarga
$(document).on("click", ".btnDescargaDirecta", function(e) {
    e.preventDefault();
    
    var id = $(this).data('id');
    var codigo = $(this).data('codigo');
    var descripcion = $(this).data('descripcion');
    var cantidad = $(this).data('cantidad');
    var despacho = $(this).data('despacho');
    var origen = $(this).data('origen');
    var transportador = $(this).data('transportador');
    
    // Guardar ID globalmente
    stockSeleccionado = id;
    
    // Llenar modal
    $("#descargaCodigo").text(codigo);
    $("#descargaDescripcion").text(descripcion);
    $("#descargaTransportador").text(transportador);
    $("#descargaOrigen").text(origen);
    $("#descargaDespacho").text(despacho);
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
    var datos = new FormData();
    datos.append("descargarStockDirecto", true);
    datos.append("idStockTransito", stockSeleccionado);
    datos.append("cantidadDescargar", cantidadDescargar);
    datos.append("observaciones", observaciones);
    
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
</script>
