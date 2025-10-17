/*=============================================
EDITAR CATEGORIA
=============================================*/
$(".tablas").on("click", ".btnEditarCategoria", function(){

	var idCategoria = $(this).attr("idCategoria");

	var datos = new FormData();
	datos.append("idCategoria", idCategoria);

	$.ajax({
		url: "ajax/categorias.ajax.php",
		method: "POST",
      	data: datos,
      	cache: false,
     	contentType: false,
     	processData: false,
     	dataType:"json",
     	success: function(respuesta){

     		$("#editarCategoria").val(respuesta["categoria"]);
     		$("#idCategoria").val(respuesta["id"]);

     	}

	})


})

/*=============================================
ELIMINAR CATEGORIA (VERSIÓN AJAX)
=============================================*/
$(".tablas").on("click", ".btnEliminarCategoria", function(){
    var idCategoria = $(this).attr("idCategoria");

    swal({
        title: '¿Está seguro de borrar la categoría?',
        text: "¡Si no lo está puede cancelar la acción!",
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        cancelButtonText: 'Cancelar',
        confirmButtonText: '¡Sí, borrar categoría!'
    }).then(function(result) {
        if (result.value) {
            var datos = new FormData();
            datos.append("idCategoriaBorrar", idCategoria); // Señal para el archivo AJAX

            $.ajax({
                url: "ajax/categorias.ajax.php",
                method: "POST",
                data: datos,
                cache: false,
                contentType: false,
                processData: false,
                success: function(respuesta) {
                    if (respuesta.trim() == "ok") {
                        swal({
                            type: "success",
                            title: "¡La categoría ha sido borrada correctamente!",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        }).then(function(result){
                            if (result.value) {
                                window.location.reload(); // Recargamos para ver los cambios
                            }
                        });
                    } else if (respuesta.trim() == "error_con_productos") {
                        swal({
                            type: "error",
                            title: "Error",
                            text: "La categoría tiene productos asociados y no puede ser eliminada.",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        });
                    } else {
                        swal({
                            type: "error",
                            title: "Error",
                            text: "Ocurrió un error al intentar borrar la categoría.",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        });
                    }
                }
            });
        }
    });
});