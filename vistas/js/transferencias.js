$(document).ready(function() {

    // Verificamos que estemos en la página de transferencias
    if($('.tablaTransferencias').length > 0) {

        // Hacemos la petición a la API usando la constante global 'apiUrl' definida en plantilla.php
        $.ajax({
            // CORRECCIÓN: Usamos la constante 'apiUrl' y le añadimos el nombre del archivo específico
            url: apiUrl + "obtener_solicitudes.php",
            method: "GET",
            dataType: "json",
            success: function(solicitudes) {
                
                $('.tablaTransferencias tbody').empty();

                solicitudes.forEach(function(solicitud) {
                    
                    // --- LÓGICA PARA COLOREAR ESTADOS ---
                    let estadoBoton = '';
                    switch(solicitud.estado) {
                        case 'pendiente':
                            estadoBoton = `<button class="btn btn-warning btn-xs">${solicitud.estado}</button>`;
                            break;
                        case 'aceptada':
                            estadoBoton = `<button class="btn btn-info btn-xs">${solicitud.estado}</button>`;
                            break;
                        case 'en_transito':
                            estadoBoton = `<button class="btn btn-primary btn-xs">${solicitud.estado}</button>`;
                            break;
                        case 'completada':
                            estadoBoton = `<button class="btn btn-success btn-xs">${solicitud.estado}</button>`;
                            break;
                        case 'rechazada':
                            estadoBoton = `<button class="btn btn-danger btn-xs">${solicitud.estado}</button>`;
                            break;
                        default:
                            estadoBoton = `<button class="btn btn-default btn-xs">${solicitud.estado}</button>`;
                    }
                    // --- FIN DE LA LÓGICA DE COLORES ---
                
                    // Definimos los botones de acciones según el estado
                    let acciones = `<div class='btn-group'>
                                      <button class='btn btn-info btn-xs btnVerProductos' idSolicitud='${solicitud.id}' data-toggle='modal' data-target='#modalVerProductos' title='Ver Productos'><i class='fa fa-eye'></i></button>
                                      <button class='btn btn-primary btn-xs btnImprimirTransferencia' idSolicitud='${solicitud.id}' title='Imprimir'><i class='fa fa-print'></i></button>`;
                
                    if(solicitud.estado === 'pendiente' && (nombreUsuario === 'Administrador' /* || es transportador */)) {
                        acciones += `<button class='btn btn-success btn-xs btnAceptarSolicitud' idSolicitud='${solicitud.id}'>Aceptar</button> 
                                     <button class='btn btn-danger btn-xs btnRechazarSolicitud' idSolicitud='${solicitud.id}'>Rechazar</button>`;
                    }
                
                    if(solicitud.estado === 'aceptada' && solicitud.sucursal_origen === nombreSucursal) {
                        // Este botón solo lo verá la sucursal que debe despachar
                        acciones += `<button class='btn btn-primary btn-xs btnDespachar' idSolicitud='${solicitud.id}'>Despachar a Tránsito</button>`;
                    }
                    
                    if(solicitud.estado === 'en_transito' && (nombreUsuario === 'Administrador' /* || es transportador */)) {
                        acciones += `<button class='btn btn-success btn-xs btnFinalizar' idSolicitud='${solicitud.id}'>Finalizar Entrega</button>`;
                    }
                
                    acciones += `</div>`;
                
                    // Creamos la fila de la tabla
                    let fila = `
                        <tr>
                            <td>${solicitud.id}</td>
                            <td>${solicitud.sucursal_origen}</td>
                            <td>${solicitud.usuario_solicitante}</td>
                            <td>${estadoBoton}</td>
                            <td>${solicitud.fecha_solicitud}</td>
                            <td>${acciones}</td>
                        </tr>
                    `;
                    
                    $('.tablaTransferencias tbody').append(fila);
                });

                // Inicializamos DataTables DESPUÉS de llenar la tabla
                if (!$.fn.DataTable.isDataTable('.tablaTransferencias')) {
                    $('.tablaTransferencias').DataTable({
                        "language": {
                            "sProcessing": "Procesando...",
                            "sLengthMenu": "Mostrar _MENU_ registros",
                            "sZeroRecords": "No se encontraron resultados",
                            "sEmptyTable": "Ninguna solicitud de transferencia encontrada.",
                            "sInfo": "Mostrando del _START_ al _END_ de un total de _TOTAL_ solicitudes",
                            "sInfoEmpty": "Mostrando 0 de 0 solicitudes",
                            "sInfoFiltered": "(filtrado de un total de _MAX_ solicitudes)",
                            "sSearch": "Buscar:",
                            "oPaginate": { "sNext": "Siguiente", "sPrevious": "Anterior" }
                        }
                    });
                }
            },
            error: function() {
                $('.tablaTransferencias tbody').append('<tr><td colspan="6">Error al cargar los datos. Verifique la URL de la API y la conexión con el servidor central.</td></tr>');
            }
        });
    }

    

// Al hacer clic en el botón "Ver Productos"
$('.tablaTransferencias').on('click', '.btnVerProductos', function(){
    let idSolicitud = $(this).attr('idSolicitud');
    
    // Limpiamos la modal por si tenía datos anteriores
    $("#listaProductosModal").empty();

    // Hacemos una llamada a la API para traer los datos de esa solicitud
    $.ajax({
        url: apiUrl + "obtener_solicitudes.php",
        method: "GET",
        data: { "id": idSolicitud },
        dataType: "json",
        success: function(respuesta){
            // El campo 'productos' es un string JSON, necesitamos decodificarlo
            let productos = JSON.parse(respuesta.productos);

            // Recorremos la lista de productos y la añadimos a la tabla de la modal
            productos.forEach(function(producto){
                let fila = `
                    <tr>
                        <td>${producto.descripcion}</td>
                        <td>${producto.cantidad}</td>
                    </tr>`;
                $("#listaProductosModal").append(fila);
            });
        }
    });
});

$('.tablaTransferencias').on('click', '.btnImprimirTransferencia', function(){
    let idSolicitud = $(this).attr('idSolicitud');
    // Abrimos el generador de PDF en una nueva pestaña
    window.open(`pdf/transferencia.php?id=${idSolicitud}`, "_blank");
});
// Al hacer clic en el botón "Aceptar Solicitud"
$('.tablaTransferencias').on('click', '.btnAceptarSolicitud', function() {
    
    const idSolicitud = $(this).attr('idSolicitud');

    swal({
        title: '¿Aceptar esta solicitud?',
        text: "La solicitud pasará a estado 'aceptada' y quedará pendiente de despacho por la sucursal de origen.",
        type: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, Aceptar'
    }).then(function(result) {
        if (result.value) {
            
            // Llamamos a la API para actualizar el estado a "aceptada"
            $.ajax({
                url: apiUrl + "actualizar_estado.php",
                method: "POST",
                data: {
                    id_solicitud: idSolicitud,
                    nuevo_estado: "aceptada",
                    usuario_accion: nombreUsuario,
                    sucursal_accion: nombreSucursal,
                    accion_log: "Aceptó la solicitud."
                },
                dataType: "json",
                success: function(respuesta) {
                    if(respuesta.status === 'ok'){
                        swal({
                            type: "success",
                            title: "¡Aceptada!",
                            text: "La solicitud ha sido aceptada correctamente.",
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        swal({ type: "error", title: "Error", text: respuesta.message });
                    }
                }
            });

        }
    });
});
});