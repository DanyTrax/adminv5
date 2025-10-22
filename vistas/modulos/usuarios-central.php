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
                        
                        <!-- ACCIONES DEL SISTEMA - COMPACTAS -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="btn-group btn-group-sm" role="group">
                                    <button class="btn btn-primary btn-sm" id="btnNuevoUsuarioCentral" title="Crear Usuario en Central">
                                        <i class="fa fa-plus"></i> Crear Usuario
                                    </button>
                                    <button class="btn btn-success btn-sm" id="btnSincronizarUsuarios" title="Sincronizar a Sucursales">
                                        <i class="fa fa-refresh"></i> Sincronizar
                                    </button>
                                    <button class="btn btn-info btn-sm" id="btnConsultarUsuariosSucursales" title="Consultar Usuarios de Sucursales">
                                        <i class="fa fa-search"></i> Consultar
                                    </button>
                                    <button class="btn btn-warning btn-sm" id="btnImportarUsuariosSucursales" title="Importar de Sucursales">
                                        <i class="fa fa-download"></i> Importar
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- SELECCIÓN MÚLTIPLE DE SUCURSALES -->
                        <div class="row" style="margin-top: 15px;">
                            <div class="col-md-12">
                                <div class="box box-warning">
                                    <div class="box-header with-border">
                                        <h3 class="box-title">
                                            <i class="fa fa-building"></i> Selección de Sucursales para Sincronización
                                        </h3>
                                    </div>
                                    <div class="box-body">
                                        <p><strong>Selecciona las sucursales donde quieres sincronizar los usuarios:</strong></p>
                                        <div id="sucursalesSeleccion" class="row">
                                            <!-- Las sucursales se cargarán aquí dinámicamente -->
                                        </div>
                                        <div class="text-center" style="margin-top: 15px;">
                                            <button class="btn btn-success btn-sm" id="btnSincronizarSeleccionadas" disabled>
                                                <i class="fa fa-refresh"></i> Sincronizar a Sucursales Seleccionadas
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>

        <!-- GESTIÓN DE USUARIOS -->
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-users"></i> Gestión de Usuarios
                        </h3>
                        <div class="box-tools pull-right">
                            <button class="btn btn-box-tool" data-widget="collapse">
                                <i class="fa fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="box-body">
                        
                        <!-- PESTAÑAS -->
                        <ul class="nav nav-tabs" role="tablist">
                            <li role="presentation" class="active">
                                <a href="#usuariosCentrales" aria-controls="usuariosCentrales" role="tab" data-toggle="tab">
                                    <i class="fa fa-database"></i> Usuarios Centrales
                                </a>
                            </li>
                            <li role="presentation">
                                <a href="#usuariosSucursales" aria-controls="usuariosSucursales" role="tab" data-toggle="tab">
                                    <i class="fa fa-building"></i> Usuarios de Sucursales
                                </a>
                            </li>
                            <li role="presentation">
                                <a href="#estadoSincronizacion" aria-controls="estadoSincronizacion" role="tab" data-toggle="tab">
                                    <i class="fa fa-sync"></i> Estado de Sincronización
                                </a>
                            </li>
                        </ul>

                        <!-- CONTENIDO DE PESTAÑAS -->
                        <div class="tab-content">
                            
                            <!-- PESTAÑA: USUARIOS CENTRALES -->
                            <div role="tabpanel" class="tab-pane active" id="usuariosCentrales">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-striped" id="tablaUsuariosCentrales">
                                                <thead>
                                                    <tr>
                                                        <th>ID</th>
                                                        <th>Usuario</th>
                                                        <th>Nombre</th>
                                                        <th>Perfil</th>
                                                        <th>Sucursal</th>
                                                        <th>Estado</th>
                                                        <th>Último Login</th>
                                                        <th>Acciones</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <!-- Los datos se cargan aquí -->
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- PESTAÑA: USUARIOS DE SUCURSALES -->
                            <div role="tabpanel" class="tab-pane" id="usuariosSucursales">
                                <div id="contenidoUsuariosSucursales">
                                    <div class="text-center">
                                        <i class="fa fa-spinner fa-spin fa-2x"></i>
                                        <p>Cargando usuarios de sucursales...</p>
                                    </div>
                                </div>
                            </div>

                            <!-- PESTAÑA: ESTADO DE SINCRONIZACIÓN -->
                            <div role="tabpanel" class="tab-pane" id="estadoSincronizacion">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="alert alert-info">
                                            <h4><i class="fa fa-info-circle"></i> Estado de Sincronización</h4>
                                            <p>Aquí se mostrará el estado de sincronización entre Central y las sucursales.</p>
                                        </div>
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

<!-- MODAL: NUEVO USUARIO CENTRAL -->
<div class="modal fade" id="modalNuevoUsuarioCentral" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">
                    <i class="fa fa-user-plus"></i> Nuevo Usuario Central
                </h4>
            </div>
            <div class="modal-body">
                <form id="formNuevoUsuarioCentral">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nombreUsuario">Nombre Completo:</label>
                                <input type="text" class="form-control" id="nombreUsuario" name="nombre" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="usuarioUsuario">Usuario:</label>
                                <input type="text" class="form-control" id="usuarioUsuario" name="usuario" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="passwordUsuario">Contraseña:</label>
                                <input type="password" class="form-control" id="passwordUsuario" name="password" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="perfilUsuario">Perfil:</label>
                                <select class="form-control" id="perfilUsuario" name="perfil" required>
                                    <option value="">Seleccionar perfil</option>
                                    <option value="Administrador">Administrador</option>
                                    <option value="Vendedor">Vendedor</option>
                                    <option value="Contador">Contador</option>
                                    <option value="Transportador">Transportador</option>
                                    <option value="Limitado">Limitado</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="telefonoUsuario">Teléfono:</label>
                                <input type="text" class="form-control" id="telefonoUsuario" name="telefono">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="direccionUsuario">Dirección:</label>
                                <input type="text" class="form-control" id="direccionUsuario" name="direccion">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fa fa-building"></i> Sucursales Asignadas:</label>
                        <div class="alert alert-info">
                            <strong>Selecciona las sucursales donde este usuario tendrá acceso:</strong>
                        </div>
                        <div id="sucursalesAsignadas" class="row">
                            <!-- Las sucursales se cargarán aquí dinámicamente -->
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnGuardarUsuarioCentral">
                    <i class="fa fa-save"></i> Guardar Usuario
                </button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    
    // Cargar estadísticas del sistema
    cargarEstadisticasSistema();
    
    // Cargar usuarios centrales
    cargarUsuariosCentrales();
    
    // Eventos de botones principales
    $("#btnNuevoUsuarioCentral").on("click", function() {
        $("#modalNuevoUsuarioCentral").modal("show");
    });
    
    $("#btnConsultarUsuariosSucursales").on("click", function() {
        cargarUsuariosSucursales();
    });
    
    $("#btnImportarUsuariosSucursales").on("click", function() {
        importarUsuariosSucursales();
    });
    
    $("#btnSincronizarUsuarios").on("click", function() {
        sincronizarUsuarios();
    });
    
    // Guardar nuevo usuario central
    $("#btnGuardarUsuarioCentral").on("click", function() {
        guardarUsuarioCentral();
    });
    
    // Función para cargar estadísticas
    function cargarEstadisticasSistema() {
        $.ajax({
            url: "ajax/usuarios-central.ajax.php",
            method: "POST",
            data: { accion: "obtener_estadisticas" },
            dataType: "json",
            success: function(respuesta) {
                if(respuesta.success) {
                    var html = '<div class="row">';
                    html += '<div class="col-md-3"><div class="info-box bg-blue"><span class="info-box-icon"><i class="fa fa-users"></i></span><div class="info-box-content"><span class="info-box-text">Usuarios Centrales</span><span class="info-box-number">' + respuesta.estadisticas.total_usuarios_central + '</span></div></div></div>';
                    html += '<div class="col-md-3"><div class="info-box bg-green"><span class="info-box-icon"><i class="fa fa-building"></i></span><div class="info-box-content"><span class="info-box-text">Sucursales Activas</span><span class="info-box-number">' + respuesta.estadisticas.sucursales_activas + '</span></div></div></div>';
                    html += '<div class="col-md-3"><div class="info-box bg-yellow"><span class="info-box-icon"><i class="fa fa-sync"></i></span><div class="info-box-content"><span class="info-box-text">Sincronizaciones</span><span class="info-box-number">' + respuesta.estadisticas.sincronizaciones_hoy + '</span></div></div></div>';
                    html += '<div class="col-md-3"><div class="info-box bg-red"><span class="info-box-icon"><i class="fa fa-exclamation-triangle"></i></span><div class="info-box-content"><span class="info-box-text">Errores</span><span class="info-box-number">' + respuesta.estadisticas.errores_hoy + '</span></div></div></div>';
                    html += '</div>';
                    $("#estadisticasSistema").html(html);
                }
            }
        });
    }
    
    // Función para cargar usuarios centrales
    function cargarUsuariosCentrales() {
        $.ajax({
            url: "ajax/usuarios-central.ajax.php",
            method: "POST",
            data: { accion: "obtener_usuarios_centrales" },
            dataType: "json",
            success: function(respuesta) {
                if(respuesta.success) {
                    var html = '';
                    respuesta.usuarios.forEach(function(usuario) {
                        html += '<tr>';
                        html += '<td>' + usuario.id + '</td>';
                        html += '<td><strong>' + usuario.usuario + '</strong></td>';
                        html += '<td>' + usuario.nombre + '</td>';
                        html += '<td><span class="label label-info">' + usuario.perfil + '</span></td>';
                        html += '<td>' + (usuario.sucursal_nombre || 'N/A') + '</td>';
                        html += '<td><span class="label label-' + (usuario.activo ? 'success' : 'danger') + '">' + (usuario.activo ? 'Activo' : 'Inactivo') + '</span></td>';
                        html += '<td>' + (usuario.ultimo_login || 'Nunca') + '</td>';
                        html += '<td>';
                        html += '<button class="btn btn-warning btn-xs btnEditarUsuario" data-id="' + usuario.id + '"><i class="fa fa-edit"></i></button> ';
                        html += '<button class="btn btn-danger btn-xs btnEliminarUsuario" data-id="' + usuario.id + '"><i class="fa fa-trash"></i></button>';
                        html += '</td>';
                        html += '</tr>';
                    });
                    $("#tablaUsuariosCentrales tbody").html(html);
                }
            }
        });
    }
    
    // Función para cargar usuarios de sucursales
    function cargarUsuariosSucursales() {
        $("#contenidoUsuariosSucursales").html('<div class="text-center"><i class="fa fa-spinner fa-spin fa-2x"></i><p>Cargando usuarios de sucursales...</p></div>');
        
        $.ajax({
            url: "ajax/consultar-usuarios-sucursales.ajax.php",
            method: "POST",
            data: { tipo_consulta: "todas" },
            dataType: "json",
            success: function(respuesta) {
                if(respuesta.success) {
                    mostrarUsuariosSucursales(respuesta.sucursales, respuesta.local);
                } else {
                    $("#contenidoUsuariosSucursales").html('<div class="alert alert-danger">Error: ' + respuesta.error + '</div>');
                }
            }
        });
    }
    
    // Función para mostrar usuarios de sucursales
    function mostrarUsuariosSucursales(sucursales, usuariosLocal) {
        var html = '';
        
        // Mostrar usuarios locales primero
        if(usuariosLocal && usuariosLocal.length > 0) {
            html += '<div class="box box-primary">';
            html += '<div class="box-header with-border">';
            html += '<h3 class="box-title">';
            html += '<i class="fa fa-home"></i> Sucursal Local (Pruebas)';
            html += '<small class="label label-primary">' + usuariosLocal.length + ' usuarios</small>';
            html += '</h3>';
            html += '<div class="box-tools pull-right">';
            html += '<span class="label label-success">Conectado</span>';
            html += '</div>';
            html += '</div>';
            html += '<div class="box-body">';
            
            html += '<div class="table-responsive">';
            html += '<table class="table table-bordered table-striped">';
            html += '<thead><tr><th>Usuario</th><th>Nombre</th><th>Perfil</th><th>Empresa Actual</th><th>Estado</th><th>Acciones</th></tr></thead>';
            html += '<tbody>';
            
            usuariosLocal.forEach(function(usuario) {
                html += '<tr>';
                html += '<td><strong>' + (usuario.usuario || 'N/A') + '</strong></td>';
                html += '<td>' + (usuario.nombre || 'N/A') + '</td>';
                html += '<td><span class="label label-info">' + (usuario.perfil || 'N/A') + '</span></td>';
                html += '<td>' + (usuario.empresa || 'Sin empresa asignada') + '</td>';
                html += '<td><span class="label label-' + (usuario.estado == 1 ? 'success' : 'danger') + '">' + (usuario.estado == 1 ? 'Activo' : 'Inactivo') + '</span></td>';
                html += '<td>';
                html += '<button class="btn btn-success btn-xs btnImportarUsuario" data-usuario="' + JSON.stringify(usuario).replace(/"/g, '&quot;') + '"><i class="fa fa-download"></i> Importar</button>';
                html += '</td>';
                html += '</tr>';
            });
            
            html += '</tbody></table>';
            html += '</div>';
            html += '</div></div>';
        }
        
        // Mostrar sucursales remotas
        if(sucursales && sucursales.length > 0) {
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
                
                if(sucursal.estado_conexion === 'conectado' && sucursal.usuarios && sucursal.usuarios.length > 0) {
                    html += '<div class="table-responsive">';
                    html += '<table class="table table-bordered table-striped">';
                    html += '<thead><tr><th>Usuario</th><th>Nombre</th><th>Perfil</th><th>Empresa Actual</th><th>Estado</th><th>Acciones</th></tr></thead>';
                    html += '<tbody>';
                    
                    sucursal.usuarios.forEach(function(usuario) {
                        html += '<tr>';
                        html += '<td><strong>' + (usuario.usuario || 'N/A') + '</strong></td>';
                        html += '<td>' + (usuario.nombre || 'N/A') + '</td>';
                        html += '<td><span class="label label-info">' + (usuario.perfil || 'N/A') + '</span></td>';
                        html += '<td>' + (usuario.empresa || 'Sin empresa asignada') + '</td>';
                        html += '<td><span class="label label-' + (usuario.estado == 1 ? 'success' : 'danger') + '">' + (usuario.estado == 1 ? 'Activo' : 'Inactivo') + '</span></td>';
                        html += '<td>';
                        html += '<button class="btn btn-success btn-xs btnImportarUsuario" data-usuario="' + JSON.stringify(usuario).replace(/"/g, '&quot;') + '"><i class="fa fa-download"></i> Importar</button>';
                        html += '</td>';
                        html += '</tr>';
                    });
                    
                    html += '</tbody></table>';
                    html += '</div>';
                } else {
                    html += '<div class="alert alert-' + estadoClass + '">';
                    html += '<i class="fa fa-exclamation-triangle"></i> No se pudo conectar con esta sucursal o no tiene usuarios.';
                    html += '</div>';
                }
                
                html += '</div></div>';
            });
        }
        
        $("#contenidoUsuariosSucursales").html(html);
    }
    
    // Función para importar usuarios de sucursales
    function importarUsuariosSucursales() {
        swal({
            title: "Importar Usuarios",
            text: "¿Desea importar todos los usuarios disponibles de las sucursales?",
            type: "warning",
            showCancelButton: true,
            confirmButtonText: "Sí, importar",
            cancelButtonText: "Cancelar"
        }).then(function(result) {
            if(result.value) {
                $.ajax({
                    url: "ajax/usuarios-central.ajax.php",
                    method: "POST",
                    data: { accion: "importar_usuarios_sucursales" },
                    dataType: "json",
                    success: function(respuesta) {
                        if(respuesta.success) {
                            swal("Éxito", "Usuarios importados correctamente", "success");
                            cargarUsuariosCentrales();
                        } else {
                            swal("Error", respuesta.error, "error");
                        }
                    }
                });
            }
        });
    }
    
    // Función para sincronizar usuarios
    function sincronizarUsuarios() {
        swal({
            title: "Sincronizar Usuarios",
            text: "¿Desea sincronizar todos los usuarios centrales con las sucursales?",
            type: "warning",
            showCancelButton: true,
            confirmButtonText: "Sí, sincronizar",
            cancelButtonText: "Cancelar"
        }).then(function(result) {
            if(result.value) {
                $.ajax({
                    url: "ajax/usuarios-central.ajax.php",
                    method: "POST",
                    data: { accion: "sincronizar_usuarios" },
                    dataType: "json",
                    success: function(respuesta) {
                        if(respuesta.success) {
                            swal("Éxito", "Usuarios sincronizados correctamente", "success");
                        } else {
                            swal("Error", respuesta.error, "error");
                        }
                    }
                });
            }
        });
    }
    
    // Función para guardar usuario central
    function guardarUsuarioCentral() {
        var formData = $("#formNuevoUsuarioCentral").serialize();
        
        $.ajax({
            url: "ajax/usuarios-central.ajax.php",
            method: "POST",
            data: formData + "&accion=crear_usuario_central",
            dataType: "json",
            success: function(respuesta) {
                if(respuesta.success) {
                    swal("Éxito", "Usuario creado correctamente", "success");
                    $("#modalNuevoUsuarioCentral").modal("hide");
                    $("#formNuevoUsuarioCentral")[0].reset();
                    cargarUsuariosCentrales();
                } else {
                    swal("Error", respuesta.error, "error");
                }
            }
        });
    }
    
    // Evento para importar usuario individual
    $(document).on("click", ".btnImportarUsuario", function() {
        var usuario = JSON.parse($(this).attr("data-usuario"));
        
        swal({
            title: "Importar Usuario",
            text: "¿Desea importar el usuario '" + usuario.usuario + "' a la base de datos central?",
            type: "warning",
            showCancelButton: true,
            confirmButtonText: "Sí, importar",
            cancelButtonText: "Cancelar"
        }).then(function(result) {
            if(result.value) {
                $.ajax({
                    url: "ajax/usuarios-central.ajax.php",
                    method: "POST",
                    data: {
                        accion: "importar_usuario_individual",
                        usuario: JSON.stringify(usuario)
                    },
                    dataType: "json",
                    success: function(respuesta) {
                        if(respuesta.success) {
                            swal("Éxito", "Usuario importado correctamente", "success");
                            cargarUsuariosCentrales();
                        } else {
                            swal("Error", respuesta.error, "error");
                        }
                    }
                });
            }
        });
    });
    
});
</script>

<!-- Incluir JavaScript específico para usuarios centrales -->
<script src="vistas/js/usuarios-central.js"></script>