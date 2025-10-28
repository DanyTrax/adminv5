/*=============================================
CARGAR ESTADÍSTICAS PARA TRANSPORTADOR
=============================================*/
function cargarEstadisticasTransportador() {

    // Solo cargar si estamos en la página de despachos y el usuario es transportador
    if (typeof $ !== 'undefined' && $('#contadorPendientes').length > 0) {

        $.ajax({
            url: "ajax/despachos.ajax.php",
            method: "POST",
            data: {
                accion: "obtener_estadisticas_transportador"
            },
            dataType: "json",
            success: function(respuesta) {

                if (respuesta.success) {
                    // Actualizar contadores
                    $('#contadorPendientes').text(respuesta.estadisticas.pendientes || 0);
                    $('#contadorEnTransito').text(respuesta.estadisticas.en_transito || 0);
                    $('#contadorEntregados').text(respuesta.estadisticas.entregados || 0);
                    $('#contadorCancelados').text(respuesta.estadisticas.cancelados || 0);
} else {
}
            },
            error: function(xhr, status, error) {
}
        });
    }
}

// Cargar estadísticas al cargar la página
$(document).ready(function() {
    cargarEstadisticasTransportador();
});
