<?php
require_once "controladores/usuarios-central.controlador.php";

$sucursales = ControladorUsuariosCentral::ctrObtenerSucursalesDisponibles();
?>

<div class="content-wrapper">
    <section class="content-header">
        <h1>
            Gestión de Usuarios Centrales
            <small>Administración de usuarios del sistema central</small>
        </h1>
    </section>

    <section class="content">
        <div class="row">
            <!-- ESTADÍSTICAS DEL SISTEMA -->
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-bar-chart"></i> Estadísticas del Sistema
                        </h3>
                    </div>
                    <div class="box-body">
                        <div class="row" id="estadisticasSistema">
                            <div class="col-md-3">
                                <div class="info-box">
                                    <span class="info-box-icon bg-aqua"><i class="fa fa-users"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Usuarios Centrales</span>
                                        <span class="info-box-number" id="totalUsuariosCentral">-</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-box">
                                    <span class="info-box-icon bg-green"><i class="fa fa-building"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Sucursales Activas</span>
                                        <span class="info-box-number" id="sucursalesActivas">-</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-box">
                                    <span class="info-box-icon bg-yellow"><i class="fa fa-refresh"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Sincronizaciones Hoy</span>
                                        <span class="info-box-number" id="sincronizacionesHoy">-</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-box">
                                    <span class="info-box-icon bg-red"><i class="fa fa-exclamation-triangle"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Errores Hoy</span>
                                        <span class="info-box-number" id="erroresHoy">-</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- ACCIONES DEL SISTEMA -->
            <div class="col-md-12">
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-cogs"></i> Acciones del Sistema
                        </h3>
                    </div>
                    <div class="box-body">
                        
                        <!-- ACCIONES DEL SISTEMA - SIMPLIFICADAS -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="text-center">
                                    <button class="btn btn-primary btn-lg" id="btnNuevoUsuarioCentral" title="Crear Usuario en Central" style="margin-right: 10px;">
                                        <i class="fa fa-plus"></i> Crear Usuario Central
                                    </button>
                                    <button class="btn btn-success btn-lg" id="btnSincronizarTodosUsuarios" title="Sincronizar Todos los Usuarios" style="margin-left: 10px;">
                                        <i class="fa fa-refresh"></i> Sincronizar Todos
                                    </button>
                                    <br><br>
                                    <p class="text-muted">
                                        <small><strong>La sincronización se realiza desde el botón "Asignar Sucursales" de cada usuario o usando "Sincronizar Todos"</strong></small>
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- USUARIOS DE SUCURSALES -->
            <div class="col-md-6">
                <div class="box box-success">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-building"></i> Usuarios de Sucursales
                        </h3>
                    </div>
                    <div class="box-body">
                        <div id="usuariosSucursales">
                            <div class="text-center">
                                <i class="fa fa-spinner fa-spin"></i> Cargando usuarios de sucursales...
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- USUARIOS CENTRALES -->
            <div class="col-md-6">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-users"></i> Usuarios Centrales
                        </h3>
                    </div>
                    <div class="box-body">
                        <div id="usuariosCentrales">
                            <div class="text-center">
                                <i class="fa fa-spinner fa-spin"></i> Cargando usuarios centrales...
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- MODAL PARA CREAR/EDITAR USUARIO CENTRAL -->
<div class="modal fade" id="modalUsuarioCentral" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title" id="tituloModalUsuario">
                    <i class="fa fa-user"></i> Crear Usuario Central
                </h4>
            </div>
            <div class="modal-body">
                <form id="formUsuarioCentral">
                    <input type="hidden" id="idUsuarioCentral" name="id">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nombreUsuario">Nombre Completo: <span class="text-red">*</span></label>
                                <input type="text" class="form-control" id="nombreUsuario" name="nombre" 
                                       placeholder="Ingrese el nombre completo" required 
                                       minlength="2" maxlength="100">
                                <div class="help-block text-red" id="errorNombre" style="display: none;"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="usuarioLogin">Usuario de Login: <span class="text-red">*</span></label>
                                <input type="text" class="form-control" id="usuarioLogin" name="usuario" 
                                       placeholder="Ingrese el nombre de usuario" required 
                                       minlength="3" maxlength="50" pattern="[a-zA-Z0-9]+"
                                       title="Solo se permiten letras y números">
                                <div class="help-block text-red" id="errorUsuario" style="display: none;"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="passwordUsuario">Contraseña: <span class="text-red">*</span></label>
                                <input type="password" class="form-control" id="passwordUsuario" name="password" 
                                       placeholder="Ingrese la contraseña" required 
                                       minlength="4" maxlength="50">
                                <div class="help-block text-red" id="errorPassword" style="display: none;"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="perfilUsuario">Perfil: <span class="text-red">*</span></label>
                                <select class="form-control" id="perfilUsuario" name="perfil" required>
                                    <option value="">Seleccionar perfil</option>
                                    <option value="Administrador">Administrador</option>
                                    <option value="Especial">Especial</option>
                                    <option value="Contador">Contador</option>
                                    <option value="Transportador">Transportador</option>
                                    <option value="Vendedor">Vendedor</option>
                                </select>
                                <div class="help-block text-red" id="errorPerfil" style="display: none;"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="telefonoUsuario">Teléfono: <span class="text-red">*</span></label>
                                <input type="text" class="form-control" id="telefonoUsuario" name="telefono" 
                                       placeholder="Ingrese el número de teléfono" required 
                                       pattern="[0-9+\-\s()]+" minlength="7" maxlength="20"
                                       title="Ingrese un número de teléfono válido">
                                <div class="help-block text-red" id="errorTelefono" style="display: none;"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fa fa-info-circle"></i> 
                        <strong>Nota:</strong> Los campos marcados con <span class="text-red">*</span> son obligatorios.
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    <i class="fa fa-times"></i> Cancelar
                </button>
                <button type="button" class="btn btn-primary" id="btnGuardarUsuario">
                    <i class="fa fa-save"></i> Guardar Usuario
                </button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL PARA ASIGNAR SUCURSALES -->
<div class="modal fade" id="modalAsignarSucursales" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">
                    <i class="fa fa-building"></i> Asignar Sucursales a Usuario
                </h4>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <strong>Usuario:</strong> <span id="nombreUsuarioAsignar"></span><br>
                    <strong>Selecciona las sucursales donde este usuario tendrá acceso:</strong>
                </div>
                <div id="sucursalesAsignar" class="row">
                    <!-- Las sucursales se cargarán aquí dinámicamente -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    <i class="fa fa-times"></i> Cancelar
                </button>
                <button type="button" class="btn btn-success" id="btnGuardarAsignacion">
                    <i class="fa fa-save"></i> Guardar y Sincronizar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL PARA CONFIRMAR ELIMINACIÓN -->
<div class="modal fade" id="modalConfirmarEliminacion" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">
                    <i class="fa fa-exclamation-triangle text-red"></i> Confirmar Eliminación de Usuario
                </h4>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <strong><i class="fa fa-warning"></i> ¡ATENCIÓN!</strong><br>
                    Estás a punto de eliminar el usuario: <strong id="nombreUsuarioEliminar"></strong>
                </div>
                
                <div class="alert alert-warning">
                    <strong>Esta acción eliminará el usuario de:</strong>
                </div>
                
                <div id="sucursalesEliminar" class="row">
                    <!-- Las sucursales se cargarán aquí dinámicamente -->
                </div>
                
                <div class="alert alert-info">
                    <strong><i class="fa fa-info-circle"></i> Información:</strong><br>
                    • El usuario será eliminado de todas las sucursales asignadas<br>
                    • El usuario será eliminado de la lista de usuarios centrales<br>
                    • Esta acción no se puede deshacer
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    <i class="fa fa-times"></i> Cancelar
                </button>
                <button type="button" class="btn btn-danger" id="btnConfirmarEliminacion">
                    <i class="fa fa-trash"></i> Sí, Eliminar Usuario
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Incluir JavaScript específico para usuarios centrales -->
<script src="vistas/js/usuarios-central.js"></script>
