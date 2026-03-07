<?php
if ($_SESSION["perfil"] == "Limitado" || $_SESSION["perfil"] == "Transportador") {
    echo '<script>window.location = "inicio";</script>';
    return;
}
require_once __DIR__ . "/../../controladores/categorias.controlador.php";
$item = null;
$valor = null;
$categorias = ControladorCategorias::ctrMostrarCategorias($item, $valor);
?>
<div class="content-wrapper">
  <section class="content-header">
    <h1>
      <i class="fa fa-cubes"></i> Stock por sucursales
      <small>Productos con stock en todas las sucursales activas</small>
    </h1>
    <ol class="breadcrumb">
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      <li><a href="productos"><i class="fa fa-cube"></i> Productos</a></li>
      <li class="active">Stock por sucursales</li>
    </ol>
  </section>

  <section class="content">
    <div class="box box-success">
      <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-warehouse"></i> Stock disponible por sucursal</h3>
        <div class="box-tools pull-right">
          <span class="label label-info" id="resumenProductosStockSuc">0 productos</span>
        </div>
      </div>
      <div class="box-body">
        <div class="form-inline" style="margin-bottom: 15px;">
          <div class="form-group">
            <label for="filtroCategoriaStockSuc" class="control-label" style="margin-right: 8px;"><i class="fa fa-th"></i> Categoría</label>
            <select class="form-control" id="filtroCategoriaStockSuc" name="filtroCategoriaStockSuc" style="max-width: 280px;">
              <option value="">Todas las categorías</option>
              <?php
              $filtroCategoriaActual = isset($_GET["filtroCategoria"]) ? (int)$_GET["filtroCategoria"] : null;
              foreach ($categorias as $cat):
                $selected = ($filtroCategoriaActual !== null && $filtroCategoriaActual == (int)$cat['id']) ? ' selected' : '';
              ?>
                <option value="<?php echo (int)$cat['id']; ?>"<?php echo $selected; ?>><?php echo htmlspecialchars($cat['categoria']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <span id="estadoCargaStockSuc" class="text-muted small" style="margin-left: 10px;"></span>
          <button type="button" class="btn btn-default btn-sm" id="btnActualizarStockSucursales" title="Actualizar datos" style="margin-left: 10px;">
            <i class="fa fa-refresh"></i> Actualizar
          </button>
        </div>
        <div class="table-responsive">
          <table class="table table-bordered table-striped" id="tablaStockPorSucursales" width="100%">
            <thead>
              <tr id="theadStockSucursales">
                <th style="width: 10px;">#</th>
                <th>Código</th>
                <th>Descripción</th>
                <th>Categoría</th>
                <!-- Columnas de sucursales se insertan por JS -->
                <th class="text-center bg-primary" style="min-width: 80px;">Total</th>
              </tr>
            </thead>
            <tbody id="tbodyStockPorSucursales">
              <tr>
                <td colspan="20" class="text-center text-muted">
                  <i class="fa fa-spinner fa-spin"></i> Cargando productos y stock por sucursales activas...
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </section>
</div>
