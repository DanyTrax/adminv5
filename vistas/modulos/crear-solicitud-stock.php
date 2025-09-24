<div class="content-wrapper">

  <section class="content-header">
    
    <h1>
      Crear Solicitud de Stock
      <small>Solicitar productos para la sucursal</small>
    </h1>

    <ol class="breadcrumb">
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      <li><a href="solicitudes-stock">Solicitudes de Stock</a></li>
      <li class="active">Crear Solicitud</li>
    </ol>

  </section>

  <section class="content">

    <div class="row">

      <!-- FORMULARIO DE SOLICITUD -->
      <div class="col-md-5">

        <div class="box box-success">
          
          <div class="box-header with-border">
            <h3 class="box-title">
              <i class="fa fa-plus-circle"></i> Nueva Solicitud
            </h3>
          </div>

          <form role="form" method="post" class="formularioCrearSolicitud" novalidate>

            <div class="box-body">

              <!-- TIPO DE SOLICITUD -->
              <div class="form-group">
                <label>
                  <i class="fa fa-tags"></i> Tipo de Solicitud: <span class="text-danger">*</span>
                </label>
                <div class="radio">
                  <label>
                    <input type="radio" name="tipo_solicitud" value="stock" required>
                    <i class="fa fa-cubes"></i> Por Stock (Productos necesarios)
                  </label>
                </div>
                <div class="radio">
                  <label>
                    <input type="radio" name="tipo_solicitud" value="remision" required>
                    <i class="fa fa-file-text-o"></i> Por Remisión (Basado en una venta)
                  </label>
                </div>
              </div>

              <!-- CAMPOS DE REMISIÓN -->
              <div class="campoRemision" style="display: none;">
                
                <div class="form-group">
                  <label>
                    <i class="fa fa-search"></i> Buscar Remisión/Venta:
                  </label>
                  <div class="input-group">
                    <input type="text" 
                           class="form-control" 
                           id="buscarRemision" 
                           placeholder="Ingrese código de venta o nombre de cliente">
                    <span class="input-group-btn">
                      <button type="button" class="btn btn-info" id="btnBuscarRemision">
                        <i class="fa fa-search"></i>
                      </button>
                    </span>
                  </div>
                  
                  <!-- RESULTADOS DE BÚSQUEDA -->
                  <div id="resultadosRemision" class="list-group" style="display: none; max-height: 200px; overflow-y: auto; margin-top: 10px;"></div>
                </div>

                <!-- CAMPOS OCULTOS PARA REMISIÓN -->
                <input type="hidden" id="codigoRemisionSeleccionada" name="codigo_remision">
                <input type="hidden" id="nombreClienteRemision" name="nombre_cliente_remision">

              </div>

              <!-- DETALLE ADICIONAL -->
              <div class="form-group">
                <label>
                  <i class="fa fa-comment"></i> Detalle Adicional:
                </label>
                <textarea class="form-control" 
                          name="detalle_adicional" 
                          id="detalleAdicional" 
                          rows="3" 
                          maxlength="500"
                          placeholder="Describa el motivo de la solicitud, urgencia, detalles especiales, etc."></textarea>
                <small class="help-block">
                  <i class="fa fa-info-circle"></i> Máximo 500 caracteres
                </small>
              </div>

            </div>

            <div class="box-footer">
              
              <div class="row">
                <div class="col-xs-6">
                  <a href="solicitudes-stock" class="btn btn-default btn-block">
                    <i class="fa fa-arrow-left"></i> Cancelar
                  </a>
                </div>
                <div class="col-xs-6">
                  <button type="submit" 
                          class="btn btn-success btn-block" 
                          id="btnCrearSolicitudFinal"
                          disabled>
                    <i class="fa fa-save"></i> Crear Solicitud
                  </button>
                </div>
              </div>

              <!-- CAMPO OCULTO PARA PRODUCTOS -->
              <input type="hidden" name="productos_solicitados" id="productosJsonInput">

            </div>

          </form>

          <?php

          $crearSolicitud = new ControladorSolicitudesStock();
          $crearSolicitud->ctrCrearSolicitud();

          ?>

        </div>

      </div>

      <!-- LISTA DE PRODUCTOS SELECCIONADOS -->
      <div class="col-md-7">

        <div class="box box-info">
          
          <div class="box-header with-border">
            <h3 class="box-title">
              <i class="fa fa-list"></i> Productos Seleccionados
              <span class="badge bg-blue" id="contadorProductos">0</span>
            </h3>
          </div>

          <div class="box-body">

            <div class="table-responsive">
              <table class="table table-condensed">
                <thead>
                  <tr class="bg-light">
                    <th>Producto</th>
                    <th class="text-center" width="100px">Cantidad</th>
                    <th class="text-center" width="80px">Acción</th>
                  </tr>
                </thead>
                <tbody id="productosSeleccionados">
                  <tr id="sinProductos">
                    <td colspan="3" class="text-center text-muted">
                      <i class="fa fa-info-circle"></i> No hay productos seleccionados
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

          </div>

        </div>

        <!-- BOTÓN PARA AGREGAR PRODUCTOS -->
        <div class="box box-primary">
          
          <div class="box-header with-border">
            <h3 class="box-title">
              <i class="fa fa-plus"></i> Agregar Productos
            </h3>
          </div>

          <div class="box-body">
            <button type="button" 
                    class="btn btn-primary btn-block" 
                    data-toggle="modal" 
                    data-target="#modalCatalogoProductos">
              <i class="fa fa-plus-circle"></i> Buscar y Agregar Productos
            </button>
          </div>

        </div>

      </div>

    </div>

  </section>

</div>

<!-- MODAL PARA CATÁLOGO DE PRODUCTOS -->
<div class="modal fade" id="modalCatalogoProductos" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
        <h4 class="modal-title">
          <i class="fa fa-search"></i> Catálogo de Productos
        </h4>
      </div>
      <div class="modal-body">
        
        <div class="table-responsive">
          <table class="table table-bordered table-condensed tablaProductosCatalogo">
            <thead>
              <tr class="bg-primary text-white">
                <th>Imagen</th>
                <th>Código</th>
                <th>Descripción</th>
                <th>Stock</th>
                <th>Acción</th>
              </tr>
            </thead>
          </table>
        </div>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">
          <i class="fa fa-times"></i> Cerrar
        </button>
      </div>
    </div>
  </div>
</div>

<!-- MODAL PARA CANTIDAD DE PRODUCTO -->
<div class="modal fade" id="modalCantidadProducto" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-sm" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
        <h4 class="modal-title">
          <i class="fa fa-plus-circle text-success"></i> Agregar Producto
        </h4>
      </div>
      <div class="modal-body">
        
        <div class="form-group">
          <label><strong>Producto:</strong></label>
          <p id="nombreProductoModal" class="text-primary"></p>
        </div>
        
        <div class="form-group">
          <label for="cantidadProductoModal">
            <i class="fa fa-calculator"></i> Cantidad a solicitar: <span class="text-danger">*</span>
          </label>
          <input type="number" 
                 class="form-control text-center" 
                 id="cantidadProductoModal" 
                 min="1" 
                 max="9999" 
                 value="1" 
                 placeholder="Ingrese cantidad">
          <small class="help-block text-muted">
            <i class="fa fa-info-circle"></i> Cantidad debe ser entre 1 y 9,999
          </small>
        </div>
        
        <div class="form-group">
          <label for="observacionProductoModal">
            <i class="fa fa-comment"></i> Observación (opcional):
          </label>
          <textarea class="form-control" 
                    id="observacionProductoModal" 
                    rows="2" 
                    maxlength="200"
                    placeholder="Ej: Urgente, color específico, medidas especiales..."></textarea>
          <small class="help-block text-muted">
            <i class="fa fa-info-circle"></i> Máximo 200 caracteres
          </small>
        </div>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">
          <i class="fa fa-times"></i> Cancelar
        </button>
        <button type="button" class="btn btn-success" id="confirmarAgregarProducto">
          <i class="fa fa-check"></i> Agregar a Solicitud
        </button>
      </div>
    </div>
  </div>
</div>