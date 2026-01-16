<?php

// CAMBIO: Obtener fechas de filtro si existen
$fechaInicial = isset($_GET["fechaInicial"]) ? $_GET["fechaInicial"] : null;
$fechaFinal = isset($_GET["fechaFinal"]) ? $_GET["fechaFinal"] : null;

// Si hay filtros de fecha, usar el método de filtrado
if ($fechaInicial && $fechaFinal) {
    $salidasInventario = ControladorSalidasInventario::ctrFilterBy($fechaInicial, $fechaFinal);
} else {
    $salidasInventario = ControladorSalidasInventario::ctrMostrarSalidasInventario(null, null);
}

?>

<div class="content-wrapper">

  <section class="content-header">
    
    <h1>
      
      Salidas de Inventario
    
    </h1>

    <ol class="breadcrumb">
      
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      
      <li class="active">Salidas de Inventario</li>
    
    </ol>

  </section>

  <section class="content">

    <!--=====================================
    PANEL PRINCIPAL
    ======================================-->
    
    <div class="row">
      
      <div class="col-md-12">
        
        <div class="box">
          
          <div class="box-header with-border">
            
            <button class="btn btn-primary pull-right" data-toggle="modal" data-target="#modalAgregarSalida">
              
              <i class="fa fa-plus"></i>
              Nueva Salida de Inventario
            
            </button>
            
            <!-- CAMBIO: Botón de filtro de fecha -->
            <button type="button" class="btn btn-default pull-right" id="daterange-btn-salidas" style="margin-right: 10px;">
              <span>
                <i class="fa fa-calendar"></i>
                <?php
                if (isset($_GET["fechaInicial"])) {
                    echo $_GET["fechaInicial"] . " - " . $_GET["fechaFinal"];
                } else {
                    echo 'Rango de fecha';
                }
                ?>
              </span>
              <i class="fa fa-caret-down"></i>
            </button>

          </div>

          <div class="box-body">
            
            <table class="table table-bordered table-striped dt-responsive tablas-salidas" width="100%">
              
              <thead>
                
                <tr>
                  
                  <th style="width:10px">#</th>
                  <th>Usuario</th>
                  <th>Producto</th>
                  <th>Cantidad</th>
                  <th>Descripción</th>
                  <th>Remisión</th>
                  <th>Fecha</th>
                  <th>Acciones</th>

                </tr>

              </thead>

              <tbody>

                <?php

                foreach ($salidasInventario as $key => $value) {

                  echo '<tr>
                          <td>'.($key+1).'</td>
                          <td>'.$value["usuario_nombre"].'</td>
                          <td>'.$value["producto_nombre"].'<br><small class="text-muted">'.$value["producto_codigo"].'</small></td>
                          <td>'.$value["cantidad"].'</td>
                          <td>'.$value["descripcion"].'</td>
                          <td>'.$value["numero_remision"].'</td>
                          <td>'.$value["fecha_salida"].'</td>
                          <td>';
                          
                          // Solo Administrador puede eliminar salidas
                          if($_SESSION["perfil"] == "Administrador"){
                              echo '<button class="btn btn-danger btnEliminarSalida" idSalida="'.$value["id"].'"><i class="fa fa-times"></i></button>';
                          } else {
                              echo '<span class="text-muted">Sin permisos</span>';
                          }
                          
                          echo '</td>
                        </tr>';

                }

                ?>

              </tbody>

            </table>

          </div>

        </div>

      </div>

    </div>

  </section>

</div>

<!--=====================================
MODAL AGREGAR SALIDA
======================================-->

<div id="modalAgregarSalida" class="modal fade" role="dialog">
  
  <div class="modal-dialog">

    <div class="modal-content">

      <form role="form" method="post">

        <!--=====================================
        CABEZA DEL MODAL
        ======================================-->
        
        <div class="modal-header" style="background:#3c8dbc; color:white">

          <button type="button" class="close" data-dismiss="modal">&times;</button>

          <h4 class="modal-title">Nueva Salida de Inventario</h4>

        </div>

        <!--=====================================
        CUERPO DEL MODAL
        ======================================-->
        
        <div class="modal-body">

          <div class="box-body">

            <!-- ENTRADA PARA EL USUARIO -->
            
            <div class="form-group">
              
              <div class="input-group">
                
                <span class="input-group-addon"><i class="fa fa-user"></i></span> 

                <input type="text" class="form-control input-lg" value="<?php echo $_SESSION["nombre"]; ?>" readonly>

                <input type="hidden" name="nuevoUsuario" value="<?php echo $_SESSION["id"]; ?>">

              </div>

            </div>

            <!-- ENTRADA PARA BUSCAR PRODUCTO -->
            
            <div class="form-group">
              
              <div class="input-group">
                
                <span class="input-group-addon"><i class="fa fa-search"></i></span>

                <input type="text" class="form-control input-lg" id="buscarProducto" placeholder="Buscar producto por código o nombre..." autocomplete="off">

                <input type="hidden" name="nuevoProducto" id="nuevoProducto">

              </div>

              <!-- RESULTADOS DE BÚSQUEDA -->
              
              <div id="resultadosProductos" style="display: none; max-height: 200px; overflow-y: auto; border: 1px solid #ddd; border-radius: 4px; background: white; position: absolute; z-index: 1000; width: 100%;">
                
                <!-- Los resultados se cargarán aquí -->
                
              </div>

            </div>

            <!-- INFORMACIÓN DEL PRODUCTO SELECCIONADO -->
            
            <div id="productoSeleccionado" style="display: none;" class="alert alert-info">
              
              <strong>Producto seleccionado:</strong>
              <div id="infoProducto"></div>
              
            </div>

            <!-- ENTRADA PARA LA CANTIDAD -->
            
            <div class="form-group">
              
              <div class="input-group">
                
                <span class="input-group-addon"><i class="fa fa-key"></i></span> 

                <input type="number" class="form-control input-lg" name="nuevaCantidad" placeholder="Cantidad (máximo 10)" min="1" max="10" required>

              </div>

            </div>

            <!-- ENTRADA PARA LA DESCRIPCIÓN -->
            
            <div class="form-group">
              
              <div class="input-group">
                
                <span class="input-group-addon"><i class="fa fa-comment"></i></span> 

                <textarea class="form-control input-lg" name="nuevaDescripcion" placeholder="Descripción de la salida" rows="3"></textarea>

              </div>

            </div>

            <!-- ENTRADA PARA LA REMISIÓN -->
            
            <div class="form-group">
              
              <div class="input-group">
                
                <span class="input-group-addon"><i class="fa fa-file-text"></i></span> 

                <input type="text" class="form-control input-lg" name="nuevaRemision" id="buscarRemision" placeholder="Buscar remisión/factura por código..." autocomplete="off">

              </div>

              <!-- RESULTADOS DE BÚSQUEDA DE REMISIONES -->
              
              <div id="resultadosRemisiones" style="display: none; max-height: 200px; overflow-y: auto; border: 1px solid #ddd; border-radius: 4px; background: white; position: absolute; z-index: 1000; width: 100%;">
                
                <!-- Los resultados se cargarán aquí -->
                
              </div>

            </div>

          </div>

        </div>

        <!--=====================================
        PIE DEL MODAL
        ======================================-->
        
        <div class="modal-footer">

          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>

          <button type="submit" class="btn btn-primary">Crear Salida</button>

        </div>

        <?php

          $crearSalida = new ControladorSalidasInventario();
          $crearSalida -> ctrCrearSalidaInventario();

        ?>

      </form>

    </div>

  </div>

</div>

<?php

$eliminarSalida = new ControladorSalidasInventario();
$eliminarSalida -> ctrEliminarSalidaInventario();

?>
