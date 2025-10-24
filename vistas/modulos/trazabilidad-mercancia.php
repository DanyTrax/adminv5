<!--=====================================
TRAZABILIDAD DE MERCANCÍA
======================================-->

<div class="content-wrapper">
    <section class="content-header">
        <h1>
            Trazabilidad de Mercancía
            <small>Historial completo de movimientos</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li class="active">Trazabilidad</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header">
                        <h3 class="box-title">Trazabilidad de Mercancía</h3>
                    </div>
                    <div class="box-body">
                        <!-- Pestañas -->
                        <ul class="nav nav-tabs" id="tabsTrazabilidad">
                            <li class="active"><a href="#tabBusqueda" data-toggle="tab">🔍 Búsqueda General</a></li>
                            <li><a href="#tabDespacho" data-toggle="tab">📊 Por Despacho</a></li>
                            <li><a href="#tabProducto" data-toggle="tab">📦 Por Producto</a></li>
                            <li><a href="#tabReportes" data-toggle="tab">📈 Reportes</a></li>
                        </ul>
                        
                        <!-- Contenido de las pestañas -->
                        <div class="tab-content">
                            
                            <!-- Pestaña 1: Búsqueda General -->
                            <div class="tab-pane active" id="tabBusqueda">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="box box-info">
                                            <div class="box-header">
                                                <h3 class="box-title">🔍 Búsqueda General</h3>
                                            </div>
                                            <div class="box-body">
                                                <div class="row">
                                                    <div class="col-md-3">
                                                        <div class="form-group">
                                                            <label>Número de Despacho:</label>
                                                            <input type="text" id="buscarDespachoGeneral" class="form-control" placeholder="DESP000001">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="form-group">
                                                            <label>Código de Producto:</label>
                                                            <input type="text" id="buscarProductoGeneral" class="form-control" placeholder="001">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="form-group">
                                                            <label>Usuario:</label>
                                                            <input type="text" id="buscarUsuarioGeneral" class="form-control" placeholder="Admin">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="form-group">
                                                            <label>&nbsp;</label>
                                                            <button class="btn btn-primary btn-block" id="btnBuscarGeneral">
                                                                <i class="fa fa-search"></i> Buscar
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Resultados de búsqueda general -->
                                        <div class="box box-success" id="resultadosBusquedaGeneral" style="display: none;">
                                            <div class="box-header">
                                                <h3 class="box-title">📊 Resultados de Búsqueda</h3>
                                            </div>
                                            <div class="box-body">
                                                <div class="table-responsive">
                                                    <table class="table table-bordered table-striped" id="tablaResultadosGenerales">
                                                        <thead>
                                                            <tr>
                                                                <th>Despacho</th>
                                                                <th>Producto</th>
                                                                <th>Usuario</th>
                                                                <th>Fecha</th>
                                                                <th>Estado</th>
                                                                <th>Acciones</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <!-- Se llena dinámicamente -->
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Pestaña 2: Por Despacho -->
                            <div class="tab-pane" id="tabDespacho">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="box box-warning">
                                            <div class="box-header">
                                                <h3 class="box-title">📊 Trazabilidad por Despacho</h3>
                                            </div>
                                            <div class="box-body">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label>Número de Despacho:</label>
                                                            <input type="text" id="buscarDespacho" class="form-control" placeholder="DESP000001">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label>&nbsp;</label>
                                                            <button class="btn btn-warning btn-block" id="btnBuscarDespacho">
                                                                <i class="fa fa-search"></i> Buscar Despacho
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Resultados por despacho -->
                                        <div class="box box-info" id="resultadosDespacho" style="display: none;">
                                            <div class="box-header">
                                                <h3 class="box-title">📦 Información del Despacho</h3>
                                            </div>
                                            <div class="box-body">
                                                <div id="infoDespacho">
                                                    <!-- Se llena dinámicamente -->
                                                </div>
                                                
                                                <div class="table-responsive">
                                                    <table class="table table-bordered table-striped" id="tablaProductosDespacho">
                                                        <thead>
                                                            <tr>
                                                                <th>Producto</th>
                                                                <th>Descripción</th>
                                                                <th>Total</th>
                                                                <th>Descargado</th>
                                                                <th>Pendiente</th>
                                                                <th>Estado</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <!-- Se llena dinámicamente -->
                                                        </tbody>
                                                    </table>
                                                </div>
                                                
                                                <div class="table-responsive">
                                                    <table class="table table-bordered table-striped" id="tablaHistorialDespacho">
                                                        <thead>
                                                            <tr>
                                                                <th>Fecha</th>
                                                                <th>Usuario</th>
                                                                <th>Producto</th>
                                                                <th>Cantidad</th>
                                                                <th>Sucursal</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <!-- Se llena dinámicamente -->
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Pestaña 3: Por Producto -->
                            <div class="tab-pane" id="tabProducto">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="box box-success">
                                            <div class="box-header">
                                                <h3 class="box-title">📦 Trazabilidad por Producto</h3>
                                            </div>
                                            <div class="box-body">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label>Código de Producto:</label>
                                                            <input type="text" id="buscarProducto" class="form-control" placeholder="001">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label>&nbsp;</label>
                                                            <button class="btn btn-success btn-block" id="btnBuscarProducto">
                                                                <i class="fa fa-search"></i> Buscar Producto
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Resultados por producto -->
                                        <div class="box box-info" id="resultadosProducto" style="display: none;">
                                            <div class="box-header">
                                                <h3 class="box-title">📊 Información del Producto</h3>
                                            </div>
                                            <div class="box-body">
                                                <div id="infoProducto">
                                                    <!-- Se llena dinámicamente -->
                                                </div>
                                                
                                                <div class="table-responsive">
                                                    <table class="table table-bordered table-striped" id="tablaDespachosProducto">
                                                        <thead>
                                                            <tr>
                                                                <th>Despacho</th>
                                                                <th>Fecha</th>
                                                                <th>Total</th>
                                                                <th>Descargado</th>
                                                                <th>Pendiente</th>
                                                                <th>Estado</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <!-- Se llena dinámicamente -->
                                                        </tbody>
                                                    </table>
                                                </div>
                                                
                                                <div class="table-responsive">
                                                    <table class="table table-bordered table-striped" id="tablaHistorialProducto">
                                                        <thead>
                                                            <tr>
                                                                <th>Fecha</th>
                                                                <th>Usuario</th>
                                                                <th>Despacho</th>
                                                                <th>Cantidad</th>
                                                                <th>Sucursal</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <!-- Se llena dinámicamente -->
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Pestaña 4: Reportes -->
                            <div class="tab-pane" id="tabReportes">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="box box-primary">
                                            <div class="box-header">
                                                <h3 class="box-title">📈 Reportes de Trazabilidad</h3>
                                                <button class="btn btn-primary pull-right" id="btnActualizarReportes">
                                                    <i class="fa fa-refresh"></i> Actualizar
                                                </button>
                                            </div>
                                            <div class="box-body">
                                                <div class="row">
                                                    <!-- Productos más movidos -->
                                                    <div class="col-md-6">
                                                        <div class="box box-info">
                                                            <div class="box-header">
                                                                <h3 class="box-title">🏆 Productos Más Movidos</h3>
                                                            </div>
                                                            <div class="box-body">
                                                                <div class="table-responsive">
                                                                    <table class="table table-bordered table-striped" id="tablaProductosMovidos">
                                                                        <thead>
                                                                            <tr>
                                                                                <th>Producto</th>
                                                                                <th>Descripción</th>
                                                                                <th>Movimientos</th>
                                                                                <th>Cantidad</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            <!-- Se llena dinámicamente -->
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Usuarios más activos -->
                                                    <div class="col-md-6">
                                                        <div class="box box-success">
                                                            <div class="box-header">
                                                                <h3 class="box-title">👥 Usuarios Más Activos</h3>
                                                            </div>
                                                            <div class="box-body">
                                                                <div class="table-responsive">
                                                                    <table class="table table-bordered table-striped" id="tablaUsuariosActivos">
                                                                        <thead>
                                                                            <tr>
                                                                                <th>Usuario</th>
                                                                                <th>Descargas</th>
                                                                                <th>Cantidad</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            <!-- Se llena dinámicamente -->
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class="row">
                                                    <!-- Despachos incompletos -->
                                                    <div class="col-md-12">
                                                        <div class="box box-warning">
                                                            <div class="box-header">
                                                                <h3 class="box-title">⚠️ Despachos Incompletos</h3>
                                                            </div>
                                                            <div class="box-body">
                                                                <div class="table-responsive">
                                                                    <table class="table table-bordered table-striped" id="tablaDespachosIncompletos">
                                                                        <thead>
                                                                            <tr>
                                                                                <th>Despacho</th>
                                                                                <th>Total Productos</th>
                                                                                <th>Entregados</th>
                                                                                <th>Parciales</th>
                                                                                <th>Pendientes</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            <!-- Se llena dinámicamente -->
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Script para trazabilidad -->
<script src="vistas/js/trazabilidad-mercancia.js"></script>
