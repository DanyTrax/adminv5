<?php
require_once "../controladores/usuarios-central.controlador.php";

// Obtener sucursales disponibles
$sucursales = ControladorUsuariosCentral::ctrObtenerSucursalesDisponibles();
?>

<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-users"></i> Gestión de Usuarios Centrales
            <small>Administrar usuarios para sincronización con sucursales</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li class="active">Usuarios Centrales</li>
        </ol>
    </section>

    <section class="content">
        
        <!-- ESTADÍSTICAS RÁPIDAS -->
        <div class="row">
            <div class="col-md-12">
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-bar-chart"></i> Estadísticas de Sincronización
                        </h3>
                    </div>
                    <div class="box-body" id="estadisticasSincronizacion">
                        <!-- Las estadísticas se cargan aquí -->
                    </div>
                </div>
            </div>
        </div>

        <!-- ACCIONES PRINCIPALES -->
        <div class="row">
            <div class="col-md-12">
                <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-cogs"></i> Acciones
                        </h3>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-6">
                                <button class="btn btn-primary btn-lg" id="btnNuevoUsuarioCentral">
                                    <i class="fa fa-user-plus"></i> Agregar Usuario
                                </button>
                                <button class="btn btn-success btn-lg" id="btnSincronizarUsuarios">
                                    <i class="fa fa-refresh"></i> Sincronizar Usuarios
                                </button>
                            </div>
                            <div class="col-md-6 text-right">
                                <button class="btn btn-info" id="btnActualizarEstadisticas">
                                    <i class="fa fa-refresh"></i> Actualizar Estadísticas
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- TABLA DE USUARIOS CENTRALES -->
        <div class="row">
            <div class="col-md-12">
                <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-list"></i> Lista de Usuarios Centrales
                        </h3>
                    </div>
                    <div class="box-body">
                        <table class="table table-bordered table-striped dt-responsive tablaUsuariosCentrales" width="100%">
                            <thead>
                                <tr>
                                    <th style="width:10px">#</th>
                                    <th>Usuario</th>
                                    <th>Nombre</th>
                                    <th>Perfil</th>
                                    <th>Sucursal</th>
                                    <th>Teléfono</th>
                                    <th>Estado Sincronización</th>
                                    <th>Fecha Creación</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Los datos se cargan con DataTables AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- MODAL CREAR USUARIO CENTRAL -->
<div class="modal fade" id="modalCrearUsuarioCentral" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form role="form" method="post" id="formCrearUsuarioCentral" enctype="multipart/form-data">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">
                        <i class="fa fa-user-plus"></i> Crear Nuevo Usuario Central
                    </h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <!-- INFORMACIÓN BÁSICA -->
                        <div class="col-md-6">
                            <h5><i class="fa fa-user"></i> Información Personal</h5>
                            <hr>
                            
                            <div class="form-group">
                                <label for="nuevoNombre">Nombre Completo: <span class="text-red">*</span></label>
                                <input type="text" class="form-control" id="nuevoNombre" name="nuevoNombre" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="nuevoUsuario">Usuario: <span class="text-red">*</span></label>
                                <input type="text" class="form-control" id="nuevoUsuario" name="nuevoUsuario" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="nuevoPassword">Contraseña: <span class="text-red">*</span></label>
                                <input type="password" class="form-control" id="nuevoPassword" name="nuevoPassword" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="nuevoPerfil">Perfil: <span class="text-red">*</span></label>
                                <select class="form-control" id="nuevoPerfil" name="nuevoPerfil" required>
                                    <option value="">Seleccionar perfil</option>
                                    <option value="Administrador">Administrador</option>
                                    <option value="Vendedor">Vendedor</option>
                                    <option value="Contador">Contador</option>
                                    <option value="Transportador">Transportador</option>
                                    <option value="Limitado">Limitado</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="nuevaFoto">Foto:</label>
                                <input type="file" class="form-control" id="nuevaFoto" name="nuevaFoto" accept="image/*">
                            </div>
                        </div>
                        
                        <!-- INFORMACIÓN DE SUCURSAL Y CONTACTO -->
                        <div class="col-md-6">
                            <h5><i class="fa fa-building"></i> Asignación y Contacto</h5>
                            <hr>
                            
                            <div class="form-group">
                                <label for="nuevaSucursal">Sucursal Principal: <span class="text-red">*</span></label>
                                <select class="form-control" id="nuevaSucursal" name="nuevaSucursal" required>
                                    <option value="">Seleccionar sucursal</option>
                                    <?php foreach ($sucursales as $sucursal): ?>
                                    <option value="<?php echo $sucursal['id']; ?>">
                                        <?php echo $sucursal['nombre']; ?> (<?php echo $sucursal['codigo_sucursal']; ?>)
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="nuevoTelefono">Teléfono:</label>
                                <input type="text" class="form-control" id="nuevoTelefono" name="nuevoTelefono">
                            </div>
                            
                            <div class="form-group">
                                <label for="nuevaDireccion">Dirección:</label>
                                <textarea class="form-control" id="nuevaDireccion" name="nuevaDireccion" rows="3"></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label for="nuevasObservaciones">Observaciones:</label>
                                <textarea class="form-control" id="nuevasObservaciones" name="nuevasObservaciones" rows="3" placeholder="Notas adicionales sobre el usuario..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal">
                        <i class="fa fa-times"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i> Crear Usuario
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL SINCRONIZAR USUARIOS -->
<div class="modal fade" id="modalSincronizarUsuarios" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">
                    <i class="fa fa-refresh"></i> Sincronizar Usuarios con Sucursales
                </h4>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fa fa-info-circle"></i>
                    <strong>Información:</strong> Esta acción sincronizará todos los usuarios pendientes con sus sucursales correspondientes.
                </div>
                
                <div id="detallesSincronizacion">
                    <!-- Se llenan dinámicamente -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default pull-left" data-dismiss="modal">
                    <i class="fa fa-times"></i> Cancelar
                </button>
                <form method="post" style="display: inline;">
                    <input type="hidden" name="sincronizarUsuarios" value="1">
                    <button type="submit" class="btn btn-success">
                        <i class="fa fa-refresh"></i> Iniciar Sincronización
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    
    // Inicializar DataTable
    var tablaUsuarios = $('.tablaUsuariosCentrales').DataTable({
        "ajax": {
            "url": "ajax/datatable-usuarios-central.ajax.php",
            "type": "POST"
        },
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
        },
        "order": [[7, "desc"]], // Ordenar por fecha de creación
        "columnDefs": [
            { "width": "10px", "targets": 0 },
            { "width": "100px", "targets": 1 },
            { "width": "150px", "targets": 2 },
            { "width": "100px", "targets": 3 },
            { "width": "150px", "targets": 4 },
            { "width": "120px", "targets": 5 },
            { "width": "120px", "targets": 6, "className": "text-center" },
            { "width": "120px", "targets": 7 },
            { "width": "100px", "targets": 8, "orderable": false, "className": "text-center" }
        ]
    });
    
    // Evento para nuevo usuario
    $("#btnNuevoUsuarioCentral").on("click", function() {
        $("#modalCrearUsuarioCentral").modal("show");
    });
    
    // Evento para sincronizar usuarios
    $("#btnSincronizarUsuarios").on("click", function() {
        cargarDetallesSincronizacion();
        $("#modalSincronizarUsuarios").modal("show");
    });
    
    // Evento para actualizar estadísticas
    $("#btnActualizarEstadisticas").on("click", function() {
        cargarEstadisticas();
    });
    
    // Cargar estadísticas iniciales
    cargarEstadisticas();
    
    // Función para cargar estadísticas
    function cargarEstadisticas() {
        $.ajax({
            url: "ajax/estadisticas-usuarios-central.ajax.php",
            method: "GET",
            dataType: "json",
            success: function(respuesta) {
                if (respuesta.success) {
                    var html = '<div class="row">';
                    
                    html += '<div class="col-md-3">';
                    html += '<div class="info-box bg-blue">';
                    html += '<span class="info-box-icon"><i class="fa fa-users"></i></span>';
                    html += '<div class="info-box-content">';
                    html += '<span class="info-box-text">Total Usuarios</span>';
                    html += '<span class="info-box-number">' + respuesta.data.total_usuarios + '</span>';
                    html += '</div></div></div>';
                    
                    html += '<div class="col-md-3">';
                    html += '<div class="info-box bg-green">';
                    html += '<span class="info-box-icon"><i class="fa fa-check"></i></span>';
                    html += '<div class="info-box-content">';
                    html += '<span class="info-box-text">Sincronizados</span>';
                    html += '<span class="info-box-number">' + respuesta.data.sincronizados + '</span>';
                    html += '</div></div></div>';
                    
                    html += '<div class="col-md-3">';
                    html += '<div class="info-box bg-yellow">';
                    html += '<span class="info-box-icon"><i class="fa fa-clock-o"></i></span>';
                    html += '<div class="info-box-content">';
                    html += '<span class="info-box-text">Pendientes</span>';
                    html += '<span class="info-box-number">' + respuesta.data.pendientes + '</span>';
                    html += '</div></div></div>';
                    
                    html += '<div class="col-md-3">';
                    html += '<div class="info-box bg-red">';
                    html += '<span class="info-box-icon"><i class="fa fa-exclamation"></i></span>';
                    html += '<div class="info-box-content">';
                    html += '<span class="info-box-text">Errores</span>';
                    html += '<span class="info-box-number">' + respuesta.data.errores + '</span>';
                    html += '</div></div></div>';
                    
                    html += '</div>';
                    $("#estadisticasSincronizacion").html(html);
                }
            }
        });
    }
    
    // Función para cargar detalles de sincronización
    function cargarDetallesSincronizacion() {
        $.ajax({
            url: "ajax/detalles-sincronizacion.ajax.php",
            method: "GET",
            dataType: "json",
            success: function(respuesta) {
                if (respuesta.success) {
                    var html = '<h5>Usuarios pendientes de sincronización:</h5>';
                    html += '<ul class="list-group">';
                    
                    respuesta.data.forEach(function(usuario) {
                        html += '<li class="list-group-item">';
                        html += '<strong>' + usuario.usuario + '</strong> - ' + usuario.nombre;
                        html += ' <span class="label label-info">' + usuario.nombre_sucursal + '</span>';
                        html += '</li>';
                    });
                    
                    html += '</ul>';
                    $("#detallesSincronizacion").html(html);
                }
            }
        });
    }
});
</script>

<?php
// PROCESAR FORMULARIOS
$crearUsuario = new ControladorUsuariosCentral();
$crearUsuario->ctrCrearUsuarioCentral();
$crearUsuario->ctrSincronizarUsuarios();
?>
