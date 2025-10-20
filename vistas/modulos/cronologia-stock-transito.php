<?php
// Vista de cronología detallada de stock en tránsito
$transportadorId = $_GET["transportador"] ?? $_SESSION["id"];
$productos = ControladorStockTransito::ctrMostrarStockDisponibleUsuarios($transportadorId);
?>

<div class="content-wrapper">
    <section class="content-header">
        <h1>
            📋 Cronología de Stock en Tránsito
            <small>Historial detallado de cargas y descargas</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="stock-transito">Stock en Tránsito</a></li>
            <li class="active">Cronología</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <!-- Filtros -->
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-filter"></i> Filtros de Cronología
                        </h3>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Transportador:</label>
                                    <select class="form-control" id="filtroTransportadorCronologia">
                                        <option value="">Todos los Transportadores</option>
                                        <?php 
                                        $transportadores = ControladorStockTransito::ctrObtenerTransportadores();
                                        foreach($transportadores as $transportador): 
                                        ?>
                                            <option value="<?php echo $transportador['transportador_id']; ?>" 
                                                    <?php echo ($transportadorId == $transportador['transportador_id']) ? 'selected' : ''; ?>>
                                                <?php echo $transportador['nombre_transportador']; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Producto:</label>
                                    <input type="text" class="form-control" id="filtroProductoCronologia" placeholder="Código del producto...">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>&nbsp;</label><br>
                                    <button class="btn btn-primary" onclick="filtrarCronologia()">
                                        <i class="fa fa-search"></i> Filtrar
                                    </button>
                                    <button class="btn btn-default" onclick="limpiarFiltrosCronologia()">
                                        <i class="fa fa-refresh"></i> Limpiar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cronología de Productos -->
                <div id="contenedorCronologia">
                    <?php foreach($productos as $transportadorId => $productosTransportador): ?>
                        <?php foreach($productosTransportador as $producto): ?>
                            <div class="box box-info producto-cronologia" 
                                 data-codigo="<?php echo strtolower($producto['codigo_producto']); ?>">
                                <div class="box-header with-border">
                                    <h3 class="box-title">
                                        <i class="fa fa-cube"></i> 
                                        <strong><?php echo $producto['codigo_producto']; ?></strong> - 
                                        <?php echo $producto['descripcion_producto']; ?>
                                    </h3>
                                    <div class="box-tools pull-right">
                                        <span class="label label-info">
                                            <i class="fa fa-truck"></i> <?php echo $producto['nombre_transportador']; ?>
                                        </span>
                                        <span class="label label-success">
                                            <i class="fa fa-cubes"></i> <?php echo $producto['cantidad_disponible']; ?> unidades
                                        </span>
                                    </div>
                                </div>
                                <div class="box-body">
                                    <!-- Cronología de Cargas -->
                                    <div class="timeline">
                                        <?php 
                                        $cronologia = json_decode($producto['cronologia_carga'] ?? '[]', true);
                                        if(empty($cronologia)) {
                                            $cronologia = [[
                                                'fecha' => $producto['fecha_carga'],
                                                'despacho' => $producto['numero_despacho'],
                                                'sucursal_origen' => $producto['sucursal_origen'],
                                                'cantidad_agregada' => $producto['cantidad_disponible'],
                                                'orden_carga' => $producto['orden_carga'] ?? 1
                                            ]];
                                        }
                                        
                                        // Ordenar por orden de carga (más reciente primero)
                                        usort($cronologia, function($a, $b) {
                                            return ($b['orden_carga'] ?? 0) - ($a['orden_carga'] ?? 0);
                                        });
                                        
                                        foreach($cronologia as $index => $carga): 
                                            $esUltima = $index === 0;
                                            $color = $esUltima ? 'success' : 'info';
                                            $icono = $esUltima ? 'fa-arrow-down' : 'fa-arrow-up';
                                        ?>
                                            <div class="timeline-item">
                                                <div class="timeline-marker bg-<?php echo $color; ?>">
                                                    <i class="fa <?php echo $icono; ?>"></i>
                                                </div>
                                                <div class="timeline-content">
                                                    <div class="timeline-header">
                                                        <h4 class="timeline-title">
                                                            <?php if($esUltima): ?>
                                                                <i class="fa fa-arrow-down text-success"></i> 
                                                                PRÓXIMO EN DESCARGAR
                                                            <?php else: ?>
                                                                <i class="fa fa-arrow-up text-info"></i> 
                                                                Carga #<?php echo $carga['orden_carga'] ?? ($index + 1); ?>
                                                            <?php endif; ?>
                                                        </h4>
                                                        <p class="timeline-time">
                                                            <i class="fa fa-clock-o"></i> 
                                                            <?php echo date('d/m/Y H:i:s', strtotime($carga['fecha'])); ?>
                                                        </p>
                                                    </div>
                                                    <div class="timeline-body">
                                                        <div class="row">
                                                            <div class="col-md-3">
                                                                <strong>Despacho:</strong><br>
                                                                <span class="label label-default">
                                                                    <?php echo $carga['despacho']; ?>
                                                                </span>
                                                            </div>
                                                            <div class="col-md-3">
                                                                <strong>Sucursal Origen:</strong><br>
                                                                <span class="text-muted">
                                                                    <i class="fa fa-building"></i> 
                                                                    <?php echo $carga['sucursal_origen']; ?>
                                                                </span>
                                                            </div>
                                                            <div class="col-md-3">
                                                                <strong>Cantidad:</strong><br>
                                                                <span class="badge bg-blue" style="font-size: 14px;">
                                                                    <?php echo $carga['cantidad_agregada'] ?? $carga['total_cantidad'] ?? 'N/A'; ?>
                                                                </span>
                                                            </div>
                                                            <div class="col-md-3">
                                                                <strong>Estado:</strong><br>
                                                                <?php if($esUltima): ?>
                                                                    <span class="label label-success">
                                                                        <i class="fa fa-check"></i> Disponible
                                                                    </span>
                                                                <?php else: ?>
                                                                    <span class="label label-warning">
                                                                        <i class="fa fa-clock-o"></i> En Cola
                                                                    </span>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    
                                    <!-- Resumen de Jerarquía -->
                                    <div class="alert alert-info" style="margin-top: 20px;">
                                        <h4><i class="fa fa-info-circle"></i> Jerarquía de Descarga (LIFO)</h4>
                                        <p>
                                            <strong>Último en cargar = Primero en descargar</strong><br>
                                            Los productos se descargan en orden inverso al de carga. 
                                            La carga más reciente será la primera en descargarse.
                                        </p>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <strong>Total de cargas:</strong> <?php echo count($cronologia); ?>
                                            </div>
                                            <div class="col-md-6">
                                                <strong>Total unidades:</strong> <?php echo $producto['cantidad_disponible']; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>
</div>

<style>
.producto-cronologia {
    margin-bottom: 30px;
}

.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    margin-bottom: 30px;
}

.timeline-marker {
    position: absolute;
    left: -30px;
    top: 0;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 10px;
}

.timeline-content {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    padding: 15px;
    margin-left: 10px;
}

.timeline-header {
    border-bottom: 1px solid #dee2e6;
    padding-bottom: 10px;
    margin-bottom: 15px;
}

.timeline-title {
    margin: 0;
    font-size: 16px;
}

.timeline-time {
    margin: 5px 0 0 0;
    color: #6c757d;
    font-size: 12px;
}

.timeline-body {
    font-size: 14px;
}

.bg-success {
    background-color: #28a745 !important;
}

.bg-info {
    background-color: #17a2b8 !important;
}

.alert {
    border-left: 4px solid #17a2b8;
}
</style>

<script>
// Función para filtrar cronología
function filtrarCronologia() {
    var transportadorId = document.getElementById('filtroTransportadorCronologia').value;
    var codigoProducto = document.getElementById('filtroProductoCronologia').value.toLowerCase();
    
    var productos = document.querySelectorAll('.producto-cronologia');
    
    productos.forEach(function(producto) {
        var codigo = producto.getAttribute('data-codigo');
        var mostrar = true;
        
        if(codigoProducto && !codigo.includes(codigoProducto)) {
            mostrar = false;
        }
        
        producto.style.display = mostrar ? 'block' : 'none';
    });
}

// Función para limpiar filtros
function limpiarFiltrosCronologia() {
    document.getElementById('filtroTransportadorCronologia').value = '';
    document.getElementById('filtroProductoCronologia').value = '';
    
    var productos = document.querySelectorAll('.producto-cronologia');
    productos.forEach(function(producto) {
        producto.style.display = 'block';
    });
}

// Event listeners
$(document).ready(function() {
    $('#filtroProductoCronologia').on('input', function() {
        filtrarCronologia();
    });
    
    $('#filtroTransportadorCronologia').on('change', function() {
        var transportadorId = $(this).val();
        if(transportadorId) {
            window.location.href = 'cronologia-stock-transito?transportador=' + transportadorId;
        } else {
            window.location.href = 'cronologia-stock-transito';
        }
    });
});
</script>
