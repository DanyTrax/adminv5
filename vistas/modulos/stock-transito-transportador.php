<?php
// Vista específica para transportadores - Solo sus productos
$despachos = ControladorStockTransito::ctrMostrarStockPorTransportador($_SESSION["id"]);
$totalDespachos = count($despachos);
$totalProductos = 0;
foreach($despachos as $productos) {
    $totalProductos += count($productos);
}
?>

<div class="content-wrapper">
    <section class="content-header">
        <h1>
            🚚 Mis Productos en Tránsito
            <small>Gestión de productos asignados</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li class="active">Stock en Tránsito</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <!-- Información del Transportador -->
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-truck"></i> Información del Transportador
                        </h3>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="info-box bg-blue">
                                    <span class="info-box-icon"><i class="fa fa-user"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Transportador</span>
                                        <span class="info-box-number"><?php echo $_SESSION["nombre"]; ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-box bg-green">
                                    <span class="info-box-icon"><i class="fa fa-cubes"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Despachos Activos</span>
                                        <span class="info-box-number"><?php echo $totalDespachos; ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-box bg-yellow">
                                    <span class="info-box-icon"><i class="fa fa-box"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Productos Totales</span>
                                        <span class="info-box-number"><?php echo $totalProductos; ?></span>
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
                    </div>
                </div>

                <?php if(empty($despachos)): ?>
                    <!-- Sin productos -->
                    <div class="box box-warning">
                        <div class="box-body text-center">
                            <i class="fa fa-info-circle fa-3x text-muted"></i>
                            <h3 class="text-muted">No tienes productos en tránsito</h3>
                            <p class="text-muted">Los productos aparecerán aquí cuando se te asignen despachos.</p>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Lista de Despachos -->
                    <?php foreach($despachos as $numeroDespacho => $productos): ?>
                        <?php 
                        $primerProducto = $productos[0];
                        $totalCantidad = array_sum(array_column($productos, 'cantidad_disponible'));
                        ?>
                        <div class="box box-success despacho-card">
                            <div class="box-header with-border">
                                <h3 class="box-title">
                                    <i class="fa fa-shipping-fast"></i> 
                                    Despacho: <?php echo $numeroDespacho; ?>
                                </h3>
                                <div class="box-tools pull-right">
                                    <span class="label label-success"><?php echo count($productos); ?> productos</span>
                                    <span class="label label-info"><?php echo $totalCantidad; ?> unidades</span>
                                </div>
                            </div>
                            <div class="box-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong><i class="fa fa-map-marker"></i> Origen:</strong> 
                                        <?php echo $primerProducto['sucursal_origen']; ?><br>
                                        <strong><i class="fa fa-calendar"></i> Fecha:</strong> 
                                        <?php echo date('d/m/Y H:i', strtotime($primerProducto['fecha_creacion'])); ?><br>
                                        <strong><i class="fa fa-flag"></i> Estado:</strong> 
                                        <span class="label label-primary"><?php echo ucfirst($primerProducto['estado_despacho']); ?></span>
                                    </div>
                                    <div class="col-md-6">
                                        <strong><i class="fa fa-truck"></i> Transportador:</strong> 
                                        <?php echo $primerProducto['nombre_transportador']; ?><br>
                                        <strong><i class="fa fa-clock-o"></i> Creado:</strong> 
                                        <?php echo date('d/m/Y H:i', strtotime($primerProducto['fecha_carga'])); ?>
                                    </div>
                                </div>
                                
                                <!-- Tabla de Productos -->
                                <div class="table-responsive" style="margin-top: 15px;">
                                    <table class="table table-striped table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Código</th>
                                                <th>Descripción</th>
                                                <th>Cantidad Disponible</th>
                                                <th>Estado</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($productos as $producto): ?>
                                                <tr>
                                                    <td><strong><?php echo $producto['codigo_producto']; ?></strong></td>
                                                    <td><?php echo $producto['descripcion_producto']; ?></td>
                                                    <td>
                                                        <span class="badge bg-blue"><?php echo $producto['cantidad_disponible']; ?></span>
                                                    </td>
                                                    <td>
                                                        <?php if($producto['cantidad_disponible'] > 0): ?>
                                                            <span class="label label-success">Disponible</span>
                                                        <?php else: ?>
                                                            <span class="label label-danger">Agotado</span>
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
                <?php endif; ?>
            </div>
        </div>
    </section>
</div>

<style>
.despacho-card {
    margin-bottom: 20px;
    border-left: 4px solid #28a745;
}

.despacho-card .box-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

.info-box {
    margin-bottom: 0;
}

.table th {
    background-color: #f8f9fa;
    font-weight: bold;
}
</style>
