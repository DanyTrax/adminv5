<?php

class ControladorCategorias{

	/*=============================================
	CREAR CATEGORIAS
	=============================================*/
	static public function ctrCrearCategoria(){

		if(isset($_POST["nuevaCategoria"])){

			if(preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["nuevaCategoria"])){

				$tabla = "categorias";
				$datos = $_POST["nuevaCategoria"];
				$respuesta = ModeloCategorias::mdlIngresarCategoria($tabla, $datos);

				if($respuesta == "ok"){
					echo'<script>
					swal({
						  type: "success",
						  title: "La categoría ha sido guardada correctamente",
						  showConfirmButton: true,
						  confirmButtonText: "Cerrar"
						  }).then(function(result){
									if (result.value) {
									window.location = "categorias";
									}
								})
					</script>';
				}

			}else{
				echo'<script>
					swal({
						  type: "error",
						  title: "¡La categoría no puede ir vacía o llevar caracteres especiales!",
						  showConfirmButton: true,
						  confirmButtonText: "Cerrar"
						  }).then(function(result){
							if (result.value) {
							window.location = "categorias";
							}
						})
			  	</script>';
			}
		}
	}

	/*=============================================
	MOSTRAR CATEGORIAS
	=============================================*/
	static public function ctrMostrarCategorias($item, $valor){
		$tabla = "categorias";
		$respuesta = ModeloCategorias::mdlMostrarCategorias($tabla, $item, $valor);
		return $respuesta;
	}

	/*=============================================
	EDITAR CATEGORIA
	=============================================*/
	static public function ctrEditarCategoria(){

		if(isset($_POST["editarCategoria"])){

			if(preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["editarCategoria"])){

				$tabla = "categorias";
				$datos = array("categoria"=>$_POST["editarCategoria"],
							   "id"=>$_POST["idCategoria"]);
				$respuesta = ModeloCategorias::mdlEditarCategoria($tabla, $datos);

				if($respuesta == "ok"){
					echo'<script>
					swal({
						  type: "success",
						  title: "La categoría ha sido cambiada correctamente",
						  showConfirmButton: true,
						  confirmButtonText: "Cerrar"
						  }).then(function(result){
									if (result.value) {
									window.location = "categorias";
									}
								})
					</script>';
				}

			}else{
				echo'<script>
					swal({
						  type: "error",
						  title: "¡La categoría no puede ir vacía o llevar caracteres especiales!",
						  showConfirmButton: true,
						  confirmButtonText: "Cerrar"
						  }).then(function(result){
							if (result.value) {
							window.location = "categorias";
							}
						})
			  	</script>';
			}
		}
	}

    /*=============================================
    BORRAR CATEGORIA (VERSIÓN AJAX CORREGIDA)
    =============================================*/
    static public function ctrBorrarCategoria($idCategoria){
    
        // Primero, verificamos si la categoría tiene productos asociados
        $productosAsociados = ModeloProductos::mdlContarProductosPorCategoria("productos", "id_categoria", $idCategoria);
    
        // Si el conteo es exactamente '0', podemos borrar
        if($productosAsociados == '0'){
    
            $tabla = "categorias";
            $datos = $idCategoria;
            $respuesta = ModeloCategorias::mdlBorrarCategoria($tabla, $datos);
            return $respuesta; // Debería retornar "ok" o "error" desde el modelo
    
        } else {
    
            // Si el conteo es mayor a 0, retornamos un error específico
            return "error_con_productos";
        }
    }

/*=============================================
BORRAR TODAS LAS CATEGORÍAS DE LA SUCURSAL
=============================================*/
static public function ctrBorrarTodasCategorias() {
    if (isset($_POST["borrarTodasCategorias"])) {
        $tabla = "categorias";
        $respuesta = ModeloCategorias::mdlBorrarTodasCategorias($tabla);
        
        if ($respuesta == "ok") {
            echo '<script>
                swal({
                    type: "success",
                    title: "Categorías eliminadas",
                    text: "Todas las categorías han sido eliminadas de esta sucursal",
                    showConfirmButton: true,
                    confirmButtonText: "Cerrar"
                }).then(function(result){
                    if (result.value) {
                        window.location = "categorias";
                    }
                });
            </script>';
        } else {
            echo '<script>
                swal({
                    type: "error",
                    title: "Error",
                    text: "No se pudieron eliminar las categorías",
                    showConfirmButton: true,
                    confirmButtonText: "Cerrar"
                });
            </script>';
        }
    }
}

/*=============================================
SINCRONIZAR CATEGORÍAS DESDE CENTRAL
=============================================*/
static public function ctrSincronizarCategorias() {
    if (isset($_POST["sincronizarCategorias"])) {
        $tabla = "categorias";
        $respuesta = ModeloCategorias::mdlSincronizarCategoriasDesdeCentral($tabla);
        
        if ($respuesta == "ok") {
            echo '<script>
                swal({
                    type: "success",
                    title: "Sincronización completada",
                    text: "Las categorías han sido sincronizadas desde las categorías centrales",
                    showConfirmButton: true,
                    confirmButtonText: "Cerrar"
                }).then(function(result){
                    if (result.value) {
                        window.location = "categorias";
                    }
                });
            </script>';
        } else if ($respuesta == "error_borrar") {
            echo '<script>
                swal({
                    type: "error",
                    title: "Error al borrar",
                    text: "No se pudieron eliminar las categorías existentes",
                    showConfirmButton: true,
                    confirmButtonText: "Cerrar"
                });
            </script>';
        } else if ($respuesta == "error_sincronizar") {
            echo '<script>
                swal({
                    type: "error",
                    title: "Error al sincronizar",
                    text: "No se pudieron sincronizar las categorías desde central",
                    showConfirmButton: true,
                    confirmButtonText: "Cerrar"
                });
            </script>';
        } else {
            echo '<script>
                swal({
                    type: "error",
                    title: "Error",
                    text: "Ocurrió un error durante la sincronización",
                    showConfirmButton: true,
                    confirmButtonText: "Cerrar"
                });
            </script>';
        }
    }
}
}