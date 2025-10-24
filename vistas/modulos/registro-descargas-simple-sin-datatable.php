<?php
/*=============================================
REGISTRO DE DESCARGAS SIMPLE - SIN DATATABLE
=============================================*/

// Incluir conexión
require_once "../../modelos/conexion.php";

try {
    $conexion = Conexion::conectar();
    
    // Consulta simple para obtener los datos
    $stmt = $conexion->prepare("
        SELECT 
            id,
            DATE_FORMAT(fecha_descarga, '%d/%m/%Y %H:%i:%s') as fecha_hora,
            codigo_producto,
            descripcion_producto,
            cantidad_descargada,
            usuario_nombre,
            transportador_nombre,
            sucursal_nombre,
            numero_despacho,
            observaciones
        FROM registro_descargas_stock_transito 
        ORDER BY created_at DESC
    ");
    $stmt->execute();
    $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    $datos = [];
    $error = $e->getMessage();
}
?>

<div class="content-wrapper">
    <section class="content-header">
        <h1>
            Registro de Descargas
            <small>Stock en Tránsito</small>
        </h1>
    </section>

    <section class="content">
        <!-- Info Boxes -->
        <div class="row">
            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-aqua">
                    <div class="inner">
                        <h3><?php echo count($datos); ?></h3>
                        <p>Total Descargas</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-download"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-green">
                    <div class="inner">
                        <h3><?php echo array_sum(array_column($datos, 'cantidad_descargada')); ?></h3>
                        <p>Total Cantidad</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-cubes"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-yellow">
                    <div class="inner">
                        <h3><?php echo count(array_unique(array_column($datos, 'codigo_producto'))); ?></h3>
                        <p>Productos Únicos</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-tags"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-red">
                    <div class="inner">
                        <h3><?php echo count(array_unique(array_column($datos, 'usuario_nombre'))); ?></h3>
                        <p>Usuarios Únicos</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-users"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla Simple -->
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header">
                        <h3 class="box-title">Registro de Descargas</h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-primary btn-sm" onclick="location.reload()">
                                <i class="fa fa-refresh"></i> Actualizar
                            </button>
                        </div>
                    </div>
                    <div class="box-body">
                        <?php if(isset($error)): ?>
                            <div class="alert alert-danger">
                                <strong>Error:</strong> <?php echo $error; ?>
                            </div>
                        <?php elseif(count($datos) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Fecha y Hora</th>
                                            <th>Código Producto</th>
                                            <th>Descripción</th>
                                            <th>Cantidad</th>
                                            <th>Usuario</th>
                                            <th>Transportador</th>
                                            <th>Sucursal</th>
                                            <th>Despacho</th>
                                            <th>Observaciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($datos as $dato): ?>
                                            <tr>
                                                <td><?php echo $dato['id']; ?></td>
                                                <td><?php echo $dato['fecha_hora']; ?></td>
                                                <td><?php echo $dato['codigo_producto']; ?></td>
                                                <td><?php echo substr($dato['descripcion_producto'], 0, 50) . '...'; ?></td>
                                                <td><?php echo $dato['cantidad_descargada']; ?></td>
                                                <td><?php echo $dato['usuario_nombre']; ?></td>
                                                <td><?php echo $dato['transportador_nombre']; ?></td>
                                                <td><?php echo $dato['sucursal_nombre']; ?></td>
                                                <td><?php echo $dato['numero_despacho']; ?></td>
                                                <td><?php echo $dato['observaciones']; ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <strong>Info:</strong> No hay registros de descargas.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
