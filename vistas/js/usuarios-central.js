$(document).ready(function() {
    
    // Variables globales
    var usuariosSucursales = [];
    var usuariosCentrales = [];
    
    // Inicializar la interfaz
    inicializarInterfaz();
    
    // Cargar estadísticas
    cargarEstadisticas();
    
    // Cargar usuarios de sucursales
    cargarUsuariosSucursales();
    
    // Cargar usuarios centrales
    cargarUsuariosCentrales();
    
    // Event listeners
    $(document).on("click", ".btnImportarUsuario", function() {
        var usuario = $(this).data("usuario");
        importarUsuarioIndividual(usuario);
    });
    
    $(document).on("click", ".btnImportarTodos", function() {
        importarTodosUsuarios();
    });
    
    $(document).on("click", ".btnSincronizar", function() {
        sincronizarUsuarios();
    });
    
    $(document).on("click", ".btnCrearUsuario", function() {
        mostrarModalCrearUsuario();
    });
    
    $(document).on("click", ".btnEditarUsuario", function() {
        var id = $(this).data("id");
        editarUsuario(id);
    });
    
    $(document).on("click", ".btnEliminarUsuario", function() {
        var id = $(this).data("id");
        eliminarUsuario(id);
    });
    
    // Funciones principales
    function inicializarInterfaz() {
        console.log("Inicializando interfaz de usuarios centrales...");
        
        // Configurar tabs
        $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            var target = $(e.target).attr("href");
            
            if (target === "#usuariosSucursales") {
                cargarUsuariosSucursales();
            } else if (target === "#usuariosCentrales") {
                cargarUsuariosCentrales();
            }
        });
    }
    
    function cargarEstadisticas() {
        console.log("Cargando estadísticas...");
        
        $.ajax({
            url: "ajax/usuarios-central.ajax.php",
            method: "POST",
            data: { accion: "obtener_estadisticas" },
            dataType: "json",
            success: function(respuesta) {
                if (respuesta.success) {
                    mostrarEstadisticas(respuesta.estadisticas);
                } else {
                    console.error("Error cargando estadísticas:", respuesta.error);
                    mostrarError("Error cargando estadísticas: " + respuesta.error);
                }
            },
            error: function(xhr, status, error) {
                console.error("Error AJAX cargando estadísticas:", error);
                mostrarError("Error de conexión cargando estadísticas");
            }
        });
    }
    
    function cargarUsuariosSucursales() {
        console.log("Cargando usuarios de sucursales...");
        
        // Mostrar loading
        $("#usuariosSucursales").html('<div class="text-center"><i class="fa fa-spinner fa-spin"></i> Cargando usuarios de sucursales...</div>');
        
        $.ajax({
            url: "ajax/usuarios-central.ajax.php",
            method: "POST",
            data: { accion: "obtener_usuarios_sucursales" },
            dataType: "json",
            success: function(respuesta) {
                if (respuesta.success) {
                    usuariosSucursales = respuesta.usuarios;
                    mostrarUsuariosSucursales(respuesta.usuarios);
                } else {
                    console.error("Error cargando usuarios de sucursales:", respuesta.error);
                    $("#usuariosSucursales").html('<div class="alert alert-danger">Error cargando usuarios: ' + respuesta.error + '</div>');
                }
            },
            error: function(xhr, status, error) {
                console.error("Error AJAX cargando usuarios de sucursales:", error);
                $("#usuariosSucursales").html('<div class="alert alert-danger">Error de conexión cargando usuarios de sucursales</div>');
            }
        });
    }
    
    function cargarUsuariosCentrales() {
        console.log("Cargando usuarios centrales...");
        
        // Mostrar loading
        $("#usuariosCentrales").html('<div class="text-center"><i class="fa fa-spinner fa-spin"></i> Cargando usuarios centrales...</div>');
        
        $.ajax({
            url: "ajax/usuarios-central.ajax.php",
            method: "POST",
            data: { accion: "obtener_usuarios_centrales" },
            dataType: "json",
            success: function(respuesta) {
                if (respuesta.success) {
                    usuariosCentrales = respuesta.usuarios;
                    mostrarUsuariosCentrales(respuesta.usuarios);
                } else {
                    console.error("Error cargando usuarios centrales:", respuesta.error);
                    $("#usuariosCentrales").html('<div class="alert alert-danger">Error cargando usuarios centrales: ' + respuesta.error + '</div>');
                }
            },
            error: function(xhr, status, error) {
                console.error("Error AJAX cargando usuarios centrales:", error);
                $("#usuariosCentrales").html('<div class="alert alert-danger">Error de conexión cargando usuarios centrales</div>');
            }
        });
    }
    
    function mostrarEstadisticas(estadisticas) {
        var html = '<div class="row">';
        html += '<div class="col-md-3"><div class="info-box bg-blue"><span class="info-box-icon"><i class="fa fa-users"></i></span><div class="info-box-content"><span class="info-box-text">Total Usuarios</span><span class="info-box-number">' + estadisticas.total_usuarios + '</span></div></div></div>';
        html += '<div class="col-md-3"><div class="info-box bg-green"><span class="info-box-icon"><i class="fa fa-check"></i></span><div class="info-box-content"><span class="info-box-text">Sincronizados</span><span class="info-box-number">' + estadisticas.sincronizados + '</span></div></div></div>';
        html += '<div class="col-md-3"><div class="info-box bg-yellow"><span class="info-box-icon"><i class="fa fa-clock-o"></i></span><div class="info-box-content"><span class="info-box-text">Pendientes</span><span class="info-box-number">' + estadisticas.pendientes + '</span></div></div></div>';
        html += '<div class="col-md-3"><div class="info-box bg-red"><span class="info-box-icon"><i class="fa fa-times"></i></span><div class="info-box-content"><span class="info-box-text">Errores</span><span class="info-box-number">' + estadisticas.errores + '</span></div></div></div>';
        html += '</div>';
        
        $("#estadisticasSistema").html(html);
    }
    
    function mostrarUsuariosSucursales(usuarios) {
        var html = '';
        
        if (usuarios.length === 0) {
            html = '<div class="alert alert-info">No hay sucursales configuradas</div>';
        } else {
        usuarios.forEach(function(sucursal, index) {
            html += '<div class="box box-primary">';
            html += '<div class="box-header with-border">';
            html += '<h3 class="box-title"><i class="fa fa-building"></i> ' + sucursal.sucursal.nombre;
            
            // Mostrar identificador de sucursal actual
            if (sucursal.es_actual) {
                html += ' <span class="label label-info"><i class="fa fa-home"></i> ACTUAL</span>';
            }
            
            html += '</h3>';
            html += '<div class="box-tools pull-right">';
            html += '<span class="label label-' + (sucursal.estado_conexion === 'conectado' ? 'success' : 'danger') + '">';
            html += sucursal.estado_conexion === 'conectado' ? 'Conectado' : 'Error';
            html += '</span>';
            html += '</div>';
            html += '</div>';
                html += '<div class="box-body">';
                
                if (sucursal.estado_conexion === 'conectado' && sucursal.usuarios.length > 0) {
                    html += '<p><strong>Total usuarios:</strong> ' + sucursal.total_usuarios + '</p>';
                    html += '<div class="table-responsive">';
                    html += '<table class="table table-striped">';
                    html += '<thead><tr><th>Usuario</th><th>Nombre</th><th>Perfil</th><th>Empresa</th><th>Estado</th><th>Acciones</th></tr></thead>';
                    html += '<tbody>';
                    
                    sucursal.usuarios.forEach(function(usuario) {
                        html += '<tr>';
                        html += '<td>' + usuario.usuario + '</td>';
                        html += '<td>' + usuario.nombre + '</td>';
                        html += '<td><span class="label label-info">' + usuario.perfil + '</span></td>';
                        html += '<td>' + usuario.empresa + '</td>';
                        html += '<td><span class="label label-' + (usuario.estado == 1 ? 'success' : 'danger') + '">' + (usuario.estado == 1 ? 'Activo' : 'Inactivo') + '</span></td>';
                        html += '<td>';
                        html += '<button class="btn btn-success btn-xs btnImportarUsuario" data-usuario=\'' + JSON.stringify(usuario) + '\'>';
                        html += '<i class="fa fa-download"></i> Importar';
                        html += '</button>';
                        html += '</td>';
                        html += '</tr>';
                    });
                    
                    html += '</tbody>';
                    html += '</table>';
                    html += '</div>';
                    
                    html += '<div class="text-center">';
                    html += '<button class="btn btn-primary btnImportarTodos" data-sucursal="' + sucursal.sucursal.id + '">';
                    html += '<i class="fa fa-download"></i> Importar Todos';
                    html += '</button>';
                    html += '</div>';
                } else {
                    html += '<div class="alert alert-warning">';
                    if (sucursal.estado_conexion === 'error') {
                        html += '<strong>Error de conexión:</strong> ' + sucursal.error;
                    } else {
                        html += 'No hay usuarios en esta sucursal';
                    }
                    html += '</div>';
                }
                
                html += '</div>';
                html += '</div>';
            });
        }
        
        $("#usuariosSucursales").html(html);
    }
    
    function mostrarUsuariosCentrales(usuarios) {
        var html = '';
        
        if (usuarios.length === 0) {
            html = '<div class="alert alert-info">No hay usuarios centrales</div>';
        } else {
            html += '<div class="table-responsive">';
            html += '<table class="table table-striped">';
            html += '<thead><tr><th>Usuario</th><th>Nombre</th><th>Perfil</th><th>Sucursal</th><th>Estado</th><th>Acciones</th></tr></thead>';
            html += '<tbody>';
            
            usuarios.forEach(function(usuario) {
                html += '<tr>';
                html += '<td>' + usuario.usuario + '</td>';
                html += '<td>' + usuario.nombre + '</td>';
                html += '<td><span class="label label-info">' + usuario.perfil + '</span></td>';
                html += '<td>' + usuario.sucursal_nombre + '</td>';
                html += '<td><span class="label label-' + (usuario.activo == 1 ? 'success' : 'danger') + '">' + (usuario.activo == 1 ? 'Activo' : 'Inactivo') + '</span></td>';
                html += '<td>';
                html += '<button class="btn btn-warning btn-xs btnEditarUsuario" data-id="' + usuario.id + '">';
                html += '<i class="fa fa-edit"></i>';
                html += '</button>';
                html += '<button class="btn btn-danger btn-xs btnEliminarUsuario" data-id="' + usuario.id + '">';
                html += '<i class="fa fa-trash"></i>';
                html += '</button>';
                html += '</td>';
                html += '</tr>';
            });
            
            html += '</tbody>';
            html += '</table>';
            html += '</div>';
        }
        
        $("#usuariosCentrales").html(html);
    }
    
    function importarUsuarioIndividual(usuario) {
        console.log("Importando usuario individual:", usuario);
        
        $.ajax({
            url: "ajax/usuarios-central.ajax.php",
            method: "POST",
            data: { 
                accion: "importar_usuario_individual",
                usuario: JSON.stringify(usuario)
            },
            dataType: "json",
            success: function(respuesta) {
                if (respuesta.success) {
                    mostrarExito("Usuario importado exitosamente");
                    cargarUsuariosCentrales();
                    cargarEstadisticas();
                } else {
                    mostrarError("Error importando usuario: " + respuesta.error);
                }
            },
            error: function(xhr, status, error) {
                console.error("Error AJAX importando usuario:", error);
                mostrarError("Error de conexión importando usuario");
            }
        });
    }
    
    function importarTodosUsuarios() {
        console.log("Importando todos los usuarios...");
        
        $.ajax({
            url: "ajax/usuarios-central.ajax.php",
            method: "POST",
            data: { accion: "importar_usuarios_sucursales" },
            dataType: "json",
            success: function(respuesta) {
                if (respuesta.success) {
                    mostrarExito("Usuarios importados exitosamente");
                    cargarUsuariosCentrales();
                    cargarEstadisticas();
                } else {
                    mostrarError("Error importando usuarios: " + respuesta.error);
                }
            },
            error: function(xhr, status, error) {
                console.error("Error AJAX importando usuarios:", error);
                mostrarError("Error de conexión importando usuarios");
            }
        });
    }
    
    function sincronizarUsuarios() {
        console.log("Sincronizando usuarios...");
        
        $.ajax({
            url: "ajax/usuarios-central.ajax.php",
            method: "POST",
            data: { accion: "sincronizar_usuarios" },
            dataType: "json",
            success: function(respuesta) {
                if (respuesta.success) {
                    mostrarExito("Usuarios sincronizados exitosamente");
                    cargarUsuariosCentrales();
                    cargarEstadisticas();
                } else {
                    mostrarError("Error sincronizando usuarios: " + respuesta.error);
                }
            },
            error: function(xhr, status, error) {
                console.error("Error AJAX sincronizando usuarios:", error);
                mostrarError("Error de conexión sincronizando usuarios");
            }
        });
    }
    
    function mostrarModalCrearUsuario() {
        // Implementar modal de crear usuario
        console.log("Mostrando modal crear usuario");
    }
    
    function editarUsuario(id) {
        // Implementar edición de usuario
        console.log("Editando usuario:", id);
    }
    
    function eliminarUsuario(id) {
        if (confirm("¿Está seguro de eliminar este usuario?")) {
            $.ajax({
                url: "ajax/usuarios-central.ajax.php",
                method: "POST",
                data: { 
                    accion: "eliminar_usuario_central",
                    id: id
                },
                dataType: "json",
                success: function(respuesta) {
                    if (respuesta.success) {
                        mostrarExito("Usuario eliminado exitosamente");
                        cargarUsuariosCentrales();
                        cargarEstadisticas();
                    } else {
                        mostrarError("Error eliminando usuario: " + respuesta.error);
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Error AJAX eliminando usuario:", error);
                    mostrarError("Error de conexión eliminando usuario");
                }
            });
        }
    }
    
    function mostrarExito(mensaje) {
        swal({
            type: "success",
            title: "Éxito",
            text: mensaje
        });
    }
    
    function mostrarError(mensaje) {
        swal({
            type: "error",
            title: "Error",
            text: mensaje
        });
    }
    
});
