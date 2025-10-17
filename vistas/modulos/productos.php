<?php

if($_SESSION["perfil"] == "Vendedor" && !isset($_GET["ruta"])){
  // Esta validación ya no es necesaria si se controla el menú y la plantilla principal
  // Pero la dejamos por si hay acceso directo a la URL
  // echo '<script>window.location = "inicio";</script>';
  // return;
}

?>
<div class="content-wrapper">

  <section class="content-header">
    
    <h1>
      
      Administrar productos
    
    </h1>

    <ol class="breadcrumb">
      
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      
      <li class="active">Administrar productos</li>
    
    </ol>

  </section>

  <section class="content">

    <div class="box">

      <div class="box-header with-border">
  
        <!--=====================================
        BOTÓN AGREGAR PRODUCTO (CON VALIDACIÓN DE PERFIL)
        ======================================-->
        <?php
        // --- INICIO DE LA CORRECCIÓN ---
        // Solo se muestra el botón si el perfil es Administrador o Especial
        if ($_SESSION["perfil"] == "Administrador" || $_SESSION["perfil"] == "Especial") {
          echo '<button class="btn btn-primary" data-toggle="modal" data-target="#modalAgregarProducto">
                  Agregar producto
                </button>';
        }
        // --- FIN DE LA CORRECCIÓN ---
        ?>

      </div>

      <div class="box-body">
        
       <table class="table table-bordered table-striped dt-responsive tablaProductos" width="100%">
        
        <thead>
         
         <tr>
           
           <th style="width:10px">#</th>
           <th>Imagen</th>
           <th>Código</th>
           <th>Descripción</th>
           <th>Categoría</th>
           <th>Stock</th>
           <th>Precio de venta</th>
           <th>Agregado</th>
           <th>Acciones</th>
           
         </tr> 

        </thead>       

       </table>

       <input type="hidden" value="<?php echo $_SESSION['perfil']; ?>" id="perfilOculto">

      </div>

    </div>

  </section>

</div>

<div id="modalAgregarProducto" class="modal fade" role="dialog">
  
  <div class="modal-dialog">

    <div class="modal-content">

      <form role="form" method="post" enctype="multipart/form-data">

        <div class="modal-header" style="background:#3c8dbc; color:white">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Agregar producto</h4>
        </div>

        <div class="modal-body">
          <div class="box-body">

            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-th"></i></span> 
                <select class="form-control input-lg" id="nuevaCategoria" name="nuevaCategoria" required>
                  <option value="">Selecionar categoría</option>
                  <?php
                  $item = null;
                  $valor = null;
                  $categorias = ControladorCategorias::ctrMostrarCategorias($item, $valor);
                  foreach ($categorias as $key => $value) {
                    echo '<option value="'.$value["id"].'">'.$value["categoria"].'</option>';
                  }
                  ?>
                </select>
              </div>
            </div>
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-code"></i></span> 
                <input type="text" class="form-control input-lg" id="nuevoCodigo" name="nuevoCodigo" placeholder="Ingresar código" required>
              </div>
            </div>
             <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-product-hunt"></i></span> 
                <input type="text" class="form-control input-lg" id="nuevaDescripcion" name="nuevaDescripcion" placeholder="Ingresar descripción" required>
              </div>
            </div>
             <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-check"></i></span> 
                <input type="number" class="form-control input-lg" name="nuevoStock" min="0" placeholder="Stock" required>
              </div>
            </div>
             <div class="form-group row">
               <div class="col-xs-12">
                 <div class="input-group">
                   <span class="input-group-addon"><i class="fa fa-arrow-down"></i></span> 
                   <input type="number" class="form-control input-lg" id="nuevoPrecioVenta" name="nuevoPrecioVenta" step="any" min="0" placeholder="Precio de venta" required>
                 </div>
               </div>

             </div>
            
             <div class="form-group">
               <div class="checkbox">
                 <label>
                   <input type="checkbox" name="esDivisible" id="esDivisibleNuevo">
                   ¿Este producto es divisible?
                 </label>
               </div>
             </div>
            
             <div id="camposDivisiblesNuevo" style="display:none;">
               <div class="form-group">
                 <label>Nombre para la Mitad:</label>
                 <input type="text" class="form-control" name="nombreMitad" placeholder="Ej: Descripcion de Media">
               </div>
               <div class="form-group">
                 <label>Precio por Mitad:</label>
                 <input type="number" step="any" class="form-control" name="precioMitad" placeholder="Precio de la mitad">
               </div>
               <div class="form-group">
                 <label>Nombre para el Tercio:</label>
                 <input type="text" class="form-control" name="nombreTercio" placeholder="Ej: Descripcion de Tercio">
               </div>
               <div class="form-group">
                 <label>Precio por Tercio:</label>
                 <input type="number" step="any" class="form-control" name="precioTercio" placeholder="Precio del tercio">
               </div>
               <div class="form-group">
                 <label>Nombre para el Cuarto:</label>
                 <input type="text" class="form-control" name="nombreCuarto" placeholder="Ej: Descripcion de Cuarto">
               </div>
               <div class="form-group">
                 <label>Precio por Cuarto:</label>
                 <input type="number" step="any" class="form-control" name="precioCuarto" placeholder="Precio del cuarto">
               </div>
             </div>

             <div class="form-group">
               <div class="panel">SUBIR IMAGEN</div>
               <input type="file" class="nuevaImagen" name="nuevaImagen">
               <p class="help-block">Peso máximo de la imagen 2MB</p>
               <img src="vistas/img/productos/default/anonymous.png" class="img-thumbnail previsualizar" width="100px">
             </div>

          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>
          <button type="submit" class="btn btn-primary">Guardar producto</button>
        </div>
      </form>
        <?php
          $crearProducto = new ControladorProductos();
          $crearProducto -> ctrCrearProducto();
        ?> 
    </div>
  </div>
</div>

<div id="modalEditarProducto" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <form role="form" method="post" enctype="multipart/form-data">

        <div class="modal-header" style="background:#3c8dbc; color:white">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Editar producto</h4>
        </div>

        <div class="modal-body">
          <div class="box-body">
          
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-th"></i></span> 
                <select class="form-control input-lg" name="editarCategoria" readonly required>
                  <option id="editarCategoria"></option>
                </select>
              </div>
            </div>
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-code"></i></span> 
                <input type="text" class="form-control input-lg" id="editarCodigo" name="editarCodigo" readonly required>
              </div>
            </div>
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-product-hunt"></i></span> 
                <input type="text" class="form-control input-lg" id="editarDescripcion" name="editarDescripcion" required>
              </div>
            </div>
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-check"></i></span> 
                <input type="number" class="form-control input-lg" id="editarStock" name="editarStock" min="0" required>
              </div>
            </div>
            <div class="form-group row">
                <div class="col-xs-12">
                    <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-arrow-down"></i></span> 
                        <input type="number" class="form-control input-lg" id="editarPrecioVenta" name="editarPrecioVenta" step="any" min="0" required>
                    </div>
                </div>
            </div>
          
            <div class="form-group">
              <div class="checkbox">
                <label>
                  <input type="checkbox" name="esDivisible" id="esDivisibleEditar">
                  ¿Este producto es divisible?
                </label>
              </div>
            </div>
          
            <div id="camposDivisiblesEditar" style="display:none;">
              <div class="form-group">
                <label>Nombre para la Mitad:</label>
                <input type="text" class="form-control" name="nombreMitad" id="nombreMitadEditar" placeholder="Ej: Descripcion de Media">
              </div>
              <div class="form-group">
                <label>Precio por Mitad:</label>
                <input type="number" step="any" class="form-control" name="precioMitad" id="precioMitadEditar" placeholder="Precio de la mitad">
              </div>
              <div class="form-group">
                <label>Nombre para el Tercio:</label>
                <input type="text" class="form-control" name="nombreTercio" id="nombreTercioEditar" placeholder="Ej: Descripcion de Tercio">
              </div>
              <div class="form-group">
                <label>Precio por Tercio:</label>
                <input type="number" step="any" class="form-control" name="precioTercio" id="precioTercioEditar" placeholder="Precio del tercio">
              </div>
              <div class="form-group">
                <label>Nombre para el Cuarto:</label>
                <input type="text" class="form-control" name="nombreCuarto" id="nombreCuartoEditar" placeholder="Ej: Descripcion de Cuarto">
              </div>
              <div class="form-group">
                <label>Precio por Cuarto:</label>
                <input type="number" step="any" class="form-control" name="precioCuarto" id="precioCuartoEditar" placeholder="Precio del cuarto">
              </div>
            </div>

            <div class="form-group">
              <div class="panel">SUBIR IMAGEN</div>
              <input type="file" class="nuevaImagen" name="editarImagen">
              <p class="help-block">Peso máximo de la imagen 2MB</p>
              <img src="vistas/img/productos/default/anonymous.png" class="img-thumbnail previsualizar" width="100px">
              <input type="hidden" name="imagenActual" id="imagenActual">
              <input type="hidden" name="idProducto" id="idProducto">
            </div>

          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>
          <button type="submit" class="btn btn-primary">Guardar cambios</button>
        </div>
      </form>
        <?php
          $editarProducto = new ControladorProductos();
          $editarProducto -> ctrEditarProducto();
        ?>      
    </div>
  </div>
</div>

<?php
  $eliminarProducto = new ControladorProductos();
  $eliminarProducto -> ctrEliminarProducto();
?>

<script>
$(document).ready(function() {
  
    // --- Lógica para el modal de AGREGAR producto ---
    $('#esDivisibleNuevo').on('change', function() {
        if ($(this).is(':checked')) {
            $('#camposDivisiblesNuevo').slideDown('fast');
        } else {
            $('#camposDivisiblesNuevo').slideUp('fast').find('input').val('');
        }
    });

    // --- Lógica para el modal de EDITAR producto ---
    $('#esDivisibleEditar').on('change', function() {
        if ($(this).is(':checked')) {
            $('#camposDivisiblesEditar').slideDown('fast');
        } else {
            $('#camposDivisiblesEditar').slideUp('fast').find('input').val('');
        }
    });

    // --- Lógica de limpieza al cerrar el modal de AGREGAR ---
    $('#modalAgregarProducto').on('hidden.bs.modal', function(){
        $(this).find('#esDivisibleNuevo').prop('checked', false);
        $(this).find('#camposDivisiblesNuevo').hide().find('input').val('');
    });

});
</script>