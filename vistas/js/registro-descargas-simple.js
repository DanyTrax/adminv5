/*=============================================
REGISTRO DE DESCARGAS SIMPLE - JAVASCRIPT
=============================================*/

$(document).ready(function() {
    // Inicializar DataTable
    inicializarDataTable();
    
    // Cargar estadísticas
    cargarEstadisticas();
    
    // Event listeners
    $("#btnFiltrar").click(function() {
        aplicarFiltros();
    });
    
    $("#btnLimpiar").click(function() {
        limpiarFiltros();
    });
});

/*=============================================
INICIALIZAR DATATABLE
=============================================*/
function inicializarDataTable() {
    $("#tablaRegistroDescargas").DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "ajax/datatable-registro-descargas-simple.ajax.php",
            "type": "POST",
            "data": function(d) {
                d.fecha_desde = $("#fechaDesde").val();
                d.fecha_hasta = $("#fechaHasta").val();
                d.codigo_producto = $("#codigoProducto").val();
            }
        },
        "columns": [
            {"data": 0},
            {"data": 1},
            {"data": 2},
            {"data": 3},
            {"data": 4},
            {"data": 5},
            {"data": 6},
            {"data": 7},
            {"data": 8}
        ],
        "language": {
            "sProcessing": "Procesando...",
            "sLengthMenu": "Mostrar _MENU_ registros",
            "sZeroRecords": "No se encontraron resultados",
            "sEmptyTable": "Ningún dato disponible en esta tabla",
            "sInfo": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_",
            "sInfoEmpty": "Mostrando registros del 0 al 0 de un total de 0",
            "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
            "sInfoPostFix": "",
            "sSearch": "Buscar:",
            "sUrl": "",
            "sInfoThousands": ",",
            "sLoadingRecords": "Cargando...",
            "oPaginate": {
                "sFirst": "Primero",
                "sLast": "Último",
                "sNext": "Siguiente",
                "sPrevious": "Anterior"
            },
            "oAria": {
                "sSortAscending": ": Activar para ordenar la columna de manera ascendente",
                "sSortDescending": ": Activar para ordenar la columna de manera descendente"
            }
        },
        "responsive": true,
        "autoWidth": false,
        "order": [[8, "desc"]]
    });
}

/*=============================================
CARGAR ESTADÍSTICAS
=============================================*/
function cargarEstadisticas() {
    $.ajax({
        url: "ajax/registro-descargas-simple.ajax.php",
        method: "POST",
        data: {
            accion: "obtener_estadisticas",
            fecha_desde: $("#fechaDesde").val(),
            fecha_hasta: $("#fechaHasta").val()
        },
        dataType: "json",
        success: function(respuesta) {
            if(respuesta) {
                $("#totalDescargas").text(respuesta.total_descargas || 0);
                $("#totalCantidad").text(respuesta.total_cantidad || 0);
                $("#productosUnicos").text(respuesta.productos_unicos || 0);
                $("#usuariosUnicos").text(respuesta.usuarios_unicos || 0);
            }
        },
        error: function() {
            console.error("Error al cargar estadísticas");
        }
    });
}

/*=============================================
APLICAR FILTROS
=============================================*/
function aplicarFiltros() {
    // Recargar DataTable con filtros
    $("#tablaRegistroDescargas").DataTable().ajax.reload();
    
    // Recargar estadísticas con filtros
    cargarEstadisticas();
}

/*=============================================
LIMPIAR FILTROS
=============================================*/
function limpiarFiltros() {
    $("#fechaDesde").val("");
    $("#fechaHasta").val("");
    $("#codigoProducto").val("");
    
    // Recargar DataTable sin filtros
    $("#tablaRegistroDescargas").DataTable().ajax.reload();
    
    // Recargar estadísticas sin filtros
    cargarEstadisticas();
}
