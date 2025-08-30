$(document).ready(function() {
    if ($('.tablaRecepciones').length > 0) {
        $('.tablaRecepciones').DataTable({
            "ajax": apiUrl + "obtener_recepciones.php",
            "deferRender": true,
            "retrieve": true,
            "processing": true,
            "columns": [
                { "data": "id" },
                { "data": "sucursal_destino" },
                { "data": "usuario_recibe" },
                { "data": "usuario_transporte" },
                { "data": "fecha_recepcion" },
                { "data": null, "render": function(data, type, row) {
                    let productosData = encodeURIComponent(row.productos_recibidos);
                    return `<div class="btn-group">
                                <button class="btn btn-info btn-xs btnVerDetalle" 
                                        data-productos='${productosData}' 
                                        data-toggle="modal" 
                                        data-target="#modalVerDetalleRecepcion" 
                                        title="Ver Detalle">
                                    <i class="fa fa-eye"></i>
                                </button>
                            </div>`;
                }}
            ],
            "language": { "sEmptyTable": "No hay recepciones registradas.", "sSearch": "Buscar:" }
        });

        $('.tablaRecepciones').on('click', '.btnVerDetalle', function(){
            let productosJSON = decodeURIComponent($(this).data('productos'));
            let productos = JSON.parse(productosJSON);

            $("#detalleProductosRecibidos").empty();
            productos.forEach(function(producto){
                let filaModal = `
                    <tr>
                        <td>${producto.descripcion}</td>
                        <td>${producto.cantidad}</td>
                    </tr>`;
                $("#detalleProductosRecibidos").append(filaModal);
            });
        });
    }
});