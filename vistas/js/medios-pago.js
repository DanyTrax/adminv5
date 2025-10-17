// EDITAR MEDIO DE PAGO
$(".tablas").on("click", ".btnEditarMedioPago", function(){
    var idMedioPago = $(this).attr("idMedioPago");
    var nombreMedioPago = $(this).closest("tr").find("td").eq(1).text();
    
    $("#editarMedioPago").val(nombreMedioPago);
    $("#idMedioPago").val(idMedioPago);
});

// ELIMINAR MEDIO DE PAGO
$(".tablas").on("click", ".btnEliminarMedioPago", function(){
    var idMedioPago = $(this).attr("idMedioPago");
    
    swal({
        title: '¿Está seguro de borrar el medio de pago?',
        text: "¡Si no lo está puede cancelar la acción!",
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        cancelButtonText: 'Cancelar',
        confirmButtonText: '¡Sí, borrar!'
    }).then(function(result){
        if (result.value) {
            window.location = "index.php?ruta=medios-pago&idMedioPago="+idMedioPago;
        }
    });
});