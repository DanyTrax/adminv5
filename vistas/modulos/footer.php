<footer class="main-footer">
	<div class="pull-left">
		<strong>Copyright &copy; 2022 <a href="https://www.dowgroupcol.com" target="_blank">DowgroupCol</a>.</strong>
		Todos los derechos reservados.
	</div>
	<div class="pull-right" id="reloj-sistema" style="font-weight: bold;">
		<i class="fa fa-clock-o"></i> <span id="fecha-hora-sistema"></span>
	</div>
	<div class="clearfix"></div>
</footer>

<script>
// Reloj en tiempo real con zona horaria de Bogotá
function actualizarReloj() {
    // Verificar si moment.js está disponible
    if (typeof moment !== 'undefined' && typeof moment.tz !== 'undefined') {
        // Usar moment-timezone para obtener la hora de Bogotá
        var ahora = moment.tz('America/Bogota');
        var fechaHora = ahora.format('DD/MM/YYYY HH:mm:ss');
        document.getElementById('fecha-hora-sistema').textContent = fechaHora;
    } else {
        // Fallback: usar JavaScript nativo
        var ahora = new Date();
        var dia = String(ahora.getDate()).padStart(2, '0');
        var mes = String(ahora.getMonth() + 1).padStart(2, '0');
        var año = ahora.getFullYear();
        var horas = String(ahora.getHours()).padStart(2, '0');
        var minutos = String(ahora.getMinutes()).padStart(2, '0');
        var segundos = String(ahora.getSeconds()).padStart(2, '0');
        var fechaHora = dia + '/' + mes + '/' + año + ' ' + horas + ':' + minutos + ':' + segundos;
        document.getElementById('fecha-hora-sistema').textContent = fechaHora;
    }
}

// Actualizar el reloj cada segundo
setInterval(actualizarReloj, 1000);

// Actualizar inmediatamente al cargar
actualizarReloj();
</script>
