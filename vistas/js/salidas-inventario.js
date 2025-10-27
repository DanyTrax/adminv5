/*=============================================
SALIDAS DE INVENTARIO - VERSIÓN SIMPLIFICADA
=============================================*/

// Inicializar DataTable solo para esta página específica
$(document).ready(function() {
    // Verificar si estamos en la página de salidas de inventario
    if (window.location.href.indexOf('salidas-inventario') > -1) {
        
        // Destruir DataTable existente si existe
        if ($.fn.DataTable.isDataTable('.tablas-salidas')) {
            $('.tablas-salidas').DataTable().destroy();
        }
        
        // Inicializar DataTable solo para esta página
        $('.tablas-salidas').DataTable({
            "language": {
                "sProcessing": "Procesando...",
                "sLengthMenu": "Mostrar _MENU_ registros",
                "sZeroRecords": "No se encontraron resultados",
                "sEmptyTable": "Ningún dato disponible en esta tabla",
                "sInfo": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_",
                "sInfoEmpty": "Mostrando registros del 0 al 0 de un total de 0",
                "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
                "sInfoPostFix": "",
                "sSearch": "Buscar:",
                "sUrl": "",
                "sInfoThousands": ",",
                "sLoadingRecords": "Cargando...",
                "oPaginate": {
                    "sFirst": "Primero",
                    "sLast": "Último",
                    "sNext": "Siguiente",
                    "sPrevious": "Anterior"
                },
                "oAria": {
                    "sSortAscending": ": Activar para ordenar la columna de manera ascendente",
                    "sSortDescending": ": Activar para ordenar la columna de manera descendente"
                }
            }
        });
        
        console.log('✅ DataTable de Salidas de Inventario inicializada');
    }
});

/*=============================================
BÚSQUEDA AJAX DE PRODUCTOS
=============================================*/

// Búsqueda de productos con AJAX
$("#buscarProducto").on("keyup", function(){
    
    var busqueda = $(this).val();
    var resultados = $("#resultadosProductos");
    
    if(busqueda.length >= 2){
        
        var datos = new FormData();
        datos.append("buscarProductos", busqueda);

        $.ajax({
            url: "ajax/salidas-inventario.ajax.php",
            method: "POST",
            data: datos,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function(respuesta){
                
                if(respuesta.length > 0){
                    
                    var html = "";
                    
                    for(var i = 0; i < respuesta.length; i++){
                        
                        html += '<div class="resultado-producto" style="padding: 10px; border-bottom: 1px solid #eee; cursor: pointer;" ' +
                                'data-id="' + respuesta[i]["id"] + '" ' +
                                'data-codigo="' + respuesta[i]["codigo"] + '" ' +
                                'data-descripcion="' + respuesta[i]["descripcion"] + '" ' +
                                'data-stock="' + respuesta[i]["stock"] + '">' +
                                '<strong>' + respuesta[i]["descripcion"] + '</strong><br>' +
                                '<small class="text-muted">Código: ' + respuesta[i]["codigo"] + ' | Stock: ' + respuesta[i]["stock"] + '</small>' +
                                '</div>';
                    }
                    
                    resultados.html(html);
                    resultados.show();
                    
                } else {
                    
                    resultados.html('<div style="padding: 10px; color: #999;">No se encontraron productos</div>');
                    resultados.show();
                    
                }
                
            },
            error: function(){
                resultados.html('<div style="padding: 10px; color: #d9534f;">Error al buscar productos</div>');
                resultados.show();
            }
        });
        
    } else {
        resultados.hide();
    }
});

/*=============================================
SELECCIONAR PRODUCTO
=============================================*/

$(document).on("click", ".resultado-producto", function(){
    
    var id = $(this).data("id");
    var codigo = $(this).data("codigo");
    var descripcion = $(this).data("descripcion");
    var stock = $(this).data("stock");
    
    // Llenar campos
    $("#buscarProducto").val(descripcion);
    $("#nuevoProducto").val(id);
    
    // Mostrar información del producto
    $("#infoProducto").html(
        '<strong>' + descripcion + '</strong><br>' +
        '<small>Código: ' + codigo + ' | Stock disponible: ' + stock + '</small>'
    );
    
    $("#productoSeleccionado").show();
    
    // Ocultar resultados
    $("#resultadosProductos").hide();
    
    // Actualizar máximo del input de cantidad
    $("input[name='nuevaCantidad']").attr("max", stock);
    
});

/*=============================================
OCULTAR RESULTADOS AL HACER CLIC FUERA
=============================================*/

$(document).on("click", function(e){
    
    if(!$(e.target).closest("#buscarProducto, #resultadosProductos").length){
        $("#resultadosProductos").hide();
    }
    
});

/*=============================================
ELIMINAR SALIDA DE INVENTARIO
=============================================*/

$(".tablas-salidas").on("click", ".btnEliminarSalida", function(){

    var idSalida = $(this).attr("idSalida");
    
    swal({
        title: '¿Está seguro de borrar la salida de inventario?',
        text: "¡Si no lo está puede cancelar la acción!",
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        cancelButtonText: 'Cancelar',
        confirmButtonText: 'Si, borrar salida!'
    }).then((result) => {
        if (result.value) {
            
            window.location = "salidas-inventario?idSalida="+idSalida;

        }

    })

});

/*=============================================
VALIDAR CANTIDAD MÁXIMA
=============================================*/

$("input[name='nuevaCantidad']").on("input", function(){

    var cantidad = $(this).val();
    
    if(parseInt(cantidad) > 10){
        
        $(this).val(10);
        
        swal({
            title: "¡Atención!",
            text: "La cantidad máxima permitida es 10 unidades",
            type: "warning",
            confirmButtonText: "Entendido"
        });

    }

});

/*=============================================
BÚSQUEDA AJAX DE REMISIONES
=============================================*/

// Búsqueda de remisiones con AJAX
$("#buscarRemision").on("keyup", function(){
    
    var busqueda = $(this).val();
    var resultados = $("#resultadosRemisiones");
    
    if(busqueda.length >= 2){
        
        var datos = new FormData();
        datos.append("buscarRemisiones", busqueda);

        $.ajax({
            url: "ajax/salidas-inventario.ajax.php",
            method: "POST",
            data: datos,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function(respuesta){
                
                if(respuesta.length > 0){
                    
                    var html = "";
                    
                    for(var i = 0; i < respuesta.length; i++){
                        
                        html += '<div class="resultado-remision" style="padding: 10px; border-bottom: 1px solid #eee; cursor: pointer;" ' +
                                'data-codigo="' + respuesta[i]["codigo"] + '">' +
                                '<strong>Remisión #' + respuesta[i]["codigo"] + '</strong><br>' +
                                '<small class="text-muted">Cliente: ' + respuesta[i]["cliente_nombre"] + ' | Vendedor: ' + respuesta[i]["vendedor_nombre"] + '<br>' +
                                'Total: $' + parseFloat(respuesta[i]["total"]).toFixed(0) + ' | Fecha: ' + respuesta[i]["fecha_venta"] + '</small>' +
                                '</div>';
                    }
                    
                    resultados.html(html);
                    resultados.show();
                    
                } else {
                    
                    resultados.html('<div style="padding: 10px; color: #999;">No se encontraron remisiones</div>');
                    resultados.show();
                    
                }
                
            },
            error: function(){
                resultados.html('<div style="padding: 10px; color: #d9534f;">Error al buscar remisiones</div>');
                resultados.show();
            }
        });
        
    } else {
        resultados.hide();
    }
});

/*=============================================
SELECCIONAR REMISIÓN
=============================================*/

$(document).on("click", ".resultado-remision", function(){
    
    var codigo = $(this).data("codigo");
    
    // Llenar campo de remisión
    $("#buscarRemision").val(codigo);
    
    // Ocultar resultados
    $("#resultadosRemisiones").hide();
    
});

/*=============================================
OCULTAR RESULTADOS DE REMISIONES AL HACER CLIC FUERA
=============================================*/

$(document).on("click", function(e){
    
    if(!$(e.target).closest("#buscarRemision, #resultadosRemisiones").length){
        $("#resultadosRemisiones").hide();
    }
    
});

/*=============================================
LIMPIAR FORMULARIO AL CERRAR MODAL
=============================================*/

$("#modalAgregarSalida").on("hidden.bs.modal", function(){
    
    // Limpiar formulario
    $("#buscarProducto").val("");
    $("#nuevoProducto").val("");
    $("input[name='nuevaCantidad']").val("");
    $("textarea[name='nuevaDescripcion']").val("");
    $("#buscarRemision").val("");
    
    // Ocultar elementos
    $("#productoSeleccionado").hide();
    $("#resultadosProductos").hide();
    $("#resultadosRemisiones").hide();
    
});
