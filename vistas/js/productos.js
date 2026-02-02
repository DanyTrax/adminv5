$(document).ready(function() {

    /*=============================================
    CARGAR LA TABLA DE PRODUCTOS (RENDERIZADO DIRECTO DESDE PHP)
    =============================================*/
    // La tabla ahora se renderiza directamente desde PHP como en ventas
    // No se usa DataTable con AJAX, se usa la clase "tablas" estándar

    /*=============================================
    SUBIENDO LA FOTO DEL PRODUCTO
    =============================================*/
    $(".nuevaImagen").change(function() {
        var imagen = this.files[0];
        if (imagen["type"] != "image/jpeg" && imagen["type"] != "image/png") {
            $(".nuevaImagen").val("");
            swal({ title: "Error al subir la imagen", text: "¡La imagen debe estar en formato JPG o PNG!", type: "error", confirmButtonText: "¡Cerrar!" });
        } else if (imagen["size"] > 2000000) {
            $(".nuevaImagen").val("");
            swal({ title: "Error al subir la imagen", text: "¡La imagen no debe pesar más de 2MB!", type: "error", confirmButtonText: "¡Cerrar!" });
        } else {
            var datosImagen = new FileReader;
            datosImagen.readAsDataURL(imagen);
            $(datosImagen).on("load", function(event) {
                var rutaImagen = event.target.result;
                $(".previsualizar").attr("src", rutaImagen);
            });
        }
    });

    /*=============================================
    EDITAR PRODUCTO (VERSIÓN CORREGIDA)
    =============================================*/
    $(document).on("click", "button.btnEditarProducto", function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        var idProducto = $(this).attr("data-id-producto") || $(this).attr("idProducto");
        
        console.log("ID Producto capturado:", idProducto);
        
        if (!idProducto || idProducto === "" || idProducto === undefined) {
            swal({
                type: "error",
                title: "Error",
                text: "No se pudo obtener el ID del producto."
            });
            return;
        }
        
        var datos = new FormData();
        datos.append("idProducto", idProducto);

        $.ajax({
            url: "ajax/productos.ajax.php",
            method: "POST",
            data: datos,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function(respuesta) {
                
                // Validar que la respuesta sea válida
                if (!respuesta || respuesta === null || typeof respuesta !== "object") {
                    swal({
                        type: "error",
                        title: "Error",
                        text: "No se encontró el producto."
                    });
                    return;
                }
                
                // LLENAR INFORMACIÓN DE SOLO LECTURA
                $("#infoCodigo").text(respuesta["codigo"] || "");
                $("#infoDescripcion").text(respuesta["descripcion"] || "");
                $("#infoPrecioVenta").text("$" + parseFloat(respuesta["precio_venta"] || 0).toLocaleString());
                $("#infoStockActual").text((respuesta["stock"] || 0) + " unidades");
                
                // Determinar tipo de producto
                var tipoProducto = respuesta["es_divisible"] == 1 ? "Divisible" : "Simple";
                $("#infoTipo").text(tipoProducto);

                // CAMPOS EDITABLES
                $("#editarStock").val(respuesta["stock"] || 0);

                // --- LÍNEA CORREGIDA PARA PASAR EL ID ---
                $("#idProducto").val(respuesta["id"]);

                if (respuesta["imagen"] != "" && respuesta["imagen"]) {
                    $("#imagenActual").val(respuesta["imagen"]);
                    $(".previsualizar").attr("src", respuesta["imagen"]);
                } else {
                    $(".previsualizar").attr("src", "vistas/img/productos/default/anonymous.png");
                }

                // Obtener nombre de la categoría
                if (respuesta["id_categoria"]) {
                    var datosCategoria = new FormData();
                    datosCategoria.append("idCategoria", respuesta["id_categoria"]);
                    $.ajax({
                        url: "ajax/categorias.ajax.php",
                        method: "POST",
                        data: datosCategoria,
                        cache: false, contentType: false, processData: false, dataType: "json",
                        success: function(respuestaCategoria) {
                            if (respuestaCategoria && respuestaCategoria["categoria"]) {
                                $("#infoCategoria").text(respuestaCategoria["categoria"]);
                            }
                        }
                    });
                }
                
                // Abrir el modal después de cargar los datos
                $("#modalEditarProducto").modal("show");
            },
            error: function(xhr, status, error) {
                console.error("Error AJAX:", error, xhr.responseText);
                swal({
                    type: "error",
                    title: "Error",
                    text: "No se pudo cargar el producto. Por favor, intente nuevamente."
                });
            }
        });
    });

    /*=============================================
    ELIMINAR PRODUCTO
    =============================================*/
    $(document).on("click", "button.btnEliminarProducto", function() {
        var idProducto = $(this).attr("data-id-producto") || $(this).attr("idProducto");
        var codigo = $(this).attr("data-codigo") || $(this).attr("codigo");
        var imagen = $(this).attr("data-imagen") || $(this).attr("imagen");
        swal({
            title: '¿Está seguro de borrar el producto?',
            text: "¡Si no lo está puede cancelar la acción!",
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6', cancelButtonColor: '#d33',
            cancelButtonText: 'Cancelar', confirmButtonText: 'Si, borrar producto!'
        }).then(function(result) {
            if (result.value) {
                window.location = "index.php?ruta=productos&idProducto=" + idProducto + "&imagen=" + imagen + "&codigo=" + codigo;
            }
        });
    });

    /*=============================================
    DIVIDIR PRODUCTO (LÓGICA NUEVA)
    =============================================*/
    $(document).on("click", "button.btnDividirProducto", function() {
        var idProducto = $(this).attr("data-id-producto") || $(this).attr("idProducto");
        var datos = new FormData();
        datos.append("idProducto", idProducto);

        $.ajax({
            url: "ajax/productos.ajax.php",
            method: "POST",
            data: datos,
            cache: false, contentType: false, processData: false, dataType: "json",
            success: function(respuesta){
                var opcionesHtml = "";
                if(respuesta["nombre_mitad"] != ""){ opcionesHtml += '<option value="mitad">En 2 Mitades ('+respuesta["nombre_mitad"]+')</option>'; }
                if(respuesta["nombre_tercio"] != ""){ opcionesHtml += '<option value="tercio">En 3 Tercios ('+respuesta["nombre_tercio"]+')</option>'; }
                if(respuesta["nombre_cuarto"] != ""){ opcionesHtml += '<option value="cuarto">En 4 Cuartos ('+respuesta["nombre_cuarto"]+')</option>'; }

                if(opcionesHtml != ""){
                    swal({
                        title: '¿Cómo deseas partir el producto?',
                        text: 'Se descontará 1 unidad del stock del producto original.',
                        type: 'info',
                        html: '<select id="swal-select1" class="swal2-input">' + opcionesHtml + '</select>',
                        showCancelButton: true,
                        confirmButtonText: '¡Sí, partir!',
                        preConfirm: function() { return document.getElementById('swal-select1').value }
                    }).then(function(result) {
                        if (result.value) {
                            var tipoDivision = result.value;
                            var datosDivision = new FormData();
                            datosDivision.append("idProductoDividir", idProducto);
                            datosDivision.append("tipoDivision", tipoDivision);
                            $.ajax({
                                url: "ajax/productos.ajax.php",
                                method: "POST",
                                data: datosDivision,
                                cache: false, contentType: false, processData: false,
                                success: function(respuestaAjax){
                                    if(respuestaAjax.trim() == "ok"){
                                        swal({ type: "success", title: "¡Producto dividido correctamente!"}).then(function(result){ if (result.value) { window.location = "productos"; } });
                                    } else {
                                        var errorTexto = "No se pudo dividir. Verifique que los nombres de las partes estén definidos.";
                                        if(respuestaAjax.trim() == "error_stock"){ errorTexto = "No hay stock suficiente."; }
                                        swal({ type: "error", title: "Error", text: errorTexto });
                                    }
                                }
                            });
                        }
                    });
                } else {
                    swal({ type: "error", title: "No definido", text: "Este producto es divisible, pero no se han definido los nombres de las partes." });
                }
            }
        });
    });
    /*=============================================
ASIGNAR CÓDIGO A PARTIR DE LA CATEGORÍA
=============================================*/
$("#nuevaCategoria").change(function(){

	var idCategoria = $(this).val();
	var datos = new FormData();
	datos.append("idCategoria", idCategoria);

	$.ajax({
		url:"ajax/productos.ajax.php",
		method: "POST",
		data: datos,
		cache: false,
		contentType: false,
		processData: false,
		dataType:"json",
		success:function(respuesta){
			// Si no hay respuesta, no hacemos nada
			if(!respuesta){
				// Si es la primera vez, el código será el ID de la categoría + "01"
				var nuevoCodigo = idCategoria + "01";
				$("#nuevoCodigo").val(nuevoCodigo);
			} else {
				// Si ya hay productos, tomamos el último código y le sumamos 1
				var nuevoCodigo = Number(respuesta["codigo"]) + 1;
    			$("#nuevoCodigo").val(nuevoCodigo);
			}
		}
	})
})
});