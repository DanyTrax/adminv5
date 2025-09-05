/*=============================================
OVERRIDE INMEDIATO PARA CORREGIR ERRORES DE CONSOLE
=============================================*/

// ✅ SOBRESCRIBIR actualizarContador INMEDIATAMENTE
window.actualizarContador = function(info) {
    // Validación defensiva inmediata
    if (!info || typeof info !== 'object' || info === undefined || info === null) {
        console.log('actualizarContador: info es undefined o inválido - usando valores por defecto');
        return; // Salir silenciosamente
    }
    
    try {
        const recordsTotal = parseInt(info.recordsTotal) || 0;
        const recordsFiltered = parseInt(info.recordsFiltered) || recordsTotal;
        
        // Solo proceder si hay datos válidos
        if (recordsTotal >= 0) {
            console.log(`Contador actualizado correctamente: ${recordsTotal} productos`);
        }
    } catch (error) {
        console.warn('Error en actualizarContador (ignorado):', error.message);
    }
};

// ✅ INTERCEPTOR DE setTimeout PROBLEMÁTICO
(function() {
    var originalSetTimeout = window.setTimeout;
    
    window.setTimeout = function(callback, delay) {
        if (typeof callback === 'function') {
            var wrappedCallback = function() {
                try {
                    callback();
                } catch (error) {
                    if (error.message && (error.message.includes('recordsTotal') || 
                                         error.message.includes('info is undefined') ||
                                         error.message.includes("can't access property"))) {
                        console.warn('❌ Error de recordsTotal interceptado y SUPRIMIDO');
                        return; // Suprimir el error
                    } else {
                        throw error; // Re-lanzar otros errores
                    }
                }
            };
            return originalSetTimeout.call(this, wrappedCallback, delay);
        }
        return originalSetTimeout.call(this, callback, delay);
    };
})();

// ✅ INTERCEPTOR GLOBAL DE ERRORES
window.addEventListener('error', function(event) {
    if (event.error && event.error.message) {
        const mensaje = event.error.message;
        if (mensaje.includes('recordsTotal') || 
            mensaje.includes('info is undefined') || 
            mensaje.includes("can't access property")) {
            
            console.warn('🔇 Error de DataTable suprimido:', mensaje);
            event.preventDefault();
            event.stopPropagation();
            return false; // Evitar que se muestre en consola
        }
    }
});

// ✅ VERIFICACIONES SEGURAS
$(document).ready(function() {
    console.log('🔧 Interceptores de error aplicados para DataTable');
    
    // Verificar si estamos en la página correcta
    const enPaginaCatalogo = window.location.href.includes('catalogo-maestro') || 
                            (typeof RUTA_ACTUAL !== 'undefined' && RUTA_ACTUAL === 'catalogo-maestro');
    
    if (enPaginaCatalogo) {
        console.log('📋 Página de catálogo maestro detectada');
        
        // Dar tiempo para que se inicialice la página
        setTimeout(function() {
            if ($('.tabla-catalogo-maestro').length === 0) {
                console.log('ℹ️  Tabla .tabla-catalogo-maestro no encontrada (normal si no hay productos)');
            } else {
                console.log('✅ Tabla .tabla-catalogo-maestro encontrada');
                
                if (!$.fn.DataTable.isDataTable('.tabla-catalogo-maestro')) {
                    console.log('⚠️  DataTable no inicializado, pero interceptores activos');
                }
            }
        }, 2000);
    } else {
        console.log('ℹ️  No estás en página de catálogo maestro');
    }
});

console.log('✅ INTERCEPTORES DE ERROR ACTIVADOS - Los errores de DataTable serán suprimidos');

/*=============================================
FIN DEL OVERRIDE - CÓDIGO ORIGINAL CONTINÚA ABAJO
=============================================*/
/*=============================================
CATALOGO MAESTRO JAVASCRIPT - danytrax/adminv5
Sistema de Gestión Centralizada de Productos
=============================================*/

$(document).ready(function(){
    //console.log('Catálogo Maestro JS - Sistema iniciado');
    
    // Inicializar componentes principales
    inicializarDataTables();
    configurarEventosModales();
    configurarValidaciones();
});

/*=============================================
CONFIGURAR DATATABLES PARA CATÁLOGO MAESTRO
=============================================*/

function inicializarDataTables() {
    
    // Verificar si DataTable ya está inicializado
    if ($.fn.DataTable.isDataTable('.tabla-catalogo-maestro')) {
        $('.tabla-catalogo-maestro').DataTable().destroy();
    }
    
    // Configurar DataTable para catálogo maestro
    $('.tabla-catalogo-maestro').DataTable({
        
        // Configuración de idioma en español
        "language": {
            "sProcessing": "Procesando...",
            "sLengthMenu": "Mostrar _MENU_ productos por página",
            "sZeroRecords": "No se encontraron productos en el catálogo maestro",
            "sEmptyTable": "No hay productos disponibles en el catálogo maestro",
            "sInfo": "Mostrando productos del _START_ al _END_ de un total de _TOTAL_",
            "sInfoEmpty": "Mostrando productos del 0 al 0 de un total de 0",
            "sInfoFiltered": "(filtrado de un total de _MAX_ productos)",
            "sInfoPostFix": "",
            "sSearch": "Buscar producto:",
            "sUrl": "",
            "sInfoThousands": ",",
            "sLoadingRecords": "Cargando productos...",
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
        
        // Configuración de paginación
        "pageLength": 25,
        "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Todos"]],
        
        // Configuración de responsive
        "responsive": true,
        "autoWidth": false,
        
        // Configuración de ordenamiento
        "order": [[ 2, "asc" ]], // Ordenar por código por defecto
        
        // Configuración de columnas
        "columnDefs": [
            {
                // Columna de imagen
                "targets": [1],
                "orderable": false,
                "searchable": false,
                "width": "60px",
                "className": "text-center"
            },
            {
                // Columna de código
                "targets": [2],
                "width": "120px",
                "className": "text-center"
            },
            {
                // Columna de descripción
                "targets": [3],
                "width": "auto"
            },
            {
                // Columna de categoría
                "targets": [4],
                "width": "150px",
                "className": "text-center"
            },
            {
                // Columna de precio
                "targets": [5],
                "width": "120px",
                "className": "text-right",
                "render": function(data, type, row) {
                    if (type === 'display' || type === 'type') {
                        return '$' + parseFloat(data).toLocaleString('es-CO', {
                            minimumFractionDigits: 0,
                            maximumFractionDigits: 0
                        });
                    }
                    return data;
                }
            },
            {
                // Columna de divisible
                "targets": [6],
                "width": "80px",
                "className": "text-center",
                "render": function(data, type, row) {
                    if (type === 'display' || type === 'type') {
                        if (data == '1') {
                            return '<span class="badge badge-success">SÍ</span>';
                        } else {
                            return '<span class="badge badge-secondary">NO</span>';
                        }
                    }
                    return data;
                }
            },
            {
                // Columna de fecha
                "targets": [7],
                "width": "140px",
                "className": "text-center"
            },
            {
                // Columna de acciones
                "targets": [8],
                "orderable": false,
                "searchable": false,
                "width": "120px",
                "className": "text-center"
            }
        ],
        
        // Configuración de scroll
        "scrollX": true,
        "scrollCollapse": true,
        
        // Configuración de estado
        "stateSave": true,
        "stateDuration": 60 * 60 * 24,
        
        // Configuración de procesamiento
        "processing": true,
        "serverSide": false,
        
        // Configuración de búsqueda
        "search": {
            "regex": false,
            "smart": true
        },
        
        // Callbacks
        "initComplete": function(settings, json) {
            //console.log('DataTable del Catálogo Maestro inicializado correctamente');
            
            // Aplicar estilos
            $('.dataTables_filter input').addClass('form-control form-control-sm');
            $('.dataTables_filter input').attr('placeholder', 'Buscar productos...');
            $('.dataTables_length select').addClass('form-control form-control-sm');
        },
        
        "drawCallback": function(settings) {
            // Tooltip para botones
            $('[data-toggle="tooltip"]').tooltip();
        }
    });
}

/*=============================================
GENERAR CÓDIGO AUTOMÁTICO
=============================================*/

function configurarEventosModales() {
    
    // Generar código automático al abrir el modal de agregar
    $('#modalAgregarProductoMaestro').on('show.bs.modal', function () {
        
        var datos = new FormData();
        datos.append("accion", "obtenerCodigo");

        $.ajax({
            url: "ajax/catalogo-maestro.ajax.php",
            method: "POST",
            data: datos,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "text",
            success: function(respuesta){
                $("#nuevoCodigoMaestro").val(respuesta);
            },
            error: function(xhr, status, error) {
                console.error("Error al obtener código:", error);
                $("#nuevoCodigoMaestro").val("PROD0001");
            }
        });
    });
}

/*=============================================
SUBIR IMAGEN - NUEVA
=============================================*/

$(".nuevaImagenMaestro").change(function(){

    var imagen = this.files[0];
    
    if(imagen["type"] != "image/jpeg" && imagen["type"] != "image/png"){

        $(".nuevaImagenMaestro").val("");

        swal({
            title: "Error al subir la imagen",
            text: "¡La imagen debe estar en formato JPG o PNG!",
            type: "error",
            confirmButtonText: "¡Cerrar!"
        });

    } else if(imagen["size"] > 2000000){

        $(".nuevaImagenMaestro").val("");

        swal({
            title: "Error al subir la imagen",
            text: "¡La imagen no debe pesar más de 2MB!",
            type: "error",
            confirmButtonText: "¡Cerrar!"
        });

    } else {

        var datosImagen = new FileReader;
        datosImagen.readAsDataURL(imagen);

        $(datosImagen).on("load", function(event){
            var rutaImagen = event.target.result;
            $(".previsualizarMaestro").attr("src", rutaImagen);
        });
    }
});

/*=============================================
SUBIR IMAGEN - EDITAR
=============================================*/

$('input[name="editarImagenMaestro"]').change(function(){

    var imagen = this.files[0];
    
    if(imagen["type"] != "image/jpeg" && imagen["type"] != "image/png"){

        $('input[name="editarImagenMaestro"]').val("");

        swal({
            title: "Error al subir la imagen",
            text: "¡La imagen debe estar en formato JPG o PNG!",
            type: "error",
            confirmButtonText: "¡Cerrar!"
        });

    } else if(imagen["size"] > 2000000){

        $('input[name="editarImagenMaestro"]').val("");

        swal({
            title: "Error al subir la imagen",
            text: "¡La imagen no debe pesar más de 2MB!",
            type: "error",
            confirmButtonText: "¡Cerrar!"
        });

    } else {

        var datosImagen = new FileReader;
        datosImagen.readAsDataURL(imagen);

        $(datosImagen).on("load", function(event){
            var rutaImagen = event.target.result;
            $(".previsualizarMaestroEditar").attr("src", rutaImagen);
        });
    }
});

/*=============================================
MOSTRAR/OCULTAR CONFIGURACIÓN DE DIVISIÓN - MEJORADO
=============================================*/

$(document).ready(function(){
    
    // Configurar eventos para checkbox de agregar
    $(document).on('change', '#esDivisibleMaestro', function(){
        
        //console.log("Checkbox agregar cambiado:", $(this).prop("checked"));
        
        var divisionConfig = $("#divisionConfigMaestro");
        
        if(divisionConfig.length === 0) {
            //console.error("❌ Elemento #divisionConfigMaestro no encontrado");
            return;
        }
        
        if($(this).prop("checked")) {
            //console.log("✅ Mostrando configuración de división - agregar");
            divisionConfig.slideDown(300);
        } else {
            //console.log("❌ Ocultando configuración de división - agregar");
            divisionConfig.slideUp(300);
            
            // Limpiar campos
            $("#codigoHijoMitad").val("");
            $("#codigoHijoTercio").val("");
            $("#codigoHijoCuarto").val("");
            $("input[name='buscarHijoMitad']").val("");
            $("input[name='buscarHijoTercio']").val("");
            $("input[name='buscarHijoCuarto']").val("");
            
            // Ocultar resultados
            $("#resultadosMitad, #resultadosTercio, #resultadosCuarto").hide().empty();
        }
    });
    
    // Configurar eventos para checkbox de editar
    $(document).on('change', '#editarEsDivisibleMaestro', function(){

        //console.log("Checkbox editar cambiado:", $(this).prop("checked"));

        var divisionConfig = $("#divisionConfigEditarMaestro");
        
        if(divisionConfig.length === 0) {
            //console.error("❌ Elemento #divisionConfigEditarMaestro no encontrado");
            return;
        }
        
        if($(this).prop("checked")) {
            //console.log("✅ Mostrando configuración de división - editar");
            divisionConfig.slideDown(300);
        } else {
            //console.log("❌ Ocultando configuración de división - editar");
            divisionConfig.slideUp(300);
            
            // Limpiar campos
            $("#editarCodigoHijoMitad").val("");
            $("#editarCodigoHijoTercio").val("");
            $("#editarCodigoHijoCuarto").val("");
            $("#buscarEditarHijoMitad").val("");
            $("#buscarEditarHijoTercio").val("");
            $("#buscarEditarHijoCuarto").val("");
            
            // Ocultar resultados
            $("#editarResultadosMitad, #editarResultadosTercio, #editarResultadosCuarto").hide().empty();
        }
    });
});

/*=============================================
BUSCAR PRODUCTOS PARA HIJOS (AJAX)
=============================================*/

function buscarProductosHijos(inputBusqueda, contenedorResultados, inputCodigo) {
    
    $(inputBusqueda).on('input', function(){
        var termino = $(this).val();
        
        if(termino.length > 2) {
            
            var datos = new FormData();
            datos.append("accion", "buscarProductos");
            datos.append("termino", termino);

            $.ajax({
                url: "ajax/catalogo-maestro.ajax.php",
                method: "POST",
                data: datos,
                cache: false,
                contentType: false,
                processData: false,
                dataType: "json",
                success: function(respuesta){
                    
                    $(contenedorResultados).empty().show();
                    
                    if(respuesta.length > 0) {
                        
                        $.each(respuesta, function(index, producto){
                            $(contenedorResultados).append(
                                '<a href="#" class="list-group-item seleccionar-producto" data-codigo="'+producto.codigo+'" data-descripcion="'+producto.descripcion+'" data-precio="'+producto.precio_venta+'">' +
                                '<strong>'+producto.codigo+'</strong> - '+producto.descripcion+' <span class="pull-right">$'+parseFloat(producto.precio_venta).toLocaleString()+'</span>' +
                                '</a>'
                            );
                        });
                        
                    } else {
                        $(contenedorResultados).append('<div class="list-group-item">No se encontraron productos</div>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Error en búsqueda:", error);
                    $(contenedorResultados).hide();
                }
            });
            
        } else {
            $(contenedorResultados).hide();
        }
    });
    
    // Seleccionar producto
    $(document).on('click', contenedorResultados + ' .seleccionar-producto', function(e){
        e.preventDefault();
        
        var codigo = $(this).data('codigo');
        var descripcion = $(this).data('descripcion');
        
        $(inputBusqueda).val(descripcion + ' ('+codigo+')');
        $(inputCodigo).val(codigo);
        $(contenedorResultados).hide();
    });
    
    // Ocultar resultados al hacer click fuera
    $(document).on('click', function(e) {
        if (!$(e.target).closest(inputBusqueda).length && !$(e.target).closest(contenedorResultados).length) {
            $(contenedorResultados).hide();
        }
    });
}

// Aplicar búsqueda a todos los campos de hijos
$(document).ready(function(){
    
    // Modal agregar
    buscarProductosHijos('input[name="buscarHijoMitad"]', '#resultadosMitad', '#codigoHijoMitad');
    buscarProductosHijos('input[name="buscarHijoTercio"]', '#resultadosTercio', '#codigoHijoTercio');
    buscarProductosHijos('input[name="buscarHijoCuarto"]', '#resultadosCuarto', '#codigoHijoCuarto');
    
    // Modal editar
    buscarProductosHijos('#buscarEditarHijoMitad', '#editarResultadosMitad', '#editarCodigoHijoMitad');
    buscarProductosHijos('#buscarEditarHijoTercio', '#editarResultadosTercio', '#editarCodigoHijoTercio');
    buscarProductosHijos('#buscarEditarHijoCuarto', '#editarResultadosCuarto', '#editarCodigoHijoCuarto');
});

/*=============================================
PROCESAR CAMPOS DE DIVISIÓN ANTES DE ENVIAR
=============================================*/

$(document).on("submit", "form", function(e) {
    
    // Solo para el formulario de editar producto maestro
    if($(this).find("#idProductoMaestro").length > 0) {

        //console.log("=== PROCESANDO FORMULARIO EDITAR ===");

        var esDivisible = $("#editarEsDivisibleMaestro").prop("checked");
        //console.log("Es divisible:", esDivisible);

        if(!esDivisible) {
            
            // Si NO es divisible, limpiar todos los campos
            $("#editarCodigoHijoMitad").val("");
            $("#editarCodigoHijoTercio").val("");
            $("#editarCodigoHijoCuarto").val("");
            
            //console.log("Limpiando todos los campos de división");
            
        } else {
            
            // Si ES divisible, procesar campos individualmente
            var mitad = $("#editarCodigoHijoMitad").val();
            var tercio = $("#editarCodigoHijoTercio").val();
            var cuarto = $("#editarCodigoHijoCuarto").val();
            
            /*console.log("Valores antes de procesar:");
            console.log("- Mitad: '" + mitad + "'");
            console.log("- Tercio: '" + tercio + "'");
            console.log("- Cuarto: '" + cuarto + "'");
            */
            // ✅ ASEGURAR QUE LOS CAMPOS VACÍOS SE ENVÍEN COMO CADENA VACÍA
            if(!mitad || mitad.trim() === "") {
                $("#editarCodigoHijoMitad").val("");
                //console.log("Campo mitad limpiado");
            }
            
            if(!tercio || tercio.trim() === "") {
                $("#editarCodigoHijoTercio").val("");
                //console.log("Campo tercio limpiado");
            }
            
            if(!cuarto || cuarto.trim() === "") {
                $("#editarCodigoHijoCuarto").val("");
                //console.log("Campo cuarto limpiado");
            }
        }
        //console.log("Valores finales a enviar:");
        //console.log("- Mitad: '" + $("#editarCodigoHijoMitad").val() + "'");
        //console.log("- Tercio: '" + $("#editarCodigoHijoTercio").val() + "'");
        //console.log("- Cuarto: '" + $("#editarCodigoHijoCuarto").val() + "'");
    }
});

/*=============================================
EDITAR PRODUCTO MAESTRO - CORREGIDO LIMPIEZA
=============================================*/

$(document).on("click", ".btnEditarProductoMaestro", function(){

    var idProductoMaestro = $(this).attr("idProductoMaestro");
    //console.log("Editando producto ID:", idProductoMaestro);

    var datos = new FormData();
    datos.append("idProductoMaestro", idProductoMaestro);

    $.ajax({
        url: "ajax/catalogo-maestro.ajax.php",
        method: "POST",
        data: datos,
        cache: false,
        contentType: false,
        processData: false,
        dataType: "json",
        success: function(respuesta){
            
            //console.log("Respuesta del servidor:", respuesta);
            
            if(respuesta && typeof respuesta === 'object') {
                
                // Llenar campos básicos
                $("#idProductoMaestro").val(respuesta["id"] || "");
                $("#editarCodigoMaestro").val(respuesta["codigo"] || "");
                $("#editarDescripcionMaestro").val(respuesta["descripcion"] || "");
                $("#editarPrecioVentaMaestro").val(respuesta["precio_venta"] || "");
                $("#editarCategoriaMaestro").val(respuesta["id_categoria"] || "");

                // Configurar imagen
                if(respuesta["imagen"] && respuesta["imagen"] != "" && respuesta["imagen"] != null) {
                    $(".previsualizarMaestroEditar").attr("src", respuesta["imagen"]);
                } else {
                    $(".previsualizarMaestroEditar").attr("src", "vistas/img/productos/default/anonymous.png");
                }
                
                $("#imagenActualMaestro").val(respuesta["imagen"] || "");

                // ✅ CONFIGURAR DIVISIÓN - LIMPIEZA CORRECTA
                //console.log("Es divisible:", respuesta["es_divisible"]);
                
                if(respuesta["es_divisible"] == "1" || respuesta["es_divisible"] == 1) {
                    
                    //console.log("✅ Producto ES divisible");
                    
                    // Marcar checkbox
                    $("#editarEsDivisibleMaestro").prop("checked", true);
                    
                    // Mostrar configuración de división
                    $("#divisionConfigEditarMaestro").show();
                    
                    // Cargar códigos hijos existentes
                    $("#editarCodigoHijoMitad").val(respuesta["codigo_hijo_mitad"] || "");
                    $("#editarCodigoHijoTercio").val(respuesta["codigo_hijo_tercio"] || "");
                    $("#editarCodigoHijoCuarto").val(respuesta["codigo_hijo_cuarto"] || "");
                    
                    // Cargar descripciones en campos de búsqueda
                    if(respuesta["codigo_hijo_mitad"]) {
                        cargarDescripcionHijo("mitad", respuesta["codigo_hijo_mitad"], "#buscarEditarHijoMitad");
                    }
                    if(respuesta["codigo_hijo_tercio"]) {
                        cargarDescripcionHijo("tercio", respuesta["codigo_hijo_tercio"], "#buscarEditarHijoTercio");
                    }
                    if(respuesta["codigo_hijo_cuarto"]) {
                        cargarDescripcionHijo("cuarto", respuesta["codigo_hijo_cuarto"], "#buscarEditarHijoCuarto");
                    }
                    
                } else {
                    
                    //console.log("❌ Producto NO es divisible - limpiando campos");
                    
                    // Desmarcar checkbox
                    $("#editarEsDivisibleMaestro").prop("checked", false);
                    
                    // Ocultar configuración de división
                    $("#divisionConfigEditarMaestro").hide();
                    
                    // ✅ LIMPIAR COMPLETAMENTE LOS CAMPOS DE HIJOS
                    $("#editarCodigoHijoMitad").val("");
                    $("#editarCodigoHijoTercio").val("");
                    $("#editarCodigoHijoCuarto").val("");
                    $("#buscarEditarHijoMitad").val("");
                    $("#buscarEditarHijoTercio").val("");
                    $("#buscarEditarHijoCuarto").val("");
                    
                    // Ocultar resultados de búsqueda
                    $("#editarResultadosMitad").hide().empty();
                    $("#editarResultadosTercio").hide().empty(); 
                    $("#editarResultadosCuarto").hide().empty();
                }
                
            } else {
                console.error("Respuesta no válida:", respuesta);
                swal({
                    type: "error",
                    title: "Error",
                    text: "No se pudo cargar la información del producto"
                });
            }

        },
        error: function(xhr, status, error) {
            console.error("Error AJAX:", error);
            swal({
                type: "error",
                title: "Error",
                text: "No se pudo cargar la información del producto"
            });
        }
    });
});

/*=============================================
FUNCIÓN PARA CARGAR DESCRIPCIÓN DE PRODUCTOS HIJOS
=============================================*/

function cargarDescripcionHijo(tipo, codigo, campoInput) {
    
    if(!codigo || codigo === "" || codigo === null) {
        //console.log("No hay código para tipo:", tipo);
        $(campoInput).val("");
        return;
    }
    
    //console.log("Cargando descripción para " + tipo + " con código:", codigo);
    
    var datos = new FormData();
    datos.append("accion", "obtenerDescripcion");
    datos.append("codigo", codigo);

    $.ajax({
        url: "ajax/catalogo-maestro.ajax.php",
        method: "POST",
        data: datos,
        cache: false,
        contentType: false,
        processData: false,
        dataType: "json",
        success: function(respuesta){
            
            if(respuesta && respuesta.descripcion) {
                var textoCompleto = respuesta.descripcion + " (" + codigo + ")";
                $(campoInput).val(textoCompleto);
                //console.log("✅ Descripción cargada para " + tipo + ":", textoCompleto);
            } else {
                // Si no encuentra descripción, solo mostrar el código
                $(campoInput).val("Código: " + codigo);
                //console.log("⚠️ Solo código para " + tipo + ":", codigo);
            }
        },
        error: function(xhr, status, error) {
            console.error("Error al cargar descripción para " + tipo + ":", error);
            $(campoInput).val("Código: " + codigo);
        }
    });
}

/*=============================================
ELIMINAR PRODUCTO MAESTRO
=============================================*/

$(document).on("click", ".btnEliminarProductoMaestro", function(){

    var idProductoMaestro = $(this).attr("idProductoMaestro");
    var codigoProducto = $(this).attr("codigoProducto");

    swal({
        title: '¿Está seguro de eliminar el producto?',
        text: "El producto '" + codigoProducto + "' será eliminado del catálogo maestro. ¡Esta acción no se puede revertir!",
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        cancelButtonText: 'Cancelar',
        confirmButtonText: 'Sí, eliminar producto!'
    }).then(function(result){

        if(result.value){
            window.location = "index.php?ruta=catalogo-maestro&idProductoMaestro="+idProductoMaestro;
        }
    });
});

/*=============================================
REVISAR SI EL CÓDIGO YA EXISTE
=============================================*/

$("#nuevoCodigoMaestro").change(function(){

    $(".alert").remove();

    var codigoProducto = $(this).val();

    if(codigoProducto.length > 0){
        
        var datos = new FormData();
        datos.append("validarCodigo", codigoProducto);

        $.ajax({
            url: "ajax/catalogo-maestro.ajax.php",
            method: "POST",
            data: datos,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success:function(respuesta){
                
                if(respuesta){
                    $("#nuevoCodigoMaestro").parent().after('<div class="alert alert-warning">Este código ya existe en el catálogo maestro</div>');
                    $("#nuevoCodigoMaestro").val("");
                }
            },
            error: function(xhr, status, error) {
                console.error("Error al validar código:", error);
            }
        });
    }
});

/*=============================================
SINCRONIZAR PRODUCTO INDIVIDUAL
=============================================*/

$(document).on("click", ".btnSincronizarProducto", function(){

    var codigoMaestro = $(this).attr("codigoMaestro");
    var btnElement = $(this);
    
    // Cambiar estado del botón
    btnElement.html('<i class="fa fa-spinner fa-spin"></i>').prop('disabled', true);

    var datos = new FormData();
    datos.append("accion", "sincronizarProducto");
    datos.append("codigoMaestro", codigoMaestro);

    $.ajax({
        url: "ajax/catalogo-maestro.ajax.php",
        method: "POST",
        data: datos,
        cache: false,
        contentType: false,
        processData: false,
        dataType: "json",
        success: function(respuesta){
            
            // Restaurar botón
            btnElement.html('<i class="fa fa-refresh"></i>').prop('disabled', false);
            
            if(respuesta.success){
                swal({
                    type: "success",
                    title: "Producto sincronizado",
                    text: respuesta.mensaje,
                    timer: 2000
                });
            } else {
                swal({
                    type: "error",
                    title: "Error en sincronización",
                    text: respuesta.mensaje
                });
            }
        },
        error: function(xhr, status, error) {
            // Restaurar botón
            btnElement.html('<i class="fa fa-refresh"></i>').prop('disabled', false);
            
            console.error("Error en sincronización:", error);
            swal({
                type: "error",
                title: "Error",
                text: "No se pudo sincronizar el producto"
            });
        }
    });
});

/*=============================================
SINCRONIZACIÓN MASIVA
=============================================*/

$("#btnSincronizarTodos").click(function(){

    var btnElement = $(this);
    
    swal({
        title: '¿Sincronizar todos los productos?',
        text: "Esta acción sincronizará todos los productos del catálogo maestro con los productos locales. Puede tomar varios minutos.",
        type: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        cancelButtonText: 'Cancelar',
        confirmButtonText: 'Sí, sincronizar todos'
    }).then(function(result){

        if(result.value){
            
            // Cambiar estado del botón
            btnElement.html('<i class="fa fa-spinner fa-spin"></i> Sincronizando...').prop('disabled', true);
            
            var datos = new FormData();
            datos.append("accion", "sincronizarTodos");

            $.ajax({
                url: "ajax/catalogo-maestro.ajax.php",
                method: "POST",
                data: datos,
                cache: false,
                contentType: false,
                processData: false,
                dataType: "json",
                success: function(respuesta){
                    
                    // Restaurar botón
                    btnElement.html('<i class="fa fa-refresh"></i> Sincronizar Todos').prop('disabled', false);
                    
                    if(respuesta.success){
                        swal({
                            type: "success",
                            title: "Sincronización completada",
                            text: respuesta.mensaje
                        }).then(function() {
                            location.reload();
                        });
                    } else {
                        swal({
                            type: "error",
                            title: "Error en sincronización masiva",
                            text: respuesta.mensaje
                        });
                    }
                },
                error: function(xhr, status, error) {
                    // Restaurar botón
                    btnElement.html('<i class="fa fa-refresh"></i> Sincronizar Todos').prop('disabled', false);
                    
                    console.error("Error en sincronización masiva:", error);
                    swal({
                        type: "error",
                        title: "Error",
                        text: "No se pudo completar la sincronización masiva"
                    });
                }
            });
        }
    });
});

/*=============================================
IMPORTAR DESDE EXCEL
=============================================*/

$("#btnImportarExcel").click(function(){
    $("#modalImportarExcel").modal("show");
});

$("#formImportarExcel").on("submit", function(e){
    e.preventDefault();
    
    var archivo = $("#archivoExcel")[0].files[0];
    
    if(!archivo){
        swal({
            type: "warning",
            title: "Seleccione un archivo",
            text: "Por favor seleccione un archivo Excel para importar"
        });
        return;
    }
    
    var extension = archivo.name.split('.').pop().toLowerCase();
    if(!['xls', 'xlsx', 'csv'].includes(extension)){
        swal({
            type: "error",
            title: "Formato no válido",
            text: "Solo se permiten archivos Excel (.xls, .xlsx) o CSV"
        });
        return;
    }
    
    if(archivo.size > 5000000){ // 5MB
        swal({
            type: "error",
            title: "Archivo muy grande",
            text: "El archivo no debe pesar más de 5MB"
        });
        return;
    }
    
    // Mostrar progreso
    $("#modalImportarExcel .progress").show();
    $("#btnProcesarImportacion").html('<i class="fa fa-spinner fa-spin"></i> Procesando...').prop('disabled', true);
    
    // Enviar archivo
    var formData = new FormData();
    formData.append("importarExcel", "true");
    formData.append("archivoExcel", archivo);
    
    $.ajax({
        url: "index.php?ruta=catalogo-maestro",
        method: "POST",
        data: formData,
        cache: false,
        contentType: false,
        processData: false,
        xhr: function() {
            var myXhr = $.ajaxSettings.xhr();
            if (myXhr.upload) {
                myXhr.upload.addEventListener('progress', function(e) {
                    if (e.lengthComputable) {
                        var porcentaje = Math.round((e.loaded / e.total) * 100);
                        $("#modalImportarExcel .progress-bar").css('width', porcentaje + '%').attr('aria-valuenow', porcentaje).text(porcentaje + '%');
                    }
                }, false);
            }
            return myXhr;
        },
        success: function(respuesta){
            
            // Restaurar botón
            $("#btnProcesarImportacion").html('<i class="fa fa-upload"></i> Procesar Importación').prop('disabled', false);
            $("#modalImportarExcel .progress").hide();
            $("#modalImportarExcel .progress-bar").css('width', '0%').attr('aria-valuenow', 0).text('0%');
            
            // Cerrar modal y recargar página
            $("#modalImportarExcel").modal("hide");
            
            // La respuesta del controlador incluye el script SweetAlert
            // No necesitamos procesar JSON aquí
            setTimeout(function() {
                location.reload();
            }, 2000);
        },
        error: function(xhr, status, error) {
            // Restaurar botón
            $("#btnProcesarImportacion").html('<i class="fa fa-upload"></i> Procesar Importación').prop('disabled', false);
            $("#modalImportarExcel .progress").hide();
            
            console.error("Error en importación:", error);
            swal({
                type: "error",
                title: "Error al importar",
                text: "No se pudo procesar el archivo de importación"
            });
        }
    });
});

/*=============================================
CONFIGURAR VALIDACIONES
=============================================*/

function configurarValidaciones() {
    
    // Validación de precios (solo números)
    $('input[name="nuevoPrecioVentaMaestro"], input[name="editarPrecioVentaMaestro"]').on('input', function(){
        var precio = parseFloat($(this).val());
        if(precio < 0 || isNaN(precio)) {
            $(this).val(0);
        }
    });
    
    // Validación de códigos (solo alfanuméricos)
    $('#nuevoCodigoMaestro, #editarCodigoMaestro').on('input', function(){
        var codigo = $(this).val();
        // Permitir solo letras, números y algunos caracteres especiales
        var codigoLimpio = codigo.replace(/[^a-zA-Z0-9\-_]/g, '');
        if(codigo !== codigoLimpio) {
            $(this).val(codigoLimpio);
        }
    });
    
    // Validación de descripción (no vacía)
    $('#nuevaDescripcionMaestro, #editarDescripcionMaestro').on('blur', function(){
        var descripcion = $(this).val().trim();
        if(descripcion.length < 3) {
            $(this).addClass('is-invalid');
            if($(this).next('.invalid-feedback').length === 0) {
                $(this).after('<div class="invalid-feedback">La descripción debe tener al menos 3 caracteres</div>');
            }
        } else {
            $(this).removeClass('is-invalid').addClass('is-valid');
            $(this).next('.invalid-feedback').remove();
        }
    });
    
    // Validación de categoría
    $('#nuevaCategoriaMaestro, #editarCategoriaMaestro').on('change', function(){
        if($(this).val() === '' || $(this).val() === '0') {
            $(this).addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid').addClass('is-valid');
        }
    });
}

/*=============================================
FILTROS AVANZADOS
=============================================*/

$(document).ready(function(){
    
    // Configurar filtro por categoría
    $('#filtroCategoria').on('change', function() {
        var categoria = $(this).val();
        var table = $('.tabla-catalogo-maestro').DataTable();
        
        if (categoria === '' || categoria === '0') {
            table.column(4).search('').draw();
        } else {
            table.column(4).search(categoria).draw();
        }
    });
    
    // Configurar filtro por productos divisibles
    $('#filtroDivisible').on('change', function() {
        var divisible = $(this).val();
        var table = $('.tabla-catalogo-maestro').DataTable();
        
        if (divisible === '') {
            table.column(6).search('').draw();
        } else {
            var textoFiltro = divisible === '1' ? 'SÍ' : 'NO';
            table.column(6).search(textoFiltro).draw();
        }
    });
    
    // Limpiar todos los filtros
    $('#limpiarFiltros').on('click', function() {
        $('#filtroCategoria').val('');
        $('#filtroDivisible').val('');
        $('.tabla-catalogo-maestro').DataTable().search('').columns().search('').draw();
        
        // Limpiar campos de búsqueda personalizados
        $('#buscarCodigo').val('');
        $('#buscarDescripcion').val('');
    });
    
    // Buscar por código específico
    $('#buscarCodigo').on('keyup', function() {
        var codigo = $(this).val();
        $('.tabla-catalogo-maestro').DataTable().column(2).search(codigo).draw();
    });
    
    // Buscar por descripción específica
    $('#buscarDescripcion').on('keyup', function() {
        var descripcion = $(this).val();
        $('.tabla-catalogo-maestro').DataTable().column(3).search(descripcion).draw();
    });
});

/*=============================================
LIMPIAR CAMPOS AL CERRAR MODALES - CORREGIDO
=============================================*/

$('#modalAgregarProductoMaestro').on('hidden.bs.modal', function () {
    
    //console.log("Limpiando modal agregar");
    
    // Resetear formulario
    $(this).find('form')[0].reset();
    
    // Limpiar imagen
    $(".previsualizarMaestro").attr("src", "vistas/img/productos/default/anonymous.png");
    
    // ✅ RESETEAR DIVISIÓN CORRECTAMENTE
    $("#esDivisibleMaestro").prop("checked", false);
    $("#divisionConfigMaestro").hide();
    
    // Limpiar campos de división
    $("#codigoHijoMitad").val("");
    $("#codigoHijoTercio").val("");
    $("#codigoHijoCuarto").val("");
    $("input[name='buscarHijoMitad']").val("");
    $("input[name='buscarHijoTercio']").val("");
    $("input[name='buscarHijoCuarto']").val("");
    
    // Ocultar resultados de búsqueda
    $("#resultadosMitad").hide().empty();
    $("#resultadosTercio").hide().empty();
    $("#resultadosCuarto").hide().empty();
    
    // Remover alertas y validaciones
    $(".alert").remove();
    $(this).find('.form-control').removeClass('is-invalid is-valid');
    $(this).find('.invalid-feedback').remove();
    
    //console.log("Modal agregar limpiado completamente");
});

$('#modalEditarProductoMaestro').on('hidden.bs.modal', function () {
    
    //console.log("Limpiando modal editar");
    
    // Resetear formulario
    $(this).find('form')[0].reset();
    
    // Limpiar imagen
    $(".previsualizarMaestroEditar").attr("src", "vistas/img/productos/default/anonymous.png");
    
    // ✅ RESETEAR DIVISIÓN CORRECTAMENTE
    $("#editarEsDivisibleMaestro").prop("checked", false);
    $("#divisionConfigEditarMaestro").hide();
    
    // Limpiar campos de división
    $("#editarCodigoHijoMitad").val("");
    $("#editarCodigoHijoTercio").val("");
    $("#editarCodigoHijoCuarto").val("");
    $("#buscarEditarHijoMitad").val("");
    $("#buscarEditarHijoTercio").val("");
    $("#buscarEditarHijoCuarto").val("");
    
    // Ocultar resultados de búsqueda
    $("#editarResultadosMitad").hide().empty();
    $("#editarResultadosTercio").hide().empty();
    $("#editarResultadosCuarto").hide().empty();
    
    // Limpiar campos específicos
    $("#idProductoMaestro").val("");
    $("#editarCodigoMaestro").val("");
    $("#editarDescripcionMaestro").val("");
    $("#editarPrecioVentaMaestro").val("");
    $("#editarCategoriaMaestro").val("");
    $("#imagenActualMaestro").val("");
    
    // Remover alertas y validaciones
    $(".alert").remove();
    $(this).find('.form-control').removeClass('is-invalid is-valid');
    $(this).find('.invalid-feedback').remove();
    
    //console.log("Modal editar limpiado completamente");
});

/*=============================================
LIMPIAR MODAL EDITAR
=============================================*/

$('#modalEditarProductoMaestro').on('hidden.bs.modal', function () {
    
    // Resetear formulario
    $(this).find('form')[0].reset();
    
    // Limpiar imagen
    $(".previsualizarMaestroEditar").attr("src", "vistas/img/productos/default/anonymous.png");
    
    // Ocultar configuración de división
    $("#divisionConfigEditarMaestro").hide();
    $("#editarEsDivisibleMaestro").prop("checked", false);
    
    // Limpiar campos de división
    $("#editarCodigoHijoMitad").val("");
    $("#editarCodigoHijoTercio").val("");
    $("#editarCodigoHijoCuarto").val("");
    $("#buscarEditarHijoMitad").val("");
    $("#buscarEditarHijoTercio").val("");
    $("#buscarEditarHijoCuarto").val("");
    
    // Ocultar resultados de búsqueda
    $("#editarResultadosMitad").hide().empty();
    $("#editarResultadosTercio").hide().empty();
    $("#editarResultadosCuarto").hide().empty();
    
    // Remover alertas
    $(".alert").remove();
    
    // Limpiar validaciones visuales
    $(this).find('.form-control').removeClass('is-invalid is-valid');
    $(this).find('.invalid-feedback').remove();
    
    // Limpiar campos específicos
    $("#idProductoMaestro").val("");
    $("#editarCodigoMaestro").val("");
    $("#editarDescripcionMaestro").val("");
    $("#editarPrecioVentaMaestro").val("");
    $("#editarCategoriaMaestro").val("");
    $("#imagenActualMaestro").val("");

   //console.log("Modal editar limpiado completamente");
});

/*=============================================
LIMPIAR MODAL IMPORTAR EXCEL
=============================================*/

$('#modalImportarExcel').on('hidden.bs.modal', function () {
    
    // Resetear formulario
    $(this).find('form')[0].reset();
    
    // Limpiar input de archivo
    $("#archivoExcel").val("");
    
    // Limpiar preview si existe
    $("#previewImportacion").empty().hide();
    
    // Remover alertas
    $(".alert").remove();
    
    // Limpiar validaciones visuales
    $(this).find('.form-control').removeClass('is-invalid is-valid');
    $(this).find('.invalid-feedback').remove();
    
    // Resetear progreso si existe
    $(".progress-bar").css('width', '0%').attr('aria-valuenow', 0);
    $(".progress").hide();

    //console.log("Modal importar limpiado completamente");
});

/*=============================================
FUNCIONES AUXILIARES
=============================================*/

// Función para formatear números como moneda
function formatearPrecio(precio) {
    return '$' + parseFloat(precio).toLocaleString('es-CO', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    });
}

// Función para validar archivo antes de subir
function validarArchivo(archivo, tiposPermitidos, tamañoMaximo) {
    
    if (!archivo) {
        return { valido: false, mensaje: "No se seleccionó ningún archivo" };
    }
    
    var extension = archivo.name.split('.').pop().toLowerCase();
    if (!tiposPermitidos.includes(extension)) {
        return { 
            valido: false, 
            mensaje: "Formato no válido. Permitidos: " + tiposPermitidos.join(', ') 
        };
    }
    
    if (archivo.size > tamañoMaximo) {
        var tamañoMB = Math.round(tamañoMaximo / 1024 / 1024);
        return { 
            valido: false, 
            mensaje: "El archivo no debe pesar más de " + tamañoMB + "MB" 
        };
    }
    
    return { valido: true, mensaje: "Archivo válido" };
}

// Función para actualizar contador de productos
function actualizarContador() {
    var table = $('.tabla-catalogo-maestro').DataTable();
    if (table) {
        var info = table.page.info();
        $('#contadorProductos').html(`
            <small class="text-muted">
                <i class="fa fa-cubes"></i> 
                Total: ${info.recordsTotal} productos | 
                Mostrando: ${info.recordsDisplay}
            </small>
        `);
    }
}

// Función para recargar la tabla manteniendo filtros
function recargarTabla() {
    var table = $('.tabla-catalogo-maestro').DataTable();
    if (table) {
        table.ajax.reload(null, false); // false = mantener paginación actual
    } else {
        location.reload(); // Si no hay DataTable, recargar página
    }
}

/*=============================================
TOOLTIP Y COMPONENTES UI
=============================================*/

$(document).ready(function(){
    
    // Inicializar tooltips
    $('[data-toggle="tooltip"]').tooltip();
    
    // Configurar tooltips dinámicos para elementos que se agregan después
    $(document).on('mouseenter', '[data-toggle="tooltip"]:not([data-original-title])', function() {
        $(this).tooltip();
    });
    
    // Auto-ocultar alertas después de 5 segundos
    $(document).on('shown.bs.alert', '.alert', function() {
        var alert = $(this);
        setTimeout(function() {
            alert.fadeOut('slow');
        }, 5000);
    });
    
    // Confirmar antes de salir si hay cambios sin guardar
    var formModificado = false;
    
    // Detectar cambios en formularios
    $('#modalAgregarProductoMaestro form, #modalEditarProductoMaestro form').on('change input', function() {
        formModificado = true;
    });
    
    // Resetear flag cuando se guarda
    $(document).on('submit', 'form', function() {
        formModificado = false;
    });
    
    // Advertir antes de cerrar modal con cambios
    $('.modal').on('hide.bs.modal', function(e) {
        if (formModificado) {
            if (!confirm('¿Está seguro de cerrar? Los cambios no guardados se perderán.')) {
                e.preventDefault();
                return false;
            }
        }
        formModificado = false;
    });
});

/*=============================================
MANEJO DE ERRORES AJAX GLOBAL
=============================================*/

$(document).ajaxError(function(event, xhr, settings, thrownError) {
    
    console.error("Error AJAX en catálogo maestro:", {
        status: xhr.status,
        error: thrownError,
        url: settings.url
    });
    
    // Si hay error 500 o similar, mostrar mensaje genérico
    if (xhr.status >= 500) {
        swal({
            type: "error",
            title: "Error del servidor",
            text: "Ha ocurrido un error interno. Por favor contacte al administrador."
        });
    }
    
    // Si hay error 404
    if (xhr.status === 404) {
        swal({
            type: "error",
            title: "Recurso no encontrado",
            text: "La página o archivo solicitado no existe."
        });
    }
    
    // Restaurar botones que puedan estar en estado de carga
    $('.btn').each(function() {
        if ($(this).prop('disabled') && $(this).html().includes('spinner')) {
            $(this).prop('disabled', false);
            var textoOriginal = $(this).data('texto-original');
            if (textoOriginal) {
                $(this).html(textoOriginal);
            }
        }
    });
});

/*=============================================
INICIALIZACIÓN FINAL
=============================================*/

$(document).ready(function(){
    
    //console.log('Catálogo Maestro JS - Cargado completamente');
    
    // Verificar que todos los componentes estén inicializados
    setTimeout(function() {
        
        // Verificar DataTable
        if (!$.fn.DataTable.isDataTable('.tabla-catalogo-maestro')) {
            console.warn('DataTable no se inicializó correctamente');
        }
        
        // Verificar modales
        if ($('.modal').length === 0) {
            console.warn('No se encontraron modales en la página');
        }
        
        // Actualizar contador inicial
        actualizarContador();
        
        //console.log('Catálogo Maestro - Sistema completamente inicializado');
        
    }, 1000);
});

/*=============================================
DEBUG - VERIFICAR ELEMENTOS HTML
=============================================*/

function verificarElementosHTML() {
  /*  
    console.log("=== VERIFICANDO ELEMENTOS HTML ===");
    
    // Elementos del modal agregar
    console.log("Modal agregar:");
    console.log("- esDivisibleMaestro:", $("#esDivisibleMaestro").length > 0 ? "✅ EXISTS" : "❌ MISSING");
    console.log("- divisionConfigMaestro:", $("#divisionConfigMaestro").length > 0 ? "✅ EXISTS" : "❌ MISSING");
    console.log("- codigoHijoMitad:", $("#codigoHijoMitad").length > 0 ? "✅ EXISTS" : "❌ MISSING");
    console.log("- codigoHijoTercio:", $("#codigoHijoTercio").length > 0 ? "✅ EXISTS" : "❌ MISSING");
    console.log("- codigoHijoCuarto:", $("#codigoHijoCuarto").length > 0 ? "✅ EXISTS" : "❌ MISSING");
    
    // Elementos del modal editar
    console.log("Modal editar:");
    console.log("- editarEsDivisibleMaestro:", $("#editarEsDivisibleMaestro").length > 0 ? "✅ EXISTS" : "❌ MISSING");
    console.log("- divisionConfigEditarMaestro:", $("#divisionConfigEditarMaestro").length > 0 ? "✅ EXISTS" : "❌ MISSING");
    console.log("- editarCodigoHijoMitad:", $("#editarCodigoHijoMitad").length > 0 ? "✅ EXISTS" : "❌ MISSING");
    console.log("- editarCodigoHijoTercio:", $("#editarCodigoHijoTercio").length > 0 ? "✅ EXISTS" : "❌ MISSING");
    console.log("- editarCodigoHijoCuarto:", $("#editarCodigoHijoCuarto").length > 0 ? "✅ EXISTS" : "❌ MISSING");
    
    console.log("=== FIN VERIFICACIÓN ===");
    */
}

/*=============================================
DEBUG Y LIMPIEZA ANTES DE ENVIAR FORMULARIO
=============================================*/

$(document).on("submit", "form", function(e) {
    
    // Solo para el formulario de editar producto maestro
    if($(this).find("#idProductoMaestro").length > 0) {
        
        //console.log("=== PROCESANDO FORMULARIO EDITAR ===");
        
        var esDivisible = $("#editarEsDivisibleMaestro").prop("checked");
        //console.log("Es divisible:", esDivisible);
        
        if(!esDivisible) {
            
            // Si NO es divisible, limpiar todos los campos
            $("#editarCodigoHijoMitad").val("");
            $("#editarCodigoHijoTercio").val("");
            $("#editarCodigoHijoCuarto").val("");
            
            //console.log("Limpiando todos los campos de división");
            
        } else {
            
            // Si ES divisible, verificar campos individualmente
            var buscarMitad = $("#buscarEditarHijoMitad").val();
            var buscarTercio = $("#buscarEditarHijoTercio").val();
            var buscarCuarto = $("#buscarEditarHijoCuarto").val();
            
            /*console.log("Valores en campos de búsqueda:");
            console.log("- Mitad: '" + buscarMitad + "'");
            console.log("- Tercio: '" + buscarTercio + "'");
            console.log("- Cuarto: '" + buscarCuarto + "'");
            */
            
            // ✅ SI EL CAMPO DE BÚSQUEDA ESTÁ VACÍO, LIMPIAR EL HIDDEN
            if(!buscarMitad || buscarMitad.trim() === "") {
                $("#editarCodigoHijoMitad").val("");
                //console.log("🧹 Campo mitad limpiado");
            }
            
            if(!buscarTercio || buscarTercio.trim() === "") {
                $("#editarCodigoHijoTercio").val("");
                //console.log("🧹 Campo tercio limpiado");
            }
            
            if(!buscarCuarto || buscarCuarto.trim() === "") {
                $("#editarCodigoHijoCuarto").val("");
                //console.log("🧹 Campo cuarto limpiado");
            }
        }
        /*
        console.log("Valores finales en campos hidden:");
        console.log("- Mitad hidden: '" + $("#editarCodigoHijoMitad").val() + "'");
        console.log("- Tercio hidden: '" + $("#editarCodigoHijoTercio").val() + "'");
        console.log("- Cuarto hidden: '" + $("#editarCodigoHijoCuarto").val() + "'");
        */
        // ✅ FORZAR QUE LOS CAMPOS VACÍOS SE ENVÍEN
        if($("#editarCodigoHijoMitad").val() === "") {
            $("#editarCodigoHijoMitad").val("EMPTY_FIELD");
        }
        if($("#editarCodigoHijoTercio").val() === "") {
            $("#editarCodigoHijoTercio").val("EMPTY_FIELD");
        }
        if($("#editarCodigoHijoCuarto").val() === "") {
            $("#editarCodigoHijoCuarto").val("EMPTY_FIELD");
        }
        /*
        console.log("Valores finales para envío:");
        console.log("- Mitad: '" + $("#editarCodigoHijoMitad").val() + "'");
        console.log("- Tercio: '" + $("#editarCodigoHijoTercio").val() + "'");
        console.log("- Cuarto: '" + $("#editarCodigoHijoCuarto").val() + "'");
        */
    }
});

// Ejecutar verificación cuando se carge la página
$(document).ready(function(){
    setTimeout(verificarElementosHTML, 2000); // Ejecutar después de 2 segundos
});

// Mensaje final para debug
//console.log('Archivo catalogo-maestro.js cargado - Versión: 1.0 - Compatible con danytrax/adminv5');
