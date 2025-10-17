$(document).ready(function() {
    if ($('.tablaAlmacenTransito').length > 0) {
        $.ajax({
            url: apiUrl + "obtener_stock_transito.php",
            method: "GET",
            dataType: "json",
            success: function(stockItems) {
                stockItems.forEach(function(item) {
                    if (parseInt(item.cantidad) <= 0) return;
                    let fila = `<tr>
                            <td>${item.id_producto_origen}</td>
                            <td>${item.descripcion}</td>
                            <td><button class="btn btn-primary btn-xs">${item.cantidad}</button></td>
                            <td>${item.usuario_transporte}</td>
                            <td>
                                <div class="btn-group">
                                    <button class="btn btn-success btn-xs btnRecibirStock" 
                                            data-id-stock-transito="${item.id}" 
                                            data-id-producto-origen="${item.id_producto_origen}"
                                            data-descripcion="${item.descripcion}" 
                                            data-cantidad-max="${item.cantidad}"
                                            data-transportador="${item.usuario_transporte}"
                                            title="Recibir en esta sucursal">
                                        <i class="fa fa-download"></i> Recibir
                                    </button>
                                </div>
                            </td>
                        </tr>`;
                    $('.tablaAlmacenTransito tbody').append(fila);
                });
                $('.tablaAlmacenTransito').DataTable({ "language": { "sEmptyTable": "El almacén en tránsito está vacío.", "sSearch": "Buscar:" } });
            }
        });
    }

$('.tablaAlmacenTransito').on('click', '.btnRecibirStock', function() {
    
    var idStockTransito = $(this).data('id-stock-transito');
    var idProductoOrigen = $(this).data('id-producto-origen');
    var descripcion = $(this).data('descripcion');
    var cantidadMaxima = parseInt($(this).data('cantidad-max')); // Aseguramos que sea un número
    var transportador = $(this).data('transportador');

    swal({
        title: `Recibir: ${descripcion}`,
        text: `Cantidad disponible en tránsito: ${cantidadMaxima}`,
        input: 'number',
        inputAttributes: {
            min: 1, // Atributo HTML para el mínimo
            max: cantidadMaxima, // Atributo HTML para el máximo
            step: 1
        },
        showCancelButton: true,
        confirmButtonText: 'Confirmar Recepción',

        // --- INICIO DE LA LÓGICA DE VALIDACIÓN ---
        inputValidator: (value) => {
            return new Promise((resolve) => {
                if (!value) {
                    resolve('¡Necesitas escribir una cantidad!');
                }

                const cantidad = parseInt(value);

                if (isNaN(cantidad) || cantidad < 1) {
                    resolve('La cantidad debe ser un número mayor o igual a 1.');
                } else if (cantidad > cantidadMaxima) {
                    resolve(`La cantidad no puede ser mayor al stock en tránsito (${cantidadMaxima}).`);
                } else {
                    // Si todas las validaciones son correctas, no devolvemos ningún error.
                    resolve();
                }
            })
        }
        // --- FIN DE LA LÓGICA DE VALIDACIÓN ---

    }).then((result) => {
        // Esta parte solo se ejecuta si la validación es exitosa y el usuario hace clic en "Confirmar"
        if (result.value) {
            let cantidadRecibida = result.value;

            // El resto de tu código AJAX que ya funcionaba
            $.ajax({
                url: "ajax/transferencias.ajax.php",
                method: "POST",
                data: {
                    accion: "agregarStock",
                    idProducto: idProductoOrigen,
                    cantidad: cantidadRecibida
                },
                success: function(respuestaLocal){
                    if(respuestaLocal === "ok"){
                        $.ajax({
                            url: apiUrl + "descargar_de_transito.php",
                            method: "POST",
                            data: {
                                id_stock_transito: idStockTransito,
                                cantidad: cantidadRecibida,
                                usuario_recibe: nombreUsuario,
                                sucursal_destino: nombreSucursal
                            },
                            dataType: "json",
                            success: function(respuestaDescuento){
                                if(respuestaDescuento.status === "ok"){
                                    let productoRecibido = [{ "descripcion": descripcion, "cantidad": cantidadRecibida }];
                                    $.ajax({
                                        url: apiUrl + "registrar_recepcion.php",
                                        method: "POST",
                                        data: {
                                            sucursal_destino: nombreSucursal,
                                            usuario_recibe: nombreUsuario,
                                            usuario_transporte: transportador,
                                            productos_recibidos: JSON.stringify(productoRecibido)
                                        },
                                        dataType: "json",
                                        success: function(respuestaRegistro){
                                            if(respuestaRegistro.status === 'ok'){
                                                swal({ type: 'success', title: '¡Éxito!', text: 'El stock ha sido actualizado y la recepción ha sido registrada.'})
                                                .then(()=> { window.location.reload(); });
                                            }
                                        }
                                    });
                                }
                            }
                        });
                    } else {
                        swal('Error', 'Error al sumar el stock local.', 'error');
                    }
                }
            });
        }
    });
});
});