<!--=============================================
REGISTRO DE DESCARGAS SIMPLE - INTERFAZ COMPLETA
=============================================-->

<div class="content-wrapper">
    <section class="content-header">
        <h1>
            Registro de Descargas
            <small>Stock en Tránsito</small>
        </h1>
    </section>

    <section class="content">
        <!-- Estadísticas -->
        <div class="row">
            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-aqua">
                    <div class="inner">
                        <h3 id="total-descargas">0</h3>
                        <p>Total Descargas</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-download"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-green">
                    <div class="inner">
                        <h3 id="total-cantidad">0</h3>
                        <p>Total Cantidad</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-cubes"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-yellow">
                    <div class="inner">
                        <h3 id="productos-unicos">0</h3>
                        <p>Productos Únicos</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-tags"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-red">
                    <div class="inner">
                        <h3 id="usuarios-unicos">0</h3>
                        <p>Usuarios Únicos</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-users"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header">
                        <h3 class="box-title">Filtros de Búsqueda</h3>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Código de Producto:</label>
                                    <input type="text" class="form-control" id="filtro-producto" placeholder="Ej: PROD001">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Usuario:</label>
                                    <input type="text" class="form-control" id="filtro-usuario" placeholder="Nombre del usuario">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Fecha Desde:</label>
                                    <input type="date" class="form-control" id="filtro-fecha-desde">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Fecha Hasta:</label>
                                    <input type="date" class="form-control" id="filtro-fecha-hasta">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <button type="button" class="btn btn-primary" id="btn-filtrar">
                                    <i class="fa fa-search"></i> Filtrar
                                </button>
                                <button type="button" class="btn btn-default" id="btn-limpiar">
                                    <i class="fa fa-refresh"></i> Limpiar
                                </button>
                                <button type="button" class="btn btn-success" id="btn-exportar">
                                    <i class="fa fa-download"></i> Exportar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de Registros -->
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header">
                        <h3 class="box-title">Registro de Descargas</h3>
                    </div>
                    <div class="box-body">
                        <div class="table-responsive">
                            <table id="tabla-registro-descargas" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Fecha</th>
                                        <th>Hora</th>
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
                            </table>
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
    
    // Inicializar DataTable
    var tabla = $('#tabla-registro-descargas').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "ajax/datatable-registro-descargas-simple.ajax.php",
            "type": "POST"
        },
        "columns": [
            { "data": "id" },
            { "data": "fecha" },
            { "data": "hora" },
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
        "order": [[ 0, "desc" ]]
    });
    
    // Cargar estadísticas
    cargarEstadisticas();
    
    // Eventos de filtros
    $('#btn-filtrar').click(function() {
        aplicarFiltros();
    });
    
    $('#btn-limpiar').click(function() {
        limpiarFiltros();
    });
    
    $('#btn-exportar').click(function() {
        exportarRegistros();
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
                if(respuesta.success) {
                    $('#total-descargas').text(respuesta.data.total_descargas);
                    $('#total-cantidad').text(respuesta.data.total_cantidad);
                    $('#productos-unicos').text(respuesta.data.productos_unicos);
                    $('#usuarios-unicos').text(respuesta.data.usuarios_unicos);
                }
            },
            error: function() {
                console.error("❌ Error al cargar estadísticas");
            }
        });
    }
    
    function aplicarFiltros() {
        var filtros = {
            producto: $('#filtro-producto').val(),
            usuario: $('#filtro-usuario').val(),
            fecha_desde: $('#filtro-fecha-desde').val(),
            fecha_hasta: $('#filtro-fecha-hasta').val()
        };
        
        tabla.ajax.url("ajax/datatable-registro-descargas-simple.ajax.php").load();
    }
    
    function limpiarFiltros() {
        $('#filtro-producto').val('');
        $('#filtro-usuario').val('');
        $('#filtro-fecha-desde').val('');
        $('#filtro-fecha-hasta').val('');
        tabla.ajax.url("ajax/datatable-registro-descargas-simple.ajax.php").load();
    }
    
    function exportarRegistros() {
        window.open("ajax/exportar-registro-descargas-simple.ajax.php", "_blank");
    }
});
</script>