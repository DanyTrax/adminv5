<?php

if($_SESSION["perfil"] == "Vendedor"){

  echo '<script>

    window.location = "inicio";

  </script>';

  return;

}

?>

<div class="content-wrapper">

  <section class="content-header">
    
    <h1>
      
      Administrar categorías
    
    </h1>

    <ol class="breadcrumb">
      
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      
      <li class="active">Administrar categorías</li>
    
    </ol>

  </section>

  <section class="content">

    <div class="box">

      <div class="box-header with-border">
  
        <button class="btn btn-primary" data-toggle="modal" data-target="#modalAgregarCategoria">
          
          Agregar categoría

        </button>
        
        <!--=====================================
        BOTONES DE SINCRONIZACIÓN Y BORRAR TODO
        ======================================-->
        <div class="btn-group pull-right" style="margin-left: 10px;">
          <button type="button" class="btn btn-danger btnBorrarTodasCategorias" title="Borrar todas las categorías de esta sucursal">
            <i class="fa fa-trash"></i> Borrar Todo
          </button>
          <button type="button" class="btn btn-success btnSincronizarCategorias" title="Sincronizar categorías desde central">
            <i class="fa fa-refresh"></i> Sincronizar con Categorías Centrales
          </button>
        </div>

      </div>

      <div class="box-body">
        
       <table class="table table-bordered table-striped dt-responsive tablas" width="100%">
         
        <thead>
         
         <tr>
           
           <th style="width:10px">#</th>
           <th style="width:80px">ID</th>
           <th>Categoría</th>
           <th>Prefijo</th>
           <th>Fecha Creación</th>
           <th>Acciones</th>

         </tr> 

        </thead>

        <tbody>

        <?php

          $item = null;
          $valor = null;

          $categorias = ControladorCategorias::ctrMostrarCategorias($item, $valor);

          if (empty($categorias)) {
            echo '<tr><td colspan="6" class="text-center">No hay categorías disponibles</td></tr>';
          } else {
            foreach ($categorias as $key => $value) {
              $prefijo = !empty($value["prefijo"]) ? $value["prefijo"] : '<span class="text-muted">Sin prefijo</span>';
              $fechaCreacion = !empty($value["fecha"]) ? date('d/m/Y', strtotime($value["fecha"])) : 'N/A';
              
              echo ' <tr>

                      <td>'.($key+1).'</td>

                      <td><span class="label label-primary" style="font-size:12px;">'.$value["id"].'</span></td>

                      <td><strong class="text-uppercase">'.$value["categoria"].'</strong></td>

                      <td><span class="label label-info">'.$prefijo.'</span></td>

                      <td>'.$fechaCreacion.'</td>

                      <td>

                        <div class="btn-group">
                            
                          <button class="btn btn-warning btn-xs btnEditarCategoria" idCategoria="'.$value["id"].'" data-toggle="modal" data-target="#modalEditarCategoria" title="Editar categoría"><i class="fa fa-pencil"></i></button>';

                          if($_SESSION["perfil"] == "Administrador"){

                            echo '<button class="btn btn-danger btn-xs btnEliminarCategoria" idCategoria="'.$value["id"].'" title="Eliminar categoría"><i class="fa fa-times"></i></button>';

                          }

                        echo '</div>  

                      </td>

                    </tr>';
            }
          }

        ?>

        </tbody>

       </table>

      </div>

    </div>

  </section>

</div>

<!--=====================================
MODAL AGREGAR CATEGORÍA
======================================-->

<div id="modalAgregarCategoria" class="modal fade" role="dialog">
  
  <div class="modal-dialog">

    <div class="modal-content">

      <form role="form" method="post">

        <!--=====================================
        CABEZA DEL MODAL
        ======================================-->

        <div class="modal-header" style="background:#3c8dbc; color:white">

          <button type="button" class="close" data-dismiss="modal">&times;</button>

          <h4 class="modal-title">Agregar categoría</h4>

        </div>

        <!--=====================================
        CUERPO DEL MODAL
        ======================================-->

        <div class="modal-body">

          <div class="box-body">

            <!-- ENTRADA PARA EL NOMBRE -->
            
            <div class="form-group">
              
              <div class="input-group">
              
                <span class="input-group-addon"><i class="fa fa-th"></i></span> 

                <input type="text" class="form-control input-lg" name="nuevaCategoria" placeholder="Ingresar categoría" required>

              </div>

            </div>
  
          </div>

        </div>

        <!--=====================================
        PIE DEL MODAL
        ======================================-->

        <div class="modal-footer">

          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>

          <button type="submit" class="btn btn-primary">Guardar categoría</button>

        </div>

        <?php

          $crearCategoria = new ControladorCategorias();
          $crearCategoria -> ctrCrearCategoria();

        ?>

      </form>

    </div>

  </div>

</div>

<!--=====================================
MODAL EDITAR CATEGORÍA
======================================-->

<div id="modalEditarCategoria" class="modal fade" role="dialog">
  
  <div class="modal-dialog">

    <div class="modal-content">

      <form role="form" method="post">

        <!--=====================================
        CABEZA DEL MODAL
        ======================================-->

        <div class="modal-header" style="background:#3c8dbc; color:white">

          <button type="button" class="close" data-dismiss="modal">&times;</button>

          <h4 class="modal-title">Editar categoría</h4>

        </div>

        <!--=====================================
        CUERPO DEL MODAL
        ======================================-->

        <div class="modal-body">

          <div class="box-body">

            <!-- ENTRADA PARA EL NOMBRE -->
            
            <div class="form-group">
              
              <div class="input-group">
              
                <span class="input-group-addon"><i class="fa fa-th"></i></span> 

                <input type="text" class="form-control input-lg" name="editarCategoria" id="editarCategoria" required>

                 <input type="hidden"  name="idCategoria" id="idCategoria" required>

              </div>

            </div>
  
          </div>

        </div>

        <!--=====================================
        PIE DEL MODAL
        ======================================-->

        <div class="modal-footer">

          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>

          <button type="submit" class="btn btn-primary">Guardar cambios</button>

        </div>

      <?php

          $editarCategoria = new ControladorCategorias();
          $editarCategoria -> ctrEditarCategoria();

        ?> 

      </form>

    </div>

  </div>

</div>

<script>
$(document).ready(function() {
    
    /*=============================================
    BORRAR TODAS LAS CATEGORÍAS
    =============================================*/
    $(document).on("click", ".btnBorrarTodasCategorias", function() {
        swal({
            title: "¿Está seguro?",
            text: "¡Esta acción eliminará TODAS las categorías de esta sucursal! Esta acción no se puede deshacer.",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "Sí, borrar todo",
            cancelButtonText: "Cancelar"
        }).then(function(result) {
            if (result.value) {
                var datos = new FormData();
                datos.append("borrarTodasCategorias", "ok");
                
                $.ajax({
                    url: "ajax/categorias.ajax.php",
                    method: "POST",
                    data: datos,
                    cache: false,
                    contentType: false,
                    processData: false,
                    success: function(respuesta) {
                        // La respuesta viene del controlador con el swal incluido
                        eval(respuesta);
                    }
                });
            }
        });
    });

    /*=============================================
    SINCRONIZAR CATEGORÍAS DESDE CENTRAL
    =============================================*/
    $(document).on("click", ".btnSincronizarCategorias", function() {
        swal({
            title: "¿Sincronizar categorías?",
            text: "Esta acción eliminará todas las categorías actuales y las reemplazará con las de categorías centrales. ¿Desea continuar?",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#5cb85c",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "Sí, sincronizar",
            cancelButtonText: "Cancelar"
        }).then(function(result) {
            if (result.value) {
                var datos = new FormData();
                datos.append("sincronizarCategorias", "ok");
                
                $.ajax({
                    url: "ajax/categorias.ajax.php",
                    method: "POST",
                    data: datos,
                    cache: false,
                    contentType: false,
                    processData: false,
                    success: function(respuesta) {
                        // La respuesta viene del controlador con el swal incluido
                        eval(respuesta);
                    }
                });
            }
        });
    });
});
</script>

