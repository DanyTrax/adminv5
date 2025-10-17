<?php
if ($_SESSION["perfil"] == "Especial") {
  echo '<script>
        window.location = "inicio";
    </script>';
  return;
}
?>

<?php

$id = $_GET["id"];

$contabilidad = ControladorContabilidad::findById($id);
?>

<div class="content-wrapper">
  <section class="content-header">
    <h1>Editar entrada</h1>
    <ol class="breadcrumb">
      <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
      <li class="active">Editar entrada</li>
    </ol>
  </section>

  <section class="content">
    <div class="row">
      <div class="col-lg-12 col-xs-12">
        <div class="box box-success">
          <div class="box-header with-border"></div>
          <form role="form" method="post" class="formularioVenta">
            <input type="hidden" name="id" value="<?= $id ?>">
            <input type="hidden" name="editarEntrada">
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
                  <input type="date" name="fecha" id="fecha" value="<?= date('Y-m-d') ?>" <?= $_SESSION["perfil"] == "Administrador" ? '' : 'readonly' ?> required>
                </div>
                <div class="form-group">
                  <label for="descripcion">Descripci¨®n</label>
                  <textarea name="descripcion" id="descripcion" cols="30" rows="10" required class="form-control">
                      <?= $contabilidad['detalle'] ?>
                  </textarea>
                </div>
                <div class="form-group">
                  <label for="valor">Valor</label>
                  <input type="text" name="valor" id="valor" class="form-control" value="<?= $contabilidad['valor'] ?>" required>
                </div>
                <div class="form-group">
                  <select id="nuevoMedioPago" name="nuevoMedioPago">
                    <option value="">Seleccione medio de pago</option>
                    <?php foreach (MedioPago::ALL as $medio) : ?>
                      <option value="<?= $medio ?>" <?= $contabilidad['medio_pago'] === $medio ? 'selected' : ''  ?>><?= $medio ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
            </div>
            <div class="box-footer">
              <button type="submit" class="btn btn-primary pull-right">Guardar cambios</button>
            </div>
          </form>
          <?php
          ControladorContabilidad::editarEntrada();
          ?>
        </div>
      </div>
    </div>
  </section>
</div>