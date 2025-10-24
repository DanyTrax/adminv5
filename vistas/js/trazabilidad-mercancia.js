/*=============================================
TRAZABILIDAD DE MERCANCÍA
=============================================*/

$(document).ready(function() {
    
    // Inicializar pestañas
    $('#tabsTrazabilidad a').click(function (e) {
        e.preventDefault();
        $(this).tab('show');
    });
    
    // Eventos de búsqueda general
    $('#btnBuscarGeneral').click(function() {
        buscarGeneral();
    });
    
    // Eventos de búsqueda por despacho
    $('#btnBuscarDespacho').click(function() {
        buscarPorDespacho();
    });
    
    // Eventos de búsqueda por producto
    $('#btnBuscarProducto').click(function() {
        buscarPorProducto();
    });
    
    // Eventos de reportes
    $('#btnActualizarReportes').click(function() {
        cargarReportes();
    });
    
    // Cargar reportes al abrir la pestaña
    $('#tabsTrazabilidad a[href="#tabReportes"]').on('shown.bs.tab', function (e) {
        cargarReportes();
    });
});

/*=============================================
BÚSQUEDA GENERAL
=============================================*/
function buscarGeneral() {
    var despacho = $('#buscarDespachoGeneral').val();
    var producto = $('#buscarProductoGeneral').val();
    var usuario = $('#buscarUsuarioGeneral').val();
    
    if(!despacho && !producto && !usuario) {
        mostrarAlerta('warning', 'Debe especificar al menos un criterio de búsqueda');
        return;
    }
    
    // Mostrar loading
    $('#btnBuscarGeneral').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Buscando...');
    
    $.ajax({
        url: 'ajax/trazabilidad.ajax.php',
        method: 'POST',
        data: {
            accion: 'buscar_general',
            despacho: despacho,
            producto: producto,
            usuario: usuario
        },
        dataType: 'json',
        success: function(response) {
            if(response.success) {
                mostrarResultadosGenerales(response.data);
            } else {
                mostrarAlerta('error', response.error);
            }
        },
        error: function() {
            mostrarAlerta('error', 'Error de conexión al buscar');
        },
        complete: function() {
            $('#btnBuscarGeneral').prop('disabled', false).html('<i class="fa fa-search"></i> Buscar');
        }
    });
}

/*=============================================
BÚSQUEDA POR DESPACHO
=============================================*/
function buscarPorDespacho() {
    var numeroDespacho = $('#buscarDespacho').val();
    
    if(!numeroDespacho) {
        mostrarAlerta('warning', 'Debe especificar el número de despacho');
        return;
    }
    
    // Mostrar loading
    $('#btnBuscarDespacho').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Buscando...');
    
    $.ajax({
        url: 'ajax/trazabilidad.ajax.php',
        method: 'POST',
        data: {
            accion: 'buscar_por_despacho',
            numero_despacho: numeroDespacho
        },
        dataType: 'json',
        success: function(response) {
            if(response.success) {
                mostrarResultadosDespacho(response.data, response.despacho);
            } else {
                mostrarAlerta('error', response.error);
            }
        },
        error: function() {
            mostrarAlerta('error', 'Error de conexión al buscar despacho');
        },
        complete: function() {
            $('#btnBuscarDespacho').prop('disabled', false).html('<i class="fa fa-search"></i> Buscar Despacho');
        }
    });
}

/*=============================================
BÚSQUEDA POR PRODUCTO
=============================================*/
function buscarPorProducto() {
    var codigoProducto = $('#buscarProducto').val();
    
    if(!codigoProducto) {
        mostrarAlerta('warning', 'Debe especificar el código del producto');
        return;
    }
    
    // Mostrar loading
    $('#btnBuscarProducto').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Buscando...');
    
    $.ajax({
        url: 'ajax/trazabilidad.ajax.php',
        method: 'POST',
        data: {
            accion: 'buscar_por_producto',
            producto_codigo: codigoProducto
        },
        dataType: 'json',
        success: function(response) {
            if(response.success) {
                mostrarResultadosProducto(response.data, response.producto);
            } else {
                mostrarAlerta('error', response.error);
            }
        },
        error: function() {
            mostrarAlerta('error', 'Error de conexión al buscar producto');
        },
        complete: function() {
            $('#btnBuscarProducto').prop('disabled', false).html('<i class="fa fa-search"></i> Buscar Producto');
        }
    });
}

/*=============================================
MOSTRAR RESULTADOS GENERALES
=============================================*/
function mostrarResultadosGenerales(data) {
    var html = '';
    
    if(data.length === 0) {
        html = '<tr><td colspan="6" class="text-center text-muted">No se encontraron resultados</td></tr>';
    } else {
        data.forEach(function(item) {
            var estadoClass = item.estado === 'entregado' ? 'success' : 
                             item.estado === 'parcial' ? 'warning' : 
                             item.estado === 'en_transito' ? 'info' : 'danger';
            
            html += '<tr>';
            html += '<td>' + item.numero_despacho + '</td>';
            html += '<td>' + item.producto_codigo + '</td>';
            html += '<td>' + (item.usuario_destino_nombre || 'N/A') + '</td>';
            html += '<td>' + formatearFecha(item.fecha_aceptacion) + '</td>';
            html += '<td><span class="label label-' + estadoClass + '">' + item.estado.toUpperCase() + '</span></td>';
            html += '<td>';
            html += '<button class="btn btn-info btn-xs btnVerDetalle" data-despacho="' + item.numero_despacho + '">';
            html += '<i class="fa fa-eye"></i>';
            html += '</button>';
            html += '</td>';
            html += '</tr>';
        });
    }
    
    $('#tablaResultadosGenerales tbody').html(html);
    $('#resultadosBusquedaGeneral').show();
}

/*=============================================
MOSTRAR RESULTADOS POR DESPACHO
=============================================*/
function mostrarResultadosDespacho(data, despacho) {
    // Información del despacho
    var infoHtml = '<div class="alert alert-info">';
    infoHtml += '<h4><i class="fa fa-truck"></i> Despacho: ' + despacho + '</h4>';
    if(data.length > 0) {
        infoHtml += '<p><strong>Transportador:</strong> ' + (data[0].transportador_nombre || 'N/A') + '</p>';
        infoHtml += '<p><strong>Fecha de Aceptación:</strong> ' + formatearFecha(data[0].fecha_aceptacion) + '</p>';
        infoHtml += '<p><strong>Sucursal Origen:</strong> ' + data[0].sucursal_origen + '</p>';
    }
    infoHtml += '</div>';
    $('#infoDespacho').html(infoHtml);
    
    // Tabla de productos
    var html = '';
    data.forEach(function(item) {
        var estadoClass = item.estado === 'entregado' ? 'success' : 
                         item.estado === 'parcial' ? 'warning' : 'info';
        
        html += '<tr>';
        html += '<td>' + item.producto_codigo + '</td>';
        html += '<td>' + item.producto_descripcion + '</td>';
        html += '<td><span class="badge badge-primary">' + item.cantidad_total + '</span></td>';
        html += '<td><span class="badge badge-success">' + item.cantidad_descargada + '</span></td>';
        html += '<td><span class="badge badge-warning">' + item.cantidad_pendiente + '</span></td>';
        html += '<td><span class="label label-' + estadoClass + '">' + item.estado.toUpperCase() + '</span></td>';
        html += '</tr>';
    });
    $('#tablaProductosDespacho tbody').html(html);
    
    // Historial de descargas
    var historialHtml = '';
    data.forEach(function(item) {
        if(item.descargas && item.descargas.length > 0) {
            item.descargas.forEach(function(descarga) {
                historialHtml += '<tr>';
                historialHtml += '<td>' + formatearFecha(descarga.fecha_descarga) + '</td>';
                historialHtml += '<td>' + (descarga.usuario_descarga_nombre || 'N/A') + '</td>';
                historialHtml += '<td>' + item.producto_codigo + '</td>';
                historialHtml += '<td><span class="badge badge-info">' + descarga.cantidad_descargada + '</span></td>';
                historialHtml += '<td>' + descarga.sucursal_descarga + '</td>';
                historialHtml += '</tr>';
            });
        }
    });
    
    if(historialHtml === '') {
        historialHtml = '<tr><td colspan="5" class="text-center text-muted">No hay descargas registradas</td></tr>';
    }
    
    $('#tablaHistorialDespacho tbody').html(historialHtml);
    $('#resultadosDespacho').show();
}

/*=============================================
MOSTRAR RESULTADOS POR PRODUCTO
=============================================*/
function mostrarResultadosProducto(data, producto) {
    // Información del producto
    var infoHtml = '<div class="alert alert-success">';
    infoHtml += '<h4><i class="fa fa-cube"></i> Producto: ' + producto + '</h4>';
    if(data.length > 0) {
        infoHtml += '<p><strong>Descripción:</strong> ' + data[0].producto_descripcion + '</p>';
        var totalMovimientos = data.length;
        var totalCantidad = data.reduce(function(sum, item) { return sum + item.cantidad_total; }, 0);
        infoHtml += '<p><strong>Total Movimientos:</strong> ' + totalMovimientos + '</p>';
        infoHtml += '<p><strong>Total Cantidad:</strong> ' + totalCantidad + '</p>';
    }
    infoHtml += '</div>';
    $('#infoProducto').html(infoHtml);
    
    // Tabla de despachos
    var html = '';
    data.forEach(function(item) {
        var estadoClass = item.estado === 'entregado' ? 'success' : 
                         item.estado === 'parcial' ? 'warning' : 'info';
        
        html += '<tr>';
        html += '<td>' + item.numero_despacho + '</td>';
        html += '<td>' + formatearFecha(item.fecha_aceptacion) + '</td>';
        html += '<td><span class="badge badge-primary">' + item.cantidad_total + '</span></td>';
        html += '<td><span class="badge badge-success">' + item.cantidad_descargada + '</span></td>';
        html += '<td><span class="badge badge-warning">' + item.cantidad_pendiente + '</span></td>';
        html += '<td><span class="label label-' + estadoClass + '">' + item.estado.toUpperCase() + '</span></td>';
        html += '</tr>';
    });
    $('#tablaDespachosProducto tbody').html(html);
    
    // Historial de descargas
    var historialHtml = '';
    data.forEach(function(item) {
        if(item.descargas && item.descargas.length > 0) {
            item.descargas.forEach(function(descarga) {
                historialHtml += '<tr>';
                historialHtml += '<td>' + formatearFecha(descarga.fecha_descarga) + '</td>';
                historialHtml += '<td>' + (descarga.usuario_descarga_nombre || 'N/A') + '</td>';
                historialHtml += '<td>' + item.numero_despacho + '</td>';
                historialHtml += '<td><span class="badge badge-info">' + descarga.cantidad_descargada + '</span></td>';
                historialHtml += '<td>' + descarga.sucursal_descarga + '</td>';
                historialHtml += '</tr>';
            });
        }
    });
    
    if(historialHtml === '') {
        historialHtml = '<tr><td colspan="5" class="text-center text-muted">No hay descargas registradas</td></tr>';
    }
    
    $('#tablaHistorialProducto tbody').html(historialHtml);
    $('#resultadosProducto').show();
}

/*=============================================
CARGAR REPORTES
=============================================*/
function cargarReportes() {
    // Mostrar loading
    $('#btnActualizarReportes').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Cargando...');
    
    $.ajax({
        url: 'ajax/trazabilidad.ajax.php',
        method: 'POST',
        data: { accion: 'obtener_reportes' },
        dataType: 'json',
        success: function(response) {
            if(response.success) {
                mostrarReportes(response.data);
            } else {
                mostrarAlerta('error', response.error);
            }
        },
        error: function() {
            mostrarAlerta('error', 'Error de conexión al cargar reportes');
        },
        complete: function() {
            $('#btnActualizarReportes').prop('disabled', false).html('<i class="fa fa-refresh"></i> Actualizar');
        }
    });
}

/*=============================================
MOSTRAR REPORTES
=============================================*/
function mostrarReportes(data) {
    // Productos más movidos
    var htmlProductos = '';
    if(data.productos_mas_movidos && data.productos_mas_movidos.length > 0) {
        data.productos_mas_movidos.forEach(function(item) {
            htmlProductos += '<tr>';
            htmlProductos += '<td>' + item.producto_codigo + '</td>';
            htmlProductos += '<td>' + item.producto_descripcion + '</td>';
            htmlProductos += '<td><span class="badge badge-primary">' + item.total_movimientos + '</span></td>';
            htmlProductos += '<td><span class="badge badge-success">' + item.total_cantidad + '</span></td>';
            htmlProductos += '</tr>';
        });
    } else {
        htmlProductos = '<tr><td colspan="4" class="text-center text-muted">No hay datos disponibles</td></tr>';
    }
    $('#tablaProductosMovidos tbody').html(htmlProductos);
    
    // Usuarios más activos
    var htmlUsuarios = '';
    if(data.usuarios_mas_activos && data.usuarios_mas_activos.length > 0) {
        data.usuarios_mas_activos.forEach(function(item) {
            htmlUsuarios += '<tr>';
            htmlUsuarios += '<td>' + (item.nombre || 'N/A') + '</td>';
            htmlUsuarios += '<td><span class="badge badge-primary">' + item.total_descargas + '</span></td>';
            htmlUsuarios += '<td><span class="badge badge-success">' + item.total_cantidad + '</span></td>';
            htmlUsuarios += '</tr>';
        });
    } else {
        htmlUsuarios = '<tr><td colspan="3" class="text-center text-muted">No hay datos disponibles</td></tr>';
    }
    $('#tablaUsuariosActivos tbody').html(htmlUsuarios);
    
    // Despachos incompletos
    var htmlDespachos = '';
    if(data.despachos_incompletos && data.despachos_incompletos.length > 0) {
        data.despachos_incompletos.forEach(function(item) {
            htmlDespachos += '<tr>';
            htmlDespachos += '<td>' + item.numero_despacho + '</td>';
            htmlDespachos += '<td><span class="badge badge-primary">' + item.total_productos + '</span></td>';
            htmlDespachos += '<td><span class="badge badge-success">' + item.productos_entregados + '</span></td>';
            htmlDespachos += '<td><span class="badge badge-warning">' + item.productos_parciales + '</span></td>';
            htmlDespachos += '<td><span class="badge badge-danger">' + item.productos_pendientes + '</span></td>';
            htmlDespachos += '</tr>';
        });
    } else {
        htmlDespachos = '<tr><td colspan="5" class="text-center text-muted">No hay despachos incompletos</td></tr>';
    }
    $('#tablaDespachosIncompletos tbody').html(htmlDespachos);
}

/*=============================================
FUNCIONES AUXILIARES
=============================================*/
function formatearFecha(fecha) {
    if(!fecha) return 'N/A';
    
    var date = new Date(fecha);
    return date.toLocaleDateString('es-ES') + ' ' + date.toLocaleTimeString('es-ES');
}

function mostrarAlerta(tipo, mensaje) {
    var alertClass = tipo === 'success' ? 'alert-success' : 
                    tipo === 'warning' ? 'alert-warning' : 
                    tipo === 'error' ? 'alert-danger' : 'alert-info';
    
    var html = '<div class="alert ' + alertClass + ' alert-dismissible">';
    html += '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>';
    html += '<h4><i class="icon fa fa-' + (tipo === 'success' ? 'check' : tipo === 'warning' ? 'warning' : tipo === 'error' ? 'ban' : 'info') + '"></i> ' + (tipo === 'success' ? 'Éxito' : tipo === 'warning' ? 'Advertencia' : tipo === 'error' ? 'Error' : 'Información') + '</h4>';
    html += mensaje;
    html += '</div>';
    
    // Mostrar alerta en la parte superior de la página
    $('.content').prepend(html);
    
    // Auto-ocultar después de 5 segundos
    setTimeout(function() {
        $('.alert').fadeOut();
    }, 5000);
}
