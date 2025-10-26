<?php
/*=============================================
MÓDULO DE GESTIÓN CENTRAL DE MEDIOS DE PAGO
=============================================*/

// Incluir plantilla
require_once "vistas/plantilla.php";

// Verificar permisos
if (!in_array($_SESSION["perfil"], ["Administrador", "Especial"])) {
    echo '<script>window.location = "inicio";</script>';
    return;
}
?>

<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-credit-card"></i>
            Medios de Pago Central
<<<<<<< HEAD
            <small>Gestión Centralizada</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
=======
            <small>Gestión Centralizada - BD Central</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="gestion-central"><i class="fa fa-cogs"></i> Gestion Central</a></li>
>>>>>>> d87c20f53cc1d45f1dc53f773d4e1987fddd46a7
            <li class="active">Medios de Pago Central</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <!-- Botones de Acción -->
            <div class="col-lg-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-cogs"></i>
                            Gestión de Medios de Pago
                        </h3>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-12">
                                <button class="btn btn-primary" id="btnNuevoMedioPago" title="Crear Nuevo Medio de Pago">
                                    <i class="fa fa-plus"></i> Nuevo Medio de Pago
                                </button>
<<<<<<< HEAD
                                <button class="btn btn-success" id="btnActivarSucursales" title="Activar Medios en Sucursales">
                                    <i class="fa fa-check"></i> Activar en Sucursales
                                </button>
                                <button class="btn btn-warning" id="btnDesactivarSucursales" title="Desactivar Medios en Sucursales">
                                    <i class="fa fa-times"></i> Desactivar en Sucursales
                                </button>
                                <button class="btn btn-info" id="btnVerEstado" title="Ver Estado por Sucursal">
                                    <i class="fa fa-eye"></i> Ver Estado
=======
                                <button class="btn btn-success" id="btnAsignarSucursales" title="Asignar Medios a Sucursales">
                                    <i class="fa fa-building"></i> Asignar a Sucursales
                                </button>
                                <button class="btn btn-info" id="btnCopiarMasivo" title="Copia Masiva de Medios">
                                    <i class="fa fa-copy"></i> Copia Masiva
                                </button>
                                <button class="btn btn-warning" id="btnSincronizarTodos" title="Sincronizar Todos los Medios">
                                    <i class="fa fa-refresh"></i> Sincronizar Todos
>>>>>>> d87c20f53cc1d45f1dc53f773d4e1987fddd46a7
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Lista de Medios de Pago Central -->
            <div class="col-lg-8">
                <div class="box box-success">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-list"></i>
                            Medios de Pago Centrales
                        </h3>
                    </div>
                    <div class="box-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover" id="tablaMediosPagoCentral">
                                <thead>
                                    <tr>
                                        <th width="10%">
                                            <input type="checkbox" id="selectAllMedios" title="Seleccionar Todos">
                                        </th>
                                        <th width="15%">Código</th>
                                        <th width="25%">Nombre</th>
                                        <th width="20%">Tipo</th>
                                        <th width="15%">Estado</th>
                                        <th width="15%">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Los datos se cargan via AJAX -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

<<<<<<< HEAD
            <!-- Estado por Sucursal -->
=======
            <!-- Asignación por Sucursal -->
>>>>>>> d87c20f53cc1d45f1dc53f773d4e1987fddd46a7
            <div class="col-lg-4">
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-building"></i>
<<<<<<< HEAD
                            Estado por Sucursal
=======
                            Asignación por Sucursal
>>>>>>> d87c20f53cc1d45f1dc53f773d4e1987fddd46a7
                        </h3>
                    </div>
                    <div class="box-body">
                        <div class="form-group">
<<<<<<< HEAD
                            <label for="selectSucursalEstado">Seleccionar Sucursal:</label>
                            <select class="form-control" id="selectSucursalEstado">
=======
                            <label for="selectSucursalAsignacion">Seleccionar Sucursal:</label>
                            <select class="form-control" id="selectSucursalAsignacion">
>>>>>>> d87c20f53cc1d45f1dc53f773d4e1987fddd46a7
                                <option value="">Seleccionar sucursal...</option>
                            </select>
                        </div>
                        
<<<<<<< HEAD
                        <div id="estadoMediosSucursal">
                            <p class="text-muted">Selecciona una sucursal para ver el estado de los medios</p>
=======
                        <div id="mediosAsignadosSucursal">
                            <p class="text-muted">Selecciona una sucursal para ver sus medios asignados</p>
>>>>>>> d87c20f53cc1d45f1dc53f773d4e1987fddd46a7
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Modal Nuevo Medio de Pago -->
<div class="modal fade" id="modalNuevoMedioPago" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">
                    <i class="fa fa-plus"></i>
                    Nuevo Medio de Pago
                </h4>
            </div>
            <div class="modal-body">
                <form id="formNuevoMedioPago">
                    <div class="form-group">
                        <label for="codigoMedioPago">Código:</label>
                        <input type="text" class="form-control" id="codigoMedioPago" placeholder="Ej: TAR003" required>
                    </div>
                    <div class="form-group">
                        <label for="nombreMedioPago">Nombre:</label>
                        <input type="text" class="form-control" id="nombreMedioPago" placeholder="Ej: Tarjeta Visa" required>
                    </div>
                    <div class="form-group">
                        <label for="descripcionMedioPago">Descripción:</label>
                        <textarea class="form-control" id="descripcionMedioPago" rows="3" placeholder="Descripción del medio de pago"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="tipoMedioPago">Tipo:</label>
                        <select class="form-control" id="tipoMedioPago" required>
                            <option value="">Seleccionar tipo...</option>
                            <option value="efectivo">Efectivo</option>
                            <option value="tarjeta">Tarjeta</option>
                            <option value="transferencia">Transferencia</option>
                            <option value="cheque">Cheque</option>
                            <option value="otro">Otro</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnGuardarMedioPago">Guardar</button>
            </div>
        </div>
    </div>
</div>

<<<<<<< HEAD
<!-- Modal Activar en Sucursales -->
<div class="modal fade" id="modalActivarSucursales" tabindex="-1" role="dialog">
=======
<!-- Modal Asignar a Sucursales -->
<div class="modal fade" id="modalAsignarSucursales" tabindex="-1" role="dialog">
>>>>>>> d87c20f53cc1d45f1dc53f773d4e1987fddd46a7
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">
<<<<<<< HEAD
                    <i class="fa fa-check"></i>
                    Activar Medios de Pago en Sucursales
=======
                    <i class="fa fa-building"></i>
                    Asignar Medios de Pago a Sucursales
>>>>>>> d87c20f53cc1d45f1dc53f773d4e1987fddd46a7
                </h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5>Medios Seleccionados:</h5>
<<<<<<< HEAD
                        <div id="mediosSeleccionadosActivar">
=======
                        <div id="mediosSeleccionadosAsignacion">
>>>>>>> d87c20f53cc1d45f1dc53f773d4e1987fddd46a7
                            <p class="text-muted">Selecciona medios de pago de la tabla</p>
                        </div>
                    </div>
                    <div class="col-md-6">
<<<<<<< HEAD
                        <h5>Sucursales Destino:</h5>
                        <div id="sucursalesDestinoActivar">
=======
                        <h5>Sucursales Disponibles:</h5>
                        <div id="sucursalesDisponiblesAsignacion">
>>>>>>> d87c20f53cc1d45f1dc53f773d4e1987fddd46a7
                            <!-- Se carga via AJAX -->
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
<<<<<<< HEAD
                <button type="button" class="btn btn-success" id="btnConfirmarActivacion">Activar</button>
=======
                <button type="button" class="btn btn-success" id="btnConfirmarAsignacion">Asignar</button>
>>>>>>> d87c20f53cc1d45f1dc53f773d4e1987fddd46a7
            </div>
        </div>
    </div>
</div>

<<<<<<< HEAD
<!-- Modal Desactivar en Sucursales -->
<div class="modal fade" id="modalDesactivarSucursales" tabindex="-1" role="dialog">
=======
<!-- Modal Copia Masiva -->
<div class="modal fade" id="modalCopiaMasiva" tabindex="-1" role="dialog">
>>>>>>> d87c20f53cc1d45f1dc53f773d4e1987fddd46a7
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">
<<<<<<< HEAD
                    <i class="fa fa-times"></i>
                    Desactivar Medios de Pago en Sucursales
=======
                    <i class="fa fa-copy"></i>
                    Copia Masiva de Medios de Pago
>>>>>>> d87c20f53cc1d45f1dc53f773d4e1987fddd46a7
                </h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5>Medios Seleccionados:</h5>
<<<<<<< HEAD
                        <div id="mediosSeleccionadosDesactivar">
=======
                        <div id="mediosSeleccionadosCopia">
>>>>>>> d87c20f53cc1d45f1dc53f773d4e1987fddd46a7
                            <p class="text-muted">Selecciona medios de pago de la tabla</p>
                        </div>
                    </div>
                    <div class="col-md-6">
<<<<<<< HEAD
                        <h5>Sucursales:</h5>
                        <div id="sucursalesDestinoDesactivar">
=======
                        <h5>Sucursales Destino:</h5>
                        <div id="sucursalesDestinoCopia">
>>>>>>> d87c20f53cc1d45f1dc53f773d4e1987fddd46a7
                            <!-- Se carga via AJAX -->
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
<<<<<<< HEAD
                <button type="button" class="btn btn-warning" id="btnConfirmarDesactivacion">Desactivar</button>
=======
                <button type="button" class="btn btn-warning" id="btnConfirmarCopiaMasiva">Copiar</button>
>>>>>>> d87c20f53cc1d45f1dc53f773d4e1987fddd46a7
            </div>
        </div>
    </div>
</div>

<script src="vistas/js/medios-pago-central.js"></script>
