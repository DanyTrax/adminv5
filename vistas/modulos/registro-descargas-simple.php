<!--=============================================
REGISTRO DE DESCARGAS SIMPLE
=============================================-->

<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Registro de Descargas
            <small>Stock en Tránsito</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li class="active">Registro de Descargas</li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">
        <!-- Info boxes -->
        <div class="row">
            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-aqua">
                    <div class="inner">
                        <h3 id="totalDescargas">0</h3>
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
                        <h3 id="totalCantidad">0</h3>
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
                        <h3 id="productosUnicos">0</h3>
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
                        <h3 id="usuariosUnicos">0</h3>
                        <p>Usuarios Únicos</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-users"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title">Filtros de Búsqueda</h3>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Fecha Desde:</label>
                                    <input type="date" class="form-control" id="fechaDesde">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Fecha Hasta:</label>
                                    <input type="date" class="form-control" id="fechaHasta">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Código Producto:</label>
                                    <input type="text" class="form-control" id="codigoProducto" placeholder="Buscar por código">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>&nbsp;</label><br>
                                    <button class="btn btn-primary" id="btnFiltrar">
                                        <i class="fa fa-search"></i> Filtrar
                                    </button>
                                    <button class="btn btn-default" id="btnLimpiar">
                                        <i class="fa fa-refresh"></i> Limpiar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de registros -->
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header">
                        <h3 class="box-title">Registro de Descargas</h3>
                    </div>
                    <div class="box-body">
                        <table id="tablaRegistroDescargas" class="table table-bordered table-striped dt-responsive tablaRegistroDescargas" width="100%">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Descripción</th>
                                    <th>Cantidad</th>
                                    <th>Usuario</th>
                                    <th>Sucursal</th>
                                    <th>Transportador</th>
                                    <th>Despacho</th>
                                    <th>Observaciones</th>
                                    <th>Fecha</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script src="vistas/js/registro-descargas-simple.js"></script>