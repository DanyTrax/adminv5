<?php
require_once __DIR__ . "/../../controladores/usuarios-central.controlador.php";

// Obtener sucursales disponibles
$sucursales = ControladorUsuariosCentral::ctrObtenerSucursalesDisponibles();
?>

<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-users"></i> Gestión de Usuarios Centrales
            <small>Sistema bidireccional: Central ↔ Sucursales</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li class="active">Usuarios Centrales</li>
        </ol>
    </section>

    <section class="content">
        
        <!-- INFORMACIÓN DEL SISTEMA -->
        <div class="row">
            <div class="col-md-12">
                <div class="alert alert-info">
                    <h4><i class="fa fa-info-circle"></i> Sistema Bidireccional de Usuarios</h4>
                    <p><strong>Central → Local:</strong> Crear usuario en Central se sincroniza automáticamente a la sucursal</p>
                    <p><strong>Local → Central:</strong> Consultar usuarios existentes en sucursales para traerlos a Central</p>
                    <p><strong>Eliminación:</strong> Eliminar de Central elimina de Local y viceversa</p>
                </div>
            </div>
        </div>

        <!-- ESTADÍSTICAS RÁPIDAS -->
        <div class="row">
            <div class="col-md-12">
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-bar-chart"></i> Estadísticas del Sistema
                        </h3>
                    </div>
                    <div class="box-body" id="estadisticasSistema">
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
                            <i class="fa fa-cogs"></i> Acciones del Sistema
                        </h3>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <!-- ACCIONES CENTRAL → LOCAL -->
                            <div class="col-md-6">
                                <h4><i class="fa fa-arrow-down text-primary"></i> Central → Local</h4>
                                <button class="btn btn-primary btn-lg" id="btnNuevoUsuarioCentral">
                                    <i class="fa fa-user-plus"></i> Crear Usuario en Central
                                </button>
                                <button class="btn btn-success btn-lg" id="btnSincronizarUsuarios">
                                    <i class="fa fa-refresh"></i> Sincronizar a Sucursales
                                </button>
                            </div>
                            
                            <!-- ACCIONES LOCAL → CENTRAL -->
                            <div class="col-md-6">
                                <h4><i class="fa fa-arrow-up text-info"></i> Local → Central</h4>
                                <button class="btn btn-info btn-lg" id="btnConsultarUsuariosSucursales">
                                    <i class="fa fa-search"></i> Consultar Usuarios de Sucursales
                                </button>
                                <button class="btn btn-warning btn-lg" id="btnImportarUsuariosSucursales">
                                    <i class="fa fa-download"></i> Importar de Sucursales
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- PESTAÑAS PARA DIFERENTES VISTAS -->
        <div class="row">
            <div class="col-md-12">
                <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-list"></i> Gestión de Usuarios
                        </h3>
                    </div>
                    <div class="box-body">
                        <!-- NAVEGACIÓN POR PESTAÑAS -->
                        <ul class="nav nav-tabs" id="tabsUsuarios">
                            <li class="active">
                                <a href="#tabUsuariosCentral" data-toggle="tab">
                                    <i class="fa fa-database"></i> Usuarios Centrales
                                </a>
                            </li>
                            <li>
                                <a href="#tabUsuariosSucursales" data-toggle="tab">
                                    <i class="fa fa-building"></i> Usuarios de Sucursales
                                </a>
                            </li>
                            <li>
                                <a href="#tabSincronizacion" data-toggle="tab">
                                    <i class="fa fa-sync"></i> Estado de Sincronización
                                </a>
                            </li>
                        </ul>

                        <!-- CONTENIDO DE LAS PESTAÑAS -->
                        <div class="tab-content">
                            
                            <!-- PESTAÑA: USUARIOS CENTRALES -->
                            <div class="tab-pane active" id="tabUsuariosCentral">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped dt-responsive tablaUsuariosCentral" width="100%">
                                        <thead>
                                            <tr>
                                                <th style="width:10px;">#</th>
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

                            <!-- PESTAÑA: USUARIOS DE SUCURSALES -->
                            <div class="tab-pane" id="tabUsuariosSucursales">
                                <div id="contenidoUsuariosSucursales">
                                    <div class="text-center">
                                        <i class="fa fa-spinner fa-spin fa-2x"></i>
                                        <p>Cargando usuarios de sucursales...</p>
                                    </div>
                                </div>
                            </div>

                            <!-- PESTAÑA: ESTADO DE SINCRONIZACIÓN -->
                            <div class="tab-pane" id="tabSincronizacion">
                                <div id="contenidoSincronizacion">
                                    <div class="text-center">
                                        <i class="fa fa-spinner fa-spin fa-2x"></i>
                                        <p>Cargando estado de sincronización...</p>
                                    </div>
                                </div>
                            </div>

                        </div>
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
            <form role="form" method="post" id="formCrearUsuarioCentral">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">
                        <i class="fa fa-user-plus text-primary"></i> Crear Usuario Central
                    </h4>
                </div>
                <div class="modal-body">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nombreUsuarioCentral">Nombre Completo:</label>
                                <input type="text" class="form-control" id="nombreUsuarioCentral" name="nombreUsuarioCentral" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="usuarioCentral">Usuario:</label>
                                <input type="text" class="form-control" id="usuarioCentral" name="usuarioCentral" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="passwordUsuarioCentral">Contraseña:</label>
                                <input type="password" class="form-control" id="passwordUsuarioCentral" name="passwordUsuarioCentral" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="perfilUsuarioCentral">Perfil:</label>
                                <select class="form-control" id="perfilUsuarioCentral" name="perfilUsuarioCentral" required>
                                    <option value="">Seleccionar perfil</option>
                                    <option value="Administrador">Administrador</option>
                                    <option value="Vendedor">Vendedor</option>
                                    <option value="Contador">Contador</option>
                                    <option value="Transportador">Transportador</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="sucursalUsuarioCentral">Sucursal:</label>
                                <select class="form-control" id="sucursalUsuarioCentral" name="sucursalUsuarioCentral" required>
                                    <option value="">Seleccionar sucursal</option>
                                    <?php foreach($sucursales as $sucursal): ?>
                                    <option value="<?php echo $sucursal['id']; ?>">
                                        <?php echo htmlspecialchars($sucursal['nombre']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="telefonoUsuarioCentral">Teléfono:</label>
                                <input type="text" class="form-control" id="telefonoUsuarioCentral" name="telefonoUsuarioCentral">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="observacionesUsuarioCentral">Observaciones:</label>
                        <textarea class="form-control" id="observacionesUsuarioCentral" name="observacionesUsuarioCentral" rows="3"></textarea>
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

<!-- MODAL IMPORTAR USUARIOS DE SUCURSALES -->
<div class="modal fade" id="modalImportarUsuariosSucursales" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">
                    <i class="fa fa-download text-info"></i> Importar Usuarios de Sucursales
                </h4>
            </div>
            <div class="modal-body">
                <div id="contenidoImportacion">
                    <div class="text-center">
                        <i class="fa fa-spinner fa-spin fa-2x"></i>
                        <p>Cargando usuarios disponibles para importar...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default pull-left" data-dismiss="modal">
                    <i class="fa fa-times"></i> Cancelar
                </button>
                <button type="button" class="btn btn-info" id="btnConfirmarImportacion">
                    <i class="fa fa-download"></i> Importar Seleccionados
                </button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    
    // Inicializar DataTable de usuarios centrales
    var tablaUsuariosCentral = $('.tablaUsuariosCentral').DataTable({
        "ajax": {
            "url": "ajax/datatable-usuarios-central.ajax.php",
            "data": function(d) {
                d.mostrarUsuariosCentral = true;
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
        "order": [[7, "desc"]],
        "columnDefs": [
            { "width": "10px", "targets": 0 },
            { "width": "120px", "targets": 1 },
            { "width": "200px", "targets": 2 },
            { "width": "100px", "targets": 3, "className": "text-center" },
            { "width": "150px", "targets": 4 },
            { "width": "100px", "targets": 5 },
            { "width": "150px", "targets": 6, "className": "text-center" },
            { "width": "120px", "targets": 7 },
            { "width": "100px", "targets": 8, "orderable": false, "className": "text-center" }
        ]
    });

    // Eventos de los botones
    $("#btnNuevoUsuarioCentral").on("click", function() {
        $("#modalCrearUsuarioCentral").modal("show");
    });

    $("#btnConsultarUsuariosSucursales").on("click", function() {
        cargarUsuariosSucursales();
        $("#tabsUsuarios a[href='#tabUsuariosSucursales']").tab('show');
    });

    $("#btnImportarUsuariosSucursales").on("click", function() {
        $("#modalImportarUsuariosSucursales").modal("show");
        cargarUsuariosParaImportar();
    });

    // Cargar contenido cuando se cambia de pestaña
    $('#tabsUsuarios a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        var target = $(e.target).attr("href");
        
        if (target === "#tabUsuariosSucursales") {
            cargarUsuariosSucursales();
        } else if (target === "#tabSincronizacion") {
            cargarEstadoSincronizacion();
        }
    });

    // Función para cargar usuarios de sucursales
    function cargarUsuariosSucursales() {
        $("#contenidoUsuariosSucursales").html('<div class="text-center"><i class="fa fa-spinner fa-spin fa-2x"></i><p>Cargando usuarios de sucursales...</p></div>');
        
        $.ajax({
            url: "ajax/consultar-usuarios-sucursales.ajax.php",
            method: "POST",
            data: { tipo_consulta: "remotas" },
            dataType: "json",
            success: function(respuesta) {
                if(respuesta.success) {
                    mostrarUsuariosSucursales(respuesta.data.sucursales);
                } else {
                    $("#contenidoUsuariosSucursales").html('<div class="alert alert-danger">Error: ' + respuesta.error + '</div>');
                }
            },
            error: function() {
                $("#contenidoUsuariosSucursales").html('<div class="alert alert-danger">Error al cargar usuarios de sucursales</div>');
            }
        });
    }

    // Función para mostrar usuarios de sucursales
    function mostrarUsuariosSucursales(sucursales) {
        var html = '';
        
        sucursales.forEach(function(sucursal) {
            var estadoClass = sucursal.estado_conexion === 'conectado' ? 'success' : 'danger';
            var estadoText = sucursal.estado_conexion === 'conectado' ? 'Conectado' : 'Error';
            
            html += '<div class="box box-' + estadoClass + '">';
            html += '<div class="box-header with-border">';
            html += '<h3 class="box-title">';
            html += '<i class="fa fa-building"></i> ' + sucursal.sucursal.nombre;
            html += '<small class="label label-' + estadoClass + '">' + sucursal.total_usuarios + ' usuarios</small>';
            html += '</h3>';
            html += '<div class="box-tools pull-right">';
            html += '<span class="label label-' + estadoClass + '">' + estadoText + '</span>';
            html += '</div>';
            html += '</div>';
            html += '<div class="box-body">';
            
            if(sucursal.estado_conexion === 'conectado' && sucursal.usuarios.length > 0) {
                html += '<div class="table-responsive">';
                html += '<table class="table table-bordered table-striped">';
                html += '<thead><tr><th>Usuario</th><th>Nombre</th><th>Perfil</th><th>Estado</th><th>Acciones</th></tr></thead>';
                html += '<tbody>';
                
                sucursal.usuarios.forEach(function(usuario) {
                    html += '<tr>';
                    html += '<td>' + (usuario.usuario || 'N/A') + '</td>';
                    html += '<td>' + (usuario.nombre || 'N/A') + '</td>';
                    html += '<td><span class="label label-info">' + (usuario.perfil || 'N/A') + '</span></td>';
                    html += '<td><span class="label label-' + (usuario.estado ? 'success' : 'danger') + '">' + (usuario.estado ? 'Activo' : 'Inactivo') + '</span></td>';
                    html += '<td><button class="btn btn-xs btn-info btnImportarUsuario" data-usuario=\'' + JSON.stringify(usuario) + '\' data-sucursal=\'' + JSON.stringify(sucursal.sucursal) + '\'><i class="fa fa-download"></i> Importar</button></td>';
                    html += '</tr>';
                });
                
                html += '</tbody></table></div>';
            } else if(sucursal.estado_conexion === 'conectado') {
                html += '<div class="alert alert-info">No hay usuarios en esta sucursal.</div>';
            } else {
                html += '<div class="alert alert-danger">Error de conexión: ' + (sucursal.error || 'Error desconocido') + '</div>';
            }
            
            html += '</div></div>';
        });
        
        $("#contenidoUsuariosSucursales").html(html);
    }

    // Función para cargar estado de sincronización
    function cargarEstadoSincronizacion() {
        $("#contenidoSincronizacion").html('<div class="text-center"><i class="fa fa-spinner fa-spin fa-2x"></i><p>Cargando estado de sincronización...</p></div>');
        
        // Aquí implementarías la lógica para mostrar el estado de sincronización
        $("#contenidoSincronizacion").html('<div class="alert alert-info">Estado de sincronización en desarrollo</div>');
    }

    // Función para cargar usuarios para importar
    function cargarUsuariosParaImportar() {
        $("#contenidoImportacion").html('<div class="text-center"><i class="fa fa-spinner fa-spin fa-2x"></i><p>Cargando usuarios disponibles para importar...</p></div>');
        
        // Aquí implementarías la lógica para mostrar usuarios disponibles para importar
        $("#contenidoImportacion").html('<div class="alert alert-info">Importación de usuarios en desarrollo</div>');
    }

    // Evento para importar usuario individual
    $(document).on("click", ".btnImportarUsuario", function() {
        var usuario = $(this).data('usuario');
        var sucursal = $(this).data('sucursal');
        
        if(confirm('¿Desea importar el usuario "' + usuario.nombre + '" desde ' + sucursal.nombre + '?')) {
            // Aquí implementarías la lógica para importar el usuario
            alert('Importación de usuario en desarrollo');
        }
    });

});
</script>
