// Usamos la clase del body para asegurarnos de que este script solo se ejecute en la página correcta
if(document.body.classList.contains('crear-transferencia')){

// 1. INICIALIZAR LA TABLA DE CATÁLOGO DE PRODUCTOS (DERECHA)
var tablaProductos = $('.tablaProductosTransferencia').DataTable({
    "ajax": "ajax/datatable-productos-transferencia.ajax.php",
    "deferRender": true,
    "retrieve": true,
    "processing": true,
    "columns": [
        { "data": null, "render": function(data, type, row, meta) { return meta.row + 1; }},
        { "data": "imagen", "render": function(data) { return '<img src="'+data+'" class="img-thumbnail" width="40px">'; }},
        { "data": "codigo" },
        { "data": "descripcion" },
        { "data": "stock", "render": function(data) {
            if (data <= 10) { return `<button class="btn btn-danger btn-xs">${data}</button>`; }
            else if (data > 11 && data <= 15) { return `<button class="btn btn-warning btn-xs">${data}</button>`; }
            else { return `<button class="btn btn-success btn-xs">${data}</button>`; }
        }},
        { "data": null, "render": function(data, type, row){
            return `<div class='btn-group'><button class='btn btn-primary btnAgregarProducto' idProducto='${row.id}'>Agregar</button></div>`;
        }}
    ],
    "language": {
        "sProcessing": "Procesando...",
        "sLengthMenu": "Mostrar _MENU_ registros",
        "sZeroRecords": "No se encontraron resultados",
        "sEmptyTable": "Ningún producto encontrado",
        "sInfo": "Mostrando _START_ al _END_ de _TOTAL_ productos",
        "sInfoEmpty": "Mostrando 0 de 0 productos",
        "sInfoFiltered": "(filtrado de un total de _MAX_ productos)",
        "sSearch": "Buscar:",
        "oPaginate": { "sNext": "Siguiente", "sPrevious": "Anterior" }
    }
});

let listaProductosSolicitados = [];

// 2. LÓGICA PARA AGREGAR PRODUCTO DESDE EL CATÁLOGO AL FORMULARIO
$('.tablaProductosTransferencia').on('click', '.btnAgregarProducto', function() {
    var idProducto = $(this).attr("idProducto");
    $(this).removeClass('btn-primary btnAgregarProducto').addClass('btn-default');
    
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
                title: 'Ingrese la cantidad a solicitar',
                input: 'number',
                inputAttributes: { min: 1, step: 1 },
                showCancelButton: true,
                confirmButtonText: 'Agregar'
            }).then(function(result) {
                if (result.value && result.value > 0) {
                    let cantidad = result.value;
                    listaProductosSolicitados.push({ "id": idProducto, "descripcion": descripcion, "cantidad": cantidad });
                    mostrarProductosEnFormulario();
                } else {
                    $(`button[idProducto='${idProducto}']`).removeClass('btn-default').addClass('btn-primary btnAgregarProducto');
                }
            });
         }
     })
});

// 3. FUNCIÓN PARA DIBUJAR LOS PRODUCTOS EN EL FORMULARIO (IZQUIERDA)
function mostrarProductosEnFormulario() {
    $(".productosSolicitados").empty();
    listaProductosSolicitados.forEach(function(producto, index) {
        let fila = `
            <tr>
                <td>${producto.descripcion}</td>
                <td>${producto.cantidad}</td>
                <td><button type="button" class="btn btn-danger btn-xs btnQuitarProducto" index="${index}" idProducto="${producto.id}"><i class="fa fa-times"></i></button></td>
            </tr>`;
        $(".productosSolicitados").append(fila);
    });
}

// 4. LÓGICA PARA QUITAR UN PRODUCTO DEL FORMULARIO
$("body").on("click", ".btnQuitarProducto", function(){
    let index = $(this).attr("index");
    let idProducto = $(this).attr("idProducto");
    listaProductosSolicitados.splice(index, 1);
    mostrarProductosEnFormulario();
    $(`button[idProducto='${idProducto}']`).removeClass('btn-default').addClass('btn-primary btnAgregarProducto');
});

// 5. LÓGICA PARA ENVIAR EL FORMULARIO FINAL A LA API CENTRAL
$("#formularioTransferencia").on("submit", function(event){
    event.preventDefault();
    let sucursalDestino = $('select[name="seleccionarDestino"]').val();

    if(!sucursalDestino){
         swal({ type: 'error', title: 'Error', text: 'Debes seleccionar una sucursal de destino.' });
         return;
    }
    if(listaProductosSolicitados.length === 0){
         swal({ type: 'error', title: 'Error', text: 'Debes agregar al menos un producto a la solicitud.' });
         return;
    }

    let productosJson = JSON.stringify(listaProductosSolicitados);

    $.ajax({
        url: apiUrl + "crear_solicitud.php",
        method: "POST",
        data: {
            "sucursal_origen": nombreSucursal,
            "sucursal_destino": sucursalDestino,
            "usuario_solicitante": nombreUsuario,
            "productos": productosJson
        },
        dataType: "json",
        success: function(respuesta){
            if(respuesta.status === 'ok'){
                swal({
                    type: "success", title: "¡Éxito!", text: "La solicitud de transferencia ha sido creada.",
                }).then(() => {
                    window.location = "transferencias";
                });
            } else {
                 swal({ type: "error", title: "Error", text: "No se pudo crear la solicitud. " + respuesta.message });
            }
        }
    });
});
}