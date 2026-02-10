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
    <div class="box box-primary">
      <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-filter"></i> Filtros y datos</h3>
      </div>
      <div class="box-body">
        <div class="row">
          <div class="col-md-4">
            <div class="form-group">
              <label for="filtroCategoriaStockSuc"><i class="fa fa-th"></i> Categoría (opcional)</label>
              <select class="form-control" id="filtroCategoriaStockSuc" name="filtroCategoriaStockSuc">
                <option value="">Todas las categorías</option>
                <?php foreach ($categorias as $cat): ?>
                  <option value="<?php echo (int)$cat['id']; ?>"><?php echo htmlspecialchars($cat['categoria']); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <div class="col-md-4" style="padding-top: 25px;">
            <button type="button" class="btn btn-primary" id="btnCargarStockSucursales">
              <i class="fa fa-refresh"></i> Cargar stock por sucursales
            </button>
            <span id="estadoCargaStockSuc" class="text-muted small" style="margin-left: 10px;"></span>
          </div>
        </div>
      </div>
    </div>

    <div class="box box-success">
      <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-table"></i> Tabla de productos y stock por sucursal</h3>
        <div class="box-tools pull-right">
          <span class="label label-info" id="resumenProductosStockSuc">0 productos</span>
        </div>
      </div>
      <div class="box-body">
        <div class="table-responsive">
          <table class="table table-bordered table-striped table-hover" id="tablaStockPorSucursales" width="100%">
            <thead>
              <tr id="theadStockSucursales">
                <th>Código</th>
                <th>Descripción</th>
                <!-- Columnas de sucursales se insertan por JS -->
                <th class="text-center bg-primary">Total</th>
              </tr>
            </thead>
            <tbody id="tbodyStockPorSucursales">
              <tr>
                <td colspan="10" class="text-center text-muted">
                  <i class="fa fa-info-circle"></i> Use el botón "Cargar stock por sucursales" para ver los datos.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </section>
</div>
