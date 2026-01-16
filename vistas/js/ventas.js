/*=============================================
CARGAR LA TABLA DINÁMICA DE VENTAS
=============================================*/

$('.tablaVentas').DataTable({
	"ajax": "ajax/datatable-ventas.ajax.php",
	"deferRender": true,
	"retrieve": true,
	"processing": true,
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
	}
});

/*=============================================
AGREGANDO PRODUCTOS A LA VENTA DESDE LA TABLA
=============================================*/
$(".tablaVentas tbody").on("click", "button.agregarProducto", function() {
	var idProducto = $(this).attr("idProducto");
	$(this).removeClass("btn-primary agregarProducto");
	$(this).addClass("btn-default");
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
			var descripcion = respuesta["descripcion"];
			var stock = respuesta["stock"];
			var precio = respuesta["precio_venta"];
			if (stock == 0) {
				swal({
					title: "No hay stock disponible",
					type: "error",
					confirmButtonText: "¡Cerrar!"
				});
				$("button[idProducto='" + idProducto + "']").addClass("btn-primary agregarProducto");
				return;
			}
			$(".nuevoProducto").append(
				'<div class="row" style="padding:5px 15px">' +
				'<div class="col-xs-6" style="padding-right:0px">' +
				'<div class="input-group">' +
				'<span class="input-group-addon"><button type="button" class="btn btn-danger btn-xs quitarProducto" idProducto="' + idProducto + '"><i class="fa fa-times"></i></button></span>' +
				'<input type="text" class="form-control nuevaDescripcionProducto" idProducto="' + idProducto + '" name="agregarProducto" value="' + descripcion + '" readonly required>' +
				'</div>' +
				'</div>' +
				'<div class="col-xs-3">' +
				'<input type="number" class="form-control nuevaCantidadProducto" name="nuevaCantidadProducto" min="1" value="1" stock="' + stock + '" nuevoStock="' + Number(stock - 1) + '" required>' +
				'</div>' +
				'<div class="col-xs-3 ingresoPrecio" style="padding-left:0px">' +
				'<div class="input-group">' +
				'<span class="input-group-addon"><i class="ion ion-social-usd"></i></span>' +
				'<input type="text" class="form-control nuevoPrecioProducto" precioReal="' + precio + '" name="nuevoPrecioProducto" value="' + precio + '" readonly >' +
				'</div>' +
				'</div>' +
				'</div>');
			sumarTotalPrecios();
			agregarImpuesto();
			listarProductos();
			$(".nuevoPrecioProducto").number(true);
			localStorage.removeItem("quitarProducto");
		}
	})
});

/*=============================================
CUANDO CARGUE LA TABLA CADA VEZ QUE NAVEGUE EN ELLA
=============================================*/
$(".tablaVentas").on("draw.dt", function() {
	if (localStorage.getItem("quitarProducto") != null) {
		var listaIdProductos = JSON.parse(localStorage.getItem("quitarProducto"));
		for (var i = 0; i < listaIdProductos.length; i++) {
			$("button.recuperarBoton[idProducto='" + listaIdProductos[i]["idProducto"] + "']").removeClass('btn-default');
			$("button.recuperarBoton[idProducto='" + listaIdProductos[i]["idProducto"] + "']").addClass('btn-primary agregarProducto');
		}
	}
});

/*=============================================
QUITAR PRODUCTOS DE LA VENTA Y RECUPERAR BOTÓN
=============================================*/
var idQuitarProducto = [];
localStorage.removeItem("quitarProducto");
$(".formularioVenta").on("click", "button.quitarProducto", function() {
	$(this).parent().parent().parent().parent().remove();
	var idProducto = $(this).attr("idProducto");
	if (localStorage.getItem("quitarProducto") == null) {
		idQuitarProducto = [];
	} else {
		idQuitarProducto.concat(localStorage.getItem("quitarProducto"))
	}
	idQuitarProducto.push({
		"idProducto": idProducto
	});
	localStorage.setItem("quitarProducto", JSON.stringify(idQuitarProducto));
	$("button.recuperarBoton[idProducto='" + idProducto + "']").removeClass('btn-default');
	$("button.recuperarBoton[idProducto='" + idProducto + "']").addClass('btn-primary agregarProducto');
	if ($(".nuevoProducto").children().length == 0) {
		$("#nuevoImpuestoVenta").val(0);
		$("#nuevoDescuentoVenta").val(0);
		$("#nuevoTotalVenta").val(0);
		$("#totalVenta").val(0);
		$("#nuevoTotalVenta").attr("total", 0);
	} else {
		sumarTotalPrecios();
		agregarImpuesto();
		listarProductos();
	}
});

/*=============================================
AGREGANDO PRODUCTOS DESDE EL BOTÓN PARA DISPOSITIVOS
=============================================*/
var numProducto = 0;
$(".btnAgregarProducto").click(function() {
	numProducto++;
	var datos = new FormData();
	datos.append("traerProductos", "ok");
	$.ajax({
		url: "ajax/productos.ajax.php",
		method: "POST",
		data: datos,
		cache: false,
		contentType: false,
		processData: false,
		dataType: "json",
		success: function(respuesta) {
			$(".nuevoProducto").append(
				'<div class="row" style="padding:5px 15px">' +
				'<div class="col-xs-6" style="padding-right:0px">' +
				'<div class="input-group">' +
				'<span class="input-group-addon"><button type="button" class="btn btn-danger btn-xs quitarProducto" idProducto><i class="fa fa-times"></i></button></span>' +
				'<select class="form-control nuevaDescripcionProducto" id="producto' + numProducto + '" idProducto name="nuevaDescripcionProducto" required>' +
				'<option>Seleccione el producto</option>' +
				'</select>' +
				'</div>' +
				'</div>' +
				'<div class="col-xs-3 ingresoCantidad">' +
				'<input type="number" class="form-control nuevaCantidadProducto" name="nuevaCantidadProducto" min="1" value="0" stock nuevoStock required>' +
				'</div>' +
				'<div class="col-xs-3 ingresoPrecio" style="padding-left:0px">' +
				'<div class="input-group">' +
				'<span class="input-group-addon"><i class="ion ion-social-usd"></i></span>' +
				'<input type="text" class="form-control nuevoPrecioProducto" precioReal="" name="nuevoPrecioProducto" readonly required>' +
				'</div>' +
				'</div>' +
				'</div>');
			respuesta.forEach(funcionForEach);
			function funcionForEach(item, index) {
				if (item.stock != 0) {
					$("#producto" + numProducto).append(
						'<option idProducto="' + item.id + '" value="' + item.descripcion + '">' + item.descripcion + '</option>'
					)
				}
			}
			sumarTotalPrecios();
			agregarImpuesto();
			$(".nuevoPrecioProducto").number(true);
		}
	})
});

/*=============================================
AGREGANDO PRODUCTOS DESDE EL BOTÓN PARA DISPOSITIVOS
=============================================*/
$(document).on("click", ".btnAgregarProducto1", function() {
	$(".nuevoProducto").append(
		'<div class="row" style="padding:5px 15px">' +
		'<div class="col-xs-6" style="padding-right:0px">' +
		'<div class="input-group">' +
		'<span class="input-group-addon"><button type="button" class="btn btn-danger btn-xs quitarProducto"><i class="fa fa-times"></i></button></span>' +
		'<input type="text" class="form-control nuevaDescripcionProducto" id="producto" name="nuevaDescripcionProducto" required>' +
		'</div>' +
		'</div>' +
		'<div class="col-xs-3 ingresoCantidad">' +
		'<input type="number" class="form-control nuevaCantidadProducto" name="nuevaCantidadProducto" min="1" required>' +
		'</div>' +
		'<div class="col-xs-3 ingresoPrecio" style="padding-left:0px">' +
		'<div class="input-group">' +
		'<span class="input-group-addon"><i class="ion ion-social-usd"></i></span>' +
		'<input type="text" class="form-control nuevoPrecioProducto" precioReal="" name="nuevoPrecioProducto" required>' +
		'</div>' +
		'</div>' +
		'</div>');
});

/*=============================================
SELECCIONAR PRODUCTO
=============================================*/
$(".formularioVenta").on("change", "select.nuevaDescripcionProducto", function() {
	var nombreProducto = $(this).val();
	var nuevaDescripcionProducto = $(this).parent().parent().parent().children().children().children(".nuevaDescripcionProducto");
	var nuevoPrecioProducto = $(this).parent().parent().parent().children(".ingresoPrecio").children().children(".nuevoPrecioProducto");
	var nuevaCantidadProducto = $(this).parent().parent().parent().children(".ingresoCantidad").children(".nuevaCantidadProducto");
	var datos = new FormData();
	datos.append("nombreProducto", nombreProducto);
	$.ajax({
		url: "ajax/productos.ajax.php",
		method: "POST",
		data: datos,
		cache: false,
		contentType: false,
		processData: false,
		dataType: "json",
		success: function(respuesta) {
			$(nuevaDescripcionProducto).attr("idProducto", respuesta["id"]);
			$(nuevaCantidadProducto).attr("stock", respuesta["stock"]);
			$(nuevaCantidadProducto).attr("nuevoStock", Number(respuesta["stock"]) - 1);
			$(nuevoPrecioProducto).val(respuesta["precio_venta"]);
			$(nuevoPrecioProducto).attr("precioReal", respuesta["precio_venta"]);
			listarProductos();
		}
	})
});

// MODIFICACIÓN DE LA DESCRIPCIÓN
$(document).on("change", ".nuevaDescripcionProducto", function() {
	sumarTotalPrecios();
	agregarImpuesto();
	listarProductos();
});

/*=============================================
MODIFICAR LA CANTIDAD
=============================================*/
$(".formularioVenta").on("change", "input.nuevaCantidadProducto", function() {
	var precio = $(this).parent().parent().children(".ingresoPrecio").children().children(".nuevoPrecioProducto");
	var precioFinal = $(this).val() * precio.attr("precioReal");
	precio.val(precioFinal);
	var nuevoStock = Number($(this).attr("stock")) - $(this).val();
	$(this).attr("nuevoStock", nuevoStock);
	if (Number($(this).val()) > Number($(this).attr("stock"))) {
		$(this).val(0);
		$(this).attr("nuevoStock", $(this).attr("stock"));
		var precioFinal = $(this).val() * precio.attr("precioReal");
		precio.val(precioFinal);
		sumarTotalPrecios();
		swal({
			title: "La cantidad supera el Stock",
			text: "¡Sólo hay " + $(this).attr("stock") + " unidades!",
			type: "error",
			confirmButtonText: "¡Cerrar!"
		});
		return;
	}
	sumarTotalPrecios();
	agregarImpuesto();
	listarProductos();
});

// MODIFICACIÓN DEL PRECIO
$(document).on("change", ".nuevoPrecioProducto", function() {
	var precio = $(this).val();
	$(this).attr("precioReal", precio);
	$(this).parent().parent().parent().children(".ingresoCantidad").children(".nuevaCantidadProducto").val(1);
	sumarTotalPrecios();
	agregarImpuesto();
	listarProductos();
});

/*=============================================
SUMAR TODOS LOS PRECIOS (FUNCIÓN CORREGIDA)
=============================================*/
function sumarTotalPrecios() {

	var precioItem = $(".nuevoPrecioProducto");
	var arraySumaPrecio = [];

	for (var i = 0; i < precioItem.length; i++) {
        var valorConFormato = $(precioItem[i]).val();
        // Se eliminan los puntos (.) de los miles para obtener un número limpio
        var valorSinPuntos = String(valorConFormato).replace(/\./g, '');
		arraySumaPrecio.push(Number(valorSinPuntos));
	}

	function sumaArrayPrecios(total, numero) {
		return total + numero;
	}

	var sumaTotalPrecio = arraySumaPrecio.reduce(sumaArrayPrecios, 0);

	$("#nuevoTotalVenta").val(sumaTotalPrecio);
	$("#totalVenta").val(sumaTotalPrecio);
	$("#nuevoTotalVenta").attr("total", sumaTotalPrecio);
}
/*=============================================
FUNCIÓN AGREGAR IMPUESTO
=============================================*/
function agregarImpuesto() {
	var impuesto = $("#nuevoImpuestoVenta").val();
	var precioTotal = $("#nuevoTotalVenta").attr("total");
	let descuento = $("#nuevoDescuentoVenta").val();
	let precioDescuento = Number(precioTotal * descuento / 100);
	let totalConDescuento = Number(precioTotal) - Number(precioDescuento);
	var precioImpuesto = Number(totalConDescuento * impuesto / 100);
	var totalConImpuesto = Number(precioImpuesto) + Number(totalConDescuento);
	$("#nuevoTotalVenta").val(totalConImpuesto);
	$("#totalVenta").val(totalConImpuesto);
	$("#nuevoPrecioImpuesto").val(precioImpuesto);
	$("#nuevoPrecioNeto").val(totalConDescuento);
	$("#nuevoPrecioDescuento").val(precioDescuento);
}

/*=============================================
CUANDO CAMBIA EL IMPUESTO
=============================================*/
$("#nuevoImpuestoVenta").change(function() {
	agregarImpuesto();
});
$("#nuevoDescuentoVenta").change(function() {
	agregarImpuesto();
});

/*=============================================
FORMATO AL PRECIO FINAL
=============================================*/
$("#nuevoTotalVenta").number(true);
$(".nuevoAbono").number(true);

/*=============================================
SELECCIONAR MÉTODO DE PAGO (CON VALIDACIÓN EN TIEMPO REAL)
=============================================*/
$(document).on("change", "#nuevoMetodoPago", function() {
    
    // CAMBIO: Verificar si estamos en editar venta o crear venta
    var esEditarVenta = $('#formEditarVenta').length > 0;

    // --- INICIO DE LA MODIFICACIÓN ---
	// 1. Creamos la estructura base del select
  var opcionesMediosPago = '<option value="">Seleccione medio de pago</option>';
    
    // Verificamos que 'listaMediosPago' exista y sea un array antes de usarla
    if (typeof listaMediosPago !== 'undefined' && Array.isArray(listaMediosPago)) {
        
        listaMediosPago.forEach(function(item, index){
            // El array 'listaMediosPago' contiene objetos, accedemos a la propiedad 'nombre'
            opcionesMediosPago += '<option value="'+item.nombre+'">'+item.nombre+'</option>';
        });
    }

    let medioPago = `
        <div class="input-group">
            <select class="form-control" id="nuevoMedioPago" name="nuevoMedioPago" required>
                ${opcionesMediosPago}
            </select>
        </div>
    `;

    // CAMBIO: Solo crear campos dinámicos si NO estamos editando
    // Al editar, se usa el campo fijo "nuevoAbono" en lugar de "nuevoValorEfectivo"
	if ($(this).val() == "Completo") {
        $('.divNuevoMetodoPago').html(medioPago);
        // Solo crear campo dinámico si NO estamos editando
        if (!esEditarVenta) {
            $(".cajasMetodoPago").empty();
            $(this).parent().parent().parent().children(".cajasMetodoPago").html('<div class="col-xs-6">' +
                '<div class="input-group">' +
                '<span class="input-group-addon"><i class="ion ion-social-usd"></i></span>' +
                '<input type="text" class="form-control nuevoValorEfectivo" value="' + $("#nuevoTotalVenta").val() + '" disabled>' +
                '</div>' +
                '</div>');
            $('.nuevoValorEfectivo').number(true);
        }
	} else if ($(this).val() == "Abono") {
        $('.divNuevoMetodoPago').html(medioPago);
        // Solo crear campo dinámico si NO estamos editando
        if (!esEditarVenta) {
            $(".cajasMetodoPago").empty();
            $(this).parent().parent().parent().children('.cajasMetodoPago').html('<div class="col-xs-6">' +
                '<div class="input-group">' +
                '<span class="input-group-addon"><i class="ion ion-social-usd"></i></span>' +
                '<input type="text" class="form-control nuevoValorEfectivo" name="nuevoValorEfectivo" placeholder="Ingrese el abono" required>' +
                '</div>' +
                '</div>');
            $('.nuevoValorEfectivo').number(true);

            // --- INICIO DE LA VALIDACIÓN AÑADIDA ---
            $(".formularioVenta").off("keyup change", "input.nuevoValorEfectivo").on("keyup change", "input.nuevoValorEfectivo", function(){
                
                // Se leen los valores y se eliminan los puntos de miles
                var abono = Number($(this).val().replace(/\./g, ''));
                var totalVenta = Number($("#totalVenta").val().replace(/\./g, ''));

                // 1. VERIFICA QUE EL ABONO NO SEA NEGATIVO
                if(abono < 0){
                    $(this).val(0);
                    swal({ title: "Error", text: "El abono no puede ser negativo", type: "error", confirmButtonText: "Cerrar" });
                    return;
                }

                // 2. VERIFICA QUE EL ABONO NO SUPERE EL TOTAL
                if(abono > totalVenta){
                    // Se corrige el valor al máximo permitido y se le vuelve a dar formato
                    $(this).val(totalVenta).number(true, 0, ',', '.');
                    swal({ title: "Error", text: "El abono no puede ser mayor que el total", type: "error", confirmButtonText: "Cerrar" });
                }
            });
            // --- FIN DE LA VALIDACIÓN AÑADIDA ---
        }
	} else {
        $(".cajasMetodoPago").empty();
		$('.divNuevoMetodoPago').empty();
        // Se desactiva la validación si se cambia a "Se Debe"
        $(".formularioVenta").off("keyup change", "input.nuevoValorEfectivo");
	}
});

/*=============================================
LISTAR TODOS LOS PRODUCTOS (FUNCIÓN CORREGIDA)
=============================================*/
function listarProductos() {
	var listaProductos = [];
	var descripcion = $(".nuevaDescripcionProducto");
	var cantidad = $(".nuevaCantidadProducto");
	var precio = $(".nuevoPrecioProducto");
	for (var i = 0; i < descripcion.length; i++) {

        // --- INICIO DE LA CORRECCIÓN ---
        // Se obtiene el valor del total del producto como texto (ej: "2.729.860")
        var totalConFormato = $(precio[i]).val();
        // Se eliminan los puntos para enviar un número limpio al servidor (ej: "2729860")
        var totalSinPuntos = String(totalConFormato).replace(/\./g, '');
        // --- FIN DE LA CORRECCIÓN ---

		if ($(descripcion[i]).attr("idProducto") != undefined) {
			listaProductos.push({
				"id": $(descripcion[i]).attr("idProducto"),
				"descripcion": $(descripcion[i]).val(),
				"cantidad": $(cantidad[i]).val(),
				"stock": $(cantidad[i]).attr("nuevoStock"),
				"precio": $(precio[i]).attr("precioReal"),
				"total": totalSinPuntos // Se envía el total sin puntos
			})
		} else {
			listaProductos.push({
				"id": "libre",
				"descripcion": $(descripcion[i]).val(),
				"cantidad": $(cantidad[i]).val(),
				"stock": 0,
				"precio": $(precio[i]).attr("precioReal"),
				"total": totalSinPuntos // Se envía el total sin puntos
			})
		}
	}
	$("#listaProductos").val(JSON.stringify(listaProductos));
}
/*=============================================
BOTON EDITAR VENTA
=============================================*/
$(".tablas").on("click", ".btnEditarVenta", function() {
	var idVenta = $(this).attr("idVenta");
	window.location = "index.php?ruta=editar-venta&idVenta=" + idVenta;
});

/*=============================================
FUNCIÓN PARA DESACTIVAR LOS BOTONES AGREGAR CUANDO EL PRODUCTO YA HABÍA SIDO SELECCIONADO EN LA CARPETA
=============================================*/
function quitarAgregarProducto() {
	var idProductos = $(".quitarProducto");
	var botonesTabla = $(".tablaVentas tbody button.agregarProducto");
	for (var i = 0; i < idProductos.length; i++) {
		var boton = $(idProductos[i]).attr("idProducto");
		for (var j = 0; j < botonesTabla.length; j++) {
			if ($(botonesTabla[j]).attr("idProducto") == boton) {
				$(botonesTabla[j]).removeClass("btn-primary agregarProducto");
				$(botonesTabla[j]).addClass("btn-default");
			}
		}
	}
}

/*=============================================
CADA VEZ QUE CARGUE LA TABLA CUANDO NAVEGAMOS EN ELLA EJECUTAR LA FUNCIÓN:
=============================================*/
$('.tablaVentas').on('draw.dt', function() {
	quitarAgregarProducto();
});

/*=============================================
BORRAR Venta (CORREGIDO CON AJAX)
=============================================*/
$(".tablas").on("click", ".btnEliminarVenta", function(){

	var idVenta = $(this).attr("idVenta");

	swal({
		title: '¿Está seguro de borrar la venta?',
		text: "¡Si no lo está puede cancelar la acción!",
		type: 'warning',
		showCancelButton: true,
		confirmButtonColor: '#3085d6',
		cancelButtonColor: '#d33',
		cancelButtonText: 'Cancelar',
		confirmButtonText: '¡Sí, borrar venta!'
	}).then(function(result){
		if (result.value) {

			// Creamos los datos que se enviarán al servidor
			var datos = new FormData();
            datos.append("idVentaBorrar", idVenta);

			// Hacemos la llamada AJAX para borrar en segundo plano
            $.ajax({
                url: "ajax/ventas.ajax.php",
                method: "POST",
                data: datos,
                cache: false,
                contentType: false,
                processData: false,
                success: function(respuesta){
					// Si el servidor responde "ok", mostramos la alerta de éxito
					// y recargamos la página para ver el cambio
                    if(respuesta == "ok"){
                        swal({
                            type: "success",
                            title: "La venta ha sido borrada correctamente",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        }).then(function(result){
                            if(result.value){
                                window.location = "ventas";
                            }
                        });
                    }
                }
            });
		}
	})
});

/*=============================================
IMPRIMIR FACTURA
=============================================*/
$(".tablas").on("click", ".btnImprimirFactura", function() {
	var codigoVenta = $(this).attr("codigoVenta");
	window.open("extensiones/tcpdf/pdf/factura.php?codigo=" + codigoVenta, "_blank");
});
/*=============================================
ABRIR ARCHIVO XML EN NUEVA PESTAÑA
=============================================*/
$(".abrirXML").click(function() {
	var archivo = $(this).attr("archivo");
	window.open(archivo, "_blank");
});

// Abonos
$(document).on("click", ".btnAbonar", function() {
	var idVenta = $(this).attr("idVenta");
	var idUsu = $(this).attr("idUsuarioAbo");
	var datos = new FormData();
	datos.append("idVenta", idVenta);
	$.ajax({
		url: "ajax/ventas.ajax.php",
		method: "POST",
		data: datos,
		cache: false,
		contentType: false,
		processData: false,
		dataType: "json",
		success: function(respuesta) {
			var restante = Number(respuesta["total"]) - Number(respuesta["abono"]);
			$(".dinRestante").val(restante);
			$('.dinRestante').number(true);
			$(".idVentaAbo").val(respuesta["id"]);
			$(".idUsuarioAbo").val(idUsu);
		}
	});
});

$(function(){

    /**
     * Función universal para aplicar un filtro a la URL actual y recargar la página.
     * Detecta la página (ruta) actual y mantiene los otros filtros activos.
     * @param {string} nombreFiltro - El nombre del parámetro en la URL (ej: "medioPago").
     * @param {string} valorFiltro - El valor seleccionado en el filtro.
     */
    function aplicarFiltroURL(nombreFiltro, valorFiltro) {
        
        // 1. Lee todos los parámetros que ya existen en la URL
        const urlParams = new URLSearchParams(window.location.search);
        
        // 2. Obtiene la ruta actual para no perder la página
        const rutaActual = urlParams.get('ruta');

        // 3. Si se selecciona un valor, lo establece. Si se selecciona "Todos" (valor vacío), lo elimina.
        if (valorFiltro) {
            urlParams.set(nombreFiltro, valorFiltro);
        } else {
            urlParams.delete(nombreFiltro);
        }

        // 4. Se asegura de que el parámetro 'ruta' siempre esté presente
        if (rutaActual) {
            urlParams.set('ruta', rutaActual);
        }

        // 5. Redirige a la página actual con los filtros nuevos o modificados
        window.location.search = urlParams.toString();
    }

    // --- MANEJADORES DE EVENTOS PARA LOS FILTROS ---

    // Evento para el filtro de Medio de Pago
    $('#filter-medioPago').on('change', function(){
        aplicarFiltroURL('medioPago', $(this).val());
    });

    // Evento para el filtro de Forma de Pago
    $('#filter-formaPago').on('change', function(){
        aplicarFiltroURL('formaPago', $(this).val());
    });

});

/*=============================================
CUANDO CAMBIA EL IMPUESTO (CON VALIDACIÓN)
=============================================*/
$("#nuevoImpuestoVenta").change(function() {

    // --- INICIO DE LA VALIDACIÓN ---
    var impuesto = $(this).val();

    // Validar que no sea mayor a 40
    if (impuesto > 40) {
        swal({
            title: "Error en el impuesto",
            text: "¡El impuesto no puede ser mayor al 40%!",
            type: "error",
            confirmButtonText: "¡Cerrar!"
        });
        // Corregimos el valor al máximo permitido
        $(this).val(40);
    }

    // Validar que no sea negativo
    if (impuesto < 0) {
        swal({
            title: "Error en el impuesto",
            text: "¡El impuesto no puede ser negativo!",
            type: "error",
            confirmButtonText: "¡Cerrar!"
        });
        // Corregimos el valor al mínimo permitido
        $(this).val(0);
    }
    // --- FIN DE LA VALIDACIÓN ---

	agregarImpuesto();
});

/*=============================================
CUANDO CAMBIA EL DESCUENTO (CON VALIDACIÓN)
=============================================*/
$("#nuevoDescuentoVenta").change(function() {

    // --- INICIO DE LA CORRECCIÓN ---
    var descuento = $(this).val();

    // Validar que no sea mayor a 50
    if (descuento > 80) {
        swal({
            title: "Error en el descuento",
            text: "¡El descuento no puede ser mayor al 80%!",
            type: "error",
            confirmButtonText: "¡Cerrar!"
        });
        // Corregimos el valor al máximo permitido
        $(this).val(80);
    }

    // Validar que no sea negativo
    if (descuento < 0) {
        swal({
            title: "Error en el descuento",
            text: "¡El descuento no puede ser negativo!",
            type: "error",
            confirmButtonText: "¡Cerrar!"
        });
        // Corregimos el valor al mínimo permitido
        $(this).val(0);
    }
    // --- FIN DE LA CORRECCIÓN ---

	agregarImpuesto();
});

/*=============================================
GUARDAR CAMBIOS DE LA VENTA (EDITAR)
=============================================*/
$('#formEditarVenta').on('submit', function(event) {

    event.preventDefault(); // Previene la recarga de la página

    listarProductos(); // Actualiza la lista de productos antes de enviar

    if ($("#listaProductos").val() === "" || $("#listaProductos").val() === "[]") {
        swal({
            type: 'error',
            title: 'No hay productos',
            text: 'Debes agregar al menos un producto a la venta.'
        });
        return;

    }

    // CAMBIO: Validar abono antes de enviar
    var abono = Number($("#nuevoAbono").val().replace(/\./g, ''));
    var totalVenta = Number($("#totalVenta").val().replace(/\./g, ''));
    
    if(abono > totalVenta) {
        swal({
            type: 'error',
            title: 'Error',
            text: 'El abono no puede ser mayor que el total de la venta.'
        });
        return;
    }

    var datos = $(this).serialize();

    $.ajax({
        url: "ajax/ventas.ajax.php", // Llama al archivo PHP
        method: "POST",
        data: datos,
        success: function(respuesta) {

            if (respuesta.trim() === "ok") {
                swal({
                    type: 'success',
                    title: '¡Venta editada correctamente!',
                    showConfirmButton: true,
                    confirmButtonText: "Cerrar"
                }).then(function(result) {
                    if (result.value) {
                        window.location = "ventas";
                    }
                });
            } else {
                swal({
                    type: 'error',
                    title: 'Error al editar la venta',
                    text: 'Respuesta del servidor: ' + respuesta
                });
            }
        }
    });
});

/*=============================================
VALIDAR Y ACTUALIZAR ABONO EN EDICIÓN DE VENTA
=============================================*/
$(document).ready(function() {
    // Solo ejecutar en la página de editar venta
    if($('#formEditarVenta').length > 0) {
        // Formatear campo de abono
        $('#nuevoAbono').number(true, 0, ',', '.');
        
        // Función para actualizar saldo pendiente
        function actualizarSaldoPendiente() {
            var abono = Number($("#nuevoAbono").val().replace(/\./g, '')) || 0;
            var totalVenta = Number($("#totalVenta").val().replace(/\./g, '')) || 0;
            var saldoPendiente = totalVenta - abono;
            
            if(saldoPendiente < 0) {
                saldoPendiente = 0;
            }
            
            $("#saldoPendiente").text("Saldo pendiente: $" + saldoPendiente.toLocaleString('es-CO'));
            
            // Cambiar color según el saldo
            if(saldoPendiente === 0) {
                $("#saldoPendiente").removeClass("text-danger text-warning").addClass("text-success");
            } else if(saldoPendiente > 0 && saldoPendiente < totalVenta) {
                $("#saldoPendiente").removeClass("text-success text-danger").addClass("text-warning");
            } else {
                $("#saldoPendiente").removeClass("text-success text-warning").addClass("text-danger");
            }
        }
        
        // Validar abono cuando cambia
        $('#nuevoAbono').on('keyup change', function() {
            var abono = Number($(this).val().replace(/\./g, ''));
            var totalVenta = Number($("#totalVenta").val().replace(/\./g, ''));
            
            // Validar que no sea negativo
            if(abono < 0) {
                $(this).val(0).number(true, 0, ',', '.');
                swal({ title: "Error", text: "El abono no puede ser negativo", type: "error", confirmButtonText: "Cerrar" });
                actualizarSaldoPendiente();
                return;
            }
            
            // Validar que no exceda el total
            if(abono > totalVenta) {
                $(this).val(totalVenta).number(true, 0, ',', '.');
                swal({ title: "Error", text: "El abono no puede ser mayor que el total", type: "error", confirmButtonText: "Cerrar" });
            }
            
            actualizarSaldoPendiente();
        });
        
        // Actualizar saldo cuando cambia el total
        $('#totalVenta, #nuevoImpuestoVenta, #nuevoDescuentoVenta').on('keyup change', function() {
            actualizarSaldoPendiente();
        });
        
        // Actualizar saldo inicial
        actualizarSaldoPendiente();
    }
});

/*=============================================
VER DETALLE DE VENTA
=============================================*/
$(document).on("click", ".btnVerDetalle", function() {
    var idVenta = $(this).attr("idVenta");
    var codigoVenta = $(this).attr("codigoVenta");

    // Mostrar modal
    $("#modalDetalleVenta").modal("show");

    // Limpiar contenido anterior
    $("#cuerpoProductosDetalle").empty();
    $("#cuerpoHistorialAbonos").empty();
    $("#sinProductos").hide();
    $("#sinHistorial").hide();

    // Mostrar loading
    $("#cuerpoProductosDetalle").html('<tr><td colspan="4" class="text-center"><i class="fa fa-spinner fa-spin"></i> Cargando productos...</td></tr>');
    $("#cuerpoHistorialAbonos").html('<tr><td colspan="6" class="text-center"><i class="fa fa-spinner fa-spin"></i> Cargando historial...</td></tr>');

    // Obtener datos de la venta
    var datos = new FormData();
    datos.append("accion", "obtener_detalle_venta");
    datos.append("idVenta", idVenta);

    $.ajax({
        url: "ajax/ventas-detalle.ajax.php",
        method: "POST",
        data: datos,
        cache: false,
        contentType: false,
        processData: false,
        dataType: "json",
        success: function(respuesta) {
            if (respuesta.success) {
                // Llenar información de la venta
                $("#codigoFacturaDetalle").text(respuesta.venta.codigo);
                $("#clienteDetalle").text(respuesta.cliente.nombre);
                $("#vendedorDetalle").text(respuesta.vendedor.nombre);
                $("#fechaDetalle").text(respuesta.venta.fecha_venta);
                $("#formaPagoDetalle").text(respuesta.venta.metodo_pago);
                $("#medioPagoDetalle").text(respuesta.venta.medio_pago);

                // Llenar información financiera
                $("#subtotalDetalle").text("$ " + number_format(respuesta.venta.neto, 0, ',', '.'));
                $("#impuestosDetalle").text("$ " + number_format(respuesta.venta.impuesto, 0, ',', '.'));
                $("#descuentoDetalle").text("$ " + number_format(respuesta.venta.descuento || 0, 0, ',', '.'));
                $("#totalDetalle").text("$ " + number_format(respuesta.venta.total, 0, ',', '.'));
                $("#abonoDetalle").text("$ " + number_format(respuesta.venta.abono || 0, 0, ',', '.'));

                // Calcular saldo pendiente
                var saldoPendiente = parseFloat(respuesta.venta.total) - parseFloat(respuesta.venta.abono || 0);
                $("#saldoDetalle").text("$ " + number_format(saldoPendiente, 0, ',', '.'));

                // Llenar detalles adicionales
                var detalleNota = respuesta.venta.detalle || '';
                var pagoDetalle = respuesta.venta.pago || '';

                if (detalleNota.trim() !== '') {
                    $("#detalleNotaTexto").removeClass('text-muted').text(detalleNota);
                } else {
                    $("#detalleNotaTexto").addClass('text-muted').text('Sin detalles adicionales');
                }

                if (pagoDetalle.trim() !== '') {
                    $("#pagoDetalleTexto").removeClass('text-muted').text(pagoDetalle);
                } else {
                    $("#pagoDetalleTexto").addClass('text-muted').text('Sin información de pago');
                }

                // Llenar productos
                if (respuesta.productos && respuesta.productos.length > 0) {
                    $("#cuerpoProductosDetalle").empty();

                    respuesta.productos.forEach(function(producto, index) {
                        var fila = '<tr>' +
                            '<td>' + (index + 1) + '</td>' +
                            '<td>' + producto.descripcion + '</td>' +
                            '<td class="text-center">' + producto.cantidad + '</td>' +
                            '<td class="text-right">$ ' + number_format(producto.total, 0, ',', '.') + '</td>' +
                            '</tr>';
                        $("#cuerpoProductosDetalle").append(fila);
                    });
                } else {
                    $("#cuerpoProductosDetalle").empty();
                    $("#sinProductos").show();
                }

                // Llenar historial de abonos
                if (respuesta.historial_abonos && respuesta.historial_abonos.length > 0) {
                    $("#cuerpoHistorialAbonos").empty();

                    respuesta.historial_abonos.forEach(function(abono, index) {
                        var estadoClass = abono.observaciones === "Pago completo" ? "success" : "warning";
                        var estadoText = abono.observaciones === "Pago completo" ? "Completo" : "Parcial";

                        var fila = '<tr class="' + estadoClass + '">' +
                            '<td>' + (index + 1) + '</td>' +
                            '<td>' + abono.fecha_abono + '</td>' +
                            '<td class="text-right">$ ' + number_format(abono.monto_abono, 0, ',', '.') + '</td>' +
                            '<td>' + abono.nombre_vendedor_abono + '</td>' +
                            '<td>' + abono.medio_pago + '</td>' +
                            '<td><span class="label label-' + (estadoClass === 'success' ? 'success' : 'warning') + '">' + estadoText + '</span></td>' +
                            '</tr>';
                        $("#cuerpoHistorialAbonos").append(fila);
                    });
                } else {
                    $("#cuerpoHistorialAbonos").empty();
                    $("#sinHistorial").show();
                }

            } else {
                swal({
                    type: 'error',
                    title: 'Error',
                    text: respuesta.error || 'Error al cargar el detalle de la venta'
                });
                $("#modalDetalleVenta").modal("hide");
            }
        },
        error: function(xhr, status, error) {
swal({
                type: 'error',
                title: 'Error de conexión',
                text: 'No se pudo cargar el detalle de la venta'
            });
            $("#modalDetalleVenta").modal("hide");
        }
    });
});

/*=============================================
IMPRIMIR DETALLE DE VENTA
=============================================*/
$(document).on("click", "#btnImprimirDetalle", function() {
    var codigoVenta = $("#codigoFacturaDetalle").text();
    if (codigoVenta) {
        window.open("extensiones/tcpdf/pdf/factura.php?codigo=" + codigoVenta, "_blank");
    }
});

/*=============================================
FUNCIÓN AUXILIAR PARA FORMATEAR NÚMEROS
=============================================*/
function number_format(number, decimals, dec_point, thousands_sep) {
    number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
    var n = !isFinite(+number) ? 0 : +number,
        prec = 0, // Forzar 0 decimales
        sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
        dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
        s = '',
        toFixedFix = function (n, prec) {
            var k = Math.pow(10, prec);
            return '' + Math.round(n * k) / k;
        };
    s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
    if (s[0].length > 3) {
        s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
    }
    if ((s[1] || '').length < prec) {
        s[1] = s[1] || '';
        s[1] += new Array(prec - s[1].length + 1).join('0');
    }
    return s.join(dec);
}

/*=============================================
ACTUALIZAR LISTA DE CLIENTES DESPUÉS DE CREAR UNO NUEVO
=============================================*/
function actualizarListaClientes(clienteCreado) {
// Seleccionar automáticamente el cliente recién creado
    seleccionarCliente(clienteCreado);
}

/*=============================================
AUTOCOMPLETADO DE CLIENTES EN CREAR VENTA
=============================================*/
$(document).ready(function() {

    // Variables para el autocompletado
    var timeoutBusqueda;
    var clientesCargados = [];

    // Evento de escritura en el campo de búsqueda
    $('#buscarCliente').on('input', function() {
        var busqueda = $(this).val().trim();

        // Limpiar timeout anterior
        clearTimeout(timeoutBusqueda);

        // Si está vacío, ocultar sugerencias
        if(busqueda.length === 0) {
            $('#sugerenciasClientes').hide();
            $('#idClienteSeleccionado').val('');
            return;
        }

        // Buscar después de 300ms de inactividad
        timeoutBusqueda = setTimeout(function() {
            buscarClientes(busqueda);
        }, 300);
    });

    // Ocultar sugerencias al hacer clic fuera
    $(document).on('click', function(e) {
        if(!$(e.target).closest('#buscarCliente, #sugerenciasClientes').length) {
            $('#sugerenciasClientes').hide();
        }
    });

    // Función para buscar clientes
    function buscarClientes(busqueda) {
        $.ajax({
            url: 'ajax/buscar-clientes.ajax.php',
            method: 'POST',
            data: { buscarCliente: busqueda },
            dataType: 'json',
            success: function(clientes) {
                mostrarSugerencias(clientes);
            },
            error: function() {
}
        });
    }

    // Función para mostrar sugerencias
    function mostrarSugerencias(clientes) {
        // Solo ejecutar en páginas de ventas, no en crear-cotizacion
        if (window.location.href.indexOf('crear-cotizacion') !== -1) {
            return;
        }
        
        var $sugerencias = $('#sugerenciasClientes');

        if(clientes.length === 0) {
            $sugerencias.html('<div class="sugerencia-cliente">No se encontraron clientes</div>').show();
            return;
        }

        var html = '';
        clientes.forEach(function(cliente) {
            html += '<div class="sugerencia-cliente" data-cliente=\'' + JSON.stringify(cliente) + '\'>';
            html += '<div class="nombre">' + cliente.nombre + '</div>';
            html += '<div class="documento">Documento: ' + cliente.documento + '</div>';
            if(cliente.email) {
                html += '<div class="email">' + cliente.email + '</div>';
            }
            html += '</div>';
        });

        $sugerencias.html(html).show();

        // Evento de clic en sugerencia
        $sugerencias.find('.sugerencia-cliente').on('click', function() {
            var cliente = JSON.parse($(this).attr('data-cliente'));
            seleccionarCliente(cliente);
        });
    }

    // Función para seleccionar un cliente
    function seleccionarCliente(cliente) {
        $('#buscarCliente').val(cliente.nombre + ' - ' + cliente.documento);
        $('#idClienteSeleccionado').val(cliente.id);
        $('#sugerenciasClientes').hide();

        // Trigger del evento change para actualizar la interfaz
        $('#idClienteSeleccionado').trigger('change');
}

    // Función global para actualizar lista de clientes (usada desde el controlador)
    window.actualizarListaClientes = function(clienteCreado) {
seleccionarCliente(clienteCreado);
};
});