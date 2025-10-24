/*=============================================
REGISTRO DE DESCARGAS SIMPLE - JAVASCRIPT ULTRA SIMPLE
=============================================*/

// Variable global para controlar inicialización
var dataTableInicializado = false;

$(document).ready(function() {
    console.log("✅ Módulo Registro de Descargas Simple cargado");
    
    // Cargar estadísticas
    cargarEstadisticas();
    
    // Inicializar DataTable con delay para asegurar DOM listo
    setTimeout(function() {
        inicializarDataTable();
        configurarFiltros();
    }, 100);
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
    
    // Verificar si ya existe y destruirlo completamente
    if ($.fn.DataTable.isDataTable("#tabla-registro-descargas")) {
        console.log("🔄 DataTable ya existe, destruyendo completamente...");
        try {
            var table = $("#tabla-registro-descargas").DataTable();
            table.destroy(true); // true = remover del DOM
            console.log("✅ DataTable destruido completamente");
        } catch (e) {
            console.log("⚠️ Error al destruir DataTable:", e.message);
            // Limpiar completamente el DOM
            $("#tabla-registro-descargas").removeClass("dataTable");
            $("#tabla-registro-descargas").find(".dataTables_wrapper").remove();
            $("#tabla-registro-descargas").unwrap();
        }
    }
    
    // Limpiar completamente el contenido
    $("#tabla-registro-descargas").empty();
    
    // Recrear la estructura básica de la tabla
    $("#tabla-registro-descargas").html(`
        <thead>
            <tr>
                <th>ID</th>
                <th>Fecha y Hora</th>
                <th>Código Producto</th>
                <th>Descripción</th>
                <th>Cantidad</th>
                <th>Usuario</th>
                <th>Transportador</th>
                <th>Sucursal</th>
                <th>Despacho</th>
                <th>Observaciones</th>
            </tr>
        </thead>
        <tbody>
            <!-- Los datos se cargarán via AJAX -->
        </tbody>
    `);
    
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