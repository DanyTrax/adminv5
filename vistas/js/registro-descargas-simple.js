/*=============================================
REGISTRO DE DESCARGAS SIMPLE - JAVASCRIPT ULTRA SIMPLE
=============================================*/

// Variable global para controlar inicialización
var dataTableInicializado = false;

$(document).ready(function() {
    console.log("✅ Módulo Registro de Descargas Simple cargado");
    
    // Cargar estadísticas
    cargarEstadisticas();
    
    // Inicializar DataTable solo una vez
    if (!dataTableInicializado) {
        setTimeout(function() {
            inicializarDataTable();
            configurarFiltros();
        }, 500); // Aumentar delay
    }
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

function inicializarDataTable() {
    console.log("🔄 Inicializando DataTable...");
    
    // Verificar si ya se inicializó
    if (dataTableInicializado) {
        console.log("⚠️ DataTable ya fue inicializado, saltando...");
        return;
    }
    
    // Verificar si el elemento existe
    if ($("#tabla-registro-descargas").length === 0) {
        console.log("❌ Elemento #tabla-registro-descargas no encontrado");
        return;
    }
    
    // Verificar si ya existe DataTable
    if ($.fn.DataTable.isDataTable("#tabla-registro-descargas")) {
        console.log("🔄 DataTable ya existe, saltando inicialización...");
        return;
    }
    
    console.log("🔄 Creando DataTable nuevo...");
    
    $("#tabla-registro-descargas").DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "ajax/datatable-registro-descargas-simple.ajax.php",
            "type": "POST",
            "data": function(d) {
                d.producto = $("#filtro-producto").val();
                d.usuario = $("#filtro-usuario").val();
                d.fecha_desde = $("#filtro-fecha-desde").val();
                d.fecha_hasta = $("#filtro-fecha-hasta").val();
            }
        },
        "columns": [
            { "data": "id" },
            { "data": "fecha_hora" },
            { "data": "codigo_producto" },
            { "data": "descripcion_producto" },
            { "data": "cantidad_descargada" },
            { "data": "usuario_nombre" },
            { "data": "transportador_nombre" },
            { "data": "sucursal_nombre" },
            { "data": "numero_despacho" },
            { "data": "observaciones" }
        ],
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
        },
        "pageLength": 25,
        "order": [[0, "desc"]],
        "responsive": true,
        "autoWidth": false,
        "error": function(xhr, error, thrown) {
            console.error("❌ Error en DataTable:", error, thrown);
            console.error("❌ Respuesta del servidor:", xhr.responseText);
        }
    });
    
    // Marcar como inicializado
    dataTableInicializado = true;
    console.log("✅ DataTable inicializado");
}

function configurarFiltros() {
    // Botón filtrar
    $("#btn-filtrar").click(function() {
        $("#tabla-registro-descargas").DataTable().ajax.reload();
    });
    
    // Botón limpiar
    $("#btn-limpiar").click(function() {
        $("#filtro-producto").val("");
        $("#filtro-usuario").val("");
        $("#filtro-fecha-desde").val("");
        $("#filtro-fecha-hasta").val("");
        $("#tabla-registro-descargas").DataTable().ajax.reload();
    });
    
    // Botón exportar
    $("#btn-exportar").click(function() {
        // TODO: Implementar exportación
        alert("Función de exportación pendiente");
    });
}