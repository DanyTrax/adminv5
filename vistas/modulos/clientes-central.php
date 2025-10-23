<?php
require_once "controladores/clientes-central.controlador.php";

$sucursales = ControladorClientesCentral::ctrObtenerSucursalesDisponibles();
?>

<div class="content-wrapper">
    <section class="content-header">
        <h1>
            Gestión de Clientes Centrales
            <small>Administración centralizada de clientes</small>
        </h1>
    </section>

    <section class="content">
        <div class="row">
            <!-- ESTADÍSTICAS DEL SISTEMA -->
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-bar-chart"></i> Estadísticas del Sistema
                        </h3>
                    </div>
                    <div class="box-body">
                        <div class="row" id="estadisticasClientes">
                            <div class="col-md-3">
                                <div class="info-box">
                                    <span class="info-box-icon bg-aqua"><i class="fa fa-users"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Clientes Centrales</span>
                                        <span class="info-box-number" id="totalClientesCentral">-</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-box">
                                    <span class="info-box-icon bg-green"><i class="fa fa-building"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Sucursales Activas</span>
                                        <span class="info-box-number" id="sucursalesActivas">-</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-box">
                                    <span class="info-box-icon bg-yellow"><i class="fa fa-check-circle"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Clientes Únicos</span>
                                        <span class="info-box-number" id="clientesUnicos">-</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-box">
                                    <span class="info-box-icon bg-red"><i class="fa fa-exclamation-triangle"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Duplicados Detectados</span>
                                        <span class="info-box-number" id="duplicadosDetectados">-</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- ACCIONES DEL SISTEMA -->
            <div class="col-md-12">
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-cogs"></i> Acciones del Sistema
                        </h3>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="text-center">
                                    <button class="btn btn-primary btn-lg" id="btnNuevoClienteCentral" title="Crear Cliente en Central" style="margin-right: 10px;">
                                        <i class="fa fa-plus"></i> Crear Cliente Central
                                    </button>
                                    <button class="btn btn-success btn-lg" id="btnSincronizarTodosClientes" title="Sincronizar Todos los Clientes" style="margin-left: 10px;">
                                        <i class="fa fa-refresh"></i> Sincronizar Todos
                                    </button>
                                    <br><br>
                                    <p class="text-muted">
                                        <small><strong>El sistema detecta automáticamente duplicados por documento o email</strong></small>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- TABLA DE CLIENTES CENTRALES -->
            <div class="col-md-12">
                <div class="box box-success">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-users"></i> Clientes Centrales
                        </h3>
                    </div>
                    <div class="box-body">
                        <table class="table table-bordered table-striped dt-responsive" id="tablaClientesCentral" width="100%">
                            <thead>
                                <tr>
                                    <th style="width:10px">#</th>
                                    <th>Nombre</th>
                                    <th>Documento</th>
                                    <th>Email</th>
                                    <th>Teléfono</th>
                                    <th>Sucursal Origen</th>
                                    <th>Sucursales Asignadas</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyClientesCentral">
                                <!-- Se carga dinámicamente -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!--=====================================
MODAL CREAR/EDITAR CLIENTE CENTRAL
======================================-->

<div id="modalClienteCentral" class="modal fade" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form role="form" id="formClienteCentral">
                <div class="modal-header" style="background: #3c8dbc; color: white;">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">
                        <i class="fa fa-user"></i> <span id="tituloModalCliente">Crear Cliente Central</span>
                    </h4>
                </div>
                <div class="modal-body">
                    <div class="box-body">
                        
                        <!-- DOCUMENTO -->
                        <div class="form-group">
                            <label for="documentoCliente">Documento *</label>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-id-card"></i></span>
                                <input type="text" class="form-control" id="documentoCliente" name="documento" placeholder="Número de documento" required autocomplete="off">
                            </div>
                            <div class="help-block text-red" id="errorDocumento" style="display: none;"></div>
                        </div>

                        <!-- EMAIL -->
                        <div class="form-group">
                            <label for="emailCliente">Email</label>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-envelope"></i></span>
                                <input type="email" class="form-control" id="emailCliente" name="email" placeholder="Email del cliente" autocomplete="off">
                            </div>
                            <div class="help-block text-red" id="errorEmail" style="display: none;"></div>
                        </div>

                        <!-- NOMBRE -->
                        <div class="form-group">
                            <label for="nombreCliente">Nombre Completo *</label>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-user"></i></span>
                                <input type="text" class="form-control" id="nombreCliente" name="nombre" placeholder="Nombre completo del cliente" required autocomplete="off">
                            </div>
                            <div class="help-block text-red" id="errorNombre" style="display: none;"></div>
                        </div>

                        <!-- TELÉFONO -->
                        <div class="form-group">
                            <label for="telefonoCliente">Teléfono</label>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-phone"></i></span>
                                <input type="text" class="form-control" id="telefonoCliente" name="telefono" placeholder="Teléfono del cliente" autocomplete="off">
                            </div>
                        </div>

                        <!-- DIRECCIÓN -->
                        <div class="form-group">
                            <label for="direccionCliente">Dirección</label>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-map-marker"></i></span>
                                <input type="text" class="form-control" id="direccionCliente" name="direccion" placeholder="Dirección del cliente" autocomplete="off">
                            </div>
                        </div>

                        <!-- FECHA DE NACIMIENTO -->
                        <div class="form-group">
                            <label for="fechaNacimientoCliente">Fecha de Nacimiento</label>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                <input type="date" class="form-control" id="fechaNacimientoCliente" name="fecha_nacimiento" autocomplete="off">
                            </div>
                        </div>

                        <!-- SUCURSALES ASIGNADAS -->
                        <div class="form-group">
                            <label>Sucursales Asignadas</label>
                            <div class="row" id="sucursalesAsignadasCliente">
                                <!-- Se carga dinámicamente -->
                            </div>
                        </div>

                        <!-- INPUT HIDDEN PARA ID -->
                        <input type="hidden" id="idClienteCentral" name="id_central">
                        <input type="hidden" id="esEdicionCliente" name="es_edicion" value="0">

                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Guardar Cliente</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!--=====================================
MODAL CONFIRMAR ELIMINACIÓN
======================================-->

<div id="modalConfirmarEliminacionCliente" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background: #dd4b39; color: white;">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">
                    <i class="fa fa-exclamation-triangle"></i> Confirmar Eliminación
                </h4>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de que deseas eliminar el cliente <strong id="nombreClienteEliminar"></strong>?</p>
                <p class="text-muted">Esta acción eliminará el cliente de todas las sucursales asignadas.</p>
                <input type="hidden" id="idClienteEliminar">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="btnConfirmarEliminacionCliente">Eliminar Cliente</button>
            </div>
        </div>
    </div>
</div>

<!--=====================================
MODAL PROGRESO DE IMPORTACIÓN
======================================-->

<div id="modalProgresoImportacion" class="modal fade" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: #3c8dbc; color: white;">
                <h4 class="modal-title">
                    <i class="fa fa-refresh fa-spin"></i> Importando Clientes...
                </h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="info-box">
                            <span class="info-box-icon bg-aqua"><i class="fa fa-spinner fa-spin"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Estado</span>
                                <span class="info-box-number" id="estadoImportacion">Conectando a sucursales...</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="info-box">
                            <span class="info-box-icon bg-green"><i class="fa fa-check"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Clientes Importados</span>
                                <span class="info-box-number" id="clientesImportados">0</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-box">
                            <span class="info-box-icon bg-yellow"><i class="fa fa-exclamation-triangle"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Clientes Duplicados</span>
                                <span class="info-box-number" id="clientesDuplicados">0</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="box box-info">
                            <div class="box-header with-border">
                                <h3 class="box-title">
                                    <i class="fa fa-building"></i> Progreso por Sucursal
                                </h3>
                            </div>
                            <div class="box-body">
                                <div id="progresoSucursales" style="max-height: 300px; overflow-y: auto;">
                                    <!-- Se carga dinámicamente -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer" id="modalFooterImportacion" style="display: none;">
                <button type="button" class="btn btn-success" data-dismiss="modal" onclick="window.location.reload();">
                    <i class="fa fa-check"></i> Cerrar y Actualizar
                </button>
            </div>
        </div>
    </div>
</div>
