<?php
if ($_SESSION["perfil"] == "Especial") {
    echo '<script>
        window.location = "inicio";
    </script>';
    return;
}
?>

<div class="content-wrapper">
    <section class="content-header">
        <h1>Crear entradas</h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li class="active">Crear entradas</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-lg-12 col-xs-12">
                <div class="box box-success">
                    <div class="box-header with-border"></div>
                    <form role="form" method="post" class="formularioVenta">
                        <input type="hidden" name="nuevoEntrada">
                        <div class="box-body">
                            <div class="box">
                                <div class="form-group">
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="fa fa-user"></i></span>
                                        <input type="text" class="form-control" id="nuevoVendedor" value="<?php echo $_SESSION["nombre"]; ?>" readonly>
                                        <input type="hidden" name="idVendedor" value="<?php echo $_SESSION["id"]; ?>">
                                    </div>
                                </div>
                                <hr>
                                <div class="form-group">
                                    <label for="fecha">Fecha</label>
                                            <?php
            // Se establece la zona horaria para el valor por defecto del input
            date_default_timezone_set('America/Bogota');
        ?>
                                    <input type="date" name="fecha" id="fecha" value="<?= date('Y-m-d') ?>" <?= $_SESSION["perfil"] == "Administrador" ? '' : 'readonly' ?> required>
                                </div>
                                <div class="form-group">
                                    <label for="descripcion">Descipción</label>
                                    <textarea class="form-control" name="descripcion" id="descripcion" cols="30" rows="10" required></textarea>
                                </div>
                                <div class="form-group">
                                    <label for="valor">Valor</label>
                                    <input type="text" name="valor" id="valor" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <select id="nuevoMedioPago" name="nuevoMedioPago" required>
                                        <option value="">Seleccione medio de pago</option>
                                        <?php foreach (MedioPago::ALL as $medio) : ?>
                                            <option value="<?= $medio ?>"><?= $medio ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="box-footer">
                            <button type="submit" class="btn btn-primary pull-right">Guardar entrada</button>
                        </div>
                    </form>
                    <?php
                    ControladorContabilidad::crearEntrada();
                    ?>
                </div>
            </div>
        </div>
    </section>
</div>

<!--=====================================
MODAL AGREGAR CLIENTE
======================================-->
<div id="modalAgregarCliente" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form role="form" method="post">
                <div class="modal-header" style="background:#3c8dbc; color:white">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Agregar cliente</h4>
                </div>
                <div class="modal-body">
                    <div class="box-body">
                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-user"></i></span>
                                <input type="text" class="form-control input-lg" name="nuevoCliente" placeholder="Ingresar nombre" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-key"></i></span>
                                <input type="number" min="0" class="form-control input-lg" name="nuevoDocumentoId" placeholder="Ingresar documento" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-envelope"></i></span>
                                <input type="email" class="form-control input-lg" name="nuevoEmail" placeholder="Ingresar email" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-phone"></i></span>
                                <input type="text" class="form-control input-lg" name="nuevoTelefono" placeholder="Ingresar teléfono" data-inputmask="'mask':'(999) 999-9999'" data-mask required>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-map-marker"></i></span>
                                <input type="text" class="form-control input-lg" name="nuevaDireccion" placeholder="Ingresar dirección" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>
                    <button type="submit" class="btn btn-primary">Guardar cliente</button>
                </div>
            </form>
            <?php
            $crearCliente = new ControladorClientes();
            $crearCliente->ctrCrearCliente();
            ?>
        </div>
    </div>
</div>