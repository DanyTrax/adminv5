<?php
require_once __DIR__ . "/../../controladores/usuarios-central.controlador.php";

// Obtener usuarios de todas las sucursales
$usuariosSucursales = ControladorUsuariosCentral::ctrConsultarUsuariosSucursales();
$usuariosLocal = ControladorUsuariosCentral::ctrObtenerUsuariosLocal();
?>

<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-users"></i> Consultar Usuarios de Sucursales
            <small>Ver usuarios existentes en todas las sucursales</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="usuarios-central"><i class="fa fa-users"></i> Usuarios Centrales</a></li>
            <li class="active">Consultar Sucursales</li>
        </ol>
    </section>

    <section class="content">
        
        <!-- ESTADÍSTICAS GENERALES -->
        <div class="row">
            <div class="col-md-3">
                <div class="info-box bg-blue">
                    <span class="info-box-icon"><i class="fa fa-building"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Sucursales</span>
                        <span class="info-box-number" id="totalSucursales"><?php echo count($usuariosSucursales); ?></span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="info-box bg-green">
                    <span class="info-box-icon"><i class="fa fa-users"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Usuarios</span>
                        <span class="info-box-number" id="totalUsuarios">0</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="info-box bg-yellow">
                    <span class="info-box-icon"><i class="fa fa-check-circle"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Conectadas</span>
                        <span class="info-box-number" id="sucursalesConectadas">0</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="info-box bg-red">
                    <span class="info-box-icon"><i class="fa fa-times-circle"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Con Error</span>
                        <span class="info-box-number" id="sucursalesError">0</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- SUCURSAL LOCAL -->
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-home"></i> Sucursal Local
                            <small class="label label-info"><?php echo count($usuariosLocal); ?> usuarios</small>
                        </h3>
                    </div>
                    <div class="box-body">
                        <?php if(!empty($usuariosLocal)): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Usuario</th>
                                        <th>Nombre</th>
                                        <th>Perfil</th>
                                        <th>Estado</th>
                                        <th>Último Login</th>
                                        <th>Fecha Creación</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($usuariosLocal as $index => $usuario): ?>
                                    <tr>
                                        <td><?php echo $index + 1; ?></td>
                                        <td><strong><?php echo htmlspecialchars($usuario['usuario']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($usuario['nombre']); ?></td>
                                        <td>
                                            <span class="label label-<?php 
                                                echo $usuario['perfil'] == 'Administrador' ? 'danger' : 
                                                    ($usuario['perfil'] == 'Vendedor' ? 'success' : 
                                                    ($usuario['perfil'] == 'Contador' ? 'info' : 'warning')); 
                                            ?>">
                                                <?php echo $usuario['perfil']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="label label-<?php echo $usuario['estado'] ? 'success' : 'danger'; ?>">
                                                <?php echo $usuario['estado_texto']; ?>
                                            </span>
                                        </td>
                                        <td><?php echo $usuario['ultimo_login_formateado']; ?></td>
                                        <td><?php echo $usuario['fecha_creacion_formateada']; ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fa fa-info-circle"></i> No hay usuarios en la sucursal local.
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- SUCURSALES REMOTAS -->
        <?php foreach($usuariosSucursales as $sucursal): ?>
        <div class="row">
            <div class="col-md-12">
                <div class="box box-<?php echo $sucursal['estado_conexion'] == 'conectado' ? 'success' : 'danger'; ?>">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-building"></i> <?php echo htmlspecialchars($sucursal['sucursal']['nombre']); ?>
                            <small class="label label-<?php echo $sucursal['estado_conexion'] == 'conectado' ? 'success' : 'danger'; ?>">
                                <?php echo $sucursal['total_usuarios']; ?> usuarios
                            </small>
                        </h3>
                        <div class="box-tools pull-right">
                            <span class="label label-<?php echo $sucursal['estado_conexion'] == 'conectado' ? 'success' : 'danger'; ?>">
                                <?php echo $sucursal['estado_conexion'] == 'conectado' ? 'Conectado' : 'Error'; ?>
                            </span>
                        </div>
                    </div>
                    <div class="box-body">
                        <?php if($sucursal['estado_conexion'] == 'conectado' && !empty($sucursal['usuarios'])): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Usuario</th>
                                        <th>Nombre</th>
                                        <th>Perfil</th>
                                        <th>Estado</th>
                                        <th>Último Login</th>
                                        <th>Fecha Creación</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($sucursal['usuarios'] as $index => $usuario): ?>
                                    <tr>
                                        <td><?php echo $index + 1; ?></td>
                                        <td><strong><?php echo htmlspecialchars($usuario['usuario'] ?? 'N/A'); ?></strong></td>
                                        <td><?php echo htmlspecialchars($usuario['nombre'] ?? 'N/A'); ?></td>
                                        <td>
                                            <span class="label label-<?php 
                                                $perfil = $usuario['perfil'] ?? 'N/A';
                                                echo $perfil == 'Administrador' ? 'danger' : 
                                                    ($perfil == 'Vendedor' ? 'success' : 
                                                    ($perfil == 'Contador' ? 'info' : 'warning')); 
                                            ?>">
                                                <?php echo $perfil; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="label label-<?php echo ($usuario['estado'] ?? false) ? 'success' : 'danger'; ?>">
                                                <?php echo ($usuario['estado'] ?? false) ? 'Activo' : 'Inactivo'; ?>
                                            </span>
                                        </td>
                                        <td><?php echo isset($usuario['ultimo_login']) && $usuario['ultimo_login'] ? date('d/m/Y H:i', strtotime($usuario['ultimo_login'])) : 'Nunca'; ?></td>
                                        <td><?php echo isset($usuario['fecha_creacion']) ? date('d/m/Y H:i', strtotime($usuario['fecha_creacion'])) : 'N/A'; ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php elseif($sucursal['estado_conexion'] == 'conectado' && empty($sucursal['usuarios'])): ?>
                        <div class="alert alert-info">
                            <i class="fa fa-info-circle"></i> No hay usuarios en esta sucursal.
                        </div>
                        <?php else: ?>
                        <div class="alert alert-danger">
                            <i class="fa fa-exclamation-triangle"></i> 
                            <strong>Error de conexión:</strong> <?php echo htmlspecialchars($sucursal['error'] ?? 'Error desconocido'); ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>

        <!-- BOTONES DE ACCIÓN -->
        <div class="row">
            <div class="col-md-12">
                <div class="box">
                    <div class="box-body text-center">
                        <a href="usuarios-central" class="btn btn-primary">
                            <i class="fa fa-arrow-left"></i> Volver a Usuarios Centrales
                        </a>
                        <button type="button" class="btn btn-success" onclick="location.reload()">
                            <i class="fa fa-refresh"></i> Actualizar Consulta
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </section>
</div>

<script>
$(document).ready(function() {
    // Calcular estadísticas
    var totalUsuarios = <?php echo count($usuariosLocal); ?>;
    var sucursalesConectadas = 0;
    var sucursalesError = 0;
    
    <?php foreach($usuariosSucursales as $sucursal): ?>
        totalUsuarios += <?php echo $sucursal['total_usuarios']; ?>;
        <?php if($sucursal['estado_conexion'] == 'conectado'): ?>
            sucursalesConectadas++;
        <?php else: ?>
            sucursalesError++;
        <?php endif; ?>
    <?php endforeach; ?>
    
    $('#totalUsuarios').text(totalUsuarios);
    $('#sucursalesConectadas').text(sucursalesConectadas);
    $('#sucursalesError').text(sucursalesError);
});
</script>
