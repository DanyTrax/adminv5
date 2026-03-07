/**
 * Transportador Móvil - Vista dedicada para transportador
 */
$(document).ready(function() {
    if ($('.transportador-movil-wrapper').length === 0) return;

    // Cargar resumen al inicio
    cargarResumen();

    // Cargar contenido al cambiar de tab
    $('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
        var target = $(e.target).attr('href');
        if (target === '#tabSolicitudes') cargarSolicitudes();
        if (target === '#tabDespachos') cargarDespachos();
        if (target === '#tabStock') cargarStock();
        if (target === '#tabDescargas') cargarDescargas();
    });

    // Delegación para aprobar/cancelar solicitud
    $(document).on('click', '.btnAprobarSolicitudMovil', function() {
        var id = $(this).data('id');
        if (confirm('¿Aprobar esta solicitud?')) ejecutarAprobarSolicitud(id);
    });
    $(document).on('click', '.btnCancelarSolicitudMovil', function() {
        var id = $(this).data('id');
        var motivo = prompt('Motivo de cancelación (mínimo 5 caracteres):');
        if (motivo && motivo.trim().length >= 5) ejecutarCancelarSolicitud(id, motivo.trim());
        else if (motivo !== null) alert('El motivo debe tener al menos 5 caracteres.');
    });

    // Delegación para aceptar despacho
    $(document).on('click', '.btnAceptarDespachoMovil', function() {
        var id = $(this).data('id');
        if (confirm('¿Aceptar este despacho? Los productos pasarán a tu camión.')) ejecutarAceptarDespacho(id);
    });
});

function ajaxTransportador(accion, datosExtra) {
    var data = { accion: accion };
    if (datosExtra) $.extend(data, datosExtra);
    return $.ajax({
        url: 'ajax/transportador-movil.ajax.php',
        type: 'POST',
        data: data,
        dataType: 'json'
    });
}

function cargarResumen() {
    ajaxTransportador('resumen').done(function(r) {
        if (!r.success) { $('#contenidoInicio').html('<div class="alert alert-danger">' + (r.error || 'Error') + '</div>'); return; }
        var d = r.data;
        var html = '<div class="resumen-grid">';
        html += '<div class="resumen-item"><div class="num">' + d.solicitudes_pendientes + '</div><div class="label">Solicitudes pendientes</div></div>';
        html += '<div class="resumen-item"><div class="num">' + d.despachos_pendientes + '</div><div class="label">Despachos por aceptar</div></div>';
        html += '<div class="resumen-item"><div class="num">' + d.productos_en_camion + '</div><div class="label">Productos en camión</div></div>';
        html += '</div>';

        if (d.ultimas_descargas && d.ultimas_descargas.length > 0) {
            html += '<h5 style="margin-bottom:10px;"><i class="fa fa-download"></i> Últimas descargas (hoy)</h5>';
            d.ultimas_descargas.forEach(function(u) {
                html += '<div class="card-movil">';
                html += '<strong>' + u.codigo_producto + '</strong> · ' + u.cantidad_descargada + ' uds · ' + (u.sucursal_nombre || '') + ' · ' + (u.hora || '') + '<br>';
                html += '<small class="text-muted">Descargó: ' + (u.usuario_nombre || '') + '</small>';
                html += '</div>';
            });
        } else {
            html += '<div class="alert alert-info"><i class="fa fa-info-circle"></i> No hay descargas registradas hoy.</div>';
        }
        $('#contenidoInicio').html(html);

        // Actualizar badges
        if (d.solicitudes_pendientes > 0) {
            $('#badgeSolicitudes').text(d.solicitudes_pendientes).show();
        }
        if (d.despachos_pendientes > 0) {
            $('#badgeDespachos').text(d.despachos_pendientes).show();
        }
    }).fail(function() {
        $('#contenidoInicio').html('<div class="alert alert-danger">Error de conexión.</div>');
    });
}

function cargarSolicitudes() {
    if ($('#contenidoSolicitudes').data('loaded')) return;
    ajaxTransportador('solicitudes').done(function(r) {
        if (!r.success) { $('#contenidoSolicitudes').html('<div class="alert alert-danger">' + (r.error || 'Error') + '</div>'); return; }
        var list = r.data || [];
        var html = '';
        if (list.length === 0) {
            html = '<div class="alert alert-info"><i class="fa fa-check"></i> No hay solicitudes pendientes.</div>';
        } else {
            list.forEach(function(s) {
                var mins = s.minutos_desde != null ? (s.minutos_desde < 60 ? s.minutos_desde + ' min' : Math.floor(s.minutos_desde/60) + ' h') : '';
                html += '<div class="card-movil">';
                html += '<div class="card-title">' + (s.numero_solicitud || 'SOL-'+s.id) + '</div>';
                html += '<div class="card-meta">Para: ' + (s.nombre_sucursal_solicitante || '') + ' · ' + (s.total_productos||0) + ' productos · ' + mins + '</div>';
                html += '<div class="btn-group btn-group-justified">';
                html += '<div class="btn-group"><button class="btn btn-success btn-movil btnAprobarSolicitudMovil" data-id="'+s.id+'"><i class="fa fa-check"></i> Aprobar</button></div>';
                html += '<div class="btn-group"><button class="btn btn-warning btn-movil btnCancelarSolicitudMovil" data-id="'+s.id+'"><i class="fa fa-times"></i> Cancelar</button></div>';
                html += '</div></div>';
            });
        }
        $('#contenidoSolicitudes').html(html).data('loaded', true);
    }).fail(function() {
        $('#contenidoSolicitudes').html('<div class="alert alert-danger">Error de conexión.</div>');
    });
}

function ejecutarAprobarSolicitud(id) {
    $.ajax({
        url: 'ajax/solicitudes-stock.ajax.php',
        type: 'POST',
        data: { accion: 'aprobar', id_solicitud: id },
        dataType: 'json'
    }).done(function(r) {
        if (r.success) {
            if (typeof swal !== 'undefined') swal('¡Aprobado!', r.message, 'success');
            else alert('Solicitud aprobada.');
            $('#contenidoSolicitudes').data('loaded', false);
            cargarSolicitudes();
            cargarResumen();
        } else {
            alert(r.message || 'Error al aprobar');
        }
    }).fail(function() { alert('Error de conexión'); });
}

function ejecutarCancelarSolicitud(id, motivo) {
    $.ajax({
        url: 'ajax/solicitudes-stock.ajax.php',
        type: 'POST',
        data: { accion: 'cancelar', id_solicitud: id, motivo: motivo },
        dataType: 'json'
    }).done(function(r) {
        if (r.success) {
            if (typeof swal !== 'undefined') swal('Cancelada', r.message, 'info');
            else alert('Solicitud cancelada.');
            $('#contenidoSolicitudes').data('loaded', false);
            cargarSolicitudes();
            cargarResumen();
        } else {
            alert(r.message || 'Error al cancelar');
        }
    }).fail(function() { alert('Error de conexión'); });
}

function cargarDespachos() {
    if ($('#contenidoDespachos').data('loaded')) return;
    ajaxTransportador('despachos').done(function(r) {
        if (!r.success) { $('#contenidoDespachos').html('<div class="alert alert-danger">' + (r.error || 'Error') + '</div>'); return; }
        var d = r.data;
        var pendientes = d.pendientes || [];
        var enTransito = d.en_transito || [];
        var html = '';

        if (pendientes.length > 0) {
            html += '<h5 style="margin-bottom:10px;"><i class="fa fa-clock-o"></i> Pendientes de aceptar</h5>';
            pendientes.forEach(function(p) {
                html += '<div class="card-movil">';
                html += '<div class="card-title">' + (p.numero_despacho || '') + '</div>';
                html += '<div class="card-meta">De: ' + (p.sucursal_origen || '') + ' · ' + (p.total_productos||0) + ' productos · ' + (p.total_cantidad||0) + ' uds</div>';
                html += '<button class="btn btn-success btn-movil btn-movil-block btnAceptarDespachoMovil" data-id="'+p.id+'"><i class="fa fa-check"></i> Aceptar despacho</button>';
                html += '</div>';
            });
        }

        if (enTransito.length > 0) {
            html += '<h5 style="margin:15px 0 10px;"><i class="fa fa-truck"></i> En mi camión</h5>';
            enTransito.forEach(function(p) {
                html += '<div class="card-movil">';
                html += '<div class="card-title">' + (p.numero_despacho || '') + '</div>';
                html += '<div class="card-meta">Origen: ' + (p.sucursal_origen || '') + ' · ' + (p.total_productos||0) + ' productos</div>';
                html += '<a href="stock-transito" class="btn btn-info btn-movil btn-movil-block"><i class="fa fa-eye"></i> Ver detalle</a>';
                html += '</div>';
            });
        }

        if (pendientes.length === 0 && enTransito.length === 0) {
            html = '<div class="alert alert-info"><i class="fa fa-info-circle"></i> No hay despachos pendientes ni en tránsito.</div>';
        }
        $('#contenidoDespachos').html(html).data('loaded', true);
    }).fail(function() {
        $('#contenidoDespachos').html('<div class="alert alert-danger">Error de conexión.</div>');
    });
}

function ejecutarAceptarDespacho(id) {
    var fd = new FormData();
    fd.append('aceptarDespacho', id);
    $.ajax({
        url: 'ajax/despachos.ajax.php',
        type: 'POST',
        data: fd,
        processData: false,
        contentType: false,
        dataType: 'json'
    }).done(function(r) {
        if (r && r.success) {
            if (typeof swal !== 'undefined') swal('¡Aceptado!', 'Despacho aceptado. Los productos están en tu camión.', 'success');
            else alert('Despacho aceptado.');
            $('#contenidoDespachos').data('loaded', false);
            cargarDespachos();
            cargarResumen();
        } else {
            alert((r && (r.error || r.message)) || 'Error al aceptar');
        }
    }).fail(function() { alert('Error de conexión'); });
}

function cargarStock() {
    if ($('#contenidoStock').data('loaded')) return;
    ajaxTransportador('stock').done(function(r) {
        if (!r.success) { $('#contenidoStock').html('<div class="alert alert-danger">' + (r.error || 'Error') + '</div>'); return; }
        var list = r.data || [];
        var html = '';
        if (list.length === 0) {
            html = '<div class="alert alert-info"><i class="fa fa-truck"></i> No tienes productos en camión.</div>';
        } else {
            list.forEach(function(desp) {
                html += '<div class="card-movil" style="border-left: 4px solid #28a745;">';
                html += '<div class="card-title"><i class="fa fa-shipping-fast"></i> ' + (desp.numero_despacho || '') + '</div>';
                html += '<div class="card-meta">Origen: ' + (desp.sucursal_origen || '') + '</div>';
                html += '<div style="margin-top:10px;">';
                (desp.productos || []).forEach(function(p) {
                    html += '<div style="padding:8px 0; border-bottom:1px solid #eee; font-size:13px;">';
                    html += '<strong>' + (p.codigo_producto||'') + '</strong> ' + (p.descripcion_producto||'') + ' <span class="badge bg-blue">' + (p.cantidad_disponible||0) + ' uds</span>';
                    html += '</div>';
                });
                html += '</div></div>';
            });
        }
        $('#contenidoStock').html(html).data('loaded', true);
    }).fail(function() {
        $('#contenidoStock').html('<div class="alert alert-danger">Error de conexión.</div>');
    });
}

function cargarDescargas() {
    if ($('#contenidoDescargas').data('loaded')) return;
    ajaxTransportador('descargas', { dias: 7 }).done(function(r) {
        if (!r.success) { $('#contenidoDescargas').html('<div class="alert alert-danger">' + (r.error || 'Error') + '</div>'); return; }
        var list = r.data || [];
        var html = '<p class="text-muted" style="margin-bottom:10px;"><i class="fa fa-download"></i> Lo que descargaron de tu camión (últimos 7 días)</p>';
        if (list.length === 0) {
            html += '<div class="alert alert-info">No hay descargas registradas.</div>';
        } else {
            list.forEach(function(u) {
                html += '<div class="card-movil">';
                html += '<strong>' + (u.codigo_producto||'') + '</strong> · ' + (u.cantidad_descargada||0) + ' uds<br>';
                html += '<small class="text-muted">Descargó: ' + (u.usuario_nombre||'') + ' · ' + (u.sucursal_nombre||'') + ' · ' + (u.fecha_hora||'') + '</small>';
                html += '</div>';
            });
        }
        $('#contenidoDescargas').html(html).data('loaded', true);
    }).fail(function() {
        $('#contenidoDescargas').html('<div class="alert alert-danger">Error de conexión.</div>');
    });
}
