<?php
/**
 * Herramientas Admin - Sincronizar SQL y Git Pull en sucursales
 * Solo visible para usuario "admin" con nombre "admin"
 */
$esAdminAdmin = (isset($_SESSION["usuario"]) && strtolower($_SESSION["usuario"]) === "admin" && 
                 isset($_SESSION["nombre"]) && strtolower($_SESSION["nombre"]) === "admin");

if (!$esAdminAdmin) {
    echo '<div class="content-wrapper"><section class="content"><div class="alert alert-danger">Acceso denegado. Solo el usuario admin puede acceder.</div></section></div>';
    return;
}

require_once __DIR__ . "/../../modelos/sucursales.modelo.php";
$respSucursales = ModeloSucursales::mdlObtenerSucursales(true);
$sucursales = ($respSucursales["success"] && !empty($respSucursales["data"])) ? $respSucursales["data"] : [];

// SQL LOCAL (para sincronizar a sucursales)
$SQL_LOCAL = [
    'crear-abonos-historial' => 'instalacion/sql/crear-abonos-historial.sql',
    'agregar-columnas-bd' => 'instalacion/sql/agregar-columnas-bd.sql',
    'crear-tablas-trazabilidad' => 'instalacion/sql/crear-tablas-trazabilidad.sql'
];
?>

<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-cogs"></i> Herramientas Admin - Sincronización</h1>
        <ol class="breadcrumb">
            <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li class="active">Herramientas Admin</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-6">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-database"></i> Sincronizar SQL a Sucursales</h3>
                    </div>
                    <div class="box-body">
                        <p class="text-muted">Ejecuta migraciones SQL en las bases de datos de las sucursales seleccionadas.</p>
                        
                        <div class="form-group">
                            <label>Sucursales:</label>
                            <div class="well" style="max-height: 200px; overflow-y: auto;">
                                <?php foreach ($sucursales as $s): ?>
                                <div class="checkbox">
                                    <label><input type="checkbox" class="sync-sql-sucursal" value="<?= htmlspecialchars($s['nombre']) ?>"> <?= htmlspecialchars($s['nombre']) ?></label>
                                </div>
                                <?php endforeach; ?>
                                <?php if (empty($sucursales)): ?>
                                <p class="text-muted">No hay sucursales configuradas.</p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Scripts SQL a ejecutar:</label>
                            <div class="well">
                                <?php foreach ($SQL_LOCAL as $nombre => $ruta): ?>
                                <div class="checkbox">
                                    <label><input type="checkbox" class="sync-sql-script" value="<?= htmlspecialchars($nombre) ?>"> <?= htmlspecialchars($nombre) ?></label>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <button type="button" class="btn btn-success" id="btnSyncSql"><i class="fa fa-sync"></i> Sincronizar SQL</button>
                        <div id="resultado-sync-sql" class="mt-3" style="display:none;"></div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-git"></i> Git Pull en Sucursales</h3>
                    </div>
                    <div class="box-body">
                        <p class="text-muted">Ejecuta <code>git pull</code> en las sucursales seleccionadas (requiere endpoint en cada sucursal).</p>
                        
                        <div class="form-group">
                            <label>Sucursales:</label>
                            <div class="well" style="max-height: 200px; overflow-y: auto;">
                                <?php foreach ($sucursales as $s): 
                                    $urlApi = rtrim($s['url_api'] ?? '', '/');
                                    if (empty($urlApi)) continue;
                                ?>
                                <div class="checkbox">
                                    <label><input type="checkbox" class="git-pull-sucursal" value="<?= htmlspecialchars($urlApi) ?>"> <?= htmlspecialchars($s['nombre']) ?> <small class="text-muted">(<?= htmlspecialchars($urlApi) ?>)</small></label>
                                </div>
                                <?php endforeach; ?>
                                <?php 
                                $conUrl = array_filter($sucursales, fn($s) => !empty($s['url_api'] ?? ''));
                                if (empty($conUrl)): ?>
                                <p class="text-muted">No hay sucursales con url_api configurada.</p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <button type="button" class="btn btn-info" id="btnGitPull"><i class="fa fa-download"></i> Ejecutar Git Pull</button>
                        <div id="resultado-git-pull" class="mt-3" style="display:none;"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
$(function() {
    $('#btnSyncSql').on('click', function() {
        var sucursales = [];
        $('.sync-sql-sucursal:checked').each(function() {
            sucursales.push($(this).val());
        });
        var scripts = [];
        $('.sync-sql-script:checked').each(function() { scripts.push($(this).val()); });

        if (sucursales.length === 0) {
            swal('Atención', 'Selecciona al menos una sucursal.', 'warning');
            return;
        }
        if (scripts.length === 0) {
            swal('Atención', 'Selecciona al menos un script SQL.', 'warning');
            return;
        }
        if (!confirm('¿Ejecutar los scripts SQL seleccionados en ' + sucursales.length + ' sucursal(es)?')) return;

        var btn = $(this);
        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Sincronizando...');
        $('#resultado-sync-sql').show().html('<div class="text-center"><i class="fa fa-spinner fa-spin fa-2x"></i><p>Procesando...</p></div>');

        $.ajax({
            url: 'ajax/sincronizar-sucursales.ajax.php',
            method: 'POST',
            data: { accion: 'sync_sql', sucursales: JSON.stringify(sucursales), scripts: JSON.stringify(scripts) },
            dataType: 'json'
        }).done(function(r) {
            var html = '';
            if (r.success && r.resultados) {
                r.resultados.forEach(function(res) {
                    var icon = res.estado === 'ok' ? '✅' : (res.estado === 'error' ? '❌' : '⚠️');
                    html += '<div class="alert alert-' + (res.estado === 'ok' ? 'success' : (res.estado === 'error' ? 'danger' : 'warning')) + '">' + icon + ' <strong>' + res.sucursal + '</strong>: ' + (res.mensaje || '') + '</div>';
                });
            } else {
                html = '<div class="alert alert-danger">' + (r.error || 'Error desconocido') + '</div>';
            }
            $('#resultado-sync-sql').html(html);
        }).fail(function() {
            $('#resultado-sync-sql').html('<div class="alert alert-danger">Error de conexión.</div>');
        }).always(function() {
            btn.prop('disabled', false).html('<i class="fa fa-sync"></i> Sincronizar SQL');
        });
    });

    $('#btnGitPull').on('click', function() {
        var urls = [];
        $('.git-pull-sucursal:checked').each(function() { urls.push($(this).val()); });

        if (urls.length === 0) {
            swal('Atención', 'Selecciona al menos una sucursal.', 'warning');
            return;
        }
        if (!confirm('¿Ejecutar git pull en ' + urls.length + ' sucursal(es)?')) return;

        var btn = $(this);
        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Ejecutando...');
        $('#resultado-git-pull').show().html('<div class="text-center"><i class="fa fa-spinner fa-spin fa-2x"></i><p>Procesando...</p></div>');

        $.ajax({
            url: 'ajax/sincronizar-sucursales.ajax.php',
            method: 'POST',
            data: { accion: 'git_pull', urls: JSON.stringify(urls) },
            dataType: 'json'
        }).done(function(r) {
            var html = '';
            if (r.success && r.resultados) {
                r.resultados.forEach(function(res) {
                    var icon = res.estado === 'ok' ? '✅' : '❌';
                    html += '<div class="alert alert-' + (res.estado === 'ok' ? 'success' : 'danger') + '">' + icon + ' <strong>' + res.sucursal + '</strong>: ' + (res.mensaje || '') + '</div>';
                });
            } else {
                html = '<div class="alert alert-danger">' + (r.error || 'Error desconocido') + '</div>';
            }
            $('#resultado-git-pull').html(html);
        }).fail(function() {
            $('#resultado-git-pull').html('<div class="alert alert-danger">Error de conexión.</div>');
        }).always(function() {
            btn.prop('disabled', false).html('<i class="fa fa-download"></i> Ejecutar Git Pull');
        });
    });
});
</script>
