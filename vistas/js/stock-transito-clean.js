/*=============================================
STOCK EN TRÁNSITO - JAVASCRIPT LIMPIO
=============================================*/

// Variable global para almacenar el stock seleccionado
var stockSeleccionado = null;

/*=============================================
CONFIGURAR EVENTOS
=============================================*/
function configurarEventosStock() {
// Event listener para búsqueda con debounce
    var timeoutBusqueda;
    $("#buscarProductos").on("input", function() {
        clearTimeout(timeoutBusqueda);
        var termino = $(this).val();

        timeoutBusqueda = setTimeout(function() {
            if(termino.length >= 2 || termino.length === 0) {
                buscarProductos(termino);
            }
        }, 300);
    });

    // Event listener para filtro de transportador
    $("#filtroTransportador").on("change", function() {
        var transportadorId = $(this).val();
        buscarProductos($("#buscarProductos").val(), transportadorId);
    });
}

/*=============================================
BUSCAR PRODUCTOS
=============================================*/
function buscarProductos(termino = "", transportadorId = null) {
$.ajax({
        url: "ajax/buscar-stock-transito.ajax.php",
        method: "POST",
        data: {
            buscarProductos: true,
            termino: termino,
            transportador: transportadorId
        },
        dataType: "json",
        success: function(respuesta) {
            if(respuesta.success) {
                $("#listaProductos").html(respuesta.html);
} else {
$("#listaProductos").html("<div class='alert alert-warning'>No se encontraron productos</div>");
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
$("#listaProductos").html("<div class='alert alert-danger'>Error al cargar productos</div>");
        }
    });
}

/*=============================================
INICIALIZAR
=============================================*/
$(document).ready(function() {
configurarEventosStock();

    // Cargar productos iniciales
    buscarProductos();
});
