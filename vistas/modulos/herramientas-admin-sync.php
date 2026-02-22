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
require_once __DIR__ . "/../../instalacion/funciones-sql-migraciones.php";
$respSucursales = ModeloSucursales::mdlObtenerSucursales(true);
$sucursales = ($respSucursales["success"] && !empty($respSucursales["data"])) ? $respSucursales["data"] : [];

$SQL_TODOS = $GLOBALS['SQL_LOCAL'];
$SQL_CENTRAL = $GLOBALS['SQL_CENTRAL'];
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
            <div class="col-md-4">
                <div class="box box-warning">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-server"></i> Actualizar BD Central</h3>
                    </div>
                    <div class="box-body">
                        <p class="text-muted small">Aplica scripts SQL en la BD central (despachos, stock_transito, historial_despachos). Compara y solo aplica lo que falta.</p>
                        <p class="small"><strong>Scripts:</strong> <?= implode(', ', array_keys($SQL_CENTRAL)) ?></p>
                        <button type="button" class="btn btn-warning" id="btnSyncSqlCentral"><i class="fa fa-database"></i> Actualizar Central</button>
                        <div id="resultado-sync-central" class="mt-3" style="display:none;"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-database"></i> Sincronizar SQL a Sucursales</h3>
                    </div>
                    <div class="box-body">
                        <p class="text-muted">Compara el esquema con los scripts del programa: aplica solo lo que falta y omite lo que ya existe.</p>
                        
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
                            <label>Scripts SQL (opcional - si no seleccionas, usa "Sincronizar todo"):</label>
                            <div class="well" style="max-height: 180px; overflow-y: auto;">
                                <?php foreach ($SQL_TODOS as $nombre => $ruta): ?>
                                <div class="checkbox">
                                    <label><input type="checkbox" class="sync-sql-script" value="<?= htmlspecialchars($nombre) ?>"> <?= htmlspecialchars($nombre) ?></label>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <button type="button" class="btn btn-success" id="btnSyncSql"><i class="fa fa-sync"></i> Sincronizar SQL seleccionados</button>
                        <button type="button" class="btn btn-primary" id="btnSyncSqlTodo"><i class="fa fa-database"></i> Sincronizar todo (comparar y aplicar)</button>
                        <div id="resultado-sync-sql" class="mt-3" style="display:none;"></div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
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
                                    $urlBase = !empty($s['url_base']) ? rtrim($s['url_base'], '/') : preg_replace('#/api-transferencias/?$#', '', rtrim($s['url_api'] ?? '', '/'));
                                    if (empty($urlBase)) continue;
                                ?>
                                <div class="checkbox">
                                    <label><input type="checkbox" class="git-pull-sucursal" value="<?= htmlspecialchars($urlBase) ?>"> <?= htmlspecialchars($s['nombre']) ?> <small class="text-muted">(<?= htmlspecialchars($urlBase) ?>)</small></label>
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
    $('#btnSyncSqlCentral').on('click', function() {
        if (!confirm('¿Aplicar todos los scripts SQL en la BD Central? (Solo se aplicará lo que falte)')) return;
        var btn = $(this);
        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Actualizando...');
        $('#resultado-sync-central').show().html('<div class="text-center"><i class="fa fa-spinner fa-spin fa-2x"></i><p>Procesando...</p></div>');
        $.ajax({
            url: 'ajax/sincronizar-sucursales.ajax.php',
            method: 'POST',
            data: { accion: 'sync_sql_central' },
            dataType: 'json'
        }).done(function(r) {
            var html = '';
            if (r.success && r.resultados) {
                r.resultados.forEach(function(res) {
                    var icon = res.estado === 'ok' ? '✅' : '❌';
                    html += '<div class="alert alert-' + (res.estado === 'ok' ? 'success' : 'danger') + '">' + icon + ' <strong>' + res.script + '</strong>: ' + (res.mensaje || '') + '</div>';
                });
            } else {
                html = '<div class="alert alert-danger">' + (r.error || 'Error desconocido') + '</div>';
            }
            $('#resultado-sync-central').html(html);
        }).fail(function() {
            $('#resultado-sync-central').html('<div class="alert alert-danger">Error de conexión.</div>');
        }).always(function() {
            btn.prop('disabled', false).html('<i class="fa fa-database"></i> Actualizar Central');
        });
    });

    function ejecutarSyncSql(modoTodos) {
        var sucursales = [];
        $('.sync-sql-sucursal:checked').each(function() { sucursales.push($(this).val()); });
        var scripts = [];
        if (!modoTodos) {
            $('.sync-sql-script:checked').each(function() { scripts.push($(this).val()); });
        }

        if (sucursales.length === 0) {
            swal('Atención', 'Selecciona al menos una sucursal.', 'warning');
            return;
        }
        if (!modoTodos && scripts.length === 0) {
            swal('Atención', 'Selecciona al menos un script SQL o usa "Sincronizar todo".', 'warning');
            return;
        }
        var msg = modoTodos ? '¿Comparar y aplicar TODOS los scripts SQL en ' + sucursales.length + ' sucursal(es)? (Solo se aplicará lo que falte)' : '¿Ejecutar los scripts seleccionados en ' + sucursales.length + ' sucursal(es)?';
        if (!confirm(msg)) return;

        var btn = modoTodos ? $('#btnSyncSqlTodo') : $('#btnSyncSql');
        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Sincronizando...');
        $('#resultado-sync-sql').show().html('<div class="text-center"><i class="fa fa-spinner fa-spin fa-2x"></i><p>Comparando y aplicando...</p></div>');

        var data = { accion: 'sync_sql', sucursales: JSON.stringify(sucursales), modo: modoTodos ? 'todos' : 'seleccionados' };
        if (!modoTodos) data.scripts = JSON.stringify(scripts);

        $.ajax({
            url: 'ajax/sincronizar-sucursales.ajax.php',
            method: 'POST',
            data: data,
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
            $('#btnSyncSql').prop('disabled', false).html('<i class="fa fa-sync"></i> Sincronizar SQL seleccionados');
            $('#btnSyncSqlTodo').prop('disabled', false).html('<i class="fa fa-database"></i> Sincronizar todo (comparar y aplicar)');
        });
    }

    $('#btnSyncSql').on('click', function() { ejecutarSyncSql(false); });
    $('#btnSyncSqlTodo').on('click', function() { ejecutarSyncSql(true); });

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
