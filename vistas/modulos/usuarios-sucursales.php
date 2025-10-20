<?php
require_once "../controladores/usuarios-sucursales.controlador.php";

// Obtener sucursales disponibles
$sucursales = ControladorUsuariosSucursales::ctrObtenerSucursalesDisponibles();
?>

<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-users"></i> Gestión de Usuarios y Sucursales
            <small>Administrar usuarios por sucursal</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li class="active">Usuarios y Sucursales</li>
        </ol>
    </section>

    <section class="content">
        
        <!-- ESTADÍSTICAS RÁPIDAS -->
        <div class="row">
            <div class="col-md-12">
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-bar-chart"></i> Estadísticas por Sucursal
                        </h3>
                    </div>
                    <div class="box-body" id="estadisticasSucursales">
                        <!-- Las estadísticas se cargan aquí -->
                    </div>
                </div>
            </div>
        </div>

        <!-- FILTROS Y ACCIONES -->
        <div class="row">
            <div class="col-md-12">
                <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-filter"></i> Filtros y Acciones
                        </h3>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Filtrar por Sucursal:</label>
                                    <select class="form-control" id="filtroSucursal">
                                        <option value="">Todas las sucursales</option>
                                        <?php foreach ($sucursales as $sucursal): ?>
                                        <option value="<?php echo $sucursal['id']; ?>">
                                            <?php echo $sucursal['nombre']; ?> (<?php echo $sucursal['codigo_sucursal']; ?>)
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Filtrar por Perfil:</label>
                                    <select class="form-control" id="filtroPerfil">
                                        <option value="">Todos los perfiles</option>
                                        <option value="Administrador">Administrador</option>
                                        <option value="Vendedor">Vendedor</option>
                                        <option value="Contador">Contador</option>
                                        <option value="Transportador">Transportador</option>
                                        <option value="Limitado">Limitado</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>&nbsp;</label><br>
                                    <button class="btn btn-primary" id="btnNuevoUsuario">
                                        <i class="fa fa-plus"></i> Nuevo Usuario
                                    </button>
                                    <button class="btn btn-success" id="btnActualizarEstadisticas">
                                        <i class="fa fa-refresh"></i> Actualizar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- TABLA DE USUARIOS -->
        <div class="row">
            <div class="col-md-12">
                <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-list"></i> Lista de Usuarios
                        </h3>
                    </div>
                    <div class="box-body">
                        <table class="table table-bordered table-striped dt-responsive tablaUsuariosSucursales" width="100%">
                            <thead>
                                <tr>
                                    <th style="width:10px">#</th>
                                    <th>Usuario</th>
                                    <th>Nombre</th>
                                    <th>Perfil</th>
                                    <th>Sucursal Principal</th>
                                    <th>Sucursales Adicionales</th>
                                    <th>Es Transportador</th>
                                    <th>Estado</th>
                                    <th>Último Login</th>
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

<!-- MODAL CREAR USUARIO -->
<div class="modal fade" id="modalCrearUsuario" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form role="form" method="post" id="formCrearUsuario" enctype="multipart/form-data">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">
                        <i class="fa fa-user-plus"></i> Crear Nuevo Usuario
                    </h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <!-- INFORMACIÓN BÁSICA -->
                        <div class="col-md-6">
                            <h5><i class="fa fa-user"></i> Información Personal</h5>
                            <hr>
                            
                            <div class="form-group">
                                <label for="nuevoNombre">Nombre Completo:</label>
                                <input type="text" class="form-control" id="nuevoNombre" name="nuevoNombre" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="nuevoUsuario">Usuario:</label>
                                <input type="text" class="form-control" id="nuevoUsuario" name="nuevoUsuario" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="nuevoPassword">Contraseña:</label>
                                <input type="password" class="form-control" id="nuevoPassword" name="nuevoPassword" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="nuevoPerfil">Perfil:</label>
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
                        
                        <!-- INFORMACIÓN DE SUCURSAL -->
                        <div class="col-md-6">
                            <h5><i class="fa fa-building"></i> Asignación de Sucursal</h5>
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
                            
                            <div class="form-group" id="grupoSucursalesAdicionales" style="display: none;">
                                <label>Sucursales Adicionales (Transportador):</label>
                                <div id="sucursalesAdicionales">
                                    <!-- Se llenan dinámicamente -->
                                </div>
                                <small class="text-muted">Solo aplica para transportadores</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="nuevoTelefono">Teléfono:</label>
                                <input type="text" class="form-control" id="nuevoTelefono" name="nuevoTelefono">
                            </div>
                            
                            <div class="form-group">
                                <label for="nuevaDireccion">Dirección:</label>
                                <textarea class="form-control" id="nuevaDireccion" name="nuevaDireccion" rows="3"></textarea>
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

<!-- MODAL EDITAR SUCURSALES DE USUARIO -->
<div class="modal fade" id="modalEditarSucursales" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">
                    <i class="fa fa-building"></i> Gestionar Sucursales del Usuario
                </h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Usuario:</label>
                    <p class="form-control-static" id="usuarioSeleccionado"></p>
                </div>
                
                <div class="form-group">
                    <label>Sucursales Permitidas:</label>
                    <div id="sucursalesUsuario">
                        <!-- Se llenan dinámicamente -->
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default pull-left" data-dismiss="modal">
                    <i class="fa fa-times"></i> Cancelar
                </button>
                <button type="button" class="btn btn-primary" id="btnGuardarSucursales">
                    <i class="fa fa-save"></i> Guardar Cambios
                </button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    
    // Inicializar DataTable
    var tablaUsuarios = $('.tablaUsuariosSucursales').DataTable({
        "ajax": {
            "url": "ajax/datatable-usuarios-sucursales.ajax.php",
            "data": function(d) {
                d.filtroSucursal = $("#filtroSucursal").val();
                d.filtroPerfil = $("#filtroPerfil").val();
            }
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
        "order": [[2, "asc"]], // Ordenar por nombre
        "columnDefs": [
            { "width": "10px", "targets": 0 },
            { "width": "100px", "targets": 1 },
            { "width": "150px", "targets": 2 },
            { "width": "100px", "targets": 3 },
            { "width": "150px", "targets": 4 },
            { "width": "150px", "targets": 5 },
            { "width": "100px", "targets": 6, "className": "text-center" },
            { "width": "80px", "targets": 7, "className": "text-center" },
            { "width": "120px", "targets": 8 },
            { "width": "100px", "targets": 9, "orderable": false, "className": "text-center" }
        ]
    });
    
    // Eventos de filtros
    $("#filtroSucursal, #filtroPerfil").on("change", function() {
        tablaUsuarios.ajax.reload();
    });
    
    // Evento para mostrar/ocultar sucursales adicionales
    $("#nuevoPerfil").on("change", function() {
        if ($(this).val() === "Transportador") {
            $("#grupoSucursalesAdicionales").show();
            cargarSucursalesAdicionales();
        } else {
            $("#grupoSucursalesAdicionales").hide();
        }
    });
    
    // Evento para nuevo usuario
    $("#btnNuevoUsuario").on("click", function() {
        $("#modalCrearUsuario").modal("show");
    });
    
    // Evento para actualizar estadísticas
    $("#btnActualizarEstadisticas").on("click", function() {
        cargarEstadisticas();
    });
    
    // Cargar estadísticas iniciales
    cargarEstadisticas();
    
    // Función para cargar sucursales adicionales
    function cargarSucursalesAdicionales() {
        $.ajax({
            url: "ajax/obtener-sucursales.ajax.php",
            method: "GET",
            dataType: "json",
            success: function(respuesta) {
                if (respuesta.success) {
                    var html = "";
                    respuesta.data.forEach(function(sucursal) {
                        html += '<div class="checkbox">';
                        html += '<label>';
                        html += '<input type="checkbox" name="sucursalesPermitidas[]" value="' + sucursal.id + '"> ';
                        html += sucursal.nombre + ' (' + sucursal.codigo_sucursal + ')';
                        html += '</label>';
                        html += '</div>';
                    });
                    $("#sucursalesAdicionales").html(html);
                }
            }
        });
    }
    
    // Función para cargar estadísticas
    function cargarEstadisticas() {
        $.ajax({
            url: "ajax/estadisticas-usuarios-sucursales.ajax.php",
            method: "GET",
            dataType: "json",
            success: function(respuesta) {
                if (respuesta.success) {
                    var html = '<div class="row">';
                    respuesta.data.forEach(function(estadistica) {
                        html += '<div class="col-md-3">';
                        html += '<div class="info-box bg-blue">';
                        html += '<span class="info-box-icon"><i class="fa fa-building"></i></span>';
                        html += '<div class="info-box-content">';
                        html += '<span class="info-box-text">' + estadistica.nombre_sucursal + '</span>';
                        html += '<span class="info-box-number">' + estadistica.total_usuarios + ' usuarios</span>';
                        html += '<div class="progress">';
                        html += '<div class="progress-bar" style="width: 100%"></div>';
                        html += '</div>';
                        html += '<span class="progress-description">';
                        html += 'Admins: ' + estadistica.administradores + ' | ';
                        html += 'Vendedores: ' + estadistica.vendedores + ' | ';
                        html += 'Transportadores: ' + estadistica.transportadores;
                        html += '</span>';
                        html += '</div>';
                        html += '</div>';
                        html += '</div>';
                    });
                    html += '</div>';
                    $("#estadisticasSucursales").html(html);
                }
            }
        });
    }
});
</script>

<?php
// PROCESAR FORMULARIO
$crearUsuario = new ControladorUsuariosSucursales();
$crearUsuario->ctrCrearUsuarioConSucursal();
?>
