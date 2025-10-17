$(".tablas").on("click", ".btnImprimirCotizacion", function () {

	var codigoVenta = $(this).attr("codigoVenta");

	window.open("pdf/cotizacion.php?codigo=" + codigoVenta, "_blank");

})

$(".tablas").on("click", ".btnEditarCotizacion", function () {

	var idVenta = $(this).attr("idVenta");

	window.location = "index.php?ruta=editar-cotizacion&idCotizacion=" + idVenta;


})

$(".tablas").on("click", ".btnEliminarCotizacion", function () {

	var idVenta = $(this).attr("idCotizacion");

	swal({
		title: '¿Está seguro de borrar la cotizacion?',
		text: "¡Si no lo está puede cancelar la accíón!",
		type: 'warning',
		showCancelButton: true,
		confirmButtonColor: '#3085d6',
		cancelButtonColor: '#d33',
		cancelButtonText: 'Cancelar',
		confirmButtonText: 'Si, borrar cotizacion!'
	}).then(function (result) {
		if (result.value) {

			window.location = "index.php?ruta=cotizacion&idCotizacion=" + idVenta;
		}

	})

})

$('#filter-medioPago-contabilidad').on('change', function () {
	let val = $(this).val();
	if (val === '') {
		window.location = 'contabilidad'
	} else {
		window.location = `index.php?ruta=contabilidad&medioPago=${val}`;
	}
})

$('#filter-formaPago-contabilidad').on('change', function () {
	let val = $(this).val();
	if (val === '') {
		window.location = 'contabilidad'
	} else {
		window.location = `index.php?ruta=contabilidad&formaPago=${val}`;
	}
})




$(".tablas").on("click", ".btnEditarGasto", function () {

	var idVenta = $(this).attr("idGasto");

	window.location = "index.php?ruta=editar-gasto&id=" + idVenta;


})

$(".tablas").on("click", ".btnEliminarGasto", function () {

	var idVenta = $(this).attr("idGasto");

	swal({
		title: '¿Está seguro de borrar la gasto?',
		text: "¡Si no lo está puede cancelar la accíón!",
		type: 'warning',
		showCancelButton: true,
		confirmButtonColor: '#3085d6',
		cancelButtonColor: '#d33',
		cancelButtonText: 'Cancelar',
		confirmButtonText: 'Si, borrar gasto!'
	}).then(function (result) {
		if (result.value) {

			window.location = "index.php?ruta=gastos&id=" + idVenta;
		}

	})

})


$(".tablas").on("click", ".btnEditarEntrada", function () {

	var idVenta = $(this).attr("idEntrada");

	window.location = "index.php?ruta=editar-entrada&id=" + idVenta;


});
// CORRECCIÓN: Ahora busca la clase específica ".tablaEntradas"
$(".tablaEntradas").on("click", ".btnEditarEntrada", function () {
    var idEntrada = $(this).attr("idEntrada");
    window.location = "index.php?ruta=editar-entrada&id=" + idEntrada;
});

$(".tablaEntradas").on("click", ".btnEliminarEntrada", function () {
    var idEntrada = $(this).attr("idEntrada");
    swal({
        title: '¿Está seguro de borrar la entrada?',
        text: "¡Si no lo está puede cancelar la acción!",
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        cancelButtonText: 'Cancelar',
        confirmButtonText: '¡Sí, borrar entrada!'
    }).then(function (result) {
        if (result.value) {
            window.location = "index.php?ruta=entradas&id=" + idEntrada;
        }
    });
});
// INICIALIZACIÓN DE LA TABLA DE ENTRADAS CON SERVER-SIDE
$(document).ready(function() {
    // Verificamos que estemos en la página de entradas
    if ($('.tablaEntradas').length) {

        // Leemos los filtros desde los atributos data de la tabla
        var tabla = $('.tablaEntradas');
        var fechaInicial = tabla.data('fecha-inicial');
        var fechaFinal = tabla.data('fecha-final');
        var medioPago = tabla.data('medio-pago');

        $('.tablaEntradas').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": "ajax/datatable-entradas.ajax.php",
                "type": "POST",
                "data": function(d) {
                    // Añadimos nuestros filtros personalizados a la petición AJAX
                    d.fechaInicial = fechaInicial;
                    d.fechaFinal = fechaFinal;
                    d.medioPago = medioPago;
                }
            },
            "language": {
                // ... Aquí van tus traducciones al español ...
                "sProcessing": "Procesando...",
                "sLengthMenu": "Mostrar _MENU_ registros",
                "sZeroRecords": "No se encontraron resultados",
                "sEmptyTable": "Ningún dato disponible en esta tabla",
                "sInfo": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_",
                "sInfoEmpty": "Mostrando registros del 0 al 0 de un total de 0",
                "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
                "sSearch": "Buscar:",
                "oPaginate": { "sNext": "Siguiente", "sPrevious": "Anterior" }
            }
        });
    }

    // Un pequeño fix para tu daterangepicker. El ID no coincidía.
    // Tu HTML tiene 'daterange-btn-entrada' pero tu JS buscaba 'daterange-btn3'
    $('#daterange-btn-entrada').daterangepicker({
        // ... la configuración de tu daterangepicker se mantiene igual ...
        ranges: {
            'Hoy': [moment(), moment()],
            'Ayer': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            'Últimos 7 días': [moment().subtract(6, 'days'), moment()],
            'Últimos 30 días': [moment().subtract(29, 'days'), moment()],
            'Este mes': [moment().startOf('month'), moment().endOf('month')],
            'Mes anterior': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        },
        startDate: moment(),
        endDate: moment()
    },
    function (start, end) {
        $('#daterange-btn-entrada span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
        var fechaInicial = start.format('YYYY-MM-DD');
        var fechaFinal = end.format('YYYY-MM-DD');
        window.location = "index.php?ruta=entradas&fechaInicial=" + fechaInicial + "&fechaFinal=" + fechaFinal;
    });

    // Cancelar Daterangepicker
    $('#daterange-btn-entrada').on('cancel.daterangepicker', function(ev, picker) {
        window.location = "entradas";
    });

});
// INICIALIZACIÓN DE LA TABLA PRINCIPAL DE CONTABILIDAD (VERSIÓN FINAL)
$(document).ready(function() {
    if ($('.tablaContabilidad').length) {

        $('.tablaContabilidad').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": "ajax/datatable-contabilidad.ajax.php",
                "type": "POST",
                "data": function(d) {
                    // Lee los filtros directamente de los atributos de la tabla en cada petición
                    d.fechaInicial = $('.tablaContabilidad').attr('data-fecha-inicial');
                    d.fechaFinal = $('.tablaContabilidad').attr('data-fecha-final');
                    d.medioPago = $('.tablaContabilidad').attr('data-medio-pago');
                    d.formaPago = $('.tablaContabilidad').attr('data-forma-pago');
                }
            },
            "language": { 
                "sProcessing":     "Procesando...",
                "sLengthMenu":     "Mostrar _MENU_ registros",
                "sZeroRecords":    "No se encontraron resultados",
                "sEmptyTable":     "Ningún dato disponible en esta tabla",
                "sInfo":           "Mostrando registros del _START_ al _END_ de un total de _TOTAL_",
                "sInfoEmpty":      "Mostrando registros del 0 al 0 de un total de 0",
                "sInfoFiltered":   "(filtrado de un total de _MAX_ registros)",
                "sSearch":         "Buscar:",
                "oPaginate": { "sNext": "Siguiente", "sPrevious": "Anterior" }
             }
        });
    }
});