<?php

if($_SESSION["perfil"] == "Limitado"){
    echo '<script>
        window.location = "inicio";
    </script>';
    return;
}

?>

<div class="content-wrapper">
    <section class="content-header">
        <h1>
            Crear Despacho
            <small>Nuevo despacho de mercancía</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="despachos"><i class="fa fa-truck"></i> Despachos</a></li>
            <li class="active">Crear Despacho</li>
        </ol>
    </section>

    <section class="content">
        
        <form role="form" method="post" id="formCrearDespacho">
            
            <div class="row">
                
                <!-- COLUMNA IZQUIERDA - INFORMACIÓN GENERAL -->
                <div class="col-md-5">
                    
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title">
                                <i class="fa fa-info-circle"></i> Información del Despacho
                            </h3>
                        </div>
                        <div class="box-body">
                            
                            <!-- INFORMACIÓN BÁSICA -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>N° Despacho:</label>
                                        <input type="text" class="form-control" id="numeroDespacho" 
                                               value="Se genera automáticamente" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Fecha:</label>
                                        <input type="text" class="form-control" 
                                               value="<?php echo date('d/m/Y H:i'); ?>" readonly>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Sucursal Origen:</label>
                                        <?php
                                        require_once "controladores/sucursales.controlador.php";
                                        $sucursalLocal = ControladorSucursales::ctrObtenerConfiguracionLocal();
                                        $nombreSucursal = $sucursalLocal ? $sucursalLocal['nombre'] : 'Sucursal Local';
                                        ?>
                                        <input type="text" class="form-control" 
                                               value="<?php echo $nombreSucursal; ?>" readonly>
                                        <input type="hidden" name="nombreSucursalOrigen" value="<?php echo $nombreSucursal; ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label>Usuario Creador:</label>
                                <input type="text" class="form-control" 
                                       value="<?php echo $_SESSION['nombre']; ?>" readonly>
                                <input type="hidden" name="idUsuarioCreador" value="<?php echo $_SESSION['id']; ?>">
                                <input type="hidden" name="nombreUsuarioCreador" value="<?php echo $_SESSION['nombre']; ?>">
                            </div>
                            
                        </div>
                    </div>
                    
                    <!-- BUSCAR SOLICITUD -->
                    <div class="box box-success">
                        <div class="box-header with-border">
                            <h3 class="box-title">
                                <i class="fa fa-search"></i> Buscar Solicitud de Stock
                            </h3>
                            <div class="box-tools pull-right">
                                <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                    <i class="fa fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="box-body">
                            
                            <div class="form-group">
                                <label>Número de Solicitud:</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="numeroSolicitudBuscar" 
                                           placeholder="Ej: SOL000001">
                                    <span class="input-group-btn">
                                        <button type="button" class="btn btn-success" onclick="buscarSolicitud()">
                                            <i class="fa fa-search"></i> Buscar
                                        </button>
                                    </span>
                                </div>
                                <small class="text-muted">
                                    <i class="fa fa-info-circle"></i> 
                                    Los productos de la solicitud se agregarán automáticamente
                                </small>
                            </div>
                            
                            <!-- INFORMACIÓN DE SOLICITUD ENCONTRADA -->
                            <div id="infoSolicitudEncontrada" style="display: none;">
                                <div class="alert alert-success">
                                    <h4><i class="fa fa-check"></i> Solicitud Encontrada</h4>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <strong>Número:</strong> <span id="numeroSolicitudInfo"></span><br>
                                            <strong>Sucursal:</strong> <span id="sucursalSolicitudInfo"></span><br>
                                        </div>
                                        <div class="col-md-6">
                                            <strong>Productos:</strong> <span id="totalProductosSolicitudInfo"></span><br>
                                            <strong>Cantidad:</strong> <span id="totalCantidadSolicitudInfo"></span><br>
                                        </div>
                                    </div>
                                    <div class="row" style="margin-top: 10px;">
                                        <div class="col-md-12">
                                            <button type="button" class="btn btn-success btn-sm" onclick="agregarProductosSolicitud()">
                                                <i class="fa fa-plus"></i> Agregar Productos de esta Solicitud
                                            </button>
                                            <button type="button" class="btn btn-default btn-sm" onclick="limpiarSolicitud()">
                                                <i class="fa fa-times"></i> Limpiar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                        </div>
                    </div>
                    
                    <!-- AGREGAR PRODUCTO MANUAL -->
                    <div class="box box-warning">
                        <div class="box-header with-border">
                            <h3 class="box-title">
                                <i class="fa fa-plus"></i> Agregar Producto Manual
                            </h3>
                            <div class="box-tools pull-right">
                                <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                    <i class="fa fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="box-body">
                            
                            <div class="form-group">
                                <label>Buscar Producto:</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="buscarProductoInput" 
                                           placeholder="Código o descripción del producto">
                                    <span class="input-group-btn">
                                        <button type="button" class="btn btn-warning" onclick="mostrarListaProductos()">
                                            <i class="fa fa-list"></i> Lista
                                        </button>
                                    </span>
                                </div>
                                <div id="resultadosBusquedaProducto" class="list-group" style="display: none; max-height: 200px; overflow-y: auto; margin-top: 5px;">
                                    <!-- Resultados de búsqueda se cargan aquí -->
                                </div>
                            </div>
                            
                            <div id="productoSeleccionadoInfo" style="display: none;">
                                <div class="alert alert-info">
                                    <h4><i class="fa fa-cube"></i> Producto Seleccionado</h4>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <strong>Código:</strong> <span id="codigoProductoSeleccionado"></span><br>
                                            <strong>Stock Actual:</strong> <span id="stockProductoSeleccionado" class="text-bold text-green"></span><br>
                                        </div>
                                        <div class="col-md-6">
                                            <strong>Descripción:</strong><br>
                                            <span id="descripcionProductoSeleccionado"></span>
                                        </div>
                                    </div>
                                    
                                    <div class="row" style="margin-top: 15px;">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Cantidad a Despachar:</label>
                                                <input type="number" class="form-control" id="cantidadProductoDespachar" 
                                                       min="1" value="1">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Observaciones:</label>
                                                <input type="text" class="form-control" id="observacionProductoDespachar" 
                                                       placeholder="Opcional">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-12">
                                            <button type="button" class="btn btn-warning" onclick="agregarProductoADespacho()">
                                                <i class="fa fa-plus"></i> Agregar al Despacho
                                            </button>
                                            <button type="button" class="btn btn-default" onclick="limpiarProductoSeleccionado()">
                                                <i class="fa fa-times"></i> Limpiar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                        </div>
                    </div>
                    
                    <!-- OBSERVACIONES GENERALES -->
                    <div class="box box-info">
                        <div class="box-header with-border">
                            <h3 class="box-title">
                                <i class="fa fa-comment"></i> Observaciones Generales
                            </h3>
                        </div>
                        <div class="box-body">
                            <div class="form-group">
                                <textarea class="form-control" name="detalleAdicional" rows="4" 
                                         placeholder="Observaciones adicionales sobre este despacho (opcional)..."></textarea>
                            </div>
                        </div>
                    </div>
                    
                </div>
                
                <!-- COLUMNA DERECHA - PRODUCTOS DEL DESPACHO -->
                <div class="col-md-7">
                    
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title">
                                <i class="fa fa-list"></i> Productos a Despachar
                            </h3>
                            <div class="box-tools pull-right">
                                <span class="label label-primary" id="contadorProductos">0 productos</span>
                                <span class="label label-success" id="contadorCantidad">0 unidades</span>
                            </div>
                        </div>
                        <div class="box-body">
                            
                            <!-- TABLA DE PRODUCTOS -->
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped" id="tablaProductosDespacho">
                                    <thead class="bg-primary">
                                        <tr>
                                            <th width="50px">#</th>
                                            <th width="100px">Código</th>
                                            <th>Descripción</th>
                                            <th width="80px">Cantidad</th>
                                            <th width="60px">Stock</th>
                                            <th>Observación</th>
                                            <th width="80px">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody id="listaProductosDespacho">
                                        <tr id="filaVaciaProductos">
                                            <td colspan="7" class="text-center text-muted">
                                                <i class="fa fa-info-circle"></i>
                                                No hay productos agregados al despacho
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tfoot id="totalDespacho" style="display: none;">
                                        <tr class="bg-light">
                                            <td colspan="3"><strong>TOTALES:</strong></td>
                                            <td class="text-center"><strong id="totalCantidadDespacho">0</strong></td>
                                            <td></td>
                                            <td></td>
                                            <td><strong id="totalProductosDespacho">0</strong></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            
                            <!-- ALERTA DE VALIDACIÓN -->
                            <div id="alertaValidacionStock" class="alert alert-danger" style="display: none;">
                                <h4><i class="fa fa-exclamation-triangle"></i> Problemas de Stock</h4>
                                <ul id="listaProblemasStock"></ul>
                            </div>
                            
                        </div>
                        <div class="box-footer">
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <a href="despachos" class="btn btn-default">
                                        <i class="fa fa-times"></i> Cancelar
                                    </a>
                                    <button type="button" class="btn btn-info" onclick="validarStockCompleto()">
                                        <i class="fa fa-check"></i> Validar Stock
                                    </button>
                                </div>
                                <div class="col-md-6 text-right">
                                    <button type="submit" name="crearDespacho" class="btn btn-primary btn-lg" id="btnCrearDespacho" disabled>
                                        <i class="fa fa-save"></i> Crear Despacho
                                    </button>
                                </div>
                            </div>
                            
                        </div>
                    </div>
                    
                </div>
                
            </div>
            
            <!-- CAMPOS OCULTOS -->
            <input type="hidden" name="productosDespacho" id="productosDespachoHidden">
            <input type="hidden" name="totalProductos" id="totalProductosHidden" value="0">
            <input type="hidden" name="totalCantidad" id="totalCantidadHidden" value="0">
            <input type="hidden" name="idSolicitudOrigen" id="idSolicitudOrigenHidden">
            
        </form>
        
    </section>
</div>

<!-- MODAL LISTA DE PRODUCTOS -->
<div class="modal fade" id="modalListaProductos" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">
                    <i class="fa fa-list"></i> Seleccionar Producto del Inventario
                </h4>
            </div>
            <div class="modal-body">
                
                <div class="form-group">
                    <label>Filtrar productos:</label>
                    <input type="text" class="form-control" id="filtroProductosModal" 
                           placeholder="Buscar por código o descripción...">
                </div>
                
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="tablaProductosModal">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Descripción</th>
                                <th>Stock</th>
                                <th>Precio</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Se carga dinámicamente -->
                        </tbody>
                    </table>
                </div>
                
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    <i class="fa fa-times"></i> Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL EDITAR CANTIDAD -->
<div class="modal fade" id="modalEditarCantidad" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">
                    <i class="fa fa-edit"></i> Editar Cantidad
                </h4>
            </div>
            <div class="modal-body">
                
                <div class="alert alert-info">
                    <strong>Producto:</strong> <span id="productoEditarInfo"></span><br>
                    <strong>Stock Disponible:</strong> <span id="stockDisponibleEditar"></span> unidades
                </div>
                
                <div class="form-group">
                    <label>Nueva Cantidad:</label>
                    <input type="number" class="form-control" id="nuevaCantidadEditar" min="1">
                </div>
                
                <div class="form-group">
                    <label>Observaciones:</label>
                    <input type="text" class="form-control" id="nuevaObservacionEditar" placeholder="Opcional">
                </div>
                
                <input type="hidden" id="indiceProductoEditar">
                
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    <i class="fa fa-times"></i> Cancelar
                </button>
                <button type="button" class="btn btn-primary" onclick="guardarEdicionCantidad()">
                    <i class="fa fa-save"></i> Guardar Cambios
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ESTILOS CSS -->
<style>
.list-group-item:hover {
    background-color: #f5f5f5;
    cursor: pointer;
}

.producto-sin-stock {
    background-color: #f2dede !important;
    color: #a94442;
}

.producto-stock-bajo {
    background-color: #fcf8e3 !important;
    color: #8a6d3b;
}

.producto-stock-ok {
    background-color: #dff0d8 !important;
    color: #3c763d;
}

.box-tools .label {
    margin-left: 5px;
    font-size: 11px;
}

#tablaProductosDespacho tbody tr {
    transition: all 0.3s ease;
}

.cantidad-editando {
    background-color: #fff3cd;
    border-color: #ffeeba;
}

.alert-dismissible {
    position: relative;
}

.btn-group-xs > .btn, .btn-xs {
    padding: 1px 5px;
    font-size: 12px;
    line-height: 1.5;
    border-radius: 3px;
}

.text-bold {
    font-weight: bold;
}
</style>

<?php
// EJECUTAR CONTROLADOR
$crearDespacho = new ControladorDespachos();
$crearDespacho->ctrCrearDespacho();
?>