$(document).ready(function() {

    if ($('.tablaStockEnTransito').length > 0) {
        
        // Carga el inventario en tránsito
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
                    $('.tablaStockEnTransito tbody').append(fila);
                });

                if (!$.fn.DataTable.isDataTable('.tablaStockEnTransito')) {
                    $('.tablaStockEnTransito').DataTable({
                        "language": {
                           "sEmptyTable": "El almacén en tránsito está vacío.",
                           // ... Tus otras traducciones ...
                        }
                    });
                }
            }
        });
    }

    // LÓGICA PARA RECIBIR PRODUCTOS (VERSIÓN FINAL Y CORRECTA)
    $('.tablaStockEnTransito').on('click', '.btnRecibirStock', function() {
        
        var idStockTransito = $(this).data('id-stock-transito');
        var idProductoOrigen = $(this).data('id-producto-origen');
        var descripcion = $(this).data('descripcion');
        var cantidadMaxima = $(this).data('cantidad-max');
        var transportador = $(this).data('transportador');

        swal({
            title: `Recibir: ${descripcion}`,
            text: `Cantidad disponible en tránsito: ${cantidadMaxima}`,
            input: 'number',
            inputAttributes: { min: 1, step: 1, max: cantidadMaxima },
            showCancelButton: true,
            confirmButtonText: 'Confirmar Recepción'
        }).then((result) => {
            if (result.value && result.value > 0) {
                let cantidadRecibida = result.value;

                // PASO A: AUMENTAR stock local
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
                             // PASO B: DESCONTAR del stock en tránsito central
                            $.ajax({
                                url: apiUrl + "descargar_de_transito.php",
                                method: "POST",
                                data: {
                                    id_stock_transito: idStockTransito,
                                    cantidad: cantidadRecibida
                                },
                                dataType: "json",
                                success: function(respuestaDescuento){
                                    if(respuestaDescuento.status === "ok"){

                                        // PASO C: REGISTRAR la recepción en el historial
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