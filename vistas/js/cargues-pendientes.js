$(document).ready(function() {
    
    var tablaCargues = $('.tablaCarguesPendientes');

    if (tablaCargues.length > 0) {

        // 1. CARGAR Y DIBUJAR LA TABLA
        $.ajax({
            url: apiUrl + "obtener_cargues.php",
            dataType: "json",
success: function(cargues) {
    
    tablaCargues.find('tbody').empty();

    cargues.forEach(function(cargue) {
        
        // --- Lógica para colorear estados ---
        let estadoBoton = '';
        switch (cargue.estado) {
            case 'pendiente_cargue':
                estadoBoton = `<button class="btn btn-warning btn-xs">Pendiente</button>`;
                break;
            case 'en_transito':
                estadoBoton = `<button class="btn btn-success btn-xs">Confirmado</button>`;
                break;
            case 'cancelada':
                estadoBoton = `<button class="btn btn-danger btn-xs">Cancelada</button>`;
                break;
            default:
                estadoBoton = `<button class="btn btn-success btn-xs">${cargue.estado}</button>`;
        }

        // --- Lógica de botones con permisos ---
        let acciones = `<button class='btn btn-info btn-xs btnVerManifiesto' idTransferencia='${cargue.id}' data-toggle='modal' data-target='#modalVerManifiesto'><i class='fa fa-eye'></i></button>`;

        // CORRECCIÓN: Definimos la variable aquí, dentro del bucle
        const esTransportadorAsignado = (perfilUsuario === 'Transportador' && nombreUsuario === cargue.usuario_transporte);

        if (cargue.estado === 'pendiente_cargue') {
            
            // Condición para CONFIRMAR
            if ((perfilUsuario === 'Administrador' || esTransportadorAsignado) && cargue.sucursal_origen === nombreSucursal) {
                acciones += `<button class='btn btn-success btn-xs btnConfirmarCargue' idTransferencia='${cargue.id}'>Confirmar</button>`;
            }
        
            // Condición para CANCELAR
            if (perfilUsuario === 'Administrador' || esTransportadorAsignado) {
               acciones += `<button class='btn btn-warning btn-xs btnCancelarCargue' idTransferencia='${cargue.id}'>Cancelar</button>`;
            }
        }
        
        // Condición para ELIMINAR
        if (perfilUsuario === 'Administrador') {
            acciones += `<button class='btn btn-danger btn-xs btnEliminarTransferencia' idTransferencia='${cargue.id}'><i class='fa fa-trash'></i></button>`;
        }
        
        let fechaEstado = cargue.fecha_actualizacion_estado ? cargue.fecha_actualizacion_estado : '---';
        
        let fila = `
            <tr>
                <td>${cargue.id}</td>
                <td>${cargue.sucursal_origen}</td>
                <td>${cargue.usuario_despacho}</td>
                <td>${cargue.usuario_transporte}</td>
                <td>${cargue.fecha_despacho}</td>
                <td>${fechaEstado}</td>
                <td>${estadoBoton}</td>
                <td><div class='btn-group'>${acciones}</div></td>
            </tr>
        `;
        
        tablaCargues.find('tbody').append(fila);
    });

    // Reinicializamos la tabla con los nuevos datos
    if (!$.fn.DataTable.isDataTable('.tablaCarguesPendientes')) {
        tablaCargues.DataTable({ "language": { /* Tus traducciones */ } });
    } else {
        tablaCargues.DataTable().clear().rows.add($(tablaCargues.find('tbody').children())).draw();
    }
}
        });
    }

    // --- LISTENERS PARA TODOS LOS BOTONES DE ACCIONES ---

    // 2. VER MANIFIESTO
    tablaCargues.on('click', '.btnVerManifiesto', function() {
        let idTransferencia = $(this).attr('idTransferencia');
        $("#listaProductosManifiesto").empty();
        $.ajax({
            url: apiUrl + "obtener_items.php?id_transferencia=" + idTransferencia,
            dataType: "json",
            success: function(items) {
                items.forEach(function(item) {
                    $("#listaProductosManifiesto").append(`<tr><td>${item.descripcion}</td><td>${item.cantidad_enviada}</td></tr>`);
                });
                $('#modalVerManifiesto').modal('show');
            }
        });
    });
    
    // 3. CONFIRMAR CARGUE
        tablaCargues.on('click', '.btnConfirmarCargue', function() {
        var idTransferencia = $(this).attr("idTransferencia");
        swal({ title: '¿Confirmar Despacho?', text: "Esta acción descontará el stock y pondrá la mercancía 'En Tránsito'.", type: 'warning', showCancelButton: true, confirmButtonText: 'Sí, ¡Confirmar!' })
        .then((result) => {
            if (result.value) {
                // Llamada LOCAL para descontar stock
                $.ajax({
                    url: "ajax/transferencias.ajax.php",
                    method: "POST",
                    data: { "accion": "descontarStock", "idTransferencia": idTransferencia },
                    success: function(respuestaLocal) {
                        if (respuestaLocal === "ok") {
                            // Llamada CENTRAL para actualizar estado
                            $.ajax({
                                url: apiUrl + "confirmar_cargue.php",
                                method: "POST",
                                data: { "id_transferencia": idTransferencia, "usuario_confirmador": nombreUsuario },
                                success: function() {
                                    swal({ type: 'success', title: '¡Despachado!', text: 'La transferencia está en tránsito.' }).then(() => { window.location.reload(); });
                                }
                            });
                        } else {
                            swal('Error', 'Hubo un problema al descontar el stock local.', 'error');
                        }
                    }
                });
            }
        });
    });

    // 4. CANCELAR CARGUE (CÓDIGO COMPLETO)
    tablaCargues.on('click', '.btnCancelarCargue', function(){
        let idTransferencia = $(this).attr('idTransferencia');
        swal({
            title: '¿Estás seguro de cancelar?',
            text: "La transferencia será marcada como 'cancelada' y no podrá procesarse.",
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#f39c12',
            cancelButtonText: 'No',
            confirmButtonText: 'Sí, cancelar'
        }).then(result => {
            if(result.value){
                $.ajax({
                    url: apiUrl + "cancelar_transferencia.php",
                    method: 'POST',
                    data: { "id_transferencia": idTransferencia },
                    dataType: 'json',
                    success: function(response){
                        if(response.status === 'ok'){
                            swal({
                                type: 'success',
                                title: '¡Cancelada!',
                                text: 'La transferencia ha sido cancelada.'
                            }).then(() => {
                                window.location.reload();
                            });
                        }
                    }
                });
            }
        });
    });

    // 5. ELIMINAR TRANSFERENCIA (CÓDIGO COMPLETO)
    tablaCargues.on('click', '.btnEliminarTransferencia', function(){
        let idTransferencia = $(this).attr('idTransferencia');
        swal({
            title: '¿Estás seguro de ELIMINAR?',
            text: "Esta acción borrará permanentemente el registro de la transferencia, su manifiesto y sus logs. ¡No se puede deshacer!",
            type: 'error',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonText: 'No',
            confirmButtonText: 'Sí, eliminar'
        }).then(result => {
            if(result.value){
                $.ajax({
                    url: apiUrl + "eliminar_transferencia.php",
                    method: 'POST',
                    data: { "id_transferencia": idTransferencia },
                    dataType: 'json',
                    success: function(response){
                        if(response.status === 'ok'){
                            swal({
                                type: 'success',
                                title: '¡Eliminada!',
                                text: 'La transferencia ha sido eliminada permanentemente.'
                            }).then(() => {
                                window.location.reload();
                            });
                        }
                    }
                });
            }
        });
    });
});