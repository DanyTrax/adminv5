/*=============================================
CONFIGURACIÓN DE ZONA HORARIA PARA MOMENT.JS
=============================================*/

$(document).ready(function() {
    
    // Configurar moment.js para usar zona horaria de Bogotá
    if (typeof moment !== 'undefined' && typeof moment.tz !== 'undefined') {
        moment.tz.setDefault('America/Bogota');
    }
    
    // Función para obtener fecha actual en zona horaria de Bogotá
    function getCurrentDateBogota() {
        if (typeof moment.tz !== 'undefined') {
            return moment().tz('America/Bogota');
        } else {
            return moment();
        }
    }
    
    // Función para formatear fecha en zona horaria de Bogotá
    function formatDateBogota(date, format = 'YYYY-MM-DD') {
        if (typeof moment.tz !== 'undefined') {
            return moment(date).tz('America/Bogota').format(format);
        } else {
            return moment(date).format(format);
        }
    }
    
    // Función para obtener rango de fechas en zona horaria de Bogotá
    function getDateRangeBogota(range) {
        var today = getCurrentDateBogota();
        
        switch(range) {
            case 'hoy':
                return {
                    start: today.format('YYYY-MM-DD'),
                    end: today.format('YYYY-MM-DD')
                };
            case 'ayer':
                var yesterday = today.clone().subtract(1, 'day');
                return {
                    start: yesterday.format('YYYY-MM-DD'),
                    end: yesterday.format('YYYY-MM-DD')
                };
            case 'ultimos_7_dias':
                return {
                    start: today.clone().subtract(6, 'days').format('YYYY-MM-DD'),
                    end: today.format('YYYY-MM-DD')
                };
            case 'ultimos_30_dias':
                return {
                    start: today.clone().subtract(29, 'days').format('YYYY-MM-DD'),
                    end: today.format('YYYY-MM-DD')
                };
            case 'este_mes':
                return {
                    start: today.clone().startOf('month').format('YYYY-MM-DD'),
                    end: today.clone().endOf('month').format('YYYY-MM-DD')
                };
            case 'mes_anterior':
                var lastMonth = today.clone().subtract(1, 'month');
                return {
                    start: lastMonth.startOf('month').format('YYYY-MM-DD'),
                    end: lastMonth.endOf('month').format('YYYY-MM-DD')
                };
            default:
                return {
                    start: today.format('YYYY-MM-DD'),
                    end: today.format('YYYY-MM-DD')
                };
        }
    }
    
    // Actualizar todos los filtros de fecha para usar zona horaria de Bogotá
    function updateDateFilters() {
        // Actualizar botones de filtro de fecha
        $('.daterangepicker').each(function() {
            var $this = $(this);
            var ranges = {
                'Hoy': [getCurrentDateBogota(), getCurrentDateBogota()],
                'Ayer': [getCurrentDateBogota().subtract(1, 'days'), getCurrentDateBogota().subtract(1, 'days')],
                'Últimos 7 días': [getCurrentDateBogota().subtract(6, 'days'), getCurrentDateBogota()],
                'Últimos 30 días': [getCurrentDateBogota().subtract(29, 'days'), getCurrentDateBogota()],
                'Este mes': [getCurrentDateBogota().startOf('month'), getCurrentDateBogota().endOf('month')],
                'Mes anterior': [getCurrentDateBogota().subtract(1, 'month').startOf('month'), getCurrentDateBogota().subtract(1, 'month').endOf('month')]
            };
            
            // Reconfigurar el daterangepicker con las fechas de Bogotá
            $this.data('daterangepicker').ranges = ranges;
        });
    }
    
    // Función para actualizar cajas superiores con zona horaria de Bogotá
    function updateDashboardBoxes() {
        // Actualizar fecha de "Hoy" en las cajas superiores
        var todayBogota = getCurrentDateBogota().format('YYYY-MM-DD');
        
        // Actualizar cualquier elemento que muestre "Hoy" para usar la fecha de Bogotá
        $('.small-box p:contains("Hoy")').each(function() {
            var $this = $(this);
            var text = $this.text();
            if (text.includes('Hoy')) {
                // Agregar información de zona horaria si es necesario
                $this.attr('title', 'Fecha: ' + todayBogota + ' (Hora de Bogotá)');
            }
        });
    }
    
    // Ejecutar actualizaciones
    updateDateFilters();
    updateDashboardBoxes();
    
    // Actualizar cada minuto para mantener sincronización
    setInterval(function() {
        updateDashboardBoxes();
    }, 60000); // 60 segundos
    
    // Hacer funciones disponibles globalmente
    window.getCurrentDateBogota = getCurrentDateBogota;
    window.formatDateBogota = formatDateBogota;
    window.getDateRangeBogota = getDateRangeBogota;
    
});
