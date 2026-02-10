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
        <h3 class="box-title"><i class="fa fa-filter"></i> Filtro por categoría (opcional)</h3>
        <div class="box-tools pull-right">
          <span id="estadoCargaStockSuc" class="text-muted small"></span>
          <button type="button" class="btn btn-default btn-sm" id="btnActualizarStockSucursales" title="Actualizar datos">
            <i class="fa fa-refresh"></i>
          </button>
        </div>
      </div>
      <div class="box-body">
        <div class="form-group" style="margin-bottom: 0;">
          <label for="filtroCategoriaStockSuc"><i class="fa fa-th"></i> Categoría</label>
          <select class="form-control" id="filtroCategoriaStockSuc" name="filtroCategoriaStockSuc" style="max-width: 300px;">
            <option value="">Todas las categorías (catálogo maestro)</option>
            <?php foreach ($categorias as $cat): ?>
              <option value="<?php echo (int)$cat['id']; ?>"><?php echo htmlspecialchars($cat['categoria']); ?></option>
            <?php endforeach; ?>
          </select>
          <small class="text-muted">Al cambiar la categoría se vuelven a cargar los datos.</small>
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
                  <i class="fa fa-spinner fa-spin"></i> Cargando catálogo maestro y stock por sucursales activas...
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </section>
</div>
