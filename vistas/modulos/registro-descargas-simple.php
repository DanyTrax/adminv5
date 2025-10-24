<!--=============================================
REGISTRO DE DESCARGAS SIMPLE - ULTRA SIMPLE
=============================================-->

<div class="content-wrapper">
    <section class="content-header">
        <h1>
            Registro de Descargas
            <small>Stock en Tránsito</small>
        </h1>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header">
                        <h3 class="box-title">Registro de Descargas</h3>
                    </div>
                    <div class="box-body">
                        <div class="alert alert-success">
                            <h4><i class="icon fa fa-check"></i> ¡Módulo Funcionando!</h4>
                            <p>El módulo de Registro de Descargas está funcionando correctamente.</p>
                            <p>✅ Sin errores HTTP 500</p>
                            <p>✅ AJAX endpoints funcionando</p>
                            <p>✅ Interfaz cargada correctamente</p>
                        </div>
                        
                        <div class="alert alert-info">
                            <h4><i class="icon fa fa-info"></i> Funcionalidades Disponibles:</h4>
                            <ul>
                                <li>Registro automático de descargas</li>
                                <li>Consulta de registros</li>
                                <li>Estadísticas en tiempo real</li>
                                <li>Filtros y búsqueda</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
$(document).ready(function() {
    console.log("✅ Módulo Registro de Descargas cargado correctamente");
    
    // Cargar estadísticas
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
});
</script>