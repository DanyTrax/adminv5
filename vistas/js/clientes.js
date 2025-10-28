/*=============================================
EDITAR CLIENTE
=============================================*/
$(".tablas").on("click", ".btnEditarCliente", function(){

	var idCliente = $(this).attr("idCliente");

	var datos = new FormData();
    datos.append("idCliente", idCliente);

    $.ajax({

      url:"ajax/clientes.ajax.php",
      method: "POST",
      data: datos,
      cache: false,
      contentType: false,
      processData: false,
      dataType:"json",
      success:function(respuesta){
      
      	   $("#idCliente").val(respuesta["id"]);
	       $("#editarCliente").val(respuesta["nombre"]);
	       $("#editarDocumentoId").val(respuesta["documento"]);
	       $("#editarEmail").val(respuesta["email"]);
	       $("#editarTelefono").val(respuesta["telefono"]);
	       $("#editarDireccion").val(respuesta["direccion"]);
           
	  }

  	})

})

/*=============================================
ELIMINAR CLIENTE
=============================================*/
$(".tablas").on("click", ".btnEliminarCliente", function(){

	var idCliente = $(this).attr("idCliente");
	
	swal({
        title: '¿Está seguro de borrar el cliente?',
        text: "¡Si no lo está puede cancelar la acción!",
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        cancelButtonText: 'Cancelar',
        confirmButtonText: 'Si, borrar cliente!'
      }).then(function(result){
        if (result.value) {
          
            window.location = "index.php?ruta=clientes&idCliente="+idCliente;
        }

  })

})

/*=============================================
VALIDACIÓN DE TELÉFONO - SOLO NÚMEROS
=============================================*/
$(document).on('input', 'input[name="nuevoTelefono"]', function() {
    // Remover cualquier carácter que no sea número
    var valor = $(this).val().replace(/[^0-9]/g, '');
    $(this).val(valor);
    
    // Validar longitud
    if (valor.length < 7) {
        $(this).css('border-color', '#f39c12');
    } else if (valor.length > 10) {
        $(this).val(valor.substring(0, 10));
        $(this).css('border-color', '#e74c3c');
    } else {
        $(this).css('border-color', '#27ae60');
    }
});

/*=============================================
VALIDACIÓN DE TELÉFONO EN MODAL EDITAR
=============================================*/
$(document).on('input', 'input[name="editarTelefono"]', function() {
    // Remover cualquier carácter que no sea número
    var valor = $(this).val().replace(/[^0-9]/g, '');
    $(this).val(valor);
    
    // Validar longitud
    if (valor.length < 7) {
        $(this).css('border-color', '#f39c12');
    } else if (valor.length > 10) {
        $(this).val(valor.substring(0, 10));
        $(this).css('border-color', '#e74c3c');
    } else {
        $(this).css('border-color', '#27ae60');
    }
});