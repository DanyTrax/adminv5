$(document).ready(function() {
    
    // Variables globales
    var usuariosSucursales = [];
    var usuariosCentrales = [];
    var sucursalesDisponibles = [];
    var sucursalesSeleccionadas = [];
    
    // Inicializar la interfaz
    inicializarInterfaz();
    
    // Cargar estadísticas
    cargarEstadisticas();
    
    // Cargar sucursales disponibles
    cargarSucursalesDisponibles();
    
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
        abrirModalUsuario();
    });
    
    $(document).on("click", "#btnNuevoUsuarioCentral", function() {
        abrirModalUsuario();
    });
    
    $(document).on("click", "#btnSincronizarTodosUsuarios", function() {
        sincronizarTodosUsuarios();
    });
    
    $(document).on("click", ".btnEditarUsuario", function() {
        var id = $(this).data("id");
        editarUsuario(id);
    });
    
    $(document).on("click", ".btnEliminarUsuario", function() {
        var id = $(this).data("id");
        eliminarUsuario(id);
    });
    
    $(document).on("change", ".checkbox-sucursal", function() {
        actualizarSucursalesSeleccionadas();
    });
    
    $(document).on("click", "#btnSincronizarSeleccionadas", function() {
        sincronizarUsuariosSeleccionadas();
    });
    
    $(document).on("change", ".checkbox-sucursal-asignada", function() {
        actualizarSucursalesAsignadas();
    });
    
    $(document).on("click", "#btnGuardarUsuario", function() {
        guardarUsuario();
    });
    
    $(document).on("click", ".btnAsignarSucursales", function() {
        var id = $(this).data("id");
        var nombre = $(this).data("nombre");
        asignarSucursales(id, nombre);
    });
    
    $(document).on("click", "#btnGuardarAsignacion", function() {
        guardarAsignacionSucursales();
    });
    
    // Validación en tiempo real
    $(document).on("input", "#nombreUsuario", function() {
        limpiarErrorCampo("errorNombre");
    });
    $(document).on("input", "#usuarioLogin", function() {
        limpiarErrorCampo("errorUsuario");
    });
    $(document).on("input", "#passwordUsuario", function() {
        limpiarErrorCampo("errorPassword");
    });
    $(document).on("change", "#perfilUsuario", function() {
        limpiarErrorCampo("errorPerfil");
    });
    $(document).on("input", "#telefonoUsuario", function() {
        limpiarErrorCampo("errorTelefono");
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
        
        // Asegurar que las sucursales estén cargadas antes de mostrar usuarios
        if (sucursalesDisponibles.length === 0) {
            console.log("Sucursales no cargadas, cargando primero...");
            cargarSucursalesDisponibles();
            
            // Esperar un poco y reintentar
            setTimeout(function() {
                cargarUsuariosCentrales();
            }, 1000);
            return;
        }
        
        $.ajax({
            url: "ajax/usuarios-central.ajax.php",
            method: "POST",
            data: { accion: "obtener_usuarios_centrales" },
            dataType: "json",
            success: function(respuesta) {
                if (respuesta.success) {
                    usuariosCentrales = respuesta.usuarios;
                    console.log("Usuarios centrales cargados:", usuariosCentrales);
                    console.log("Sucursales disponibles:", sucursalesDisponibles);
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
                        html += '<button class="btn btn-success btn-xs btnImportarUsuario" data-usuario=\'' + JSON.stringify(usuario) + '\' title="Importar usuario">';
                        html += '<i class="fa fa-download"></i>';
                        html += '</button>';
                        html += '</td>';
                        html += '</tr>';
                    });
                    
                    html += '</tbody>';
                    html += '</table>';
                    html += '</div>';
                    
                    html += '<div class="text-center">';
                    html += '<button class="btn btn-primary btn-sm btnImportarTodos" data-sucursal="' + sucursal.sucursal.id + '" title="Importar todos los usuarios de esta sucursal">';
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
            html += '<thead><tr><th>Usuario</th><th>Nombre</th><th>Perfil</th><th>Sucursales Asignadas</th><th>Estado</th><th>Acciones</th></tr></thead>';
            html += '<tbody>';
            
            usuarios.forEach(function(usuario) {
                // Obtener nombres de sucursales asignadas
                var sucursalesNombres = [];
                if (usuario.sucursales_asignadas) {
                    var sucursalesIds = usuario.sucursales_asignadas.split(',');
                    sucursalesIds.forEach(function(sucursalId) {
                        var sucursal = sucursalesDisponibles.find(function(s) {
                            return s.id == sucursalId;
                        });
                        if (sucursal) {
                            sucursalesNombres.push(sucursal.nombre);
                        }
                    });
                }
                
                html += '<tr>';
                html += '<td>' + usuario.usuario + '</td>';
                html += '<td>' + usuario.nombre + '</td>';
                html += '<td><span class="label label-info">' + usuario.perfil + '</span></td>';
                html += '<td>';
                if (sucursalesNombres.length > 0) {
                    sucursalesNombres.forEach(function(nombre, index) {
                        html += '<span class="label label-primary" style="margin-right: 3px;">' + nombre + '</span>';
                        if (index < sucursalesNombres.length - 1) {
                            html += ' ';
                        }
                    });
                } else {
                    html += '<span class="text-muted">Sin sucursales asignadas</span>';
                }
                html += '</td>';
                html += '<td><span class="label label-' + (usuario.activo == 1 ? 'success' : 'danger') + '">' + (usuario.activo == 1 ? 'Activo' : 'Inactivo') + '</span></td>';
                html += '<td>';
                html += '<button class="btn btn-warning btn-xs btnEditarUsuario" data-id="' + usuario.id + '" title="Editar usuario">';
                html += '<i class="fa fa-edit"></i>';
                html += '</button>';
                html += '<button class="btn btn-info btn-xs btnAsignarSucursales" data-id="' + usuario.id + '" data-nombre="' + usuario.nombre + '" title="Asignar sucursales">';
                html += '<i class="fa fa-building"></i>';
                html += '</button>';
                html += '<button class="btn btn-danger btn-xs btnEliminarUsuario" data-id="' + usuario.id + '" title="Eliminar usuario">';
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
        console.log("Editando usuario:", id);
        
        // Buscar el usuario en la lista de usuarios centrales
        var usuario = usuariosCentrales.find(function(u) {
            return u.id == id;
        });
        
        if (!usuario) {
            mostrarError("Usuario no encontrado");
            return;
        }
        
        console.log("Usuario encontrado:", usuario);
        
        // Crear objeto usuario para edición (sin sucursales, ya que se manejan por separado)
        var usuarioEdit = {
            id: usuario.id,
            nombre: usuario.nombre,
            usuario: usuario.usuario,
            password: usuario.password,
            perfil: usuario.perfil,
            telefono: usuario.telefono || ''
        };
        
        // Abrir modal en modo edición
        abrirModalUsuario(usuarioEdit);
    }
    
    function eliminarUsuario(id) {
        if (confirm("¿Estás seguro de que quieres eliminar este usuario? Esta acción no se puede deshacer.")) {
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
                        alert("Usuario eliminado exitosamente");
                        cargarUsuariosCentrales();
                        cargarEstadisticas();
                    } else {
                        alert("Error eliminando usuario: " + (respuesta.error || "Error desconocido"));
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Error AJAX eliminando usuario:", {xhr, status, error});
                    alert("Error eliminando usuario: Error de conexión");
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
    
    function cargarSucursalesDisponibles() {
        console.log("Cargando sucursales disponibles...");
        
        $.ajax({
            url: "ajax/usuarios-central.ajax.php",
            method: "POST",
            data: { accion: "obtener_sucursales_disponibles" },
            dataType: "json",
            success: function(respuesta) {
                if (respuesta.success) {
                    sucursalesDisponibles = respuesta.sucursales;
                    console.log("Sucursales cargadas:", sucursalesDisponibles);
                    mostrarSucursalesSeleccion();
                    
                    // Si el modal está abierto, actualizar las sucursales
                    if ($("#modalUsuarioCentral").hasClass('in') || $("#modalUsuarioCentral").is(':visible')) {
                        cargarSucursalesAsignadas();
                    }
                } else {
                    console.error("Error cargando sucursales:", respuesta.error);
                }
            },
            error: function(xhr, status, error) {
                console.error("Error AJAX cargando sucursales:", error);
            }
        });
    }
    
    function mostrarSucursalesSeleccion() {
        var html = '';
        
        sucursalesDisponibles.forEach(function(sucursal) {
            html += '<div class="col-md-4">';
            html += '<div class="checkbox">';
            html += '<label>';
            html += '<input type="checkbox" class="checkbox-sucursal" value="' + sucursal.id + '" data-sucursal=\'' + JSON.stringify(sucursal) + '\'>';
            html += '<strong>' + sucursal.nombre + '</strong>';
            if (sucursal.es_actual) {
                html += ' <span class="label label-info">ACTUAL</span>';
            }
            html += '</label>';
            html += '</div>';
            html += '</div>';
        });
        
        $("#sucursalesSeleccion").html(html);
    }
    
    function actualizarSucursalesSeleccionadas() {
        sucursalesSeleccionadas = [];
        
        $(".checkbox-sucursal:checked").each(function() {
            var sucursalData = $(this).data("sucursal");
            sucursalesSeleccionadas.push(sucursalData);
        });
        
        // Habilitar/deshabilitar botón de sincronización
        if (sucursalesSeleccionadas.length > 0) {
            $("#btnSincronizarSeleccionadas").prop("disabled", false);
        } else {
            $("#btnSincronizarSeleccionadas").prop("disabled", true);
        }
        
        console.log("Sucursales seleccionadas:", sucursalesSeleccionadas.length);
    }
    
    function sincronizarUsuariosSeleccionadas() {
        if (sucursalesSeleccionadas.length === 0) {
            mostrarError("Selecciona al menos una sucursal para sincronizar");
            return;
        }
        
        console.log("Sincronizando usuarios a sucursales seleccionadas:", sucursalesSeleccionadas);
        
        $.ajax({
            url: "ajax/usuarios-central.ajax.php",
            method: "POST",
            data: { 
                accion: "sincronizar_usuarios_sucursales",
                sucursales: JSON.stringify(sucursalesSeleccionadas)
            },
            dataType: "json",
            success: function(respuesta) {
                if (respuesta.success) {
                    mostrarExito("Usuarios sincronizados exitosamente a " + sucursalesSeleccionadas.length + " sucursales");
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
    
    function abrirModalUsuario(usuario = null) {
        console.log("Abriendo modal usuario:", usuario);
        
        if (usuario) {
            // Modo edición
            console.log("Llenando campos del modal con datos:", usuario);
            $("#tituloModalUsuario").html('<i class="fa fa-edit"></i> Editar Usuario Central');
            $("#idUsuarioCentral").val(usuario.id);
            $("#nombreUsuario").val(usuario.nombre);
            $("#usuarioLogin").val(usuario.usuario);
            $("#passwordUsuario").val(usuario.password || '');
            $("#perfilUsuario").val(usuario.perfil);
            $("#telefonoUsuario").val(usuario.telefono || '');
            
            console.log("Campos llenados - ID:", $("#idUsuarioCentral").val());
            console.log("Campos llenados - Nombre:", $("#nombreUsuario").val());
            console.log("Campos llenados - Usuario:", $("#usuarioLogin").val());
        } else {
            // Modo creación
            $("#tituloModalUsuario").html('<i class="fa fa-user"></i> Crear Usuario Central');
            $("#formUsuarioCentral")[0].reset();
            $("#idUsuarioCentral").val('');
        }
        
        $("#modalUsuarioCentral").modal("show");
    }
    
    function cargarSucursalesAsignadas(sucursalesAsignadas = null) {
        console.log("Cargando sucursales asignadas:", sucursalesAsignadas);
        console.log("Sucursales disponibles:", sucursalesDisponibles);
        
        var html = '';
        
        if (sucursalesDisponibles.length === 0) {
            html = '<div class="alert alert-warning">Cargando sucursales...</div>';
            $("#sucursalesAsignadas").html(html);
            
            // Cargar sucursales si no están disponibles
            cargarSucursalesDisponibles();
            return;
        }
        
        sucursalesDisponibles.forEach(function(sucursal) {
            var checked = '';
            if (sucursalesAsignadas && sucursalesAsignadas.includes(sucursal.id.toString())) {
                checked = 'checked';
            }
            
            html += '<div class="col-md-4">';
            html += '<div class="checkbox">';
            html += '<label>';
            html += '<input type="checkbox" class="checkbox-sucursal-asignada" value="' + sucursal.id + '" data-sucursal=\'' + JSON.stringify(sucursal) + '\' ' + checked + '>';
            html += '<strong>' + sucursal.nombre + '</strong>';
            if (sucursal.es_actual) {
                html += ' <span class="label label-info">ACTUAL</span>';
            }
            html += '</label>';
            html += '</div>';
            html += '</div>';
        });
        
        $("#sucursalesAsignadas").html(html);
    }
    
    function actualizarSucursalesAsignadas() {
        var sucursalesSeleccionadas = [];
        
        $(".checkbox-sucursal-asignada:checked").each(function() {
            sucursalesSeleccionadas.push($(this).val());
        });
        
        console.log("Sucursales asignadas:", sucursalesSeleccionadas);
    }
    
    function guardarUsuario() {
        // Limpiar errores anteriores
        limpiarErrores();
        
        // Validar formulario
        if (!validarFormularioUsuario()) {
            return;
        }
        
        var formData = {
            id: $("#idUsuarioCentral").val(),
            nombre: $("#nombreUsuario").val().trim(),
            usuario: $("#usuarioLogin").val().trim(),
            password: $("#passwordUsuario").val(),
            perfil: $("#perfilUsuario").val(),
            telefono: $("#telefonoUsuario").val().trim()
        };
        
        var accion = formData.id ? "editar_usuario_central" : "crear_usuario_central";
        
        $.ajax({
            url: "ajax/usuarios-central.ajax.php",
            method: "POST",
            data: {
                accion: accion,
                datos: JSON.stringify(formData)
            },
            dataType: "json",
            success: function(respuesta) {
                if (respuesta.success) {
                    mostrarExito(respuesta.message);
                    $("#modalUsuarioCentral").modal("hide");
                    cargarUsuariosCentrales();
                    cargarEstadisticas();
                } else {
                    mostrarError(respuesta.error);
                }
            },
            error: function(xhr, status, error) {
                console.error("Error AJAX guardando usuario:", error);
                mostrarError("Error de conexión guardando usuario");
            }
        });
    }
    
    function validarFormularioUsuario() {
        var esValido = true;
        
        // Validar nombre
        var nombre = $("#nombreUsuario").val().trim();
        if (nombre.length < 2) {
            mostrarErrorCampo("errorNombre", "El nombre debe tener al menos 2 caracteres");
            esValido = false;
        } else if (nombre.length > 100) {
            mostrarErrorCampo("errorNombre", "El nombre no puede tener más de 100 caracteres");
            esValido = false;
        }
        
        // Validar usuario
        var usuario = $("#usuarioLogin").val().trim();
        if (usuario.length < 3) {
            mostrarErrorCampo("errorUsuario", "El usuario debe tener al menos 3 caracteres");
            esValido = false;
        } else if (usuario.length > 50) {
            mostrarErrorCampo("errorUsuario", "El usuario no puede tener más de 50 caracteres");
            esValido = false;
        } else if (!/^[a-zA-Z0-9]+$/.test(usuario)) {
            mostrarErrorCampo("errorUsuario", "El usuario solo puede contener letras y números");
            esValido = false;
        }
        
        // Validar contraseña
        var password = $("#passwordUsuario").val();
        if (password.length < 4) {
            mostrarErrorCampo("errorPassword", "La contraseña debe tener al menos 4 caracteres");
            esValido = false;
        } else if (password.length > 50) {
            mostrarErrorCampo("errorPassword", "La contraseña no puede tener más de 50 caracteres");
            esValido = false;
        }
        
        // Validar perfil
        var perfil = $("#perfilUsuario").val();
        if (!perfil) {
            mostrarErrorCampo("errorPerfil", "Debe seleccionar un perfil");
            esValido = false;
        }
        
        // Validar teléfono
        var telefono = $("#telefonoUsuario").val().trim();
        if (telefono.length < 7) {
            mostrarErrorCampo("errorTelefono", "El teléfono debe tener al menos 7 caracteres");
            esValido = false;
        } else if (telefono.length > 20) {
            mostrarErrorCampo("errorTelefono", "El teléfono no puede tener más de 20 caracteres");
            esValido = false;
        } else if (!/^[0-9+\-\s()]+$/.test(telefono)) {
            mostrarErrorCampo("errorTelefono", "El teléfono contiene caracteres no válidos");
            esValido = false;
        }
        
        return esValido;
    }
    
    function mostrarErrorCampo(campoId, mensaje) {
        $("#" + campoId).text(mensaje).show();
        $("#" + campoId.replace("error", "")).addClass("has-error");
    }
    
    function limpiarErrores() {
        $(".help-block").hide();
        $(".form-group").removeClass("has-error");
    }
    
    function limpiarErrorCampo(campoId) {
        $("#" + campoId).hide();
        $("#" + campoId.replace("error", "")).removeClass("has-error");
    }
    
    // Variables para asignación de sucursales
    var usuarioAsignacionId = null;
    var sucursalesAsignacion = [];
    
    function asignarSucursales(id, nombre) {
        console.log("Asignando sucursales al usuario:", id, nombre);
        
        usuarioAsignacionId = id;
        $("#nombreUsuarioAsignar").text(nombre);
        
        // Buscar el usuario para obtener sus sucursales actuales
        var usuario = usuariosCentrales.find(function(u) {
            return u.id == id;
        });
        
        var sucursalesActuales = [];
        if (usuario && usuario.sucursales_asignadas) {
            sucursalesActuales = usuario.sucursales_asignadas.split(',');
        }
        
        // Cargar sucursales en el modal de asignación
        cargarSucursalesAsignacion(sucursalesActuales);
        
        $("#modalAsignarSucursales").modal("show");
    }
    
    function cargarSucursalesAsignacion(sucursalesActuales = []) {
        console.log("Cargando sucursales para asignación:", sucursalesActuales);
        
        var html = '';
        
        if (sucursalesDisponibles.length === 0) {
            html = '<div class="alert alert-warning">Cargando sucursales...</div>';
            $("#sucursalesAsignar").html(html);
            
            // Cargar sucursales si no están disponibles
            cargarSucursalesDisponibles();
            return;
        }
        
        sucursalesDisponibles.forEach(function(sucursal) {
            var checked = '';
            if (sucursalesActuales.includes(sucursal.id.toString())) {
                checked = 'checked';
            }
            
            html += '<div class="col-md-4">';
            html += '<div class="checkbox">';
            html += '<label>';
            html += '<input type="checkbox" class="checkbox-sucursal-asignacion" value="' + sucursal.id + '" data-sucursal=\'' + JSON.stringify(sucursal) + '\' ' + checked + '>';
            html += '<strong>' + sucursal.nombre + '</strong>';
            if (sucursal.es_actual) {
                html += ' <span class="label label-info">ACTUAL</span>';
            }
            html += '</label>';
            html += '</div>';
            html += '</div>';
        });
        
        $("#sucursalesAsignar").html(html);
    }
    
    function guardarAsignacionSucursales() {
        if (!usuarioAsignacionId) {
            mostrarError("No se ha seleccionado un usuario");
            return;
        }
        
        // Recopilar sucursales seleccionadas
        var sucursalesSeleccionadas = [];
        $(".checkbox-sucursal-asignacion:checked").each(function() {
            sucursalesSeleccionadas.push($(this).val());
        });
        
        if (sucursalesSeleccionadas.length === 0) {
            mostrarError("Selecciona al menos una sucursal para el usuario");
            return;
        }
        
        console.log("Guardando asignación de sucursales:", sucursalesSeleccionadas);
        
        $.ajax({
            url: "ajax/usuarios-central.ajax.php",
            method: "POST",
            data: {
                accion: "asignar_sucursales_usuario",
                usuario_id: usuarioAsignacionId,
                sucursales: JSON.stringify(sucursalesSeleccionadas)
            },
            dataType: "json",
            success: function(respuesta) {
                if (respuesta.success) {
                    mostrarExito("Sucursales asignadas y sincronizadas exitosamente");
                    $("#modalAsignarSucursales").modal("hide");
                    cargarUsuariosCentrales();
                    cargarEstadisticas();
                } else {
                    mostrarError("Error asignando sucursales: " + respuesta.error);
                }
            },
            error: function(xhr, status, error) {
                console.error("Error AJAX asignando sucursales:", error);
                mostrarError("Error de conexión asignando sucursales");
            }
        });
    }
    
    function sincronizarTodosUsuarios() {
        // Mostrar loading inmediatamente
        var loadingDiv = $('<div id="loading-sync" style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 20px; border: 1px solid #ccc; border-radius: 5px; z-index: 9999; box-shadow: 0 4px 8px rgba(0,0,0,0.3);"><i class="fa fa-spinner fa-spin"></i> Sincronizando usuarios...</div>');
        $('body').append(loadingDiv);
        
        $.ajax({
            url: "ajax/sincronizar-usuarios-final.ajax.php",
            method: "POST",
            data: {},
            dataType: "json",
            success: function(respuesta) {
                console.log("Respuesta AJAX sincronización:", respuesta);
                $('#loading-sync').remove();
                
                if (respuesta && respuesta.success) {
                    alert('¡Sincronización exitosa!\n\n' + (respuesta.message || 'Usuarios sincronizados correctamente'));
                    cargarUsuariosCentrales();
                    cargarEstadisticas();
                } else {
                    alert('Error en sincronización:\n\n' + (respuesta.error || 'Error desconocido'));
                }
            },
            error: function(xhr, status, error) {
                console.error("Error AJAX sincronización:", {
                    xhr: xhr,
                    status: status,
                    error: error,
                    responseText: xhr.responseText
                });
                $('#loading-sync').remove();
                
                // Intentar parsear la respuesta como texto
                var responseText = xhr.responseText;
                try {
                    var jsonResponse = JSON.parse(responseText);
                    if (jsonResponse.success) {
                        alert('¡Sincronización exitosa!\n\n' + (jsonResponse.message || 'Usuarios sincronizados correctamente'));
                        cargarUsuariosCentrales();
                        cargarEstadisticas();
                        return;
                    }
                } catch (e) {
                    console.log("No se pudo parsear como JSON:", responseText);
                }
                
                alert('Error de conexión:\n\nNo se pudo completar la sincronización.\nVer consola para detalles.');
            }
        });
    }
    
});
