// INICIALIZACIÓN DE LA TABLA DE CATÁLOGO DE PRODUCTOS (DERECHA)
$('.tablaCatalogoDespacho').DataTable({
    "ajax": "ajax/datatable-productos-transferencia.ajax.php", // Reutilizamos el ajax que ya creamos
    "deferRender": true,
    "retrieve": true,
    "processing": true,
    "columns": [
        { "data": null, "render": function(data, type, row, meta) { return meta.row + 1; }},
        { "data": "imagen", "render": function(data) { return '<img src="'+data+'" class="img-thumbnail" width="40px">'; }},
        { "data": "codigo" },
        { "data": "descripcion" },
        { "data": "stock", "render": function(data) {
            return `<button class="btn bg-olive btn-xs">${data}</button>`; 
        }},
        { "data": null, "render": function(data, type, row){
            return `<div class='btn-group'><button class='btn btn-primary btnAgregarDespacho' idProducto='${row.id}' stockActual='${row.stock}'>Agregar</button></div>`;
        }}
    ],
    "language": { /* ... Tus traducciones al español ... */ }
});

let listaProductosDespacho = [];

// LÓGICA PARA AGREGAR PRODUCTO DESDE EL CATÁLOGO AL MANIFIESTO
$('.tablaCatalogoDespacho').on('click', '.btnAgregarDespacho', function() {
    var idProducto = $(this).attr("idProducto");
    var stockActual = $(this).attr("stockActual");
    
    $(this).removeClass('btn-primary btnAgregarDespacho').addClass('btn-default');
    
    var datos = new FormData();
    datos.append("idProducto", idProducto);

     $.ajax({
         url:"ajax/productos.ajax.php", 
         method: "POST", 
         data: datos, 
         cache: false, 
         contentType: false, 
         processData: false, 
         dataType:"json",
         success:function(respuesta){
            let descripcion = respuesta.descripcion;
            
            swal({
                title: 'Ingrese la cantidad a despachar',
                input: 'number', // Esto ya restringe a solo números
                inputAttributes: { 
                    min: 1, // El mínimo que se puede ingresar es 1
                    step: 1
                },
                showCancelButton: true,
                confirmButtonText: 'Agregar'
            }).then(function(result) {
                
                // Verificamos si el usuario escribió un valor
                if (result.value) {
                    
                    let cantidad = parseInt(result.value);

                    // --- INICIO DE LAS NUEVAS VALIDACIONES ---

                    // Regla 1: La cantidad debe ser un número válido y mayor a 0
                    if (isNaN(cantidad) || cantidad <= 0) {
                        swal({
                            type: 'error',
                            title: 'Cantidad no válida',
                            text: 'Por favor, ingrese un número entero mayor a cero.'
                        });
                        // Volvemos a activar el botón de "Agregar"
                        $(`button[idProducto='${idProducto}']`).removeClass('btn-default').addClass('btn-primary btnAgregarDespacho');
                        return; // Detenemos la ejecución
                    }

                    // Regla 2: La cantidad no puede ser mayor al stock
                    if (cantidad > parseInt(stockActual)) {
                        swal({
                            type: 'error',
                            title: 'Cantidad excede el stock',
                            text: 'No puedes despachar más productos de los que tienes en inventario (' + stockActual + ' disponibles).'
                        });
                        // Volvemos a activar el botón de "Agregar"
                        $(`button[idProducto='${idProducto}']`).removeClass('btn-default').addClass('btn-primary btnAgregarDespacho');
                        return; // Detenemos la ejecución
                    }

                    // --- FIN DE LAS NUEVAS VALIDACIONES ---

                    // Si todas las validaciones pasan, agregamos el producto
                    listaProductosDespacho.push({ "id": idProducto, "descripcion": descripcion, "cantidad": cantidad });
                    mostrarProductosEnDespacho();

                } else {
                    // Si el usuario cancela, también reactivamos el botón
                    $(`button[idProducto='${idProducto}']`).removeClass('btn-default').addClass('btn-primary btnAgregarDespacho');
                }
            });
         }
     })
});
// FUNCIÓN PARA DIBUJAR LOS PRODUCTOS EN EL FORMULARIO (IZQUIERDA)
function mostrarProductosEnDespacho() {
    $(".productosDespacho").empty();
    listaProductosDespacho.forEach(function(producto, index) {
        let fila = `
            <tr>
                <td>${producto.descripcion}</td>
                <td>${producto.cantidad}</td>
                <td><button type="button" class="btn btn-danger btn-xs btnQuitarDespacho" index="${index}" idProducto="${producto.id}"><i class="fa fa-times"></i></button></td>
            </tr>`;
        $(".productosDespacho").append(fila);
    });
}

// LÓGICA PARA QUITAR UN PRODUCTO DEL FORMULARIO
$("body").on("click", ".btnQuitarDespacho", function(){
    let index = $(this).attr("index");
    let idProducto = $(this).attr("idProducto");
    listaProductosDespacho.splice(index, 1);
    mostrarProductosEnDespacho();
    $(`button[idProducto='${idProducto}']`).removeClass('btn-default').addClass('btn-primary btnAgregarDespacho');
});

// LÓGICA PARA ENVIAR EL FORMULARIO FINAL A LA API CENTRAL
$("#formularioDespacho").on("submit", function(event){
    event.preventDefault();
    let transportador = $('select[name="seleccionarTransportador"]').val();

    if(!transportador){
         swal({ type: 'error', title: 'Error', text: 'Debes seleccionar un transportador.' });
         return;
    }
    if(listaProductosDespacho.length === 0){
         swal({ type: 'error', title: 'Error', text: 'Debes agregar al menos un producto al despacho.' });
         return;
    }

    let productosJson = JSON.stringify(listaProductosDespacho);

    $.ajax({
        url: apiUrl + "iniciar_cargue.php",
        method: "POST",
        data: {
            "sucursal_origen": nombreSucursal,
            "usuario_despacho": nombreUsuario,
            "usuario_transporte": transportador,
            "productos": productosJson
        },
        dataType: "json",
        success: function(respuesta){
            if(respuesta.status === 'ok'){
                swal({
                    type: "success", title: "¡Éxito!", text: "El cargue se ha iniciado y se ha notificado al transportador.",
                }).then(() => {
                    // Redirigimos a la nueva página de cargues pendientes
                    window.location = "cargues-pendientes"; 
                });
            } else {
                 swal({ type: "error", title: "Error", text: "No se pudo iniciar el cargue. " + respuesta.message });
            }
        }
    });
});