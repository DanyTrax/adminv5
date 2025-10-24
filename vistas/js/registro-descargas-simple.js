/*=============================================
REGISTRO DE DESCARGAS SIMPLE - JAVASCRIPT ULTRA SIMPLE
=============================================*/

$(document).ready(function() {
    console.log("✅ Módulo Registro de Descargas Simple cargado");
    
    // Cargar estadísticas
    cargarEstadisticas();
});

function cargarEstadisticas() {
    $.ajax({
        url: "ajax/registro-descargas-simple.ajax.php",
        method: "POST",
        data: {
            accion: "obtener_estadisticas"
        },
        dataType: "json",
        success: function(respuesta) {
            console.log("✅ Estadísticas cargadas:", respuesta);
        },
        error: function() {
            console.error("❌ Error al cargar estadísticas");
        }
    });
}