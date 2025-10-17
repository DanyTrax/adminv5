<div class="content-wrapper">
  
  <section class="content-header">
    
    <h1>
      Catálogo Maestro de Productos
      <small>Matriz Central de Productos para Todas las Sucursales</small>
    </h1>

    <ol class="breadcrumb">
      
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      
      <li class="active">Catálogo Maestro</li>
    
    </ol>

  </section>

  <section class="content">

    <div class="box">

      <div class="box-header with-border">
  
        <button class="btn btn-primary" data-toggle="modal" data-target="#modalAgregarProductoMaestro">
          <i class="fa fa-plus"></i> Agregar Producto Maestro
        </button>

        <button class="btn btn-success" data-toggle="modal" data-target="#modalSincronizarCatalogo">
          <i class="fa fa-refresh"></i> Sincronizar a Productos
        </button>

        <button class="btn btn-info" data-toggle="modal" data-target="#modalImportarExcel">
          <i class="fa fa-upload"></i> Importar Excel
        </button>

        <a href="descargar-plantilla-excel.php?descargar=csv" class="btn btn-success">
            <i class="fa fa-download"></i> Descargar Plantilla CSV
        </a>

      </div>

      <div class="box-body">
        
       <table class="table table-bordered table-striped dt-responsive tablas" width="100%">
         
        <thead>
         
         <tr>
           
           <th style="width:10px">#</th>
           <th>Imagen</th>
           <th>Código</th>
           <th>Descripción</th>
           <th>Categoría</th>
           <th>Precio Venta</th>
           <th>Divisible</th>
           <th>Divididos Configurados</th>
           <th>Última Actualización</th>
           <th>Acciones</th>

         </tr> 

        </thead>

        <tbody>

        <?php

          $item = null;
          $valor = null;

          $productos = ControladorCatalogoMaestro::ctrMostrarCatalogoMaestro($item, $valor);

          foreach ($productos as $key => $value){

            echo '<tr>

                    <td>'.($key+1).'</td>

                    <td>';

                    if($value["imagen"] != ""){

                      echo '<img src="'.$value["imagen"].'" class="img-thumbnail" width="40px">';

                    }else{

                      echo '<img src="vistas/img/productos/default/anonymous.png" class="img-thumbnail" width="40px">';

                    }

                    echo '</td>

                    <td>'.$value["codigo"].'</td>

                    <td>'.$value["descripcion"].'</td>

                    <td>'.$value["nombre_categoria"].'</td>

                    <td>$ '.number_format($value["precio_venta"],0).'</td>

                    <td>';

                    if($value["es_divisible"] == 1){

                      echo '<span class="label label-success">Sí</span>';

                    }else{

                      echo '<span class="label label-default">No</span>';

                    }

                    echo '</td>

                    <td>';

                    $hijosConfigurados = 0;
                    $hijosTexto = '';

                    if(!empty($value["codigo_hijo_mitad"]) && trim($value["codigo_hijo_mitad"]) != ""){
                    $hijosConfigurados++;
                    $hijosTexto .= '<small class="label label-info">1/2</small> ';
                    }

                    if(!empty($value["codigo_hijo_tercio"]) && trim($value["codigo_hijo_tercio"]) != ""){
                    $hijosConfigurados++;
                    $hijosTexto .= '<small class="label label-warning">1/3</small> ';
                    }

                    if(!empty($value["codigo_hijo_cuarto"]) && trim($value["codigo_hijo_cuarto"]) != ""){
                    $hijosConfigurados++;
                    $hijosTexto .= '<small class="label label-success">1/4</small> ';
                    }

                    if($hijosConfigurados > 0){
                      echo $hijosTexto;
                    }else{
                      echo '<span class="label label-default">Ninguno</span>';
                    }

                    echo '</td>

                    <td>'.date('d/m/Y H:i', strtotime($value["fecha_actualizacion"])).'</td>

                    <td>

                      <div class="btn-group">

                        <button class="btn btn-warning btn-xs btnEditarProductoMaestro" 
                                idProductoMaestro="'.$value["id"].'" 
                                data-toggle="modal" 
                                data-target="#modalEditarProductoMaestro">
                          <i class="fa fa-pencil"></i>
                        </button>';

                        echo '<button class="btn btn-danger btn-xs btnEliminarProductoMaestro" 
                                idProductoMaestro="'.$value["id"].'" 
                                codigoProducto="'.$value["codigo"].'" 
                                imagenProducto="'.$value["imagen"].'">
                          <i class="fa fa-times"></i>
                        </button>

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

<!--=====================================
MODAL AGREGAR PRODUCTO MAESTRO
======================================-->

<div class="modal fade" id="modalAgregarProductoMaestro">
    <div class="modal-dialog">
        <div class="modal-content">
            <form role="form" method="post" enctype="multipart/form-data">
        <!--=====================================
        CABEZA DEL MODAL
        ======================================-->

        <div class="modal-header" style="background:#3c8dbc; color:white">

          <button type="button" class="close" data-dismiss="modal">&times;</button>

          <h4 class="modal-title">Agregar Producto al Catálogo Maestro</h4>

        </div>

        <!--=====================================
        CUERPO DEL MODAL
        ======================================-->

        <div class="modal-body">

          <div class="box-body">

            <!-- SELECCIONAR CATEGORÍA -->
            
            <div class="form-group">
              
              <div class="input-group">
              
                <span class="input-group-addon"><i class="fa fa-th"></i></span> 

                <select class="form-control input-lg" name="nuevaCategoriaMaestro" required>
                  
                  <option value="">Seleccionar categoría</option>

                  <?php

                  $categorias = ControladorCatalogoMaestro::ctrMostrarCategoriasCentrales();

                  foreach ($categorias as $key => $value) {
                    
                    echo '<option value="'.$value["id"].'">'.$value["categoria"].'</option>';
                  }

                  ?>
                  
                </select>

              </div>

            </div>

            <!-- INGRESAR CÓDIGO -->

            <div class="form-group">
              
              <div class="input-group">
              
                <span class="input-group-addon"><i class="fa fa-code"></i></span> 

                <input type="text" class="form-control input-lg" name="nuevoCodigoMaestro" id="nuevoCodigoMaestro" placeholder="Código del producto" readonly required>

              </div>

            </div>

            <!-- INGRESAR DESCRIPCIÓN -->

             <div class="form-group">
              
              <div class="input-group">
              
                <span class="input-group-addon"><i class="fa fa-product-hunt"></i></span> 

                <input type="text" class="form-control input-lg" name="nuevaDescripcionMaestro" placeholder="Ingresar descripción" required>

              </div>

            </div>

             <!-- INGRESAR PRECIO VENTA -->

             <div class="form-group">
              
              <div class="input-group">
              
                <span class="input-group-addon"><i class="fa fa-arrow-up"></i></span> 

                <input type="number" class="form-control input-lg" name="nuevoPrecioVentaMaestro" min="0" step="any" placeholder="Precio de venta" required>

              </div>

            </div>

            <!-- SUBIR IMAGEN -->

             <div class="form-group">
              
              <div class="panel">SUBIR IMAGEN</div>

              <input type="file" class="nuevaImagenMaestro" name="nuevaImagenMaestro">

              <p class="help-block">Peso máximo de la imagen 2MB</p>

              <img src="vistas/img/productos/default/anonymous.png" class="img-thumbnail previsualizarMaestro" width="100px">

            </div>

             <!-- CHECKBOX PARA HABILITAR DIVISIÓN - AGREGAR -->
            <div class="form-group">
                <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-cogs"></i></span>
                    <div class="form-control" style="height: auto; padding: 10px 15px; display: flex; align-items: center;">
                        <div class="icheck-primary d-inline" style="margin: 0;">
                            <input type="checkbox" id="esDivisibleMaestro" name="esDivisibleMaestro">
                            <label for="esDivisibleMaestro" style="margin-left: 5px; font-weight: bold;">
                                Habilitar División del Producto
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- CONFIGURACIÓN DE DIVISIÓN - AGREGAR -->
            <div id="divisionConfigMaestro" style="display: none; margin-top: 15px; padding: 15px; background-color: #f9f9f9; border: 1px solid #e3e3e3; border-radius: 4px;">
                
                <!-- TÍTULO DE LA SECCIÓN -->
                <div class="form-group" style="margin-bottom: 20px;">
                    <div class="input-group">
                        <span class="input-group-addon" style="background-color: #3c8dbc; color: white;"><i class="fa fa-wrench"></i></span>
                        <div class="form-control" style="background-color: #3c8dbc; color: white; font-weight: bold; text-align: center; cursor: default;">
                            <i class="fa fa-cogs"></i> Configurar División del Producto
                        </div>
                    </div>
                </div>
                
                <!-- DIVISIÓN MITAD -->
                <div class="form-group">
                    <div class="input-group">
                        <span class="input-group-addon" style="background-color: #f39c12; color: white;"><i class="fa fa-pie-chart"></i></span>
                        <input type="text" name="buscarHijoMitad" class="form-control" placeholder="Buscar producto para mitad (1/2)..." style="font-weight: 500;">
                        <input type="hidden" id="codigoHijoMitad" name="codigoHijoMitad">
                    </div>
                    <div id="resultadosMitad" class="list-group" style="display: none; position: absolute; z-index: 1000; width: 100%; max-height: 200px; overflow-y: auto; box-shadow: 0 4px 8px rgba(0,0,0,0.1);"></div>
                </div>
            
                <!-- DIVISIÓN TERCIO -->
                <div class="form-group">
                    <div class="input-group">
                        <span class="input-group-addon" style="background-color: #00a65a; color: white;"><i class="fa fa-pie-chart"></i></span>
                        <input type="text" name="buscarHijoTercio" class="form-control" placeholder="Buscar producto para tercio (1/3)..." style="font-weight: 500;">
                        <input type="hidden" id="codigoHijoTercio" name="codigoHijoTercio">
                    </div>
                    <div id="resultadosTercio" class="list-group" style="display: none; position: absolute; z-index: 1000; width: 100%; max-height: 200px; overflow-y: auto; box-shadow: 0 4px 8px rgba(0,0,0,0.1);"></div>
                </div>
            
                <!-- DIVISIÓN CUARTO -->
                <div class="form-group">
                    <div class="input-group">
                        <span class="input-group-addon" style="background-color: #dd4b39; color: white;"><i class="fa fa-pie-chart"></i></span>
                        <input type="text" name="buscarHijoCuarto" class="form-control" placeholder="Buscar producto para cuarto (1/4)..." style="font-weight: 500;">
                        <input type="hidden" id="codigoHijoCuarto" name="codigoHijoCuarto">
                    </div>
                    <div id="resultadosCuarto" class="list-group" style="display: none; position: absolute; z-index: 1000; width: 100%; max-height: 200px; overflow-y: auto; box-shadow: 0 4px 8px rgba(0,0,0,0.1);"></div>
                </div>
                
            </div>

              <div class="alert alert-info">
                <i class="fa fa-info-circle"></i> <strong>Información:</strong> 
                Seleccione los productos que funcionarán como divisiones de este producto principal. 
                Cuando se divida en productos.php, se descontará 1 unidad del producto padre y se agregará la cantidad correspondiente a los productos hijos seleccionados.
              </div>

            </div>

          </div>

        </div>

        <!--=====================================
        PIE DEL MODAL
        ======================================-->

        <div class="modal-footer">

          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cancelar</button>

          <button type="submit" class="btn btn-primary">Guardar en Catálogo Maestro</button>

        </div>

        <?php

          $crearProductoMaestro = new ControladorCatalogoMaestro();
          $crearProductoMaestro -> ctrCrearProductoMaestro();

        ?>

      </form>

    </div>

  </div>

</div>

<!--=====================================
MODAL EDITAR PRODUCTO MAESTRO
======================================-->

<div class="modal fade" id="modalEditarProductoMaestro">
    <div class="modal-dialog">
        <div class="modal-content">
            <form role="form" method="post" enctype="multipart/form-data">
        <!--=====================================
        CABEZA DEL MODAL
        ======================================-->

        <div class="modal-header" style="background:#3c8dbc; color:white">

          <button type="button" class="close" data-dismiss="modal">&times;</button>

          <h4 class="modal-title">Editar Producto del Catálogo Maestro</h4>

        </div>

        <!--=====================================
        CUERPO DEL MODAL
        ======================================-->

        <div class="modal-body">

          <div class="box-body">

            <!-- SELECCIONAR CATEGORÍA -->
            
            <div class="form-group">
              
              <div class="input-group">
              
                <span class="input-group-addon"><i class="fa fa-th"></i></span> 

                <select class="form-control input-lg" name="editarCategoriaMaestro" required>
                  
                  <option id="editarCategoriaMaestro">Seleccionar categoría</option>

                  <?php

                  $categorias = ControladorCatalogoMaestro::ctrMostrarCategoriasCentrales();

                  foreach ($categorias as $key => $value) {
                    
                    echo '<option value="'.$value["id"].'">'.$value["categoria"].'</option>';
                  }

                  ?>
                  
                </select>

              </div>

            </div>

            <!-- MOSTRAR CÓDIGO -->

            <div class="form-group">
              
              <div class="input-group">
              
                <span class="input-group-addon"><i class="fa fa-code"></i></span> 

                <input type="text" class="form-control input-lg" id="editarCodigoMaestro" name="editarCodigoMaestro" readonly required>

              </div>

            </div>

            <!-- EDITAR DESCRIPCIÓN -->

             <div class="form-group">
              
              <div class="input-group">
              
                <span class="input-group-addon"><i class="fa fa-product-hunt"></i></span> 

                <input type="text" class="form-control input-lg" name="editarDescripcionMaestro" id="editarDescripcionMaestro" required>

              </div>

            </div>

             <!-- EDITAR PRECIO VENTA -->

             <div class="form-group">
              
              <div class="input-group">
              
                <span class="input-group-addon"><i class="fa fa-arrow-up"></i></span> 

                <input type="number" class="form-control input-lg" name="editarPrecioVentaMaestro" id="editarPrecioVentaMaestro" min="0" step="any" required>

              </div>

            </div>

            <!-- SUBIR IMAGEN -->

             <div class="form-group">
              
              <div class="panel">SUBIR IMAGEN</div>

              <input type="file" class="nuevaImagenMaestro" name="editarImagenMaestro">

              <p class="help-block">Peso máximo de la imagen 2MB</p>

              <img src="vistas/img/productos/default/anonymous.png" class="img-thumbnail previsualizarMaestroEditar" width="100px">

              <input type="hidden" name="imagenActualMaestro" id="imagenActualMaestro">

            </div>

            <!-- CHECKBOX PARA HABILITAR DIVISIÓN - MEJORADO -->
            <div class="form-group">
                <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-cogs"></i></span>
                    <div class="form-control" style="height: auto; padding: 10px 15px; display: flex; align-items: center;">
                        <div class="icheck-primary d-inline" style="margin: 0;">
                            <input type="checkbox" id="editarEsDivisibleMaestro" name="editarEsDivisibleMaestro">
                            <label for="editarEsDivisibleMaestro" style="margin-left: 5px; font-weight: bold;">
                                Habilitar División del Producto
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- CONFIGURACIÓN DE DIVISIÓN - MEJORADA -->
            <div id="divisionConfigEditarMaestro" style="display: none; margin-top: 15px; padding: 15px; background-color: #f9f9f9; border: 1px solid #e3e3e3; border-radius: 4px;">
                
                <!-- TÍTULO DE LA SECCIÓN -->
                <div class="form-group" style="margin-bottom: 20px;">
                    <div class="input-group">
                        <span class="input-group-addon" style="background-color: #3c8dbc; color: white;"><i class="fa fa-wrench"></i></span>
                        <div class="form-control" style="background-color: #3c8dbc; color: white; font-weight: bold; text-align: center; cursor: default;">
                            <i class="fa fa-cogs"></i> Configurar División del Producto
                        </div>
                    </div>
                </div>
                
                <!-- DIVISIÓN MITAD -->
                <div class="form-group">
                    <div class="input-group">
                        <span class="input-group-addon" style="background-color: #f39c12; color: white;"><i class="fa fa-pie-chart"></i></span>
                        <input type="text" id="buscarEditarHijoMitad" class="form-control" placeholder="Buscar producto para mitad (1/2)..." style="font-weight: 500;">
                        <input type="hidden" id="editarCodigoHijoMitad" name="editarCodigoHijoMitad" value="">
                    </div>
                    <div id="editarResultadosMitad" class="list-group" style="display: none; position: absolute; z-index: 1000; width: 100%; max-height: 200px; overflow-y: auto; box-shadow: 0 4px 8px rgba(0,0,0,0.1);"></div>
                </div>
            
                <!-- DIVISIÓN TERCIO -->
                <div class="form-group">
                    <div class="input-group">
                        <span class="input-group-addon" style="background-color: #00a65a; color: white;"><i class="fa fa-pie-chart"></i></span>
                        <input type="text" id="buscarEditarHijoTercio" class="form-control" placeholder="Buscar producto para tercio (1/3)..." style="font-weight: 500;">
                        <input type="hidden" id="editarCodigoHijoTercio" name="editarCodigoHijoTercio" value="">
                    </div>
                    <div id="editarResultadosTercio" class="list-group" style="display: none; position: absolute; z-index: 1000; width: 100%; max-height: 200px; overflow-y: auto; box-shadow: 0 4px 8px rgba(0,0,0,0.1);"></div>
                </div>
            
                <!-- DIVISIÓN CUARTO -->
                <div class="form-group">
                    <div class="input-group">
                        <span class="input-group-addon" style="background-color: #dd4b39; color: white;"><i class="fa fa-pie-chart"></i></span>
                        <input type="text" id="buscarEditarHijoCuarto" class="form-control" placeholder="Buscar producto para cuarto (1/4)..." style="font-weight: 500;">
                        <input type="hidden" id="editarCodigoHijoCuarto" name="editarCodigoHijoCuarto" value="">
                    </div>
                    <div id="editarResultadosCuarto" class="list-group" style="display: none; position: absolute; z-index: 1000; width: 100%; max-height: 200px; overflow-y: auto; box-shadow: 0 4px 8px rgba(0,0,0,0.1);"></div>
                </div>
                
            </div>

            <input type="hidden" name="idProductoMaestro" id="idProductoMaestro">

          </div>

        </div>

        <!--=====================================
        PIE DEL MODAL
        ======================================-->

        <div class="modal-footer">

          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cancelar</button>

          <button type="submit" class="btn btn-primary">Guardar Cambios</button>

        </div>

        <?php

          $editarProductoMaestro = new ControladorCatalogoMaestro();
          $editarProductoMaestro -> ctrEditarProductoMaestro();

        ?>

      </form>

    </div>

  </div>

</div>

<!--=====================================
MODAL SINCRONIZAR CATÁLOGO
======================================-->

<div id="modalSincronizarCatalogo" class="modal fade" role="dialog">
  
  <div class="modal-dialog">

    <div class="modal-content">

      <form role="form" method="post">

        <!--=====================================
        CABEZA DEL MODAL
        ======================================-->

        <div class="modal-header" style="background:#28a745; color:white">

          <button type="button" class="close" data-dismiss="modal">&times;</button>

          <h4 class="modal-title">Sincronizar Catálogo a Productos Locales</h4>

        </div>

        <!--=====================================
        CUERPO DEL MODAL
        ======================================-->

        <div class="modal-body">

          <div class="box-body">

            <div class="alert alert-info">
              <h4><i class="fa fa-info-circle"></i> Información Importante</h4>
              Esta acción sincronizará todos los productos del <strong>Catálogo Maestro</strong> hacia la tabla local de <strong>Productos</strong>. 
              <br><br>
              <strong>¿Qué se sincroniza?</strong>
              <ul>
                <li>✅ Nombres y descripciones</li>
                <li>✅ Precios de venta</li>
                <li>✅ Categorías</li>
                <li>✅ Imágenes</li>
              </div>

            </div>

            <div class="form-group text-center">
              <h3>¿Está seguro de sincronizar el catálogo?</h3>
              <p>Los productos nuevos se crearán con stock 0, los existentes actualizarán solo datos maestros.</p>
            </div>

          </div>

        </div>

        <!--=====================================
        PIE DEL MODAL
        ======================================-->

        <div class="modal-footer">

          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cancelar</button>

          <button type="submit" class="btn btn-success" name="sincronizarCatalogo">
            <i class="fa fa-refresh"></i> Sincronizar Ahora
          </button>

        </div>

        <?php

          $sincronizarCatalogo = new ControladorCatalogoMaestro();
          $sincronizarCatalogo -> ctrSincronizarCatalogo();

        ?>

      </form>

    </div>

  </div>

</div>

<!--=====================================
MODAL IMPORTAR EXCEL
======================================-->

<div id="modalImportarExcel" class="modal fade" role="dialog">
  
  <div class="modal-dialog">

    <div class="modal-content">

      <form role="form" method="post" enctype="multipart/form-data">

        <!--=====================================
        CABEZA DEL MODAL
        ======================================-->

        <div class="modal-header" style="background:#17a2b8; color:white">

          <button type="button" class="close" data-dismiss="modal">&times;</button>

          <h4 class="modal-title">Importar Productos desde Excel</h4>

        </div>

        <!--=====================================
        CUERPO DEL MODAL
        ======================================-->

        <div class="modal-body">

          <div class="box-body">

            <div class="alert alert-warning">
              <h4><i class="fa fa-warning"></i> Instrucciones para Importar</h4>
              <p><strong>1.</strong> Descargue la plantilla Excel desde el botón "Descargar Plantilla Excel"</p>
              <p><strong>2.</strong> Complete los datos requeridos en el archivo:</p>
              <ul>
                <li><strong>codigo:</strong> Código del producto (opcional - se genera automático)</li>
                <li><strong>descripcion:</strong> Nombre del producto (OBLIGATORIO)</li>
                <li><strong>id_categoria:</strong> ID de la categoría (OBLIGATORIO - ver referencia en plantilla)</li>
                <li><strong>precio_venta:</strong> Precio de venta (OBLIGATORIO)</li>
              </ul>
              <p><strong>3.</strong> Guarde el archivo y súbalo aquí</p>
            </div>

            <div class="form-group">
              <label>Seleccionar archivo Excel:</label>
              <input type="file" class="form-control" name="archivoExcel" accept=".xlsx,.xls,.csv" required>
              <p class="help-block">Formatos permitidos: .xlsx, .xls, .csv</p>
            </div>

          </div>

        </div>

        <!--=====================================
        PIE DEL MODAL
        ======================================-->

        <div class="modal-footer">

          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cancelar</button>

          <button type="submit" class="btn btn-info" name="importarExcel">
            <i class="fa fa-upload"></i> Importar Archivo
          </button>

        </div>

        <?php

          $importarExcel = new ControladorCatalogoMaestro();
          $importarExcel -> ctrImportarDesdeExcel();

        ?>

      </form>

    </div>

  </div>

</div>

<?php

  $eliminarProductoMaestro = new ControladorCatalogoMaestro();
  $eliminarProductoMaestro -> ctrEliminarProductoMaestro();

?>