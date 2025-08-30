
$(document).ready(function() {

    /*=============================================
    FUNCIÓN GENÉRICA PARA FILTROS DE FECHA (BASADA EN TU CÓDIGO)
    =============================================*/
    function activarDateRangePicker(idBoton, ruta, localStorageKey) {

        // Si el botón con el ID específico existe en la página actual...
        if ($('#' + idBoton).length) {

            // Se lee el rango guardado para mantener el estado del botón
            if (localStorage.getItem(localStorageKey) != null) {
                $('#' + idBoton + ' span').html(localStorage.getItem(localStorageKey));
            } else {
                $('#' + idBoton + ' span').html('<i class="fa fa-calendar"></i> Rango de fecha');
            }

            // Se inicializa el calendario en el botón
            $('#' + idBoton).daterangepicker({
                ranges: {
                    'Hoy'           : [moment(), moment()],
                    'Ayer'          : [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Últimos 7 días'  : [moment().subtract(6, 'days'), moment()],
                    'Últimos 30 días': [moment().subtract(29, 'days'), moment()],
                    'Este mes'      : [moment().startOf('month'), moment().endOf('month')],
                    'Mes anterior'    : [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                },
                startDate: moment(),
                endDate: moment()
            },
            function(start, end) {
                var capturarRango = start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY');
                $('#' + idBoton + ' span').html(capturarRango);
                
                var fechaInicial = start.format('YYYY-MM-DD');
                var fechaFinal = end.format('YYYY-MM-DD');

                localStorage.setItem(localStorageKey, capturarRango);
                window.location = "index.php?ruta=" + ruta + "&fechaInicial=" + fechaInicial + "&fechaFinal=" + fechaFinal;
            });

            // Se maneja el botón de cancelar
            $('#' + idBoton).on('cancel.daterangepicker', function() {
                localStorage.removeItem(localStorageKey);
                window.location = ruta;
            });

            // Se captura el clic en los rangos predefinidos (como "Hoy")
            // Este es el bloque que te funcionó, ahora adaptado para ser genérico
            $('.daterangepicker').on('click', '.ranges li', function() {
                if ($('#' + idBoton).is(':visible')) { // Se asegura de que solo afecte al calendario activo
                    var textoOpcion = $(this).text();
                    var fechaInicial, fechaFinal;
                    
                    if (textoOpcion == "Hoy") {
                        var d = new Date();
                        fechaInicial = moment(d).format('YYYY-MM-DD');
                        fechaFinal = moment(d).format('YYYY-MM-DD');
                        localStorage.setItem(localStorageKey, "Hoy");
                        window.location = "index.php?ruta=" + ruta + "&fechaInicial=" + fechaInicial + "&fechaFinal=" + fechaFinal;
                    }
                }
            });
        }
    }

    // --- SE LLAMA A LA FUNCIÓN PARA CADA PÁGINA ---
    activarDateRangePicker('daterange-btn', 'ventas', 'capturarRangoVentas');
    activarDateRangePicker('daterange-btn-reportes', 'reportes', 'capturarRangoReportes');
    activarDateRangePicker('daterange-btn-gastos', 'gastos', 'capturarRangoGastos');
    activarDateRangePicker('daterange-btn-entrada', 'entradas', 'capturarRangoEntradas');
    activarDateRangePicker('daterange-btn-detallado', 'reporte-detallado', 'capturarRangoDetallado');
    activarDateRangePicker('daterange-btn-contabilidad', 'contabilidad', 'capturarRangoContabilidad');

});