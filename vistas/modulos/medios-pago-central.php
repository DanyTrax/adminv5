<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-credit-card"></i>
            Medios de Pago Central
            <small>Gestión Centralizada</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li class="active">Medios de Pago Central</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <!-- Panel Principal -->
            <div class="col-lg-8">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-list"></i>
                            Medios de Pago Centrales
                        </h3>
                        <div class="box-tools pull-right">
                            <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalAgregarMedioPagoCentral" title="Crear Nuevo Medio de Pago">
                                <i class="fa fa-plus"></i> Nuevo Medio
                            </button>
                        </div>
                    </div>
                    <div class="box-body">
                        <!-- Botones de Acción -->
                        <div class="btn-group" style="margin-bottom: 15px;">
                            <button class="btn btn-success" id="btnSincronizarTodos" title="Sincronizar con Sucursales Activas">
                                <i class="fa fa-refresh"></i> Sincronizar Todos
                            </button>
                            <button class="btn btn-info" id="btnAsignarSucursales" title="Asignar a Sucursales Específicas">
                                <i class="fa fa-building"></i> Asignar a Sucursales
                            </button>
                            <button class="btn btn-warning" id="btnGestionarAsignaciones" title="Gestionar Asignaciones Existentes">
                                <i class="fa fa-cogs"></i> Gestionar Asignaciones
                            </button>
                        </div>

                        <!-- Tabla de Medios de Pago Central -->
                        <table class="table table-bordered table-striped dt-responsive tablaMediosPagoCentral" width="100%">
                            <thead>
                                <tr>
                                    <th style="width:10px"><input type="checkbox" id="selectAllMedios"></th>
                                    <th>Código</th>
                                    <th>Nombre</th>
                                    <th>Tipo</th>
                                    <th>Estado</th>
                                    <th>Sucursales Activas</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Datos cargados por AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Panel de Estado por Sucursal -->
            <div class="col-lg-4">
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-building"></i>
                            Estado por Sucursal
                        </h3>
                    </div>
                    <div class="box-body">
                        <div class="form-group">
                            <label for="selectSucursalEstado">Seleccionar Sucursal:</label>
                            <select class="form-control" id="selectSucursalEstado">
                                <option value="">Seleccionar sucursal...</option>
                            </select>
                        </div>
                        
                        <div id="estadoMediosSucursal">
                            <p class="text-muted">Selecciona una sucursal para ver el estado de los medios</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Modal Agregar Medio de Pago Central -->
<div id="modalAgregarMedioPagoCentral" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form role="form" method="post">
                <div class="modal-header" style="background:#3c8dbc; color:white">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title"><i class="fa fa-plus"></i> Agregar Medio de Pago Central</h4>
                </div>
                <div class="modal-body">
                    <div class="box-body">
                        <div class="form-group">
                            <label for="nuevoCodigoMedio">Código:</label>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-key"></i></span>
                                <input type="text" class="form-control input-lg" id="nuevoCodigoMedio" name="nuevoCodigoMedio" placeholder="Ej: EFECTIVO001" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="nuevoNombreMedio">Nombre:</label>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-credit-card"></i></span>
                                <input type="text" class="form-control input-lg" id="nuevoNombreMedio" name="nuevoNombreMedio" placeholder="Ej: Efectivo" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="nuevaDescripcionMedio">Descripción:</label>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-info"></i></span>
                                <input type="text" class="form-control input-lg" id="nuevaDescripcionMedio" name="nuevaDescripcionMedio" placeholder="Descripción del medio de pago">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="nuevoTipoMedio">Tipo:</label>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-tag"></i></span>
                                <select class="form-control input-lg" id="nuevoTipoMedio" name="nuevoTipoMedio">
                                    <option value="efectivo">Efectivo</option>
                                    <option value="tarjeta">Tarjeta</option>
                                    <option value="transferencia">Transferencia</option>
                                    <option value="otro">Otro</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>
                    <button type="submit" class="btn btn-primary" id="btnGuardarMedioPagoCentral">Guardar Medio</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Editar Medio de Pago Central -->
<div id="modalEditarMedioPagoCentral" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form role="form" method="post">
                <div class="modal-header" style="background:#f39c12; color:white">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title"><i class="fa fa-pencil"></i> Editar Medio de Pago Central</h4>
                </div>
                <div class="modal-body">
                    <div class="box-body">
                        <input type="hidden" id="editarIdMedio" name="editarIdMedio">
                        <div class="form-group">
                            <label for="editarCodigoMedio">Código:</label>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-key"></i></span>
                                <input type="text" class="form-control input-lg" id="editarCodigoMedio" name="editarCodigoMedio" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="editarNombreMedio">Nombre:</label>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-credit-card"></i></span>
                                <input type="text" class="form-control input-lg" id="editarNombreMedio" name="editarNombreMedio" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="editarDescripcionMedio">Descripción:</label>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-info"></i></span>
                                <input type="text" class="form-control input-lg" id="editarDescripcionMedio" name="editarDescripcionMedio">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="editarTipoMedio">Tipo:</label>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-tag"></i></span>
                                <select class="form-control input-lg" id="editarTipoMedio" name="editarTipoMedio">
                                    <option value="efectivo">Efectivo</option>
                                    <option value="tarjeta">Tarjeta</option>
                                    <option value="transferencia">Transferencia</option>
                                    <option value="otro">Otro</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="editarEstadoMedio">Estado:</label>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-power-off"></i></span>
                                <select class="form-control input-lg" id="editarEstadoMedio" name="editarEstadoMedio">
                                    <option value="1">Activo</option>
                                    <option value="0">Inactivo</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>
                    <button type="submit" class="btn btn-warning" id="btnActualizarMedioPagoCentral">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Asignar a Sucursales -->
<div class="modal fade" id="modalAsignarSucursales" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">
                    <i class="fa fa-building"></i>
                    Asignar Medios de Pago a Sucursales
                </h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5>Medios Seleccionados:</h5>
                        <div id="mediosSeleccionadosAsignar">
                            <p class="text-muted">Selecciona medios de pago de la tabla</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h5>Sucursales Destino:</h5>
                        <div id="sucursalesDestinoAsignar">
                            <!-- Se carga via AJAX -->
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="btnConfirmarAsignacion">Asignar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Gestionar Asignaciones -->
<div class="modal fade" id="modalGestionarAsignaciones" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">
                    <i class="fa fa-cogs"></i>
                    Gestionar Asignaciones de Medios de Pago
                </h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5>Medios Seleccionados:</h5>
                        <div id="mediosSeleccionadosGestionar">
                            <p class="text-muted">Selecciona medios de pago de la tabla</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h5>Acciones:</h5>
                        <div class="btn-group-vertical" style="width: 100%;">
                            <button class="btn btn-info" id="btnVerEstadoCompleto">
                                <i class="fa fa-eye"></i> Ver Estado Completo
                            </button>
                            <button class="btn btn-warning" id="btnDesactivarTodos">
                                <i class="fa fa-times"></i> Desactivar en Todas las Sucursales
                            </button>
                            <button class="btn btn-danger" id="btnEliminarAsignaciones">
                                <i class="fa fa-trash"></i> Eliminar Todas las Asignaciones
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script src="vistas/js/medios-pago-central.js"></script>