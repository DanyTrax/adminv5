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

    // Delegación para ver detalle de solicitud
    $(document).on('click', '.btnVerDetalleSolicitudMovil', function() {
        var id = $(this).data('id');
        var numero = $(this).data('numero') || 'SOL-'+id;
        var sucursal = $(this).data('sucursal') || '';
        abrirDetalleSolicitudMovil(id, numero, sucursal);
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

    // Delegación para ver detalle de despacho
    $(document).on('click', '.btnVerDetalleDespachoMovil', function() {
        var numero = $(this).data('numero');
        var sucursal = $(this).data('sucursal') || '';
        var estado = $(this).data('estado') || '';
        abrirDetalleDespachoMovil(numero, sucursal, estado);
    });

    // Delegación para cancelar despacho
    $(document).on('click', '.btnCancelarDespachoMovil', function() {
        var id = $(this).data('id');
        cancelarDespachoMovil(id);
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
                html += '<div class="card-meta card-meta-lineas">Solicitó: ' + (s.nombre_usuario_solicitante || 'N/A') + '<br>Para: <strong>' + (s.nombre_sucursal_solicitante || '') + '</strong><br><span class="text-muted">' + (s.total_productos||0) + ' productos · ' + mins + '</span></div>';
                html += '<button class="btn btn-info btn-movil btn-movil-block btnVerDetalleSolicitudMovil" data-id="'+s.id+'" data-numero="'+(s.numero_solicitud||'')+'" data-sucursal="'+(s.nombre_sucursal_solicitante||'')+'"><i class="fa fa-eye"></i> Ver detalle (productos a despachar)</button>';
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

function abrirDetalleSolicitudMovil(id, numero, sucursal) {
    $('#modalSolicitudTitulo').text(numero || 'Solicitud');
    $('#modalSolicitudMeta').text('Para: ' + sucursal);
    $('#modalSolicitudProductosBody').html('<tr><td colspan="3" class="text-center"><i class="fa fa-spinner fa-spin"></i> Cargando...</td></tr>');
    $('#tbodyStockSucursalesMovil').html('<tr><td colspan="5" class="text-center text-muted"><i class="fa fa-spinner fa-spin"></i> Cargando...</td></tr>');
    $('#modalDetalleSolicitudMovil').modal('show');

    $.ajax({
        url: 'ajax/solicitudes-stock.ajax.php',
        type: 'POST',
        data: { accion: 'ver_detalle', id_solicitud: id },
        dataType: 'json'
    }).done(function(r) {
        if (r.success && r.data) {
            var meta = 'Para: ' + sucursal;
            if (r.data.nombre_usuario_solicitante) meta = 'Solicitó: ' + r.data.nombre_usuario_solicitante + '<br>' + meta;
            $('#modalSolicitudMeta').html(meta);
            var productos = [];
            if (r.data.productos_solicitados) {
                try {
                    productos = typeof r.data.productos_solicitados === 'string' 
                        ? JSON.parse(r.data.productos_solicitados) : r.data.productos_solicitados;
                } catch(e) { productos = []; }
            }
            var html = '';
            if (productos.length === 0) {
                html = '<tr><td colspan="3" class="text-center text-muted">Sin productos</td></tr>';
            } else {
                productos.forEach(function(p) {
                    var cod = p.codigo || p.codigo_producto || p.codigoProducto || '';
                    var desc = p.descripcion || p.descripcion_producto || '';
                    var cant = p.cantidad || p.cantidad_solicitada || 0;
                    html += '<tr><td><strong>'+cod+'</strong></td><td>'+desc+'</td><td class="text-center">'+cant+'</td></tr>';
                });
            }
            $('#modalSolicitudProductosBody').html(html);
            cargarStockSucursalesMovil(productos);
        } else {
            $('#modalSolicitudProductosBody').html('<tr><td colspan="3" class="text-center text-danger">Error al cargar</td></tr>');
            $('#tbodyStockSucursalesMovil').html('<tr><td colspan="5" class="text-center text-danger">Error al cargar</td></tr>');
        }
    }).fail(function() {
        $('#modalSolicitudProductosBody').html('<tr><td colspan="3" class="text-center text-danger">Error de conexión</td></tr>');
        $('#tbodyStockSucursalesMovil').html('<tr><td colspan="5" class="text-center text-danger">Error de conexión</td></tr>');
    });
}

function cargarStockSucursalesMovil(productos) {
    if (!productos || productos.length === 0) {
        $('#tbodyStockSucursalesMovil').html('<tr><td colspan="5" class="text-center text-muted">No hay productos para consultar</td></tr>');
        return;
    }
    var productosParaApi = productos.map(function(p) {
        return { codigo: p.codigo || p.codigo_producto || p.codigoProducto || '', descripcion: p.descripcion || p.descripcion_producto || '', cantidad: parseInt(p.cantidad || p.cantidad_solicitada || 0, 10) };
    }).filter(function(p) { return p.codigo; });

    if (productosParaApi.length === 0) {
        $('#tbodyStockSucursalesMovil').html('<tr><td colspan="5" class="text-center text-muted">No hay productos válidos</td></tr>');
        return;
    }

    $.ajax({
        url: 'ajax/stock-disponible-sucursales.ajax.php',
        type: 'POST',
        data: { accion: 'consultar_stock_sucursales', productos: JSON.stringify(productosParaApi) },
        dataType: 'json'
    }).done(function(response) {
        if (response.success && response.data) {
            mostrarStockSucursalesMovil(response.data);
        } else {
            $('#tbodyStockSucursalesMovil').html('<tr><td colspan="5" class="text-center text-danger">' + (response.message || 'Error') + '</td></tr>');
        }
    }).fail(function() {
        $('#tbodyStockSucursalesMovil').html('<tr><td colspan="5" class="text-center text-danger">Error de conexión</td></tr>');
    });
}

function mostrarStockSucursalesMovil(stockData) {
    if (!stockData || !Array.isArray(stockData) || stockData.length === 0) {
        $('#tbodyStockSucursalesMovil').html('<tr><td colspan="5" class="text-center text-muted">No hay datos de stock</td></tr>');
        return;
    }
    var sucursalesUnicas = [];
    var sucursalesMap = {};
    stockData.forEach(function(producto) {
        if (producto.sucursales && Array.isArray(producto.sucursales)) {
            producto.sucursales.forEach(function(sucursal) {
                if (!sucursalesMap[sucursal.id]) {
                    sucursalesMap[sucursal.id] = sucursal;
                    sucursalesUnicas.push(sucursal);
                }
            });
        }
    });
    sucursalesUnicas.sort(function(a, b) { return (a.nombre || '').localeCompare(b.nombre || ''); });

    var headerHtml = '<th style="width:30px">#</th><th>Código</th><th>Descripción</th><th class="text-center">Cant.</th>';
    sucursalesUnicas.forEach(function(sucursal) {
        headerHtml += '<th class="text-center" style="min-width:60px">' + (sucursal.nombre || '') + '</th>';
    });
    $('#theadStockSucursalesMovil').html(headerHtml);

    var tbodyHtml = '';
    stockData.forEach(function(producto, index) {
        tbodyHtml += '<tr><td class="text-center">' + (index + 1) + '</td>';
        tbodyHtml += '<td><code>' + (producto.codigo || '') + '</code></td>';
        tbodyHtml += '<td>' + (producto.descripcion || '') + '</td>';
        tbodyHtml += '<td class="text-center"><span class="label label-primary">' + (producto.cantidad_solicitada || 0) + '</span></td>';
        sucursalesUnicas.forEach(function(sucursal) {
            var stockSucursal = 0;
            if (producto.sucursales && Array.isArray(producto.sucursales)) {
                var s = producto.sucursales.find(function(x) { return x.id === sucursal.id; });
                if (s) stockSucursal = s.stock_disponible || 0;
            }
            var cls = stockSucursal >= (producto.cantidad_solicitada || 0) ? 'success' : (stockSucursal > 0 ? 'warning' : 'danger');
            tbodyHtml += '<td class="text-center text-' + cls + '"><strong>' + stockSucursal + '</strong></td>';
        });
        tbodyHtml += '</tr>';
    });
    $('#tbodyStockSucursalesMovil').html(tbodyHtml);
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
                html += '<div class="card-meta card-meta-lineas">Despachó: ' + (p.nombre_usuario_creador || 'N/A') + '<br>De: <strong>' + (p.sucursal_origen || '') + '</strong>' + (p.detalle_adicional ? '<br>Detalle adicional: ' + (p.detalle_adicional + '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;') : '') + '<br><span class="text-muted">' + (p.total_productos||0) + ' productos · ' + (p.total_cantidad||0) + ' uds</span></div>';
                html += '<button class="btn btn-info btn-movil btn-movil-block btnVerDetalleDespachoMovil" data-numero="'+(p.numero_despacho||'')+'" data-sucursal="'+(p.sucursal_origen||'')+'" data-estado="pendiente"><i class="fa fa-eye"></i> Ver detalle del despacho</button>';
                html += '<div class="btn-group btn-group-justified">';
                html += '<div class="btn-group"><button class="btn btn-success btn-movil btnAceptarDespachoMovil" data-id="'+p.id+'"><i class="fa fa-check"></i> Aceptar</button></div>';
                html += '<div class="btn-group"><button class="btn btn-warning btn-movil btnCancelarDespachoMovil" data-id="'+p.id+'"><i class="fa fa-times"></i> Cancelar</button></div>';
                html += '</div>';
                html += '</div>';
            });
        }

        if (enTransito.length > 0) {
            html += '<h5 style="margin:15px 0 10px;"><i class="fa fa-truck"></i> En mi camión</h5>';
            enTransito.forEach(function(p) {
                html += '<div class="card-movil">';
                html += '<div class="card-title">' + (p.numero_despacho || '') + '</div>';
                html += '<div class="card-meta card-meta-lineas">Despachó: ' + (p.nombre_usuario_creador || 'N/A') + '<br>De: <strong>' + (p.sucursal_origen || '') + '</strong>' + (p.detalle_adicional ? '<br>Detalle adicional: ' + (p.detalle_adicional + '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;') : '') + '<br><span class="text-muted">' + (p.total_productos||0) + ' productos</span></div>';
                html += '<button class="btn btn-info btn-movil btn-movil-block btnVerDetalleDespachoMovil" data-numero="'+(p.numero_despacho||'')+'" data-sucursal="'+(p.sucursal_origen||'')+'" data-estado="en_transito"><i class="fa fa-eye"></i> Ver detalle del despacho</button>';
                html += '<a href="stock-transito" class="btn btn-default btn-movil btn-movil-block"><i class="fa fa-cubes"></i> Ver stock en tránsito</a>';
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

function abrirDetalleDespachoMovil(numero, sucursal, estado) {
    if (!numero) { alert('Número de despacho no disponible'); return; }
    $('#modalDespachoTitulo').text(numero);
    $('#modalDespachoMeta').text('De: ' + sucursal + (estado ? ' · Estado: ' + estado : ''));
    $('#modalDespachoProductosBody').html('<tr><td colspan="3" class="text-center"><i class="fa fa-spinner fa-spin"></i> Cargando...</td></tr>');
    $('#modalDetalleDespachoMovil').modal('show');

    $.ajax({
        url: 'ajax/despachos.ajax.php',
        type: 'POST',
        data: { accion: 'obtener_productos_despacho', numero_despacho: numero },
        dataType: 'json'
    }).done(function(r) {
        if (r.success && r.productos) {
            var meta = 'De: ' + sucursal + (estado ? ' · Estado: ' + estado : '');
            if (r.despacho && r.despacho.nombre_usuario_creador) meta = 'Despachó: ' + r.despacho.nombre_usuario_creador + '<br>' + meta;
            $('#modalDespachoMeta').html(meta);

            var detalleAdicional = (r.despacho && r.despacho.detalle_adicional) ? (r.despacho.detalle_adicional + '').trim() : '';
            if (detalleAdicional) {
                $('#modalDespachoDetalleAdicionalTexto').text(detalleAdicional);
                $('#modalDespachoDetalleAdicional').show();
            } else {
                $('#modalDespachoDetalleAdicional').hide();
            }

            var productos = r.productos;
            var html = '';
            if (productos.length === 0) {
                html = '<tr><td colspan="4" class="text-center text-muted">Sin productos</td></tr>';
            } else {
                productos.forEach(function(p) {
                    var cod = p.codigo || p.codigo_producto || p.codigoProducto || '';
                    var desc = p.descripcion || p.descripcion_producto || '';
                    var cant = p.cantidad || p.cantidad_solicitada || 0;
                    var obs = p.observacion || '';
                    html += '<tr><td><strong>'+cod+'</strong></td><td>'+desc+'</td><td class="text-center">'+cant+'</td><td><small class="text-muted">'+obs+'</small></td></tr>';
                });
            }
            $('#modalDespachoProductosBody').html(html);
        } else {
            $('#modalDespachoDetalleAdicional').hide();
            $('#modalDespachoProductosBody').html('<tr><td colspan="4" class="text-center text-danger">' + (r.error || 'Error al cargar') + '</td></tr>');
        }
    }).fail(function() {
        $('#modalDespachoDetalleAdicional').hide();
        $('#modalDespachoProductosBody').html('<tr><td colspan="4" class="text-center text-danger">Error de conexión</td></tr>');
    });
}

function cancelarDespachoMovil(id) {
    if (typeof swal !== 'undefined') {
        swal({
            title: '¿Cancelar despacho?',
            text: 'Ingrese el motivo de la cancelación:',
            type: 'warning',
            input: 'textarea',
            inputPlaceholder: 'Motivo de la cancelación...',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3c8dbc',
            confirmButtonText: 'Sí, cancelar',
            cancelButtonText: 'No cancelar',
            inputValidator: function(value) { return value ? null : 'Debe ingresar un motivo de cancelación'; }
        }).then(function(result) {
            if (result.value) ejecutarCancelarDespachoMovil(id, result.value);
        });
    } else {
        var motivo = prompt('Motivo de la cancelación:');
        if (motivo && motivo.trim()) ejecutarCancelarDespachoMovil(id, motivo.trim());
        else if (motivo !== null) alert('Debe ingresar un motivo.');
    }
}

function ejecutarCancelarDespachoMovil(id, motivo) {
    var fd = new FormData();
    fd.append('cancelarDespacho', id);
    fd.append('motivoCancelacion', motivo);
    $.ajax({
        url: 'ajax/despachos.ajax.php',
        type: 'POST',
        data: fd,
        processData: false,
        contentType: false,
        dataType: 'json'
    }).done(function(r) {
        if (r && r.success) {
            if (typeof swal !== 'undefined') swal('Cancelado', r.message, 'info');
            else alert('Despacho cancelado.');
            $('#contenidoDespachos').data('loaded', false);
            cargarDespachos();
            cargarResumen();
        } else {
            alert((r && (r.error || r.message)) || 'Error al cancelar');
        }
    }).fail(function() { alert('Error de conexión'); });
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
