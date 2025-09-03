$(document).ready(function() {

    /*=============================================
    CARGAR LA TABLA DINÁMICA DE SUCURSALES
    =============================================*/
    var tablaSucursales = $('.tablaSucursales').DataTable({
        "ajax": {
            "url": "ajax/sucursales.ajax.php",
            "type": "POST",
            "data": { "accion": "datatable" },
            "dataSrc": function(json) {
                if (json.error) {
                    console.error("Error del servidor:", json.error);
                    return [];
                }
                return json.data;
            }
        },
        "deferRender": true,
        "retrieve": true,
        "processing": true,
        "responsive": true,
        "language": { "url": "//cdn.datatables.net/plug-ins/1.10.20/i18n/Spanish.json" }
    });

    /*=============================================
    LÓGICA PARA LOS MODALES DE AGREGAR/EDITAR
    =============================================*/
    // (Aquí va todo tu código JS para previsualizar logos, generar códigos,
    //  probar conexiones, llenar el modal de editar y validar formularios).
    // Esta parte de tu archivo original no necesita grandes cambios,
    // ya que es la funcionalidad principal que quieres conservar.

    /*=============================================
    ELIMINAR SUCURSAL
    =============================================*/
    $(".tablaSucursales").on("click", ".btnEliminarSucursal", function() {
        var idSucursal = $(this).attr("idSucursal");
        // ... (Tu código con SweetAlert para confirmar la eliminación se mantiene igual)
    });
});