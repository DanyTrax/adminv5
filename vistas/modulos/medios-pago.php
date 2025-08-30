<div class="content-wrapper">
    <section class="content-header">
        <h1>Administrar Medios de Pago</h1>
        <ol class="breadcrumb">
            <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li class="active">Administrar Medios de Pago</li>
        </ol>
    </section>

    <section class="content">
        <div class="box">
            <div class="box-header with-border">
                <button class="btn btn-primary" data-toggle="modal" data-target="#modalAgregarMedioPago">
                    Agregar Medio de Pago
                </button>
            </div>
            <div class="box-body">
                <table class="table table-bordered table-striped dt-responsive tablas">
                    <thead>
                        <tr>
                            <th style="width:10px">#</th>
                            <th>Nombre</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            $mediosPago = ControladorMediosPago::ctrMostrarMediosPago();
                            foreach ($mediosPago as $key => $value) {
                                echo '<tr>
                                        <td>'.($key+1).'</td>
                                        <td>'.$value["nombre"].'</td>
                                        <td>
                                            <div class="btn-group">
                                                <button class="btn btn-warning btnEditarMedioPago" idMedioPago="'.$value["id"].'" data-toggle="modal" data-target="#modalEditarMedioPago"><i class="fa fa-pencil"></i></button>
                                                <button class="btn btn-danger btnEliminarMedioPago" idMedioPago="'.$value["id"].'"><i class="fa fa-times"></i></button>
                                            </div>
                                        </td>
                                      </tr>';
                            }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>

<div id="modalAgregarMedioPago" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form role="form" method="post">
                <div class="modal-header" style="background:#3c8dbc; color:white">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Agregar Medio de Pago</h4>
                </div>
                <div class="modal-body">
                    <div class="box-body">
                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-credit-card"></i></span>
                                <input type="text" class="form-control" name="nuevoMedioPago" placeholder="Ingresar nuevo medio de pago" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
                <?php
                    $crearMedioPago = new ControladorMediosPago();
                    $crearMedioPago -> ctrCrearMedioPago();
                ?>
            </form>
        </div>
    </div>
</div>

<div id="modalEditarMedioPago" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form role="form" method="post">
                <div class="modal-header" style="background:#3c8dbc; color:white">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Editar Medio de Pago</h4>
                </div>
                <div class="modal-body">
                    <div class="box-body">
                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-credit-card"></i></span>
                                <input type="text" class="form-control" id="editarMedioPago" name="editarMedioPago" required>
                                <input type="hidden" id="idMedioPago" name="idMedioPago">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </div>
                <?php
                    $editarMedioPago = new ControladorMediosPago();
                    $editarMedioPago -> ctrEditarMedioPago();
                ?>
            </form>
        </div>
    </div>
</div>
<?php
    $borrarMedioPago = new ControladorMediosPago();
    $borrarMedioPago -> ctrBorrarMedioPago();
?>